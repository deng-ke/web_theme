<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2026-06-15 23:55:03
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */
function zib_share()
{
    echo zib_get_share();
}

/**
 * 分享平台显示名称
 *
 * @return array
 */
function zib_get_share_platform_labels()
{
    return array(
        'qzone'    => __('QQ空间', 'zib_language'),
        'weibo'    => __('微博', 'zib_language'),
        'qq'       => __('QQ好友', 'zib_language'),
        'facebook' => 'Facebook',
        'twitter'  => 'X',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'linkedin' => 'LinkedIn',
        'reddit'   => 'Reddit',
        'line'     => 'LINE',
        'vk'       => 'VK',
        'email'    => __('邮件', 'zib_language'),
        'poster'   => __('海报分享', 'zib_language'),
        'copy'     => __('复制链接', 'zib_language'),
    );
}

/**
 * CSF sorter 默认配置
 *
 * @return array
 */
function zib_get_share_items_sorter_default()
{
    $labels = zib_get_share_platform_labels();

    return array(
        'enabled'  => array(
            'qzone'  => $labels['qzone'],
            'weibo'  => $labels['weibo'],
            'qq'     => $labels['qq'],
            'poster' => $labels['poster'],
            'copy'   => $labels['copy'],
        ),
        'disabled' => array(
            'facebook' => $labels['facebook'],
            'twitter'  => $labels['twitter'],
            'telegram' => $labels['telegram'],
            'whatsapp' => $labels['whatsapp'],
            'linkedin' => $labels['linkedin'],
            'reddit'   => $labels['reddit'],
            'line'     => $labels['line'],
            'vk'       => $labels['vk'],
            'email'    => $labels['email'],
        ),
    );
}

/**
 * 拼接分享跳转 URL 查询参数
 *
 * @param string $base
 * @param array  $params
 * @return string
 */
function zib_build_share_query_url($base, $params = array())
{
    if (!$params) {
        return $base;
    }

    $query = array();
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $query[] = rawurlencode($key) . '=' . rawurlencode($value);
    }

    if (!$query) {
        return $base;
    }

    $separator = (false !== strpos($base, '?')) ? '&' : '?';

    return $base . $separator . implode('&', $query);
}

/**
 * 获取已启用的分享平台 key 列表（保持后台排序）
 *
 * @return array
 */
function zib_get_active_share_items()
{
    $items   = _pz('share_items');
    $default = zib_get_share_items_sorter_default();
    $labels  = zib_get_share_platform_labels();
    $enabled = array();

    if (empty($items) || !is_array($items)) {
        $enabled = array_keys($default['enabled']);
    } elseif (isset($items['enabled']) && is_array($items['enabled'])) {
        $enabled = array_keys($items['enabled']);
    } elseif (function_exists('wp_is_numeric_array') ? wp_is_numeric_array($items) : array_values($items) === $items) {
        $enabled = array_values($items);
    } else {
        foreach ($items as $key => $value) {
            if ($value) {
                $enabled[] = $key;
            }
        }
    }

    $enabled = array_values(array_unique(array_filter($enabled, function ($key) use ($labels) {
        return is_string($key) && isset($labels[$key]);
    })));

    if (!_pz('share_img')) {
        $enabled = array_values(array_diff($enabled, array('poster')));
    }

    return apply_filters('zib_active_share_items', $enabled);
}

/**
 * 估算移动端分享弹窗高度
 *
 * @return int
 */
function zib_get_share_modal_height()
{
    $count = max(1, count(zib_get_active_share_items()));

    return min(520, 180 + (int) ceil($count / 5) * 72);
}

/**
 * 构建单个平台的分享跳转地址
 *
 * @param string $key
 * @param array  $ctx
 * @return string
 */
