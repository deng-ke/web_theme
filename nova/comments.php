<?php
/*
 * Project       : Nova主题
 * Description   : 评论模板
 */

if (!defined('ABSPATH')) exit;

if (post_password_required()) return;

$GLOBALS['comment_count'] = 0;
?>
<div class="nova-comments">
    <?php echo nova_comments_head_html(); // phpcs:ignore ?>

    <?php if (have_comments()) : ?>
        <ol class="nova-comment-list">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 44,
                'callback'    => 'nova_comment_list',
                'end-callback' => 'nova_comment_list_end',
            ));
            ?>
        </ol>
        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php if (!comments_open()) : ?>
        <p class="nova-comments-closed muted-2-color"><?php esc_html_e('评论已关闭', 'nova'); ?></p>
    <?php else : ?>
        <?php
        comment_form();
        ?>
    <?php endif; ?>
</div>
