<?php
/*
 * Project       : Nova主题
 * Description   : 普通页面模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<?php while (have_posts()) : the_post(); ?>
<div class="nova-container nova-page-wrap">
    <?php if (!is_front_page()) : ?>
        <?php echo nova_page_header_html(get_the_title(), ''); // phpcs:ignore ?>
    <?php endif; ?>
    <div class="nova-page-layout">
        <div class="nova-content">
            <article id="post-<?php the_ID(); ?>" <?php post_class('nova-single-page-content card'); ?>>
                <?php echo nova_page_content_html(); // phpcs:ignore ?>
            </article>
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="nova-single-comments card">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</div>
<?php endwhile; ?>
<?php
get_footer();
