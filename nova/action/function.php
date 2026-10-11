<?php
/*
 * Project       : Nova主题
 * Description   : 主题动作函数（激活钩子/重写规则/默认设置/后台初始化）
 */

if (!defined('ABSPATH')) exit;

/**
 * 主题激活
 */
function nova_theme_activated()
{
    //建表
    NovaDB::setup_tables();

    //刷新重写规则
    nova_flush_rewrite();

    //默认设置
    if (!get_option('nova_theme_settings')) {
        update_option('nova_theme_settings', array(
            'color'          => '#0e76f8',
            'container'      => '1200',
            'radius'         => '12',
            'pay_balance_s'  => '1',
            'pay_alipay_s'   => '1',
            'pay_bank_s'     => '1',
            'pay_wechat_s'   => '0',
            'vip_s'          => '1',
            'card_s'         => '1',
            'withdraw_s'     => '1',
            'withdraw_min'   => '10',
            'post_sale_percent' => '70',
            'points_buy_rate' => '10',
            'forum_s'        => '1',
            'lazy_img_s'     => '1',
            'reg_points'     => '20',
            'sign_points'    => '5',
        ));
    }
}
add_action('after_switch_theme', 'nova_theme_activated');

/**
 * 刷新重写规则
 */
function nova_flush_rewrite()
{
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

/**
 * 主题切换时清理
 */
function nova_theme_deactivated()
{
    flush_rewrite_rules();
}
add_action('switch_theme', 'nova_theme_deactivated');

/**
 * 后台管理员访问时确保重写规则存在（防止丢失）
 */
function nova_maybe_flush_rewrite()
{
    if (get_option('nova_flush_rewrite') !== NOVA_VERSION) {
        nova_flush_rewrite();
        update_option('nova_flush_rewrite', NOVA_VERSION);
    }
}
add_action('init', 'nova_maybe_flush_rewrite', 99);

/**
 * 移除后台工具栏多余节点
 */
function nova_admin_bar_clean($wp_admin_bar)
{
    if (!is_admin() && !current_user_can('administrator')) {
        $wp_admin_bar->remove_node('wp-logo');
    }
}
add_action('admin_bar_menu', 'nova_admin_bar_clean', 999);

/**
 * 注册用户时初始化默认 meta
 */
function nova_user_register_init($user_id)
{
    update_user_meta($user_id, 'nova_balance', 0);
    update_user_meta($user_id, 'nova_points', 0);
    update_user_meta($user_id, 'nova_vip_end', 0);
    update_user_meta($user_id, 'nova_paid_posts', array());
    update_user_meta($user_id, 'nova_liked_posts', array());
}
add_action('user_register', 'nova_user_register_init');

/**
 * 评论积分奖励
 */
function nova_comment_points_reward($comment_id)
{
    $comment = get_comment($comment_id);
    if (!$comment || !$comment->user_id) return;
    if (!$comment->comment_approved) return;

    //每个评论者只奖励一次
    $rewarded = get_comment_meta($comment_id, 'nova_points_rewarded', true);
    if ($rewarded) return;

    $points = (int) nova_get_setting('comment_points', 2);
    if ($points > 0) {
        NovaDB::update_user_points($comment->user_id, $points, 'add', __('发表评论', 'nova'));
    }
    update_comment_meta($comment_id, 'nova_points_rewarded', 1);
}
add_action('comment_post', 'nova_comment_points_reward');

/**
 * 每日清理过期 transient
 */
function nova_daily_cleanup()
{
    global $wpdb;
    //清理过期验证码
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_nova_%' AND option_value < UNIX_TIMESTAMP()");
}
if (!wp_next_scheduled('nova_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'nova_daily_cleanup');
}
add_action('nova_daily_cleanup', 'nova_daily_cleanup');

/**
 * 处理论坛帖子待审核状态显示
 */
function nova_display_post_states($post_states, $post)
{
    if ('forum_post' === $post->post_type && 'pending_forum' === $post->post_status) {
        $post_states['nova_pending_forum'] = __('待审核', 'nova');
    }
    return $post_states;
}
