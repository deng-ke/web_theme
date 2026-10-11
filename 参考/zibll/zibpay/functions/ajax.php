<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-29 19:22:40
 * @LastEditTime : 2026-06-22 22:53:39
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//收银台
function zibpay_ajax_pay_cashier_modal()
{
    $id         = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $price_type = !empty($_REQUEST['price_type']) ? $_REQUEST['price_type'] : '';
    //判断文章是否存在
    $post = get_post($id);
    if (!$post) {
        zib_ajax_notice_modal('danger', __('内容不存在或参数错误', 'zib_language'));
    }

    $user_id  = get_current_user_id();
    $pay_mate = get_post_meta($id, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        zib_ajax_notice_modal('danger', __('当前内容未设置付费，或参数错误', 'zib_language'));
    }

    $is_points = zibpay_post_is_points_modo($pay_mate);
    if ($is_points && !$user_id) {
        zib_ajax_notice_modal('danger', __('请先登录', 'zib_language'));
    }

    //判断购买限制
    $pay_limit = !empty($pay_mate['pay_limit']) ? (int) $pay_mate['pay_limit'] : '0';
    if ($pay_limit > 0 && !$user_id) {
        zib_ajax_notice_modal('danger', __('请先登录', 'zib_language'));
    }

    //购买权限
    if ($pay_limit > 0 && (_pz('pay_user_vip_1_s', true) || _pz('pay_user_vip_2_s', true))) {
        if (!$user_id) {
            zib_ajax_notice_modal('danger', __('请先登录', 'zib_language'));
        }

        $vip_icon = zib_get_svg('vip_' . $pay_limit, '0 0 1024 1024', 'mr3');
        //开始限制购买权限
        $user_vip_level = zib_get_user_vip_level($user_id);

        if (!$user_vip_level) {
            $pay_vip_text = __('开通会员', 'zib_language');
        } elseif ($user_vip_level < $pay_limit) {
            $pay_vip_text = __('升级', 'zib_language') . _pz('pay_user_vip_' . $pay_limit . '_name');
        }

        if (!$user_vip_level || $user_vip_level < $pay_limit) {
            $pay_button = '<div class="mb20 em09"><span class="badg c-yellow em09"><i class="fa fa-fw fa-info-circle fa-fw mr6" aria-hidden="true"></i>' . sprintf(__('您暂无购买权限，请先%s', 'zib_language'), $pay_vip_text) . '</span></div>';
            $pay_button .= '<a href="javascript:;" vip-level="' . $pay_limit . '" class="em09 but btn-block jb-vip' . $pay_limit . ($user_id ? ' pay-vip' : ' signin-loader') . ' padding-lg">' . $vip_icon . $pay_vip_text . '</a>';
            zib_ajax_notice_modal('warning', $pay_button);
        }
    }

    $_modal = $is_points ? zibpay_pay_points_cashier_modal($id, $price_type) : zibpay_pay_cashier_modal($id, $price_type);
    if (!$_modal) {
        zib_ajax_notice_modal('danger', __('参数异常', 'zib_language'));
    }
    echo $_modal;
    exit;
}
add_action('wp_ajax_pay_cashier_modal', 'zibpay_ajax_pay_cashier_modal');
add_action('wp_ajax_nopriv_pay_cashier_modal', 'zibpay_ajax_pay_cashier_modal');

//用户订单列表
function zibpay_ajax_user_order()
{
    $html = zibpay_get_user_order();
    echo '<body style="display:none;"><main><div class="ajaxpager" id="user_order_lists">' . $html . '</div></main></body>';
    exit;
}
add_action('wp_ajax_user_pay_order', 'zibpay_ajax_user_order');

//订单详情
function zibpay_ajax_order_details_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);

    if (!$order) {
        zib_ajax_notice_modal('danger', __('订单不存在', 'zib_language'));
    }

    //验证权限
    if ($order['user_id'] != get_current_user_id() && !zib_current_user_can('view_order')) {
        zib_ajax_notice_modal('danger', __('您无权限查看此订单', 'zib_language'));
    }

    echo zibpay_get_order_details_modal($order);
    exit;
}
add_action('wp_ajax_order_details_modal', 'zibpay_ajax_order_details_modal');
add_action('wp_ajax_nopriv_order_details_modal', 'zibpay_ajax_order_details_modal');

//AJAX获取用户提现记录列表
function zibpay_ajax_rebate_user_withdraw_detail()
{
    $current_id = get_current_user_id();
    if (!$current_id) {
        return;
    }

    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : $current_id;
    if ($user_id !== (int) $current_id && !zib_current_user_can('view_withdraw_record')) {
        zib_send_json_error(__('您无权限查看此信息', 'zib_language'));
    }

    $ice_perpage = !empty($_REQUEST['ice_perpage']) ? (int) $_REQUEST['ice_perpage'] : 10;

    zib_ajax_send_ajaxpager(zibpay_get_withdraw_record_lists($user_id, $ice_perpage));
}
add_action('wp_ajax_withdraw_detail', 'zibpay_ajax_rebate_user_withdraw_detail');

//AJAX申请提现模态框
function zibpay_ajax_withdraw_record_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', __('参数错误', 'zib_language'));
    }

    echo zibpay_get_withdraw_record_modal($user_id);
    exit;
}
add_action('wp_ajax_withdraw_record_modal', 'zibpay_ajax_withdraw_record_modal');

//AJAX申请提现模态框
function zibpay_ajax_modal_apply_withdraw()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', __('参数错误', 'zib_language'));
    }

    echo zibpay_get_apply_withdraw_modal($user_id);
    exit;
}
add_action('wp_ajax_apply_withdraw_modal', 'zibpay_ajax_modal_apply_withdraw');

