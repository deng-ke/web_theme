<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-03-30 12:52:47
 * @LastEditTime : 2026-06-22 11:19:45
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**
 * 获取后台商城中心的链接
 */
function zibpay_get_admin_shop_url($page = '', $query = null)
{
    $_url = admin_url('admin.php?page=zibpay_page');

    if ($page) {
        $_url .= '#/' . $page;
    }

    if ($query) {
        $_url .= '?' . (is_array($query) ? http_build_query($query) : $query);
    }

    return $_url;
}

/**
 * 获取后台订单的链接
 */
function zibpay_get_admin_shop_order_url($query = null)
{

    if (is_numeric($query)) {
        $query = ['id' => $query];
    }

    return zibpay_get_admin_shop_url('order', $query);
}

/**
 * @description: 获取订单的有效金额，参与分成的
 * @param {*} $order
 * @return {*}
 */
function zibpay_get_order_effective_amount($order)
{
    $order      = (array) $order;
    $pay_detail = maybe_unserialize($order['pay_detail']);
    if ($pay_detail && is_array($pay_detail)) {
        $price  = 0;
        $method = array('wechat', 'alipay', 'balance', 'card_pass', 'paypal', 'stripe'); //哪些支付方式是有效的
        foreach ($method as $t) {
            if (!empty($pay_detail[$t])) {
                $price += $pay_detail[$t];
            }
        }
        return $price;
    }

    return $order['pay_price'];
}

/**
 * @description: 获取订单的有效积分金额，参与分成的
 * @param {*} $order
 * @return {*}
 */
function zibpay_get_order_effective_points($order)
{
    $order      = (array) $order;
    $pay_detail = maybe_unserialize($order['pay_detail']);
    $points     = 0;
    if ($pay_detail && is_array($pay_detail)) {
        if (!empty($pay_detail['points'])) {
            $points = (int) $pay_detail['points'];
        }
    }

    return $points;
}
/**
 * @description: 获取订单的支付金额显示
 * @param {*} $order
 * @return {*}
 */
function zibpay_get_order_pay_price($order)
{
    $order      = (array) $order;
    $pay_detail = maybe_unserialize($order['pay_detail']);
    if ($order['pay_type'] === 'points') {
        $mark  = zibpay_get_points_mark();
        $price = isset($pay_detail['points']) ? $pay_detail['points'] : 0;
    } else {
        $mark  = zibpay_get_pay_mark();
        $price = zibpay_get_order_effective_amount($order);
    }
    return '<span class="pay-mark">' . $mark . '</span>' . $price;
}

/**
 * @description: 获取用户显示的付款明细
 * @param {*} $order
 * @param {*} $class
 * @return {*}
 */
function zibpay_get_order_pay_detail_lists($order, $separator = '<span class="icon-spot"></span>', $class = '')
{
    $methods    = zibpay_get_payment_method_args();
    $order      = (array) $order;
    $pay_detail = maybe_unserialize($order['pay_detail']);
    $lists      = '';
    $i          = 1;
    foreach ($methods as $k => $v) {
        if (isset($pay_detail[$k])) {
            $val = (float) $pay_detail[$k] ? __('：', 'zib_language') . zib_floatval_round($pay_detail[$k]) : ($k === 'card_pass' ? __('兑换', 'zib_language') : 0);
            if ($val) {
                $lists .= $i !== 1 ? $separator : '';
                $lists .= '<lists class="' . $class . '">' . $v['name'] . $val . '</lists>';
                $i++;
            }
        }
    }
    if (!$lists) {
        $lists = '<lists class="' . $class . '">' . ($order['pay_type'] === 'points' ? zib_floatval_round($order['pay_price']) . __('积分', 'zib_language') . ' ' : zibpay_get_pay_mark() . zib_floatval_round($order['pay_price'])) . '</lists>';
    }

    return $lists;
}

/**
 * @description: 获取订单支付方式明细的文字数组
 * @param {*} $order
 * @param {*} $suffix
 * @return {*}
 */
function zibpay_get_order_pay_detail_text_args($order, $suffix = '')
{
    $methods    = zibpay_get_payment_method_args();
    $order      = (array) $order;
    $pay_detail = maybe_unserialize($order['pay_detail']);
    $lists      = array();
    foreach ($methods as $k => $v) {
        if (!empty($pay_detail[$k])) {
            $lists[] = $v['name'] . __('：', 'zib_language') . $pay_detail[$k] . $suffix;
        }
    }
    if (!$lists) {
        $lists[] = zib_floatval_round($order['pay_price']) . $suffix;
    }
    return $lists;
}

//支付方式
function zibpay_get_payment_methods($pay_type = 0)
{
    $payment_method_args = zibpay_get_payment_method_args();
    $methods             = array();
    $pay_wechat_sdk      = _pz('pay_wechat_sdk_options');
    $pay_alipay_sdk      = _pz('pay_alipay_sdk_options');

    if ($pay_wechat_sdk && 'null' != $pay_wechat_sdk) {
        $methods['wechat'] = $payment_method_args['wechat'];
    }

    if ($pay_alipay_sdk && 'null' != $pay_alipay_sdk) {
        $methods['alipay'] = $payment_method_args['alipay'];
    }

    if (_pz('pay_paypal_sdk_s')) {
        $methods['paypal'] = $payment_method_args['paypal'];
    }

    if (_pz('pay_stripe_sdk_s')) {
        $methods['stripe'] = $payment_method_args['stripe'];
    }

    if (zibpay_is_allow_balance_pay($pay_type)) {
        $methods['balance'] = $payment_method_args['balance'];
    }

    if (zibpay_is_allow_card_pass_pay($pay_type)) {
        $methods['card_pass'] = $payment_method_args['card_pass'];
    }

    //排序
    $pay_sdk_order = _pz('pay_sdk_order', array('wechat' => '1', 'alipay' => '1', 'paypal' => '1', 'stripe' => '1', 'balance' => '1', 'card_pass' => '1'));
    if ($pay_sdk_order && is_array($pay_sdk_order)) {
        foreach ($pay_sdk_order as $k => $v) {
            if (isset($methods[$k])) {
                $new[$k] = $methods[$k];
                unset($methods[$k]);
                $methods = array_merge($new, $methods);
            }
        }
    }

    return apply_filters('zibpay_payment_methods', $methods, $pay_type);
}

