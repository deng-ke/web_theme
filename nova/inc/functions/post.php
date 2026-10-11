<?php
/*
 * Project       : Nova主题
 * Description   : 文章详情页函数（正文/TOC/版权/上下篇/相关文章）
 */

if (!defined('ABSPATH')) exit;

/**
 * 文章正文输出（处理付费锁定）
 */
function nova_post_content_html($post_id = 0)
{
    $post_id = $post_id ?: get_the_ID();
    $post = get_post($post_id);
    $content = apply_filters('the_content', $post->post_content);

    //付费文章锁定判断
    if (nova_post_is_locked($post_id)) {
        //截取免费预览部分（前200字）
        $preview = mb_substr(wp_strip_all_tags($post->post_content), 0, 200);
        $html = '<div class="nova-post-content">';
        $html .= '<div class="nova-pay-preview">' . esc_html($preview) . '…</div>';
        $html .= nova_post_pay_box($post_id);
        $html .= '</div>';
        return $html;
    }
    return '<div class="nova-post-content">' . $content . '</div>';
}

/**
 * 文章目录（TOC）
 */
function nova_post_toc_html($post_id = 0)
{
    if (!nova_get_setting('toc_s', '1')) return '';
    $post_id = $post_id ?: get_the_ID();
    $content = get_post_field('post_content', $post_id);
    if (!preg_match_all('/<h([1-4])[^>]*>(.*?)<\/h[1-4]>/is', $content, $matches, PREG_SET_ORDER)) {
        return '';
    }
    $items = array();
    $index = 0;
    foreach ($matches as $match) {
        $level = (int) $match[1];
        $text  = trim(wp_strip_all_tags($match[2]));
        if (!$text) continue;
        $index++;
        $id = 'nova-toc-' . $index;
        $items[] = array('level' => $level, 'text' => $text, 'id' => $id);
        //给原文标题添加锚点
        $content = preg_replace('/<h' . $level . '([^>]*)>' . preg_quote($match[2], '/') . '<\/h' . $level . '>/is', '<h' . $level . '$1 id="' . $id . '">' . $match[2] . '</h' . $level . '>', $content, 1);
    }
    //更新内容（带锚点）
    update_post_meta($post_id, 'nova_toc_content', $content);
    if (count($items) < 2) return '';

    $html = '<div class="nova-toc card"><div class="nova-toc-title"><i class="nova-icon-list"></i>' . esc_html__('目录', 'nova') . '</div><ul>';
    foreach ($items as $item) {
        $html .= '<li class="toc-level-' . $item['level'] . '"><a href="#' . esc_attr($item['id']) . '">' . esc_html($item['text']) . '</a></li>';
    }
    $html .= '</ul></div>';
    return $html;
}

/**
 * 文章正文（支持目录锚点）
 */
function nova_post_content_with_toc()
{
    $post_id = get_the_ID();
    $cached = get_post_meta($post_id, 'nova_toc_content', true);
    $content = $cached ? $cached : get_post_field('post_content', $post_id);
    if (nova_post_is_locked($post_id)) {
        $preview = mb_substr(wp_strip_all_tags($content), 0, 200);
        return '<div class="nova-post-content"><div class="nova-pay-preview">' . esc_html($preview) . '…</div>' . nova_post_pay_box($post_id) . '</div>';
    }
    return '<div class="nova-post-content">' . apply_filters('the_content', $content) . '</div>';
}

/**
 * 上一篇/下一篇导航
 */
