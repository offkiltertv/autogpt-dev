<?php
if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Editorial
{
    public static function init()
    {
        add_filter('body_class', array(__CLASS__, 'add_auth_body_class'));
        add_shortcode('oktv_discuss_cta', array(__CLASS__, 'render_discuss_cta'));
        add_shortcode('oktv_discuss_slot', array(__CLASS__, 'render_discuss_slot'));
        add_shortcode('oktv_content_explainer', array(__CLASS__, 'render_content_explainer'));
        add_shortcode('oktv_next_action', array(__CLASS__, 'render_next_action'));
    }

    /**
     * [oktv_discuss_slot]
     * One consistent integration point reserved for SidebarChat. Establishes the
     * three named hooks the platform will activate — Discuss, Creator Community, and
     * Live Discussion — as coming-soon slots today, so placement is uniform across
     * Pulse, creator pages, and content. No Stream implementation yet: flip
     * coming_soon to live (or wire the provider) when SidebarChat ships.
     *
     * Attributes:
     *   type   discuss | creator_community | live   (default: discuss)
     *   href   optional override target
     */
    public static function render_discuss_slot($atts)
    {
        $atts = shortcode_atts(array(
            'type' => 'discuss',
            'href' => '',
        ), $atts, 'oktv_discuss_slot');

        $map = array(
            'discuss'           => array('label' => 'Discuss',            'context' => 'default', 'href' => '/community/'),
            'creator_community' => array('label' => 'Creator Community',  'context' => 'creator', 'href' => '/community/'),
            'live'              => array('label' => 'Live Discussion',    'context' => 'signals', 'href' => '/community/'),
        );
        $type = isset($map[$atts['type']]) ? $atts['type'] : 'discuss';
        $cfg  = $map[$type];

        return self::render_discuss_cta(array(
            'context'     => $cfg['context'],
            'label'       => $cfg['label'],
            'href'        => $atts['href'] ? $atts['href'] : $cfg['href'],
            'coming_soon' => '1',
        ));
    }

    /**
     * Adds ok-auth-frame to login and register page bodies so Section G
     * CSS can scope the OFFKILTER wordmark context block cleanly.
     */
    public static function add_auth_body_class($classes)
    {
        $auth_page_ids = array(7695, 7697);
        if (is_page($auth_page_ids)) {
            $classes[] = 'ok-auth-frame';
        }
        return $classes;
    }

    /**
     * [oktv_discuss_cta]
     * Renders a "Discuss" CTA pill. Can be placed below any video, post,
     * or creator page. Use coming_soon="1" to reserve placement without
     * activating navigation yet.
     *
     * Attributes:
     *   context     signals|arcana|video|creator|default  (default: default)
     *   label       string                                 (default: "Discuss")
     *   href        URL string                             (default: /community/)
     *   coming_soon 0|1                                   (default: 0)
     */
    public static function render_discuss_cta($atts)
    {
        $atts = shortcode_atts(array(
            'context'     => 'default',
            'label'       => 'Discuss',
            'href'        => '/community/',
            'coming_soon' => '0',
        ), $atts, 'oktv_discuss_cta');

        $is_coming_soon = (bool) $atts['coming_soon'];
        $context        = sanitize_key($atts['context']);
        $label          = esc_html($atts['label']);
        $href           = esc_url($atts['href']);

        $modifier_map = array(
            'signals' => 'ok-discuss-cta--signals',
            'arcana'  => 'ok-discuss-cta--arcana',
            'video'   => 'ok-discuss-cta--signals',
            'creator' => 'ok-discuss-cta--arcana',
        );

        $class = 'ok-discuss-cta';
        if (isset($modifier_map[$context])) {
            $class .= ' ' . $modifier_map[$context];
        }

        if ($is_coming_soon) {
            $class .= ' ok-discuss-cta--coming-soon';
        }

        $icon_html = '<span class="ok-discuss-cta__icon" aria-hidden="true"><i class="fas fa-comments"></i></span>';

        if ($is_coming_soon) {
            $sub_html = '<span class="ok-discuss-cta__sub">Community opens soon</span>';
            return sprintf(
                '<span class="%s" role="button" aria-disabled="true" tabindex="-1">%s%s%s</span>',
                esc_attr($class),
                $icon_html,
                $label,
                $sub_html
            );
        }

        return sprintf(
            '<a href="%s" class="%s" role="button">%s%s</a>',
            $href,
            esc_attr($class),
            $icon_html,
            $label
        );
    }

    /**
     * [oktv_content_explainer]
     * Renders an editorial "What is this?" block. Pre-filled defaults exist
     * for each platform content type so operators can use [oktv_content_explainer
     * type="signals"] with zero additional configuration.
     *
     * Attributes:
     *   type          signals|arcana|community|creators|watch  (default: watch)
     *   headline      string override (optional)
     *   body          string override (optional)
     *   wrapper_class additional CSS classes for outer element
     */
    public static function render_content_explainer($atts)
    {
        $atts = shortcode_atts(array(
            'type'          => 'watch',
            'headline'      => '',
            'body'          => '',
            'wrapper_class' => '',
        ), $atts, 'oktv_content_explainer');

        $type = sanitize_key($atts['type']);

        $defaults = array(
            'signals'   => array(
                'icon'     => 'fas fa-bolt',
                'headline' => 'Fast clips. Under 90 seconds.',
                'body'     => 'Short observations from creators across OFFKILTER.',
                'modifier' => 'ok-content-explainer--signals',
            ),
            'arcana'    => array(
                'icon'     => 'fas fa-gem',
                'headline' => 'Readings. Premonitions. Outcomes.',
                'body'     => 'Curated interpretive content for entertainment. Trust your own intuition.',
                'modifier' => 'ok-content-explainer--arcana',
            ),
            'community' => array(
                'icon'     => 'fas fa-comments',
                'headline' => 'The conversation lives here.',
                'body'     => 'Join discussions around Signals, Arcana, and creators.',
                'modifier' => 'ok-content-explainer--community',
            ),
            'creators'  => array(
                'icon'     => 'fas fa-user-group',
                'headline' => 'The people behind OFFKILTER.',
                'body'     => 'Independent creators building something different.',
                'modifier' => 'ok-content-explainer--creators',
            ),
            'watch'     => array(
                'icon'     => 'fas fa-play-circle',
                'headline' => 'Watch OFFKILTER.',
                'body'     => 'Arcana readings, short signals, and creator content in one place.',
                'modifier' => '',
            ),
        );

        $config   = isset($defaults[$type]) ? $defaults[$type] : $defaults['watch'];
        $headline = $atts['headline'] ? esc_html($atts['headline']) : esc_html($config['headline']);
        $body_txt = $atts['body'] ? esc_html($atts['body']) : esc_html($config['body']);
        $icon_cls = esc_attr($config['icon']);
        $modifier = $config['modifier'];

        $outer_class = 'ok-content-explainer';
        if ($modifier) {
            $outer_class .= ' ' . $modifier;
        }
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        return sprintf(
            '<div class="%s" role="region" aria-label="%s">
  <span class="ok-content-explainer__icon" aria-hidden="true"><i class="%s"></i></span>
  <div class="ok-content-explainer__body">
    <p class="ok-content-explainer__headline">%s</p>
    <p class="ok-content-explainer__text">%s</p>
  </div>
</div>',
            esc_attr($outer_class),
            esc_attr($headline),
            $icon_cls,
            $headline,
            $body_txt
        );
    }

    /**
     * [oktv_next_action]
     * Renders a flex row of ghost-style CTA buttons. Each named action
     * resolves to a canonical OFFKILTER route. Operators can override
     * the resolved URL by providing a custom href via the actions list
     * (see format note below).
     *
     * Attributes:
     *   actions       comma-separated: watch,discuss,arcana,signals,creators
     *   wrapper_class additional CSS classes
     *
     * Action format: plain name "arcana" OR "arcana:/custom-url/" if
     * the canonical URL must be overridden.
     */
    public static function render_next_action($atts)
    {
        $atts = shortcode_atts(array(
            'actions'       => 'watch,discuss',
            'wrapper_class' => '',
        ), $atts, 'oktv_next_action');

        $route_map = array(
            'watch'    => array('label' => 'Watch',    'href' => '/',                                  'icon' => 'fas fa-play-circle'),
            'discuss'  => array('label' => 'Discuss',  'href' => '/community/',                        'icon' => 'fas fa-comments'),
            'arcana'   => array('label' => 'Arcana',   'href' => '/video-category/arcana/',            'icon' => 'fas fa-gem'),
            'signals'  => array('label' => 'Signals',  'href' => '/video-category/signals/',           'icon' => 'fas fa-bolt'),
            'creators' => array('label' => 'Creators', 'href' => '/member-list/',                      'icon' => 'fas fa-user-group'),
            'explore'  => array('label' => 'Explore',  'href' => '/trending/',                         'icon' => 'fas fa-compass'),
        );

        $raw_actions = array_map('trim', explode(',', $atts['actions']));
        $buttons     = array();

        foreach ($raw_actions as $raw) {
            if (!$raw) {
                continue;
            }

            // Support "key:/custom-url/" override syntax
            $parts = explode(':', $raw, 2);
            $key   = sanitize_key($parts[0]);
            $override_href = isset($parts[1]) ? esc_url($parts[1]) : null;

            if (!isset($route_map[$key])) {
                continue;
            }

            $action   = $route_map[$key];
            $href     = $override_href ? $override_href : esc_url($action['href']);
            $label    = esc_html($action['label']);
            $icon_cls = esc_attr($action['icon']);

            $buttons[] = sprintf(
                '<a href="%s" class="ok-btn ok-btn--ghost"><i class="%s" aria-hidden="true"></i>%s</a>',
                $href,
                $icon_cls,
                $label
            );
        }

        if (!$buttons) {
            return '';
        }

        $outer_class = 'ok-next-actions';
        if ($atts['wrapper_class']) {
            $outer_class .= ' ' . esc_attr($atts['wrapper_class']);
        }

        $label_html = '<span class="ok-next-actions__label sr-only">Next:</span>';

        return sprintf(
            '<nav class="%s" aria-label="Next actions">%s%s</nav>',
            esc_attr($outer_class),
            $label_html,
            implode('', $buttons)
        );
    }
}
