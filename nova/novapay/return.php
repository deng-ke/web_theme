<?php
/*
 * Project       : Nova主题
 * Description   : 支付同步返回页面（支付宝/微信跳转回来）
 */

if (!defined('ABSPATH')) exit;

get_header();

$channel = get_query_var('nova_pay_return');
$order_num = isset($_GET['out_trade_no']) ? sanitize_text_field(wp_unslash($_GET['out_trade_no'])) : ''; // phpcs:ignore
$order = $order_num ? Novapay::get_order_by_num($order_num) : null;
$success = $order && 1 == $order->order_status;

?>
<div class="nova-container nova-return-page">
    <div class="nova-return-box card">
        <div class="nova-return-icon">
            <i class="<?php echo $success ? 'nova-icon-success' : 'nova-icon-time'; ?>"></i>
        </div>
        <?php if ($success) : ?>
            <h2><?php esc_html_e('支付成功', 'nova'); ?></h2>
            <p class="muted-2-color"><?php printf(esc_html__('订单号：%s，支付金额 ¥%s', 'nova'), esc_html($order->order_num), esc_html(number_format((float) $order->pay_price, 2))); ?></p>
        <?php else : ?>
            <h2><?php esc_html_e('支付结果确认中', 'nova'); ?></h2>
            <p class="muted-2-color"><?php esc_html_e('如已支付，请稍后在我的订单中查看状态', 'nova'); ?></p>
        <?php endif; ?>
        <div class="nova-return-actions">
            <?php if ($order && $order->post_id) : ?>
                <a class="btn btn-primary" href="<?php echo esc_url(get_permalink($order->post_id)); ?>"><?php esc_html_e('查看内容', 'nova'); ?></a>
            <?php endif; ?>
            <a class="btn btn-outline" href="<?php echo esc_url(nova_user_center_url('orders')); ?>"><?php esc_html_e('我的订单', 'nova'); ?></a>
            <a class="btn btn-outline" href="<?php echo esc_url(home_url()); ?>"><?php esc_html_e('返回首页', 'nova'); ?></a>
        </div>
    </div>
</div>
<?php
get_footer();
