<?php
/*
 * Project       : Nova主题
 * Description   : 页面底部模板
 */

if (!defined('ABSPATH')) exit;
?>
<?php
//全站底部小工具
if (is_active_sidebar('all_bottom_fluid')) {
    echo '<div class="nova-widgets-fluid">';
    dynamic_sidebar('all_bottom_fluid');
    echo '</div>';
}
?>
</div><!-- .nova-main -->

<?php nova_footer(); ?>
<?php nova_back_top(); ?>

</div><!-- #nova-app -->

<?php wp_footer(); ?>
</body>
</html>