//ajax处理用户提现申请
function zibpay_ajax_apply_withdraw()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $user_id = get_current_user_id();
    if (!$user_id || empty($_POST['user_id']) || $_POST['user_id'] != $user_id) {
        zib_send_json_error(__('处理出错，请刷新后重试', 'zib_language'));
    }
    //函数节流
    zib_ajax_debounce('apply_withdraw', $user_id);

    //判断是否有正在提现的申请
    $withdraw_ing = (array) zibpay_get_user_withdraw_ing($user_id);
    if (!empty($withdraw_ing['meta']['withdraw_price'])) {
        zib_send_json_error(__('您的申请已提交，请耐心等待', 'zib_language'));
    }

    if (!zibpay_user_has_withdraw_collection($user_id)) {
        zib_send_json_error(__('请先完成收款设置', 'zib_language'));
    }

    //推广佣金
    $all_effective_sum = 0;
    $pay_rebate_s      = _pz('pay_rebate_s');
    $__rebate_sum      = 0;
    $__rebate_ids      = '';
    if ($pay_rebate_s) {
        $rebate_effective_data = zibpay_get_user_rebate_data($user_id, 'effective'); //佣金统计
        if (!isset($_REQUEST['rebate_ids']) || $_REQUEST['rebate_ids'] != $rebate_effective_data['ids']) {
            zib_send_json_error(__('您的推广佣金发生变动，请刷新页面后重新申请', 'zib_language'));
        }
        $all_effective_sum += $rebate_effective_data['sum'];
        $__rebate_sum = $rebate_effective_data['sum'];
        $__rebate_ids = $rebate_effective_data['ids'];
    }

    //收入分成
    $pay_income_s = _pz('pay_income_s');
    $__income_sum = 0;
    $__income_ids = '';
    if ($pay_income_s) {
        $income_price_effective = zibpay_get_user_income_data($user_id, 'effective'); //分成统计
        if (!isset($_REQUEST['income_ids']) || $_REQUEST['income_ids'] != $income_price_effective['ids']) {
            zib_send_json_error(__('您的创作分成发生变动，请刷新页面后重新申请', 'zib_language'));
        }
        $all_effective_sum += $income_price_effective['sum'];
        $__income_sum = $income_price_effective['sum'];
        $__income_ids = $income_price_effective['ids'];
    }

    //余额功能
    $pay_balance_s          = _pz('pay_balance_s');
    $pay_balance_withdraw_s = _pz('pay_balance_withdraw_s');
    $__user_balance         = 0;
    if ($pay_balance_s && $pay_balance_withdraw_s) {
        $user_balance = zibpay_get_user_balance($user_id); //余额统计
        $all_effective_sum += $user_balance;
        $__user_balance = $user_balance;
    }
    $all_effective_sum = round((float) $all_effective_sum, 2);
    //可用余额判断，避免出现时间差而导致金额错误
    $effective_sum = !empty($_REQUEST['effective_sum']) ? round((float) $_REQUEST['effective_sum'], 2) : 0;
    if (!$effective_sum || $effective_sum > $all_effective_sum) {
        zib_send_json_error(__('您的余额有变动，请刷新页面后重新申请', 'zib_language'));
    }

    //提现金额判断
    $withdraw_money_type = !empty($_REQUEST['withdraw_money_type']) ? $_REQUEST['withdraw_money_type'] : '';
    if ($withdraw_money_type === 'custom') {
        //自定义提现金额
        $custom_money = !empty($_REQUEST['custom_money']) ? round((float) $_REQUEST['custom_money'], 2) : 0;
        if (!$custom_money || $custom_money <= 0) {
            zib_send_json_error(__('请输入有效的提现金额', 'zib_language'));
        }
        $lowest_money = (int) _pz('pay_rebate_withdraw_lowest_money'); //提现限制
        if ($custom_money < $lowest_money) {
            zib_send_json_error(sprintf(__('最低提现%s，请修改您的提现金额', 'zib_language'), zibpay_format_local_price_text($lowest_money)));
        }
        if ($custom_money > $all_effective_sum) {
            zib_send_json_error(sprintf(__('您最高可提现%s，请修改您的提现金额', 'zib_language'), zibpay_format_local_price_text((int) $all_effective_sum)));
        }
        $__withdraw_price = $custom_money;
    } else {
        //全额提现
        if ((int) $effective_sum !== (int) $all_effective_sum) {
            zib_send_json_error(__('您的资产有变动，请刷新页面后重新申请', 'zib_language'));
        }
        $__withdraw_price = $all_effective_sum;
    }

    //判断结束，开始处理 ----------------------------

    //修改推广返佣的状态
    if ($__rebate_ids) {
        zibpay_withdraw_order_set_ing('rebate', $__rebate_ids);
    }
    //修改创作分成的状态
    if ($__income_ids) {
        zibpay_withdraw_order_set_ing('income', $__income_ids);
    }
    //修改余额的状态
    if ($withdraw_money_type === 'custom') {
        $__balance_sum = $__withdraw_price - ($__income_sum + $__rebate_sum);
    } else {
        //全额提现
        $__balance_sum = $__user_balance;
    }

    zibpay_withdraw_balance_set_ing($user_id, $__balance_sum);

    // 开始记录消息系统
    $service_charge   = _pz('withdraw_service_charge'); //提现手续费费率
    $__service_charge = round(($__withdraw_price * $service_charge) / 100, 2); //手续费
    $__payment_price  = $__withdraw_price - $__service_charge; //支付金额
    $process_url      = add_query_arg(array('page' => 'zibpay_withdraw', 'status' => '0'), admin_url('admin.php')); //佣金处理链接
    $__message        = !empty($_REQUEST['message']) ? esc_attr($_REQUEST['message']) : '';

    //准备通知消息
    $msg_con = '';
    $msg_con .= sprintf(__('用户：%s，正在申请佣金提现', 'zib_language'), zib_get_user_name_link($user_id)) . '<br>';
    $msg_con .= sprintf(__('提现金额：%s', 'zib_language'), zibpay_format_local_price_text($__withdraw_price)) . '<br />';
    $msg_con .= sprintf(__('需支付金额：%s', 'zib_language'), zibpay_format_local_price_text($__payment_price)) . ($__service_charge > 0 ? sprintf(__('(扣除%s手续费)', 'zib_language'), zibpay_format_local_price_text($__service_charge)) : '') . '<br>';
    $msg_parts = '';
    if ($__rebate_sum) {
        $msg_parts .= sprintf(__('推广佣金%s. ', 'zib_language'), zibpay_format_local_price_text($__rebate_sum));
    }
    if ($__income_sum) {
        $msg_parts .= sprintf(__('创作分成%s. ', 'zib_language'), zibpay_format_local_price_text($__income_sum));
    }
    if ($__balance_sum > 0) {
        $msg_parts .= sprintf(__('余额%s. ', 'zib_language'), zibpay_format_local_price_text($__balance_sum));
    }
    if ($__balance_sum < 0) {
        $msg_parts .= sprintf(__('其中%s转入余额. ', 'zib_language'), zibpay_format_local_price_text(abs($__balance_sum)));
    }
    $msg_con .= $msg_parts ? sprintf(__('包含：%s', 'zib_language'), $msg_parts) . '<br>' : '';
    $msg_con .= sprintf(__('申请时间：%s', 'zib_language'), current_time('Y-m-d H:i:s')) . '<br>';
    $msg_con .= '<br>';
    $msg_con .= $__message ? __('用户留言：', 'zib_language') . '<br>' . $__message . '<br /><br />' : '';
    $msg_con .= __('您可以点击下方按钮快速处理此申请', 'zib_language') . '<br>';
    $msg_con .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($process_url) . '">' . __('立即处理', 'zib_language') . '</a>' . '<br>';

    $msg_args = array(
        'send_user'    => $user_id,
        'receive_user' => 'admin',
        'type'         => 'withdraw',
        'title'        => sprintf(__('有新的提现申请待处理-用户：%1$s，金额：%2$s', 'zib_language'), get_userdata($user_id)->display_name, zibpay_format_local_price($__withdraw_price)),
        'content'      => $msg_con,
        'meta'         => array(
            'withdraw_price'   => $__withdraw_price,
            'service_price'    => $__service_charge,
            'withdraw_message' => esc_sql($__message),
            'withdraw_orders'  => array(
                'rebate' => $__rebate_ids,
                'income' => $__income_ids,
            ),
            'withdraw_detail'  => array(
                'rebate'  => $__rebate_sum,
                'income'  => $__income_sum,
                'balance' => $__balance_sum,
            ),
        ),
    );

    //创建消息
    $add_msg = ZibMsg::add($msg_args);
    if (!$add_msg) {
        zib_send_json_error(__('提现系统出现错误，请与客服联系', 'zib_language'));
    }
    //添加处理挂钩
    do_action('user_apply_withdraw', $msg_args);

    zib_send_json_success(array('msg' => __('提交成功，等待客服处理', 'zib_language'), 'reload' => 1));
}
add_action('wp_ajax_apply_withdraw', 'zibpay_ajax_apply_withdraw');

