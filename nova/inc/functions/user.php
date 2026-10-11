<?php
/*
 * Project       : Nova主题
 * Description   : 用户功能（登录/注册/签到/资料）
 */

if (!defined('ABSPATH')) exit;

/**
 * AJAX 登录
 */
function nova_ajax_signin()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    if (is_user_logged_in()) nova_ajax_error('您已登录');

    $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
    $password = isset($_POST['password']) ? wp_unslash($_POST['password']) : ''; // phpcs:ignore
    $remember = !empty($_POST['remember']);
    $captcha  = isset($_POST['captcha']) ? sanitize_text_field(wp_unslash($_POST['captcha'])) : '';

    if (!$username || !$password) nova_ajax_error('请输入用户名和密码');

    //图形验证码校验
    if (nova_get_setting('captcha_s', '1') && !nova_verify_captcha($captcha)) {
        nova_ajax_error('验证码错误');
    }

    //支持邮箱登录
    if (is_email($username)) {
        $user = get_user_by('email', $username);
        $username = $user ? $user->user_login : '';
    }

    $creds = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    );
    $user = wp_signon($creds, is_ssl());
    if (is_wp_error($user)) {
        nova_ajax_error('登录失败，用户名或密码错误');
    }
    nova_ajax_success(array('redirect' => nova_user_center_url()));
}
add_action('wp_ajax_nova_signin', 'nova_ajax_signin');
add_action('wp_ajax_nopriv_nova_signin', 'nova_ajax_signin');

/**
 * AJAX 注册
 */
function nova_ajax_signup()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    if (is_user_logged_in()) nova_ajax_error('您已登录');
    if (!nova_get_setting('register_s', '1')) nova_ajax_error('注册功能已关闭');

    $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
    $email    = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $code     = isset($_POST['email_code']) ? sanitize_text_field(wp_unslash($_POST['email_code'])) : '';
    $password = isset($_POST['password']) ? wp_unslash($_POST['password']) : ''; // phpcs:ignore
    $password2 = isset($_POST['password2']) ? wp_unslash($_POST['password2']) : ''; // phpcs:ignore
    $captcha  = isset($_POST['captcha']) ? sanitize_text_field(wp_unslash($_POST['captcha'])) : '';

    //校验
    if (strlen($username) < 2 || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $username)) {
        nova_ajax_error('用户名格式不正确（2-20位字母数字下划线）');
    }
    if (!is_email($email)) nova_ajax_error('邮箱格式不正确');
    if (username_exists($username)) nova_ajax_error('用户名已存在');
    if (email_exists($email)) nova_ajax_error('邮箱已被注册');
    if (strlen($password) < 6) nova_ajax_error('密码至少6位');
    if ($password !== $password2) nova_ajax_error('两次密码输入不一致');

    //验证码
    if (!nova_check_verify_code('email', $email, $code)) nova_ajax_error('邮箱验证码错误或已过期');
    if (nova_get_setting('captcha_s', '1') && !nova_verify_captcha($captcha)) nova_ajax_error('验证码错误');

    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) nova_ajax_error($user_id->get_error_message());

    //注册奖励积分
    $reg_points = (int) nova_get_setting('reg_points', 0);
    if ($reg_points > 0) {
        NovaDB::update_user_points($user_id, $reg_points, 'add', __('注册奖励', 'nova'));
    }
    //发送系统消息
    NovaMsg::add_system($user_id, sprintf(__('欢迎加入%s！', 'nova'), get_bloginfo('name')));

    //自动登录
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    nova_ajax_success(array('redirect' => nova_user_center_url()));
}
add_action('wp_ajax_nova_signup', 'nova_ajax_signup');
add_action('wp_ajax_nopriv_nova_signup', 'nova_ajax_signup');

/**
 * AJAX 找回密码
 */
function nova_ajax_lostpwd()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $code  = isset($_POST['email_code']) ? sanitize_text_field(wp_unslash($_POST['email_code'])) : '';
    $password = isset($_POST['password']) ? wp_unslash($_POST['password']) : ''; // phpcs:ignore
    $captcha  = isset($_POST['captcha']) ? sanitize_text_field(wp_unslash($_POST['captcha'])) : '';

    $user = get_user_by('email', $email);
    if (!$user) nova_ajax_error('该邮箱未注册');
    if (strlen($password) < 6) nova_ajax_error('密码至少6位');
    if (!nova_check_verify_code('email', $email, $code)) nova_ajax_error('邮箱验证码错误或已过期');
    if (nova_get_setting('captcha_s', '1') && !nova_verify_captcha($captcha)) nova_ajax_error('验证码错误');

    reset_password($user, $password);
    nova_ajax_success(array('redirect' => nova_signin_url()));
}
add_action('wp_ajax_nova_lostpwd', 'nova_ajax_lostpwd');
add_action('wp_ajax_nopriv_nova_lostpwd', 'nova_ajax_lostpwd');

