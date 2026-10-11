<?php
/*
 * Project       : Nova主题
 * Description   : 前置依赖函数
 */

if (!defined('ABSPATH')) exit;

/**
 * 加载主题翻译文件
 */
function nova_load_theme_textdomain()
{
    load_theme_textdomain('nova', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'nova_load_theme_textdomain');

/**
 * 强制HTTPS判断，兼容CDN SSL设置错误时网站也能访问
 */
function nova_content_url_filter($url)
{
    if (!preg_match('/^https/', $url)) {
        $home_url = home_url();
        if (preg_match('/^https/', $home_url)) {
            $url = str_replace('http', 'https', $url);
        }
    }
    return $url;
}
add_filter('content_url', 'nova_content_url_filter');

/**
 * 获取当前请求的 IP
 */
function nova_get_ip()
{
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = '0.0.0.0';
    }
    //优先获取代理IP（Cloudflare 等）
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip   = trim(end($list));
    }
    return sanitize_text_field(wp_unslash($ip));
}

/**
 * 获取用户浏览设备类型
 * @return string mobile|pc
 */
function nova_get_device()
{
    $is_mobile = false;
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $ua = strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])));
        if (preg_match('/(android|iphone|ipad|ipod|windows phone|mobile)/i', $ua)) {
            $is_mobile = true;
        }
    }
    return $is_mobile ? 'mobile' : 'pc';
}

/**
 * 获取主题设置项（简化版，兼容数组设置）
 */
function nova_get_setting($key = '', $default = '')
{
    global $nova_settings;
    if (!isset($nova_settings)) {
        $nova_settings = (array) get_option('nova_theme_settings', array());
    }
    if ('' === $key) {
        return $nova_settings;
    }
    return isset($nova_settings[$key]) && '' !== $nova_settings[$key] ? $nova_settings[$key] : $default;
}

/**
 * 检查是否为移动端
 */
function nova_is_mobile()
{
    return nova_get_device() === 'mobile';
}

/**
 * 获取图片占位图（懒加载用）
 */
function nova_placeholder_img($type = 'card')
{
    static $cache = array();
    if (isset($cache[$type])) return $cache[$type];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500"><rect width="800" height="500" fill="#f1f2f3"/><g fill="#c9cdd4"><circle cx="320" cy="200" r="60"/><path d="M140 350l110-120 90 90 80-70 150 100z"/></g></svg>';
    $cache[$type] = 'data:image/svg+xml,' . rawurlencode($svg);
    return $cache[$type];
}

/**
 * 获取默认头像
 */
function nova_default_avatar()
{
    return NOVA_TEMPLATE_DIRECTORY_URI . '/img/avatar-default.svg';
}

/**
 * 时间格式化：显示人性化时间
 */
function nova_time_ago($time = null)
{
    if (is_numeric($time)) {
        $time = date('Y-m-d H:i:s', $time);
    }
    if (empty($time)) return '';
    $between = current_time('U') - strtotime($time);
    if ($between < 60) {
        return __('刚刚', 'nova');
    } elseif ($between < 3600) {
        return floor($between / 60) . __('分钟前', 'nova');
    } elseif ($between < 86400) {
        return floor($between / 3600) . __('小时前', 'nova');
    } elseif ($between < 2592000) {
        return floor($between / 86400) . __('天前', 'nova');
    } elseif ($between < 31536000) {
        return floor($between / 2592000) . __('个月前', 'nova');
    }
    return date('Y-m-d', strtotime($time));
}

/**
 * 数字人性化显示
 */
function nova_count_format($num = 0)
{
    if ($num < 1000) return $num;
    if ($num < 10000) return round($num / 1000, 1) . 'k';
    if ($num < 100000000) return round($num / 10000, 1) . 'w';
    return round($num / 100000000, 1) . '亿';
}

/**
 * 获取文章缩略图
 */
function nova_post_thumbnail_url($post = null, $size = 'full')
{
    $post = get_post($post);
    if (!$post) return '';
    if (has_post_thumbnail($post)) {
        $img = get_the_post_thumbnail_url($post, $size);
        if ($img) return $img;
    }
    //文章内第一张图片
    $content = $post->post_content;
    if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $match)) {
        return $match[1];
    }
    return '';
}

/**
 * 文章浏览数
 */
function nova_get_post_views($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();
    if (!$post_id) return 0;
    $views = (int) get_post_meta($post_id, 'nova_views', true);
    return $views;
}

/**
 * 更新浏览数
 */
function nova_update_post_views()
{
    if (!is_singular('post')) return;
    $post_id = get_the_ID();
    $count   = (int) get_post_meta($post_id, 'nova_views', true);
    update_post_meta($post_id, 'nova_views', $count + 1);
}
add_action('wp_head', 'nova_update_post_views', 1);

/**
 * 安全过滤输出
 */
function nova_esc_html_strip($html, $tags = '<p><br><img><a><strong><b><em><i><blockquote><code><pre><ul><ol><li><h1><h2><h3><h4><h5><table><tr><td><th><span><div>')
{
    return wp_kses($html, array('img' => array('src' => true, 'alt' => true, 'class' => true, 'width' => true, 'height' => true)));
}

/**
 * SVG 图标（CSS mask 方案，返回图标元素）
 */
function nova_svg_icon($icon = 'null')
{
    $icon = sanitize_key($icon);
    //图标名映射到 icons.css 中定义的图标类
    $map = array(
        'null'     => 'post',
        'search'   => 'search',
        'user'     => 'user',
        'comment'  => 'comment',
        'like'     => 'like',
        'msg'      => 'msg',
        'order'    => 'order',
        'bag'      => 'bag',
        'points'   => 'points',
        'vip'      => 'vip',
        'wallet'   => 'wallet',
        'notice'   => 'notice',
        'post'     => 'post',
    );
    $class = isset($map[$icon]) ? $map[$icon] : 'post';
    return '<i class="nova-icon-' . $class . '"></i>';
}
