<?php
/*
 * @Description: 支付系统：官方接口 API 提现打款
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 指定渠道：后台是否开启 API 自动打款
 *
 * @param string $channel wechat|alipay|paypal
 */
function zibpay_payout_is_channel_enabled($channel)
{
    switch ($channel) {
        case 'wechat':
            return (bool) _pz('official_wechat', '', 'api_payout_s');
        case 'alipay':
            return (bool) _pz('official_alipay', '', 'api_payout_s');
        case 'paypal':
            return (bool) _pz('paypal', '', 'api_payout_s');
    }
    return false;
}

/**
 * 指定渠道官方收款配置是否齐全
 *
 * @param string $channel wechat|alipay|paypal
 */
function zibpay_payout_is_official_ready($channel)
{
    switch ($channel) {
        case 'wechat':
            if (_pz('pay_wechat_sdk_options') !== 'official_wechat') {
                return false;
            }
            $config = zibpay_get_payconfig('official_wechat');
            return !empty($config['merchantid']) && !empty($config['appid']) && !empty($config['key'])
                && zibpay_payout_resolve_cert_path($config['api_cert'] ?? '')
                && zibpay_payout_resolve_cert_path($config['api_key'] ?? '');
        case 'alipay':
            if (_pz('pay_alipay_sdk_options') !== 'official_alipay') {
                return false;
            }
            $config = zibpay_get_payconfig('official_alipay');
            return !empty($config['webappid']) && !empty($config['webprivatekey']);
        case 'paypal':
            if (!_pz('pay_paypal_sdk_s')) {
                return false;
            }
            $config = zibpay_get_payconfig('paypal');
            if (!empty($config['rest_s'])) {
                return !empty($config['client_id']) && !empty($config['client_secret']);
            }
            return !empty($config['username']) && !empty($config['password']) && !empty($config['signature']);
    }
    return false;
}

/**
 * 公众号登录 AppID 是否与微信支付 AppID 一致
 */
function zibpay_payout_wechat_appid_matched()
{
    $config = zibpay_get_payconfig('official_wechat');
    if (empty($config['appid'])) {
        return false;
    }

    //判断微信公众号登录是否开启
    if(!_pz('oauth_weixingzh_s')){
        return false;
    }

    $wx_oauth = get_oauth_config('weixingzh');
    if (empty($wx_oauth['appid'])) {
        return false;
    }
    return $wx_oauth['appid'] === $config['appid'];
}

function zibpay_payout_user_can_bind($channel)
{
    $ready = zibpay_payout_is_channel_enabled($channel) && zibpay_payout_is_official_ready($channel);
    if ($channel === 'wechat') {
        return $ready && zibpay_payout_wechat_appid_matched();
    }
    return $ready;
}

function zibpay_payout_admin_can_payout($channel)
{
    return zibpay_payout_user_can_bind($channel);
}

function zibpay_payout_has_ready_channel()
{
    foreach (array('wechat', 'alipay', 'paypal') as $channel) {
        if (zibpay_payout_user_can_bind($channel)) {
            return true;
        }
    }
    return false;
}

/**
 * 获取已就绪的 API 打款渠道列表
 *
 * @return string[]
 */
function zibpay_payout_get_ready_channels()
{
    $channels = array();
    foreach (array('wechat', 'alipay', 'paypal') as $channel) {
        if (zibpay_payout_user_can_bind($channel)) {
            $channels[] = $channel;
        }
    }
    return $channels;
}

function zibpay_payout_get_channel_name($channel)
{
    $names = array(
        'wechat' => __('微信', 'zib_language'),
        'alipay' => __('支付宝', 'zib_language'),
        'paypal' => 'PayPal',
    );
    return $names[$channel] ?? $channel;
}

/**
 * 获取打款渠道汇率
 */
function zibpay_payout_get_rate($channel)
{
    switch ($channel) {
        case 'wechat':
            return zibpay_get_wechat_settle_rate();
        case 'alipay':
            return zibpay_get_alipay_settle_rate();
        case 'paypal':
            $config = zibpay_get_payconfig('paypal');
            $rate   = isset($config['rates']) ? (float) $config['rates'] : 1;
            return $rate > 0 ? $rate : 1;
        default:
            return 1;
    }
}

/** 打款结算货币符号 */
function zibpay_payout_get_settle_mark($channel)
{
    switch ($channel) {
        case 'wechat':
            return zibpay_get_settle_mark('wechat');
        case 'alipay':
            return zibpay_get_settle_mark('alipay');
        case 'paypal':
            $config   = zibpay_get_payconfig('paypal');
            $currency = !empty($config['currency']) ? strtoupper($config['currency']) : 'USD';
            $symbols  = array('USD' => '$', 'EUR' => '€', 'GBP' => '£', 'CNY' => '￥', 'JPY' => '¥');
            return $symbols[$currency] ?? $currency;
        default:
            return '';
    }
}

