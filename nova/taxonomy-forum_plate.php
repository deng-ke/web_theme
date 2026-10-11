<?php
/*
 * Project       : Nova主题
 * Description   : 论坛板块归档模板
 */

if (!defined('ABSPATH')) exit;

get_header();

$plate = get_queried_object();
?>
<div class="nova-container nova-bbs-plate-page">
    <?php echo nova_bbs_plate_header($plate); // phpcs:ignore ?>
    <div class="nova-bbs-single-layout">
        <div class="nova-content">
            <div class="nova-bbs-post-list">
                <?php
                if (have_posts()) {
                    while (have_posts()) {
                        the_post();
                        echo nova_bbs_post_item(get_post()); // phpcs:ignore
                    }
                } else {
                    echo nova_empty_box(__('暂无帖子', 'nova'), __('该板块还没有帖子', 'nova')); // phpcs:ignore
                }
                ?>
            </div>
            <?php nova_paging(); ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</div>
<?php
get_footer();
