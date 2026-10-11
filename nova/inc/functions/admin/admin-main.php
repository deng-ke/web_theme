<?php
/*
 * Project       : Nova主题
 * Description   : 后台管理（文章付费设置/商品设置/订单/充值卡/提现管理）
 */

if (!defined('ABSPATH')) exit;

/* ============================================================
 * 文章付费设置 Meta Box
 * ============================================================ */
function nova_admin_add_meta_boxes()
{
    add_meta_box('nova_pay_setting', __('付费设置', 'nova'), 'nova_admin_pay_meta_box', 'post', 'side', 'high');
    add_meta_box('nova_shop_setting', __('商品设置', 'nova'), 'nova_admin_shop_meta_box', 'shop_product', 'side', 'high');
}
add_action('add_meta_boxes', 'nova_admin_add_meta_boxes');

function nova_admin_pay_meta_box($post)
{
    wp_nonce_field('nova_pay_setting', 'nova_pay_setting_nonce');
    $pay_type = get_post_meta($post->ID, 'nova_pay_type', true);
    $pay_price = (float) get_post_meta($post->ID, 'nova_pay_price', true);
    $pay_points = (int) get_post_meta($post->ID, 'nova_pay_points', true);
    ?>
    <p>
        <label><?php esc_html_e('付费类型', 'nova'); ?></label>
        <select name="nova_pay_type" style="width:100%">
            <option value=""><?php esc_html_e('免费（不开启）', 'nova'); ?></option>
            <option value="money" <?php selected($pay_type, 'money'); ?>><?php esc_html_e('付费可见（现金）', 'nova'); ?></option>
            <option value="points" <?php selected($pay_type, 'points'); ?>><?php esc_html_e('付费可见（积分）', 'nova'); ?></option>
            <option value="both" <?php selected($pay_type, 'both'); ?>><?php esc_html_e('两者皆可', 'nova'); ?></option>
        </select>
    </p>
    <p>
        <label><?php esc_html_e('付费价格（元）', 'nova'); ?></label>
        <input type="number" name="nova_pay_price" value="<?php echo esc_attr($pay_price); ?>" min="0" step="0.01" style="width:100%">
    </p>
    <p>
        <label><?php esc_html_e('付费积分', 'nova'); ?></label>
        <input type="number" name="nova_pay_points" value="<?php echo esc_attr($pay_points); ?>" min="0" style="width:100%">
    </p>
    <p class="description"><?php esc_html_e('正文内容将被隐藏，付费后可见。', 'nova'); ?></p>
    <?php
}

function nova_admin_shop_meta_box($post)
{
    wp_nonce_field('nova_shop_setting', 'nova_shop_setting_nonce');
    $price = (float) get_post_meta($post->ID, 'nova_shop_price', true);
    $stock = (int) get_post_meta($post->ID, 'nova_shop_stock', true);
    ?>
    <p>
        <label><?php esc_html_e('商品价格（元）', 'nova'); ?></label>
        <input type="number" name="nova_shop_price" value="<?php echo esc_attr($price); ?>" min="0" step="0.01" style="width:100%">
    </p>
    <p>
        <label><?php esc_html_e('库存数量', 'nova'); ?></label>
        <input type="number" name="nova_shop_stock" value="<?php echo esc_attr($stock); ?>" min="0" style="width:100%">
    </p>
    <?php
}

function nova_admin_save_meta($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    //付费设置
    if (isset($_POST['nova_pay_setting_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nova_pay_setting_nonce'])), 'nova_pay_setting')) {
        update_post_meta($post_id, 'nova_pay_type', sanitize_text_field(wp_unslash($_POST['nova_pay_type'])));
        update_post_meta($post_id, 'nova_pay_price', (float) $_POST['nova_pay_price']);
        update_post_meta($post_id, 'nova_pay_points', (int) $_POST['nova_pay_points']);
    }
    //商品设置
    if (isset($_POST['nova_shop_setting_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nova_shop_setting_nonce'])), 'nova_shop_setting')) {
        update_post_meta($post_id, 'nova_shop_price', (float) $_POST['nova_shop_price']);
        update_post_meta($post_id, 'nova_shop_stock', max(0, (int) $_POST['nova_shop_stock']));
    }
}
add_action('save_post', 'nova_admin_save_meta');

/* ============================================================
 * 论坛板块分类字段
 * ============================================================ */
