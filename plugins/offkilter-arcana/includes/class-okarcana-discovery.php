<?php
if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Discovery
{
    public static function init()
    {
        add_shortcode('oktv_platform_intro',    array(__CLASS__, 'render_platform_intro'));
        add_shortcode('oktv_curated_section',   array(__CLASS__, 'render_curated_section'));
        add_shortcode('oktv_creator_page',      array(__CLASS__, 'render_creator_page'));
        add_shortcode('oktv_discover_page',     array(__CLASS__, 'render_discover_page'));
        add_shortcode('oktv_recommended_next',  array(__CLASS__, 'render_recommended_next'));
        add_shortcode('oktv_watch_nav',         array(__CLASS__, 'render_watch_nav'));
        add_shortcode('oktv_featured_creator',  array(__CLASS__, 'render_featured_creator'));
        add_shortcode('oktv_platform_story',    array(__CLASS__, 'render_platform_story'));
    }

    // -------------------------------------------------------------------------
    // [oktv_platform_story]
    // Homepage narrative — a visitor understands the platform in seconds. One
    // lede + one line per pillar explaining WHY it exists. Editorial, not generic
    // mission copy.
    //
    // Attributes:
    //   lede          string  default below
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_platform_story($atts)
    {
        $atts = shortcode_atts(array(
            'lede'          => 'OFFKILTER surfaces the content worth returning to.',
            'wrapper_class' => '',
        ), $atts, 'oktv_platform_story');

        $rows = array(
            'pulse'     => array('term' => 'Pulse',     'desc' => 'Fast observations, breaking developments, and transformative commentary — short-form discovery.'),
            'signals'   => array('term' => 'Signals',   'desc' => 'The standout clips worth your attention. Under 90 seconds.'),
            'arcana'    => array('term' => 'Arcana',    'desc' => 'The intuitive layer — readings, premonitions, and outcomes.'),
            'creators'  => array('term' => 'Creators',  'desc' => 'Independent voices who belong here, with real audiences.'),
            'community' => array('term' => 'Community',  'desc' => 'Where the conversation lives — discuss what you discover.'),
        );

        $outer_class = 'ok-platform-story';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <section class="<?php echo esc_attr($outer_class); ?>" role="region" aria-label="<?php esc_attr_e('What OFFKILTER is', 'offkilter-arcana'); ?>">
            <p class="ok-platform-story__lede"><?php echo esc_html($atts['lede']); ?></p>
            <div class="ok-platform-story__rows">
                <?php foreach ($rows as $key => $row) : ?>
                    <p class="ok-platform-story__row ok-platform-story__row--<?php echo esc_attr($key); ?>">
                        <span class="ok-platform-story__term"><?php echo esc_html($row['term']); ?></span>
                        <span class="ok-platform-story__desc"><?php echo esc_html($row['desc']); ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // Pillar config — static data for intro and curated section headers.
    // Kept here (not imported from OKArcana_Destinations) to avoid coupling.
    // -------------------------------------------------------------------------

    private static function pillar_config()
    {
        return array(
            'pulse'     => array(
                'icon'  => 'fas fa-wave-square',
                'name'  => 'Pulse',
                'desc'  => 'Fast observations. Quick reactions.',
                'href'  => '/pulse/',
            ),
            'signals'   => array(
                'icon'  => 'fas fa-bolt',
                'name'  => 'Signals',
                'desc'  => 'Fast clips. Under 90 seconds.',
                'href'  => '/video-category/signals/',
            ),
            'arcana'    => array(
                'icon'  => 'fas fa-gem',
                'name'  => 'Arcana',
                'desc'  => 'Readings, premonitions, outcomes.',
                'href'  => '/video-category/arcana/',
            ),
            'creators'  => array(
                'icon'  => 'fas fa-user-group',
                'name'  => 'Creators',
                'desc'  => 'Independent creators. Real communities.',
                'href'  => '/member-list/',
            ),
            'community' => array(
                'icon'  => 'fas fa-comments',
                'name'  => 'Community',
                'desc'  => 'Join the conversation.',
                'href'  => '/community/',
            ),
            'watch'     => array(
                'icon'  => 'fas fa-play-circle',
                'name'  => 'Watch',
                'desc'  => 'All OFFKILTER content.',
                'href'  => '/',
            ),
        );
    }

    // -------------------------------------------------------------------------
    // [oktv_platform_intro]
    // "What is OFFKILTER?" — pillar grid for homepage orientation.
    //
    // Attributes:
    //   headline      string   "OFFKILTER is a discovery platform."
    //   sub           string   "Three things worth understanding."
    //   pillars       string   "signals,arcana,creators" (comma-separated keys)
    //   wrapper_class string   ""
    // -------------------------------------------------------------------------

    public static function render_platform_intro($atts)
    {
        $atts = shortcode_atts(array(
            'headline'      => 'What makes OFFKILTER worth returning to.',
            'sub'           => 'Five distinctive pillars. One platform.',
            'pillars'       => 'pulse,signals,arcana,creators,community',
            'wrapper_class' => '',
        ), $atts, 'oktv_platform_intro');

        $config        = self::pillar_config();
        $pillar_keys   = array_filter(array_map('sanitize_key', explode(',', (string) $atts['pillars'])));
        $outer_class   = 'ok-platform-intro';

        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <section class="<?php echo esc_attr($outer_class); ?>" role="region" aria-label="<?php echo esc_attr($atts['headline']); ?>">
            <h2 class="ok-platform-intro__headline"><?php echo esc_html($atts['headline']); ?></h2>
            <?php if ($atts['sub']) : ?>
                <p class="ok-platform-intro__sub"><?php echo esc_html($atts['sub']); ?></p>
            <?php endif; ?>
            <div class="ok-platform-intro__grid">
                <?php foreach ($pillar_keys as $key) :
                    if (!isset($config[$key])) {
                        continue;
                    }
                    $p = $config[$key];
                ?>
                    <a href="<?php echo esc_url($p['href']); ?>"
                       class="ok-platform-intro__card ok-platform-intro__card--<?php echo esc_attr($key); ?>">
                        <span class="ok-platform-intro__card-icon" aria-hidden="true">
                            <i class="<?php echo esc_attr($p['icon']); ?>"></i>
                        </span>
                        <span class="ok-platform-intro__card-name"><?php echo esc_html($p['name']); ?></span>
                        <span class="ok-platform-intro__card-desc"><?php echo esc_html($p['desc']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_curated_section]
    // Editorial curation wrapper — hand-picked or category-scoped section
    // with an editorial accent header (title + optional label badge).
    //
    // Attributes:
    //   title         string  "Worth Watching"
    //   label         string  "" (no badge if empty)
    //   post_ids      string  "" (comma-separated IDs — overrides query)
    //   categories    string  ""
    //   limit         int     6
    //   layout        string  "cards" | "list"
    //   pillar        string  "" (signals|arcana|creators|community — tints header)
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_curated_section($atts)
    {
        $atts = shortcode_atts(array(
            'title'         => 'Worth Watching',
            'label'         => '',
            'post_ids'      => '',
            'categories'    => '',
            'limit'         => 6,
            'layout'        => 'cards',
            'pillar'        => '',
            'wrapper_class' => '',
        ), $atts, 'oktv_curated_section');

        $limit       = max(1, min(30, (int) $atts['limit']));
        $layout      = in_array((string) $atts['layout'], array('cards', 'list'), true) ? $atts['layout'] : 'cards';
        $pillar      = sanitize_key($atts['pillar']);
        $outer_class = 'ok-curated-section';

        if ($pillar) {
            $outer_class .= ' ok-curated-section--' . $pillar;
        }

        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        // Build query
        $explicit_ids = array_filter(array_map('absint', explode(',', (string) $atts['post_ids'])));
        $categories   = OKArcana_Settings::split_terms((string) $atts['categories']);

        if (!empty($explicit_ids)) {
            $query_args = array(
                'post_type'      => 'vidmov_video',
                'post_status'    => 'publish',
                'posts_per_page' => count($explicit_ids),
                'post__in'       => $explicit_ids,
                'orderby'        => 'post__in',
                'no_found_rows'  => true,
            );
        } else {
            $query_args = array(
                'post_type'      => 'vidmov_video',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            );

            if (!empty($categories)) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'vidmov_video_category',
                        'field'    => 'name',
                        'terms'    => $categories,
                    ),
                );
            }
        }

        $q = new WP_Query($query_args);

        ob_start();
        ?>
        <section class="<?php echo esc_attr($outer_class); ?>" role="region" aria-label="<?php echo esc_attr($atts['title']); ?>">
            <header class="ok-curated-section__header">
                <h3 class="ok-curated-section__title"><?php echo esc_html($atts['title']); ?></h3>
                <?php if ($atts['label']) : ?>
                    <span class="ok-curated-section__label"><?php echo esc_html($atts['label']); ?></span>
                <?php endif; ?>
            </header>

            <?php if (!$q->have_posts()) : ?>
                <div class="ok-empty-state">
                    <span class="ok-empty-state__icon" aria-hidden="true"><i class="fas fa-layer-group"></i></span>
                    <span class="ok-empty-state__label"><?php esc_html_e('Nothing here yet.', 'offkilter-arcana'); ?></span>
                </div>
            <?php elseif ($layout === 'cards') : ?>
                <div class="oktv-signals-cards">
                    <?php while ($q->have_posts()) : $q->the_post();
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        $duration  = self::format_duration((int) get_post_meta(get_the_ID(), OKArcana_Signals::META_DURATION_SECONDS, true));
                        $creator   = get_the_author_meta('display_name', (int) get_post_field('post_author', get_the_ID()));
                    ?>
                        <article class="oktv-signals-card">
                            <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
                                <div class="oktv-signals-card__thumb<?php echo $thumb_url ? '' : ' oktv-signals-card__thumb--empty'; ?>">
                                    <?php if ($thumb_url) : ?>
                                        <img src="<?php echo esc_url($thumb_url); ?>"
                                             alt="<?php echo esc_attr(get_the_title()); ?>"
                                             loading="lazy" width="320" height="180">
                                    <?php endif; ?>
                                    <?php if ($duration !== '') : ?>
                                        <span class="oktv-signals-card__duration"><?php echo esc_html($duration); ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="oktv-signals-card__body">
                                <p class="oktv-signals-card__title">
                                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                </p>
                                <div class="oktv-signals-card__footer">
                                    <?php if ($creator !== '') : ?>
                                        <span class="oktv-signals-card__creator"><?php echo esc_html($creator); ?></span>
                                    <?php endif; ?>
                                    <span class="oktv-signals-card__date"><?php echo esc_html(get_the_date('M j')); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
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

    // -------------------------------------------------------------------------
    // [oktv_creator_page]
    // Full creator page layout — replaces compact spotlight on channel pages.
    //
    // Attributes:
    //   user_id          int     0  (required)
    //   show_bio         int     0  (renders WP user description)
    //   featured_ids     string  "" (comma-separated post IDs for pinned section)
    //   show_signals     int     1
    //   signals_limit    int     6
    //   show_videos      int     0  (long-form Watch content)
    //   videos_limit     int     6
    //   videos_label     string  "Videos"
    //   show_playlists   int     0
    //   playlist_title   string  "Playlist"
    //   playlist_ids     string  "" (comma-separated post IDs — ordered list)
    //   show_related     int     0
    //   related_ids      string  "" (comma-separated user IDs)
    //   show_discuss_cta int     0
    //   wrapper_class    string  ""
    // -------------------------------------------------------------------------

    public static function render_creator_page($atts)
    {
        $atts = shortcode_atts(array(
            'user_id'          => 0,
            'show_bio'         => 1,
            'featured_ids'     => '',
            'show_signals'     => 1,
            'signals_limit'    => 6,
            'show_pulse'       => 0,
            'pulse_limit'      => 6,
            'pulse_label'      => 'Pulse',
            'show_videos'      => 0,
            'videos_limit'     => 6,
            'videos_label'     => 'Videos',
            'show_playlists'   => 0,
            'playlist_title'   => 'Playlist',
            'playlist_ids'     => '',
            'show_related'      => 0,
            'related_ids'       => '',
            'show_discuss_cta'  => 0,
            'show_completeness' => 0,
            'show_banner'       => 1,
            'wrapper_class'     => '',
        ), $atts, 'oktv_creator_page');

        $user_id = (int) $atts['user_id'];
        if ($user_id <= 0) {
            return '';
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return '';
        }

        $display_name  = $user->display_name;
        $handle        = $user->user_login;
        $clip_count    = count_user_posts($user_id, 'vidmov_video', true);
        $outer_class   = 'ok-creator-page';
        $signals_limit = max(1, min(12, (int) $atts['signals_limit']));
        $videos_limit  = max(1, min(12, (int) $atts['videos_limit']));

        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>">

            <?php if (!empty($atts['show_banner'])) :
                $banner_url      = get_user_meta($user_id, 'okarcana_banner', true);
                $banner_fallback = OKARCANA_PLUGIN_URL . 'assets/img/default-channel-banner.svg';
            ?>
            <div class="ok-creator-page__banner"
                 role="img"
                 aria-label="<?php echo esc_attr($display_name); ?>"
                 style="background-image:url('<?php echo esc_url($banner_url ?: $banner_fallback); ?>')">
            </div>
            <?php endif; ?>

            <!-- Creator header -->
            <div class="ok-creator-page__header">
                <div class="ok-creator-page__avatar">
                    <?php $custom_avatar_url = get_user_meta($user_id, 'okarcana_avatar', true); ?>
                    <?php if ($custom_avatar_url) : ?>
                        <img src="<?php echo esc_url($custom_avatar_url); ?>"
                             alt="<?php echo esc_attr($display_name); ?>"
                             class="ok-creator-page__avatar-img"
                             width="80" height="80" loading="lazy">
                    <?php else : ?>
                        <?php echo get_avatar($user_id, 80, '', esc_attr($display_name)); ?>
                    <?php endif; ?>
                </div>
                <div class="ok-creator-page__identity">
                    <p class="ok-creator-page__name">
                        <?php echo esc_html($display_name); ?>
                        <?php if (get_user_meta($user_id, '_ok_creator_verified', true)) : ?>
                            <span class="ok-verified-badge" title="<?php esc_attr_e('Verified creator', 'offkilter-arcana'); ?>" aria-label="<?php esc_attr_e('Verified creator', 'offkilter-arcana'); ?>"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </p>
                    <p class="ok-creator-page__handle">@<?php echo esc_html($handle); ?></p>
                    <p class="ok-creator-page__stat">
                        <strong><?php echo (int) $clip_count; ?></strong> <?php esc_html_e('clips', 'offkilter-arcana'); ?>
                    </p>
                </div>
            </div>

            <?php
            // Profile completeness — owner-only prompt to finish their destination.
            if (!empty($atts['show_completeness']) && get_current_user_id() === $user_id) :
                $checks = array(
                    __('Avatar', 'offkilter-arcana')   => (bool) get_user_meta($user_id, 'okarcana_avatar', true) || (strpos(get_avatar_url($user_id), 'gravatar.com/avatar') === false),
                    __('Bio', 'offkilter-arcana')      => (bool) get_user_meta($user_id, 'description', true),
                    __('Content', 'offkilter-arcana')  => $clip_count > 0,
                    __('Verified', 'offkilter-arcana') => (bool) get_user_meta($user_id, '_ok_creator_verified', true),
                );
                $done = count(array_filter($checks));
                $total = count($checks);
                $pct = $total ? (int) round($done / $total * 100) : 0;
            ?>
                <div class="ok-creator-page__section ok-profile-meter">
                    <p class="ok-creator-page__section-title">
                        <?php printf(esc_html__('Profile %d%% complete', 'offkilter-arcana'), $pct); ?>
                    </p>
                    <div class="ok-profile-meter__bar"><span style="width:<?php echo (int) $pct; ?>%"></span></div>
                    <ul class="ok-profile-meter__list">
                        <?php foreach ($checks as $label => $ok) : ?>
                            <li class="<?php echo $ok ? 'is-done' : 'is-todo'; ?>">
                                <i class="fas <?php echo $ok ? 'fa-circle-check' : 'fa-circle'; ?>" aria-hidden="true"></i>
                                <?php echo esc_html($label); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
            // Bio / About
            if (!empty($atts['show_bio'])) :
                $bio = get_user_meta($user_id, 'description', true);
                if (!empty($bio)) :
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php esc_html_e('About', 'offkilter-arcana'); ?></p>
                    <p class="ok-creator-page__bio"><?php echo wp_kses_post($bio); ?></p>
                </div>
            <?php endif; endif; ?>

            <?php
            // Featured content
            $featured_ids = array_filter(array_map('absint', explode(',', (string) $atts['featured_ids'])));
            if (!empty($featured_ids)) :
                $featured_q = new WP_Query(array(
                    'post_type'      => 'vidmov_video',
                    'post_status'    => 'publish',
                    'posts_per_page' => count($featured_ids),
                    'post__in'       => $featured_ids,
                    'orderby'        => 'post__in',
                    'no_found_rows'  => true,
                ));
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php esc_html_e('Featured', 'offkilter-arcana'); ?></p>
                    <div class="ok-creator-page__featured">
                        <?php while ($featured_q->have_posts()) : $featured_q->the_post();
                            $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        ?>
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="ok-creator-page__featured-item">
                                <?php if ($thumb) : ?>
                                    <img src="<?php echo esc_url($thumb); ?>"
                                         alt="<?php echo esc_attr(get_the_title()); ?>"
                                         loading="lazy" width="320" height="180">
                                <?php endif; ?>
                                <span class="ok-creator-page__featured-title"><?php echo esc_html(get_the_title()); ?></span>
                            </a>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            // Latest Signals
            if (!empty($atts['show_signals'])) :
                $signals_q = new WP_Query(array(
                    'post_type'      => 'vidmov_video',
                    'post_status'    => 'publish',
                    'author'         => $user_id,
                    'posts_per_page' => $signals_limit,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'no_found_rows'  => true,
                    'meta_query'     => array(
                        array(
                            'key'     => OKArcana_Signals::META_CLASS,
                            'value'   => OKArcana_Signals::CLASS_SIGNAL,
                            'compare' => '=',
                        ),
                    ),
                ));
                if ($signals_q->have_posts()) :
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php esc_html_e('Latest Signals', 'offkilter-arcana'); ?></p>
                    <div class="oktv-signals-cards">
                        <?php while ($signals_q->have_posts()) : $signals_q->the_post();
                            $thumb    = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                            $duration = self::format_duration((int) get_post_meta(get_the_ID(), OKArcana_Signals::META_DURATION_SECONDS, true));
                        ?>
                            <article class="oktv-signals-card">
                                <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="oktv-signals-card__thumb<?php echo $thumb ? '' : ' oktv-signals-card__thumb--empty'; ?>">
                                        <?php if ($thumb) : ?>
                                            <img src="<?php echo esc_url($thumb); ?>"
                                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                                 loading="lazy" width="320" height="180">
                                        <?php endif; ?>
                                        <?php if ($duration !== '') : ?>
                                            <span class="oktv-signals-card__duration"><?php echo esc_html($duration); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div class="oktv-signals-card__body">
                                    <p class="oktv-signals-card__title">
                                        <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                    </p>
                                    <div class="oktv-signals-card__footer">
                                        <span class="oktv-signals-card__date"><?php echo esc_html(get_the_date('M j')); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; endif; ?>

            <?php
            // Pulse (short-form clips published via the Pulse Clipper)
            if (!empty($atts['show_pulse']) && class_exists('OKArcana_Pulse')) :
                $pulse_html = OKArcana_Pulse::render_pulse_feed_shortcode(array(
                    'creator_id' => $user_id,
                    'limit'      => max(1, min(12, (int) $atts['pulse_limit'])),
                    'layout'     => 'cards',
                ));
                // Only render the section if the feed produced cards (not the empty state).
                if (strpos($pulse_html, 'oktv-signals-card') !== false) :
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php echo esc_html($atts['pulse_label']); ?></p>
                    <?php echo $pulse_html; ?>
                </div>
            <?php endif; endif; ?>

            <?php
            // Videos (long-form Watch content)
            if (!empty($atts['show_videos'])) :
                $videos_q = new WP_Query(array(
                    'post_type'      => 'vidmov_video',
                    'post_status'    => 'publish',
                    'author'         => $user_id,
                    'posts_per_page' => $videos_limit,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'no_found_rows'  => true,
                    'meta_query'     => array(
                        array(
                            'key'     => OKArcana_Signals::META_CLASS,
                            'value'   => OKArcana_Signals::CLASS_VIDEO,
                            'compare' => '=',
                        ),
                    ),
                ));
                if ($videos_q->have_posts()) :
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php echo esc_html($atts['videos_label']); ?></p>
                    <div class="oktv-signals-cards">
                        <?php while ($videos_q->have_posts()) : $videos_q->the_post();
                            $thumb    = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                            $duration = self::format_duration((int) get_post_meta(get_the_ID(), OKArcana_Signals::META_DURATION_SECONDS, true));
                        ?>
                            <article class="oktv-signals-card">
                                <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="oktv-signals-card__thumb<?php echo $thumb ? '' : ' oktv-signals-card__thumb--empty'; ?>">
                                        <?php if ($thumb) : ?>
                                            <img src="<?php echo esc_url($thumb); ?>"
                                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                                 loading="lazy" width="320" height="180">
                                        <?php endif; ?>
                                        <?php if ($duration !== '') : ?>
                                            <span class="oktv-signals-card__duration"><?php echo esc_html($duration); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div class="oktv-signals-card__body">
                                    <p class="oktv-signals-card__title">
                                        <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                    </p>
                                    <div class="oktv-signals-card__footer">
                                        <span class="oktv-signals-card__date"><?php echo esc_html(get_the_date('M j')); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; endif; ?>

            <?php
            // Playlist
            $playlist_ids = array_filter(array_map('absint', explode(',', (string) $atts['playlist_ids'])));
            if (!empty($atts['show_playlists']) && !empty($playlist_ids)) :
                $playlist_q = new WP_Query(array(
                    'post_type'      => 'vidmov_video',
                    'post_status'    => 'publish',
                    'posts_per_page' => count($playlist_ids),
                    'post__in'       => $playlist_ids,
                    'orderby'        => 'post__in',
                    'no_found_rows'  => true,
                ));
                if ($playlist_q->have_posts()) :
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php echo esc_html($atts['playlist_title']); ?></p>
                    <ol class="ok-creator-page__playlist">
                        <?php $n = 1; while ($playlist_q->have_posts()) : $playlist_q->the_post(); ?>
                            <li class="ok-creator-page__playlist-item">
                                <span class="ok-creator-page__playlist-num"><?php echo (int) $n++; ?></span>
                                <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ol>
                </div>
            <?php endif; endif; ?>

            <?php
            // Related creators
            $related_ids = array_filter(array_map('absint', explode(',', (string) $atts['related_ids'])));
            if (!empty($atts['show_related']) && !empty($related_ids)) :
            ?>
                <div class="ok-creator-page__section">
                    <p class="ok-creator-page__section-title"><?php esc_html_e('Discover Creators', 'offkilter-arcana'); ?></p>
                    <div class="ok-creator-page__related">
                        <?php foreach ($related_ids as $related_uid) :
                            $related_uid = (int) $related_uid;
                            if ($related_uid <= 0 || $related_uid === $user_id) {
                                continue;
                            }
                            echo OKArcana_Signals::render_creator_spotlight_shortcode(array(
                                'user_id'     => $related_uid,
                                'show_stats'  => 1,
                                'show_latest' => 0,
                            ));
                        endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            // Discuss CTA
            if (!empty($atts['show_discuss_cta'])) :
                echo OKArcana_Editorial::render_discuss_cta(array('context' => 'creator', 'coming_soon' => '1'));
            endif;
            ?>

        </div>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_discover_page]
    // Editorial Discover page — an alternative to Trending.
    // Four sections: Featured Signals, Featured Arcana, Creators to Watch,
    // Discussions placeholder.
    //
    // Attributes:
    //   signals_label    string  "Featured Signals"
    //   signals_ids      string  "" (hand-picked; falls back to category query)
    //   signals_limit    int     4
    //   arcana_label     string  "Featured Arcana"
    //   arcana_ids       string  ""
    //   arcana_limit     int     4
    //   show_creators    int     1
    //   creators_label   string  "Creators to Watch"
    //   creator_ids      string  "" (comma-sep user IDs; falls back to top creators)
    //   creators_limit   int     3
    //   show_discussions int     1
    //   discussions_label string "In the Community"
    //   wrapper_class    string  ""
    // -------------------------------------------------------------------------

    public static function render_discover_page($atts)
    {
        $atts = shortcode_atts(array(
            'show_editors'     => 0,
            'editors_label'    => "Editor's Picks",
            'editors_ids'      => '',
            'show_pulse'       => 1,
            'pulse_label'      => 'Featured Pulse',
            'pulse_ids'        => '',
            'pulse_limit'      => 4,
            'signals_label'    => 'Featured Signals',
            'signals_ids'      => '',
            'signals_limit'    => 4,
            'arcana_label'     => 'Featured Arcana',
            'arcana_ids'       => '',
            'arcana_limit'     => 4,
            'show_creators'    => 1,
            'creators_label'   => 'Creators to Watch',
            'creator_ids'      => '',
            'creators_limit'   => 3,
            'show_discussions' => 1,
            'discussions_label' => 'In the Community',
            'wrapper_class'    => '',
        ), $atts, 'oktv_discover_page');

        $outer_class = 'ok-discover-page';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>">

            <?php // Editor's Picks — hand-picked, cross-type, leads when enabled. ?>
            <?php if (!empty($atts['show_editors']) && $atts['editors_ids']) : ?>
            <div class="ok-discover-section">
                <?php
                echo self::render_curated_section(array(
                    'title'    => $atts['editors_label'],
                    'label'    => "Editor's Pick",
                    'post_ids' => $atts['editors_ids'],
                    'layout'   => 'cards',
                ));
                ?>
            </div>
            <?php endif; ?>

            <?php // Featured Pulse — editorial, leads the destination. ?>
            <?php if (!empty($atts['show_pulse']) && class_exists('OKArcana_Pulse')) : ?>
            <div class="ok-discover-section">
                <?php
                echo OKArcana_Pulse::render_pulse_feed_shortcode(array(
                    'title'    => $atts['pulse_label'],
                    'post_ids' => $atts['pulse_ids'],
                    'limit'    => (int) $atts['pulse_limit'],
                    'layout'   => 'cards',
                ));
                ?>
            </div>
            <?php endif; ?>

            <!-- Featured Signals section -->
            <div class="ok-discover-section">
                <?php
                echo self::render_curated_section(array(
                    'title'      => $atts['signals_label'],
                    'post_ids'   => $atts['signals_ids'],
                    'categories' => $atts['signals_ids'] ? '' : 'signals',
                    'limit'      => (int) $atts['signals_limit'],
                    'layout'     => 'cards',
                    'pillar'     => 'signals',
                ));
                ?>
            </div>

            <!-- Featured Arcana section -->
            <div class="ok-discover-section">
                <?php
                echo self::render_curated_section(array(
                    'title'      => $atts['arcana_label'],
                    'post_ids'   => $atts['arcana_ids'],
                    'categories' => $atts['arcana_ids'] ? '' : 'arcana',
                    'limit'      => (int) $atts['arcana_limit'],
                    'layout'     => 'cards',
                    'pillar'     => 'arcana',
                ));
                ?>
            </div>

            <?php
            // Creators to Watch
            if (!empty($atts['show_creators'])) :
                $creator_ids   = array_filter(array_map('absint', explode(',', (string) $atts['creator_ids'])));
                $creators_limit = max(1, min(8, (int) $atts['creators_limit']));

                // When no IDs given, fetch creators with published vidmov_video content
                if (empty($creator_ids)) {
                    $creator_users = get_users(array(
                        'has_published_posts' => array('vidmov_video'),
                        'number'             => $creators_limit,
                        'orderby'            => 'post_count',
                        'order'              => 'DESC',
                    ));
                    $creator_ids = wp_list_pluck($creator_users, 'ID');
                }
            ?>
                <div class="ok-discover-section">
                    <header class="ok-curated-section__header">
                        <h3 class="ok-curated-section__title"><?php echo esc_html($atts['creators_label']); ?></h3>
                    </header>
                    <div class="ok-discover-creators-row">
                        <?php foreach ($creator_ids as $uid) :
                            echo OKArcana_Signals::render_creator_spotlight_shortcode(array(
                                'user_id'     => (int) $uid,
                                'show_stats'  => 1,
                                'show_latest' => 0,
                            ));
                        endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            // Discussions placeholder
            if (!empty($atts['show_discussions'])) :
            ?>
                <div class="ok-discover-section">
                    <header class="ok-curated-section__header">
                        <h3 class="ok-curated-section__title"><?php echo esc_html($atts['discussions_label']); ?></h3>
                    </header>
                    <div class="ok-discuss-placeholder">
                        <?php echo OKArcana_Editorial::render_discuss_cta(array(
                            'context'    => 'discover',
                            'label'      => __('Join the Community', 'offkilter-arcana'),
                            'href'       => '/community/',
                            'coming_soon' => '0',
                        )); ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_recommended_next]
    // "Watch Next" block for video detail pages.
    // Queries related content by shared taxonomy; auto-detects current post.
    //
    // Attributes:
    //   post_id       int     0  (auto-detects get_the_ID() if 0)
    //   limit         int     3
    //   label         string  "Watch Next"
    //   pillar        string  "" (fallback: auto-detected from post taxonomy)
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_recommended_next($atts)
    {
        $atts = shortcode_atts(array(
            'post_id'       => 0,
            'limit'         => 3,
            'label'         => 'Watch Next',
            'pillar'        => '',
            'wrapper_class' => '',
        ), $atts, 'oktv_recommended_next');

        $post_id = (int) $atts['post_id'];
        if ($post_id <= 0) {
            $post_id = (int) get_the_ID();
        }

        if ($post_id <= 0) {
            return '';
        }

        $limit = max(1, min(6, (int) $atts['limit']));

        // Detect taxonomy terms for related query
        $terms   = get_the_terms($post_id, 'vidmov_video_category');
        $term_ids = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            $term_ids = wp_list_pluck($terms, 'term_id');
        }

        if (!empty($term_ids)) {
            $query_args = array(
                'post_type'      => 'vidmov_video',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => array($post_id),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'vidmov_video_category',
                        'field'    => 'term_id',
                        'terms'    => $term_ids,
                    ),
                ),
            );
        } else {
            // Fallback: recent posts
            $query_args = array(
                'post_type'      => 'vidmov_video',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => array($post_id),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            );
        }

        $q = new WP_Query($query_args);

        if (!$q->have_posts()) {
            return '';
        }

        $outer_class = 'ok-recommended-next';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>">
            <p class="ok-recommended-next__label"><?php echo esc_html($atts['label']); ?></p>
            <div class="ok-recommended-next__items">
                <?php while ($q->have_posts()) : $q->the_post();
                    $thumb   = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    $creator = get_the_author_meta('display_name', (int) get_post_field('post_author', get_the_ID()));
                    $aria    = $creator ? get_the_title() . ' — ' . $creator : get_the_title();
                ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="ok-recommended-next__item" aria-label="<?php echo esc_attr($aria); ?>">
                        <div class="ok-recommended-next__thumb<?php echo $thumb ? '' : ' ok-recommended-next__thumb--empty'; ?>">
                            <?php if ($thumb) : ?>
                                <img src="<?php echo esc_url($thumb); ?>"
                                     alt="<?php echo esc_attr(get_the_title()); ?>"
                                     loading="lazy" width="320" height="180">
                            <?php endif; ?>
                        </div>
                        <span class="ok-recommended-next__title"><?php echo esc_html(get_the_title()); ?></span>
                        <?php if ($creator) : ?>
                            <span class="ok-recommended-next__creator"><?php echo esc_html($creator); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_watch_nav]
    // Playlist prev/next navigation for video detail pages.
    //
    // Attributes:
    //   playlist_ids  string  "" (required: comma-sep post IDs defining order)
    //   current_id    int     0  (auto-detects get_the_ID() if 0)
    //   prev_label    string  "Previous"
    //   next_label    string  "Next"
    //   show_title    int     1  (show post title alongside direction label)
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_watch_nav($atts)
    {
        $atts = shortcode_atts(array(
            'playlist_ids'  => '',
            'current_id'    => 0,
            'prev_label'    => 'Previous',
            'next_label'    => 'Next',
            'show_title'    => 1,
            'wrapper_class' => '',
        ), $atts, 'oktv_watch_nav');

        $playlist_ids = array_values(array_filter(array_map('absint', explode(',', (string) $atts['playlist_ids']))));
        if (empty($playlist_ids)) {
            return '';
        }

        $current_id = (int) $atts['current_id'];
        if ($current_id <= 0) {
            $current_id = (int) get_the_ID();
        }

        $position = array_search($current_id, $playlist_ids, true);
        if ($position === false) {
            return '';
        }

        $prev_id = ($position > 0) ? $playlist_ids[$position - 1] : 0;
        $next_id = ($position < count($playlist_ids) - 1) ? $playlist_ids[$position + 1] : 0;

        if (!$prev_id && !$next_id) {
            return '';
        }

        $outer_class = 'ok-watch-nav';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <nav class="<?php echo esc_attr($outer_class); ?>" aria-label="<?php esc_attr_e('Playlist navigation', 'offkilter-arcana'); ?>">
            <?php if ($prev_id) : ?>
                <a href="<?php echo esc_url(get_permalink($prev_id)); ?>" class="ok-watch-nav__prev" rel="prev"
                   aria-label="<?php echo esc_attr($atts['prev_label'] . ': ' . get_the_title($prev_id)); ?>">
                    <span class="ok-watch-nav__direction">&#8592; <?php echo esc_html($atts['prev_label']); ?></span>
                    <?php if (!empty($atts['show_title'])) : ?>
                        <span class="ok-watch-nav__title"><?php echo esc_html(get_the_title($prev_id)); ?></span>
                    <?php endif; ?>
                </a>
            <?php else : ?>
                <span class="ok-watch-nav__prev ok-watch-nav__prev--empty"></span>
            <?php endif; ?>

            <?php if ($next_id) : ?>
                <a href="<?php echo esc_url(get_permalink($next_id)); ?>" class="ok-watch-nav__next" rel="next"
                   aria-label="<?php echo esc_attr($atts['next_label'] . ': ' . get_the_title($next_id)); ?>">
                    <span class="ok-watch-nav__direction"><?php echo esc_html($atts['next_label']); ?> &#8594;</span>
                    <?php if (!empty($atts['show_title'])) : ?>
                        <span class="ok-watch-nav__title"><?php echo esc_html(get_the_title($next_id)); ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </nav>
        <?php
        return (string) ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [oktv_featured_creator]
    // Prominent editorial creator callout — horizontal card with avatar,
    // editorial description, and CTA. Larger than [oktv_creator_spotlight].
    //
    // Attributes:
    //   user_id       int     0  (required)
    //   headline      string  "" (overrides display_name if set)
    //   body          string  "" (editorial description, 1–2 sentences)
    //   cta_label     string  "See their work"
    //   cta_href      string  "" (falls back to author archive URL)
    //   pillar        string  "" (signals|arcana|creators|community — accent color)
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_featured_creator($atts)
    {
        $atts = shortcode_atts(array(
            'user_id'       => 0,
            'headline'      => '',
            'body'          => '',
            'cta_label'     => 'See their work',
            'cta_href'      => '',
            'pillar'        => '',
            'wrapper_class' => '',
        ), $atts, 'oktv_featured_creator');

        $user_id = (int) $atts['user_id'];
        if ($user_id <= 0) {
            return '';
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return '';
        }

        $display_name = $atts['headline'] ? $atts['headline'] : $user->display_name;
        $handle       = $user->user_login;
        $cta_href     = $atts['cta_href'] ? $atts['cta_href'] : get_author_posts_url($user_id);
        $pillar       = sanitize_key($atts['pillar']);
        $outer_class  = 'ok-featured-creator';

        if ($pillar) {
            $outer_class .= ' ok-featured-creator--' . $pillar;
        }

        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>">
            <div class="ok-featured-creator__avatar">
                <?php echo get_avatar($user_id, 96, '', esc_attr($display_name)); ?>
            </div>
            <div class="ok-featured-creator__body">
                <p class="ok-featured-creator__name"><?php echo esc_html($display_name); ?></p>
                <p class="ok-featured-creator__handle">@<?php echo esc_html($handle); ?></p>
                <?php if ($atts['body']) : ?>
                    <p class="ok-featured-creator__desc"><?php echo wp_kses_post($atts['body']); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url($cta_href); ?>" class="ok-btn ok-btn--sm"
                   aria-label="<?php echo esc_attr($atts['cta_label'] . ' — ' . $display_name); ?>">
                    <?php echo esc_html($atts['cta_label']); ?>
                </a>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private static function format_duration($seconds)
    {
        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return '';
        }

        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
