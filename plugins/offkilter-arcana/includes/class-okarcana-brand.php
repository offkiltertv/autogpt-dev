<?php
/**
 * OKArcana_Brand — canonical logo/favicon/manifest wiring.
 *
 * WordPress core's wp_site_icon() already emits <link rel="icon"> and
 * <link rel="apple-touch-icon"> once the Site Icon option points at a
 * properly square attachment — this class only adds what core doesn't
 * cover (manifest link, theme-color meta, a purpose-built apple-touch-icon
 * distinct from core's transparent-source crop) and strips core's
 * apple-touch-icon tag so the two never conflict.
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Brand
{
    public static function init()
    {
        add_action('wp_head', array(__CLASS__, 'head_tags'), 4);
        add_filter('site_icon_meta_tags', array(__CLASS__, 'strip_core_apple_touch_icon'));
        add_action('admin_init', array(__CLASS__, 'maybe_place_root_assets'));
    }

    /**
     * Emit manifest link + theme-color meta + our own apple-touch-icon.
     * Deliberately does NOT emit <link rel="icon"> — core's wp_site_icon()
     * already owns that once the Site Icon option is set correctly.
     */
    public static function head_tags()
    {
        $base = OKARCANA_PLUGIN_URL . 'assets/img/brand/';
        ?>
<link rel="apple-touch-icon" href="<?php echo esc_url($base . 'apple-touch-icon.png'); ?>">
<link rel="manifest" href="<?php echo esc_url($base . 'site.webmanifest'); ?>">
<meta name="theme-color" content="#E50914">
        <?php
    }

    /**
     * Core's wp_site_icon() emits its own apple-touch-icon tag derived from
     * the Site Icon attachment (transparent-source crop). We ship a purpose-
     * built, flattened apple-touch-icon instead (see head_tags()), so strip
     * core's version to avoid two conflicting apple-touch-icon links.
     *
     * @param string $meta_tags
     * @return string
     */
    public static function strip_core_apple_touch_icon($meta_tags)
    {
        return preg_replace('/<link rel="apple-touch-icon"[^>]*>\s*/', '', $meta_tags);
    }

    /**
     * Some crawlers/unfurlers/PWA installers request /favicon.ico and
     * /apple-touch-icon.png at the document root directly, ignoring <link>
     * tags entirely. Copy our assets there once (idempotent, cheap check).
     */
    public static function maybe_place_root_assets()
    {
        $pairs = array(
            OKARCANA_PLUGIN_DIR . 'assets/img/brand/favicon.ico'          => ABSPATH . 'favicon.ico',
            OKARCANA_PLUGIN_DIR . 'assets/img/brand/apple-touch-icon.png' => ABSPATH . 'apple-touch-icon.png',
        );

        foreach ($pairs as $source => $dest) {
            if (!file_exists($source)) {
                continue;
            }
            if (file_exists($dest) && filesize($dest) === filesize($source)) {
                continue;
            }
            // Best-effort; root may not be writable under some hosting setups.
            @copy($source, $dest);
        }
    }
}