function zib_build_share_platform_href($key, $ctx)
{
    $url   = $ctx['url'];
    $title = $ctx['link_title'];
    $desc  = $ctx['content'];
    $pic   = $ctx['pic'];

    switch ($key) {
        case 'qzone':
            return zib_build_share_query_url('https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey', array(
                'url'     => $url,
                'title'   => $title,
                'pics'    => $pic,
                'summary' => $desc,
            ));
        case 'weibo':
            return zib_build_share_query_url('https://service.weibo.com/share/share.php', array(
                'url'       => $url,
                'title'     => $title,
                'pic'       => $pic,
                'searchPic' => 'false',
            ));
        case 'qq':
            return zib_build_share_query_url('https://connect.qq.com/widget/shareqq/index.html', array(
                'url'   => $url,
                'title' => $title,
                'pics'  => $pic,
                'desc'  => $desc,
            ));
        case 'facebook':
            return zib_build_share_query_url('https://www.facebook.com/sharer/sharer.php', array(
                'u' => $url,
            ));
        case 'twitter':
            return zib_build_share_query_url('https://twitter.com/intent/tweet', array(
                'url'  => $url,
                'text' => $title,
            ));
        case 'telegram':
            return zib_build_share_query_url('https://t.me/share/url', array(
                'url'  => $url,
                'text' => $title,
            ));
        case 'whatsapp':
            return zib_build_share_query_url('https://api.whatsapp.com/send', array(
                'text' => trim($title . ' ' . $url),
            ));
        case 'linkedin':
            return zib_build_share_query_url('https://www.linkedin.com/sharing/share-offsite/', array(
                'url' => $url,
            ));
        case 'reddit':
            return zib_build_share_query_url('https://www.reddit.com/submit', array(
                'url'   => $url,
                'title' => $title,
            ));
        case 'line':
            return zib_build_share_query_url('https://social-plugins.line.me/lineit/share', array(
                'url' => $url,
            ));
        case 'vk':
            return zib_build_share_query_url('https://vk.com/share.php', array(
                'url'   => $url,
                'title' => $title,
                'image' => $pic,
            ));
        case 'email':
            return zib_build_share_query_url('mailto:', array(
                'subject' => $title,
                'body'    => trim($title . "\n" . $url . ($desc ? "\n\n" . $desc : '')),
            ));
        case 'poster':
        case 'copy':
            return 'javascript:;';
    }

    return '';
}

/**
 * 分享平台注册表
 *
 * @param array $ctx
 * @param mixed $poster_id
 * @return array
 */
function zib_get_share_platform_registry($ctx, $poster_id = 0)
{
    $labels   = zib_get_share_platform_labels();
    $registry = array();

    foreach ($labels as $key => $text) {
        $item = array(
            'text'   => $text,
            'icon'   => 'email' === $key ? zib_get_svg('d-email') : zib_get_svg($key . '-color'),
            'href'   => zib_build_share_platform_href($key, $ctx),
            'target' => '_blank',
            'attr'   => '',
        );

        if ('poster' === $key) {
            $item['target'] = '';
            $item['attr']   = 'poster-share="' . esc_attr($poster_id) . '"';
        } elseif ('copy' === $key) {
            $item['target'] = '';
            $item['attr']   = 'data-clipboard-text="' . esc_url($ctx['url']) . '" data-clipboard-tag="' . esc_attr(__('链接', 'zib_language')) . '"';
        } elseif ('email' === $key) {
            $item['target'] = '';
        }

        $registry[$key] = $item;
    }

    if (!_pz('share_img')) {
        unset($registry['poster']);
    }

    return apply_filters('zib_share_platform_registry', $registry, $ctx, $poster_id);
}

/**
 * 获取分类term的分享按钮
 * @param mixed $term 分类term
 * @param string $class
 * @param bool $modal
 * @return string
 */
function zib_get_term_share_btn($term = null, $class = '', $modal = false)
{
    if (!is_object($term)) {
        $term = get_term($term);
    }

    if (empty($term->term_id)) {
        return;
    }

    $is_m = wp_is_mobile();
    $icon = zib_get_svg('share');

    if ($is_m || $modal) {

        $con  = $icon . '<text>' . __('分享', 'zib_language') . '</text>';
        $args = array(
            'tag'           => 'a',
            'class'         => $class,
            'mobile_bottom' => true,
            'height'        => zib_get_share_modal_height(),
            'data_class'    => 'modal-mini',
            'text'          => $con,
            'query_arg'     => array(
                'action' => 'share_modal',
                'id'     => $term->term_id,
                'type'   => 'term',
            ),
        );

        //每次都刷新的modal
        return zib_get_refresh_modal_link($args);
    } else {
        $class = $class ? ' ' . $class : '';

        return '<span class="hover-show dropup' . $class . '">
        ' . $icon . '<text>' . __('分享', 'zib_language') . '</text><div class="zib-widget hover-show-con share-button dropdown-menu">' . zib_get_term_share('', $term) . '</div></span>';
    }
}

