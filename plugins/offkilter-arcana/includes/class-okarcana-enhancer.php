<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Enhancer
{
    public static function init()
    {
        add_action('save_post_' . OKArcana_Post_Types::POST_TYPE, array(__CLASS__, 'enrich_entry'), 20, 3);
    }

    public static function enrich_entry($post_id, $post, $update)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        static $running = array();
        if (isset($running[$post_id])) {
            return;
        }
        $running[$post_id] = true;

        $source_name = self::detect_source_playlist($post_id);
        if ($source_name !== '') {
            update_post_meta($post_id, '_arcana_source_playlist', $source_name);
        }

        if (!get_post_meta($post_id, '_arcana_imported_at', true)) {
            update_post_meta($post_id, '_arcana_imported_at', current_time('mysql'));
        }
        update_post_meta($post_id, '_arcana_enriched_at', current_time('mysql'));

        self::apply_playlist_mapping($post_id, $source_name);
        self::update_related_references($post_id);

        unset($running[$post_id]);
    }

    private static function detect_source_playlist($post_id)
    {
        $existing = trim((string) get_post_meta($post_id, '_arcana_source_playlist', true));
        if ($existing !== '') {
            return $existing;
        }

        $camp_id = (int) get_post_meta($post_id, 'wp_automatic_camp', true);
        if ($camp_id <= 0) {
            return '';
        }

        global $wpdb;
        $table = $wpdb->prefix . 'automatic_camps';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            return '';
        }

        $source = $wpdb->get_var($wpdb->prepare("SELECT camp_name FROM {$table} WHERE camp_id = %d LIMIT 1", $camp_id));
        return is_string($source) ? trim($source) : '';
    }

    private static function apply_playlist_mapping($post_id, $source_name)
    {
        $root = get_term_by('name', 'The Arcana', OKArcana_Post_Types::TAX_CATEGORY);
        $category_names = array();
        if ($root && !is_wp_error($root)) {
            $category_names[] = 'The Arcana';
        }

        $tag_names = array();
        $mapping = OKArcana_Settings::find_mapping($source_name);
        if (is_array($mapping)) {
            $category_names = array_merge($category_names, isset($mapping['categories']) ? (array) $mapping['categories'] : array());
            $tag_names = array_merge($tag_names, isset($mapping['tags']) ? (array) $mapping['tags'] : array());
        }

        $default_tags = OKArcana_Settings::split_terms((string) OKArcana_Settings::get('default_tags', ''));
        if (!empty($default_tags)) {
            $tag_names = array_merge($tag_names, $default_tags);
        }

        $category_names = array_values(array_unique(array_filter(array_map('sanitize_text_field', $category_names))));
        $tag_names = array_values(array_unique(array_filter(array_map('sanitize_text_field', $tag_names))));

        if (!empty($category_names)) {
            wp_set_object_terms($post_id, $category_names, OKArcana_Post_Types::TAX_CATEGORY, true);
        }

        if (!empty($tag_names)) {
            wp_set_object_terms($post_id, $tag_names, OKArcana_Post_Types::TAX_TAG, true);
        }
    }

    public static function update_related_references($post_id)
    {
        $related_count = max(1, min(20, (int) OKArcana_Settings::get('related_count', 5)));

        $tag_terms = wp_get_post_terms($post_id, OKArcana_Post_Types::TAX_TAG, array('fields' => 'ids'));
        $cat_terms = wp_get_post_terms($post_id, OKArcana_Post_Types::TAX_CATEGORY, array('fields' => 'ids'));

        $tax_query = array('relation' => 'OR');
        if (!empty($tag_terms)) {
            $tax_query[] = array(
                'taxonomy' => OKArcana_Post_Types::TAX_TAG,
                'field' => 'term_id',
                'terms' => array_map('intval', $tag_terms),
            );
        }
        if (!empty($cat_terms)) {
            $tax_query[] = array(
                'taxonomy' => OKArcana_Post_Types::TAX_CATEGORY,
                'field' => 'term_id',
                'terms' => array_map('intval', $cat_terms),
            );
        }

        if (count($tax_query) <= 1) {
            update_post_meta($post_id, '_arcana_related_post_ids', '');
            return;
        }

        $ids = get_posts(array(
            'post_type' => OKArcana_Post_Types::POST_TYPE,
            'post_status' => array('publish', 'future', 'draft', 'pending'),
            'post__not_in' => array((int) $post_id),
            'tax_query' => $tax_query,
            'fields' => 'ids',
            'orderby' => 'modified',
            'order' => 'DESC',
            'numberposts' => $related_count,
            'suppress_filters' => false,
        ));

        $ids = array_values(array_filter(array_map('intval', (array) $ids)));
        update_post_meta($post_id, '_arcana_related_post_ids', implode(',', $ids));
        update_post_meta($post_id, '_arcana_related_updated_at', current_time('mysql'));
    }
}
