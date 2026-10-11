<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:37
 * @LastEditTime : 2026-05-05 13:47:39
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

$functions = array(
    'class/message-class',
    'class/private-class',
    'functions/ajax',
    'functions/user',
    'functions/new',
    'functions/wechat-template-msg',
);

foreach ($functions as $function) {
    $path = 'inc/functions/message/' . $function . '.php';
    require_once get_theme_file_path($path);
}
//后台
if (is_admin() && _pz('message_s')) {
    require_once get_theme_file_path('inc/functions/message/functions/admin.php');
}

/**
 * @description: 初始化消息数据库
 * @param {*}
 * @return {*}
 */
add_action('admin_head', 'zibmsg_create_db');
function zibmsg_create_db()
{
    ZibMsg::create_db();
}

/**
 * @description: 获取前台用户的所有收件人数组
 * @param {*}
 * @return {*}
 */
function zibmsg_get_receive_user_args($user_id = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user_args = array($user_id, 'all');
    //会员消息
    $vip = zib_get_user_vip_level($user_id);
    if ($vip) {
        $user_args[] = 'vip';
        $user_args[] = 'vip' . $vip;
    }
    //管理员消息
    if (is_super_admin()) {
        $user_args[] = 'admin';
    }

    return $user_args;
}

function zibmsg_nav_radius_button($user_id = 0, $calss = 'ml10', $show_drop = true)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return;
    }

    if (!_pz('message_s')) {
        return;
    }

    $badge = zibmsg_get_user_msg_count($user_id, '', 'top');
    $icon  = '<span class="toggle-radius msg-icon"><i class="fa fa-bell-o" aria-hidden="true"></i></span>';
    $icon .= $badge;

    $icon_a = '<a rel="nofollow" href="' . esc_url(zibmsg_get_conter_url('news')) . '" class="msg-news-icon ' . $calss . '">' . $icon . '</a>';
    if ($badge && $show_drop && !wp_is_mobile()) {
        $ajaxpager = array(
            'class'  => '',
            'loader' => ' ', // 加载动画
            'query'  => array(
                'action' => 'newmsg_drop',
            ),
        );
        $remote_box = zib_get_remote_box($ajaxpager);

        $html = '<div class="dropdown pull-right hover-show msg-news-dropdown msg-center-nav">' . $icon_a . $remote_box . '</div>';
        return $html;
    } else {
        return $icon_a;
    }
}

function zibmsg_get_user_new_lists($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $img_uri     = ZIB_TEMPLATE_DIRECTORY_URI . '/img/';
    $list        = '';
    $list_args[] = [
        'href'  => zibmsg_get_conter_url(),
        'badge' => zibmsg_get_user_msg_count($user_id),
        'name'  => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-news.svg', 'fit-cover') . ' alt="' . esc_attr__('未读消息', 'zib_language') . '">' . __('全部未读', 'zib_language'),
    ];
    $list_args[] = [
        'href'  => zibmsg_get_conter_url('posts'),
        'badge' => zibmsg_get_user_msg_count($user_id, 'posts'),
        'name'  => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-comment.svg', 'fit-cover img-icon') . ' alt="' . esc_attr__('文章评论', 'zib_language') . '">' . __('文章评论', 'zib_language'),
    ];
    $list_args[] = [
        'href'  => zibmsg_get_conter_url('like'),
        'badge' => zibmsg_get_user_msg_count($user_id, 'like'),
        'name'  => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-followed.svg', 'fit-cover img-icon') . ' alt="' . esc_attr__('点赞喜欢', 'zib_language') . '">' . __('点赞喜欢', 'zib_language'),
    ];
    $list_args[] = [
        'href'  => zibmsg_get_conter_url('system'),
        'badge' => zibmsg_get_user_msg_count($user_id, 'system'),
        'name'  => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-system.svg', 'fit-cover img-icon') . ' alt="' . esc_attr__('系统通知', 'zib_language') . '">' . __('系统通知', 'zib_language'),
    ];
    if (_pz('private_s', true)) {
        $list_args[] = [
            'href'  => zibmsg_get_conter_url('private'),
            'badge' => zibmsg_get_user_msg_count($user_id, 'private'),
            'name'  => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-private.svg', 'fit-cover img-icon') . ' alt="' . esc_attr__('私信消息', 'zib_language') . '">' . __('私信消息', 'zib_language'),
        ];
    }

    foreach ($list_args as $li) {
        if ($li['badge']) {
            $list .= '<li><a rel="nofollow" href="' . esc_url($li['href']) . '">' . $li['name'] . $li['badge'] . '</a></li>';
        }
    }

    return $list;
}

