<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2026-08-26 22:16:30
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */
function zib_get_page_header($post_id = '')
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $header_style = zib_get_page_header_style($post_id);
    if (!$header_style) {
        return;
    }

    $title = get_the_title($post_id);
    $html  = '';
    if ($header_style == 1) {
        $html = '<div class="box-body notop"><h3 class="title-h-center text-center mt10">' . $title . '</h3></div>';
    } elseif ($header_style == 2) {
        $html = '<div class="zib-widget"><div><h3 class="title-h-center text-center">' . $title . '</h3></div></div>';
    } elseif ($header_style == 3) {
        $img               = '';
        $post_thumbnail_id = get_post_thumbnail_id($post_id);
        if ($post_thumbnail_id) {
            $image = wp_get_attachment_image_src($post_thumbnail_id, 'full');
            $img   = !empty($image[0]) ? $image[0] : '';
        }
        if (!$img) {
            $img = zib_get_post_meta($post_id, 'thumbnail_url', true);
        }

        $src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
        $img = $img ? $img : _pz('page_header_cover_img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg');

        $html = '<div class="page-cover theme-box radius8 main-shadow">
        <img ' . (zib_is_lazy('lazy_cover', true) ? 'class="fit-cover no-scale lazyload" src="' . $src . '" data-src="' . $img . '"' : 'class="fit-cover no-scale"  src="' . $img . '"') . '>
        <div class="absolute page-mask"></div>
            <div class="list-inline box-body abs-center text-center">
                <div class="title-h-center">
                    <h3>' . $title . '</h3>
                </div>
            </div>
        </div>';
    }
    return $html;
}

function zib_get_page_header_style($post_id = '')
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $header_style = zib_get_post_meta($post_id, 'page_header_style', true);
    if ($header_style == 'not') {
        return false;
    }
    if (!$header_style) {
        $header_style = _pz('page_header_style');
    }

    return $header_style;
}

function zib_get_page_content_style($post_id = '')
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }

    $style = zib_get_post_meta($post_id, 'page_content_style', true);
    return $style;
}

/**
 * 文章归档页每页条数
 *
 * @return int
 */
function zib_get_archives_posts_per_page()
{
    return (int) apply_filters('zib_archives_posts_per_page', 100);
}

/**
 * 获取文章归档列表（按月分组，AJAX 分页）
 *
 * @param int $paged 页码
 * @return string
 */
function zib_get_archives_posts_lists($paged = 0)
{
    $paged       = $paged ? (int) $paged : zib_get_the_paged();
    $ice_perpage = zib_get_archives_posts_per_page();
    $counts      = wp_count_posts('post');
    $count_all   = isset($counts->publish) ? (int) $counts->publish : 0;

    $query = new WP_Query(array(
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => $ice_perpage,
        'paged'                  => $paged,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => true,
        'update_post_term_cache' => false,
    ));

    if (!$query->have_posts()) {
        wp_reset_postdata();
        if (1 === $paged) {
            return zib_get_ajax_null();
        }
        return '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    $previous_year  = 0;
    $previous_month = 0;
    if ($paged > 1) {
        $prev_posts = get_posts(array(
            'numberposts'         => 1,
            'offset'              => ($paged - 1) * $ice_perpage - 1,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'post_status'         => 'publish',
            'post_type'           => 'post',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ));
        if (!empty($prev_posts[0])) {
            $previous_year  = (int) mysql2date('Y', $prev_posts[0]->post_date);
            $previous_month = (int) mysql2date('n', $prev_posts[0]->post_date);
        }
    }

    $html    = '';
    $group   = '';
    $ul_open = false;

    while ($query->have_posts()) {
        $query->the_post();
        global $post;

        $year  = (int) mysql2date('Y', $post->post_date);
        $month = (int) mysql2date('n', $post->post_date);

        if ($year !== $previous_year || $month !== $previous_month) {
            if ($ul_open) {
                $html .= '<div class="ajax-item zib-widget">' . $group . '</ul></div>';
                $group   = '';
                $ul_open = false;
            }

            $group .= '<h4 class="text-center title-h-center">' . get_the_time('Y年M') . '</h4>';
            $group .= '<ul class="list-inline">';
            $ul_open = true;
        } elseif (!$ul_open) {
            $group .= '<ul class="list-inline">';
            $ul_open = true;
        }

        $previous_year  = $year;
        $previous_month = $month;

        $comment_html = '';
        if ((int) $post->comment_count) {
            $comment_html = '<span class="muted-2-color ml6">' . zib_get_svg('comment') . (int) $post->comment_count . '</span>';
        }

        $like      = get_post_meta($post->ID, 'like', true);
        $like_html = $like ? '<span class="muted-2-color ml6">' . zib_get_svg('like') . $like . '</span>' : '';

        $group .= '<li class="author-set-left muted-color"><time>' . get_the_time('j') . '日</time></li>';
        $group .= '<li class="author-set-right"><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . ' </a>';
        $group .= '<span class="muted-2-color ml6">' . zib_get_svg('view') . get_post_view_count('', '') . '</span>';
        $group .= $comment_html . $like_html . '</li>';
    }

    if ($ul_open) {
        $html .= '<div class="ajax-item zib-widget">' . $group . '</ul></div>';
    }

    wp_reset_postdata();

    $html .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, zib_get_admin_ajax_url('archives_posts_lists'));

    return $html;
}