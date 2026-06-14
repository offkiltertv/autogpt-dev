<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Activator
{
    public static function activate()
    {
        OKArcana_Settings::ensure_defaults();
        OKArcana_Post_Types::register();
        OKArcana_DB::create_tables();
        OKArcana_Post_Types::ensure_default_terms();
        OKArcana_Scheduler::register_cron_schedules();
        OKArcana_Scheduler::schedule_events();
        OKArcana_Signals::schedule_backfill_event();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        OKArcana_Scheduler::clear_events();
        OKArcana_Signals::clear_backfill_events();
        flush_rewrite_rules();
    }
}
