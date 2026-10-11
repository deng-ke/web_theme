<?php
/*
 * Project       : Nova主题
 * Description   : 支付系统（订单/余额/VIP/充值卡/提现/积分购买/文章付费/打赏/支付通道）
 */

if (!defined('ABSPATH')) exit;

/* ============================================================
 * 订单核心类
 * ============================================================ */
class Novapay
{
    /**
     * 订单状态名称
     */
    public static function order_status_name($status)
    {
        $names = array(
            0 => __('未支付', 'nova'),
            1 => __('已支付', 'nova'),
            2 => __('待审核', 'nova'),
            3 => __('已关闭', 'nova'),
        );
        return isset($names[$status]) ? $names[$status] : $status;
    }

    /**
     * 订单类型名称
     */
    public static function order_type_name($type)
    {
        $names = array(
            'post'     => __('文章购买', 'nova'),
            'shop'     => __('商城购买', 'nova'),
            'vip'      => __('VIP会员', 'nova'),
            'points'   => __('积分充值', 'nova'),
            'reward'   => __('文章打赏', 'nova'),
        );
        return isset($names[$type]) ? $names[$type] : $type;
    }

    /**
     * 支付方式名称
     */
    public static function pay_type_name($type)
    {
        $names = array(
            'alipay' => __('支付宝', 'nova'),
            'wechat' => __('微信支付', 'nova'),
            'balance' => __('余额支付', 'nova'),
            'bank'   => __('转账支付', 'nova'),
        );
        return isset($names[$type]) ? $names[$type] : $type;
    }

    /**
     * 创建订单
     * @param array $args order_type/post_id/user_id/order_price/pay_type
     * @return int|WP_Error
     */
    public static function create_order($args)
    {
        global $wpdb;
        $defaults = array(
            'user_id'     => get_current_user_id(),
            'post_id'     => 0,
            'order_type'  => 'post',
            'pay_type'    => 'alipay',
            'order_price' => 0,
            'pay_price'   => 0,
            'order_num'   => '',
        );
        $args = wp_parse_args($args, $defaults);
        $args['order_price'] = round((float) $args['order_price'], 2);
        $args['pay_price']   = round((float) $args['pay_price'], 2);
        if ($args['pay_price'] <= 0) return new WP_Error('nova_pay', '金额错误');
        if (!$args['order_num']) {
            $args['order_num'] = date('YmdHis') . mt_rand(100000, 999999);
        }
        $table = $wpdb->prefix . 'nova_order';
        $result = $wpdb->insert($table, array(
            'order_num'    => $args['order_num'],
            'user_id'      => $args['user_id'],
            'post_id'      => $args['post_id'],
            'order_type'   => $args['order_type'],
            'pay_type'     => $args['pay_type'],
            'order_price'  => $args['order_price'],
            'pay_price'    => $args['pay_price'],
            'order_status' => 0,
            'create_time'  => current_time('mysql'),
            'ip_address'   => nova_get_ip(),
        ));
        if (!$result) return new WP_Error('nova_pay', '订单创建失败');
        return $wpdb->insert_id;
    }

    /**
     * 获取订单
     */
    public static function get_order($order_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_order';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $order_id));
    }

    /**
     * 通过订单号获取订单
     */
    public static function get_order_by_num($order_num)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_order';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE order_num = %s", $order_num));
    }

    /**
     * 获取用户订单列表
     */
    public static function get_user_orders($user_id, $paged = 1, $per_page = 10)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_order';
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC LIMIT %d, %d", $user_id, ($paged - 1) * $per_page, $per_page);
        return $wpdb->get_results($sql);
    }

    /**
     * 更新订单状态
     */
    public static function update_status($order_id, $status)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_order';
        $wpdb->update($table, array('order_status' => $status), array('id' => $order_id));
    }

    /**
     * 可用支付通道（按设置开关）
     */
    public static function get_channels()
    {
        $channels = array();
        if (nova_get_setting('pay_balance_s', '1')) {
            $channels['balance'] = array('name' => __('余额支付', 'nova'), 'icon' => 'nova-icon-money');
        }
        if (nova_get_setting('pay_alipay_s', '1')) {
            $channels['alipay'] = array('name' => __('支付宝', 'nova'), 'icon' => 'nova-icon-alipay');
        }
        if (nova_get_setting('pay_wechat_s', '0')) {
            $channels['wechat'] = array('name' => __('微信支付', 'nova'), 'icon' => 'nova-icon-wechat');
        }
        if (nova_get_setting('pay_bank_s', '1')) {
            $channels['bank'] = array('name' => __('转账支付', 'nova'), 'icon' => 'nova-icon-bank');
        }
        return apply_filters('nova_pay_channels', $channels);
    }

    /**
     * 支付成功统一处理（各通道回调/余额支付/后台确认均调用此方法）
     * @param object $order 订单行
     * @param string $pay_num 第三方支付流水号
     */
    public static function pay_success($order, $pay_num = '')
    {
        if (!$order || 1 == $order->order_status) return false; //已支付过

        global $wpdb;
        $table = $wpdb->prefix . 'nova_order';
        $wpdb->update($table, array(
            'order_status' => 1,
            'pay_num'      => $pay_num,
            'pay_time'     => current_time('mysql'),
        ), array('id' => $order->id));

        $user_id  = (int) $order->user_id;
        $post_id  = (int) $order->post_id;
        $pay_price = (float) $order->pay_price;

        switch ($order->order_type) {
            case 'post': //文章购买
                nova_order_add_paid_post($user_id, $post_id);
                NovaMsg::add_system($user_id, sprintf(__('你已成功购买文章《%s》，感谢支持！', 'nova'), get_the_title($post_id)));
                nova_order_author_profit($post_id, $pay_price);
                break;
            case 'shop': //商城购买
                nova_order_pay_shop($order->id);
                NovaMsg::add_system($user_id, sprintf(__('订单 %s 支付成功，我们将尽快发货', 'nova'), $order->order_num));
                break;
            case 'vip': //VIP会员
                nova_vip_add_time($user_id, (int) nova_order_get_meta($order->id, 'vip_days', 30));
                NovaMsg::add_system($user_id, sprintf(__('VIP会员开通成功，有效期至 %s', 'nova'), date('Y-m-d H:i', (int) get_user_meta($user_id, 'nova_vip_end', true))));
                break;
            case 'points': //积分充值
                $points = (int) nova_order_get_meta($order->id, 'points_num', 0);
                NovaDB::update_user_points($user_id, $points, 'add', __('积分充值', 'nova'));
                NovaMsg::add_system($user_id, sprintf(__('积分充值成功，到账 %s 积分', 'nova'), $points));
                break;
            case 'reward': //文章打赏
                NovaMsg::add_system($user_id, sprintf(__('你对《%s》的打赏已支付成功', 'nova'), get_the_title($post_id)));
                nova_order_author_profit($post_id, $pay_price);
                break;
        }
        do_action('nova_pay_success', $order);
        return true;
    }
}