//在顶部导航显示新消息详细
if (in_array('nav_menu', (array) _pz('message_icon_show', array('nav_menu', 'm_nav_user')))) {
    add_filter('zib_nav_radius_button', 'zibmsg_nav_radius_button_filter', 15, 2);
}

function zibmsg_nav_radius_button_filter($but, $user_id)
{
    return $but . zibmsg_nav_radius_button($user_id, 'ml10');
}

/**
 * @description: 获取用户通知消息的图标
 * @param {*}
 * @return {*}
 */
function zibmsg_get_user_icon($user_id = '', $class = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return;
    }

    if (!_pz('message_s')) {
        return;
    }

    $badge = zibmsg_get_user_msg_count($user_id, '', 'top');

    $icon = '<span class="toggle-radius msg-icon"><i class="fa fa-bell-o" aria-hidden="true"></i></span>';
    $icon .= $badge;

    $href   = zibmsg_get_conter_url('');
    $icon_a = '<a href="' . $href . '" class="msg-news-icon ' . $class . '">' . $icon . '</a>';
    return $icon_a;
}

/**
 * @description: 获取用户消息数量的徽章
 * @param {*}
 * @return {*}
 */
function zibmsg_get_user_msg_count($user_id, $cat = '', $class = '', $show_count = false)
{
    if (!_pz('message_s', true)) {
        return;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return;
    }

    $cat   = $cat ? $cat : 'all';
    $count = 0;

    static $counts = array();
    if (isset($counts[$user_id][$cat])) {
        $count = $counts[$user_id][$cat];
    } else {
        //准备查询参数
        $get_count = array(
            'receive_user'   => zibmsg_get_receive_user_args($user_id),
            'status'         => 0,
            'no_readed_user' => $user_id,
        );

        $msg_cat = zib_get_msg_cat();
        if ($cat && 'all' != $cat) {
            $get_count['type'] = $msg_cat[$cat];
        }

        $count = ZibMsg::get_count($get_count);
        if (!_pz('private_s', true) && 'all' == $cat) {
            //如果没有开启私信，获取全部未读消息则要减去私信数量
            $get_count['type'] = 'private';
            $count             = $count - ZibMsg::get_count($get_count);
        }
    }

    $counts[$user_id][$cat] = $count;

    if ($show_count) {
        return $count;
    }

    $class = $class ? ' class="' . $class . '"' : '';
    $badge = $count ? '<badge' . $class . ' msg-cat="' . $cat . '">' . $count . '</badge>' : '';

    return $badge;
}

/**
 * @description: 获取用户未读消息数量的集合
 * @param {*} $user_id
 * @return {*}
 */
function zibmsg_get_user_new_msg_counts($user_id = 0)
{
    $counts = array(
        'all' => zibmsg_get_user_msg_count($user_id, 'all', '', true),
    );

    $msg_cats = zib_get_msg_cat();
    foreach ($msg_cats as $k => $v) {
        $counts[$k] = zibmsg_get_user_msg_count($user_id, $k, '', true);
    }

    return $counts;
}

/**
 * @description: 获取用户未读消息数量的集合的显示html，用于前端数据获取
 * @param {*} $user_id
 * @return {*}
 */
function zibmsg_get_user_new_msg_counts_html_data($user_id = 0)
{
    $counts = zibmsg_get_user_new_msg_counts($user_id);
    return '<div class="hide" msg-new-count-obj="' . esc_attr(json_encode($counts)) . '"></div>';
}

/**
 * @description: 消息发件人名称格式化
 * @param {*}
 * @return array
 */
function zib_get_msg_send_user_text($user_id)
{
    if ('admin' === $user_id) {
        return __('系统消息', 'zib_language');
    }

    $udata = get_userdata($user_id);

    if ($udata) {
        return '<a href="' . zib_get_user_home_url($user_id) . '">' . $udata->display_name . '</a>';
    }

    return $user_id;
}

