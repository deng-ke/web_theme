<?php
/*
 * Project       : Nova主题
 * Description   : 商城模块（商品/购物车/订单）
 */

if (!defined('ABSPATH')) exit;

/**
 * 注册商城文章类型和分类
 */
function nova_shop_init()
{
    register_taxonomy('shop_cat', 'shop_product', array(
        'labels' => array(
            'name'          => __('商品分类', 'nova'),
            'singular_name' => __('商品分类', 'nova'),
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'shop-cat'),
    ));

    register_post_type('shop_product', array(
        'labels' => array(
            'name'          => __('商品', 'nova'),
            'singular_name' => __('商品', 'nova'),
            'add_new'       => __('添加商品', 'nova'),
        ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-cart',
        'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite'       => array('slug' => 'product'),
        'has_archive'   => false,
    ));
}
add_action('init', 'nova_shop_init');

/**
 * 获取商品价格
 */
function nova_shop_get_price($product_id)
{
    return (float) get_post_meta($product_id, 'nova_shop_price', true);
}

/**
 * 获取商品库存
 */
function nova_shop_get_stock($product_id)
{
    $stock = (int) get_post_meta($product_id, 'nova_shop_stock', true);
    return max(0, $stock);
}

/**
 * 获取商品销量
 */
function nova_shop_get_sales($product_id)
{
    return (int) get_post_meta($product_id, 'nova_shop_sales', true);
}

/**
 * 商品卡片
 */
function nova_shop_product_card($post)
{
    $price = nova_shop_get_price($post->ID);
    $sales = nova_shop_get_sales($post->ID);
    $stock = nova_shop_get_stock($post->ID);
    $thumb = nova_post_thumbnail_url($post, 'nova-card');

    $html = '<div class="nova-product-card card">';
    if ($thumb) {
        $html .= '<a class="nova-product-thumb" href="' . esc_url(get_permalink($post)) . '">';
        $html .= '<img src="' . esc_url($thumb) . '" alt="' . esc_attr($post->post_title) . '">';
        if ($stock <= 0) {
            $html .= '<span class="nova-product-soldout">' . esc_html__('已售罄', 'nova') . '</span>';
        }
        $html .= '</a>';
    }
    $html .= '<div class="nova-product-body">';
    $html .= '<h3 class="nova-product-title"><a href="' . esc_url(get_permalink($post)) . '">' . esc_html($post->post_title) . '</a></h3>';
    $html .= '<div class="nova-product-footer">';
    $html .= '<div class="nova-product-price">¥ <b>' . number_format($price, 2) . '</b></div>';
    $html .= '<div class="nova-product-sales muted-2-color">' . sprintf(__('已售 %s', 'nova'), $sales) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 商城首页
 */
function nova_shop_index_html()
{
    $html = '';
    //商品分类导航
    $cats = get_terms(array('taxonomy' => 'shop_cat', 'hide_empty' => true, 'number' => 12));
    if (!empty($cats) && !is_wp_error($cats)) {
        $html .= '<div class="nova-shop-cats card">';
        $html .= '<a class="active" href="' . esc_url(nova_shop_url()) . '">' . esc_html__('全部', 'nova') . '</a>';
        foreach ($cats as $cat) {
            $html .= '<a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a>';
        }
        $html .= '</div>';
    }

    //商品列表
    $query = new WP_Query(array(
        'post_type'      => 'shop_product',
        'posts_per_page' => 12,
        'paged'          => nova_get_the_paged(),
        'orderby'        => 'date',
    ));
    if (!$query->have_posts()) {
        $html .= nova_empty_box(__('暂无商品', 'nova'), __('商城还没有上架商品', 'nova'));
    } else {
        $html .= '<div class="nova-product-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            $html .= nova_shop_product_card(get_post());
        }
        $html .= '</div>';
        nova_paging($query);
    }
    wp_reset_postdata();
    return $html;
}

/**
 * 商品详情页内容
 */
function nova_shop_product_single($post)
{
    $price = nova_shop_get_price($post->ID);
    $sales = nova_shop_get_sales($post->ID);
    $stock = nova_shop_get_stock($post->ID);
    $thumb = nova_post_thumbnail_url($post, 'full');

    $html = '<div class="nova-product-single card">';
    $html .= '<div class="nova-product-gallery">';
    if ($thumb) {
        $html .= '<img src="' . esc_url($thumb) . '" alt="' . esc_attr($post->post_title) . '">';
    }
    $html .= '</div>';
    $html .= '<div class="nova-product-info">';
    $html .= '<h1 class="nova-product-title">' . esc_html($post->post_title) . '</h1>';
    $html .= '<div class="nova-product-price-box">';
    $html .= '<div class="nova-product-price">¥ <b>' . number_format($price, 2) . '</b></div>';
    $html .= '<div class="nova-product-stats muted-2-color">';
    $html .= '<span>' . sprintf(__('销量 %s', 'nova'), $sales) . '</span>';
    $html .= '<span>' . sprintf(__('库存 %s', 'nova'), $stock) . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="nova-product-buy">';
    $html .= '<div class="nova-product-qty">';
    $html .= '<button type="button" class="nova-qty-minus">-</button>';
    $html .= '<input type="number" value="1" min="1" max="' . ($stock ?: 1) . '" class="nova-qty-input">';
    $html .= '<button type="button" class="nova-qty-plus">+</button>';
    $html .= '</div>';
    if ($stock > 0) {
        $html .= '<button class="btn btn-outline nova-add-cart-btn" data-id="' . $post->ID . '">' . esc_html__('加入购物车', 'nova') . '</button>';
        $html .= '<button class="btn btn-primary nova-buy-now-btn" data-id="' . $post->ID . '">' . esc_html__('立即购买', 'nova') . '</button>';
    } else {
        $html .= '<button class="btn btn-disabled" disabled>' . esc_html__('已售罄', 'nova') . '</button>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    //商品详情
    $html .= '<div class="nova-product-detail card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('商品详情', 'nova') . '</span></h3>';
    $html .= '<div class="nova-product-content">' . apply_filters('the_content', $post->post_content) . '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 获取购物车
 */
function nova_shop_get_cart($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    $cart = get_user_meta($user_id, 'nova_shop_cart', true);
    return is_array($cart) ? $cart : array();
}

/**
 * 保存购物车
 */
function nova_shop_save_cart($cart, $user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    update_user_meta($user_id, 'nova_shop_cart', $cart);
}

/**
 * AJAX 加入购物车
 */
function nova_ajax_add_cart()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $product_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
    $product = get_post($product_id);
    if (!$product || 'shop_product' !== $product->post_type) nova_ajax_error('商品不存在');

    $stock = nova_shop_get_stock($product_id);
    if ($stock <= 0) nova_ajax_error('商品已售罄');

    $cart = nova_shop_get_cart($user_id);
    $current_qty = isset($cart[$product_id]) ? (int) $cart[$product_id] : 0;
    $new_qty = min($stock, $current_qty + max(1, $qty));
    $cart[$product_id] = $new_qty;
    nova_shop_save_cart($cart, $user_id);
    nova_ajax_success(array('cart_count' => array_sum($cart)), '已加入购物车');
}
add_action('wp_ajax_nova_add_cart', 'nova_ajax_add_cart');

/**
 * AJAX 更新购物车
 */
function nova_ajax_update_cart()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $product_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 0;
    $cart = nova_shop_get_cart($user_id);

    if ($qty <= 0) {
        unset($cart[$product_id]);
    } else {
        $cart[$product_id] = $qty;
    }
    nova_shop_save_cart($cart, $user_id);
    nova_ajax_success(array('cart_count' => array_sum($cart)));
}
add_action('wp_ajax_nova_update_cart', 'nova_ajax_update_cart');

/**
 * 购物车页面
 */
function nova_shop_cart_html()
{
    $user_id = get_current_user_id();
    $cart = nova_shop_get_cart($user_id);

    if (empty($cart)) {
        return nova_empty_box(__('购物车是空的', 'nova'), __('快去挑选心仪的商品吧', 'nova'));
    }

    $html = '<div class="nova-cart-page">';
    $html .= '<div class="nova-cart-list">';
    $total = 0;
    foreach ($cart as $product_id => $qty) {
        $product = get_post($product_id);
        if (!$product) continue;
        $price = nova_shop_get_price($product_id);
        $stock = nova_shop_get_stock($product_id);
        $thumb = nova_post_thumbnail_url($product, 'nova-thumb');
        $total += $price * $qty;

        $html .= '<div class="nova-cart-item card" data-id="' . $product_id . '">';
        if ($thumb) {
            $html .= '<a class="nova-cart-thumb" href="' . esc_url(get_permalink($product)) . '"><img src="' . esc_url($thumb) . '" alt="product"></a>';
        }
        $html .= '<div class="nova-cart-info">';
        $html .= '<a class="nova-cart-title" href="' . esc_url(get_permalink($product)) . '">' . esc_html($product->post_title) . '</a>';
        $html .= '<div class="nova-cart-price">¥ ' . number_format($price, 2) . '</div>';
        $html .= '</div>';
        $html .= '<div class="nova-cart-qty">';
        $html .= '<div class="nova-product-qty">';
        $html .= '<button type="button" class="nova-qty-minus">-</button>';
        $html .= '<input type="number" value="' . $qty . '" min="1" max="' . $stock . '" class="nova-qty-input">';
        $html .= '<button type="button" class="nova-qty-plus">+</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<button class="nova-cart-del" data-id="' . $product_id . '"><i class="nova-icon-close"></i></button>';
        $html .= '</div>';
    }
    $html .= '</div>';

    $html .= '<div class="nova-cart-summary card">';
    $html .= '<div class="nova-cart-total">' . esc_html__('合计：', 'nova') . ' <b>¥ ' . number_format($total, 2) . '</b></div>';
    $html .= '<a class="btn btn-primary btn-block" href="' . esc_url(nova_checkout_url()) . '">' . esc_html__('去结算', 'nova') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * 结算页地址
 */
function nova_checkout_url()
{
    return home_url('/checkout');
}

/**
 * 结算页面
 */
function nova_checkout_html()
{
    $user_id = get_current_user_id();
    $cart = nova_shop_get_cart($user_id);
    if (empty($cart)) {
        wp_safe_redirect(nova_shop_url());
        exit;
    }

    $total = 0;
    $items = array();
    foreach ($cart as $product_id => $qty) {
        $product = get_post($product_id);
        if (!$product) continue;
        $price = nova_shop_get_price($product_id);
        $stock = nova_shop_get_stock($product_id);
        $qty = min($qty, $stock);
        if ($qty <= 0) continue;
        $items[$product_id] = $qty;
        $total += $price * $qty;
    }
    if (empty($items)) {
        wp_safe_redirect(nova_shop_url());
        exit;
    }

    $html = '<div class="nova-checkout-page">';
    $html .= '<div class="nova-checkout-form card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('收货信息', 'nova') . '</span></h3>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('收货人', 'nova') . '</label><input type="text" class="nova-checkout-name" placeholder="' . esc_attr__('姓名', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('手机号', 'nova') . '</label><input type="text" class="nova-checkout-phone" placeholder="' . esc_attr__('手机号', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('收货地址', 'nova') . '</label><input type="text" class="nova-checkout-address" placeholder="' . esc_attr__('详细地址', 'nova') . '"></div>';
    $html .= '<div class="nova-form-group"><label>' . esc_html__('备注', 'nova') . '</label><input type="text" class="nova-checkout-remark" placeholder="' . esc_attr__('选填', 'nova') . '"></div>';
    $html .= '</div>';

    $html .= '<div class="nova-checkout-summary card">';
    $html .= '<h3 class="nova-block-title"><span>' . esc_html__('订单确认', 'nova') . '</span></h3>';
    foreach ($items as $product_id => $qty) {
        $product = get_post($product_id);
        $price = nova_shop_get_price($product_id);
        $html .= '<div class="nova-checkout-item">';
        $html .= '<span>' . esc_html($product->post_title) . ' × ' . $qty . '</span>';
        $html .= '<b>¥ ' . number_format($price * $qty, 2) . '</b>';
        $html .= '</div>';
    }
    $html .= '<div class="nova-checkout-total">' . esc_html__('应付金额', 'nova') . '：<b>¥ ' . number_format($total, 2) . '</b></div>';
    $html .= '<button class="btn btn-primary btn-block nova-checkout-submit">' . esc_html__('提交订单', 'nova') . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * AJAX 创建商城订单
 */
function nova_ajax_create_shop_order()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone   = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $address = isset($_POST['address']) ? sanitize_text_field(wp_unslash($_POST['address'])) : '';
    $remark  = isset($_POST['remark']) ? sanitize_text_field(wp_unslash($_POST['remark'])) : '';
    if (!$name || !$phone || !$address) nova_ajax_error('请填写完整的收货信息');

    $cart = nova_shop_get_cart($user_id);
    if (empty($cart)) nova_ajax_error('购物车是空的');

    $items = array();
    $total = 0;
    foreach ($cart as $product_id => $qty) {
        $product = get_post($product_id);
        if (!$product) continue;
        $price = nova_shop_get_price($product_id);
        $stock = nova_shop_get_stock($product_id);
        $qty = min($qty, $stock);
        if ($qty <= 0) continue;
        $items[] = array('id' => $product_id, 'title' => $product->post_title, 'price' => $price, 'qty' => $qty);
        $total += $price * $qty;
    }
    if (empty($items)) nova_ajax_error('购物车是空的');

    global $wpdb;
    $table = $wpdb->prefix . 'nova_order';
    $order_num = date('YmdHis') . mt_rand(100000, 999999);
    $wpdb->insert($table, array(
        'order_num'    => $order_num,
        'user_id'      => $user_id,
        'post_id'      => 0,
        'order_type'   => 'shop',
        'pay_type'     => 'alipay',
        'order_price'  => $total,
        'pay_price'    => $total,
        'order_status' => 0,
        'create_time'  => current_time('mysql'),
        'ip_address'   => nova_get_ip(),
    ));
    $order_id = $wpdb->insert_id;

    //保存商品明细
    nova_order_update_meta($order_id, 'nova_order_items', $items);
    nova_order_update_meta($order_id, 'nova_order_address', array('name' => $name, 'phone' => $phone, 'address' => $address, 'remark' => $remark));

    //清空购物车
    nova_shop_save_cart(array(), $user_id);

    nova_ajax_success(array('order_id' => $order_id, 'order_num' => $order_num), '订单创建成功');
}
add_action('wp_ajax_nova_create_shop_order', 'nova_ajax_create_shop_order');
