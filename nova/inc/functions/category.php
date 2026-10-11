<?php
/*
 * Project       : Nova主题
 * Description   : 分类/标签归档功能
 */

if (!defined('ABSPATH')) exit;

/**
 * 获取归档页头部信息
 */
function nova_archive_header()
{
    $term = get_queried_object();
    if (empty($term) || is_wp_error($term)) return '';

    $desc = term_description($term->term_id, $term->taxonomy);
    $count = is_category() ? nova_get_term_posts_count($term) : $term->count;

    $html = '<div class="nova-archive-header card">';
    $html .= '<div class="nova-archive-cover" style="background-image:url(' . esc_url(nova_get_archive_bg()) . ')">';
    $html .= '<div class="nova-archive-mask"></div>';
    $html .= '<div class="nova-archive-title-wrap">';
    $html .= '<h1 class="nova-archive-title">' . single_term_title('', false) . '</h1>';
    $html .= '<div class="nova-archive-meta">' . sprintf(__('共 %s 篇文章', 'nova'), $count) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    if ($desc) {
        $html .= '<div class="nova-archive-desc">' . wp_kses_post($desc) . '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 获取分类文章总数（含子分类）
 */
function nova_get_term_posts_count($term)
{
    global $wpdb;
    $count = 0;
    $terms = array_merge(array($term->term_id), get_term_children($term->term_id, 'category'));
    if (!empty($terms)) {
        $ids = implode(',', array_map('absint', $terms));
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ($ids)");
    }
    return $count;
}

/**
 * 分类子分类导航
 */
function nova_category_children_nav()
{
    if (!is_category()) return '';
    $term = get_queried_object();
    $children = get_term_children($term->term_id, 'category');
    if (empty($children)) {
        //无子分类时显示兄弟分类
        if ($term->parent) {
            $children = get_term_children($term->parent, 'category');
            if (!empty($children) && !in_array($term->term_id, $children)) {
                array_unshift($children, $term->parent);
            }
        }
    }
    if (empty($children)) return '';
    $html = '<div class="nova-cat-nav card">';
    $html .= '<a href="' . esc_url(get_category_link($term->term_id)) . '" class="active">' . esc_html($term->name) . '</a>';
    foreach ($children as $cid) {
        if ($cid == $term->term_id) continue;
        $child = get_term($cid);
        if ($child && !is_wp_error($child)) {
            $html .= '<a href="' . esc_url(get_term_link($child)) . '">' . esc_html($child->name) . '</a>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * 获取分类图标
 */
function nova_get_term_icon($term_id, $taxonomy = 'category')
{
    $icon = get_term_meta($term_id, 'nova_term_icon', true);
    if ($icon) return $icon;
    //默认图标
    return '<i class="nova-icon-folder"></i>';
}

/**
 * 获取分类颜色
 */
function nova_get_term_color($term_id, $taxonomy = 'category')
{
    return get_term_meta($term_id, 'nova_term_color', true);
}
