<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime : 2026-04-11 17:05:39
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//为用户授予徽章
function zib_ajax_user_medal_manually_add()
{
    $medal_name = isset($_REQUEST['name']) ? strip_tags(trim($_REQUEST['name'])) : '';
    $user_id    = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

    if (!_pz('user_medal_s', true) || !zib_current_user_can('medal_manually_set')) {
        zib_send_json_error(__('暂无此权限', 'zib_language'));
    }

    $medal_args = zib_get_single_medal_args($medal_name);
    if (!$medal_args) {
        zib_send_json_error(__('没有此徽章或参数错误', 'zib_language'));
    }

    //添加徽章
    zib_add_user_medal($user_id, $medal_name, '');

    zib_send_json_success(array('msg' => sprintf(__('已授予徽章【%s】', 'zib_language'), $medal_name), 'hide_modal' => true));
}
add_action('wp_ajax_user_medal_manually_add', 'zib_ajax_user_medal_manually_add');

//收回用户徽章
function zib_ajax_user_medal_manually_remove()
{
    $medal_name = isset($_REQUEST['name']) ? strip_tags(trim($_REQUEST['name'])) : '';
    $user_id    = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

    if (!_pz('user_medal_s', true) || !zib_current_user_can('medal_manually_set')) {
        zib_send_json_error(__('暂无此权限', 'zib_language'));
    }

    //收回徽章
    zib_remove_user_medal($user_id, $medal_name);

    zib_send_json_success(array('msg' => sprintf(__('已收回徽章【%s】', 'zib_language'), $medal_name), 'hide_modal' => true));
}
add_action('wp_ajax_user_medal_manually_remove', 'zib_ajax_user_medal_manually_remove');

//为用户授予徽章的模态框
function zib_ajax_user_medal_manually_add_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $userdata = get_userdata($id);
    if (empty($userdata->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
    }

    if (!_pz('user_medal_s', true) || !zib_current_user_can('medal_manually_set')) {
        zib_ajax_notice_modal('danger', __('您没有此权限', 'zib_language'));
    }

    $html = zib_get_user_medal_manually_add_modal($id);

    echo $html;
    exit;
}
add_action('wp_ajax_user_medal_manually_add_modal', 'zib_ajax_user_medal_manually_add_modal');

//保存用户佩戴徽章
function zib_ajax_user_medal_wear()
{
    $name = isset($_REQUEST['name']) ? strip_tags(trim($_REQUEST['name'])) : '';

    if (!$name) {
        zib_send_json_error(__('参数传入错误', 'zib_language'));
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(__('登录失效，请刷新页面后重试', 'zib_language'));
    }
    $user_medal = zib_get_user_medal_details($user_id);

    if (!isset($user_medal[$name])) {
        zib_send_json_error(__('暂未获得此徽章', 'zib_language'));
    }

    update_user_meta($user_id, 'wear_medal', $name);

    zib_send_json_success(array('msg' => sprintf(__('已佩戴徽章【%s】', 'zib_language'), $name), 'hide_modal' => true));
}
add_action('wp_ajax_user_medal_wear', 'zib_ajax_user_medal_wear');

//用户徽章信息的模态框
function zib_ajax_single_medal_info_modal()
{
    $name    = isset($_REQUEST['name']) ? strip_tags(trim($_REQUEST['name'])) : '';
    $user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

    if (!$name) {
        zib_ajax_notice_modal('danger', __('参数传入错误', 'zib_language'));
    }

    $html = zib_get_single_medal_info_modal($name, $user_id);

    echo $html;
    exit;
}
add_action('wp_ajax_single_medal_info_modal', 'zib_ajax_single_medal_info_modal');
add_action('wp_ajax_nopriv_single_medal_info_modal', 'zib_ajax_single_medal_info_modal');