/** 打款结算货币单位 */
function zibpay_payout_get_settle_unit($channel)
{
    switch ($channel) {
        case 'wechat':
            return zibpay_get_settle_unit('wechat');
        case 'alipay':
            return zibpay_get_settle_unit('alipay');
        case 'paypal':
            $config = zibpay_get_payconfig('paypal');
            return !empty($config['currency']) ? strtoupper($config['currency']) : 'USD';
        default:
            return '';
    }
}

/**
 * 本地金额换算为 API 打款结算金额
 */
function zibpay_payout_convert_local_amount($local_amount, $channel)
{
    $local_amount = zib_floatval_round($local_amount);
    switch ($channel) {
        case 'wechat':
            return zibpay_convert_for_wechat($local_amount);
        case 'alipay':
            return zibpay_convert_for_alipay($local_amount);
        case 'paypal':
            return zib_floatval_round(bcmul((string) $local_amount, (string) zibpay_payout_get_rate('paypal'), 2));
        default:
            return $local_amount;
    }
}

/** 格式化打款结算金额文案 */
function zibpay_payout_format_settle_text($amount, $channel)
{
    return zibpay_payout_get_settle_mark($channel) . zib_floatval_round($amount) . zibpay_payout_get_settle_unit($channel);
}

/** 是否需要展示汇率提示 */
function zibpay_payout_need_rate_hint($channel)
{
    if (abs(zibpay_payout_get_rate($channel) - 1) > 0.000001) {
        return true;
    }
    return zibpay_get_pay_mark() !== zibpay_payout_get_settle_mark($channel);
}

/** 汇率规则：1 本地货币 = X 结算货币 */
function zibpay_payout_get_rate_rule_text($channel)
{
    if (!$channel || !zibpay_payout_need_rate_hint($channel)) {
        return '';
    }
    return sprintf(
        '%s1=%s%s%s',
        zibpay_get_pay_mark(),
        zibpay_payout_get_settle_mark($channel),
        zibpay_payout_get_rate($channel),
        zibpay_payout_get_settle_unit($channel)
    );
}

/**
 * 指定本地金额的打款提示 HTML
 */
function zibpay_payout_get_amount_hint_html($local_amount, $channel, $channel_name = '')
{
    $local_amount = zib_floatval_round($local_amount);
    if (!$channel || $local_amount <= 0) {
        return '';
    }

    $settle_amount = zibpay_payout_convert_local_amount($local_amount, $channel);
    $prefix        = $channel_name ? esc_html($channel_name) . '：' : '';

    if (zibpay_payout_need_rate_hint($channel)) {
        return '<div class="em09 muted-2-color mt6">' . $prefix . sprintf(
            __('本地 %1$s，按汇率 %2$s 计算，实际打款 %3$s', 'zib_language'),
            esc_html(zibpay_format_local_price_text($local_amount)),
            esc_html(zibpay_payout_get_rate_rule_text($channel)),
            esc_html(zibpay_payout_format_settle_text($settle_amount, $channel))
        ) . '</div>';
    }

    return '<div class="em09 muted-2-color mt6">' . $prefix . sprintf(
        __('实际打款 %s', 'zib_language'),
        esc_html(zibpay_payout_format_settle_text($settle_amount, $channel))
    ) . '</div>';
}

/**
 * 收款设置：各渠道汇率说明（无具体金额）
 */
function zibpay_payout_get_channel_rate_hint_html($channel)
{
    if (!$channel || !zibpay_payout_user_can_bind($channel)) {
        return '';
    }

    if (!zibpay_payout_need_rate_hint($channel)) {
        return '<div class="em09 muted-2-color mt6">' . sprintf(
            __('打款币种与站内定价一致（%s）', 'zib_language'),
            esc_html(zibpay_format_local_price_text(1))
        ) . '</div>';
    }

    return '<div class="em09 muted-2-color mt6">' . sprintf(
        __('打款将按汇率 %1$s 折算', 'zib_language'),
        esc_html(zibpay_payout_get_rate_rule_text($channel)),
        esc_html(zibpay_payout_get_settle_mark($channel))
    ) . '</div>';
}

/**
 * 证书/私钥：支持附件 ID、URL、本地绝对路径
 */
function zibpay_payout_resolve_cert_path($cert)
{
    if (!$cert) {
        return '';
    }
    if (is_numeric($cert)) {
        $path = get_attached_file((int) $cert);
        return ($path && file_exists($path)) ? $path : '';
    }
    if (is_string($cert) && strpos($cert, 'http') === 0) {
        $id = attachment_url_to_postid($cert);
        if ($id) {
            $path = get_attached_file($id);
            return ($path && file_exists($path)) ? $path : '';
        }
    }
    return (is_string($cert) && file_exists($cert)) ? $cert : '';
}

/**
 * 微信支付公共参数
 */
