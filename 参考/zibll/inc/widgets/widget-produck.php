<?php

/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-14 18:30:30
 * @LastEditTime : 2026-05-27 10:35:06
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2026 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

function zib_get_widget_pay_btn_fields($id = 'btn', $title = null)
{
    if ($title === null) {
        $title = __('按钮', 'zib_language');
    }

    return array(
        'id'     => $id,
        'type'   => 'fieldset',
        'title'  => $title,
        'fields' => array(
            array(
                'title'   => __('按钮类型', 'zib_language'),
                'id'      => 'type',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'link' => __('链接跳转', 'zib_language'),
                    'post' => __('知识付费', 'zib_language'),
                    'vip'  => __('开通会员', 'zib_language'),
                ),
                'default' => 'link',
            ),
            array(
                'dependency'  => array('type', '==', 'post'),
                'title'       => __('绑定知识付费文章', 'zib_language'),
                'id'          => 'post_id',
                'default'     => '',
                'options'     => 'posts',
                'ajax'        => true,
                'multiple'    => false,
                'chosen'      => true,
                'placeholder' => __('输入关键词以搜索文章或帖子', 'zib_language'),
                'settings'    => array(
                    'min_length' => 2,
                ),
                'query_args'  => array(
                    'post_type' => ['forum_post', 'post'],
                ),
                'type'        => 'select',
            ),
            array(
                'dependency' => array('type', '==', 'post'),
                'title'      => ' ',
                'subtitle'   => __('购买按钮文案', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'post_text',
                'type'       => 'text',
                'default'    => '立即购买',
            ),
            array(
                'dependency' => array('type', '==', 'post'),
                'title'      => ' ',
                'subtitle'   => __('已经购买过的按钮文案', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'post_paid_text',
                'type'       => 'text',
                'default'    => '去查看',
            ),
            array(
                'dependency' => array('type', '==', 'vip'),
                'title'      => __('绑定会员等级', 'zib_language'),
                'id'         => 'vip_level',
                'type'       => 'radio',
                'inline'     => true,
                'options'    => array(
                    '1' => _pz('pay_user_vip_1_name'),
                    '2' => _pz('pay_user_vip_2_name'),
                ),
                'default'    => '1',
            ),
            array(
                'dependency' => array('type', '==', 'vip'),
                'title'      => ' ',
                'subtitle'   => __('开通会员按钮文案', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'vip_text',
                'type'       => 'text',
                'default'    => '立即开通',
            ),
            array(
                'dependency' => array('type|vip_level', '==|>=', 'vip|2'),
                'title'      => ' ',
                'subtitle'   => __('升级会员按钮文案', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'vip_upgrade_text',
                'type'       => 'text',
                'default'    => '升级会员',
            ),
            array(
                'dependency' => array('type', '==', 'vip'),
                'title'      => ' ',
                'subtitle'   => __('已经是会员的按钮文案', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'vip_already_text',
                'type'       => 'text',
                'default'    => '我的会员',
                'desc'       => '',
            ),
            array(
                'dependency' => array('type', '==', 'link'),
                'title'      => __('链接设置', 'zib_language'),
                'id'         => 'link',
                'type'       => 'link',
                'default'    => array('url' => '#', 'text' => '立即购买', 'target' => '_blank'),
            ),
        ),
    );
}

//获取知识付费文章或帖子的按钮
function zib_get_widget_pay_btn_link($opts = array(), $class = '', $icon = '')
{

    $type = $opts['type'] ?? 'link';
    $icon = $icon ? '<icon>' . $icon . '</icon>' : '';
    switch ($type) {
        case 'link':
            $link = $opts['link'] ?? array();
            if (empty($link['text'])) {
                return '';
            }
            return '<a href="' . esc_url($link['url'] ?? '#') . '"' . (empty($link['target']) ? '' : ' target="' . esc_attr($link['target']) . '"') . ' class="' . $class . '">' . $icon . ($link['text']) . '</a>';

        case 'post':
            $post_id = $opts['post_id'] ?? 0;
            $post    = get_post($post_id);
            if (!$post) {
                return '';
            }

            $paid_data = zibpay_is_paid($post_id);
            if ($paid_data) {
                return '<a href="' . get_permalink($post_id) . '" class="' . $class . '">' . $icon . ($opts['post_paid_text'] ?? __('去查看', 'zib_language')) . '</a>';
            } else {
                return zibpay_get_post_cashier_link($post_id, $class, $icon . ($opts['post_text'] ?? __('立即购买', 'zib_language')));
            }

        case 'vip':
            $vip_level      = $opts['vip_level'] ?? 1;
            $user_vip_level = zib_get_user_vip_level();
            if (!$user_vip_level) {
                return '<a href="javascript:;" vip-level="' . $vip_level . '" class="pay-vip ' . $class . '">' . $icon . ($opts['vip_text'] ?? __('立即开通', 'zib_language')) . '</a>';
            } elseif ($user_vip_level && $user_vip_level < $vip_level) {
                return '<a href="javascript:;" vip-level="' . $vip_level . '" class="pay-vip ' . $class . '">' . $icon . ($opts['vip_upgrade_text'] ?? __('升级会员', 'zib_language')) . '</a>';
            } else {
                return '<a href="' . zib_get_user_center_url('vip') . '" class="' . $class . '">' . $icon . ($opts['vip_already_text'] ?? __('我的会员', 'zib_language')) . '</a>';
            }
    }

    return '';
}

function zib_widget_pricing_plans_parse_lines($raw)
{
    $lines = preg_split('/\n/', $raw);
    $out   = array();
    foreach ($lines as $line) {
        $line = trim($line);
        if ('' !== $line) {
            $out[] = $line;
        }
    }

    return $out;
}

function zib_widget_pricing_plans_get_price_html($item)
{

    $mark           = $item['mark'] ?: zibpay_get_pay_mark();
    $main_price     = zib_floatval_round($item['price']);
    $original_price = zib_floatval_round($item['show_price']);

    $original_price = $original_price > $main_price ? '<span class="original-price muted-2-color mt6" title="' . esc_attr(sprintf(__('原价 %s', 'zib_language'), zib_floatval_round($original_price))) . '"><span class="pay-mark">' . $mark . '</span>' . zib_floatval_round($original_price) . '</span>' : '';
    $promotion_tag  = !empty($item['promotion_tag']) && $original_price ? '<badge>' . $item['promotion_tag'] . '</badge><br>' : '';

    $original_price = $original_price ? '<div class="plan-price-original">' . $promotion_tag . $original_price . '</div>' : '';

    $price = '<span class="plan-price-main"><span class="pay-mark plan-price-prefix">' . $mark . '</span>' . zib_floatval_round($main_price) . '</span>';

    return '<div class="flex ac jsb gap10"><div class="flex ac gap10">' . $price . $original_price . '</div></div>';
}

/**
 * @param array $args
 * @param array $instance
 */
function zib_widget_pricing_plans($args, $instance)
{
    $trust           = !empty($instance['trust_items']) && is_array($instance['trust_items']) ? $instance['trust_items'] : array();
    $product_options = !empty($instance['lists']) && is_array($instance['lists']) ? $instance['lists'] : array();
    $animation_class = Zib_CFSwidget::animation_class($instance, true);

    //开启渲染
    echo '<div class="produck-pricing-plans mb20">';
    echo '<div class="produck-plan-cards">';

    $allow_accents = array('pink', 'purple', 'blue', 'pink', 'purple', 'blue');
    foreach ($product_options as $i => $plan) {
        $ptitle = !empty($plan['name']) ? $plan['name'] : '';
        if ('' === $ptitle) {
            continue;
        }
        $accent = $plan['color'];
        $feat   = !empty($plan['is_featured']) ? $plan['is_featured'] : false;
        $psub   = !empty($plan['desc']) ? $plan['desc'] : '';
        $pfn    = zib_widget_pricing_plans_parse_lines(!empty($plan['intro']) ? $plan['intro'] : '');
        $badge  = !empty($plan['tag']) ? $plan['tag'] : '';
        $price  = zib_widget_pricing_plans_get_price_html($plan);

        $card_class = 'produck-plan-card accent-' . $accent . $animation_class;
        if ($feat) {
            $card_class .= ' is-featured';
        }

        echo '<div class="' . esc_attr($card_class) . '">';
        echo '<div class="produck-plan-card-inner">';
        if ($feat || $badge) {
            echo '<span class="plan-badge">' . ($badge ? $badge : __('推荐', 'zib_language')) . '</span>';
        }
        echo '<h3 class="plan-card-title">' . esc_html($ptitle) . '</h3>';
        if ('' !== $psub) {
            echo '<p class="plan-card-sub muted-2-color">' . esc_html($psub) . '</p>';
        }
        echo '<div class="plan-card-price">';
        echo ' ' . $price . '';
        echo '</div>';
        echo '<div class="plan-card-divider"></div>';
        if ($pfn) {
            echo '<ul class="plan-card-features">';
            foreach ($pfn as $ft) {
                echo '<li><i class="fa fa-check plan-check" aria-hidden="true"></i><span>' . ($ft) . '</span></li>';
            }
            echo '</ul>';
        }
        echo '<div class="plan-card-action">';
        echo zib_get_widget_pay_btn_link($plan['btn'] ?? [], 'produck-plan-btn');
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';

    if ($trust) {
        echo '<div class="produck-plan-trust-bar' . $animation_class . '">';
        foreach ($trust as $ti) {
            if (empty($ti['title'])) {
                continue;
            }
            $tic = !empty($ti['icon']) ? $ti['icon'] : 'fa fa-check-circle';
            echo '<div class="produck-plan-trust-item">';
            echo '<span class="trust-icon-wrap">' . zib_get_cfs_icon($tic) . '</span>';
            echo '<span class="trust-text">';
            echo '<strong class="trust-title">' . esc_html($ti['title']) . '</strong>';
            if (!empty($ti['desc'])) {
                echo '<span class="trust-desc muted-2-color">' . esc_html($ti['desc']) . '</span>';
            }
            echo '</span>';
            echo '</div>';
        }
        echo '</div>';
    }

    $fnote = !empty($instance['foot_note']) ? trim($instance['foot_note']) : '';
    $fico  = !empty($instance['foot_icon']) ? $instance['foot_icon'] : 'fa fa-shield';
    if ('' !== $fnote) {
        echo '<p class="produck-plan-foot muted-2-color em09' . $animation_class . '">';
        echo '<i class="' . esc_attr($fico) . ' mr6" aria-hidden="true"></i>';
        echo esc_html($fnote);
        echo '</p>';
    }

    echo '</div>';
}

//更适合产品页的评价卡片

function zib_widget_sales_hl_card_full_fields()
{
    return array(
        array(
            'id'      => 'color',
            'type'    => 'select',
            'title'   => __('配色', 'zib_language'),
            'class'   => 'compact',
            'default' => 'pink',
            'options' => array(
                'pink'   => __('粉色', 'zib_language'),
                'purple' => __('紫色', 'zib_language'),
                'orange' => __('橙色', 'zib_language'),
                'blue'   => __('蓝色', 'zib_language'),
                'green'  => __('绿色', 'zib_language'),
                'red'    => __('红色', 'zib_language'),
            ),
        ),
        array(
            'id'           => 'icon',
            'type'         => 'icon',
            'title'        => __('左上图标', 'zib_language'),
            'class'        => 'compact',
            'button_title' => __('选择图标', 'zib_language'),
            'default'      => 'fa fa-shopping-cart',
        ),
        array(
            'id'      => 'title',
            'type'    => 'text',
            'title'   => __('标题', 'zib_language'),
            'default' => '',
        ),
        array(
            'id'      => 'desc',
            'type'    => 'text',
            'title'   => __('副标题/简介', 'zib_language'),
            'class'   => 'compact',
            'default' => '',
        ),
        array(
            'id'         => 'features',
            'type'       => 'textarea',
            'title'      => __('功能列表', 'zib_language'),
            'class'      => 'compact',
            'default'    => '',
            'desc'       => __('一行一条，每行前自带 ✓ 勾选图标', 'zib_language'),
            'attributes' => array('rows' => 4, 'placeholder' => __("商品管理 & 订单系统\n优惠券 & 营销活动\n多种支付方式支持", 'zib_language')),
        ),
        array(
            'id'      => 'image',
            'type'    => 'upload',
            'title'   => __('右侧产品图（白天）', 'zib_language'),
            'class'   => 'compact',
            'library' => 'image',
            'preview' => true,
            'desc'    => __('推荐尺寸 400×400px，PNG 透明背景效果最佳', 'zib_language'),
        ),
        array(
            'id'      => 'image_dark',
            'type'    => 'upload',
            'title'   => __('右侧产品图（夜间，可选）', 'zib_language'),
            'class'   => 'compact',
            'library' => 'image',
            'preview' => true,
            'desc'    => __('留空则夜间沿用白天图', 'zib_language'),
        ),
        array(
            'id'      => 'button_text',
            'type'    => 'text',
            'title'   => __('按钮文案', 'zib_language'),
            'class'   => 'compact',
            'default' => '了解更多',
        ),
        array(
            'id'      => 'button_url',
            'type'    => 'text',
            'title'   => __('按钮链接（留空则不显示按钮）', 'zib_language'),
            'class'   => 'compact',
            'default' => '',
        ),
    );
}

/** 小卡片字段（仅 图标 + 标题 + 副标题） */
function zib_widget_sales_hl_card_small_fields()
{
    return array(
        array(
            'id'      => 'title',
            'type'    => 'text',
            'title'   => __('标题', 'zib_language'),
            'default' => '',
        ),
        array(
            'id'      => 'desc',
            'type'    => 'text',
            'title'   => __('副标题', 'zib_language'),
            'class'   => 'compact',
            'default' => '',
        ),
        array(
            'id'           => 'icon',
            'type'         => 'icon',
            'title'        => __('图标', 'zib_language'),
            'class'        => 'compact',
            'button_title' => __('选择图标', 'zib_language'),
            'default'      => 'fa fa-download',
        ),
        array(
            'id'      => 'color',
            'type'    => 'select',
            'title'   => __('配色', 'zib_language'),
            'class'   => 'compact',
            'default' => 'blue',
            'options' => array(
                'pink'   => __('粉色', 'zib_language'),
                'purple' => __('紫色', 'zib_language'),
                'orange' => __('橙色', 'zib_language'),
                'blue'   => __('蓝色', 'zib_language'),
                'green'  => __('绿色', 'zib_language'),
                'red'    => __('红色', 'zib_language'),
            ),
        ),
    );
}

function zib_widget_reviews($args, $instance)
{
    $reviews = !empty($instance['reviews']) && is_array($instance['reviews']) ? $instance['reviews'] : array();
    if (!$reviews) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . esc_html__('虚拟用户评价：', 'zib_language') . '</b>' . esc_html__('请添加至少一条评价', 'zib_language') . '</div>';
        }
        return;
    }

    echo '<div class="mb20">';

    $slides = '';
    foreach ($reviews as $r) {
        if (empty($r['content']) && empty($r['name'])) {
            continue;
        }
        $avatar  = !empty($r['avatar']) ? $r['avatar'] : '';
        $name    = !empty($r['name']) ? $r['name'] : '';
        $role    = !empty($r['role']) ? $r['role'] : '';
        $rating  = isset($r['rating']) ? max(0, min(5, (int) $r['rating'])) : 5;
        $content = !empty($r['content']) ? $r['content'] : '';
        $url     = !empty($r['url']) ? $r['url'] : '';

        $stars_html = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars_html .= '<i class="fa fa-star ' . ($i <= $rating ? 'is-on' : 'is-off') . '"></i>';
        }

        $avatar_html = $avatar
            ? '<img class="review-avatar" src="' . esc_url($avatar) . '" alt="' . esc_attr($name) . '">'
            : '<span class="review-avatar review-avatar-placeholder"><i class="fa fa-user"></i></span>';

        $slides .= '<div class="swiper-slide">';
        $slides .= '<div class="review-card">';
        if ($url) {
            $slides .= '<a class="review-card-link flex jsb" href="' . esc_url($url) . '" target="_blank">';
        }
        $slides .= '<div class="review-head flex ac">';
        $slides .= $avatar_html;
        $slides .= '<div class="review-meta ml10">';
        $slides .= '<div class="review-name"><b>' . esc_html($name) . '</b></div>';
        if ($role) {
            $slides .= '<div class="review-role muted-2-color">' . esc_html($role) . '</div>';
        }
        $slides .= '</div>';
        $slides .= '</div>';
        if ($url) {
            $slides .= '<i class="em12 fa fa-angle-right mt6 opacity5"></i></a>';
        }
        $slides .= '<div class="review-stars">' . $stars_html . '</div>';
        $slides .= '<p class="review-content">' . nl2br(wp_kses_post($content)) . '</p>';
        $slides .= '<i class="fa fa-quote-right review-quote"></i>';
        $slides .= '</div>';
        $slides .= '</div>';
    }

    $pc_per = !empty($instance['desktop_per_view']) ? (float) $instance['desktop_per_view'] : 4;
    $m_per  = !empty($instance['mobile_per_view']) ? (float) $instance['mobile_per_view'] : 1.1;
    if ($pc_per <= 0) {$pc_per = 4;}
    if ($m_per <= 0) {$m_per = 1.1;}

    $style = '--per-pc:' . $pc_per . ';--per-m:' . $m_per . ';';

    $swiper_attr = '';
    if (!empty($instance['swiper_loop'])) {
        $swiper_attr .= ' data-loop="true"';
    }

    if (!empty($instance['swiper_autoplay'])) {
        $swiper_attr .= ' data-autoplay="true"';
        $swiper_attr .= ' data-interval="' . ($instance['swiper_autoplay'] * 1000) . '"';
    }

    echo '<div class="produck-wid-reviews" style="' . esc_attr($style) . '">';
    echo '<div class="swiper-container swiper-scroll produck-wid-reviews-swiper" ' . $swiper_attr . '>';
    echo '<div class="swiper-wrapper">' . $slides . '</div>';
    echo '<div class="swiper-button-prev"></div>';
    echo '<div class="swiper-button-next"></div>';
    echo '<div class="swiper-pagination"></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
function zib_widget_faq($args, $instance)
{

    $faqs = !empty($instance['faqs']) && is_array($instance['faqs']) ? $instance['faqs'] : array();
    if (!$faqs) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . esc_html__('常见问题解答：', 'zib_language') . '</b>' . esc_html__('请添加至少一条问题', 'zib_language') . '</div>';
        }
        return;
    }

    $search_ph  = isset($instance['search_placeholder']) ? $instance['search_placeholder'] : __('搜索问题…', 'zib_language');
    $search_btn = isset($instance['search_btn_text']) ? $instance['search_btn_text'] : __('搜索', 'zib_language');

    $doc_title = isset($instance['doc_title']) ? $instance['doc_title'] : '';
    $doc_desc  = isset($instance['doc_desc']) ? $instance['doc_desc'] : '';
    $doc_icon  = !empty($instance['doc_icon']) ? $instance['doc_icon'] : 'fa fa-book';
    $doc_links = !empty($instance['doc_links']) && is_array($instance['doc_links']) ? $instance['doc_links'] : array();

    $help_title = isset($instance['help_title']) ? $instance['help_title'] : '';
    $help_sub   = isset($instance['help_subtitle']) ? $instance['help_subtitle'] : '';
    $help_icon  = !empty($instance['help_icon']) ? $instance['help_icon'] : 'fa fa-mortar-board';
    $help_btn   = isset($instance['help_btn_text']) ? $instance['help_btn_text'] : '';
    $help_url   = isset($instance['help_btn_url']) ? $instance['help_btn_url'] : '';

    $uid = function_exists('wp_unique_id') ? wp_unique_id('produck-faq-') : uniqid('produck-faq-');

    echo '<div class="mb20">';
    $animation_class = Zib_CFSwidget::animation_class($instance, true);

    echo '<section class="produck-wid-faq-block" id="' . esc_attr($uid) . '">';

    if (!empty($instance['search_s'])) {
        /* 头部：渐变大标题 + 副标题 + 搜索 */
        echo '<header class="produck-faq-head' . $animation_class . '">';
        echo '<form method="get" class="search-form" action="' . esc_url(home_url()) . '">';

        echo '<div class="produck-faq-search" role="search">';
        echo '<span class="produck-faq-search-icon" aria-hidden="true">' . zib_get_cfs_icon('fa fa-search') . '</span>';
        echo '<input type="search" id="' . esc_attr($uid) . '-q" class="produck-faq-search-input" name="s" placeholder="' . esc_attr($search_ph) . '" autocomplete="off" title="' . esc_attr($search_ph) . '">';
        echo '<button type="button" type="submit" class="produck-faq-search-btn"><span>' . esc_html($search_btn) . '</span></button>';
        echo '</div>';
        echo '</form>';

        echo '</header>';
    }

// 文档和帮助卡片
    $has_doc  = ($doc_title !== '' || $doc_desc !== '' || $doc_links);
    $has_help = ($help_title !== '' || $help_sub !== '' || ($help_btn && $help_url));

    echo '<div class="produck-faq-main' . ((!$has_doc && !$has_help) ? ' produck-faq-main--full' : '') . '">';
    echo '<div class="produck-faq-list-col flex hh">';

    $badge_classes = array('produck-faq-badge--purple', 'produck-faq-badge--pink', 'produck-faq-badge--green', 'produck-faq-badge--orange', 'produck-faq-badge--blue');
    $n             = 0;
    foreach ($faqs as $f) {
        if (empty($f['question'])) {
            continue;
        }
        $question = $f['question'];
        $answer   = !empty($f['answer']) ? $f['answer'] : '';
        $fcat     = isset($f['category']) ? sanitize_key($f['category']) : 'all';
        if ($fcat === '') {
            $fcat = 'all';
        }

        $n++;
        $badge_cls = $badge_classes[($n - 1) % count($badge_classes)];
        $num       = str_pad((string) $n, 2, '0', STR_PAD_LEFT);

        echo '<div class="produck-faq-summary' . $animation_class . '">';
        echo '<span class="produck-faq-badge ' . esc_attr($badge_cls) . '">' . esc_html($num) . '</span>';
        echo '<span class="produck-faq-qblock">';
        echo '<span class="produck-faq-q">' . wp_kses_post($question) . '</span>';
        if ($answer !== '') {
            echo '<span class="produck-faq-preview muted-2-color">' . ($answer) . '</span>';
        }
        echo '</span>';
        echo '<i class="fa fa-angle-down produck-faq-chevron em12" aria-hidden="true"></i>';
        echo '</div>';
    }

    echo '</div>';

    if ($has_doc || $has_help) {
        echo '<div class="produck-faq-aside">';
        if ($has_doc) {
            echo '<div class="produck-faq-doc-card zib-widget' . $animation_class . '">';
            echo '<div class="produck-faq-doc-head">';
            if ($doc_icon) {
                echo '<span class="produck-faq-doc-head-ico">' . zib_get_cfs_icon($doc_icon) . '</span>';
            }
            echo '<div>';
            if ($doc_title) {
                echo '<h3 class="produck-faq-doc-title">' . esc_html($doc_title) . '</h3>';
            }
            if ($doc_desc) {
                echo '<p class="produck-faq-doc-desc muted-2-color">' . nl2br(esc_html($doc_desc)) . '</p>';
            }
            echo '</div></div>';
            if ($doc_links) {
                echo '<ul class="produck-faq-doc-list">';
                foreach ($doc_links as $dl) {
                    if (empty($dl['title']) && empty($dl['url'])) {
                        continue;
                    }
                    $durl   = !empty($dl['url']) ? $dl['url'] : '#';
                    $_title = !empty($dl['title']) ? $dl['title'] : '';
                    if (!empty($dl['tag'])) {
                        $_title .= '<span class="badg badg-sm c-red ml6">' . esc_html($dl['tag']) . '</span>';
                    }
                    echo '<li class="produck-faq-doc-li">';
                    echo '<a class="produck-faq-doc-a" href="' . esc_url($durl) . '">';
                    if (!empty($dl['icon'])) {
                        echo '<span class="produck-faq-doc-li-ico">' . zib_get_cfs_icon($dl['icon']) . '</span>';
                    }
                    echo '<span class="produck-faq-doc-li-mid">';
                    echo '<span class="produck-faq-doc-li-title">' . $_title . '</span>';
                    if (!empty($dl['desc'])) {
                        echo '<span class="produck-faq-doc-li-desc muted-2-color">' . esc_html($dl['desc']) . '</span>';
                    }
                    echo '</span>';
                    echo '<span class="produck-faq-doc-go flex ac">' . esc_html(!empty($dl['btn_text']) ? $dl['btn_text'] : __('查看教程', 'zib_language')) . ' <i class="em12 fa fa-angle-right"></i></span>';
                    echo '</a></li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }

        if ($has_help) {
            echo '<div class="produck-faq-help-card' . $animation_class . '">';
            echo '<div class="produck-faq-help-inner">';
            echo '<div class="produck-faq-help-top flex ac gap10">';
            if ($help_icon) {
                echo '<span class="produck-faq-help-ico">' . zib_get_cfs_icon($help_icon) . '</span>';
            }
            echo '<div class="produck-faq-help-text">';
            if ($help_title) {
                echo '<h3 class="produck-faq-help-title">' . esc_html($help_title) . '</h3>';
            }
            if ($help_sub) {
                echo '<p class="produck-faq-help-sub">' . nl2br(esc_html($help_sub)) . '</p>';
            }
            echo '</div>';
            echo '</div>';
            if ($help_btn && $help_url) {
                echo '<a class="produck-faq-help-btn" href="' . esc_url($help_url) . '">' . esc_html($help_btn) . ' <i class="fa fa-arrow-right"></i></a>';
            }
            echo '</div></div>';
        }

        echo '</div>';
    }

    echo '</div>';

    echo '</section>';
    echo '</div>';
}

function zib_widget_stats($args, $instance)
{
    $stats = !empty($instance['stats']) && is_array($instance['stats']) ? $instance['stats'] : array();
    if (!$stats) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20">' . esc_html__('请添加至少一个数据项', 'zib_language') . '</div>';
        }
        return;
    }

    $layout    = !empty($instance['layout']) ? $instance['layout'] : 'split';
    $m_columns = isset($instance['mobile_columns']) ? (int) $instance['mobile_columns'] : 2;
    $m_columns = in_array($m_columns, array(1, 2, 3)) ? $m_columns : 2;

/* 自动循环色板 */
    $colorway     = array('pink', 'purple', 'orange', 'blue', 'green');
    $allow_colors = array('pink', 'purple', 'orange', 'blue', 'green', 'auto');

    echo '<div class="mb20">';

/* ============== 渲染卡片网格 ============== */
    if ($layout === 'split') {
        $pc_cols = isset($instance['split_card_columns']) ? (int) $instance['split_card_columns'] : 2;
        $pc_cols = in_array($pc_cols, array(1, 2, 3, 4)) ? $pc_cols : 2;

        $cards_html = '<div class="stat-number-wrap stats-cards pc-cols-' . $pc_cols . ' m-cols-' . $m_columns . '">';
        $i          = 0;
        foreach ($stats as $s) {
            $number = isset($s['number']) ? trim((string) $s['number']) : '';
            if ($number === '') {
                continue;
            }
            $color = isset($s['color']) && in_array($s['color'], $allow_colors, true) ? $s['color'] : 'auto';
            if ($color === 'auto') {
                $color = $colorway[$i % count($colorway)];
            }
            $unit   = isset($s['unit']) ? $s['unit'] : '';
            $prefix = isset($s['prefix']) ? $s['prefix'] : '';
            $label  = isset($s['label']) ? $s['label'] : '';
            $icon   = isset($s['icon']) ? $s['icon'] : '';

            $cards_html .= '<div class="stat-item color-' . esc_attr($color) . '">';
            if ($icon) {
                $cards_html .= '<div class="stat-icon-block">' . zib_get_cfs_icon($icon) . '</div>';
            }

            $cards_html .= '<div class="stat-value">';
            if ($prefix !== '') {
                $cards_html .= '<span class="stat-prefix">' . esc_html($prefix) . '</span>';
            }
            $cards_html .= '<span class="stat-number" data-target="' . esc_attr($number) . '">0</span>';
            if ($unit !== '') {
                $cards_html .= '<span class="stat-unit">' . esc_html($unit) . '</span>';
            }
            $cards_html .= '</div>';
            if ($label) {
                $cards_html .= '<div class="stat-label">' . esc_html($label) . '</div>';
            }
            /* 数字下方短渐变分隔条 */
            $cards_html .= '<span class="stat-divider" aria-hidden="true"></span>';
            /* 底部柔和双层山峰装饰（远山 + 近山） */
            $cards_html .= '<svg class="stat-decor" viewBox="0 0 200 80" preserveAspectRatio="none" aria-hidden="true">'
                . '<path class="decor-back" d="M0,80 L0,52 Q40,28 90,46 T200,38 L200,80 Z"/>'
                . '<path class="decor-front" d="M0,80 L0,66 Q55,48 105,60 Q150,72 200,52 L200,80 Z"/>'
                . '</svg>';
            $cards_html .= '</div>';
            $i++;
        }
        $cards_html .= '</div>';

        /* 左侧文案 */
        $intro_html = '';
        $badge_text = isset($instance['intro_badge_text']) ? $instance['intro_badge_text'] : '';
        $title      = isset($instance['intro_title']) ? $instance['intro_title'] : '';
        $highlight  = isset($instance['intro_title_highlight']) ? trim((string) $instance['intro_title_highlight']) : '';
        $subtitle   = isset($instance['intro_subtitle']) ? $instance['intro_subtitle'] : '';
        $trust      = !empty($instance['intro_trust_badges']) && is_array($instance['intro_trust_badges']) ? $instance['intro_trust_badges'] : array();

        $has_intro = $badge_text || $title || $subtitle || $trust;

        if ($has_intro) {
            $intro_html .= '<div class="stats-intro">';

            if ($badge_text) {
                $badge_icon = !empty($instance['intro_badge_icon']) ? $instance['intro_badge_icon'] : 'fa fa-shield';
                $intro_html .= '<span class="intro-badge">' . zib_get_cfs_icon($badge_icon) . esc_html($badge_text) . '</span>';
            }

            if ($title) {
                $title_html = esc_html($title);
                /* 把首次出现的 highlight 文本包裹为 <em> 渐变高亮 */
                if ($highlight !== '') {
                    $highlight_esc = esc_html($highlight);
                    $pos           = strpos($title_html, $highlight_esc);
                    if ($pos !== false) {
                        $title_html = substr($title_html, 0, $pos)
                        . '<em>' . $highlight_esc . '</em>'
                        . substr($title_html, $pos + strlen($highlight_esc));
                    }
                }
                $intro_html .= '<h2 class="intro-title">' . $title_html . '</h2>';
            }

            if ($subtitle) {
                $intro_html .= '<p class="intro-subtitle muted-2-color">' . nl2br(esc_html($subtitle)) . '</p>';
            }

            if ($trust) {
                $intro_html .= '<div class="intro-trust-badges">';
                foreach ($trust as $tb) {
                    if (empty($tb['title']) && empty($tb['desc'])) {
                        continue;
                    }
                    $intro_html .= '<div class="trust-item">';
                    if (!empty($tb['icon'])) {
                        $intro_html .= '<span class="trust-icon">' . zib_get_cfs_icon($tb['icon']) . '</span>';
                    }
                    $intro_html .= '<div class="trust-text">';
                    if (!empty($tb['title'])) {
                        $intro_html .= '<h6>' . esc_html($tb['title']) . '</h6>';
                    }
                    if (!empty($tb['desc'])) {
                        $intro_html .= '<p class="muted-2-color">' . esc_html($tb['desc']) . '</p>';
                    }
                    $intro_html .= '</div></div>';
                }
                $intro_html .= '</div>';
            }

            $intro_html .= '</div>';
        }

        $wrap_class = 'produck-wid-stats layout-split' . ($has_intro ? '' : ' no-intro');
        echo '<div class="' . esc_attr($wrap_class) . '">';
        echo $intro_html . $cards_html;
        echo '</div>';

    } else {
        /* ============== 旧版极简网格 ============== */
        $card_style   = !empty($instance['card_style']) ? $instance['card_style'] : 'plain';
        $number_color = !empty($instance['number_color']) ? $instance['number_color'] : 'pink';

        $wrap_class = 'produck-wid-stats layout-grid card-' . $card_style . ' num-' . $number_color
        . ' cols-' . count($stats) . ' m-cols-' . $m_columns;

        echo '<div class="' . esc_attr($wrap_class) . ' stat-number-wrap">';
        foreach ($stats as $s) {
            $number = isset($s['number']) ? trim((string) $s['number']) : '';
            if ($number === '') {
                continue;
            }
            $unit   = isset($s['unit']) ? $s['unit'] : '';
            $prefix = isset($s['prefix']) ? $s['prefix'] : '';
            $label  = isset($s['label']) ? $s['label'] : '';
            $icon   = isset($s['icon']) ? $s['icon'] : '';

            echo '<div class="stat-item">';
            if ($icon) {
                echo zib_get_cfs_icon($icon, 'stat-icon');
            }
            echo '<div class="stat-value">';
            if ($prefix !== '') {
                echo '<span class="stat-prefix">' . esc_html($prefix) . '</span>';
            }
            echo '<span class="stat-number" data-target="' . esc_attr($number) . '">0</span>';
            if ($unit !== '') {
                echo '<span class="stat-unit">' . esc_html($unit) . '</span>';
            }
            echo '</div>';
            if ($label) {
                echo '<div class="stat-label muted-2-color">' . esc_html($label) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</div>';
}
function zib_widget_highlights_render_card($card, $size)
{
    $allow_colors = array('pink', 'purple', 'orange', 'blue', 'green', 'red');
    $color        = !empty($card['color']) && in_array($card['color'], $allow_colors, true) ? $card['color'] : 'pink';
    $icon         = !empty($card['icon']) ? $card['icon'] : '';
    $title        = !empty($card['title']) ? $card['title'] : '';
    $desc         = !empty($card['desc']) ? $card['desc'] : '';

    if (!$title && !$desc && !$icon) {
        return '';
    }

    $is_full = ($size === 'large' || $size === 'medium');

/* features：textarea 一行一条 */
    $features = array();
    if ($is_full && !empty($card['features'])) {
        $lines = preg_split('/\r\n|\r|\n/', (string) $card['features']);
        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln !== '') {
                $features[] = $ln;
            }
        }
    }

    $btn_text = $is_full && !empty($card['button_text']) ? $card['button_text'] : '';
    $btn_url  = $is_full && !empty($card['button_url']) ? $card['button_url'] : '';
    $image    = $is_full && !empty($card['image']) ? $card['image'] : '';
    $image_d  = $is_full && !empty($card['image_dark']) ? $card['image_dark'] : '';

    $has_btn   = ($btn_text && $btn_url);
    $has_media = ($image !== '');

    $card_class = 'hl-card hl-card-' . $size . ' color-' . $color;
    if (!$has_media && $is_full) {
        $card_class .= ' hl-card-no-media';
    }

    $html = '<article class="' . esc_attr($card_class) . '">';
/* 右侧产品图 */
    if ($has_media) {
        $alt  = $title ? $title : '';
        $more = 'class="hl-image" loading="lazy"';
        $html .= '<div class="hl-card-media">';
        if (function_exists('zib_get_adaptive_theme_img')) {
            $html .= zib_get_adaptive_theme_img($image, $image_d, $alt, $more);
        } else {
            $html .= '<img class="hl-image" src="' . esc_url($image) . '" alt="' . esc_attr($alt) . '" loading="lazy">';
        }
        $html .= '</div>';
    }

    $html .= '<div class="hl-card-body relative">';

/* 头部：图标 + 标题 + 描述 */
    $html .= '<div class="hl-card-head">';
    if ($icon) {
        $html .= '<div class="hl-icon-block">' . zib_get_cfs_icon($icon) . '</div>';
    }
    $html .= '<div class="hl-text">';
    if ($title) {
        $html .= '<h3 class="hl-title">' . esc_html($title) . '</h3>';
    }
    if ($desc) {
        $html .= '<p class="hl-desc muted-2-color">' . esc_html($desc) . '</p>';
    }
    $html .= '</div></div>'; // hl-text / hl-card-head

/* 功能列表 */
    if ($features) {
        $html .= '<ul class="hl-features">';
        foreach ($features as $feat) {
            $html .= '<li><i class="fa fa-check hl-tick" aria-hidden="true"></i><span>' . esc_html($feat) . '</span></li>';
        }
        $html .= '</ul>';
    }

/* 按钮 */
    if ($has_btn) {
        $html .= '<a class="hl-btn" href="' . esc_url($btn_url) . '">'
        . '<span>' . esc_html($btn_text) . '</span>'
            . '<i class="fa fa-arrow-right" aria-hidden="true"></i>'
            . '</a>';
    }

    $html .= '</div>'; // .hl-card-body

    $html .= '</article>';
    return $html;
}
function zib_widget_highlights($args, $instance)
{
    $large  = !empty($instance['large_cards']) && is_array($instance['large_cards']) ? $instance['large_cards'] : array();
    $medium = !empty($instance['medium_cards']) && is_array($instance['medium_cards']) ? $instance['medium_cards'] : array();
    $small  = !empty($instance['small_cards']) && is_array($instance['small_cards']) ? $instance['small_cards'] : array();

    if (!$large && !$medium && !$small) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20">' . esc_html__('请至少添加一张卡片', 'zib_language') . '</div>';
        }
        return;
    }

    $mobile_image = !empty($instance['mobile_image']) ? $instance['mobile_image'] : 'shrink';
    $wrap_class   = 'produck-wid-highlights mobile-image-' . $mobile_image;

    echo '<div class="mb20">';
    $animation_class = Zib_CFSwidget::animation_class($instance, true);

    echo '<div class="' . esc_attr($wrap_class) . '">';

/* 大卡片行 */
    if ($large) {
        echo '<div class="hl-row hl-row-large' . $animation_class . '">';
        foreach ($large as $card) {
            echo zib_widget_highlights_render_card($card, 'large');
        }
        echo '</div>';
    }

/* 中卡片行 */
    if ($medium) {
        echo '<div class="hl-row hl-row-medium' . $animation_class . '">';
        foreach ($medium as $card) {
            echo zib_widget_highlights_render_card($card, 'medium');
        }
        echo '</div>';
    }

/* 小卡片行 */
    if ($small) {
        echo '<div class="hl-row hl-row-small' . $animation_class . '">';
        foreach ($small as $card) {
            echo zib_widget_highlights_render_card($card, 'small');
        }
        echo '</div>';
    }

    echo '</div>';

    echo '</div>';
}
function zib_widget_feature_intro_render_panel($slide, $browser_chrome, $ratio = 45)
{
    $type = !empty($slide['media_type']) ? $slide['media_type'] : 'image';
    ob_start();

    if ('video' === $type && !empty($slide['video_url'])) {
        $url    = esc_url($slide['video_url']);
        $poster = !empty($slide['video_poster']) ? esc_url($slide['video_poster']) : '';
        echo '<video class="fi-video fit-cover lazyload" preload="metadata" autoplay loop muted data-src="' . esc_url($url) . '" data-poster="' . esc_url($poster) . '"></video>';
    } elseif (!empty($slide['image'])) {
        $img   = $slide['image'];
        $img_d = !empty($slide['image_dark']) ? $slide['image_dark'] : '';
        $alt   = !empty($slide['tab_label']) ? $slide['tab_label'] : '';
        $more  = 'class="fi-media-img fit-cover"';
        if (function_exists('zib_get_adaptive_theme_img')) {
            echo zib_get_adaptive_theme_img($img, $img_d, $alt, $more);
        } else {
            echo '<img src="' . esc_url($img) . '" alt="' . esc_attr($alt) . '" class="fi-media-img" loading="lazy">';
        }
    }

    $inner = ob_get_clean();
    if ('' === trim($inner)) {
        return;
    }

    if ($browser_chrome) {
        echo '<div class="fi-browser">';
        echo '<div class="fi-browser-bar" aria-hidden="true"><span></span><span></span><span></span></div>';
        echo '<div class="fi-browser-body graphic" style="padding-bottom: ' . (int) ($ratio) . '%;">' . $inner . '</div>';
        echo '</div>';
    } else {
        echo '<div class="fi-browser fi-browser-nochrome"><div class="fi-browser-body graphic" style="padding-bottom: ' . (int) ($ratio) . '%;">' . $inner . '</div></div>';
    }
}

/**
 * @param array $args
 * @param array $instance
 */
function zib_widget_feature_intro($args, $instance)
{
    $slides = !empty($instance['slides']) && is_array($instance['slides']) ? $instance['slides'] : array();
    $valid  = array();
    foreach ($slides as $s) {
        $mt = !empty($s['media_type']) ? $s['media_type'] : 'image';
        if ('video' === $mt) {
            if (empty($s['video_url'])) {
                continue;
            }
        } elseif (empty($s['image'])) {
            continue;
        }
        $valid[] = $s;
    }

    if (!$valid) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20">' . esc_html__('请至少添加一组带图片/视频的媒体TAB', 'zib_language') . '</div>';
        }
        return;
    }

    $animation      = !empty($instance['obs_animation']) ? $instance['obs_animation'] : '';
    $animation_attr = $animation ? ' data-animation="' . esc_attr($animation) . '"' : '';
    $animation_attr .= $animation && !empty($instance['animation_repeat']) ? ' data-animation-repeat="true"' : '';

    $media_pos = !empty($instance['media_position']) && 'left' === $instance['media_position'] ? 'left' : 'right';
    $heading   = isset($instance['heading']) ? $instance['heading'] : '';
    $desc      = isset($instance['description']) ? $instance['description'] : '';
    $show_stat = !empty($instance['show_stat']);
    $chrome    = !isset($instance['browser_chrome']) || $instance['browser_chrome'];

    $wid = !empty($args['widget_id']) ? $args['widget_id'] : 'produck-wid-fi-' . uniqid('', false);

    echo '<div class="mb20' . (!empty($instance['show_box']) ? ' zib-widget' : '') . '">';

    echo '<div class="produck-wid-feature-intro stat-number-wrap" data-produck-wid-fi id="' . esc_attr($wid) . '">';
    echo '<div class="fi-row fi-row--media-' . esc_attr($media_pos) . '">';

    echo '<div class="fi-col fi-col-text' . ($animation ? ' obs-animate ani-' . $animation : '') . '"' . $animation_attr . '>';
    if ('' !== $heading) {
        echo '<h2 class="fi-title">' . wp_kses_post($heading) . '</h2>';
    }
    if ('' !== $desc) {
        echo '<div class="fi-desc">' . wp_kses_post($desc) . '</div>';
    }
    if ($show_stat) {
        $sn = isset($instance['stat_number']) ? $instance['stat_number'] : '3';
        $ss = isset($instance['stat_suffix']) ? $instance['stat_suffix'] : '+';
        $sl = isset($instance['stat_label']) ? $instance['stat_label'] : '';
        echo '<div class="fi-stat"><span class="fi-stat-num stat-number" data-duration="800" data-target="' . esc_attr($sn) . '">0</span>';
        echo '<div class="fi-stat-top">';
        if ('' !== $sl) {
            echo '<span class="fi-stat-label">' . esc_html($sl) . '</span>';
        }
        if ('' !== $ss) {
            echo '<span class="fi-stat-plus">' . esc_html($ss) . '</span>';
        }

        echo '</div>';
        echo '</div>';
    }

    if (count($valid) > 1) {
        echo '<div class="fi-tabs" role="tablist">';
        foreach ($valid as $i => $s) {
            $is_first = (0 === $i);
            $tab_cls  = $is_first ? 'fi-tab is-active' : 'fi-tab';
            $sel      = $is_first ? 'true' : 'false';
            echo '<button type="button" class="' . esc_attr($tab_cls) . '" role="tab" aria-selected="' . esc_attr($sel) . '">' . esc_html($s['tab_label']) . '</button>';
        }
        echo '</div>';
    }

    echo '</div>';

    echo '<div class="fi-col fi-col-media' . ($animation ? ' obs-animate ani-' . $animation : '') . '"' . $animation_attr . '>';
    echo '<div class="fi-panels' . (!empty($instance['imgbox']) ? ' imgbox-container' : '') . '">';
    foreach ($valid as $i => $s) {
        $is_first = (0 === $i);
        $p_cls    = $is_first ? 'fi-panel is-active' : 'fi-panel';
        $p_hid    = $is_first ? '' : ' hidden';
        echo '<div class="' . esc_attr($p_cls) . '" role="tabpanel"' . $p_hid . '>';
        zib_widget_feature_intro_render_panel($s, (bool) $chrome, $instance['media_ratio'] ?? 66);
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</div>';

    echo '</div>';
}