//用户徽章信息的模态框
function zib_ajax_user_medal_info_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $userdata = get_userdata($id);
    if (empty($userdata->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
    }

    $html = zib_get_user_medal_info_modal($id);

    echo $html;
    exit;
}
add_action('wp_ajax_user_medal_info_modal', 'zib_ajax_user_medal_info_modal');
add_action('wp_ajax_nopriv_user_medal_info_modal', 'zib_ajax_user_medal_info_modal');

//认证信息的模态框
function zib_ajax_user_auth_info_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $userdata = get_userdata($id);
    if (empty($userdata->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
    }

    $html = zib_get_user_auth_info_modal($id);

    echo $html;
    exit;
}
add_action('wp_ajax_user_auth_info_modal', 'zib_ajax_user_auth_info_modal');
add_action('wp_ajax_nopriv_user_auth_info_modal', 'zib_ajax_user_auth_info_modal');

//申请认证模态框
function zib_ajax_user_auth_apply_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    if (zib_is_user_auth()) {
        zib_ajax_notice_modal('warning', __('您已是认证用户', 'zib_language'));
    }

    $html = zib_get_user_auth_apply_modal($id);

    echo $html;
    exit;
}
add_action('wp_ajax_user_auth_apply_modal', 'zib_ajax_user_auth_apply_modal');

//提交认证申请
function zib_ajax_user_auth_apply()
{

    $action  = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(__('登录失效，请刷新页面后重试', 'zib_language'));
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //判断已经有申请
    if (zib_get_user_auth_apply_ing($user_id)) {
        zib_send_json_error(__('您的申请已提交，请耐心等待', 'zib_language'));
    }

    $name = isset($_REQUEST['name']) && is_string($_REQUEST['name']) ? sanitize_text_field(wp_unslash($_REQUEST['name'])) : '';
    $desc = isset($_REQUEST['desc']) && is_string($_REQUEST['desc']) ? sanitize_textarea_field(wp_unslash($_REQUEST['desc'])) : '';
    $msg  = isset($_REQUEST['msg']) && is_string($_REQUEST['msg']) ? sanitize_textarea_field(wp_unslash($_REQUEST['msg'])) : '';

    if (!$name) {
        zib_send_json_error(__('请输入申请认证的身份名称', 'zib_language'));
    }

    if (zib_new_strlen($name) < 4) {
        zib_send_json_error(__('身份名称过短，不能低于4个字符', 'zib_language'));
    }

    if (zib_new_strlen($name) > 16) {
        zib_send_json_error(__('身份名称过长，不能超过16个字符', 'zib_language'));
    }

    $img = array();

    //图片处理
    if (!empty($_FILES['file'])) {
        //开始上传图像
        $img_ids = zib_php_upload('file', 0, false);
        if (!empty($img_ids['error'])) {
            zib_send_json_error($img_ids['msg']);
        }

        if (!is_array($img_ids)) {
            $img_ids = array($img_ids);
        }

        foreach ($img_ids as $imgid) {
            $image_urls = wp_get_attachment_image_src($imgid, 'full');
            if (isset($image_urls[0])) {
                $img[] = $image_urls[0];
            }
        }
    }

    $user_display_name = get_userdata($user_id)->display_name;

    $process_url = zib_get_user_auth_apply_process_url($user_id);

    $msg_con = '';
    $msg_con .= sprintf(__('用户：%s，正在申请身份认证', 'zib_language'), zib_get_user_name_link($user_id)) . '<br>';
    $msg_con .= sprintf(__('身份名称：%s', 'zib_language'), esc_html($name)) . '<br>';
    $msg_con .= $desc ? sprintf(__('身份简介：%s', 'zib_language'), esc_html($desc)) . '<br>' : '';
    $msg_con .= $msg ? sprintf(__('申请说明：%s', 'zib_language'), esc_html($msg)) . '<br>' : '';

    $msg_con .= sprintf(__('申请时间：%s', 'zib_language'), current_time('Y-m-d H:i:s')) . '<br>';
    $msg_con .= '<br>';
    $msg_con .= __('您可以点击下方按钮快速处理此申请', 'zib_language') . '<br>';
    $msg_con .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($process_url) . '">' . __('立即处理', 'zib_language') . '</a>' . '<br>';

    $msg_args = array(
        'send_user'    => $user_id,
        'receive_user' => 'admin',
        'type'         => 'auth_apply',
        'title'        => __('有新的身份认证申请待处理-用户：', 'zib_language') . $user_display_name,
        'content'      => $msg_con,
        'meta'         => array(
            'name' => $name,
            'desc' => $desc,
            'msg'  => $msg,
            'img'  => $img,
            'time' => current_time('Y-m-d H:i:s'),
        ),
    );

    //创建消息
    $add_msg = ZibMsg::add($msg_args);
    if (!$add_msg) {
        zib_send_json_error(__('提交失败，请稍候再试', 'zib_language'));
    }

    //用户申请身份认证 向管理员发送邮件
    if (_pz('email_auth_apply_to_admin', true)) {
        $title     = $msg_args['title'];
        $message   = $msg_args['content'];
        /**发送邮件 */
        zib_mail_to_admin($title, $message);
    }

    //发送微信模板消息
    $wechat_template_data = array(
        'name' => $name,
        'time' => current_time('Y-m-d H:i:s'),
        'user' => $user_display_name,
    );
    zib_send_wechat_template_msg_to_admin('auth_apply_admin', $wechat_template_data, $process_url);

    zib_send_json_success(array('msg' => __('申请提交成功，请耐心等待', 'zib_language'), 'hide_modal' => true));
}
add_action('wp_ajax_user_auth_apply', 'zib_ajax_user_auth_apply');

