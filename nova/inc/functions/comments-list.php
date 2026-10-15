<?php
/*
 * Project       : Nova主题
 * Description   : 评论列表与评论表单
 */

if (!defined('ABSPATH')) exit;

/**
 * 评论列表容器
 */
function nova_comment_list($comment, $args, $depth)
{
    $GLOBALS['comment'] = $comment;
    $comment_id = $comment->comment_ID;
    $user_id = $comment->user_id;
    ?>
    <li <?php comment_class('nova-comment-item'); ?> id="comment-<?php comment_ID(); ?>">
        <div class="nova-comment-inner">
            <div class="nova-comment-avatar">
                <?php if ($user_id) : ?>
                    <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>">
                        <img src="<?php echo esc_url(nova_get_user_avatar($user_id, 100)); ?>" alt="avatar">
                    </a>
                <?php else : ?>
                    <img src="<?php echo esc_url(nova_default_avatar()); ?>" alt="avatar">
                <?php endif; ?>
            </div>
            <div class="nova-comment-body">
                <div class="nova-comment-head">
                    <?php if ($user_id) : ?>
                        <a class="nova-comment-author" href="<?php echo esc_url(get_author_posts_url($user_id)); ?>"><?php echo esc_html(nova_get_user_name($user_id)); ?></a>
                        <?php if (nova_is_vip($user_id)) : ?>
                            <span class="nova-vip-badge"><i class="nova-icon-vip"></i>VIP</span>
                        <?php endif; ?>
                    <?php else : ?>
                        <span class="nova-comment-author"><?php comment_author(); ?></span>
                    <?php endif; ?>
                    <span class="nova-comment-time muted-2-color"><?php printf(esc_html__('%s 发布', 'nova'), esc_html(nova_time_ago(get_comment_time('Y-m-d H:i:s')))); ?></span>
                    <span class="nova-comment-floor muted-2-color">#<?php echo esc_html($GLOBALS['comment_count']); ?></span>
                </div>
                <div class="nova-comment-content">
                    <?php if ('0' === $comment->comment_approved) : ?>
                        <em class="nova-comment-pending"><?php esc_html_e('评论审核中…', 'nova'); ?></em>
                    <?php endif; ?>
                    <?php comment_text(); ?>
                </div>
                <div class="nova-comment-actions">
                    <?php
                    comment_reply_link(array_merge($args, array(
                        'reply_text' => '<i class="nova-icon-reply"></i>' . __('回复', 'nova'),
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                    )));
                    ?>
                </div>
            </div>
        </div>
    <?php
}

/**
 * 评论列表结束标签
 */
function nova_comment_list_end()
{
    echo '</li>';
}

/**
 * 评论表单（替换默认）
 */
function nova_comment_form($defaults)
{
    $commenter = wp_get_current_commenter();
    $req = get_option('require_name_email');
    $aria_req = $req ? " aria-required='true'" : '';

    $fields = array(
        'author' => '<p class="nova-comment-field">'
            . '<input id="author" name="author" type="text" placeholder="' . esc_attr__('昵称', 'nova') . ($req ? ' *' : '') . '" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . $aria_req . '>'
            . '</p>',
        'email' => '<p class="nova-comment-field">'
            . '<input id="email" name="email" type="email" placeholder="' . esc_attr__('邮箱', 'nova') . ($req ? ' *' : '') . '" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . $aria_req . '>'
            . '</p>',
        'url' => '<p class="nova-comment-field">'
            . '<input id="url" name="url" type="url" placeholder="' . esc_attr__('网址', 'nova') . '" value="' . esc_attr($commenter['comment_author_url']) . '" size="30">'
            . '</p>',
    );

    $defaults['fields'] = $fields;
    $defaults['comment_field'] = '<p class="nova-comment-field-textarea"><textarea id="comment" name="comment" placeholder="' . esc_attr__('写下你的评论…', 'nova') . '" rows="4" aria-required="true"></textarea></p>';
    $defaults['comment_notes_before'] = '';
    $defaults['comment_notes_after'] = '';
    $defaults['label_submit'] = __('发表评论', 'nova');
    $defaults['submit_button'] = '<button name="%1$s" type="submit" id="%2$s" class="%3$s btn btn-primary">%4$s</button>';
    $defaults['submit_field'] = '<p class="form-submit">%1$s %2$s</p>';
    $defaults['title_reply'] = '';
    $defaults['title_reply_before'] = '<h3 class="nova-block-title"><span>';
    $defaults['title_reply_after'] = '</span></h3>';
    $defaults['class_form'] = 'nova-comment-form';
    $defaults['class_submit'] = 'submit';
    $defaults['cancel_reply_link'] = '<span class="nova-cancel-reply"><i class="nova-icon-close"></i>' . __('取消回复', 'nova') . '</span>';
    return $defaults;
}
add_filter('comment_form_defaults', 'nova_comment_form');

/**
 * 评论头部信息
 */
function nova_comments_head_html()
{
    $count = get_comments_number();
    $html = '<div class="nova-comments-head card">';
    $html .= '<h3 class="nova-block-title"><span><i class="nova-icon-comment"></i>' . esc_html__('评论', 'nova') . ($count ? ' <em class="muted-2-color">' . $count . '</em>' : '') . '</span></h3>';
    $html .= '</div>';
    return $html;
}
