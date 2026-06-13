<?php

if (!defined('ABSPATH')) {
    exit;
}

class OKArcana_WPForo
{
    public static function init()
    {
        add_action('transition_post_status', array(__CLASS__, 'create_discussion_on_publish'), 20, 3);
    }

    public static function create_discussion_on_publish($new_status, $old_status, $post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        if ($post->post_type !== OKArcana_Post_Types::POST_TYPE || $new_status !== 'publish') {
            return;
        }

        $existing_topic_id = (int) get_post_meta($post->ID, '_arcana_wpforo_topic_id', true);
        if ($existing_topic_id > 0) {
            return;
        }

        $topic_title = 'Discuss: ' . $post->post_title;
        $topic_url = '';
        $topic_id = 0;

        if (function_exists('wpforo')) {
            try {
                $forum_id = (int) apply_filters('okarcana_wpforo_forum_id', 1, $post);

                if (isset(wpforo()->topic) && method_exists(wpforo()->topic, 'add')) {
                    $result = wpforo()->topic->add(array(
                        'forumid' => $forum_id,
                        'userid' => get_current_user_id() ? get_current_user_id() : 1,
                        'title' => $topic_title,
                        'body' => self::starter_post_body($post),
                        'created' => current_time('mysql', 1),
                    ));

                    if (is_array($result) && !empty($result['topicid'])) {
                        $topic_id = (int) $result['topicid'];
                    } elseif (is_numeric($result)) {
                        $topic_id = (int) $result;
                    }
                }

                if ($topic_id > 0 && isset(wpforo()->topic) && method_exists(wpforo()->topic, 'get_topic_url')) {
                    $topic_url = (string) wpforo()->topic->get_topic_url($topic_id);
                }
            } catch (Exception $e) {
                update_post_meta($post->ID, '_arcana_wpforo_error', $e->getMessage());
            }
        }

        if ($topic_id > 0) {
            update_post_meta($post->ID, '_arcana_wpforo_topic_id', $topic_id);
            update_post_meta($post->ID, '_arcana_wpforo_topic_url', esc_url_raw($topic_url));
        }
    }

    private static function starter_post_body($post)
    {
        $entry_url = get_permalink($post->ID);

        return sprintf(
            "Arcana Entry: %s\n\nDiscuss the symbolism, themes, and interpretation.\n\nSource: %s",
            esc_url_raw($entry_url),
            esc_url_raw((string) get_post_meta($post->ID, '_arcana_youtube_url', true))
        );
    }
}
