<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-13 21:01:16
 * @LastEditTime : 2026-08-25 12:18:02
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题|商城消息
 * Copyright (c) 2025 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**
 * 订单支付类型后缀（积分/空）
 */
function zib_shop_msg_pay_type_suffix($order)
{
    return ($order['pay_type'] === 'points' ? __('积分', 'zib_language') : '');
}

/**
 * 消息问候语
 */
function zib_shop_msg_greeting($display_name)
{
    return sprintf(__('您好！%s', 'zib_language'), $display_name) . '<br>';
}

/**
 * 商品链接 HTML
 */
function zib_shop_msg_product_link($product_id, $post_title, $options_active_name = '')
{
    $label = $post_title . ($options_active_name ? '[' . $options_active_name . ']' : '');

    return sprintf(__('商品：%s', 'zib_language'), '<a href="' . get_the_permalink($product_id) . '">' . $label . '</a>') . '<br>';
}

/**
 * 查看订单按钮
 */
function zib_shop_msg_view_order_btn($link, $text = null)
{
    if (null === $text) {
        $text = __('立即查看', 'zib_language');
    }

    return __('您可以点击下方按钮查看订单详情', 'zib_language') . '<br>'
    . '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($link) . '">' . $text . '</a>' . '<br>';
}

//自动发货失败，通知用户联系商家
function zib_shop_auto_delivery_fail_to_user(array $order, array $order_meta_data)
{
    //发送邮件及站内信
    $product_id = $order['post_id'];
    $post_data  = get_post($product_id);
    $post_title = $order_meta_data['product_title'] ?? '';
    if ($post_data) {
        $post_title = zib_str_cut($post_data->post_title, 0, 20, '...');
    }

    $receive_user_id   = $order['user_id'];
    $receive_user_data = get_userdata($receive_user_id);
    $user_email[]      = $order_meta_data['consignee']['email'] ?? '';
    if (isset($receive_user_data->user_email)) {
        $user_email[] = $receive_user_data->user_email;
    }
    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $title   = sprintf(__('您购买的商品自动发货失败，请与客服联系[商品：%1$s%2$s]', 'zib_language'), ($post_title ? '[' . $post_title . ']' : ''), (!empty($order_meta_data['options_active_name']) ? '[' . $order_meta_data['options_active_name'] . ']' : ''));
    $message = __('您好！', 'zib_language') . '<br>' . __('您购买的商品自动发货失败，请与客服联系', 'zib_language') . '<br>';
    $message .= sprintf(__('商品：%s', 'zib_language'), '<a href="' . get_the_permalink($product_id) . '">' . ($post_title ? '[' . $post_title . ']' : '') . (!empty($order_meta_data['options_active_name']) ? '[' . $order_meta_data['options_active_name'] . ']' : '') . '</a>') . '<br>';

    //发送邮件
    zib_send_email($user_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true) && $receive_user_data) {
        ZibMsg::add(array(
            'send_user'    => 'admin',
            'receive_user' => $receive_user_id,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }

    //发送微信模板消息
    if ($receive_user_data) {
        $wechat_template_data = array(
            'status' => __('自动发货失败，请联系客服', 'zib_language'),
            'name'   => $post_title,
            'num'    => $order['order_num'],
            'time'   => $order['pay_time'],
        );
        zib_wechat_template_send($receive_user_id, 'shop_auto_delivery_fail', $wechat_template_data, zib_get_user_center_url('order'));
    }
    //恢复到原来的语言
    zib_restore_previous_locale();
}

//虚拟商品商品，将内容发送给用户
function zib_shop_virtual_shipping_to_user(array $order, array $order_meta_data)
{

    $delivery_html = $order_meta_data['shipping_data']['delivery_content'] ?? '';

    //发送邮件及站内信
    $product_id = $order['post_id'];
    $post_data  = get_post($product_id);
    $post_title = $order_meta_data['product_title'] ?? '';
    if ($post_data) {
        $post_title = zib_str_cut($post_data->post_title, 0, 20, '...');
    }

    $receive_user_id   = $order['user_id'];
    $receive_user_data = get_userdata($receive_user_id);
    $user_email[]      = $order_meta_data['consignee']['email'] ?? '';
    if (isset($receive_user_data->user_email)) {
        $user_email[] = $receive_user_data->user_email;
    }

    $title   = sprintf(__('请查收您购买的商品%1$s%2$s', 'zib_language'), ($post_title ? '[' . $post_title . ']' : ''), (!empty($order_meta_data['options_active_name']) ? '[' . $order_meta_data['options_active_name'] . ']' : ''));
    $message = __('您好！', 'zib_language') . '<br>' . __('请查收您购买的商品', 'zib_language') . '<br>';
    $message .= sprintf(__('商品：%s', 'zib_language'), '<a href="' . get_the_permalink($product_id) . '">' . ($post_title ? '[' . $post_title . ']' : '') . (!empty($order_meta_data['options_active_name']) ? '[' . $order_meta_data['options_active_name'] . ']' : '') . '</a>') . '<br>';
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), ($order['pay_type'] === 'points' ? __('积分', 'zib_language') : '')) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= __('内容：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box">' . $delivery_html . '</div>';

    //发送邮件
    zib_send_email($user_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true) && $receive_user_data) {
        ZibMsg::add(array(
            'send_user'    => $post_data->post_author ?? 'admin',
            'receive_user' => $order['user_id'],
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }
}

//通知商家发货
function zib_shop_notify_shipping_to_author(array $order, array $order_meta_data)
{
    $product_id  = $order['post_id'];
    $post_data   = get_post($product_id);
    $author_id   = $order['post_author'] ?: $post_data->post_author;
    $author_data = get_userdata($author_id);
    if (!isset($author_data->display_name)) {
        return;
    }

    $author_email        = $author_data->user_email ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';
    $options_active_name = $order_meta_data['options_active_name'] ?? '';
    if ($post_data) {
        $post_title = zib_str_cut($post_data->post_title, 0, 20, '...');
    }

    $shipping_type = $order_meta_data['shipping_type'] ?? '';
    $_title        = $shipping_type === 'auto' ? __('商品自动发货失败，请及时处理', 'zib_language') : __('您有新的订单需要发货', 'zib_language');
    $link          = admin_url('admin.php?page=zibpay_page#/shipping?shipping_status=0');

    $title   = sprintf(__('%1$s[商品：%2$s%3$s]', 'zib_language'), $_title, $post_title, (!$options_active_name ? '' : '[' . $options_active_name . ']'));
    $message = zib_shop_msg_greeting($author_data->display_name) . $_title . '<br>';
    $message .= zib_shop_msg_product_link($product_id, $post_title, $options_active_name);
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= !empty($order_meta_data['remark']) ? sprintf(__('订单备注：%s', 'zib_language'), $order_meta_data['remark']) . '<br>' : '';
    $message .= zib_shop_msg_view_order_btn($link, __('去发货', 'zib_language'));

    //发送邮件
    zib_send_email($author_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true)) {
        ZibMsg::add(array(
            'send_user'    => 'admin',
            'receive_user' => $author_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }

    $wechat_template_data = array(
        'name' => $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']'),
        'num'  => $order['order_num'],
        'time' => $order['pay_time'],
        'desc' => $shipping_type === 'auto' ? __('商品自动发货失败，请及时处理', 'zib_language') : __('您有新的订单需要发货', 'zib_language'),
    );
    zib_wechat_template_send($author_data->ID, 'shop_notify_shipping_to_author', $wechat_template_data, $link);
}

//商家发货后通知用户
function zib_shop_manual_shipping_to_user(array $order, array $order_meta_data)
{
    $delivery_type       = $order_meta_data['shipping_data']['delivery_type'] ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';
    $delivery_remark     = $order_meta_data['shipping_data']['delivery_remark'] ?? '';
    $options_active_name = $order_meta_data['options_active_name'] ?? '';
    $post_data           = get_post($order['post_id']);
    $user_data           = get_userdata($order['user_id']);
    $order_link          = zib_get_user_center_url('order', 'wait-receive');

    //手动切换到前台语言
    zib_switch_to_frontend_locale();
    if ($post_data) {
        $post_title = zib_str_cut($post_data->post_title, 0, 20, '...');
    }

    $title   = sprintf(__('您购买的商品%1$s%2$s已发货', 'zib_language'), ($post_title ? '[' . $post_title . ']' : ''), (!$options_active_name ? '' : '[' . $options_active_name . ']'));
    $message = zib_shop_msg_greeting($user_data->display_name);
    $message .= __('您购买的商品已发货，请注意查收！', 'zib_language') . '<br>';
    if ($post_data) {
        $message .= zib_shop_msg_product_link($order['post_id'], $post_title, $options_active_name);
    }
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= sprintf(__('发货时间：%s', 'zib_language'), $order_meta_data['shipping_data']['delivery_time']) . '<br>';

    if ($delivery_type == 'express') {
        $message .= sprintf(__('快递单号：%s', 'zib_language'), $order_meta_data['shipping_data']['express_number']) . '<br>';
        $message .= sprintf(__('快递公司：%s', 'zib_language'), $order_meta_data['shipping_data']['express_company_name']) . '<br>';
    }

    if ($delivery_type == 'no_express') {
        $message .= __('商家选择', 'zib_language') . '<b>' . __('无需物流发货', 'zib_language') . '</b>' . '<br>';
    }

    if ($delivery_remark) {
        $message .= sprintf(__('发货备注：%s', 'zib_language'), $delivery_remark) . '<br>';
    }

    $message .= zib_shop_msg_view_order_btn($order_link);

    if ($user_data) {
        zib_send_email($user_data->user_email, $title, $message);
    }

    //发送站消息
    if (_pz('message_s', true) && $user_data) {
        ZibMsg::add(array(
            'send_user'    => $post_data->post_author ?? 'admin',
            'receive_user' => $user_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }

    //发送微信模板消息
    if (in_array($delivery_type, ['express', 'no_express']) && $user_data) {
        if ($delivery_type == 'express') {
            $express_number       = $order_meta_data['shipping_data']['express_number'] ?? '';
            $express_company_name = $order_meta_data['shipping_data']['express_company_name'] ?? '';
        } else {
            $express_number       = '0';
            $express_company_name = __('无需物流发货', 'zib_language');
        }

        $wechat_template_data = array(
            'name'    => $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']'),
            'num'     => $order['order_num'],
            'time'    => $order_meta_data['shipping_data']['delivery_time'] ?? '',
            'express' => $express_company_name,
            'number'  => $express_number,
        );
        zib_wechat_template_send($user_data->ID, 'shop_express_shipping', $wechat_template_data, $order_link);
    }
    //恢复到原来的语言
    zib_restore_previous_locale();
}

//用户申请售后，通知商家
function zib_shop_user_apply_after_sale_to_author(array $order, array $order_meta_data)
{
    $product_id           = $order['post_id'];
    $post_data            = get_post($product_id);
    $author_id            = $order['post_author'] ?: $post_data->post_author;
    $author_data          = get_userdata($author_id);
    $after_sale_type_name = zib_shop_get_after_sale_type_name($order_meta_data['after_sale_data']['type'] ?? '');

    if (!isset($author_data->display_name)) {
        return;
    }

    $author_email        = $author_data->user_email ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';
    $options_active_name = $order_meta_data['options_active_name'] ?? '';
    $link                = admin_url('admin.php?page=zibpay_page#/after-sale');
    $price               = $order_meta_data['after_sale_data']['price'] ?? 0;
    $reason              = $order_meta_data['after_sale_data']['reason'] ?? '';
    $remark              = $order_meta_data['after_sale_data']['remark'] ?? '';
    $user_data           = get_userdata($order['user_id']);
    $user_name           = $user_data->display_name ?? '';

    $title   = __('您有新的售后申请，请及时处理', 'zib_language');
    $message = zib_shop_msg_greeting($author_data->display_name);
    $message .= __('您有新的售后申请，请及时处理！', 'zib_language') . '<br>';
    $message .= zib_shop_msg_product_link($product_id, $post_title, $options_active_name);
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('售后类型：%s', 'zib_language'), $after_sale_type_name) . '<br>';
    $message .= sprintf(__('申请用户：%s', 'zib_language'), $user_name) . '<br>';
    if ($price) {
        $message .= sprintf(__('退款金额：%1$s%2$s', 'zib_language'), $price, zib_shop_msg_pay_type_suffix($order)) . '<br>';
    }
    if ($reason) {
        $message .= sprintf(__('申请原因：%s', 'zib_language'), $reason) . '<br>';
    }
    if ($remark) {
        $message .= sprintf(__('申请备注：%s', 'zib_language'), $remark) . '<br>';
    }
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= sprintf(__('售后申请时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_apply_time']) . '<br>';
    $message .= __('您可以点击下方按钮处理此售后', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($link) . '">' . __('立即处理', 'zib_language') . '</a>' . '<br>';

    //发送邮件
    zib_send_email($author_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true)) {
        ZibMsg::add(array(
            'send_user'    => 'admin',
            'receive_user' => $author_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }

    //发送微信模板消息
    $wechat_template_data = array(
        'name' => $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']'),
        'num'  => $order['order_num'],
        'user' => $user_name,
        'time' => $order_meta_data['after_sale_data']['user_apply_time'],
        'type' => $after_sale_type_name,
    );
    zib_wechat_template_send($author_data->ID, 'shop_after_sale_to_author', $wechat_template_data, $link);
}

//商家同意售后，等待用户发货
function zib_shop_after_sale_wait_user_return_to_user(array $order, array $order_meta_data)
{
    $product_id           = $order['post_id'];
    $post_data            = get_post($product_id);
    $user_id              = $order['user_id'];
    $user_data            = get_userdata($user_id);
    $link                 = zib_get_user_center_url('order', 'after-sale');
    $after_sale_type_name = zib_shop_get_after_sale_type_name($order_meta_data['after_sale_data']['type'] ?? '');

    if (!isset($user_data->display_name)) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $user_email          = $user_data->user_email ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';
    $options_active_name = $order_meta_data['options_active_name'] ?? '';

    $title   = __('您的售后申请商家已同意，等待您发货', 'zib_language');
    $message = zib_shop_msg_greeting($user_data->display_name);
    $message .= __('您的售后申请商家已同意，等待您发货！', 'zib_language') . '<br>';
    $message .= zib_shop_msg_product_link($product_id, $post_title, $options_active_name);
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('售后类型：%s', 'zib_language'), $after_sale_type_name) . '<br>';
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= sprintf(__('售后申请时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_apply_time']) . '<br>';
    $message .= sprintf(__('售后处理时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['author_handle_time']) . '<br>';
    $message .= !empty($order_meta_data['after_sale_data']['author_handle_remark']) ? sprintf(__('售后处理备注：%s', 'zib_language'), $order_meta_data['after_sale_data']['author_handle_remark']) . '<br>' : '';
    $message .= zib_shop_msg_view_order_btn($link);

    //发送邮件
    zib_send_email($user_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true) && $user_data) {
        ZibMsg::add(array(
            'send_user'    => $post_data->post_author ?? 'admin',
            'receive_user' => $user_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }

    //发送微信模板消息
    if ($user_data) {
        $wechat_template_data = array(
            'name' => $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']'),
            'num'  => $order['order_num'],
            'time' => $order_meta_data['after_sale_data']['user_apply_time'],
            'type' => $after_sale_type_name,
            'desc' => $title,
        );
        zib_wechat_template_send($user_data->ID, 'shop_after_sale_wait_user_return', $wechat_template_data, $link);
    }
    //恢复到原来的语言
    zib_restore_previous_locale();
}

//用户退货，等待商家收货
function zib_shop_after_sale_user_returned_to_author(array $order, array $order_meta_data)
{
    $product_id           = $order['post_id'];
    $post_data            = get_post($product_id);
    $author_id            = $order['post_author'] ?: $post_data->post_author;
    $author_data          = get_userdata($author_id);
    $link                 = admin_url('admin.php?page=zibpay_page#/after-sale');
    $after_sale_type_name = zib_shop_get_after_sale_type_name($order_meta_data['after_sale_data']['type'] ?? '');
    $price                = $order_meta_data['after_sale_data']['price'] ?? 0;

    if (!isset($author_data->display_name)) {
        return;
    }

    $author_email        = $author_data->user_email ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';
    $options_active_name = $order_meta_data['options_active_name'] ?? '';

    $title   = __('售后订单，用户已发货', 'zib_language');
    $message = zib_shop_msg_greeting($author_data->display_name);
    $message .= __('售后订单，用户已发货！', 'zib_language') . '<br>';
    $message .= zib_shop_msg_product_link($product_id, $post_title, $options_active_name);
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('售后类型：%s', 'zib_language'), $after_sale_type_name) . '<br>';
    if ($price) {
        $message .= sprintf(__('申请退款金额：%1$s%2$s', 'zib_language'), $price, zib_shop_msg_pay_type_suffix($order)) . '<br>';
    }
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= sprintf(__('售后申请时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_apply_time']) . '<br>';
    $message .= sprintf(__('售后处理时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['author_handle_time']) . '<br>';
    $message .= sprintf(__('用户发货时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_return_time']) . '<br>';
    $message .= sprintf(__('快递单号：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_return_data']['express_number']) . '<br>';
    $message .= sprintf(__('快递公司：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_return_data']['express_company_name']) . '<br>';
    $message .= $order_meta_data['after_sale_data']['user_return_data']['return_remark'] ? sprintf(__('发货备注：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_return_data']['return_remark']) . '<br>' : '';
    $message .= zib_shop_msg_view_order_btn($link);

    //发送邮件
    zib_send_email($author_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true)) {
        ZibMsg::add(array(
            'send_user'    => 'admin',
            'receive_user' => $author_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }
}

//售后处理结束，通知用户
function zib_shop_after_sale_to_end_to_user(array $order, array $order_meta_data)
{
    $after_sale_status = $order_meta_data['after_sale_data']['status'] ?? 0;
    $status_name       = [
        1 => __('待处理', 'zib_language'),
        2 => __('处理中', 'zib_language'),
        3 => __('处理完成', 'zib_language'),
        4 => __('用户取消', 'zib_language'),
        5 => __('商家驳回', 'zib_language'),
    ];

    if ($after_sale_status === 4) {
        //用户取消，通知商家
        zib_shop_after_sale_user_cancels_to_author($order, $order_meta_data);
        return;
    }

    $user_data = get_userdata($order['user_id']);
    if (!isset($user_data->display_name)) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $product_id           = $order['post_id'];
    $after_sale_type_name = zib_shop_get_after_sale_type_name($order_meta_data['after_sale_data']['type'] ?? '');
    $link                 = zib_get_user_center_url('order', ($after_sale_status == 5 ? '' : 'after-sale'));
    $user_email           = $user_data->user_email ?? '';
    $post_title           = $order_meta_data['product_title'] ?? '';
    $options_active_name  = $order_meta_data['options_active_name'] ?? '';
    $price                = $order_meta_data['after_sale_data']['price'] ?? 0;

    $title   = $after_sale_status == 5 ? __('您的售后申请商家已驳回', 'zib_language') : __('售后已处理完成', 'zib_language');
    $message = zib_shop_msg_greeting($user_data->display_name);
    $message .= $title . '<br>';
    $message .= zib_shop_msg_product_link($product_id, $post_title, $options_active_name);
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('售后类型：%s', 'zib_language'), $after_sale_type_name) . '<br>';
    if ($price) {
        $message .= sprintf(__('申请退款金额：%1$s%2$s', 'zib_language'), $price, zib_shop_msg_pay_type_suffix($order)) . '<br>';
    }
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= sprintf(__('售后申请时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_apply_time']) . '<br>';
    $message .= sprintf(__('售后处理时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['end_time']) . '<br>';
    $message .= $order_meta_data['after_sale_data']['author_remark'] ? sprintf(__('商家处理备注：%s', 'zib_language'), $order_meta_data['after_sale_data']['author_remark']) . '<br>' : '';
    $message .= zib_shop_msg_view_order_btn($link);

    //发送邮件
    zib_send_email($user_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true) && $user_data) {
        ZibMsg::add(array(
            'send_user'    => 'admin',
            'receive_user' => $user_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }

    //发送微信模板消息
    if ($user_data) {
        $wechat_template_data = array(
            'name' => $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']'),
            'num'  => $order['order_num'],
            'time' => $order_meta_data['after_sale_data']['user_apply_time'],
            'end'  => $order_meta_data['after_sale_data']['end_time'],
            'type' => $after_sale_type_name,
            'desc' => $title,
        );
        zib_wechat_template_send($user_data->ID, 'shop_after_sale_end', $wechat_template_data, $link);
    }
    //恢复到原来的语言
    zib_restore_previous_locale();
}

//用户取消售后，通知商家
function zib_shop_after_sale_user_cancels_to_author(array $order, array $order_meta_data)
{
    $product_id           = $order['post_id'];
    $post_data            = get_post($product_id);
    $author_id            = $order['post_author'] ?: $post_data->post_author;
    $author_data          = get_userdata($author_id);
    $after_sale_type_name = zib_shop_get_after_sale_type_name($order_meta_data['after_sale_data']['type'] ?? '');
    $link                 = admin_url('admin.php?page=zibpay_page#/after-sale');

    if (!isset($author_data->display_name)) {
        return;
    }

    $author_email        = $author_data->user_email ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';
    $options_active_name = $order_meta_data['options_active_name'] ?? '';
    $price               = $order_meta_data['after_sale_data']['price'] ?? 0;

    $title   = __('用户已取消售后申请', 'zib_language');
    $message = zib_shop_msg_greeting($author_data->display_name);
    $message .= __('用户已取消售后申请！', 'zib_language') . '<br>';
    $message .= zib_shop_msg_product_link($product_id, $post_title, $options_active_name);
    $message .= sprintf(__('订单号：%s', 'zib_language'), $order['order_num']) . '<br>';
    $message .= sprintf(__('售后类型：%s', 'zib_language'), $after_sale_type_name) . '<br>';
    if ($price) {
        $message .= sprintf(__('申请退款金额：%1$s%2$s', 'zib_language'), $price, zib_shop_msg_pay_type_suffix($order)) . '<br>';
    }
    $message .= sprintf(__('订单金额：%1$s%2$s', 'zib_language'), zib_floatval_round($order['pay_price']), zib_shop_msg_pay_type_suffix($order)) . '<br>';
    $message .= sprintf(__('付款时间：%s', 'zib_language'), $order['pay_time']) . '<br>';
    $message .= sprintf(__('售后申请时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['user_apply_time']) . '<br>';
    $message .= sprintf(__('用户取消时间：%s', 'zib_language'), $order_meta_data['after_sale_data']['end_time']) . '<br>';
    $message .= zib_shop_msg_view_order_btn($link);

    //发送邮件
    zib_send_email($author_email, $title, $message);

    //发送站消息
    if (_pz('message_s', true)) {
        ZibMsg::add(array(
            'send_user'    => 'admin',
            'receive_user' => $author_data->ID,
            'type'         => 'pay',
            'title'        => $title,
            'content'      => $message,
        ));
    }
}
