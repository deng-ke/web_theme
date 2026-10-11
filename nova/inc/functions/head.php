<?php
/*
 * Project       : Nova主题
 * Description   : 头部输出（SEO/图标/统计代码）
 */

if (!defined('ABSPATH')) exit;

/**
 * SEO 优化
 */
function nova_seo_init()
{
    if (!nova_get_setting('seo_s', '1')) return;
    //关键词/描述 meta
    add_action('wp_head', 'nova_seo_meta', 5);
    //canonical
    remove_action('wp_head', 'rel_canonical');
    add_action('wp_head', 'nova_canonical', 4);
    //文章内容图片加alt
    add_filter('the_content', 'nova_content_img_alt');
}
add_action('init', 'nova_seo_init');

/**
 * 输出 SEO meta
 */
function nova_seo_meta()
{
    $description = '';
    $keywords    = '';

    if (is_singular()) {
        $post_id     = get_queried_object_id();
        $description = get_post_meta($post_id, 'nova_seo_desc', true);
        $keywords    = get_post_meta($post_id, 'nova_seo_keywords', true);
        if (!$description) {
            $description = nova_excerpt(150);
        }
    } elseif (is_category() || is_tag()) {
        $term        = get_queried_object();
        $description = term_description($term->term_id, $term->taxonomy);
        $keywords    = $term->name;
    }
    if (!$description) $description = nova_get_setting('seo_description');
    if (!$keywords) $keywords = nova_get_setting('seo_keywords');

    if ($description) {
        echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($description)) . '">' . "\n";
    }
    if ($keywords) {
        echo '<meta name="keywords" content="' . esc_attr(wp_strip_all_tags($keywords)) . '">' . "\n";
    }
}

/**
 * canonical 链接
 */
function nova_canonical()
{
    $url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));
    if (is_singular()) {
        $url = get_permalink();
    } elseif (is_category() || is_tag()) {
        $url = get_term_link(get_queried_object());
    } elseif (is_paged()) {
        $url = get_pagenum_link();
    }
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
}

/**
 * 文章图片自动加 alt 属性
 */
function nova_content_img_alt($content)
{
    $content = preg_replace_callback('/<img([^>]*)>/i', function ($matches) {
        $img = $matches[1];
        if (!preg_match('/alt\s*=/i', $img)) {
            global $post;
            $alt = $post ? $post->post_title : get_bloginfo('name');
            $img .= ' alt="' . esc_attr($alt) . '"';
        }
        return '<img' . $img . '>';
    }, $content);
    return $content;
}

/**
 * 百度主动推送
 */
function nova_baidu_push($post_id)
{
    if (!nova_get_setting('baidu_push_s')) return;
    if (wp_is_post_revision($post_id) || 'publish' !== get_post_status($post_id)) return;
    $token = nova_get_setting('seo_baidu_token');
    if (!$token) return;
    $url  = get_permalink($post_id);
    $api  = 'http://data.zz.baidu.com/urls?site=' . parse_url(home_url(), PHP_URL_HOST) . '&token=' . $token;
    wp_remote_post($api, array(
        'headers' => array('Content-Type' => 'text/plain'),
        'body'    => $url,
        'timeout' => 5,
    ));
}
add_action('publish_post', 'nova_baidu_push');

/**
 * 输出 favicon
 */
function nova_favicon()
{
    $icon = get_site_icon_url(32);
    if ($icon) {
        echo '<link rel="icon" href="' . esc_url($icon) . '" sizes="32x32">' . "\n";
        return;
    }
    echo '<link rel="icon" href="' . esc_url(NOVA_TEMPLATE_DIRECTORY_URI . '/img/favicon.svg') . '">' . "\n";
}
add_action('wp_head', 'nova_favicon', 1);

/**
 * 输出统计代码
 */