/* ============================================================
 * 订单附加信息（独立 meta 表）
 * ============================================================ */
function nova_order_update_meta($order_id, $meta_key, $meta_value)
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_order_meta';
    $exists = $wpdb->get_var($wpdb->prepare("SELECT meta_id FROM $table WHERE order_id = %d AND meta_key = %s", $order_id, $meta_key));
    if ($exists) {
        $wpdb->update($table, array('meta_value' => maybe_serialize($meta_value)), array('meta_id' => $exists));
    } else {
        $wpdb->insert($table, array(
            'order_id'   => $order_id,
            'meta_key'   => $meta_key,
            'meta_value' => maybe_serialize($meta_value),
        ));
    }
}

function nova_order_get_meta($order_id, $meta_key, $default = '')
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_order_meta';
    $value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table WHERE order_id = %d AND meta_key = %s", $order_id, $meta_key));
    if (null === $value) return $default;
    return maybe_unserialize($value);
}

/* ============================================================
 * 用户已购买文章
 * ============================================================ */
function nova_order_add_paid_post($user_id, $post_id)
{
    $paid = get_user_meta($user_id, 'nova_paid_posts', true);
    if (!is_array($paid)) $paid = array();
    if (!in_array($post_id, $paid)) {
        $paid[] = $post_id;
        update_user_meta($user_id, 'nova_paid_posts', $paid);
    }
    //更新文章购买人数
    $count = (int) get_post_meta($post_id, 'nova_pay_count', true);
    update_post_meta($post_id, 'nova_pay_count', $count + 1);
}

/**
 * 文章作者收益分成
 */
function nova_order_author_profit($post_id, $pay_price)
{
    $percent = (float) nova_get_setting('post_sale_percent', 70);
    if ($percent <= 0) return;
    $author_id = (int) get_post_field('post_author', $post_id);
    if (!$author_id) return;
    $profit = round($pay_price * $percent / 100, 2);
    if ($profit <= 0) return;
    NovaDB::update_user_balance($author_id, $profit, sprintf(__('《%s》付费收益', 'nova'), get_the_title($post_id)));
}

/**
 * 商城订单支付成功：减库存、加销量
 */
function nova_order_pay_shop($order_id)
{
    $items = nova_order_get_meta($order_id, 'nova_order_items', array());
    if (empty($items)) return;
    foreach ($items as $item) {
        $product_id = isset($item['id']) ? (int) $item['id'] : 0;
        $qty = isset($item['qty']) ? (int) $item['qty'] : 0;
        if (!$product_id || !$qty) continue;
        $stock = nova_shop_get_stock($product_id);
        $sales = nova_shop_get_sales($product_id);
        update_post_meta($product_id, 'nova_shop_stock', max(0, $stock - $qty));
        update_post_meta($product_id, 'nova_shop_sales', $sales + $qty);
    }
}

/* ============================================================
 * VIP 会员系统
 * ============================================================ */
/**
 * VIP等级列表（设置中 JSON 配置）
 */
function nova_vip_levels()
{
    $levels = array();
    if (nova_get_setting('vip_levels')) {
        $data = json_decode(nova_get_setting('vip_levels'), true);
        if (is_array($data)) $levels = $data;
    }
    if (empty($levels)) {
        //默认等级
        $levels = array(
            array('name' => __('月度会员', 'nova'), 'price' => 19.9, 'days' => 30),
            array('name' => __('季度会员', 'nova'), 'price' => 49.9, 'days' => 90),
            array('name' => __('年度会员', 'nova'), 'price' => 168, 'days' => 365),
        );
    }
    return $levels;
}

/**
 * 用户是否VIP
 */
function nova_is_vip($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return false;
    $end = (int) get_user_meta($user_id, 'nova_vip_end', true);
    return $end > current_time('U');
}

/**
 * 延长VIP时间
 */
function nova_vip_add_time($user_id, $days)
{
    $end = (int) get_user_meta($user_id, 'nova_vip_end', true);
    $end = max($end, current_time('U'));
    $end = $end + max(1, (int) $days) * DAY_IN_SECONDS;
    update_user_meta($user_id, 'nova_vip_end', $end);
    update_user_meta($user_id, 'nova_vip_type', 'vip');
    return $end;
}

/**
 * VIP购买页面内容
 */
