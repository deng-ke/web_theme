<?php
/*
 * Project       : Nova主题
 * Description   : 主题初始化
 */

if (!defined('ABSPATH')) exit;

/**
 * 主题支持功能
 */
function nova_theme_setup()
{
    //语言
    load_theme_textdomain('nova', get_template_directory() . '/languages');
    //标题
    add_theme_support('title-tag');
    //缩略图
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(300, 200, true);
    add_image_size('nova-card', 640, 400, true);
    add_image_size('nova-thumb', 300, 188, true);
    //摘要
    add_theme_support('post-formats', array('aside', 'image', 'video', 'quote', 'link', 'status'));
    //feed
    add_theme_support('automatic-feed-links');
    //html5
    add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script'));
    //自定义logo
    add_theme_support('custom-logo', array(
        'height'      => 50,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    //编辑器样式
    add_theme_support('editor-styles');
    add_editor_style('css/editor-style.css');
    //注册菜单
    register_nav_menus(array(
        'main_menu'   => __('主导航菜单', 'nova'),
        'top_menu'    => __('顶部菜单', 'nova'),
        'footer_menu' => __('页脚菜单', 'nova'),
    ));
    //内容宽度
    if (!isset($GLOBALS['content_width'])) {
        $GLOBALS['content_width'] = 800;
    }
}
add_action('after_setup_theme', 'nova_theme_setup');

/**
 * 加载主题静态资源
 */
function nova_enqueue_scripts()
{
    $dir_uri = NOVA_TEMPLATE_DIRECTORY_URI;

    //样式
    wp_enqueue_style('nova-icons', $dir_uri . '/css/icons.css', array(), NOVA_VERSION);
    wp_enqueue_style('nova-main', $dir_uri . '/css/main.css', array('nova-icons'), NOVA_VERSION);
    wp_enqueue_style('nova-style', get_stylesheet_uri(), array('nova-main'), NOVA_VERSION);

    //脚本
    wp_enqueue_script('jquery');
    wp_enqueue_script('nova-main', $dir_uri . '/js/main.js', array('jquery'), NOVA_VERSION, true);

    //前端发布页：加载媒体库
    if ('newposts' === get_query_var('nova_page')) {
        wp_enqueue_media();
    }

    //评论回复
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    //本地化
    wp_localize_script('nova-main', 'nova_js', nova_get_js_vars());
}
add_action('wp_enqueue_scripts', 'nova_enqueue_scripts');

/**
 * JS 全局变量
 */
function nova_get_js_vars()
{
    $is_login = is_user_logged_in() ? 1 : 0;
    return array(
        'ajax_url'     => admin_url('admin-ajax.php'),
        'home_url'     => home_url(),
        'theme_uri'    => NOVA_TEMPLATE_DIRECTORY_URI,
        'is_login'     => $is_login,
        'user_id'      => get_current_user_id(),
        'lazy_img'     => nova_get_setting('lazy_img_s', '1') ? 1 : 0,
        'ver'          => NOVA_VERSION,
        'nonce'        => wp_create_nonce('nova_ajax_nonce'),
    );
}

/**
 * 注册小工具区域
 */
function nova_widgets_init()
{
    $areas = array(
        'home_top_fluid'    => __('首页顶部全宽', 'nova'),
        'all_top_fluid'     => __('全站顶部全宽', 'nova'),
        'home_top_content'  => __('首页内容区顶部', 'nova'),
        'home_bottom_content' => __('首页内容区底部', 'nova'),
        'all_bottom_fluid'  => __('全站底部全宽', 'nova'),
        'sidebar'           => __('侧边栏', 'nova'),
        'single_top'        => __('文章详情页顶部', 'nova'),
        'single_bottom'     => __('文章详情页底部', 'nova'),
        'footer_widgets'    => __('页脚小工具', 'nova'),
    );
    foreach ($areas as $id => $name) {
        register_sidebar(array(
            'name'          => $name,
            'id'            => $id,
            'description'   => $name,
            'before_widget' => '<div id="%1$s" class="widget card %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title"><span>',
            'after_title'   => '</span></h3>',
        ));
    }
}
add_action('widgets_init', 'nova_widgets_init');

/**
 * 获取主题分页页码
 */
function nova_get_the_paged()
{
    global $paged;
    $page = $paged ? $paged : 1;
    if (get_query_var('paged')) {
        $page = get_query_var('paged');
    } elseif (get_query_var('page')) {
        $page = get_query_var('page');
    }
    return (int) $page;
}

/**
 * 文章摘要截取
 */
function nova_excerpt($length = 0, $post = null)
{
    $post = get_post($post);
    if (!$post) return '';
    if (has_excerpt($post)) {
        $text = $post->post_excerpt;
    } else {
        $text = $post->post_content;
    }
    $text = wp_strip_all_tags(strip_shortcodes($text));
    $text = preg_replace('/[\r\n\t ]+/', ' ', $text);
    $length = $length ? $length : (int) nova_get_setting('excerpt_length', 100);
    if (mb_strlen($text, 'UTF-8') > $length) {
        $text = mb_substr($text, 0, $length, 'UTF-8') . '…';
    }
    return $text;
}

/**
 * 获取文章卡片分类
 */
function nova_get_post_cat($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();
    $cats = get_the_category($post_id);
    if (empty($cats)) return '';
    return $cats[0];
}

/**
 * 判断是否开启懒加载
 */
function nova_is_lazy()
{
    return nova_get_setting('lazy_img_s', '1') ? true : false;
}

/**
 * 获取头像
 */
function nova_get_user_avatar($user_id = 0, $size = 96)
{
    $user_id = $user_id ?: get_current_user_id();
    $avatar_url = get_user_meta($user_id, 'nova_avatar', true);
    if (!$avatar_url) {
        // 无自定义头像时使用本地默认头像（避免依赖 gravatar 外网服务）
        $avatar_url = nova_default_avatar();
    }
    return $avatar_url;
}

/**
 * 获取用户昵称
 */
function nova_get_user_name($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    $user = get_userdata($user_id);
    if (!$user) return __('游客', 'nova');
    $name = get_user_meta($user_id, 'nova_name', true);
    return $name ? $name : $user->display_name;
}

/**
 * 获取用户等级
 */
function nova_get_user_level($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    $level = (int) get_user_meta($user_id, 'nova_level', true);
    return max(1, $level);
}

/**
 * 获取用户VIP剩余天数
 */
function nova_get_vip_days($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    $vip_end = (int) get_user_meta($user_id, 'nova_vip_end', true);
    if (!$vip_end) return 0;
    $days = ($vip_end - current_time('U')) / 86400;
    return max(0, (int) ceil($days));
}

/**
 * 获取用户签名
 */
function nova_get_user_sign($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    return (string) get_user_meta($user_id, 'nova_sign_text', true);
}

/**
 * 获取主题自定义徽章(认证标识)
 */
function nova_get_user_badge($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    $verify = get_user_meta($user_id, 'nova_verify', true);
    if ($verify) {
        return '<i class="nova-verify-badge" title="' . esc_attr__('已认证用户', 'nova') . '"></i>';
    }
    if (nova_is_vip($user_id)) {
        return '<span class="nova-vip-badge">VIP</span>';
    }
    return '';
}

/**
 * 判断用户是否关注了目标用户
 */
function nova_is_following($target_id, $user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id || !$target_id) return false;
    $follows = (array) get_user_meta($user_id, 'nova_follows', true);
    return in_array($target_id, $follows);
}

/**
 * 获取用户关注数/粉丝数
 */
function nova_get_user_count($user_id, $type = 'follows')
{
    $user_id = $user_id ?: get_current_user_id();
    $list = (array) get_user_meta($user_id, 'nova_' . $type, true);
    return count(array_filter($list));
}

/**
 * 页面标题（兼容SEO）
 */
function nova_get_page_title()
{
    $title = wp_get_document_title();
    if (is_singular()) {
        $seo_title = get_post_meta(get_the_ID(), 'nova_seo_title', true);
        if ($seo_title) $title = $seo_title;
    }
    return $title;
}

/**
 * 允许访问附件页面（禁用 WP 默认跳转到原文件的 canonical 重定向）
 */
function nova_keep_attachment_page($redirect_url, $requested_url)
{
    if (is_attachment()) {
        return false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'nova_keep_attachment_page', 10, 2);

/**
 * 主题激活时记录安装日期（运行天数统计用）
 */
function nova_record_install_date()
{
    if (!get_option('nova_install_date')) {
        update_option('nova_install_date', current_time('Y-m-d H:i:s'));
    }
}
add_action('after_switch_theme', 'nova_record_install_date');
