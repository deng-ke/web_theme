<?php
/*
 * Project       : Nova主题
 * Description   : 小工具数据函数（热门/最新/随机/评论/标签等）
 */

if (!defined('ABSPATH')) exit;

/**
 * 通用小工具文章列表项
 */
function nova_widget_post_item($post, $show_meta = 'views')
{
    $thumb = nova_post_thumbnail_url($post, 'nova-thumb');
    $html = '<div class="nova-widget-post-item">';
    if ($thumb) {
        $html .= '<a class="nova-widget-post-thumb" href="' . esc_url(get_permalink($post)) . '">';
        $html .= '<img src="' . esc_url($thumb) . '" alt="' . esc_attr($post->post_title) . '">';
        $html .= '</a>';
    }
    $html .= '<div class="nova-widget-post-body">';
    $html .= '<a class="nova-widget-post-title" href="' . esc_url(get_permalink($post)) . '">' . esc_html($post->post_title) . '</a>';
    if ($show_meta) {
        $html .= '<div class="nova-widget-post-meta muted-2-color">';
        if ('views' === $show_meta) {
            $html .= '<i class="nova-icon-view"></i>' . nova_count_format(nova_get_post_views($post->ID));
        } elseif ('date' === $show_meta) {
            $html .= nova_time_ago($post->post_date);
        } elseif ('comment' === $show_meta) {
            $html .= '<i class="nova-icon-comment"></i>' . get_comments_number($post);
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取热门文章（按浏览量）
 */
function nova_widget_get_hot_posts($num = 5, $days = 0)
{
    $args = array(
        'post_type'           => 'post',
        'posts_per_page'      => $num,
        'ignore_sticky_posts' => true,
        'meta_key'            => 'nova_views',
        'orderby'             => 'meta_value_num',
        'order'               => 'DESC',
        'no_found_rows'       => true,
    );
    if ($days > 0) {
        $args['date_query'] = array(array('after' => $days . ' days ago'));
    }
    $query = new WP_Query($args);
    $posts = $query->posts;
    wp_reset_postdata();
    return $posts;
}

/**
 * 热门文章列表 HTML
 */
function nova_widget_hot_posts_html($num = 5, $days = 0)
{
    $posts = nova_widget_get_hot_posts($num, $days);
    if (empty($posts)) return '';
    $html = '<div class="nova-widget-post-list">';
    foreach ($posts as $post) {
        $html .= nova_widget_post_item($post, 'views');
    }
    $html .= '</div>';
    return $html;
}

/**
 * 最新文章列表 HTML
 */
function nova_widget_latest_posts_html($num = 5)
{
    $query = new WP_Query(array(
        'post_type'           => 'post',
        'posts_per_page'      => $num,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));
    $posts = $query->posts;
    wp_reset_postdata();
    if (empty($posts)) return '';
    $html = '<div class="nova-widget-post-list">';
    foreach ($posts as $post) {
        $html .= nova_widget_post_item($post, 'date');
    }
    $html .= '</div>';
    return $html;
}

/**
 * 随机文章列表 HTML
 */
function nova_widget_random_posts_html($num = 5)
{
    $query = new WP_Query(array(
        'post_type'           => 'post',
        'posts_per_page'      => $num,
        'orderby'             => 'rand',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));
    $posts = $query->posts;
    wp_reset_postdata();
    if (empty($posts)) return '';
    $html = '<div class="nova-widget-post-list">';
    foreach ($posts as $post) {
        $html .= nova_widget_post_item($post, 'date');
    }
    $html .= '</div>';
    return $html;
}

/**
 * 最近评论列表 HTML
 */
function nova_widget_comments_html($num = 5)
{
    $comments = get_comments(array(
        'number'      => $num,
        'status'      => 'approve',
        'type'        => 'comment',
        'post_type'   => 'post',
        'no_found_rows' => true,
    ));
    if (empty($comments)) return '';
    $html = '<div class="nova-widget-comment-list">';
    foreach ($comments as $comment) {
        $html .= '<div class="nova-widget-comment-item">';
        $html .= '<a class="nova-avatar-sm" href="' . esc_url(get_comment_link($comment)) . '">' . get_avatar($comment, 80) . '</a>';
        $html .= '<div class="nova-widget-comment-body">';
        $html .= '<div class="nova-widget-comment-head"><a class="nova-widget-comment-author" href="' . esc_url(get_comment_link($comment)) . '">' . esc_html($comment->comment_author) . '</a><span class="muted-2-color">' . esc_html(nova_time_ago($comment->comment_date)) . '</span></div>';
        $html .= '<div class="nova-widget-comment-content muted-color">' . esc_html(mb_substr(wp_strip_all_tags($comment->comment_content), 0, 40)) . '</div>';
        $html .= '<div class="nova-widget-comment-post muted-2-color"><i class="nova-icon-comment"></i>' . esc_html(get_the_title($comment->comment_post_ID)) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 标签云 HTML
 */
function nova_widget_tags_html($num = 20)
{
    $tags = get_terms(array(
        'taxonomy' => 'post_tag',
        'number'   => $num,
        'orderby'  => 'count',
        'order'    => 'DESC',
        'hide_empty' => true,
    ));
    if (empty($tags) || is_wp_error($tags)) return '';
    $html = '<div class="nova-tag-cloud">';
    foreach ($tags as $tag) {
        $html .= '<a href="' . esc_url(get_term_link($tag)) . '">' . esc_html($tag->name) . '<em>' . $tag->count . '</em></a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 分类列表 HTML
 */
function nova_widget_cats_html($num = 10)
{
    $cats = get_terms(array(
        'taxonomy' => 'category',
        'number'   => $num,
        'orderby'  => 'count',
        'order'    => 'DESC',
        'hide_empty' => true,
    ));
    if (empty($cats) || is_wp_error($cats)) return '';
    $html = '<div class="nova-cat-list">';
    foreach ($cats as $cat) {
        $html .= '<a class="nova-cat-item" href="' . esc_url(get_term_link($cat)) . '">';
        $html .= '<span class="nova-cat-name">' . esc_html($cat->name) . '</span>';
        $html .= '<em class="muted-2-color">' . $cat->count . '</em>';
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 关于本站小工具
 */
function nova_widget_about_html()
{
    $logo = nova_get_setting('logo_url', NOVA_TEMPLATE_DIRECTORY_URI . '/img/logo.png');
    $desc = nova_get_setting('site_desc', get_bloginfo('description'));
    $html = '<div class="nova-widget-about">';
    $html .= '<img class="nova-widget-about-logo" src="' . esc_url($logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
    if ($desc) {
        $html .= '<p class="nova-widget-about-desc muted-color">' . esc_html($desc) . '</p>';
    }
    $html .= '<div class="nova-widget-about-stats">';
    $count_users = count_users();
    $html .= '<div><b>' . $count_users['total_users'] . '</b><span>' . esc_html__('会员', 'nova') . '</span></div>';
    $html .= '<div><b>' . nova_count_format(wp_count_posts()->publish) . '</b><span>' . esc_html__('文章', 'nova') . '</span></div>';
    $html .= '<div><b>' . nova_count_format(wp_count_comments()->approved) . '</b><span>' . esc_html__('评论', 'nova') . '</span></div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 登录用户信息卡（用户中心侧栏用）
 */
function nova_widget_user_card_html($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return '';
    $user = get_userdata($user_id);
    $html = '<div class="nova-widget-user-card">';
    $html .= '<div class="nova-widget-user-head">';
    $html .= '<a href="' . esc_url(get_author_posts_url($user_id)) . '"><img class="nova-widget-user-avatar" src="' . esc_url(nova_get_user_avatar($user_id, 120)) . '" alt="avatar"></a>';
    $html .= '<div class="nova-widget-user-info">';
    $html .= '<div class="nova-widget-user-name"><a href="' . esc_url(get_author_posts_url($user_id)) . '">' . esc_html(nova_get_user_name($user_id)) . '</a>';
    if (nova_is_vip($user_id)) {
        $html .= '<span class="nova-vip-badge"><i class="nova-icon-vip"></i>VIP</span>';
    }
    $html .= '</div>';
    if (isset($user->user_email)) {
        $html .= '<div class="muted-2-color">' . esc_html($user->user_email) . '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="nova-widget-user-stats">';
    $html .= '<a href="' . esc_url(nova_user_center_url('posts')) . '"><b>' . count_user_posts($user_id) . '</b><span>' . esc_html__('文章', 'nova') . '</span></a>';
    $html .= '<a href="' . esc_url(nova_user_center_url('fans')) . '"><b>' . nova_count_format(count((array) get_user_meta($user_id, 'nova_fans', true))) . '</b><span>' . esc_html__('粉丝', 'nova') . '</span></a>';
    $html .= '<a href="' . esc_url(nova_user_center_url('follows')) . '"><b>' . nova_count_format(count((array) get_user_meta($user_id, 'nova_follows', true))) . '</b><span>' . esc_html__('关注', 'nova') . '</span></a>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
