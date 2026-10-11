<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-22 12:34:29
 * @LastEditTime : 2026-05-22 18:48:36
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2026 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

function zib_ai_seo_is_enabled()
{
    return _pz('ai_seo_s', true) && _pz('post_keywords_description_s', true);
}

/**
 * 注册ai能力
 */
function zib_ai_seo_register_abilities()
{
    if (!zib_ai_seo_is_enabled()) {
        return;
    }

    $abilities = array(
        'seo-title'       => 'Zib_AI_SEO_Title_Ability',
        'seo-keywords'    => 'Zib_AI_SEO_Keywords_Ability',
        'seo-description' => 'Zib_AI_SEO_Description_Ability',
    );

    foreach ($abilities as $name => $ability_class) {
        zib_ai_register_ability($name, $ability_class);
    }
}
add_action('wp_abilities_api_init', 'zib_ai_seo_register_abilities');

//加载js和css，只在后台加载
function zib_ai_seo_enqueue_assets()
{
    if (!zib_ai_seo_is_enabled()) {
        return;
    }

    //只在post页面和term页面加载
    if (!in_array(get_current_screen()->base, array('post', 'edit-tags', 'term'))) {
        return;
    }

    $data = [
        'ai_seo_s' => true,
    ];

    zib_ai_seo_enqueue_common_assets($data);
}
add_action('admin_enqueue_scripts', 'zib_ai_seo_enqueue_assets');


function zib_ai_seo_run_field($ability, $type, $id)
{

    $ability_name = 'zib-ai/' . $ability;
    if (!zib_ai_has_ability($ability_name)) {
        return ['error' => sprintf(__('能力 %s 未注册', 'zib_language'), $ability_name)];
    }

    $ability = wp_get_ability($ability_name);
    $result  = $ability->execute(array('post_id' => $id, 'type' => $type, 'term_id' => $id));
    if (is_wp_error($result)) {
        return ['error' => $result->get_error_message()];
    }

    return $result;
}
