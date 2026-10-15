<?php
/*
 * Project       : Nova主题
 * Description   : 用户页面（登录/注册/找回密码/用户中心）
 */

if (!defined('ABSPATH')) exit;

/**
 * 注册虚拟页面路由
 */
function nova_user_routes()
{
    add_rewrite_rule('^signin/?$', 'index.php?nova_page=signin', 'top');
    add_rewrite_rule('^signup/?$', 'index.php?nova_page=signup', 'top');
    add_rewrite_rule('^lost-password/?$', 'index.php?nova_page=lostpwd', 'top');
    add_rewrite_rule('^user/?$', 'index.php?nova_page=user', 'top');
    add_rewrite_rule('^user/([^/]+)/?$', 'index.php?nova_page=user&nova_tab=$matches[1]', 'top');
    add_rewrite_rule('^forum/?$', 'index.php?nova_page=forum', 'top');
    add_rewrite_rule('^forum/([^/]+)/?$', 'index.php?nova_page=forum&nova_tab=$matches[1]', 'top');
    add_rewrite_rule('^shop/?$', 'index.php?nova_page=shop', 'top');
    add_rewrite_rule('^shop/([^/]+)/?$', 'index.php?nova_page=shop&nova_tab=$matches[1]', 'top');
    add_rewrite_rule('^checkout/?$', 'index.php?nova_page=checkout', 'top');
    add_rewrite_rule('^pay/?$', 'index.php?nova_page=pay', 'top');
    add_rewrite_rule('^links/?$', 'index.php?nova_page=links', 'top');
    add_rewrite_rule('^newposts/?$', 'index.php?nova_page=newposts', 'top');
}
add_action('init', 'nova_user_routes');

/**
 * 注册查询变量
 */
function nova_query_vars($vars)
{
    $vars[] = 'nova_page';
    $vars[] = 'nova_tab';
    $vars[] = 'nova_id';
    return $vars;
}
add_filter('query_vars', 'nova_query_vars');

/**
 * 模板路由
 */
function nova_template_include($template)
{
    $nova_page = get_query_var('nova_page');
    if (!$nova_page) return $template;

    $pages = array('signin', 'signup', 'lostpwd', 'user', 'forum', 'shop', 'checkout', 'pay', 'links', 'newposts');
    if (in_array($nova_page, $pages)) {
        status_header(200);
        $file = get_theme_file_path('pages/' . $nova_page . '.php');
        if (file_exists($file)) {
            return $file;
        }
    }
    return $template;
}
add_filter('template_include', 'nova_template_include', 99);

/**
 * 登录页面 HTML
 */
function nova_signin_page_html()
{
    $html = '<div class="nova-auth-page">';
    $html .= '<div class="nova-auth-box card">';
    $html .= '<h2 class="nova-auth-title">' . esc_html__('登录', 'nova') . '</h2>';
    $html .= '<form class="nova-auth-form" id="nova-signin-form">';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('用户名或邮箱', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-user"></i><input type="text" name="username" required placeholder="' . esc_attr__('请输入用户名或邮箱', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('密码', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-lock"></i><input type="password" name="password" required placeholder="' . esc_attr__('请输入密码', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= nova_captcha_html();
    $html .= '<div class="nova-form-actions">';
    $html .= '<label class="nova-checkbox"><input type="checkbox" name="remember" value="1"> ' . esc_html__('记住我', 'nova') . '</label>';
    $html .= '<a class="nova-lostpwd-link" href="' . esc_url(nova_lostpwd_url()) . '">' . esc_html__('忘记密码？', 'nova') . '</a>';
    $html .= '</div>';
    $html .= '<button type="submit" class="btn btn-primary btn-block nova-auth-submit">' . esc_html__('登 录', 'nova') . '</button>';
    $html .= '</form>';
    $html .= nova_oauth_login_buttons();
    if (nova_get_setting('register_s', '1')) {
        $html .= '<div class="nova-auth-switch">' . esc_html__('还没有账号？', 'nova') . ' <a href="' . esc_url(nova_signup_url()) . '">' . esc_html__('立即注册', 'nova') . '</a></div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 注册页面 HTML
 */
function nova_signup_page_html()
{
    $html = '<div class="nova-auth-page">';
    $html .= '<div class="nova-auth-box card">';
    $html .= '<h2 class="nova-auth-title">' . esc_html__('注册账号', 'nova') . '</h2>';
    $html .= '<form class="nova-auth-form" id="nova-signup-form">';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('用户名', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-user"></i><input type="text" name="username" required placeholder="' . esc_attr__('2-20位字母数字或下划线', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('邮箱', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-email"></i><input type="email" name="email" required placeholder="' . esc_attr__('用于找回密码', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('邮箱验证码', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap nova-input-code">';
    $html .= '<i class="nova-icon-code"></i><input type="text" name="email_code" required placeholder="' . esc_attr__('请输入邮箱验证码', 'nova') . '">';
    $html .= '<button type="button" class="nova-code-btn" data-type="email" data-scene="register">' . esc_html__('获取验证码', 'nova') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('密码', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-lock"></i><input type="password" name="password" required placeholder="' . esc_attr__('至少6位密码', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('确认密码', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-lock"></i><input type="password" name="password2" required placeholder="' . esc_attr__('再次输入密码', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= nova_captcha_html();
    $html .= '<button type="submit" class="btn btn-primary btn-block nova-auth-submit">' . esc_html__('注 册', 'nova') . '</button>';
    $html .= '</form>';
    $html .= nova_oauth_login_buttons();
    $html .= '<div class="nova-auth-switch">' . esc_html__('已有账号？', 'nova') . ' <a href="' . esc_url(nova_signin_url()) . '">' . esc_html__('去登录', 'nova') . '</a></div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 找回密码页面 HTML
 */
function nova_lostpwd_page_html()
{
    $html = '<div class="nova-auth-page">';
    $html .= '<div class="nova-auth-box card">';
    $html .= '<h2 class="nova-auth-title">' . esc_html__('找回密码', 'nova') . '</h2>';
    $html .= '<form class="nova-auth-form" id="nova-lostpwd-form">';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('注册邮箱', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-email"></i><input type="email" name="email" required placeholder="' . esc_attr__('请输入注册时使用的邮箱', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('邮箱验证码', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap nova-input-code">';
    $html .= '<i class="nova-icon-code"></i><input type="text" name="email_code" required placeholder="' . esc_attr__('请输入邮箱验证码', 'nova') . '">';
    $html .= '<button type="button" class="nova-code-btn" data-type="email" data-scene="reset">' . esc_html__('获取验证码', 'nova') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="nova-form-group">';
    $html .= '<label>' . esc_html__('新密码', 'nova') . '</label>';
    $html .= '<div class="nova-input-wrap"><i class="nova-icon-lock"></i><input type="password" name="password" required placeholder="' . esc_attr__('设置新密码', 'nova') . '"></div>';
    $html .= '</div>';
    $html .= nova_captcha_html();
    $html .= '<button type="submit" class="btn btn-primary btn-block nova-auth-submit">' . esc_html__('重置密码', 'nova') . '</button>';
    $html .= '</form>';
    $html .= '<div class="nova-auth-switch">' . esc_html__('想起密码了？', 'nova') . ' <a href="' . esc_url(nova_signin_url()) . '">' . esc_html__('去登录', 'nova') . '</a></div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 社交登录按钮
 */
/*
 * 社交登录按钮与回调地址在 oauth/oauth.php 中定义
 */
