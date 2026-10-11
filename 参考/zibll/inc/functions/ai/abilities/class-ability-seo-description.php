<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 20:28:24
 * @LastEditTime : 2026-05-23 20:58:17
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | AI 扩展seo标题能力
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

class Zib_AI_SEO_Description_Ability extends Zib_AI_SEO_Ability
{
    public static function ability_args()
    {
        return array(
            'label'         => __('zibll SEO 描述生成', 'zib_language'),
            'description'   => __('基于文章上下文，符合搜索习惯又具备点击力的SEO描述', 'zib_language'),
            'ability_class' => self::class,
        );
    }

    //输出数据过滤
    protected function output_filter($text)
    {
        $description = preg_replace('/[\r\n]+/u', ' ', (string) $text);
        $description = trim($description, " \t\r\n\"'`*");

        if ($description === '') {
            return new WP_Error('empty_result', __('AI 未返回有效描述', 'zib_language'));
        }

        return sanitize_text_field($description);
    }

    protected function system_instruction()
    {
        $prompt = _pz('ai_seo_opt','','description_prompt');
        if($prompt){
            return $prompt;
        }

        return '你是一名资深的中文 SEO 编辑，擅长撰写吸引点击的搜索结果摘要（meta description）。
请严格遵循以下规则：
1. 输出 1 段描述，作为该文章的 meta description。
2. 字数控制在 80 至 150 个汉字之间，写成1至4句通顺的中文。
3. 必须自然融入文章的主关键词，但不要堆砌。
4. 体现文章的核心价值、目标读者或可获得的收益，要有吸引力但不夸张。
5. 不要分行、不要使用项目符号、不要使用 Markdown，不要包含品牌后缀或网址。
6. 直接输出描述文本本身，不要任何前缀、引号或解释。';
    }
}
