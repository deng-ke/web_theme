<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-12-23 22:31:32
 * @LastEditTime : 2026-08-25 12:10:27
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|前置依赖函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//添加强制HTTPS判断，兼容CDN SSL设置错误时网站也能访问
function zib_content_url_filter($url)
{
    if (!preg_match('/^https/', $url)) {
        $home_url = home_url();
        if (preg_match('/^https/', $home_url)) {
            $url = str_replace('http', 'https', $url);
        }
    }
    return $url;
}
add_filter('content_url', 'zib_content_url_filter');

/**
 * 加载主题翻译文件
 *
 */
function zib_load_theme_textdomain()
{
    load_theme_textdomain('zib_language', get_template_directory() . '/languages');
}
add_action('setup_theme', 'zib_load_theme_textdomain');

/**
 * 是否按后台语言处理当前请求
 *
 * @return bool
 */
function zib_is_admin_context()
{
    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        $action = !empty($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';

        $frontend_prefixes = apply_filters('zib_locale_frontend_ajax_prefixes', array('zib_', 'zibpay_'));
        foreach ($frontend_prefixes as $prefix) {
            if ($prefix !== '' && strpos($action, $prefix) === 0) {
                return false;
            }
        }

        $frontend_actions = apply_filters('zib_locale_frontend_ajax_actions', array(
            'initiate_pay',
            'check_pay',
            'points_initiate_pay',
            'get_gzh_open_id',
        ));
        if ($action && in_array($action, $frontend_actions, true)) {
            return false;
        }

        $referer = function_exists('wp_get_referer') ? wp_get_referer() : '';
        if ($referer && strpos($referer, admin_url()) === 0) {
            return true;
        }

        return false;
    }

    return is_admin();
}

/**
 * 前后台分离语言
 *
 * @param string $locale
 * @return string
 */
function zib_filter_split_locale($locale)
{
    if (!function_exists('_pz') || !_pz('locale_split_s')) {
        return $locale;
    }

    //是否手动切换语言
    if (is_locale_switched()) {
        return $locale;
    }

    if ((defined('WP_CLI') && WP_CLI) || (function_exists('wp_doing_cron') && wp_doing_cron())) {
        return $locale;
    }

    if (zib_is_admin_context()) {
        return _pz('locale_admin', 'zh_CN');
    }

    return _pz('locale_frontend', 'en_US');
}
add_filter('determine_locale', 'zib_filter_split_locale', 10);
add_filter('user_locale', 'zib_filter_split_locale', 10);

/**
 * 手动切换到前台语言
 * @param string $locale
 * @return string
 */
function zib_switch_to_frontend_locale()
{
    //获取当前语言
    $current_locale = get_locale();
    //保存到全局变量
    $GLOBALS['zib_current_locale'] = $current_locale;

    if (!_pz('locale_split_s')) {
        return;
    }

    $locale = _pz('locale_frontend', 'en_US');
    switch_to_locale($locale);
}

/**
 * 恢复到原来的语言
 * @return void
 */
function zib_restore_previous_locale()
{
    if (isset($GLOBALS['zib_current_locale'])) {
        switch_to_locale($GLOBALS['zib_current_locale']);
        unset($GLOBALS['zib_current_locale']);
    }
}
add_action('shutdown', 'zib_restore_previous_locale');

/**
 * @description: 获取模板页面的URL
 * @param {*} $template 模板路径
 * @param {*} $args 模板参数
 * @return {*} URL
 */
function zib_get_template_page_url($template, $args = array())
{
    $cache = wp_cache_get($template, 'page_url', true);
    if ($cache !== false) {
        return $cache;
    }

    $templates = array(
        'pages/newposts.php'  => array(__('发布文章', 'zib_language'), 'newposts'),
        'pages/user-sign.php' => array(__('登录/注册/找回密码', 'zib_language'), 'user-sign'),
        'pages/download.php'  => array(__('资源下载', 'zib_language'), 'download'),
    );
    $templates = array_merge($templates, $args);

    $query_args = array(
        'orderby'                => 'date',
        'order'                  => 'ASC',
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'showposts'              => 1,
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => true,
        'post_type'              => 'page',
        'post_status'            => 'publish',
        'fields'                 => 'ids',
        'meta_query'             => array(
            array(
                'key'   => '_wp_page_template',
                'value' => $template,
            )),
    );
    $query = new WP_Query($query_args);
    $pages = $query->get_posts();

    $page_id = 0;
    if (!empty($pages[0])) {
        $page_id = $pages[0];
    } elseif (!empty($templates[$template][0])) {
        $one_page = array(
            'post_title'  => $templates[$template][0],
            'post_name'   => $templates[$template][1],
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 1,
        );

        $page_id = wp_insert_post($one_page);
        update_post_meta($page_id, '_wp_page_template', $template);
    }
    if ($page_id) {
        $url = get_permalink($page_id);
        wp_cache_set($template, $url, 'page_url');
        return $url;
    } else {
        return false;
    }
}

