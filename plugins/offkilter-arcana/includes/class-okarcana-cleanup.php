<?php
/**
 * OKArcana_Cleanup — Public Surface Lockdown (Priority Zero).
 *
 * Defensive layer that prevents VidMov / ARMember admin-editor metadata
 * (Purchase Price, Expiration, Video/Audio Categories editor fields, Pay Per
 * View controls) from leaking onto public pages.
 *
 * The VidMov theme source is not part of this repository, so the underlying
 * templates cannot be corrected here. This class is the immediate mitigation:
 *   - A `the_content` filter strips meta/custom-field dumps embedded in content.
 *   - A `body_class` marker (`ok-frontend`) anchors CSS Section V suppression.
 *
 * The permanent fix is a child-theme template override on production — see
 * docs/platform-v2-product-review.md (Priority Zero remediation).
 */

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Cleanup
{
    public static function init()
    {
        add_filter('the_content', array(__CLASS__, 'strip_leaked_meta'), 5);
        add_filter('body_class', array(__CLASS__, 'mark_frontend'));
    }

    /**
     * Add a guaranteed frontend-only body class so CSS Section V can scope
     * suppression rules without affecting wp-admin (the stylesheet is only
     * enqueued on wp_enqueue_scripts, but the marker keeps intent explicit).
     *
     * @param array $classes
     * @return array
     */
    public static function mark_frontend($classes)
    {
        $classes[] = 'ok-frontend';
        return $classes;
    }

    /**
     * Remove the_meta()/custom-field dumps if they appear *inside* post content.
     *
     * Most leaks are template-rendered (outside the_content) and handled by CSS
     * Section V, but any meta list embedded in content is removed here. Scoped to
     * the frontend and to common leaked-markup signatures to stay conservative.
     *
     * @param string $content
     * @return string
     */
    public static function strip_leaked_meta($content)
    {
        if (is_admin() || !is_string($content) || $content === '') {
            return $content;
        }

        // the_meta() output: <ul class="post-meta">...</ul>
        $content = preg_replace(
            '#<ul[^>]*class=["\'][^"\']*post-meta[^"\']*["\'][^>]*>.*?</ul>#is',
            '',
            $content
        );

        return $content;
    }
}
