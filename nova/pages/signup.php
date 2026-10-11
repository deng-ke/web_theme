<?php
/*
 * Project       : Nova主题
 * Description   : 注册页
 */

if (!defined('ABSPATH')) exit;

if (is_user_logged_in()) {
    wp_safe_redirect(nova_user_center_url());
    exit;
}
if (!nova_get_setting('register_s', '1')) {
    wp_safe_redirect(nova_signin_url());
    exit;
}

get_header();
?>
<div class="nova-container">
    <?php echo nova_signup_page_html(); // phpcs:ignore ?>
</div>
<?php
get_footer();
