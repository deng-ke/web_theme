<?php
/*
 * Project       : Nova主题
 * Description   : 用户中心（个人中心/资料/文章/评论/点赞/消息/签到/积分/余额/订单等）
 */

if (!defined('ABSPATH')) exit;

nova_user_page_permission();

$tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : ''; // phpcs:ignore
if (!$tab && get_query_var('nova_tab')) {
    $tab = sanitize_text_field(get_query_var('nova_tab'));
}
$msg_type = isset($_GET['msg_type']) ? sanitize_text_field(wp_unslash($_GET['msg_type'])) : '';

//Tab 内容分发
switch ($tab) {
    case 'profile':
        $content = nova_user_profile_html();
        break;
    case 'posts':
        $content = nova_user_posts_html();
        break;
    case 'comments':
        $content = nova_user_comments_html();
        break;
    case 'likes':
        $content = nova_user_likes_html();
        break;
    case 'msg':
        $content = nova_msg_center_html($msg_type);
        break;
    case 'sign':
        $content = nova_sign_html();
        break;
    case 'points':
        $content = nova_user_points_html();
        break;
    case 'balance':
        $content = nova_user_balance_html();
        break;
    case 'withdraw':
        $content = nova_withdraw_html();
        break;
    case 'vip':
        $content = nova_vip_html();
        break;
    case 'card':
        $content = nova_card_html();
        break;
    case 'orders':
        $content = nova_user_orders_html();
        break;
    case 'bind':
    case 'oauth':
        $content = nova_oauth_bind_html();
        break;
    case 'shop-order':
        $content = nova_user_shop_orders_html();
        break;
    default:
        $content = nova_user_home_html();
}

get_header();
?>
<div class="nova-container nova-uc-page">
    <div class="nova-uc-layout">
        <div class="nova-uc-side">
            <?php echo nova_user_center_menu(); // phpcs:ignore ?>
        </div>
        <div class="nova-uc-main">
            <?php echo $content; // phpcs:ignore ?>
        </div>
    </div>
</div>
<?php
get_footer();

/**
 * 商城订单列表
 */
function nova_user_shop_orders_html()
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_order';
    $user_id = get_current_user_id();
    $orders = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d AND order_type = 'shop' ORDER BY id DESC LIMIT 20", $user_id));
    $html = '<div class="nova-uc-shop-orders">';
    if (empty($orders)) {
        $html .= nova_empty_box(__('暂无商城订单', 'nova'), __('你还没有商城订单', 'nova'));
    } else {
        $html .= '<div class="nova-order-list">';
        foreach ($orders as $order) {
            $address = nova_order_get_meta($order->id, 'nova_order_address', array());
            $html .= '<div class="nova-order-item card">';
            $html .= '<div class="nova-order-head">';
            $html .= '<span class="nova-order-num muted-2-color">' . esc_html($order->order_num) . '</span>';
            $html .= '<span class="nova-order-status st-' . (int) $order->order_status . '">' . esc_html(Novapay::order_status_name($order->order_status)) . '</span>';
            $html .= '</div>';
            $html .= '<div class="nova-order-body">';
            $html .= '<b>¥ ' . number_format((float) $order->pay_price, 2) . '</b>';
            $html .= '</div>';
            if (!empty($address['address'])) {
                $html .= '<div class="nova-order-foot muted-2-color">' . esc_html($address['name'] . ' · ' . $address['phone'] . ' · ' . $address['address']) . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}
