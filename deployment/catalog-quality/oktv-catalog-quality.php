<?php
/**
 * Plugin Name: OKTV Catalog Quality
 * Description: Keep unavailable video placeholders out of discovery while preserving their pages and discussions.
 * Version: 1.0.0
 */
declare(strict_types=1);

namespace OffKilter\CatalogQuality;

if (!defined('ABSPATH')) {
    exit;
}

final class CatalogQuality
{
    public const RELEASE = 'oktv-catalog-quality-1.0.0';
    public const MARKER = '/opt/bitnami/oktv-release-state/catalog-quality-enabled';
    public const SNAPSHOT = 'oktv_catalog_health_v1';
    public const AVAILABILITY = '_oktv_source_availability';
    private const SQL_MARKER = '/* oktv-catalog-quality */';
    private const UNAVAILABLE = ['unavailable', 'not_embeddable'];
    private const FRONTEND_AJAX = ['beeteam368_filter_posts'];

    public static function init(): void
    {
        add_filter('posts_where', [self::class, 'filter_where'], 30, 2);
        add_filter('the_content', [self::class, 'availability_notice'], 35);
        add_action('send_headers', [self::class, 'release_header']);
        add_action('admin_menu', [self::class, 'admin_menu']);
    }

    public static function marker_valid(string $bytes): bool
    {
        return trim($bytes) === self::RELEASE;
    }

    public static function enabled(): bool
    {
        if (defined('OKTV_CATALOG_QUALITY_ENABLED')) {
            return OKTV_CATALOG_QUALITY_ENABLED === true;
        }
        $bytes = is_file(self::MARKER) && !is_link(self::MARKER)
            ? @file_get_contents(self::MARKER, false, null, 0, 128) : false;
        return is_string($bytes) && self::marker_valid($bytes);
    }

    /** The filter changes discovery only; administrative and API queries retain their contract. */
    public static function request_eligible(array $context): bool
    {
        if (!empty($context['cli']) || !empty($context['cron']) || !empty($context['rest'])) {
            return false;
        }
        if (!empty($context['ajax'])) {
            return in_array($context['action'] ?? '', self::FRONTEND_AJAX, true);
        }
        return empty($context['admin']);
    }

    private static function public_request(): bool
    {
        return self::request_eligible([
            'cli' => defined('WP_CLI') && WP_CLI,
            'cron' => function_exists('wp_doing_cron') && wp_doing_cron(),
            'rest' => defined('REST_REQUEST') && REST_REQUEST,
            'ajax' => function_exists('wp_doing_ajax') && wp_doing_ajax(),
            'admin' => is_admin(),
            'action' => isset($_REQUEST['action']) && is_string($_REQUEST['action'])
                ? sanitize_key(wp_unslash($_REQUEST['action'])) : '',
        ]);
    }

    public static function filter_where(string $where, $query): string
    {
        return self::enabled() && self::public_request() ? self::discovery_where($where, $query) : $where;
    }

    /** Also callable from a read-only WP-CLI qualification without enabling the live release. */
    public static function discovery_where(string $where, $query): string
    {
        global $wpdb;
        if (!is_object($query) || !method_exists($query, 'get') || !method_exists($query, 'is_singular')
            || $query->is_singular() || strpos($where, self::SQL_MARKER) !== false) {
            return $where;
        }
        foreach (['p', 'page_id', 'name', 'pagename', 'attachment', 'attachment_id'] as $key) {
            if ($query->get($key)) {
                return $where;
            }
        }
        $statuses = $query->get('post_status');
        if ($statuses && array_diff((array) $statuses, ['publish'])) {
            return $where;
        }
        $types = $query->get('post_type');
        if ($types && $types !== 'any' && !in_array('vidmov_video', (array) $types, true)) {
            return $where;
        }
        $clause = $wpdb->prepare(
            " AND NOT ({$wpdb->posts}.post_type = %s AND ("
            . "LOWER(TRIM({$wpdb->posts}.post_title)) IN (%s,%s) OR EXISTS ("
            . "SELECT 1 FROM {$wpdb->postmeta} oktv_availability WHERE oktv_availability.post_id = {$wpdb->posts}.ID"
            . " AND oktv_availability.meta_key = %s AND oktv_availability.meta_value IN (%s,%s)))) ",
            'vidmov_video', 'deleted video', 'private video', self::AVAILABILITY, ...self::UNAVAILABLE
        );
        return $where . self::SQL_MARKER . $clause;
    }

    public static function placeholder(string $title): bool
    {
        return in_array(strtolower(trim($title)), ['deleted video', 'private video'], true);
    }

