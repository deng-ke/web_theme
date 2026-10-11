<?php
/*
 * Project       : Nova主题
 * Description   : 首页功能
 */

if (!defined('ABSPATH')) exit;

/**
 * 首页 Tab 切换导航（最新/推荐/热门）
 */
function nova_index_tab_html()
{
    $tabs = array(
        'new'  => __('最新发布', 'nova'),
        'hot'  => __('热门文章', 'nova'),
        'view' => __('浏览最多', 'nova'),
    );
    $html = '<div class="nova-index-tabs">';
    $html .= '<ul class="nova-tab-nav" role="tablist">';
    $i = 0;
    foreach ($tabs as $key => $name) {
        $html .= '<li class="' . ($i === 0 ? 'active' : '') . '"><a href="#tab-' . $key . '" data-tab="' . $key . '" class="nova-tab-link">' . esc_html($name) . '</a></li>';
        $i++;
    }
    $html .= '</ul>';
    $html .= '</div>';
    return $html;
}

/**
 * 首页列表查询参数
 */
function nova_index_query_args($tab = 'new')
{
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'posts_per_page'      => (int) nova_get_setting('posts_per_page', 12),
        'paged'               => nova_get_the_paged(),
    );
    switch ($tab) {
        case 'hot':
            $args['orderby']  = 'comment_count';
            $args['order']    = 'DESC';
            break;
        case 'view':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'nova_views';
            $args['order']    = 'DESC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
    }
    return $args;
}

/**
 * 首页顶部轮播图
 */
function nova_home_slider()
{
    $slides = nova_get_setting('home_slides');
    if (empty($slides)) return '';
    $html = '<div class="nova-slider swiper" id="nova-home-slider">';
    $html .= '<div class="swiper-wrapper">';
    foreach ((array) $slides as $slide) {
        if (empty($slide['image'])) continue;
        $html .= '<div class="swiper-slide">';
        $html .= '<a href="' . esc_url($slide['url']) . '"><img src="' . esc_url($slide['image']) . '" alt="' . esc_attr($slide['title']) . '"></a>';
        if (!empty($slide['title'])) {
            $html .= '<div class="nova-slider-caption">' . esc_html($slide['title']) . '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '<div class="swiper-pagination"></div>';
    $html .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
    $html .= '</div>';
    return $html;
}

/**
 * 首页公告
 */
function nova_home_notice()
{
    $notice = nova_get_setting('home_notice');
    if (!$notice) return '';
    return '<div class="nova-notice card"><i class="nova-icon-notice"></i><div class="nova-notice-text">' . wp_kses_post($notice) . '</div></div>';
}
