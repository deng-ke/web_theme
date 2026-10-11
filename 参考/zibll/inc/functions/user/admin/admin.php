<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime : 2025-05-07 15:58:15
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户认证相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//邀请码注册

function zib_add_invit_code_submenu_page()
{
    add_submenu_page('users.php', __('邀请码管理', 'zib_language'), __('邀请码管理', 'zib_language'), 'administrator', 'invit_code', 'zib_require_invit_code_submenu_page');
}
add_action('admin_menu', 'zib_add_invit_code_submenu_page');

function zib_require_invit_code_submenu_page()
{
    require get_theme_file_path('inc/functions/user/admin/invit-code-page.php');
}
//邀请码注册结束

if (_pz('user_auth_s', true)) {
    add_action('admin_menu', 'zib_add_user_auth_submenu_page');
    add_action('admin_notices', 'zib_add_user_auth_apply_admin_notice');
}

function zib_add_user_auth_apply_admin_notice()
{
    if (isset($_GET['page']) && 'user_auth' == $_GET['page']) {
        return;
    }

    $withdraw_count = ZibMsg::get_count(array(
        'type'   => 'auth_apply',
        'status' => 0,
    ));

    if ($withdraw_count > 0) {
        $html = '<div class="notice notice-info is-dismissible">';
        $html .= '<h3>' . __('用户身份认证申请待处理', 'zib_language') . '</h3>';
        $html .= '<p>' . sprintf(__('您有%d个用户身份认证申请待处理', 'zib_language'), $withdraw_count) . '</p>';
        $html .= '<p><a class="button" href="' . add_query_arg(array('page' => 'user_auth', 'status' => 0), admin_url('admin.php')) . '">' . __('立即处理', 'zib_language') . '</a></p>';
        $html .= '</div>';
        echo $html;
    }

}

//后台申请审核
function zib_add_user_auth_submenu_page()
{
    add_submenu_page('users.php', __('处理身份认证申请', 'zib_language'), __('身份认证', 'zib_language'), 'administrator', 'user_auth', 'zib_require_user_auth_submenu_page');
}

function zib_require_user_auth_submenu_page()
{
    require get_theme_file_path('inc/functions/user/admin/auth-page.php');
}

function zib_render_profile_auth_form_fields($profile_user)
{
    $fields = array();
    $value  = array();

    if (_pz('user_auth_s', true)) {
        if (!empty($profile_user->ID)) {
            $value = array(
                'auth'      => get_user_meta($profile_user->ID, 'auth', true),
                'auth_info' => zib_get_user_meta($profile_user->ID, 'auth_info', true),
            );
        }

        $fields = array_merge($fields, array(
            array(
                'type'    => 'content',
                'content' => '<h3>' . __('身份认证', 'zib_language') . '</h3><p>' . __('手动设置用户的身份认证信息', 'zib_language') . '</p>',
            ),
            array(
                'title'   => __('已认证用户', 'zib_language'),
                'id'      => 'auth',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('auth', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('认证信息', 'zib_language'),
                'id'         => 'auth_info',
                'type'       => 'fieldset',
                'class'      => 'compact',
                'fields'     => array(
                    array(
                        'title' => __('认证名称(必填)', 'zib_language'),
                        'id'    => 'name',
                        'type'  => 'text',
                    ),
                    array(
                        'title' => __('认证简介', 'zib_language'),
                        'class' => 'compact',
                        'id'    => 'desc',
                        'type'  => 'text',
                    ),
                    array(
                        'title'    => __('认证时间', 'zib_language'),
                        'id'       => 'time',
                        'type'     => 'date',
                        'default'  => current_time('Y-m-d H:i'),
                        'settings' => array(
                            'dateFormat'  => 'yy-mm-dd ' . current_time('H:i'),
                            'changeMonth' => true,
                            'changeYear'  => true,
                        ),
                    ),
                ),
            ),
        ));
    }

    if (_pz('user_level_s', true)) {
        if (!empty($profile_user->ID)) {
            $value += array(
                'level'          => get_user_meta($profile_user->ID, 'level', true),
                'level_integral' => zib_get_user_meta($profile_user->ID, 'level_integral', true),
            );
        }

        $fields = array_merge($fields,
            array(
                array(
                    'type'    => 'content',
                    'content' => '<h3>' . __('用户等级', 'zib_language') . '</h3><p>' . __('手动设置用户的用户经验值', 'zib_language') . '</p>',
                ),
                array(
                    'title'   => __('用户等级', 'zib_language'),
                    'id'      => 'level',
                    'default' => 0,
                    'max'     => 100,
                    'min'     => 1,
                    'step'    => 1,
                    'type'    => 'spinner',
                ),
                array(
                    'title'    => __('用户经验值', 'zib_language'),
                    'subtitle' => '',
                    'id'       => 'level_integral',
                    'class'    => 'compact',
                    'default'  => 1,
                    'max'      => 10000000000000000,
                    'min'      => 0,
                    'step'     => 2,
                    'type'     => 'spinner',
                    'desc'     => __('修改用户等级和经验值，请务必与设置的等级经验值相对应', 'zib_language'),
                ),
            ));
    }

    $csf_args = array(
        'class'  => 'csf-profile-options',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );
    ZCSF::instance('profile_options', $csf_args);
}

function zib_admin_save_profile_auth($cuid)
{

    $fields = array(
        'auth',
        'auth_info',
        'level', //不能加入zib聚合
        'level_integral',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_user_meta($cuid, $field, $_POST[$field]);
        }
    }

    update_meta_cache('user', array($cuid));
}