/***************禁封功能 */
function zib_ajax_set_user_ban_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $userdata = get_userdata($id);
    if (empty($userdata->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
    }

    //权限判断
    if (!zib_current_user_can('set_user_ban')) {
        zib_ajax_notice_modal('warning', __('抱歉，您暂时没有此权限', 'zib_language'));
    }

    if ('user_ban_log_modal' === $action) {
        echo zib_get_ban_log_modal($id);
    } else {
        echo zib_get_set_user_ban_modal($id);
    }
    exit;
}
add_action('wp_ajax_set_user_ban_modal', 'zib_ajax_set_user_ban_modal');
add_action('wp_ajax_user_ban_log_modal', 'zib_ajax_set_user_ban_modal');

//禁封模态框
function zib_ajax_user_ban_info_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    if ((!$id || get_current_user_id() != $id) && (!zib_current_user_can('set_user_ban'))) {
        zib_ajax_notice_modal('warning', __('抱歉，您暂时没有此权限', 'zib_language'));
    }

    if ('user_ban_info_modal' === $action) {
        echo zib_get_ban_info_modal($id);
    } else {
        echo zib_get_user_ban_appeal_modal($id);
    }
    exit;
}
add_action('wp_ajax_user_ban_info_modal', 'zib_ajax_user_ban_info_modal');
add_action('wp_ajax_user_ban_appeal_modal', 'zib_ajax_user_ban_info_modal');

