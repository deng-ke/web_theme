<?php
/*
 * Project       : Nova主题
 * Description   : 核心功能函数入口
 */

if (!defined('ABSPATH')) exit;

nova_require(array(
    'theme',
    'head',
    'header',
    'footer',
    'index',
    'category',
    'author',
    'post',
    'posts-list',
    'share',
    'search',
    'user/user',
    'user/user-center',
    'user',
    'page',
    'single',
    'comments-list',
    'captcha',
    'email',
    'ajax',
    'message/functions',
    'bbs/bbs',
    'shop/shop',
    'widget',
), false, 'inc/functions/');

if (is_admin()) {
    nova_require(array(
        'admin/admin-main',
    ), false, 'inc/functions/');
}
