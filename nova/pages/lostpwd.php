<?php
/*
 * Project       : Nova主题
 * Description   : 找回密码页
 */

if (!defined('ABSPATH')) exit;

if (is_user_logged_in()) {
    wp_safe_redirect(nova_user_center_url());
    exit;
}

get_header();
?>
<div class="nova-container">
    <?php echo nova_lostpwd_page_html(); // phpcs:ignore ?>
</div>
<?php
get_footer();
