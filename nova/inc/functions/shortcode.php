<?php
/*
 * Project       : Nova主题
 * Description   : 短代码系统（隐藏内容/回复可见/付费可见/文章盒子/文章列表/下载按钮/折叠面板/提示框/按钮）
 */

if (!defined('ABSPATH')) exit;

/**
 * 判断当前用户是否评论过当前文章
 */
function nova_user_has_commented($post_id = 0, $user_id = 0)
{
    $post_id = $post_id ?: get_the_ID();
    $user_id = $user_id ?: get_current_user_id();
    if (!$post_id) return false;

    $q_args = array(
        'post_id' => $post_id,
        'status'  => 'approve',
        'number'  => 1,
    );
    if ($user_id) {
        $q_args['user_id'] = $user_id;
    } elseif (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {
        $q_args['author_email'] = str_replace('%40', '@', sanitize_text_field(wp_unslash($_COOKIE['comment_author_email_' . COOKIEHASH])));
    } else {
        return false;
    }
    $comment = get_comments($q_args);
    return $comment ? true : false;
}

/**
 * 隐藏内容通用渲染
 *
 * @param string $type  reply|logged|vip|pay
 * @param string $content 隐藏的内容
 * @param string $desc   自定义提示
 */
function nova_hide_content_html($type, $content, $desc = '')
{
    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    $can_show = false;
    $default  = '';

    switch ($type) {
        case 'reply':
            $can_show = nova_user_has_commented($post_id, $user_id) || current_user_can('edit_post', $post_id);
            $default  = __('此处内容已隐藏，评论本文后刷新即可查看', 'nova');
            break;
        case 'logged':
            $can_show = (bool) $user_id || current_user_can('edit_post', $post_id);
            $default  = __('此处内容已隐藏，请登录后查看', 'nova');
            break;
        case 'vip':
            $can_show = (nova_is_vip($user_id)) || current_user_can('edit_post', $post_id);
            $default  = __('此处内容已隐藏，仅限VIP会员查看', 'nova');
            break;
        case 'pay':
            $can_show = !nova_post_is_locked($post_id);
            $default  = __('此处内容已付费隐藏，支付后可查看', 'nova');
            break;
    }

    if ($can_show) {
        return '<div class="nova-hide-box nova-hide-show"><div class="nova-hide-head"><i class="nova-icon-unlock"></i>' . esc_html__('隐藏内容', 'nova') . '</div><div class="nova-hide-body">' . do_shortcode($content) . '</div></div>';
    }

    $desc = $desc ? $desc : $default;
    $html = '<div class="nova-hide-box">';
    $html .= '<div class="nova-hide-mask"><i class="nova-icon-lock"></i><div class="nova-hide-text">' . esc_html($desc) . '</div>';
    if ('reply' === $type) {
        $html .= '<a class="nova-hide-btn btn btn-primary" href="#respond"><i class="nova-icon-comment"></i>' . esc_html__('去评论', 'nova') . '</a>';
    } elseif ('logged' === $type) {
        $html .= '<a class="nova-hide-btn btn btn-primary" href="' . esc_url(nova_signin_url()) . '"><i class="nova-icon-user"></i>' . esc_html__('去登录', 'nova') . '</a>';
    } elseif ('pay' === $type) {
        $html .= '<button class="nova-hide-btn btn btn-primary nova-pay-trigger" data-id="' . $post_id . '"><i class="nova-icon-money"></i>' . esc_html__('支付解锁', 'nova') . '</button>';
    }
    $html .= '</div></div>';
    return $html;
}

/**
 * [nova_hide type="reply|logged|vip|pay" desc=""]内容[/nova_hide]
 */
function nova_shortcode_hide($atts, $content = null)
{
    $atts = shortcode_atts(array(
        'type' => 'reply',
        'desc' => '',
    ), $atts, 'nova_hide');
    if (!in_array($atts['type'], array('reply', 'logged', 'vip', 'pay'), true)) {
        $atts['type'] = 'reply';
    }
    return nova_hide_content_html($atts['type'], $content, $atts['desc']);
}
add_shortcode('nova_hide', 'nova_shortcode_hide');

/**
 * [nova_reply desc=""]内容[/nova_reply] 回复可见简写
 */
function nova_shortcode_reply($atts, $content = null)
{
    $atts = shortcode_atts(array('desc' => ''), $atts, 'nova_reply');
    return nova_hide_content_html('reply', $content, $atts['desc']);
}
add_shortcode('nova_reply', 'nova_shortcode_reply');

/**
 * [nova_postsbox post_id="1"] 文章推荐盒子
 */
function nova_shortcode_postsbox($atts)
{
    $atts = shortcode_atts(array('post_id' => 0), $atts, 'nova_postsbox');
    $post = get_post((int) $atts['post_id']);
    if (!$post) return '';

    $thumb = nova_post_thumbnail_url($post, 'nova-card');
    $html  = '<div class="nova-postsbox card">';
    if ($thumb) {
        $html .= '<a class="nova-postsbox-thumb" href="' . esc_url(get_permalink($post)) . '"><img src="' . esc_url($thumb) . '" alt="' . esc_attr($post->post_title) . '"></a>';
    }
    $html .= '<div class="nova-postsbox-body">';
    $html .= '<a class="nova-postsbox-title" href="' . esc_url(get_permalink($post)) . '">' . esc_html($post->post_title) . '</a>';
    $html .= '<div class="nova-postsbox-meta muted-2-color"><i class="nova-icon-time"></i>' . esc_html(nova_time_ago(get_the_date('Y-m-d H:i:s', $post))) . '<i class="nova-icon-view"></i>' . nova_count_format(nova_get_post_views($post->ID)) . '<i class="nova-icon-comment"></i>' . nova_count_format($post->comment_count) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
add_shortcode('nova_postsbox', 'nova_shortcode_postsbox');

/**
 * [nova_postslists count="6" cat="1" orderby="date" style="list|card"] 文章列表
 */
function nova_shortcode_postslists($atts)
{
    $atts = shortcode_atts(array(
        'count'   => 6,
        'cat'     => '',
        'orderby' => 'date',
        'style'   => 'list',
    ), $atts, 'nova_postslists');

    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => max(1, (int) $atts['count']),
        'ignore_sticky_posts' => true,
    );
    if ($atts['cat']) {
        $args['cat'] = (int) $atts['cat'];
    }
    $orderbys = array('date', 'comment_count', 'rand');
    $args['orderby'] = in_array($atts['orderby'], $orderbys, true) ? $atts['orderby'] : 'date';
    if ('comment_count' === $args['orderby']) {
        $args['order'] = 'DESC';
    }

    $query = new WP_Query($args);
    if (!$query->have_posts()) return '';

    $html = '<div class="nova-posts-lists nova-posts-lists-' . esc_attr($atts['style']) . '">';
    while ($query->have_posts()) {
        $query->the_post();
        if ('card' === $atts['style']) {
            $html .= nova_post_card();
        } else {
            $html .= nova_post_list_item();
        }
    }
    wp_reset_postdata();
    $html .= '</div>';
    return $html;
}
add_shortcode('nova_postslists', 'nova_shortcode_postslists');

/**
 * [nova_download href="" name="下载文件" price="" points=""] 下载按钮（支持付费/积分下载）
 */
function nova_shortcode_download($atts)
{
    $atts = shortcode_atts(array(
        'href'   => '',
        'name'   => __('下载文件', 'nova'),
        'price'  => '',
        'points' => '',
    ), $atts, 'nova_download');

    $href   = esc_url($atts['href']);
    $price  = (float) $atts['price'];
    $points = (int) $atts['points'];

    $html = '<div class="nova-download-box card">';
    $html .= '<div class="nova-download-info"><i class="nova-icon-download"></i><div><div class="nova-download-name">' . esc_html($atts['name']) . '</div>';
    if ($price) {
        $html .= '<div class="nova-download-price">¥ ' . number_format($price, 2) . '</div>';
    }
    if ($points) {
        $html .= '<div class="nova-download-points">' . $points . ' ' . esc_html__('积分', 'nova') . '</div>';
    }
    $html .= '</div></div>';
    if ($price || $points) {
        $html .= '<button class="btn btn-primary nova-download-btn" data-href="' . esc_attr($href) . '" data-price="' . $price . '" data-points="' . $points . '"><i class="nova-icon-money"></i>' . esc_html__('付费下载', 'nova') . '</button>';
    } else {
        $html .= '<a class="btn btn-primary" href="' . $href . '" download><i class="nova-icon-download"></i>' . esc_html__('立即下载', 'nova') . '</a>';
    }
    $html .= '</div>';
    return $html;
}
add_shortcode('nova_download', 'nova_shortcode_download');

/**
 * [nova_panel title=""]内容[/nova_panel] 折叠面板
 */
function nova_shortcode_panel($atts, $content = null)
{
    $atts = shortcode_atts(array('title' => __('详情', 'nova')), $atts, 'nova_panel');
    $html = '<div class="nova-panel">';
    $html .= '<div class="nova-panel-head"><span class="nova-panel-title">' . esc_html($atts['title']) . '</span><i class="nova-icon-down"></i></div>';
    $html .= '<div class="nova-panel-body">' . do_shortcode($content) . '</div>';
    $html .= '</div>';
    return $html;
}
add_shortcode('nova_panel', 'nova_shortcode_panel');

/**
 * [nova_tip type="info|success|warning|danger"]内容[/nova_tip] 提示框
 */
function nova_shortcode_tip($atts, $content = null)
{
    $atts = shortcode_atts(array('type' => 'info'), $atts, 'nova_tip');
    if (!in_array($atts['type'], array('info', 'success', 'warning', 'danger'), true)) {
        $atts['type'] = 'info';
    }
    return '<div class="nova-tip nova-tip-' . esc_attr($atts['type']) . '">' . do_shortcode($content) . '</div>';
}
add_shortcode('nova_tip', 'nova_shortcode_tip');

/**
 * [nova_btn href="" color="" target=""]文字[/nova_btn] 按钮
 */
function nova_shortcode_btn($atts, $content = null)
{
    $atts = shortcode_atts(array(
        'href'   => '',
        'color'  => 'primary',
        'target' => '',
    ), $atts, 'nova_btn');
    $colors = array('primary', 'red', 'yellow', 'blue', 'cyan', 'green', 'purple', 'gray');
    if (!in_array($atts['color'], $colors, true)) {
        $atts['color'] = 'primary';
    }
    $tag   = $atts['href'] ? 'a' : 'span';
    $html  = '<' . $tag . ' class="btn btn-' . esc_attr($atts['color']) . '"';
    if ($atts['href']) {
        $html .= ' href="' . esc_url($atts['href']) . '"';
    }
    if ($atts['target']) {
        $html .= ' target="_blank" rel="noopener"';
    }
    $html .= '>' . do_shortcode($content) . '</' . $tag . '>';
    return $html;
}
add_shortcode('nova_btn', 'nova_shortcode_btn');

/**
 * 短代码注册到编辑器（经典编辑器工具栏下拉插入）
 */
function nova_shortcode_admin_assets()
{
    wp_enqueue_script('nova-shortcode-admin', NOVA_TEMPLATE_DIRECTORY_URI . '/js/admin-shortcode.js', array(), NOVA_VERSION, true);
    wp_localize_script('nova-shortcode-admin', 'nova_shortcodes', array(
        'list' => array(
            'nova_hide'       => __('隐藏内容', 'nova'),
            'nova_reply'      => __('回复可见', 'nova'),
            'nova_postsbox'   => __('文章盒子', 'nova'),
            'nova_postslists' => __('文章列表', 'nova'),
            'nova_download'   => __('下载按钮', 'nova'),
            'nova_panel'      => __('折叠面板', 'nova'),
            'nova_tip'        => __('提示框', 'nova'),
            'nova_btn'        => __('按钮', 'nova'),
        ),
    ));
}
add_action('admin_enqueue_scripts', 'nova_shortcode_admin_assets');