//后台导出卡密数据
function zibpay_ajax_card_pass_export()
{
    if (!is_super_admin()) {
        wp_die(__('暂无此权限', 'zib_language'));
    }

    @set_time_limit(0);

    $export_format = !empty($_REQUEST['export_format']) ? esc_sql($_REQUEST['export_format']) : 'xls';
    $where         = array();
    $type          = !empty($_REQUEST['type']) ? esc_sql($_REQUEST['type']) : '';
    global $wpdb;
    $DB = ZibDB::table($wpdb->zibpay_card_password);
    if (isset($_REQUEST['status']) && $_REQUEST['status'] !== 'all') {
        $DB->where('status', $_REQUEST['status']);
    }
    if ($type) {
        $DB->where('type', $type);
    }

    switch ($type) {
        case 'balance_charge': //余额充值
            $field    = 'card,password,meta,other,status';
            $title    = array(__('卡号', 'zib_language'), __('密码', 'zib_language'), __('面额', 'zib_language'), __('备注', 'zib_language'), __('状态', 'zib_language'));
            $data_map = 'zib_card_pass_export_balance_charge_map';
            break;
        case 'points_exchange': //积分兑换
            $field    = 'card,password,meta,other,status';
            $title    = array(__('卡号', 'zib_language'), __('密码', 'zib_language'), __('面额', 'zib_language'), __('备注', 'zib_language'), __('状态', 'zib_language'));
            $data_map = 'zib_card_pass_points_exchange_charge_map';
            break;

        case 'vip_exchange': //会员兑换
            $field    = 'card,password,meta,other,status';
            $title    = array(__('卡号', 'zib_language'), __('密码', 'zib_language'), __('兑换会员', 'zib_language'), __('备注', 'zib_language'), __('状态', 'zib_language'));
            $data_map = 'zib_card_pass_vip_exchange_charge_map';
            break;

        case 'invit_code': //邀请码注册
            $field    = 'password,meta,status,other';
            $title    = array(__('邀请码', 'zib_language'), __('奖励', 'zib_language'), __('状态', 'zib_language'), __('备注', 'zib_language'));
            $data_map = 'zib_card_pass_export_invit_code_map';
            break;

        case 'coupon': //优惠券
            $field    = 'password,post_id,meta,other';
            $title    = array(__('优惠码', 'zib_language'), __('商品ID', 'zib_language'), __('优惠折扣', 'zib_language'), __('可使用次数', 'zib_language'), __('已使用次数', 'zib_language'), __('优惠码名称', 'zib_language'), __('备注', 'zib_language'));
            $data_map = 'zib_card_pass_export_coupon_map';
            break;

        case 'vip_coupon': //会员优惠券-暂未启用
            $field    = 'password,meta,status,other';
            $title    = array(__('邀请码', 'zib_language'), __('奖励', 'zib_language'), __('状态', 'zib_language'), __('备注', 'zib_language'));
            $data_map = 'zib_card_pass_export_invit_code_map';
            break;

        case 'custom': //自定义
            $field    = 'card,password,other,meta';
            $title    = array(__('卡号', 'zib_language'), __('密码', 'zib_language'), __('备注', 'zib_language'), __('状态', 'zib_language'));
            $data_map = 'zib_card_pass_export_custom_map';
            break;

    }

    $db_data  = $DB->field($field)->select()->toArrayMap($data_map);
    $filename = $type . '_' . gmdate('d_m_Y');
    if (!$db_data) {
        wp_die(__('暂无可导出的内容', 'zib_language'));
    }

    switch ($export_format) {
        case 'text':
            $text_division = !empty($_REQUEST['text_division']) ? wp_unslash($_REQUEST['text_division']) : ' ';

            header('Content-type:application/octet-stream');
            header('Accept-Ranges:bytes');
            header('Content-Disposition:attachment;filename=' . $filename . '.txt');
            header('Pragma: no-cache');
            header('Pragma: public');
            header('Expires: 0');
            $data = $db_data;
            if (!empty($data)) {
                $_data = array();
                foreach ($data as $val) {
                    $val     = (array) $val;
                    $_data[] = implode($text_division, $val);
                }
                echo implode("\n", $_data);
            }

            break;
        default:
            zib_export_excel($db_data, $title, $filename);
    }

    exit;
}
add_action('wp_ajax_card_pass_export', 'zibpay_ajax_card_pass_export');

//导出数据处理：导出优惠券
function zib_card_pass_export_coupon_map($data)
{
    $title  = array(__('优惠码', 'zib_language'), __('商品ID', 'zib_language'), __('优惠折扣', 'zib_language'), __('可使用次数', 'zib_language'), __('已使用次数', 'zib_language'), __('优惠码名称', 'zib_language'), __('备注', 'zib_language'));
    $coupon = zibpay_filter_coupon_data($data);

    return array(
        'password'      => $coupon['password'],
        'post_id'       => $coupon['post_id'],
        'discount_text' => $coupon['discount_text'],
        'reuse'         => $coupon['reuse'],
        'used_count'    => $coupon['used_count'],
        'title'         => $coupon['title'],
        'other'         => $coupon['other'],
    );
}

//导出数据处理：导出积分兑换
function zib_card_pass_points_exchange_charge_map($data)
{
    $data           = (array) $data;
    $data['status'] = $data['status'] === 'used' ? __('已使用', 'zib_language') : __('未使用', 'zib_language');
    $data['meta']   = zibpay_get_pass_exchange_points($data);
    return $data;
}

//导出数据处理：导出充值卡
function zib_card_pass_export_balance_charge_map($data)
{
    $data           = (array) $data;
    $data['status'] = $data['status'] === 'used' ? __('已使用', 'zib_language') : __('未使用', 'zib_language');
    $data['meta']   = zibpay_get_recharge_card_price($data);
    return $data;
}

//导出数据处理：导出充值卡
function zib_card_pass_vip_exchange_charge_map($data)
{
    $data           = (array) $data;
    $data['status'] = $data['status'] === 'used' ? __('已使用', 'zib_language') : __('未使用', 'zib_language');
    $meta           = zibpay_get_vip_exchange_card_data($data);
    $data['meta']   = build_query(array(
        'level' => $meta['level'],
        'time'  => $meta['time'],
        'unit'  => $meta['unit'], //天还是月
    ));

    return $data;
}

//导出数据处理：邀请码
function zib_card_pass_export_invit_code_map($data)
{
    $data           = (array) $data;
    $meta           = maybe_unserialize($data['meta']);
    $data['status'] = $data['status'] === 'used' ? __('已使用', 'zib_language') : __('未使用', 'zib_language');
    $data['meta']   = __('无奖励', 'zib_language');

    if (isset($meta['reward']) && is_array($meta['reward'])) {
        $data['meta'] = build_query($meta['reward']);
    }

    return $data;
}

