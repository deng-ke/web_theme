<?php
/*
 * Project       : Nova主题
 * Description   : 商城首页
 */

if (!defined('ABSPATH')) exit;

if (!nova_get_setting('shop_s', '1')) {
    wp_safe_redirect(home_url());
    exit;
}

get_header();
?>
<div class="nova-container">
    <?php echo nova_shop_index_html(); // phpcs:ignore ?>
</div>
<?php
get_footer();
