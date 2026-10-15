<?php
/*
 * Project       : Nova主题
 * Description   : 首页模板（轮播/公告/Tab文章流/侧边栏）
 */

if (!defined('ABSPATH')) exit;

get_header();

$tab = nova_index_tab_html();
?>
<div class="nova-container nova-index-page">
    <?php if (is_active_sidebar('home_top_fluid')) : ?>
        <div class="nova-widgets-fluid">
            <?php dynamic_sidebar('home_top_fluid'); ?>
        </div>
    <?php endif; ?>

    <div class="nova-index-layout">
        <div class="nova-content">
            <?php echo nova_home_slider(); // phpcs:ignore ?>

            <?php echo nova_home_notice(); // phpcs:ignore ?>

            <?php echo nova_sticky_posts(); // phpcs:ignore ?>

            <?php if (is_active_sidebar('home_top_content')) : ?>
                <?php dynamic_sidebar('home_top_content'); ?>
            <?php endif; ?>

            <?php echo $tab; // phpcs:ignore ?>

            <div class="nova-posts-wrap" id="nova-posts-wrap">
                <?php
                $args = nova_index_query_args('new');
                if (nova_get_setting('loadmore_s', '1')) {
                    $args['posts_per_page'] = 10;
                }
                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    echo '<div class="nova-posts-list">';
                    while ($query->have_posts()) {
                        $query->the_post();
                        echo nova_post_card(); // phpcs:ignore
                    }
                    echo '</div>';
                    wp_reset_postdata();
                } else {
                    echo nova_empty_box(__('暂无内容', 'nova'), __('这里还什么都没有，去别处看看吧', 'nova')); // phpcs:ignore
                }
                ?>
            </div>

            <?php if (nova_get_setting('loadmore_s', '1')) : ?>
                <div class="nova-loadmore-box">
                    <button class="btn btn-outline nova-loadmore-btn" data-paged="2" data-tab="new" data-max="<?php echo esc_attr($query->max_num_pages); ?>"><i class="nova-icon-more"></i><?php esc_html_e('加载更多', 'nova'); ?></button>
                </div>
            <?php else : ?>
                <?php nova_paging($query); ?>
            <?php endif; ?>

            <?php if (is_active_sidebar('home_bottom_content')) : ?>
                <?php dynamic_sidebar('home_bottom_content'); ?>
            <?php endif; ?>
        </div>

        <?php get_sidebar(); ?>
    </div>
</div>
<?php
get_footer();
