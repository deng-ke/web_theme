<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 20:09:57
 * @LastEditTime : 2026-05-22 14:58:29
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | AI SEO 模块入口
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

abstract class Zib_AI_SEO_Ability extends Zib_AI_Ability
{

    /**输入schema
     * @return array<string,mixed>
     */
    protected function input_schema()
    {
        return array(
            'type'       => 'object',
            'properties' => array(
                'post_id' => array(
                    'type'              => 'integer',
                    'description'       => __('文章 ID。', 'zib_language'),
                    'sanitize_callback' => 'absint',
                ),
                'type'    => array(
                    'type'              => 'string',
                    'description'       => __('类型：post|term', 'zib_language'),
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => 'post',
                ),
                'term_id' => array(
                    'type'              => 'integer',
                    'description'       => __('分类 ID。', 'zib_language'),
                    'sanitize_callback' => 'absint',
                ),
            ),
            'required'   => array('type', 'post_id', 'term_id'),
        );
    }

    /**
     * 输入验证
     * @param mixed $input
     * @return true|WP_Error
     */
    public function validate_input($input = null)
    {

        $post_id = is_array($input) && isset($input['post_id']) ? ($input['post_id']) : 0;
        $term_id = is_array($input) && isset($input['term_id']) ? ($input['term_id']) : 0;
        $type    = is_array($input) && isset($input['type']) ? ($input['type']) : 'post';

        if ($type === 'post') {
            if (!$post_id) {
                return new WP_Error('no_post', __('需要提供post_id', 'zib_language'));
            }

            $post = get_post($post_id);
            if (!$post) {
                return new WP_Error('no_post', sprintf(__('文章 #%d 不存在', 'zib_language'), $post_id));
            }

            //判断权限
            if (!current_user_can('edit_post', $post_id)) {
                return new WP_Error('forbidden', __('权限不足，无法编辑此文章', 'zib_language'));
            }

        } elseif ($type === 'term') {
            if (!$term_id) {
                return new WP_Error('no_term', __('需要提供term_id', 'zib_language'));
            }

            $term = get_term($term_id);
            if (!$term) {
                return new WP_Error('no_term', sprintf(__('term ID #%d 不存在', 'zib_language'), $term_id));
            }

            //判断权限
            if (!current_user_can('edit_term', $term_id)) {
                return new WP_Error('forbidden', __('权限不足，无法编辑此term', 'zib_language'));
            }
        } else {
            return new WP_Error('invalid_type', __('无效的类型', 'zib_language'));
        }

        return true;
    }

    protected function prompt_text()
    {
        $type = $this->input['type'] ?? 'post';

        if ($type === 'post') {
            return $this->build_post_context($this->input['post_id']);
        } else {
            return $this->build_term_context($this->input['term_id']);
        }
    }

    /**
     * 构建分类上下文
     * @param int $term_id
     * @return string|WP_Error
     */
    protected function build_term_context($term_id)
    {
        $term = get_term($term_id);
        if (!$term) {
            return new WP_Error('no_term', __('分类不存在', 'zib_language'));
        }

        $parts = array();
        if ($term->name !== '') {
            $parts[] = '标题: ' . $term->name;
        }
        if ($term->description !== '') {
            $parts[] = '描述: ' . $term->description;
        }

        if (empty($parts)) {
            return new WP_Error('empty_term', __('没有可用的标题或描述', 'zib_language'));
        }

        return implode("\n\n", $parts);
    }

    /**
     * 构建文章上下文
     * @param int $post_id
     * @param int $word_limit
     * @return string|WP_Error
     */
    protected function build_post_context($post_id, $word_limit = 1500)
    {
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('no_post', __('内容不存在', 'zib_language'));
        }

        $title   = trim((string) $post->post_title);
        $excerpt = trim((string) $post->post_excerpt);

        $content = wp_strip_all_tags((string) $post->post_content);
        $content = trim(preg_replace('/\s+/u', ' ', $content));
        $content = wp_trim_words($content, max(50, (int) $word_limit), '');

        if ($title === '' && $content === '' && $excerpt === '') {
            return new WP_Error('empty_content', __('无可用内容，请先填写标题、摘要或正文并保存', 'zib_language'));
        }

        $parts = array();
        if ($title !== '') {
            $parts[] = '标题: ' . $title;
        }
        if ($excerpt !== '') {
            $parts[] = '摘要: ' . $excerpt;
        }
        if ($content !== '') {
            $parts[] = '正文:' . "\n" . $content;
        }

        return implode("\n\n", $parts);
    }

}
