<?php
if (!defined('ABSPATH')) { exit; }

// Enqueue parent stylesheet (required for child themes).
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'vidmov-parent',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme(get_template())->get('Version')
    );
});

// Explicitly disable the lockdown buffer when this child theme is active.
// The data-layer guard (okarcana_guard_monetization_meta) and content
// filters remain active as the primary protection layer.
// Template overrides for single-vidmov_video.php should be added here
// once the VidMov theme source is available for review (v2.8+).
add_filter('okarcana_guard_buffer_enabled', '__return_false');
