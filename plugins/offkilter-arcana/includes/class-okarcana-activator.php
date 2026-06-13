<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Activator
{
    public static function activate()
    {
        OKArcana_Post_Types::register();
        OKArcana_DB::create_tables();
        OKArcana_Post_Types::ensure_default_terms();
        OKArcana_Scheduler::register_cron_schedules();
        OKArcana_Scheduler::schedule_events();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        OKArcana_Scheduler::clear_events();
        flush_rewrite_rules();
    }
}
