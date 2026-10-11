<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 17:11:44
 * @LastEditTime : 2026-08-25 12:15:41
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**用户评论通过审核之后发消息给评论用户 */
add_action('comment_unapproved_to_approved', 'zib_newmsg_comment_approved', 99);
function zib_newmsg_comment_approved($comment)
{

    if (!$comment->user_id) {
        return;
    }

    $is_notify = zib_get_comment_meta($comment->comment_ID, 'is_notify', true);
    if ($is_notify) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();
    $_link      = get_comment_link($comment->comment_ID);
    $post       = get_post($comment->comment_post_ID);
    $post_title = zib_str_cut($post->post_title, 0, 16, '...');

    $post_link = get_permalink($comment->comment_post_ID);

    $title = sprintf(__('您发表的评论已通过审核:[%s]', 'zib_language'), $post_title);

    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $message         = sprintf(__('您好！%s', 'zib_language'), get_comment_author($comment->comment_ID)) . '<br>';
    $message .= __('您在[', 'zib_language') . sprintf('<a href="%1$s">%2$s</a>', esc_url($post_link), esc_html($post->post_title)) . __(']中的评论，已经通过审核', 'zib_language') . '<br>';
    $message .= __('评论内容：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= sprintf(__('评论时间：%s', 'zib_language'), $comment->comment_date) . '<br>';
    $message .= '<br>';

    $message .= __('您可以点击下方按钮查看评论', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('查看评论', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $comment->user_id,
        'type'         => 'comment',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    if (zib_msg_is_allow_receive($comment->user_id, 'comment')) {
        ZibMsg::add($msg_arge);
    }

    wp_new_comment_notify_postauthor($comment->comment_ID);
    zib_update_comment_meta($comment->comment_ID, 'is_notify', true);
    //恢复到原来的语言
    zib_restore_previous_locale();
}

/**用户评论通过审核之后，如果是回复给其它人，则给其他人发送消息以及邮件 */
add_action('comment_post', 'zib_newmsg_comment_approved_toparent', 99);
add_action('comment_unapproved_to_approved', 'zib_newmsg_comment_approved_toparent', 99);
function zib_newmsg_comment_approved_toparent($comment)
{
    $comment = get_comment($comment);

    if (!$comment->comment_parent || '1' != $comment->comment_approved) {
        return;
    }

    $_link          = get_comment_link($comment);
    $parent_id      = $comment->comment_parent;
    $parent_comment = get_comment($parent_id);
    $post           = get_post($parent_comment->comment_post_ID);

    if ($parent_comment->user_id) {

        //自己回复自己不通知
        if ($parent_comment->user_id == $comment->user_id) {
            return;
        }

        //自己是作者不通知
        if ($parent_comment->user_id == $post->post_author) {
            return;
        }
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $post_title = zib_str_cut($post->post_title, 0, 16, '...');
    $post_link  = get_permalink($parent_comment->comment_post_ID);

    $title = sprintf(__('您发表的评论有新的回复:[%s]', 'zib_language'), $post_title);

    $comment_content        = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $parent_comment_content = zib_comment_filters(get_comment_text($parent_comment->comment_ID), '', false);
    $comment_author         = get_comment_author($comment->comment_ID);
    $parent_comment_author  = get_comment_author($parent_comment->comment_ID);

    $message = sprintf(__('您好！%s', 'zib_language'), $parent_comment_author) . '<br>';
    $message .= __('您在[', 'zib_language') . sprintf('<a href="%1$s">%2$s</a>', esc_url($post_link), esc_html($post->post_title)) . __(']中的发表的评论，有新的回复内容', 'zib_language') . '<br>';
    $message .= __('您的评论：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $parent_comment_content . '</div>';
    $message .= sprintf(__('评论时间：%s', 'zib_language'), $parent_comment->comment_date) . '<br>';
    $message .= '<br>';

    $message .= __('回复内容：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= sprintf(__('回复时间：%s', 'zib_language'), $comment->comment_date) . '<br>';
    $message .= sprintf(__('回复人：%s', 'zib_language'), $comment_author) . '<br>';
    $message .= '<br>';

    $message .= __('您可以点击下方按钮查看回复内容', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('查看回复', 'zib_language') . '</a>' . '<br>';

    if ($parent_comment->user_id) {
        //创建站内新消息
        if (zib_msg_is_allow_receive($parent_comment->user_id, 'comment')) {
            $msg_arge = array(
                'send_user'    => 'admin',
                'receive_user' => $parent_comment->user_id,
                'type'         => 'comment',
                'title'        => $title,
                'content'      => $message,
                'meta'         => '',
                'other'        => '',
            );
            ZibMsg::add($msg_arge);
        }

        /**发送微信模板消息 */
        $c = zib_comment_filters(get_comment_text($comment->comment_ID), 'noimg', false);
        $c = zib_str_cut(trim(strip_tags($c)), 0, 17, '...');

        $wechat_template_data = array(
            'name'    => $comment_author,
            'time'    => $comment->comment_date,
            'content' => $c,
        );
        zib_wechat_template_send($parent_comment->user_id, 'comment_to_parent', $wechat_template_data, $_link);
    }

    //创建新的邮件通知
    if (_pz('email_comment_toparent', true)) {

        $email = get_comment_author_email($parent_comment);

        /**判断邮箱状态 */
        if (is_email($email) && !stristr($email, '@no')) {
            $title = sprintf(__('您发表的评论已有新的回复:[%s]', 'zib_language'), $post_title);
            /**发送邮件 */
            @wp_mail($email, $title, $message);
        }
    }

    //恢复到原来的语言
    zib_restore_previous_locale();
}

//用户投稿后向管理员发送邮件
add_action('new_posts_pending', 'zib_email_newpost_contribution_to_admin', 99);
function zib_email_newpost_contribution_to_admin($post)
{

    $user_id = $post->post_author;
    $udata   = get_userdata($user_id);
    /**判断是否是管理员或者作者 */
    if (in_array('administrator', $udata->roles) || in_array('roles', $udata->roles)) {
        return false;
    }

    /**判断通知状态 */
    if (get_post_meta($post->ID, 'contribution_msg_to_admin', true)) {
        return false;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $blog_name = get_bloginfo('name');
    $_link     = admin_url('/edit.php?post_status=pending&post_type=post');
    $title     = sprintf(__('有新的文章投稿待审核：%s', 'zib_language'), zib_str_cut(trim(strip_tags($post->post_title)), 0, 20));

    $message = __('有新的文章投稿待审核', 'zib_language') . '<br>';
    $message .= sprintf(__('文章标题：%s', 'zib_language'), trim(strip_tags($post->post_title))) . '<br>';
    $message .= __('内容摘要：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(125, 125, 125, 0.06); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= sprintf(__('投稿时间：%s', 'zib_language'), get_the_time('Y-m-d H:i:s', $post)) . '<br>';

    $send_user = 'admin';
    if (_pz('post_article_user', 1) != $user_id) {
        $send_user = $user_id;
        $message .= __('投稿用户：', 'zib_language') . sprintf('<a target="_blank" href="%1$s">%2$s</a>', esc_url(zib_get_user_home_url($user_id)), esc_html($udata->display_name)) . '<br>';
    }

    $message .= '<br>';
    $message .= __('您可以点击下方按钮以审核投稿文章', 'zib_language') . '<br>';
    $message .= '<a class="but c-blue" target="_blank" style="margin-top: 20px" href="' . esc_url($_link) . '">' . __('查看待审核文章', 'zib_language') . '</a>' . '<br>';

    /**发送邮件 */
    update_post_meta($post->ID, 'contribution_msg_to_admin', true);

    $msg_arge = array(
        'send_user'    => $send_user,
        'receive_user' => 'admin',
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
    );
    //创建新消息
    ZibMsg::add($msg_arge);

    if (_pz('email_newpost_contribution_to_admin', true)) {
        zib_mail_to_admin($title, $message);
    }

    //恢复到原来的语言
    zib_restore_previous_locale();
}

/**当投稿的文章从草稿状态变更到已发布时 */
add_action('pending_to_publish', 'zib_newmsg_pending_to_publish', 99);
function zib_newmsg_pending_to_publish($post)
{
    $user_id = $post->post_author;
    //用户是否接收
    if (!$user_id && !zib_msg_is_allow_receive($user_id, 'posts')) {
        return;
    }

    /**判断是否登录后投稿 */
    if (_pz('post_article_user', 1) == $user_id) {
        return;
    }

    $udata = get_userdata($user_id);
    /**判断是否是管理员或者作者 */
    if (in_array('administrator', $udata->roles) || in_array('roles', $udata->roles)) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $_link      = get_permalink($post->ID);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $title = sprintf(__('您发布的内容已通过审核：[%s]', 'zib_language'), $post_title);

    $message = sprintf(__('您好！%s', 'zib_language'), $udata->display_name) . '<br>';
    $message .= sprintf(__('您发布的内容[%s]，已经通过审核', 'zib_language'), $post->post_title) . '<br>';
    $message .= __('内容摘要：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= sprintf(__('提交时间：%s', 'zib_language'), $post->post_date) . '<br>';
    $message .= sprintf(__('审核时间：%s', 'zib_language'), $post->post_modified) . '<br>';
    $message .= '<br>';

    $message .= __('您可以点击下方按钮查看此内容', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('立即查看', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);

    //恢复到原来的语言
    zib_restore_previous_locale();
}

//帖子被删除后通知作者
add_action('bbs_posts_delete', 'zib_newmsg_trashed_post', 99);
function zib_newmsg_trashed_post($post)
{
    //用户是否接收
    if (empty($post->post_author) || empty($_REQUEST['msg_s'])) {
        return;
    }

    /**判断是否登录后投稿 */
    if ($post->post_type == 'post' && _pz('post_article_user', 1) == $post->post_author) {
        return;
    }

    $user_id = $post->post_author;
    $udata   = get_userdata($user_id);
    /**作者删除自己的的不通知 */
    if ($user_id == get_current_user_id()) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $msg        = !empty($_REQUEST['msg']) ? trim(strip_tags($_REQUEST['msg'])) : '';
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');
    $title      = sprintf(__('您发布的内容已被删除：[%s]', 'zib_language'), $post_title);

    $message = sprintf(__('您好！%s', 'zib_language'), $udata->display_name) . '<br>';
    $message .= sprintf(__('您发布的内容[%s]，已被删除', 'zib_language'), $post->post_title) . '<br>';
    $message .= __('内容摘要：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . trim(zib_str_cut(trim(strip_tags($post->post_content)), 0, 100, '...')) . '</div>';
    $message .= sprintf(__('发布时间：%s', 'zib_language'), $post->post_date) . '<br>';
    $message .= $msg ? sprintf(__('删除说明：%s', 'zib_language'), $msg) . '<br>' : '';
    $message .= '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);

    //恢复到原来的语言
    zib_restore_previous_locale();
}

//帖子或版块驳回后通知作者
add_action('publish_to_pending', 'zib_newmsg_publish_to_pending', 99);
function zib_newmsg_publish_to_pending($post)
{
    //用户是否接收
    if (empty($post->post_author) || empty($_REQUEST['msg_s'])) {
        return;
    }
    $msg     = !empty($_REQUEST['msg']) ? trim(strip_tags($_REQUEST['msg'])) : '';
    $user_id = $post->post_author;

    /**判断是否登录后投稿 */
    if ($post->post_type == 'post' && _pz('post_article_user', 1) == $user_id) {
        return;
    }

    $udata = get_userdata($user_id);
    /**作者自己驳回的不通知 */
    if ($user_id == get_current_user_id()) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $_link      = get_permalink($post->ID);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $title = sprintf(__('您发布的内容被审核驳回：[%s]', 'zib_language'), $post_title);

    $message = sprintf(__('您好！%s', 'zib_language'), $udata->display_name) . '<br>';
    $message .= sprintf(__('您发布的内容[%s]，已被驳回，当前为待审核状态', 'zib_language'), $post->post_title) . '<br>';
    $message .= __('内容摘要：', 'zib_language') . '<br>';

    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= sprintf(__('提交时间：%s', 'zib_language'), $post->post_date) . '<br>';
    $message .= sprintf(__('驳回时间：%s', 'zib_language'), $post->post_modified) . '<br>';
    $message .= $msg ? sprintf(__('驳回说明：%s', 'zib_language'), $msg) . '<br>' : '';
    $message .= '<br>';

    $message .= __('您可以点击下方按钮查看此内容', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('立即查看', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);

    //恢复到原来的语言
    zib_restore_previous_locale();
}

/**新的链接需要管理员审核 */
add_action('zib_ajax_frontend_links_submit_success', 'zib_newmsg_links_submit', 99);
function zib_newmsg_links_submit($data)
{
    if (!_pz('message_s', true) || $data['link_visible'] === 'Y') {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $linkdata = array(
        'link_name'        => esc_attr($data['link_name']),
        'link_url'         => esc_url($data['link_url']),
        'link_description' => !empty($data['link_description']) ? esc_attr($data['link_description']) : __('无', 'zib_language'),
        'link_image'       => !empty($data['link_image']) ? '<img src="' . esc_attr($data['link_image']) . '" style=" width: 80px; height: auto; ">' : __('空', 'zib_language'),
    );
    $_link = admin_url('link-manager.php?orderby=visible&order=asc');

    $title = sprintf(__('新的链接待审核：%s', 'zib_language'), $linkdata['link_name']);

    $message = __('网站有新的链接提交：', 'zib_language') . '<br>';
    $message .= sprintf(__('链接名称：%s', 'zib_language'), $linkdata['link_name']) . '<br>';
    $message .= sprintf(__('链接地址：%s', 'zib_language'), $linkdata['link_url']) . '<br>';
    $message .= sprintf(__('链接简介：%s', 'zib_language'), $linkdata['link_description']) . '<br>';
    $message .= sprintf(__('链接Logo：%s', 'zib_language'), $linkdata['link_image']) . '<br>';
    $message .= '<br>';

    $message .= __('您可以点击下方按钮快速管理链接', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('管理链接', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => 'admin',
        'type'         => 'system',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);

    //恢复到原来的语言
    zib_restore_previous_locale();
}

//文章有新的评论后给文章作者发通知的内容过滤
function zib_comment_notification_text_filter($notify_message, $comment_id)
{
    //手动切换到前台语言
    zib_switch_to_frontend_locale();
    $comment         = get_comment($comment_id);
    $post_id         = $comment->comment_post_ID;
    $post            = get_post($post_id);
    $_link           = get_comment_link($comment);
    $comment_author  = get_comment_author($comment->comment_ID);
    $post_link       = get_permalink($comment->comment_post_ID);
    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);

    $notify_message = __('您好！', 'zib_language') . '<br>';
    $notify_message .= __('您发布的[', 'zib_language') . sprintf('<a href="%1$s">%2$s</a>', esc_url($post_link), esc_html($post->post_title)) . __(']有新的评论', 'zib_language') . '<br>';
    $notify_message .= __('评论内容：', 'zib_language') . '<br>';
    $notify_message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $notify_message .= sprintf(__('评论用户：%s', 'zib_language'), $comment_author) . '<br>';
    $notify_message .= sprintf(__('评论时间：%s', 'zib_language'), $comment->comment_date) . '<br>';
    $notify_message .= '<br>';
    $notify_message .= __('您可以点击下方按钮查看此评论', 'zib_language') . '<br>';
    $notify_message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('查看评论', 'zib_language') . '</a>' . '<br>';

    //恢复到原来的语言
    zib_restore_previous_locale();
    return $notify_message;
}
add_filter('comment_notification_text', 'zib_comment_notification_text_filter', 10, 2);

/** 文章有新评论时候给作者发消息 */
add_filter('notify_post_author', 'zib_newmsg_new_comment', 10, 2);
function zib_newmsg_new_comment($maybe_notify, $comment_id)
{
    $comment = get_comment($comment_id);
    $post_id = $comment->comment_post_ID;
    $post    = get_post($post_id);

    //自己回复自己不通知
    if ($post->post_author == $comment->user_id) {
        return;
    }

    //手动切换到前台语言
    zib_switch_to_frontend_locale();
    $notify_message = zib_comment_notification_text_filter('', $comment);
    $post_title     = zib_str_cut($post->post_title, 0, 20, '...');
    $title          = sprintf(__('有新的评论:[%s]', 'zib_language'), $post_title);

    if (_pz('message_s', true) && zib_msg_is_allow_receive($post->post_author, 'comment')) {
        $msg_arge = array(
            'send_user'    => 'admin',
            'receive_user' => $post->post_author,
            'type'         => 'comment',
            'title'        => $title,
            'content'      => $notify_message,
            'meta'         => '',
            'other'        => '',
        );
        //创建新消息
        ZibMsg::add($msg_arge);
    }

    //发送微信模板消息
    $c                    = zib_comment_filters(get_comment_text($comment->comment_ID), 'noimg', false);
    $c                    = zib_str_cut(trim(strip_tags($c)), 0, 17, '...');
    $wechat_template_data = array(
        'name'    => get_comment_author($comment->comment_ID),
        'time'    => $comment->comment_date,
        'content' => $c,
        'post'    => $post->post_title,
    );
    zib_wechat_template_send($post->post_author, 'comment_to_postauthor', $wechat_template_data, get_comment_link($comment));

    //恢复到原来的语言
    zib_restore_previous_locale();
    return $maybe_notify;
}

/** 有评论待审核给管理员发消息 */
add_filter('comment_moderation_text', 'zib_newmsg_moderation_notify', 10, 2);
function zib_newmsg_moderation_notify($notify_message, $comment_id)
{
    //手动切换到前台语言
    zib_switch_to_frontend_locale();

    $comment    = get_comment($comment_id);
    $post_id    = $comment->comment_post_ID;
    $post       = get_post($post_id);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $post_link       = get_permalink($comment->comment_post_ID);
    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $comment_author  = get_comment_author($comment->comment_ID);
    $_link           = get_comment_link($comment);

    $notify_message = __('您好！', 'zib_language') . '<br>';
    $notify_message .= __('在[', 'zib_language') . sprintf('<a href="%1$s">%2$s</a>', esc_url($post_link), esc_html($post->post_title)) . __(']中有新的评论待审核', 'zib_language') . '<br>';
    $notify_message .= __('评论内容：', 'zib_language') . '<br>';
    $notify_message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $notify_message .= sprintf(__('评论用户：%s', 'zib_language'), $comment_author) . '<br>';
    $notify_message .= sprintf(__('评论时间：%s', 'zib_language'), $comment->comment_date) . '<br>';
    $notify_message .= '<br>';
    $notify_message .= '<a target="_blank" href="' . esc_url($_link) . '">' . __('您可以点此查看此评论', 'zib_language') . '</a><br>';
    $notify_message .= __('也可以点击以下链接进入后台处理待审核的评论', 'zib_language') . '<br>';
    $notify_message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url(admin_url('edit-comments.php?comment_status=moderated#wpbody-content')) . '">' . __('后台审核', 'zib_language') . '</a>' . '<br>';

    $title = sprintf(__('有新的评论待审核:[%s]', 'zib_language'), $post_title);
    if (_pz('message_s', true)) {
        $msg_arge = array(
            'send_user'    => 'admin',
            'receive_user' => 'admin',
            'type'         => 'comment',
            'title'        => $title,
            'content'      => $notify_message,
            'meta'         => '',
            'other'        => '',
        );
        //创建新消息
        ZibMsg::add($msg_arge);
    }
    //恢复到原来的语言
    zib_restore_previous_locale();
    return $notify_message;
}

/**用户文章获得点赞发消息 */
add_action('like-posts', 'zib_newmsg_post_like', 99, 3);
function zib_newmsg_post_like($post_id, $count, $user_id = 0)
{
    $post = get_post($post_id);
    //判断是否是自己操作
    if ($user_id == $post->post_author) {
        return;
    }

    if (!zib_msg_is_allow_receive($post->post_author, 'like')) {
        return;
    }

    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $title = sprintf(__('您发布的内容获得点赞：[%s]，共计%d次点赞', 'zib_language'), $post_title, $count);

    $message = '';
    $message .= __('您发布的内容获得新的点赞！', 'zib_language') . '<br>';
    $message .= sprintf(__('文章：[%s]', 'zib_language'), $post->post_title) . '<br>';
    $message .= sprintf(__('共计点赞：%d次', 'zib_language'), $count) . '<br>';
    if ($user_id) {
        $data = get_userdata($user_id);
        $message .= __('点赞用户：', 'zib_language') . sprintf('<a target="_blank" href="%1$s">%2$s</a>', esc_url(zib_get_user_home_url($user_id)), esc_html($data->display_name)) . '<br>';
    }

    $_link = get_permalink($post_id);
    $message .= __('您可以点击下方按钮查看文章', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('查看文章', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $post->post_author,
        'type'         => 'like',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

/**用户评论获得点赞发消息 */
add_action('like-comment', 'zib_newmsg_comment_like', 99, 3);
function zib_newmsg_comment_like($comment_id, $count, $user_id = 0)
{

    $comment = get_comment($comment_id);

    //判断是否是自己操作
    if ($user_id == $comment->user_id) {
        return;
    }

    if (!zib_msg_is_allow_receive($comment->user_id, 'like')) {
        return;
    }

    $post_id    = $comment->comment_post_ID;
    $post       = get_post($post_id);
    $post_link  = get_permalink($post_id);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');
    $_link      = get_comment_link($comment->comment_ID);
    $title      = sprintf(__('您在文章[%s]中的评论获得点赞，共计%d次点赞', 'zib_language'), $post_title, $count);

    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);

    $message = sprintf(__('您好！%s', 'zib_language'), get_comment_author($comment->comment_ID)) . '<br>';
    $message .= __('您在[', 'zib_language') . sprintf('<a href="%1$s">%2$s</a>', esc_url($post_link), esc_html($post->post_title)) . __(']中的评论，获得新的点赞', 'zib_language') . '<br>';
    $message .= __('评论内容：', 'zib_language') . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= sprintf(__('评论时间：%s', 'zib_language'), $comment->comment_date) . '<br>';

    $message .= sprintf(__('共计点赞：%d次', 'zib_language'), $count) . '<br>';
    if ($user_id) {
        $data = get_userdata($user_id);
        $message .= __('点赞用户：', 'zib_language') . sprintf('<a target="_blank" href="%1$s">%2$s</a>', esc_url(zib_get_user_home_url($user_id)), esc_html($data->display_name)) . '<br>';
    }

    $message .= __('您可以点击下方按钮查看此评论', 'zib_language') . '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('查看评论', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $comment->user_id,
        'type'         => 'like',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

/**文章被收藏发消息 */
add_action('favorite-posts', 'zib_newmsg_post_favorite', 99, 3);
function zib_newmsg_post_favorite($post_id, $count, $user_id = 0)
{

    $post = get_post($post_id);
    //判断是否是自己操作
    if ($user_id == $post->post_author) {
        return;
    }

    if (!zib_msg_is_allow_receive($post->post_author, 'favorite')) {
        return;
    }

    $post_title = zib_str_cut($post->post_title, 0, 20, '...');
    $udata      = get_userdata($user_id);
    $user_name  = zib_str_cut($udata->display_name, 0, 8, '...');
    $user_url   = esc_url(zib_get_user_home_url($user_id));

    $title = sprintf(__('用户[%1$s]收藏了您发布的内容[%2$s]', 'zib_language'), $user_name, $post_title);

    $message = '';
    $message .= __('有用户收藏了您发布的内容', 'zib_language') . '<br>';
    $message .= sprintf(__('标题：[%s]', 'zib_language'), $post->post_title) . '<br>';
    $message .= sprintf(__('发布时间：%s', 'zib_language'), $post->post_date) . '<br>';

    $message .= __('收藏用户：', 'zib_language') . sprintf('<a target="_blank" href="%1$s">%2$s</a>', esc_url($user_url), esc_html($udata->display_name)) . '<br>';

    $_link = get_permalink($post_id);
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">' . __('查看内容', 'zib_language') . '</a>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-red ml10" href="' . $user_url . '">' . __('查看用户', 'zib_language') . '</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $post->post_author,
        'type'         => 'favorite',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

/**有新的粉丝、用户被关注 */
add_action('follow-user', 'zib_newmsg_followed', 99, 4);
function zib_newmsg_followed($follow_user_id, $followed_user_id, $follow_count = 0, $followed_count = 0)
{
    if (!zib_msg_is_allow_receive($followed_user_id, 'followed')) {
        return;
    }

    //判断是否是自己操作
    if ($follow_user_id == $followed_user_id) {
        return;
    }

    $udata     = get_userdata($follow_user_id);
    $user_name = zib_str_cut($udata->display_name, 0, 8, '...');
    $user_url  = esc_url(zib_get_user_home_url($follow_user_id));

    $title = sprintf(__('您有新的粉丝：[%s]', 'zib_language'), $user_name);

    $message = '';
    $message .= __('有用户关注您', 'zib_language') . '<br>';
    $message .= __('用户：', 'zib_language') . sprintf('<a target="_blank" href="%1$s">%2$s</a>', esc_url($user_url), esc_html($udata->display_name)) . '<br>';
    $message .= sprintf(__('粉丝总数：%d个', 'zib_language'), $followed_count) . '<br>';

    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-red mr10" href="' . $user_url . '">' . __('查看用户', 'zib_language') . '</a>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . zib_get_user_home_url($followed_user_id, array('tab' => 'follow')) . '">' . __('查看所有粉丝', 'zib_language') . '</a>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $followed_user_id,
        'type'         => 'followed',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

//用户绑定手机号通知
add_action('zib_user_bind_phone', 'zib_newmsg_bind_phone', 99, 3);
function zib_newmsg_bind_phone($user_id, $new_phone, $old_phone)
{
    $udata = get_userdata($user_id);

    $blog_name = get_bloginfo('name');
    $new_phone = zib_get_hide_phone($new_phone);
    $old_phone = $old_phone ? zib_get_hide_phone($old_phone) : false;

    $title       = $old_phone ? __('绑定手机号修改成功', 'zib_language') : __('手机号绑定成功', 'zib_language');
    $action_text = $old_phone ? sprintf(__('由%s修改为%s', 'zib_language'), $old_phone, $new_phone) . '<br><br>' : sprintf(__('手机号：%s', 'zib_language'), $new_phone) . '<br><br>';

    $message = sprintf(__('您好，%s!', 'zib_language'), $udata->display_name) . '<br>';
    $message .= $old_phone ? __('您的账号绑定的手机号已修改', 'zib_language') : __('您的账号已成功绑定手机号', 'zib_language');
    $message .= '<br>';
    $message .= $action_text;
    $message .= __('如非您本人操作，请及时与客服联系', 'zib_language');

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'system',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    ZibMsg::add($msg_arge);
    if (_pz('email_update_bind_phone', true)) {
        if (is_email($udata->user_email) && !stristr($udata->user_email, '@no')) {
            @wp_mail($udata->user_email, $title, $message);
        }
    }

    //发送微信模板消息
    $wechat_template_data = array(
        'name' => $udata->user_login . '（' . ($old_phone ? __('手机号修改成功', 'zib_language') : __('手机号绑定成功', 'zib_language')) . '）',
        'num'  => $new_phone,
        'time' => current_time('Y-m-d H:i:s'),
    );
    zib_wechat_template_send($user_id, 'bind_phone', $wechat_template_data, zib_get_user_center_url('account'));
}

//用户绑定有邮箱通知
add_action('zib_user_bind_email', 'zib_newmsg_bind_email', 99, 3);
function zib_newmsg_bind_email($user_id, $new, $old)
{
    $udata = get_userdata($user_id);
    $new   = zib_get_hide_email($new);
    $old   = $old ? zib_get_hide_email($old) : false;

    $title       = $old ? __('邮箱绑定成功', 'zib_language') : __('绑定邮箱修改成功', 'zib_language');
    $action_text = $old ? sprintf(__('由%s修改为%s', 'zib_language'), $old, $new) . '<br><br>' : sprintf(__('绑定邮箱：%s', 'zib_language'), $new);

    $message = sprintf(__('您好，%s!', 'zib_language'), $udata->display_name) . '<br>';
    $message .= $old ? __('您的账号已完成邮箱绑定', 'zib_language') : __('绑定邮箱修改成功', 'zib_language');
    $message .= '<br>';
    $message .= $action_text;
    $message .= __('如非您本人操作，请及时与客服联系', 'zib_language');

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'system',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    ZibMsg::add($msg_arge);

    //发送微信模板消息
    $wechat_template_data = array(
        'name' => $udata->user_login . '（' . ($old ? __('邮箱修改成功', 'zib_language') : __('邮箱绑定成功', 'zib_language')) . '）',
        'time' => current_time('Y-m-d H:i:s'),
        'num'  => $new,
    );
    zib_wechat_template_send($user_id, 'bind_email', $wechat_template_data, zib_get_user_center_url('account'));
}