/**
 * @description: 消息收件人名称格式化
 * @param {*}
 * @return array
 */
function zib_get_msg_receive_user_text($receive_user)
{
    $sys_receive = array(
        'all'  => __('所有用户', 'zib_language'),
        'vip'  => __('所有VIP会员', 'zib_language'),
        'vip1' => _pz('pay_user_vip_1_name', '一级会员'),
        'vip2' => _pz('pay_user_vip_2_name', '二级会员'),
    );

    if (!empty($sys_receive[$receive_user])) {
        return $sys_receive[$receive_user];
    }

    if (is_numeric($receive_user)) {
        $udata = get_userdata($receive_user);
        if ($udata) {
            return '<a href="' . zib_get_user_home_url($receive_user) . '">' . $udata->display_name . '</a>';
        }
    }
    return $receive_user;
}

/**
 * @description: 消息附加信息
 * @param array $msg 消息的全部数组
 * @return array
 */
function zib_get_msg_dec($msg)
{
    $msg = (array) $msg;

    $msg_type    = zib_get_msg_type_text($msg['type']);
    $create_time = $msg['create_time'];

    $html = '';
    $html .= '<span data-toggle="tooltip" title="' . $create_time . '" data-placement="bottom"><i class="fa fa-clock-o mr3" aria-hidden="true"></i>' . zib_get_time_ago($create_time) . '</span>';
    $html .= '<span data-toggle="tooltip" title="' . esc_attr__('消息类型', 'zib_language') . '" data-placement="bottom"><i class="fa fa-bell-o mr3 ml10" aria-hidden="true"></i>' . $msg_type . '</span>';

    return $html;
}

/**
 * @description: 获取通知消息的列表盒子
 * @param array $msg 消息的全部数组
 * @return {*}
 */
function zib_get_msg_box($msg, $class = '', $user_id = '', $cat = 'news')
{
    $msg   = (array) $msg;
    $title = $msg['title'];
    $img   = zibmsg_get_msg_img($msg);

    //判断是否为新消息
    if (!$msg['readed_user'] || !strstr($msg['readed_user'], '[' . $user_id . ']')) {
        $img .= '<badge class="top">NEW</badge>';
    }

    //准备返回按钮的参数
    $back_id = 'msg-tab-' . $cat;
    //准备url参数
    $ajax_query_arg = array(
        'action'  => 'user_msg_content',
        'id'      => $msg['id'],
        'back_id' => $back_id,
    );

    $ajax_url = esc_url(add_query_arg($ajax_query_arg, admin_url('admin-ajax.php')));
    $dec      = zib_get_msg_dec($msg);

    $html = '';
    $html .= '<ul class="list-inline relative msg-list">';
    $html .= '<li>';
    $html .= '<span class="msg-img">' . $img . '</span>';
    $html .= '</li>';
    $html .= '<li><dl>';
    $html .= '<dt class="">' . $title . '</dt>';
    $html .= '<dd class="mt6 em09 muted-2-color text-ellipsis">' . $dec . '</dd>';
    $html .= '</dl></li>';
    $html .= '</ul>';

    $html = '<div class="border-bottom box-body ' . $class . '"><a href="javascript:;" ajax-tab="#user_msg_content" data-ajax="' . $ajax_url . '" ajax-replace="true">' . $html . '</a></div>';
    return $html;
}

/**
 * @description: 获取通知消息的内容
 * @param array $msg 消息的全部数组
 * @return {*}
 */
