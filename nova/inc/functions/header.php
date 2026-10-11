<?php
/*
 * Project       : Nova主题
 * Description   : 顶部导航栏
 */

if (!defined('ABSPATH')) exit;

/**
 * 输出顶部导航 HTML
 */
function nova_header()
{
    $is_login = is_user_logged_in();
    $user_id  = get_current_user_id();

    echo '<header class="nova-header" id="nova-header">';
    echo '<div class="nova-container nova-header-inner">';

    //Logo
    echo '<div class="nova-logo">';
    if (has_custom_logo()) {
        the_custom_logo();
    } else {
        $logo_text = nova_get_setting('site_logo_text');
        if ($logo_text) {
            echo '<a class="nova-logo-text" href="' . esc_url(home_url()) . '">' . esc_html($logo_text) . '</a>';
        } else {
            echo '<a class="nova-logo-text" href="' . esc_url(home_url()) . '">' . esc_html(get_bloginfo('name')) . '</a>';
        }
    }
    echo '</div>';

    //导航菜单
    echo '<nav class="nova-nav" id="nova-nav">';
    wp_nav_menu(array(
        'theme_location' => 'main_menu',
        'container'      => false,
        'menu_class'     => 'nova-menu',
        'fallback_cb'    => 'nova_nav_fallback',
        'depth'          => 3,
    ));
    echo '</nav>';

    //右侧：搜索 + 用户
    echo '<div class="nova-header-right">';
    //搜索
    echo '<div class="nova-header-search">';
    echo '<form class="nova-search-form" role="search" method="get" action="' . esc_url(home_url()) . '">';
    echo '<input class="nova-search-input" type="search" name="s" placeholder="' . esc_attr__('搜索…', 'nova') . '" value="' . esc_attr(get_search_query()) . '" autocomplete="off">';
    echo '<button class="nova-search-btn" type="submit"><i class="nova-icon-search"></i></button>';
    echo '</form>';
    echo '</div>';

    if ($is_login) {
        //用户头像
        echo '<div class="nova-user-box">';
        echo '<a class="nova-avatar-sm" href="' . esc_url(nova_user_center_url()) . '">';
        echo '<img src="' . esc_url(nova_get_user_avatar($user_id, 60)) . '" alt="avatar">';
        if (nova_is_vip($user_id)) echo '<span class="nova-vip-badge">VIP</span>';
        echo '</a>';
        echo '<div class="nova-user-menu">';
        echo '<a href="' . esc_url(nova_user_center_url()) . '"><i class="nova-icon-user"></i>' . esc_html__('个人中心', 'nova') . '</a>';
        echo '<a href="' . esc_url(nova_user_center_url('posts')) . '"><i class="nova-icon-post"></i>' . esc_html__('我的文章', 'nova') . '</a>';
        echo '<a href="' . esc_url(nova_user_center_url('msg')) . '"><i class="nova-icon-msg"></i>' . esc_html__('消息中心', 'nova') . '</a>';
        if (nova_get_setting('shop_s', '1')) {
            echo '<a href="' . esc_url(nova_user_center_url('order')) . '"><i class="nova-icon-order"></i>' . esc_html__('我的订单', 'nova') . '</a>';
        }
        if (current_user_can('manage_options')) {
            echo '<a href="' . esc_url(admin_url()) . '"><i class="nova-icon-set"></i>' . esc_html__('后台管理', 'nova') . '</a>';
        }
        echo '<a class="nova-signout-link" href="' . esc_url(wp_logout_url(home_url())) . '"><i class="nova-icon-signout"></i>' . esc_html__('退出登录', 'nova') . '</a>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="nova-login-btns">';
        echo '<a class="btn btn-primary btn-sm nova-signin-btn" href="' . esc_url(nova_signin_url()) . '">' . esc_html__('登录', 'nova') . '</a>';
        if (nova_get_setting('register_s', '1')) {
            echo '<a class="btn btn-outline btn-sm" href="' . esc_url(nova_signup_url()) . '">' . esc_html__('注册', 'nova') . '</a>';
        }
        echo '</div>';
    }

    //移动端菜单按钮
    echo '<button class="nova-nav-toggle" id="nova-nav-toggle"><span></span><span></span><span></span></button>';
    echo '</div>';
    echo '</div>';

    //移动端菜单
    echo '<div class="nova-mobile-nav" id="nova-mobile-nav">';
    wp_nav_menu(array(
        'theme_location' => 'main_menu',
        'container'      => false,
        'menu_class'     => 'nova-menu-mobile',
        'fallback_cb'    => 'nova_nav_fallback',
    ));
    echo '</div>';

    echo '</header>';
}

/**
 * 菜单无设置时的回退
 */
function nova_nav_fallback()
{
    echo '<ul class="nova-menu">';
    echo '<li' . (is_home() ? ' class="current-menu-item"' : '') . '><a href="' . esc_url(home_url()) . '">' . esc_html__('首页', 'nova') . '</a></li>';
    $pages = get_pages(array('number' => 6));
    foreach ($pages as $page) {
        echo '<li><a href="' . esc_url(get_permalink($page)) . '">' . esc_html($page->post_title) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * 获取登录页地址
 */
function nova_signin_url()
{
    $url = nova_get_setting('signin_page');
    if ($url) return $url;
    return home_url('/signin');
}

/**
 * 获取注册页地址
 */
function nova_signup_url()
{
    $url = nova_get_setting('signup_page');
    if ($url) return $url;
    return home_url('/signup');
}

/**
 * 获取用户中心地址
 */
function nova_user_center_url($tab = '')
{
    $url = nova_get_setting('user_page');
    if (!$url) $url = home_url('/user');
    if ($tab) {
        $url = add_query_arg(array('tab' => $tab), $url);
    }
    return $url;
}

/**
 * 获取忘记密码地址
 */
function nova_lostpwd_url()
{
    $url = nova_get_setting('lostpwd_page');
    if ($url) return $url;
    return home_url('/lost-password');
}
