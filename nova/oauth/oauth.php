<?php
/*
 * Project       : Nova主题
 * Description   : 社交登录（QQ/微信/微博/Github）
 */

if (!defined('ABSPATH')) exit;

/* ============================================================
 * 平台配置
 * ============================================================ */
function nova_oauth_platforms()
{
    return array(
        'qq'     => array('name' => 'QQ', 'icon' => 'nova-icon-qq', 'setting' => 'oauth_qq_s'),
        'wechat' => array('name' => __('微信', 'nova'), 'icon' => 'nova-icon-wechat', 'setting' => 'oauth_wechat_s'),
        'weibo'  => array('name' => __('微博', 'nova'), 'icon' => 'nova-icon-weibo', 'setting' => 'oauth_weibo_s'),
        'github' => array('name' => 'Github', 'icon' => 'nova-icon-github', 'setting' => 'oauth_github_s'),
    );
}

/**
 * 获取平台配置
 */
function nova_oauth_config($type)
{
    return array(
        'appid'     => nova_get_setting('oauth_' . $type . '_appid'),
        'appkey'    => nova_get_setting('oauth_' . $type . '_appkey'),
        'appsecret' => nova_get_setting('oauth_' . $type . '_appsecret'),
    );
}

/**
 * 平台回调地址
 */
function nova_oauth_callback_url($type)
{
    return home_url('/oauth/' . $type . '/callback');
}

/* ============================================================
 * 登录按钮
 * ============================================================ */
function nova_oauth_redirect()
{
    $url = isset($_SERVER['HTTP_REFERER']) ? wp_get_referer() : '';
    return $url ? $url : home_url();
}

function nova_oauth_login_buttons()
{
    $platforms = nova_oauth_platforms();
    $buttons = array();
    foreach ($platforms as $key => $platform) {
        if (!nova_get_setting($platform['setting'], '1')) continue;
        $buttons[] = '<a class="nova-oauth-btn oauth-' . esc_attr($key) . '" href="' . esc_url(home_url('/oauth/' . $key . '?redirect=' . rawurlencode(nova_oauth_redirect()))) . '"><i class="' . esc_attr($platform['icon']) . '"></i>' . esc_html($platform['name']) . '</a>';
    }
    if (empty($buttons)) return '';
    return '<div class="nova-oauth-box"><div class="nova-oauth-divider"><span>' . esc_html__('社交账号登录', 'nova') . '</span></div><div class="nova-oauth-btns">' . implode('', $buttons) . '</div></div>';
}

/* ============================================================
 * 路由注册
 * ============================================================ */
function nova_oauth_add_rewrite()
{
    add_rewrite_rule('^oauth/(qq|wechat|weibo|github)/?$', 'index.php?nova_oauth=$matches[1]', 'top');
    add_rewrite_rule('^oauth/(qq|wechat|weibo|github)/callback/?$', 'index.php?nova_oauth_cb=$matches[1]', 'top');
}
add_action('init', 'nova_oauth_add_rewrite');

function nova_oauth_query_vars($vars)
{
    $vars[] = 'nova_oauth';
    $vars[] = 'nova_oauth_cb';
    return $vars;
}
add_filter('query_vars', 'nova_oauth_query_vars');

/* ============================================================
 * 授权流程
 * ============================================================ */
function nova_oauth_route()
{
    $type = get_query_var('nova_oauth');
    $cb = get_query_var('nova_oauth_cb');
    if ($type) {
        nova_oauth_authorize_redirect($type);
        exit;
    }
    if ($cb) {
        nova_oauth_handle_callback($cb);
        exit;
    }
}
add_action('template_redirect', 'nova_oauth_route', 5);

/**
 * 生成授权链接并跳转
 */
