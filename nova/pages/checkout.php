<?php
/*
 * Project       : Nova主题
 * Description   : 结算页
 */

if (!defined('ABSPATH')) exit;

nova_user_page_permission();
if (!nova_get_setting('shop_s', '1')) {
    wp_safe_redirect(home_url());
    exit;
}

get_header();
?>
<div class="nova-container">
    <?php echo nova_checkout_html(); // phpcs:ignore ?>
</div>
<?php
get_footer();