function nova_vip_html()
{
    if (!nova_get_setting('vip_s', '1')) {
        return nova_empty_box(__('VIP功能未开启', 'nova'), __('管理员暂未开启VIP会员功能', 'nova'));
    }
    $levels = nova_vip_levels();
    $is_vip = nova_is_vip();

    $html = '<div class="nova-vip-page">';
    $html .= '<div class="nova-vip-head card">';
    $html .= '<div class="nova-vip-head-icon"><i class="nova-icon-vip"></i></div>';
    $html .= '<div class="nova-vip-head-info">';
    $html .= '<h2>' . esc_html__('VIP会员', 'nova') . '</h2>';
    if ($is_vip) {
        $html .= '<p class="muted-2-color">' . sprintf(__('会员有效期至 %s', 'nova'), date('Y-m-d H:i', (int) get_user_meta(get_current_user_id(), 'nova_vip_end', true))) . '</p>';
    } else {
        $html .= '<p class="muted-2-color">' . esc_html__('开通VIP会员，尊享全站特权', 'nova') . '</p>';
    }
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="nova-vip-grid">';
    foreach ($levels as $level) {
        $price = (float) (isset($level['price']) ? $level['price'] : 0);
        $days  = (int) (isset($level['days']) ? $level['days'] : 30);
        $name  = isset($level['name']) ? $level['name'] : '';
        $html .= '<div class="nova-vip-card card">';
        $html .= '<div class="nova-vip-name">' . esc_html($name) . '</div>';
        $html .= '<div class="nova-vip-price">¥ <b>' . number_format($price, 2) . '</b></div>';
        $html .= '<div class="nova-vip-days muted-2-color">' . sprintf(__('有效期 %s 天', 'nova'), $days) . '</div>';
        $html .= '<button class="btn btn-primary nova-vip-buy" data-days="' . $days . '" data-price="' . $price . '" data-name="' . esc_attr($name) . '">' . esc_html__('立即开通', 'nova') . '</button>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * AJAX 创建VIP订单
 */
function nova_ajax_buy_vip()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('vip_s', '1')) nova_ajax_error('VIP功能未开启');

    $days  = isset($_POST['days']) ? (int) $_POST['days'] : 0;
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $name  = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : __('VIP会员', 'nova');
    if ($days <= 0 || $price <= 0) nova_ajax_error('参数错误');

    $order_id = Novapay::create_order(array(
        'user_id'     => $user_id,
        'order_type'  => 'vip',
        'pay_type'    => 'alipay',
        'order_price' => $price,
        'pay_price'   => $price,
    ));
    if (is_wp_error($order_id)) nova_ajax_error($order_id->get_error_message());
    nova_order_update_meta($order_id, 'vip_days', $days);
    nova_order_update_meta($order_id, 'vip_name', $name);
    nova_ajax_success(array('order_id' => $order_id), '订单创建成功');
}
add_action('wp_ajax_nova_buy_vip', 'nova_ajax_buy_vip');

/* ============================================================
 * 充值卡系统
 * ============================================================ */
/**
 * 生成充值卡（后台调用）
 */
function nova_card_generate($num = 1, $price = 10, $type = 'money')
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_charge';
    $cards = array();
    for ($i = 0; $i < $num; $i++) {
        $card_num  = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));
        $card_pass = strtoupper(substr(md5(uniqid(mt_rand(), true) . time()), 0, 8));
        $wpdb->insert($table, array(
            'card_num'    => $card_num,
            'card_pass'   => $card_pass,
            'card_price'  => round((float) $price, 2),
            'card_type'   => $type,
            'card_status' => 0,
            'create_time' => current_time('mysql'),
        ));
        $cards[] = array('num' => $card_num, 'pass' => $card_pass);
    }
    return $cards;
}

/**
 * 使用充值卡
 */
function nova_card_use($card_num, $card_pass, $user_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_charge';
    $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE card_num = %s", $card_num));
    if (!$card) return new WP_Error('nova_card', '充值卡不存在');
    if ($card->card_status) return new WP_Error('nova_card', '充值卡已被使用');
    if ($card->card_pass !== strtoupper($card_pass)) return new WP_Error('nova_card', '卡密错误');

    $wpdb->update($table, array(
        'card_status' => 1,
        'user_id'     => $user_id,
        'use_time'    => current_time('mysql'),
    ), array('id' => $card->id));

    if ('points' === $card->card_type) {
        NovaDB::update_user_points($user_id, (int) $card->card_price, 'add', __('充值卡兑换积分', 'nova'));
        NovaMsg::add_system($user_id, sprintf(__('充值卡兑换成功，到账 %s 积分', 'nova'), (int) $card->card_price));
    } else {
        NovaDB::update_user_balance($user_id, (float) $card->card_price, __('充值卡充值余额', 'nova'));
        NovaMsg::add_system($user_id, sprintf(__('充值卡充值成功，到账 ¥%s 余额', 'nova'), number_format((float) $card->card_price, 2)));
    }
    return $card;
}

/**
 * 充值卡兑换页面
 */
