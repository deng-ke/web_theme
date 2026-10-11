<?php

if (!defined('ABSPATH')) {
    exit;
}

//seo生成
function zib_ajax_ai_seo_generate()
{

    if (!zib_ai_seo_is_enabled()) {
        zib_send_json_error(__('AI SEO 功能未启用', 'zib_language'));
    }

    //环境验证
    zib_ajax_wp_verify_nonce('zib_ai');

    //参数验证
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $id   = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if (!$type || !in_array($type, array('post', 'term')) || !$id) {
        zib_send_json_error(__('参数错误', 'zib_language'));
    }

    //分别使用三个能力生成
    $title       = zib_ai_seo_run_field('seo-title', $type, $id);
    $keywords    = zib_ai_seo_run_field('seo-keywords', $type, $id);
    $description = zib_ai_seo_run_field('seo-description', $type, $id);
    zib_send_json_success(array(
        'title'       => $title,
        'keywords'    => $keywords,
        'description' => $description,
    ));
}
add_action('wp_ajax_ai_seo_generate', 'zib_ajax_ai_seo_generate');
add_action('wp_ajax_nopriv_ai_seo_generate', 'zib_ajax_ai_seo_generate');
