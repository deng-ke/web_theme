<?php
/*
 * Project       : Nova主题
 * Description   : 消息中心（系统消息/私信/通知）
 */

if (!defined('ABSPATH')) exit;

class NovaMsg
{
    /**
     * 添加系统消息
     */
    public static function add_system($user_id, $content, $meta = array())
    {
        return self::add($user_id, 0, 'system', $content, $meta);
    }

    /**
     * 添加普通通知
     */
    public static function add_notice($user_id, $content, $meta = array())
    {
        return self::add($user_id, 0, 'notice', $content, $meta);
    }

    /**
     * 添加私信
     */
    public static function add_private($from_user, $to_user, $content)
    {
        if (!$from_user || !$to_user) return new WP_Error('nova_msg', '参数错误');
        if ($from_user == $to_user) return new WP_Error('nova_msg', '不能给自己发私信');
        return self::add($to_user, $from_user, 'private', $content);
    }

    /**
     * 通用添加
     */
    public static function add($user_id, $from_user, $type, $content, $meta = array())
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_message';
        $result = $wpdb->insert($table, array(
            'user_id'     => $user_id,
            'from_user'   => $from_user,
            'msg_type'    => $type,
            'msg_content' => $content,
            'msg_meta'    => maybe_serialize($meta),
            'create_time' => current_time('mysql'),
        ));
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * 标记已读
     */
    public static function set_read($msg_id, $user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_message';
        $wpdb->update($table, array('msg_read' => 1), array('id' => $msg_id, 'user_id' => $user_id));
    }

    /**
     * 全部已读
     */
    public static function set_all_read($user_id, $type = '')
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_message';
        $where = array('user_id' => $user_id, 'msg_read' => 0);
        if ($type) $where['msg_type'] = $type;
        $wpdb->update($table, array('msg_read' => 1), $where);
    }

    /**
     * 未读数量
     */
    public static function unread_count($user_id, $type = '')
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_message';
        $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d AND msg_read = 0", $user_id);
        if ($type) {
            $sql .= $wpdb->prepare(" AND msg_type = %s", $type);
        }
        return (int) $wpdb->get_var($sql);
    }

    /**
     * 获取消息列表
     */
    public static function get_list($user_id, $type = '', $paged = 1, $per_page = 20)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_message';
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id);
        if ($type) {
            $sql .= $wpdb->prepare(" AND msg_type = %s", $type);
        }
        $sql .= " ORDER BY id DESC LIMIT " . (($paged - 1) * $per_page) . ", $per_page";
        return $wpdb->get_results($sql);
    }

    /**
     * 删除消息
     */
    public static function delete($msg_id, $user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nova_message';
        $wpdb->delete($table, array('id' => $msg_id, 'user_id' => $user_id));
    }
}

/**
 * 消息类型名称
 */
function nova_msg_type_name($type)
{
    $names = array(
        'system'  => __('系统消息', 'nova'),
        'notice'  => __('通知', 'nova'),
        'private' => __('私信', 'nova'),
        'follow'  => __('关注', 'nova'),
        'comment' => __('评论', 'nova'),
        'like'    => __('点赞', 'nova'),
        'order'   => __('订单', 'nova'),
    );
    return isset($names[$type]) ? $names[$type] : $type;
}

/**
 * 消息中心页面内容
 */