//支付方式参数数组
function zibpay_get_payment_method_args()
{

    $payment_method_names = array(
        'wechat'    => array(
            'name' => __('微信', 'zib_language'),
            'img'  => '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/assets/img/pay-wechat-logo.svg" alt="wechat-logo">',
        ),
        'alipay'    => array(
            'name' => __('支付宝', 'zib_language'),
            'img'  => '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/assets/img/pay-alipay-logo.svg" alt="alipay-logo">',
        ),
        'balance'   => array(
            'name' => __('余额', 'zib_language'),
            'img'  => '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/assets/img/pay-balance-logo.svg" alt="balance-logo">',
        ),
        'points'    => array(
            'name' => __('积分', 'zib_language'),
            'img'  => '<svg aria-hidden="true"><use xlink:href="#icon-points-color"></use></svg>',
        ),
        'card_pass' => array(
            'name' => __('卡密', 'zib_language'),
            'img'  => '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/assets/img/pay-card-pass-logo.svg" alt="card-pass-logo">',
        ),
        'paypal'    => array(
            'name' => 'PayPal',
            'img'  => '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/assets/img/pay-paypal-logo.svg" alt="PayPal-logo">',
        ),
        'stripe'    => array(
            'name' => 'Stripe',
            'img'  => '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/assets/img/pay-stripe-logo.svg" alt="Stripe-logo">',
        ),
    );

    return $payment_method_names;
}

function zibpay_get_refund_channel_name($refund_channel = '')
{
    $name = array(
        'points'  => __('积分', 'zib_language'),
        'balance' => __('余额', 'zib_language'),
        'wechat'  => __('微信', 'zib_language'),
        'alipay'  => __('支付宝', 'zib_language'),
        'paypal'  => 'PayPal',
        'stripe'  => 'Stripe',
    );
    if (!$refund_channel) {
        return $name;
    }
    return $name[$refund_channel] ?? __('其他', 'zib_language');
}

//获取付费类型的名称
function zibpay_get_pay_type_name($pay_type = null, $show_icon = false)
{
    $name = array(
        '1'  => __('付费阅读', 'zib_language'), //文章，帖子
        '2'  => __('付费资源', 'zib_language'), //文章
        '5'  => __('付费图片', 'zib_language'), //文章
        '6'  => __('付费视频', 'zib_language'), //文章
        '3'  => __('产品购买', 'zib_language'), //页面，未使用
        '4'  => __('购买会员', 'zib_language'), //用户
        '7'  => __('自动售卡', 'zib_language'), //未启用
        '8'  => __('余额充值', 'zib_language'), //用户
        '9'  => __('购买积分', 'zib_language'), //用户
        '10' => __('购买商品', 'zib_language'), //商城，商品
        '11' => __('关注版块', 'zib_language'), //版块
    );

    if (!empty($GLOBALS['zib_bbs']->plate_name)) {
        $name['11'] = $GLOBALS['zib_bbs']->plate_follow_name . $GLOBALS['zib_bbs']->plate_name;
    }

    if (!$pay_type) {
        return $name;
    }

    $n = isset($name[$pay_type]) ? $name[$pay_type] : __('付费内容', 'zib_language');
    if ($show_icon) {
        return zibpay_get_pay_type_icon($pay_type, 'mr3') . $n;
    }
    return $n;
}

function zibpay_get_order_status_name($status = null)
{
    $name = array(
        -2 => __('已退款', 'zib_language'),
        -1 => __('已关闭', 'zib_language'),
        0  => __('待支付', 'zib_language'),
        1  => __('已支付', 'zib_language'),
    );

    if (!$status) {
        return $name;
    }

    return isset($name[$status]) ? $name[$status] : __('未知', 'zib_language');
}

function zibpay_get_post_thumbnail_url($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    if (!$post) {
        return '';
    }

    $post_type = $post->post_type;
    switch ($post_type) {
        case 'shop_product':
            $product_config = get_post_meta($post->ID, 'product_config', true);
            $img_url        = $product_config['main_image'] ?? '';

            //获取第一张封面图
            if (!$img_url) {
                $cover_images = explode(',', ($product_config['cover_images'] ?? ''));
                if (is_array($cover_images) && isset($cover_images[0]) && $cover_images[0]) {
                    $img_url = zib_get_attachment_image_src((int) $cover_images[0], 'medium')[0] ?? '';
                }
            }

            if (!$img_url) {
                $img_url = _pz('shop_main_image_default', '') ?: ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail.svg';
            }

            break;

        case 'forum_posts': //论坛帖子
            $img_url = zib_get_post_meta($post->ID, 'thumbnail_url', true);
            break;

        default:
            $img_url = zib_post_thumbnail('medium', 'radius8 fit-cover', true, $post) ?: zib_get_spare_thumb();
            break;
    }

    return $img_url;
}

//获取订单的商品图片
function zibpay_get_order_order_thumb($order, $class = '')
{
    $order         = (array) $order;
    $_img          = '';
    $product_image = zibpay::get_meta($order['id'], 'order_data.product_image');

    if ($product_image) {
        $_img = '<img class="radius8 fit-cover" src="' . $product_image . '" alt="product-image">';
    }

    if (!$_img && !empty($order['post_id'])) {
        $post = get_post($order['post_id']);
        if ($post && isset($post->post_title)) {
            $_img_url = zibpay_get_post_thumbnail_url($post);
            if ($_img_url) {
                $alt  = $post->post_title . zib_get_delimiter_blog_name();
                $_img = '<img class="radius8 fit-cover" src="' . $_img_url . '" alt="' . $alt . '">';
            }
        }
    }

    if (!$_img && !empty($order['order_type'])) {
        switch ($order['order_type']) {
            case '4':
                $_img = '<img src="' . zibpay_get_vip_icon_img_url(1) . '" class="radius8 fit-cover vip-card" alt="product-image">';
                break;
            case '8':
                $_img = zib_get_svg('money-color-2', null, 'muted-box fit-cover c-blue-2');
                break;
            case '9':
                $_img = zib_get_svg('points-color', null, 'muted-box fit-cover c-yellow-2');
                break;
        }
    }

    $_img = $_img ?: zib_get_svg('order-color', null, 'fit-cover muted-box');

    return '<div class="order-thumb ' . $class . '">' . $_img . '</div>';
}

//获取订单标题
function zibpay_get_order_title($order, $class = '')
{
    $order  = (array) $order;
    $_title = '';
    if (!empty($order['post_id'])) {
        $post = get_post($order['post_id']);
        if ($post && isset($post->post_title)) {
            $_title = $post->post_title;
        }
    }

    if (!$_title && !empty($order['order_type'])) {
        $_title = zibpay_get_pay_type_name($order['order_type']);
    }

    $_title = apply_filters('zibpay_order_title', $_title, $order);
    return '<div class="order-title ' . $class . '">' . $_title . '</div>';
}

