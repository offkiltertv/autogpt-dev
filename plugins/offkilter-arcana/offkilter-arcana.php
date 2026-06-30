<?php
/**
 * Plugin Name: OffKilter Arcana
 * Plugin URI: https://offkilter.tv
 * Description: OFFKILTER platform identity, discovery, and content intelligence layer for OffKilter.TV.
 * Version: 2.6.0
 * Author: OffKilter.TV
 * License: GPLv2 or later
 * Text Domain: offkilter-arcana
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OKARCANA_VERSION', '2.6.0');
define('OKARCANA_PLUGIN_FILE', __FILE__);
define('OKARCANA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OKARCANA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OKARCANA_QUEUE_TABLE', 'okarcana_import_queue');

autoload_okarcana();

register_activation_hook(OKARCANA_PLUGIN_FILE, array('OKArcana_Activator', 'activate'));
register_deactivation_hook(OKARCANA_PLUGIN_FILE, array('OKArcana_Activator', 'deactivate'));

add_action('plugins_loaded', 'okarcana_bootstrap');

/**
 * Lightweight class loader.
 */
function autoload_okarcana()
{
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-activator.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-db.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-settings.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-post-types.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-disclaimer.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-enhancer.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-signals.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-importer.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-scheduler.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-wpforo.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-identity.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-frontend.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-cleanup.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-editorial.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-destinations.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-discovery.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-pulse.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-creator.php';
    require_once OKARCANA_PLUGIN_DIR . 'includes/class-okarcana-admin.php';
}

/**
 * Main plugin bootstrap.
 */
function okarcana_bootstrap()
{
    OKArcana_Settings::ensure_defaults();
    OKArcana_Post_Types::init();
    OKArcana_Disclaimer::init();
    OKArcana_Enhancer::init();
    OKArcana_Signals::init();
    OKArcana_Scheduler::init();
    OKArcana_WPForo::init();
    OKArcana_Identity::init();
    OKArcana_Frontend::init();
    OKArcana_Cleanup::init();
    OKArcana_Editorial::init();
    OKArcana_Destinations::init();
    OKArcana_Discovery::init();
    OKArcana_Pulse::init();
    OKArcana_Creator::init();
    OKArcana_Admin::init();
}
