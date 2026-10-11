<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 */

require_once get_theme_file_path('/oauth/sdk/apple/OAuth2.php');

@session_start();

$callbackUri = !empty($_GET['redirect_uri']) ? esc_url_raw($_GET['redirect_uri']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $callbackUri) {
    $fields = array('code', 'id_token', 'state', 'user');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Apple登录</title></head><body>';
    echo '<form id="apple-agent-form" method="post" action="' . esc_url($callbackUri) . '">';
    foreach ($fields as $field) {
        if (!empty($_POST[$field])) {
            echo '<input type="hidden" name="' . esc_attr($field) . '" value="' . esc_attr(wp_unslash($_POST[$field])) . '">';
        }
    }
    echo '</form><script>document.getElementById("apple-agent-form").submit();</script></body></html>';
    exit;
}

$agentUrl = esc_url_raw(add_query_arg('redirect_uri', $callbackUri, home_url('/oauth/appleagent')));

$authUrl = 'https://appleid.apple.com/auth/authorize?' . http_build_query(array(
    'client_id'     => !empty($_GET['client_id']) ? $_GET['client_id'] : '',
    'redirect_uri'  => $agentUrl,
    'response_type' => !empty($_GET['response_type']) ? $_GET['response_type'] : 'code',
    'response_mode' => !empty($_GET['response_mode']) ? $_GET['response_mode'] : 'form_post',
    'scope'         => !empty($_GET['scope']) ? $_GET['scope'] : 'name email',
    'state'         => !empty($_GET['state']) ? $_GET['state'] : '',
    'nonce'         => !empty($_GET['nonce']) ? $_GET['nonce'] : '',
));

wp_redirect($authUrl);
exit;
