<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/apple/OAuth2.php');

@session_start();

$appleConfig = get_oauth_config('apple');
$appleOAuth  = new \Zib\OAuthLogin\Apple\OAuth2($appleConfig['appid'], '', $appleConfig['backurl']);
$appleOAuth->setAppleConfig($appleConfig);

if ($appleConfig['agent']) {
    $appleOAuth->loginAgentUrl = esc_url(home_url('/oauth/appleagent'));
}

$nonce = bin2hex(random_bytes(16));
$appleOAuth->setNonce($nonce);

zib_agent_login();

$url = $appleOAuth->getAuthUrl();

$_SESSION['YURUN_APPLE_STATE'] = $appleOAuth->state;
$_SESSION['YURUN_APPLE_NONCE'] = $nonce;
$_SESSION['oauth_rurl']        = !empty($_REQUEST['rurl']) ? $_REQUEST['rurl'] : '';

header('location:' . $url);
exit;