function nova_admin_add_plate_fields($term)
{
    $icon = get_term_meta($term->term_id, 'nova_plate_icon', true);
    $color = get_term_meta($term->term_id, 'nova_plate_color', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label><?php esc_html_e('板块图标HTML', 'nova'); ?></label></th>
        <td><input type="text" name="nova_plate_icon" value="<?php echo esc_attr($icon); ?>" placeholder="<i class='nova-icon-forum'></i>"></td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label><?php esc_html_e('图标背景色', 'nova'); ?></label></th>
        <td><input type="text" name="nova_plate_color" value="<?php echo esc_attr($color); ?>" placeholder="#0e76f8"></td>
    </tr>
    <?php
}
add_action('forum_plate_add_form_fields', 'nova_admin_add_plate_fields');
add_action('forum_plate_edit_form_fields', 'nova_admin_add_plate_fields');

function nova_admin_save_plate_fields($term_id)
{
    if (isset($_POST['nova_plate_icon'])) {
        update_term_meta($term_id, 'nova_plate_icon', wp_kses_post(wp_unslash($_POST['nova_plate_icon'])));
    }
    if (isset($_POST['nova_plate_color'])) {
        update_term_meta($term_id, 'nova_plate_color', sanitize_text_field(wp_unslash($_POST['nova_plate_color'])));
    }
}
add_action('created_forum_plate', 'nova_admin_save_plate_fields');
add_action('edited_forum_plate', 'nova_admin_save_plate_fields');

/* ============================================================
 * 订单管理页面
 * ============================================================ */
function nova_admin_menu()
{
    add_menu_page(
        __('Nova设置', 'nova'),
        __('Nova', 'nova'),
        'manage_options',
        'nova-dashboard',
        'nova_admin_dashboard_html',
        'dashicons-admin-settings',
        59
    );
    add_submenu_page('nova-dashboard', __('订单管理', 'nova'), __('订单管理', 'nova'), 'manage_options', 'nova-orders', 'nova_admin_orders_html');
    add_submenu_page('nova-dashboard', __('充值卡管理', 'nova'), __('充值卡管理', 'nova'), 'manage_options', 'nova-cards', 'nova_admin_cards_html');
    add_submenu_page('nova-dashboard', __('提现管理', 'nova'), __('提现管理', 'nova'), 'manage_options', 'nova-withdraws', 'nova_admin_withdraws_html');
}
add_action('admin_menu', 'nova_admin_menu');

function nova_admin_dashboard_html()
{
    global $wpdb;
    $count_users = count_users();
    $order_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nova_order");
    $order_amount = (float) $wpdb->get_var("SELECT SUM(pay_price) FROM {$wpdb->prefix}nova_order WHERE order_status = 1");
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Nova 主题概览', 'nova'); ?></h1>
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:16px;">
            <div style="background:#fff;padding:20px;border-radius:8px;min-width:200px;border:1px solid #e5e7eb;">
                <h2 style="margin:0;color:#0e76f8;"><?php echo esc_html($count_users['total_users']); ?></h2>
                <p style="margin:8px 0 0;color:#6b7280;"><?php esc_html_e('注册会员', 'nova'); ?></p>
            </div>
            <div style="background:#fff;padding:20px;border-radius:8px;min-width:200px;border:1px solid #e5e7eb;">
                <h2 style="margin:0;color:#0e76f8;"><?php echo esc_html($order_count); ?></h2>
                <p style="margin:8px 0 0;color:#6b7280;"><?php esc_html_e('订单总数', 'nova'); ?></p>
            </div>
            <div style="background:#fff;padding:20px;border-radius:8px;min-width:200px;border:1px solid #e5e7eb;">
                <h2 style="margin:0;color:#0e76f8;">¥ <?php echo esc_html(number_format($order_amount, 2)); ?></h2>
                <p style="margin:8px 0 0;color:#6b7280;"><?php esc_html_e('成交金额', 'nova'); ?></p>
            </div>
            <div style="background:#fff;padding:20px;border-radius:8px;min-width:200px;border:1px solid #e5e7eb;">
                <h2 style="margin:0;color:#0e76f8;"><?php echo esc_html(wp_count_posts()->publish); ?></h2>
                <p style="margin:8px 0 0;color:#6b7280;"><?php esc_html_e('文章总数', 'nova'); ?></p>
            </div>
        </div>
        <p style="margin-top:24px;"><?php esc_html_e('请在「外观 → 自定义」中配置主题选项。', 'nova'); ?></p>
    </div>
    <?php
}

/**
 * 订单列表
 */
function nova_admin_orders_html()
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_order';
    $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
    $order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

    //线下转账确认
    if ('confirm' === $action && $order_id && current_user_can('manage_options')) {
        check_admin_referer('nova_confirm_order_' . $order_id);
        $order = Novapay::get_order($order_id);
        if ($order) {
            Novapay::pay_success($order, 'admin_confirm');
        }
        echo '<div class="notice notice-success"><p>' . esc_html__('订单已确认支付', 'nova') . '</p></div>';
    }

    $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
    $per_page = 20;
    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $orders = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('订单管理', 'nova'); ?></h1>
        <table class="widefat striped" style="margin-top:16px;">
            <thead>
                <tr>
                    <th><?php esc_html_e('订单号', 'nova'); ?></th>
                    <th><?php esc_html_e('用户', 'nova'); ?></th>
                    <th><?php esc_html_e('类型', 'nova'); ?></th>
                    <th><?php esc_html_e('金额', 'nova'); ?></th>
                    <th><?php esc_html_e('支付方式', 'nova'); ?></th>
                    <th><?php esc_html_e('状态', 'nova'); ?></th>
                    <th><?php esc_html_e('时间', 'nova'); ?></th>
                    <th><?php esc_html_e('操作', 'nova'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><?php echo esc_html($order->order_num); ?></td>
                        <td><?php echo esc_html(nova_get_user_name($order->user_id)); ?> (ID:<?php echo esc_html($order->user_id); ?>)</td>
                        <td><?php echo esc_html(Novapay::order_type_name($order->order_type)); ?></td>
                        <td>¥ <?php echo esc_html(number_format((float) $order->pay_price, 2)); ?></td>
                        <td><?php echo esc_html(Novapay::pay_type_name($order->pay_type)); ?></td>
                        <td><?php echo esc_html(Novapay::order_status_name($order->order_status)); ?></td>
                        <td><?php echo esc_html($order->create_time); ?></td>
                        <td>
                            <?php if (2 == $order->order_status) : ?>
                                <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=nova-orders&action=confirm&order_id=' . $order->id), 'nova_confirm_order_' . $order->id)); ?>"><?php esc_html_e('确认收款', 'nova'); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($total > $per_page) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php echo paginate_links(array('base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $paged, 'total' => ceil($total / $per_page))); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * 充值卡管理
 */
