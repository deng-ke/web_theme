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

/**
 * 归档页统计卡片（文章数/评论数/标签数/最后更新）
 */
function nova_archives_stats_html()
{
    $counts   = wp_count_posts('post');
    $post_n   = isset($counts->publish) ? (int) $counts->publish : 0;
    $comment_n = (int) get_comments(array('count' => true));
    $tags     = wp_count_terms('post_tag');
    $tag_n    = is_wp_error($tags) ? 0 : (int) $tags;
    $last     = get_posts(array('numberposts' => 1, 'orderby' => 'date', 'order' => 'DESC', 'post_status' => 'publish'));
    $last_time = !empty($last) ? get_the_time('Y-m-d', $last[0]) : '-';

    $items = array(
        array('icon' => 'nova-icon-post', 'num' => nova_count_format($post_n), 'label' => esc_html__('文章总数', 'nova')),
        array('icon' => 'nova-icon-comment', 'num' => nova_count_format($comment_n), 'label' => esc_html__('评论总数', 'nova')),
        array('icon' => 'nova-icon-tag', 'num' => nova_count_format($tag_n), 'label' => esc_html__('标签总数', 'nova')),
        array('icon' => 'nova-icon-time', 'num' => esc_html($last_time), 'label' => esc_html__('最后更新', 'nova')),
    );

    $html = '<div class="nova-archives-stats">';
    foreach ($items as $it) {
        $html .= '<div class="nova-archives-stat card"><div class="nova-stat-icon"><i class="' . esc_attr($it['icon']) . '"></i></div>';
        $html .= '<div class="nova-stat-info"><span class="nova-stat-num">' . $it['num'] . '</span><span class="nova-stat-label muted-2-color">' . $it['label'] . '</span></div></div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 归档页时间线（按月分组列出全部文章）
 */
function nova_archives_timeline_html()
{
    $query = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'update_post_term_cache' => false,
    ));

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return '<div class="card nova-archives-empty muted-2-color">' . esc_html__('暂无文章', 'nova') . '</div>';
    }

    // 按月分组收集
    $groups = array();
    while ($query->have_posts()) {
        $query->the_post();
        $pid   = get_the_ID();
        $key   = get_the_time('Y') . '-' . get_the_time('n');
        if (!isset($groups[$key])) {
            $groups[$key] = array('title' => get_the_time('Y') . esc_html__('年', 'nova') . get_the_time('n') . esc_html__('月', 'nova'), 'items' => array());
        }
        $groups[$key]['items'][] = $pid;
    }
    wp_reset_postdata();

    $html = '<div class="nova-archives-timeline">';
    foreach ($groups as $group) {
        $html .= '<div class="nova-archives-group card">';
        $html .= '<h3 class="nova-archives-month"><i class="nova-icon-folder"></i>' . esc_html($group['title']) . '<span class="nova-archives-count muted-2-color">' . esc_html__('共', 'nova') . ' ' . count($group['items']) . ' ' . esc_html__('篇', 'nova') . '</span></h3>';
        $html .= '<div class="nova-archives-list">';
        foreach ($group['items'] as $pid) {
            $cats_html = '';
            $cats = get_the_category($pid);
            if (!empty($cats)) {
                $cats_html = '<a class="nova-archive-cat" href="' . esc_url(get_category_link($cats[0])) . '">' . esc_html($cats[0]->name) . '</a>';
            }
            $html .= '<div class="nova-archive-item">';
            $html .= '<span class="nova-archive-day">' . get_the_time('d', $pid) . '</span>';
            $html .= '<div class="nova-archive-info">';
            $html .= '<a class="nova-archive-title" href="' . esc_url(get_permalink($pid)) . '">' . get_the_title($pid) . '</a>';
            $html .= '<span class="nova-archive-meta muted-2-color">' . $cats_html;
            $html .= '<span class="nova-archive-views"><i class="nova-icon-view"></i>' . nova_count_format(nova_get_post_views($pid)) . '</span>';
            $html .= '<span class="nova-archive-comments"><i class="nova-icon-comment"></i>' . (int) get_comments_number($pid) . '</span>';
            $html .= '</span></div></div>';
        }
        $html .= '</div></div>';
    }
    $html .= '</div>';

    return $html;
}
