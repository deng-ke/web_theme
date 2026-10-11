<?php
/*
 * Project       : Nova主题
 * Description   : 通用页面函数（页面头部/404/页面内容）
 */

if (!defined('ABSPATH')) exit;

/**
 * 通用页面头部（标题+描述+面包屑）
 */
function nova_page_header_html($title = '', $desc = '')
{
    $html = '<div class="nova-page-header card">';
    $html .= '<div class="nova-page-header-inner">';
    $html .= '<h1 class="nova-page-title">' . esc_html($title) . '</h1>';
    if ($desc) {
        $html .= '<div class="nova-page-desc muted-2-color">' . esc_html($desc) . '</div>';
    }
    $html .= '</div>';
    $html .= nova_breadcrumb_html();
    $html .= '</div>';
    return $html;
}

/**
 * 面包屑导航
 */
function nova_breadcrumb_html()
{
    if (is_front_page()) return '';
    $html = '<div class="nova-breadcrumb muted-2-color">';
    $html .= '<a href="' . esc_url(home_url()) . '"><i class="nova-icon-home"></i>' . esc_html__('首页', 'nova') . '</a>';
    if (is_singular('post')) {
        $cats = get_the_category();
        if (!empty($cats)) {
            $html .= '<i class="nova-icon-chevron-right"></i><a href="' . esc_url(get_category_link($cats[0])) . '">' . esc_html($cats[0]->name) . '</a>';
        }
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html(mb_substr(get_the_title(), 0, 20)) . '</span>';
    } elseif (is_singular('forum_post')) {
        $plates = get_the_terms(get_the_ID(), 'forum_plate');
        if (!empty($plates)) {
            $html .= '<i class="nova-icon-chevron-right"></i><a href="' . esc_url(get_term_link($plates[0])) . '">' . esc_html($plates[0]->name) . '</a>';
        }
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html(mb_substr(get_the_title(), 0, 20)) . '</span>';
    } elseif (is_singular('shop_product')) {
        $html .= '<i class="nova-icon-chevron-right"></i><a href="' . esc_url(nova_shop_url()) . '">' . esc_html__('商城', 'nova') . '</a>';
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html(mb_substr(get_the_title(), 0, 20)) . '</span>';
    } elseif (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html($term->name) . '</span>';
    } elseif (is_author()) {
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html(get_the_author()) . '</span>';
    } elseif (is_search()) {
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html__('搜索', 'nova') . '</span>';
    } elseif (is_page()) {
        $html .= '<i class="nova-icon-chevron-right"></i><span>' . esc_html(mb_substr(get_the_title(), 0, 20)) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 404页面内容
 */
function nova_404_html()
{
    $html = '<div class="nova-404-page">';
    $html .= '<div class="nova-404-box card">';
    $html .= '<img class="nova-404-img" src="' . esc_url(NOVA_TEMPLATE_DIRECTORY_URI . '/img/404.svg') . '" alt="404">';
    $html .= '<h2>' . esc_html__('页面走丢了…', 'nova') . '</h2>';
    $html .= '<p class="muted-2-color">' . esc_html__('你访问的页面不存在或已被删除', 'nova') . '</p>';
    $html .= '<div class="nova-404-actions">';
    $html .= '<a class="btn btn-primary" href="' . esc_url(home_url()) . '">' . esc_html__('返回首页', 'nova') . '</a>';
    $html .= '<a class="btn btn-outline" href="javascript:history.back();">' . esc_html__('返回上页', 'nova') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 页面内容输出（处理 [login] 等短代码）
 */
function nova_page_content_html()
{
    the_content();
}

/**
 * 页面内容（用户中心等虚拟页面用主循环）
 */
function nova_get_the_content()
{
    return apply_filters('the_content', get_the_content());
}
