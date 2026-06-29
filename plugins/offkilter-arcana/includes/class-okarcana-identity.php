<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hooks WordPress image/avatar pipeline to serve OFFKILTER-branded fallbacks
 * without modifying VidMov theme files.
 */
class OKArcana_Identity
{
    const AVATAR_KEY = 'offkilter';

    public static function init()
    {
        add_filter('avatar_defaults', array(__CLASS__, 'register_avatar_default'));
        add_filter('get_avatar_url', array(__CLASS__, 'filter_avatar_url'), 10, 3);
        add_filter('wp_lazy_load_image_placeholder', array(__CLASS__, 'filter_lazy_placeholder'), 10, 2);
    }

    /**
     * Adds the OFFKILTER creator placeholder as a selectable default avatar
     * in WP Settings > Discussion > Default Avatar.
     */
    public static function register_avatar_default($defaults)
    {
        $defaults[self::AVATAR_KEY] = __('OFFKILTER Creator', 'offkilter-arcana');
        return $defaults;
    }

    /**
     * Returns the branded placeholder when a user has no real avatar.
     * Only applies when the resolved URL points to Gravatar's mystery-person
     * or when the active avatar_default setting is 'offkilter'.
     */
    public static function filter_avatar_url($url, $id_or_email, $args)
    {
        $default = get_option('avatar_default', 'mystery');

        if ($default === self::AVATAR_KEY) {
            return self::placeholder_url();
        }

        if (is_string($url) && strpos($url, 'gravatar.com') !== false && strpos($url, 'd=mystery') !== false) {
            return self::placeholder_url();
        }

        return $url;
    }

    /**
     * Replaces the browser-native lazy-load placeholder (a grey data-URI)
     * with the OFFKILTER creator placeholder SVG when images are deferred.
     * Applies only to vidmov_video posts where the image is the primary media.
     */
    public static function filter_lazy_placeholder($placeholder, $image)
    {
        if (!is_array($image) || empty($image[0])) {
            return $placeholder;
        }

        $src = (string) $image[0];
        if (strpos($src, 'vidmov') !== false || strpos($src, 'placeholder') !== false) {
            return self::placeholder_url();
        }

        return $placeholder;
    }

    private static function placeholder_url()
    {
        return OKARCANA_PLUGIN_URL . 'assets/img/creator-placeholder.svg';
    }

    public static function channel_banner_url()
    {
        return OKARCANA_PLUGIN_URL . 'assets/img/default-channel-banner.svg';
    }
}
