<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Scheduler
{
    const CRON_HOOK = 'okarcana_schedule_queue';

    public static function init()
    {
        add_filter('cron_schedules', array(__CLASS__, 'add_cron_interval'));
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_scheduler'));
        add_action('transition_post_status', array(__CLASS__, 'mark_published'), 10, 3);
        add_action('init', array(__CLASS__, 'register_cron_schedules'));
        add_action('init', array(__CLASS__, 'schedule_events'));
    }

    public static function register_cron_schedules()
    {
        add_filter('cron_schedules', array(__CLASS__, 'add_cron_interval'));
    }

    public static function add_cron_interval($schedules)
    {
        if (!isset($schedules['okarcana_quarter_hour'])) {
            $schedules['okarcana_quarter_hour'] = array(
                'interval' => 900,
                'display' => __('Every 15 Minutes (Arcana)', 'offkilter-arcana'),
            );
        }

        return $schedules;
    }

    public static function schedule_events()
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + 120, 'okarcana_quarter_hour', self::CRON_HOOK);
        }
    }

    public static function clear_events()
    {
        $ts = wp_next_scheduled(self::CRON_HOOK);
        while ($ts) {
            wp_unschedule_event($ts, self::CRON_HOOK);
            $ts = wp_next_scheduled(self::CRON_HOOK);
        }
    }

    public static function run_scheduler()
    {
        global $wpdb;

        $table = OKArcana_DB::table_name();

        $imported = $wpdb->get_results("SELECT id, post_id FROM {$table} WHERE queue_state = 'imported' ORDER BY imported_at ASC LIMIT 400", ARRAY_A);
        if (empty($imported)) {
            return;
        }

        // Promote imported records into queued before time-slot assignment.
        foreach ($imported as $row) {
            $wpdb->update(
                $table,
                array(
                    'queue_state' => 'queued',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => (int) $row['id']),
                array('%s', '%s'),
                array('%d')
            );
            update_post_meta((int) $row['post_id'], '_arcana_queue_state', 'queued');
        }

        $queued = $wpdb->get_results("SELECT id, post_id FROM {$table} WHERE queue_state = 'queued' AND scheduled_for IS NULL ORDER BY imported_at ASC LIMIT 400", ARRAY_A);
        if (empty($queued)) {
            return;
        }

        $slots = self::build_available_slots(90, count($queued));
        if (empty($slots)) {
            return;
        }

        foreach ($queued as $index => $row) {
            if (!isset($slots[$index])) {
                break;
            }

            $slot_local = $slots[$index];
            $slot_gmt = get_gmt_from_date($slot_local, 'Y-m-d H:i:s');

            wp_update_post(array(
                'ID' => (int) $row['post_id'],
                'post_status' => 'future',
                'post_date' => $slot_local,
                'post_date_gmt' => $slot_gmt,
            ));

            update_post_meta((int) $row['post_id'], '_arcana_queue_state', 'scheduled');
            update_post_meta((int) $row['post_id'], '_arcana_scheduled_for', $slot_local);

            $wpdb->update(
                $table,
                array(
                    'queue_state' => 'scheduled',
                    'scheduled_for' => $slot_local,
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => (int) $row['id']),
                array('%s', '%s', '%s'),
                array('%d')
            );
        }
    }

    public static function mark_published($new_status, $old_status, $post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        if ($post->post_type !== OKArcana_Post_Types::POST_TYPE) {
            return;
        }

        if ($new_status !== 'publish') {
            return;
        }

        global $wpdb;
        $table = OKArcana_DB::table_name();

        update_post_meta($post->ID, '_arcana_queue_state', 'published');

        $wpdb->update(
            $table,
            array(
                'queue_state' => 'published',
                'published_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('post_id' => (int) $post->ID),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }

    private static function build_available_slots($days, $needed)
    {
        global $wpdb;

        $table = OKArcana_DB::table_name();
        $occupied = $wpdb->get_col("SELECT scheduled_for FROM {$table} WHERE scheduled_for IS NOT NULL");
        $occupied_map = array();

        foreach ($occupied as $dt) {
            $occupied_map[$dt] = true;
        }

        $slots = array();
        $start = strtotime(current_time('mysql'));

        for ($d = 0; $d < $days; $d++) {
            $day_ts = strtotime('+' . $d . ' day', $start);
            $count_today = wp_rand(3, 5);

            for ($i = 0; $i < $count_today; $i++) {
                $hour = wp_rand(8, 22);
                $minute = wp_rand(0, 3) * 15;

                $slot = date_i18n('Y-m-d', $day_ts) . sprintf(' %02d:%02d:00', $hour, $minute);
                if (!isset($occupied_map[$slot])) {
                    $slots[] = $slot;
                    $occupied_map[$slot] = true;
                }
            }

            if (count($slots) >= $needed) {
                break;
            }
        }

        sort($slots);
        return $slots;
    }
}
