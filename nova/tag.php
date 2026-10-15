<?php
/*
 * Project       : Nova主题
 * Description   : 标签归档模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<div class="nova-container nova-archive-page">
    <div class="nova-archive-layout">
        <div class="nova-content">
            <?php echo nova_archive_header(); // phpcs:ignore ?>
            <div class="nova-posts-wrap">
                <?php
                if (have_posts()) {
                    echo '<div class="nova-posts-list">';
                    while (have_posts()) {
                        the_post();
                        echo nova_post_card(); // phpcs:ignore
                    }
                    echo '</div>';
                } else {
                    echo nova_empty_box(__('暂无内容', 'nova'), __('该标签下还没有内容', 'nova')); // phpcs:ignore
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
