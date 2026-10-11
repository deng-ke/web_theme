<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-08-26
 */

require_once get_theme_file_path('/oauth/sdk/douyin/OAuth2.php');

@session_start();

$douyinOAuth = new \Zib\OAuthLogin\Douyin\OAuth2;
$douyinOAuth->displayLoginAgent();
