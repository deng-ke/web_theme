<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:38:06
 * @LastEditTime : 2026-05-18 21:13:16
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//话题列表

function zib_bbs_widget_ui_topic_lists($args, $instance)
{

    $widget_id   = $args['widget_id'];
    $id_base     = 'zib_bbs_widget_ui_topic_lists';
    $index       = str_replace($id_base . '-', '', $widget_id);
    $style       = !empty($instance['style']) ? $instance['style'] : 'mini';
    $placeholder = '<div class="flex padding-10"><div class="square-box mr10 placeholder radius4"></div><div class="flex1"><div class="placeholder k1" style="margin-bottom: 3px;height: 18px;"></div><div class="placeholder s1"></div></div></div><div class="flex padding-10"><div class="square-box mr10 placeholder radius4"></div><div class="flex1"><div class="placeholder k1" style="margin-bottom: 3px;height: 18px;"></div><div class="placeholder s1"></div></div></div><div class="flex padding-10"><div class="square-box mr10 placeholder radius4"></div><div class="flex1"><div class="placeholder k1" style="margin-bottom: 3px;height: 18px;"></div><div class="placeholder s1"></div></div></div><div class="flex padding-10"><div class="square-box mr10 placeholder radius4"></div><div class="flex1"><div class="placeholder k1" style="margin-bottom: 3px;height: 18px;"></div><div class="placeholder s1"></div></div></div>';

    $ias_args = array(
        'type'   => 'ias',
        'id'     => '',
        'class'  => '',
        'loader' => $placeholder, // 加载动画
        'query'  => array(
            'action' => 'ajax_widget_ui',
            'id'     => $id_base,
            'index'  => $index,
        ),
    );

    echo '<div class="zib-widget padding-6">';
    echo zib_get_ias_ajaxpager($ias_args);
    echo '</div>';
}

function zib_bbs_widget_ui_topic_lists_ajax($instance)
{

    $style = !empty($instance['style']) ? $instance['style'] : 'mini';

    $query = array(
        'taxonomy'   => array('forum_topic'), //分类法
        'orderby'    => $instance['orderby'], //默认为版块数量
        'hide_empty' => $instance['hide_empty'],
        'number'     => $instance['paged_size'],
        'include'    => $instance['include'],
    );

    $new_query = zib_bbs_get_term_query($query);
    $lists     = '';
    if ($new_query) {
        foreach ($new_query as $term) {
            $lists .= zib_bbs_get_topic_lists($term, $style);
        }
    }

    if (!$lists && is_super_admin()) {
        $lists = zib_get_ajax_null(__('该模块没有内容显示', 'zib_language'), 20);
    }

    zib_ajax_send_ajaxpager($lists, true);
}
add_action('after_setup_theme', 'zib_bbs_widget_create_term');

function zib_bbs_widget_create_term()
{
    global $zib_bbs;

    Zib_CFSwidget::create('zib_bbs_widget_ui_topic_lists', array(
        'title'       => sprintf(__('[%1$s]%2$s列表', 'zib_language'), $zib_bbs->forum_name, $zib_bbs->topic_name),
        'zib_title'   => true,
        'size'        => 'mini',
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('显示', 'zib_language') . $zib_bbs->topic_name . '列表的模块，不同排序方式可实现多种效果',
        'fields'      => array(
            array(
                'id'          => 'include',
                'title'       => __('显示' . $zib_bbs->topic_name, 'zib_language'),
                'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->topic_name . '，留空则按照全部' . $zib_bbs->topic_name . '排序显示，支持单选、多选。输入关键词搜索选择',
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy' => 'forum_topic',
                ),
                'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->topic_name,
                'chosen'      => true,
                'multiple'    => true,
                'ajax'        => true,
                'sortable'    => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'type'        => 'select',
            ),
            array(
                'title'   => __('排序方式', 'zib_language'),
                'id'      => 'orderby',
                'default' => 'date',
                'type'    => "select",
                'options' => array(
                    'name'    => __('名称排序', 'zib_language'),
                    'count'   => sprintf(__('%s数量', 'zib_language'), $zib_bbs->posts_name),
                    'views'   => __('最多查看', 'zib_language'),
                    'include' => __('手动排序(上方选项不为空时才有效)', 'zib_language'),
                ),
            ),
            array(
                'label'   => __('排除', 'zib_language') . $zib_bbs->posts_name . '为空的' . $zib_bbs->topic_name,
                'id'      => 'hide_empty',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'title'   => __('显示数量(0为显示全部)', 'zib_language'),
                'id'      => 'paged_size',
                'class'   => '',
                'default' => 6,
                'max'     => 20,
                'min'     => 0,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
        ),
    ));
}
