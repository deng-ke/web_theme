<?php
/*
 * Project       : Nova主题
 * Description   : 论坛模块（板块/帖子/发布/审核）
 */

if (!defined('ABSPATH')) exit;

/**
 * 注册论坛文章类型和板块分类
 */
function nova_bbs_init()
{
    //板块分类法
    register_taxonomy('forum_plate', 'forum_post', array(
        'labels' => array(
            'name'          => __('论坛板块', 'nova'),
            'singular_name' => __('论坛板块', 'nova'),
            'add_new_item'  => __('添加板块', 'nova'),
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'forum-plate'),
    ));

    //论坛帖子
    register_post_type('forum_post', array(
        'labels' => array(
            'name'          => __('论坛帖子', 'nova'),
            'singular_name' => __('论坛帖子', 'nova'),
            'add_new'       => __('发布帖子', 'nova'),
        ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-buddicons-forums',
        'supports'      => array('title', 'editor', 'comments', 'author', 'thumbnail'),
        'rewrite'       => array('slug' => 'forum-post'),
        'has_archive'   => false,
    ));

    //帖子状态：待审核
    register_post_status('pending_forum', array(
        'label'       => __('待审核', 'nova'),
        'public'      => true,
        'protected'   => true,
        'label_count' => _n_noop('待审核 <span class="count">(%s)</span>', '待审核 <span class="count">(%s)</span>', 'nova'),
    ));
}
add_action('init', 'nova_bbs_init');

/**
 * 获取论坛板块列表
 */
function nova_bbs_get_plates()
{
    $plates = get_terms(array(
        'taxonomy'   => 'forum_plate',
        'hide_empty' => false,
        'orderby'    => 'meta_value_num',
        'order'      => 'ASC',
        'meta_key'   => 'nova_plate_order',
    ));
    if (is_wp_error($plates)) return array();
    return $plates;
}

/**
 * 板块卡片
 */
function nova_bbs_plate_card($plate)
{
    $icon = get_term_meta($plate->term_id, 'nova_plate_icon', true);
    $color = get_term_meta($plate->term_id, 'nova_plate_color', true);
    $desc = term_description($plate->term_id, 'forum_plate');
    $post_count = $plate->count;

    //板块最新帖子
    $latest = nova_bbs_plate_latest_post($plate->term_id);

    $html = '<div class="nova-plate-card card">';
    $html .= '<div class="nova-plate-icon" style="' . ($color ? 'background:' . esc_attr($color) : '') . '">';
    $html .= $icon ? $icon : '<i class="nova-icon-forum"></i>';
    $html .= '</div>';
    $html .= '<div class="nova-plate-info">';
    $html .= '<a class="nova-plate-name" href="' . esc_url(get_term_link($plate)) . '">' . esc_html($plate->name) . '</a>';
    if ($desc) {
        $html .= '<div class="nova-plate-desc muted-2-color">' . esc_html(wp_strip_all_tags($desc)) . '</div>';
    }
    $html .= '<div class="nova-plate-meta muted-2-color">';
    $html .= '<span>' . esc_html__('帖子', 'nova') . ' ' . $post_count . '</span>';
    if ($latest) {
        $html .= '<span class="nova-plate-latest"><a href="' . esc_url(get_permalink($latest)) . '">' . esc_html__('最新：', 'nova') . esc_html($latest->post_title) . '</a></span>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取板块最新帖子
 */
function nova_bbs_plate_latest_post($plate_id)
{
    $query = new WP_Query(array(
        'post_type'      => 'forum_post',
        'posts_per_page' => 1,
        'tax_query'      => array(array(
            'taxonomy' => 'forum_plate',
            'terms'    => $plate_id,
        )),
    ));
    $post = $query->have_posts() ? $query->posts[0] : null;
    wp_reset_postdata();
    return $post;
}

/**
 * 板块页面头部
 */
function nova_bbs_plate_header($plate)
{
    $icon = get_term_meta($plate->term_id, 'nova_plate_icon', true);
    $color = get_term_meta($plate->term_id, 'nova_plate_color', true);
    $desc = term_description($plate->term_id, 'forum_plate');

    $html = '<div class="nova-plate-header card">';
    $html .= '<div class="nova-plate-icon-lg" style="' . ($color ? 'background:' . esc_attr($color) : '') . '">' . ($icon ? $icon : '<i class="nova-icon-forum"></i>') . '</div>';
    $html .= '<div class="nova-plate-header-info">';
    $html .= '<h1 class="nova-plate-title">' . esc_html($plate->name) . '</h1>';
    if ($desc) {
        $html .= '<div class="nova-plate-desc muted-2-color">' . esc_html(wp_strip_all_tags($desc)) . '</div>';
    }
    $html .= '<div class="nova-plate-meta muted-2-color">' . sprintf(__('共 %s 个帖子', 'nova'), $plate->count) . '</div>';
    $html .= '</div>';
    $html .= '<div class="nova-plate-header-action">';
    if (is_user_logged_in()) {
        $html .= '<a class="btn btn-primary" href="' . esc_url(nova_bbs_edit_url($plate->term_id)) . '"><i class="nova-icon-edit"></i>' . esc_html__('发布帖子', 'nova') . '</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 发帖页面地址
 */
function nova_bbs_edit_url($plate_id = 0, $post_id = 0)
{
    $url = nova_forum_url() . '/edit';
    $args = array();
    if ($plate_id) $args['plate'] = $plate_id;
    if ($post_id) $args['id'] = $post_id;
    if ($args) $url = add_query_arg($args, $url);
    return $url;
}

/**
 * 论坛帖子列表项
 */
function nova_bbs_post_item($post)
{
    $user_id = $post->post_author;
    $plate = wp_get_post_terms($post->ID, 'forum_plate');
    $plate = !empty($plate) ? $plate[0] : null;

    $html = '<div class="nova-bbs-post-item card">';
    $html .= '<a class="nova-avatar-sm" href="' . esc_url(get_author_posts_url($user_id)) . '"><img src="' . esc_url(nova_get_user_avatar($user_id, 80)) . '" alt="avatar"></a>';
    $html .= '<div class="nova-bbs-post-body">';
    $html .= '<div class="nova-bbs-post-title-row">';
    if (get_post_meta($post->ID, 'nova_bbs_top', true)) {
        $html .= '<span class="nova-bbs-tag top">' . esc_html__('置顶', 'nova') . '</span>';
    }
    if (get_post_meta($post->ID, 'nova_bbs_essence', true)) {
        $html .= '<span class="nova-bbs-tag essence">' . esc_html__('精华', 'nova') . '</span>';
    }
    $html .= '<h3 class="nova-bbs-post-title"><a href="' . esc_url(get_permalink($post)) . '">' . esc_html($post->post_title) . '</a></h3>';
    $html .= '</div>';
    $html .= '<div class="nova-bbs-post-meta muted-2-color">';
    $html .= '<span>' . esc_html(nova_get_user_name($user_id)) . '</span>';
    if ($plate) {
        $html .= '<span class="nova-bbs-plate-tag"><a href="' . esc_url(get_term_link($plate)) . '">' . esc_html($plate->name) . '</a></span>';
    }
    $html .= '<span><i class="nova-icon-time"></i>' . esc_html(nova_time_ago($post->post_date)) . '</span>';
    $html .= '<span><i class="nova-icon-view"></i>' . nova_get_post_views($post->ID) . '</span>';
    $html .= '<span><i class="nova-icon-comment"></i>' . get_comments_number($post) . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 论坛首页板块展示
 */
function nova_bbs_index_html()
{
    $html = '';
    //统计
    $count_posts = wp_count_posts('forum_post');
    $count_users = count_users();
    $html .= '<div class="nova-bbs-stats card">';
    $html .= '<div class="nova-bbs-stat"><b>' . (isset($count_posts->publish) ? $count_posts->publish : 0) . '</b><span>' . esc_html__('帖子总数', 'nova') . '</span></div>';
    $html .= '<div class="nova-bbs-stat"><b>' . $count_users['total_users'] . '</b><span>' . esc_html__('会员总数', 'nova') . '</span></div>';
    $html .= '<div class="nova-bbs-stat"><b>' . wp_count_comments()->approved . '</b><span>' . esc_html__('评论总数', 'nova') . '</span></div>';
    $html .= '</div>';

    //板块列表
    $plates = nova_bbs_get_plates();
    if (empty($plates)) {
        $html .= nova_empty_box(__('暂无板块', 'nova'), __('管理员还未创建论坛板块', 'nova'));
    } else {
        $html .= '<div class="nova-plate-grid">';
        foreach ($plates as $plate) {
            $html .= nova_bbs_plate_card($plate);
        }
        $html .= '</div>';
    }
    return $html;
}

/**
 * AJAX 发布论坛帖子
 */
function nova_ajax_bbs_publish()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('forum_s', '1')) nova_ajax_error('论坛功能已关闭');

    $post_id  = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $plate_id = isset($_POST['plate']) ? (int) $_POST['plate'] : 0;
    $title    = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $content  = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';

    if (mb_strlen($title, 'UTF-8') < 2) nova_ajax_error('标题至少2个字');
    if (mb_strlen(wp_strip_all_tags($content), 'UTF-8') < 5) nova_ajax_error('内容至少5个字');
    if (!$plate_id) nova_ajax_error('请选择板块');

    $plate = get_term($plate_id, 'forum_plate');
    if (!$plate || is_wp_error($plate)) nova_ajax_error('板块不存在');

    //编辑模式
    if ($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'forum_post') nova_ajax_error('帖子不存在');
        if (!current_user_can('edit_post', $post_id) && $post->post_author != $user_id) nova_ajax_error('无权限编辑');
        wp_update_post(array(
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_content' => $content,
        ));
        wp_set_post_terms($post_id, array($plate_id), 'forum_plate');
        nova_ajax_success(array('url' => get_permalink($post_id)));
    }

    //发布模式
    $need_review = nova_get_setting('forum_review_s', '0');
    $status = $need_review ? 'pending_forum' : 'publish';

    $new_id = wp_insert_post(array(
        'post_type'    => 'forum_post',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $status,
        'post_author'  => $user_id,
    ));
    if (is_wp_error($new_id)) nova_ajax_error('发布失败');
    wp_set_post_terms($new_id, array($plate_id), 'forum_plate');

    //发布奖励积分
    $post_points = (int) nova_get_setting('forum_post_points', 5);
    if ($post_points) {
        NovaDB::update_user_points($user_id, $post_points, 'add', __('发布论坛帖子', 'nova'));
    }

    $msg = $need_review ? '发布成功，等待管理员审核' : '发布成功';
    nova_ajax_success(array('url' => get_permalink($new_id)), $msg);
}
add_action('wp_ajax_nova_bbs_publish', 'nova_ajax_bbs_publish');

/**
 * AJAX 帖子置顶/精华（管理员）
 */
function nova_ajax_bbs_manage()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    if (!current_user_can('moderate_comments')) nova_ajax_error('无权限');

    $post_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $action  = isset($_POST['act']) ? sanitize_text_field(wp_unslash($_POST['act'])) : '';
    if (!$post_id) nova_ajax_error('参数错误');

    switch ($action) {
        case 'top':
            $key = 'nova_bbs_top';
            $current = get_post_meta($post_id, $key, true);
            update_post_meta($post_id, $key, $current ? 0 : 1);
            break;
        case 'essence':
            $key = 'nova_bbs_essence';
            $current = get_post_meta($post_id, $key, true);
            update_post_meta($post_id, $key, $current ? 0 : 1);
            break;
        case 'delete':
            if (current_user_can('delete_post', $post_id)) {
                wp_delete_post($post_id, true);
            }
            break;
        default:
            nova_ajax_error('未知操作');
    }
    nova_ajax_success();
}
add_action('wp_ajax_nova_bbs_manage', 'nova_ajax_bbs_manage');

/**
 * 论坛板块小工具展示（热门板块）
 */
function nova_bbs_hot_plates()
{
    $plates = get_terms(array(
        'taxonomy'   => 'forum_plate',
        'hide_empty' => true,
        'number'     => 6,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ));
    if (empty($plates) || is_wp_error($plates)) return '';
    $html = '<div class="nova-hot-plates">';
    foreach ($plates as $plate) {
        $color = get_term_meta($plate->term_id, 'nova_plate_color', true);
        $html .= '<a class="nova-hot-plate" href="' . esc_url(get_term_link($plate)) . '" style="' . ($color ? 'background:' . esc_attr($color) : '') . '">';
        $html .= '<span>' . esc_html($plate->name) . '</span>';
        $html .= '<em>' . $plate->count . '</em>';
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
}
