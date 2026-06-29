<?php
if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Discovery
{
    public static function init()
    {
        add_shortcode('oktv_platform_intro',   array(__CLASS__, 'render_platform_intro'));
        add_shortcode('oktv_curated_section',  array(__CLASS__, 'render_curated_section'));
        add_shortcode('oktv_creator_page',     array(__CLASS__, 'render_creator_page'));
    }

    // -------------------------------------------------------------------------
    // Pillar config — static data for intro and curated section headers.
    // Kept here (not imported from OKArcana_Destinations) to avoid coupling.
    // -------------------------------------------------------------------------

    private static function pillar_config()
    {
        return array(
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
    //   headline     string   "OFFKILTER is a discovery platform."
    //   sub          string   "Three things worth understanding."
    //   pillars      string   "signals,arcana,creators" (comma-separated keys)
    //   wrapper_class string  ""
    // -------------------------------------------------------------------------

    public static function render_platform_intro($atts)
    {
        $atts = shortcode_atts(array(
            'headline'      => 'OFFKILTER is a discovery platform.',
            'sub'           => 'Three things worth understanding.',
            'pillars'       => 'signals,arcana,creators',
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
                    <p><?php esc_html_e('No content found.', 'offkilter-arcana'); ?></p>
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
    //   user_id         int     0  (required)
    //   featured_ids    string  "" (comma-separated post IDs for pinned section)
    //   show_signals    int     1
    //   signals_limit   int     6
    //   show_playlists  int     0
    //   playlist_title  string  "Playlist"
    //   playlist_ids    string  "" (comma-separated post IDs — ordered list)
    //   show_related    int     0
    //   related_ids     string  "" (comma-separated user IDs)
    //   show_discuss_cta int    0
    //   wrapper_class   string  ""
    // -------------------------------------------------------------------------

    public static function render_creator_page($atts)
    {
        $atts = shortcode_atts(array(
            'user_id'          => 0,
            'featured_ids'     => '',
            'show_signals'     => 1,
            'signals_limit'    => 6,
            'show_playlists'   => 0,
            'playlist_title'   => 'Playlist',
            'playlist_ids'     => '',
            'show_related'     => 0,
            'related_ids'      => '',
            'show_discuss_cta' => 0,
            'wrapper_class'    => '',
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

        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($outer_class); ?>">

            <!-- Creator header -->
            <div class="ok-creator-page__header">
                <div class="ok-creator-page__avatar">
                    <?php echo get_avatar($user_id, 80, '', esc_attr($display_name)); ?>
                </div>
                <div class="ok-creator-page__identity">
                    <p class="ok-creator-page__name"><?php echo esc_html($display_name); ?></p>
                    <p class="ok-creator-page__handle">@<?php echo esc_html($handle); ?></p>
                    <p class="ok-creator-page__stat">
                        <strong><?php echo (int) $clip_count; ?></strong> <?php esc_html_e('clips', 'offkilter-arcana'); ?>
                    </p>
                </div>
            </div>

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
                    <p class="ok-creator-page__section-title"><?php esc_html_e('More Creators', 'offkilter-arcana'); ?></p>
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
