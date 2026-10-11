<?php
/*
 * Project       : Nova主题
 * Description   : 用户中心各Tab页面内容（概览/资料/文章/评论/点赞/签到/积分/余额）
 */

if (!defined('ABSPATH')) exit;

/**
 * 用户中心主页（概览）
 */
function nova_user_home_html()
{
    $user_id = get_current_user_id();
    $html = '<div class="nova-uc-home">';
    //数据概览
    $html .= '<div class="nova-uc-stats card">';
    $html .= '<div class="nova-uc-stat"><b>' . count_user_posts($user_id) . '</b><span>' . esc_html__('我的文章', 'nova') . '</span></div>';
    $html .= '<div class="nova-uc-stat"><b>' . NovaDB::get_user_points($user_id) . '</b><span>' . esc_html__('我的积分', 'nova') . '</span></div>';
    $html .= '<div class="nova-uc-stat"><b>¥' . NovaDB::get_user_balance($user_id) . '</b><span>' . esc_html__('我的余额', 'nova') . '</span></div>';
    $html .= '<div class="nova-uc-stat"><b>' . NovaMsg::unread_count($user_id) . '</b><span>' . esc_html__('未读消息', 'nova') . '</span></div>';
    $html .= '</div>';

    //近期动态
    $query = new WP_Query(array(
        'author'         => $user_id,
        'post_type'      => array('post', 'forum_post'),
        'posts_per_page' => 5,
        'post_status'    => array('publish', 'pending_forum'),
    ));
    $html .= '<div class="nova-uc-recent card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('最近发布', 'nova') . '</span></h3>';
    if (!$query->have_posts()) {
        $html .= '<div class="muted-2-color nova-uc-empty">' . esc_html__('还没有发布过内容', 'nova') . '</div>';
    } else {
        $html .= '<div class="nova-uc-recent-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $html .= '<a class="nova-uc-recent-item" href="' . esc_url(get_permalink()) . '">';
            $html .= '<span class="nova-uc-recent-title">' . esc_html(get_the_title()) . '</span>';
            $html .= '<span class="muted-2-color">' . esc_html(nova_time_ago(get_the_time('Y-m-d H:i:s'))) . '</span>';
            $html .= '</a>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    wp_reset_postdata();
    $html .= '</div>';
    return $html;
}

/**
 * 资料设置
 */
function nova_user_profile_html()
{
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $sign_text = get_user_meta($user_id, 'nova_sign_text', true);
    $site_url = $user->user_url;
    $html = '<div class="nova-uc-profile">';
    $html .= '<div class="nova-profile-form card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('基本资料', 'nova') . '</span></h3>';
    //头像上传
    $html .= '<div class="nova-profile-avatar-row">';
    $html .= '<div class="nova-avatar-lg"><img src="' . esc_url(nova_get_user_avatar($user_id, 160)) . '" alt="avatar"></div>';
    $html .= '<div class="nova-profile-avatar-action">';
    $html .= '<button class="btn btn-outline nova-avatar-upload-btn"><i class="nova-icon-upload"></i>' . esc_html__('更换头像', 'nova') . '</button>';
    $html .= '<input type="file" class="nova-avatar-file" accept="image/*" style="display:none">';
    $html .= '<p class="muted-2-color">' . esc_html__('支持 JPG/PNG，建议正方形图片', 'nova') . '</p>';
    $html .= '</div>';
    $html .= '</div>';
    //昵称
    $html .= '<div class="nova-form-group"><label>' . esc_html__('昵称', 'nova') . '</label><input type="text" class="nova-profile-name" value="' . esc_attr(nova_get_user_name($user_id)) . '"></div>';
    //签名
    $html .= '<div class="nova-form-group"><label>' . esc_html__('个性签名', 'nova') . '</label><input type="text" class="nova-profile-sign" maxlength="60" placeholder="' . esc_attr__('一句话介绍自己', 'nova') . '" value="' . esc_attr($sign_text) . '"></div>';
    //网站
    $html .= '<div class="nova-form-group"><label>' . esc_html__('个人网站', 'nova') . '</label><input type="url" class="nova-profile-site" placeholder="https://" value="' . esc_attr($site_url) . '"></div>';
    $html .= '<button class="btn btn-primary nova-profile-save">' . esc_html__('保存资料', 'nova') . '</button>';
    $html .= '</div>';

    //修改密码
    $html .= '<div class="nova-password-form card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('修改密码', 'nova') . '</span></h3>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('当前密码', 'nova') . '</label><input type="password" class="nova-pwd-old" placeholder="' . esc_attr__('请输入当前密码', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('新密码', 'nova') . '</label><input type="password" class="nova-pwd-new" placeholder="' . esc_attr__('至少6位', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('确认新密码', 'nova') . '</label><input type="password" class="nova-pwd-new2" placeholder="' . esc_attr__('再次输入新密码', 'nova') . '"></div>';
    $html .= '<button class="btn btn-primary nova-password-save">' . esc_html__('修改密码', 'nova') . '</button>';
    $html .= '</div>';

    //邮箱（非OAuth账号可修改）
    $oauth_email = preg_match('/@(qq|wechat|weibo|github)\.oauth\.nova$/', $user->user_email);
    if (!$oauth_email) {
        $html .= '<div class="nova-email-form card">';
        $html .= '<h3 class="nova-block-title"><span>' . esc_html__('绑定邮箱', 'nova') . '</span></h3>';
        $html .= '<div class="nova-form-group"><label>' . esc_html__('新邮箱', 'nova') . '</label><input type="email" class="nova-email-new" placeholder="' . esc_attr__('请输入新邮箱', 'nova') . '"></div>';
        $html .= '<div class="nova-form-group"><label>' . esc_html__('邮箱验证码', 'nova') . '</label><div class="nova-input-wrap nova-input-code"><input type="text" class="nova-email-code" placeholder="' . esc_attr__('验证码', 'nova') . '"><button type="button" class="nova-code-btn" data-type="email" data-scene="bind">' . esc_html__('获取验证码', 'nova') . '</button></div></div>';
        $html .= '<button class="btn btn-primary nova-email-save">' . esc_html__('更换邮箱', 'nova') . '</button>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 我的文章
 */
function nova_user_posts_html()
{
    $user_id = get_current_user_id();
    $query = new WP_Query(array(
        'author'         => $user_id,
        'post_type'      => 'post',
        'posts_per_page' => 10,
        'paged'          => nova_get_the_paged(),
        'post_status'    => array('publish', 'pending', 'draft'),
    ));
    $html = '<div class="nova-uc-posts">';
    if (!$query->have_posts()) {
        $html .= nova_empty_box(__('暂无文章', 'nova'), __('你还没有发布过文章', 'nova'));
    } else {
        $html .= '<div class="nova-uc-post-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $status = get_post_status();
            $status_name = array('publish' => __('已发布', 'nova'), 'pending' => __('待审核', 'nova'), 'draft' => __('草稿', 'nova'));
            $html .= '<div class="nova-uc-post-item card">';
            $html .= '<div class="nova-uc-post-info">';
            $html .= '<a class="nova-uc-post-title" href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            $html .= '<div class="muted-2-color">' . esc_html(get_the_time('Y-m-d H:i')) . '</div>';
            $html .= '</div>';
            $html .= '<span class="nova-uc-post-status st-' . $status . '">' . esc_html(isset($status_name[$status]) ? $status_name[$status] : $status) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        nova_paging($query);
    }
    $html .= '</div>';
    wp_reset_postdata();
    return $html;
}

/**
 * 我的评论
 */
function nova_user_comments_html()
{
    $user_id = get_current_user_id();
    $comments = get_comments(array('user_id' => $user_id, 'number' => 20, 'status' => 'approve'));
    $html = '<div class="nova-uc-comments">';
    if (empty($comments)) {
        $html .= nova_empty_box(__('暂无评论', 'nova'), __('你还没有发表过评论', 'nova'));
    } else {
        $html .= '<div class="nova-uc-comment-list">';
        foreach ($comments as $comment) {
            $html .= '<div class="nova-uc-comment-item card">';
            $html .= '<div class="nova-uc-comment-content muted-color">' . esc_html(mb_substr(wp_strip_all_tags($comment->comment_content), 0, 60)) . '</div>';
            $html .= '<div class="nova-uc-comment-meta muted-2-color">';
            $html .= '<span>' . esc_html(nova_time_ago($comment->comment_date)) . '</span>';
            $html .= '<a href="' . esc_url(get_comment_link($comment)) . '">' . esc_html__('评论于：', 'nova') . esc_html(get_the_title($comment->comment_post_ID)) . '</a>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 我的点赞
 */
function nova_user_likes_html()
{
    $user_id = get_current_user_id();
    $liked = get_user_meta($user_id, 'nova_liked_posts', true);
    $liked = is_array($liked) ? $liked : array();
    $html = '<div class="nova-uc-likes">';
    if (empty($liked)) {
        $html .= nova_empty_box(__('暂无点赞', 'nova'), __('你还没有点赞过文章', 'nova'));
    } else {
        $query = new WP_Query(array(
            'post__in'        => $liked,
            'post_type'       => 'post',
            'posts_per_page'  => count($liked),
            'orderby'         => 'post__in',
            'ignore_sticky_posts' => true,
        ));
        $html .= '<div class="nova-uc-post-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $thumb = nova_post_thumbnail_url(null, 'nova-thumb');
            $html .= '<div class="nova-uc-post-item card">';
            if ($thumb) {
                $html .= '<a class="nova-uc-post-thumb" href="' . esc_url(get_permalink()) . '"><img src="' . esc_url($thumb) . '" alt="thumb"></a>';
            }
            $html .= '<div class="nova-uc-post-info">';
            $html .= '<a class="nova-uc-post-title" href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            $html .= '<div class="muted-2-color">' . esc_html(get_the_time('Y-m-d')) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        wp_reset_postdata();
    }
    $html .= '</div>';
    return $html;
}

/**
 * 每日签到
 */
function nova_sign_html()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $table = $wpdb->prefix . 'nova_sign';
    $today = current_time('Y-m-d');
    $signed = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d AND sign_date = %s", $user_id, $today));

    //最近签到记录
    $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC LIMIT 30", $user_id));

    //连续签到天数
    $continue = 0;
    foreach ($records as $i => $record) {
        $expect = date('Y-m-d', current_time('U') - $i * DAY_IN_SECONDS);
        if ($record->sign_date === $expect) {
            $continue++;
        } else {
            break;
        }
    }
    if ($signed) $continue++;

    $sign_points = (int) nova_get_setting('sign_points', 5);
    $html = '<div class="nova-sign-page">';
    $html .= '<div class="nova-sign-box card">';
    $html .= '<div class="nova-sign-calendar"><i class="nova-icon-sign"></i></div>';
    $html .= '<h3>' . esc_html__('每日签到', 'nova') . '</h3>';
    $html .= '<p class="muted-2-color">' . sprintf(esc_html__('已连续签到 %d 天，每次签到可得 %d 积分', 'nova'), $continue, $sign_points) . '</p>';
    if ($signed) {
        $html .= '<button class="btn btn-disabled" disabled>' . esc_html__('今日已签到', 'nova') . '</button>';
    } else {
        $html .= '<button class="btn btn-primary nova-sign-btn">' . esc_html__('立即签到', 'nova') . '</button>';
    }
    $html .= '</div>';
    if (!empty($records)) {
        $html .= '<div class="nova-sign-records card">';
        $html .= '<h3 class="nova-block-title"><span>' . esc_html__('签到记录', 'nova') . '</span></h3>';
        $html .= '<div class="nova-sign-record-list">';
        foreach ($records as $record) {
            $html .= '<div class="nova-sign-record-item"><span>' . esc_html($record->sign_date) . '</span><b>+' . esc_html($record->points_num) . '</b></div>';
        }
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 积分明细
 */
function nova_user_points_html()
{
    $user_id = get_current_user_id();
    global $wpdb;
    $table = $wpdb->prefix . 'nova_points';
    $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC LIMIT 50", $user_id));
    $html = '<div class="nova-points-page">';
    $html .= '<div class="nova-points-total card">';
    $html .= '<div class="nova-points-num">' . NovaDB::get_user_points($user_id) . '</div>';
    $html .= '<div class="muted-2-color">' . esc_html__('当前积分', 'nova') . '</div>';
    $html .= '<button class="btn btn-primary nova-points-buy-btn">' . esc_html__('购买积分', 'nova') . '</button>';
    $html .= '</div>';
    if (empty($records)) {
        $html .= nova_empty_box(__('暂无记录', 'nova'), __('还没有积分变动记录', 'nova'));
    } else {
        $html .= '<div class="nova-points-list card">';
        $html .= '<h3 class="nova-block-title"><span>' . esc_html__('积分明细', 'nova') . '</span></h3>';
        foreach ($records as $record) {
            $html .= '<div class="nova-points-item">';
            $html .= '<div class="nova-points-info"><b>' . esc_html($record->points_desc) . '</b><span class="muted-2-color">' . esc_html($record->create_time) . '</span></div>';
            $html .= '<span class="' . ($record->points_num > 0 ? 'add' : 'sub') . '">' . ($record->points_num > 0 ? '+' : '') . esc_html($record->points_num) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 余额明细
 */
function nova_user_balance_html()
{
    $user_id = get_current_user_id();
    $balance = NovaDB::get_user_balance($user_id);
    $html = '<div class="nova-balance-page">';
    $html .= '<div class="nova-balance-total card">';
    $html .= '<div class="nova-balance-num">¥ ' . number_format($balance, 2) . '</div>';
    $html .= '<div class="muted-2-color">' . esc_html__('当前余额', 'nova') . '</div>';
    $html .= '<div class="nova-balance-actions">';
    if (nova_get_setting('withdraw_s', '1')) {
        $html .= '<a class="btn btn-outline" href="' . esc_url(nova_user_center_url('withdraw')) . '">' . esc_html__('申请提现', 'nova') . '</a>';
    }
    if (nova_get_setting('card_s', '1')) {
        $html .= '<a class="btn btn-primary" href="' . esc_url(nova_user_center_url('card')) . '">' . esc_html__('充值卡充值', 'nova') . '</a>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="nova-balance-tip card">';
    $html .= '<p class="muted-2-color">' . esc_html__('余额来源于：文章收益、打赏、充值卡充值等', 'nova') . '</p>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
