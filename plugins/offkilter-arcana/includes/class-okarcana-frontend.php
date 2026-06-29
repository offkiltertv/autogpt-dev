<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Frontend
{
    public static function init()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
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
}