function nova_card_html()
{
    if (!nova_get_setting('card_s', '1')) {
        return nova_empty_box(__('充值卡功能未开启', 'nova'), __('管理员暂未开启充值卡功能', 'nova'));
    }
    $html = '<div class="nova-card-page">';
    $html .= '<div class="nova-card-form card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('充值卡兑换', 'nova') . '</span></h3>';
    $html .= '<p class="muted-2-color">' . esc_html__('输入卡号和卡密，兑换余额或积分', 'nova') . '</p>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('卡号', 'nova') . '</label><input type="text" class="nova-card-num" placeholder="' . esc_attr__('请输入卡号', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('卡密', 'nova') . '</label><input type="text" class="nova-card-pass" placeholder="' . esc_attr__('请输入卡密', 'nova') . '"></div>';
    $html .= '<button class="btn btn-primary btn-block nova-card-submit">' . esc_html__('立即兑换', 'nova') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * AJAX 使用充值卡
 */
function nova_ajax_use_card()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('card_s', '1')) nova_ajax_error('充值卡功能未开启');

    $card_num  = isset($_POST['card_num']) ? sanitize_text_field(wp_unslash($_POST['card_num'])) : '';
    $card_pass = isset($_POST['card_pass']) ? sanitize_text_field(wp_unslash($_POST['card_pass'])) : '';
    if (!$card_num || !$card_pass) nova_ajax_error('请填写完整信息');

    $result = nova_card_use($card_num, $card_pass, $user_id);
    if (is_wp_error($result)) nova_ajax_error($result->get_error_message());
    nova_ajax_success(array(), '兑换成功');
}
add_action('wp_ajax_nova_use_card', 'nova_ajax_use_card');

/* ============================================================
 * 提现系统
 * ============================================================ */
/**
 * 提现页面
 */
function nova_withdraw_html()
{
    if (!nova_get_setting('withdraw_s', '1')) {
        return nova_empty_box(__('提现功能未开启', 'nova'), __('管理员暂未开启提现功能', 'nova'));
    }
    $user_id = get_current_user_id();
    $balance = NovaDB::get_user_balance($user_id);
    $min = (float) nova_get_setting('withdraw_min', 10);

    global $wpdb;
    $table = $wpdb->prefix . 'nova_withdraw';
    $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY id DESC LIMIT 10", $user_id));

    $html = '<div class="nova-withdraw-page">';
    $html .= '<div class="nova-withdraw-form card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('余额提现', 'nova') . '</span></h3>';
    $html .= '<div class="nova-withdraw-balance">' . esc_html__('当前余额：', 'nova') . ' <b>¥ ' . number_format($balance, 2) . '</b></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('提现金额', 'nova') . '</label><input type="number" class="nova-withdraw-amount" min="' . $min . '" max="' . $balance . '" step="0.01" placeholder="' . sprintf(esc_attr__('最低提现 ¥%s', 'nova'), $min) . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('收款方式', 'nova') . '</label><select class="nova-withdraw-type"><option value="alipay">' . esc_html__('支付宝', 'nova') . '</option><option value="wechat">' . esc_html__('微信', 'nova') . '</option></select></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('收款账号', 'nova') . '</label><input type="text" class="nova-withdraw-account" placeholder="' . esc_attr__('收款账号', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('真实姓名', 'nova') . '</label><input type="text" class="nova-withdraw-name" placeholder="' . esc_attr__('真实姓名', 'nova') . '"></div>';
    $html .= '<button class="btn btn-primary btn-block nova-withdraw-submit">' . esc_html__('提交申请', 'nova') . '</button>';
    $html .= '</div>';

    if (!empty($records)) {
        $html .= '<div class="nova-withdraw-list card">';
        $html .= '<h3 class="nova-block-title"><span>' . esc_html__('提现记录', 'nova') . '</span></h3>';
        foreach ($records as $record) {
            $status_name = array(0 => __('待处理', 'nova'), 1 => __('已完成', 'nova'), 2 => __('已拒绝', 'nova'));
            $html .= '<div class="nova-withdraw-item">';
            $html .= '<div class="nova-withdraw-info"><b>¥ ' . number_format((float) $record->withdraw_amount, 2) . '</b><span class="muted-2-color">' . esc_html($status_name[$record->withdraw_status]) . '</span></div>';
            $html .= '<div class="muted-2-color">' . esc_html(nova_time_ago($record->create_time)) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * AJAX 提交提现
 */
function nova_ajax_withdraw()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('withdraw_s', '1')) nova_ajax_error('提现功能未开启');

    $amount  = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $type    = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'alipay';
    $account = isset($_POST['account']) ? sanitize_text_field(wp_unslash($_POST['account'])) : '';
    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

    $min = (float) nova_get_setting('withdraw_min', 10);
    if ($amount < $min) nova_ajax_error(sprintf('最低提现 ¥%s', $min));
    if (!$account || !$name) nova_ajax_error('请填写完整的收款信息');

    $balance = NovaDB::get_user_balance($user_id);
    if ($amount > $balance) nova_ajax_error('余额不足');

    global $wpdb;
    $table = $wpdb->prefix . 'nova_withdraw';
    $wpdb->insert($table, array(
        'user_id'          => $user_id,
        'withdraw_type'    => $type,
        'withdraw_account' => $account,
        'withdraw_name'    => $name,
        'withdraw_amount'  => round($amount, 2),
        'withdraw_status'  => 0,
        'create_time'      => current_time('mysql'),
    ));
    //冻结提现金额
    NovaDB::update_user_balance($user_id, -$amount, __('申请提现（待处理）', 'nova'));
    nova_ajax_success(array(), '提现申请已提交，等待处理');
}
add_action('wp_ajax_nova_withdraw', 'nova_ajax_withdraw');

/* ============================================================
 * 积分购买
 * ============================================================ */
/**
 * AJAX 创建积分充值订单
 */
function nova_ajax_buy_points()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $money  = isset($_POST['money']) ? (float) $_POST['money'] : 0;
    $rate   = (int) nova_get_setting('points_buy_rate', 10); //1元 = N积分
    if ($money <= 0) nova_ajax_error('金额错误');
    $points = round($money * $rate);

    $order_id = Novapay::create_order(array(
        'user_id'     => $user_id,
        'order_type'  => 'points',
        'pay_type'    => 'alipay',
        'order_price' => $money,
        'pay_price'   => $money,
    ));
    if (is_wp_error($order_id)) nova_ajax_error($order_id->get_error_message());
    nova_order_update_meta($order_id, 'points_num', $points);
    nova_ajax_success(array('order_id' => $order_id, 'points' => $points), '订单创建成功');
}
add_action('wp_ajax_nova_buy_points', 'nova_ajax_buy_points');

