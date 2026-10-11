<?php
/*
 * Project       : Nova主题
 * Description   : 文章分享功能
 */

if (!defined('ABSPATH')) exit;

/**
 * 获取分享链接列表
 */
function nova_get_share_links($post_id = 0)
{
    $post_id = $post_id ?: get_the_ID();
    $url   = get_permalink($post_id);
    $title = get_the_title($post_id);
    $thumb = nova_post_thumbnail_url($post_id);

    $links = array(
        'weibo'   => array(
            'name' => __('微博', 'nova'),
            'icon' => 'nova-icon-weibo',
            'url'  => 'https://service.weibo.com/share/share.php?url=' . urlencode($url) . '&title=' . urlencode($title) . '&pic=' . urlencode($thumb),
        ),
        'qq'      => array(
            'name' => __('QQ', 'nova'),
            'icon' => 'nova-icon-qq',
            'url'  => 'https://connect.qq.com/widget/shareqq/index.html?url=' . urlencode($url) . '&title=' . urlencode($title),
        ),
        'qzone'   => array(
            'name' => __('QQ空间', 'nova'),
            'icon' => 'nova-icon-qzone',
            'url'  => 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . urlencode($url) . '&title=' . urlencode($title),
        ),
        'wechat'  => array(
            'name' => __('微信', 'nova'),
            'icon' => 'nova-icon-wechat',
            'url'  => '',
            'type' => 'qrcode',
        ),
        'copy'    => array(
            'name' => __('复制链接', 'nova'),
            'icon' => 'nova-icon-copy',
            'url'  => '',
            'type' => 'copy',
        ),
    );
    return apply_filters('nova_share_links', $links, $post_id);
}

/**
 * 分享弹窗内容
 */
function nova_share_box($post_id = 0)
{
    $post_id = $post_id ?: get_the_ID();
    $links = nova_get_share_links($post_id);
    $url   = get_permalink($post_id);

    $html = '<div class="nova-share-box">';
    foreach ($links as $key => $link) {
        if ('wechat' === $key) {
            $html .= '<div class="nova-share-item" data-type="qrcode" data-url="' . esc_url($url) . '">';
            $html .= '<span class="nova-share-icon ' . esc_attr($link['icon']) . '"></span>';
            $html .= '<span>' . esc_html($link['name']) . '</span>';
            $html .= '</div>';
        } elseif ('copy' === $key) {
            $html .= '<div class="nova-share-item" data-type="copy" data-url="' . esc_url($url) . '">';
            $html .= '<span class="nova-share-icon ' . esc_attr($link['icon']) . '"></span>';
            $html .= '<span>' . esc_html($link['name']) . '</span>';
            $html .= '</div>';
        } else {
            $html .= '<a class="nova-share-item" href="' . esc_url($link['url']) . '" target="_blank" rel="nofollow noopener">';
            $html .= '<span class="nova-share-icon ' . esc_attr($link['icon']) . '"></span>';
            $html .= '<span>' . esc_html($link['name']) . '</span>';
            $html .= '</a>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * 生成二维码（本地生成，无需外链）
 */
function nova_get_qrcode($data, $size = 200)
{
    $hash = md5($data);
    $dir  = NOVA_TEMP_DIR . '/qrcode';
    $file = $dir . '/' . $hash . '.png';
    $url  = content_url('/nova-temp/qrcode/' . $hash . '.png');

    if (file_exists($file)) return $url;

    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }

    //调用主题内置 qrcode 类
    $qr_file = NOVA_ROOT_PATH . 'inc/class/qrcode.php';
    if (file_exists($qr_file)) {
        require_once $qr_file;
        if (class_exists('NovaQrcode')) {
            NovaQrcode::png($data, $file, 'L', 6, 2);
            if (file_exists($file)) return $url;
        }
    }
    return '';
}
