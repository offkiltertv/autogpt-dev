<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Settings
{
    const OPTION_KEY = 'okarcana_settings';

    public static function ensure_defaults()
    {
        if (get_option(self::OPTION_KEY, null) === null) {
            add_option(self::OPTION_KEY, self::defaults(), '', false);
        }
    }

    public static function defaults()
    {
        return array(
            'enable_disclaimer' => 1,
            'enable_wpforo' => 1,
            'wpforo_forum_id' => 1,
            'related_count' => 5,
            'default_tags' => '',
            'enable_signals_classification' => 1,
            'signals_threshold_seconds' => 90,
            'signals_backfill_batch_size' => 75,
            'signals_duration_meta_keys' => 'beeteam368_video_duration',
            // Watch experience (v2.9): append Watch Next + discuss slot to
            // public single-video pages.
            'enable_watch_next_append' => 1,
            // Launch curation layer (v2.8). Comma-separated ID lists.
            'featured_creator_ids' => '',          // launch-creator user IDs, priority lineup for discovery/pulse
            'deprecated_creator_user_ids' => '',   // legacy author user IDs excluded from discovery/pulse fallback
            'deprecated_creator_term_ids' => '',   // legacy creator term IDs (e.g. 2055) the scheduler will hold
            'playlist_mappings' => array(
                array(
                    'match' => 'Poem',
                    'categories' => array('Poems'),
                    'tags' => array('Literature', 'Journey'),
                ),
                array(
                    'match' => 'Prem and Outcome 2026',
                    'categories' => array('Premonitions', 'Outcomes'),
                    'tags' => array('Destiny', 'Choice'),
                ),
            ),
        );
    }

    public static function all()
    {
        $saved = get_option(self::OPTION_KEY, array());
        if (!is_array($saved)) {
            $saved = array();
        }

        return array_replace_recursive(self::defaults(), $saved);
    }

    public static function get($key, $fallback = null)
    {
        $all = self::all();
        return array_key_exists($key, $all) ? $all[$key] : $fallback;
    }

    public static function update($settings)
    {
        $clean = self::sanitize($settings);
        update_option(self::OPTION_KEY, $clean, false);
        return $clean;
    }

    public static function sanitize($settings)
    {
        $defaults = self::defaults();

        $out = array();
        $out['enable_disclaimer'] = !empty($settings['enable_disclaimer']) ? 1 : 0;
        $out['enable_wpforo'] = !empty($settings['enable_wpforo']) ? 1 : 0;
        $out['wpforo_forum_id'] = isset($settings['wpforo_forum_id']) ? max(1, (int) $settings['wpforo_forum_id']) : (int) $defaults['wpforo_forum_id'];
        $out['related_count'] = isset($settings['related_count']) ? max(1, min(20, (int) $settings['related_count'])) : (int) $defaults['related_count'];
        $out['default_tags'] = isset($settings['default_tags']) ? sanitize_textarea_field((string) $settings['default_tags']) : '';
        $out['enable_signals_classification'] = !empty($settings['enable_signals_classification']) ? 1 : 0;
        $out['signals_threshold_seconds'] = isset($settings['signals_threshold_seconds']) ? max(10, min(3600, (int) $settings['signals_threshold_seconds'])) : (int) $defaults['signals_threshold_seconds'];
        $out['signals_backfill_batch_size'] = isset($settings['signals_backfill_batch_size']) ? max(1, min(500, (int) $settings['signals_backfill_batch_size'])) : (int) $defaults['signals_backfill_batch_size'];
        $out['signals_duration_meta_keys'] = isset($settings['signals_duration_meta_keys']) ? sanitize_textarea_field((string) $settings['signals_duration_meta_keys']) : (string) $defaults['signals_duration_meta_keys'];

        $out['enable_watch_next_append'] = !empty($settings['enable_watch_next_append']) ? 1 : 0;
        $out['featured_creator_ids'] = self::normalize_id_csv(isset($settings['featured_creator_ids']) ? $settings['featured_creator_ids'] : '');
        $out['deprecated_creator_user_ids'] = self::normalize_id_csv(isset($settings['deprecated_creator_user_ids']) ? $settings['deprecated_creator_user_ids'] : '');
        $out['deprecated_creator_term_ids'] = self::normalize_id_csv(isset($settings['deprecated_creator_term_ids']) ? $settings['deprecated_creator_term_ids'] : '');

        $out['playlist_mappings'] = array();
        if (!empty($settings['playlist_mappings']) && is_array($settings['playlist_mappings'])) {
            foreach ($settings['playlist_mappings'] as $row) {
                $match = isset($row['match']) ? sanitize_text_field((string) $row['match']) : '';
                if ($match === '') {
                    continue;
                }

                $categories = isset($row['categories']) ? self::split_terms($row['categories']) : array();
                $tags = isset($row['tags']) ? self::split_terms($row['tags']) : array();

                $out['playlist_mappings'][] = array(
                    'match' => $match,
                    'categories' => $categories,
                    'tags' => $tags,
                );
            }
        }

        if (empty($out['playlist_mappings'])) {
            $out['playlist_mappings'] = $defaults['playlist_mappings'];
        }

        return $out;
    }

    public static function split_terms($raw)
    {
        if (is_array($raw)) {
            $parts = $raw;
        } else {
            $parts = preg_split('/[\r\n,]+/', (string) $raw);
        }

        $terms = array();
        foreach ((array) $parts as $item) {
            $term = trim((string) $item);
            if ($term !== '') {
                $terms[] = sanitize_text_field($term);
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * Normalize a comma/newline/space separated list of IDs into a clean,
     * de-duplicated, comma-separated string of positive integers for storage.
     *
     * @param mixed $raw
     * @return string
     */
    public static function normalize_id_csv($raw)
    {
        return implode(',', self::ids_from_csv($raw));
    }

    /**
     * Parse a stored ID-CSV value into an array of positive ints.
     *
     * @param mixed $raw
     * @return int[]
     */
    public static function ids_from_csv($raw)
    {
        if (is_array($raw)) {
            $parts = $raw;
        } else {
            $parts = preg_split('/[\s,]+/', (string) $raw);
        }

        $ids = array();
        foreach ((array) $parts as $part) {
            $id = (int) trim((string) $part);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Launch-creator user IDs (priority lineup for discovery/pulse).
     *
     * @return int[]
     */
    public static function featured_creator_ids()
    {
        return self::ids_from_csv(self::get('featured_creator_ids', ''));
    }

    /**
     * Legacy author user IDs to exclude from discovery/pulse fallbacks.
     *
     * @return int[]
     */
    public static function deprecated_creator_user_ids()
    {
        return self::ids_from_csv(self::get('deprecated_creator_user_ids', ''));
    }

    /**
     * Legacy creator term IDs (e.g. 2055) the scheduler should hold.
     *
     * @return int[]
     */
    public static function deprecated_creator_term_ids()
    {
        return self::ids_from_csv(self::get('deprecated_creator_term_ids', ''));
    }

    public static function render_mapping_lines($mappings)
    {
        $lines = array();
        foreach ((array) $mappings as $map) {
            $match = isset($map['match']) ? (string) $map['match'] : '';
            if ($match === '') {
                continue;
            }

            $categories = isset($map['categories']) ? implode(',', (array) $map['categories']) : '';
            $tags = isset($map['tags']) ? implode(',', (array) $map['tags']) : '';
            $lines[] = $match . '|' . $categories . '|' . $tags;
        }

        return implode("\n", $lines);
    }

    public static function parse_mapping_lines($raw)
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $raw);
        $mappings = array();

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $parts = explode('|', $line);
            $match = isset($parts[0]) ? trim($parts[0]) : '';
            if ($match === '') {
                continue;
            }

            $categories = isset($parts[1]) ? self::split_terms($parts[1]) : array();
            $tags = isset($parts[2]) ? self::split_terms($parts[2]) : array();

            $mappings[] = array(
                'match' => $match,
                'categories' => $categories,
                'tags' => $tags,
            );
        }

        return $mappings;
    }

    public static function find_mapping($source_name)
    {
        $source_name = (string) $source_name;
        if ($source_name === '') {
            return null;
        }

        $source_lc = strtolower($source_name);
        $all = self::all();

        foreach ((array) $all['playlist_mappings'] as $map) {
            $match = isset($map['match']) ? strtolower((string) $map['match']) : '';
            if ($match !== '' && strpos($source_lc, $match) !== false) {
                return $map;
            }
        }

        return null;
    }
}
