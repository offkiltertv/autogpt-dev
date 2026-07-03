<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Frontend
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
        // v3.0 safe perf: preconnect to origins every page hits early
        // (Google Fonts CSS is render-blocking; YouTube serves embeds/thumbs).
        add_filter('wp_resource_hints', array(__CLASS__, 'resource_hints'), 10, 2);
    }

    public static function enqueue_styles()
    {
        wp_enqueue_style(
            'offkilter-platform',
            OKARCANA_PLUGIN_URL . 'assets/css/offkilter-platform.css',
            array(),
            OKARCANA_VERSION
        );
    }

    /**
     * Preconnect hints for third-party origins on the critical path.
     * Filterable via okarcana_preconnect_origins.
     *
     * @param array  $urls
     * @param string $relation_type
     * @return array
     */
    public static function resource_hints($urls, $relation_type)
    {
        if ($relation_type !== 'preconnect') {
            return $urls;
        }

        $origins = apply_filters('okarcana_preconnect_origins', array(
            array('href' => 'https://fonts.googleapis.com'),
            array('href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'),
            array('href' => 'https://i.ytimg.com'),
            array('href' => 'https://www.youtube.com'),
        ));

        foreach ($origins as $origin) {
            $urls[] = $origin;
        }

        return $urls;
    }
}
