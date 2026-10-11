<?php
/*
 * Project       : Nova主题
 * Description   : 支付页
 */

if (!defined('ABSPATH')) exit;

nova_user_page_permission();
if (!nova_get_setting('pay_s', '1')) {
    wp_safe_redirect(home_url());
    exit;
}

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

get_header();
?>
<div class="nova-container nova-pay-container">
    <?php echo nova_pay_html($order_id); // phpcs:ignore ?>
</div>
<?php
get_footer();
