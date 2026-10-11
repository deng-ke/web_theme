<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/facebook/OAuth2.php');

@session_start();

if (empty($_SESSION['YURUN_FACEBOOK_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

$facebookConfig = get_oauth_config('facebook');
$facebookOAuth  = new \Zib\OAuthLogin\Facebook\OAuth2($facebookConfig['appid'], $facebookConfig['appkey'], $facebookConfig['backurl']);

if ($facebookConfig['agent']) {
    $facebookOAuth->loginAgentUrl = esc_url(home_url('/oauth/facebookagent'));
}

try {
    $accessToken = $facebookOAuth->getAccessToken($_SESSION['YURUN_FACEBOOK_STATE']);
    $userInfo    = $facebookOAuth->getUserInfo();
    $openid      = $facebookOAuth->openid;
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

if ($openid && $userInfo) {
    $display_name          = !empty($userInfo['name']) ? $userInfo['name'] : '';
    $userInfo['nick_name'] = $display_name;

    $oauth_data = array(
        'type'        => 'facebook',
        'openid'      => $openid,
        'name'        => $display_name,
        'avatar'      => $facebookOAuth->getAvatarUrl($userInfo),
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
