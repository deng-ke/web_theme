<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/microsoft/OAuth2.php');

@session_start();

$microsoftConfig = get_oauth_config('microsoft');
$microsoftOAuth  = new \Zib\OAuthLogin\Microsoft\OAuth2($microsoftConfig['appid'], $microsoftConfig['appkey'], $microsoftConfig['backurl']);

if ($microsoftConfig['agent']) {
    $microsoftOAuth->loginAgentUrl = esc_url(home_url('/oauth/microsoftagent'));
}

zib_agent_login();

$url = $microsoftOAuth->getAuthUrl();

$_SESSION['YURUN_MICROSOFT_STATE'] = $microsoftOAuth->state;
$_SESSION['oauth_rurl']            = !empty($_REQUEST['rurl']) ? $_REQUEST['rurl'] : '';

header('location:' . $url);
exit;
