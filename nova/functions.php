<?php
/*
 * Project       : Nova主题
 * Description   : 一款功能全面的现代化 Wordpress 主题
 */

if (!defined('ABSPATH')) exit;

require_once get_theme_file_path('/inc/inc.php');

/**
 * 如果您需要添加一些自定义的PHP代码
 * 您可以在当前目录下新建一个 func.php 的文件，然后在最顶部写上 <?php ，再写入您的php代码
 * 主题会自动判断文件进行引入
 */
if (file_exists(get_theme_file_path('/func.php'))) {
    require_once get_theme_file_path('/func.php');
}
