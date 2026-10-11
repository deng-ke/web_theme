<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 19:47:41
 * @LastEditTime : 2026-05-22 12:35:05
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

if (!class_exists('WP_Ability')) {
    return;
}

zib_require(['class-ability', 'class-ability-seo'], false, 'inc/functions/ai/abilities/');

/**
 * 注册 zib-ai 能力分类。
 *
 * WP 6.9+ Abilities API 要求每个 ability 必须挂在一个已注册的 category 下。
 */
function zib_ai_seo_register_ability_category()
{
    if (!function_exists('wp_register_ability_category')) {
        return;
    }
    wp_register_ability_category('zib-ai', array(
        'label'       => 'zibll AI',
        'description' => __('子比主题AI扩展模块', 'zib_language'),
    ));
}
add_action('wp_abilities_api_categories_init', 'zib_ai_seo_register_ability_category');

/**
 * 注册能力
 * @param string $name 能力名称
 * @param array $args 能力参数
 * @return void
 */
function zib_ai_register_ability($name, $ability_class)
{
    if (!function_exists('wp_register_ability')) {
        return;
    }
    if (!class_exists('WP_Ability')) {
        return;
    }

    $file_path = get_theme_file_path('inc/functions/ai/abilities/class-ability-' . $name . '.php');
    if (file_exists($file_path)) {
        require $file_path;

        wp_register_ability('zib-ai/' . $name, $ability_class::ability_args());
    }
}
