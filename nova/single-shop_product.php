<?php
/*
 * Project       : Nova主题
 * Description   : 商城商品详情模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<?php while (have_posts()) : the_post(); ?>
<div class="nova-container nova-shop-single-page">
    <?php echo nova_shop_product_single(get_post()); // phpcs:ignore ?>
    <?php
    //同类商品推荐
    $cats = wp_get_post_terms(get_the_ID(), 'shop_cat', array('fields' => 'ids'));
    if (!empty($cats)) {
        $related_query = new WP_Query(array(
            'post_type'      => 'shop_product',
            'posts_per_page' => 4,
            'post__not_in'   => array(get_the_ID()),
            'tax_query'      => array(array('taxonomy' => 'shop_cat', 'terms' => $cats)),
        ));
        if ($related_query->have_posts()) {
            echo '<div class="nova-related-posts card"><h3 class="nova-block-title"><span>' . esc_html__('相关商品', 'nova') . '</span></h3><div class="nova-product-grid">';
            while ($related_query->have_posts()) {
                $related_query->the_post();
                echo nova_shop_product_card(get_post()); // phpcs:ignore
            }
            echo '</div></div>';
        }
        wp_reset_postdata();
    }
    ?>
</div>
<?php endwhile; ?>
<?php
get_footer();
