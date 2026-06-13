<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Importer
{
    public static function import_manual_urls($raw_urls, $source_name, $source_type = 'manual')
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $raw_urls);
        $urls = array_values(array_filter(array_map('trim', $lines)));

        return self::import_urls($urls, $source_name, $source_type);
    }

    public static function import_csv_file($tmp_path, $source_name_fallback = 'CSV Import')
    {
        $fh = fopen($tmp_path, 'r');
        if (!$fh) {
            return array('created' => 0, 'skipped' => 0, 'errors' => array('Unable to read CSV file.'));
        }

        $header = fgetcsv($fh);
        if (!$header) {
            fclose($fh);
            return array('created' => 0, 'skipped' => 0, 'errors' => array('CSV header row is missing.'));
        }

        $header = array_map('trim', $header);
        $result = array('created' => 0, 'skipped' => 0, 'errors' => array());

        while (($row = fgetcsv($fh)) !== false) {
            $data = array();
            foreach ($header as $i => $key) {
                $data[$key] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }

            $url = !empty($data['youtube_url']) ? $data['youtube_url'] : (!empty($data['url']) ? $data['url'] : '');
            if (!$url) {
                $result['skipped']++;
                continue;
            }

            $source_name = !empty($data['source_playlist']) ? $data['source_playlist'] : $source_name_fallback;
            $single = self::import_urls(array($url), $source_name, 'csv', $data);
            $result['created'] += (int) $single['created'];
            $result['skipped'] += (int) $single['skipped'];
            $result['errors'] = array_merge($result['errors'], $single['errors']);
        }

        fclose($fh);
        return $result;
    }

    public static function import_takeout_file($tmp_path, $source_name = 'Google Takeout')
    {
        $raw = file_get_contents($tmp_path);
        if ($raw === false) {
            return array('created' => 0, 'skipped' => 0, 'errors' => array('Unable to read Takeout file.'));
        }

        preg_match_all('#https?://(?:www\.)?(?:youtube\.com/watch\?v=[A-Za-z0-9_-]{11}|youtu\.be/[A-Za-z0-9_-]{11})[^\s"\']*#', $raw, $matches);
        $urls = array_values(array_unique($matches[0]));

        return self::import_urls($urls, $source_name, 'takeout');
    }

    public static function import_urls($urls, $source_name, $source_type = 'manual', $row_payload = array())
    {
        global $wpdb;

        $result = array('created' => 0, 'skipped' => 0, 'errors' => array());
        $table = OKArcana_DB::table_name();

        foreach ($urls as $url) {
            $youtube_id = self::extract_youtube_id($url);
            if (!$youtube_id) {
                $result['skipped']++;
                $result['errors'][] = 'Invalid YouTube URL: ' . $url;
                continue;
            }

            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE youtube_id = %s LIMIT 1", $youtube_id));
            if ($existing) {
                $result['skipped']++;
                continue;
            }

            $meta = self::fetch_oembed_metadata($url);
            $title = !empty($row_payload['title']) ? $row_payload['title'] : (!empty($meta['title']) ? $meta['title'] : ('Arcana ' . $youtube_id));
            $desc = !empty($row_payload['description']) ? $row_payload['description'] : '';

            $post_id = wp_insert_post(array(
                'post_type' => OKArcana_Post_Types::POST_TYPE,
                'post_status' => 'draft',
                'post_title' => wp_strip_all_tags($title),
                'post_content' => $desc,
            ), true);

            if (is_wp_error($post_id)) {
                $result['errors'][] = 'Post create failed for ' . $url . ': ' . $post_id->get_error_message();
                $result['skipped']++;
                continue;
            }

            $channel_name = !empty($row_payload['channel_name']) ? $row_payload['channel_name'] : (!empty($meta['author_name']) ? $meta['author_name'] : '');
            $channel_url = !empty($row_payload['channel_url']) ? $row_payload['channel_url'] : (!empty($meta['author_url']) ? $meta['author_url'] : '');
            $thumb = !empty($row_payload['thumbnail_url']) ? $row_payload['thumbnail_url'] : (!empty($meta['thumbnail_url']) ? $meta['thumbnail_url'] : '');

            update_post_meta($post_id, '_arcana_youtube_id', $youtube_id);
            update_post_meta($post_id, '_arcana_youtube_url', esc_url_raw($url));
            update_post_meta($post_id, '_arcana_embed_url', 'https://www.youtube.com/embed/' . $youtube_id);
            update_post_meta($post_id, '_arcana_channel_name', sanitize_text_field($channel_name));
            update_post_meta($post_id, '_arcana_channel_url', esc_url_raw($channel_url));
            update_post_meta($post_id, '_arcana_publish_date', !empty($row_payload['publish_date']) ? sanitize_text_field($row_payload['publish_date']) : '');
            update_post_meta($post_id, '_arcana_thumbnail_url', esc_url_raw($thumb));
            update_post_meta($post_id, '_arcana_source_playlist', sanitize_text_field($source_name));
            update_post_meta($post_id, '_arcana_source_playlist_id', !empty($row_payload['source_playlist_id']) ? sanitize_text_field($row_payload['source_playlist_id']) : '');
            update_post_meta($post_id, '_arcana_imported_at', current_time('mysql'));
            update_post_meta($post_id, '_arcana_queue_state', 'imported');

            OKArcana_Post_Types::apply_default_categories($post_id, $source_name);

            $insert = $wpdb->insert(
                $table,
                array(
                    'post_id' => (int) $post_id,
                    'source_type' => sanitize_text_field($source_type),
                    'source_name' => sanitize_text_field($source_name),
                    'source_ref' => '',
                    'youtube_id' => sanitize_text_field($youtube_id),
                    'youtube_url' => esc_url_raw($url),
                    'payload' => wp_json_encode($row_payload),
                    'queue_state' => 'imported',
                    'imported_at' => current_time('mysql'),
                    'scheduled_for' => null,
                    'published_at' => null,
                    'error_message' => null,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );

            if ($insert === false) {
                $result['errors'][] = 'Queue insert failed for ' . $url;
                $result['skipped']++;
                continue;
            }

            $result['created']++;
        }

        return $result;
    }

    private static function fetch_oembed_metadata($url)
    {
        $endpoint = add_query_arg(array(
            'url' => rawurlencode($url),
            'format' => 'json',
        ), 'https://www.youtube.com/oembed');

        $res = wp_remote_get($endpoint, array('timeout' => 10));
        if (is_wp_error($res)) {
            return array();
        }

        $code = wp_remote_retrieve_response_code($res);
        if ($code !== 200) {
            return array();
        }

        $json = json_decode(wp_remote_retrieve_body($res), true);
        return is_array($json) ? $json : array();
    }

    public static function extract_youtube_id($url)
    {
        $url = trim((string) $url);

        if (preg_match('#youtu\.be/([A-Za-z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }

        $parts = wp_parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return '';
        }

        if (strpos($parts['host'], 'youtube.com') !== false && !empty($parts['query'])) {
            parse_str($parts['query'], $query);
            if (!empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $query['v'])) {
                return $query['v'];
            }
        }

        if (strpos($parts['host'], 'youtube.com') !== false && !empty($parts['path'])) {
            if (preg_match('#/shorts/([A-Za-z0-9_-]{11})#', $parts['path'], $m)) {
                return $m[1];
            }
        }

        return '';
    }
}
