<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/twitter/OAuth2.php');

@session_start();

$twitterConfig = get_oauth_config('twitter');
$twitterOAuth  = new \Zib\OAuthLogin\Twitter\OAuth2($twitterConfig['appid'], $twitterConfig['appkey'], $twitterConfig['backurl']);

if ($twitterConfig['agent']) {
    $twitterOAuth->loginAgentUrl = esc_url(home_url('/oauth/twitteragent'));
}

$codeVerifier = \Zib\OAuthLogin\Twitter\OAuth2::generateCodeVerifier();
$twitterOAuth->setCodeVerifier($codeVerifier);

zib_agent_login();

$url = $twitterOAuth->getAuthUrl();

$_SESSION['YURUN_TWITTER_STATE']         = $twitterOAuth->state;
$_SESSION['YURUN_TWITTER_CODE_VERIFIER'] = $codeVerifier;
$_SESSION['oauth_rurl']                  = !empty($_REQUEST['rurl']) ? $_REQUEST['rurl'] : '';

header('location:' . $url);
exit;
