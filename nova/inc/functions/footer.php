<?php
/*
 * Project       : Nova主题
 * Description   : 页脚
 */

if (!defined('ABSPATH')) exit;

/**
 * 输出页脚 HTML
 */
function nova_footer()
{
    $footer_text = nova_get_setting('footer_text');
    $beian       = nova_get_setting('beian_number');

    echo '<footer class="nova-footer">';
    echo '<div class="nova-container">';

    //页脚小工具区
    if (is_active_sidebar('footer_widgets')) {
        echo '<div class="nova-footer-widgets">';
        dynamic_sidebar('footer_widgets');
        echo '</div>';
    }

    echo '<div class="nova-footer-bottom">';
    echo '<div class="nova-footer-copy">';
    if ($footer_text) {
        echo '<span>' . wp_kses_post($footer_text) . '</span>';
    } else {
        echo '<span>Copyright © ' . date('Y') . ' <a href="' . esc_url(home_url()) . '">' . esc_html(get_bloginfo('name')) . '</a> · ' . esc_html__('保留所有权利', 'nova') . '</span>';
    }
    echo '</div>';

    echo '<div class="nova-footer-links">';
    wp_nav_menu(array(
        'theme_location' => 'footer_menu',
        'container'      => false,
        'menu_class'     => 'nova-footer-menu',
        'fallback_cb'    => false,
        'depth'          => 1,
    ));
    if ($beian) {
        echo '<a class="nova-beian" href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow">' . esc_html($beian) . '</a>';
    }
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</footer>';

    //移动端底部导航
    if (nova_is_mobile()) {
        nova_mobile_tabbar();
    }
}

/**
 * 移动端底部导航栏
 */
function nova_mobile_tabbar()
{
    echo '<div class="nova-tabbar">';
    echo '<a href="' . esc_url(home_url()) . '" class="' . (is_home() ? 'active' : '') . '"><i class="nova-icon-home"></i><span>' . esc_html__('首页', 'nova') . '</span></a>';
    if (nova_get_setting('forum_s', '1')) {
        echo '<a href="' . esc_url(nova_forum_url()) . '" class=""><i class="nova-icon-forum"></i><span>' . esc_html__('论坛', 'nova') . '</span></a>';
    }
    if (nova_get_setting('shop_s', '1')) {
        echo '<a href="' . esc_url(nova_shop_url()) . '" class=""><i class="nova-icon-shop"></i><span>' . esc_html__('商城', 'nova') . '</span></a>';
    }
    if (is_user_logged_in()) {
        echo '<a href="' . esc_url(nova_user_center_url()) . '" class=""><i class="nova-icon-user"></i><span>' . esc_html__('我的', 'nova') . '</span></a>';
    } else {
        echo '<a href="' . esc_url(nova_signin_url()) . '" class=""><i class="nova-icon-user"></i><span>' . esc_html__('登录', 'nova') . '</span></a>';
    }
    echo '</div>';
}

/**
 * 论坛地址
 */
function nova_forum_url()
{
    $url = nova_get_setting('forum_page');
    return $url ? $url : home_url('/forum');
}

/**
 * 商城地址
 */
function nova_shop_url()
{
    $url = nova_get_setting('shop_page');
    return $url ? $url : home_url('/shop');
}

/**
 * 回到顶部按钮
 */
function nova_back_top()
{
    echo '<div class="nova-backtop" id="nova-backtop"><i class="nova-icon-top"></i></div>';
}
