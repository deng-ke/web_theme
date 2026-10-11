<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-10-14 13:54:18
 * @LastEditTime : 2026-06-16 12:31:10
 */

/**
 * PayPal 同步回调
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url();

if (!empty($_GET['cancel']) || empty($_GET['token'])) {
    wp_safe_redirect($return_url);
    exit;
}

$config = zibpay_get_payconfig('paypal');

// REST 新接口：token 为 PayPal Order ID，无 PayerID
$is_rest = !empty($config['rest_s']) && (empty($_GET['PayerID']) || (!empty($_GET['api']) && $_GET['api'] === 'rest'));

if ($is_rest) {
    require_once get_theme_file_path('/zibpay/sdk/paypal/rest.php');

    $order_num = !empty($_GET['order_num']) ? sanitize_text_field(wp_unslash($_GET['order_num'])) : '';
    $token     = sanitize_text_field(wp_unslash($_GET['token']));

    if ($order_num) {
        $pay_order = ZibDB::name('zibpay_payment')->where(['order_num' => $order_num, 'status' => 1])->find()->toArray();
        if ($pay_order) {
            wp_safe_redirect($return_url);
            exit;
        }
    }

    $paypal = new PayPalRest($config);
    $result = $paypal->captureOrder($token);

    if (!is_wp_error($result)) {
        $parsed = PayPalRest::parsePaymentData($result);
        if (empty($parsed['order_num']) && $order_num) {
            $parsed['order_num'] = $order_num;
        }
        if (empty($parsed['pay_num']) && !empty($result['id'])) {
            $parsed['pay_num'] = $result['id'];
        }
        if (!empty($parsed['order_num']) && !empty($parsed['pay_num'])) {
            zibpay_paypal_rest_payment_order($parsed['order_num'], $parsed['pay_num'], isset($parsed['pay_price']) ? $parsed['pay_price'] : 0);
        }
    }

    wp_safe_redirect($return_url);
    exit;
}

// 旧版 NVP 接口
require_once get_theme_file_path('/zibpay/sdk/paypal/paypal.php');
require_once get_theme_file_path('/zibpay/sdk/paypal/httprequest.php');

$pay     = new \PayPal($config);
$request = $pay->doPayment();

if (isset($request['ACK']) && $request['ACK'] == 'Success' && isset($request['TOKEN'])) {
    $order = $pay->getCheckoutDetails($request['TOKEN']);

    if (!isset($order['ACK']) || $order['ACK'] !== 'Success') {
        $err_msg = isset($order['L_LONGMESSAGE0']) ? '错误码：' . $order['L_LONGMESSAGE0'] : __('PayPal配置错误，或网络连接失败', 'zib_language');
        wp_die('PayPal收款失败<br>' . $err_msg);
    }

    $order_num = $order['INVNUM'];
    $pay_num   = $request['TRANSACTIONID'];

    $pay_order_data = array(
        'order_num' => $order_num,
        'pay_type'  => 'paypal',
        'pay_price' => isset($order['AMT']) ? $order['AMT'] : (isset($request['AMT']) ? $request['AMT'] : 0),
        'pay_num'   => $pay_num,
    );

    ZibPay::payment_order($pay_order_data);
}

wp_safe_redirect($return_url);
exit;
