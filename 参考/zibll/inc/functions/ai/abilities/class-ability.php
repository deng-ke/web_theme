<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-21 20:09:57
 * @LastEditTime : 2026-05-22 15:33:32
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | AI 扩展模块入口
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

abstract class Zib_AI_Ability extends WP_Ability
{
    protected $category     = 'zib-ai';
    protected $temperature  = 0.7;
    protected $input        = array();
    protected $show_in_rest = true;
    protected $output_key   = 'content';

    public function __construct($name, array $properties = array())
    {
        parent::__construct(
            $name,
            array(
                'label'               => isset($properties['label']) ? (string) $properties['label'] : '',
                'description'         => isset($properties['description']) ? (string) $properties['description'] : '',
                'category'            => $this->get_category(),
                'input_schema'        => $this->input_schema(),
                'output_schema'       => $this->output_schema(),
                'execute_callback'    => array($this, 'execute_callback'),
                'permission_callback' => array($this, 'permission_callback'),
                'meta'                => $this->meta(),
            )
        );
    }

    /**
     * @return array<string,mixed>
     */
    protected function meta()
    {
        return array(
            'show_in_rest' => $this->show_in_rest,
        );
    }

    /**
     * @return array<string,mixed>
     */
    abstract protected function input_schema();

    /**
     * @return array<string,mixed>
     */
    protected function output_schema()
    {
        return array(
            'type'       => 'object',
            'properties' => array(
                $this->output_key => array(
                    'type'        => 'string',
                    'description' => __('生成的结果', 'zib_language'),
                ),
            ),
        );
    }

    //对输出的数据先进行过滤
    protected function output_filter($data)
    {
        return sanitize_text_field($data);
    }

    //输出数据
    protected function output($data)
    {
        $data = $this->output_filter($data);

        if (is_wp_error($data)) {
            return $data;
        }

        return array(
            $this->output_key => $data,
        );
    }

    /**
     * @param mixed $input
     * @return mixed|WP_Error
     */
    protected function execute_callback($input)
    {
        $this->input = $input;

        $text = $this->generate_text();
        if (is_wp_error($text)) {
            return $text;
        }

        return $this->output($text);
    }

    /**
     * 执行前权限回调,默认返回true
     * @param mixed $input
     * @return true|WP_Error
     */
    protected function permission_callback($input)
    {
        return true;
    }

    /**
     * 输入验证
     * @param mixed $input
     * @return true|WP_Error
     */
    public function validate_input($input = null)
    {
        return parent::validate_input($input);
    }

    /**
     * 获取需要喂给AI的prompt
     * @param int $post_id
     * @return string|WP_Error
     */
    abstract protected function prompt_text();

    /**
     * 获取系统指令词语
     * @return string|WP_Error
     */
    protected function system_instruction()
    {
        return '';
    }

    /**
     * 生成文本
     * @return string|WP_Error
     */
    protected function generate_text()
    {

        $prompt = $this->prompt_text();
        if (is_wp_error($prompt)) {
            return $prompt;
        }

        if (!is_string($prompt) || trim($prompt) === '') {
            return new WP_Error('empty_prompt', __('AI 未获取到可用的内容', 'zib_language'));
        }

        $system_instruction = $this->system_instruction();

        try {
            $text = wp_ai_client_prompt($prompt)
                ->using_system_instruction($system_instruction)
                ->generate_text();
        } catch (\Throwable $t) {
            return new WP_Error('ai_exception', $t->getMessage());
        }

        if (is_wp_error($text)) {
            return $text;
        }
        if (!is_string($text) || trim($text) === '') {
            return new WP_Error('empty_result', __('AI 未返回任何文本', 'zib_language'));
        }

        return $text;
    }

}
