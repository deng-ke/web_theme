<?php
/*
 * Project       : Nova主题
 * Description   : 搜索功能
 */

if (!defined('ABSPATH')) exit;

/**
 * 搜索页头部
 */
function nova_search_header()
{
    $keyword = get_search_query();
    global $wp_query;
    $html = '<div class="nova-search-header card">';
    $html .= '<div class="nova-search-title"><i class="nova-icon-search"></i>' . esc_html__('搜索：', 'nova') . ' <b>' . esc_html($keyword) . '</b></div>';
    $html .= '<div class="nova-search-count muted-2-color">' . sprintf(__('共找到 %s 条结果', 'nova'), $wp_query->found_posts) . '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 搜索联想（AJAX）
 */
function nova_search_suggest()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
    if (mb_strlen($keyword, 'UTF-8') < 1) {
        wp_send_json_error('请输入关键词');
    }
    $args = array(
        's'                   => $keyword,
        'posts_per_page'      => 8,
        'ignore_sticky_posts' => true,
        'post_status'         => 'publish',
    );
    $query = new WP_Query($args);
    $list  = array();
    while ($query->have_posts()) {
        $query->the_post();
        $list[] = array(
            'id'    => get_the_ID(),
            'title' => get_the_title(),
            'url'   => get_permalink(),
        );
    }
    wp_reset_postdata();
    wp_send_json_success($list);
}
add_action('wp_ajax_nova_search_suggest', 'nova_search_suggest');
add_action('wp_ajax_nopriv_nova_search_suggest', 'nova_search_suggest');

/**
 * 搜索关键词高亮
 */
function nova_search_highlight($text)
{
    $keyword = get_search_query();
    if (!$keyword) return $text;
    $text = preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<mark class="nova-hl">$1</mark>', $text);
    return $text;
}