/**
 * AJAX 发送验证码（邮箱/短信）
 */
function nova_ajax_send_code()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $type  = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'email';
    $scene = isset($_POST['scene']) ? sanitize_text_field(wp_unslash($_POST['scene'])) : 'register';
    $target = isset($_POST['target']) ? sanitize_text_field(wp_unslash($_POST['target'])) : '';

    if (!$target) nova_ajax_error('请输入邮箱或手机号');

    //防刷检查
    $check = nova_captcha_send_check($type);
    if (is_wp_error($check)) nova_ajax_error($check->get_error_message());

    $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);

    if ('email' === $type) {
        if (!is_email($target)) nova_ajax_error('邮箱格式不正确');
        //注册时检查邮箱唯一
        if ('register' === $scene && email_exists($target)) nova_ajax_error('该邮箱已被注册');
        if ('reset' === $scene && !email_exists($target)) nova_ajax_error('该邮箱未注册');
        $sent = nova_send_code_email($target, $code, $scene);
        if (!$sent) nova_ajax_error('邮件发送失败，请检查SMTP配置');
    } else {
        if (!preg_match('/^1[3-9]\d{9}$/', $target)) nova_ajax_error('手机号格式不正确');
        $sent = NovaSms::send_code($target, $code);
        if (!$sent) nova_ajax_error('短信发送失败，请检查短信配置');
    }

    nova_set_verify_code($type, $target, $code, 300);
    nova_ajax_success(array(), '验证码已发送，5分钟内有效');
}
add_action('wp_ajax_nova_send_code', 'nova_ajax_send_code');
add_action('wp_ajax_nopriv_nova_send_code', 'nova_ajax_send_code');

/**
 * AJAX 每日签到
 */
function nova_ajax_sign()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('sign_s', '1')) nova_ajax_error('签到功能已关闭');

    global $wpdb;
    $table = $wpdb->prefix . 'nova_sign';
    $today = current_time('Y-m-d');

    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d AND sign_date = %s", $user_id, $today));
    if ($exists) nova_ajax_error('今天已经签到过了');

    //连续签到奖励
    $yesterday = date('Y-m-d', strtotime('-1 day', current_time('U')));
    $yesterday_sign = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d AND sign_date = %s", $user_id, $yesterday));
    $continuous = (int) get_user_meta($user_id, 'nova_sign_continuous', true);
    $continuous = $yesterday_sign ? $continuous + 1 : 1;

    //基础积分+连续奖励（最多每日50积分）
    $points = 10 + min($continuous * 2, 40);
    update_user_meta($user_id, 'nova_sign_continuous', $continuous);

    $wpdb->insert($table, array(
        'user_id'     => $user_id,
        'sign_date'   => $today,
        'points_num'  => $points,
        'create_time' => current_time('mysql'),
    ));
    NovaDB::update_user_points($user_id, $points, 'add', sprintf(__('每日签到(连续%d天)', 'nova'), $continuous));

    nova_ajax_success(array('points' => $points, 'continuous' => $continuous), sprintf(__('签到成功！获得%d积分，已连续签到%d天', 'nova'), $points, $continuous));
}
add_action('wp_ajax_nova_sign', 'nova_ajax_sign');

/**
 * AJAX 更新个人资料
 */
function nova_ajax_update_profile()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $field = isset($_POST['field']) ? sanitize_text_field(wp_unslash($_POST['field'])) : '';
    $value = isset($_POST['value']) ? sanitize_text_field(wp_unslash($_POST['value'])) : '';

    $allowed_fields = array('nova_name', 'nova_sign_text', 'description', 'user_url');
    if (!in_array($field, $allowed_fields)) nova_ajax_error('不允许修改该字段');

    if ('nova_name' === $field && mb_strlen($value, 'UTF-8') > 20) nova_ajax_error('昵称最长20字');
    if ('nova_sign_text' === $field && mb_strlen($value, 'UTF-8') > 100) nova_ajax_error('签名最长100字');

    if (in_array($field, array('description', 'user_url'))) {
        wp_update_user(array('ID' => $user_id, $field => $value));
    } else {
        update_user_meta($user_id, $field, $value);
    }
    nova_ajax_success();
}
add_action('wp_ajax_nova_update_profile', 'nova_ajax_update_profile');

/**
 * AJAX 上传头像
 */
function nova_ajax_upload_avatar()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $base64 = isset($_POST['file']) ? sanitize_text_field(wp_unslash($_POST['file'])) : '';
    $result = NovaFile::upload('', $base64, 'nova-avatar');
    if (is_wp_error($result)) nova_ajax_error($result->get_error_message());

    //删除旧头像
    $old = get_user_meta($user_id, 'nova_avatar', true);
    if ($old) {
        $old_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $old);
        NovaFile::delete($old_path);
    }
    update_user_meta($user_id, 'nova_avatar', $result['url']);
    nova_ajax_success(array('url' => $result['url']));
}
add_action('wp_ajax_nova_upload_avatar', 'nova_ajax_upload_avatar');

