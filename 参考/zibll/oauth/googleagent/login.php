<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-06-15
 */

require_once get_theme_file_path('/oauth/sdk/google/OAuth2.php');

//启用 session
@session_start();

$googleOAuth = new \Zib\OAuthLogin\Google\OAuth2;
$googleOAuth->displayLoginAgent();
