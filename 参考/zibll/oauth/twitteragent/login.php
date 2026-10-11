<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/twitter/OAuth2.php');

@session_start();

$twitterOAuth = new \Zib\OAuthLogin\Twitter\OAuth2;
$twitterOAuth->displayLoginAgent();
