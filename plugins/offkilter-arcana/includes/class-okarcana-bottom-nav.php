<?php
/**
 * OKArcana_Bottom_Nav — primary product navigation (native-app style).
 *
 * A fixed bottom tab bar rendered on the public front-end at mobile + tablet
 * widths (desktop keeps the header). Six destinations, each with a pillar
 * icon + accent + active state. Server-rendered via wp_footer (no flash).
 *
 * Additive and fully disable-able: `add_filter('okarcana_bottom_nav_enabled','__return_false')`.
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Bottom_Nav
{
    public static function init()
    {
        add_action('wp_footer', array(__CLASS__, 'render'), 20);
    }

    /**
     * The six tabs. Filterable so the operator can re-order/replace.
     * Each: key, label, href, accent (token name), icon (FA class) or svg=true.
     *
     * @return array[]
     */
    public static function items()
    {
        $profile = self::profile_href();

        $items = array(
            array('key' => 'home',      'label' => __('Home', 'offkilter-arcana'),      'href' => home_url('/'),          'accent' => 'accent',    'icon' => 'fas fa-house'),
            array('key' => 'pulse',     'label' => __('Pulse', 'offkilter-arcana'),     'href' => home_url('/pulse/'),    'accent' => 'pulse',     'icon' => 'fas fa-wave-square'),
            array('key' => 'discover',  'label' => __('Discover', 'offkilter-arcana'),  'href' => home_url('/discover/'), 'accent' => 'signal',    'icon' => 'fas fa-compass'),
            array('key' => 'arcana',    'label' => __('Arcana', 'offkilter-arcana'),    'href' => home_url('/arcana/'),   'accent' => 'arcana',    'svg' => true),
            array('key' => 'community', 'label' => __('Community', 'offkilter-arcana'), 'href' => home_url('/community/'),'accent' => 'community', 'icon' => 'fas fa-comments'),
            array('key' => 'profile',   'label' => $profile['label'],                    'href' => $profile['href'],       'accent' => 'creators',  'icon' => 'fas fa-user'),
        );

        return apply_filters('okarcana_bottom_nav_items', $items);
    }

    /**
     * Profile tab target: the logged-in creator's channel; login/join otherwise.
     *
     * @return array{href:string,label:string,is_auth:bool}
     */
    private static function profile_href()
    {
        if (is_user_logged_in()) {
            $url = get_author_posts_url(get_current_user_id());
            return array('href' => $url ?: home_url('/'), 'label' => __('Profile', 'offkilter-arcana'), 'is_auth' => true);
        }
        $login = wp_login_url();
        return array('href' => apply_filters('okarcana_bottom_nav_login_url', $login), 'label' => __('Sign In', 'offkilter-arcana'), 'is_auth' => false);
    }

    /**
     * Which tab is active for the current request. Path-prefix match, with the
     * home tab as the exact-root fallback.
     *
     * @return string tab key
     */
    public static function active_key()
    {
        $path = '/' . trim(parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH), '/') . '/';
        $path = str_replace('//', '/', $path);

        $map = array(
            'pulse'     => array('/pulse/'),
            'discover'  => array('/discover/'),
            'arcana'    => array('/arcana/', '/video-category/arcana/'),
            'community' => array('/community/'),
        );
        foreach ($map as $key => $prefixes) {
            foreach ($prefixes as $p) {
                if (strpos($path, $p) === 0) {
                    return $key;
                }
            }
        }
        if ($path === '/' || is_front_page() || is_home()) {
            return 'home';
        }
        return '';
    }

    public static function render()
    {
        if (is_admin()) {
            return;
        }
        if (!apply_filters('okarcana_bottom_nav_enabled', true)) {
            return;
        }

        $items  = self::items();
        $active = self::active_key();
        ?>
        <nav class="ok-bottom-nav" role="navigation" aria-label="<?php esc_attr_e('Primary', 'offkilter-arcana'); ?>">
            <ul class="ok-bottom-nav__list">
                <?php foreach ($items as $item) :
                    $is_active = ($item['key'] === $active);
                    $cls = 'ok-bottom-nav__item ok-bottom-nav__item--' . sanitize_html_class($item['accent']);
                    if ($is_active) {
                        $cls .= ' is-active';
                    }
                ?>
                    <li class="<?php echo esc_attr($cls); ?>">
                        <a href="<?php echo esc_url($item['href']); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                            <span class="ok-bottom-nav__icon" aria-hidden="true"><?php
                                if (!empty($item['svg']) && class_exists('OKArcana_Discovery')) {
                                    echo OKArcana_Discovery::crystal_ball_svg();
                                } else {
                                    echo '<i class="' . esc_attr($item['icon']) . '"></i>';
                                }
                            ?></span>
                            <span class="ok-bottom-nav__label"><?php echo esc_html($item['label']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php
    }
}