//按钮小工具

function zib_widget_ui_tilt_cases($args, $instance)
{
    $default = [
        'obs_animation'               => '',
        'animation_repeat'            => false,
        'intro_badge_text'            => '',
        'intro_badge_icon'            => '',
        'intro_badge_color'           => 'c-blue',
        'intro_title'                 => '',
        'intro_title_highlight'       => '',
        'intro_title_highlight_color' => '',
        'intro_subtitle'              => '',
        'intro_trust_badges'          => [],
        'intro_showcase_images'       => [],
        'intro_buttons'               => [],
    ];

    $instance = wp_parse_args($instance, $default);

    //动画class
    $animation_class = Zib_CFSwidget::animation_class($instance, true);
    //左侧
    $left_html = '';
    if ($instance['intro_badge_text']) {
        $left_html .= '<div class="tilt-cases-badge"><span class="badg radius ' . ($instance['intro_badge_color']) . '">' . zib_get_cfs_icon($instance['intro_badge_icon']) . '<span class="ml6">' . esc_html($instance['intro_badge_text']) . '</span></span></div>';
    }

    if ($instance['intro_title']) {

        //按行分隔
        $title_lines = preg_split('/\r\n|\r|\n/', (string) $instance['intro_title']);

        $title_html = '';
        foreach ($title_lines as $i => $title_line) {
            $title_html .= '<div class="tilt-cases-title-line ' . ($i === 0 ? '' : ' t-sub') . '">' . $title_line . '</div>';
        }

        if ($instance['intro_title_highlight']) {
            $title = str_replace($instance['intro_title_highlight'], '<span class="title-highlight ' . $instance['intro_title_highlight_color'] . '">' . $instance['intro_title_highlight'] . '</span>', $title_html);
        }
        $left_html .= '<h2 class="tilt-cases-title">' . $title . '</h2>';
    }

    //subtitle
    if ($instance['intro_subtitle']) {
        $left_html .= '<p class="tilt-cases-subtitle">' . esc_html($instance['intro_subtitle']) . '</p>';
    }

    //features
    if (!empty($instance['intro_features'])) {
        $left_html .= '<ul class="tilt-cases-features flex ac hh gap10">';
        foreach ($instance['intro_features'] as $feature) {
            $left_html .= '<li>' . zib_get_cfs_icon($feature['icon']) . '<span class="ml6">' . esc_html($feature['text']) . '</span></li>';
        }
        $left_html .= '</ul>';
    }

    //actions
    if (!empty($instance['intro_buttons'])) {
        $left_html .= '<div class="tilt-cases-actions flex ac hh gap10">';
        foreach ($instance['intro_buttons'] as $button) {
            $left_html .= zib_get_widget_pay_btn_link($button['btn'] ?? [], 'but radius ' . $button['color'], zib_get_cfs_icon($button['icon']));
        }
        $left_html .= '</div>';
    }

    //trust
    if (!empty($instance['intro_trust_badges'])) {
        $left_html .= '<div class="tilt-cases-trust flex ac hh gap10">';
        foreach ($instance['intro_trust_badges'] as $trust) {
            $left_html .= '<span><span class="mr6 c-blue">' . zib_get_cfs_icon($trust['icon']) . '</span>' . esc_html($trust['text']) . '</span>';
        }
        $left_html .= '</div>';
    }

    //右侧
    $right_html = '';
    if (!empty($instance['intro_showcase_images'][0])) {
        $right_html .= '<div class="tilt-cases-showcase vanilla-tilt" data-tilt-max="10" data-tilt-speed="600" data-tilt-perspective="1500" data-tilt-mouse-event-element=".tilt-cases-mouse-container" data-tilt-reverse="true">';

        foreach ($instance['intro_showcase_images'] as $i => $item) {
            if (empty($item['image'])) {
                continue;
            }
            $scale   = isset($item['scale']) && $item['scale'] !== '' ? (float) $item['scale'] : 100;
            $scale   = zib_floatval_round($scale / 100);
            $z_index = isset($item['z_index']) && $item['z_index'] !== '' ? (int) $item['z_index'] : ($i + 1);
            $depth   = isset($item['depth']) && $item['depth'] !== '' ? (int) $item['depth'] : 20;

            $offset_pc_left  = !empty($item['offset_pc']['left']) ? (float) $item['offset_pc']['left'] : 0;
            $offset_pc_top   = !empty($item['offset_pc']['top']) ? (float) $item['offset_pc']['top'] : 0;
            $offset_pc_units = !empty($item['offset_pc']['unit']) ? $item['offset_pc']['unit'] : '%';
            $offset_m_left   = !empty($item['offset_m']['left']) ? (float) $item['offset_m']['left'] : 0;
            $offset_m_top    = !empty($item['offset_m']['top']) ? (float) $item['offset_m']['top'] : 0;
            $offset_m_units  = !empty($item['offset_m']['unit']) ? $item['offset_m']['unit'] : '%';

            $style = sprintf(
                '--pc-left:%s%s;--pc-top:%s%s;--m-left:%s%s;--m-top:%s%s;transform:translate3d(0,0,0) scale(%s);z-index:%d;',
                $offset_pc_left,
                $offset_pc_units,
                $offset_pc_top,
                $offset_pc_units,
                $offset_m_left,
                $offset_m_units,
                $offset_m_top,
                $offset_m_units,
                $scale,
                $z_index
            );

            $class = 'showcase-img showcase-item-' . ($i + 1) . ($i === 0 ? ' showcase-main' : '');

            $white_src = $item['image'];
            $dark_src  = !empty($item['image_dark']) ? $item['image_dark'] : '';
            $more      = sprintf(
                'class="%s" style="%s" data-tilt-depth="%s"',
                esc_attr($class),
                esc_attr($style),
                esc_attr($depth)
            );

            $right_html .= zib_get_adaptive_theme_img($white_src, $dark_src, '', $more);
        }

        $right_html .= '</div>';
    }

    echo '<div class="tilt-cases-mouse-container">';
    echo '<div class="widget-tilt-cases mb20' . (!empty($instance['show_box']) ? ' zib-widget' : '') . '">';
    echo '<div class="tilt-cases-container flex hh ac"><div class="tilt-cases-left flex xx' . $animation_class . '">' . $left_html . '</div><div class="tilt-cases-right ' . $animation_class . '">' . $right_html . '</div></div>';
    echo '</div>';
    echo '</div>';
}
add_action('after_setup_theme', 'zib_widget_register_cfs_produck');

