<?php
/*
 * Project       : Nova主题
 * Description   : 前端发布文章页面模板
 * Template Name : Nova-发布文章
 */

if (!defined('ABSPATH')) exit;

// 功能未开启时显示 404
if (!nova_newpost_enabled()) {
    status_header(404);
    get_header();
    echo nova_404_html(); // phpcs:ignore
    get_footer();
    exit;
}

// 未登录跳转登录页
if (!is_user_logged_in()) {
    nova_user_page_permission();
    exit;
}

$edit_post = nova_newpost_get_edit_post();

get_header();
?>
<div class="nova-container nova-page-wrap">
    <div class="nova-page-layout nova-newpost-layout">
        <div class="nova-content">
            <div class="nova-newpost-header card">
                <h1 class="nova-newpost-h1"><?php echo $edit_post ? esc_html__('编辑文章', 'nova') : esc_html__('发布文章', 'nova'); ?></h1>
                <div class="nova-newpost-desc muted-2-color">
                    <?php
                    if ($edit_post) {
                        echo esc_html__('正在编辑：', 'nova') . ' ' . esc_html($edit_post->post_title);
                    } else {
                        esc_html_e('分享你的知识，记录你的成长', 'nova');
                    }
                    ?>
                </div>
            </div>
            <?php echo nova_newpost_form_html(); // phpcs:ignore ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</div>
<?php
get_footer();
