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
        add_action('init', array(__CLASS__, 'maybe_flush_rewrites'), 99);
        add_filter('template_include', array(__CLASS__, 'route_archive'));
        add_action('rest_api_init', array(__CLASS__, 'register_rest'));

        add_shortcode('oktv_pulse_feed', array(__CLASS__, 'render_pulse_feed_shortcode'));
        add_shortcode('oktv_pulse_destination', array(__CLASS__, 'render_pulse_destination'));
    }

    /**
     * Flush rewrite rules once after the CPT is first registered (or after a version
     * change) so the `/pulse/` archive + single routes resolve without a manual
     * Settings → Permalinks save.
     */
    public static function maybe_flush_rewrites()
    {
        if (get_option('okarcana_pulse_rewrites') !== OKARCANA_VERSION) {
            flush_rewrite_rules(false);
            update_option('okarcana_pulse_rewrites', OKARCANA_VERSION);
        }
    }

    /**
     * Route the Pulse archive to the plugin template so `/pulse/` renders the
     * branded destination inside the theme chrome (production-ready, theme-agnostic).
     *
     * @param string $template
     * @return string
     */
    public static function route_archive($template)
    {
        if (is_post_type_archive(self::POST_TYPE) || is_tax(self::TAX_TAG)) {
            $custom = OKARCANA_PLUGIN_DIR . 'templates/archive-pulse_item.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }
        return $template;
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
    // REST — the OKTV side of the Pulse Clipper publishing contract.
    // (docs/pulse-integration.md). create + finalize + get are functional;
    // resumable media upload (tus) is a documented placeholder pending storage infra.
    // -------------------------------------------------------------------------

    public static function register_rest()
    {
        $ns = 'offkilter/v1';

        register_rest_route($ns, '/pulse/items', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'rest_create'),
            'permission_callback' => array(__CLASS__, 'rest_can_author'),
        ));

        register_rest_route($ns, '/pulse/items/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array(__CLASS__, 'rest_get'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($ns, '/pulse/items/(?P<id>\d+)/finalize', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'rest_finalize'),
            'permission_callback' => array(__CLASS__, 'rest_can_author'),
        ));

        // Placeholder: resumable media upload (tus). Pending storage/Bunny infra.
        register_rest_route($ns, '/pulse/items/(?P<id>\d+)/upload', array(
            'methods'             => array('POST', 'PATCH', 'HEAD'),
            'callback'            => array(__CLASS__, 'rest_upload_placeholder'),
            'permission_callback' => array(__CLASS__, 'rest_can_author'),
        ));
    }

    public static function rest_can_author()
    {
        // Placeholder auth: capability check. Real auth = Google ID token exchange
        // (shared identity) — see docs/pulse-integration.md §5.
        return current_user_can('edit_posts');
    }

    /** Create a pulse_item in the `processing` state and return its id + upload target. */
    public static function rest_create($request)
    {
        $p = $request->get_json_params();
        if (!is_array($p)) {
            $p = $request->get_params();
        }

        $post_id = wp_insert_post(array(
            'post_type'    => self::POST_TYPE,
            'post_status'  => 'publish',
            'post_title'   => sanitize_text_field($p['title'] ?? __('Untitled Pulse', 'offkilter-arcana')),
            'post_content' => wp_kses_post($p['description'] ?? ''),
        ), true);

        if (is_wp_error($post_id)) {
            return new WP_Error('ok_pulse_create_failed', $post_id->get_error_message(), array('status' => 500));
        }

        update_post_meta($post_id, self::META_STATUS, self::STATUS_PROCESSING);
        if (isset($p['source']))          { update_post_meta($post_id, self::META_SOURCE, wp_json_encode($p['source'])); }
        if (isset($p['durationMs']))      { update_post_meta($post_id, self::META_DURATION, (int) $p['durationMs']); }
        if (isset($p['transformations'])) { update_post_meta($post_id, self::META_TRANSFORMATIONS, sanitize_text_field(is_array($p['transformations']) ? implode(',', $p['transformations']) : $p['transformations'])); }
        if (isset($p['visibility']))      { update_post_meta($post_id, self::META_VISIBILITY, sanitize_key($p['visibility'])); }
        if (!empty($p['tags']) && is_array($p['tags'])) {
            wp_set_object_terms($post_id, array_map('sanitize_text_field', $p['tags']), self::TAX_TAG);
        }

        return new WP_REST_Response(array(
            'id'             => $post_id,
            'status'         => self::STATUS_PROCESSING,
            // Placeholder: a real tus upload URL is issued once storage infra exists.
            'uploadUrl'      => null,
            'uploadProtocol' => 'pending',
            'note'           => 'Media upload (tus) not yet enabled; metadata accepted.',
        ), 201);
    }

    /** Finalize: mark ready and accept media/thumbnail URLs once upload exists. */
    public static function rest_finalize($request)
    {
        $id = (int) $request['id'];
        if (get_post_type($id) !== self::POST_TYPE) {
            return new WP_Error('ok_pulse_not_found', 'Not found', array('status' => 404));
        }
        $p = $request->get_json_params();
        if (!is_array($p)) {
            $p = $request->get_params();
        }

        update_post_meta($id, self::META_STATUS, self::STATUS_READY);
        if (isset($p['discussionId'])) {
            update_post_meta($id, self::META_DISCUSSION, sanitize_text_field($p['discussionId']));
        }

        return new WP_REST_Response(self::map_item($id), 200);
    }

    public static function rest_get($request)
    {
        $id = (int) $request['id'];
        if (get_post_type($id) !== self::POST_TYPE || get_post_status($id) !== 'publish') {
            return new WP_Error('ok_pulse_not_found', 'Not found', array('status' => 404));
        }
        $visibility = (string) get_post_meta($id, self::META_VISIBILITY, true);
        $ready      = get_post_meta($id, self::META_STATUS, true) === self::STATUS_READY;
        if ((!$ready || $visibility === 'private') && !current_user_can('edit_post', $id)) {
            return new WP_Error('ok_pulse_forbidden', 'Not available', array('status' => 403));
        }
        return new WP_REST_Response(self::map_item($id), 200);
    }

    public static function rest_upload_placeholder($request)
    {
        return new WP_REST_Response(array(
            'error' => 'not_implemented',
            'note'  => 'Resumable media upload (tus) is pending storage infra. See docs/pulse-integration.md.',
        ), 501);
    }

    /** Map a pulse_item to the platform-neutral Pulse Item shape. */
    private static function map_item($id)
    {
        $author = (int) get_post_field('post_author', $id);
        return array(
            'id'              => $id,
            'creator'         => array(
                'id'          => $author,
                'handle'      => get_the_author_meta('user_login', $author),
                'displayName' => get_the_author_meta('display_name', $author),
            ),
            'title'           => get_the_title($id),
            'description'     => get_post_field('post_content', $id),
            'durationMs'      => (int) get_post_meta($id, self::META_DURATION, true),
            'source'          => json_decode((string) get_post_meta($id, self::META_SOURCE, true), true),
            'tags'            => wp_get_object_terms($id, self::TAX_TAG, array('fields' => 'names')),
            'transformations' => array_filter(explode(',', (string) get_post_meta($id, self::META_TRANSFORMATIONS, true))),
            'visibility'      => (string) get_post_meta($id, self::META_VISIBILITY, true),
            'discussionId'    => (string) get_post_meta($id, self::META_DISCUSSION, true) ?: null,
            'status'          => (string) get_post_meta($id, self::META_STATUS, true),
            'mediaUrl'        => null,
            'thumbnailUrl'    => get_the_post_thumbnail_url($id, 'large') ?: null,
        );
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
                <div class="ok-empty-state">
                    <span class="ok-empty-state__icon" aria-hidden="true"><i class="fas fa-wave-square"></i></span>
                    <span class="ok-empty-state__label"><?php esc_html_e('No Pulse yet.', 'offkilter-arcana'); ?></span>
                </div>
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
        $post_id    = (int) $post_id;
        $thumb      = get_the_post_thumbnail_url($post_id, 'medium');
        $creator_id = (int) get_post_field('post_author', $post_id);
        $creator    = get_the_author_meta('display_name', $creator_id);
        $status     = (string) get_post_meta($post_id, self::META_STATUS, true);
        $is_owner   = get_current_user_id() && $creator_id === get_current_user_id();
        // "NEW" while the Pulse is fresh (< 48h) — reinforces the "what's happening now" promise.
        $is_new     = (time() - (int) get_post_time('U', true, $post_id)) < (48 * HOUR_IN_SECONDS);

        // v3.0 identity chips: duration (from clipper meta) + first pulse tag.
        $duration_ms = (int) get_post_meta($post_id, self::META_DURATION, true);
        $duration    = '';
        if ($duration_ms > 0) {
            $secs     = (int) round($duration_ms / 1000);
            $duration = sprintf('%d:%02d', (int) floor($secs / 60), $secs % 60);
        }
        $tags      = wp_get_post_terms($post_id, self::TAX_TAG, array('fields' => 'names'));
        $first_tag = (!is_wp_error($tags) && !empty($tags)) ? (string) $tags[0] : '';

        ob_start();
        ?>
        <article class="oktv-signals-card ok-pulse-card">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" tabindex="-1" aria-hidden="true">
                <div class="oktv-signals-card__thumb<?php echo $thumb ? '' : ' oktv-signals-card__thumb--empty'; ?>">
                    <?php if ($thumb) : ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy" width="320" height="180">
                    <?php endif; ?>
                    <span class="ok-pulse-badge">Pulse</span>
                    <?php if ($duration !== '') : ?>
                        <span class="ok-pulse-duration"><?php echo esc_html($duration); ?></span>
                    <?php endif; ?>
                    <?php if ($is_new) : ?>
                        <span class="ok-pulse-new"><?php esc_html_e('New', 'offkilter-arcana'); ?></span>
                    <?php endif; ?>
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
                        <span class="oktv-signals-card__creator"><a href="<?php echo esc_url(get_author_posts_url($creator_id)); ?>"><?php echo esc_html($creator); ?></a></span>
                    <?php endif; ?>
                    <?php if ($first_tag !== '') : ?>
                        <span class="ok-pulse-tag">#<?php echo esc_html($first_tag); ?></span>
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
                <h1 class="ok-pulse-destination__headline">
                    <span class="ok-pulse-destination__dot" aria-hidden="true"></span>
                    <?php echo esc_html($atts['headline']); ?>
                </h1>
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
                        // Prefer the curated launch lineup; otherwise top creators
                        // by post count, excluding deprecated ones.
                        $featured_pulse = class_exists('OKArcana_Settings') ? OKArcana_Settings::featured_creator_ids() : array();
                        if (!empty($featured_pulse)) {
                            $creator_ids = array_slice($featured_pulse, 0, 3);
                        } else {
                            $pulse_query = array(
                                'has_published_posts' => array(self::POST_TYPE),
                                'number'              => 3,
                                'orderby'             => 'post_count',
                                'order'               => 'DESC',
                            );
                            $excluded_pulse = class_exists('OKArcana_Settings') ? OKArcana_Settings::deprecated_creator_user_ids() : array();
                            if (!empty($excluded_pulse)) {
                                $pulse_query['exclude'] = $excluded_pulse;
                            }
                            $creator_ids = wp_list_pluck(get_users($pulse_query), 'ID');
                        }
                        if (!empty($creator_ids) && class_exists('OKArcana_Signals')) {
                            foreach ($creator_ids as $cid) {
                                echo OKArcana_Signals::render_creator_spotlight_shortcode(array(
                                    'user_id'     => (int) $cid,
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