function zib_get_msg_content($msg, $class = '')
{
    $msg = (array) $msg;

    $title = $msg['title'];

    $content = ZibMsg::get_content($msg);

    $send_user = zib_get_msg_send_user_text($msg['send_user']);
    $img       = zibmsg_get_msg_img($msg);
    $dec       = zib_get_msg_dec($msg);

    //准备返回按钮的参数
    $back_but = '';
    $back_id  = !empty($_REQUEST['back_id']) ? '#' . $_REQUEST['back_id'] : '#msg-tab-news';

    $back_but = '<a href="javascript:;" data-onclick="[data-target=\'' . $back_id . '\']" class="focus-color"><i class="fa fa-angle-left"></i> ' . __('返回列表', 'zib_language') . '</a>';

    $con = '';
    $con .= '<div class="box-body nopw-sm border-bottom">';
    $con .= '<dt class="em12">' . $title . '</dt>';
    $con .= '<dd class="mt10 muted-2-color flex ac"><span class="msg-img">' . $img . '</span>' . $send_user . '</dd>';

    $con .= '</div>';

    $con .= '<div class="box-body nopw-sm border-bottom">';
    $con .= $content;

    $con .= '</div>';
    $con .= '<div class="box-body nopw-sm">';
    $con .= $back_but;
    $con .= '<div class="muted-2-color pull-right">' . $dec . '</div>';
    $con .= '</div>';
    $html = '<div class="msg-content ' . $class . '">' . $con . '</div>';
    return $html;
}

/**
 * @description: 判断是否接收消息
 * @param int $user_id 用户ID
 * @param string $type 消息类型
 * @return {*}
 */
function zib_msg_is_allow_receive($user_id, $type = '')
{
    if (!_pz('message_s', true)) {
        return false;
    }

    if ($type && in_array($type, (array) _pz('message_close_msg_type'))) {
        return false;
    }

    if (_pz('message_user_set', true)) {
        $type_args      = zib_get_msg_cat();
        $message_shield = (array) zib_get_user_meta($user_id, 'message_shield', true); //["posts","like","system"]
        if ($message_shield) {
            foreach ($message_shield as $shield) {
                if (!empty($type_args[$shield]) && in_array($type, (array) $type_args[$shield])) {
                    return false;
                }
            }
        }
    }

    return true;
}

/**F
 * @description: 消息type分类
 * @param {*}
 * @return array
 */
function zib_get_msg_cat()
{
    $cat_type            = array();
    $cat_type['posts']   = array('posts', 'comment', 'favorite');
    $cat_type['like']    = array('like', 'followed', 'hot');
    $cat_type['system']  = array('order_modify_address', 'order_modify_address_reply', 'balance', 'points', 'ban_appeal', 'user_report', 'user_report_reply', 'user_ban', 'medal', 'system', 'promotion', 'vip', 'withdraw_reply', 'withdraw', 'moderator_apply', 'moderator_apply_reply', 'auth_apply_reply', 'auth_apply', 'pay');
    $cat_type['private'] = 'private';
    return apply_filters('message_cats', $cat_type);
}

/**
 * @description: 消息类型名称格式化
 * @param {*}
 * @return array
 */
function zib_get_msg_type_text($type)
{
    $type_args = array(
        'ban_appeal'                 => __('账号申诉', 'zib_language'),
        'user_report_reply'          => __('举报', 'zib_language'),
        'user_report'                => __('举报', 'zib_language'),
        'user_ban'                   => __('账号禁封', 'zib_language'),
        'medal'                      => __('徽章', 'zib_language'),
        'moderator_apply'            => __('申请', 'zib_language'),
        'moderator_apply_reply'      => __('审批', 'zib_language'),
        'favorite'                   => __('文章收藏', 'zib_language'),
        'posts'                      => __('文章', 'zib_language'),
        'comment'                    => __('评论', 'zib_language'),
        'like'                       => __('点赞', 'zib_language'),
        'followed'                   => __('关注', 'zib_language'),
        'hot'                        => __('系统', 'zib_language'),
        'system'                     => __('系统', 'zib_language'),
        'withdraw_reply'             => __('提现', 'zib_language'),
        'withdraw'                   => __('提现', 'zib_language'),
        'private'                    => __('私信', 'zib_language'),
        'auth_apply'                 => __('认证申请', 'zib_language'),
        'auth_apply_reply'           => __('认证申请', 'zib_language'),
        'order_modify_address'       => __('收货地址修改', 'zib_language'),
        'order_modify_address_reply' => __('收货地址修改', 'zib_language'),
        'pay'                        => __('订单', 'zib_language'),
        'balance'                    => __('余额变动', 'zib_language'),
        'points'                     => __('积分变动', 'zib_language'),
        'vip'                        => __('会员', 'zib_language'),
        'vip1'                       => __('会员', 'zib_language'),
        'vip2'                       => __('会员', 'zib_language'),
        'promotion'                  => __('活动', 'zib_language'),
    );

    return !empty($type_args[$type]) ? sprintf(__('%s消息', 'zib_language'), $type_args[$type]) : __('其它消息', 'zib_language');
}

