<?php if (!defined('ABSPATH')) {exit;} // Cannot access directly.
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-12 13:28:48
 * @LastEditTime : 2026-09-15 13:32:52
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2026 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */
/**
 *
 * Widgets Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
class CSF_Widget extends WP_Widget
{

    // constans
    public $unique = '';
    public $args   = [];

    public function __construct($key, $params)
    {
        $args_default = [
            'title'          => '',
            'classname'      => '',
            'description'    => '',
            'reminder'       => '', //提示信息
            'width'          => '',
            'class'          => '',
            'size'           => '',
            'widget_class'   => '',
            'is_show_filter' => '',
            'callback'       => '',
            'defaults'       => array(),
            'fields'         => array(),
        ];

        $widget_ops  = array();
        $control_ops = array();

        $this->unique = $key;
        $this->args   = apply_filters("csf_{$this->unique}_args", wp_parse_args($params, $args_default), $this);

        // Set control options
        if (!empty($this->args['width'])) {
            $control_ops['width'] = esc_attr($this->args['width']);
        }

        // Set widget options
        if (!empty($this->args['description'])) {
            $widget_ops['description'] = esc_attr($this->args['description']);
        }

        if (!empty($this->args['classname'])) {
            $widget_ops['classname'] = esc_attr($this->args['classname']);
        }

        // Set filters
        $widget_ops  = apply_filters("csf_{$this->unique}_widget_ops", $widget_ops, $this);
        $control_ops = apply_filters("csf_{$this->unique}_control_ops", $control_ops, $this);

        parent::__construct($this->unique, esc_attr($this->args['title']), $widget_ops, $control_ops);

    }

    // Register widget with WordPress
    public static function instance($key, $params = array())
    {
        $params = self::params($params);
        return new self($key, $params);
    }

    /**
     * 按 id_base 获取可渲染表单的小工具实例。
     * 子比通过 CSF_Widget::instance() 注册后，工厂内对象会在 widgets_init 后被移除，不能依赖 get_widget_object()。
     *
     * @param string $id_base
     * @return CSF_Widget|null
     */
    public static function get_widget_for_form($id_base)
    {
        if (!$id_base || !class_exists('CSF') || empty(CSF::$args['widget_options'][$id_base])) {
            return null;
        }

        return self::instance($id_base, CSF::$args['widget_options'][$id_base]);
    }

    public static function params($args = array())
    {

        $args['title'] = 'Zibll ' . $args['title'];
        $more_args     = array();

        if (!empty($args['description'])) {
            $more_args[] = array(
                'content' => $args['description'],
                'style'   => 'text',
                'type'    => 'content',
            );
        }

        if (!empty($args['reminder'])) {
            $more_args[] = array(
                'content' => '<i class="fa fa-fw fa-info-circle"></i>' . $args['reminder'],
                'style'   => 'warning',
                'type'    => 'submessage',
            );
        }

        if (isset($args['reminder'])) {
            unset($args['reminder']);
        }

        if (!empty($args['zib_title']) || !isset($args['zib_title'])) {
            if (!isset($args['zib_title'])) {
                unset($args['zib_title']);
            }

            $more_args[] = array(
                'title'      => __('模块标题', 'zib_language'),
                'id'         => 'title',
                'default'    => '',
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            );
            $more_args[] = array(
                'dependency' => array('title', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('副标题', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'subtitle',
                'default'    => '',
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            );

            $more_args[] = array(
                'dependency' => array('title', '!=', ''),
                'title'      => __('标题样式', 'zib_language'),
                'id'         => 'title_style',
                'type'       => 'radio',
                'inline'     => true,
                'default'    => '',
                'options'    => array(
                    ''    => __('简约', 'zib_language'),
                    'big' => __('居中大标题', 'zib_language'),
                ),
            );

            $more_args[] = array(
                'dependency' => array('title', '!=', ''),
                'title'      => __('标题选字高亮', 'zib_language'),
                'id'         => 'title_highlight',
                'type'       => 'text',
                'desc'       => __('标题中要被高亮的部分', 'zib_language'),
            );

            $more_args[] = array(
                'dependency' => array('title|title_highlight', '!=|!=', ''),
                'id'         => 'title_highlight_color',
                'title'      => __('高亮颜色', 'zib_language'),
                'class'      => 'skin-color compact',
                'default'    => 'focus-color',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(array('' => array('#4f5359')), array('text', 'cg')),
            );

            $more_args[] = array(
                'dependency'   => array('title', '!=', ''),
                'title'        => __('标题链接', 'zib_language'),
                'id'           => 'title_link',
                'type'         => 'link',
                'add_title'    => __('添加链接', 'zib_language'),
                'edit_title'   => __('编辑链接', 'zib_language'),
                'remove_title' => __('删除链接', 'zib_language'),
            );
            $more_args[] = array(
                'dependency'   => array('title|title_style', '!=|==', '|big'),
                'title'        => ' ',
                'subtitle'     => __('标题链接2', 'zib_language'),
                'class'        => 'compact',
                'id'           => 'title_link2',
                'type'         => 'link',
                'add_title'    => __('添加链接', 'zib_language'),
                'edit_title'   => __('编辑链接', 'zib_language'),
                'remove_title' => __('删除链接', 'zib_language'),
            );
            $more_args[] = array(
                'dependency'   => array('title|title_style', '!=|==', '|big'),
                'title'        => ' ',
                'subtitle'     => __('标题链接3', 'zib_language'),
                'class'        => 'compact',
                'id'           => 'title_link3',
                'type'         => 'link',
                'add_title'    => __('添加链接', 'zib_language'),
                'edit_title'   => __('编辑链接', 'zib_language'),
                'remove_title' => __('删除链接', 'zib_language'),
            );
            $more_args[] = array(
                'dependency' => array('title|title_style|title_link', '!=|==|!=', '|big|'),
                'id'         => 'title_link_style',
                'type'       => 'accordion',
                'accordions' => array(
                    array(
                        'title'  => __('链接样式', 'zib_language'),
                        'fields' => array(
                            array(
                                'id'      => 'color_1',
                                'title'   => __('链接1：颜色', 'zib_language'),
                                'class'   => 'skin-color',
                                'default' => 'jb-blue',
                                'type'    => 'palette',
                                'options' => CFS_Module::zib_palette(
                                    ['' => array('rgba(225, 225, 225, 0.4)')]
                                ),
                            ),
                            array(
                                'id'      => 'color_2',
                                'title'   => __('链接2：颜色', 'zib_language'),
                                'class'   => 'skin-color',
                                'default' => 'jb-yellow',
                                'type'    => 'palette',
                                'options' => CFS_Module::zib_palette(
                                    ['' => array('rgba(225, 225, 225, 0.4)')]
                                ),
                            ),
                            array(
                                'id'      => 'color_3',
                                'title'   => __('链接3：颜色', 'zib_language'),
                                'class'   => 'skin-color',
                                'default' => 'jb-green',
                                'type'    => 'palette',
                                'options' => CFS_Module::zib_palette(
                                    ['' => array('rgba(225, 225, 225, 0.4)')]
                                ),
                            ),
                        ),
                    ),
                ),
            );
        }
        if (!empty($args['zib_affix'])) {
            unset($args['zib_affix']);
            $more_args[] = array(
                'title'    => __('侧栏随动', 'zib_language'),
                'subtitle' => '',
                'id'       => 'sidebar_affix',
                'label'    => __('仅在侧边栏有效', 'zib_language'),
                'type'     => 'switcher',
                'default'  => false,
            );
        }
        if (!empty($args['zib_show'])) {
            unset($args['zib_show']);
            $more_args[] = array(
                'title'   => __('显示规则', 'zib_language'),
                'id'      => 'show_type',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'all',
                'options' => array(
                    'all'     => __('全部显示', 'zib_language'),
                    'only_pc' => __('仅PC端显示', 'zib_language'),
                    'only_sm' => __('仅移动端显示', 'zib_language'),
                ),
            );

            $more_args[] = array(
                'title'   => __('按ID显示或隐藏', 'zib_language'),
                'id'      => 'show_id_type',
                'type'    => 'radio',
                'inline'  => true,
                'default' => '',
                'options' => array(
                    'show' => __('在指定ID中显示', 'zib_language'),
                    'hide' => __('在指定ID中隐藏', 'zib_language'),
                ));
            $more_args[] = array(
                'id'    => 'show_ids',
                'class' => 'compact',
                'type'  => 'text',
                'desc'  => '<span class="px12">' . __('当此模块添加在文章、帖子、板块、商品页面时候，可以通过此处设置ID，来控制显示或隐藏此模块，多个ID用英文逗号隔开。 例如：', 'zib_language') . '<code>1,2,3</code></span>',
            );
        }

        if (!empty($args['zib_animation_in']) || !isset($args['zib_animation_in'])) {
            if (!isset($args['zib_animation_in'])) {
                unset($args['zib_animation_in']);
            }
            $more_args[] = array(
                'title'   => __('模块入场动画', 'zib_language'),
                'id'      => 'animation_in',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('当页面滚动到此模块时，会自动触发此动画，注意：合理适当启用才能达到最佳效果', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            );

            $more_args[] = array(
                'title'   => __('重复动画', 'zib_language'),
                'id'      => 'animation_repeat',
                'type'    => 'switcher',
                'class'   => 'compact',
                'default' => false,
                'desc'    => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            );
        }

        if (!empty($args['zib_layout']) || !isset($args['zib_layout'])) {
            if (!isset($args['zib_layout'])) {
                unset($args['zib_layout']);
            }

            $layout_fields = array(
                array(
                    'id'          => 'layout_pin_pc',
                    'type'        => 'spacing',
                    'title'       => __('模块额外间距（PC端）', 'zib_language'),
                    'left'        => false,
                    'right'       => false,
                    'top'         => true,
                    'bottom'      => true,
                    'top_icon'    => '<i title="fa fa-arrow-up" class="fa fa-arrow-up"></i>',
                    'bottom_icon' => '<i title="fa fa-arrow-down" class="fa fa-arrow-down" style=""></i>',
                    'units'       => array('px'),
                ),
                array(
                    'id'          => 'layout_pin_m',
                    'type'        => 'spacing',
                    'title'       => ' ',
                    'subtitle'    => __('移动端额外间距', 'zib_language'),
                    'class'       => 'compact',
                    'desc'        => __('添加额外间距（默认已有20px下边距）', 'zib_language'),
                    'left'        => false,
                    'right'       => false,
                    'top'         => true,
                    'bottom'      => true,
                    'top_icon'    => '<i title="fa fa-arrow-up" class="fa fa-arrow-up"></i>',
                    'bottom_icon' => '<i title="fa fa-arrow-down" class="fa fa-arrow-down" style=""></i>',
                    'units'       => array('px'),
                ),
                array(
                    'id'         => 'layout_bg',
                    'type'       => 'accordion',
                    'desc'       => __('背景及间距需根据所在容器位置合理配置！', 'zib_language'),
                    'title'      => __('区块背景', 'zib_language'),
                    'accordions' => array(
                        array(
                            'title'  => __('日间模式背景', 'zib_language'),
                            'fields' => array(
                                array(
                                    'id'                    => 'img_white',
                                    'type'                  => 'background',
                                    'title'                 => __('日间模式背景', 'zib_language'),
                                    'background_color'      => true,
                                    'background_image'      => true,
                                    'background-position'   => true,
                                    'background_repeat'     => true,
                                    'background_attachment' => true,
                                    'background_size'       => true,
                                    'background_origin'     => true,
                                    'background_clip'       => false,
                                    'background_blend_mode' => false,
                                    'background_gradient'   => false,
                                    'desc'                  => __('可同时设置图片和颜色，图片优先级高于颜色', 'zib_language'),
                                    'default'               => array(
                                        'background-position'   => 'center',
                                        'background-repeat'     => 'no-repeat',
                                        'background-attachment' => 'scroll',
                                        'background-size'       => 'cover',
                                        'background-origin'     => '',
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'title'  => __('夜间模式背景', 'zib_language'),
                            'fields' => array(
                                array(
                                    'id'                    => 'img_dark',
                                    'type'                  => 'background',
                                    'title'                 => __('夜间模式背景', 'zib_language'),
                                    'background_color'      => true,
                                    'background_image'      => true,
                                    'background-position'   => true,
                                    'background_repeat'     => true,
                                    'background_attachment' => true,
                                    'background_size'       => true,
                                    'background_origin'     => true,
                                    'background_clip'       => false,
                                    'background_blend_mode' => false,
                                    'background_gradient'   => false,
                                    'desc'                  => __('如果留空则使用日间模式背景', 'zib_language'),
                                    'default'               => array(
                                        'background-position'   => 'center',
                                        'background-repeat'     => 'no-repeat',
                                        'background-attachment' => 'scroll',
                                        'background-size'       => 'cover',
                                        'background-origin'     => '',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),

            );

            $more_args = array_merge($more_args, $layout_fields);
        }

        $more_args[] = array(
            'title'    => ' ',
            'subtitle' => __('模块内容配置', 'zib_language'),
            'type'     => 'subheading',
        );

        $args['fields'] = array_merge($more_args, $args['fields']);
        return $args;
    }

    public function is_size_limit($wp_args = array())
    {
        if (!empty($this->args['size']) && !empty($wp_args['id']) && is_super_admin()) {
            if ($this->args['size'] == 'mini' && (!strstr($wp_args['id'], 'sidebar') && !strstr($wp_args['id'], 'nav'))) {
                return true;
            } elseif ($this->args['size'] == 'big' && (strstr($wp_args['id'], 'sidebar') || strstr($wp_args['id'], 'nav'))) {
                return true;
            }
        }
        return false;
    }

    public static function wrap_attributes($args = array(), $widget = null)
    {
        $styles     = array();
        $classes    = array('zib-widget-wrap');
        $show_class = self::show_class($args);
        if ($show_class && $show_class !== true) {
            $classes[] = $show_class;
        }

        if (!empty($widget) && !empty($widget->args['classname'])) {
            $classes[] = esc_attr($widget->args['classname']);
        }

        if (!empty($args['layout_pin_pc']['top'])) {
            $styles[] = '--pt-pc: ' . $args['layout_pin_pc']['top'] . 'px;';
        }

        if (!empty($args['layout_pin_m']['top'])) {
            $styles[] = '--pt-m: ' . $args['layout_pin_m']['top'] . 'px;';
        }

        if (!empty($args['layout_pin_pc']['bottom'])) {
            $styles[] = '--pb-pc: ' . $args['layout_pin_pc']['bottom'] . 'px;';
        }

        if (!empty($args['layout_pin_m']['bottom'])) {
            $styles[] = '--pb-m: ' . $args['layout_pin_m']['bottom'] . 'px;';
        }

        if (!empty($args['layout_bg']['img_white']['background-color'])) {
            $styles[] = '--bg-color-white: ' . $args['layout_bg']['img_white']['background-color'] . ';';
        }

        if (!empty($args['layout_bg']['img_white']['background-image'])) {
            $styles[] = '--bg-img-white: url("' . $args['layout_bg']['img_white']['background-image'] . '");';
            $styles[] = '--bg-img-white-position: ' . ($args['layout_bg']['img_white']['background-position'] ?? '') . ';';
            $styles[] = '--bg-img-white-repeat: ' . ($args['layout_bg']['img_white']['background-repeat'] ?? '') . ';';
            $styles[] = '--bg-img-white-attachment: ' . ($args['layout_bg']['img_white']['background-attachment'] ?? '') . ';';
            $styles[] = '--bg-img-white-size: ' . ($args['layout_bg']['img_white']['background-size'] ?? '') . ';';
            $styles[] = '--bg-img-white-origin: ' . ($args['layout_bg']['img_white']['background-origin'] ?? '') . ';';
        }

        if (!empty($args['layout_bg']['img_dark']['background-color'])) {
            $styles[] = '--bg-color-dark: ' . $args['layout_bg']['img_dark']['background-color'] . ';';
        }

        if (!empty($args['layout_bg']['img_dark']['background-image'])) {
            $styles[] = '--bg-img-dark: url("' . $args['layout_bg']['img_dark']['background-image'] . '");';
            $styles[] = '--bg-img-dark-position: ' . $args['layout_bg']['img_dark']['background-position'] . ';';
            $styles[] = '--bg-img-dark-repeat: ' . $args['layout_bg']['img_dark']['background-repeat'] . ';';
            $styles[] = '--bg-img-dark-attachment: ' . $args['layout_bg']['img_dark']['background-attachment'] . ';';
            $styles[] = '--bg-img-dark-size: ' . $args['layout_bg']['img_dark']['background-size'] . ';';
            $styles[] = '--bg-img-dark-origin: ' . $args['layout_bg']['img_dark']['background-origin'] . ';';
        }

        $class_attr = ' class="' . esc_attr(implode(' ', $classes)) . '"';
        $style_attr = $styles ? ' style="' . esc_attr(implode('', $styles)) . '"' : '';

        if (!empty($args['sidebar_affix'])) {
            $style_attr .= ' data-affix="true"';
        }

        return $class_attr . $style_attr;
    }

    //判断是否显示此模块
    public function is_show($args, $instance)
    {
        $show_class = self::show_class($instance);
        if ($this->args['is_show_filter'] && function_exists($this->args['is_show_filter'])) {
            $show_class = call_user_func($this->args['is_show_filter'], $show_class, $args, $instance);
        } elseif (function_exists($this->unique . '_is_show')) {
            $show_class = call_user_func($this->unique . '_is_show', $show_class, $args, $instance);
        }

        return apply_filters('widget_is_show_' . $this->unique, $show_class, $args, $instance);
    }

    /**
     * 可用于是否显示判断
     * @param array $instance
     * @return string
     */
    public static function show_class($instance)
    {
        $show_type = isset($instance['show_type']) ? $instance['show_type'] : 'all';

        $wp_is_mobile = wp_is_mobile();
        if ($show_type == 'only_pc' && $wp_is_mobile) {
            //   return '';
        }

        if ($show_type == 'only_sm' && !$wp_is_mobile) {
            //    return '';
        }

        if (!empty($instance['show_id_type']) && !empty($instance['show_ids'])) {
            if (is_singular()) {
                $the_id   = get_the_ID();
                $show_ids = preg_split("/,|，|\s|\n/", $instance['show_ids']);

                if ($instance['show_id_type'] == 'show' && !in_array($the_id, $show_ids)) {
                    return '';
                }
                if ($instance['show_id_type'] == 'hide' && in_array($the_id, $show_ids)) {
                    return '';
                }
            }
        }

        if ($show_type == 'only_pc') {
            return 'hidden-xs';
        }

        if ($show_type == 'only_sm') {
            return 'visible-xs-block';
        }

        return true;
    }

    public static function animation_class($args = array(), $is_title = false)
    {

        $animation = !empty($args['animation_in']) ? $args['animation_in'] : '';
        if ($is_title && !empty($args['obs_animation'])) {
            $animation = $args['obs_animation'];
        }

        $animation_class = $animation ? ' obs-animate ani-' . $animation : '';
        if ($animation && !empty($args['animation_repeat'])) {
            $animation_class .= ' obs-animate-repeat';
        }

        return $animation_class;
    }

    public static function show_title($args = array(), $widget = null)
    {
        if (empty($args['title']) || empty($widget->args['zib_title'])) {
            return;
        }

        //模块出现动画
        $animation_class = self::animation_class($args, true);

        $title     = $args['title'];
        $highlight = isset($args['title_highlight']) ? trim((string) $args['title_highlight']) : '';
        $subtitle  = !empty($args['subtitle']) ? $args['subtitle'] : '';

        $link_url   = !empty($args['title_link']['url']) ? $args['title_link']['url'] : '';
        $link_text  = !empty($args['title_link']['text']) ? $args['title_link']['text'] : '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language');
        $link_blank = !empty($args['title_link']['target']) && $args['title_link']['target'] == '_blank' ? ' target="_blank"' : '';

        if ($highlight) {
            $highlight_color = isset($args['title_highlight_color']) ? $args['title_highlight_color'] : 'focus-color';
            $title           = str_replace($highlight, '<span class="title-highlight ' . $highlight_color . '">' . $highlight . '</span>', $title);
        }

        if (!empty($args['title_style']) && $args['title_style'] == 'big') {
            $link_url_1 = !empty($args['title_link']['url']) && $args['title_link']['text'] ? '<a' . (!empty($args['title_link']['target']) && $args['title_link']['target'] == '_blank' ? ' target="_blank"' : '') . ' href="' . $args['title_link']['url'] . '" class="but ' . (!empty($args['title_link_style']['color_1']) ? $args['title_link_style']['color_1'] : '') . '">' . $args['title_link']['text'] . '</a>' : '';
            $link_url_2 = !empty($args['title_link2']['url']) && $args['title_link2']['text'] ? '<a' . (!empty($args['title_link2']['target']) && $args['title_link2']['target'] == '_blank' ? ' target="_blank"' : '') . ' href="' . $args['title_link2']['url'] . '" class="but ' . (!empty($args['title_link_style']['color_2']) ? $args['title_link_style']['color_2'] : '') . '">' . $args['title_link2']['text'] . '</a>' : '';
            $link_url_3 = !empty($args['title_link3']['url']) && $args['title_link3']['text'] ? '<a' . (!empty($args['title_link3']['target']) && $args['title_link3']['target'] == '_blank' ? ' target="_blank"' : '') . ' href="' . $args['title_link3']['url'] . '" class="but ' . (!empty($args['title_link_style']['color_3']) ? $args['title_link_style']['color_3'] : '') . '">' . $args['title_link3']['text'] . '</a>' : '';

            $link = $link_url_1 . $link_url_2 . $link_url_3;
            $link = $link ? '<div class="flex jc big-title-buts ' . $animation_class . '">' . $link . '</div>' : '';

            $subtitle   = $subtitle ? '<p class="big-title-sub muted-2-color ' . $animation_class . '">' . $subtitle . '</p>' : '';
            $title_html = '<div class="big-title-wrap"><h2 class="big-title' . $animation_class . '">' . $title . '</h2>' . $subtitle . $link . '</div>';
        } else {
            $subtitle   = $subtitle ? '<small class="ml10">' . $subtitle . '</small>' : '';
            $more_but   = $link_url ? '<div class="pull-right em09 mt3"><a' . $link_blank . ' href="' . $link_url . '" class="muted-2-color">' . $link_text . '</a></div>' : '';
            $title_html = '<div class="box-body notop' . $animation_class . '"><div class="title-theme">' . $title . $subtitle . $more_but . '</div></div>';
        }

        return $title_html;
    }

    // Front-end display of widget.
    public function widget($args, $instance)
    {
        $is_show = $this->is_show($args, $instance);
        if (!$is_show) {
            return;
        }

        //如果是预览模式
        $is_preview = $this->is_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . __('编辑', 'zib_language') . '</div>';
        }

        //获取所有的defaults
        $is_size_limit = $this->is_size_limit($args);
        if ($is_size_limit) {
            $desc       = $this->args['size'] == 'mini' ? __('此模块宽度较小，PC端放置在此处，显示效果不佳，建议更换到侧边栏等较窄的容器，或者开启仅移动端显示。', 'zib_language') : __('此模块宽度较大，放置在此处，显示效果不佳，建议更换为全宽度、主内容上下等较宽的容器', 'zib_language');
            $size_limit = '<div class="badg btn-block c-red padding-lg"><i class="fa fa-fw fa-info-circle mr10"></i>' . __('此模块不推荐放置在此位置！', 'zib_language') . $desc . '<br>' . __('(当前提醒只会在管理员登录时显示)', 'zib_language') . '</div>';
            echo '<div style="padding:6px;background:rgba(255, 0, 143, 0.1);">' . $size_limit;
        }

        $animation_class = self::animation_class($instance);

        $class_attr = ' class="widget-content' . $animation_class . '"';
        $class      = $this->args['widget_class'];

        echo '<div' . self::wrap_attributes($instance, $this) . '>';
        echo '<div class="widget-container">';
        echo '<div class="' . ($class ? ' ' . $class : '') . '">';
        echo self::show_title($instance, $this);
        echo '<div' . $class_attr . '>';

        //开始输出模块内容
        //$instance合并默认值
        $defaults = $this->get_all_defaults();
        $instance = array_merge($defaults, $instance);

        if (!empty($this->args['callback']) && function_exists($this->args['callback'])) {
            call_user_func($this->args['callback'], $args, $instance);
        } else {
            call_user_func($this->unique, $args, $instance);
        }
        //结束输出模块内容

        if ($is_size_limit) {
            echo '</div>';
        }
        if ($is_preview) {
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    public function get_all_defaults()
    {
        $defaults = array();
        foreach ($this->args['fields'] as $field) {
            if (!empty($field['id'])) {
                $defaults[$field['id']] = $this->get_default($field);
            }
        }

        return $defaults;
    }

    // get default value
    public function get_default($field)
    {

        $default = (isset($field['default'])) ? $field['default'] : '';
        $default = (isset($this->args['defaults'][$field['id']])) ? $this->args['defaults'][$field['id']] : $default;

        return $default;

    }

    // get widget value
    public function get_widget_value($instance, $field)
    {

        $default = (isset($field['id'])) ? $this->get_default($field) : '';
        $value   = (isset($field['id']) && isset($instance[$field['id']])) ? $instance[$field['id']] : $default;

        return $value;

    }

    /**
     * 小工具「模板」表单（自定义器添加列表 / 多实例占位 number=-1）走 AJAX，减轻 DOM
     */
    public function is_widget_form_template()
    {
        //判断是否是更新的ajax请求
        if (isset($_POST['action']) && $_POST['action'] == 'update-widget') {
            return false;
        }

        return true;
    }

    /**
     * 是否在当前环境启用 AJAX 表单（测试阶段：仅模板占位）
     */
    public function should_use_ajax_widget_form()
    {
        if (!$this->is_widget_form_template()) {
            return false;
        }

        if ($this->is_preview()) {
            return true;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        return $screen && in_array($screen->id, array('widgets', 'customize'), true);
    }

    // Back-end widget form.
    public function form($instance)
    {
        if (empty($this->args['fields'])) {
            return;
        }

        if ($this->should_use_ajax_widget_form()) {
            $this->render_ajax_form_placeholder($instance);
            return;
        }

        $this->render_form_fields($instance);
    }

    /**
     * AJAX 占位：仅输出加载壳，完整字段由 admin-ajax 返回
     */
    public function render_ajax_form_placeholder()
    {
        $class = !empty($this->args['class']) ? ' ' . $this->args['class'] : '';

        echo '<div class="csf csf-widgets csf-fields zib-csf-widget-form-ajax' . esc_attr($class) . '"';
        echo ' data-id-base="' . esc_attr($this->id_base) . '"';
        echo ' data-widget-number="' . esc_attr((string) $this->number) . '"';
        echo '>';
        echo '<div class="zib-csf-widget-form-status">' . esc_html__('配置将在展开或添加模块后加载', 'zib_language') . '</div>';
        echo '</div>';
    }

    /**
     * 输出完整 CSF 字段（同步表单与 AJAX 共用）
     */
    public function render_form_fields($instance)
    {
        $class = !empty($this->args['class']) ? ' ' . $this->args['class'] : '';

        echo '<div class="csf csf-widgets csf-fields' . esc_attr($class) . '">';

        foreach ($this->args['fields'] as $field) {
            $field_unique = '';

            if (!empty($field['id'])) {
                $field_unique = 'widget-' . $this->unique . '[' . $this->number . ']';

                if ($field['id'] === 'title') {
                    if (empty($field['attributes']) || !is_array($field['attributes'])) {
                        $field['attributes'] = array();
                    }
                    $field['attributes']['id'] = 'widget-' . $this->unique . '-' . $this->number . '-title';
                }

                $field['default'] = $this->get_default($field);
            }

            CSF::field($field, $this->get_widget_value($instance, $field), $field_unique);
        }

        echo '</div>';
    }

    /**
     * AJAX：加载小工具完整表单 HTML
     */
    public static function ajax_load_widget_form()
    {
        check_ajax_referer('zib_csf_widget_form', 'nonce');

        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => __('权限不足', 'zib_language')), 403);
        }

        $id_base = isset($_POST['id_base']) ? sanitize_key(wp_unslash($_POST['id_base'])) : '';
        $number  = isset($_POST['widget_number']) ? (int) $_POST['widget_number'] : 0;

        if (!$id_base) {
            wp_send_json_error(array('message' => __('无效的小工具', 'zib_language')));
        }

        if ($number < 1) {
            wp_send_json_error(array('message' => __('无效的小工具实例', 'zib_language')));
        }

        $widget = self::get_widget_for_form($id_base);
        if (!$widget || !$widget instanceof CSF_Widget) {
            wp_send_json_error(array('message' => __('不支持的小工具类型', 'zib_language')));
        }

        $instance = $widget->get_instance($number);

        ob_start();
        $widget->render_form_fields($instance);
        $html = ob_get_clean();

        wp_send_json_success(
            array(
                'html' => $html,
            )
        );
    }

    public function get_instance($number)
    {

        if ($number <= 0) {
            // We echo out a form where 'number' can be set later.
            $this->_set('__i__');
            $instance = array();
        } else {
            $all_instances = $this->get_settings();
            $this->_set($number);
            $instance = $all_instances[$number] ?? array();
        }

        /**
         * Filters the widget instance's settings before displaying the control form.
         *
         * Returning false effectively short-circuits display of the control form.
         *
         * @since 2.8.0
         *
         * @param array     $instance The current widget instance's settings.
         * @param WP_Widget $widget   The current widget instance.
         */
        $instance = apply_filters('widget_form_callback', $instance, $this);

        return $instance;
    }

    /**
     * 自定义器 / 小工具页加载 AJAX 脚本
     */
    public static function enqueue_ajax_form_script()
    {
        if (!is_admin()) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (!$screen || !in_array($screen->id, array('widgets', 'customize'), true)) {
            return;
        }

        wp_enqueue_script(
            'zib-csf-widget-form-ajax',
            ZIB_TEMPLATE_DIRECTORY_URI . '/inc/csf-framework/assets/js/widget.min.js',
            array('jquery', 'csf'),
            THEME_VERSION,
            true
        );

        wp_localize_script(
            'zib-csf-widget-form-ajax',
            'zib_csf_widget_form_var',
            array(
                'loading_text' => esc_attr__('正在加载模块配置…', 'zib_language'),
                'error_text'   => esc_attr__('加载失败，请刷新页面后重试', 'zib_language'),
                'success_text' => esc_attr__('配置将在展开或添加模块后加载', 'zib_language'),
                'nonce'        => wp_create_nonce('zib_csf_widget_form'),
                'ajax_url'     => admin_url('admin-ajax.php'),
            )
        );

    }

    // Sanitize widget form values as they are saved.
    public function update($new_instance, $old_instance)
    {

        // auto sanitize
        foreach ($this->args['fields'] as $field) {
            if (!empty($field['id']) && (!isset($new_instance[$field['id']]) || is_null($new_instance[$field['id']]))) {
                $new_instance[$field['id']] = '';
            }
        }

        $new_instance = apply_filters("csf_{$this->unique}_save", $new_instance, $this->args, $this);

        do_action("csf_{$this->unique}_save_before", $new_instance, $this->args, $this);

        return $new_instance;

    }
}

add_action('wp_ajax_zib_csf_widget_form', array('CSF_Widget', 'ajax_load_widget_form'));
add_action('customize_controls_enqueue_scripts', array('CSF_Widget', 'enqueue_ajax_form_script'), 20);
add_action('admin_enqueue_scripts', array('CSF_Widget', 'enqueue_ajax_form_script'), 20);