function zibpay_payout_weixin_public_params()
{
    $config = zibpay_get_payconfig('official_wechat');
    $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
    $params->appID  = $config['appid'];
    $params->mch_id = $config['merchantid'];
    $params->key    = $config['key'];

    $cert_path = zibpay_payout_resolve_cert_path($config['api_cert'] ?? '');
    $key_path  = zibpay_payout_resolve_cert_path($config['api_key'] ?? '');
    if ($cert_path) {
        $params->certPath = $cert_path;
    }
    if ($key_path) {
        $params->keyPath = $key_path;
    }

    return $params;
}

function zibpay_payout_build_out_no($prefix, $biz_id)
{
    return $prefix . $biz_id . date('YmdHis') . mt_rand(100, 999);
}

/**
 * 记录 API 调用日志（提现写入 ZibMsg meta）
 */
function zibpay_payout_api_log($scene, $biz_id, array $data)
{
    if ($scene === 'payout' && $biz_id) {
        ZibMsg::set_meta((int) $biz_id, 'api_payout', $data);
    }
}

function zibpay_payout_mask_account($account, $channel = '')
{
    if (!$account) {
        return '';
    }
    if ($channel === 'wechat' && strlen($account) > 8) {
        return substr($account, 0, 4) . '****' . substr($account, -4);
    }
    if (strpos($account, '@') !== false) {
        $parts = explode('@', $account);
        $name  = $parts[0];
        $mask  = strlen($name) > 2 ? substr($name, 0, 2) . '***' : $name . '***';
        return $mask . '@' . $parts[1];
    }
    if (strlen($account) > 7) {
        return substr($account, 0, 3) . '****' . substr($account, -4);
    }
    return $account;
}

/**
 * 读取用户 API 打款账户
 */
function zibpay_get_user_payout_account($user_id, $channel)
{
    $user_id = (int) $user_id;
    if (!$user_id) {
        return '';
    }

    switch ($channel) {
        case 'wechat':
            if (!zibpay_payout_wechat_appid_matched()) {
                return '';
            }
            return (string) get_user_meta($user_id, 'oauth_weixingzh_openid', true);
        case 'alipay':
            return (string) zib_get_user_meta($user_id, 'rewards_alipay_account', true);
        case 'paypal':
            return (string) zib_get_user_meta($user_id, 'rewards_paypal_email', true);
    }
    return '';
}

function zibpay_user_has_payout_account($user_id, $channel = '')
{
    if ($channel) {
        return (bool) zibpay_get_user_payout_account($user_id, $channel);
    }
    foreach (zibpay_payout_get_ready_channels() as $_channel) {
        if (zibpay_get_user_payout_account($user_id, $_channel)) {
            return true;
        }
    }
    return false;
}

/**
 * 用户是否已绑定任一就绪渠道的 API 账户
 */
function zibpay_user_has_ready_payout_account($user_id)
{
    foreach (zibpay_payout_get_ready_channels() as $channel) {
        if (zibpay_get_user_payout_account($user_id, $channel)) {
            return true;
        }
    }
    return false;
}

/**
 * 发起 API 打款
 *
 * @return array{success:bool,out_no:string,msg:string,raw:array}
 */
function zibpay_payout_request(array $args)
{
    $channel = $args['channel'] ?? '';
    $error   = array(
        'success' => false,
        'out_no'  => '',
        'msg'     => __('不支持的打款渠道', 'zib_language'),
        'raw'     => array(),
    );

    if (!$channel || !zibpay_payout_admin_can_payout($channel)) {
        $error['msg'] = __('该渠道未开启 API 打款或配置不完整', 'zib_language');
        return $error;
    }

    $withdraw_id = (int) ($args['withdraw_id'] ?? 0);
    if ($withdraw_id) {
        $msg_row = (array) ZibMsg::get_row(array('id' => $withdraw_id, 'type' => 'withdraw'));
        if (!empty($msg_row['meta']['api_payout']['success'])) {
            return array(
                'success' => true,
                'out_no'  => $msg_row['meta']['api_payout']['out_no'] ?? '',
                'msg'     => __('该提现已打款成功', 'zib_language'),
                'raw'     => $msg_row['meta']['api_payout']['raw'] ?? array(),
            );
        }
    }

    $local_amount         = isset($args['local_amount']) ? (float) $args['local_amount'] : (isset($args['amount']) ? (float) $args['amount'] : 0);
    $args['local_amount'] = zib_floatval_round($local_amount);
    $args['amount']       = zibpay_payout_convert_local_amount($local_amount, $channel);
    $args['settle_rate']  = zibpay_payout_get_rate($channel);

    return apply_filters('zibpay_payout_' . $channel, $error, $args);
}

require_once get_theme_file_path('zibpay/sdk/wechat/payout.php');
require_once get_theme_file_path('zibpay/sdk/alipay/payout.php');
require_once get_theme_file_path('zibpay/sdk/paypal/rest.php');
