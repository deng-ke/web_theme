<?php
/*
 * Project       : Nova主题
 * Description   : 作者页面功能
 */

if (!defined('ABSPATH')) exit;

/**
 * 作者页头部信息卡
 */
function nova_author_page_header()
{
    $user_id = get_queried_object_id();
    $user = get_userdata($user_id);
    if (!$user) return '';

    $html = '<div class="nova-author-page-header card">';
    $html .= '<div class="nova-author-cover" style="background-image:url(' . esc_url(nova_get_setting('author_bg_img', NOVA_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg')) . ')"></div>';
    $html .= '<div class="nova-author-page-info">';
    $html .= '<div class="nova-avatar-lg"><img src="' . esc_url(nova_get_user_avatar($user_id, 140)) . '" alt="' . esc_attr($user->display_name) . '">' . nova_get_user_badge($user_id) . '</div>';
    $html .= '<div class="nova-author-page-meta">';
    $html .= '<h1 class="nova-author-name">' . esc_html(nova_get_user_name($user_id)) . '</h1>';
    $html .= '<div class="nova-author-desc muted">' . esc_html(nova_get_user_sign($user_id)) . '</div>';
    $html .= '<div class="nova-author-stats">';
    $html .= '<span>' . esc_html__('文章', 'nova') . ' <b>' . count_user_posts($user_id) . '</b></span>';
    $html .= '<span>' . esc_html__('粉丝', 'nova') . ' <b>' . nova_get_user_count($user_id, 'fans') . '</b></span>';
    $html .= '<span>' . esc_html__('关注', 'nova') . ' <b>' . nova_get_user_count($user_id, 'follows') . '</b></span>';
    $html .= '<span>' . esc_html__('评论', 'nova') . ' <b>' . nova_get_user_comment_count($user_id) . '</b></span>';
    $html .= '</div>';
    $html .= '</div>';
    if (get_current_user_id() && get_current_user_id() != $user_id) {
        $following = nova_is_following($user_id);
        $html .= '<div class="nova-author-page-actions">';
        $html .= '<button class="btn btn-primary nova-follow-btn' . ($following ? ' followed' : '') . '" data-user="' . $user_id . '">' . ($following ? esc_html__('已关注', 'nova') : esc_html__('+ 关注', 'nova')) . '</button>';
        $html .= '<button class="btn btn-outline nova-msg-btn" data-user="' . $user_id . '">' . esc_html__('私信', 'nova') . '</button>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取用户评论数
 */
function nova_get_user_comment_count($user_id)
{
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = %d AND comment_approved = '1'", $user_id));
}

/**
 * 作者页文章Tab
 */
function nova_author_tab_html()
{
    $tabs = array(
        'posts'    => __('文章', 'nova'),
        'comments' => __('评论', 'nova'),
        'follows'  => __('关注', 'nova'),
        'fans'     => __('粉丝', 'nova'),
    );
    $user_id = get_queried_object_id();
    $html = '<div class="nova-author-tabs card">';
    $html .= '<ul class="nova-tab-nav">';
    foreach ($tabs as $key => $name) {
        $html .= '<li><a href="' . esc_url(add_query_arg(array('tab' => $key), get_author_posts_url($user_id))) . '" class="nova-tab-link' . (nova_author_current_tab() === $key ? ' active' : '') . '">' . esc_html($name) . '</a></li>';
    }
    $html .= '</ul>';
    $html .= '</div>';
    return $html;
}

/**
 * 当前作者页 Tab
 */
function nova_author_current_tab()
{
    $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'posts';
    $allowed = array('posts', 'comments', 'follows', 'fans');
    return in_array($tab, $allowed) ? $tab : 'posts';
}
