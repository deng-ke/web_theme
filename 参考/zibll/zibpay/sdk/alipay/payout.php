<?php
/*
 * @Description: 支付宝官方 — 单笔转账到支付宝账户
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('zibpay_payout_alipay', 'zibpay_payout_alipay_request', 10, 2);

/**
 * @param array $error
 * @param array $args
 * @return array
 */
function zibpay_payout_alipay_request($error, $args)
{
    $user_id     = (int) ($args['user_id'] ?? 0);
    $amount      = isset($args['amount']) ? (float) $args['amount'] : 0;
    $withdraw_id = (int) ($args['withdraw_id'] ?? 0);
    $desc        = !empty($args['desc']) ? $args['desc'] : __('佣金提现', 'zib_language');

    $account = zibpay_get_user_payout_account($user_id, 'alipay');
    if (!$account) {
        $error['msg'] = __('用户未绑定支付宝收款账号', 'zib_language');
        return $error;
    }

    if ($amount < 0.1) {
        $error['msg'] = __('打款金额不能低于0.1元', 'zib_language');
        return $error;
    }

    $config = zibpay_get_payconfig('official_alipay');
    $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
    $params->appID         = $config['webappid'];
    $params->appPrivateKey = $config['webprivatekey'];
    $params->appPublicKey  = $config['publickey'];

    $sdk     = new \Yurun\PaySDK\AlipayApp\SDK($params);
    $request = new \Yurun\PaySDK\AlipayApp\Fund\Transfer\Request;

    $out_no = zibpay_payout_build_out_no('WD', $withdraw_id ?: $user_id);

    $request->businessParams->out_biz_no      = $out_no;
    $request->businessParams->payee_type      = strpos($account, '@') !== false || preg_match('/^1\d{10}$/', $account) ? 'ALIPAY_LOGONID' : 'ALIPAY_LOGONID';
    $request->businessParams->payee_account   = $account;
    $request->businessParams->amount          = (string) zib_floatval_round($amount, 2);
    $request->businessParams->payer_show_name = get_bloginfo('name');
    $request->businessParams->remark          = mb_substr($desc, 0, 100);

    try {
        $result = (array) $sdk->execute($request);
        $body   = !empty($result['alipay_fund_trans_toaccount_transfer_response']) ? $result['alipay_fund_trans_toaccount_transfer_response'] : $result;

        if (!empty($body['code']) && $body['code'] === '10000') {
            $success = array(
                'success' => true,
                'out_no'  => $out_no,
                'msg'     => __('支付宝打款成功', 'zib_language'),
                'raw'     => $body,
            );
            if ($withdraw_id) {
                zibpay_payout_api_log('payout', $withdraw_id, array_merge($success, array(
                    'channel'       => 'alipay',
                    'local_amount'  => $args['local_amount'] ?? 0,
                    'settle_amount' => $amount,
                    'settle_rate'   => $args['settle_rate'] ?? 1,
                    'time'          => current_time('Y-m-d H:i:s'),
                )));
            }
            return $success;
        }

        $error['msg'] = !empty($body['sub_msg']) ? $body['sub_msg'] : (!empty($body['msg']) ? $body['msg'] : __('支付宝打款失败', 'zib_language'));
        $error['raw'] = $body;
    } catch (Exception $e) {
        $error['msg'] = $e->getMessage();
    }

    if ($withdraw_id) {
        zibpay_payout_api_log('payout', $withdraw_id, array(
            'success'       => false,
            'channel'       => 'alipay',
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
