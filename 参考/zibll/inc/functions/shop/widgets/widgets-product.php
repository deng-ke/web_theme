<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-02-16 22:11:42
 * @LastEditTime : 2026-05-18 21:48:21
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | 商城系统 | 商品小工具
 * Copyright (c) 2025 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//商品列表
add_action('after_setup_theme', 'zib_shop_widget_create_product');
function zib_shop_widget_create_product()
{

    //引入依赖函数
    zib_require(ZIB_SHOP_REQUIRE_URI . 'admin/options/option-module', true);

    Zib_CFSwidget::create('zib_shop_widget_ui_product_lists', array(
        'title'       => __('[商城]商品列表', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('显示商品列表，支持多种筛选、样式、排序、翻页等功能，可实现多种效果', 'zib_language'),
        'fields'      => array(
            array(
                'id'          => 'exclude_cat',
                'title'       => __('排除分类', 'zib_language'),
                'desc'        => __('排除所选分类的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_cat',
                    
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
                'dependency'  => array('exclude_cat', '==', '', '', 'visible'),
                'id'          => 'include_cat',
                'title'       => __('包含分类', 'zib_language'),
                'desc'        => __('仅显示所选分类的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_cat',
                    
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
                'id'          => 'include_dis',
                'title'       => __('包含优惠活动', 'zib_language'),
                'desc'        => __('仅显示所选优惠活动的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_discount',
                    
                ),
                'placeholder' => __('输入关键词以搜索优惠活动', 'zib_language'),
                'chosen'      => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'id'          => 'include_tag',
                'title'       => __('包含特色标签', 'zib_language'),
                'desc'        => __('仅显示所选特色标签的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_tag',
                    
                ),
                'placeholder' => __('输入关键词以搜索特色标签', 'zib_language'),
                'chosen'      => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'title'   => __('排序方式', 'zib_language'),
                'id'      => 'orderby',
                'options' => zib_shop_csf_module::product_orderby_options(),
                'type'    => 'select',
                'default' => 'views',
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'count',
                'class'   => '',
                'default' => 12,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'paginate',
                'title'   => __('翻页按钮', 'zib_language'),
                'default' => '',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    ''       => __('不翻页', 'zib_language'),
                    'ajax'   => __('AJAX追加列表翻页', 'zib_language'),
                    'number' => __('数字翻页按钮', 'zib_language'),
                ),
            ),
            zib_shop_csf_module::list_style(),
        ),
    ));

    Zib_CFSwidget::create('zib_shop_widget_ui_oneline_product_lists', array(
        'title'       => __('[商城]单行商品列表', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('显示商品列表，单行显示，自动左右横向滚动', 'zib_language'),
        'fields'      => array(
            array(
                'id'          => 'exclude_cat',
                'title'       => __('排除分类', 'zib_language'),
                'desc'        => __('排除所选分类的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_cat',
                    
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
                'dependency'  => array('exclude_cat', '==', '', '', 'visible'),
                'id'          => 'include_cat',
                'title'       => __('包含分类', 'zib_language'),
                'desc'        => __('仅显示所选分类的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_cat',
                    
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
                'id'          => 'include_dis',
                'title'       => __('包含优惠活动', 'zib_language'),
                'desc'        => __('仅显示所选优惠活动的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_discount',
                    
                ),
                'placeholder' => __('输入关键词以搜索优惠活动', 'zib_language'),
                'chosen'      => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'id'          => 'include_tag',
                'title'       => __('包含特色标签', 'zib_language'),
                'desc'        => __('仅显示所选特色标签的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy'   => 'shop_tag',
                    
                ),
                'placeholder' => __('输入关键词以搜索特色标签', 'zib_language'),
                'chosen'      => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'title'   => __('排序方式', 'zib_language'),
                'id'      => 'orderby',
                'options' => zib_shop_csf_module::product_orderby_options(),
                'type'    => 'select',
                'default' => 'views',
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'count',
                'class'   => '',
                'default' => 12,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            zib_shop_csf_module::list_style(),
        ),
    ));

//主要的帖子输出列表
    Zib_CFSwidget::create('zib_shop_widget_ui_tab_product', array(
        'title'       => __('[商城]多栏目商品列表', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('多个TAB栏目切换显示商品列表，支持多种筛选、排序、样式、翻页等功能', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'count',
                'class'   => '',
                'default' => 12,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'paginate',
                'title'   => __('翻页按钮', 'zib_language'),
                'default' => '',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    ''       => __('不翻页', 'zib_language'),
                    'ajax'   => __('AJAX追加列表翻页', 'zib_language'),
                    'number' => __('数字翻页按钮', 'zib_language'),
                ),
            ),
            zib_shop_csf_module::list_style(),
            array(
                'id'                     => 'tabs',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'button_title'           => __('添加栏目', 'zib_language'),
                'sanitize'               => false,
                'title'                  => __('栏目', 'zib_language'),
                'default'                => array(
                    array(
                        'title' => '热门推荐',
                        'orderby' => 'views',
                    ),
                    array(
                        'title' => '最新上架',
                        'orderby' => 'date',
                    ),
                    array(
                        'title' => '最热销量',
                        'orderby' => 'sales_volume',
                    ),
                ),
                'fields'                 => array(
                    array(
                        'id'         => 'title',
                        'title'      => __('标题（必填）', 'zib_language'),
                        'desc'       => __('栏目显示的标题，支持HTML代码，注意代码规范', 'zib_language'),
                        'attributes' => array(
                            'rows' => 1,
                        ),
                        'sanitize'   => false,
                        'type'       => 'textarea',
                    ), array(
                        'id'          => 'exclude_cat',
                        'title'       => __('排除分类', 'zib_language'),
                        'desc'        => __('排除所选分类的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy'   => 'shop_cat',
                            
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
                        'dependency'  => array('exclude_cat', '==', '', '', 'visible'),
                        'id'          => 'include_cat',
                        'title'       => __('包含分类', 'zib_language'),
                        'desc'        => __('仅显示所选分类的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy'   => 'shop_cat',
                            
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
                        'id'          => 'include_dis',
                        'title'       => __('包含优惠活动', 'zib_language'),
                        'desc'        => __('仅显示所选优惠活动的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy'   => 'shop_discount',
                            
                        ),
                        'placeholder' => __('输入关键词以搜索优惠活动', 'zib_language'),
                        'chosen'      => true,
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'id'          => 'include_tag',
                        'title'       => __('包含特色标签', 'zib_language'),
                        'desc'        => __('仅显示所选特色标签的商品，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy' => 'shop_tag',
                        ),
                        'placeholder' => __('输入关键词以搜索特色标签', 'zib_language'),
                        'chosen'      => true,
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'title'   => __('排序方式', 'zib_language'),
                        'id'      => 'orderby',
                        'options' => zib_shop_csf_module::product_orderby_options(),
                        'type'    => 'select',
                        'default' => 'views',
                    ),
                ),
            ),
        ),
    ));

}

function zib_shop_widget_ui_product_lists($args, $instance)
{

    $widget_id = $args['widget_id'];
    $id_base   = 'zib_shop_widget_ui_product_lists';
    $index     = str_replace($id_base . '-', '', $widget_id);

    $list_style = $instance['list_style'] ?? [];

    $placeholder_i = wp_is_mobile() ? 4 : ($instance['count'] ?? 8);
    $ias_args      = array(
        'type'   => 'ias',
        'id'     => '',
        'class'  => 'product-lists-row',
        'loader' => zib_shop_get_lists_card_placeholder($list_style, $placeholder_i), // 加载动画
        'query'  => array(
            'action' => 'ajax_widget_ui',
            'id'     => $id_base,
            'index'  => $index,
        ),
    );

    echo '<div class="mb10">';
    echo zib_get_ias_ajaxpager($ias_args);
    echo '</div>';
}

function zib_shop_widget_ui_product_lists_ajax($instance)
{

    $paged          = zib_get_the_paged();
    $ajax_url       = zib_get_current_url();
    $orderby        = $instance['orderby'] ?? 'views';
    $count          = $instance['count'] ?? 12;
    $paginate       = $instance['paginate'] ?? false;
    $list_card_args = $instance['list_style'] ?? [];

    $tax_query = array();
    if (!empty($instance['exclude_cat'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_cat',
            'field'    => 'term_id',
            'terms'    => $instance['exclude_cat'],
            'operator' => 'NOT IN',
        );
    } elseif (!empty($instance['include_cat'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_cat',
            'field'    => 'term_id',
            'terms'    => $instance['include_cat'],
        );
    }

    if (!empty($instance['include_dis'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_discount',
            'field'    => 'term_id',
            'terms'    => $instance['include_dis'],
        );
    }

    if (!empty($instance['include_tag'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_tag',
            'field'    => 'term_id',
            'terms'    => $instance['include_tag'],
        );
    }

    $query_args = array(
        'ignore_sticky_posts' => 1, //忽略置顶
        'post_type'           => 'shop_product',
        'post_status'         => 'publish',
        'tax_query'           => $tax_query,
        'posts_per_page'      => $count,
    );
    //排序
    $query_args = zib_query_orderby_filter($orderby, $query_args);

    //分页
    if ($paginate) {
        $query_args['paged'] = $paged;
    } else {
        $query_args['no_found_rows'] = true; //不查询分页需要的总数量
    }

    $query = new WP_Query($query_args);
    $lists = '';
    while ($query->have_posts()) {
        $query->the_post();
        $lists .= zib_shop_get_product_list_card($list_card_args);
    }
    wp_reset_query();

    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null('暂无内容', 10);
    }

    //分页paginate
    if ($paginate === 'ajax') {
        $lists .= zib_get_ajax_next_paginate($query->found_posts, $paged, $count, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no');
    } elseif ($paginate === 'number') {
        $lists .= zib_get_ajax_number_paginate($query->found_posts, $paged, $count, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'paged');
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    zib_ajax_send_ajaxpager($lists, false);
}

function zib_shop_widget_ui_tab_product_is_show($show_class, $args, $instance)
{
    if (empty($instance['tabs'])) {
        return false;
    }

    return $show_class;
}

function zib_shop_widget_ui_tab_product($args, $instance)
{

    $widget_id = $args['widget_id'];
    $id_base   = 'zib_shop_widget_ui_tab_product';
    $index     = str_replace($id_base . '-', '', $widget_id);

    $tabs_con  = '';
    $tabs_nav  = '';
    $tabs_i    = 1;
    $tabs      = $instance['tabs'];
    $ajax_href = add_query_arg(array(
        'action' => 'ajax_widget_ui',
        'id'     => $id_base,
        'index'  => $index,
    ), admin_url('/admin-ajax.php'));

    $list_style    = $instance['list_style'] ?? [];
    $placeholder_i = wp_is_mobile() ? 4 : ($instance['count'] ?? 8);
    $placeholder   = zib_shop_get_lists_card_placeholder($list_style, $placeholder_i);

    foreach ($instance['tabs'] as $tabs_key => $tabs) {
        if (empty($tabs['title'])) {
            continue;
        }
        $tab_id    = $widget_id . '-' . $tabs_i;
        $nav_class = $tabs_i == 1 ? 'active' : '';
        $con_class = $tabs_i == 1 ? ' active in' : '';

        if ($tabs_i == 1) {
            $ias_args = array(
                'type'   => 'ias',
                'class'  => 'product-lists-row',
                'loader' => $placeholder, // 加载动画
                'url'    => $ajax_href,
            );

            $con_html = zib_get_ias_ajaxpager($ias_args);
        } else {
            $con_html = '';
            $con_html .= '<span class="post_ajax_trigger hide"><a href="' . add_query_arg('tab', $tabs_key, $ajax_href) . '" class="ajax_load ajax-next ajax-open" no-scroll="true"></a></span>';
            $con_html .= '<div class="post_ajax_loader" style="display: none;">' . $placeholder . '</div>';
            $con_html = '<div class="ajaxpager product-lists-row">' . $con_html . '</div>';
        }

        $tabs_nav .= '<li class="' . $nav_class . '"><a' . ($tabs_i !== 1 ? ' data-ajax' : '') . ' data-toggle="tab" href="#' . $tab_id . '">' . $tabs['title'] . '</a></li>';
        $tabs_con .= '<div class="tab-pane fade' . $con_class . '" id="' . $tab_id . '">' . $con_html . '</div>';
        $tabs_i++;
    }

    if (!$tabs_nav) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . __('[商城]TAB栏目商品列表模块：', 'zib_language') . '</b>' . __('当前配置下没有可显示的内容，请正确配置栏目标题', 'zib_language') . '</div>';
        }

        return;
    }

    $main_html = '
        <div class="index-tab rectangular relative mb15">
            <ul class="list-inline scroll-x no-scrollbar">
                ' . $tabs_nav . '
            </ul>
        </div>
        <div class="tab-content">
            ' . $tabs_con . '
        </div>';

    //开始输出
    echo '<div class="widget-tab-product">';
    echo $main_html;
    echo '</div>';
}

function zib_shop_widget_ui_tab_product_ajax($instance)
{
    $tab                    = isset($_REQUEST['tab']) ? (int) $_REQUEST['tab'] : 0;
    $tab_args               = isset($instance['tabs'][$tab]) ? $instance['tabs'][$tab] : array();
    $tab_args['count']      = $instance['count'] ?? 12;
    $tab_args['paginate']   = $instance['paginate'] ?? false;
    $tab_args['list_style'] = $instance['list_style'] ?? [];

    zib_shop_widget_ui_product_lists_ajax($tab_args);
}

function zib_shop_widget_ui_oneline_product_lists($args, $instance)
{

    $orderby        = $instance['orderby'] ?? 'views';
    $count          = $instance['count'] ?? 12;
    $list_card_args = $instance['list_style'] ?? [];

    $tax_query = array();
    if (!empty($instance['exclude_cat'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_cat',
            'field'    => 'term_id',
            'terms'    => $instance['exclude_cat'],
            'operator' => 'NOT IN',
        );
    } elseif (!empty($instance['include_cat'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_cat',
            'field'    => 'term_id',
            'terms'    => $instance['include_cat'],
        );
    }

    if (!empty($instance['include_dis'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_discount',
            'field'    => 'term_id',
            'terms'    => $instance['include_dis'],
        );
    }

    if (!empty($instance['include_tag'])) {
        $tax_query[] = array(
            'taxonomy' => 'shop_tag',
            'field'    => 'term_id',
            'terms'    => $instance['include_tag'],
        );
    }

    $query_args = array(
        'ignore_sticky_posts' => 1, //忽略置顶
        'post_type'           => 'shop_product',
        'post_status'         => 'publish',
        'tax_query'           => $tax_query,
        'posts_per_page'      => $count,
        'no_found_rows'       => true,
    );

    $list_card_args['class'] = 'swiper-slide';
    //排序
    $query_args = zib_query_orderby_filter($orderby, $query_args);
    $query      = new WP_Query($query_args);
    $lists      = '';
    while ($query->have_posts()) {
        $query->the_post();
        $lists .= zib_shop_get_product_list_card($list_card_args);
    }
    wp_reset_query();

    if (!$lists) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . __('[商城]单行商品列表模块：', 'zib_language') . '</b>' . __('当前配置下没有可显示的内容', 'zib_language') . '</div>';
        }
        return;
    }

    $html = '<div class="swiper-container swiper-scroll mb20">
                <div class="swiper-wrapper swiper-wrapper-product-lists">
                    ' . $lists . '
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>';

    echo $html;
}
