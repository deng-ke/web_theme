<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-08 13:51:44
 * @LastEditTime : 2026-05-16 12:58:58
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//引入文件
zib_require(array(
    'posts',
    'plate',
    'term',
    'other',
), false, ZIB_BBS_REQUIRE_URI . 'widgets/widgets-');

//注册小工具位置
function zib_bbs_register_sidebar()
{
    global $zib_bbs;

    $pags = array(
        'home'  => __('首页', 'zib_language'),
        'plate' => sprintf(__('%s页', 'zib_language'), $zib_bbs->plate_name),
        'posts' => sprintf(__('%s页', 'zib_language'), $zib_bbs->posts_name),
    );

    $poss = array(
        'sidebar'        => __('侧边栏', 'zib_language'),
        'top_fluid'      => __('顶部全宽度', 'zib_language'),
        'top_content'    => __('主内容上面', 'zib_language'),
        'bottom_content' => __('主内容下面', 'zib_language'),
        'bottom_fluid'   => __('底部全宽度', 'zib_language'),
    );

    foreach ($pags as $key => $value) {
        foreach ($poss as $poss_key => $poss_value) {
            $sidebars[] = array(
                'name'        => sprintf(__('[%1$s]%2$s-%3$s', 'zib_language'), $zib_bbs->forum_name, $value, $poss_value),
                'id'          => 'bbs_' . $key . '_' . $poss_key,
                'description' => sprintf(__('显示在 %1$s%2$s 的 %3$s 位置', 'zib_language'), $zib_bbs->forum_name, $value, $poss_value) . ($poss_key === 'sidebar' ? __('，由于宽度较小，请勿添加大尺寸模块', 'zib_language') : '') . __('，由于位置较多，建议使用实时预览管理！', 'zib_language'),
            );
        }
    }

    $sidebars[] = array(
        'name'        => sprintf(__('[%1$s]发帖页面—侧边栏顶部', 'zib_language'), $zib_bbs->forum_name),
        'id'          => 'bbs_new_posts_sidebar_top',
        'description' => __('显示在发帖页面的侧边栏顶部，由于宽度较小，请勿添加大尺寸模块，同时会在移动端显示', 'zib_language'),
    );
    $sidebars[] = array(
        'name'        => sprintf(__('[%1$s]发帖页面—侧边栏底部', 'zib_language'), $zib_bbs->forum_name),
        'id'          => 'bbs_new_posts_sidebar_bottom',
        'description' => __('显示在发帖页面的侧边栏底部，由于宽度较小，请勿添加大尺寸模块，同时会在移动端显示', 'zib_language'),
    );

    zib_register_sidebar($sidebars);
}

add_action('widgets_init', 'zib_bbs_register_sidebar');
//添加容器
add_action('bbs_plate_page_content', function () {
    dynamic_sidebar('bbs_plate_top_content');
}, 1);
add_action('bbs_plate_page_content', function () {
    dynamic_sidebar('bbs_plate_bottom_content');
}, 99);
add_action('bbs_home_tab_content_top', function () {
    dynamic_sidebar('bbs_home_top_content');
});
add_action('bbs_home_tab_content_bottom', function () {
    dynamic_sidebar('bbs_home_bottom_content');
});
add_action('bbs_posts_page_content_top', function () {
    dynamic_sidebar('bbs_posts_top_content');
});
add_action('bbs_posts_page_content_bottom', function () {
    dynamic_sidebar('bbs_posts_bottom_content');
});