/**
 * @description: 获取后台设置：自动清理的选项
 * @param {*}
 * @return {*}
 */
function zibmsg_get_clear_type_options()
{

    $type_args = array(
        'private'         => __('私信消息', 'zib_language'),
        'ban_appeal'      => __('账号申诉', 'zib_language'),
        'user_report'     => __('举报用户', 'zib_language'),
        'user_ban'        => __('账号禁封', 'zib_language'),
        'medal'           => __('用户徽章', 'zib_language'),
        'auth_apply'      => __('用户认证', 'zib_language'),
        'moderator_apply' => __('版主申请', 'zib_language'),
        'favorite'        => __('文章收藏', 'zib_language'),
        'posts'           => __('文章相关', 'zib_language'),
        'comment'         => __('评论消息', 'zib_language'),
        'like'            => __('文章点赞', 'zib_language'),
        'followed'        => __('关注用户', 'zib_language'),
        'hot'             => __('热门或精华', 'zib_language'),
        'withdraw'        => __('资金提现', 'zib_language'),
        'pay'             => __('订单消息', 'zib_language'),
        'balance'         => __('余额变动', 'zib_language'),
        'points'          => __('积分变动', 'zib_language'),
        'promotion'       => __('活动消息', 'zib_language'),
        'system'          => __('其他系统消息', 'zib_language'),
    );

    return $type_args;
}

/**
 * @description: 获取自动清理的类型
 * @param {*}
 * @return {*}
 */
function zibmsg_get_clear_types()
{

    if (!_pz('message_auto_clear_s')) {
        return false;
    }

    $types = _pz('message_auto_clear_types');
    if (!$types || !is_array($types)) {
        return false;
    }

    if (in_array('user_report', $types)) {
        $types[] = 'user_report_reply';
    }
    if (in_array('moderator_apply', $types)) {
        $types[] = 'moderator_apply_reply';
    }
    if (in_array('auth_apply', $types)) {
        $types[] = 'auth_apply_reply';
    }
    if (in_array('withdraw', $types)) {
        $types[] = 'withdraw_reply';
    }

    return $types;
}

/**
 * @description: 获取通知消息的图标
 * @param array $msg 消息的全部数组
 * @return {*}
 */
function zibmsg_get_msg_img($msg, $class = '')
{
    $msg  = (array) $msg;
    $meta = @maybe_unserialize($msg['meta']);
    $src  = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-sm.svg';

    $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="fit-cover lazyload ' . $class . '" src="' . $src . '" data-' : 'class="fit-cover ' . $class . '"';

    if (!empty($meta['customize_icon'])) {
        return '<img ' . $lazy_attr . 'src="' . $meta['customize_icon'] . '" alt="' . esc_attr__('消息图标', 'zib_language') . '">';
    }
    $msg_type  = $msg['type'];
    $send_user = $msg['send_user'];
    if (is_numeric($send_user)) {
        $udata = get_userdata($send_user);
        if ($udata) {
            return zib_get_data_avatar($udata->ID);
        }
    }

    $img_uri = ZIB_TEMPLATE_DIRECTORY_URI . '/img/';
    $img_src = $img_uri . 'msg-system.svg';

    $img_args = array(
        'auth_apply_reply' => $img_uri . 'msg-auth.svg',
        'auth_apply'       => $img_uri . 'msg-auth.svg',
        'withdraw_reply'   => $img_uri . 'msg-withdraw.svg',
        'withdraw'         => $img_uri . 'msg-withdraw.svg',
        'posts'            => $img_uri . 'msg-posts.svg',
        'favorite'         => $img_uri . 'msg-posts.svg',
        'comment'          => $img_uri . 'msg-comment.svg',
        'like'             => $img_uri . 'msg-like.svg',
        'followed'         => $img_uri . 'msg-followed.svg',
        'private'          => $img_uri . 'msg-private.svg',
        'pay'              => $img_uri . 'msg-pay.svg',
        'promotion'        => $img_uri . 'msg-promotion.svg',
        'vip'              => $img_uri . 'msg-vip.svg',
    );
    $img_src = !empty($img_args[$msg_type]) ? $img_args[$msg_type] : $img_src;
    $img     = '<img ' . $lazy_attr . 'src="' . $img_src . '" alt="' . esc_attr__('消息图标', 'zib_language') . '">';
    return $img;
}

