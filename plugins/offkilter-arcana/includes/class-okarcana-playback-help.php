<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Opt-in assistance only: Termly and VidMov retain consent and access control.
 */
class OKArcana_Playback_Help
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'), 20);
    }

    public static function enqueue_assets()
    {
        $enabled = defined('OKTV_PLAYBACK_HELP_ENABLED') && OKTV_PLAYBACK_HELP_ENABLED === true;
        if (!apply_filters('oktv_playback_help_enabled', $enabled) || is_admin()) {
            return;
        }

        $video = is_singular('vidmov_video')
            && get_post_status(get_queried_object_id()) === 'publish'
            && !post_password_required();
        $category = '';
        if ($video && !isset($_GET['trailer'])) {
            // Confirmed site mapping, not a universal classification of YouTube.
            // Native protection may substitute a different trailer provider.
            $url = get_post_meta(get_queried_object_id(), 'beeteam368_video_url', true);
            $category = self::consent_category($url);
        }

        $config = array(
            'video' => $video,
            'category' => $category,
            'strings' => array(
                'summary' => __('Playback help', 'offkilter-arcana'),
                'consent' => __('Your current privacy settings do not allow functionality cookies. This video may need them. You can review your choices without enabling analytics or advertising.', 'offkilter-arcana'),
                'consentPresent' => __('A player is already present, but your current privacy settings do not allow functionality cookies. This does not confirm successful playback. Review your preferences or reload to let the site re-evaluate your current choices. Reloading may discard an unfinished comment.', 'offkilter-arcana'),
                'loading' => __('The player is still loading. If it does not start, review your privacy preferences or reload this page. Your choices stay yours.', 'offkilter-arcana'),
                'unavailable' => __('The player has not initialized. Privacy settings, a connection issue, or the video provider may be responsible. Review your preferences or reload when you are ready.', 'offkilter-arcana'),
                'ready' => __('Use the player controls to watch. If playback fails, review your privacy preferences or reload this page. Reloading will interrupt playback and may discard an unfinished comment.', 'offkilter-arcana'),
                'preferences' => __('Privacy preferences', 'offkilter-arcana'),
                'reload' => __('Reload page', 'offkilter-arcana'),
                'reloadNote' => __('Reloading may discard an unfinished comment.', 'offkilter-arcana'),
                'preferencesRequested' => __('If privacy controls do not open, try again shortly or reload after saving any unfinished comment. No privacy choices have been changed by playback help.', 'offkilter-arcana'),
                'preferencesUnavailable' => __('Privacy controls are not available yet. Try again shortly, or reload after saving any unfinished comment. No privacy choices have been changed.', 'offkilter-arcana'),
            ),
        );
        foreach (array('css', 'js') as $type) {
            $path = 'assets/' . $type . '/ok-playback-help.' . $type;
            // Reproducible release archives normalize mtimes; bytes own cache identity.
            $version = substr(hash_file('sha256', OKARCANA_PLUGIN_DIR . $path), 0, 16);
            if ($type === 'css') {
                wp_enqueue_style('oktv-playback-help', OKARCANA_PLUGIN_URL . $path, array(), $version);
            } else {
                wp_enqueue_script('oktv-playback-help', OKARCANA_PLUGIN_URL . $path, array(), $version, true);
            }
        }
        wp_add_inline_script('oktv-playback-help', 'window.OKTVPlaybackHelpConfig = ' . wp_json_encode($config) . ';', 'before');
    }

    public static function consent_category($url)
    {
        if (!is_string($url) || $url !== trim($url)) {
            return '';
        }
        $parts = wp_parse_url($url);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)
            || isset($parts['user']) || isset($parts['pass'])) {
            return '';
        }
        $hosts = array('youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com');
        return in_array(strtolower($parts['host']), $hosts, true) ? 'performance' : '';
    }
}