/* ============================================================
 * 文章付费购买与打赏
 * ============================================================ */
/**
 * AJAX 创建文章购买订单（余额/积分/第三方）
 */
function nova_ajax_buy_post()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('pay_s', '1')) nova_ajax_error('支付功能未开启');

    $post_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $pay_way = isset($_POST['pay_way']) ? sanitize_text_field(wp_unslash($_POST['pay_way'])) : 'money';
    $post = get_post($post_id);
    if (!$post) nova_ajax_error('文章不存在');

    //已购买直接解锁
    $paid = get_user_meta($user_id, 'nova_paid_posts', true);
    if (is_array($paid) && in_array($post_id, $paid)) nova_ajax_success(array(), '已解锁');

    $pay_price  = (float) get_post_meta($post_id, 'nova_pay_price', true);
    $pay_points = (int) get_post_meta($post_id, 'nova_pay_points', true);

    if ('points' === $pay_way) {
        //积分支付直接完成
        if ($pay_points <= 0) nova_ajax_error('此文章不支持积分购买');
        $points = NovaDB::get_user_points($user_id);
        if ($points < $pay_points) nova_ajax_error('积分不足');
        NovaDB::update_user_points($user_id, -$pay_points, 'sub', sprintf(__('购买文章《%s》', 'nova'), $post->post_title));
        nova_order_add_paid_post($user_id, $post_id);
        nova_ajax_success(array('url' => get_permalink($post_id)), '购买成功');
    }

    //余额支付
    $pay_type = isset($_POST['pay_type']) ? sanitize_text_field(wp_unslash($_POST['pay_type'])) : 'alipay';
    if ('balance' === $pay_type) {
        if ($pay_price <= 0) nova_ajax_error('价格错误');
        $balance = NovaDB::get_user_balance($user_id);
        if ($balance < $pay_price) nova_ajax_error('余额不足');
        NovaDB::update_user_balance($user_id, -$pay_price, sprintf(__('购买文章《%s》', 'nova'), $post->post_title));
        nova_order_add_paid_post($user_id, $post_id);
        nova_order_author_profit($post_id, $pay_price);
        NovaMsg::add_system($user_id, sprintf(__('你已成功购买文章《%s》，感谢支持！', 'nova'), $post->post_title));
        nova_ajax_success(array('url' => get_permalink($post_id)), '购买成功');
    }

    //第三方支付创建订单
    $order_id = Novapay::create_order(array(
        'user_id'     => $user_id,
        'post_id'     => $post_id,
        'order_type'  => 'post',
        'pay_type'    => $pay_type,
        'order_price' => $pay_price,
        'pay_price'   => $pay_price,
    ));
    if (is_wp_error($order_id)) nova_ajax_error($order_id->get_error_message());
    nova_ajax_success(array('order_id' => $order_id), '订单创建成功');
}
add_action('wp_ajax_nova_buy_post', 'nova_ajax_buy_post');

/**
 * AJAX 创建打赏订单
 */
function nova_ajax_reward_post()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('pay_s', '1')) nova_ajax_error('支付功能未开启');

    $post_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $money   = isset($_POST['money']) ? (float) $_POST['money'] : 0;
    $pay_type = isset($_POST['pay_type']) ? sanitize_text_field(wp_unslash($_POST['pay_type'])) : 'alipay';
    if (!$post_id || $money <= 0) nova_ajax_error('参数错误');

    $order_id = Novapay::create_order(array(
        'user_id'     => $user_id,
        'post_id'     => $post_id,
        'order_type'  => 'reward',
        'pay_type'    => $pay_type,
        'order_price' => $money,
        'pay_price'   => $money,
    ));
    if (is_wp_error($order_id)) nova_ajax_error($order_id->get_error_message());
    nova_ajax_success(array('order_id' => $order_id), '订单创建成功');
}
add_action('wp_ajax_nova_reward_post', 'nova_ajax_reward_post');

/* ============================================================
 * 余额支付订单（支付页使用）
 * ============================================================ */
function nova_ajax_pay_balance()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('pay_balance_s', '1')) nova_ajax_error('余额支付未开启');

    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $order = Novapay::get_order($order_id);
    if (!$order || $order->user_id != $user_id) nova_ajax_error('订单不存在');
    if ($order->order_status != 0) nova_ajax_error('订单状态异常');

    $balance = NovaDB::get_user_balance($user_id);
    if ($balance < (float) $order->pay_price) nova_ajax_error('余额不足');

    NovaDB::update_user_balance($user_id, -(float) $order->pay_price, sprintf(__('订单 %s 支付', 'nova'), $order->order_num));
    Novapay::pay_success($order, 'balance');
    nova_ajax_success(array('url' => nova_user_center_url('orders')), '支付成功');
}
add_action('wp_ajax_nova_pay_balance', 'nova_ajax_pay_balance');

/**
 * 查询订单支付状态（微信扫码轮询用）
 */
function nova_ajax_pay_status()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请登录');

    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $order = Novapay::get_order($order_id);
    if (!$order) nova_ajax_error('订单不存在');
    if ($order->user_id != $user_id) nova_ajax_error('无权查询');

    nova_ajax_success(array('status' => (int) $order->order_status));
}
add_action('wp_ajax_nova_pay_status', 'nova_ajax_pay_status');