    public static function youtube_id(string $url): string
    {
        $parts = wp_parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return '';
        }
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $id = '';
        if ($host === 'youtu.be' && preg_match('~^/([A-Za-z0-9_-]{11})/?$~D', $path, $match)) {
            $id = $match[1];
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = is_string($query['v'] ?? null) ? $query['v'] : '';
            } elseif (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})/?$~D', $path, $match)) {
                $id = $match[1];
            }
        }
        return preg_match('/^[A-Za-z0-9_-]{11}$/D', $id) ? $id : '';
    }

    public static function availability_notice(string $content): string
    {
        if (!self::enabled() || !self::public_request() || !is_singular('vidmov_video')
            || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        $post = get_post();
        if (!$post || (!self::placeholder($post->post_title)
            && !in_array(get_post_meta($post->ID, self::AVAILABILITY, true), self::UNAVAILABLE, true))) {
            return $content;
        }
        $id = self::youtube_id((string) get_post_meta($post->ID, 'beeteam368_video_url', true));
        $link = $id ? ' <a href="' . esc_url('https://www.youtube.com/watch?v=' . $id)
            . '" rel="noopener noreferrer">Check the original on YouTube</a>.' : '';
        return '<aside class="oktv-source-notice" role="note"><p><strong>This source is currently unavailable on OKTV.</strong> '
            . 'This page and its discussion are preserved.' . $link . '</p></aside>' . $content;
    }

    public static function release_header(): void
    {
        if (self::enabled() && self::public_request() && !headers_sent()) {
            header('X-OKTV-Catalog-Quality: ' . self::RELEASE);
        }
    }

    public static function admin_menu(): void
    {
        add_management_page('OKTV Catalog Health', 'OKTV Catalog Health', 'manage_options', 'oktv-catalog-health', [self::class, 'render_admin']);
    }

    public static function render_admin(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view this page.'));
        }
        $snapshot = get_option(self::SNAPSHOT, []);
        $snapshot = is_array($snapshot) && ($snapshot['version'] ?? null) === 1 ? $snapshot : [];
        echo '<div class="wrap"><h1>OKTV Catalog Health</h1><p>Discovery protection: <strong>'
            . (self::enabled() ? 'Active' : 'Disabled') . '</strong>.</p>';
        echo '<p>Source checks measure library coverage separately from featured publishing. Existing pages and discussions are retained.</p>';
        if (!$snapshot) {
            echo '<div class="notice notice-info inline"><p>No completed source inventory has been recorded yet. Importer activity alone does not establish coverage.</p></div></div>';
            return;
        }
        echo '<p>Last source check: <strong>' . esc_html((string) ($snapshot['checked_at'] ?? 'Unknown')) . '</strong> (UTC).</p>';
        $checked = strtotime((string) ($snapshot['checked_at'] ?? '') . ' UTC');
        if (!$checked || time() - $checked > 14400) {
            echo '<div class="notice notice-warning inline"><p>The source check is over four hours old. Check the verified publishing job before treating these counts as current.</p></div>';
        }
        if (!empty($snapshot['last_error'])) {
            echo '<div class="notice notice-warning inline"><p>The latest attempt failed. These counts are from the last completed check.</p></div>';
        }
        echo '<table class="widefat striped"><thead><tr><th>Source</th><th>Eligible videos</th><th>Published</th><th>Draft</th><th>Missing</th><th>Unavailable at source</th><th>Embedding restricted</th><th>Unknown availability</th><th>Unreturned playlist items</th><th>Last verified import (UTC)</th></tr></thead><tbody>';
        foreach (($snapshot['sources'] ?? []) as $source) {
            if (!is_array($source)) {
                continue;
            }
            echo '<tr><th scope="row">' . esc_html((string) ($source['label'] ?? 'Source')) . '</th>';
            foreach (['eligible', 'published', 'draft', 'missing', 'unavailable', 'not_embeddable', 'unknown', 'unresolved_items'] as $field) {
                $value = isset($source[$field]) && is_int($source[$field]) && $source[$field] >= 0
                    ? (string) $source[$field] : 'Unknown';
                echo '<td>' . esc_html($value) . '</td>';
            }
            echo '<td>' . esc_html((string) ($source['last_import_at'] ?? 'No verified import recorded')) . '</td></tr>';
        }
        echo '</tbody></table><p>Missing counts cover the identifiers returned by YouTube. Unreturned playlist items remain unresolved; they do not count as completed imports. Drafts may include existing editorial holds. Unavailable and embedding-restricted sources are excluded from discovery while their pages and discussions are retained.</p></div>';
    }
}

CatalogQuality::init();
