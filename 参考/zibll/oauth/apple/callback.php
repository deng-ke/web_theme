<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/apple/OAuth2.php');

@session_start();

if (empty($_SESSION['YURUN_APPLE_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

$state = !empty($_POST['state']) ? $_POST['state'] : (!empty($_GET['state']) ? $_GET['state'] : '');

if ($state !== $_SESSION['YURUN_APPLE_STATE']) {
    zib_oauth_die(__('Apple 登录 state 验证失败', 'zib_language'));
}

$appleConfig = get_oauth_config('apple');
$appleOAuth  = new \Zib\OAuthLogin\Apple\OAuth2($appleConfig['appid'], '', $appleConfig['backurl']);
$appleOAuth->setAppleConfig($appleConfig);

if ($appleConfig['agent']) {
    $appleOAuth->loginAgentUrl = esc_url(home_url('/oauth/appleagent'));
}

$firstAuthUser = $appleOAuth->parseFirstAuthUser(!empty($_POST['user']) ? $_POST['user'] : '');

try {
    $appleOAuth->getAccessToken($_SESSION['YURUN_APPLE_STATE'], null, $state);
    $userInfo = $appleOAuth->getUserInfo(null, $firstAuthUser);
    $openid   = $appleOAuth->openid;
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

if ($openid && $userInfo) {
    $display_name          = !empty($userInfo['name']) ? $userInfo['name'] : '';
    $userInfo['nick_name'] = $display_name;

    $oauth_data = array(
        'type'        => 'apple',
        'openid'      => $openid,
        'name'        => $display_name,
        'avatar'      => '',
        'description' => '',
        'getUserInfo' => $userInfo,
    );

    zib_agent_callback($oauth_data);

    $oauth_result = zib_oauth_update_user($oauth_data);

    if ($oauth_result['error']) {
        zib_oauth_die($oauth_result['msg']);
    }

    unset($_SESSION['YURUN_APPLE_NONCE']);

    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['redirect_url'];
    wp_safe_redirect($rurl);
    exit;
}

zib_oauth_die();
wp_safe_redirect(home_url());
exit;