//获取订单支付名称
function zibpay_get_pay_order_name($order_type_name)
{
    $order_name = '';
    if (_pz('pay_sdk_custom_title_s')) {
        $pay_sdk_custom_title = _pz('pay_sdk_custom_title');
        if ($pay_sdk_custom_title) {
            $order_name = str_replace('%order_type%', $order_type_name, $pay_sdk_custom_title);
        }
    }
    return $order_name ? $order_name : $order_type_name . '-' . get_bloginfo('name');
}

//获取付费类型的图标
function zibpay_get_pay_type_icon($pay_type, $class = '', $tip = false)
{
    $class = $class ? ' ' . $class : '';
    $icons = array(
        '1'  => '<i class="fa fa-book' . $class . '"></i>',
        '2'  => '<i class="fa fa-download' . $class . '"></i>',
        '3'  => '<i class="fa fa-shopping-cart' . $class . '"></i>',
        '4'  => '<i class="fa fa-diamond' . $class . '"></i>',
        '5'  => '<i class="fa fa-file-image-o' . $class . '"></i>',
        '6'  => '<i class="fa fa-play-circle' . $class . '"></i>',
        '7'  => '<i class="fa fa-credit-card' . $class . '"></i>',
        '8'  => '<i class="fa fa-jpy' . $class . '"></i>',
        '9'  => '<i class="fa fa-rub' . $class . '"></i>',
        '10' => '<i class="fa fa-shopping-cart' . $class . '"></i>',
    );
    if ($tip) {
        return '<span title="' . zibpay_get_pay_type_name($pay_type) . '" data-toggle="tooltip">' . $icons[$pay_type] . '<span>';
    } else {
        return $icons[$pay_type];
    }
}

/**
 * @description: 获取文章的推广优惠金额
 * @param {*} $post_id
 * @return {*}
 */
function zibpay_get_post_rebate_discount($post_id = 0, $user_id = 0)
{
    if (!_pz('pay_rebate_s')) {
        return 0;
    }
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    //文章参数判断
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type'] || empty($pay_mate['pay_rebate_discount'])) {
        return 0;
    }

    //当前推荐人返利判断
    $rebate_ratio = zibpay_get_the_referrer_rebate_ratio($pay_mate['pay_type'], $user_id);

    if ($rebate_ratio) {
        return round((float) $pay_mate['pay_rebate_discount'], 2);
    }

    return 0;
}

/**
 * @description: 获取form中使用的支付按钮
 * @param {*} $pay_type
 * @param {*} $pay_price
 * @param {*} $class
 * @param {*} $text
 * @return {*}
 */
function zibpay_get_initiate_pay_input($pay_type, $pay_price = 0, $post_id = 0, $is_initiate_pay = false, $text = null)
{
    if (null === $text) {
        $text = __('立即支付', 'zib_language');
    }
    //准备订单数据
    $user_id             = get_current_user_id();
    $payment_methods     = zibpay_get_payment_methods($pay_type);
    $methods_lists       = '';
    $methods_active_html = '';

    if (!$payment_methods) {
        if (is_super_admin()) {
            return '<a href="' . zib_get_admin_csf_url('支付付费/收款接口') . '" class="but c-red btn-block">' . __('请先在主题设置中配置收款方式及收款接口', 'zib_language') . '</a>';
        } else {
            return '<span class="badg px12 c-yellow-2">' . __('暂时无法支付，请与客服联系', 'zib_language') . '</span>';
        }
    }

    $ii = 1;
    foreach ($payment_methods as $method_key => $method_val) {
        if ($ii === 1) {
            $method_default = $method_key;
        }
        $methods_lists .= '<div class="flex jc hh payment-method-radio hollow-radio flex-auto pointer' . ($ii === 1 ? ' active' : '') . '" data-for="payment_method"  data-value="' . $method_key . '" >' . $method_val['img'] . '<div>' . $method_val['name'] . '</div></div>';
        $ii++;
    }

    if ($methods_lists && $ii > 2) {
        $methods_active_html = '<div class="muted-2-color em09 mb6">' . __('请选择支付方式', 'zib_language') . '</div><div class="flex mb10">' . $methods_lists . '</div>';
    }

    //如果存在余额支付，则需显示我的余额
    $user_balance_box = '';
    if (isset($payment_methods['balance'])) {
        $user_balance        = zibpay_get_user_balance($user_id);
        $balance_charge_link = '';
        if ($pay_price && $pay_price > $user_balance) {
            $balance_charge_link = zibpay_get_balance_charge_link('but c-red block mt6', __('抱歉！余额不足，请先充值', 'zib_language') . ' <i class="ml6 fa fa-angle-right em12"></i>');
        }

        $user_balance_box = '<div class="mb10 muted-box padding-h10" data-controller="payment_method" data-condition="==" data-value="balance"' . ($method_default !== 'balance' ? ' style="display: none;"' : '') . '>
        <div class="flex jsb ac">
            <span class="muted-2-color">' . zib_get_svg('money-color-2', null, 'em12 mr6') . __('我的余额', 'zib_language') . '</span>
            <div><span class="c-blue-2"><span class="mr3 px12">' . zibpay_get_pay_mark() . '<span class="em14">' . $user_balance . '</span></span></div>
            </div>' . $balance_charge_link . '
        </div>';
    }

    //如果存在卡密支付，则显示卡密内容
    $password_box = '';
    if (isset($payment_methods['card_pass'])) {
        //单密码模式
        $only_password = zibpay_card_pass_is_only_password($pay_type);
        $password_box  = '<div class="mb10  padding-h10 padding-w6" data-controller="payment_method" data-condition="==" data-value="card_pass"' . ($method_default !== 'card_pass' ? ' style="display: none;"' : '') . '>
        ' . apply_filters('zibpay_card_pass_payment_desc', '') . '
        <div class="muted-2-color em09 mb6">' . sprintf(__('请输入%s', 'zib_language'), ($only_password ? __('卡密', 'zib_language') : __('卡号和密码', 'zib_language'))) . '</div>
            ' . ($only_password ? '' : '<div class="mb6"><input type="input" class="form-control" name="card_pass[card]" placeholder="' . esc_attr__('卡号', 'zib_language') . '" value=""></div>') . '
            <div class="">
                <input type="input" class="form-control" name="card_pass[password]" placeholder="' . esc_attr__('卡密', 'zib_language') . '" value="">
            </div>
        </div>';
    }

    //积分抵扣
    $points_deduction = '';
    if (!$is_initiate_pay && zibpay_is_allow_points_deduction($pay_type)) {
        $points_deduction_rate  = _pz('points_deduction_rate', 30);
        $user_points            = zibpay_get_user_points($user_id);
        $points_deduction_price = round(($user_points / $points_deduction_rate), 2);
        $points_deduction .= $points_deduction_price > 0 ? '<label class="flex jsb ac mb10 muted-box padding-h10"><input class="hide" name="points_deduction" type="checkbox"><div class="flex1 mr20"><div class="muted-color mb6">' . __('积分抵扣', 'zib_language') . '</div><div style="font-weight: normal;" class="muted-2-color px12 points-deduction-text">' . sprintf(__('使用%s积分抵扣%s', 'zib_language'), $user_points, zibpay_format_local_price_text($points_deduction_price)) . '</div></div><div class="form-switch flex0"></div></label>' : '';
    }

    //使用优惠码
    $coupon_box = '';
    //优惠码需要在下单时使用
    if (!$is_initiate_pay && zibpay_is_allow_coupon($pay_type, $post_id)) {
        $coupon_desc = zibpay_get_coupon_desc($pay_type, $post_id);
        $coupon_desc = $coupon_desc ? '<div class="muted-3-color px12 mt6">' . $coupon_desc . '</div>' : '';
        $coupon_box  = '<div class="mb10 coupon-input-box" data-controller="payment_method" data-condition="!=" data-value="card_pass">
                        <div class="muted-2-color em09 mb6">' . __('使用优惠码', 'zib_language') . '</div>
                        <div class="relative">
                            <input type="input" class="form-control coupon-input" name="coupon" placeholder="' . esc_attr__('请输入优惠码', 'zib_language') . '" value="">
                            <span class="abs-right"><button type="button" class="but c-blue coupon-submit">' . __('检查优惠码', 'zib_language') . '</button></span>
                        </div>
                        ' . $coupon_desc . '
                        <div class="coupon-data-box"></div>
                    </div>';
    }

    $html = '<div class="dependency-box">';
    $html .= $points_deduction;
    $html .= $coupon_box;
    $html .= $methods_active_html;
    $html .= $user_balance_box . $password_box;
    $html .= '<input type="hidden" name="payment_method" value="' . $method_default . '">';
    $html .= '<input type="hidden" name="action" value="' . (!$is_initiate_pay ? 'submit_order' : 'initiate_pay') . '">';
    $html .= '<button class="mt6 but jb-red initiate-pay btn-block radius">' . $text . '<span class="pay-price-text">' . ($pay_price ? '<span class="px12 ml10">' . zibpay_get_pay_mark() . '</span><span class="actual-price-number" data-price="' . $pay_price . '">' . $pay_price . '</span>' : '') . '</span></button>';
    $html .= '</div>';

    return $html;
}

