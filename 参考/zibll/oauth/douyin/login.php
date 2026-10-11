<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-08-26
 */

require_once get_theme_file_path('/oauth/sdk/douyin/OAuth2.php');

@session_start();

$douyinConfig = get_oauth_config('douyin');
$douyinOAuth  = new \Zib\OAuthLogin\Douyin\OAuth2($douyinConfig['appid'], $douyinConfig['appkey'], $douyinConfig['backurl']);

if ($douyinConfig['agent']) {
    $douyinOAuth->loginAgentUrl = esc_url(home_url('/oauth/douyinagent'));
}

zib_agent_login();

$url = $douyinOAuth->getAuthUrl();

$_SESSION['YURUN_DOUYIN_STATE'] = $douyinOAuth->state;
$_SESSION['oauth_rurl']         = !empty($_REQUEST['rurl']) ? $_REQUEST['rurl'] : '';

header('location:' . $url);
exit;
