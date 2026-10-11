<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-06-15
 */

/**
 * Stripe 异步回调（Webhook）
 */

header('Content-Type: text/plain; charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$config = zibpay_get_payconfig('stripe');
if (empty($config['secret_key'])) {
    http_response_code(400);
    echo 'disabled';
    exit;
}

$raw_body = file_get_contents('php://input');
if (!$raw_body) {
    http_response_code(400);
    echo 'empty';
    exit;
}

require_once get_theme_file_path('/zibpay/sdk/stripe/checkout.php');

$stripe     = new StripeCheckout($config);
$sig_header = !empty($_SERVER['HTTP_STRIPE_SIGNATURE']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_STRIPE_SIGNATURE'])) : '';
$verify     = $stripe->verifyWebhook($raw_body, $sig_header);

if (is_wp_error($verify)) {
    http_response_code(400);
    echo 'verify_fail';
    exit;
}

$event = json_decode($raw_body, true);
if (!is_array($event) || empty($event['type'])) {
    http_response_code(400);
    echo 'invalid';
    exit;
}

if ($event['type'] !== 'checkout.session.completed') {
    http_response_code(200);
    echo 'ignored';
    exit;
}

$session = !empty($event['data']['object']) ? $event['data']['object'] : array();
if (empty($session['payment_status']) || $session['payment_status'] !== 'paid') {
    http_response_code(200);
    echo 'ignored';
    exit;
}

$parsed = StripeCheckout::parsePaymentData($session);
if (empty($parsed['order_num'])) {
    http_response_code(400);
    echo 'no_order';
    exit;
}

if (empty($parsed['pay_num'])) {
    $parsed['pay_num'] = !empty($session['id']) ? $session['id'] : $parsed['order_num'];
}

$result = zibpay_stripe_payment_order($parsed['order_num'], $parsed['pay_num'], isset($parsed['pay_price']) ? $parsed['pay_price'] : 0);
if ($result === false) {
    http_response_code(400);
    echo 'fail';
    exit;
}

http_response_code(200);
echo 'success';
exit;