/**
 * @description: 判断是否允许积分抵扣
 * @param {*}
 * @return {*}
 */
function zibpay_is_allow_points_deduction($pay_type)
{

    //暂未启用
    return false;
    if (!_pz('points_s') || !_pz('points_deduction_s')) {
        return false;
    }
    $pay_type = (int) $pay_type;
    $user_id  = get_current_user_id();
    //禁止
    $prohibit_types = array(8, 9);

    return ($user_id && !in_array($pay_type, $prohibit_types));
}

/**
 * @description: 判断哪些哪些支付方式允许使用余额支付
 * @param {*} $pay_type
 * @return {*}
 */
function zibpay_is_allow_balance_pay($pay_type)
{

    if (!_pz('pay_balance_s') || !get_current_user_id()) {
        return false;
    }
    $pay_type = (int) $pay_type;
    $user_id  = get_current_user_id();
    //禁止
    $prohibit_types = array(8);

    return apply_filters('zibpay_is_allow_balance_pay', ($user_id && !in_array($pay_type, $prohibit_types)), $pay_type);
}

function zibpay_is_allow_card_pass_pay($pay_type)
{
    return apply_filters('zibpay_is_allow_card_pass_pay', false, $pay_type);
}

//卡密支付是否是单密码模式
function zibpay_card_pass_is_only_password($pay_type)
{
    if ($pay_type == 8) {
        return _pz('pay_balance_pass_charge_only_password');
    }

    if ($pay_type == 4) {
        return _pz('pay_vip_pass_charge_only_password');
    }

    if ($pay_type == 9) {
        return _pz('points_pass_exchange_only_password');
    }

    return false;
}

/**
 * @description: 获取货币符号
 * @param {*}
 * @return {*}
 */
function zibpay_get_pay_mark()
{
    //声明静态变量，加速获取
    static $pay_mark = null;
    if (!$pay_mark) {
        $pay_mark = _pz('pay_mark') ?: '￥';
    }

    return $pay_mark;
}

/**获取本地货币单位名称 */
function zibpay_get_currency_unit()
{
    static $unit = null;
    if (null === $unit) {
        $unit = _pz('pay_currency_unit');
        if (!$unit) {
            $unit = __('元', 'zib_language');
        }
    }

    return $unit;
}

/**获取微信结算汇率 */
function zibpay_get_wechat_settle_rate()
{
    static $rate = null;
    if (null === $rate) {
        $rate = _pz('pay_wechat_settle', '1', 'settle_rate') ?: '1';
    }

    $rate = $rate ? (float) $rate : 1;
    $rate = $rate > 0 ? $rate : 1;

    return $rate;
}

/**获取支付宝结算汇率 */
function zibpay_get_alipay_settle_rate()
{
    static $rate = null;
    if (null === $rate) {
        $rate = _pz('pay_alipay_settle', '1', 'settle_rate') ?: '1';
    }

    $rate = $rate ? (float) $rate : 1;
    $rate = $rate > 0 ? $rate : 1;

    return $rate;
}

//根据不同收款方式获取汇率
function zibpay_get_settle_rate($method = 'wechat')
{
    $method = 'alipay' === $method ? 'alipay' : 'wechat';
    switch ($method) {
        case 'wechat':
            return zibpay_get_wechat_settle_rate();
        case 'alipay':
            return zibpay_get_alipay_settle_rate();
        default:
            return 1;
    }
}

/**获取结算货币符号 */
function zibpay_get_settle_mark($method = 'wechat')
{
    static $cache = array();
    $method       = 'alipay' === $method ? 'alipay' : 'wechat';
    if (!isset($cache[$method])) {
        $key            = 'alipay' === $method ? 'pay_alipay_settle' : 'pay_wechat_settle';
        $cache[$method] = _pz($key, '￥', 'settle_mark') ?: '￥';
    }

    return $cache[$method];
}

