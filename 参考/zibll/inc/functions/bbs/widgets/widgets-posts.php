<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:38:06
 * @LastEditTime : 2026-05-27 10:32:03
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//主要的帖子输出列表

function zib_bbs_widget_ui_posts_lists_is_show($show_class, $args, $instance)
{
    $current_plate = 0;
    if (!empty($instance['current_plate'])) {
        $current_plate = zib_bbs_get_the_plate_id();
        if (!$current_plate) {
            return false;
        }
    }

    return $show_class;
}

function zib_bbs_widget_ui_posts_lists($args, $instance)
{

    $widget_id   = $args['widget_id'];
    $id_base     = 'zib_bbs_widget_ui_posts_lists';
    $index       = str_replace($id_base . '-', '', $widget_id);
    $alone       = !empty($instance['alone']);
    $style       = !empty($instance['style']) ? $instance['style'] : 'mini';
    $placeholder = 'posts_' . $style;
    $placeholder .= $alone ? '_alone' : '';
    $current_plate = 0;
    if (!empty($instance['current_plate'])) {
        $current_plate = zib_bbs_get_the_plate_id();
        if (!$current_plate) {
            return;
        }
    }

    $ias_args = array(
        'type'   => 'ias',
        'id'     => '',
        'class'  => '',
        'loader' => zib_bbs_get_placeholder($placeholder), // 加载动画
        'query'  => array(
            'action'        => 'ajax_widget_ui',
            'id'            => $id_base,
            'index'         => $index,
            'current_plate' => $current_plate,
        ),
    );

    echo $alone ? '' : '<div class="zib-widget padding-h6">';
    echo zib_get_ias_ajaxpager($ias_args);
    echo $alone ? '' : '</div>';
}

function zib_bbs_widget_ui_posts_lists_ajax($instance)
{

    $paged       = zib_get_the_paged();
    $style       = !empty($instance['style']) ? $instance['style'] : 'mini';
    $alone       = !empty($instance['alone']);
    $lists_class = $alone ? 'alone ajax-item' : 'ajax-item';
    $ajax_url    = zib_get_current_url();
    $paginate    = $instance['paginate'] ?? '';
    $paged_size  = $instance['paged_size'] ?? 12;

    $posts_args = array(
        'plate'         => $instance['include_plate'] ?? '',
        'plate_exclude' => $instance['exclude_plate'] ?? '',
        'topic'         => $instance['include_topic'] ?? '',
        'tag'           => $instance['include_tag'] ?? '',
        'orderby'       => $instance['orderby'] ?? 'date',
        'bbs_type'      => $instance['bbs_type'] ?? '',
        'filter'        => $instance['filter'] ?? [],
        'allow_view'    => $instance['allow_view'] ?? [],
        'paged'         => $paged,
        'paged_size'    => $paged_size,
    );
    $show_topping = $posts_args['filter'] && is_array($posts_args['filter']) && in_array('topping', $posts_args['filter']);

    if (!empty($_REQUEST['current_plate'])) {
        $posts_args['plate'] = $_REQUEST['current_plate'];
    }
    $posts = zib_bbs_get_posts_query($posts_args);

    $lists = '';
    if ($posts->have_posts()) {
        while ($posts->have_posts()): $posts->the_post();
            if ('detail' === $style) {
                $lists .= zib_bbs_get_posts_list('class=' . $lists_class . '&show_topping=' . $show_topping);
            } elseif ('minimalism' === $style) {
            $lists .= '<posts class="forum-posts minimalism ' . $lists_class . '">';
            $lists .= zib_bbs_get_posts_lists_title('forum-title', 'em09', $show_topping, true, false);
            $lists .= '</posts>';
        } else {
            $lists .= zib_bbs_get_posts_mini_list($lists_class, $show_topping);
        }
        endwhile;
        wp_reset_query();
    }
    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null(__('暂无内容', 'zib_language'), 10);
    }
    //帖子分页paginate
    if ('none' !== $paginate) {
        $paginate = zib_bbs_get_paginate($posts->found_posts, $paged, $paged_size, $ajax_url, $paginate, 'close');
        if (!$paginate && 1 == $paged) {
            $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
        } else {
            $lists .= $paginate;
        }
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }
    zib_ajax_send_ajaxpager($lists);
}

//主要的帖子输出列表

function zib_bbs_widget_ui_tab_posts_is_show($show_class, $args, $instance)
{
    if (empty($instance['tabs'])) {
        return false;
    }

    return $show_class;
}

