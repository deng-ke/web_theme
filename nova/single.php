<?php
/*
 * Project       : Nova主题
 * Description   : 文章详情页模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<?php while (have_posts()) : the_post(); ?>
<div class="nova-container nova-single-page">
    <?php if (is_active_sidebar('single_top')) : ?>
        <div class="nova-widgets-fluid">
            <?php dynamic_sidebar('single_top'); ?>
        </div>
    <?php endif; ?>

    <div class="nova-single-layout">
        <div class="nova-content">
            <article id="post-<?php the_ID(); ?>" <?php post_class('nova-single-post card'); ?>>
                <?php echo nova_post_header(); // phpcs:ignore ?>
                <div class="nova-single-body">
                    <?php echo nova_post_content(); // phpcs:ignore ?>
                    <?php echo nova_post_tags(); // phpcs:ignore ?>
                </div>
                <?php echo nova_post_action_bar(); // phpcs:ignore ?>
            </article>

            <?php echo nova_post_author_box(); // phpcs:ignore ?>

            <?php echo nova_post_nav(); // phpcs:ignore ?>

            <?php echo nova_related_posts(6); // phpcs:ignore ?>

            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="nova-single-comments card">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php get_sidebar(); ?>
    </div>

    <?php if (is_active_sidebar('single_bottom')) : ?>
        <div class="nova-widgets-fluid">
            <?php dynamic_sidebar('single_bottom'); ?>
        </div>
    <?php endif; ?>
</div>
<?php endwhile; ?>
<?php
get_footer();