//保存禁封数据
function zib_ajax_set_user_ban()
{

    if (!isset($_REQUEST['ban']) || empty($_REQUEST['user_id'])) {
        zib_send_json_error(__('用户不存在或参数传入错误', 'zib_language'));
    }

    $ban     = $_REQUEST['ban'];
    $user_id = $_REQUEST['user_id'];
    $desc    = isset($_REQUEST['desc']) && is_string($_REQUEST['desc']) ? trim(sanitize_textarea_field(wp_unslash($_REQUEST['desc']))) : '';

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //执行权限检查
    if (!zib_current_user_can('set_user_ban')) {
        zib_send_json_error(__('抱歉，您暂时没有此权限', 'zib_language'));
    }

    //解封
    if (0 == $ban) {
        zib_updata_user_ban($user_id, 0, array('desc' => $desc));
        zib_send_json_success(array('msg' => __('已解封此用户', 'zib_language'), 'hide_modal' => true));
    }

    //原因
    $reason   = isset($_REQUEST['reason']) && is_string($_REQUEST['reason']) ? sanitize_text_field(wp_unslash($_REQUEST['reason'])) : '';
    $time_day = isset($_REQUEST['time_day']) ? (int) $_REQUEST['time_day'] : 0;
    if (!$reason || 'other' === $reason) {
        $reason = isset($_REQUEST['reason_other']) && is_string($_REQUEST['reason_other']) ? sanitize_textarea_field(wp_unslash($_REQUEST['reason_other'])) : '';
    }
    $reason = trim($reason);
    if (!$reason) {
        zib_send_json_error(__('请输入禁封原因', 'zib_language'));
    }

    $current_time = current_time('Y-m-d H:i:s');
    if ($time_day) {
        $time_day = date('Y-m-d H:i:s', strtotime('+' . $time_day . ' day', strtotime($current_time)));
    }

    $no_appeal_desc = isset($_REQUEST['no_appeal_desc']) && is_string($_REQUEST['no_appeal_desc']) ? trim(sanitize_textarea_field(wp_unslash($_REQUEST['no_appeal_desc']))) : '';
    $no_appeal      = !empty($_REQUEST['no_appeal']);
    if ($no_appeal && !$no_appeal_desc) {
        zib_send_json_error(__('请输入禁止用户申诉的说明或原因', 'zib_language'));
    }

    //整理数据
    $data = array(
        'type'           => $ban,
        'time'           => $current_time,
        'banned_time'    => $time_day,
        'reason'         => $reason, //原因
        'desc'           => $desc, //说明
        'no_appeal'      => $no_appeal, //禁止申诉
        'no_appeal_desc' => $no_appeal_desc, //禁止申诉说明
    );

    zib_updata_user_ban($user_id, $ban, $data);
    $msg_args = array(
        0 => __('已解封此用户', 'zib_language'),
        1 => __('已将此用户拉入小黑屋', 'zib_language'),
        2 => __('已禁封此用户', 'zib_language'),
    );

    $send_msg = $msg_args[$ban];
    //删除用户发布内容
    $post_i = 0;
    if (!empty($_REQUEST['delete_content'])) {
        //删除文章和帖子
        $args = array(
            'post_type'      => array('post', 'forum_post'),
            'author'         => $user_id,
            'post_status'    => 'publish',
            'order'          => 'DESC',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );
        $new_query = new WP_Query($args);

        if (!empty($new_query->posts[0])) {
            foreach ($new_query->posts as $post_id) {
                //执行删除
                wp_trash_post($post_id);
                $post_i++;
            }
        }

        //删除评论
        $omment_i     = 0;
        $comment_args = array(
            'user_id' => $user_id,
            'status'  => 'approve',
            'fields'  => 'ids',
        );
        $get_comments = get_comments($comment_args);

        if (!empty($get_comments[0])) {
            foreach ($get_comments as $comment_id) {
                //执行删除
                wp_trash_comment($comment_id);
                $omment_i++;
            }
        }

        if ($post_i) {
            $send_msg .= sprintf(__('，删除%d个发布内容', 'zib_language'), $post_i);
        }
        if ($omment_i) {
            $send_msg .= sprintf(__('，删除%d条评论', 'zib_language'), $omment_i);
        }
    }

    zib_send_json_success(array('msg' => $send_msg, 'reload' => true));
}
add_action('wp_ajax_set_user_ban', 'zib_ajax_set_user_ban');

