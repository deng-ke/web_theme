<?php
/*
 * Project       : Nova主题
 * Description   : 作者页模板
 */

if (!defined('ABSPATH')) exit;

get_header();

$author_id = get_queried_object_id();
?>
<div class="nova-container nova-author-page">
    <?php echo nova_author_page_header(); // phpcs:ignore ?>
    <?php echo nova_author_tab_html(); // phpcs:ignore ?>
    <div class="nova-archive-layout">
        <div class="nova-content">
            <div class="nova-posts-wrap">
                <?php
                if (have_posts()) {
                    echo '<div class="nova-posts-list">';
                    while (have_posts()) {
                        the_post();
                        echo nova_post_card('card'); // phpcs:ignore
                    }
                    echo '</div>';
                } else {
                    echo nova_empty_box(__('暂无内容', 'nova'), __('该用户还没有发布内容', 'nova')); // phpcs:ignore
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
