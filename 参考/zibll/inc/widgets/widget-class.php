<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-11-29 21:35:52
 * @LastEditTime : 2026-05-27 12:16:12
 */

class Zib_CFSwidget
{
    /**
     * 此对象已经没有使用，为了兼容旧版插件保留，请使用CSF_Widget::create()
     */
    public static function create($id, $args = array())
    {
        CSF::createWidget($id, $args);
    }

    //可用于是否显示判断
    public static function show_class($instance)
    {
        return CSF_Widget::show_class($instance);
    }

    public static function echo_before($args = array(), $class = 'mb20', $wp_args = array())
    {
        return;
    }

    public static function echo_after($args = array(), $wp_args = array())
    {
        return;
    }

    public static function animation_class($instance = array(), $is_title = false)
    {
        return CSF_Widget::animation_class($instance, $is_title);
    }

    public static function wrap_attributes($args = array())
    {
        return CSF_Widget::wrap_attributes($args);
    }

    public static function show_title($args = array())
    {
        if (empty($args['title'])) {
            return;
        }

        return CSF_Widget::show_title($args);
    }

    public static function args($args = array())
    {
        return $args;
    }
}

function zib_cfswidget_customize_controls_init()
{
    //加载css代码
    wp_enqueue_style('zib-customize-controls', ZIB_TEMPLATE_DIRECTORY_URI . '/css/customize-controls.min.css', array(), THEME_VERSION);
}
add_action('customize_controls_init', 'zib_cfswidget_customize_controls_init');

function zib_cfswidget_dynamic_sidebar_params($params)
{
    // 侧边栏位置 ID（register_sidebar 的 id）
    $id = $params[0]['id'];

    if (strstr($id, 'fluid')) {
        $params[0]['before_widget'] = '<div class="widget-container">' . $params[0]['before_widget'];
        $params[0]['after_widget']  = $params[0]['after_widget'] . '</div>';
    }

    return $params;
}
add_filter('dynamic_sidebar_params', 'zib_cfswidget_dynamic_sidebar_params');
