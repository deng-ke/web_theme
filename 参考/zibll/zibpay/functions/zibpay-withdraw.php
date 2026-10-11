<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-04-17 17:49:02
 * @LastEditTime : 2026-08-25 12:24:44
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|支付系统：提现功能 withdraw
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//获取提现按钮
function zibpay_get_withdraw_link($class = 'but radius c-white', $con = null)
{
    if (null === $con) {
        $con = __('立即提现', 'zib_language') . '<i style="margin:0 0 0 10px;" class="fa fa-angle-right"></i>';
    }

    $args = array(
        'class'         => $class,
        'data_class'    => 'full-sm',
        'mobile_bottom' => true,
        'height'        => 386,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'apply_withdraw_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取提现记录按钮
function zibpay_get_withdraw_record_link($class = 'but', $con = null)
{
    if (null === $con) {
        $con = __('查看提现记录', 'zib_language') . '<i style="margin:0 0 0 6px;" class="fa fa-angle-right"></i>';
    }

    $args = array(
        'class'         => $class,
        'data_class'    => 'full-sm',
        'mobile_bottom' => true,
        'height'        => 386,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'withdraw_record_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 用户提现记录明细
 * @param {*} $user_id
 * @param {*} $ice_perpage
 * @return {*}
 */
function zibpay_get_withdraw_record_lists($user_id = 0, $ice_perpage = 10)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }
    $paged  = zib_get_the_paged();
    $offset = $ice_perpage * ($paged - 1);

    $offset = $ice_perpage * ($paged - 1);

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'withdraw',
    );
    if (isset($_REQUEST['status'])) {
        $msg_get_args['status'] = $_REQUEST['status'];
    }

    $db_msg    = ZibMsg::get($msg_get_args, 'modified_time', $offset, $ice_perpage);
    $count_all = ZibMsg::get_count($msg_get_args);
    $lists     = '';
    if ($db_msg) {
        foreach ($db_msg as $msg) {
            //准备参数
            $meta        = maybe_unserialize($msg->meta);
            $price       = $meta['withdraw_price'];
            $create_time = date('Y-m-d H:i', strtotime($msg->create_time));
            $status      = '<span class="badg c-yellow mr10 em09">' . __('待处理', 'zib_language') . '</span>';
            if ($msg->status == 1) {
                $status = '<span class="badg c-blue mr10 em09">' . __('已提现', 'zib_language') . '</span>';
            } elseif ($msg->status == 2) {
                $status = '<span class="badg c-red mr10 em09">' . __('已拒绝', 'zib_language') . '</span>';
            }

            $__withdraw_price = $meta['withdraw_price'];
            $__service_price  = isset($meta['service_price']) ? $meta['service_price'] : 0;

            $__rebate_sum  = isset($meta['withdraw_detail']['rebate']) ? $meta['withdraw_detail']['rebate'] : 0;
            $__income_sum  = isset($meta['withdraw_detail']['income']) ? $meta['withdraw_detail']['income'] : 0;
            $__balance_sum = isset($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;

            $withdraw_details = '' . ($__rebate_sum ? sprintf(__('推广佣金%s', 'zib_language'), zibpay_format_local_price($__rebate_sum)) : '') . ($__income_sum ? ' ' . sprintf(__('创作分成%s', 'zib_language'), zibpay_format_local_price($__income_sum)) : '') . ($__balance_sum > 0 ? ' ' . sprintf(__('余额%s', 'zib_language'), zibpay_format_local_price($__balance_sum)) : '') . ($__balance_sum < 0 ? ' ' . sprintf(__('其中%s转入余额', 'zib_language'), zibpay_format_local_price(abs($__balance_sum))) : '') . '';
            //折叠
            $mag_collapse = '';
            $mag_collapse .= '<div id="msg_collapse_' . $msg->id . '" class="collapse ml6">';
            $mag_collapse .= '<div class="muted-3-color em09">';
            $mag_collapse .= '<div class="mt10">' . __('提现金额：', 'zib_language') . ' <span class="ml10">' . zibpay_format_local_price($__withdraw_price) . '</span></div>';
            $mag_collapse .= $withdraw_details ? '<div class="mt10">' . __('提现详情：', 'zib_language') . ' <span class="ml10">' . $withdraw_details . '</span></div>' : '';
            $mag_collapse .= '<div class="mt10">' . __('申请时间：', 'zib_language') . ' <span class="ml10">' . $msg->create_time . '</span></div>';
            $mag_collapse .= !empty($meta['withdraw_message']) ? '<div class="mt10">' . __('申请留言：', 'zib_language') . ' <span class="ml10">' . $meta['withdraw_message'] . '</span></div>' : '';
            if ($msg->status) {
                $mag_collapse .= '<div class="mt10">' . __('处理结果：', 'zib_language') . ' <span class="ml10">' . $status . '</span></div>';
                if ($msg->status == 1) {
                    $mag_collapse .= '<div class="mt10">' . __('支付金额：', 'zib_language') . ' <span class="ml10">' . ($__service_price > 0 ? '<span class="badg c-red mr6">' . zibpay_format_local_price($__withdraw_price - $__service_price) . '</span><span class="badg">' . sprintf(__('手续费%s', 'zib_language'), zibpay_format_local_price($__service_price)) . '</span>' : '<span class="badg c-red mr6">' . zibpay_format_local_price($__withdraw_price) . '</span>') . '</span></div>';
                }
                $mag_collapse .= '<div class="mt10">' . __('处理时间：', 'zib_language') . ' <span class="ml10">' . $msg->modified_time . '</span></div>';
                $mag_collapse .= !empty($meta['admin_message']) ? '<div class="mt10">' . __('处理反馈：', 'zib_language') . ' <span class="ml10">' . $meta['admin_message'] . '</span></div>' : '';
            }
            $mag_collapse .= '</div>';
            $mag_collapse .= '</div>';

            //开始构建列表
            $lists .= '<div class="ajax-item border-bottom" style="padding:8px 0;">';
            $lists .= '<div data-toggle="collapse" data-target="#msg_collapse_' . $msg->id . '" class="collapsed pointer meta-time muted-color flex ac jsb"><div class="flex ac em09-sm">' . $status . $create_time . '</div><div class="em12">' . zibpay_format_local_price($price) . '<i class="fa fa-angle-down ml10"></i></div></div>';
            $lists .= $mag_collapse;
            $lists .= '</div>';

            $ajax_url = esc_url(add_query_arg('action', 'withdraw_detail', admin_url('admin-ajax.php')));
        }
        $lists .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no');
    } else {
        $lists .= zib_get_ajax_null(__('暂无提现记录', 'zib_language'), 60, 'null-order.svg');
    }

    return $lists;
}

/**
 * @description: 获取用户提现记录的明细模态框
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_withdraw_record_modal($user_id)
{
    if (!$user_id) {
        return;
    }

    //提现记录AJAX tab-content
    $withdraw_ajax_href = esc_url(add_query_arg('action', 'withdraw_detail', admin_url('admin-ajax.php')));

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'withdraw',
    );
    $withdraw_count_all     = ZibMsg::get_count($msg_get_args);
    $msg_get_args['status'] = 1;
    $withdraw_count_1       = ZibMsg::get_count($msg_get_args);

    if (!$withdraw_count_all) {
        return zib_get_null(__('暂无提现记录', 'zib_language'), 40, 'null-money.svg');
    }

    //过滤
    $filter = '<div class="mb10">';
    $filter .= '<a ajax-replace="1" no-scroll="1" ajax-href="' . $withdraw_ajax_href . '" class="but mr10 ajax-next">' . sprintf(__('全部 %s', 'zib_language'), $withdraw_count_all) . '</a>';
    $filter .= '<a ajax-replace="1" no-scroll="1" ajax-href="' . add_query_arg('status', 1, $withdraw_ajax_href) . '" class="but mr10 ajax-next">' . sprintf(__('已处理 %s', 'zib_language'), $withdraw_count_1) . '</a>';
    $filter .= '<a ajax-replace="1" no-scroll="1" ajax-href="' . add_query_arg('status', 0, $withdraw_ajax_href) . '" class="but mr10 ajax-next">' . __('待处理', 'zib_language') . '</a>';
    $filter .= '</div>';

    $lists = zibpay_get_withdraw_record_lists($user_id);
    $lists .= '<div class="post_ajax_loader" style="display: none;"><i class="placeholder s1 mt10" style="height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i><i class="placeholder s1 mt10" style="height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i><i class="placeholder s1 mt10" style="height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i> <i class="placeholder s1 mt10" style=" height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i> <i class="placeholder s1 mt10" style=" height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i></div>';

    $header = '<div class="mb20 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-jpy"></i></span>' . __('提现记录', 'zib_language') . '</b></div>';

    return $header . '<div class="ajaxpager">' . $filter . '<div class="mini-scrollbar scroll-y max-vh5">' . $lists . '</div></div>';
}

/**
 * @description: 用户提现的模态框
 * @param {*}
 * @return {*}
 */
function zibpay_get_apply_withdraw_modal()
{

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $text_details   = _pz('pay_rebate_withdraw_text_details'); //文案
    $lowest_money   = (int) _pz('pay_rebate_withdraw_lowest_money'); //提现限制
    $service_charge = _pz('withdraw_service_charge'); //提现手续费
    if ($service_charge) {
        $text_details .= '<div class="mt6 c-yellow">' . sprintf(__('向您付款时会扣除%s%%的手续费', 'zib_language'), $service_charge) . '</div>';
    }

    if (zibpay_payout_has_ready_channel()) {
        $rate_hints = '';
        foreach (zibpay_payout_get_ready_channels() as $_channel) {
            $rule = zibpay_payout_get_rate_rule_text($_channel);
            if ($rule) {
                $rate_hints .= '<div class="em09">' . esc_html(zibpay_payout_get_channel_name($_channel)) . '：' . esc_html($rule) . '</div>';
            }
        }
        if ($rate_hints) {
            $text_details .= '<div class="mt6 muted-2-color">' . __('付款时将按以下汇率折算实际付款金额', 'zib_language') . $rate_hints . '</div>';
        }
    }
    $withdraw_ing = (array) zibpay_get_user_withdraw_ing($user_id);
    if (!empty($withdraw_ing['meta']['withdraw_price'])) {
        $header = zib_get_modal_colorful_header('jb-yellow', zib_get_svg('money'), __('提现正在处理中', 'zib_language'));

        $__withdraw_price = $withdraw_ing['meta']['withdraw_price'];
        $__service_price  = isset($withdraw_ing['meta']['service_price']) ? $withdraw_ing['meta']['service_price'] : 0;

        $__rebate_sum  = isset($withdraw_ing['meta']['withdraw_detail']['rebate']) ? $withdraw_ing['meta']['withdraw_detail']['rebate'] : 0;
        $__income_sum  = isset($withdraw_ing['meta']['withdraw_detail']['income']) ? $withdraw_ing['meta']['withdraw_detail']['income'] : 0;
        $__balance_sum = isset($withdraw_ing['meta']['withdraw_detail']['balance']) ? $withdraw_ing['meta']['withdraw_detail']['balance'] : 0;

        $table_lists = '';
        $table_lists .= '<tr><th>' . __('提现金额', 'zib_language') . '</th><td>' . zibpay_format_local_price($__withdraw_price) . zibpay_get_currency_unit() . '</td><td class="px12">' . ($__rebate_sum ? sprintf(__('推广佣金%s', 'zib_language'), zibpay_format_local_price($__rebate_sum)) . '<br>' : '') . ($__income_sum ? sprintf(__('创作分成%s', 'zib_language'), zibpay_format_local_price($__income_sum)) . '<br>' : '') . ($__balance_sum > 0 ? sprintf(__('余额%s', 'zib_language'), zibpay_format_local_price($__balance_sum)) : '') . ($__balance_sum < 0 ? sprintf(__('其中%s转入余额', 'zib_language'), zibpay_format_local_price(abs($__balance_sum))) : '') . '</td></tr>';
        $table_lists .= $__service_price > 0 ? '<tr class="c-blue"><th>' . __('付款金额', 'zib_language') . '</th><td>' . zibpay_format_local_price($__withdraw_price - $__service_price) . zibpay_get_currency_unit() . '</td><td class="px12">' . sprintf(__('扣除手续费%s', 'zib_language'), zibpay_format_local_price($__service_price)) . '</td></tr>' : '';
        $table_lists .= '<tr><th>' . __('提交时间', 'zib_language') . '</th><td>' . $withdraw_ing['create_time'] . '</td><td></td></tr>';
        $table = '<table class="table table-bordered table-mini"><tbody class="muted-color">' . $table_lists . '</tbody></table>';

        $html = $header;
        $html .= '<div class="c-red mb10">' . __('您的提现正在处理中，请您耐心等待', 'zib_language') . '</div>';
        $html .= $table;
        $html .= '<div class="muted-color mb20">' . $text_details . '</div>';
        return $html;
    }

    //开始构建
    $all_effective_sum = 0;
    $lists             = '';
    $hidden            = '';

    //推广返佣
    $pay_rebate_s = _pz('pay_rebate_s');
    if ($pay_rebate_s) {
        $rebate_effective_data = zibpay_get_user_rebate_data($user_id, 'effective'); //佣金统计
        $all_effective_sum += $rebate_effective_data['sum'];
        $hidden .= '<input type="hidden" name="rebate_ids" value="' . $rebate_effective_data['ids'] . '">';
        $lists .= $rebate_effective_data['sum'] ? '<tr class="em09"><td>' . __('推广佣金', 'zib_language') . '</td><td>' . zibpay_get_pay_mark() . $rebate_effective_data['sum'] . '</td><td>' . sprintf(__('共%s笔佣金订单', 'zib_language'), $rebate_effective_data['count']) . '</td></tr>' : '';
    }

    //收入分成
    $pay_income_s = _pz('pay_income_s');
    if ($pay_income_s) {
        $income_price_effective = zibpay_get_user_income_data($user_id, 'effective'); //分成统计
        $all_effective_sum += $income_price_effective['sum'];
        $hidden .= '<input type="hidden" name="income_ids" value="' . $income_price_effective['ids'] . '">';
        $lists .= $income_price_effective['sum'] ? '<tr class="em09"><td>' . __('创作分成', 'zib_language') . '</td><td>' . zibpay_get_pay_mark() . $income_price_effective['sum'] . '</td><td>' . sprintf(__('共%s笔分成订单', 'zib_language'), $income_price_effective['count']) . '</td></tr>' : '';
    }

    //余额功能
    $pay_balance_s          = _pz('pay_balance_s');
    $pay_balance_withdraw_s = _pz('pay_balance_withdraw_s'); //允许将余额提现
    if ($pay_balance_s && $pay_balance_withdraw_s) {
        $user_balance = zibpay_get_user_balance($user_id); //余额统计
        $all_effective_sum += $user_balance;
        $lists .= $user_balance ? '<tr class="em09"><td>' . __('余额', 'zib_language') . '</td><td>' . zibpay_get_pay_mark() . $user_balance . '</td><td></td></tr>' : 0;
    }

    if (!$all_effective_sum) {
        $header = zib_get_modal_colorful_header('jb-blue', zib_get_svg('money'), __('提现申请', 'zib_language'));
        return $header . zib_get_null(__('暂无可提现的金额', 'zib_language'), 20, 'null-money.svg', '', 280, 150);
    }

    $html = '';
    $but  = '';
    $lists .= '<tr class="c-blue"><th>' . __('合计', 'zib_language') . '</th><td>' . zibpay_get_pay_mark() . $all_effective_sum . '</td><td></td></tr>';
    $hidden .= '<input type="hidden" name="effective_sum" value="' . $all_effective_sum . '">
                <input type="hidden" name="user_id" value="' . $user_id . '">
                <input type="hidden" name="action" value="apply_withdraw">';
    $hidden .= wp_nonce_field('apply_withdraw', '_wpnonce', false, false); //安全效验

    $table          = '<table class="table table-bordered table-mini"><thead class=""><tr><th>' . __('类型', 'zib_language') . '</th><th>' . __('金额', 'zib_language') . '</th><th>' . __('描述', 'zib_language') . '</th></tr></thead><tbody class="muted-color">' . $lists . '</tbody></table>';
    $collection_set = zib_get_user_withdraw_collection_set_link('but c-yellow mr10 rewards-tabshow padding-lg', __('收款设置', 'zib_language'), true);

    if (!zibpay_user_has_withdraw_collection($user_id)) {
        $but .= '<div class="c-red mb20">' . __('您还未绑定收款账户，请先完成收款设置', 'zib_language') . '</div>';
        $but .= '<div class="modal-buts but-average">' . $collection_set . '<div>';
    } elseif ($all_effective_sum >= $lowest_money) {
        if ($pay_balance_s) {
            //余额功能开启，可选部分金额提现
            $table .= '<div class="em09 muted-2-color mb6">' . __('提现金额', 'zib_language') . '</div>';
            $withdraw_limit_tips = sprintf(__('最低提现%s，最高提现%s', 'zib_language'), zibpay_format_local_price((int) $lowest_money), zibpay_format_local_price((int) $all_effective_sum));
            if ($pay_rebate_s || $pay_balance_s) {
                $fund_type_label = ($pay_rebate_s ? __('佣金', 'zib_language') : '') . ($pay_balance_s ? __('分成', 'zib_language') : '');
                $withdraw_limit_tips .= sprintf(__('，如果提现金额低于%1$s总金额，剩余%1$s则会全部转入余额', 'zib_language'), $fund_type_label);
            }
            if (!$pay_balance_withdraw_s) {
                $withdraw_limit_tips .= '<span class="c-red">' . __('，转入余额后的金额将无法提现，建议您全额提现', 'zib_language') . '</span>';
            }
            $table .= '<div class="dependency-box mb20">
                        <div>
                            <label class="badg mr10 pointer"><input type="radio" checked="checked" name="withdraw_money_type" value="all"> ' . sprintf(__('全额提现 %1$s%2$s', 'zib_language'), zibpay_get_pay_mark(), $all_effective_sum) . '</label>
                            <label class="badg mr10 pointer"><input type="radio" name="withdraw_money_type" value="custom"> ' . __('提现部分金额', 'zib_language') . '</label>
                        </div>
                        <div style="display: none;" data-controller="withdraw_money_type" data-condition="==" data-value="custom">
                        <div class="em09 muted-2-color mt6">' . __('输入金额', 'zib_language') . '</div>
                        <div class="relative flex ab">
                            <span class="ml6 mr10 muted-color">' . zibpay_get_pay_mark() . '</span>
                            <input name="custom_money" type="number" limit-min="' . $lowest_money . '" limit-max="' . (int) $all_effective_sum . '"  warning-max="' . esc_attr(sprintf(__('最高可提现1$%s', 'zib_language'), zibpay_get_currency_unit())) . '" warning-min="' . esc_attr(sprintf(__('最低需提现1$%s', 'zib_language'), zibpay_get_currency_unit())) . '" style="padding: 0;" class="line-form-input em16 key-color">
                            <i class="line-form-line"></i>
                        </div>
                        <div class="em09 c-yellow mt3">' . $withdraw_limit_tips . '</div>
                        </div>
                    </div>';
        }

        $but .= $collection_set . $hidden;
        $but .= '<button type="button" zibajax="submit" class="but c-blue padding-lg">' . __('提交申请', 'zib_language') . '</button>';
        $but = '<div class="mr6 mb20"><input type="text" name="message" placeholder="' . esc_attr__('给客服留言', 'zib_language') . '" class="form-control"></div><div class="modal-buts but-average">' . $but . '<div>';
    } else {
        $but .= '<div class="c-red mb20">' . sprintf(__('您当前的可提现金额低于%s，暂时不能申请提现', 'zib_language'), zibpay_format_local_price($lowest_money)) . '</div>';
        $but .= '<div class="modal-buts but-average">' . $collection_set . '<div>';
    }

    $header = '<div class="mb20 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-jpy"></i></span>' . __('提现申请', 'zib_language') . '</b></div>';
    $html .= $header;
    $html .= '<div class="muted-box muted-2-color mb20">' . $text_details . '</div>';
    $html .= '<form>' . $table . $but . '</form>';

    return $html;

}

function zib_get_user_withdraw_collection_set_link($class = '', $text = null, $new_modal = false)
{
    if (null === $text) {
        $text = __('收款设置', 'zib_language');
    }
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => 'withdraw-collection-set-link ' . $class,
        'mobile_bottom' => true,
        'data_class'    => 'full-sm',
        'height'        => 423,
        'text'          => $text,
        'query_arg'     => array(
            'action' => 'user_withdraw_collection_set_modal',
        ),
    );

    if ($new_modal) {
        $args['new'] = true;
    }

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取用户正在提现的订单
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_withdraw_ing($user_id)
{

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'withdraw',
        'status'    => 0,
    );
    return ZibMsg::get_row($msg_get_args);
}

/**
 * @description: 设置订单的提现状态为正在提现
 * @param {*}
 * @return {*}
 */
function zibpay_withdraw_order_set_ing($type = 'rebate', $ids = array())
{
    global $wpdb;
    if (in_array($type, ['rebate', 'income'])) {
        $status_key = $type . '_status';
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        return $wpdb->query("update $wpdb->zibpay_order set $status_key = 3 where id IN ($ids)");
    }

    return false;
}

/**
 * @description:
 * @param {*} $id
 * @param {*} $is_allow 是否批准提现
 * @param {*} $msg 给用户留言
 * @return {*}
 */
function zibpay_withdraw_process($id, $is_allow = true, $msg = '', $payout_args = array())
{
    //查找数据
    global $wpdb;
    $msg_db_where = array('id' => $id, 'type' => 'withdraw', 'status' => 0);
    $msg_db       = (array) ZibMsg::get_row($msg_db_where);
    if (empty($msg_db['meta']['withdraw_price'])) {
        return false;
    }

    $user_id = $msg_db['send_user'];
    $meta    = $msg_db['meta'];

    if ($is_allow && !empty($payout_args['method']) && $payout_args['method'] === 'api') {
        $channel = !empty($payout_args['channel']) ? sanitize_text_field($payout_args['channel']) : '';
        if (!$channel || !zibpay_payout_admin_can_payout($channel)) {
            return false;
        }
        if (!zibpay_get_user_payout_account($user_id, $channel)) {
            return false;
        }

        $__service_price = isset($meta['service_price']) ? (float) $meta['service_price'] : 0;
        $pay_amount      = zib_floatval_round($meta['withdraw_price'] - $__service_price);
        $payout_result   = zibpay_payout_request(array(
            'withdraw_id' => $id,
            'user_id'     => $user_id,
            'amount'      => $pay_amount,
            'channel'     => $channel,
            'desc'        => get_bloginfo('name') . __('提现', 'zib_language'),
        ));
        if (empty($payout_result['success'])) {
            return false;
        }
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    //设置推广返佣订单状态
    $status_set    = $is_allow ? 1 : 0;
    $rebate_orders = !empty($meta['withdraw_orders']['rebate']) ? $meta['withdraw_orders']['rebate'] : 0;
    $rebate_orders = is_array($rebate_orders) ? implode(',', $rebate_orders) : $rebate_orders;
    if ($rebate_orders) {

        $detail = '';
        if ($status_set) {
            $detail_set = maybe_serialize(array(
                'withdraw_id'   => $id,
                'withdraw_time' => current_time('Y-m-d H:i:s'),
            ));

            $detail = ",rebate_detail = '$detail_set'";
        }

        $wpdb->query("update $wpdb->zibpay_order set rebate_status = $status_set $detail where id IN ($rebate_orders)");
    }

    //设置创作分成订单状态
    $income_orders = !empty($meta['withdraw_orders']['income']) ? $meta['withdraw_orders']['income'] : 0;
    $income_orders = is_array($income_orders) ? implode(',', $income_orders) : $income_orders;
    if ($income_orders) {
        $detail = '';
        if ($status_set) {

            $detail_set = maybe_serialize(array(
                'withdraw_id'   => $id,
                'withdraw_time' => current_time('Y-m-d H:i:s'),
            ));

            $detail = ",income_detail = '$detail_set'";
        }

        $wpdb->query("update $wpdb->zibpay_order set income_status = $status_set $detail where id IN ($income_orders)");
    }

    //设置余额处理
    $balance_sum = !empty($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;
    if ($balance_sum > 0) {
        //提现金额：先把冻结金额还原
        $user_balance = zibpay_get_user_balance($user_id);
        update_user_meta($user_id, 'balance', ($user_balance + $balance_sum));
        update_user_meta($user_id, 'balance_withdraw_ing', 0);

        if ($is_allow) {
            //提现批准，设置余额扣除
            if (!zibpay_update_user_balance($user_id, array(
                'value' => 0 - $balance_sum, //转为负数，扣除余额
                'type'  => __('提现', 'zib_language'),
                'desc'  => '', //说明
            ))) {
                //强制扣除，避免出现余额不足的而没有扣除正确的余额
                $_user_balance = $user_balance <= 0 ? 0 : $user_balance;
                update_user_meta($user_id, 'balance', $_user_balance);
            }
        }
    }

    if ($balance_sum < 0) {
        //提现金额小于0，也就是转入余额
        update_user_meta($user_id, 'balance_add_ing', 0); //转入中归零

        if ($is_allow) {
            //提现批准，设置将额外金额转入余额
            zibpay_update_user_balance($user_id, array(
                'value' => abs($balance_sum), //负数转正数，添加余额
                'type'  => __('提现转入', 'zib_language'),
                'desc'  => __('提现剩余金额自动转入余额', 'zib_language'), //说明
            ));
        }
        //提现拒绝，余额不变
    }

    //保存管理员留言
    ZibMsg::set_meta($id, 'admin_message', $msg);
    ZibMsg::set_status($id, ($is_allow ? 1 : 2));

    //准备新消息，发送给提现人
    $__withdraw_price = $meta['withdraw_price'];
    $__service_price  = isset($meta['service_price']) ? $meta['service_price'] : 0;
    $__rebate_sum     = isset($meta['withdraw_detail']['rebate']) ? $meta['withdraw_detail']['rebate'] : 0;
    $__income_sum     = isset($meta['withdraw_detail']['income']) ? $meta['withdraw_detail']['income'] : 0;
    $__balance_sum    = isset($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;

    $new_msg_con = sprintf(__('您好！ %s', 'zib_language'), zib_get_user_name_link($user_id)) . '<br>';
    $new_msg_con .= sprintf(__('您的提现申请%s', 'zib_language'), ($is_allow ? __('已处理完成', 'zib_language') : __('被拒绝', 'zib_language'))) . '<br>';
    $new_msg_con .= sprintf(__('提现金额：%s', 'zib_language'), zibpay_format_local_price($__withdraw_price)) . '<br>';
    $new_msg_con .= ($__service_price > 0 ? sprintf(__('付款金额：%1$s（扣除手续费%2$s）', 'zib_language'), zibpay_format_local_price($__withdraw_price - $__service_price), zibpay_format_local_price($__service_price)) : '') . '<br>';
    $msg_parts = '';
    if ($__rebate_sum) {
        $msg_parts .= '<br /> ' . sprintf(__('推广佣金%s', 'zib_language'), zibpay_format_local_price($__rebate_sum));
    }
    if ($__income_sum) {
        $msg_parts .= '<br /> ' . sprintf(__('创作分成%s', 'zib_language'), zibpay_format_local_price($__income_sum));
    }
    if ($__balance_sum > 0) {
        $msg_parts .= '<br /> ' . sprintf(__('余额%s', 'zib_language'), zibpay_format_local_price($__balance_sum));
    }
    if ($__balance_sum < 0) {
        $msg_parts .= '<br /> ' . sprintf(__('其中%s转入余额', 'zib_language'), zibpay_format_local_price(abs($__balance_sum)));
    }
    $new_msg_con .= $msg_parts ? __('包含：', 'zib_language') . $msg_parts . '<br>' : '';
    $new_msg_con .= '<br />' . sprintf(__('提交时间：%s', 'zib_language'), $msg_db['create_time']) . '<br>';
    $new_msg_con .= sprintf(__('处理时间：%s', 'zib_language'), current_time('Y-m-d H:i:s')) . '<br>' . $msg;

    $new_msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'withdraw_reply',
        'title'        => sprintf(__('您的提现申请%1$s，提现金额：%2$s', 'zib_language'), ($is_allow ? __('已处理完成', 'zib_language') : __('被拒绝', 'zib_language')), zibpay_format_local_price($__withdraw_price)),
        'content'      => $new_msg_con,
        'parent'       => $id,
        'meta'         => '',
        'other'        => '',
    );

    if (zib_msg_is_allow_receive($user_id, 'withdraw_reply')) {
        ZibMsg::add($new_msg_arge);
    }

    //添加挂钩
    do_action('withdraw_process_newmsg', $new_msg_arge, $msg_db);
    do_action('withdraw_process', $msg_db, $is_allow, $msg);
    //恢复到原来的语言
    zib_restore_previous_locale();
    return true;
}

/**
 * 后台提现处理：API / 手动打款选项
 */
function zibpay_withdraw_admin_process_form($user_id, $pay_amount = 0)
{
    $ready_channels = zibpay_payout_get_ready_channels();
    if (!$ready_channels) {
        return '';
    }

    $html = '<div class="payout-method-box" style="margin-bottom:12px;">';
    $html .= '<p><strong>' . esc_html__('打款方式', 'zib_language') . '</strong></p>';
    $html .= '<p><input type="radio" name="payout_method" id="payout_manual" value="manual" checked="checked"><label for="payout_manual"> ' . esc_html__('手动打款（扫收款码）', 'zib_language') . '</label></p>';
    $html .= '<p><input type="radio" name="payout_method" id="payout_api" value="api"><label for="payout_api" style="color:#036ee2;"> ' . esc_html__('API 自动打款', 'zib_language') . '</label></p>';
    $html .= '<div class="payout-channel-select" style="display:none;margin:8px 0 0 16px;">';
    $html .= '<p class="description">' . esc_html__('请选择打款渠道：', 'zib_language') . '</p>';

    $first_checked = true;
    foreach ($ready_channels as $channel) {
        $account  = zibpay_get_user_payout_account($user_id, $channel);
        $disabled = $account ? '' : ' disabled="disabled"';
        $checked  = ($account && $first_checked) ? ' checked="checked"' : '';
        if ($account && $first_checked) {
            $first_checked = false;
        }
        $tip = $account ? $account : __('未绑定', 'zib_language');
        $html .= '<p><input type="radio" name="payout_channel" id="payout_ch_' . esc_attr($channel) . '" value="' . esc_attr($channel) . '"' . $checked . $disabled . ' data-rate="' . esc_attr(zibpay_payout_get_rate($channel)) . '" data-mark="' . esc_attr(zibpay_payout_get_settle_mark($channel)) . '" data-unit="' . esc_attr(zibpay_payout_get_settle_unit($channel)) . '"><label for="payout_ch_' . esc_attr($channel) . '"> ' . esc_html(zibpay_payout_get_channel_name($channel)) . ' — ' . esc_html($tip) . '</label>';
        if (zibpay_payout_need_rate_hint($channel)) {
            $html .= ' <span class="badg badg-sm c-yellow">'.__('汇率', 'zib_language').' ' . esc_html(zibpay_payout_get_rate_rule_text($channel)) . '</span>';
        }
        $html .= '</p>';
    }

    $html .= '<p class="description payout-amount-preview">' . sprintf(
        esc_html__('本地应付：%s', 'zib_language'),
        zibpay_format_local_price($pay_amount) . zibpay_get_currency_unit()
    ) . '</p>';
    $html .= '</div>';
    $html .= '<script>jQuery(function($){
        var localAmount=' . json_encode((float) $pay_amount) . ';
        var localText=' . json_encode(zibpay_format_local_price($pay_amount) . zibpay_get_currency_unit()) . ';
        function updatePayoutPreview(){
            var $ch=$("input[name=payout_channel]:checked");
            if(!$("#payout_api").is(":checked")||!$ch.length){$(".payout-amount-preview").html(' . json_encode(sprintf(__('本地应付：%s', 'zib_language'), '__LOCAL__')) . '.replace("__LOCAL__",localText));return;}
            var rate=parseFloat($ch.data("rate"))||1,mark=$ch.data("mark")||"",unit=$ch.data("unit")||"";
            var settle=(Math.round(localAmount*rate*100)/100).toFixed(2);
            $(".payout-amount-preview").html(' . json_encode(__('本地 %1$s，实际 API 打款 %2$s', 'zib_language')) . '.replace("%1$s",localText).replace("%2$s",mark+settle+unit));
        }
        function togglePayoutChannel(){var api=$("#payout_api").is(":checked");$(".payout-channel-select").toggle(api);updatePayoutPreview();}
        $("input[name=payout_method],input[name=payout_channel]").on("change",togglePayoutChannel);
        togglePayoutChannel();
    });</script>';
    $html .= '</div>';

    return $html;
}

/**
 * @description: 设置订单的提现状态为正在提现
 * @param {*} $user_id
 * @param {*} $money 金额
 * @return {*}
 */
function zibpay_withdraw_balance_set_ing($user_id, $money)
{
    global $wpdb;
    if ($money > 0) {
        $user_balance = zibpay_get_user_balance($user_id);

        $money = $money >= $user_balance ? $user_balance : $money;

        update_user_meta($user_id, 'balance', ($user_balance - $money));
        update_user_meta($user_id, 'balance_withdraw_ing', $money);
    }
    if ($money < 0) {
        update_user_meta($user_id, 'balance_add_ing', $money);
    }

    return true;
}

//获取待提现数量
function zibpay_get_withdraw_pending_count()
{
    $withdraw_count = ZibMsg::get_count(array(
        'type'   => 'withdraw',
        'status' => 0,
    ));

    return (int) $withdraw_count;
}

//判断用户是否有可用的佣金收款方式
function zibpay_user_has_withdraw_collection($user_id = 0)
{
    $channels = zibpay_get_user_withdraw_collection_data($user_id);
    return !empty($channels);
}

//获取用户全部可用的佣金收款方式
function zibpay_get_user_withdraw_collection_data($user_id = 0)
{
    $channels         = array();
    $rewards_img_urls = zib_get_user_rewards_img_urls($user_id);
    if ($rewards_img_urls['weixin']) {
        $channels['weixin_img'] = $rewards_img_urls['weixin'];
    }
    if ($rewards_img_urls['alipay']) {
        $channels['alipay_img'] = $rewards_img_urls['alipay'];
    }

    //API打款
    if (zibpay_payout_user_can_bind('wechat')) {
        $openid = zibpay_get_user_payout_account($user_id, 'wechat');
        if ($openid) {
            $channels['weixin_openid'] = $openid;
        }
    }

    if (zibpay_payout_user_can_bind('alipay')) {
        $alipay_account = zibpay_get_user_payout_account($user_id, 'alipay');
        if ($alipay_account) {
            $channels['alipay_account'] = $alipay_account;
        }
    }

    if (zibpay_payout_user_can_bind('paypal')) {
        $paypal_email = zibpay_get_user_payout_account($user_id, 'paypal');
        if ($paypal_email) {
            $channels['paypal_email'] = $paypal_email;
        }
    }

    return $channels;
}