function nova_post_nav_html()
{
    $prev = get_previous_post();
    $next = get_next_post();
    if (!$prev && !$next) return '';
    $html = '<div class="nova-post-nav">';
    if ($prev) {
        $html .= '<a class="nova-post-nav-item prev" href="' . esc_url(get_permalink($prev)) . '">';
        $html .= '<span class="nova-post-nav-label muted-2-color"><i class="nova-icon-arrow-left"></i>' . esc_html__('上一篇', 'nova') . '</span>';
        $html .= '<span class="nova-post-nav-title">' . esc_html($prev->post_title) . '</span>';
        $html .= '</a>';
    } else {
        $html .= '<span class="nova-post-nav-item prev disabled"><span class="nova-post-nav-label muted-2-color">' . esc_html__('已是第一篇', 'nova') . '</span></span>';
    }
    if ($next) {
        $html .= '<a class="nova-post-nav-item next" href="' . esc_url(get_permalink($next)) . '">';
        $html .= '<span class="nova-post-nav-label muted-2-color">' . esc_html__('下一篇', 'nova') . '<i class="nova-icon-arrow-right"></i></span>';
        $html .= '<span class="nova-post-nav-title">' . esc_html($next->post_title) . '</span>';
        $html .= '</a>';
    } else {
        $html .= '<span class="nova-post-nav-item next disabled"><span class="nova-post-nav-label muted-2-color">' . esc_html__('已是最后一篇', 'nova') . '</span></span>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 相关文章
 */
function nova_related_posts_html($post_id = 0, $num = 6)
{
    if (!nova_get_setting('related_s', '1')) return '';
    $post_id = $post_id ?: get_the_ID();
    $cats = wp_get_post_categories($post_id);
    $tags = wp_get_post_tags($post_id);
    if (empty($cats) && empty($tags)) return '';

    $query = new WP_Query(array(
        'post_type'           => 'post',
        'posts_per_page'      => $num,
        'post__not_in'        => array($post_id),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'tax_query'           => array(
            'relation' => 'OR',
            array('taxonomy' => 'category', 'terms' => $cats, 'field' => 'term_id'),
            array('taxonomy' => 'post_tag', 'terms' => wp_list_pluck($tags, 'term_id'), 'field' => 'term_id'),
        ),
        'orderby'             => 'rand',
    ));
    if (!$query->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    $html = '<div class="nova-related-posts card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('相关推荐', 'nova') . '</span></h3>';
    $html .= '<div class="nova-related-grid">';
    while ($query->have_posts()) {
        $query->the_post();
        $thumb = nova_post_thumbnail_url(null, 'nova-card');
        $html .= '<a class="nova-related-item" href="' . esc_url(get_permalink()) . '">';
        if ($thumb) {
            $html .= '<div class="nova-related-thumb"><img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title()) . '"></div>';
        }
        $html .= '<span class="nova-related-title">' . esc_html(get_the_title()) . '</span>';
        $html .= '<span class="nova-related-meta muted-2-color"><i class="nova-icon-view"></i>' . nova_count_format(nova_get_post_views()) . '</span>';
        $html .= '</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    wp_reset_postdata();
    return $html;
}

/**
 * 文章版权信息
 */
function nova_post_copyright_html()
{
    if (!nova_get_setting('copyright_s', '1')) return '';
    $author = get_the_author();
    $url = home_url();
    $post_url = get_permalink();
    $html = '<div class="nova-post-copyright card">';
    $html .= '<div class="nova-copyright-title"><i class="nova-icon-copy"></i>' . esc_html__('版权声明', 'nova') . '</div>';
    $html .= '<p>' . sprintf(esc_html__('本文由 %s 原创发布，版权归 %s 所有。', 'nova'), esc_html($author), esc_html(get_bloginfo('name'))) . '</p>';
    $html .= '<p>' . esc_html__('本文链接：', 'nova') . '<a href="' . esc_url($post_url) . '">' . esc_html($post_url) . '</a></p>';
    $html .= '<p>' . sprintf(esc_html__('转载请注明出处：%s', 'nova'), esc_html(get_bloginfo('name'))) . '</p>';
    $html .= '</div>';
    return $html;
}

/**
 * 文章标签
 */
function nova_post_tags_html()
{
    $tags = get_the_tags();
    if (empty($tags)) return '';
    $html = '<div class="nova-post-tags">';
    foreach ($tags as $tag) {
        $html .= '<a href="' . esc_url(get_tag_link($tag->term_id)) . '"><i class="nova-icon-tag"></i>' . esc_html($tag->name) . '</a>';
    }
    $html .= '</div>';
    return $html;
}