//获取经验值add的参数
function zib_get_user_integral_add_options()
{
    $options = array(
        'sign_up'         => array(__('首次注册', 'zib_language'), 20, '', __('用户', 'zib_language')),
        'sign_in'         => array(__('每日登录', 'zib_language'), 5, __('每日登录', 'zib_language'), __('用户', 'zib_language')),
        'followed'        => array(__('被关注', 'zib_language'), 5, __('有新的粉丝关注', 'zib_language'), __('用户', 'zib_language')),

        'post_new'        => array(__('发布文章', 'zib_language'), 5, __('发布优质文章并审核通过', 'zib_language'), __('文章', 'zib_language')),
        'post_like'       => array(__('文章获赞', 'zib_language'), 1, __('发布内容获得用户点赞，每篇文章最多加5次', 'zib_language'), __('文章', 'zib_language')),
        'post_favorite'   => array(__('文章被收藏', 'zib_language'), 2, __('发布的内容被用户收藏', 'zib_language'), __('文章', 'zib_language')),
        'comment_new'     => array(__('发表评论', 'zib_language'), 2, __('发表评论并审核通过', 'zib_language'), __('文章', 'zib_language')),
        'comment_like'    => array(__('评论获赞', 'zib_language'), 1, __('发布评论获得用户点赞，每个评论最多加5次', 'zib_language'), __('文章', 'zib_language')),

        'bbs_posts_new'   => array(__('发布帖子', 'zib_language'), 3, __('发布优质帖子并审核通过', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_score_extra' => array(__('帖子被加分', 'zib_language'), 1, __('帖子被加分，每篇帖子最多加5次', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_essence'     => array(__('帖子评为精华', 'zib_language'), 2, __('帖子评为精华', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_posts_hot'   => array(__('帖子成为热门', 'zib_language'), 2, __('帖子成为热门', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_plate_new'   => array(__('创建版块', 'zib_language'), 2, __('创建新版块并审核通过', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_plate_hot'   => array(__('版块成为热门', 'zib_language'), 2, __('创建的版块成为热门版块', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_adopt'       => array(__('回答被采纳', 'zib_language'), 2, __('回答被提问作者采纳', 'zib_language'), __('论坛', 'zib_language')),
        'bbs_comment_hot' => array(__('评论成为神评', 'zib_language'), 2, __('发表的评论成为神评论', 'zib_language'), __('论坛', 'zib_language')),
    );
    return apply_filters('integral_add_options', $options);
}

/**
 * 从 'zibll_options' 数组中检索特定选项的值
 *
 * @param string $name     选项的名称。
 * @param mixed  $default  如果未找到选项，则返回的默认值。
 * @param string $subname  （可选）如果选项是嵌套数组，则为选项的子名称。
 * @return mixed           选项的值，如果未找到则返回默认值。
 */
function _pz($name, $default = false, $subname = '')
{
    // 声明静态变量以加快检索速度
    static $options = null;
    if ($options === null) {
        $options = get_option('zibll_options');
    }

    if (isset($options[$name])) {
        if ($subname) {
            return isset($options[$name][$subname]) ? $options[$name][$subname] : $default;
        } else {
            return $options[$name];
        }
    }
    return $default;
}

//单独设置主题配置参数
function _spz($name, $value)
{
    $get_option        = get_option('zibll_options');
    $get_option        = is_array($get_option) ? $get_option : array();
    $get_option[$name] = $value;
    return update_option('zibll_options', $get_option);
}

/**
 * @description: 获取option or meta数据的key
 * @param {*}
 * @return {*}
 */
function zib_get_option_meta_keys($type)
{
    $keys = array(
        'option'       => array('weixingzh_event_data', 'theme_auto_aut', 'weixingzh_access_token', 'wechatshare_ticket', 'search_keywords', 'update_theme_tasks_completed'),
        'user_meta'    => array('author_addresses', 'shop_addresses', 'favorite_product', 'quick_often', 'vip_level_expired', '_user_points_followed', '_signin_points_time', 'free_points_detail', 'points_record', 'rebate_rule', 'income_rule', 'pay_down_number', 'balance_record', 'pay_down_log', 'user_addr', '_user_integral_followed', '_signin_integral_time', 'level_integral_date_detail', 'level_integral_detail', 'checkin_reward_days', 'checkin_detail', 'banned_log', 'auth_info', 'medal_details', 'qq', 'weixin', 'weibo', 'github', 'url_name', 'address', 'privacy', 'message_shield', 'favorite_forum_posts', 'follow_plate', 'like-comment', 'like-posts', 'favorite-posts', 'rewards_title', 'cover_image', 'cover_image_id', 'custom_avatar', 'custom_avatar_id', 'rewards_wechat_image_id', 'rewards_alipay_image_id', 'rewards_alipay_account', 'rewards_paypal_email', 'follow-user', 'followed-user'),
        'post_meta'    => array('follow_pay_args', 'score_data', '_user_integral_like', '_user_integral_hot', '_user_integral_essence', '_user_integral_score_extra', '_user_integral_favorite', '_user_integral_new', 'is_hot_notify', '_user_points_hot', '_user_points_essence', '_user_points_score_extra', '_user_points_favorite', '_user_points_like', '_user_points_new', 'vote_option', 'pay_hide_part', 'vote_data', 'add_posts_pending_msg', 'add_posts_publish_msg', 'score_detail', 'page_links_submit_dec', 'page_links_submit_cats', 'page_links_submit_sign_s', 'page_links_submit_title', 'page_links_blank_s', 'page_links_nofollow_s', 'page_links_go_s', 'page_links_submit_s', 'page_links_category', 'page_links_style', 'page_links_search_s', 'page_links_search_types', 'page_links_limit', 'page_links_order', 'page_links_orderby', 'page_links_content_position', 'page_links_content_s', 'documentnav_options', 'description', 'keywords', 'title', 'xzh_tui_back', 'layout_bg', 'layout_max_width', 'page_content_style', 'page_header_style', 'article_maxheight_xz', 'no_article-navs', 'show_layout', 'subtitle', 'featured_video_title', 'featured_video_episode', 'featured_slide', 'featured_video', 'cover_image', 'thumbnail_url', 'pay_down_log'),
        'comment_meta' => array('order_data', 'score_data', '_user_points_adopt', '_user_integral_adopt', '_user_integral_like', '_user_integral_hot', '_user_integral_new', 'is_notify', 'is_hot_notify', 'is_adopted_notify', 'comment_addr', '_user_points_hot', '_user_points_like', '_user_points_new'),
        'term_meta'    => array('xzh_tui_back', 'cover_image', 'term_seo'),
    );

    //添加user_meta
    $social_type = array(
        'qq',
        'weixin',
        'weixingzh',
        'weibo',
        'douyin',
        'gitee',
        'baidu',
        'alipay',
        'dingtalk',
        'huawei',
        'xiaomi',
        'github',
        'google',
        'apple',
        'microsoft',
        'facebook',
        'twitter',
    );
    foreach ($social_type as $value) {
        $keys['user_meta'][] = 'oauth_' . $value . '_getUserInfo';
    }

    return $keys[$type];
}

//获取WP—option
function zib_get_option($key)
{
    $option_meta_keys = zib_get_option_meta_keys('option');
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta('option', $key);
    }

    return get_option($key);
}

//设置WP—option
function zib_update_option($key, $value)
{
    $option_meta_keys = zib_get_option_meta_keys('option');
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta('option', $key, $value);
    }

    return update_option($key, $value);
}

//zib - 用户user_meta
function zib_get_user_meta($id, $key, $single = false)
{
    $_type            = 'user_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('user', $id, $key, true);
}

function zib_update_user_meta($id, $key, $value)
{
    $_type            = 'user_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('user', $id, $key, $value);
}

//zib - 文章 post_meta
function zib_get_post_meta($id, $key, $single = false)
{
    $_type            = 'post_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('post', $id, $key, true);
}

function zib_update_post_meta($id, $key, $value)
{
    $_type            = 'post_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('post', $id, $key, $value);
}

//zib - 评论 comment_meta
function zib_get_comment_meta($id, $key, $single = false)
{
    $_type            = 'comment_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('comment', $id, $key, true);
}

function zib_update_comment_meta($id, $key, $value)
{
    $_type            = 'comment_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('comment', $id, $key, $value);
}

//zib - 分类 term_meta
function zib_get_term_meta($id, $key, $single = false)
{
    $_type            = 'term_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('term', $id, $key, true);
}

function zib_update_term_meta($id, $key, $value)
{
    $_type            = 'term_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('term', $id, $key, $value);
}

/**
 * @description:获取 option or meta数据的统一函数
 * @param {*}
 * @param {*}
 * @return {*}
 */
function zib_get_option_meta($type, $key, $meta_id = 0)
{
    $type_args = array(
        'option'       => array('zib_other_options', 'get_option', ''),
        'user_meta'    => array('zib_other_data', 'get_user_meta', ''),
        'post_meta'    => array('zib_other_data', 'get_post_meta', ''),
        'comment_meta' => array('zib_other_data', 'get_comment_meta', ''),
        'term_meta'    => array('zib_other_data', 'get_term_meta', ''),
    );

    $options_key = $type_args[$type][0];
    $fun         = $type_args[$type][1];

    //先查询缓存
    $option_meta_data = wp_cache_get($type . ($meta_id ? '_' . $meta_id : ''), 'zib_option_meta_data');

    if (false === $option_meta_data) {
        if ($type === 'option') {
            $option_meta_data = (array) call_user_func($fun, $options_key);
        } else {
            $option_meta_data = (array) call_user_func($fun, $meta_id, $options_key, true);
        }

        wp_cache_set($type . ($meta_id ? '_' . $meta_id : ''), $option_meta_data, 'zib_option_meta_data');
    }

    return isset($option_meta_data[$key]) ? $option_meta_data[$key] : false;
}

/**
 * @description: 设置option or meta数据的统一函数
 * @param {*}
 * @return {*}
 */
function zib_update_option_meta($type, $key, $value, $meta_id = 0)
{
    $type_args = array(
        'option'       => array('zib_other_options', 'get_option', 'update_option'),
        'user_meta'    => array('zib_other_data', 'get_user_meta', 'update_user_meta'),
        'post_meta'    => array('zib_other_data', 'get_post_meta', 'update_post_meta'),
        'comment_meta' => array('zib_other_data', 'get_comment_meta', 'update_comment_meta'),
        'term_meta'    => array('zib_other_data', 'get_term_meta', 'update_term_meta'),
    );

    $options_key = $type_args[$type][0];
    $get_fun     = $type_args[$type][1];
    $set_fun     = $type_args[$type][2];

    if ($type === 'option') {
        $option_meta_data       = (array) call_user_func($get_fun, $options_key);
        $option_meta_data[$key] = $value;
        $set                    = call_user_func($set_fun, $options_key, $option_meta_data);
    } else {
        $option_meta_data       = (array) call_user_func($get_fun, $meta_id, $options_key, true);
        $option_meta_data[$key] = $value;
        $set                    = call_user_func($set_fun, $meta_id, $options_key, $option_meta_data);
    }

    //更新缓存
    wp_cache_set($type . ($meta_id ? '_' . $meta_id : ''), $option_meta_data, 'zib_option_meta_data');

    return $set;
}

//获取一个随机数
function zib_get_mt_rand_number($var)
{
    $defaults = array(
        'max' => 0,
        'min' => 0,
    );
    $var = wp_parse_args((array) $var, $defaults);

    // 添加参数验证 - 仅需这5行代码
    $min = (int) $var['min'];
    $max = (int) $var['max'];
    if ($min > $max) {
        list($min, $max) = array($max, $min);
    }

    return @mt_rand($min, $max);
}

function zib_get_csf_option_new_badge()
{
    return array(
        '7.0' => '',
        '7.1' => '',
        '7.2' => '',
        '7.3' => '',
        '7.4' => '',
        '7.5' => '',
        '7.6' => '',
        '7.7' => '',
        '7.8' => '',
        '7.9' => '<badge style="background: #ff876b;">V7.9</badge>',
        '8.0' => '<badge style="background: #ff876b;">V8.0</badge>',
        '8.1' => '<badge style="background: #ff876b;">V8.1</badge>',
        '8.2' => '<badge style="background: #ff876b;">V8.2</badge>',
        '8.3' => '<badge style="background: #ff876b;">V8.3</badge>',
        '8.4' => '<badge style="background: #ff876b;">V8.4</badge>',
        '8.5' => '<badge style="background: #ff876b;">V8.5</badge>',
        '8.6' => '<badge>NEW</badge>',
        '8.7' => '<badge>NEW</badge>',
        '8.8' => '<badge>NEW</badge>',
        '8.9' => '<badge>NEW</badge>',
        '9.0' => '<badge>NEW</badge>',
        '9.1' => '<badge>NEW</badge>',
        '9.2' => '<badge>NEW</badge>',
        '9.3' => '<badge>NEW</badge>',
        '9.4' => '<badge>NEW</badge>',
        '9.5' => '<badge>NEW</badge>',
        '9.6' => '<badge>NEW</badge>',
        '9.7' => '<badge>NEW</badge>',
        '9.8' => '<badge>NEW</badge>',
        '9.9' => '<badge>NEW</badge>',
    );
}
