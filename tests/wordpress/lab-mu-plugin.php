<?php
// Synthetic lab only; no production configuration or data is imported.
if (wp_get_environment_type() !== 'local' || !defined('DB_ENGINE') || DB_ENGINE !== 'sqlite') exit;
add_filter('pre_wp_mail', '__return_false');
add_filter('pre_http_request', function () {
    return new WP_Error('local_lab_offline', 'External HTTP disabled in local integration lab.');
});
add_filter('oktv_playback_help_enabled', function ($enabled) {
    return !is_file(dirname(rtrim(ABSPATH, '/')) . '/private/disable-playback') && $enabled;
});
if (defined('WP_CLI') && WP_CLI) {
    set_error_handler(function ($severity, $message, $file) {
        return $severity === E_DEPRECATED && str_starts_with($file, 'phar://');
    });
}