function nova_oauth_authorize_redirect($type)
{
    $platforms = nova_oauth_platforms();
    if (!isset($platforms[$type])) wp_die('未知登录平台');
    if (!nova_get_setting($platforms[$type]['setting'], '1')) wp_die('此登录方式未开启');

    $config = nova_oauth_config($type);
    $redirect = isset($_GET['redirect']) ? esc_url_raw(wp_unslash($_GET['redirect'])) : home_url(); // phpcs:ignore
    $state = md5($type . uniqid(mt_rand(), true));
    //state 与回跳地址存 transient（5分钟）
    set_transient('nova_oauth_state_' . $state, $redirect, 5 * MINUTE_IN_SECONDS);

    switch ($type) {
        case 'qq':
            $url = 'https://graph.qq.com/oauth2.0/authorize?' . http_build_query(array(
                'response_type' => 'code',
                'client_id'     => $config['appid'],
                'redirect_uri'  => nova_oauth_callback_url('qq'),
                'state'         => $state,
                'scope'         => 'get_user_info',
            ));
            break;
        case 'wechat':
            $url = 'https://open.weixin.qq.com/connect/qrconnect?' . http_build_query(array(
                'appid'       => $config['appid'],
                'redirect_uri' => nova_oauth_callback_url('wechat'),
                'response_type' => 'code',
                'scope'       => 'snsapi_login',
                'state'       => $state,
            )) . '#wechat_redirect';
            break;
        case 'weibo':
            $url = 'https://api.weibo.com/oauth2/authorize?' . http_build_query(array(
                'client_id'    => $config['appid'],
                'redirect_uri' => nova_oauth_callback_url('weibo'),
                'response_type' => 'code',
                'state'        => $state,
            ));
            break;
        case 'github':
            $url = 'https://github.com/login/oauth/authorize?' . http_build_query(array(
                'client_id'    => $config['appid'],
                'redirect_uri' => nova_oauth_callback_url('github'),
                'scope'        => 'read:user',
                'state'        => $state,
            ));
            break;
        default:
            wp_die('未知登录平台');
    }
    wp_redirect($url);
}

/**
 * 处理回调
 */
function nova_oauth_handle_callback($type)
{
    $platforms = nova_oauth_platforms();
    if (!isset($platforms[$type])) wp_die('未知登录平台');

    $config = nova_oauth_config($type);
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    $redirect = $state ? get_transient('nova_oauth_state_' . $state) : false;
    if ($state) delete_transient('nova_oauth_state_' . $state);
    if (!$redirect) $redirect = home_url();

    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    if (!$code) nova_oauth_fail($redirect, '授权失败，未获取到授权码');

    $user_info = nova_oauth_get_user_info($type, $code);
    if (is_wp_error($user_info)) nova_oauth_fail($redirect, $user_info->get_error_message());

    $result = nova_oauth_login($type, $user_info['openid'], $user_info);
    if (is_wp_error($result)) nova_oauth_fail($redirect, $result->get_error_message());

    wp_redirect($redirect);
    exit;
}

/**
 * 获取各平台用户信息
 * @return array|WP_Error array(openid,nickname,avatar)
 */
function nova_oauth_get_user_info($type, $code)
{
    $config = nova_oauth_config($type);
    switch ($type) {
        case 'qq':
            return nova_oauth_qq_user_info($code);
        case 'wechat':
            return nova_oauth_wechat_user_info($code);
        case 'weibo':
            return nova_oauth_weibo_user_info($code);
        case 'github':
            return nova_oauth_github_user_info($code);
    }
    return new WP_Error('nova_oauth', '未知登录平台');
}

/**
 * QQ 用户信息
 */
function nova_oauth_qq_user_info($code)
{
    $config = nova_oauth_config('qq');
    //1.获取 access_token
    $token_url = 'https://graph.qq.com/oauth2.0/token?' . http_build_query(array(
        'grant_type'    => 'authorization_code',
        'client_id'     => $config['appid'],
        'client_secret' => $config['appkey'],
        'code'          => $code,
        'redirect_uri'  => nova_oauth_callback_url('qq'),
    ));
    $response = wp_remote_get($token_url, array('timeout' => 30));
    if (is_wp_error($response)) return $response;
    $body = wp_remote_retrieve_body($response);
    parse_str($body, $token);
    if (empty($token['access_token'])) return new WP_Error('nova_oauth', '获取 access_token 失败：' . $body);

    //2.获取 openid
    $openid_url = 'https://graph.qq.com/oauth2.0/me?access_token=' . $token['access_token'];
    $response = wp_remote_get($openid_url, array('timeout' => 30));
    if (is_wp_error($response)) return $response;
    $body = wp_remote_retrieve_body($response);
    if (preg_match('/"openid":"([^"]+)"/', $body, $match)) {
        $openid = $match[1];
    } else {
        return new WP_Error('nova_oauth', '获取 openid 失败');
    }

    //3.获取用户信息
    $info_url = 'https://graph.qq.com/user/get_user_info?' . http_build_query(array(
        'access_token'       => $token['access_token'],
        'oauth_consumer_key' => $config['appid'],
        'openid'             => $openid,
    ));
    $response = wp_remote_get($info_url, array('timeout' => 30));
    if (is_wp_error($response)) return $response;
    $info = json_decode(wp_remote_retrieve_body($response), true);
    return array(
        'openid'   => $openid,
        'nickname' => isset($info['nickname']) ? $info['nickname'] : 'QQ用户',
        'avatar'   => isset($info['figureurl_qq_2']) ? $info['figureurl_qq_2'] : (isset($info['figureurl_qq_1']) ? $info['figureurl_qq_1'] : ''),
    );
}

