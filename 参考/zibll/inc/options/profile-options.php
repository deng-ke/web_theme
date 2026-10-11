<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-18 23:36:57
 * @LastEditTime: 2024-07-05 12:35:26
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台用户编辑配置项目|仅在后台引用
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

function zib_render_profile_form_fields($profile_user)
{
    $fields = array(
        array(
            'type'    => 'content',
            'content' => '<h3>' . esc_html__('更多资料', 'zib_language') . '</h3><span style="color: #fb3f2c;">' . esc_html__('此处内容建议在前台用户中心修改', 'zib_language') . '</span>',
        ),
        array(
            'id'      => 'custom_avatar',
            'type'    => 'upload',
            'library' => 'image',
            'title'   => __('自定义头像', 'zib_language'),
        ),
        array(
            'id'      => 'cover_image',
            'type'    => 'upload',
            'library' => 'image',
            'title'   => __('自定义封面', 'zib_language'),
        ),
        array(
            'id'      => 'gender',
            'type'    => 'select',
            'title'   => __('性别', 'zib_language'),
            'options' => array(
                '保密' => __('保密', 'zib_language'),
                '男'  => __('男', 'zib_language'),
                '女'  => __('女', 'zib_language'),
            ),
            'default' => '保密',
        ),
        array(
            'id'          => 'qq',
            'type'        => 'text',
            'title'       => 'QQ',
            'placeholder' => __('请输入QQ号', 'zib_language'),
        ),
        array(
            'id'          => 'weixin',
            'type'        => 'text',
            'title'       => __('微信', 'zib_language'),
            'placeholder' => __('请输入微信号', 'zib_language'),
        ),
        array(
            'id'          => 'weibo',
            'type'        => 'text',
            'title'       => __('微博', 'zib_language'),
            'placeholder' => __('请输入微博地址', 'zib_language'),
        ),
        array(
            'id'          => 'github',
            'type'        => 'text',
            'title'       => 'GitHub',
            'placeholder' => __('请输入GitHub地址', 'zib_language'),
        ),
        array(
            'id'          => 'address',
            'type'        => 'text',
            'title'       => __('住址', 'zib_language'),
            'placeholder' => __('请输入住址', 'zib_language'),
        )
        , array(
            'id'          => 'phone_number',
            'type'        => 'text',
            'title'       => __('绑定手机号', 'zib_language'),
            'placeholder' => __('请输入手机号', 'zib_language'),
            'attributes'  => array(
                'data-readonly-id' => 'user_phone_number',
                'readonly'         => 'readonly',
            ),
            'desc'        => __('手机号涉及到用户登录的功能，不建议后台修改', 'zib_language') . '<br><a href="javascript:;" class="but c-yellow remove-readonly" readonly-id="user_phone_number">' . esc_html__('修改用户手机号', 'zib_language') . '</a>',
        ),
    );

    $value = array();
    if (!empty($profile_user->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('user_meta');
        $zib_meta         = get_user_meta($profile_user->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_user_meta($profile_user->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
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

add_action('show_user_profile', 'zib_render_profile_form_fields');
add_action('edit_user_profile', 'zib_render_profile_form_fields');

function zib_admin_save_profile($cuid)
{

    $fields = array(
        'custom_avatar',
        'cover_image',
        'gender',
        'qq',
        'weixin',
        'weibo',
        'github',
        'address',
        'phone_number',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_user_meta($cuid, $field, $_POST[$field]);
        }
    }
}
add_action('personal_options_update', 'zib_admin_save_profile');
add_action('edit_user_profile_update', 'zib_admin_save_profile');
