<?php
/*
 * Project       : Nova主题
 * Description   : 通用归档模板（category/tag/taxonomy/date 回退）
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<div class="nova-container nova-archive-page">
    <div class="nova-archive-layout">
        <div class="nova-content">
            <?php if (is_category() || is_tag() || is_tax()) : ?>
                <?php echo nova_archive_header(); // phpcs:ignore ?>
            <?php else : ?>
                <?php echo nova_page_header_html(get_the_archive_title(), ''); // phpcs:ignore ?>
            <?php endif; ?>

            <?php if (is_category()) : ?>
                <?php echo nova_category_children_nav(); // phpcs:ignore ?>
            <?php endif; ?>

            <div class="nova-posts-wrap">
                <?php
                global $wp_query;
                if (have_posts()) {
                    echo '<div class="nova-posts-list">';
                    while (have_posts()) {
                        the_post();
                        echo nova_post_card('card'); // phpcs:ignore
                    }
                    echo '</div>';
                } else {
                    echo nova_empty_box(__('暂无内容', 'nova'), __('该分类下还没有内容', 'nova')); // phpcs:ignore
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
