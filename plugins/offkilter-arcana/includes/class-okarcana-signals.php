<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Signals
{
    const META_CLASS = '_oktv_content_class';
    const META_IS_SIGNAL = '_oktv_is_signal';
    const META_DURATION_SECONDS = '_oktv_duration_seconds';
    const META_DURATION_SOURCE = '_oktv_duration_source';
    const META_CLASSIFIED_AT = '_oktv_signals_classified_at';
    const CLASS_SIGNAL = 'signal';
    const CLASS_VIDEO = 'video';
    const CLASS_UNKNOWN = 'unknown';
    const BACKFILL_CRON_HOOK = 'okarcana_signals_backfill';

    public static function init()
    {
        add_action('save_post_vidmov_video', array(__CLASS__, 'classify_vidmov_post'), 20, 3);
        add_action('init', array(__CLASS__, 'schedule_backfill_event'));
        add_action(self::BACKFILL_CRON_HOOK, array(__CLASS__, 'run_backfill_batch'));
        add_shortcode('oktv_signals_latest', array(__CLASS__, 'render_signals_shortcode'));
        add_shortcode('oktv_arcana_signals_latest', array(__CLASS__, 'render_arcana_signals_shortcode'));
        add_shortcode('oktv_creator_spotlight', array(__CLASS__, 'render_creator_spotlight_shortcode'));
    }

    public static function schedule_backfill_event()
    {
        if (!wp_next_scheduled(self::BACKFILL_CRON_HOOK)) {
            wp_schedule_event(time() + 180, 'hourly', self::BACKFILL_CRON_HOOK);
        }
    }

    public static function clear_backfill_events()
    {
        $ts = wp_next_scheduled(self::BACKFILL_CRON_HOOK);
        while ($ts) {
            wp_unschedule_event($ts, self::BACKFILL_CRON_HOOK);
            $ts = wp_next_scheduled(self::BACKFILL_CRON_HOOK);
        }
    }

    public static function classify_vidmov_post($post_id, $post, $update)
    {
        if (!(int) OKArcana_Settings::get('enable_signals_classification', 1)) {
            return;
        }

        if (!($post instanceof WP_Post) || $post->post_type !== 'vidmov_video') {
            return;
        }

        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if (!self::is_imported_post($post_id)) {
            return;
        }

        self::classify_post($post_id, $post);
    }

    public static function run_backfill_batch()
    {
        if (!(int) OKArcana_Settings::get('enable_signals_classification', 1)) {
            return 0;
        }

        $batch_size = max(1, min(500, (int) OKArcana_Settings::get('signals_backfill_batch_size', 75)));
        $ids = get_posts(array(
            'post_type' => 'vidmov_video',
            'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
            'fields' => 'ids',
            'posts_per_page' => $batch_size,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'wp_automatic_camp',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => self::META_CLASS,
                    'compare' => 'NOT EXISTS',
                ),
            ),
            'suppress_filters' => false,
            'no_found_rows' => true,
        ));

        $processed = 0;
        foreach ((array) $ids as $post_id) {
            $post = get_post((int) $post_id);
            if (!($post instanceof WP_Post)) {
                continue;
            }

            self::classify_post((int) $post_id, $post);
            $processed++;
        }

        return $processed;
    }

    public static function render_signals_shortcode($atts = array())
    {
        $atts = shortcode_atts(array(
            'title'         => '⚡ Signals',
            'limit'         => 8,
            'categories'    => '',
            'show_creator'  => 1,
            'show_duration' => 1,
            'show_date'     => 1,
            'layout'        => 'list',
            'wrapper_class' => 'oktv-signals-rail',
        ), $atts, 'oktv_signals_latest');

        $limit = max(1, min(30, (int) $atts['limit']));
        $categories = OKArcana_Settings::split_terms((string) $atts['categories']);

        $query_args = array(
            'post_type' => 'vidmov_video',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
            'no_found_rows' => true,
            'meta_query' => array(
                array(
                    'key' => self::META_CLASS,
                    'value' => self::CLASS_SIGNAL,
                    'compare' => '=',
                ),
            ),
        );

        if (!empty($categories)) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'vidmov_video_category',
                    'field' => 'name',
                    'terms' => $categories,
                ),
            );
        }

        $q = new WP_Query($query_args);
        $layout = in_array((string) $atts['layout'], array('list', 'cards'), true) ? (string) $atts['layout'] : 'list';

        ob_start();

        if ($layout === 'cards') :
            self::render_signals_cards($q, $atts);
        else :
            self::render_signals_list($q, $atts);
        endif;

        wp_reset_postdata();

        return (string) ob_get_clean();
    }

    private static function render_signals_list(WP_Query $q, array $atts)
    {
        ?>
        <section class="<?php echo esc_attr((string) $atts['wrapper_class']); ?>" aria-label="<?php echo esc_attr((string) $atts['title']); ?>">
            <h3><?php echo esc_html((string) $atts['title']); ?></h3>
            <?php if (!$q->have_posts()) : ?>
                <div class="oktv-signals-empty">
                    <span class="oktv-signals-empty__icon" aria-hidden="true">⚡</span>
                    <span class="oktv-signals-empty__label"><?php esc_html_e('No signal clips found yet.', 'offkilter-arcana'); ?></span>
                </div>
            <?php else : ?>
                <ul class="oktv-signals-list">
                    <?php while ($q->have_posts()) : $q->the_post(); ?>
                        <li class="oktv-signals-item">
                            <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            <?php if (!empty($atts['show_creator'])) : ?>
                                <span class="oktv-signals-meta">
                                    <?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', get_the_ID()))); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($atts['show_duration'])) : ?>
                                <?php
                                $duration = self::format_duration((int) get_post_meta(get_the_ID(), self::META_DURATION_SECONDS, true));
                                if ($duration !== '') :
                                ?>
                                    <span class="oktv-signals-meta is-duration"><?php echo esc_html($duration); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($atts['show_date'])) : ?>
                                <span class="oktv-signals-meta"><?php echo esc_html(get_the_date('M j')); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>
        </section>
        <?php
    }

    private static function render_signals_cards(WP_Query $q, array $atts)
    {
        ?>
        <section class="<?php echo esc_attr((string) $atts['wrapper_class']); ?>" aria-label="<?php echo esc_attr((string) $atts['title']); ?>">
            <h3><?php echo esc_html((string) $atts['title']); ?></h3>
            <?php if (!$q->have_posts()) : ?>
                <div class="oktv-signals-empty">
                    <span class="oktv-signals-empty__icon" aria-hidden="true">⚡</span>
                    <span class="oktv-signals-empty__label"><?php esc_html_e('No signal clips found yet.', 'offkilter-arcana'); ?></span>
                </div>
            <?php else : ?>
                <div class="oktv-signals-cards">
                    <?php while ($q->have_posts()) : $q->the_post();
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        $duration  = self::format_duration((int) get_post_meta(get_the_ID(), self::META_DURATION_SECONDS, true));
                        $creator   = get_the_author_meta('display_name', (int) get_post_field('post_author', get_the_ID()));
                    ?>
                        <article class="oktv-signals-card">
                            <a href="<?php echo esc_url(get_permalink()); ?>" tabindex="-1" aria-hidden="true">
                                <div class="oktv-signals-card__thumb<?php echo $thumb_url ? '' : ' oktv-signals-card__thumb--empty'; ?>">
                                    <?php if ($thumb_url) : ?>
                                        <img src="<?php echo esc_url($thumb_url); ?>"
                                             alt="<?php echo esc_attr(get_the_title()); ?>"
                                             loading="lazy"
                                             width="320" height="180">
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
                                    <?php if (!empty($atts['show_creator']) && $creator !== '') : ?>
                                        <span class="oktv-signals-card__creator"><?php echo esc_html($creator); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($atts['show_date'])) : ?>
                                        <span class="oktv-signals-card__date"><?php echo esc_html(get_the_date('M j')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }

    public static function render_creator_spotlight_shortcode($atts = array())
    {
        $atts = shortcode_atts(array(
            'user_id'       => 0,
            'show_stats'    => 1,
            'show_latest'   => 3,
            'wrapper_class' => 'ok-creator-spotlight',
        ), (array) $atts, 'oktv_creator_spotlight');

        $user_id = (int) $atts['user_id'];
        if ($user_id <= 0) {
            return '';
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return '';
        }

        $limit = max(1, min(10, (int) $atts['show_latest']));

        $latest_posts = array();
        if ($limit > 0) {
            $latest_posts = get_posts(array(
                'post_type'   => 'vidmov_video',
                'post_status' => 'publish',
                'author'      => $user_id,
                'numberposts' => $limit,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'fields'      => 'ids',
                'suppress_filters' => false,
            ));
        }

        $video_count  = count_user_posts($user_id, 'vidmov_video', true);
        $display_name = $user->display_name;
        $handle       = $user->user_login;

        ob_start();
        ?>
        <div class="<?php echo esc_attr((string) $atts['wrapper_class']); ?>">
            <div class="ok-creator-spotlight__avatar">
                <?php echo get_avatar($user_id, 72, '', esc_attr($display_name)); ?>
            </div>
            <div class="ok-creator-spotlight__body">
                <p class="ok-creator-spotlight__name"><?php echo esc_html($display_name); ?></p>
                <p class="ok-creator-spotlight__handle">@<?php echo esc_html($handle); ?></p>
                <?php if (!empty($atts['show_stats'])) : ?>
                    <div class="ok-creator-spotlight__stats">
                        <span><strong><?php echo (int) $video_count; ?></strong> videos</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($latest_posts)) : ?>
                    <div class="ok-creator-spotlight__latest">
                        <p class="ok-creator-spotlight__latest-title"><?php esc_html_e('Latest', 'offkilter-arcana'); ?></p>
                        <ul class="ok-creator-spotlight__latest-list">
                            <?php foreach ($latest_posts as $post_id) : ?>
                                <li class="ok-creator-spotlight__latest-item">
                                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_arcana_signals_shortcode($atts = array())
    {
        $atts = shortcode_atts(array(
            'title'         => '🔮 Latest Signals',
            'limit'         => 8,
            'categories'    => 'Arcana,Premonitions,Outcomes',
            'show_creator'  => 1,
            'show_duration' => 1,
            'show_date'     => 1,
            'wrapper_class' => 'oktv-arcana-signals-rail',
            'empty_icon'    => '🔮',
        ), (array) $atts, 'oktv_arcana_signals_latest');

        return self::render_signals_shortcode($atts);
    }

    public static function get_summary_counts()
    {
        global $wpdb;

        $post_type = 'vidmov_video';
        $meta_class = self::META_CLASS;
        $import_meta = 'wp_automatic_camp';

        $sql = $wpdb->prepare(
            "SELECT 
                COUNT(DISTINCT p.ID) AS total,
                SUM(CASE WHEN cls.meta_value = %s THEN 1 ELSE 0 END) AS signals,
                SUM(CASE WHEN cls.meta_value = %s THEN 1 ELSE 0 END) AS videos,
                SUM(CASE WHEN cls.meta_value = %s THEN 1 ELSE 0 END) AS unknown
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} imp
                ON imp.post_id = p.ID AND imp.meta_key = %s
            LEFT JOIN {$wpdb->postmeta} cls
                ON cls.post_id = p.ID AND cls.meta_key = %s
            WHERE p.post_type = %s",
            self::CLASS_SIGNAL,
            self::CLASS_VIDEO,
            self::CLASS_UNKNOWN,
            $import_meta,
            $meta_class,
            $post_type
        );

        $row = $wpdb->get_row($sql, ARRAY_A);
        if (!is_array($row)) {
            return array('total' => 0, 'signals' => 0, 'videos' => 0, 'unknown' => 0);
        }

        return array(
            'total' => (int) $row['total'],
            'signals' => (int) $row['signals'],
            'videos' => (int) $row['videos'],
            'unknown' => (int) $row['unknown'],
        );
    }

    private static function classify_post($post_id, WP_Post $post)
    {
        list($duration_seconds, $source) = self::detect_duration_seconds($post_id, $post);
        $threshold = max(10, min(3600, (int) OKArcana_Settings::get('signals_threshold_seconds', 90)));

        $class = self::CLASS_UNKNOWN;
        $is_signal = 0;

        if ($duration_seconds !== null) {
            if ($duration_seconds <= $threshold) {
                $class = self::CLASS_SIGNAL;
                $is_signal = 1;
            } else {
                $class = self::CLASS_VIDEO;
            }
        }

        update_post_meta($post_id, self::META_CLASS, $class);
        update_post_meta($post_id, self::META_IS_SIGNAL, $is_signal);
        if ($duration_seconds !== null) {
            update_post_meta($post_id, self::META_DURATION_SECONDS, (int) $duration_seconds);
            update_post_meta($post_id, self::META_DURATION_SOURCE, sanitize_text_field((string) $source));
        } else {
            delete_post_meta($post_id, self::META_DURATION_SECONDS);
            delete_post_meta($post_id, self::META_DURATION_SOURCE);
        }
        update_post_meta($post_id, self::META_CLASSIFIED_AT, current_time('mysql'));
    }

    private static function detect_duration_seconds($post_id, WP_Post $post)
    {
        $meta_keys = OKArcana_Settings::split_terms((string) OKArcana_Settings::get('signals_duration_meta_keys', 'beeteam368_video_duration'));
        foreach ($meta_keys as $meta_key) {
            $raw = get_post_meta($post_id, $meta_key, true);
            $seconds = self::parse_duration_value($raw);
            if ($seconds !== null) {
                return array($seconds, 'meta:' . $meta_key);
            }
        }

        $seconds = self::parse_duration_from_content($post->post_content);
        if ($seconds !== null) {
            return array($seconds, 'post_content_runtime');
        }

        return array(null, 'unknown');
    }

    private static function parse_duration_from_content($content)
    {
        $plain = wp_strip_all_tags((string) $content, true);
        $plain = preg_replace('/\s+/', ' ', $plain);

        if (preg_match('/Runtime:\s*(\d{1,2}):(\d{2}):(\d{2})/i', (string) $plain, $m)) {
            return ((int) $m[1] * 3600) + ((int) $m[2] * 60) + (int) $m[3];
        }

        if (preg_match('/Runtime:\s*(\d{1,3}):(\d{2})\b/i', (string) $plain, $m)) {
            return ((int) $m[1] * 60) + (int) $m[2];
        }

        if (preg_match('/Duration:\s*(\d{1,2}):(\d{2}):(\d{2})/i', (string) $plain, $m)) {
            return ((int) $m[1] * 3600) + ((int) $m[2] * 60) + (int) $m[3];
        }

        return null;
    }

    private static function parse_duration_value($raw)
    {
        if (is_numeric($raw)) {
            $seconds = (int) $raw;
            if ($seconds > 0 && $seconds < 86400 * 3) {
                return $seconds;
            }
        }

        if (!is_string($raw)) {
            return null;
        }

        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m)) {
            return ((int) $m[1] * 3600) + ((int) $m[2] * 60) + (int) $m[3];
        }

        if (preg_match('/^(\d{1,3}):(\d{2})$/', $value, $m)) {
            return ((int) $m[1] * 60) + (int) $m[2];
        }

        // ISO-8601 style from APIs, e.g. PT1M18S
        if (preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', strtoupper($value), $m)) {
            $hours = isset($m[1]) ? (int) $m[1] : 0;
            $minutes = isset($m[2]) ? (int) $m[2] : 0;
            $seconds = isset($m[3]) ? (int) $m[3] : 0;
            $total = ($hours * 3600) + ($minutes * 60) + $seconds;
            if ($total > 0) {
                return $total;
            }
        }

        return null;
    }

    private static function format_duration($seconds)
    {
        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return '';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    private static function is_imported_post($post_id)
    {
        $camp = get_post_meta((int) $post_id, 'wp_automatic_camp', true);
        return $camp !== '' && $camp !== null;
    }
}
