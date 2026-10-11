<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-06-15
 */

/**
 * PayPal REST 异步回调（Webhook）
 */

header('Content-Type: text/plain; charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$config = zibpay_get_payconfig('paypal');
if (empty($config['rest_s'])) {
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

require_once get_theme_file_path('/zibpay/sdk/paypal/rest.php');

$paypal  = new PayPalRest($config);
$headers = function_exists('getallheaders') ? getallheaders() : array();
$verify  = $paypal->verifyWebhook($headers, $raw_body);

if (is_wp_error($verify)) {
    http_response_code(400);
    echo 'verify_fail';
    exit;
}

$event = json_decode($raw_body, true);
if (!is_array($event) || empty($event['event_type'])) {
    http_response_code(400);
    echo 'invalid';
    exit;
}

$allowed_events = array(
    'PAYMENT.CAPTURE.COMPLETED',
    'CHECKOUT.ORDER.COMPLETED',
);

if (!in_array($event['event_type'], $allowed_events, true)) {
    http_response_code(200);
    echo 'ignored';
    exit;
}

$resource = !empty($event['resource']) ? $event['resource'] : array();
$parsed   = PayPalRest::parsePaymentData($resource);

if ($event['event_type'] === 'CHECKOUT.ORDER.COMPLETED' && empty($parsed['pay_num']) && !empty($resource['id'])) {
    $parsed['pay_num'] = $resource['id'];
}

if (empty($parsed['order_num'])) {
    http_response_code(400);
    echo 'no_order';
    exit;
}

if (empty($parsed['pay_num'])) {
    $parsed['pay_num'] = !empty($resource['id']) ? $resource['id'] : $parsed['order_num'];
}

$result = zibpay_paypal_rest_payment_order($parsed['order_num'], $parsed['pay_num'], isset($parsed['pay_price']) ? $parsed['pay_price'] : 0);
if ($result === false) {
    http_response_code(400);
    echo 'fail';
    exit;
}

http_response_code(200);
echo 'success';
exit;
