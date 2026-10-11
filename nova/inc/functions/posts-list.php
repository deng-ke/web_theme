<?php
/*
 * Project       : Nova主题
 * Description   : 文章列表卡片
 */

if (!defined('ABSPATH')) exit;

/**
 * 输出文章列表
 * @param array $args WP_Query 参数
 * @param string $style 列表样式 card|text
 */
function nova_posts_list($args = array(), $style = '')
{
    if (empty($args)) {
        global $wp_query;
        $args = $wp_query->query_vars;
    }
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        echo nova_empty_box(__('暂无内容', 'nova'), __('这里还什么都没有，去别处看看吧', 'nova'));
        return;
    }

    echo '<div class="nova-posts-list">';
    while ($query->have_posts()) {
        $query->the_post();
        echo nova_post_card($style);
    }
    echo '</div>';
    wp_reset_postdata();
}

/**
 * 单个文章卡片
 */
function nova_post_card($style = '')
{
    $post_id = get_the_ID();
    $thumb   = nova_post_thumbnail_url($post_id, 'nova-card');
    $cat     = nova_get_post_cat($post_id);
    $lazy    = nova_is_lazy();

    $meta = nova_post_card_meta();
    $title = get_the_title();

    $html = '<article class="nova-post-card card ' . esc_attr($style) . '">';

    //缩略图
    if ($thumb) {
        $html .= '<div class="nova-post-thumb">';
        $html .= '<a href="' . esc_url(get_permalink()) . '" title="' . esc_attr($title) . '">';
        if ($lazy) {
            $html .= '<img class="nova-lazy" src="' . esc_url(nova_placeholder_img()) . '" data-src="' . esc_url($thumb) . '" alt="' . esc_attr($title) . '">';
        } else {
            $html .= '<img src="' . esc_url($thumb) . '" alt="' . esc_attr($title) . '">';
        }
        $html .= '</a>';
        if ($cat) {
            $color = nova_get_term_color($cat->term_id);
            $html .= '<a class="nova-post-cat' . ($color ? ' cat-color' : '') . '" style="' . ($color ? 'background:' . esc_attr($color) : '') . '" href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a>';
        }
        $html .= '</div>';
    }

    $html .= '<div class="nova-post-body">';
    //标题
    $html .= '<h2 class="nova-post-title"><a href="' . esc_url(get_permalink()) . '" title="' . esc_attr($title) . '">' . esc_html($title) . '</a></h2>';
    //摘要
    $excerpt = nova_excerpt();
    if ($excerpt) {
        $html .= '<div class="nova-post-excerpt muted-2-color">' . esc_html($excerpt) . '</div>';
    }
    //meta
    $html .= '<div class="nova-post-meta muted-2-color">' . $meta . '</div>';
    $html .= '</div>';

    $html .= '</article>';
    return $html;
}

/**
 * 文章卡片 meta 行
 */
function nova_post_card_meta()
{
    $html = '<span class="nova-meta-author"><a class="nova-meta-avatar" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '"><img src="' . esc_url(nova_get_user_avatar(get_the_author_meta('ID'), 40)) . '" alt="avatar"></a><a class="nova-meta-name" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>';
    $html .= '<span class="nova-meta-time"><i class="nova-icon-time"></i>' . esc_html(nova_time_ago(get_post_time('Y-m-d H:i:s'))) . '</span>';
    if (nova_get_setting('post_views_s', '1')) {
        $html .= '<span class="nova-meta-view"><i class="nova-icon-view"></i>' . nova_count_format(nova_get_post_views()) . '</span>';
    }
    $html .= '<span class="nova-meta-comment"><i class="nova-icon-comment"></i>' . nova_count_format(get_comments_number()) . '</span>';
    $tags = get_the_tags();
    if ($tags) {
        $html .= '<span class="nova-meta-tags">';
        $i = 0;
        foreach ($tags as $tag) {
            if ($i > 2) break;
            $html .= '<a href="' . esc_url(get_tag_link($tag)) . '"># ' . esc_html($tag->name) . '</a>';
            $i++;
        }
        $html .= '</span>';
    }
    return $html;
}

/**
 * 置顶文章卡片（首页推荐区）
 */
function nova_sticky_posts()
{
    $stickies = get_option('sticky_posts');
    if (empty($stickies)) return '';
    $args = array(
        'post__in'            => $stickies,
        'ignore_sticky_posts' => 1,
        'posts_per_page'      => 4,
        'orderby'             => 'date',
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) return '';

    $html = '<div class="nova-sticky-posts">';
    while ($query->have_posts()) {
        $query->the_post();
        $thumb = nova_post_thumbnail_url(null, 'nova-card');
        $html .= '<div class="nova-sticky-card card">';
        if ($thumb) {
            $html .= '<a class="nova-sticky-thumb" href="' . esc_url(get_permalink()) . '"><img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title()) . '"></a>';
        }
        $html .= '<div class="nova-sticky-body">';
        $html .= '<span class="nova-sticky-tag">' . esc_html__('置顶', 'nova') . '</span>';
        $html .= '<h3 class="nova-post-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    wp_reset_postdata();
    return $html;
}

/**
 * 空状态盒子
 */
function nova_empty_box($title = '', $desc = '', $icon = 'null')
{
    $html = '<div class="nova-empty-box card">';
    $html .= '<div class="nova-empty-icon">' . nova_svg_icon($icon) . '</div>';
    $html .= '<h3>' . esc_html($title) . '</h3>';
    if ($desc) {
        $html .= '<p class="muted-2-color">' . esc_html($desc) . '</p>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 分页
 */
function nova_paging($query = null)
{
    if (!$query) {
        global $wp_query;
        $query = $wp_query;
    }
    $big = 999999999;
    $paged = max(1, nova_get_the_paged());
    $total = (int) $query->max_num_pages;

    if ($total < 2) return;

    $html = '<div class="nova-paging">';

    //上一页
    if ($paged > 1) {
        $html .= '<a class="nova-page-prev" href="' . esc_url(get_pagenum_link($paged - 1)) . '"><i class="nova-icon-left"></i></a>';
    }

    //页码
    $start = max(1, $paged - 2);
    $end   = min($total, $paged + 2);
    if ($start > 1) {
        $html .= '<a href="' . esc_url(get_pagenum_link(1)) . '">1</a>';
        if ($start > 2) $html .= '<span class="nova-page-dots">…</span>';
    }
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $paged) {
            $html .= '<span class="current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . esc_url(get_pagenum_link($i)) . '">' . $i . '</a>';
        }
    }
    if ($end < $total) {
        if ($end < $total - 1) $html .= '<span class="nova-page-dots">…</span>';
        $html .= '<a href="' . esc_url(get_pagenum_link($total)) . '">' . $total . '</a>';
    }

    //下一页
    if ($paged < $total) {
        $html .= '<a class="nova-page-next" href="' . esc_url(get_pagenum_link($paged + 1)) . '"><i class="nova-icon-right"></i></a>';
    }

    $html .= '</div>';
    echo $html;
}

/**
 * AJAX 翻页加载更多按钮
 */
function nova_ajax_loadmore()
{
    global $wp_query;
    $paged = nova_get_the_paged();
    if ($paged >= $wp_query->max_num_pages) return;
    echo '<div class="nova-loadmore-wrap"><button class="btn btn-outline nova-ajax-loadmore" data-paged="' . $paged . '" data-max="' . $wp_query->max_num_pages . '">' . esc_html__('加载更多', 'nova') . ' <i class="nova-icon-down"></i></button></div>';
}
