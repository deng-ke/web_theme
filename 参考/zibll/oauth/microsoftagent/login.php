<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/microsoft/OAuth2.php');

@session_start();

$microsoftOAuth = new \Zib\OAuthLogin\Microsoft\OAuth2;
$microsoftOAuth->displayLoginAgent();