/**获取结算货币单位 */
function zibpay_get_settle_unit($method = 'wechat')
{
    static $cache = array();
    $method       = 'alipay' === $method ? 'alipay' : 'wechat';
    if (!isset($cache[$method])) {
        $key            = 'alipay' === $method ? 'pay_alipay_settle' : 'pay_wechat_settle';
        $cache[$method] = _pz($key, __('元', 'zib_language'), 'settle_unit') ?: __('元', 'zib_language');
        if ('' === $cache[$method] || null === $cache[$method]) {
        }
    }
    $cache[$method] = $cache[$method] ?: __('元', 'zib_language');
    return $cache[$method];
}

/**本地金额换算为微信结算金额（字符串） */
function zibpay_convert_for_wechat($local_amount)
{
    return zib_floatval_round(bcmul((string) $local_amount, (string) zibpay_get_wechat_settle_rate(), 2));
}

/**本地金额换算为支付宝结算金额（字符串） */
function zibpay_convert_for_alipay($local_amount)
{
    return zib_floatval_round(bcmul((string) $local_amount, (string) zibpay_get_alipay_settle_rate(), 2));
}

/**结算金额转微信分 */
function zibpay_settle_price_to_fen($settle_price)
{
    return (int) bcmul((string) $settle_price, '100', 0);
}

/**解析扫码展示金额：SDK 优先 */
function zibpay_resolve_settle_display_price($calculated, $sdk_amount = null)
{
    $calculated = zib_floatval_round($calculated);
    if (null !== $sdk_amount && '' !== $sdk_amount) {
        return zib_floatval_round($sdk_amount);
    }

    return $calculated;
}

/**格式化本地货币金额（符号+数值） */
function zibpay_format_local_price($amount)
{
    return zibpay_get_pay_mark() . zib_floatval_round($amount);
}

/**格式化本地货币金额文本（数值+单位） */
function zibpay_format_local_price_text($amount)
{
    return zib_floatval_round($amount) . zibpay_get_currency_unit();
}

/**是否为 CNY 结算支付 SDK */
function zibpay_is_cny_settle_sdk($sdk)
{
    $cny_sdks = array('official_wechat', 'official_alipay', 'epay', 'vmqphp', 'codepay', 'xunhupay', 'payjs', 'xhpay');

    return in_array($sdk, $cny_sdks, true);
}

/**是否需要结算换算 */
function zibpay_need_settle_convert($payment_method)
{
    return in_array($payment_method, array('wechat', 'alipay'), true);
}

/**生成扫码结算提示 HTML */
function zibpay_get_settle_scan_more_html($settle_mark, $settle_price, $settle_unit)
{
    return sprintf(
        '<div class="badg btn-block c-yellow em09 padding-h6">' . __('请扫码后支付%s%s%s，为了确保支付成功，请注意付款金额请勿出错', 'zib_language') . '</div>',
        $settle_mark,
        $settle_price,
        $settle_unit
    );
}

/**统一扫码结算 JSON 字段 */
function zibpay_build_settle_scan_response($order_data, $base_result, $sdk_amount = null)
{
    if (empty($base_result['url_qrcode']) || empty($order_data['payment_method'])) {
        return $base_result;
    }

    $method        = $order_data['payment_method'];
    $calculated    = !empty($order_data['calculated_settle_price']) ? $order_data['calculated_settle_price'] : $order_data['order_price'];
    $settle_mark   = zibpay_get_settle_mark($method);
    $settle_unit   = zibpay_get_settle_unit($method);
    $settle_rate   = zibpay_get_settle_rate($method);
    $currency_mark = zibpay_get_pay_mark();
    $settle_price  = zibpay_resolve_settle_display_price($calculated, $sdk_amount);

    $base_result['settle_mark']  = $settle_mark;
    $base_result['settle_price'] = $settle_price;
    $base_result['settle_unit']  = $settle_unit;
    $base_result['order_price']  = $settle_price;
    $base_result['more_html']    = $base_result['more_html'] ?? '';

    //显示汇率计算提示
    if ($order_data['local_price'] !== $settle_price && $currency_mark !== $settle_mark) {
        $base_result['more_html'] .= '<div class="badg btn-block c-blue px12 padding-h6">' . __('已按汇率计算付款金额：', 'zib_language') . sprintf('%s1=%s%s%s', $currency_mark, $settle_mark, $settle_rate, $settle_unit) . '</div>';
    }

    //扫码支付金额与结算金额不一致时，显示提示
    if (null !== $sdk_amount && '' !== $sdk_amount && zib_floatval_round($sdk_amount) !== zib_floatval_round($calculated)) {
        $base_result['more_html'] .= zibpay_get_settle_scan_more_html($settle_mark, $settle_price, $settle_unit);
    }

    return $base_result;
}

/**从 SDK 返回中提取结算金额 */
function zibpay_extract_sdk_settle_amount($payresult, $calculated_settle_price = null)
{
    if (!empty($payresult['money'])) {
        return $payresult['money'];
    }

    if (!empty($payresult['order_price']) && null !== $calculated_settle_price && zib_floatval_round($payresult['order_price']) !== zib_floatval_round($calculated_settle_price)) {
        return $payresult['order_price'];
    }

    return null;
}

/**获取支付参数函数 */
function zibpay_get_payconfig($type)
{
    $defaults             = array();
    $defaults['xunhupay'] = array(
        'wechat_appid'     => '',
        'wechat_appsecret' => '',
        'alipay_appid'     => '',
        'alipay_appsecret' => '',
    );
    $defaults['official_wechat'] = array(
        'merchantid'   => '',
        'appid'      => '',
        'key'        => '',
        'jsapi'      => '',
        'h5'         => '',
        'appsecret'  => '',
        'api_payout_s' => false,
        'api_cert'   => '',
        'api_key'    => '',
    );
    $defaults['official_alipay'] = array(
        'appid'         => '',
        'privatekey'    => '',
        'publickey'     => '',
        'pid'           => '',
        'md5key'        => '',
        'webappid'      => '',
        'webprivatekey' => '',
        'h5'            => '',
        'api_payout_s'  => false,
    );
    $defaults['codepay'] = array(
        'apiurl' => '',
        'id'     => '',
        'key'    => '',
        'token'  => '',
    );
    $defaults['payjs'] = array(
        'mchid' => '',
        'key'   => '',
    );
    $defaults['xhpay'] = array(
        'mchid'   => '',
        'key'     => '',
        'api_url' => '',
    );
    $defaults['epay'] = array(
        'apiurl'  => '',
        'partner' => '',
        'key'     => '',
        'qrcode'  => true,
    );
    $defaults['vmqphp'] = array(
        'apiurl' => '',
        'key'    => '',
    );
    $defaults['paypal'] = array(
        'rest_s'        => true,
        'client_id'     => '',
        'client_secret' => '',
        'webhook_id'    => '',
        'username'      => '',
        'password'      => '',
        'signature'     => '',
        'currency'      => 'USD',
        'rates'         => '0.14',
        'debug'         => false,
        'api_payout_s'  => false,
    );
    $defaults['stripe'] = array(
        'secret_key'     => '',
        'webhook_secret' => '',
        'currency'       => 'USD',
        'rates'          => '0.14',
        'debug'          => false,
    );
    $defaults_parse = isset($defaults[$type]) ? $defaults[$type] : array();
    $config         = wp_parse_args((array) _pz($type), $defaults_parse);
    return zib_trim($config);
}

