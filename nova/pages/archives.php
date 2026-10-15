<?php
/*
 * Project       : Nova主题
 * Description   : 文章归档页面模板（按时间轴列出全站文章）
 * Template Name : Nova-文章归档
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<div class="nova-container nova-page-wrap">
    <?php echo nova_page_header_html(get_the_title(), esc_html__('全站文章按发布时间归档', 'nova')); // phpcs:ignore ?>
    <div class="nova-page-layout">
        <div class="nova-content">
            <?php echo nova_archives_stats_html(); // phpcs:ignore ?>
            <?php echo nova_archives_timeline_html(); // phpcs:ignore ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</div>
<?php
get_footer();
