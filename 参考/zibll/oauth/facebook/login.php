<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/facebook/OAuth2.php');

@session_start();

$facebookConfig = get_oauth_config('facebook');
$facebookOAuth  = new \Zib\OAuthLogin\Facebook\OAuth2($facebookConfig['appid'], $facebookConfig['appkey'], $facebookConfig['backurl']);

if ($facebookConfig['agent']) {
    $facebookOAuth->loginAgentUrl = esc_url(home_url('/oauth/facebookagent'));
}

zib_agent_login();

$url = $facebookOAuth->getAuthUrl();

$_SESSION['YURUN_FACEBOOK_STATE'] = $facebookOAuth->state;
$_SESSION['oauth_rurl']           = !empty($_REQUEST['rurl']) ? $_REQUEST['rurl'] : '';

header('location:' . $url);
exit;
