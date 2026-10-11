<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-08-26
 */

require_once get_theme_file_path('/oauth/sdk/douyin/OAuth2.php');

@session_start();

if (empty($_SESSION['YURUN_DOUYIN_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

$douyinConfig = get_oauth_config('douyin');
$douyinOAuth  = new \Zib\OAuthLogin\Douyin\OAuth2($douyinConfig['appid'], $douyinConfig['appkey'], $douyinConfig['backurl']);

if ($douyinConfig['agent']) {
    $douyinOAuth->loginAgentUrl = esc_url(home_url('/oauth/douyinagent'));
}

try {
    $accessToken = $douyinOAuth->getAccessToken($_SESSION['YURUN_DOUYIN_STATE']);
    $userInfo    = $douyinOAuth->getUserInfo();
    $openid      = $douyinOAuth->openid;
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

if ($openid && $userInfo) {
    $display_name          = !empty($userInfo['name']) ? $userInfo['name'] : '';
    $userInfo['nick_name'] = $display_name;

    $oauth_data = array(
        'type'        => 'douyin',
        'openid'      => $openid,
        'name'        => $display_name,
        'avatar'      => !empty($userInfo['avatar']) ? $userInfo['avatar'] : '',
        'description' => '',
        'getUserInfo' => $userInfo,
    );

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