//提交禁封申诉
function zib_ajax_user_ban_appeal()
{
    $user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //判断已经有申请
    if (zib_get_user_ban_appeal_ing($user_id)) {
        zib_send_json_error(__('您的申诉正在处理中，请耐心等待', 'zib_language'), 'warning');
    }

    //判断用户存在
    $user_data = get_userdata($user_id);
    if (!isset($user_data->display_name)) {
        zib_send_json_error(__('用户不存在或参数错误', 'zib_language'));
    }

    $data         = array();
    $data_html    = '';
    $request_data = (isset($_REQUEST['data']) && is_array($_REQUEST['data'])) ? $_REQUEST['data'] : array();
    if (!$request_data) {
        zib_send_json_error(__('请填写申诉信息', 'zib_language'));
    }
    foreach ($request_data as $k => $v) {
        if (!is_string($k) || !is_string($v)) {
            zib_send_json_error(__('请填写申诉信息', 'zib_language'));
        }
        $k = sanitize_text_field(wp_unslash($k));
        $v = trim(sanitize_textarea_field(wp_unslash($v)));
        if (!$k || !$v) {
            zib_send_json_error(sprintf(__('请输入%s', 'zib_language'), $k ? $k : __('申诉信息', 'zib_language')));
        }
        $data[$k] = $v;
        $data_html .= esc_html($k) . '：' . esc_html($v) . '<br>';
    }

    $process_url       = zib_get_user_ban_appeal_process_url($user_id);
    $user_display_name = $user_data->display_name;
    $is_ban            = zib_get_user_ban_info($user_id);
    $in_type           = $is_ban['type'];

    $ban_status = 2 == $in_type ? __('禁封中(禁止登录)', 'zib_language') : __('小黑屋禁封中', 'zib_language');
    $end_time   = $is_ban['banned_time'] ? $is_ban['banned_time'] : __('永久', 'zib_language');

    $msg_con = sprintf(__('用户：%s，正在进行账号禁封申诉', 'zib_language'), zib_get_user_name_link($user_id)) . '<br>';
    $msg_con .= '<div style="border-radius:8px;margin:10px 0;color: #959595;font-size: 13px;">';
    $msg_con .= sprintf(__('禁封状态：%s', 'zib_language'), $ban_status) . '<br>';
    $msg_con .= sprintf(__('开始时间：%s', 'zib_language'), $is_ban['time']) . '<br>';
    $msg_con .= sprintf(__('结束时间：%s', 'zib_language'), $end_time) . '<br>';
    $msg_con .= sprintf(__('禁封原因：%s', 'zib_language'), esc_html($is_ban['reason'])) . '<br>';
    $msg_con .= ($is_ban['desc'] ? sprintf(__('说明：%s', 'zib_language'), esc_html($is_ban['desc'])) . '<br>' : '');
    $msg_con .= '</div>';
    $msg_con .= $data_html;
    $msg_con .= sprintf(__('提交时间：%s', 'zib_language'), current_time('Y-m-d H:i:s')) . '<br><br>';
    $msg_con .= __('您可以点击下方按钮快速处理此申请', 'zib_language') . '<br>';
    $msg_con .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($process_url) . '">' . __('立即处理', 'zib_language') . '</a>';
    $title    = sprintf(__('有新的账号禁封申诉待处理-用户：%s', 'zib_language'), $user_display_name);
    $msg_args = array(
        'send_user'    => $user_id,
        'receive_user' => 'admin',
        'type'         => 'ban_appeal',
        'title'        => $title,
        'content'      => $msg_con,
        'meta'         => array(
            'data' => $data,
            'time' => current_time('Y-m-d H:i:s'),
        ),
    );

    //创建消息
    $add_msg = ZibMsg::add($msg_args);
    if (!$add_msg) {
        zib_send_json_error(__('提交失败，请稍候再试', 'zib_language'));
    }

    //给管理员发送邮件
    if (_pz('email_ban_appeal_to_admin', true)) {
        //发送邮件
        zib_mail_to_admin($title, $msg_con);
    }

    zib_send_json_success(array('msg' => __('申诉已提交，请耐心等待', 'zib_language'), 'hide_modal' => true));
}
add_action('wp_ajax_user_ban_appeal', 'zib_ajax_user_ban_appeal');
add_action('wp_ajax_nopriv_user_ban_appeal', 'zib_ajax_user_ban_appeal');

