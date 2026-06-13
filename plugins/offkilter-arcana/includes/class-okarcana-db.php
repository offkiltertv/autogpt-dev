<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_DB
{
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . OKARCANA_QUEUE_TABLE;
    }

    public static function create_tables()
    {
        global $wpdb;

        $table_name = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED DEFAULT NULL,
            source_type VARCHAR(32) NOT NULL,
            source_name VARCHAR(191) DEFAULT '',
            source_ref VARCHAR(191) DEFAULT '',
            youtube_id VARCHAR(32) NOT NULL,
            youtube_url TEXT NOT NULL,
            payload LONGTEXT NULL,
            queue_state VARCHAR(20) NOT NULL DEFAULT 'imported',
            imported_at DATETIME NOT NULL,
            scheduled_for DATETIME NULL,
            published_at DATETIME NULL,
            error_message TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_youtube_id (youtube_id),
            KEY idx_post_id (post_id),
            KEY idx_queue_state (queue_state),
            KEY idx_scheduled_for (scheduled_for)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
