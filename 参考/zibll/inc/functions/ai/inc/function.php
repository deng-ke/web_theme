<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-22 12:42:21
 * @LastEditTime : 2026-05-22 13:24:00
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | AI 通用函数
 * Copyright (c) 2026 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**
 * 加载通用静态资源
 * @param array $data
 * @return void
 */
function zib_ai_seo_enqueue_common_assets($data = [])
{
    wp_enqueue_script('zib-ai-common', ZIB_TEMPLATE_DIRECTORY_URI . '/inc/functions/ai/assets/ai.min.js', array('jquery'), THEME_VERSION, true);
    wp_enqueue_style('zib-ai-common', ZIB_TEMPLATE_DIRECTORY_URI . '/inc/functions/ai/assets/ai.min.css', array(), THEME_VERSION, 'all');

    //加载js数据资源
    wp_localize_script('zib-ai-common', 'zib_ai', array_merge(array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('zib_ai'),
        'i18n'     => array(
            'request_failed'  => __('网络异常，请稍后重试', 'zib_language'),
            'request_timeout' => __('（请求超时，请稍后重试）', 'zib_language'),
        ),
    ), $data));
}