function zib_widget_register_cfs_produck()
{
    Zib_CFSwidget::create('zib_widget_pricing_plans', array(
        'title'            => __('产品选项卡片', 'zib_language'),
        'zib_title'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('产品不同选项不同价格、特权的卡片，适合做产品介绍页的选项购买卡片', 'zib_language'),
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
            array(
                'title'                  => __('商品选项卡片', 'zib_language'),
                'id'                     => 'lists',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'button_title'           => __('添加一项', 'zib_language'),
                'sanitize'               => false,
                'fields'                 => array(
                    array(
                        'id'      => 'name',
                        'type'    => 'text',
                        'title'   => __('选项名称', 'zib_language'),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'desc',
                        'type'    => 'text',
                        'class'   => 'compact',
                        'title'   => __('一句话简介', 'zib_language'),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'tag',
                        'type'    => 'text',
                        'title'   => __('卡片标签', 'zib_language'),
                        'default' => '',
                    ),
                    array(
                        'title'   => __('重点选项', 'zib_language'),
                        'id'      => 'is_featured',
                        'type'    => 'switcher',
                        'default' => false,
                        'label'   => __('只能设置一个重点选项', 'zib_language'),
                    ),
                    array(
                        'id'      => 'color',
                        'type'    => 'radio',
                        'title'   => __('配色', 'zib_language'),
                        'inline'  => true,
                        'default' => 'pink',
                        'options' => array(
                            'pink'   => __('粉色', 'zib_language'),
                            'purple' => __('紫色', 'zib_language'),
                            'blue'   => __('蓝色', 'zib_language'),
                        ),
                    ),
                    array(
                        'id'         => 'intro',
                        'type'       => 'textarea',
                        'title'      => __('详情列表(一行一个)', 'zib_language'),
                        'default'    => '',
                        'attributes' => array(
                            'rows' => 3,
                        ),
                    ),
                    array(
                        'id'      => 'price',
                        'title'   => __('执行价', 'zib_language'),
                        'default' => '699',
                        'type'    => 'number',
                        'unit'    => zibpay_get_currency_unit(),
                    ),
                    array(
                        'id'      => 'show_price',
                        'title'   => __('原价', 'zib_language'),
                        'desc'    => __('显示在执行价格前面，并划掉', 'zib_language'),
                        'default' => '999',
                        'type'    => 'number',
                        'unit'    => zibpay_get_currency_unit(),
                        'class'   => 'compact',
                    ),
                    array(
                        'id'      => 'mark',
                        'type'    => 'text',
                        'title'   => __('价格前缀', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '￥',
                    ),
                    array(
                        'id'         => 'promotion_tag',
                        'title'      => __('价格促销标签', 'zib_language'),
                        'class'      => 'compact',
                        'desc'       => __('支持HTML，请注意控制长度', 'zib_language'),
                        'default'    => '',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                        'type'       => 'textarea',
                    ),
                    zib_get_widget_pay_btn_fields(),
                ),
            ),

            array(
                'title'                  => __('信任卡片', 'zib_language'),
                'id'                     => 'trust_items',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'sanitize'               => false,
                'button_title'           => __('添加一项', 'zib_language'),
                'default'                => array(
                    array(
                        'icon'  => 'fa fa-shield',
                        'title' => '正版授权',
                        'desc'  => '官方授权 · 可验真',
                    ),
                    array(
                        'icon'  => 'fa fa-gift',
                        'title' => '终身使用',
                        'desc'  => '一次购买长期可用',
                    ),
                    array(
                        'icon'  => 'fa fa-headphones',
                        'title' => '专业支持',
                        'desc'  => '工单与更新通道',
                    ),
                    array(
                        'icon'  => 'fa fa-refresh',
                        'title' => '持续更新',
                        'desc'  => '主题与模块迭代',
                    ),
                    array(
                        'icon'  => 'fa fa-lock',
                        'title' => '数据安全',
                        'desc'  => '站点数据您掌控',
                    ),
                ),
                'fields'                 => array(
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => 'fa fa-check-circle',
                    ),
                    array(
                        'id'      => 'title',
                        'type'    => 'text',
                        'title'   => __('标题', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '',
                    ),
                    array(
                        'id'      => 'desc',
                        'type'    => 'text',
                        'title'   => __('说明', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '',
                    ),
                ),
            ),
            array(
                'title'      => __('底部提示文案', 'zib_language'),
                'id'         => 'foot_note',
                'type'       => 'textarea',
                'default'    => '所有方案均包含 30 天无理由退款保障，让您购买无忧',
                'attributes' => array('rows' => 2),
            ),
            array(
                'title'        => __('底部提示图标', 'zib_language'),
                'id'           => 'foot_icon',
                'type'         => 'icon',
                'class'        => 'compact',
                'button_title' => __('选择图标', 'zib_language'),
                'default'      => 'fa fa-shield',
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_reviews', array(
        'title'       => __('产品虚拟评价', 'zib_language'),
        'zib_title'   => true,
        'zib_show'    => true,
        'description' => __('横向滑动虚拟评价卡片，每张含头像/姓名/身份/星级/评价内容', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('每屏卡片数（PC）', 'zib_language'),
                'id'      => 'desktop_per_view',
                'type'    => 'radio',
                'inline'  => true,
                'default' => '4',
                'options' => array(
                    '2' => __('2 张', 'zib_language'),
                    '3' => __('3 张', 'zib_language'),
                    '4' => __('4 张', 'zib_language'),
                    '5' => __('5 张', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('每屏卡片数（移动端）', 'zib_language'),
                'id'      => 'mobile_per_view',
                'type'    => 'radio',
                'inline'  => true,
                'default' => '1.5',
                'options' => array(
                    '1'   => __('1 张', 'zib_language'),
                    '1.1' => __('1 张 + 露出右侧', 'zib_language'),
                    '1.5' => __('1.5 张', 'zib_language'),
                    '2'   => __('2 张', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('自动轮播', 'zib_language'),
                'id'      => 'swiper_autoplay',
                'type'    => 'spinner',
                'class'   => 'compact',
                'min'     => 0,
                'max'     => 10000,
                'step'    => 1,
                'unit'    => __('秒', 'zib_language'),
                'default' => 4,
                'desc'    => __('0 表示不自动轮播，单位：秒', 'zib_language'),
            ),
            array(
                'title'   => __('循环滚动', 'zib_language'),
                'id'      => 'swiper_loop',
                'type'    => 'switcher',
                'class'   => 'compact',
                'default' => true,
            ),
            array(
                'title'                  => __('评价列表', 'zib_language'),
                'id'                     => 'reviews',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'button_title'           => __('添加评价', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array(
                        'name'    => '站长小李',
                        'role'    => '资深下载站站长',
                        'rating'  => '5',
                        'content' => '使用 Zibll 接建的资源站，会员和付费功能非常完善，大大提升了我的变现效率，强烈推荐！',
                    ),
                    array(
                        'name'    => '小美的博客',
                        'role'    => '内容创作者',
                        'rating'  => '5',
                        'content' => '主题设计干净美观，模块化布局让我可以自由组合，打造独一无二的博客网站！',
                    ),
                    array(
                        'name'    => '老张',
                        'role'    => '社区论坛站长',
                        'rating'  => '5',
                        'content' => '社区论坛系统太强大了，用户体验很好、互动性强，运营起来非常轻松！',
                    ),
                    array(
                        'name'    => '科技派',
                        'role'    => '技术博主',
                        'rating'  => '5',
                        'content' => '更新频率非常高，每次都能解决我开发日常常用需求，技术支持也很及时！',
                    ),
                ),
                'fields'                 => array(
                    array(
                        'id'      => 'name',
                        'type'    => 'text',
                        'title'   => __('姓名', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '',
                    ),
                    array(
                        'id'      => 'role',
                        'type'    => 'text',
                        'title'   => __('身份/标签', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '',
                    ),
                    array(
                        'id'      => 'avatar',
                        'type'    => 'upload',
                        'title'   => __('头像', 'zib_language'),
                        'library' => 'image',
                        'preview' => true,
                    ),
                    array(
                        'id'      => 'rating',
                        'type'    => 'select',
                        'title'   => __('星级', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '5',
                        'options' => array(
                            '5' => __('★★★★★ (5星)', 'zib_language'),
                            '4' => __('★★★★ (4星)', 'zib_language'),
                            '3' => __('★★★ (3星)', 'zib_language'),
                            '2' => __('★★ (2星)', 'zib_language'),
                            '1' => __('★ (1星)', 'zib_language'),
                        ),
                    ),
                    array(
                        'id'      => 'url',
                        'type'    => 'text',
                        'title'   => __('用户链接', 'zib_language'),
                        'class'   => 'compact',
                        'default' => '',
                    ),
                    array(
                        'id'         => 'content',
                        'type'       => 'textarea',
                        'title'      => __('评价内容', 'zib_language'),
                        'sanitize'   => false,
                        'default'    => '',
                        'attributes' => array('rows' => 3),
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_faq', array(
        'title'            => __('产品常见问题解答', 'zib_language'),
        'zib_title'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,

        'description'      => __('带搜索、左侧问答、右侧教程/帮助卡片的常见问题', 'zib_language'),
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),

            array(
                'title'   => __('显示搜索框', 'zib_language'),
                'id'      => 'search_s',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'title'   => __('搜索框占位提示', 'zib_language'),
                'id'      => 'search_placeholder',
                'class'   => 'compact',
                'type'    => 'text',
                'default' => '搜索问题，例如：安装、授权、更新…',
            ),
            array(
                'title'   => __('搜索按钮文案', 'zib_language'),
                'id'      => 'search_btn_text',
                'type'    => 'text',
                'class'   => 'compact',
                'default' => '搜索',
            ),
            array(
                'title'                  => __('问答列表', 'zib_language'),
                'id'                     => 'faqs',
                'type'                   => 'group',
                'accordion_title_number' => true,

                'button_title'           => __('添加问题', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array('category' => 'purchase', 'question' => '购买后可以使用在多个网站吗?', 'answer' => '默认授权 1 个域名，可联系客服扩展授权数。'),
                    array('category' => 'purchase', 'question' => '购买后如何获取主题?', 'answer' => '支付完成后可在用户中心立即下载，永久免费。'),
                    array('category' => 'install', 'question' => '是否提供安装服务?', 'answer' => '提供有偿安装与配置服务，详情联系客服。'),
                    array('category' => 'updates', 'question' => '是否支持免费更新?', 'answer' => '终身免费更新，购买后所有版本都可下载。'),
                    array('category' => 'purchase', 'question' => '是否支持退款?', 'answer' => '虚拟商品默认不支持退款，详情请阅读购买说明。'),
                    array('category' => 'support', 'question' => '遇到问题如何获得支持?', 'answer' => '可在官方论坛提问，或联系客服 1 对 1 协助。'),
                    array('category' => 'features', 'question' => '主题是否支持二次开发?', 'answer' => '完全开放代码，支持二次开发，提供详细的开发文档。'),
                    array('category' => 'features', 'question' => '主题是否兼容最新的 WordPress 版本?', 'answer' => '持续跟进 WordPress 最新版本，保证良好兼容。'),
                    array('category' => 'install', 'question' => '是否有详细的使用文档?', 'answer' => '官方提供完整中文文档与视频教程。'),
                ),
                'fields'                 => array(
                    array(
                        'id'      => 'question',
                        'type'    => 'text',
                        'title'   => __('问题', 'zib_language'),
                        'default' => '',
                    ),
                    array(
                        'id'         => 'answer',
                        'type'       => 'textarea',
                        'title'      => __('答案', 'zib_language'),
                        'sanitize'   => false,
                        'default'    => '',
                        'attributes' => array('rows' => 3),
                    ),
                ),
            ),
            array(
                'type'    => 'subheading',
                'content' => __('— 教程与文档卡片 —', 'zib_language'),
            ),
            array(
                'title'   => __('卡片标题', 'zib_language'),
                'id'      => 'doc_title',
                'type'    => 'text',
                'default' => '教程与文档',
            ),
            array(
                'title'      => __('卡片简介', 'zib_language'),
                'id'         => 'doc_desc',
                'type'       => 'textarea',
                'class'      => 'compact',
                'sanitize'   => false,
                'default'    => '从入门到进阶，手把手带您玩转 Zibll',
                'attributes' => array('rows' => 2),
            ),
            array(
                'id'           => 'doc_icon',
                'type'         => 'icon',
                'title'        => __('卡片图标', 'zib_language'),
                'class'        => 'compact',
                'button_title' => __('选择', 'zib_language'),
                'default'      => 'fa fa-book',
            ),
            array(
                'title'                  => __('文档/教程链接列表', 'zib_language'),
                'id'                     => 'doc_links',
                'type'                   => 'group',
                'accordion_title_number' => true,

                'button_title'           => __('添加条目', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array('icon' => 'fa fa-graduation-cap', 'title' => '新手指南', 'desc' => '安装与基础配置', 'url' => '#', 'tag' => '热门'),
                    array('icon' => 'fa fa-cog', 'title' => '主题设置', 'desc' => '后台选项详解', 'url' => '#', 'tag' => ''),
                    array('icon' => 'fa fa-list-alt', 'title' => '功能手册', 'desc' => '各模块使用说明', 'url' => '#', 'tag' => '进阶'),
                    array('icon' => 'fa fa-code', 'title' => '开发文档', 'desc' => '钩子与二次开发', 'url' => '#', 'tag' => ''),
                ),
                'fields'                 => array(
                    array('id' => 'title', 'type' => 'text', 'title' => __('标题', 'zib_language'), 'class' => 'compact', 'default' => ''),
                    array('id' => 'icon', 'type' => 'icon', 'title' => __('图标', 'zib_language'), 'class' => 'compact', 'default' => 'fa fa-file-text-o'),
                    array('id' => 'desc', 'type' => 'text', 'title' => __('描述', 'zib_language'), 'class' => 'compact', 'default' => ''),
                    array('id' => 'url', 'type' => 'text', 'title' => __('链接', 'zib_language'), 'class' => 'compact', 'default' => '#'),
                    array('id' => 'tag', 'type' => 'text', 'title' => __('角标（可选）', 'zib_language'), 'class' => 'compact', 'default' => '', 'desc' => __('如：热门、进阶', 'zib_language')),
                    array('id' => 'btn_text', 'type' => 'text', 'title' => __('按钮文案（可选）', 'zib_language'), 'class' => 'compact', 'default' => '', 'desc' => __('如：查看教程', 'zib_language')),
                ),
            ),
            array(
                'type'    => 'subheading',
                'content' => __('— 更多帮助卡片（渐变底） —', 'zib_language'),
            ),
            array(
                'title'   => __('标题', 'zib_language'),
                'id'      => 'help_title',
                'type'    => 'text',
                'default' => '更多帮助与支持',
            ),
            array(
                'title'      => __('副标题', 'zib_language'),
                'id'         => 'help_subtitle',
                'type'       => 'textarea',
                'class'      => 'compact',
                'sanitize'   => false,
                'default'    => '工单与在线客服，随时为您解答',
                'attributes' => array('rows' => 2),
            ),
            array(
                'id'           => 'help_icon',
                'type'         => 'icon',
                'title'        => __('图标', 'zib_language'),
                'class'        => 'compact',
                'button_title' => __('选择', 'zib_language'),
                'default'      => 'fa fa-mortar-board',
            ),
            array(
                'title'   => __('按钮文案', 'zib_language'),
                'id'      => 'help_btn_text',
                'type'    => 'text',
                'class'   => 'compact',
                'default' => '联系客服',
            ),
            array(
                'title'   => __('按钮链接', 'zib_language'),
                'id'      => 'help_btn_url',
                'type'    => 'text',
                'class'   => 'compact',
                'default' => '#',
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_stats', array(
        'title'       => __('产品数据展示', 'zib_language'),
        'zib_title'   => true,
        'zib_show'    => true,
        'description' => __('横排数字统计展示模块，常用于产品介绍页面，展示产品数据', 'zib_language'),
        'reminder'    => __('此模块适不能放置在较小的容器中', 'zib_language'),
        'size'        => 'big',
        'fields'      => array(
            array(
                'title'   => __('布局方式', 'zib_language'),
                'id'      => 'layout',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'split',
                'options' => array(
                    'split' => __('左右分栏（左：文案+信任 / 右：彩色卡片）', 'zib_language'),
                    'grid'  => __('极简网格（仅数字+标签）', 'zib_language'),
                ),
            ),

            /* ============== split 布局：左侧文案 ============== */
            array(
                'title'      => __('徽标图标', 'zib_language'),
                'id'         => 'intro_badge_icon',
                'type'       => 'icon',
                'class'      => 'compact',
                'default'    => 'fa fa-shield',
                'dependency' => array('layout', '==', 'split'),
            ),
            array(
                'title'      => __('徽标文字', 'zib_language'),
                'id'         => 'intro_badge_text',
                'type'       => 'text',
                'class'      => 'compact',
                'default'    => '值得信赖的选择',
                'dependency' => array('layout', '==', 'split'),
            ),
            array(
                'title'      => __('主标题', 'zib_language'),
                'id'         => 'intro_title',
                'type'       => 'text',
                'default'    => '超 3,000+ 站长的共同选择',
                'dependency' => array('layout', '==', 'split'),
            ),
            array(
                'title'      => __('主标题高亮片段', 'zib_language'),
                'id'         => 'intro_title_highlight',
                'type'       => 'text',
                'class'      => 'compact',
                'default'    => '3,000+',
                'desc'       => __('主标题中要被渐变高亮的部分（首次出现时被替换）', 'zib_language'),
                'dependency' => array('layout', '==', 'split'),
            ),
            array(
                'title'      => __('副标题', 'zib_language'),
                'id'         => 'intro_subtitle',
                'type'       => 'textarea',
                'default'    => "Zibll 主题以强大的功能、稳定的性能和优质的服务，赢得了广大站长的信赖。\n我们持续迭代更新，只为您提供更好的产品和服务体验。",
                'dependency' => array('layout', '==', 'split'),
            ),
            array(
                'title'                  => __('信任徽章（左下两栏）', 'zib_language'),
                'id'                     => 'intro_trust_badges',
                'type'                   => 'group',
                'accordion_title_number' => true,

                'button_title'           => __('添加徽章', 'zib_language'),
                'sanitize'               => false,
                'dependency'             => array('layout', '==', 'split'),
                'default'                => array(
                    array('icon' => 'fa fa-shield', 'title' => '持续迭代更新', 'desc' => '定期更新，功能不断完善'),
                    array('icon' => 'fa fa-heart', 'title' => '专业技术支持', 'desc' => '快速响应解决问题'),
                ),
                'fields'                 => array(
                    array('id' => 'icon', 'type' => 'icon', 'title' => __('图标', 'zib_language'), 'class' => 'compact', 'default' => ''),
                    array('id' => 'title', 'type' => 'text', 'title' => __('徽章标题', 'zib_language'), 'class' => 'compact', 'default' => ''),
                    array('id' => 'desc', 'type' => 'text', 'title' => __('徽章描述', 'zib_language'), 'class' => 'compact', 'default' => ''),
                ),
            ),
            array(
                'title'      => __('右侧卡片每行列数（PC）', 'zib_language'),
                'id'         => 'split_card_columns',
                'type'       => 'radio',
                'inline'     => true,
                'default'    => '4',
                'dependency' => array('layout', '==', 'split'),
                'options'    => array(
                    '1' => __('1 列', 'zib_language'),
                    '2' => __('2 列', 'zib_language'),
                    '3' => __('3 列', 'zib_language'),
                    '4' => __('4 列', 'zib_language'),
                ),
            ),

            /* ============== grid 布局（旧版） ============== */
            array(
                'title'      => __('显示样式', 'zib_language'),
                'id'         => 'card_style',
                'type'       => 'radio',
                'inline'     => true,
                'default'    => 'plain',
                'dependency' => array('layout', '==', 'grid'),
                'options'    => array(
                    'plain'    => __('极简（无背景）', 'zib_language'),
                    'box'      => __('盒子卡片', 'zib_language'),
                    'gradient' => __('渐变彩色卡片', 'zib_language'),
                ),
            ),
            array(
                'title'      => __('数字颜色', 'zib_language'),
                'id'         => 'number_color',
                'type'       => 'radio',
                'inline'     => true,
                'default'    => 'pink',
                'dependency' => array('layout', '==', 'grid'),
                'options'    => array(
                    'pink'    => __('粉红渐变', 'zib_language'),
                    'blue'    => __('蓝色渐变', 'zib_language'),
                    'theme'   => __('主题色', 'zib_language'),
                    'inherit' => __('继承父级', 'zib_language'),
                ),
            ),

            /* ============== 通用 ============== */
            array(
                'title'   => __('每行列数（移动端）', 'zib_language'),
                'id'      => 'mobile_columns',
                'type'    => 'radio',
                'inline'  => true,
                'default' => '2',
                'options' => array(
                    '1' => __('1 列', 'zib_language'),
                    '2' => __('2 列', 'zib_language'),
                    '3' => __('3 列', 'zib_language'),
                ),
            ),
            array(
                'title'                  => __('数据项', 'zib_language'),
                'id'                     => 'stats',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'button_title'           => __('添加数据项', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array('color' => 'pink', 'icon' => 'fa fa-users', 'number' => '3000', 'unit' => '+', 'label' => '活跃用户'),
                    array('color' => 'purple', 'icon' => 'fa fa-window-restore', 'number' => '10000', 'unit' => '+', 'label' => '网站使用'),
                    array('color' => 'orange', 'icon' => 'fa fa-thumbs-up', 'number' => '99', 'unit' => '%', 'label' => '好评率'),
                    array('color' => 'blue', 'icon' => 'fa fa-star', 'number' => '4.9', 'unit' => '/5', 'label' => '用户评分'),
                ),
                'fields'                 => array(
                    array(
                        'id'      => 'label',
                        'type'    => 'text',
                        'title'   => __('标签', 'zib_language'),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'color',
                        'type'    => 'select',
                        'title'   => __('配色', 'zib_language'),
                        'class'   => 'compact',
                        'default' => 'auto',
                        'options' => array(
                            'auto'   => __('自动循环', 'zib_language'),
                            'pink'   => __('粉色', 'zib_language'),
                            'purple' => __('紫色', 'zib_language'),
                            'orange' => __('橙色', 'zib_language'),
                            'blue'   => __('蓝色', 'zib_language'),
                            'green'  => __('绿色', 'zib_language'),
                        ),
                    ),
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'class'        => 'compact',
                        'default'      => '',
                    ),
                    array(
                        'id'      => 'number',
                        'type'    => 'text',
                        'title'   => __('数字', 'zib_language'),
                        'desc'    => __('支持小数，如 99.9', 'zib_language'),
                        'default' => '0',
                    ),
                    array(
                        'id'      => 'unit',
                        'type'    => 'text',
                        'title'   => __('单位/后缀', 'zib_language'),
                        'default' => '',
                        'desc'    => __('如 +、%、/5、万 等', 'zib_language'),
                    ),
                    array(
                        'id'      => 'prefix',
                        'type'    => 'text',
                        'title'   => __('前缀', 'zib_language'),
                        'default' => '',
                        'desc'    => __('如 ¥、$、超 等', 'zib_language'),
                    ),

                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_highlights', array(
        'title'            => __('产品亮点卡片', 'zib_language'),
        'zib_title'        => true,
        'zib_show'         => true,
        'zib_desc'         => true,
        'zib_animation_in' => false,
        'description'      => __('三种尺寸的产品亮点卡片聚合模块，常用于介绍产品亮点特色', 'zib_language'),
        'reminder'         => __('此模块适合放在页面的顶部全宽度容器中，不能放置在较小的容器中', 'zib_language'),
        'size'             => 'big',
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
            array(
                'title'                  => __('大卡片', 'zib_language'),
                'id'                     => 'large_cards',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'button_title'           => __('添加大卡片', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array(
                        'color'       => 'pink', 'icon'       => 'fa fa-shopping-cart',
                        'title'       => '商城系统', 'desc'       => '完整的电商解决方案，助力内容变现',
                        'features'    => "商品管理 & 订单系统\n优惠券 & 营销活动\n多种支付方式支持\n库存管理 & 物流支持",
                        'button_text' => '了解更多', 'button_url' => '#',
                    ),
                    array(
                        'color'       => 'blue', 'icon'       => 'fa fa-comments',
                        'title'       => '社区论坛系统', 'desc'     => '专业的社区互动平台，增强用户粘性',
                        'features'    => "多板块 & 子论坛\n话题讨论 & 回复\n用户等级 & 积分\n私信 & @ 提醒",
                        'button_text' => '了解更多', 'button_url' => '#',
                    ),
                ),
                'fields'                 => zib_widget_sales_hl_card_full_fields(),
            ),
            array(
                'title'                  => __('中卡片', 'zib_language'),
                'id'                     => 'medium_cards',
                'type'                   => 'group',
                'accordion_title_number' => true,

                'button_title'           => __('添加中卡片', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array(
                        'color'       => 'green', 'icon'  => 'fa fa-th-large',
                        'title'       => '模块化布局', 'desc'  => '自由灵活的页面构建',
                        'features'    => "可视化拖拽布局\n多种模块组合\n自定义样式设计",
                        'button_text' => '', 'button_url' => '',
                    ),
                    array(
                        'color'       => 'orange', 'icon' => 'fa fa-user-circle',
                        'title'       => '会员系统', 'desc'   => '强大的会员管理体系',
                        'features'    => "多级会员 & 权限\n成长值 & 积分系统\n会员专属内容",
                        'button_text' => '', 'button_url' => '',
                    ),
                    array(
                        'color'       => 'purple', 'icon' => 'fa fa-credit-card',
                        'title'       => '支付集成', 'desc'   => '多种支付方式支持',
                        'features'    => "微信 & 支付宝\nZibPay 支付方案\n自动发货 & 退款",
                        'button_text' => '', 'button_url' => '',
                    ),
                ),
                'fields'                 => zib_widget_sales_hl_card_full_fields(),
            ),
            array(
                'title'                  => __('小卡片', 'zib_language'),
                'id'                     => 'small_cards',
                'type'                   => 'group',
                'accordion_title_number' => true,

                'button_title'           => __('添加小卡片', 'zib_language'),
                'sanitize'               => false,
                'default'                => array(
                    array('color' => 'blue', 'icon' => 'fa fa-download', 'title' => '资源下载', 'desc' => '专业的资源管理系统'),
                    array('color' => 'purple', 'icon' => 'fa fa-shield', 'title' => '内容保护', 'desc' => '全方位内容保护机制'),
                    array('color' => 'pink', 'icon' => 'fa fa-gift', 'title' => '付费内容', 'desc' => '多样化内容变现方式'),
                    array('color' => 'blue', 'icon' => 'fa fa-android', 'title' => 'AI 集成', 'desc' => '智能化内容创作助手'),
                    array('color' => 'red', 'icon' => 'fa fa-paint-brush', 'title' => '个性定制', 'desc' => '高度可定制化设置'),
                ),
                'fields'                 => zib_widget_sales_hl_card_small_fields(),
            ),

            /* ============ 通用 ============ */
            array(
                'title'   => __('移动端处理产品图', 'zib_language'),
                'id'      => 'mobile_image',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'shrink',
                'desc'    => __('大/中卡片移动端时如何展示右侧产品图', 'zib_language'),
                'options' => array(
                    'shrink' => __('缩小展示在卡片底部', 'zib_language'),
                    'hide'   => __('隐藏（节省空间）', 'zib_language'),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_feature_intro', array(
        'title'            => __('产品图文亮点', 'zib_language'),
        'zib_title'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('左右图文布局模块，可多组媒体由 Tab 切换，常用于产品介绍页面，展示产品亮点特色', 'zib_language'),
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
            array(
                'title'   => __('媒体区域位置（PC）', 'zib_language'),
                'id'      => 'media_position',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'right',
                'options' => array(
                    'left'  => __('媒体在左，文字在右', 'zib_language'),
                    'right' => __('媒体在右，文字在左', 'zib_language'),
                ),
                'desc'    => __('放置在小容器中时无效！', 'zib_language'),
            ),
            array(
                'title'   => __('媒体长宽比例', 'zib_language'),
                'id'      => 'media_ratio',
                'type'    => 'spinner',
                'min'     => 70,
                'max'     => 200,
                'step'    => 5,
                'unit'    => '%',
                'default' => '70',
            ),
            array(
                'label'   => __('显示框架盒子', 'zib_language'),
                'id'      => 'show_box',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'      => __('主标题', 'zib_language'),
                'id'         => 'heading',
                'type'       => 'textarea',
                'sanitize'   => false,
                'default'    => '多元化布局 灵活多变',
                'attributes' => array('rows' => 2),
                'desc'       => __('支持 HTML；前台使用蓝→青渐变样式', 'zib_language'),
            ),
            array(
                'title'      => __('描述文案', 'zib_language'),
                'id'         => 'description',
                'type'       => 'textarea',
                'sanitize'   => false,
                'default'    => '多种复杂表单布局方式，包括栅格、弹性盒子、表格等，这些功能让复杂的表单布局变得趋于简洁明了',
                'attributes' => array('rows' => 3),
            ),
            array(
                'label'   => __('显示数据高亮', 'zib_language'),
                'id'      => 'show_stat',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('show_stat', '==', true),
                'title'      => __('高亮数字', 'zib_language'),
                'id'         => 'stat_number',
                'type'       => 'spinner',
                'default'    => '3',
            ),
            array(
                'dependency' => array('show_stat', '==', true),
                'title'      => __('数字旁符号（如 +）', 'zib_language'),
                'id'         => 'stat_suffix',
                'type'       => 'text',
                'class'      => 'compact',
                'default'    => '+',
            ),
            array(
                'dependency' => array('show_stat', '==', true),
                'title'      => __('高亮说明文字', 'zib_language'),
                'id'         => 'stat_label',
                'type'       => 'text',
                'class'      => 'compact',
                'default'    => '布局',
            ),
            array(
                'label'   => __('媒体外框（仿窗口顶栏圆点）', 'zib_language'),
                'id'      => 'browser_chrome',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'label'   => __('图片点击放大', 'zib_language'),
                'id'      => 'imgbox',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'title'                  => __('媒体切换项（多个时出现底部 Tab）', 'zib_language'),
                'id'                     => 'slides',
                'type'                   => 'group',
                'accordion_title_number' => true,

                'button_title'           => __('添加一组', 'zib_language'),
                'default'                => array(
                    array(
                        'tab_label'    => '栅格布局',
                        'media_type'   => 'image',
                        'image'        => '',
                        'image_dark'   => '',
                        'video_url'    => '',
                        'video_poster' => '',
                    ),
                    array(
                        'tab_label'    => '盒子布局',
                        'media_type'   => 'image',
                        'image'        => '',
                        'image_dark'   => '',
                        'video_url'    => '',
                        'video_poster' => '',
                    ),
                    array(
                        'tab_label'    => '表格布局',
                        'media_type'   => 'image',
                        'image'        => '',
                        'image_dark'   => '',
                        'video_url'    => '',
                        'video_poster' => '',
                    ),
                ),
                'fields'                 => array(
                    array(
                        'id'      => 'tab_label',
                        'type'    => 'text',
                        'title'   => __('Tab 名称', 'zib_language'),
                        'default' => '',
                    ),
                    array(
                        'id'      => 'media_type',
                        'type'    => 'radio',
                        'title'   => __('类型', 'zib_language'),
                        'inline'  => true,
                        'default' => 'image',
                        'options' => array(
                            'image' => __('图片', 'zib_language'),
                            'video' => __('视频', 'zib_language'),
                        ),
                    ),
                    array(
                        'dependency' => array('media_type', '==', 'image'),
                        'id'         => 'image',
                        'type'       => 'upload',
                        'title'      => __('图片（亮色模式）', 'zib_language'),
                        'library'    => 'image',
                        'preview'    => true,
                    ),
                    array(
                        'dependency' => array('media_type', '==', 'image'),
                        'id'         => 'image_dark',
                        'type'       => 'upload',
                        'title'      => __('图片（暗色模式，可选）', 'zib_language'),
                        'class'      => 'compact',
                        'library'    => 'image',
                        'preview'    => true,
                    ),
                    array(
                        'dependency' => array('media_type', '==', 'video'),
                        'id'         => 'video_url',
                        'type'       => 'upload',
                        'library'    => 'video',
                        'title'      => __('视频地址', 'zib_language'),
                        'preview'    => false,
                        'default'    => '',
                        'desc'       => __('上传或选择视频；也可在部分环境下粘贴直链', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('media_type', '==', 'video'),
                        'id'         => 'video_poster',
                        'type'       => 'upload',
                        'title'      => __('视频封面图', 'zib_language'),
                        'class'      => 'compact',
                        'library'    => 'image',
                        'preview'    => true,
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_tilt_cases', array(
        'title'            => __('产品3D交互展示', 'zib_language'),
        'zib_title'        => false,
        'zib_affix'        => false,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('左侧产品介绍，右侧带3D交互图片展示，常用于产品介绍页面的顶部展示', 'zib_language'),
        'reminder'         => __('此模块适合放在页面的顶部全宽度容器中，不能放置在较小的容器中', 'zib_language'),
        'size'             => 'big',
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
            array(
                'label'   => __('显示框架盒子', 'zib_language'),
                'id'      => 'show_box',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'type'    => 'subheading',
                'content' => '<i class="fa fa-magic mr6"></i>' . __('左侧产品信息', 'zib_language'),
            ),
            array(
                'title'    => __('顶部徽章', 'zib_language'),
                'subtitle' => '',
                'id'       => 'intro_badge_text',
                'type'     => 'text',
                'default'  => '',
                'desc'     => __('例如：专为社区而生的 WordPress 主题；留空则不显示徽章', 'zib_language'),
            ),
            array(
                'title'    => ' ',
                'subtitle' => __('徽章图标', 'zib_language'),
                'class'    => 'compact',
                'id'       => 'intro_badge_icon',
                'type'     => 'icon',
                'default'  => 'fa fa-users',
            ),
            array(
                'title'    => ' ',
                'subtitle' => __('徽章颜色', 'zib_language'),
                'class'    => 'compact skin-color',
                'id'       => 'intro_badge_color',
                'type'     => 'palette',
                'default'  => 'c-blue',
                'options'  => CFS_Module::zib_palette(),
            ),

            array(
                'title'      => __('主标题', 'zib_language'),
                'subtitle'   => __('主标题（支持换行）', 'zib_language'),
                'id'         => 'intro_title',
                'type'       => 'textarea',
                'sanitize'   => false,
                'default'    => "zibll主题\n更优雅的全能型主题",
                'attributes' => array(
                    'rows' => 2,
                ),
            ),
            array(
                'dependency' => array('intro_title', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('主标题选字高亮', 'zib_language'),
                'class'      => 'compact',
                'id'         => 'intro_title_highlight',
                'type'       => 'text',
                'default'    => '全能型主题',
                'desc'       => __('主标题中要高亮显示的词', 'zib_language'),
            ),
            array(
                'dependency' => array('intro_title|intro_title_highlight', '!=|!=', ''),
                'id'         => 'intro_title_highlight_color',
                'type'       => 'palette',
                'title'      => ' ',
                'subtitle'   => __('高亮颜色', 'zib_language'),
                'class'      => 'compact skin-color',
                'options'    => CFS_Module::zib_palette(array('' => array('#4f5359')), array('text', 'cg')),
                'default'    => 'cg-red',
            ),
            array(
                'title'      => __('副标题', 'zib_language'),
                'id'         => 'intro_subtitle',
                'type'       => 'textarea',
                'sanitize'   => false,
                'default'    => '集成会员系统、付费功能、商城、论坛等强大功能于一体助力您快速搭建内容、社区与商业化兼具的专业网站',
                'attributes' => array(
                    'rows' => 2,
                ),
            ),

            array(
                'title'        => __('特性标签', 'zib_language'),
                'id'           => 'intro_features',
                'type'         => 'group',
                'button_title' => __('添加特性标签', 'zib_language'),
                'default'      => array(
                    array('icon' => 'fa fa-refresh', 'text' => '持续迭代更新'),
                    array('icon' => 'fa fa-wrench', 'text' => '专业技术支持'),
                    array('icon' => 'fa fa-magic', 'text' => '高度自由化'),
                ),
                'fields'       => array(
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => 'fa fa-check',
                    ),
                    array(
                        'id'      => 'text',
                        'type'    => 'text',
                        'title'   => __('文字', 'zib_language'),
                        'default' => '',
                    ),
                ),
            ),

            array(
                'title'        => __('跳转按钮', 'zib_language'),
                'id'           => 'intro_buttons',
                'type'         => 'group',
                'max'          => 3,
                'button_title' => __('添加跳转按钮', 'zib_language'),
                'default'      => array(
                    array('url' => ['url' => '#', 'text' => '立即购买', 'target' => '_blank'], 'icon' => 'shopping-cart', 'color' => 'jb-red'),
                ),
                'fields'       => array(
                    zib_get_widget_pay_btn_fields(),
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => '',
                    ),
                    array(
                        'id'      => 'color',
                        'type'    => 'palette',
                        'title'   => __('颜色', 'zib_language'),
                        'class'   => 'skin-color',
                        'options' => CFS_Module::zib_palette(),
                        'default' => 'jb-red',
                    ),
                ),
            ),

            array(
                'title'        => __('信任标识', 'zib_language'),
                'id'           => 'intro_trust_badges',
                'type'         => 'group',
                'button_title' => __('添加信任标识', 'zib_language'),
                'default'      => array(
                    array('icon' => 'fa fa-check-circle', 'text' => '一次购买，终身使用'),
                    array('icon' => 'fa fa-shield', 'text' => '30天无理由退款保障'),
                ),
                'fields'       => array(
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => 'fa fa-check-circle',
                    ),
                    array(
                        'id'      => 'text',
                        'type'    => 'text',
                        'title'   => __('文字', 'zib_language'),
                        'default' => '',
                    ),
                ),
            ),

            array(
                'title'        => __('右侧展示图', 'zib_language'),
                'id'           => 'intro_showcase_images',
                'type'         => 'group',
                'button_title' => __('添加展示图', 'zib_language'),
                'default'      => array(),
                'fields'       => array(
                    array(
                        'id'      => 'image',
                        'type'    => 'upload',
                        'title'   => __('图片（白天模式）', 'zib_language'),
                        'library' => 'image',
                        'preview' => true,
                    ),
                    array(
                        'id'      => 'image_dark',
                        'type'    => 'upload',
                        'title'   => __('图片（夜间模式 · 可选）', 'zib_language'),
                        'library' => 'image',
                        'class'   => 'compact',
                        'preview' => true,
                        'desc'    => __('留空则夜间模式沿用白天图', 'zib_language'),
                    ),
                    array(
                        'id'        => 'offset_pc',
                        'type'      => 'spacing',
                        'title'     => __('偏移（PC）', 'zib_language'),
                        'left_icon' => '<i class="fa fa-arrows-h"></i>',
                        'top_icon'  => '<i class="fa fa-arrows-v"></i>',
                        'left'      => true,
                        'right'     => false,
                        'bottom'    => false,
                        'top'       => true,
                        'default'   => array('left' => 0, 'top' => 0, 'unit' => '%'),
                        'units'     => array('%', 'px'),
                    ),
                    array(
                        'id'        => 'offset_m',
                        'type'      => 'spacing',
                        'left_icon' => '<i class="fa fa-arrows-h"></i>',
                        'top_icon'  => '<i class="fa fa-arrows-v"></i>',
                        'class'     => 'compact',
                        'title'     => __('偏移（移动端）', 'zib_language'),
                        'left'      => true,
                        'right'     => false,
                        'top'       => true,
                        'bottom'    => false,
                        'default'   => array('left' => 0, 'top' => 0, 'unit' => '%'),
                        'units'     => array('%', 'px'),
                        'desc'      => __('支持负数，如 -10%', 'zib_language'),
                    ),
                    array(
                        'id'      => 'scale',
                        'type'    => 'spinner',
                        'title'   => __('缩放', 'zib_language'),
                        'default' => 100,
                        'min'     => 1,
                        'max'     => 200,
                        'step'    => 5,
                        'unit'    => '%',
                        'desc'    => __('支持负数，如 -10%，原图大小为 100%', 'zib_language'),
                    ),
                    array(
                        'id'      => 'z_index',
                        'type'    => 'spinner',
                        'title'   => __('层级(z-index)', 'zib_language'),
                        'default' => 1,
                        'min'     => 1,
                        'max'     => 100,
                        'step'    => 1,
                        'unit'    => '',
                    ),
                    array(
                        'id'      => 'depth',
                        'type'    => 'text',
                        'title'   => __('3D 视差深度', 'zib_language'),
                        'default' => '20',
                        'desc'    => __('数值越大鼠标移动时此图层位移越明显（推荐 0-60）', 'zib_language'),
                    ),
                ),
            ),

        ),
    ));
}
