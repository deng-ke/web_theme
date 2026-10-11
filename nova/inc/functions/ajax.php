<?php
/*
 * Project       : Nova主题
 * Description   : 通用 AJAX 接口
 */

if (!defined('ABSPATH')) exit;

/**
 * 统一 AJAX 响应格式
 */
function nova_ajax_success($data = array(), $msg = 'success')
{
    wp_send_json_success(array('msg' => $msg, 'data' => $data));
}

function nova_ajax_error($msg = 'error', $code = 400)
{
    wp_send_json_error(array('msg' => $msg), $code);
}

/**
 * 文章点赞/取消点赞
 */
function nova_ajax_like()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $post_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if (!$post_id) nova_ajax_error('参数错误');

    $liked = (array) get_user_meta($user_id, 'nova_liked_posts', true);
    $likes = (int) get_post_meta($post_id, 'nova_likes', true);

    if (in_array($post_id, $liked)) {
        $liked = array_diff($liked, array($post_id));
        $likes = max(0, $likes - 1);
        $is_like = false;
    } else {
        $liked[] = $post_id;
        $likes++;
        $is_like = true;
    }
    update_user_meta($user_id, 'nova_liked_posts', $liked);
    update_post_meta($post_id, 'nova_likes', $likes);
    nova_ajax_success(array('likes' => $likes, 'is_like' => $is_like));
}
add_action('wp_ajax_nova_like', 'nova_ajax_like');

/**
 * 关注/取消关注
 */
function nova_ajax_follow()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $target_id = isset($_POST['user']) ? (int) $_POST['user'] : 0;
    if (!$target_id || $target_id == $user_id) nova_ajax_error('参数错误');

    $follows = (array) get_user_meta($user_id, 'nova_follows', true);
    $fans    = (array) get_user_meta($target_id, 'nova_fans', true);

    if (in_array($target_id, $follows)) {
        $follows = array_diff($follows, array($target_id));
        $fans = array_diff($fans, array($user_id));
        $is_follow = false;
    } else {
        $follows[] = $target_id;
        $fans[] = $user_id;
        $is_follow = true;
    }
    update_user_meta($user_id, 'nova_follows', $follows);
    update_user_meta($target_id, 'nova_fans', $fans);

    if ($is_follow) {
        NovaMsg::add($target_id, $user_id, 'follow', __('关注了你', 'nova'));
    }
    nova_ajax_success(array('is_follow' => $is_follow));
}
add_action('wp_ajax_nova_follow', 'nova_ajax_follow');

/**
 * 发送私信
 */
function nova_ajax_send_message()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $to_user  = isset($_POST['to']) ? (int) $_POST['to'] : 0;
    $content  = isset($_POST['content']) ? sanitize_textarea_field(wp_unslash($_POST['content'])) : '';
    if (!$to_user || !$content) nova_ajax_error('参数错误');
    if (mb_strlen($content, 'UTF-8') > 1000) nova_ajax_error('内容过长');

    $result = NovaMsg::add_private($user_id, $to_user, $content);
    if (is_wp_error($result)) nova_ajax_error($result->get_error_message());
    nova_ajax_success();
}
add_action('wp_ajax_nova_send_message', 'nova_ajax_send_message');

/**
 * 消息已读
 */
function nova_ajax_read_message()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $msg_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($msg_id) {
        NovaMsg::set_read($msg_id, $user_id);
    } else {
        NovaMsg::set_all_read($user_id);
    }
    nova_ajax_success();
}
add_action('wp_ajax_nova_read_message', 'nova_ajax_read_message');

/**
 * 上传图片（AJAX）
 */
function nova_ajax_upload_img()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $base64 = isset($_POST['file']) ? sanitize_text_field(wp_unslash($_POST['file'])) : '';
    $result = NovaFile::upload('', $base64, 'nova-upload/' . $user_id);
    if (is_wp_error($result)) nova_ajax_error($result->get_error_message());
    nova_ajax_success(array('url' => $result['url']));
}
add_action('wp_ajax_nova_upload_img', 'nova_ajax_upload_img');

/**
 * 生成分享二维码
 */
function nova_ajax_qrcode()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
    if (!$url) nova_ajax_error('参数错误');
    $qrcode = nova_get_qrcode($url);
    if (!$qrcode) nova_ajax_error('二维码生成失败');
    nova_ajax_success(array('qrcode' => $qrcode));
}
add_action('wp_ajax_nova_qrcode', 'nova_ajax_qrcode');
add_action('wp_ajax_nopriv_nova_qrcode', 'nova_ajax_qrcode');

/**
 * 首页 Tab AJAX 加载
 */
function nova_ajax_tab_posts()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $tab = isset($_POST['tab']) ? sanitize_text_field(wp_unslash($_POST['tab'])) : 'new';
    $paged = isset($_POST['paged']) ? (int) $_POST['paged'] : 1;

    $args = nova_index_query_args($tab);
    $args['paged'] = $paged;

    $query = new WP_Query($args);
    $html  = '';
    while ($query->have_posts()) {
        $query->the_post();
        $html .= nova_post_card();
    }
    wp_reset_postdata();

    nova_ajax_success(array('html' => $html, 'max' => $query->max_num_pages));
}
add_action('wp_ajax_nova_tab_posts', 'nova_ajax_tab_posts');
add_action('wp_ajax_nopriv_nova_tab_posts', 'nova_ajax_tab_posts');

/**
 * AJAX 加载更多
 */
function nova_ajax_load_more()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $paged = isset($_POST['paged']) ? (int) $_POST['paged'] + 1 : 2;
    $tab = isset($_POST['tab']) ? sanitize_text_field(wp_unslash($_POST['tab'])) : '';

    global $wp_query;
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'posts_per_page'      => (int) nova_get_setting('posts_per_page', 12),
        'paged'               => $paged,
    );
    //归档页
    if (isset($_POST['taxonomy']) && isset($_POST['term_id'])) {
        $args['tax_query'] = array(array(
            'taxonomy' => sanitize_key($_POST['taxonomy']),
            'terms'    => (int) $_POST['term_id'],
        ));
    }
    //搜索页
    if (isset($_POST['keyword'])) {
        $args['s'] = sanitize_text_field(wp_unslash($_POST['keyword']));
    }
    //tab排序
    switch ($tab) {
        case 'hot':
            $args['orderby'] = 'comment_count';
            $args['order'] = 'DESC';
            break;
        case 'view':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'nova_views';
            $args['order'] = 'DESC';
            break;
    }

    $query = new WP_Query($args);
    $html = '';
    while ($query->have_posts()) {
        $query->the_post();
        $html .= nova_post_card();
    }
    wp_reset_postdata();

    nova_ajax_success(array('html' => $html, 'max' => $query->max_num_pages, 'paged' => $paged));
}
add_action('wp_ajax_nova_load_more', 'nova_ajax_load_more');
add_action('wp_ajax_nopriv_nova_load_more', 'nova_ajax_load_more');