/* ============================================================
 * 线下转账：提交凭证（等待管理员审核）
 * ============================================================ */
function nova_ajax_pay_bank_submit()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_get_setting('pay_bank_s', '1')) nova_ajax_error('转账支付未开启');

    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $remark   = isset($_POST['remark']) ? sanitize_text_field(wp_unslash($_POST['remark'])) : '';
    $order = Novapay::get_order($order_id);
    if (!$order || $order->user_id != $user_id) nova_ajax_error('订单不存在');
    if ($order->order_status != 0) nova_ajax_error('订单状态异常');

    nova_order_update_meta($order_id, 'bank_remark', $remark);
    Novapay::update_status($order_id, 2); //待审核
    NovaMsg::add_system($user_id, sprintf(__('订单 %s 转账凭证已提交，等待管理员确认', 'nova'), $order->order_num));
    nova_ajax_success(array(), '凭证已提交，等待确认');
}
add_action('wp_ajax_nova_pay_bank_submit', 'nova_ajax_pay_bank_submit');

/* ============================================================
 * 支付宝支付
 * ============================================================ */
/**
 * 生成支付宝网页支付跳转链接（RSA2签名）
 */
function nova_alipay_pay_url($order)
{
    $appid = nova_get_setting('pay_alipay_appid');
    $private_key = nova_get_setting('pay_alipay_private_key');
    if (!$appid || !$private_key) return '';

    $biz = array(
        'out_trade_no'   => $order->order_num,
        'product_code'   => 'FAST_INSTANT_TRADE_PAY',
        'total_amount'   => number_format((float) $order->pay_price, 2, '.', ''),
        'subject'        => mb_substr(Novapay::order_type_name($order->order_type), 0, 40),
    );
    $params = array(
        'app_id'      => $appid,
        'method'      => 'alipay.trade.page.pay',
        'format'      => 'JSON',
        'charset'     => 'utf-8',
        'sign_type'   => 'RSA2',
        'timestamp'   => current_time('Y-m-d H:i:s'),
        'version'     => '1.0',
        'notify_url'  => home_url('/nova-pay-notify/alipay'),
        'return_url'  => home_url('/nova-pay-return/alipay'),
        'biz_content' => wp_json_encode($biz),
    );
    ksort($params);
    $str = '';
    foreach ($params as $k => $v) {
        $str .= $k . '=' . $v . '&';
    }
    $str = rtrim($str, '&');
    $sign = '';
    openssl_sign($str, $sign, $private_key, OPENSSL_ALGO_SHA256);
    $params['sign'] = base64_encode($sign);
    return 'https://openapi.alipay.com/gateway.do?' . http_build_query($params);
}

/**
 * 验证支付宝异步通知签名
 */
function nova_alipay_verify_notify($post)
{
    $public_key = nova_get_setting('pay_alipay_public_key');
    if (!$public_key) return false;
    $sign = isset($post['sign']) ? $post['sign'] : '';
    $sign_type = isset($post['sign_type']) ? $post['sign_type'] : 'RSA2';
    unset($post['sign'], $post['sign_type']);
    ksort($post);
    $str = '';
    foreach ($post as $k => $v) {
        $str .= $k . '=' . $v . '&';
    }
    $str = rtrim($str, '&');
    $result = false;
    if ('RSA2' === $sign_type) {
        $result = (bool) openssl_verify($str, base64_decode($sign), $public_key, OPENSSL_ALGO_SHA256);
    }
    return $result;
}

/* ============================================================
 * 微信支付（Native扫码，MD5签名）
 * ============================================================ */
function nova_wechat_sign($params, $key)
{
    ksort($params);
    $str = '';
    foreach ($params as $k => $v) {
        if ('' !== $v && 'sign' !== $k) {
            $str .= $k . '=' . $v . '&';
        }
    }
    $str .= 'key=' . $key;
    return strtoupper(md5($str));
}

function nova_wechat_array_to_xml($params)
{
    $xml = '<xml>';
    foreach ($params as $k => $v) {
        $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
    }
    $xml .= '</xml>';
    return $xml;
}

function nova_wechat_xml_to_array($xml)
{
    if (!$xml) return array();
    libxml_disable_entity_loader(true);
    $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return is_array($arr) ? $arr : array();
}

/**
 * 创建微信Native支付二维码链接
 */
function nova_wechat_pay_code_url($order)
{
    $appid  = nova_get_setting('pay_wechat_appid');
    $mch_id = nova_get_setting('pay_wechat_mch_id');
    $key    = nova_get_setting('pay_wechat_key');
    if (!$appid || !$mch_id || !$key) return '';

    $params = array(
        'appid'            => $appid,
        'mch_id'           => $mch_id,
        'nonce_str'        => strtoupper(md5(uniqid(mt_rand(), true))),
        'body'             => mb_substr(Novapay::order_type_name($order->order_type), 0, 40),
        'out_trade_no'     => $order->order_num,
        'total_fee'        => (string) round((float) $order->pay_price * 100),
        'spbill_create_ip' => nova_get_ip(),
        'notify_url'       => home_url('/nova-pay-notify/wechat'),
        'trade_type'       => 'NATIVE',
    );
    $params['sign'] = nova_wechat_sign($params, $key);
    $xml = nova_wechat_array_to_xml($params);
    $response = wp_remote_post('https://api.mch.weixin.qq.com/pay/unifiedorder', array(
        'timeout' => 30,
        'body'    => $xml,
    ));
    if (is_wp_error($response)) return '';
    $result = nova_wechat_xml_to_array(wp_remote_retrieve_body($response));
    if (isset($result['return_code']) && 'SUCCESS' === $result['return_code'] && isset($result['code_url'])) {
        return $result['code_url'];
    }
    return '';
}

