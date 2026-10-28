<?php
/** Operator-installed, default-off scope restriction. Not part of plugin.zip. */
if (!defined('ABSPATH')) exit;

function oktv_playback_canary_ready()
{
    $marker = '/opt/bitnami/oktv-release-state/playback-canary-enabled';
    if (!is_file($marker)
        || trim((string) file_get_contents($marker)) !== 'oktv-playback-3.3.6-db31a5a937a7d1af'
        || !defined('OKARCANA_VERSION') || OKARCANA_VERSION !== '3.3.6'
        || !class_exists('OKArcana_Playback_Help')
        || is_admin() || is_user_logged_in() || wp_doing_ajax() || wp_doing_cron()
        || (defined('REST_REQUEST') && REST_REQUEST)
        || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET'
        || ($_SERVER['HTTP_HOST'] ?? '') !== 'www.offkilter.tv'
        || ($_SERVER['REQUEST_URI'] ?? '') !== '/video/why-i-want-youtube-to-be-my-main-platform-streaming-stream/'
        || !is_ssl() || !is_singular('vidmov_video') || get_queried_object_id() !== 7917) return false;
    $post = get_post(7917);
    return $post && $post->post_status === 'publish' && $post->post_password === ''
        && $post->post_name === 'why-i-want-youtube-to-be-my-main-platform-streaming-stream';
}

// No query, cookie, account or remote API can turn the feature on.
add_filter('oktv_playback_help_enabled', 'oktv_playback_canary_ready', PHP_INT_MAX);
add_action('template_redirect', function () {
    if (!oktv_playback_canary_ready()) return;
    if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
    nocache_headers();
    header('X-OKTV-Playback-Release: oktv-playback-3.3.6-db31a5a937a7d1af');
}, 0);
