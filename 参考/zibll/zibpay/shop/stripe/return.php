<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2026-06-15
 */

/**
 * Stripe 同步回调
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url();

if (!empty($_GET['cancel']) || empty($_GET['session_id'])) {
    wp_safe_redirect($return_url);
    exit;
}

$config = zibpay_get_payconfig('stripe');
if (empty($config['secret_key'])) {
    wp_safe_redirect($return_url);
    exit;
}

$order_num  = !empty($_GET['order_num']) ? sanitize_text_field(wp_unslash($_GET['order_num'])) : '';
$session_id = sanitize_text_field(wp_unslash($_GET['session_id']));

if ($order_num) {
    $pay_order = ZibDB::name('zibpay_payment')->where(['order_num' => $order_num, 'status' => 1])->find()->toArray();
    if ($pay_order) {
        wp_safe_redirect($return_url);
        exit;
    }
}

require_once get_theme_file_path('/zibpay/sdk/stripe/checkout.php');

$stripe  = new StripeCheckout($config);
$session = $stripe->retrieveSession($session_id);

if (!is_wp_error($session) && !empty($session['payment_status']) && $session['payment_status'] === 'paid') {
    $parsed = StripeCheckout::parsePaymentData($session);
    if (empty($parsed['order_num']) && $order_num) {
        $parsed['order_num'] = $order_num;
    }
    if (empty($parsed['pay_num']) && !empty($session['id'])) {
        $parsed['pay_num'] = $session['id'];
    }
    if (!empty($parsed['order_num']) && !empty($parsed['pay_num'])) {
        zibpay_stripe_payment_order($parsed['order_num'], $parsed['pay_num'], isset($parsed['pay_price']) ? $parsed['pay_price'] : 0);
    }
}

wp_safe_redirect($return_url);
exit;