/**根据订单号获取链接 */
function zibpay_get_order_num_link($order_num, $class = '')
{
    $href    = '';
    $user_id = get_current_user_id();
    if ($user_id) {
        $href = zib_get_user_center_url('order');
    }
    $a = '<a rel="nofollow" target="_blank" href="' . $href . '" class="' . $class . '">' . $order_num . '</a>';
    if ($href) {
        return $a;
    } else {
        return '<span class="' . $class . '">' . $order_num . '</span>';
    }
}

/**查看权限转文字 */
function zibpay_get_paid_type_name($paid_type)
{
    $paid_name = array(
        'free'      => __('免费内容', 'zib_language'),
        'paid'      => __('已购买', 'zib_language'),
        'vip1_free' => _pz('pay_user_vip_1_name') . __('免费', 'zib_language'),
        'vip2_free' => _pz('pay_user_vip_2_name') . __('免费', 'zib_language'),
        'trial'     => __('试用', 'zib_language'),
    );

    return $paid_name[$paid_type];
}

/**
 * @description: 判断是否允许查看（已付费）
 * @param {*} $post_id
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_is_paid($post_id, $user_id = 0)
{
    // 准备判断参数
    if (!$post_id) {
        return false;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    //静态变量，避免重复查询
    static $pay_order = null;
    if (isset($pay_order[$post_id][$user_id])) {
        return $pay_order[$post_id][$user_id];
    }

    //1.已经支付判断
    $order_expired_option = zibpay_get_post_order_expired_option($post_id);
    if ($user_id) {
        // 如果已经登录，根据用户id查找数据库订单
        $pay_order[$post_id][$user_id] = zibpay_get_post_paid_data_by_user($post_id, $user_id, $order_expired_option);

        if ($pay_order[$post_id][$user_id]) {
            $pay_order[$post_id][$user_id]['paid_type'] = 'paid';
            if ($order_expired_option) {
                //计算到期时间
                $pay_order[$post_id][$user_id]['order_expired_time'] = date('Y-m-d H:i:s', strtotime('+' . $order_expired_option['time'] . ' ' . $order_expired_option['unit'], strtotime($pay_order[$post_id][$user_id]['pay_time'])));
            }
            return $pay_order[$post_id][$user_id];
        }
    }

    //未登录，根据浏览器Cookie查找
    //是否允许未登录：游客支付订单
    $is_allow_tourists_pay = apply_filters('tourists_pay_is_allow', (!$user_id && _pz('pay_no_logged_in', true)), $post_id);
    if ($is_allow_tourists_pay && isset($_COOKIE['zibpay_' . $post_id])) {
        $pay_order[$post_id][$user_id] = zibpay_get_post_paid_data_by_order_num($post_id, $_COOKIE['zibpay_' . $post_id], $order_expired_option);
        if ($pay_order[$post_id][$user_id]) {
            //浏览器Cookie订单存在

            //1.判断时间时间是否超限制
            $pay_cookie_day = apply_filters('tourists_pay_cookie_days', _pz('pay_cookie_day', 15), $post_id) ?: 15;
            $pay_time       = $pay_order[$post_id][$user_id]['pay_time'];
            //如果购买时间超过Cookie时间限制，则认为订单已过期
            if (strtotime('+' . $pay_cookie_day . ' day', strtotime($pay_time)) < current_time('timestamp')) {
                $pay_order[$post_id][$user_id] = false;
                return $pay_order[$post_id][$user_id];
            }

            //校验Cookie是否被篡改
            if (!zibpay_posts_order_check_cookie_token($pay_order[$post_id][$user_id])) {
                $pay_order[$post_id][$user_id] = false;
                return $pay_order[$post_id][$user_id];
            }

            $pay_order[$post_id][$user_id]['paid_type'] = 'paid';
            if ($order_expired_option) {
                //计算到期时间
                $pay_order[$post_id][$user_id]['order_expired_time'] = date('Y-m-d H:i:s', strtotime('+' . $order_expired_option['time'] . ' ' . $order_expired_option['unit'], strtotime($pay_order[$post_id][$user_id]['pay_time'])));
            }
            return $pay_order[$post_id][$user_id];
        }
    }

    //2.免费判断：包含免费和会员免费
    $posts_pay = get_post_meta($post_id, 'posts_zibpay', true);
    if (zibpay_post_is_points_modo($posts_pay)) {
        //积分商品
        $points_price = !empty($posts_pay['points_price']) ? (int) $posts_pay['points_price'] : 0;
        if ($points_price <= 0) {
            $pay_order[$post_id][$user_id] = array('paid_type' => 'free', 'modo' => 'points');
            return $pay_order[$post_id][$user_id];
        }
        $vip_level  = zib_get_user_vip_level($user_id);
        $vip_points = !empty($posts_pay['vip_' . $vip_level . '_points']) ? round((float) $posts_pay['vip_' . $vip_level . '_points'], 2) : 0;
        if ($vip_level && $vip_points <= 0) {
            $pay_order[$post_id][$user_id] = array('paid_type' => 'vip' . $vip_level . '_free', 'vip_level' => $vip_level, 'modo' => 'points');
            return $pay_order[$post_id][$user_id];
        }
    } else {
        $pay_price = !empty($posts_pay['pay_price']) ? round((float) $posts_pay['pay_price'], 2) : 0;
        if ($pay_price <= 0) {
            $pay_order[$post_id][$user_id] = array('paid_type' => 'free');
            return $pay_order[$post_id][$user_id];
        }

        $vip_level = zib_get_user_vip_level($user_id);
        $vip_price = !empty($posts_pay['vip_' . $vip_level . '_price']) ? round((float) $posts_pay['vip_' . $vip_level . '_price'], 2) : 0;
        if ($vip_level && $vip_price <= 0) {
            $pay_order[$post_id][$user_id] = array('paid_type' => 'vip' . $vip_level . '_free', 'vip_level' => $vip_level);
            return $pay_order[$post_id][$user_id];
        }
    }

    $pay_order[$post_id][$user_id] = false;
    return $pay_order[$post_id][$user_id];
}

//设置cookie密钥
function zibpay_posts_order_created_set_cookie($order)
{

    $order = (array) $order;
    if (!empty($order['post_id'])) {
        $expire = time() + 3600 * 24 * _pz('pay_cookie_day', '15');
        setcookie('zibpay_' . $order['post_id'], $order['order_num'], $expire, '/', '', false);
        //设置cookie密钥，根据订单ID、订单号、订单金额、订单

        $token_key = zibpay_posts_get_order_cookie_token_key($order);
        $token     = md5($token_key);
        setcookie('zibpay_token_' . $order['id'], $token, $expire, '/', '', false);
    }
}

//校验cookie密钥
function zibpay_posts_order_check_cookie_token($order)
{
    if (empty($_COOKIE['zibpay_token_' . $order['id']])) {
        return false;
    }

    $token_key = zibpay_posts_get_order_cookie_token_key($order);

    return md5($token_key) === $_COOKIE['zibpay_token_' . $order['id']];
}

//获取cookie密钥生成的key
function zibpay_posts_get_order_cookie_token_key($order)
{
    $token_key = (int) $order['id'] . '_' . (int) $order['post_id'] . '_' . trim($order['order_num']) . '_' . zib_floatval_round($order['order_price']) . '_' . trim($order['ip_address']);
    return $token_key;
}

/**
 * 获取文章订单时效配置参数
 */
