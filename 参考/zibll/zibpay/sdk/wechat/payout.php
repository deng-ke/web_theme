<?php
/*
 * @Description: 微信官方 — 企业付款到零钱
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('zibpay_payout_wechat', 'zibpay_payout_wechat_request', 10, 2);

/**
 * @param array $error
 * @param array $args
 * @return array
 */
function zibpay_payout_wechat_request($error, $args)
{
    $user_id     = (int) ($args['user_id'] ?? 0);
    $amount      = isset($args['amount']) ? (float) $args['amount'] : 0;
    $withdraw_id = (int) ($args['withdraw_id'] ?? 0);
    $desc        = !empty($args['desc']) ? $args['desc'] : __('佣金提现', 'zib_language');

    $openid = zibpay_get_user_payout_account($user_id, 'wechat');
    if (!$openid) {
        $error['msg'] = __('用户未绑定微信公众号登录', 'zib_language');
        return $error;
    }

    if ($amount <= 0) {
        $error['msg'] = __('打款金额无效', 'zib_language');
        return $error;
    }

    $config = zibpay_get_payconfig('official_wechat');
    $params = zibpay_payout_weixin_public_params();
    $sdk    = new \Yurun\PaySDK\Weixin\SDK($params);

    $out_no  = zibpay_payout_build_out_no('WD', $withdraw_id ?: $user_id);
    $request = new \Yurun\PaySDK\Weixin\CompanyPay\Weixin\Pay\Request;
    $request->mch_appid        = $config['appid'];
    $request->mchid            = $config['merchantid'];
    $request->partner_trade_no = $out_no;
    $request->openid           = $openid;
    $request->check_name       = 'NO_CHECK';
    $request->amount           = (string) zibpay_settle_price_to_fen($amount);
    $request->desc             = mb_substr($desc, 0, 80);
    $request->spbill_create_ip = zib_get_remote_ip_addr();

    try {
        $result = (array) $sdk->execute($request);
        if ($sdk->checkResult()) {
            $success = array(
                'success' => true,
                'out_no'  => $out_no,
                'msg'     => __('微信打款成功', 'zib_language'),
                'raw'     => $result,
            );
            if ($withdraw_id) {
                zibpay_payout_api_log('payout', $withdraw_id, array_merge($success, array(
                    'channel'       => 'wechat',
                    'local_amount'  => $args['local_amount'] ?? 0,
                    'settle_amount' => $amount,
                    'settle_rate'   => $args['settle_rate'] ?? 1,
                    'time'          => current_time('Y-m-d H:i:s'),
                )));
            }
            return $success;
        }
        $error['msg'] = $sdk->getError() . ' ' . $sdk->getErrorCode();
        $error['raw'] = $result;
    } catch (Exception $e) {
        $error['msg'] = $e->getMessage();
    }

    if ($withdraw_id) {
        zibpay_payout_api_log('payout', $withdraw_id, array(
            'success'       => false,
            'channel'       => 'wechat',
            'local_amount'  => $args['local_amount'] ?? 0,
            'settle_amount' => $amount,
            'settle_rate'   => $args['settle_rate'] ?? 1,
            'out_no'        => $out_no,
            'msg'           => $error['msg'],
            'raw'           => $error['raw'],
            'time'          => current_time('Y-m-d H:i:s'),
        ));
    }

    return $error;
}
