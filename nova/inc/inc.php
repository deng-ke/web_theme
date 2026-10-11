<?php
/*
 * Project       : Nova主题
 * Description   : 核心加载文件
 */

if (!defined('ABSPATH')) exit;

//定义常量
define('NOVA_TEMPLATE_DIRECTORY_URI', get_template_directory_uri()); //本主题URI
define('NOVA_ROOT_PATH', dirname(__DIR__) . '/'); //本主题路径
define('NOVA_TEMP_DIR', apply_filters('nova_temp_dir', WP_CONTENT_DIR . '/nova-temp')); //临时文件夹

$theme_data = wp_get_theme();
define('NOVA_VERSION', $theme_data['Version']);

//php版本判断
if (PHP_VERSION_ID < 70000) {
    wp_die(sprintf('PHP 版本过低，请先升级php版本到7.0及以上版本，当前php版本为：%s', PHP_VERSION));
}

/**
 * @description: 封装一个引入函数
 * @param {string|array} $data 文件名或数组
 * @param {bool} $is_once 是否使用 require_once
 * @param {string} $prefix 前缀路径
 */
function nova_require($data, $is_once = false, $prefix = '')
{
    if (is_array($data)) {
        foreach ($data as $d) {
            nova_require($d, $is_once, $prefix);
        }
    } else {
        $file = get_theme_file_path($prefix . $data . '.php');
        if ($is_once) {
            require_once $file;
        } else {
            require $file;
        }
    }
}

//载入文件
nova_require(array(
    'inc/dependent',
    'inc/class/class',
    'inc/options/options',
    'inc/functions/functions',
    'inc/widgets/widget-index',
    'oauth/oauth',
    'novapay/functions',
    'action/function',
), true);

do_action('nova_require_end');