function zib_card_pass_export_custom_map($data)
{
    $data = (array) $data;
    $meta = maybe_unserialize($data['meta']);
    if (!empty($meta['shipped_order_id'])) {
        $data['meta'] = sprintf(__('已售[OrderID:%s]', 'zib_language'), $meta['shipped_order_id']);
    } else {
        $data['meta'] = '';
    }
    return $data;
}

function zibpay_ajax_admin_assets_details()
{
    if (!is_super_admin()) {
        echo __('权限不足', 'zib_language');
        exit;
    }
    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;

    $balance_record_lists = zibpay_get_user_balance_record_lists($user_id);
    $points_record_lists  = zibpay_get_user_points_record_lists($user_id);

    echo '<div class="zib-tab">
    <div class="flex ac mb20">
        <div class="active"><a class="but zib-tab-toggle" href="javascript:;" tab-id="1">' . __('余额记录', 'zib_language') . '</a></div>
        <div class=""><a class="but zib-tab-toggle" href="javascript:;" tab-id="2">' . __('积分记录', 'zib_language') . '</a></div>
    </div>
    <div class="zib-tab-content">
        <div class="zib-tab-pane active in max-vh5" tab-id="1">' . $balance_record_lists . '</div>
        <div class="zib-tab-pane max-vh5" tab-id="2">' . $points_record_lists . '</div>
    </div>
    </div>';

    exit;

}
add_action('wp_ajax_admin_assets_details', 'zibpay_ajax_admin_assets_details');

function zibpay_ajax_admin_paydown_log()
{
    if (!is_super_admin()) {
        echo __('权限不足', 'zib_language');
        exit;
    }
    $id        = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $type      = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'post';
    $paged     = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $page_size = 50;
    $count     = 0;

    if (!$id) {
        echo __('参数错误', 'zib_language');
        exit;
    }

    $meta_key = 'pay_down_log';
    $record   = $type === 'user' ? zib_get_user_meta($id, $meta_key, true) : zib_get_post_meta($id, $meta_key, true);
    if ($type === 'user') {
        $h_user         = get_userdata($id);
        $h_display_name = isset($h_user->display_name) ? $h_user->display_name : 'user_id:' . $id;
        $header         = '<div class="border-title">' . sprintf(__('用户', 'zib_language') . '<b class="c-blue">[%s]</b>' . __('的下载记录', 'zib_language'), $h_display_name) . '</div>';
    } else {
        $h_post       = get_post($id);
        $h_post_title = isset($h_post->post_title) ? zib_str_cut($h_post->post_title, 0, 10) : 'post_id:' . $id;
        $header       = '<div class="border-title">' . sprintf(__('文章', 'zib_language') . '<b class="c-blue">[%s]</b>' . __('的下载记录', 'zib_language'), $h_post_title) . '</div>';
    }

    $lists = '';
    if ($record && is_array($record)) {
        //数组分页
        $count    = count($record);
        $record   = array_chunk($record, $page_size);
        $record_p = $record[$paged - 1];
        foreach ($record_p as $k => $v) {
            $paid_name = '<badge class="badg badg-sm mr6 c-yellow">' . zibpay_get_paid_type_name($v['paid_type']) . '</badge>';
            $down_id   = '<div class="em09">' . sprintf(__('资源序号：%s', 'zib_language'), ($v['down_id'] + 1)) . '</div>';
            $ip        = !empty($v['ip']) ? '<div class="em09">' . sprintf(__('IP地址：%s', 'zib_language'), $v['ip']) . '</div>' : '';
            $order_num = !empty($v['order_num']) ? '<div class="em09">' . sprintf(__('订单号：%s', 'zib_language'), '<a href="' . zibpay_get_admin_shop_order_url('search=') . $v['order_num'] . '" target="_blank">' . $v['order_num'] . '</a>') . '</div>' : '';

            if ($type === 'user') {
                //需要输出文章信息
                $post   = get_post($v['post_id']);
                $_name  = isset($post->post_title) ? zib_str_cut($post->post_title, 0, 18) : 'post_id:' . $v['post_id'];
                $m_link = zibpay_get_paydown_log_admin_link('post', $v['post_id'], 'ml10 mr10 c-blue', __('查看此文章下载记录', 'zib_language'));
            } else {
                //需要输出用户信息
                $user   = get_userdata($v['user_id']);
                $_name  = __('未登录用户', 'zib_language');
                $m_link = '';
                if (isset($user->display_name)) {
                    $_name  = zib_str_cut($user->display_name, 0, 18);
                    $m_link = zibpay_get_paydown_log_admin_link('user', $v['user_id'], 'ml10 mr10 c-blue', __('查看此用户下载记录', 'zib_language'));
                }
            }

            $lists .= '<div class="border-bottom padding-h10">
                        <div>
                            <div class="mb6">' . $paid_name . $_name . '</div>' . $down_id . $order_num . $ip . '<div class="flex jsb ab em09"><span>' . sprintf(__('时间：%s', 'zib_language'), $v['time']) . '</span>' . $m_link . '</div>
                        </div>
                </div>';
        }

        $paged_html = '';
        if (isset($record[$paged - 2])) {
            //有下一页
            $paged_html .= zibpay_get_paydown_log_admin_link($type, $id, 'but c-yellow', __('上一页', 'zib_language'), $paged - 1);
        }

        if (isset($record[$paged])) {
            //有下一页
            $paged_html .= zibpay_get_paydown_log_admin_link($type, $id, 'but c-blue', __('下一页', 'zib_language'), $paged + 1);
        }

        $lists .= $paged_html ? '<div class="padding-h10 flex jc"><span class="mr10">' . sprintf(__('共%s条 第%s页', 'zib_language'), $count, $paged) . '</span>' . $paged_html . '</div>' : '';
    }

    if (!$lists) {
        $lists = zib_get_null(__('暂无下载记录', 'zib_language'), 42, 'null-order.svg');
    }

    echo $header . '<div class="max-vh5">' . $lists . '</div>';
    exit;
}
add_action('wp_ajax_admin_paydown_log', 'zibpay_ajax_admin_paydown_log');

//购买会员
function zibpay_pay_vip_modal()
{
    if (!is_user_logged_in()) {
        zib_send_json_error(array('ys' => 'danger', 'msg' => __('请先登录', 'zib_language'), 'code' => 'no_logged'));
    }

    $modal = zibpay_get_pay_uservip_modal();
    zib_send_json_success(array('html' => $modal));
}
add_action('wp_ajax_pay_vip', 'zibpay_pay_vip_modal');
add_action('wp_ajax_nopriv_pay_vip', 'zibpay_pay_vip_modal');

//积分兑换会员
function zibpay_vip_points_exchange_modal()
{
    if (!_pz('points_s', true) || !_pz('pay_vip_points_exchange_s', true) || (!_pz('pay_user_vip_1_s', true) && !_pz('pay_user_vip_2_s', true))) {
        zib_ajax_notice_modal('danger', __('当前功能已关闭', 'zib_language'));
    }

    if (!is_user_logged_in()) {
        zib_ajax_notice_modal('danger', __('请先登录', 'zib_language'));
    }

    echo zibpay_get_vip_points_exchange_modal();
    exit;
}
add_action('wp_ajax_vip_points_exchange_modal', 'zibpay_vip_points_exchange_modal');