//举报模态框
function zib_ajax_report_modal()
{
    $user_id = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $url     = isset($_REQUEST['url']) && is_string($_REQUEST['url']) ? esc_url_raw(wp_unslash($_REQUEST['url'])) : '';

    //执行安全验证检查
    zib_ajax_verify_nonce('report_modal');

    //执行用户检查
    $userdata = get_userdata($user_id);
    if (empty($userdata->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
    }

    //用户状态检查
    if (zib_user_is_ban($user_id)) {
        zib_ajax_notice_modal('success', __('用户已禁封，感谢您的反馈', 'zib_language'));
    }

    //权限判断
    if (!zib_current_user_can('user_report')) {
        zib_ajax_notice_modal('warning', __('抱歉，您暂时没有举报权限', 'zib_language'));
    }

    echo zib_get_report_modal($user_id, $url);
    exit;
}
add_action('wp_ajax_report_modal', 'zib_ajax_report_modal');
add_action('wp_ajax_nopriv_report_modal', 'zib_ajax_report_modal');

//提交举报
function zib_ajax_report_user()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //执行用户检查
    $user_id   = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $user_data = get_userdata($user_id);
    if (empty($user_data->display_name)) {
        zib_send_json_error(__('用户不存在或参数传入错误', 'zib_language'));
    }

    //用户状态检查
    if (zib_user_is_ban($user_id)) {
        zib_send_json_error(__('用户已禁封，感谢您的反馈', 'zib_language'));
    }

    //判断已经有举报
    if (zib_get_user_report_ing($user_id)) {
        zib_send_json_error(__('您的举报已提交，感谢您的反馈', 'zib_language'), 'warning');
    }

    //权限判断
    if (!zib_current_user_can('user_report')) {
        zib_send_json_error(__('抱歉，您暂时没有举报权限', 'zib_language'));
    }

    // 预设原因同样来自请求，必须和「其它原因」一样去掉标签
    $reason = isset($_REQUEST['reason']) && is_string($_REQUEST['reason']) ? sanitize_text_field(wp_unslash($_REQUEST['reason'])) : '';
    if (!$reason || 'other' === $reason) {
        $reason = isset($_REQUEST['reason_other']) && is_string($_REQUEST['reason_other']) ? sanitize_textarea_field(wp_unslash($_REQUEST['reason_other'])) : '';
    }
    $reason = trim($reason);
    if (!$reason) {
        zib_send_json_error(__('请选择或输入举报原因', 'zib_language'));
    }

    //图片处理
    $img_ids = array();
    if (!empty($_FILES['file'])) {
        //开始上传图像
        $img_ids = zib_php_upload('file', 0, false);
        if (!empty($img_ids['error'])) {
            zib_send_json_error($img_ids['msg']);
        }

        if (!is_array($img_ids)) {
            $img_ids = array($img_ids);
        }
    }

    //开始发送消息
    $desc              = isset($_REQUEST['desc']) && is_string($_REQUEST['desc']) ? trim(sanitize_textarea_field(wp_unslash($_REQUEST['desc']))) : '';
    $url               = isset($_REQUEST['url']) && is_string($_REQUEST['url']) ? esc_url_raw(trim(wp_unslash($_REQUEST['url']))) : '';
    $current_user_id   = get_current_user_id();
    $process_url       = zib_get_user_report_process_url();
    $user_display_name = $user_data->display_name;
    $title             = sprintf(__('收到新的不良信息举报-被举报用户：%s', 'zib_language'), $user_display_name);
    $current_time      = current_time('Y-m-d H:i:s');

    $msg_con = __('收到新的不良信息举报', 'zib_language') . '<br>';
    $msg_con .= sprintf(__('被举报用户：%s', 'zib_language'), zib_get_user_name_link($user_id)) . '<br>';
    $msg_con .= sprintf(__('举报原因：%s', 'zib_language'), esc_html($reason)) . '<br>';
    $msg_con .= ($url ? sprintf(__('违规链接：%s', 'zib_language'), '<a target="_blank" href="' . esc_url($url) . '">' . esc_html($url) . '</a>') . '<br>' : '');
    $msg_con .= ($desc ? sprintf(__('举报详情：%s', 'zib_language'), esc_html($desc)) . '<br>' : '');
    $msg_con .= sprintf(__('提交时间：%s', 'zib_language'), $current_time) . '<br><br>';
    $msg_con .= __('您可以点击下方按钮快速处理此申请', 'zib_language') . '<br>';
    $msg_con .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($process_url) . '">' . __('立即处理', 'zib_language') . '</a>';
    $msg_args = array(
        'send_user'    => ($current_user_id ? $current_user_id : 'admin'),
        'receive_user' => 'admin',
        'type'         => 'user_report',
        'title'        => $title,
        'content'      => $msg_con,
        'meta'         => array(
            'report_user'    => 'report_user_id_' . $user_id,
            'report_user_id' => $user_id,
            'desc'           => $desc,
            'reason'         => $reason,
            'url'            => $url,
            'img'            => $img_ids,
            'time'           => $current_time,
        ),
    );

    //创建消息
    $add_msg = ZibMsg::add($msg_args);
    if (!$add_msg) {
        zib_send_json_error(__('提交失败，请稍候再试', 'zib_language'));
    }

    //给管理员发送邮件
    if (_pz('email_report_to_admin', true)) {
        //发送邮件
        zib_mail_to_admin($title, $msg_con);
    }

    //发送微信模板消息
    $wechat_template_data = array(
        'reason' => $reason,
        'time' => current_time('Y-m-d H:i:s'),
        'desc'   => $desc,
        'user'   => $user_display_name,
    );
    zib_send_wechat_template_msg_to_admin('report_user_admin', $wechat_template_data, $process_url);

    zib_send_json_success(array('msg' => __('您的举报已提交，我们会尽快处理，感谢您的反馈', 'zib_language'), 'hide_modal' => true));
}
add_action('wp_ajax_report_user', 'zib_ajax_report_user');
add_action('wp_ajax_nopriv_report_user', 'zib_ajax_report_user');