//获取文章分享按钮
function zib_get_post_share_btn($post = null, $class = '', $modal = false)
{
    $post = get_post($post);

    $is_m = wp_is_mobile();
    $icon = zib_get_svg('share');
    if ($is_m || $modal) {

        $con  = $icon . '<text>' . __('分享', 'zib_language') . '</text>';
        $args = array(
            'tag'           => 'a',
            'class'         => $class,
            'mobile_bottom' => true,
            'height'        => zib_get_share_modal_height(),
            'data_class'    => 'modal-mini',
            'text'          => $con,
            'query_arg'     => array(
                'action' => 'share_modal',
                'id'     => $post->ID,
                'type'   => 'post',
            ),
        );

        //每次都刷新的modal
        return zib_get_refresh_modal_link($args);
    } else {
        $class = $class ? ' ' . $class : '';

        return '<span class="hover-show dropup' . $class . '">
        ' . $icon . '<text>' . __('分享', 'zib_language') . '</text><div class="zib-widget hover-show-con share-button dropdown-menu">' . zib_get_share('', $post) . '</div></span>';
    }
}

function zib_get_share($class = '', $post = null)
{
    $btns = zib_get_posts_share_btns(zib_get_active_share_items(), $post);
    if (!$btns) {
        return;
    }

    $class = $class ? ' class="' . $class . '"' : '';
    return '<div' . $class . '>' . $btns . '</div>';
}

/**
 * @description: 获取文章分享列表
 * @param {*} $btns_opt
 * @param {*} $post
 * @return {*}
 */
function zib_get_posts_share_btns($btns_opt, $post = null)
{
    $post = get_post($post);

    if (empty($post->ID)) {
        return;
    }

    $subtitle = trim(strip_tags(zib_get_post_meta($post->ID, 'subtitle', true)));

    $title = trim(strip_tags(get_the_title($post))) . $subtitle;

    $desc = zib_get_excerpt(160, '...', $post);
    $pic  = zib_post_thumbnail('full', '', true, $post);

    $url = get_permalink($post);

    return zib_get_share_btns($btns_opt, $title, $desc, $pic, $url, $post->ID);
}

/**
 * @description: 获取分类term的分享
 * @param {*} $class
 * @param {*} $term
 * @return {*}
 */
function zib_get_term_share($class = '', $term = null)
{

    $btns = zib_get_term_share_btns(zib_get_active_share_items(), $term);

    if (!$btns) {
        return;
    }

    $class = $class ? ' class="' . $class . '"' : '';
    return '<div' . $class . '>' . $btns . '</div>';
}

function zib_get_term_share_btns($btns_opt, $term = null)
{
    $term = get_term($term);

    if (empty($term->term_id)) {
        return;
    }

    $title = trim(strip_tags($term->name));

    $desc = zib_str_cut($term->description, 0, 160, '...');
    $pic  = zib_get_taxonomy_img_url($term->term_id, 'full');

    $url = get_term_link($term);

    return zib_get_share_btns($btns_opt, $title, $desc, $pic, $url, 'term_' . $term->term_id);
}

/**
 * @description: 获取分享按钮列表
 * @param {*} $btns_opt
 * @param {*} $title
 * @param {*} $content
 * @param {*} $pic
 * @param {*} $url
 * @param {*} $poster_id
 * @return {*}
 */
function zib_get_share_btns($btns_opt, $title, $content, $pic, $url, $poster_id = 0)
{
    if (!$btns_opt || !is_array($btns_opt)) {
        return;
    }

    if (!$poster_id) {
        $poster_id = get_queried_object_id();
    }

    $link_title = $title . zib_get_delimiter_blog_name();

    //返利链接
    $user_id = get_current_user_id();
    if (_pz('pay_rebate_s') && $user_id) {
        $url = zibpay_get_rebate_link($user_id, $url);
    }

    $ctx = array(
        'url'        => $url,
        'link_title' => $link_title,
        'content'    => $content,
        'pic'        => $pic,
    );

    $args = zib_get_share_platform_registry($ctx, $poster_id);

    $btns = '';

    foreach ($btns_opt as $key) {
        if (!isset($args[$key])) {
            continue;
        }

        $target = $args[$key]['target'] ? '  target="_blank"' : '';
        $attr   = !empty($args[$key]['attr']) ? ' ' . $args[$key]['attr'] : '';
        $href   = $args[$key]['href'] ? $args[$key]['href'] : 'javascript:;';

        if (0 !== strpos($href, 'javascript:')) {
            $href = esc_url($href);
        }

        $btns .= '<a rel="nofollow" class="share-btn ' . esc_attr($key) . '"' . $target . $attr . ' title="' . esc_attr($args[$key]['text']) . '" href="' . $href . '"><icon>' . $args[$key]['icon'] . '</icon><text>' . esc_html($args[$key]['text']) . '<text></a>';
    }

    return $btns;
}
