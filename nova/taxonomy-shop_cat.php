<?php
/*
 * Project       : Nova主题
 * Description   : 商城分类归档模板
 */

if (!defined('ABSPATH')) exit;

get_header();

$cat = get_queried_object();
?>
<div class="nova-container nova-shop-cat-page">
    <div class="nova-shop-cat-header card">
        <h1><?php echo esc_html($cat->name); ?></h1>
        <?php if (term_description()) : ?>
            <p class="muted-2-color"><?php echo esc_html(wp_strip_all_tags(term_description())); ?></p>
        <?php endif; ?>
    </div>
    <div class="nova-shop-single-layout">
        <div class="nova-content">
            <div class="nova-product-grid">
                <?php
                if (have_posts()) {
                    while (have_posts()) {
                        the_post();
                        echo nova_shop_product_card(get_post()); // phpcs:ignore
                    }
                } else {
                    echo nova_empty_box(__('暂无商品', 'nova'), __('该分类下还没有商品', 'nova')); // phpcs:ignore
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