function zib_bbs_widget_ui_tab_posts($args, $instance)
{

    $widget_id = $args['widget_id'];
    $id_base   = 'zib_bbs_widget_ui_tab_posts';
    $index     = str_replace($id_base . '-', '', $widget_id);
    $alone     = !empty($instance['alone']);

    $tabs_con  = '';
    $tabs_nav  = '';
    $tabs_i    = 1;
    $tabs      = $instance['tabs'];
    $ajax_href = add_query_arg(array(
        'action' => 'ajax_widget_ui',
        'id'     => $id_base,
        'index'  => $index,
    ), admin_url('/admin-ajax.php'));

    $alone            = !empty($instance['alone']);
    $style            = !empty($instance['style']) ? $instance['style'] : 'mini';
    $placeholder_type = 'posts_' . $style;
    $placeholder_type .= $alone ? '_alone' : '';
    $placeholder = zib_bbs_get_placeholder($placeholder_type);

    foreach ($instance['tabs'] as $tabs_key => $tabs) {
        if (empty($tabs['title'])) {
            continue;
        }
        $tab_id    = $widget_id . '-' . $tabs_i;
        $nav_class = $tabs_i == 1 ? 'active' : '';
        $con_class = $tabs_i == 1 ? ' active in' : '';

        if (!empty($tabs['current_plate'])) {
            $current_plate = zib_bbs_get_the_plate_id();
            if (!$current_plate) {
                continue;
            }
            $ajax_href = add_query_arg('current_plate', $current_plate, $ajax_href);
        }

        if ($tabs_i == 1) {
            $ias_args = array(
                'type'   => 'ias',
                'loader' => $placeholder, // 加载动画
                'url'    => $ajax_href,
            );

            $con_html = zib_get_ias_ajaxpager($ias_args);
        } else {
            $con_html = '';
            $con_html .= '<span class="post_ajax_trigger hide"><a href="' . add_query_arg('tab', $tabs_key, $ajax_href) . '" class="ajax_load ajax-next ajax-open" no-scroll="true"></a></span>';
            $con_html .= '<div class="post_ajax_loader" style="display: none;">' . $placeholder . '</div>';
        }

        $tabs_nav .= '<li class="' . $nav_class . '"><a' . ($tabs_i !== 1 ? ' data-ajax' : '') . ' data-toggle="tab" href="#' . $tab_id . '">' . $tabs['title'] . '</a></li>';
        $tabs_con .= '<div class="tab-pane fade' . $con_class . '" id="' . $tab_id . '"><div class="ajaxpager' . (!$alone ? ' zib-widget padding-h6' : ' mb20') . '">' . $con_html . '</div></div>';
        $tabs_i++;
    }

    if (!$tabs_nav) {
        return;
    }

    $main_html = '
        <div class="index-tab rectangular relative mb10">
            <ul class="list-inline scroll-x no-scrollbar">
                ' . $tabs_nav . '
            </ul>
        </div>
        <div class="tab-content">
            ' . $tabs_con . '
        </div>';

    //开始输出
    echo '<div class="widget-tab-bbs-posts style-' . $style . '">';
    echo $main_html;
    echo '</div>';
}

function zib_bbs_widget_ui_tab_posts_ajax($instance)
{
    $tab                    = isset($_REQUEST['tab']) ? (int) $_REQUEST['tab'] : 0;
    $tab_args               = isset($instance['tabs'][$tab]) ? $instance['tabs'][$tab] : array();
    $tab_args['style']      = $instance['style'] ?? 'mini';
    $tab_args['alone']      = $instance['alone'] ?? false;
    $tab_args['paginate']   = $instance['paginate'] ?? 'none';
    $tab_args['paged_size'] = $instance['paged_size'] ?? 10;

    zib_bbs_widget_ui_posts_lists_ajax($tab_args);
}
add_action('after_setup_theme', 'zib_bbs_widget_create_posts');

