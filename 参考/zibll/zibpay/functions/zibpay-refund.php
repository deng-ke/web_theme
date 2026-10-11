<?php
/*
 * @Description: 支付系统：官方接口原路退款（预留，本版不实现具体适配器）
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 预留：售后原路退款
 *
 * @return array{success:bool,refund_no:string,msg:string,raw:array}
 */
function zibpay_refund_request(array $args)
{
    $order = $args['order'] ?? array();
    $sdk   = zibpay_get_order_pay_sdk($order);
    $error = array(
        'success'   => false,
        'refund_no' => '',
        'msg'       => __('退款功能尚未启用', 'zib_language'),
        'raw'       => array(),
    );
    return apply_filters('zibpay_refund_' . $sdk, $error, $args);
}

/**
 * 预留：根据订单解析收款 SDK（退款阶段实现）
 */
function zibpay_get_order_pay_sdk($order)
{
    if (empty($order['pay_type'])) {
        return '';
    }
    $pay_type = $order['pay_type'];
    if ($pay_type === 'weixin' || $pay_type === 'wechat') {
        return _pz('pay_wechat_sdk_options');
    }
    if ($pay_type === 'alipay') {
        return _pz('pay_alipay_sdk_options');
    }
    if ($pay_type === 'paypal') {
        return 'paypal';
    }
    if ($pay_type === 'stripe') {
        return 'stripe';
    }
    return '';
}
