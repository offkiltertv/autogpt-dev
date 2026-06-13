<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Post_Types
{
    const POST_TYPE = 'arcana_entry';
    const TAX_CATEGORY = 'arcana_category';
    const TAX_TAG = 'arcana_tag';

    public static function init()
    {
        add_action('init', array(__CLASS__, 'register'));
        add_action('init', array(__CLASS__, 'ensure_default_terms'), 20);
    }

    public static function register()
    {
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Arcana Entries', 'offkilter-arcana'),
                'singular_name' => __('Arcana Entry', 'offkilter-arcana'),
                'add_new_item' => __('Add Arcana Entry', 'offkilter-arcana'),
                'edit_item' => __('Edit Arcana Entry', 'offkilter-arcana'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-site-alt3',
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions'),
            'rewrite' => array('slug' => 'arcana', 'with_front' => false),
            'taxonomies' => array(self::TAX_CATEGORY, self::TAX_TAG),
        ));

        register_taxonomy(self::TAX_CATEGORY, self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Arcana Categories', 'offkilter-arcana'),
                'singular_name' => __('Arcana Category', 'offkilter-arcana'),
            ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'arcana-category', 'with_front' => false),
        ));

        register_taxonomy(self::TAX_TAG, self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Arcana Tags', 'offkilter-arcana'),
                'singular_name' => __('Arcana Tag', 'offkilter-arcana'),
            ),
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'arcana-tag', 'with_front' => false),
        ));
    }

    public static function ensure_default_terms()
    {
        if (!taxonomy_exists(self::TAX_CATEGORY) || !taxonomy_exists(self::TAX_TAG)) {
            return;
        }

        $root = self::ensure_term('The Arcana', self::TAX_CATEGORY, 0);

        $categories = array(
            'Poems',
            'Premonitions',
            'Outcomes',
            'Tarot',
            'Archetypes',
            'Symbolism',
            'Dreams',
            'Synchronicities',
            'Literature',
        );

        foreach ($categories as $name) {
            self::ensure_term($name, self::TAX_CATEGORY, $root);
        }

        $tags = array(
            'Destiny',
            'Choice',
            'Crossroads',
            'Transformation',
            'Shadow',
            'Fool',
            'Tower',
            'Journey',
            'Love',
            'Loss',
        );

        foreach ($tags as $name) {
            self::ensure_term($name, self::TAX_TAG, 0);
        }
    }

    public static function apply_default_categories($post_id, $source_name)
    {
        $root = get_term_by('name', 'The Arcana', self::TAX_CATEGORY);
        $assign = array();

        if ($root && !is_wp_error($root)) {
            $assign[] = (int) $root->term_id;
        }

        $source_lc = strtolower((string) $source_name);

        if (strpos($source_lc, 'prem and outcome 2026') !== false) {
            $assign[] = self::get_term_id_by_name('Premonitions', self::TAX_CATEGORY);
            $assign[] = self::get_term_id_by_name('Outcomes', self::TAX_CATEGORY);
        }

        if (strpos($source_lc, 'poem') !== false) {
            $assign[] = self::get_term_id_by_name('Poems', self::TAX_CATEGORY);
        }

        $assign = array_values(array_filter(array_unique(array_map('intval', $assign))));

        if (!empty($assign)) {
            wp_set_object_terms($post_id, $assign, self::TAX_CATEGORY, true);
        }
    }

    private static function ensure_term($name, $taxonomy, $parent)
    {
        $existing = get_term_by('name', $name, $taxonomy);
        if ($existing && !is_wp_error($existing)) {
            return (int) $existing->term_id;
        }

        $result = wp_insert_term($name, $taxonomy, array('parent' => (int) $parent));
        if (is_wp_error($result)) {
            return 0;
        }

        return isset($result['term_id']) ? (int) $result['term_id'] : 0;
    }

    private static function get_term_id_by_name($name, $taxonomy)
    {
        $term = get_term_by('name', $name, $taxonomy);
        if (!$term || is_wp_error($term)) {
            return 0;
        }

        return (int) $term->term_id;
    }
}
