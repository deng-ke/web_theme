<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 21:33:26
 * @LastEditTime : 2026-05-23 20:58:00
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2026 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 20:28:24
 * @LastEditTime : 2026-05-21 21:33:24
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

class Zib_AI_SEO_Keywords_Ability extends Zib_AI_SEO_Ability
{
    public static function ability_args()
    {
        return array(
            'label'         => __('zibll SEO 关键词生成', 'zib_language'),
            'description'   => __('基于文章上下文，符合搜索习惯又具备点击力的SEO关键词', 'zib_language'),
            'ability_class' => self::class,
        );
    }

    //输出数据过滤
    protected function output_filter($text)
    {
        $keywords = $this->normalize_keywords($text);

        if (empty($keywords)) {
            return new WP_Error('empty_result', __('AI 未返回有效关键词', 'zib_language'));
        }

        return sanitize_text_field($keywords);
    }

    protected function normalize_keywords($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        // 去除常见的包裹符号 / 前后缀 / Markdown。
        $text = trim($text, " \t\r\n\"'`*《》【】[]()（）。.,，;；");
        // 各类分隔符统一成英文逗号。
        $text = preg_replace('/[\r\n、；;，]+/u', ',', $text);
        $text = preg_replace('/\s*,\s*/', ',', $text);
        $text = preg_replace('/,+/', ',', $text);
        $text = trim($text, ', ');

        // 去重，保留顺序。
        $parts  = array_filter(array_map('trim', explode(',', $text)), 'strlen');
        $unique = array();
        foreach ($parts as $part) {
            if (!in_array($part, $unique, true)) {
                $unique[] = $part;
            }
        }
        return implode(',', $unique);
    }

    protected function system_instruction()
    {

        $prompt = _pz('ai_seo_opt','','keywords_prompt');
        if($prompt){
            return $prompt;
        }

        return '你是一名资深 SEO 编辑，负责为网站生成文章 meta keywords。
请严格遵循以下规则：
1. 输出 4 到 8 个最具搜索价值的关键词。
2. 全部使用中文（专有英文词可保留原文，例如 WordPress、AI、SEO）。
3. 使用英文逗号"," 分隔，不要使用顿号、句号、换行或编号。
4. 第一个关键词必须是文章的主关键词，紧扣标题与正文核心主题。
5. 不要堆砌、不要重复同义词、不要出现"等"、"以及"等填充语。
6. 直接输出关键词列表，不要任何前缀、解释、Markdown、引号或括号。
7. 关键词总长度建议控制在 50 个汉字以内。';
    }
}
