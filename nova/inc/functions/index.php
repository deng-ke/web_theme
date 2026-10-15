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
        $html .= '<li><a href="#tab-' . $key . '" data-tab="' . $key . '" class="nova-tab-link' . ($i === 0 ? ' nova-active' : '') . '">' . esc_html($name) . '</a></li>';
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
 * 首页顶部轮播图（Zibll 风格大图轮播）
 * 数据来源：后台手动配置 JSON，留空则自动取置顶文章/最新文章特色图
 */
function nova_home_slider()
{
    if (!nova_get_setting('home_slider_s', '1')) return '';
    $slides = nova_home_slider_items();
    if (empty($slides)) return '';

    $html = '<div class="nova-slider" id="nova-home-slider" data-interval="4000">';
    $html .= '<div class="nova-slider-track">';
    $i = 0;
    foreach ($slides as $slide) {
        if (empty($slide['image'])) continue;
        $active = $i === 0 ? ' nova-active' : '';
        $html .= '<a class="nova-slide' . $active . '" href="' . esc_url($slide['url']) . '"' . (!empty($slide['blank']) ? ' target="_blank" rel="noopener"' : '') . '>';
        $html .= '<img class="nova-slider-img" src="' . esc_url($slide['image']) . '" alt="' . esc_attr($slide['title']) . '">';
        if (!empty($slide['title'])) {
            $html .= '<div class="nova-slider-caption">';
            $html .= '<div class="nova-slider-title">' . esc_html($slide['title']) . '</div>';
            if (!empty($slide['desc'])) {
                $html .= '<div class="nova-slider-desc">' . esc_html($slide['desc']) . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</a>';
        $i++;
    }
    $html .= '</div>';
    $html .= '<button class="nova-slider-arrow nova-slider-prev" type="button" aria-label="' . esc_attr__('上一张', 'nova') . '"><i class="nova-icon-left"></i></button>';
    $html .= '<button class="nova-slider-arrow nova-slider-next" type="button" aria-label="' . esc_attr__('下一张', 'nova') . '"><i class="nova-icon-right"></i></button>';
    $html .= '<div class="nova-slider-dots"></div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取轮播数据：手动配置优先，否则取置顶文章/最新文章
 */
function nova_home_slider_items()
{
    //手动配置
    $json = trim((string) nova_get_setting('home_slides'));
    if ($json && '[' === $json[0]) {
        $items = json_decode($json, true);
        if (is_array($items)) {
            $slides = array();
            foreach ($items as $item) {
                if (empty($item['img']) && empty($item['image'])) continue;
                $slides[] = array(
                    'image' => !empty($item['img']) ? $item['img'] : $item['image'],
                    'url'   => !empty($item['url']) ? $item['url'] : '',
                    'title' => !empty($item['title']) ? $item['title'] : '',
                    'desc'  => !empty($item['desc']) ? $item['desc'] : '',
                    'blank' => !empty($item['blank']),
                );
            }
            if ($slides) return $slides;
        }
    }

    //自动模式：置顶文章优先，不足补最新文章
    $count = (int) nova_get_setting('home_slider_count', 5);
    $count = max(1, min(8, $count));
    $ids = get_option('sticky_posts');
    $ids = is_array($ids) ? array_filter($ids) : array();
    $slides = array();

    if ($ids) {
        $query = new WP_Query(array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $count,
            'post__in'            => array_slice($ids, 0, $count),
            'orderby'             => 'post__in',
            'ignore_sticky_posts' => true,
        ));
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $thumb = nova_post_thumbnail_url(null, 'large');
                if (!$thumb) continue;
                $slides[] = array(
                    'image' => $thumb,
                    'url'   => get_permalink(),
                    'title' => get_the_title(),
                    'desc'  => wp_strip_all_tags(nova_excerpt(40)),
                    'blank' => false,
                );
            }
            wp_reset_postdata();
        }
    }

    if (count($slides) < $count) {
        $fill = new WP_Query(array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $count - count($slides),
            'post__not_in'        => $ids,
            'ignore_sticky_posts' => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ));
        if ($fill->have_posts()) {
            while ($fill->have_posts()) {
                $fill->the_post();
                $thumb = nova_post_thumbnail_url(null, 'large');
                if (!$thumb) continue;
                $slides[] = array(
                    'image' => $thumb,
                    'url'   => get_permalink(),
                    'title' => get_the_title(),
                    'desc'  => wp_strip_all_tags(nova_excerpt(40)),
                    'blank' => false,
                );
            }
            wp_reset_postdata();
        }
    }

    return $slides;
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
