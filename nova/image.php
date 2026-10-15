<?php
/*
 * Project       : Nova主题
 * Description   : 附件页面模板（图片附件大图展示 + 上一篇/下一篇导航）
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<div class="nova-container nova-page-wrap">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('nova-attachment'); ?>>
            <div class="nova-attachment-head card">
                <h1 class="nova-attachment-title"><?php the_title(); ?></h1>
                <div class="nova-attachment-meta muted-2-color">
                    <span><i class="nova-icon-time"></i><?php echo esc_html(get_the_date()); ?></span>
                    <?php if ($post->post_parent) : ?>
                        <span>
                            <i class="nova-icon-folder"></i><?php esc_html_e('发布在', 'nova'); ?>
                            <a href="<?php echo esc_url(get_permalink($post->post_parent)); ?>"><?php echo esc_html(get_the_title($post->post_parent)); ?></a>
                        </span>
                    <?php endif; ?>
                    <span><i class="nova-icon-view"></i><?php echo nova_count_format(nova_get_post_views()); // phpcs:ignore ?></span>
                    <?php edit_post_link(esc_html__('编辑', 'nova'), '<span><i class="nova-icon-edit"></i>', '</span>'); ?>
                </div>
            </div>

            <div class="nova-attachment-img card">
                <?php
                // 同父级附件导航：下一张/上一张
                $attachments = array_values(get_children(array(
                    'post_parent'    => $post->post_parent,
                    'post_status'    => 'inherit',
                    'post_type'      => 'attachment',
                    'post_mime_type' => 'image',
                    'order'          => 'ASC',
                    'orderby'        => 'menu_order ID',
                )));
                $next_url = wp_get_attachment_url();
                if (count($attachments) > 1) {
                    $current = 0;
                    foreach ($attachments as $k => $att) {
                        if ($att->ID == $post->ID) {
                            $current = $k;
                            break;
                        }
                    }
                    $next_url = isset($attachments[$current + 1])
                        ? get_attachment_link($attachments[$current + 1]->ID)
                        : get_attachment_link($attachments[0]->ID);
                }
                ?>
                <a href="<?php echo esc_url($next_url); ?>" rel="attachment" title="<?php the_title_attribute(); ?>">
                    <?php echo wp_get_attachment_image($post->ID, 'large'); // phpcs:ignore ?>
                </a>
                <?php if (count($attachments) > 1) : ?>
                    <nav class="nova-attachment-nav">
                        <?php previous_image_link(false, '<i class="nova-icon-arrow-left"></i> ' . esc_html__('上一张', 'nova')); ?>
                        <span class="nova-attachment-page muted-2-color"><?php echo esc_html($current + 1) . ' / ' . esc_html(count($attachments)); ?></span>
                        <?php next_image_link(false, esc_html__('下一张', 'nova') . ' <i class="nova-icon-arrow-right"></i>'); ?>
                    </nav>
                <?php endif; ?>
            </div>

            <?php if (!empty($post->post_excerpt)) : ?>
                <div class="nova-attachment-caption card muted-2-color">
                    <?php echo wp_kses_post(wp_get_attachment_caption($post->ID)); ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</div>
<?php
get_footer();