/**
 * @description: 获取消息中心的链接
 * @param {*} $type
 * @return {*}
 */
function zibmsg_get_conter_url($type = '')
{
    if (get_option('permalink_structure')) {
        $rewrite_slug = trim(_pz('msg_center_rewrite_slug'));
        $rewrite_slug = $rewrite_slug ? $rewrite_slug : 'message';

        return home_url($rewrite_slug . '/' . $type);
    }

    return add_query_arg('msg_center', ($type ? $type : '1'), home_url());
}

//消息中心的路由设置
if (_pz('message_s', true)) {
    add_action('generate_rewrite_rules', 'zibmsg_rewrite_rules');
    add_filter('query_vars', 'zibmsg_query_vars');
    add_action('template_redirect', 'zibmsg_load_template', 5);
    add_action('pre_get_posts', 'zibmsg_pre_get_posts');
    add_filter('pre_handle_404', 'zibmsg_pre_handle_404', 10, 2);
    add_action('ajax_get_current_user', 'zibmsg_auto_clear');
}

/**
 * @description: 自动清理消息
 * @param {*} $user_id
 * @return {*}
 */
function zibmsg_auto_clear($user_id)
{

    if (!$user_id) {
        return;
    }

    $clear_types = zibmsg_get_clear_types();
    $clear_time  = (int) _pz('message_auto_clear_time', 365);

    if (!$clear_types || $clear_time < 10) {
        return;
    }

    $receive_user = $user_id;
    if (is_super_admin()) {
        $receive_user = array($user_id, 'admin');
    }

    $ago_time = date('Y-m-d H:i:s', strtotime("-$clear_time day", strtotime(current_time('Y-m-d H:i:s'))));

    $where = array(
        ['receive_user', is_array($receive_user) ? 'in' : '=', $receive_user],
        ['readed_user', is_array($user_id) ? 'in' : '=', $user_id],
        ['type', is_array($clear_types) ? 'in' : '=', $clear_types],
        ['modified_time', '<', $ago_time],

    );
    return ZibMsg::delete($where);
}

/**
 * @description: 消息中心的固定链接
 * @param {*} $wp_rewrite
 * @return {*}
 */
function zibmsg_rewrite_rules($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        $rewrite_slug                               = trim(_pz('msg_center_rewrite_slug'));
        $rewrite_slug                               = $rewrite_slug ? $rewrite_slug : 'message';
        $new_rules[$rewrite_slug . '$']             = 'index.php?msg_center=1';
        $new_rules[$rewrite_slug . '/([A-Za-z]+)$'] = 'index.php?msg_center=$matches[1]';
        $wp_rewrite->rules                          = $new_rules + $wp_rewrite->rules;
    }
}

function zibmsg_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'msg_center';
    }
    return $public_query_vars;
}

function zibmsg_load_template()
{
    global $wp_query;
    $user_center = get_query_var('msg_center');
    if ($user_center) {
        global $wp_query;
        $wp_query->is_home = false;
        $wp_query->is_404  = false;
        status_header(200);

        $template = get_theme_file_path('inc/functions/message/page/msg-center.php');
        load_template($template);
        exit;
    }
}

function zibmsg_pre_get_posts($wp_query)
{
    if (!$wp_query->is_main_query() || is_admin()) {
        return;
    }

    if ($wp_query->get('msg_center')) {
        // 消息中心分页由主题自行处理，避免 WP 主查询把 ?paged=N 当成文章翻页
        $wp_query->set('paged', 1);
        $wp_query->is_paged = false;
    }
}

/**
 * 消息中心为虚拟页面，跳过 WordPress 默认的 404 判定
 *
 * @param bool|false $preempt  是否短路默认 404 处理
 * @param WP_Query   $wp_query 查询对象
 * @return bool|false
 */
function zibmsg_pre_handle_404($preempt, $wp_query)
{
    if ($wp_query->get('msg_center')) {
        return true;
    }

    return $preempt;
}

//路由设置结束