function nova_admin_cards_html()
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_charge';

    //生成卡密
    if (isset($_POST['nova_card_generate']) && current_user_can('manage_options')) {
        check_admin_referer('nova_card_generate');
        $num = isset($_POST['card_num_count']) ? max(1, min(100, (int) $_POST['card_num_count'])) : 1;
        $price = isset($_POST['card_price']) ? (float) $_POST['card_price'] : 10;
        $type = isset($_POST['card_type']) ? sanitize_text_field(wp_unslash($_POST['card_type'])) : 'money';
        $cards = nova_card_generate($num, $price, $type);
        echo '<div class="notice notice-success"><p>' . sprintf(esc_html__('成功生成 %d 张充值卡', 'nova'), count($cards)) . '</p>';
        echo '<p style="word-break:break-all">';
        foreach ($cards as $card) {
            echo esc_html('卡号：' . $card['num'] . ' 卡密：' . $card['pass']) . '<br>';
        }
        echo '</p></div>';
    }

    $cards = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 50");
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('充值卡管理', 'nova'); ?></h1>
        <form method="post" style="margin:16px 0;padding:16px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;max-width:600px;">
            <?php wp_nonce_field('nova_card_generate'); ?>
            <p>
                <label><?php esc_html_e('生成数量', 'nova'); ?></label>
                <input type="number" name="card_num_count" value="1" min="1" max="100">
            </p>
            <p>
                <label><?php esc_html_e('面值', 'nova'); ?></label>
                <input type="number" name="card_price" value="10" min="0" step="0.01">
                <select name="card_type">
                    <option value="money"><?php esc_html_e('余额卡', 'nova'); ?></option>
                    <option value="points"><?php esc_html_e('积分卡', 'nova'); ?></option>
                </select>
            </p>
            <button type="submit" name="nova_card_generate" class="button button-primary"><?php esc_html_e('生成充值卡', 'nova'); ?></button>
        </form>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('卡号', 'nova'); ?></th>
                    <th><?php esc_html_e('卡密', 'nova'); ?></th>
                    <th><?php esc_html_e('面值', 'nova'); ?></th>
                    <th><?php esc_html_e('类型', 'nova'); ?></th>
                    <th><?php esc_html_e('状态', 'nova'); ?></th>
                    <th><?php esc_html_e('使用用户', 'nova'); ?></th>
                    <th><?php esc_html_e('使用时间', 'nova'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cards as $card) : ?>
                    <tr>
                        <td><?php echo esc_html($card->card_num); ?></td>
                        <td><?php echo esc_html($card->card_pass); ?></td>
                        <td><?php echo esc_html($card->card_price); ?></td>
                        <td><?php echo 'money' === $card->card_type ? esc_html__('余额', 'nova') : esc_html__('积分', 'nova'); ?></td>
                        <td><?php echo $card->card_status ? esc_html__('已使用', 'nova') : esc_html__('未使用', 'nova'); ?></td>
                        <td><?php echo $card->user_id ? esc_html(nova_get_user_name($card->user_id)) : '—'; ?></td>
                        <td><?php echo $card->card_status ? esc_html($card->use_time) : '—'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * 提现管理
 */
