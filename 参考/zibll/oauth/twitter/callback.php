<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/twitter/OAuth2.php');

@session_start();

if (empty($_SESSION['YURUN_TWITTER_STATE']) || empty($_SESSION['YURUN_TWITTER_CODE_VERIFIER'])) {
    wp_safe_redirect(home_url());
    exit;
}

$twitterConfig = get_oauth_config('twitter');
$twitterOAuth  = new \Zib\OAuthLogin\Twitter\OAuth2($twitterConfig['appid'], $twitterConfig['appkey'], $twitterConfig['backurl']);

if ($twitterConfig['agent']) {
    $twitterOAuth->loginAgentUrl = esc_url(home_url('/oauth/twitteragent'));
}

$twitterOAuth->setCodeVerifier($_SESSION['YURUN_TWITTER_CODE_VERIFIER']);

try {
    $accessToken = $twitterOAuth->getAccessToken($_SESSION['YURUN_TWITTER_STATE']);
    $userInfo    = $twitterOAuth->getUserInfo();
    $openid      = $twitterOAuth->openid;
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

if ($openid && $userInfo) {
    $display_name          = !empty($userInfo['name']) ? $userInfo['name'] : '';
    $userInfo['nick_name'] = $display_name;

    $oauth_data = array(
        'type'        => 'twitter',
        'openid'      => $openid,
        'name'        => $display_name,
        'avatar'      => $twitterOAuth->getAvatarUrl($userInfo),
        'description' => '',
        'getUserInfo' => $userInfo,
    );

    zib_agent_callback($oauth_data);

    $oauth_result = zib_oauth_update_user($oauth_data);

    if ($oauth_result['error']) {
        zib_oauth_die($oauth_result['msg']);
    }

    unset($_SESSION['YURUN_TWITTER_CODE_VERIFIER']);

    $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['redirect_url'];
    wp_safe_redirect($rurl);
    exit;
}

zib_oauth_die();
wp_safe_redirect(home_url());
exit;
