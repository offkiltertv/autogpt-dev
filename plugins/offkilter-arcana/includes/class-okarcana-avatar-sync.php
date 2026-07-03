<?php
/**
 * OKArcana_Avatar_Sync — YouTube creator avatar / profile sync.
 *
 * Post-import enrichment for imported creators, per
 * docs/arcana-avatar-automation-roadmap.md. When a vidmov_video is imported and
 * its author lacks an avatar, resolve the source YouTube channel avatar,
 * sideload it, write the Beeteam avatar size-set + profile linkage, and mirror
 * the URL to okarcana_avatar so plugin discovery surfaces agree with the theme.
 *
 * No VidMov / WP-Automatic core edits. Network access is only used server-side
 * during enrichment (the same egress WP-Automatic already relies on). All work
 * is guarded, idempotent, filterable, and reversible.
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Avatar_Sync
{
    /** Beeteam avatar crop sizes (mirrors the theme's schema). */
    const SIZES = array(28, 56, 50, 100, 61, 122);

    /** Per-user status meta: '', 'synced', 'failed:<reason>', 'skipped'. */
    const META_STATUS = '_okarcana_avatar_sync';

    public static function init()
    {
        // Enrich shortly after WP-Automatic writes the imported post. Late
        // priority so channel_id / video-url custom fields are already saved.
        add_action('save_post_vidmov_video', array(__CLASS__, 'on_video_saved'), 50, 3);
    }

    /**
     * Import hook: enrich the author's avatar if missing. Cheap guards first;
     * the network path only runs for a genuinely un-avatared author.
     */
    public static function on_video_saved($post_id, $post, $update)
    {
        if (!self::enabled() || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        if (get_post_status($post_id) === 'auto-draft') {
            return;
        }

        $user_id = (int) $post->post_author;
        if ($user_id <= 0 || self::user_has_avatar($user_id)) {
            return;
        }

        // Only act on imported content (has a source video URL / channel id),
        // and only once per user per pending state.
        if (get_user_meta($user_id, self::META_STATUS, true) === 'synced') {
            return;
        }

        self::sync_user($user_id, $post_id);
    }

    public static function enabled()
    {
        $on = !class_exists('OKArcana_Settings') || OKArcana_Settings::get('enable_avatar_sync', 1);
        return (bool) apply_filters('okarcana_avatar_sync_enabled', $on);
    }

    /**
     * Does the user already have a usable creator avatar (either system)?
     *
     * @param int $user_id
     * @return bool
     */
    public static function user_has_avatar($user_id)
    {
        if (get_user_meta($user_id, 'beeteam368_user_avatar_wd_bf', true)) {
            return true;
        }
        if (get_user_meta($user_id, 'okarcana_avatar', true)) {
            return true;
        }
        return false;
    }

    /**
     * Backfill / pilot entry point. Resolves + applies (or, in dry-run, only
     * resolves and reports) the YouTube avatar for one user.
     *
     * @param int  $user_id
     * @param int  $source_post_id 0 = auto-detect the user's newest video
     * @param bool $dry_run        true = resolve + report, no writes
     * @return array  { status, avatar_url, attachment_id, message }
     */
    public static function sync_user($user_id, $source_post_id = 0, $dry_run = false)
    {
        $user_id = (int) $user_id;
        $result  = array('status' => 'failed', 'avatar_url' => '', 'attachment_id' => 0, 'message' => '');

        if (!get_userdata($user_id)) {
            $result['message'] = 'no such user';
            return $result;
        }

        if ($source_post_id <= 0) {
            $source_post_id = self::newest_video($user_id);
        }
        if ($source_post_id <= 0) {
            $result['message'] = 'no source video';
            return self::mark($user_id, $result, $dry_run);
        }

        // 1. Resolve the channel avatar URL from the source video.
        $avatar_url = self::resolve_channel_avatar($source_post_id);
        if (!$avatar_url) {
            $result['message'] = 'could not resolve channel avatar';
            return self::mark($user_id, $result, $dry_run);
        }
        $result['avatar_url'] = $avatar_url;

        if ($dry_run) {
            $result['status']  = 'resolved';
            $result['message'] = 'dry run — resolved only';
            return $result;
        }

        // 2. Sideload to the media library.
        $att_id = self::sideload($avatar_url, $user_id, $source_post_id);
        if (is_wp_error($att_id)) {
            $result['message'] = 'sideload failed: ' . $att_id->get_error_message();
            return self::mark($user_id, $result, false);
        }
        $result['attachment_id'] = (int) $att_id;

        // 3. Write Beeteam avatar meta (size-set + wd_bf) + okarcana mirror.
        self::write_avatar_meta($user_id, (int) $att_id);

        // 4. Ensure profile linkage.
        self::ensure_profile($user_id);

        update_user_meta($user_id, self::META_STATUS, 'synced');
        $result['status']  = 'synced';
        $result['message'] = 'ok';
        return $result;
    }

    /**
     * Resolve the YouTube channel avatar URL from a source video post.
     * Primary: channel_id meta -> channel page. Fallback: video URL -> oEmbed
     * author_url -> channel page. Returns a yt3.* URL or ''.
     *
     * @param int $post_id
     * @return string
     */
    public static function resolve_channel_avatar($post_id)
    {
        $channel_url = '';

        $channel_id = trim((string) get_post_meta($post_id, 'channel_id', true));
        if ($channel_id !== '' && preg_match('#^UC[\w-]{20,}$#', $channel_id)) {
            $channel_url = 'https://www.youtube.com/channel/' . $channel_id;
        }

        if ($channel_url === '') {
            $video_url = (string) get_post_meta($post_id, 'beeteam368_video_url', true);
            if ($video_url === '') {
                $video_url = self::first_youtube_url((string) get_post_field('post_content', $post_id));
            }
            if ($video_url !== '') {
                $author = self::oembed_author_url($video_url);
                if ($author !== '') {
                    $channel_url = $author;
                }
            }
        }

        if ($channel_url === '') {
            return '';
        }

        return self::scrape_channel_avatar($channel_url);
    }

    /** oEmbed (stable JSON) -> author_url. */
    private static function oembed_author_url($video_url)
    {
        $endpoint = 'https://www.youtube.com/oembed?format=json&url=' . rawurlencode($video_url);
        $resp = wp_remote_get($endpoint, array('timeout' => 12, 'user-agent' => 'OFFKILTER-avatar-sync/1.0'));
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            return '';
        }
        $data = json_decode((string) wp_remote_retrieve_body($resp), true);
        return (is_array($data) && !empty($data['author_url'])) ? (string) $data['author_url'] : '';
    }

    /** Fetch a channel/@handle page and extract the yt3 avatar URL. */
    private static function scrape_channel_avatar($channel_url)
    {
        $resp = wp_remote_get($channel_url, array('timeout' => 15, 'user-agent' => 'Mozilla/5.0 (compatible; OFFKILTER-avatar-sync/1.0)'));
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            return '';
        }
        $html = (string) wp_remote_retrieve_body($resp);

        // og:image is the most stable signal on a channel page.
        if (preg_match('#<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m)) {
            $url = html_entity_decode($m[1]);
            if (strpos($url, 'ggpht.com') !== false || strpos($url, 'ytimg.com') !== false) {
                return self::normalize_avatar_size($url);
            }
        }
        // Fallback: first avatar-shaped yt3 URL in the page JSON blob.
        if (preg_match('#https://yt3\.(?:ggpht|googleusercontent)\.com/[\w\-/=.]+#', $html, $m)) {
            return self::normalize_avatar_size($m[0]);
        }

        return '';
    }

    /** Request a reasonably large square crop (=s512) where the URL supports it. */
    private static function normalize_avatar_size($url)
    {
        $url = preg_replace('#=s\d+(-c)?#', '=s512-c', $url, 1, $count);
        return $url;
    }

    private static function first_youtube_url($content)
    {
        if (preg_match('#https?://(?:www\.)?(?:youtube\.com/[^\s"\'<]+|youtu\.be/[\w-]+)#', (string) $content, $m)) {
            return $m[0];
        }
        return '';
    }

    private static function newest_video($user_id)
    {
        $ids = get_posts(array(
            'post_type' => 'vidmov_video', 'post_status' => 'publish', 'author' => (int) $user_id,
            'numberposts' => 1, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC',
            'suppress_filters' => false,
        ));
        return !empty($ids) ? (int) $ids[0] : 0;
    }

    /**
     * Sideload a remote image into the media library.
     *
     * @return int|WP_Error attachment ID
     */
    private static function sideload($url, $user_id, $source_post_id)
    {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 20);
        if (is_wp_error($tmp)) {
            return $tmp;
        }

        $ext = 'jpg';
        if (preg_match('#\.(png|webp|jpg|jpeg)#i', $url, $m)) {
            $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        }
        $login = sanitize_file_name(get_the_author_meta('user_login', $user_id));
        $file  = array(
            'name'     => 'arcana-' . $login . '-yt-avatar.' . $ext,
            'tmp_name' => $tmp,
        );

        $att_id = media_handle_sideload($file, (int) $source_post_id, null);
        if (is_wp_error($att_id)) {
            @unlink($tmp);
            return $att_id;
        }
        return (int) $att_id;
    }

    /**
     * Write the Beeteam avatar size-set + wd_bf keys, plus the okarcana_avatar
     * mirror, from a media attachment. Uses the theme's crop generator when
     * available; otherwise maps every size to the uploaded file (WordPress still
     * serves it correctly — the theme reads these paths as URLs).
     *
     * @param int $user_id
     * @param int $att_id
     */
    public static function write_avatar_meta($user_id, $att_id)
    {
        $rel = (string) get_post_meta($att_id, '_wp_attached_file', true); // e.g. 2026/07/foo.jpg
        if ($rel === '') {
            return;
        }
        $rel_path = '/' . ltrim($rel, '/');
        $full_url = wp_get_attachment_url($att_id);
        $subdir   = dirname($rel); // e.g. 2026/07

        // Map each Beeteam crop size to the closest WP-generated intermediate
        // (square), falling back to the original. The theme reads these as
        // uploads-relative image paths, so either resolves to a valid <img src>.
        $map = array('original' => $rel_path);
        foreach (self::SIZES as $sz) {
            $img = image_get_intermediate_size($att_id, array($sz, $sz));
            if (is_array($img) && !empty($img['file'])) {
                $map[$sz] = '/' . ltrim($subdir . '/' . $img['file'], '/');
            } else {
                $map[$sz] = $rel_path;
            }
        }

        update_user_meta($user_id, 'beeteam368_user_avatar', $map);
        update_user_meta($user_id, 'beeteam368_user_avatar_wd_bf', $full_url);
        update_user_meta($user_id, 'beeteam368_user_avatar_wd_bf_id', (int) $att_id);

        // Mirror to the plugin surface so discovery cards/spotlights match.
        update_user_meta($user_id, 'okarcana_avatar', $full_url);
    }

    /**
     * Ensure a vidmov_user_profile post exists and is linked.
     *
     * @param int $user_id
     */
    public static function ensure_profile($user_id)
    {
        $pid = (int) get_user_meta($user_id, 'beeteam368_user_profile_id', true);
        if ($pid > 0 && get_post($pid)) {
            return;
        }

        $existing = get_posts(array(
            'post_type' => 'vidmov_user_profile', 'post_status' => 'any', 'author' => (int) $user_id,
            'numberposts' => 1, 'fields' => 'ids', 'suppress_filters' => false,
        ));
        if (!empty($existing)) {
            update_user_meta($user_id, 'beeteam368_user_profile_id', (int) $existing[0]);
            return;
        }

        $new = wp_insert_post(array(
            'post_type'   => 'vidmov_user_profile',
            'post_status' => 'publish',
            'post_author' => (int) $user_id,
            'post_title'  => get_the_author_meta('display_name', $user_id),
        ), true);
        if (!is_wp_error($new)) {
            update_user_meta($user_id, 'beeteam368_user_profile_id', (int) $new);
        }
    }

    /**
     * Mirror an already-set Beeteam avatar to okarcana_avatar without any
     * network access. Used to reconcile creators enriched before this module.
     *
     * @param int $user_id
     * @return bool true if a mirror value was written
     */
    public static function mirror_from_beeteam($user_id)
    {
        if (get_user_meta($user_id, 'okarcana_avatar', true)) {
            return false;
        }
        $url = (string) get_user_meta($user_id, 'beeteam368_user_avatar_wd_bf', true);
        if ($url === '') {
            $map = get_user_meta($user_id, 'beeteam368_user_avatar', true);
            if (is_array($map) && !empty($map['original'])) {
                $url = content_url('/uploads' . $map['original']);
            }
        }
        if ($url === '') {
            return false;
        }
        update_user_meta($user_id, 'okarcana_avatar', $url);
        return true;
    }

    /** Record a failure/skip status and return the result unchanged. */
    private static function mark($user_id, $result, $dry_run)
    {
        if (!$dry_run) {
            $reason = $result['message'] !== '' ? $result['message'] : 'unknown';
            update_user_meta($user_id, self::META_STATUS, 'failed:' . substr($reason, 0, 120));
        }
        return $result;
    }
}