function nova_msg_center_html($type = '')
{
    $user_id = get_current_user_id();
    $paged = nova_get_the_paged();
    $per_page = 20;
    $list = NovaMsg::get_list($user_id, $type, $paged, $per_page);

    $types = array(
        ''        => __('全部消息', 'nova'),
        'system'  => __('系统消息', 'nova'),
        'notice'  => __('通知', 'nova'),
        'private' => __('私信', 'nova'),
    );

    $html = '<div class="nova-msg-center">';
    //类型筛选
    $html .= '<div class="nova-msg-tabs card">';
    foreach ($types as $key => $name) {
        $unread = NovaMsg::unread_count($user_id, $key);
        $active = ($type === $key) ? ' active' : '';
        $html .= '<a class="' . $active . '" href="' . esc_url(nova_user_center_url('msg' . ($key ? '&msg_type=' . $key : ''))) . '">' . esc_html($name);
        if ($unread) $html .= '<span class="nova-msg-badge">' . $unread . '</span>';
        $html .= '</a>';
    }
    $html .= '<div class="nova-msg-all-read"><a href="javascript:;" class="nova-msg-read-all">' . esc_html__('全部已读', 'nova') . '</a></div>';
    $html .= '</div>';

    //消息列表
    if (empty($list)) {
        $html .= nova_empty_box(__('暂无消息', 'nova'), __('还没有收到任何消息', 'nova'));
    } else {
        $html .= '<div class="nova-msg-list">';
        foreach ($list as $msg) {
            $avatar = $msg->from_user ? nova_get_user_avatar($msg->from_user, 80) : NOVA_TEMPLATE_DIRECTORY_URI . '/img/msg-system.svg';
            $from_name = $msg->from_user ? nova_get_user_name($msg->from_user) : __('系统', 'nova');
            $html .= '<div class="nova-msg-item card' . (!$msg->msg_read ? ' unread' : '') . '" data-id="' . $msg->id . '">';
            $html .= '<div class="nova-msg-avatar"><img src="' . esc_url($avatar) . '" alt="avatar"></div>';
            $html .= '<div class="nova-msg-body">';
            $html .= '<div class="nova-msg-head">';
            $html .= '<span class="nova-msg-from">' . esc_html($from_name) . '</span>';
            $html .= '<span class="nova-msg-type">' . esc_html(nova_msg_type_name($msg->msg_type)) . '</span>';
            $html .= '<span class="nova-msg-time muted-2-color">' . esc_html(nova_time_ago($msg->create_time)) . '</span>';
            $html .= '</div>';
            $html .= '<div class="nova-msg-content">' . esc_html($msg->msg_content) . '</div>';
            $html .= '</div>';
            $html .= '<div class="nova-msg-actions">';
            if ('private' === $msg->msg_type && $msg->from_user) {
                $html .= '<button class="nova-msg-reply-btn" data-user="' . $msg->from_user . '">' . esc_html__('回复', 'nova') . '</button>';
            }
            $html .= '<button class="nova-msg-del-btn" data-id="' . $msg->id . '">' . esc_html__('删除', 'nova') . '</button>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * AJAX 删除消息
 */
function nova_ajax_delete_message()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    $msg_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    NovaMsg::delete($msg_id, $user_id);
    nova_ajax_success();
}
add_action('wp_ajax_nova_delete_message', 'nova_ajax_delete_message');

/**
 * 私信对话列表（用于私信弹窗）
 */
function nova_private_contacts_html()
{
    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nova_message';

    //获取最近联系人
    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT from_user, MAX(id) as max_id FROM $table WHERE user_id = %d AND msg_type = 'private' GROUP BY from_user ORDER BY max_id DESC LIMIT 10",
        $user_id
    ));
    if (empty($contacts)) return '';

    $html = '<div class="nova-private-contacts">';
    foreach ($contacts as $contact) {
        $html .= '<a class="nova-contact-item" href="javascript:;" data-user="' . $contact->from_user . '">';
        $html .= '<img src="' . esc_url(nova_get_user_avatar($contact->from_user, 60)) . '" alt="avatar">';
        $html .= '<span>' . esc_html(nova_get_user_name($contact->from_user)) . '</span>';
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 评论被回复时发送消息
 */
function nova_comment_reply_notify($comment_id)
{
    $comment = get_comment($comment_id);
    if (!$comment) return;
    //仅回复时通知
    if (!$comment->comment_parent) return;
    $parent = get_comment($comment->comment_parent);
    if (!$parent || !$parent->user_id) return;
    if ($parent->user_id == $comment->user_id) return;

    $post_title = get_the_title($comment->comment_post_ID);
    $meta = array('comment_id' => $comment_id, 'post_id' => $comment->comment_post_ID);
    NovaMsg::add($parent->user_id, $comment->user_id, 'comment', sprintf(__('你的评论收到新回复：%s', 'nova'), mb_substr($comment->comment_content, 0, 50)), $meta);
}
add_action('comment_post', 'nova_comment_reply_notify');

/**
 * 文章被点赞通知
 */
function nova_like_notify($post_id, $user_id)
{
    $author_id = (int) get_post_field('post_author', $post_id);
    if ($author_id && $author_id != $user_id) {
        NovaMsg::add($author_id, $user_id, 'like', sprintf(__('你的文章《%s》收到新点赞', 'nova'), get_the_title($post_id)));
    }
}
