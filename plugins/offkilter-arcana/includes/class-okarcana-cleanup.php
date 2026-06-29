<?php
/**
 * OKArcana_Cleanup — Public Surface Lockdown (Priority Zero).
 *
 * Defensive layer that prevents VidMov / ARMember admin-editor metadata
 * (Purchase Price, Expiration, Video/Audio Categories editor fields, Pay Per
 * View controls) from leaking onto public pages.
 *
 * The VidMov theme source is not part of this repository, so the underlying
 * templates cannot be corrected here. This class removes the leak at the DATA
 * layer (v2.1) rather than relying on CSS:
 *   - `get_post_metadata` guard empties display/authoring monetization meta for
 *     anonymous / non-editor views, so the theme template renders nothing.
 *   - Filterable `remove_action` list + authoring-shortcode stripping neutralize
 *     the theme-rendered submission/edit form (the category-editor + price/PPV box).
 *   - An admin-only diagnostic (`?okarcana_diag=surface`) dumps the post's meta
 *     keys + bound hook callbacks to pinpoint the exact culprit for a precise lock.
 *   - `the_content` strip + `ok-frontend` body class (v2.0) remain as belt-and-braces.
 *
 * `beeteam368_membership_plans` is intentionally NOT touched — it drives access
 * gating. The permanent fix is a child-theme template override on production — see
 * docs/platform-v2-product-review.md and docs/platform-v2_1-destination-strategy.md.
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Cleanup
{
    public static function init()
    {
        add_filter('the_content', array(__CLASS__, 'strip_leaked_meta'), 5);
        add_filter('body_class', array(__CLASS__, 'mark_frontend'));

        // Priority Zero — data-layer lockdown (not CSS):
        // empty display/authoring monetization meta for anonymous/non-editor views.
        add_filter('get_post_metadata', array(__CLASS__, 'guard_monetization_meta'), 10, 4);

        // Remove operator-supplied theme/plugin render hooks for anonymous views,
        // and strip authoring shortcodes that leak the submission/edit form.
        add_action('wp', array(__CLASS__, 'guard_singular_video'));
        add_filter('the_content', array(__CLASS__, 'strip_authoring_shortcodes'), 4);

        // Admin-only forensic diagnostic to pinpoint the exact theme hook/meta keys.
        add_action('wp_footer', array(__CLASS__, 'maybe_render_diagnostic'), 999);
    }

    /**
     * True when the current request is an anonymous / non-editor frontend view of
     * the given object — i.e. the public surface we must lock down.
     *
     * @param int $object_id
     * @return bool
     */
    private static function is_public_view($object_id)
    {
        if (is_admin()) {
            return false;
        }
        // Logged-out, or logged in but cannot edit this post.
        return !current_user_can('edit_post', (int) $object_id);
    }

    /**
     * Meta-key patterns whose VALUES are display/authoring monetization fields that
     * must never render on the public surface. NOTE: `beeteam368_membership_plans`
     * is intentionally excluded — it drives access gating, and emptying it could
     * unlock gated content. Filterable so the operator can tighten with exact keys
     * captured from the diagnostic.
     *
     * @return string[]
     */
    private static function monetization_patterns()
    {
        $patterns = array(
            '/(^|_)price($|_)/i',
            '/(^|_)ppv($|_)/i',
            '/pay[_-]?per[_-]?view/i',
            '/(^|_)purchase($|_)/i',
            '/expir/i',
            '/monetiz/i',
        );

        return apply_filters('okarcana_guard_meta_patterns', $patterns);
    }

    /**
     * Data-layer suppression: short-circuit get_post_meta() for monetization
     * display/authoring keys on the public surface so the theme template reads an
     * empty value and renders nothing. This removes the field from anonymous
     * rendering at the source — no CSS involved.
     *
     * @param mixed  $value     Short-circuit value (null = let WP resolve normally).
     * @param int    $object_id Post ID.
     * @param string $meta_key  Requested meta key ('' for "all meta").
     * @param bool   $single
     * @return mixed
     */
    public static function guard_monetization_meta($value, $object_id, $meta_key, $single)
    {
        // "Get all meta" calls pass an empty key — don't interfere (avoids recursion
        // and over-broad suppression); we only act on specific, named keys.
        if ($meta_key === '' || $meta_key === null) {
            return $value;
        }

        if (!self::is_public_view($object_id)) {
            return $value;
        }

        foreach (self::monetization_patterns() as $pattern) {
            if (preg_match($pattern, (string) $meta_key)) {
                // Mirror WP's expected shape: '' for single, [] for multi.
                return $single ? '' : array();
            }
        }

        return $value;
    }

    /**
     * On singular video views for the public surface, remove operator-identified
     * theme/plugin render hooks. Ships empty: the exact VidMov hook isn't knowable
     * from this repo (theme not included), so the operator captures it via the
     * diagnostic and supplies it through the filter below.
     *
     * Each entry: array('hook' => string, 'callback' => callable|string, 'priority' => int).
     */
    public static function guard_singular_video()
    {
        if (is_admin() || !is_singular()) {
            return;
        }

        $object_id = get_queried_object_id();
        if (!self::is_public_view($object_id)) {
            return;
        }

        /**
         * Example (operator fills from diagnostic output):
         *   add_filter('okarcana_guard_remove_actions', function ($r) {
         *     $r[] = array('hook' => 'vidmov_after_single_video', 'callback' => 'beeteam368_render_ppv_box', 'priority' => 10);
         *     return $r;
         *   });
         */
        $removals = apply_filters('okarcana_guard_remove_actions', array());

        foreach ($removals as $r) {
            if (!empty($r['hook']) && isset($r['callback'])) {
                remove_action($r['hook'], $r['callback'], isset($r['priority']) ? (int) $r['priority'] : 10);
                remove_filter($r['hook'], $r['callback'], isset($r['priority']) ? (int) $r['priority'] : 10);
            }
        }
    }

    /**
     * Strip authoring/submission shortcodes (the VidMov frontend submit/edit form,
     * which carries the category-editor + price/PPV/expiration fields) from content
     * on the public surface. Tag list is filterable.
     *
     * @param string $content
     * @return string
     */
    public static function strip_authoring_shortcodes($content)
    {
        if (is_admin() || !is_string($content) || $content === '') {
            return $content;
        }

        $object_id = get_the_ID();
        if ($object_id && !self::is_public_view($object_id)) {
            return $content;
        }

        $tags = apply_filters('okarcana_guard_strip_shortcodes', array(
            'bt368_submit_video',
            'bt368_edit_video',
            'beeteam368_submit_video',
            'beeteam368_edit_video',
            'vidmov_submit_video',
            'vidmov_edit_video',
        ));

        foreach ($tags as $tag) {
            $tag = preg_quote((string) $tag, '#');
            // Remove both self-closing and enclosing shortcode forms.
            $content = preg_replace('#\[' . $tag . '[^\]]*\](.*?\[/' . $tag . '\])?#is', '', $content);
        }

        return $content;
    }

    /**
     * Admin-only forensic diagnostic. Visit any single video page as an admin with
     * `?okarcana_diag=surface` to dump the post's meta keys and the callbacks bound
     * to the key render hooks — so the exact theme/plugin culprit can be identified
     * and locked precisely via the filters above.
     */
    public static function maybe_render_diagnostic()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (!isset($_GET['okarcana_diag']) || sanitize_key(wp_unslash($_GET['okarcana_diag'])) !== 'surface') {
            return;
        }
        if (!is_singular()) {
            return;
        }

        $object_id = get_queried_object_id();
        $meta = get_post_meta($object_id);
        $keys = is_array($meta) ? array_keys($meta) : array();

        echo "\n<!-- OKARCANA DIAGNOSTIC (admin only)\n";
        echo "post_id: " . (int) $object_id . "\n";
        echo "post_type: " . esc_html(get_post_type($object_id)) . "\n";
        echo "meta_keys:\n";
        foreach ($keys as $k) {
            echo "  - " . esc_html($k) . "\n";
        }

        global $wp_filter;
        foreach (array('the_content', 'wp_footer', 'wp_head') as $hook) {
            echo "hook {$hook} callbacks:\n";
            if (isset($wp_filter[$hook])) {
                foreach ($wp_filter[$hook]->callbacks as $priority => $cbs) {
                    foreach ($cbs as $cb) {
                        $name = self::describe_callback($cb['function']);
                        echo "  - [{$priority}] " . esc_html($name) . "\n";
                    }
                }
            }
        }
        echo "-->\n";
    }

    private static function describe_callback($fn)
    {
        if (is_string($fn)) {
            return $fn;
        }
        if (is_array($fn) && count($fn) === 2) {
            $obj = is_object($fn[0]) ? get_class($fn[0]) : (string) $fn[0];
            return $obj . '::' . (string) $fn[1];
        }
        if ($fn instanceof Closure) {
            return 'Closure';
        }
        return 'unknown';
    }

    /**
     * Add a guaranteed frontend-only body class so CSS Section V can scope
     * suppression rules without affecting wp-admin (the stylesheet is only
     * enqueued on wp_enqueue_scripts, but the marker keeps intent explicit).
     *
     * @param array $classes
     * @return array
     */
    public static function mark_frontend($classes)
    {
        $classes[] = 'ok-frontend';
        return $classes;
    }

    /**
     * Remove the_meta()/custom-field dumps if they appear *inside* post content.
     *
     * Most leaks are template-rendered (outside the_content) and handled by CSS
     * Section V, but any meta list embedded in content is removed here. Scoped to
     * the frontend and to common leaked-markup signatures to stay conservative.
     *
     * @param string $content
     * @return string
     */
    public static function strip_leaked_meta($content)
    {
        if (is_admin() || !is_string($content) || $content === '') {
            return $content;
        }

        // the_meta() output: <ul class="post-meta">...</ul>
        $content = preg_replace(
            '#<ul[^>]*class=["\'][^"\']*post-meta[^"\']*["\'][^>]*>.*?</ul>#is',
            '',
            $content
        );

        return $content;
    }
}
