<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:38:06
 * @LastEditTime : 2026-05-25 13:10:15
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//开始创建模块

function zib_bbs_widget_ui_plate_info_is_show($show_class, $args, $instance)
{
    $obj = zib_bbs_get_the_plate();
    if (!$obj) {
        return false;
    }

    return $show_class;
}


function zib_bbs_widget_ui_plate_info($args, $instance)
{
    $obj = zib_bbs_get_the_plate();
    if (!$obj) {
        return;
    }
    echo zib_bbs_get_plate_info_mini_box($obj, 'mb20');
}

//版块信息模块

function zib_bbs_widget_ui_plate_moderator_is_show($show_class, $args, $instance)
{
    $obj = zib_bbs_get_the_plate();
    if (!$obj) {
        return false;
    }

    return $show_class;
}

//显示版主列表
function zib_bbs_widget_ui_plate_moderator($args, $instance)
{

    $obj = zib_bbs_get_the_plate();
    if (!$obj) {
        return;
    }

    $moderator_lists = zib_bbs_get_moderator_lists('plate', $obj);
    if ($moderator_lists) {
        $title     = !empty($instance['title_text']) ? '<div class="title-theme">' . $instance['title_text'] . '</div>' : '<div></div>';
        $apply_btn = !empty($instance['apply_btn']) ? zib_bbs_get_apply_moderator_link(0, 'but hollow c-blue p2-10 em09') : '';

        echo '<div class="zib-widget moderator-box">';
        if ($title || $apply_btn) {
            if (!$apply_btn) {
                $apply_btn = zib_bbs_get_edit_plate_moderator_link($obj->ID, 'c-blue em09');
            }
            echo '<div class="mb10 flex jsb ac">' . $title . $apply_btn . '</div>';
        }
        echo $moderator_lists;
        echo '</div>';
    }
}

//版块列表

function zib_bbs_widget_ui_plate_lists($args, $instance)
{

    $widget_id = $args['widget_id'];
    $id_base   = 'zib_bbs_widget_ui_plate_lists';
    $index     = str_replace($id_base . '-', '', $widget_id);

    $placeholder = '<div class="plate-lists"><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div></div>';

    if (isset($instance['style']) && $instance['style'] === 'card') {
        $placeholder = '<div class="panel-plate" style="overflow: hidden;"><div class="flex scroll-plate"><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div></div></div>';
    }

    $ias_args = array(
        'type'   => 'ias',
        'loader' => $placeholder, // 加载动画
        'query'  => array(
            'action' => 'ajax_widget_ui',
            'id'     => $id_base,
            'index'  => $index,
        ),
    );

    echo '<div class="mb20 widget-plate-lists">';
    echo zib_get_ias_ajaxpager($ias_args);
    echo '</div>';
}

function zib_bbs_widget_ui_plate_lists_ajax($instance)
{

    $style = $instance['style'] ? $instance['style'] : 'default';

    $posts_args = array(
        'cat'       => $instance['cat'],
        'filter'    => $instance['filter'],
        'orderby'   => $instance['orderby'],
        'showposts' => $instance['showposts'],
    );

    $lists = '';
    if ('card' == $style) {
        $lists = zib_bbs_get_plate_slide_card($posts_args);
    } else {
        $lists = '<div class="plate-lists">' . zib_bbs_get_plate_main_lists($posts_args) . '</div>';
    }

    if (!$lists && is_super_admin()) {
        $lists = zib_get_ajax_null(__('该模块没有内容显示', 'zib_language'), 20);
    }

    zib_ajax_send_ajaxpager($lists, true);
}

function zib_bbs_widget_create_plate()
{
    global $zib_bbs;

    Zib_CFSwidget::create('zib_bbs_widget_ui_plate_info', array(
        'title'       => sprintf(__('[%1$s]%2$s信息', 'zib_language'), $zib_bbs->forum_name, $zib_bbs->plate_name),
        'size'        => 'mini',
        'zib_affix'   => true,
        'zib_show'    => false,
        'zib_title'   => false,
        'reminder'    => sprintf(__('只能添加到%1$s或%2$s页面的侧边栏', 'zib_language'), $zib_bbs->plate_name, $zib_bbs->posts_name),
        'description' => sprintf(__('显示当前%1$s的%2$s信息，只能添加到%3$s页面的侧边栏', 'zib_language'), $zib_bbs->plate_name, $zib_bbs->plate_name, $zib_bbs->plate_name),
        'fields'      => array(
        ),
    ));
    
    Zib_CFSwidget::create('zib_bbs_widget_ui_plate_moderator', array(
        'title'       => sprintf(__('[%1$s]%2$s管理员列表', 'zib_language'), $zib_bbs->forum_name, $zib_bbs->plate_name),
        'size'        => 'mini',
        'zib_affix'   => true,
        'zib_show'    => true,
        'reminder'    => sprintf(__('只能添加到%1$s或%2$s页面的侧边栏', 'zib_language'), $zib_bbs->plate_name, $zib_bbs->posts_name),
        'description' => sprintf(__('显示当前%1$s的%2$s和%3$s信息，只能添加到%4$s页面的侧边栏', 'zib_language'), $zib_bbs->plate_name, $zib_bbs->plate_author_name, $zib_bbs->plate_moderator_name, $zib_bbs->plate_name),
        'fields'      => array(
            array(
                'title'      => __('标题内容', 'zib_language'),
                'id'         => 'title_text',
                'default'    => $zib_bbs->plate_name . $zib_bbs->plate_moderator_name,
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            ),
            array(
                'title'   => __('申请入口', 'zib_language'),
                'id'      => 'apply_btn',
                'label'   => __('显示申请按钮', 'zib_language'),
                'type'    => 'switcher',
                'default' => true,
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_bbs_widget_ui_plate_lists', array(
        'title'       => sprintf(__('[%1$s]%2$s列表', 'zib_language'), $zib_bbs->forum_name, $zib_bbs->plate_name),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('显示', 'zib_language') . $zib_bbs->plate_name . '列表，支持多种筛选、样式、排序、翻页等功能，可实现多种效果',
        'fields'      => array(
            array(
                'id'          => 'cat',
                'title'       => __('分类筛选', 'zib_language'),
                'desc'        => __('仅显示所选分类的帖子，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy' => 'plate_cat',
                ),
                'placeholder' => __('输入关键词以搜索分类', 'zib_language'),
                'chosen'      => true,
                'multiple'    => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'type'        => 'select',
            ),
            array(
                'title'       => __('其它筛选', 'zib_language'),
                'id'          => 'filter',
                'default'     => '',
                'type'        => 'select',
                'placeholder' => __('不做其它筛选', 'zib_language'),
                'options'     => array(
                    'is_hot' => sprintf(__('热门%s', 'zib_language'), $zib_bbs->plate_name),
                ),
            ),
            array(
                'title'   => __('排序方式', 'zib_language'),
                'id'      => 'orderby',
                'default' => 'date',
                'type'    => 'select',
                'options' => zib_bbs_get_plate_order_options(),
            ),
            array(
                'title'   => __('列表样式', 'zib_language'),
                'id'      => 'style',
                'default' => 'default',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'default' => __('默认列表', 'zib_language'),
                    'card'    => __('单行滚动卡片', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'showposts',
                'class'   => '',
                'default' => 6,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
        ),
    ));
}
add_action('after_setup_theme', 'zib_bbs_widget_create_plate');
