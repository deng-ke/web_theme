<?php
/*
 * Project       : Nova主题
 * Description   : 404页面模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<div class="nova-container">
    <?php echo nova_404_html(); // phpcs:ignore ?>
</div>
<?php
get_footer();