if (is_super_admin()) {
    add_action('show_user_profile', 'zib_render_profile_auth_form_fields');
    add_action('edit_user_profile', 'zib_render_profile_auth_form_fields');
    add_action('personal_options_update', 'zib_admin_save_profile_auth');
    add_action('edit_user_profile_update', 'zib_admin_save_profile_auth');
}

/**********用户禁封和小黑屋*************/
if (_pz('user_ban_s', true)) {
    //后台页面和后台通知
    add_action('admin_menu', 'zib_add_user_ban_submenu_page');
    add_action('admin_notices', 'zib_add_user_ban_admin_notice');
}

//后台菜单
function zib_add_user_ban_submenu_page()
{
    add_submenu_page('users.php', __('处理举报及禁封申诉', 'zib_language'), __('举报&禁封申诉', 'zib_language'), 'administrator', 'user_ban', 'zib_require_user_ban_submenu_page');
}

//后台页面
function zib_require_user_ban_submenu_page()
{
    require get_theme_file_path('inc/functions/user/admin/ban-page.php');
}

//后台通知
function zib_add_user_ban_admin_notice()
{
    if (isset($_GET['page']) && 'user_ban' == $_GET['page']) {
        return;
    }

    $ban_appeal_count = ZibMsg::get_count(array(
        'type'   => 'ban_appeal',
        'status' => 0,
    ));

    if ($ban_appeal_count > 0) {
        $html = '<div class="notice notice-info is-dismissible">';
        $html .= '<h3>' . __('有新的用户禁封申诉待处理', 'zib_language') . '</h3>';
        $html .= '<p>' . sprintf(__('您有%d个用户禁封申诉待处理', 'zib_language'), $ban_appeal_count) . '</p>';
        $html .= '<p><a class="button" href="' . add_query_arg(array('page' => 'user_ban', 'type' => 'ban', 'status' => 0), admin_url('admin.php')) . '">' . __('立即处理', 'zib_language') . '</a></p>';
        $html .= '</div>';
        echo $html;
    }

    //举报通知
    $report_count = ZibMsg::get_count(array(
        'type'   => 'user_report',
        'status' => 0,
    ));

    if ($report_count > 0) {
        $html = '<div class="notice notice-info is-dismissible">';
        $html .= '<h3>' . __('收到新的不良信息举报', 'zib_language') . '</h3>';
        $html .= '<p>' . sprintf(__('您有%d个举报信息待处理', 'zib_language'), $report_count) . '</p>';
        $html .= '<p><a class="button" href="' . add_query_arg(array('page' => 'user_ban', 'type' => 'report', 'status' => 0), admin_url('admin.php')) . '">' . __('立即处理', 'zib_language') . '</a></p>';
        $html .= '</div>';
        echo $html;
    }

}