/**
 * 微信用户信息（开放平台扫码登录）
 */
function nova_oauth_wechat_user_info($code)
{
    $config = nova_oauth_config('wechat');
    //1.获取 access_token
    $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query(array(
        'appid'      => $config['appid'],
        'secret'     => $config['appkey'],
        'code'       => $code,
        'grant_type' => 'authorization_code',
    ));
    $response = wp_remote_get($token_url, array('timeout' => 30));
    if (is_wp_error($response)) return $response;
    $token = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($token['openid'])) return new WP_Error('nova_oauth', isset($token['errmsg']) ? '微信授权失败：' . $token['errmsg'] : '获取 openid 失败');

    //2.获取用户信息
    $info_url = 'https://api.weixin.qq.com/sns/userinfo?' . http_build_query(array(
        'access_token' => $token['access_token'],
        'openid'       => $token['openid'],
    ));
    $response = wp_remote_get($info_url, array('timeout' => 30));
    if (is_wp_error($response)) return $response;
    $info = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($info['openid'])) return new WP_Error('nova_oauth', '获取微信用户信息失败');

    return array(
        'openid'   => $info['openid'],
        'nickname' => isset($info['nickname']) ? nova_oauth_emoji_clean($info['nickname']) : '微信用户',
        'avatar'   => isset($info['headimgurl']) ? $info['headimgurl'] : '',
    );
}

/**
 * 微博用户信息
 */
function nova_oauth_weibo_user_info($code)
{
    $config = nova_oauth_config('weibo');
    //1.获取 access_token（POST）
    $response = wp_remote_post('https://api.weibo.com/oauth2/access_token', array(
        'timeout' => 30,
        'body'    => array(
            'client_id'     => $config['appid'],
            'client_secret' => $config['appkey'],
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => nova_oauth_callback_url('weibo'),
        ),
    ));
    if (is_wp_error($response)) return $response;
    $token = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($token['access_token'])) return new WP_Error('nova_oauth', isset($token['error_description']) ? $token['error_description'] : '获取 access_token 失败');

    //2.获取用户信息
    $info_url = 'https://api.weibo.com/2/users/show.json?' . http_build_query(array(
        'access_token' => $token['access_token'],
        'uid'          => $token['uid'],
    ));
    $response = wp_remote_get($info_url, array('timeout' => 30));
    if (is_wp_error($response)) return $response;
    $info = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($info['id'])) return new WP_Error('nova_oauth', '获取微博用户信息失败');

    return array(
        'openid'   => (string) $info['id'],
        'nickname' => isset($info['screen_name']) ? $info['screen_name'] : '微博用户',
        'avatar'   => isset($info['avatar_large']) ? $info['avatar_large'] : '',
    );
}

/**
 * Github 用户信息
 */
function nova_oauth_github_user_info($code)
{
    $config = nova_oauth_config('github');
    //1.获取 access_token
    $response = wp_remote_post('https://github.com/login/oauth/access_token', array(
        'timeout' => 30,
        'headers' => array('Accept' => 'application/json'),
        'body'    => array(
            'client_id'     => $config['appid'],
            'client_secret' => $config['appkey'],
            'code'          => $code,
            'redirect_uri'  => nova_oauth_callback_url('github'),
        ),
    ));
    if (is_wp_error($response)) return $response;
    $token = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($token['access_token'])) return new WP_Error('nova_oauth', isset($token['error_description']) ? $token['error_description'] : '获取 access_token 失败');

    //2.获取用户信息
    $response = wp_remote_get('https://api.github.com/user', array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'token ' . $token['access_token'],
            'User-Agent'    => 'NovaTheme',
        ),
    ));
    if (is_wp_error($response)) return $response;
    $info = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($info['id'])) return new WP_Error('nova_oauth', '获取 Github 用户信息失败');

    return array(
        'openid'   => (string) $info['id'],
        'nickname' => isset($info['login']) ? $info['login'] : 'Github用户',
        'avatar'   => isset($info['avatar_url']) ? $info['avatar_url'] : '',
    );
}

/**
 * 清除 emoji 字符（微信昵称可能包含）
 */
function nova_oauth_emoji_clean($str)
{
    return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $str);
}

/* ============================================================
 * 登录/注册/绑定
 * ============================================================ */
/**
 * 统一登录处理：已登录→绑定；未登录→查找或注册
 */
