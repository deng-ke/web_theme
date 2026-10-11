<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/facebook/OAuth2.php');

@session_start();

$facebookOAuth = new \Zib\OAuthLogin\Facebook\OAuth2;
$facebookOAuth->displayLoginAgent();
