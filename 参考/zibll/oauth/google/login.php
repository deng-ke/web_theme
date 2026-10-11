<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-06-15
 */

require_once get_theme_file_path('/oauth/sdk/google/OAuth2.php');

//启用 session
@session_start();

//获取后台配置
$googleConfig = get_oauth_config('google');
$googleOAuth  = new \Zib\OAuthLogin\Google\OAuth2($googleConfig['appid'], $googleConfig['appkey'], $googleConfig['backurl']);

if ($googleConfig['agent']) {
    $googleOAuth->loginAgentUrl = esc_url(home_url('/oauth/googleagent'));
}

//代理登录
zib_agent_login();

$url = $googleOAuth->getAuthUrl();

// 存储sdk自动生成的state，回调处理时候要验证
$_SESSION['YURUN_GOOGLE_STATE'] = $googleOAuth->state;
// 储存返回页面
$_SESSION['oauth_rurl'] = !empty($_REQUEST['rurl']) ? $_REQUEST['rurl'] : '';

// 跳转到登录页
header('location:' . $url);
exit;