function nova_oauth_login($type, $openid, $user_info)
{
    $meta_key = 'nova_oauth_' . $type;

    //查找已绑定的用户
    $users = get_users(array(
        'meta_key'   => $meta_key,
        'meta_value' => $openid,
        'number'     => 1,
    ));

    //已登录：绑定到当前账号
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        if (!empty($users) && $users[0]->ID != $user_id) {
            return new WP_Error('nova_oauth', '该账号已被其他用户绑定');
        }
        update_user_meta($user_id, $meta_key, $openid);
        if (!empty($user_info['avatar'])) {
            update_user_meta($user_id, 'nova_avatar', $user_info['avatar']);
        }
        return $user_id;
    }

    //已绑定：直接登录
    if (!empty($users)) {
        wp_set_auth_cookie($users[0]->ID, true);
        return $users[0]->ID;
    }

    //注册新用户
    $user_id = nova_oauth_register($type, $openid, $user_info);
    if (is_wp_error($user_id)) return $user_id;
    wp_set_auth_cookie($user_id, true);
    return $user_id;
}

/**
 * 注册新用户
 */
function nova_oauth_register($type, $openid, $user_info)
{
    $nickname = sanitize_user($user_info['nickname'], true);
    if (!$nickname) $nickname = 'user';

    //用户名去重
    $username = 'oauth_' . $type . '_' . substr(md5($openid), 0, 8);
    if (username_exists($username)) {
        $username .= '_' . mt_rand(100, 999);
    }
    //昵称去重
    if (get_user_by('login', $nickname)) {
        $nickname = $nickname . '_' . mt_rand(100, 999);
    }

    //虚拟邮箱
    $email = $openid . '@' . $type . '.oauth.nova';
    if (email_exists($email)) {
        $email = substr(md5($openid . time()), 0, 12) . '@' . $type . '.oauth.nova';
    }

    $user_id = wp_insert_user(array(
        'user_login'   => $username,
        'user_pass'    => wp_generate_password(16, true),
        'user_email'   => $email,
        'display_name' => $nickname,
        'nickname'     => $nickname,
        'role'         => 'subscriber',
    ));
    if (is_wp_error($user_id)) return $user_id;

    update_user_meta($user_id, 'nova_oauth_' . $type, $openid);
    update_user_meta($user_id, 'nova_name', $nickname);
    if (!empty($user_info['avatar'])) {
        update_user_meta($user_id, 'nova_avatar', $user_info['avatar']);
    }
    //新人注册奖励积分
    $reg_points = (int) nova_get_setting('reg_points', 20);
    if ($reg_points) {
        NovaDB::update_user_points($user_id, $reg_points, 'add', __('新用户注册奖励', 'nova'));
    }
    NovaMsg::add_system($user_id, __('欢迎加入本站！已为你自动注册账号', 'nova'));
    return $user_id;
}

/**
 * 失败处理
 */
function nova_oauth_fail($redirect, $msg)
{
    //回跳地址附带错误信息
    $url = add_query_arg(array('oauth_error' => rawurlencode($msg)), $redirect);
    wp_redirect($url);
    exit;
}

/* ============================================================
 * 账号绑定面板与解绑
 * ============================================================ */
/**
 * 用户中心绑定面板
 */
function nova_oauth_bind_html()
{
    $user_id = get_current_user_id();
    $platforms = nova_oauth_platforms();
    $html = '<div class="nova-bind-box card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('账号绑定', 'nova') . '</span></h3>';
    $html .= '<div class="nova-bind-list">';
    foreach ($platforms as $key => $platform) {
        if (!nova_get_setting($platform['setting'], '1')) continue;
        $bound = get_user_meta($user_id, 'nova_oauth_' . $key, true);
        $html .= '<div class="nova-bind-item">';
        $html .= '<i class="' . esc_attr($platform['icon']) . '"></i>';
        $html .= '<span>' . esc_html($platform['name']) . '</span>';
        if ($bound) {
            $html .= '<em class="muted-2-color">' . esc_html__('已绑定', 'nova') . '</em>';
            $html .= '<button class="btn btn-sm btn-outline nova-oauth-unbind" data-type="' . $key . '">' . esc_html__('解绑', 'nova') . '</button>';
        } else {
            $html .= '<em class="muted-2-color">' . esc_html__('未绑定', 'nova') . '</em>';
            $html .= '<a class="btn btn-sm btn-outline" href="' . esc_url(home_url('/oauth/' . $key . '?redirect=' . rawurlencode(nova_user_center_url('bind')))) . '">' . esc_html__('绑定', 'nova') . '</a>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * AJAX 解绑
 */
function nova_ajax_oauth_unbind()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
    if (!array_key_exists($type, nova_oauth_platforms())) nova_ajax_error('参数错误');
    delete_user_meta($user_id, 'nova_oauth_' . $type);
    nova_ajax_success();
}
add_action('wp_ajax_nova_oauth_unbind', 'nova_ajax_oauth_unbind');