//ajax验证优惠码
function zibpay_ajax_coupon_submit()
{
    $post_id    = !empty($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
    $coupon     = !empty($_REQUEST['coupon']) ? esc_sql($_REQUEST['coupon']) : '';
    $order_type = !empty($_REQUEST['order_type']) ? (int) $_REQUEST['order_type'] : 0;

    if (!$coupon) {
        zib_send_json_error(__('参数错误', 'zib_language'));
    }

    $coupon_data = zibpay_is_coupon_available($coupon, $order_type, $post_id);

    if (!empty($coupon_data['error'])) {
        zib_send_json_error($coupon_data['msg']);
    }

    $coupon_data['msg'] = __('优惠码可用，请尽快使用，以免被抢用或过期', 'zib_language');
    zib_send_json_success($coupon_data);

}
add_action('wp_ajax_coupon_submit', 'zibpay_ajax_coupon_submit');
add_action('wp_ajax_nopriv_coupon_submit', 'zibpay_ajax_coupon_submit');

//关闭订单模态框
function zibpay_ajax_close_order_modal()
{

    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;

    if (!$order_id) {
        zib_ajax_notice_modal('danger', __('参数错误', 'zib_language'));
    }

    $order = zibpay::get_order($order_id, 'id,order_num,user_id,status');
    if (!$order) {
        zib_ajax_notice_modal('danger', __('订单不存在', 'zib_language'));
    }

    //订单状态判断
    if ($order['status'] == '-1') {
        zib_ajax_notice_modal('info', __('订单已关闭', 'zib_language'));
    }

    if ($order['status'] == '1') {
        zib_ajax_notice_modal('info', __('订单已支付', 'zib_language'));
    }

    //判断用户权限
    if ($order['user_id'] != get_current_user_id() && !zib_current_user_can('order_close', $order)) {
        zib_ajax_notice_modal('danger', __('权限不足', 'zib_language'));
    }

    $cancel_reason = [
        __('暂时不需要了', 'zib_language'),
        __('价格不合适', 'zib_language'),
        __('信息填写错误', 'zib_language'),
        __('支付遇到问题', 'zib_language'),
        __('商家原因', 'zib_language'),
        __('其他原因', 'zib_language'),
    ];

    $cancel_reason_radio = '';
    foreach ($cancel_reason as $key => $value) {
        $checked = $key === 0 ? 'checked' : '';
        $cancel_reason_radio .= '<label class="flex jsb ac padding-h6"><span style="font-weight: normal;">' . $value . '</span><input ' . $checked . ' type="radio" name="cancel_reason" value="' . $value . '"></label>';
    }

    $cancel_reason_radio = '<div class="form-radio">' . $cancel_reason_radio . '</div>';

    $header = '<div class="touch border-title flex jc c-yellow">' . __('确定取消该订单吗？', 'zib_language') . '</div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $con    = '<form class="zib-modal-form">';
    $con .= '<div class="mb20">';
    $con .= '<div class="mb10 muted-2-color">' . __('请选择取消原因：', 'zib_language') . '</div>';
    $con .= $cancel_reason_radio;
    $con .= '<div class="mt10 other-reason-input" style="display:none;">';
    $con .= '<textarea class="form-control" name="other_reason" placeholder="' . esc_attr__('请填写取消原因', 'zib_language') . '" rows="3"></textarea>';
    $con .= '</div>';
    $con .= '</div>';
    $con .= '<input type="hidden" name="action" value="close_order">';
    $con .= '<input type="hidden" name="order_id" value="' . $order_id . '">';
    $con .= wp_nonce_field('close_order', '_wpnonce', false, false);

    $footer = '<div class="mt20 but-average">';
    $footer .= '<button class="but jb-yellow padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>' . __('确认取消', 'zib_language') . '</button>';
    $footer .= '</div></form>';

    echo $header . $con . $footer;
    exit;
}
add_action('wp_ajax_close_order_modal', 'zibpay_ajax_close_order_modal');
add_action('wp_ajax_nopriv_close_order_modal', 'zibpay_ajax_close_order_modal');

//取消订单
function zibpay_ajax_close_order()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;

    if (!$order_id) {
        zib_send_json_error(__('参数错误', 'zib_language'));
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $order = zibpay::get_order($order_id, 'id,order_num,user_id,status');
    if (!$order) {
        zib_send_json_error(__('订单不存在', 'zib_language'));
    }

    if ($order['status'] == 1) {
        zib_send_json_error(__('订单已支付，无法关闭', 'zib_language'));
    }

    //判断用户权限
    if ($order['user_id'] != get_current_user_id() && !zib_current_user_can('order_close', $order)) {
        zib_send_json_error(__('权限不足', 'zib_language'));
    }

    $reason = !empty($_REQUEST['cancel_reason']) ? sanitize_text_field($_REQUEST['cancel_reason']) : __('其他原因', 'zib_language');
    if ($reason === __('其他原因', 'zib_language') && !empty($_REQUEST['other_reason'])) {
        $reason = sanitize_text_field($_REQUEST['other_reason']);
    }

    //关闭订单
    zibpay::close_order($order_id, 'user', $reason);

    zib_send_json_success(['reload' => true, 'msg' => __('订单已关闭', 'zib_language')]);
}
add_action('wp_ajax_close_order', 'zibpay_ajax_close_order');
add_action('wp_ajax_nopriv_close_order', 'zibpay_ajax_close_order');

function zibpay_ajax_order_pay_modal($payment_id = null)
{
    $payment_id = $payment_id ?: (!empty($_REQUEST['payment_id']) ? (int) $_REQUEST['payment_id'] : 0);

    if (empty($payment_id)) {
        zib_ajax_notice_modal('danger', __('参数错误', 'zib_language'));
    }

    $payment_data = zibpay::get_payment($payment_id);
    if (empty($payment_data)) {
        zib_ajax_notice_modal('danger', __('支付数据不存在', 'zib_language'));
    }

    //判断订单是否已支付
    if ($payment_data['status'] == '1') {
        zib_ajax_notice_modal('success', __('订单已支付，请刷新页面', 'zib_language'));
    }

    //判断订单是否已经关闭
    if ($payment_data['status'] == '-1') {
        zib_ajax_notice_modal('danger', __('订单已关闭，请刷新页面', 'zib_language'));
    }

    $html = zibpay_get_order_pay_modal_content($payment_data);
    echo $html;
    exit;
}
add_action('wp_ajax_order_pay_modal', 'zibpay_ajax_order_pay_modal');
add_action('wp_ajax_nopriv_order_pay_modal', 'zibpay_ajax_order_pay_modal');

//获取订单支付模态框内容
function zibpay_get_order_pay_modal_content(array $payment_data, $return_url = null)
{
    $payment_id = $payment_data['id'];

    //判断订单是否失效
    $time_remaining = zibpay_get_payment_pay_over_time($payment_data);
    if ($time_remaining == 'over') {
        $header = zib_get_modal_colorful_header('jb-red', '<i class="fa fa-times-circle-o fa-2x" aria-hidden="true"></i>');
        $html   = $header;
        $html .= '<div class="em12 text-center c-red" style="padding: 30px 0;">' . __('订单已关闭，请重新下单', 'zib_language') . '</div>';
        return $html;
    }

    //根据$payment_id获取订单数据
    $orders_data = zibpay::get_order_by_payment_id($payment_id);
    if (empty($orders_data[0])) {
        $header = zib_get_modal_colorful_header('jb-red', '<i class="fa fa-times-circle-o fa-2x" aria-hidden="true"></i>');
        $html   = $header;
        $html .= '<div class="em12 text-center c-red" style="padding: 30px 0;">' . __('订单错误，请重新下单', 'zib_language') . '</div>';
        return $html;
    }

    //开始构建模态框
    $user_id             = get_current_user_id();
    $html                = '';
    $lists               = '';
    $_total_price        = 0;
    $_total_pay_price    = 0;
    $_total_count        = 0;
    $_total_shipping_fee = 0;
    $_i                  = 0;
    $_expand_count       = 3;
    $shop_s              = _pz('shop_s');
    $order_type          = 1; //第一个post_id
    $first_post_id       = 0;

    foreach ($orders_data as $order) {
        $_order_data  = zibpay::get_meta($order['id'], 'order_data');
        $post_id      = $order['post_id'];
        $_order_thumb = zibpay_get_order_order_thumb($order, 'mr10');
        $_opt_name    = '';
        $order_type   = $order['order_type'];
        $_count       = $_order_data['count'] ?? 1;
        $_total_count += $_count;
        $_i++;

        if (!$first_post_id) {
            $first_post_id = $post_id;
        }

        if (isset($_order_data['prices']['pay_price'])) {
            $_unit_price = $_order_data['prices']['unit_price']; //原价的单价
            $_total_price += $_order_data['prices']['total_price'];
            $_total_pay_price += $_order_data['prices']['pay_price'];
            $_total_shipping_fee += $_order_data['prices']['shipping_fee'] ?? 0;
        } else {
            $_unit_price = $order['order_price'];
            if (!$_unit_price && $order_type !== '10' && $_count == 1) {
                $_unit_price = zibpay_get_order_effective_amount($order);
            }
            $_total_price += $_unit_price;
            $_total_pay_price += $_unit_price;
        }

        $_title    = zibpay_get_order_title($order, 'text-ellipsis mb6');
        $_opt_name = $_opt_name ? '<div class="muted-color em09 text-ellipsis">' . $_opt_name . '</div>' : '';

        $lists .= '
            <div class="show-order-modal flex mb10 order-item border-bottom ac padding-h10' . ($_expand_count <= $_i ? ' hide' : '') . '" data-order-id="' . $order['id'] . '">
                ' . $_order_thumb . '
                <div class="flex1 flex jsb xx">
                    <div class="flex1 flex jsb">
                        <div class="flex1 mr20">
                            ' . $_title . $_opt_name . '
                            <div class="muted-color em09 mt6 text-ellipsis">' . $order['order_num'] . '</div>
                        </div>
                        <div class="flex xx ab">
                            <div class="unit-price">' . $_unit_price . '</div>
                            ' . ($_count ? '<div class="count mt6 muted-color">x' . $_count . '</div>' : '') . '
                        </div>
                    </div>
                </div>
                <i class="fa fa-angle-right em12 ml10 muted-2-color"></i>
            </div>
            ';
    }

    if ($_i >= $_expand_count) {
        $text = sprintf(__('查看其余%s笔订单信息', 'zib_language'), ($_i - $_expand_count + 1));
        $lists .= '<div class="pointer expand-toggle text-center mb10" closest-selector=".paid-modal-lists-box" expand-count="' . $_expand_count . '" expand-text="' . esc_attr($text) . '" collapse-text="' . esc_attr__('收起更多订单信息', 'zib_language') . '" >
                <div class="muted-2-color em09"><span class="btn-text">' . $text . '</span><i class="ml6 fa fa-angle-down"></i></div>
            </div>';
    }

    $is_points = $payment_data['method'] === 'points';
    $pay_mark  = $is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark();

    $total_html = '<div class="order-info-box muted-box mb10">
            <div class="order-info-body">
                <div class="order-info-item flex at jsb mb10">
                    <div class="item-label muted-color">
                        ' . __('支付单号', 'zib_language') . '
                    </div>
                    <div class="item-value">' . $payment_data['order_num'] . '</div>
                </div>
                <div class="order-info-item flex at jsb mb10">
                    <div class="item-label muted-color">
                        ' . __('总价', 'zib_language') . '
                        <span class="muted-3-color px12">' . sprintf(__('共%s件', 'zib_language'), $_total_count) . '</span>
                    </div>
                    <div class="item-value">
                        <div class="order-pay-prices-box flex abl xx">
                            <div class="order-pay-prices-item flex abl">
                                <span class="pay-mark px12">' . $pay_mark . '</span>
                                <span class="price-str">' . $_total_price . '</span>
                            </div>
                        </div>
                    </div>
                </div>
                ' . ($_total_shipping_fee ? '
                <div class="order-info-item flex ac jsb mb10">
                    <div class="item-label muted-color">' . __('运费', 'zib_language') . '</div>
                    <div class="item-value flex abl">
                        <span class="pay-mark px12">' . $pay_mark . '</span>
                        <span class="price-str">' . zib_floatval_round($_total_shipping_fee) . '</span>
                    </div>
                </div>
                ' : '') . (
        $_total_price - $_total_pay_price > 0 ? '
                <div class="order-info-item flex ac jsb mb10">
                    <div class="item-label muted-color">' . __('优惠', 'zib_language') . '</div>
                    <div class="item-value">
                        <div class="order-pay-prices-box flex abl xx">
                            <div class="order-pay-prices-item flex abl c-red">
                                <span class="mr3">-</span>
                                <span class="pay-mark px12">' . $pay_mark . '</span>
                                <span class="price-str">' . zib_floatval_round($_total_price - $_total_pay_price) . '</span>
                            </div>
                        </div>
                    </div>
                </div>' : '') . '
                <div class="order-info-item flex ac jsb">
                    <div class="item-label muted-color">' . __('合计', 'zib_language') . '</div>
                    <div class="item-value">
                        <div class="order-pay-prices-box flex abl xx">
                            <div class="order-pay-prices-item flex abl c-red em12">
                                <span class="pay-mark px12">' . $pay_mark . '</span>
                                <span class="price-str">' . zib_floatval_round($_total_pay_price) . '</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

    if (!$return_url) {
        if ($user_id) {
            $return_url = zib_get_user_center_url('orders');
        } elseif ($first_post_id) {
            //获取文章的链接
            $return_url = get_permalink($first_post_id);
        } else {
            $return_url = home_url();
        }
    }

    $form = '';
    if ($is_points) {
        $user_points = zibpay_get_user_points($user_id);

        $form .= '<div class="mb10 muted-box">';
        $form .= '<div class="flex jsb ab"><span class="muted-2-color">' . zib_get_svg('points-color', null, 'em12 mr6') . __('我的积分', 'zib_language') . '</span><div><span class="c-green">' . $pay_mark . '<span class="em14">' . $user_points . '</span></span></div></div>';
        $form .= '</div>';

        $form = '<form>';
        $form .= $return_url ? '<input type="hidden" name="return_url" value="' . $return_url . '">' : '';
        $form .= '<input type="hidden" name="payment_id" value="' . $payment_id . '">';
        $form .= '<button class="but jb-yellow padding-lg btn-block radius initiate-pay mt10" >' . __('立即支付', 'zib_language') . '<span class="ml6 px12">' . zibpay_get_points_mark() . '</span>' . $_total_pay_price . '</button>';
        $form .= '</form>';

        //如果积分不足
        if ($_total_pay_price > $user_points) {
            $points_pay_link = zibpay_get_points_pay_link('but c-green padding-lg', __('购买积分', 'zib_language'));
            $points_user_url = zib_get_user_center_url('balance');

            $form = '';
            $form .= '<div class="badg c-red btn-block mb20">' . __('抱歉，您的积分不足，暂时无法支付', 'zib_language') . '</div>';
            $form .= '<div class="modal-buts but-average"><a rel="nofollow" type="button" class="but padding-lg" href="' . $points_user_url . '">' . __('我的积分', 'zib_language') . '</a>' . $points_pay_link . '</div>';
        }

    } else {
        $form = '<form class="mt10">';
        $form .= '<input type="hidden" name="payment_modal" value="true">';
        $form .= '<input type="hidden" name="payment_id" value="' . $payment_id . '">';
        $form .= $return_url ? '<input type="hidden" name="return_url" value="' . $return_url . '">' : '';
        $form .= zibpay_get_initiate_pay_input($order_type, $_total_pay_price, $post_id, true);
        $form .= '</form>';
    }

    if ($time_remaining) {
        $time_remaining = zib_time_to_c($time_remaining);
        $time_remaining = '<span class="ml6 c-yellow px12 badg badg-sm" int-second="1" max-unit="minute" data-over-text="' . esc_attr__('交易已关闭', 'zib_language') . '" data-countdown="' . $time_remaining . '"><span class="minute"></span>:<span class="second"></span></span>';
    }

    $header = '<div class="touch border-title flex jc font-bold">' . __('立即支付', 'zib_language') . $time_remaining . '</div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $html   = $header . '<div class="mini-scrollbar scroll-y paid-modal-content"><div class="paid-modal-lists-box">' . $lists . '</div>' . $total_html . '</div>' . $form;

    return $html;
}

//AJAX获取用户提现收款设置模态框
function zibpay_ajax_user_withdraw_collection_set_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', __('请先登录', 'zib_language'));
    }

    //微信收款
    $weixin_html      = '';
    $rewards_img_urls = zib_get_user_rewards_img_urls($user_id);
    $img_add          = '<img style="width: 100%;" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg" alt="' . esc_attr__('点击上传收款码', 'zib_language') . '">';

    if (zibpay_payout_user_can_bind('wechat')) {
        $oauth_openid = get_user_meta($user_id, 'oauth_weixingzh_openid', true);
        if ($oauth_openid) {
            //已经绑定
            $oauth_info  = zib_get_user_meta($user_id, 'oauth_weixingzh_getUserInfo', true);
            $user_name   = !empty($oauth_info['name']) ? esc_attr($oauth_info['name']) : (!empty($oauth_info['nick_name']) ? esc_attr($oauth_info['nick_name']) : __('微信账号', 'zib_language'));
            $user_avatar = !empty($oauth_info['avatar']) ? $oauth_info['avatar'] : zib_default_avatar();
            $lazy_attr   = zib_get_lazy_attr('lazy_avatar', $user_avatar, 'avatar', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
            $user_avatar = '<span class="avatar-img"><img ' . $lazy_attr . ' alt="' . esc_attr(__('微信头像', 'zib_language') . zib_get_delimiter_blog_name()) . '"></span>';

            $weixin_html = '<div class="mb10 border-box flex ac gap10">
                <div class="shrink0">' . $user_avatar . '</div>
                <div class="flex1">
                    <div class="flex ac gap6">' . $user_name . '<span class="badg badg-sm c-green">' . __('已绑定', 'zib_language') . '</span></div>
                    <div class="muted-2-color mt6 em09">OpenID:' . zibpay_payout_mask_account($oauth_openid, 'wechat') . '</div>
                </div>
            </div>';

        } else {
            //未绑定
            $user_avatar = zib_default_avatar();
            $lazy_attr   = zib_get_lazy_attr('lazy_avatar', $user_avatar, 'avatar', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
            $link_html   = '<a href="' . esc_url(zib_get_oauth_login_url('weixingzh', zib_get_user_center_url('rebate'))) . '" class="but c-blue hollow' . (zib_weixingzh_is_qrcode() ? ' qrcode-signin' : '') . '">' . __('立即绑定', 'zib_language') . '</a>';
            $user_avatar = '<span class="avatar-img"><img ' . $lazy_attr . ' alt="' . esc_attr(__('微信头像', 'zib_language') . zib_get_delimiter_blog_name()) . '"></span>';
            $weixin_html = '<div class="mb10 border-box flex ac gap10">
                <div class="shrink0">' . $user_avatar . '</div>
                <div class="flex1">
                    <div class="flex ac gap6">' . __('微信账号', 'zib_language') . '<span class="badg badg-sm c-green">' . __('未绑定', 'zib_language') . '</span></div>
                    <div class="muted-2-color mt6 em09">' . __('绑定后可自动收款及微信快捷登录', 'zib_language') . '</div>
                </div>
                <div class="shrink0">' . $link_html . '</div>
            </div>';
        }
    }

    $weixin_img = $rewards_img_urls['weixin'];
    $weixin_img = $weixin_img ? '<img class="fit-cover" src="' . esc_attr($weixin_img) . '" alt="' . esc_attr__('微信收款码', 'zib_language') . '">' : $img_add;

    $weixin_html .= '
            <label style="width: 100%;" class="border-box flex gap20">
                    <div class="preview weixin upload-preview radius4 pointer" style="width:80px;height: 80px;margin:0">' . $weixin_img . '</div>
                    <div class="">
                        <div class=""><i class="fa fa-qrcode mr6" aria-hidden="true"></i>' . __('微信收款码', 'zib_language') . '<span class="ml6 badg badg-sm' . ($weixin_img ? ' c-green' : ' c-red') . '">' . ($weixin_img ? __('已上传', 'zib_language') : __('未上传', 'zib_language')) . '</span></div>
                        <div class="muted-2-color em09 mt6" style=" font-weight: normal; ">' . __('请确保收款码清晰可见', 'zib_language') . '</div>
                        <div class="c-blue but hollow mt10 p2-10 pointer"">' . ($weixin_img ? __('更换收款码', 'zib_language') : __('上传收款码', 'zib_language')) . '</div>
                        <input class="hide" type="file" zibupload="image_upload" data-preview=".preview.weixin" accept="image/gif,image/jpeg,image/jpg,image/png" data-tag="weixin" name="image_upload" action="image_upload">
                </div>
            </label>';

    $weixin_html = '<div class="border-box mb20">
    <div class="flex ac mb10"><div class="em12"><i class="fa fa-weixin mr6 c-green" aria-hidden="true"></i></div><div class="font-bold">' . __('微信收款', 'zib_language') . '</div></div>
    ' . $weixin_html .  '</div>';

    //支付宝
    $alipay_account = zibpay_get_user_payout_account($user_id, 'alipay');
    $alipay_html    = '';
    $alipay_html .= '<div class="border-box mb10">
        <div class="font-bold">' . __('支付宝账号', 'zib_language') . '</div>
        <div class="mt6">
            <input type="text" name="alipay_account" class="form-control" placeholder="' . __('请输入支付宝账号（手机号或邮箱）', 'zib_language') . '" value="' . esc_attr($alipay_account) . '">
        </div>
    </div>';

    $alipay_img = $rewards_img_urls['alipay'];
    $alipay_img = $alipay_img ? '<img class="fit-cover" src="' . esc_attr($alipay_img) . '" alt="' . esc_attr__('支付宝收款码', 'zib_language') . '">' : $img_add;
    $alipay_html .= '
    <label style="width: 100%;" class="border-box flex gap20">
            <div class="preview alipay upload-preview radius4 pointer" style="width:80px;height: 80px;margin:0">' . $alipay_img . '</div>
            <div class="">
                <div class=""><i class="fa fa-qrcode mr6" aria-hidden="true"></i>' . __('支付宝收款码', 'zib_language') . '<span class="ml6 badg badg-sm' . ($alipay_img ? ' c-green' : ' c-red') . '">' . ($alipay_img ? __('已上传', 'zib_language') : __('未上传', 'zib_language')) . '</span></div>
                <div class="muted-2-color em09 mt6" style=" font-weight: normal; ">' . __('请确保收款码清晰可见', 'zib_language') . '</div>
                <div class="c-blue but hollow mt10 p2-10 pointer"">' . ($alipay_img ? __('更换收款码', 'zib_language') : __('上传收款码', 'zib_language')) . '</div>
                <input class="hide" type="file" zibupload="image_upload" data-preview=".preview.alipay" accept="image/gif,image/jpeg,image/jpg,image/png" data-tag="alipay" name="image_upload" action="image_upload">
        </div>
    </label>';

    $alipay_html = '<div class="border-box mb20">
    <div class="flex ac mb10"><div class="em12">' . zib_get_svg('alipay', null, 'mr6 c-blue') . '</div><div class="font-bold">' . __('支付宝收款', 'zib_language') . '</div></div>
    ' . $alipay_html .  '</div>';

    //PayPal
    $paypal_html = '';
    if (zibpay_payout_user_can_bind('paypal')) {
        $paypal_email = zibpay_get_user_payout_account($user_id, 'paypal');
        $paypal_html .= '<div class="border-box mb20">
    <div class="flex ac mb10"><div class="em12"><i class="fa fa-paypal mr6 c-blue" aria-hidden="true"></i></div><div class="font-bold">' . __('PayPal收款', 'zib_language') . '</div></div>
        <div class="mt6">
            <input type="text" name="paypal_email" class="form-control" placeholder="' . __('请输入PayPal账户邮箱', 'zib_language') . '" value="' . esc_attr($paypal_email) . '">
        </div>
    </div>';
    }

    $form = '<form class="set-rewards-form mini-upload">';
    $form .= '<div class="mini-scrollbar scroll-y max-vh7">';
    $form .= $weixin_html;
    $form .= $alipay_html;
    $form .= $paypal_html;
    $form .= '</div>';

    $form .= '<input type="hidden" name="user_id" value="' . $user_id . '">';
    $form .= '<input type="hidden" name="action" value="user_set_withdraw_collection">';
    $form .= zib_nonce_field('user_set_withdraw_collection');
    $form .= '<div class="modal-buts but-average">';
    $form .= '<a type="button" data-dismiss="modal" class="but" href="javascript:;">' . __('取消', 'zib_language') . '</a><button type="button" action="info.upload" zibupload="submit" zibupload-nomust="true" class="but c-blue padding-lg" name="submit"><i class="fa fa-check mr10"></i>' . __('确认修改', 'zib_language') . '</button>';
    $form .= '</div>';
    $form .= '</form>';

    $header = '<div class="mb10 touch"><button class="close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><b class="modal-title flex ac"><span class="mr6 em14"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-money-color-2"></use></svg></span>' . __('设置收款账户', 'zib_language') . '</b></div>';

    $html = $header . $form;

    echo $html;
    exit;
}
add_action('wp_ajax_user_withdraw_collection_set_modal', 'zibpay_ajax_user_withdraw_collection_set_modal');

//AJAX处理用户提现收款设置
function zibpay_ajax_user_set_withdraw_collection()
{
    $cuid = get_current_user_id();
    if (!$cuid) {
        zib_send_json_error(__('请先登录', 'zib_language'));
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //保存微信收款码
    $weixin_lao_id = zib_get_user_meta($cuid, 'rewards_wechat_image_id', true);
    $weixin_is_ok  = $weixin_lao_id ? true : false;
    if (!empty($_FILES['weixin'])) {
        $weixin_img_id = zib_php_upload('weixin');
        if (!empty($weixin_img_id['error'])) {
            zib_send_json_error($weixin_img_id['msg']);
        }
        $weixin_is_ok = true;
        if ($weixin_lao_id) {
            wp_delete_attachment($weixin_lao_id, true);
        }
        zib_update_user_meta($cuid, 'rewards_wechat_image_id', $weixin_img_id);
    }

    //保存支付宝收款码
    $alipay_lao_id = zib_get_user_meta($cuid, 'rewards_alipay_image_id', true);
    $alipay_is_ok  = $alipay_lao_id ? true : false;
    if (!empty($_FILES['alipay'])) {
        $alipay_img_id = zib_php_upload('alipay');
        if (!empty($alipay_img_id['error'])) {
            zib_send_json_error($alipay_img_id['msg']);
        }
        $alipay_is_ok = true;
        if ($alipay_lao_id) {
            wp_delete_attachment($alipay_lao_id, true);
        }
        zib_update_user_meta($cuid, 'rewards_alipay_image_id', $alipay_img_id);
    }

    if (!$weixin_is_ok && zibpay_payout_user_can_bind('wechat')) {
        $weixin_is_ok = get_user_meta($cuid, 'oauth_weixingzh_openid', true) ? true : false;
    }

    //保存支付宝账号
    if (isset($_POST['alipay_account'])) {
        $account = sanitize_text_field(wp_unslash($_POST['alipay_account']));
        if (!$account) {
            zib_send_json_error(__('支付宝账号格式异常', 'zib_language'));
        }
        $alipay_is_ok = true;
        zib_update_user_meta($cuid, 'rewards_alipay_account', $account);
    }

    //保存PayPal账号
    $paypal_is_ok = false;
    if (isset($_POST['paypal_email'])) {
        $email = sanitize_email(wp_unslash($_POST['paypal_email']));
        if (!$email || !is_email($email)) {
            zib_send_json_error(__('PayPal 邮箱格式不正确', 'zib_language'));
        }
        $paypal_is_ok = true;
        zib_update_user_meta($cuid, 'rewards_paypal_email', $email);
    }

    if (!$weixin_is_ok && !$alipay_is_ok && !$paypal_is_ok) {
        zib_send_json_error(__('请至少完成一个收款设置', 'zib_language'));
    }

    //刷新缓存
    wp_cache_delete($cuid, 'user_rewards_img_urls');
    //重新获取
    zib_get_user_rewards_img_urls($cuid);

    echo(json_encode(array('error' => 0, 'hide_modal' => true, 'no_preview_reset' => 1, 'msg' => __('设置成功', 'zib_language'))));

    exit();

}
add_action('wp_ajax_user_set_withdraw_collection', 'zibpay_ajax_user_set_withdraw_collection');