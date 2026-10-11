<?php
/*
 * Project       : Nova主题
 * Description   : 搜索结果页模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<div class="nova-container nova-search-page">
    <?php echo nova_search_header(); // phpcs:ignore ?>
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
                    echo nova_empty_box(__('未找到相关内容', 'nova'), __('换个关键词试试吧', 'nova'), 'search'); // phpcs:ignore
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
