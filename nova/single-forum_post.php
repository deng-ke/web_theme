<?php
/*
 * Project       : Nova主题
 * Description   : 论坛帖子详情模板
 */

if (!defined('ABSPATH')) exit;

get_header();
?>
<?php while (have_posts()) : the_post(); ?>
<div class="nova-container nova-bbs-single-page">
    <div class="nova-bbs-single-layout">
        <div class="nova-content">
            <?php
            $post = get_post();
            $user_id = $post->post_author;
            $plate = wp_get_post_terms($post->ID, 'forum_plate');
            $plate = !empty($plate) ? $plate[0] : null;
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('nova-bbs-post-single card'); ?>>
                <div class="nova-bbs-post-head">
                    <h1 class="nova-bbs-post-title">
                        <?php if (get_post_meta($post->ID, 'nova_bbs_top', true)) : ?>
                            <span class="nova-bbs-tag top"><?php esc_html_e('置顶', 'nova'); ?></span>
                        <?php endif; ?>
                        <?php if (get_post_meta($post->ID, 'nova_bbs_essence', true)) : ?>
                            <span class="nova-bbs-tag essence"><?php esc_html_e('精华', 'nova'); ?></span>
                        <?php endif; ?>
                        <?php the_title(); ?>
                    </h1>
                </div>
                <div class="nova-bbs-post-author">
                    <a class="nova-avatar-sm" href="<?php echo esc_url(get_author_posts_url($user_id)); ?>">
                        <img src="<?php echo esc_url(nova_get_user_avatar($user_id, 80)); ?>" alt="avatar">
                    </a>
                    <div class="nova-bbs-post-author-info">
                        <a class="nova-bbs-post-author-name" href="<?php echo esc_url(get_author_posts_url($user_id)); ?>"><?php echo esc_html(nova_get_user_name($user_id)); ?></a>
                        <div class="muted-2-color">
                            <?php if ($plate) : ?>
                                <a href="<?php echo esc_url(get_term_link($plate)); ?>"><?php echo esc_html($plate->name); ?></a> ·
                            <?php endif; ?>
                            <?php echo esc_html(nova_time_ago(get_the_time('Y-m-d H:i:s'))); ?>
                        </div>
                    </div>
                </div>
                <div class="nova-bbs-post-content">
                    <?php the_content(); ?>
                </div>
                <div class="nova-bbs-post-actions">
                    <?php echo nova_post_like(); // phpcs:ignore ?>
                    <?php if (is_user_logged_in() && (get_current_user_id() === (int) $user_id || current_user_can('edit_post', $post->ID))) : ?>
                        <a class="btn btn-outline" href="<?php echo esc_url(nova_bbs_edit_url($plate ? $plate->term_id : 0, $post->ID)); ?>"><i class="nova-icon-edit"></i><?php esc_html_e('编辑帖子', 'nova'); ?></a>
                    <?php endif; ?>
                </div>
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