/* ============================================================
 * 支付回调与同步通知路由
 * ============================================================ */
function nova_pay_add_rewrite()
{
    add_rewrite_rule('^nova-pay-notify/(alipay|wechat)/?$', 'index.php?nova_pay_notify=$matches[1]', 'top');
    add_rewrite_rule('^nova-pay-return/(alipay|wechat)/?$', 'index.php?nova_pay_return=$matches[1]', 'top');
    add_rewrite_rule('^nova-pay-gateway/?$', 'index.php?nova_pay_gateway=1', 'top');
}
add_action('init', 'nova_pay_add_rewrite');

function nova_pay_query_vars($vars)
{
    $vars[] = 'nova_pay_notify';
    $vars[] = 'nova_pay_return';
    $vars[] = 'nova_pay_gateway';
    return $vars;
}
add_filter('query_vars', 'nova_pay_query_vars');

/**
 * 支付回调与返回处理
 */
function nova_pay_route()
{
    $notify = get_query_var('nova_pay_notify');
    $return = get_query_var('nova_pay_return');
    if (!$notify && !$return) return;

    if ($notify) {
        //异步通知
        if ('alipay' === $notify) {
            $post = $_POST; // phpcs:ignore
            if (nova_alipay_verify_notify($post)) {
                $order_num = isset($post['out_trade_no']) ? sanitize_text_field(wp_unslash($post['out_trade_no'])) : '';
                $order = Novapay::get_order_by_num($order_num);
                if ($order && 'TRADE_SUCCESS' === (isset($post['trade_status']) ? $post['trade_status'] : '')) {
                    $trade_no = isset($post['trade_no']) ? sanitize_text_field(wp_unslash($post['trade_no'])) : '';
                    Novapay::pay_success($order, $trade_no);
                }
                echo 'success';
            }
        } elseif ('wechat' === $notify) {
            $xml = file_get_contents('php://input');
            $data = nova_wechat_xml_to_array($xml);
            $key = nova_get_setting('pay_wechat_key');
            if ($data && isset($data['sign']) && $data['sign'] === nova_wechat_sign($data, $key)) {
                $order_num = isset($data['out_trade_no']) ? $data['out_trade_no'] : '';
                $order = Novapay::get_order_by_num($order_num);
                if ($order && 'SUCCESS' === (isset($data['result_code']) ? $data['result_code'] : '')) {
                    $trade_no = isset($data['transaction_id']) ? $data['transaction_id'] : '';
                    Novapay::pay_success($order, $trade_no);
                }
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }
        }
        exit;
    }

    //同步返回：显示支付结果
    nova_require('novapay/return', true);
    exit;
}
add_action('template_redirect', 'nova_pay_route', 1);

/* ============================================================
 * 支付页面（/pay?order_id=xx）
 * ============================================================ */
/**
 * 支付页面内容
 */