/**
 * AJAX 修改密码
 */
function nova_ajax_change_password()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $old_pass = isset($_POST['old_password']) ? wp_unslash($_POST['old_password']) : ''; // phpcs:ignore
    $new_pass = isset($_POST['new_password']) ? wp_unslash($_POST['new_password']) : ''; // phpcs:ignore

    $user = get_userdata($user_id);
    if (!wp_check_password($old_pass, $user->user_pass, $user_id)) nova_ajax_error('原密码错误');
    if (strlen($new_pass) < 6) nova_ajax_error('新密码至少6位');

    wp_set_password($new_pass, $user_id);
    //重新登录
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    nova_ajax_success(array(), '密码修改成功');
}
add_action('wp_ajax_nova_change_password', 'nova_ajax_change_password');

/**
 * 绑定/解绑社交账号
 */
function nova_ajax_oauth_bind()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
    if (!in_array($type, array('qq', 'wechat', 'weibo', 'github'))) nova_ajax_error('参数错误');

    $bind = isset($_POST['bind']) && $_POST['bind'] === '1';
    if (!$bind) {
        //解绑
        delete_user_meta($user_id, 'nova_oauth_' . $type);
        nova_ajax_success(array(), '解绑成功');
    }
    nova_ajax_error('请通过社交登录页面进行绑定');
}
add_action('wp_ajax_nova_oauth_bind', 'nova_ajax_oauth_bind');

/**
 * 判断用户中心权限
 */
function nova_user_page_permission()
{
    if (is_user_logged_in()) return true;
    wp_safe_redirect(nova_signin_url());
    exit;
}

/**
 * 用户中心侧边栏菜单
 */
function nova_user_center_menu()
{
    $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
    $menus = array(
        ''         => array(__('个人中心', 'nova'), 'nova-icon-home'),
        'profile'  => array(__('资料设置', 'nova'), 'nova-icon-set'),
        'posts'    => array(__('我的文章', 'nova'), 'nova-icon-post'),
        'comments' => array(__('我的评论', 'nova'), 'nova-icon-comment'),
        'likes'    => array(__('我的点赞', 'nova'), 'nova-icon-like'),
        'msg'      => array(__('消息中心', 'nova'), 'nova-icon-msg'),
        'sign'     => array(__('每日签到', 'nova'), 'nova-icon-sign'),
        'points'   => array(__('我的积分', 'nova'), 'nova-icon-points'),
        'vip'      => array(__('会员中心', 'nova'), 'nova-icon-vip'),
    );
    if (nova_get_setting('pay_s', '1')) {
        $menus['balance'] = array(__('我的余额', 'nova'), 'nova-icon-wallet');
        $menus['withdraw'] = array(__('提现中心', 'nova'), 'nova-icon-money');
    }
    if (nova_get_setting('shop_s', '1')) {
        $menus['order'] = array(__('我的订单', 'nova'), 'nova-icon-order');
        $menus['shop-order'] = array(__('商城订单', 'nova'), 'nova-icon-bag');
    }
    $menus['oauth'] = array(__('账号绑定', 'nova'), 'nova-icon-bind');

    $html = '<div class="nova-uc-menu card">';
    $html .= '<div class="nova-uc-user">';
    $html .= '<div class="nova-avatar-lg"><img src="' . esc_url(nova_get_user_avatar(get_current_user_id(), 120)) . '" alt="avatar">' . nova_get_user_badge() . '</div>';
    $html .= '<div class="nova-uc-user-name">' . esc_html(nova_get_user_name()) . '</div>';
    if (nova_is_vip()) {
        $html .= '<div class="nova-uc-vip">VIP · ' . sprintf(__('剩余%d天', 'nova'), nova_get_vip_days()) . '</div>';
    }
    $html .= '<div class="nova-uc-user-stats">';
    $html .= '<span>' . esc_html__('积分', 'nova') . ' <b>' . NovaDB::get_user_points(get_current_user_id()) . '</b></span>';
    $html .= '<span>' . esc_html__('余额', 'nova') . ' <b>¥' . NovaDB::get_user_balance(get_current_user_id()) . '</b></span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<ul class="nova-uc-nav">';
    foreach ($menus as $key => $menu) {
        $active = ($tab === $key) ? ' active' : '';
        $html .= '<li><a class="' . $active . '" href="' . esc_url(nova_user_center_url($key)) . '"><i class="' . esc_attr($menu[1]) . '"></i>' . esc_html($menu[0]) . '</a></li>';
    }
    $html .= '</ul>';
    $html .= '</div>';
    return $html;
}
