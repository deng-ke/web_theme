<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-06-15
 */

require_once get_theme_file_path('/oauth/sdk/google/OAuth2.php');

//启用 session
@session_start();

if (empty($_SESSION['YURUN_GOOGLE_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//获取后台配置
$googleConfig = get_oauth_config('google');
$googleOAuth  = new \Zib\OAuthLogin\Google\OAuth2($googleConfig['appid'], $googleConfig['appkey'], $googleConfig['backurl']);

if ($googleConfig['agent']) {
    $googleOAuth->loginAgentUrl = esc_url(home_url('/oauth/googleagent'));
}

try {
    $accessToken = $googleOAuth->getAccessToken($_SESSION['YURUN_GOOGLE_STATE']);
    $userInfo    = $googleOAuth->getUserInfo();
    $openid      = $googleOAuth->openid;
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

// 处理本地业务逻辑
if ($openid && $userInfo) {
    $display_name = !empty($userInfo['name']) ? $userInfo['name'] : '';
    $userInfo['nick_name'] = $display_name;

    $oauth_data = array(
        'type'        => 'google',
        'openid'      => $openid,
        'name'        => $display_name,
        'avatar'      => !empty($userInfo['picture']) ? $userInfo['picture'] : '',
        'description' => '',
        'getUserInfo' => $userInfo,
    );

    //代理登录
    zib_agent_callback($oauth_data);

    $oauth_result = zib_oauth_update_user($oauth_data);

    if ($oauth_result['error']) {
        zib_oauth_die($oauth_result['msg']);
    }

    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['redirect_url'];
    wp_safe_redirect($rurl);
    exit;
}

zib_oauth_die();
wp_safe_redirect(home_url());
exit;
