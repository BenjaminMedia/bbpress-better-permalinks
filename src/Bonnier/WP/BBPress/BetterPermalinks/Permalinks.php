<?php

namespace Bonnier\WP\BBPress\BetterPermalinks;

/**
 * Class Permalinks
 *
 * @package \Bonnier\WP\BBPress\BetterPermalinks
 */
class Permalinks
{
    public static function bootstrap()
    {
        if (!did_action('bbp_after_setup_actions')) {
            add_action('bbp_after_setup_actions', [__CLASS__, 'after_bbPress_setup']);
        } else {
            static::after_bbPress_setup();
        }
    }

    /**
     * After bbPress Setup.
     *
     * @author Nashwan Doaqan
     */
    public static function after_bbPress_setup()
    {
        if (function_exists('is_bbpress')) {
            add_action('registered_post_type', [__CLASS__, 'add_post_types_rewrite'], 1, 2);
            add_filter('post_type_link', [__CLASS__, 'filter_post_type_link'], 1, 2);
        }
    }

    /**
     * Add the forum parents slug structure to bbPress topics
     *
     *
     * @param $postType
     * @param $args
     */
    public static function add_post_types_rewrite($postType, $args)
    {
        switch ($postType) {
            case bbp_get_topic_post_type():
                add_rewrite_rule(
                    '^'.bbp_get_forum_slug().'(.+?)'.bbp_get_topic_slug().'/(.+?)/?$',
                    'index.php?forumnames=$matches[1]&name=$matches[2]&post_type='.$postType,
                    'top'
                );
                add_permastruct($postType, bbp_get_forum_slug()."%forumnames%".get_option( '_bbp_topic_slug', 'topic' )."/%postname%/", $args->rewrite);
                static::flush_rewrite_rules_if_needed();
                break;
        }
    }

    /**
     * Change bbPress topic links to prefix forum slugs
     *
     * @author Nashwan Doaqan
     *
     * @param $post_link
     * @param $_post
     *
     * @return mixed|string|void
     */
    public static function filter_post_type_link($post_link, $_post)
    {
        global $wp_rewrite;
        if (empty($_post) || $_post->post_status === 'auto-draft' || strpos('post_type', $post_link)) {
            return $post_link;
        }
        switch ($_post->post_type) {
            case bbp_get_topic_post_type():
                $post_link = $wp_rewrite->get_extra_permastruct($_post->post_type);
                $post_link = str_replace("%forumnames%", static::get_topic_parent_forums_slug($_post->ID), $post_link);
                $post_link = str_replace("%postname%", static::get_topic_name_slug($_post), $post_link);
                $post_link = home_url(user_trailingslashit($post_link));
                break;
        }
        return $post_link;
    }

    public static function get_topic_parent_forums_slug($topicId)
    {
        $forumId = bbp_get_topic_forum_id($topicId);
        $forumSlugs = [];
        if ($forumId === 0) { // means the topic belongs to no forum
            return '/no-forum/';
        }
        $forum = get_post($forumId);
        $hasParent = true;
        while ($hasParent) {
            $forumSlugs[] = $forum->post_name;
            if ($forum->post_parent === 0) {
                $hasParent = false;
            } else {
                $forum = get_post($forum->post_parent);
            }
        }
        return '/' . implode('/', array_reverse($forumSlugs)) . '/';
    }

    public static function get_topic_name_slug($post)
    {
        return empty($post->post_name) ? sanitize_title_with_dashes($post->post_title) : $post->post_name;
    }

    private static function flush_rewrite_rules_if_needed() {
        if ( get_option( Plugin::FLUSH_REWRITE_RULES_FLAG ) ) {
            flush_rewrite_rules();
            delete_option( Plugin::FLUSH_REWRITE_RULES_FLAG );
        }
    }
}
