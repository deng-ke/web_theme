<?php
/*
 * Project       : Nova主题
 * Description   : 侧边栏模板
 */

if (!defined('ABSPATH')) exit;
?>
<?php if (is_active_sidebar('sidebar')) : ?>
<aside class="nova-sidebar" id="nova-sidebar">
    <div class="nova-sidebar-inner">
        <?php dynamic_sidebar('sidebar'); ?>
    </div>
</aside>
<?php endif; ?>
