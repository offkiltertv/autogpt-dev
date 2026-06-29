<?php
if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Destinations
{
    public static function init()
    {
        add_shortcode('oktv_destination_hero', array(__CLASS__, 'render_destination_hero'));
        add_shortcode('oktv_pillar_strip', array(__CLASS__, 'render_pillar_strip'));
    }

    /**
     * [oktv_destination_hero]
     * Renders a full-width hero block for pillar destination pages.
     * Designed to be embedded via Elementor HTML widget at the top
     * of /signals/, /arcana/, /member-list/, and /community/ pages.
     *
     * Attributes:
     *   pillar     signals|arcana|creators|community|watch  (default: watch)
     *   headline   string override
     *   sub        string override
     *   cta_label  string override
     *   cta_href   URL override
     */
    public static function render_destination_hero($atts)
    {
        $atts = shortcode_atts(array(
            'pillar'    => 'watch',
            'headline'  => '',
            'sub'       => '',
            'cta_label' => '',
            'cta_href'  => '',
        ), $atts, 'oktv_destination_hero');

        $pillar = sanitize_key($atts['pillar']);

        $defaults = array(
            'pulse'     => array(
                'icon'      => 'fas fa-wave-square',
                'headline'  => 'OFFKILTER Pulse.',
                'sub'       => 'Fast observations. Breaking developments. Quick reactions.',
                'cta_label' => 'Open Pulse',
                'cta_href'  => '/pulse/',
            ),
            'signals'   => array(
                'icon'      => 'fas fa-bolt',
                'headline'  => 'Every signal deserves attention.',
                'sub'       => 'Short clips, fast observations. Under 90 seconds.',
                'cta_label' => 'Browse Signals',
                'cta_href'  => '/video-category/signals/',
            ),
            'arcana'    => array(
                'icon'      => 'fas fa-gem',
                'headline'  => 'The intuitive layer of OFFKILTER.',
                'sub'       => 'Readings, premonitions, and outcomes from Arcana creators.',
                'cta_label' => 'Explore Arcana',
                'cta_href'  => '/video-category/arcana/',
            ),
            'creators'  => array(
                'icon'      => 'fas fa-user-group',
                'headline'  => 'The people building OFFKILTER.',
                'sub'       => 'Independent creators. Real communities.',
                'cta_label' => 'Find Creators',
                'cta_href'  => '/member-list/',
            ),
            'community' => array(
                'icon'      => 'fas fa-comments',
                'headline'  => 'The conversation starts here.',
                'sub'       => 'Discuss Signals, Arcana, and everything in between.',
                'cta_label' => 'Join the Community',
                'cta_href'  => '/community/',
            ),
            'watch'     => array(
                'icon'      => 'fas fa-play-circle',
                'headline'  => 'Watch OFFKILTER.',
                'sub'       => 'Arcana readings, Signals clips, creator content.',
                'cta_label' => 'Start Watching',
                'cta_href'  => '/',
            ),
        );

        $config = isset($defaults[$pillar]) ? $defaults[$pillar] : $defaults['watch'];

        $headline  = $atts['headline']  ? esc_html($atts['headline'])  : esc_html($config['headline']);
        $sub       = $atts['sub']       ? esc_html($atts['sub'])       : esc_html($config['sub']);
        $cta_label = $atts['cta_label'] ? esc_html($atts['cta_label']) : esc_html($config['cta_label']);
        $cta_href  = $atts['cta_href']  ? esc_url($atts['cta_href'])   : esc_url($config['cta_href']);
        $icon_cls  = esc_attr($config['icon']);

        $section_class = 'ok-destination-hero ok-destination-hero--' . esc_attr($pillar);

        return sprintf(
            '<section class="%s" role="region" aria-label="%s">
  <span class="ok-destination-hero__icon" aria-hidden="true"><i class="%s"></i></span>
  <h2 class="ok-destination-hero__headline">%s</h2>
  <p class="ok-destination-hero__sub">%s</p>
  <a href="%s" class="ok-destination-hero__cta">%s</a>
</section>',
            $section_class,
            esc_attr($headline),
            $icon_cls,
            $headline,
            $sub,
            $cta_href,
            $cta_label
        );
    }

    /**
     * [oktv_pillar_strip]
     * Renders a horizontal row of 4 platform navigation chips.
     * Designed for embedding at the top of destination pages or
     * on the homepage above content rails.
     *
     * Attributes:
     *   active  signals|arcana|creators|community  (marks active chip)
     */
    public static function render_pillar_strip($atts)
    {
        $atts = shortcode_atts(array(
            'active' => '',
        ), $atts, 'oktv_pillar_strip');

        $active = sanitize_key($atts['active']);

        $pillars = array(
            'pulse'     => array('label' => 'Pulse',     'href' => '/pulse/',                  'icon' => 'fas fa-wave-square'),
            'signals'   => array('label' => 'Signals',   'href' => '/video-category/signals/', 'icon' => 'fas fa-bolt'),
            'arcana'    => array('label' => 'Arcana',    'href' => '/video-category/arcana/',  'icon' => 'fas fa-gem'),
            'creators'  => array('label' => 'Creators',  'href' => '/member-list/',            'icon' => 'fas fa-user-group'),
            'community' => array('label' => 'Community', 'href' => '/community/',              'icon' => 'fas fa-comments'),
        );

        $chips = array();
        foreach ($pillars as $key => $pillar) {
            $is_active   = ($active === $key);
            $chip_class  = 'ok-pillar-chip ok-pillar-chip--' . esc_attr($key);
            $aria_current = '';

            if ($is_active) {
                $chip_class  .= ' ok-pillar-chip--active';
                $aria_current = ' aria-current="page"';
            }

            $chips[] = sprintf(
                '<a href="%s" class="%s"%s><i class="%s" aria-hidden="true"></i>%s</a>',
                esc_url($pillar['href']),
                esc_attr($chip_class),
                $aria_current,
                esc_attr($pillar['icon']),
                esc_html($pillar['label'])
            );
        }

        return sprintf(
            '<nav class="ok-pillar-strip" aria-label="Platform sections">%s</nav>',
            implode('', $chips)
        );
    }
}
