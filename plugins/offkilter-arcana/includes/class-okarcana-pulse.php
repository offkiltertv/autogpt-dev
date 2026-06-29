<?php
/**
 * OKArcana_Pulse — OFFKILTER as the destination for Pulse content.
 *
 * Registers the platform-neutral `pulse_item` entity (the OKTV side of the Pulse
 * Clipper integration contract, docs/pulse-integration.md) and the destination
 * surfaces: a feed and a full Pulse destination page.
 *
 * Pulse = fast observations, breaking developments, quick reactions, short-form
 * creator content — published by the standalone Pulse Clipper app and surfaced here.
 *
 * Shortcodes:
 *   [oktv_pulse_feed]         The Pulse feed (cards or list).
 *   [oktv_pulse_destination]  Home → Pulse → Creator destination prototype.
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Pulse
{
    const POST_TYPE   = 'pulse_item';
    const TAX_TAG     = 'pulse_tag';

    const META_SOURCE          = '_ok_pulse_source';
    const META_DURATION        = '_ok_pulse_duration_ms';
    const META_TRANSFORMATIONS = '_ok_pulse_transformations';
    const META_VISIBILITY      = '_ok_pulse_visibility';
    const META_STATUS          = '_ok_pulse_status';
    const META_DISCUSSION      = '_ok_pulse_discussion_id'; // reserved — SidebarChat

    const STATUS_PROCESSING = 'processing';
    const STATUS_READY      = 'ready';
    const STATUS_FAILED     = 'failed';

    public static function init()
    {
        add_action('init', array(__CLASS__, 'register'));

        add_shortcode('oktv_pulse_feed', array(__CLASS__, 'render_pulse_feed_shortcode'));
        add_shortcode('oktv_pulse_destination', array(__CLASS__, 'render_pulse_destination'));
    }

    // -------------------------------------------------------------------------
    // Entity registration (matches the Pulse integration contract).
    // -------------------------------------------------------------------------

    public static function register()
    {
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name'          => __('Pulse', 'offkilter-arcana'),
                'singular_name' => __('Pulse Item', 'offkilter-arcana'),
                'add_new_item'  => __('Add Pulse Item', 'offkilter-arcana'),
                'edit_item'     => __('Edit Pulse Item', 'offkilter-arcana'),
            ),
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-format-status',
            'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'author'),
            'rewrite'      => array('slug' => 'pulse', 'with_front' => false),
            'taxonomies'   => array(self::TAX_TAG),
        ));

        register_taxonomy(self::TAX_TAG, self::POST_TYPE, array(
            'labels' => array(
                'name'          => __('Pulse Tags', 'offkilter-arcana'),
                'singular_name' => __('Pulse Tag', 'offkilter-arcana'),
            ),
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite'      => array('slug' => 'pulse-tag', 'with_front' => false),
        ));

        foreach (array(
            self::META_SOURCE          => 'string',
            self::META_DURATION        => 'integer',
            self::META_TRANSFORMATIONS => 'string',
            self::META_VISIBILITY      => 'string',
            self::META_STATUS          => 'string',
            self::META_DISCUSSION      => 'string',
        ) as $key => $type) {
            register_post_meta(self::POST_TYPE, $key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ));
        }
    }

    // -------------------------------------------------------------------------
    // [oktv_pulse_feed]
    // -------------------------------------------------------------------------

    public static function render_pulse_feed_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'title'         => '',
            'limit'         => 12,
            'tag'           => '',
            'creator_id'    => 0,
            'post_ids'      => '',
            'layout'        => 'cards',
            'status'        => self::STATUS_READY,
            'orderby'       => 'date', // date | trending (engagement proxy)
            'pillar'        => 'pulse',
            'wrapper_class' => '',
        ), $atts, 'oktv_pulse_feed');

        $limit  = max(1, min(40, (int) $atts['limit']));
        $layout = in_array((string) $atts['layout'], array('cards', 'list'), true) ? $atts['layout'] : 'cards';

        $explicit_ids = array_filter(array_map('absint', explode(',', (string) $atts['post_ids'])));

        $query_args = array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        );

        if (!empty($explicit_ids)) {
            $query_args['post__in']       = $explicit_ids;
            $query_args['orderby']        = 'post__in';
            $query_args['posts_per_page'] = count($explicit_ids);
        } else {
            $query_args['posts_per_page'] = $limit;
            if ($atts['orderby'] === 'trending') {
                // Engagement proxy until a dedicated views/score metric exists.
                $query_args['orderby'] = array('comment_count' => 'DESC', 'date' => 'DESC');
            } else {
                $query_args['orderby'] = 'date';
                $query_args['order']   = 'DESC';
            }

            // Public surface: only show ready items.
            $meta_query = array();
            if ($atts['status']) {
                $meta_query[] = array(
                    'key'     => self::META_STATUS,
                    'value'   => sanitize_key($atts['status']),
                    'compare' => '=',
                );
            }
            if (!empty($meta_query)) {
                $query_args['meta_query'] = $meta_query;
            }

            if ((int) $atts['creator_id'] > 0) {
                $query_args['author'] = (int) $atts['creator_id'];
            }
            if ($atts['tag']) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => self::TAX_TAG,
                        'field'    => 'slug',
                        'terms'    => sanitize_title($atts['tag']),
                    ),
                );
            }
        }

        $q = new WP_Query($query_args);

        $outer_class = 'ok-pulse-feed';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <section class="<?php echo esc_attr($outer_class); ?>" role="region" aria-label="<?php echo esc_attr($atts['title'] ? $atts['title'] : 'Pulse'); ?>">
            <?php if ($atts['title']) : ?>
                <header class="ok-curated-section__header">
                    <h3 class="ok-curated-section__title"><?php echo esc_html($atts['title']); ?></h3>
                </header>
            <?php endif; ?>

            <?php if (!$q->have_posts()) : ?>
                <div class="ok-empty-state"><p><?php esc_html_e('No Pulse yet.', 'offkilter-arcana'); ?></p></div>
            <?php elseif ($layout === 'cards') : ?>
                <div class="oktv-signals-cards">
                    <?php while ($q->have_posts()) : $q->the_post(); echo self::pulse_card(get_the_ID()); endwhile; ?>
                </div>
            <?php else : ?>
                <ul class="oktv-signals-list">
                    <?php while ($q->have_posts()) : $q->the_post(); ?>
                        <li class="oktv-signals-item">
                            <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            <span class="oktv-signals-meta"><?php echo esc_html(get_the_author_meta('display_name')); ?></span>
                            <span class="oktv-signals-meta"><?php echo esc_html(get_the_date('M j')); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </section>
        <?php
        wp_reset_postdata();
        return (string) ob_get_clean();
    }

    /**
     * Render a single Pulse card. Reuses the signals-card CSS. Carries a reserved
     * discuss slot hook + a processing badge when the owner views their own item.
     *
     * @param int $post_id
     * @return string
     */
    public static function pulse_card($post_id)
    {
        $post_id   = (int) $post_id;
        $thumb     = get_the_post_thumbnail_url($post_id, 'medium');
        $creator   = get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));
        $status    = (string) get_post_meta($post_id, self::META_STATUS, true);
        $is_owner  = get_current_user_id() && (int) get_post_field('post_author', $post_id) === get_current_user_id();

        ob_start();
        ?>
        <article class="oktv-signals-card ok-pulse-card">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" tabindex="-1" aria-hidden="true">
                <div class="oktv-signals-card__thumb<?php echo $thumb ? '' : ' oktv-signals-card__thumb--empty'; ?>">
                    <?php if ($thumb) : ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy" width="320" height="180">
                    <?php endif; ?>
                    <span class="ok-pulse-badge">Pulse</span>
                    <?php if ($is_owner && $status === self::STATUS_PROCESSING) : ?>
                        <span class="ok-pulse-status ok-pulse-status--processing"><?php esc_html_e('Processing…', 'offkilter-arcana'); ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <div class="oktv-signals-card__body">
                <p class="oktv-signals-card__title">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
                </p>
                <div class="oktv-signals-card__footer">
                    <?php if ($creator !== '') : ?>
                        <span class="oktv-signals-card__creator"><?php echo esc_html($creator); ?></span>
                    <?php endif; ?>
                    <span class="oktv-signals-card__date"><?php echo esc_html(get_the_date('M j', $post_id)); ?></span>
                </div>
                <?php // Reserved SidebarChat discuss slot (coming soon). ?>
                <?php if (class_exists('OKArcana_Editorial')) {
                    echo OKArcana_Editorial::render_discuss_cta(array('context' => 'pulse', 'coming_soon' => '1'));
                } ?>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_pulse_destination] — Home → Pulse → Creator prototype.
    // -------------------------------------------------------------------------

    public static function render_pulse_destination($atts)
    {
        $atts = shortcode_atts(array(
            'headline'        => 'OFFKILTER Pulse',
            'sub'             => 'Fast observations. Breaking developments. Quick reactions.',
            'show_explainer'  => 1,
            'featured_ids'    => '',
            'featured_label'  => 'Featured Pulse',
            'trending_label'  => 'Trending Pulse',
            'latest_label'    => 'Latest Pulse',
            'trending_limit'  => 6,
            'latest_limit'    => 12,
            'show_creators'   => 1,
            'show_discuss'    => 1, // reserved slot only
            'wrapper_class'   => '',
        ), $atts, 'oktv_pulse_destination');

        $outer_class = 'ok-pulse-destination';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>">
            <header class="ok-pulse-destination__hero">
                <span class="ok-pulse-destination__icon" aria-hidden="true"><i class="fas fa-wave-square"></i></span>
                <h1 class="ok-pulse-destination__headline"><?php echo esc_html($atts['headline']); ?></h1>
                <p class="ok-pulse-destination__sub"><?php echo esc_html($atts['sub']); ?></p>
            </header>

            <?php if (!empty($atts['show_explainer'])) : ?>
                <div class="ok-pulse-explainer">
                    <p class="ok-pulse-explainer__title"><?php esc_html_e('What is Pulse?', 'offkilter-arcana'); ?></p>
                    <p class="ok-pulse-explainer__body">
                        <?php esc_html_e('Pulse is OFFKILTER’s short-form discovery layer — fast observations, breaking developments, quick creator updates, and transformative commentary. Not shorts for their own sake: moments worth sharing, with something added.', 'offkilter-arcana'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php // Featured Pulse — editorial, hand-picked. ?>
            <?php if ($atts['featured_ids']) : ?>
                <?php echo self::render_pulse_feed_shortcode(array(
                    'title'    => $atts['featured_label'],
                    'post_ids' => $atts['featured_ids'],
                    'layout'   => 'cards',
                )); ?>
            <?php endif; ?>

            <?php // Trending Pulse — engagement proxy. ?>
            <?php echo self::render_pulse_feed_shortcode(array(
                'title'   => $atts['trending_label'],
                'orderby' => 'trending',
                'limit'   => (int) $atts['trending_limit'],
                'layout'  => 'cards',
            )); ?>

            <?php // Latest Pulse — chronological. ?>
            <?php echo self::render_pulse_feed_shortcode(array(
                'title'  => $atts['latest_label'],
                'limit'  => (int) $atts['latest_limit'],
                'layout' => 'cards',
            )); ?>

            <?php if (!empty($atts['show_creators'])) : ?>
                <div class="ok-pulse-destination__creators">
                    <header class="ok-curated-section__header">
                        <h3 class="ok-curated-section__title"><?php esc_html_e('Creators on Pulse', 'offkilter-arcana'); ?></h3>
                    </header>
                    <div class="ok-discover-creators-row">
                        <?php
                        $creators = get_users(array(
                            'has_published_posts' => array(self::POST_TYPE),
                            'number'              => 3,
                            'orderby'             => 'post_count',
                            'order'               => 'DESC',
                        ));
                        if (!empty($creators) && class_exists('OKArcana_Signals')) {
                            foreach ($creators as $u) {
                                echo OKArcana_Signals::render_creator_spotlight_shortcode(array(
                                    'user_id'     => (int) $u->ID,
                                    'show_stats'  => 1,
                                    'show_latest' => 0,
                                ));
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php // Reserved discussion slot — Discuss / Community / Creator room (SidebarChat). ?>
            <?php if (!empty($atts['show_discuss']) && class_exists('OKArcana_Editorial')) : ?>
                <div class="ok-pulse-destination__discuss">
                    <?php echo OKArcana_Editorial::render_discuss_cta(array(
                        'context'     => 'pulse',
                        'label'       => __('Discuss on OFFKILTER', 'offkilter-arcana'),
                        'coming_soon' => '1',
                    )); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