function zib_bbs_widget_create_posts()
{
    global $zib_bbs;

    Zib_CFSwidget::create('zib_bbs_widget_ui_posts_lists', array(
        'title'       => sprintf(__('[%1$s]%2$s列表', 'zib_language'), $zib_bbs->forum_name, $zib_bbs->posts_name),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('显示', 'zib_language') . $zib_bbs->posts_name . '列表，支持多种筛选、样式、排序、翻页等功能，可实现多种效果',
        'fields'      => array(
            array(
                'label'   => __('仅显示当前', 'zib_language') . $zib_bbs->plate_name . '的' . $zib_bbs->posts_name,
                'id'      => 'current_plate',
                'desc'    => __('当此模块放置在', 'zib_language') . $zib_bbs->plate_name . '页面的时候，开启此功能后，则按照当前' . $zib_bbs->plate_name . '进行筛选。可实现本版热门、本版精华等效果<div style="color: #ff6c6c;">开启此功能后，该模块只会在' . $zib_bbs->plate_name . '和' . $zib_bbs->posts_name . '页面显示</div>',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency'  => array('current_plate', '==', '', '', 'visible'),
                'id'          => 'include_plate',
                'title'       => __('包含' . $zib_bbs->plate_name, 'zib_language'),
                'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->plate_name . '的帖子，支持单选、多选。输入版块关键词搜索选择',
                'default'     => '',
                'options'     => 'post',
                'query_args'  => array(
                    'post_type' => 'plate',
                ),
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->plate_name,
                'chosen'      => true,
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'dependency'  => array('include_plate|current_plate', '==|==', '|', '', 'visible'),
                'id'          => 'exclude_plate',
                'title'       => __('排除版块', 'zib_language'),
                'desc'        => __('排除所选版块的帖子，支持单选、多选。输入版块关键词搜索选择', 'zib_language'),
                'default'     => '',
                'options'     => 'post',
                'query_args'  => array(
                    'post_type' => 'plate',
                ),
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'placeholder' => __('输入关键词以搜索版块分类', 'zib_language'),
                'chosen'      => true,
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'id'          => 'include_topic',
                'title'       => __('包含' . $zib_bbs->topic_name, 'zib_language'),
                'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->topic_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy' => 'forum_topic',
                ),
                'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->topic_name,
                'chosen'      => true,
                'multiple'    => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'type'        => 'select',
            ),
            array(
                'id'          => 'include_tag',
                'title'       => __('包含' . $zib_bbs->tag_name, 'zib_language'),
                'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->tag_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
                'default'     => '',
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy' => 'forum_tag',
                ),
                'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->tag_name,
                'chosen'      => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'multiple'    => true,
                'type'        => 'select',
            ),
            array(
                'title'       => __('类型筛选', 'zib_language'),
                'id'          => 'bbs_type',
                'default'     => '',
                'type'        => 'checkbox',
                'placeholder' => __('限制帖子类型，支持单选、多选', 'zib_language'),
                'inline'      => true,
                'options'     => 'zib_bbs_get_posts_type_options',
            ),
            array(
                'title'       => __('阅读权限筛选', 'zib_language'),
                'id'          => 'allow_view',
                'default'     => [],
                'inline'      => true,
                'type'        => 'checkbox',
                'placeholder' => __('不做其它筛选', 'zib_language'),
                'options'     => array(
                    'signin'  => __('登录后查看', 'zib_language'),
                    'comment' => __('评论后查看', 'zib_language'),
                    'pay'     => __('付费查看', 'zib_language'),
                    'points'  => __('支付积分查看', 'zib_language'),
                    'roles'   => __('部分用户可查看', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('其它筛选', 'zib_language'),
                'id'      => 'filter',
                'default' => [],
                'inline'  => true,
                'type'    => 'checkbox',
                'options' => array(
                    'topping'         => __('置顶帖子', 'zib_language'),
                    'vote'            => __('投票帖子', 'zib_language'),
                    'essence'         => __('精华帖子', 'zib_language'),
                    'question_status' => __('提问已解决', 'zib_language'),
                    'is_hot'          => __('热门帖子', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('排序方式', 'zib_language'),
                'id'      => 'orderby',
                'default' => 'date',
                'type'    => 'select',
                'options' => zib_bbs_get_posts_order_options(),
            ),
            array(
                'title'   => __('列表样式', 'zib_language'),
                'id'      => 'style',
                'default' => 'detail',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'detail'     => __('详细内容', 'zib_language'),
                    'mini'       => __('简约风格', 'zib_language'),
                    'minimalism' => __('极简风格', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('列表独立', 'zib_language'),
                'id'      => 'alone',
                'desc'    => __('每一个列表都独立显示为模块', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'paged_size',
                'class'   => '',
                'default' => 10,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'paginate',
                'title'   => __('翻页按钮', 'zib_language'),
                'default' => 'none',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'none'       => __('不允许翻页', 'zib_language'),
                    'ajax_lists' => __('AJAX追加列表翻页', 'zib_language'),
                    'default'    => __('数字翻页按钮', 'zib_language'),
                ),
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_bbs_widget_ui_tab_posts', array(
        'title'       => sprintf(__('[%1$s]多栏目%2$s列表', 'zib_language'), $zib_bbs->forum_name, $zib_bbs->posts_name),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('多个TAB栏目切换显示', 'zib_language') . $zib_bbs->posts_name . '列表，支持多种筛选、排序、样式、翻页等功能',
        'fields'      => array(
            array(
                'title'   => __('列表样式', 'zib_language'),
                'id'      => 'style',
                'default' => 'detail',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'detail'     => __('详细内容', 'zib_language'),
                    'mini'       => __('简约风格', 'zib_language'),
                    'minimalism' => __('极简风格', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('列表独立', 'zib_language'),
                'id'      => 'alone',
                'desc'    => __('每一个列表都独立显示为模块', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'paged_size',
                'class'   => '',
                'default' => 10,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'paginate',
                'title'   => __('翻页按钮', 'zib_language'),
                'default' => 'none',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'none'       => __('不允许翻页', 'zib_language'),
                    'ajax_lists' => __('AJAX追加列表翻页', 'zib_language'),
                    'default'    => __('数字翻页按钮', 'zib_language'),
                ),
            ),
    
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
                        'title' => '最新帖子',
                        'orderby' => 'modified',
                    ),
                    array(
                        'title' => '热门帖子',
                        'orderby' => 'views',
                    ),
                    array(
                        'title' => '精华帖子',
                        'orderby' => 'modified',
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
                    ),
                    array(
                        'label'   => __('仅显示当前', 'zib_language') . $zib_bbs->plate_name . '的' . $zib_bbs->posts_name,
                        'id'      => 'current_plate',
                        'desc'    => __('当此模块放置在', 'zib_language') . $zib_bbs->plate_name . '页面的时候，开启此功能后，则按照当前' . $zib_bbs->plate_name . '进行筛选。可实现本版热门、本版精华等效果<div style="color: #ff6c6c;">开启此功能后，该栏目只会在' . $zib_bbs->plate_name . '和' . $zib_bbs->posts_name . '页面显示</div>',
                        'type'    => 'switcher',
                        'default' => false,
                    ),
                    array(
                        'dependency'  => array('current_plate', '==', '', '', 'visible'),
                        'id'          => 'include_plate',
                        'title'       => __('包含' . $zib_bbs->plate_name, 'zib_language'),
                        'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->plate_name . '的帖子，支持单选、多选。输入版块关键词搜索选择',
                        'default'     => '',
                        'options'     => 'post',
                        'query_args'  => array(
                            'post_type' => 'plate',
                        ),
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->plate_name,
                        'chosen'      => true,
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'dependency'  => array('include_plate|current_plate', '==|==', '|', '', 'visible'),
                        'id'          => 'exclude_plate',
                        'title'       => __('排除版块', 'zib_language'),
                        'desc'        => __('排除所选版块的帖子，支持单选、多选。输入版块关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'post',
                        'query_args'  => array(
                            'post_type' => 'plate',
                        ),
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'placeholder' => __('输入关键词以搜索版块分类', 'zib_language'),
                        'chosen'      => true,
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'id'          => 'include_topic',
                        'title'       => __('包含' . $zib_bbs->topic_name, 'zib_language'),
                        'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->topic_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy' => 'forum_topic',
                        ),
                        'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->topic_name,
                        'chosen'      => true,
                        'multiple'    => true,
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'type'        => 'select',
                    ),
                    array(
                        'id'          => 'include_tag',
                        'title'       => __('包含' . $zib_bbs->tag_name, 'zib_language'),
                        'desc'        => __('仅显示所选', 'zib_language') . $zib_bbs->tag_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy' => 'forum_tag',
                        ),
                        'placeholder' => __('输入关键词以搜索', 'zib_language') . $zib_bbs->tag_name,
                        'chosen'      => true,
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'title'       => __('类型筛选', 'zib_language'),
                        'id'          => 'bbs_type',
                        'default'     => '',
                        'type'        => 'checkbox',
                        'placeholder' => __('限制帖子类型，支持单选、多选', 'zib_language'),
                        'inline'      => true,
                        'options'     => 'zib_bbs_get_posts_type_options',
                    ),
                    array(
                        'title'       => __('阅读权限筛选', 'zib_language'),
                        'id'          => 'allow_view',
                        'default'     => [],
                        'inline'      => true,
                        'type'        => 'checkbox',
                        'placeholder' => __('不做其它筛选', 'zib_language'),
                        'options'     => array(
                            'signin'  => __('登录后查看', 'zib_language'),
                            'comment' => __('评论后查看', 'zib_language'),
                            'pay'     => __('付费查看', 'zib_language'),
                            'points'  => __('支付积分查看', 'zib_language'),
                            'roles'   => __('部分用户可查看', 'zib_language'),
                        ),
                    ),
                    array(
                        'title'   => __('其它筛选', 'zib_language'),
                        'id'      => 'filter',
                        'default' => [],
                        'inline'  => true,
                        'type'    => 'checkbox',
                        'options' => array(
                            'topping'         => __('置顶帖子', 'zib_language'),
                            'vote'            => __('投票帖子', 'zib_language'),
                            'essence'         => __('精华帖子', 'zib_language'),
                            'question_status' => __('提问已解决', 'zib_language'),
                            'is_hot'          => __('热门帖子', 'zib_language'),
                        ),
                    ),
                    array(
                        'title'   => __('排序方式', 'zib_language'),
                        'id'      => 'orderby',
                        'default' => 'date',
                        'type'    => 'select',
                        'options' => zib_bbs_get_posts_order_options(),
                    ),
                ),
            ),
        ),
    ));
}
