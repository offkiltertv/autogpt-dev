<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_Disclaimer
{
    public static function init()
    {
        add_filter('the_content', array(__CLASS__, 'append_disclaimer'));
    }

    public static function append_disclaimer($content)
    {
        if (!is_singular(OKArcana_Post_Types::POST_TYPE) || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $text = self::get_disclaimer_text();
        if ($text === '') {
            return $content;
        }

        $html = '<section class="okarcana-disclaimer" style="margin-top:2rem;padding:1rem;border:1px solid #444;border-radius:8px;">';
        $html .= '<h3 style="margin-top:0;">FOR ENTERTAINMENT PURPOSES ONLY</h3>';
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '' || stripos($line, 'for entertainment purposes only') === 0) {
                continue;
            }
            $html .= '<p>' . esc_html($line) . '</p>';
        }
        $html .= '</section>';

        return $content . $html;
    }

    private static function get_disclaimer_text()
    {
        $paths = array(
            defined('OKARCANA_DISCLAIMER_PATH') ? OKARCANA_DISCLAIMER_PATH : '',
            OKARCANA_PLUGIN_DIR . 'arcana_disclaimer.md',
            dirname(OKARCANA_PLUGIN_DIR) . '/documentation/arcana_disclaimer.md',
            ABSPATH . 'documentation/arcana_disclaimer.md',
        );

        foreach ($paths as $path) {
            if ($path && file_exists($path) && is_readable($path)) {
                $raw = file_get_contents($path);
                if ($raw !== false && trim($raw) !== '') {
                    return trim(preg_replace('/^#.*$/m', '', $raw));
                }
            }
        }

        return "FOR ENTERTAINMENT PURPOSES ONLY\n"
            . "The Arcana is a curated collection of media, symbolism, poetry, tarot-inspired interpretation, and community discussion.\n"
            . "No content on OffKilter.TV should be interpreted as financial, medical, legal, psychological, or professional advice.";
    }
}