function zibpay_get_post_order_expired_option($post_id)
{
    if (!$post_id) {
        return false;
    }

    $posts_pay = get_post_meta($post_id, 'posts_zibpay', true);
    if (!$posts_pay) {
        return false;
    }

    if (empty($posts_pay['pay_type']) || 'no' === $posts_pay['pay_type'] || empty($posts_pay['order_expired_s']) || empty($posts_pay['order_expired_opt']['time']) || empty($posts_pay['order_expired_opt']['unit'])) {
        return false;
    }

    $time = (int) $posts_pay['order_expired_opt']['time'];

    if ($time <= 0) {
        return false;
    }

    return array(
        'time' => $time,
        'unit' => $posts_pay['order_expired_opt']['unit'],
    );
}

//根据用户ID获取文章订单数据
function zibpay_get_post_paid_data_by_user($post_id, $user_id = 0, $order_expired_option = false)
{
    if (!$post_id || !$user_id) {
        return false;
    }

    $where = [
        'post_id' => $post_id,
        'user_id' => $user_id,
        'status'  => 1,
    ];

    $DB = ZibDB::name(zibpay::$order_table_name);
    $DB->where($where)->order('id', 'DESC');
    if ($order_expired_option) {
        $current_time = current_time('Y-m-d H:i:s');
        $DB->where('pay_time', '>=', date('Y-m-d H:i:s', strtotime('-' . $order_expired_option['time'] . ' ' . $order_expired_option['unit'], strtotime($current_time))));
    }

    $pay_order = zibpay::order_data_map($DB->find()->toArray());

    return $pay_order;
}

function zibpay_get_post_paid_data_by_order_num($post_id, $order_num, $order_expired_option = false)
{
    if (!$post_id || !$order_num) {
        return false;
    }

    $where = [
        'post_id'   => $post_id,
        'order_num' => $order_num,
        'status'    => 1,
    ];

    $DB = ZibDB::name(zibpay::$order_table_name);
    $DB->where($where)->order('id', 'DESC');
    if ($order_expired_option) {
        $current_time = current_time('Y-m-d H:i:s');
        $DB->where('pay_time', '>=', date('Y-m-d H:i:s', strtotime('-' . $order_expired_option['time'] . ' ' . $order_expired_option['unit'], strtotime($current_time))));
    }

    $pay_order = zibpay::order_data_map($DB->find()->toArray());

    return $pay_order;
}

/**
 * @description: 判断文章付费功能是不是积分支付
 * @param {*} $post_meta
 * @param {*} $post_id
 * @return {*}
 */
function zibpay_post_is_points_modo($pay_mate = array(), $post_id = 0)
{

    if (!isset($pay_mate['pay_type'])) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (!isset($pay_mate['pay_type'])) {
        return false;
    }

    return isset($pay_mate['pay_modo']) && $pay_mate['pay_modo'] === 'points';
}

/**
 * 下单数据是否允许走积分支付
 *
 * 现金收银台的 submit_order 只接受服务端确认过的积分商品。
 * 文章、会员积分兑换走 points_initiate_pay，不从这里放行。
 *
 * @param int   $order_type 订单类型
 * @param array $data       已整理的下单数据
 * @return bool
 */
function zibpay_submit_data_allow_points_pay($order_type, $data)
{
    $post_id = !empty($data['post_id']) ? (int) $data['post_id'] : 0;
    if ((int) $order_type !== 11 || !$post_id) {
        return false;
    }

    $opt = zib_bbs_get_plate_follow_pay_options($post_id);
    return !empty($opt['pay_modo']) && $opt['pay_modo'] === 'points';
}

/**
 * 已创建的订单是否允许用积分入账
 *
 * @param array $order 订单
 * @return bool
 */
function zibpay_order_allow_points_pay($order)
{
    $order      = (array) $order;
    $order_type = (int) ($order['order_type'] ?? 0);
    $post_id    = (int) ($order['post_id'] ?? 0);
    if (!$post_id) {
        return false;
    }

    if (get_post_type($post_id) === 'shop_product') {
        return zib_shop_get_product_config($post_id, 'pay_modo', '0') === 'points';
    }

    if (in_array($order_type, array(1, 2, 5, 6, 7), true)) {
        return zibpay_post_is_points_modo(array(), $post_id);
    }

    if ($order_type === 11) {
        $opt = zib_bbs_get_plate_follow_pay_options($post_id);
        return !empty($opt['pay_modo']) && $opt['pay_modo'] === 'points';
    }

    return false;
}

/**
 * 同一笔支付下的订单是否全部允许积分支付
 *
 * @param array $orders 订单列表
 * @return bool
 */