//签到模态框
function zib_ajax_checkin_details_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', __('请先登录', 'zib_language'));
    }
    if (!_pz('checkin_s')) {
        zib_ajax_notice_modal('danger', __('此功能已关闭', 'zib_language'));
    }

    $html = zib_get_user_checkin_details_modal($user_id);

    echo $html;
    exit;

}
add_action('wp_ajax_checkin_details_modal', 'zib_ajax_checkin_details_modal');

//发起签到
function zib_ajax_user_checkin()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(__('请先登录', 'zib_language'));
    }
    if (!_pz('checkin_s')) {
        zib_send_json_error(__('此功能已关闭', 'zib_language'));
    }
    //小黑屋禁封判断
    if (_pz('user_ban_s', true) && zib_user_is_ban($user_id)) {
        zib_send_json_error(__('您已被拉入小黑屋，暂时无法签到', 'zib_language'));
    }

    //函数节流
    zib_ajax_debounce('user_checkin', $user_id);

    //今日已经签到
    if (zib_user_is_checkined($user_id)) {
        zib_send_json_error(__('今日已签到', 'zib_language'), 'info');
    }

    $reward    = zib_get_user_checkin_should_reward($user_id); //先获取本次加分值
    $checkined = zib_user_checkin($user_id);

    $msg = $reward['continuous_day'] > 1 ? sprintf(__('连续%d天签到成功！', 'zib_language'), $reward['continuous_day']) : __('签到成功！', 'zib_language');
    $msg .= $checkined['points'] ? ' ' . __('积分', 'zib_language') . '+' . $checkined['points'] : '';
    $msg .= $checkined['integral'] ? ' ' . __('经验值', 'zib_language') . '+' . $checkined['integral'] : '';

    zib_send_json_success(array('msg' => $msg, 'data' => $checkined, 'continuous_day' => $reward['continuous_day'], 'details_link' => zib_get_user_checkin_details_link()));

}
add_action('wp_ajax_user_checkin', 'zib_ajax_user_checkin');