function nova_pay_html($order_id = 0)
{
    $order_id = $order_id ?: (isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0); // phpcs:ignore
    if (!$order_id) return nova_empty_box(__('订单不存在', 'nova'), __('缺少订单参数', 'nova'));
    $user_id = get_current_user_id();
    if (!$user_id) return nova_empty_box(__('请先登录', 'nova'), __('登录后才能支付订单', 'nova'));

    $order = Novapay::get_order($order_id);
    if (!$order || $order->user_id != $user_id) return nova_empty_box(__('订单不存在', 'nova'), __('订单不存在或无权访问', 'nova'));

    if (1 == $order->order_status) {
        return nova_empty_box(__('订单已支付', 'nova'), __('该订单已经支付成功，请勿重复支付', 'nova'), 'nova-icon-success');
    }
    if (3 == $order->order_status) {
        return nova_empty_box(__('订单已关闭', 'nova'), __('该订单已被关闭', 'nova'));
    }

    $channels = Novapay::get_channels();
    $balance = NovaDB::get_user_balance($user_id);

    $html = '<div class="nova-pay-page" data-order="' . $order->id . '">';
    //订单信息
    $html .= '<div class="nova-pay-order card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('订单信息', 'nova') . '</span></h3>';
    $html .= '<div class="nova-pay-order-row"><span>' . esc_html__('订单号', 'nova') . '</span><b>' . esc_html($order->order_num) . '</b></div>';
    $html .= '<div class="nova-pay-order-row"><span>' . esc_html__('订单类型', 'nova') . '</span><b>' . esc_html(Novapay::order_type_name($order->order_type)) . '</b></div>';
    if ($order->post_id) {
        $html .= '<div class="nova-pay-order-row"><span>' . esc_html__('商品内容', 'nova') . '</span><b><a href="' . esc_url(get_permalink($order->post_id)) . '">' . esc_html(get_the_title($order->post_id)) . '</a></b></div>';
    }
    $html .= '<div class="nova-pay-order-row"><span>' . esc_html__('支付金额', 'nova') . '</span><b class="nova-pay-price">¥ ' . number_format((float) $order->pay_price, 2) . '</b></div>';
    $html .= '</div>';

    //支付方式
    $html .= '<div class="nova-pay-channels card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('选择支付方式', 'nova') . '</span></h3>';
    $html .= '<div class="nova-pay-channel-list">';
    foreach ($channels as $key => $channel) {
        $html .= '<label class="nova-pay-channel" data-channel="' . $key . '">';
        $html .= '<input type="radio" name="nova_pay_channel" value="' . $key . '"' . ('balance' === $key ? ' checked' : '') . '>';
        $html .= '<i class="' . esc_attr($channel['icon']) . '"></i>';
        $html .= '<span>' . esc_html($channel['name']) . '</span>';
        if ('balance' === $key) {
            $html .= '<em class="muted-2-color">' . sprintf(__('余额 ¥%s', 'nova'), number_format($balance, 2)) . '</em>';
        }
        $html .= '</label>';
    }
    $html .= '</div>';

    //余额支付按钮
    if (isset($channels['balance'])) {
        $html .= '<div class="nova-pay-action"><button class="btn btn-primary btn-block nova-pay-balance-btn">' . esc_html__('确认支付', 'nova') . '</button></div>';
    }
    //支付宝跳转
    if (isset($channels['alipay'])) {
        $html .= '<div class="nova-pay-action nova-pay-action-alipay" style="display:none"><a class="btn btn-primary btn-block" href="' . esc_url(add_query_arg(array('order_id' => $order->id, 'pay_type' => 'alipay'), home_url('/nova-pay-gateway'))) . '">' . esc_html__('前往支付宝支付', 'nova') . '</a></div>';
    }
    //微信二维码
    if (isset($channels['wechat'])) {
        $html .= '<div class="nova-pay-action nova-pay-action-wechat" style="display:none">';
        $html .= '<div class="nova-wechat-qrcode" data-url="' . esc_url(add_query_arg(array('order_id' => $order->id, 'pay_type' => 'wechat'), home_url('/nova-pay-gateway'))) . '"></div>';
        $html .= '<p class="muted-2-color">' . esc_html__('请使用微信扫码支付', 'nova') . '</p>';
        $html .= '</div>';
    }
    //转账支付
    if (isset($channels['bank'])) {
        $bank_info = nova_get_setting('pay_bank_info');
        $html .= '<div class="nova-pay-action nova-pay-action-bank" style="display:none">';
        $html .= '<div class="nova-bank-info card">' . wpautop($bank_info) . '</div>';
        if (2 == $order->order_status) {
            $html .= '<div class="nova-bank-pending"><i class="nova-icon-time"></i>' . esc_html__('凭证已提交，等待管理员确认', 'nova') . '</div>';
        } else {
            $html .= '<div class="nova-form-group"><label>' . esc_html__('转账备注（订单号等）', 'nova') . '</label><input type="text" class="nova-bank-remark" placeholder="' . esc_attr__('请填写转账账号昵称或流水号，便于核对', 'nova') . '"></div>';
            $html .= '<button class="btn btn-primary btn-block nova-bank-submit-btn">' . esc_html__('我已转账，提交凭证', 'nova') . '</button>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 支付网关跳转（支付宝链接/微信二维码）
 */
function nova_pay_gateway()
{
    if (!isset($_GET['order_id']) || !isset($_GET['pay_type'])) return; // phpcs:ignore
    $order_id = (int) $_GET['order_id']; // phpcs:ignore
    $pay_type = sanitize_text_field(wp_unslash($_GET['pay_type'])); // phpcs:ignore
    $order = Novapay::get_order($order_id);
    if (!$order || 0 != $order->order_status) return;

    if ('alipay' === $pay_type) {
        $url = nova_alipay_pay_url($order);
        if (!$url) wp_die('支付宝支付未正确配置');
        wp_redirect($url);
        exit;
    }
    if ('wechat' === $pay_type) {
        $code_url = nova_wechat_pay_code_url($order);
        if (!$code_url) wp_die('微信支付未正确配置或下单失败');
        nova_require('inc/class/qrcode', true);
        $qrcode = new NovaQrcode();
        header('Content-Type: image/png');
        echo $qrcode->png($code_url, false, 'L', 4, 6); // phpcs:ignore
        exit;
    }
}
add_action('template_redirect', 'nova_pay_gateway', 2);

/* ============================================================
 * 我的订单页面
 * ============================================================ */
function nova_user_orders_html()
{
    $user_id = get_current_user_id();
    $paged = nova_get_the_paged();
    $orders = Novapay::get_user_orders($user_id, $paged, 10);

    $html = '<div class="nova-orders-page">';
    if (empty($orders)) {
        $html .= nova_empty_box(__('暂无订单', 'nova'), __('你还没有创建过订单', 'nova'));
    } else {
        $html .= '<div class="nova-order-list">';
        foreach ($orders as $order) {
            $status = (int) $order->order_status;
            $html .= '<div class="nova-order-item card">';
            $html .= '<div class="nova-order-head">';
            $html .= '<span class="nova-order-type">' . esc_html(Novapay::order_type_name($order->order_type)) . '</span>';
            $html .= '<span class="nova-order-num muted-2-color">' . esc_html($order->order_num) . '</span>';
            $html .= '<span class="nova-order-status st-' . $status . '">' . esc_html(Novapay::order_status_name($status)) . '</span>';
            $html .= '</div>';
            $html .= '<div class="nova-order-body">';
            if ($order->post_id) {
                $html .= '<a href="' . esc_url(get_permalink($order->post_id)) . '">' . esc_html(get_the_title($order->post_id)) . '</a>';
            } else {
                $html .= '<span class="muted-2-color">' . esc_html__('—', 'nova') . '</span>';
            }
            $html .= '<b>¥ ' . number_format((float) $order->pay_price, 2) . '</b>';
            $html .= '</div>';
            $html .= '<div class="nova-order-foot muted-2-color">';
            $html .= '<span>' . esc_html($order->create_time) . '</span>';
            if (0 == $status) {
                $html .= '<a class="btn btn-sm btn-primary" href="' . esc_url(add_query_arg('order_id', $order->id, nova_pay_url())) . '">' . esc_html__('去支付', 'nova') . '</a>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 支付页地址
 */
function nova_pay_url()
{
    return home_url('/pay');
}