function zibpay_orders_allow_points_pay($orders)
{
    if (empty($orders) || !is_array($orders)) {
        return false;
    }

    foreach ($orders as $order) {
        if (!zibpay_order_allow_points_pay($order)) {
            return false;
        }
    }

    return true;
}

/**
 * 积分应付是否为有效整数
 *
 * 现金金额被改成积分支付时，常带小数；截成整数会少扣或不扣。
 *
 * @param mixed $price 应付积分
 * @return bool
 */
function zibpay_points_price_is_valid($price)
{
    if (!is_numeric($price)) {
        return false;
    }

    $raw = (float) $price;
    if ($raw < 0) {
        return false;
    }

    if ($raw == 0) {
        return true;
    }

    $points = (int) $raw;
    return $points >= 1 && abs($raw - $points) < 0.001;
}

//获取站点今日的订单统计
function zibpay_get_order_statistics_totime($time_type = 'today')
{
    $error = array(
        'count' => 0,
        'sum'   => 0,
        'ids'   => '',
    );

    if (!$time_type) {
        return $error;
    }

    //静态缓存
    static $this_data = null;
    if (isset($this_data[$time_type])) {
        return $this_data[$time_type];
    }

    global $wpdb;

    $time_where = zib_get_time_where_sql($time_type, 'pay_time');
    $data       = $wpdb->get_row("SELECT COUNT(*) as count,SUM(pay_price) as sum FROM {$wpdb->zibpay_order} WHERE $time_where and `status`=1 and pay_price > 0 and pay_type not in ('points','balance')");
    $data       = (array) $data;
    if (!isset($data['count'])) {
        $this_data[$time_type] = $error;
    } else {
        $this_data[$time_type] = array(
            'count' => $data['count'] ?: 0,
            'sum'   => $data['sum'] ? floatval($data['sum']) : 0,
            'ids'   => '',
        );
    }
    return $this_data[$time_type];
}

function zibpay_get_order_status(array &$order)
{
    $status = (int) $order['status'];
    if ($status == 0) {
        $time_remaining = zibpay_get_order_pay_over_time($order);
        if ($time_remaining == 'over') {
            $status = -1;
        }
    }

    return $status;
}

function zibpay_get_order_pay_over_time(array &$order)
{
    if ($order['status'] != 0) {
        return false;
    }

    $max_time     = zibpay_get_order_pay_max_time(); //不能为0
    $current_time = current_time('Y-m-d H:i:s');
    //返回最后到期时间
    $last_time = strtotime('+' . $max_time * 60 . ' Second', strtotime($order['create_time']));
    if (strtotime($current_time) > $last_time) {
        //更新订单状态为失效
        $order['status'] = -1;
        zibpay::close_order($order['id'], 'timeout', __('超时自动关闭', 'zib_language'));
        return 'over';
    }

    return $last_time;
}

//获取支付最大时间
function zibpay_get_order_pay_max_time()
{
    $max_time = (int) _pz('order_pay_max_minutes', 30) ?: 30; //不能为0
    $max_time = $max_time < 5 ? 5 : $max_time;

    return $max_time;
}

//过期时间
function zibpay_get_payment_pay_over_time(array &$payment_data)
{
    $max_time = zibpay_get_order_pay_max_time(); //分钟

    if ($payment_data['status'] != 0) {
        return false;
    }

    $current_time = current_time('Y-m-d H:i:s');
    //返回最后到期时间，过期时间
    $last_time = strtotime('+' . $max_time * 60 . ' Second', strtotime($payment_data['create_time']));
    if (strtotime($current_time) > $last_time) {
        //更新订单状态为失效
        $payment_data['status'] = -1;
        zibpay::close_payment($payment_data['id'], 'timeout', __('超时自动关闭', 'zib_language'));
        return 'over';
    }

    return $last_time;
}

//防止刷单
//限制评论频率
function zib_brush_limit_create_order($order_data)
{

    if (empty($order_data['user_id']) && empty($order_data['ip_address'])) {
        return $order_data;
    }

    //管理员不限制
    if (!empty($order_data['user_id']) && is_super_admin($order_data['user_id'])) {
        //    return $order_data;
    }

    $user_id    = $order_data['user_id'] ?? 0;
    $ip_address = $order_data['ip_address'] ?? '';

    //如果IP地址为本地，则为false
    if (!$user_id && (!$ip_address || preg_match('/^(?:127|172\.16|192\.168)\./', $ip_address))) {
        return $order_data;
    }

    global $wpdb;

    $brush_limit_post = _pz('brush_limit_order');
    $minutes_10       = isset($brush_limit_post['minutes_10']) ? (int) $brush_limit_post['minutes_10'] : 0;
    $day_1            = isset($brush_limit_post['day_1']) ? (int) $brush_limit_post['day_1'] : 0;

    $DB = ZibDB::table($wpdb->zibpay_order);
    if ($user_id) {
        $DB->where('user_id', $user_id);
    } else {
        $DB->where('ip_address', $ip_address);
    }

    if ($minutes_10) {
        $new_DB = clone $DB;
        $count  = $new_DB->whereTime('create_time', 'last_10_minutes')->count();

        if ($count > $minutes_10) {
            return new WP_Error('brush_limit_minutes_10', __('下单过于频繁，请10分钟后再试', 'zib_language'));
        }
    }

    if ($day_1) {
        $new_DB = clone $DB;
        $count  = $new_DB->whereTime('create_time', 'last_1_day')->count();
        if ($count > $day_1) {
            return new WP_Error('brush_limit_day', __('您的下单数量已达今日上限，请明日再试', 'zib_language'));
        }
    }

    return $order_data;
}
add_filter('pre_order_create_data', 'zib_brush_limit_create_order', 99);

/**
 * 后台订单列表排序字段白名单
 *
 * @param string $orderby
 * @param string $default
 * @return string
 */
function zibpay_sanitize_admin_orderby($orderby, $default = 'pay_time')
{
    $allow = array(
        'id',
        'order_num',
        'order_price',
        'pay_price',
        'pay_time',
        'create_time',
        'user_id',
        'post_id',
        'post_author',
        'order_type',
        'pay_type',
        'status',
        'income_price',
        'income_status',
        'rebate_price',
        'rebate_status',
        'referrer_id',
    );
    return in_array($orderby, $allow, true) ? $orderby : $default;
}

/**
 * 后台订单列表排序方向
 *
 * @param string $dir
 * @return string
 */
function zibpay_sanitize_admin_order_dir($dir)
{
    return strtoupper((string) $dir) === 'ASC' ? 'ASC' : 'DESC';
}