//为用户授予徽章的模态框
function zib_ajax_user_edit_quick_often()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', __('登录失效，请刷新页面后重新登录', 'zib_language'));
    }

    $quick_often = zib_get_user_quick_often($user_id);
    $max         = 20;
    //投票选项
    $option_html = '';
    if ($quick_often) {
        foreach ($quick_often as $v) {
            $option_html .= '<div class="cloneable-item edit-quick-often-item form-right-icon mb6">
            <textarea placeholder="' . esc_attr__('请输入快捷回复', 'zib_language') . '" autoheight="true" maxheight="90" class="form-control grin" name="val[]" rows="1">' . $v . '</textarea>
            <a href="javascript:;" class="abs-right muted-color cloneable-remove">' . zib_get_svg('close') . '</a></div>';
        }
    }

    if (!$option_html) {
        $option_html = '<div class="cloneable-item edit-quick-often-item form-right-icon mb6">
        <textarea placeholder="' . esc_attr__('请输入快捷回复', 'zib_language') . '" autoheight="true" maxheight="90" class="form-control grin" name="val[]" rows="2"></textarea>
        <a href="javascript:;" class="abs-right muted-color cloneable-remove">' . zib_get_svg('close') . '</a></div>';
    }

    $option_html = '<div class="cloneable vote-options" data-max="' . $max . '" data-min="0">' . $option_html . '</div><botton type="button" class="cloneable-add but block c-blue">' . zib_get_svg('add') . __('添加', 'zib_language') . '</botton>';

    $html = '<div class="mb20 touch"><button class="close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme">' . zib_get_svg('quick-reply') . '</span>' . __('我的快捷回复', 'zib_language') . '</b></div>

    <form>
        <div class="mb10 mini-scrollbar scroll-y max-vh5" style="padding: 5px; ">' . $option_html . '</div>
        <input type="hidden" name="action" value="save_user_quick_often">
        <div class="modal-buts but-average"> <button type="button" class="but c-blue padding-lg wp-ajax-submit user-quick-often-save-btn">' . __('确认提交', 'zib_language') . '</button></div>
    </form>';

    echo $html;
    exit;
}
add_action('wp_ajax_user_edit_quick_often', 'zib_ajax_user_edit_quick_often');

function zib_ajax_save_user_quick_often()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(__('登录失效，请刷新页面后重新登录', 'zib_language'));
    }

    $quick_often     = isset($_REQUEST['val']) ? $_REQUEST['val'] : array();
    $new_quick_often = array();
    $item_html       = '';
    if ($quick_often) {
        foreach ($quick_often as $item) {
            //循环处理数据，过滤空数据，过滤特殊字符，避免xss攻击
            $item = trim(strip_tags($item));
            if ($item) {
                $new_quick_often[] = $item;
                $item_html .= '<div class="quick-reply-item">' . esc_attr($item) . '</div>';
            }
        }
    }

    $quick_often = array_filter($quick_often);

    if (count($quick_often) > 20) {
        zib_send_json_error(__('最多只能添加20个快捷回复', 'zib_language'));
    }

    if (!$item_html) {
        $item_html = '<div class="text-center muted-2-color" style="margin: 40px 0 30px;">' . __('暂无快捷回复', 'zib_language') . '</div>';
    }

    zib_save_user_quick_often($user_id, $quick_often);

    zib_send_json_success(array('msg' => __('保存成功', 'zib_language'), 'hide_modal' => true, 'item' => $item_html));
}
add_action('wp_ajax_save_user_quick_often', 'zib_ajax_save_user_quick_often');
