<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 20:28:24
 * @LastEditTime : 2026-05-23 20:57:31
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

class Zib_AI_SEO_Title_Ability extends Zib_AI_SEO_Ability
{
    public static function ability_args()
    {
        return array(
            'label'         => __('zibll SEO 标题生成', 'zib_language'),
            'description'   => __('基于文章上下文，符合搜索习惯又具备点击力的SEO标题', 'zib_language'),
            'ability_class' => self::class,
        );
    }

    //输出数据过滤
    protected function output_filter($data)
    {
        return sanitize_text_field($data) . zib_get_delimiter_blog_name();
    }

    protected function system_instruction()
    {
        $prompt = _pz('ai_seo_opt','','title_prompt');
        if($prompt){
            return $prompt;
        }

        return '你是一名资深的网站 SEO 编辑，擅长撰写既符合搜索习惯又具备点击力的文章标题。
请严格遵循以下规则：
1. 输出 1 条最佳标题，作为该文章的 <title>。
2. 字数控制在 20 至 40 个汉字之间，避免过短或过长。
3. 必须包含文章的主关键词，且把最重要的关键词放在标题前部。
4. 不要堆砌关键词、不要重复同义词、不要使用煽动性或夸张词汇。
5. 不要包含网站名称、品牌后缀，我们会自动追加站点名。
6. 直接输出标题文本本身，不要任何前缀、引号、Markdown、序号或解释。
7. 不要输出"标题："、"答："、"以下是" 等任何前导词。';
    }
}
