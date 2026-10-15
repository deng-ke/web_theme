<?php
/*
 * Project       : Nova主题
 * Description   : 文章详情页
 */

if (!defined('ABSPATH')) exit;

/**
 * 文章面包屑
 */
function nova_breadcrumb()
{
    $html = '<div class="nova-breadcrumb"><a href="' . esc_url(home_url()) . '"><i class="nova-icon-home"></i>' . esc_html__('首页', 'nova') . '</a>';
    if (is_singular('post')) {
        $cat = nova_get_post_cat();
        if ($cat) {
            $html .= '<i class="nova-icon-right"></i><a href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a>';
        }
        $html .= '<i class="nova-icon-right"></i><span>' . esc_html(get_the_title()) . '</span>';
    } elseif (is_category() || is_tag()) {
        $html .= '<i class="nova-icon-right"></i><span>' . single_term_title('', false) . '</span>';
    } elseif (is_search()) {
        $html .= '<i class="nova-icon-right"></i><span>' . esc_html__('搜索', 'nova') . '</span>';
    } elseif (is_author()) {
        $html .= '<i class="nova-icon-right"></i><span>' . esc_html(get_the_author()) . '</span>';
    } elseif (is_page()) {
        $html .= '<i class="nova-icon-right"></i><span>' . esc_html(get_the_title()) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 文章头部（标题、meta）
 */
function nova_post_header()
{
    $html = '<div class="nova-post-header">';
    $html .= nova_breadcrumb();
    $html .= '<h1 class="nova-post-title">' . esc_html(get_the_title()) . '</h1>';
    $html .= '<div class="nova-post-header-meta muted-2-color">';
    $html .= '<a class="nova-meta-avatar" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '"><img src="' . esc_url(nova_get_user_avatar(get_the_author_meta('ID'), 56)) . '" alt="avatar"></a>';
    $html .= '<a class="nova-meta-name" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a>';
    $html .= '<span class="nova-meta-time"><i class="nova-icon-time"></i>' . esc_html(get_the_date('Y-m-d')) . '</span>';
    $html .= '<span class="nova-meta-view"><i class="nova-icon-view"></i>' . nova_count_format(nova_get_post_views()) . '</span>';
    $html .= '<span class="nova-meta-comment"><i class="nova-icon-comment"></i>' . nova_count_format(get_comments_number()) . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 文章内容（处理付费可见短代码等）
 */
function nova_post_content()
{
    $content = get_the_content();
    $content = apply_filters('the_content', $content);
    $content = nova_code_blocks($content);
    echo $content;
}

/**
 * 代码块包装：语言标签 + 复制按钮（配合前端高亮）
 */
function nova_code_blocks($content)
{
    if (false === strpos($content, '<pre')) return $content;

    $content = preg_replace_callback('/<pre([^>]*)>(.*?)<\/pre>/is', function ($m) {
        $attrs   = $m[1];
        $code    = $m[2];
        $lang    = '';
        if (preg_match('/(?:^|\s)(?:lang|language)-([a-z0-9_+#]+)/i', $attrs, $lm)) {
            $lang = strtolower($lm[1]);
        }
        $lang_map = array(
            'py' => 'python', 'js' => 'javascript', 'ts' => 'typescript', 'sh' => 'bash',
            'shell' => 'bash', 'c++' => 'cpp', 'md' => 'markdown',
        );
        if (isset($lang_map[$lang])) $lang = $lang_map[$lang];
        $lang_allow = array('php', 'javascript', 'typescript', 'python', 'css', 'bash', 'c', 'cpp', 'java', 'go', 'json', 'sql', 'ruby', 'rust', 'swift', 'kotlin', 'yaml', 'xml', 'markdown', 'latex');
        if (!in_array($lang, $lang_allow, true)) $lang = '';
        //剥离 inner code 标签
        $code = preg_replace('/^<code[^>]*>|\n?<\/code>$/i', '', trim($code));
        $code = htmlspecialchars_decode($code, ENT_QUOTES);
        $code = htmlspecialchars($code, ENT_QUOTES);

        $label = $lang ? strtoupper($lang) : 'CODE';
        $html  = '<div class="nova-code-block"' . ($lang ? ' data-lang="' . esc_attr($lang) . '"' : '') . '>';
        $html .= '<div class="nova-code-head"><span class="nova-code-lang">' . esc_html($label) . '</span><button class="nova-code-copy" type="button"><i class="nova-icon-copy"></i>复制</button></div>';
        $html .= '<pre class="nova-code-pre"><code>' . $code . '</code></pre>';
        $html .= '</div>';
        return $html;
    }, $content);

    return $content;
}

/**
 * 文章标签
 */
function nova_post_tags()
{
    $tags = get_the_tags();
    if (!$tags) return '';
    $html = '<div class="nova-post-tags">';
    foreach ($tags as $tag) {
        $html .= '<a href="' . esc_url(get_tag_link($tag)) . '"># ' . esc_html($tag->name) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 上一篇/下一篇
 */
function nova_post_nav()
{
    $prev = get_previous_post();
    $next = get_next_post();
    if (!$prev && !$next) return '';

    $html = '<div class="nova-post-nav card">';
    if ($prev) {
        $html .= '<div class="nova-post-nav-item nova-post-nav-prev">';
        $html .= '<span class="nova-post-nav-label muted-2-color"><i class="nova-icon-left"></i>' . esc_html__('上一篇', 'nova') . '</span>';
        $html .= '<a href="' . esc_url(get_permalink($prev)) . '">' . esc_html(get_the_title($prev)) . '</a>';
        $html .= '</div>';
    } else {
        $html .= '<div class="nova-post-nav-item"></div>';
    }
    if ($next) {
        $html .= '<div class="nova-post-nav-item nova-post-nav-next">';
        $html .= '<span class="nova-post-nav-label muted-2-color">' . esc_html__('下一篇', 'nova') . '<i class="nova-icon-right"></i></span>';
        $html .= '<a href="' . esc_url(get_permalink($next)) . '">' . esc_html(get_the_title($next)) . '</a>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 相关文章
 */
function nova_related_posts($limit = 6)
{
    $post_id = get_the_ID();
    $cats = wp_get_post_categories($post_id);
    if (empty($cats)) return '';

    $args = array(
        'post__not_in'        => array($post_id),
        'category__in'        => $cats,
        'posts_per_page'      => $limit,
        'ignore_sticky_posts' => true,
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) return '';

    $html = '<div class="nova-related-posts">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('相关文章', 'nova') . '</span></h3>';
    $html .= '<div class="nova-related-grid">';
    while ($query->have_posts()) {
        $query->the_post();
        $thumb = nova_post_thumbnail_url(null, 'nova-thumb');
        $html .= '<div class="nova-related-card card">';
        if ($thumb) {
            $html .= '<a class="nova-related-thumb" href="' . esc_url(get_permalink()) . '"><img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title()) . '"></a>';
        }
        $html .= '<div class="nova-related-body">';
        $html .= '<h4><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h4>';
        $html .= '<span class="muted-2-color"><i class="nova-icon-time"></i>' . esc_html(get_the_date('Y-m-d')) . '</span>';
        $html .= '</div>';
        $html .= '</div>';
    }
    $html .= '</div></div>';
    wp_reset_postdata();
    return $html;
}

/**
 * 文章点赞按钮
 */
function nova_post_like()
{
    if (!nova_get_setting('like_s', '1')) return '';
    $post_id = get_the_ID();
    $likes = (int) get_post_meta($post_id, 'nova_likes', true);
    $html = '<div class="nova-post-actions">';
    $html .= '<button class="nova-like-btn btn btn-outline" data-id="' . $post_id . '"><i class="nova-icon-like"></i><span class="nova-like-count">' . esc_html__('点赞', 'nova') . ' ' . nova_count_format($likes) . '</span></button>';
    $html .= '</div>';
    return $html;
}

/**
 * 文章底部用户操作条
 */
function nova_post_action_bar()
{
    $html = '<div class="nova-post-action-bar card">';
    $html .= nova_post_like();
    if (nova_get_setting('post_share_s', '1')) {
        $html .= '<button class="nova-share-btn btn btn-outline" data-id="' . get_the_ID() . '"><i class="nova-icon-share"></i>' . esc_html__('分享', 'nova') . '</button>';
    }
    if (nova_get_setting('pay_s', '1') && get_post_meta(get_the_ID(), 'nova_pay_type', true)) {
        $html .= '<button class="nova-reward-btn btn btn-primary" data-id="' . get_the_ID() . '"><i class="nova-icon-money"></i>' . esc_html__('打赏', 'nova') . '</button>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 判断文章是否付费且未支付
 */
function nova_post_is_locked($post_id = 0)
{
    $post_id = $post_id ?: get_the_ID();
    if (!nova_get_setting('pay_s', '1')) return false;
    $pay_type = get_post_meta($post_id, 'nova_pay_type', true);
    if (!$pay_type) return false;
    //管理员/作者直接可见
    if (current_user_can('edit_post', $post_id)) return false;
    $user_id = get_current_user_id();
    if ($user_id) {
        $paid = get_user_meta($user_id, 'nova_paid_posts', true);
        if (is_array($paid) && in_array($post_id, $paid)) return false;
    }
    return true;
}

/**
 * 付费文章解锁提示框
 */
function nova_post_pay_box($post_id = 0)
{
    $post_id = $post_id ?: get_the_ID();
    $pay_price = (float) get_post_meta($post_id, 'nova_pay_price', true);
    $pay_points = (int) get_post_meta($post_id, 'nova_pay_points', true);

    $html = '<div class="nova-pay-box card">';
    $html .= '<div class="nova-pay-icon"><i class="nova-icon-lock"></i></div>';
    $html .= '<h3>' . esc_html__('此处内容已付费隐藏', 'nova') . '</h3>';
    $html .= '<p class="muted-2-color">' . esc_html__('付费后即可查看全部内容', 'nova') . '</p>';
    if ($pay_price) {
        $html .= '<div class="nova-pay-price">¥ ' . number_format($pay_price, 2) . '</div>';
    }
    if ($pay_points) {
        $html .= '<div class="nova-pay-points">' . $pay_points . ' ' . esc_html__('积分', 'nova') . '</div>';
    }
    $html .= '<button class="btn btn-primary nova-pay-trigger" data-id="' . $post_id . '"><i class="nova-icon-money"></i>' . esc_html__('立即支付解锁', 'nova') . '</button>';
    $html .= '</div>';
    return $html;
}