function nova_footer_stats_code()
{
    $code = nova_get_setting('stats_code');
    if ($code) {
        echo $code; // phpcs:ignore -- 用户自定义统计代码
    }
}
add_action('wp_footer', 'nova_footer_stats_code', 99);

/**
 * 页面顶部进度条开关
 */
function nova_page_loader()
{
    if (nova_get_setting('page_loader_s')) {
        echo '<div class="nova-page-loader"><div class="nova-loader-bar"></div></div>';
    }
}

/**
 * 获取头部图片（归档页封面）
 */
function nova_get_archive_bg()
{
    if (is_category() || is_tag()) {
        $term = get_queried_object();
        $img  = get_term_meta($term->term_id, 'nova_term_img', true);
        if ($img) return $img;
    }
    $img = nova_get_setting('archive_bg_img');
    return $img ? $img : NOVA_TEMPLATE_DIRECTORY_URI . '/img/topic.jpg';
}

/**
 * 文章作者信息卡
 */
function nova_get_author_card($user_id)
{
    $user  = get_userdata($user_id);
    if (!$user) return '';
    $posts_count = count_user_posts($user_id);
    $html = '<div class="nova-author-card card">';
    $html .= '<div class="nova-author-cover" style="background-image:url(' . esc_url(nova_get_setting('author_bg_img', NOVA_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg')) . ')"></div>';
    $html .= '<div class="nova-author-info">';
    $html .= '<div class="nova-avatar-lg"><img src="' . esc_url(nova_get_user_avatar($user_id, 120)) . '" alt="' . esc_attr($user->display_name) . '">' . nova_get_user_badge($user_id) . '</div>';
    $html .= '<h3 class="nova-author-name">' . esc_html($user->display_name) . '</h3>';
    $html .= '<div class="nova-author-desc muted">' . esc_html(nova_get_user_sign($user_id)) . '</div>';
    $html .= '<div class="nova-author-meta">';
    $html .= '<span>' . esc_html__('文章', 'nova') . ' <b>' . $posts_count . '</b></span>';
    $html .= '<span>' . esc_html__('粉丝', 'nova') . ' <b>' . nova_get_user_count($user_id, 'fans') . '</b></span>';
    $html .= '<span>' . esc_html__('关注', 'nova') . ' <b>' . nova_get_user_count($user_id, 'follows') . '</b></span>';
    $html .= '</div>';
    if (get_current_user_id() && get_current_user_id() != $user_id) {
        $following = nova_is_following($user_id);
        $html .= '<button class="btn btn-primary btn-sm nova-follow-btn' . ($following ? ' followed' : '') . '" data-user="' . $user_id . '">' . ($following ? esc_html__('已关注', 'nova') : esc_html__('关注', 'nova')) . '</button>';
    }
    $html .= '</div></div>';
    return $html;
}

/**
 * 文章详情页底部作者信息
 */
function nova_post_author_box()
{
    $user_id = get_the_author_meta('ID');
    if (!$user_id) return '';
    $html = '<div class="nova-post-author card">';
    $html .= '<div class="nova-avatar-lg"><img src="' . esc_url(nova_get_user_avatar($user_id, 96)) . '" alt="avatar">' . nova_get_user_badge($user_id) . '</div>';
    $html .= '<div class="nova-post-author-info">';
    $html .= '<h3 class="nova-author-name">' . esc_html(get_the_author()) . '</h3>';
    $html .= '<div class="nova-author-desc muted-2-color">' . esc_html(nova_get_user_sign($user_id)) . '</div>';
    $html .= '</div>';
    if (get_current_user_id() && get_current_user_id() != $user_id) {
        $following = nova_is_following($user_id);
        $html .= '<div class="nova-post-author-btn"><button class="btn btn-primary btn-sm nova-follow-btn' . ($following ? ' followed' : '') . '" data-user="' . $user_id . '">' . ($following ? esc_html__('已关注', 'nova') : esc_html__('关注', 'nova')) . '</button></div>';
    }
    $html .= '</div>';
    return $html;
}