function nova_admin_withdraws_html()
{
    global $wpdb;
    $table = $wpdb->prefix . 'nova_withdraw';
    $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
    $withdraw_id = isset($_GET['withdraw_id']) ? (int) $_GET['withdraw_id'] : 0;

    if ($withdraw_id && current_user_can('manage_options')) {
        check_admin_referer('nova_withdraw_action_' . $withdraw_id);
        $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $withdraw_id));
        if ($record) {
            if ('done' === $action) {
                $wpdb->update($table, array('withdraw_status' => 1), array('id' => $withdraw_id));
                NovaMsg::add_system($record->user_id, sprintf(__('你的提现申请 ¥%s 已打款', 'nova'), number_format((float) $record->withdraw_amount, 2)));
            } elseif ('reject' === $action) {
                $wpdb->update($table, array('withdraw_status' => 2), array('id' => $withdraw_id));
                //退回余额
                NovaDB::update_user_balance($record->user_id, (float) $record->withdraw_amount, __('提现申请被拒绝，退回余额', 'nova'));
                NovaMsg::add_system($record->user_id, __('你的提现申请被拒绝，金额已退回余额', 'nova'));
            }
        }
    }

    $records = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 50");
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('提现管理', 'nova'); ?></h1>
        <table class="widefat striped" style="margin-top:16px;">
            <thead>
                <tr>
                    <th><?php esc_html_e('用户', 'nova'); ?></th>
                    <th><?php esc_html_e('金额', 'nova'); ?></th>
                    <th><?php esc_html_e('方式', 'nova'); ?></th>
                    <th><?php esc_html_e('账号', 'nova'); ?></th>
                    <th><?php esc_html_e('姓名', 'nova'); ?></th>
                    <th><?php esc_html_e('状态', 'nova'); ?></th>
                    <th><?php esc_html_e('时间', 'nova'); ?></th>
                    <th><?php esc_html_e('操作', 'nova'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record) : ?>
                    <tr>
                        <td><?php echo esc_html(nova_get_user_name($record->user_id)); ?> (ID:<?php echo esc_html($record->user_id); ?>)</td>
                        <td>¥ <?php echo esc_html(number_format((float) $record->withdraw_amount, 2)); ?></td>
                        <td><?php echo 'alipay' === $record->withdraw_type ? esc_html__('支付宝', 'nova') : esc_html__('微信', 'nova'); ?></td>
                        <td><?php echo esc_html($record->withdraw_account); ?></td>
                        <td><?php echo esc_html($record->withdraw_name); ?></td>
                        <td><?php echo esc_html(array(0 => __('待处理', 'nova'), 1 => __('已完成', 'nova'), 2 => __('已拒绝', 'nova'))[$record->withdraw_status]); ?></td>
                        <td><?php echo esc_html($record->create_time); ?></td>
                        <td>
                            <?php if (0 == $record->withdraw_status) : ?>
                                <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=nova-withdraws&action=done&withdraw_id=' . $record->id), 'nova_withdraw_action_' . $record->id)); ?>"><?php esc_html_e('确认打款', 'nova'); ?></a>
                                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=nova-withdraws&action=reject&withdraw_id=' . $record->id), 'nova_withdraw_action_' . $record->id)); ?>"><?php esc_html_e('拒绝', 'nova'); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
