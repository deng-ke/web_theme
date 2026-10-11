<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-03 00:09:44
 * @LastEditTime : 2026-06-17 12:34:01
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//后台使用VUE的数据合并函数
function zibpay_admin_page_vue_data_filter($vue_data)
{

    add_filter('admin_shop_page_vue_data', function ($__vue_data) use ($vue_data) {
        return array_merge($__vue_data, $vue_data);
    });
}

if (_pz('pay_rebate_s')) {
    add_action('admin_notices', 'zib_withdraw_admin_notice', 1, 1);
}
function zib_withdraw_admin_notice()
{
    if (isset($_GET['page']) && in_array($_GET['page'], ['zibpay_withdraw', 'zibpay_page'])) {
        return;
    }

    $withdraw_count = ZibMsg::get_count(array(
        'type'   => 'withdraw',
        'status' => 0,
    ));
    if ($withdraw_count > 0) {
        $html = '<div class="notice notice-info is-dismissible">';
        $html .= '<h3>' . esc_html__('提现申请待处理', 'zib_language') . '</h3>';
        $html .= '<p>' . sprintf(esc_html__('您有%s个提现申请待处理', 'zib_language'), $withdraw_count) . '</p>';
        $html .= '<p><a class="button" href="' . add_query_arg(array('page' => 'zibpay_withdraw', 'status' => 0), admin_url('admin.php')) . '">' . esc_html__('立即处理', 'zib_language') . '</a></p>';
        $html .= '</div>';
        echo $html;
    }
}

/**
 * @description: 后台用户列表添加会员筛选
 * @param {*}
 * @return {*}
 */
add_filter('views_users', 'zib_admin_user_views');
function zib_admin_user_views($views)
{

    $vip = isset($_REQUEST['vip']) ? $_REQUEST['vip'] : '';
    if (!$views) {
        $views = array();
    }

    for ($i = 1; $i <= 2; $i++) {
        if (_pz('pay_user_vip_' . $i . '_s', true)) {
            $views['vip' . $i] = '<a' . ($vip == $i ? ' class="current"' : '') . ' href="users.php?vip=' . $i . '">' . _pz('pay_user_vip_' . $i . '_name') . '</a>（' . zib_get_vip_user_count($i) . '）';
        }
    }
    return $views;
}

//为后台文章添加表格项目
function zib_admin_post_posts_columns($columns)
{
    $order    = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';
    $order_by = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $o_icon   = '<i class="opacity5 ml3 fa fa-long-arrow-' . ($order == 'asc' ? 'down' : 'up') . '"></i>';

    if (isset($columns['cb'])) {
        $add_columns['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    if (isset($columns['title'])) {
        $add_columns['title'] = $columns['title'];
        unset($columns['title']);
    }
    if (isset($columns['author'])) {
        $add_columns['author'] = $columns['author'];
        unset($columns['author']);
    }

    $add_columns['all_count'] = '<a href="' . add_query_arg(array('orderby' => 'views', 'order' => $order)) . '"><span>' . esc_html__('阅读', 'zib_language') . ($order_by == 'views' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'like', 'order' => $order)) . '"><span>' . esc_html__('点赞', 'zib_language') . ($order_by == 'like' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'favorite', 'order' => $order)) . '"><span>' . esc_html__('收藏', 'zib_language') . ($order_by == 'favorite' ? $o_icon : '') . '</span></a>';
    $add_columns['pay_data']  = '<a href="' . add_query_arg(array('orderby' => 'sales_volume', 'order' => $order)) . '"><span>' . esc_html__('销售数据', 'zib_language') . ($order_by == 'sales_volume' ? $o_icon : '') . '</span></a>';

    return array_merge($add_columns, $columns);
}
add_filter('manage_post_posts_columns', 'zib_admin_post_posts_columns');

function zib_admin_post_posts_custom_column($column_name, $posts_id)
{

    switch ($column_name) {
        case 'pay_data':
            $sales_volume    = (int) get_post_meta($posts_id, 'sales_volume', true);
            $sales_volume_db = zibDB::name(zibpay::$order_table_name)->where('post_id', $posts_id)->where('status', 1)->count();
            $posts_pay       = get_post_meta($posts_id, 'posts_zibpay', true);
            $zibpay_type     = isset($posts_pay['pay_type']) ? (int) $posts_pay['pay_type'] : 0;
            if (!$zibpay_type) {
                echo '<div style="font-size: 12px;">' . esc_html__('未开启付费', 'zib_language') . '</div>';
                break;
            }

            $pay_modo = isset($posts_pay['pay_modo']) ? $posts_pay['pay_modo'] : '0';
            if ($pay_modo === 'points') {
                $points_price = isset($posts_pay['points_price']) ? (int) $posts_pay['points_price'] : 0;
                $zibpay_price = sprintf(__('积分:%s', 'zib_language'), $points_price);
            } else {
                $pay_price    = isset($posts_pay['pay_price']) ? round((float) $posts_pay['pay_price'], 2) : 0;
                $zibpay_price = zibpay_get_pay_mark() . ':' . $pay_price;
            }

            $paydown_log_link = $zibpay_type == 2 ? '<br>' . zibpay_get_paydown_log_admin_link('post', $posts_id, 'px12', '[' . __('查看下载记录', 'zib_language') . ']') : '';

            $con = '<a style="color: ' . ['#ff4747', '#ee5307', '#1e8608', '#1a8a65', '#0c9cc8', '#086ae8', '#3353fd', '#4641e8', '#853bf2', '#e94df7', '#ca2b7d', '#d7354c', '#ff4747', '#8e24ac'][$zibpay_type] . ';" href="' . add_query_arg(array('zibpay_type' => $zibpay_type)) . '">' . zibpay_get_pay_type_name($zibpay_type) . $paydown_log_link . '</a>';
            $con .= '<div style="font-size: 12px;">' . $zibpay_price . ' · ' . esc_html__('销量', 'zib_language') . $sales_volume . ($sales_volume_db ? '<a title="' . esc_attr__('点击查看销售明细', 'zib_language') . '" href="' . zibpay_get_admin_shop_order_url('status=1&post_id=' . $posts_id) . '"> [' . sprintf(esc_html__('真实销量%s', 'zib_language'), $sales_volume_db) . ']</a>' : '') . '</div>';
            echo '<div>' . $con . '</div>';
            break;

        case 'all_count':
            $views          = _cut_count((string) get_post_meta($posts_id, 'views', true));
            $score          = _cut_count((string) get_post_meta($posts_id, 'like', true));
            $favorite_count = _cut_count((string) get_post_meta($posts_id, 'favorite', true));

            echo '<div style="font-size: 12px;">' . esc_html__('阅读', 'zib_language') . $views . ' · ' . esc_html__('点赞', 'zib_language') . $score . ' · ' . esc_html__('收藏', 'zib_language') . $favorite_count . '</div>';
            break;

    }
}
add_action('manage_post_posts_custom_column', 'zib_admin_post_posts_custom_column', 11, 2);

function zib_admin_restrict_manage_posts($post_type)
{
    if ('post' === $post_type) {
        $zibpay_type   = isset($_GET['zibpay_type']) ? (int) $_GET['zibpay_type'] : 0;
        $pay_type_args = array(
            '1' => __('付费阅读', 'zib_language'),
            '2' => __('付费下载', 'zib_language'),
            '5' => __('付费图片', 'zib_language'),
            '6' => __('付费视频', 'zib_language'),
            //  '7' => '自动售卡',
        );
        $option = '<option value="">' . esc_html__('商品类型', 'zib_language') . '</option>';
        foreach ($pay_type_args as $k => $name) {
            $option .= '<option ' . selected($k, $zibpay_type, false) . ' value="' . $k . '">' . $name . '</option>';
        }

        echo '<select class="form-control" name="zibpay_type">' . $option . '</select>';
    }
}
add_action('restrict_manage_posts', 'zib_admin_restrict_manage_posts');

//为后台文章添加筛选和配置
function zib_admin_main_post_query($query)
{

    if ($query->is_main_query() && $query->is_admin) {
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 0;
        if ($orderby) {
            $orderby_keys      = zib_get_query_mate_orderby_keys();
            $mate_orderbys     = $orderby_keys['value'];
            $mate_orderbys_num = $orderby_keys['value_num'];

            if (in_array($orderby, $mate_orderbys_num)) {
                $query->set('orderby', 'meta_value_num');
                $query->set('meta_key', $orderby);
            } elseif (in_array($orderby, $mate_orderbys)) {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', $orderby);
            } else {
                $query->set('orderby', $orderby);
            }
        }

        $zibpay_type = isset($_GET['zibpay_type']) ? (int) $_GET['zibpay_type'] : 0;

        if ($zibpay_type) {
            $meta_query = $query->get('meta_query');

            $filters_meta_query = array(
                'key'   => 'zibpay_type',
                'value' => $zibpay_type,
            );

            $meta_query   = is_array($meta_query) ? $meta_query : array();
            $meta_query[] = $filters_meta_query;

            $query->set('meta_query', $meta_query);
        }

    }

}
add_action('pre_get_posts', 'zib_admin_main_post_query', 99);

//为后台用户列表添加表格项目
function zib_admin_users_list_table_query_args($args)
{
    $orderby           = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $orderby_keys      = zib_get_query_mate_orderby_keys();
    $mate_orderbys     = $orderby_keys['value'];
    $mate_orderbys_num = $orderby_keys['value_num'];

    if (in_array($orderby, $mate_orderbys_num)) {
        $args['orderby']  = 'meta_value_num';
        $args['meta_key'] = $orderby;
    } elseif (in_array($orderby, $mate_orderbys)) {
        $args['orderby']  = 'meta_value';
        $args['meta_key'] = $orderby;
    }

    //默认排序方式为注册时间
    if (!isset($_REQUEST['orderby'])) {
        $args['order']   = 'desc';
        $args['orderby'] = 'user_registered';
    }
    $vip = isset($_REQUEST['vip']) ? $_REQUEST['vip'] : '';
    for ($i = 1; $i <= 2; $i++) {
        if ($vip == $i && _pz('pay_user_vip_' . $i . '_s', true)) {
            $args['meta_key']   = 'vip_level';
            $args['meta_value'] = $i;
        }
    }

    //搜索手机号
    if ($args['search'] && !empty($_REQUEST['search_phone'])) {
        $search             = trim($args['search'], '*');
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key'     => 'phone_number',
                'value'   => $search,
                'compare' => 'like',
            ),
        );
        $args['search'] = '';
    }

    return $args;
}
add_filter('users_list_table_query_args', 'zib_admin_users_list_table_query_args');

//搜索手机号
add_action('manage_users_extra_tablenav', 'zib_manage_users_extra_tablenav');
function zib_manage_users_extra_tablenav()
{
    $html = '<div class="alignright ml10"><label class="button"><input class="hide-column-tog" name="search_phone" type="checkbox" value="on">' . esc_html__('按手机号搜索', 'zib_language') . '</label></div>';
    echo $html;
}

/**挂钩后台用户中心-用户列表 */
function zib_users_columns($columns)
{
    $orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $order   = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';

    unset($columns['role']);
    unset($columns['name']);
    unset($columns['posts']);
    unset($columns['email']);

    $columns['show_name'] = '<a href="' . add_query_arg(array('orderby' => 'display_name', 'order' => $order)) . '"><span>' . esc_html__('昵称', 'zib_language') . '</span></a>';
    $columns['show_name'] .= ' · <a href="' . add_query_arg(array('orderby' => 'email', 'order' => $order)) . '"><span>' . esc_html__('邮箱', 'zib_language') . '</span></a>';
    if (_pz('user_level_s')) {
        $columns['show_name'] .= ' · <a href="' . add_query_arg(array('orderby' => 'level', 'order' => $order)) . '"><span>' . esc_html__('等级', 'zib_language') . '</span></a>';
    }
    $columns['oauth'] = __('社交登录', 'zib_language');
    $columns['oauth'] .= ' · <a href="' . add_query_arg(array('orderby' => 'phone_number', 'order' => $order)) . '"><span>' . esc_html__('手机号', 'zib_language') . '</span></a>';

    $points_s      = _pz('points_s');
    $pay_balance_s = _pz('pay_balance_s');
    if ($pay_balance_s || $points_s) { //资产
        $columns['assets'] = '<a href="' . add_query_arg(array('orderby' => 'balance', 'order' => $order)) . '"><span>' . esc_html__('余额', 'zib_language') . '</span></a>';
        $columns['assets'] .= ' · <a href="' . add_query_arg(array('orderby' => 'points', 'order' => $order)) . '"><span>' . esc_html__('积分', 'zib_language') . '</span></a>';
    }

    $columns['vip_type'] = '<a href="' . add_query_arg(array('orderby' => 'vip_level', 'order' => $order)) . '"><span>' . esc_html__('VIP会员', 'zib_language') . '</span></a>';
    if (_pz('pay_rebate_s')) {
        $columns['rebate_info'] = __('推广返利', 'zib_language');
        $columns['referrer']    = '<a href="' . add_query_arg(array('orderby' => 'referrer_id', 'order' => $order)) . '"><span>' . esc_html__('推荐人', 'zib_language') . '</span></a>';
    }

    if (_pz('pay_income_s')) {
        $columns['income'] = __('创作分成', 'zib_language');
    }

    $columns['all_time'] = '<a href="' . add_query_arg(array('orderby' => 'user_registered', 'order' => $order)) . '"><span>' . esc_html__('注册', 'zib_language') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'last_login', 'order' => $order)) . '"><span>' . esc_html__('登录', 'zib_language') . '</span></a>';
    $columns['count']    = esc_html__('文章', 'zib_language') . ' · ' . esc_html__('评论', 'zib_language');

    return $columns;
}

/**
 * @description: 后台用户表格添加自定义内容
 * @param {*}
 * @return {*}
 */
function zib_output_users_columns($var, $column_name, $user_id)
{

    $user = get_userdata($user_id);

    switch ($column_name) {
        case 'show_name':
            $html = '<a title="' . esc_attr__('在前台查看此用户', 'zib_language') . '" href="' . zib_get_user_home_url($user_id) . '">' . $user->display_name . '</a>';

            if (_pz('user_level_s')) {
                $user_level = zib_get_user_level($user_id);
                $title      = esc_attr(_pz('user_level_opt', 'LV' . $user_level, 'name_' . $user_level));
                $html .= ' · ' . $title;
            }
            if (_pz('user_ban_s')) {
                $is_ban = zib_user_is_ban($user_id);
                if ($is_ban) {
                    $is_ban = 2 === $is_ban ? __('已封号', 'zib_language') : __('小黑屋', 'zib_language');
                    $html .= ' · <code style="font-size: 12px;">' . $is_ban . '</code>';
                }
            }
            if (_pz('user_auth_s')) {
                $is_ban = zib_is_user_auth($user_id);
                if ($is_ban) {
                    $html .= ' · <code style="font-size: 12px;">' . esc_html__('已认证', 'zib_language') . '</code>';
                }
            }

            $html .= '<br><a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';
            if (_pz('user_medal_s')) {
                $medal_details = zib_get_user_medal_details($user_id);
                if ($medal_details) {
                    $i         = 0;
                    $icon_html = '';
                    $max_icon  = 3;
                    $count     = count($medal_details);

                    foreach ($medal_details as $k => $v) {
                        if ($i >= $max_icon) {
                            break;
                        }
                        $i++;
                        $icon_url = $v['icon'];
                        $icon_html .= '<img src="' . $v['icon'] . '" style="vertical-align: -3px;width: 14px;height: 14px;margin-right: 1px;margin-top: 2px;" data-toggle="tooltip" title="' . esc_attr($k) . '" alt="' . esc_attr(sprintf(__('徽章-%s', 'zib_language'), $k)) . '">';
                    }

                    $html .= '<div>' . $icon_html . '<span>' . sprintf(esc_html__('徽章[%s]', 'zib_language'), $count) . '</span></div>';
                }

            }

            return '<div style="font-size: 12px;">' . $html . '</div>';
            break;
        case 'vip_type':
            $level = zib_get_user_vip_level($user_id);
            return $level ? _pz('pay_user_vip_' . $level . '_name') . '<br>' . zib_get_user_vip_exp_date_text($user_id) : __('普通用户', 'zib_language');
            break;
        case 'rebate_info':
            $rebate_ratio = zibpay_get_user_rebate_rule($user->ID);
            if (!$rebate_ratio['type'] || !is_array($rebate_ratio['type'])) {
                return __('不返佣', 'zib_language');
            }

            $rebate_type = zibpay_get_user_rebate_type($rebate_ratio['type'], '/');
            $html        = sprintf(__('%1$s：%2$s%%', 'zib_language'), $rebate_type, $rebate_ratio['ratio']);

            $all     = zibpay_get_user_rebate_data($user_id, 'all')['sum'];
            $invalid = zibpay_get_user_rebate_data($user_id, 'effective')['sum'];
            $invalid = $invalid ? $invalid : 0;

            $html .= $all ? '<br><a title="' . esc_attr__('查看明细', 'zib_language') . '" href="' . admin_url('admin.php?page=zibpay_rebate_page&referrer_id=' . $user->ID) . '">' . sprintf(esc_html__('佣金累计：%1$s · 待提现：%2$s', 'zib_language'), $all, $invalid) . '</a>' : '<br>' . esc_html__('暂无佣金', 'zib_language');

            return '<div style="font-size: 12px;">' . $html . '</div>';

            break;

        case 'income':

            $income_points_ratio    = zibpay_get_user_income_points_ratio($user_id);
            $income_ratio           = zibpay_get_user_income_ratio($user_id);
            $income_price_all       = zibpay_get_user_income_data($user_id, 'all');
            $income_price_effective = zibpay_get_user_income_data($user_id, 'effective');

            $html = '<div style="font-size: 12px;" title="' . esc_attr__('分成比例', 'zib_language') . '">' . sprintf(esc_html__('现金%s%% · 积分%s%%', 'zib_language'), $income_ratio, $income_points_ratio) . '</div>';
            $html .= floatval($income_price_all['sum']) ? '<a title="' . esc_attr__('查看明细', 'zib_language') . '" href="' . admin_url('admin.php?page=zibpay_income_page&post_author=' . $user->ID) . '">' . sprintf(esc_html__('累计：%1$s · 待提现：%2$s', 'zib_language'), (floatval($income_price_all['sum']) ?: '0'), (floatval($income_price_effective['sum']) ?: '0')) . '</a>': '';
            return '<div style="font-size: 12px;">' . $html . '</div>';

            break;

        case 'assets':
            $user_points  = zibpay_get_user_points($user_id);
            $user_balance = _pz('pay_balance_s') ? zibpay_get_user_balance($user_id) : '';
            $html         = '<a title="' . esc_attr__('查看消费记录', 'zib_language') . '" href="' . zibpay_get_admin_shop_order_url('pay_type=balance&user_id=' . $user->ID) . '">' . sprintf(esc_html__('余额：%s', 'zib_language'), $user_balance) . '</a>';
            $html .= '<br><a title="' . esc_attr__('查看消费记录', 'zib_language') . '" href="' . zibpay_get_admin_shop_order_url('pay_type=points&user_id=' . $user->ID) . '">' . sprintf(esc_html__('积分：%s', 'zib_language'), $user_points) . '</a>';
            $html .= '<div class="row-actions px12">' . zibpay_get_user_assets_details_admin_link($user_id, '', __('查看明细', 'zib_language')) . '</div>';

            $pay_down_log = zib_get_user_meta($user_id, 'pay_down_log', true);
            $html .= $pay_down_log ? '<div class="row-actions px12">' . zibpay_get_paydown_log_admin_link('user', $user_id, 'px12', '[' . __('付费资源下载记录', 'zib_language') . ']') . '</div>' : '';

            return '<div style="font-size: 12px;">' . $html . '</div>';
            break;

        case 'referrer':
            $referrer_id = get_user_meta($user_id, 'referrer_id', true);
            if ($referrer_id) {
                $referrer_name = get_userdata($referrer_id)->display_name;
                $level         = zib_get_user_vip_level($referrer_id);
                return '<a title="' . esc_attr__('查看此用户', 'zib_language') . '" href="' . add_query_arg('s', $referrer_name, admin_url('users.php')) . '">' . $referrer_name . '</a>' . ($level ? '<br>' . _pz('pay_user_vip_' . $level . '_name') : '');
            }
            return __('无', 'zib_language');
            break;

        case 'all_time':
            $last_login = get_user_meta($user->ID, 'last_login', true);
            $last_login = $last_login ? '<span title="' . esc_attr($last_login) . '">' . zib_get_time_ago($last_login) . esc_html__('登录', 'zib_language') . '</span>' : '--';

            $reg_time = get_date_from_gmt($user->user_registered);
            $reg_time = $reg_time ? '<span title="' . esc_attr($reg_time) . '">' . zib_get_time_ago($reg_time) . esc_html__('注册', 'zib_language') . '</span>' : '--';

            //登录地址
            $addr      = '';
            $addr_data = zib_get_user_meta($user_id, 'user_addr', true);
            $addr      = zib_get_ip_geographical_position_badge($addr_data, 'city', '');
            $addr      = $addr ? '<br><code style="font-size: 12px;border-radius: 4px;padding: 1px 5px;" title="' . esc_attr(!empty($addr_data['ip']) ? sprintf(__('IP：%s', 'zib_language'), $addr_data['ip']) : '') . '">IP:' . $addr : '</code>';
            return '<div style="font-size: 12px;">' . $reg_time . '<br>' . $last_login . $addr . '</div>';
            break;

        case 'oauth':

            $args   = array();
            $args[] = array(
                'name' => 'QQ',
                'type' => 'qq',
            );
            $args[] = array(
                'name' => __('微信', 'zib_language'),
                'type' => 'weixin',
            );
            $args[] = array(
                'name' => __('微信', 'zib_language'),
                'type' => 'weixingzh',
            );
            $args[] = array(
                'name' => __('微博', 'zib_language'),
                'type' => 'weibo',
            );
            $args[] = array(
                'name' => 'GitHub',
                'type' => 'github',
            );
            $args[] = array(
                'name' => __('码云', 'zib_language'),
                'type' => 'gitee',
            );
            $args[] = array(
                'name' => __('百度', 'zib_language'),
                'type' => 'baidu',
            );
            $args[] = array(
                'name' => __('支付宝', 'zib_language'),
                'type' => 'alipay',
            );
            $oauth = array();
            foreach ($args as $arg) {
                $name = $arg['name'];
                $type = $arg['type'];

                $bind_href = zib_get_oauth_login_url($type);
                if ($bind_href) {
                    $oauth_info = zib_get_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', true);
                    $oauth_id   = get_user_meta($user_id, 'oauth_' . $type . '_openid', true);
                    if ($oauth_info && $oauth_id) {
                        $oauth[] = $name;
                    }
                }
            }

            $html         = $oauth ? sprintf(__('已绑定%s', 'zib_language'), implode(__('、', 'zib_language'), $oauth)) : __('未绑定社交账号', 'zib_language');
            $phone_number = get_user_meta($user->ID, 'phone_number', true);
            $html .= $phone_number ? '<br>' . $phone_number : '<br>' . esc_html__('未绑定手机号', 'zib_language');
            return '<div style="font-size: 12px;">' . $html . '</div>';
            break;
        case 'count':
            $com_n  = (int) get_user_comment_count($user_id);
            $post_n = (int) count_user_posts($user_id, 'post', true);

            $html = '';
            $html .= $post_n ? '<div><a href="edit.php?author=' . $user_id . '">' . sprintf(esc_html__('文章[%s]', 'zib_language'), $post_n) . '</a></div>' : '<div>' . esc_html__('暂无文章', 'zib_language') . '</div>';
            $html .= $com_n ? '<div><a href="edit-comments.php?user_id=' . $user_id . '">' . sprintf(esc_html__('评论[%s]', 'zib_language'), $com_n) . '</a></div>' : '<div>' . esc_html__('暂无评论', 'zib_language') . '</div>';

            return $html;
            break;
    }

    return $var;
}
add_filter('manage_users_columns', 'zib_users_columns');
add_filter('manage_users_custom_column', 'zib_output_users_columns', 10, 3);

//后台用户资料修改

function zib_csf_user_vip_fields()
{
    $args       = array();
    $profile_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if (!$profile_id && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
        $profile_id = get_current_user_id();
    }

    $vip_dec = '<h3>' . esc_html__('会员设置', 'zib_language') . '</h3><p>' . sprintf(esc_html__('修改用户的会员信息，请确保主题设置中的%s已开启', 'zib_language'), '<code>' . esc_html__('VIP会员功能', 'zib_language') . '</code>') . '</p>';

    if ($profile_id) {
        $vip_exp_date = get_user_meta($profile_id, 'vip_exp_date', true);
        $vip_level    = zib_get_user_vip_level($profile_id);

        $vip_dec .= esc_html__('当前用户：', 'zib_language');
        if ($vip_level) {
            if ('Permanent' == $vip_exp_date) {
                $vip_dec .= sprintf(esc_html__('已开通%s，永久有效', 'zib_language'), '<code>' . _pz('pay_user_vip_' . $vip_level . '_name') . '</code>');
            } else {
                $vip_dec .= sprintf(esc_html__('已开通%s，到期时间：%s', 'zib_language'), '<code>' . _pz('pay_user_vip_' . $vip_level . '_name') . '</code>', date(__('Y年m月d日', 'zib_language'), strtotime($vip_exp_date)));
            }
        } else {
            $vip_level_expired = (int) zib_get_user_meta($profile_id, 'vip_level_expired', true);
            if ($vip_level_expired) {
                $vip_dec .= sprintf(esc_html__('开通的%s已过期，过期时间：%s', 'zib_language'), '<code>' . _pz('pay_user_vip_' . $vip_level_expired . '_name') . '</code>', date(__('Y年m月d日', 'zib_language'), strtotime($vip_exp_date)));
            } else {
                $vip_dec .= esc_html__('未开通会员', 'zib_language');
            }
        }
    }

    $args[] = array(
        'type'    => 'content',
        'content' => $vip_dec,
    );

    $args[] = array(
        'id'      => 'vip_level',
        'type'    => 'radio',
        'title'   => __('VIP会员设置', 'zib_language'),
        'default' => '0',
        'desc'    => __('在此直接修改此用户的会员信息，涉及到用户权益请谨慎修改', 'zib_language'),
        'options' => array(
            '0' => __('普通用户', 'zib_language'),
            '1' => _pz('pay_user_vip_1_name'),
            '2' => _pz('pay_user_vip_2_name'),
        ),
    );
    $args[] = array(
        'id'         => 'vip_exp_date',
        'dependency' => array('vip_level', '>=', '1'),
        'type'       => 'date',
        'title'      => __('会员有效期', 'zib_language'),
        'desc'       => '<p>' . __('请输入或选择有效期，请确保格式正确，例如：', 'zib_language') . '<code>2020-10-10 23:59:59</code></p>' . sprintf(__('如果需要设置为“永久有效会员”，请手动设置为：%s', 'zib_language'), '<code>Permanent</code>'),
        'settings'   => array(
            'dateFormat'  => 'yy-mm-dd 23:59:59',
            'changeMonth' => true,
            'changeYear'  => true,
        ),
    );
    return $args;
}

function zib_csf_user_points_balance_fields($type = 'points')
{

    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;

    if (!$user_id && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
        $user_id = get_current_user_id();
    }

    $user_points  = zibpay_get_user_points($user_id);
    $user_balance = _pz('pay_balance_s') ? zibpay_get_user_balance($user_id) : '';

    $text       = $type === 'balance' ? __('余额', 'zib_language') : __('积分', 'zib_language');
    $type_class = $type === 'balance' ? 'c-blue-2' : 'c-green';
    $action     = 'admin_update_user_' . $type; //管理
    $val        = $type === 'balance' ? zibpay_get_user_balance($user_id) : zibpay_get_user_points($user_id); //
    if ($type === 'balance') {
    }

    $con = '<div class="options-notice">
        <div class="explain">
        <p><b>' . sprintf(esc_html__('您可以在此处手动为用户添加或扣除%s', 'zib_language'), $text) . '</b></p>
        <ajaxform class="ajax-form">
            <p class="flex ac"><select ajax-name="type">
                    <option value="">' . esc_html__('请选择添加或扣除', 'zib_language') . '</option>
                    <option value="add">' . esc_html__('添加', 'zib_language') . '</option>
                    <option value="delete">' . esc_html__('扣除', 'zib_language') . '</option>
                </select>
                <input style="max-width:120px;" ajax-name="val" type="number" placeholder="' . esc_attr__('请输入数额', 'zib_language') . '"></p>
            <p class="">
                <div class="">' . esc_html__('请填写添加或扣除的简短说明', 'zib_language') . '</div>
                <input type="text" placeholder="' . esc_attr__('添加或扣除的简短说明', 'zib_language') . '" style="width: 95%;" ajax-name="decs">
            </p>
            <div class="ajax-notice"></div>
            <p><a href="javascript:;" class="but ajax-submit ' . $type_class . '"> ' . esc_html__('确认提交', 'zib_language') . '</a></p>
            <input type="hidden" ajax-name="action" value="' . $action . '">
            <input type="hidden" ajax-name="user_id" value="' . $user_id . '">
            <input type="hidden" ajax-name="_wpnonce" value="' . wp_create_nonce($action) . '">
        </ajaxform>
    </div></div>';

    $args[] = array(
        'type'    => 'content',
        'content' => '<h3>' . sprintf(esc_html__('用户%s', 'zib_language'), $text) . '</h3>',
    );
    $args[] = array(
        'title'   => sprintf(__('当前%s', 'zib_language'), $text),
        'type'    => 'content',
        'content' => '<span class="but ' . $type_class . '">' . $val . '</span>',
    );
    $args[] = array(
        'class'   => 'compact',
        'type'    => 'submessage',
        'style'   => 'warning',
        'content' => $con,
    );

    return $args;

}

function zib_admin_user_balance_points_csf()
{
    $points_s      = _pz('points_s');
    $pay_balance_s = _pz('pay_balance_s');

    if ($pay_balance_s) {
        CSF::createProfileOptions('user_balance', array(
            'data_type' => 'unserialize',
        ));
        CSF::createSection('user_balance', array(
            'fields' => zib_csf_user_points_balance_fields('balance'),
        ));
    }
    if ($points_s) {
        CSF::createProfileOptions('user_points', array(
            'data_type' => 'unserialize',
        ));
        CSF::createSection('user_points', array(
            'fields' => zib_csf_user_points_balance_fields('points'),
        ));
    }
}
add_action('after_setup_theme', 'zib_admin_user_balance_points_csf');

function zib_admin_user_vip_csf()
{
    CSF::createProfileOptions('user_vip', array(
        'data_type' => 'unserialize',
    ));
    CSF::createSection('user_vip', array(
        'fields' => zib_csf_user_vip_fields(),
    ));
}

if (is_super_admin()) {
    add_action('after_setup_theme', 'zib_admin_user_vip_csf');
    add_action('show_user_profile', 'zib_render_profile_rebate_income_form_fields', 20);
    add_action('edit_user_profile', 'zib_render_profile_rebate_income_form_fields', 20);
    add_action('personal_options_update', 'zib_admin_save_profile_rebate_income');
    add_action('edit_user_profile_update', 'zib_admin_save_profile_rebate_income');
}

function zib_render_profile_rebate_income_form_fields($profile_user)
{
    $fields = array();
    $value  = array();

    if (_pz('pay_income_s', true)) {
        if (!empty($profile_user->ID)) {
            $value = array(
                'income_rule' => zib_get_user_meta($profile_user->ID, 'income_rule', true),
            );
        }

        $fields = array_merge($fields, array(
            array(
                'id'     => 'income_rule',
                'type'   => 'fieldset',
                'fields' => array(
                    array(
                        'type'    => 'content',
                        'content' => '<h3>' . esc_html__('创作分成', 'zib_language') . '</h3>' . esc_html__('在此处您可以单独为此用户设置创作分成比例', 'zib_language'),
                    ),
                    array(
                        'id'    => 'switch',
                        'type'  => 'switcher',
                        'title' => __('独立设置', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'ratio',
                        'type'       => 'spinner',
                        'title'      => __('现金分成比例', 'zib_language'),
                        'desc'       => __('为0则不参与分成', 'zib_language'),
                        'min'        => 0,
                        'max'        => 100,
                        'step'       => 5,
                        'unit'       => '%',
                        'default'    => 0,
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'points_ratio',
                        'type'       => 'spinner',
                        'title'      => __('积分分成比例', 'zib_language'),
                        'desc'       => __('为0则不参与分成（用户采用积分支付的订单给与作者的分成比例）', 'zib_language'),
                        'min'        => 0,
                        'max'        => 100,
                        'step'       => 5,
                        'unit'       => '%',
                        'default'    => 0,
                    ),
                ),
            ),
        ));
    }

    if (_pz('pay_rebate_s', true)) {
        if (!empty($profile_user->ID)) {
            $value += array(
                'rebate_rule' => zib_get_user_meta($profile_user->ID, 'rebate_rule', true),
            );
        }

        $fields = array_merge($fields, array(
            array(
                'id'     => 'rebate_rule',
                'type'   => 'fieldset',
                'fields' => array(
                    array(
                        'type'    => 'content',
                        'content' => '<h3>' . esc_html__('推广返利', 'zib_language') . '</h3>' . esc_html__('在此处您可以单独为此用户设置返利规则。为用户开启独立设置后，则不受主题设置的规则约束', 'zib_language'),
                    ),
                    array(
                        'id'    => 'switch',
                        'type'  => 'switcher',
                        'title' => __('独立设置', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'type',
                        'type'       => 'checkbox',
                        'title'      => __('返利订单', 'zib_language'),
                        'desc'       => __('给用户返利的订单类型', 'zib_language') . '<br>' . __('全部关闭，则代表此用户不参与推广返佣', 'zib_language'),
                        'options'    => CFS_Module::rebate_type(),
                        'default'    => array('all'),
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'ratio',
                        'type'       => 'spinner',
                        'title'      => __('佣金比例', 'zib_language'),
                        'min'        => 0,
                        'max'        => 100,
                        'step'       => 5,
                        'unit'       => '%',
                        'default'    => 10,
                    ),
                ),
            ),
        ));
    }

    $csf_args = array(
        'class'  => 'csf-profile-options',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );
    ZCSF::instance('profile_options', $csf_args);
}

function zib_admin_save_profile_rebate_income($cuid)
{

    $fields = array(
        'rebate_rule',
        'income_rule',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_user_meta($cuid, $field, $_POST[$field]);
        }
    }
}

/**
 * @description: 文章付费设置的数据转换
 * @param {*}
 * @return {*}
 */
function zibpay_post_meta_to_csf($post_type, $post)
{
    $post_id = !empty($post->ID) ? $post->ID : '';

    if (!$post_id) {
        return;
    }

    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);

    if (!empty($pay_mate['pay_download']) && !is_array($pay_mate['pay_download'])) {
        $pay_download_args        = zibpay_get_post_down_array($pay_mate);
        $pay_mate['pay_download'] = $pay_download_args;
        update_post_meta($post_id, 'posts_zibpay', $pay_mate);
    }
}
add_action('add_meta_boxes', 'zibpay_post_meta_to_csf', 1, 2);

//添加文章付费参数
function zib_admin_post_posts_zibpay_metabox()
{
    if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') !== false || strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false) {
        CSF::createMetabox('posts_zibpay', zibpay_post_mate_csf_meta());
        CSF::createSection('posts_zibpay', array(
            'fields' => zibpay_post_mate_csf_fields(),
        ));
    }
}
add_action('after_setup_theme', 'zib_admin_post_posts_zibpay_metabox');

function zibpay_post_mate_csf_meta()
{
    $meta = array(
        'title'     => __('付费功能', 'zib_language'),
        'post_type' => array('post'),
        'data_type' => 'serialize',
    );
    return apply_filters('zib_add_pay_meta_box_meta', $meta);
}

function zibpay_get_pay_type_options()
{
    return array(
        'no' => __('关闭', 'zib_language'),
        '1'  => __('付费阅读', 'zib_language'),
        '2'  => __('付费下载', 'zib_language'),
        '5'  => __('付费图片', 'zib_language'),
        '6'  => __('付费视频', 'zib_language'),
    );
}

/**
 * @description: 文章post_mate的设置数据
 * @param {*}
 * @return {*}
 */
function zibpay_post_mate_csf_fields()
{
    //对老板数据做兼容处理
    $post_id = !empty($_GET['post']) ? (int) $_GET['post'] : 0;
    if ($post_id) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
        if (!empty($pay_mate['pay_download']) && !is_array($pay_mate['pay_download'])) {
            $pay_mate['pay_download'] = zibpay_get_post_down_array($pay_mate);
            update_post_meta($post_id, 'posts_zibpay', $pay_mate);
        }
    }
    $pay_cuont_default = zib_get_mt_rand_number(_pz('pay_cuont_default', 0));
    $new_badge         = zib_get_csf_option_new_badge();

    $fields = array(
        array(
            'title'   => __('付费模式', 'zib_language'),
            'id'      => 'pay_type',
            'type'    => 'radio',
            'default' => 'no',
            'inline'  => true,
            'options' => 'zibpay_get_pay_type_options',
        ),
        //显示购买用户权限
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('购买权限', 'zib_language'),
            'id'         => 'pay_limit',
            'type'       => 'radio',
            'default'    => '0',
            'desc'       => __('设置此处可实现会员专享资源功能，配置对应的会员价格可实现专享免费资源', 'zib_language') . '<br/><i class="fa fa-fw fa-info-circle fa-fw"></i> ' . __('使用此功能，请确保付费会员功能已开启，否则会出错', 'zib_language'),
            'options'    => array(
                '0' => __('所有人可购买', 'zib_language'),
                '1' => _pz('pay_user_vip_1_name') . __('及以上会员可购买', 'zib_language'),
                '2' => sprintf(__('仅%s可购买', 'zib_language'), _pz('pay_user_vip_2_name')),
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('支付类型', 'zib_language'),
            'id'         => 'pay_modo',
            'type'       => 'radio',
            'default'    => _pz('pay_modo_default', 0),
            'options'    => array(
                '0'      => __('普通商品（金钱购买）', 'zib_language'),
                'points' => __('积分商品（积分购买，依赖于用户积分功能）', 'zib_language'),
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('订单时效', 'zib_language') . $new_badge['8.7'],
            'id'         => 'order_expired_s',
            'default'    => false,
            'type'       => 'switcher',
            'desc'       => __('设置订单时效后，用户购买后在订单时效内可查看内容，超时后需再次购买才能查看（注意：设置后建议在简介或内容中提醒用户订单时效，避免用户投诉）', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('order_expired_s', '!=', ''),
            ),
            'id'         => 'order_expired_opt',
            'type'       => 'fieldset',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => __('有效时间', 'zib_language'),
            'sanitize'   => false,
            'fields'     => array(
                array(
                    'title'   => __('订单有效期', 'zib_language'),
                    'id'      => 'time',
                    'default' => 0,
                    'type'    => 'number',
                ),
                array(
                    'title'   => __('单位', 'zib_language'),
                    'type'    => 'select',
                    'class'   => 'compact',
                    'id'      => 'unit',
                    'options' => array(
                        'day'    => __('天', 'zib_language'),
                        'hour'   => __('小时', 'zib_language'),
                        'minute' => __('分钟', 'zib_language'),
                    ),
                    'default' => 'day',
                ),
            ),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'id'         => 'points_price',
            'title'      => __('积分售价', 'zib_language'),
            'class'      => '',
            'default'    => _pz('points_price_default'),
            'type'       => 'number',
            'unit'       => __('积分', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . __('积分售价', 'zib_language'),
            'id'         => 'vip_1_points',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_1_name')),
            'default'    => _pz('vip_1_points_default'),
            'type'       => 'number',
            'unit'       => __('积分', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . __('积分售价', 'zib_language'),
            'id'         => 'vip_2_points',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_2_name')),
            'default'    => _pz('vip_2_points_default'),
            'type'       => 'number',
            'unit'       => __('积分', 'zib_language'),
            'desc'       => __('会员价格不能高于售价', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_price',
            'title'      => __('执行价', 'zib_language'),
            'subtitle'   => __('填0则为免费', 'zib_language'),
            'default'    => _pz('pay_price_default', '0.01'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_original_price',
            'title'      => __('原价', 'zib_language'),
            'class'      => 'compact',
            'subtitle'   => __('显示在执行价格前面，并划掉', 'zib_language'),
            'default'    => _pz('pay_original_price_default'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_original_price', '!=', ''),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => ' ',
            'subtitle'   => __('促销标签', 'zib_language'),
            'class'      => 'compact',
            'id'         => 'promotion_tag',
            'sanitize'   => false,
            'type'       => 'textarea',
            'default'    => _pz('pay_promotion_tag_default', '<i class="fa fa-fw fa-bolt"></i> 限时特惠'),
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . __('价格', 'zib_language'),
            'id'         => 'vip_1_price',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_1_name')),
            'default'    => _pz('vip_1_price_default'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . __('价格', 'zib_language'),
            'id'         => 'vip_2_price',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_2_name')),
            'default'    => _pz('vip_2_price_default'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
            'desc'       => __('会员价格不能高于执行价', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => __('推广折扣', 'zib_language'),
            'id'         => 'pay_rebate_discount',
            'class'      => 'compact',
            'subtitle'   => __('通过推广链接购买，额外优惠的金额', 'zib_language'),
            'desc'       => __('1.需开启推广返佣功能  2.注意此金不能超过实际购买价，避免出现负数', 'zib_language'),
            'default'    => _pz('pay_rebate_discount', 0),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array('pay_type', '==', 2),
            'id'         => 'download_limit_over_price',
            'title'      => __('免费下载次数超限后价格', 'zib_language') . $new_badge['8.7'],
            'desc'       => __('如开启免费资源每日下载次数限制，则用户下载次数超限后，可按照此价格进行购买（填0则不可购买）', 'zib_language') . ' ' . __('配置每日下载次数限制 ', 'zib_language') . '<a href="' . esc_url(zib_get_admin_csf_url('支付付费/vip-会员')) . '">' . __('【去配置】', 'zib_language') . '</a>',
            'class'      => '',
            'default'    => _pz('pay_download_limit_over_price', 0),
            'type'       => 'number',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('销量浮动', 'zib_language'),
            'id'         => 'pay_cuont',
            'subtitle'   => __('为真实销量增加或减少的数量', 'zib_language'),
            'default'    => $pay_cuont_default,
            'type'       => 'number',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('优惠码', 'zib_language'),
            'label'      => __('允许使用优惠码', 'zib_language'),
            'desc'       => __('开启后请在优惠码管理中添加优惠码 ', 'zib_language') . '<a target="_blank" href="' . esc_url(admin_url('admin.php?page=zibpay_coupon_page')) . '">' . __('【去管理】', 'zib_language') . '</a>' . '<div class="c-yellow">' . __('由于php特性，此功能有一定风险可能会出现优惠码被多个订单同时使用的情况，建议仅在特殊活动时，短时间开启', 'zib_language') . '</div>',
            'id'         => 'coupon_s',
            'default'    => false,
            'type'       => 'switcher',
        ),
        array(
            'dependency' => array('coupon_s|pay_type', '!=|!=', '|no'),
            'title'      => ' ',
            'subtitle'   => __('优惠券默认说明', 'zib_language'),
            'class'      => 'compact',
            'id'         => 'coupon_desc',
            'default'    => '',
            'desc'       => __('用户填写优惠码时，展示的提醒内容，支持html代码，请注意代码规范', 'zib_language'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency'  => array('pay_type', '==', 5),
            'title'       => __('付费图片', 'zib_language'),
            'id'          => 'pay_gallery',
            'type'        => 'gallery',
            'add_title'   => __('新增图片', 'zib_language'),
            'edit_title'  => __('编辑图片', 'zib_language'),
            'clear_title' => __('清空图片', 'zib_language'),
            'default'     => false,
        ),
        array(
            'dependency' => array('pay_type|pay_gallery', '==|!=', '5|'),
            'title'      => ' ',
            'subtitle'   => __('免费查看', 'zib_language'),
            'class'      => 'compact',
            'id'         => 'pay_gallery_show',
            'default'    => _pz('pay_gallery_show_default', 1),
            'min'        => 0,
            'step'       => 1,
            'unit'       => __('张', 'zib_language'),
            'desc'       => __('设置可免费查看前几张图片的数量，不能大于付费图片数量，否则无效', 'zib_language'),
            'type'       => 'spinner',
        ),
        array(
            'dependency' => array('pay_type', '==', 6),
            'title'      => __('视频资源', 'zib_language'),
            'id'         => 'video_url',
            'type'       => 'upload',
            'library'    => 'video',
            'preview'    => false,
            'default'    => '',
            'desc'       => __('输入视频地址或选择、上传本地视频', 'zib_language'),
        ),
        array(
            'dependency' => array('pay_type|video_url', '==|!=', '6|'),
            'title'      => ' ',
            'subtitle'   => __('视频封面(可选)', 'zib_language'),
            'class'      => 'compact',
            'id'         => 'video_pic',
            'type'       => 'upload',
            'library'    => 'image',
            'default'    => '',
        ),
        array(
            'dependency'  => array('pay_type|video_url', '==|!=', '6|'),
            'id'          => 'video_title',
            'title'       => ' ',
            'subtitle'    => __('本集标题(如需添加剧集则需填写此处)', 'zib_language'),
            'placeholder' => __('第1集', 'zib_language'),
            'default'     => '',
            'class'       => 'compact',
            'type'        => 'text',
        ),
        array(
            'dependency'   => array('pay_type|video_url', '==|!=', '6|'),
            'id'           => 'video_episode',
            'type'         => 'group',
            'button_title' => __('添加剧集', 'zib_language'),
            'class'        => 'compact',
            'title'        => __('更多剧集', 'zib_language'),
            'subtitle'     => __('为付费视频添加更多剧集', 'zib_language'),
            'default'      => array(),
            'fields'       => array(
                array(
                    'id'       => 'title',
                    'title'    => ' ',
                    'subtitle' => __('剧集标题', 'zib_language'),
                    'default'  => '',
                    'type'     => 'text',
                ),
                array(
                    'title'       => ' ',
                    'subtitle'    => __('视频地址', 'zib_language'),
                    'id'          => 'url',
                    'class'       => 'compact',
                    'type'        => 'upload',
                    'preview'     => false,
                    'library'     => 'video',
                    'placeholder' => __('选择视频或填写视频地址', 'zib_language'),
                    'default'     => false,
                ),

            ),
        ),
        array(
            'dependency' => array('pay_type', '==', '6'),
            'id'         => 'video_scale_height',
            'title'      => __('视频设置', 'zib_language'),
            'subtitle'   => __('固定长宽比例', 'zib_language'),
            'default'    => 0,
            'max'        => 200,
            'min'        => 0,
            'step'       => 5,
            'unit'       => '%',
            'type'       => 'spinner',
            'desc'       => __('为0则不固定长宽比例', 'zib_language'),
        ),
        array(
            'dependency'   => array('pay_type', '==', 2),
            'id'           => 'pay_download',
            'type'         => 'group',
            'button_title' => __('添加资源', 'zib_language'),
            'title'        => __('资源下载', 'zib_language'),
            'sanitize'     => false,
            'class'        => 'pay-download-group',
            'fields'       => array(
                array(
                    'title'       => __('下载地址', 'zib_language'),
                    'id'          => 'link',
                    'placeholder' => __('上传文件或输入下载地址', 'zib_language'),
                    'preview'     => false,
                    'type'        => 'upload',
                    'desc'        => __('部分云盘的分享链接直接粘贴，可自动识别链接及提取码', 'zib_language'),
                ),
                array(
                    'title'      => __('资源备注', 'zib_language'),
                    'desc'       => __('按钮旁边的额外内容，例如：提取密码、解压密码等', 'zib_language'),
                    'id'         => 'more',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'    => __('点击复制', 'zib_language'),
                    'subtitle' => __('复制的名称', 'zib_language'),
                    'class'    => 'compact',
                    'default'  => '',
                    'id'       => 'copy_key',
                    'type'     => 'text',
                ),
                array(
                    'title'    => ' ',
                    'subtitle' => __('复制的内容', 'zib_language'),
                    'class'    => 'compact',
                    'default'  => '',
                    'id'       => 'copy_val',
                    'type'     => 'text',
                    'desc'     => __('为“资源备注”按钮添加点击复制功能，请设置复制名称和复制内容', 'zib_language'),
                ),
                array(
                    'id'           => 'icon',
                    'type'         => 'icon',
                    'title'        => __('自定义按钮图标', 'zib_language'),
                    'button_title' => __('选择图标', 'zib_language'),
                    'default'      => 'fa fa-download',
                ),
                array(
                    'title'      => __('自定义按钮文案', 'zib_language'),
                    'class'      => 'compact',
                    'id'         => 'name',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'   => __('自定义按钮颜色', 'zib_language'),
                    'class'   => 'compact skin-color',
                    'desc'    => __('按钮图标、文案、颜色默认均会自动获取，建议为空即可。', 'zib_language') . '<br>' . __('上方的按钮图标为主题自带的fontawesome 4图标库，如需添加其它图标可采用HTML代码，请注意代码规范！', 'zib_language') . '<br><a href="https://www.zibll.com/547.html" target="_blank">' . __('使用阿里巴巴Iconfont图标详细图文教程', 'zib_language') . '</a>',
                    'id'      => 'class',
                    'type'    => 'palette',
                    'options' => CFS_Module::zib_palette(),
                ),
            ),
        ),
        array(
            'dependency'   => array('pay_type', '==', 2),
            'id'           => 'attributes',
            'type'         => 'group',
            'button_title' => __('添加属性', 'zib_language'),
            'title'        => __('资源属性', 'zib_language'),
            'default'      => _pz('pay_attributes_default', array()),
            'fields'       => array(
                array(
                    'title'   => __('属性名称', 'zib_language'),
                    'default' => '',
                    'id'      => 'key',
                    'type'    => 'text',
                ),
                array(
                    'title'   => __('属性内容', 'zib_language'),
                    'class'   => 'compact',
                    'default' => '',
                    'id'      => 'value',
                    'type'    => 'text',
                ),
            ),
        ),
        array(
            'dependency'   => array('pay_type', '==', 2),
            'title'        => __('演示地址', 'zib_language'),
            'id'           => 'demo_link',
            'default'      => array(),
            'add_title'    => __('添加演示', 'zib_language'),
            'edit_title'   => __('编辑地址', 'zib_language'),
            'remove_title' => __('移除演示地址', 'zib_language'),
            'type'         => 'link',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('商品信息', 'zib_language'),
            'subtitle'   => __('商品标题', 'zib_language'),
            'desc'       => __('（可选）如需要单独显示商品标题请填写此项', 'zib_language'),
            'id'         => 'pay_title',
            'type'       => 'text',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => ' ',
            'subtitle'   => __('商品简介', 'zib_language'),
            'id'         => 'pay_doc',
            'desc'       => __('（可选）如需要单独显示商品介绍请填写此项', 'zib_language'),
            'class'      => 'compact',
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => ' ',
            'subtitle'   => __('更多详情', 'zib_language'),
            'id'         => 'pay_details',
            'desc'       => __('（可选）显示在商品卡片下方的内容（支持HTML代码，请注意代码规范）', 'zib_language'),
            'class'      => 'compact',
            'default'    => _pz('pay_details_default'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 3,
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => ' ',
            'subtitle'   => __('额外隐藏内容', 'zib_language'),
            'id'         => 'pay_extra_hide',
            'desc'       => __('（可选）付费后显示的额外隐藏内容（支持HTML代码，请注意代码规范）', 'zib_language'),
            'class'      => 'compact',
            'default'    => _pz('pay_extra_hide_default'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 3,
            ),
        ),

        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'content'    => '<li><qc style="color:#fb2121;background:undefined">' . esc_html__('付费阅读', 'zib_language') . '</qc>' . sprintf(esc_html__('功能需要配合%s或者古腾堡%s使用', 'zib_language'), '<qc style="color:#fb2121;background:undefined">' . esc_html__('短代码', 'zib_language') . '</qc>', '<qc style="color:#fb2121;background:undefined">' . esc_html__('隐藏内容块', 'zib_language') . '</qc>') . ' </li><li>' . esc_html__('古腾堡编辑器：添加块-zibll主题模块-隐藏内容块-设置隐藏模式为：付费阅读', 'zib_language') . ' </li><li>' . sprintf(esc_html__('经典编辑器：插入短代码： %1$s 隐藏内容 %2$s', 'zib_language'), '<code>[hidecontent type="payshow"]</code>', '<code>[/hidecontent]</code>') . ' </li><li><a href="https://www.zibll.com/580.html" target="_blank">' . esc_html__('官方教程', 'zib_language') . '</a> | <a href="' . zib_get_admin_csf_url('商城配置') . '" target="_blank">' . esc_html__('商城设置', 'zib_language') . '</a></li>',
            'style'      => 'warning',
            'type'       => 'submessage',
        ),
    );
    return apply_filters('zib_add_pay_meta_box_args', $fields);
}

/**
 * 获取管理员查看付费下载记录明细的链接
 * @param {*}
 * @return {*}
 */
function zibpay_get_paydown_log_admin_link($type, $id, $class = '', $con = null, $paged = 1)
{
    if (!$id) {
        return;
    }

    if (null === $con) {
        $con = __('下载记录', 'zib_language');
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => '' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'admin_paydown_log',
            'type'   => $type,
            'id'     => $id,
            'paged'  => $paged,
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取管理员查看用户资产记录明细的链接
 * @param {*}
 * @return {*}
 */
function zibpay_get_user_assets_details_admin_link($user_id, $class = '', $con = null)
{

    if (null === $con) {
        $con = __('查看明细', 'zib_language');
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => '' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action'  => 'admin_assets_details',
            'user_id' => $user_id,
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//后台列表中的批量编辑和快速编辑
add_action('bulk_edit_custom_box', 'zibpay_bulk_edit_custom_box', 50, 2);
add_action('quick_edit_custom_box', 'zibpay_bulk_edit_custom_box', 50, 2);
add_action('save_post', 'zibpay_bulk_edit_save_post', 10, 3);
function zibpay_bulk_edit_custom_box($column_name, $post_type)
{
    $permissible_posts_type = ['post'];
    if (!in_array($post_type, $permissible_posts_type) || $column_name !== 'taxonomy-topics') {
        return;
    }

    $item_html   = '';
    $fields_args = array(
        array(
            'id'      => 'pay_type',
            'type'    => 'radio',
            'title'   => __('付费模式', 'zib_language'),
            'options' => zibpay_get_pay_type_options(),
        ),
        array(
            'id'      => 'pay_limit',
            'type'    => 'radio',
            'title'   => __('购买权限', 'zib_language'),
            'options' => array(
                '0' => __('所有人可购买', 'zib_language'),
                '1' => _pz('pay_user_vip_1_name') . __('及以上会员可购买', 'zib_language'),
                '2' => sprintf(__('仅%s可购买', 'zib_language'), _pz('pay_user_vip_2_name')),
            ),
        ),
        array(
            'id'      => 'pay_modo',
            'type'    => 'radio',
            'title'   => __('支付类型', 'zib_language'),
            'options' => array(
                '0'      => __('普通商品', 'zib_language'),
                'points' => __('积分商品', 'zib_language'),
            ),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'id'         => 'points_price',
            'title'      => __('积分售价', 'zib_language'),
            'class'      => '',
            'default'    => _pz('points_price_default'),
            'type'       => 'number',
            'unit'       => __('积分', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . __('积分售价', 'zib_language'),
            'id'         => 'vip_1_points',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_1_name')),
            'default'    => _pz('vip_1_points_default'),
            'type'       => 'number',
            'unit'       => __('积分', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . __('积分售价', 'zib_language'),
            'id'         => 'vip_2_points',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_2_name')),
            'default'    => _pz('vip_2_points_default'),
            'type'       => 'number',
            'unit'       => __('积分', 'zib_language'),
            'desc'       => __('会员价格不能高于售价', 'zib_language'),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_price',
            'title'      => __('执行价', 'zib_language'),
            'default'    => _pz('pay_price_default', '0.01'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_original_price',
            'title'      => __('原价', 'zib_language'),
            'class'      => 'compact',
            'subtitle'   => __('显示在执行价格前面，并划掉', 'zib_language'),
            'default'    => _pz('pay_original_price_default'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . __('价格', 'zib_language'),
            'id'         => 'vip_1_price',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_1_name')),
            'default'    => _pz('vip_1_price_default'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . __('价格', 'zib_language'),
            'id'         => 'vip_2_price',
            'class'      => 'compact',
            'subtitle'   => sprintf(__('填0则为%s免费', 'zib_language'), _pz('pay_user_vip_2_name')),
            'default'    => _pz('vip_2_price_default'),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
            'desc'       => __('会员价格不能高于执行价', 'zib_language'),
        ),
        array(
            'dependency' => array('pay_type', '==', 2),
            'id'         => 'download_limit_over_price',
            'title'      => __('免费下载次数超限后价格', 'zib_language'),
            'class'      => '',
            'default'    => '',
            'type'       => 'number',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => __('推广折扣', 'zib_language'),
            'id'         => 'pay_rebate_discount',
            'class'      => 'compact',
            'subtitle'   => __('通过推广链接购买，额外优惠的金额', 'zib_language'),
            'desc'       => __('1.需开启推广返佣功能  2.注意此金不能超过实际购买价，避免出现负数', 'zib_language'),
            'default'    => _pz('pay_rebate_discount', 0),
            'type'       => 'number',
            'unit'       => zibpay_get_currency_unit(),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('销量浮动', 'zib_language'),
            'id'         => 'pay_cuont',
            'subtitle'   => __('为真实销量增加或减少的数量', 'zib_language'),
            'default'    => '',
            'type'       => 'number',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('允许使用优惠码', 'zib_language'),
            'id'         => 'coupon_s',
            'default'    => false,
            'type'       => 'switcher',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => __('订单时效', 'zib_language'),
            'id'         => 'order_expired_s',
            'default'    => false,
            'type'       => 'switcher',
        ),
        array(
            'title'   => __('订单时效有效期数值', 'zib_language'),
            'id'      => 'order_expired_time',
            'default' => 0,
            'type'    => 'number',
        ),
        array(
            'title'   => __('订单时效有效期单位', 'zib_language'),
            'type'    => 'select',
            'class'   => 'compact',
            'id'      => 'order_expired_unit',
            'options' => array(
                'day'    => __('天', 'zib_language'),
                'hour'   => __('小时', 'zib_language'),
                'minute' => __('分钟', 'zib_language'),
            ),
            'default' => 'day',
        ),
    );

    echo zib_get_quick_edit_custom_input($fields_args, 'pay', __('付费参数', 'zib_language'), '<div class="c-yellow">' . esc_html__('批量设置付费参数，请仔细检查配置内容，更新后无法撤回。请注意配置逻辑，例如会员价不能高于普通价，执行价不能高于原价等', 'zib_language') . '</div>');
}

//批量编辑保存
function zibpay_bulk_edit_save_post($post_id, $post, $update)
{
    $permissible_posts_type = ['post', ''];
    if (!$update || !in_array($post->post_type, $permissible_posts_type) || empty($_REQUEST['zib_bulk_edit']['pay']) || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== 'edit-post') {
        return;
    }

    $zibpay_bulk_edit = $_REQUEST['zib_bulk_edit']['pay'];
    $pay_data         = get_post_meta($post_id, 'posts_zibpay', true);
    if (!$pay_data || !is_array($pay_data)) {
        $pay_data = array();
    }
    foreach ($zibpay_bulk_edit as $field_id => $field_value) {
        if ($field_value === 'ignore') {
            continue;
        }

        switch ($field_id) {
            //开关或选择
            case 'order_expired_unit':
            case 'pay_type':
            case 'pay_limit':
            case 'pay_modo':
            case 'coupon_s':
            case 'order_expired_s':
                $pay_data[$field_id] = $field_value;

                break;

            //数值类型
            case 'order_expired_time':
            case 'points_price':
            case 'vip_1_points':
            case 'vip_2_points':
            case 'pay_original_price':
            case 'pay_price':
            case 'vip_1_price':
            case 'vip_2_price':
            case 'download_limit_over_price':
            case 'pay_rebate_discount':
            case 'pay_cuont':

                $operation = $field_value['operation'];
                $pay_val   = isset($pay_data[$field_id]) ? (float) $pay_data[$field_id] : 0; // 0 is default value
                if($field_id === 'order_expired_time') {
                    $pay_val = isset($pay_data['order_expired_opt']['time'] ) ? (int) $pay_data['order_expired_opt']['time'] : 0;
                }

                if ($operation !== 'ignore' && is_numeric($field_value['val'])) {
                    $val = (float) $field_value['val'];
                    switch ($operation) {
                        case 'set': //统一设置为
                            $pay_val = $val;
                            break;
                        case 'plus':
                            $pay_val = round($pay_val + $val, 2);
                            break;
                        case 'subtract':
                            $pay_val = round($pay_val - $val, 2);
                            break;
                        case 'multiply':
                            $pay_val = round($pay_val * $val, 2);
                            break;
                        case 'division':
                            if ($pay_val != 0 && $val != 0) {
                                $pay_val = round($pay_val / $val, 2);
                            }
                            break;
                    }

                    if (in_array($field_id, array('points_price', 'vip_1_points', 'vip_2_points', 'pay_cuont', 'order_expired_time'))) {
                        $pay_val = (int) $pay_val;
                    }
                    $pay_data[$field_id] = $pay_val < 0 ? 0 : $pay_val;
                }
                break;
        }

    }

    if (isset($pay_data['order_expired_time'])) {
        $pay_data['order_expired_opt']         = $pay_data['order_expired_opt'] ?? [];
        $pay_data['order_expired_opt']['time'] = $pay_data['order_expired_time'];
        unset($pay_data['order_expired_time']);
    }

    if (isset($pay_data['order_expired_unit'])) {
        $pay_data['order_expired_opt']         = $pay_data['order_expired_opt'] ?? [];
        $pay_data['order_expired_opt']['unit'] = $pay_data['order_expired_unit'];
        unset($pay_data['order_expired_unit']);
    }

    update_post_meta($post_id, 'posts_zibpay', $pay_data);
}

//后台仪表盘增加付费文章统计
add_action('wp_dashboard_setup', 'zibpay_add_dashboard_widgets');
function zibpay_add_dashboard_widgets()
{
    if (is_super_admin()) {
        wp_add_dashboard_widget(
            'zibpay_dashboard_widget',
            __('商城统计', 'zib_language'),
            'zibpay_dashboard_widget_function'
        );
    }
}

//后台仪表盘小部件
function zibpay_dashboard_widget_function()
{
    wp_enqueue_style('zibpay_page', get_template_directory_uri() . '/zibpay/assets/css/pay-page.css', array(), THEME_VERSION);
    wp_enqueue_script('highcharts', get_template_directory_uri() . '/zibpay/assets/js/highcharts.js', array('jquery'), THEME_VERSION);
    wp_enqueue_script('westeros', get_template_directory_uri() . '/zibpay/assets/js/westeros.min.js', array('jquery', 'highcharts'), THEME_VERSION);

    echo '<div class="pay-dashboard-widget">';
    zib_require('zibpay/page/index');
    echo '</div>';
}

//后台仪表盘增加付费文章统计
function zibpay_get_admin_dashboard_data()
{
    global $wpdb;
    $today     = zibpay_get_order_statistics_totime('today');
    $yester    = zibpay_get_order_statistics_totime('yester');
    $thismonth = zibpay_get_order_statistics_totime('thismonth');
    $lastmonth = zibpay_get_order_statistics_totime('lastmonth');
    $all       = zibpay_get_order_statistics_totime('all');
    $thisyear  = zibpay_get_order_statistics_totime('thisyear');
    $rebate    = '';

    $_all                 = (array) $wpdb->get_row("SELECT SUM(rebate_price) as rebate,SUM(income_price) as income  FROM $wpdb->zibpay_order WHERE  `status` = 1");
    $thismonth_time_where = zib_get_time_where_sql('thismonth', 'pay_time');
    $_thismonth           = (array) $wpdb->get_row("SELECT SUM(rebate_price) as rebate,SUM(income_price) as income FROM $wpdb->zibpay_order WHERE  `status` = 1 and $thismonth_time_where");

    $rebate = array(
        'all'       => isset($_all['rebate']) ? floatval($_all['rebate']) : 0,
        'thismonth' => isset($_thismonth['rebate']) ? floatval($_thismonth['rebate']) : 0,
    );
    $income = array(
        'all'       => isset($_all['income']) ? floatval($_all['income']) : 0,
        'thismonth' => isset($_thismonth['income']) ? floatval($_thismonth['income']) : 0,
    );

    $_rebate_1 = $wpdb->get_var("SELECT SUM(rebate_price) FROM $wpdb->zibpay_order WHERE  `status` = 1 and `rebate_status` = 1");
    $_income_1 = $wpdb->get_var("SELECT SUM(income_price) FROM $wpdb->zibpay_order WHERE  `status` = 1 and `income_status` = 1");

    //有效
    $rebate['effective'] = ($rebate['all'] - $_rebate_1);
    $income['effective'] = ($income['all'] - $_income_1);

    $data = array(
        array(
            'top'    => __('今日订单', 'zib_language'),
            'val'    => $today['count'],
            'bottom' => sprintf(__('昨日订单：%s', 'zib_language'), $yester['count']),
        ),
        array(
            'top'    => __('今日收款', 'zib_language'),
            'val'    => ($today['sum'] > 1000) ? (int) $today['sum'] : $today['sum'],
            'bottom' => sprintf(__('昨日收款：%s', 'zib_language'), $yester['sum']),
        ),
        array(
            'top'    => __('本月订单', 'zib_language'),
            'val'    => $thismonth['count'],
            'bottom' => sprintf(__('上月订单：%s', 'zib_language'), $lastmonth['count']),
        ),
        array(
            'top'    => __('本月收款', 'zib_language'),
            'val'    => ($thismonth['sum'] > 10000) ? (int) $thismonth['sum'] : $thismonth['sum'],
            'bottom' => sprintf(__('上月收款：%s', 'zib_language'), $lastmonth['sum']),
        ),
        array(
            'top'    => __('有效单量', 'zib_language'),
            'val'    => $all['count'],
            'bottom' => sprintf(__('今年订单：%s', 'zib_language'), $thisyear['count']),
        ),
        array(
            'top'    => __('有效收款', 'zib_language'),
            'val'    => ($all['sum'] > 10000) ? (int) $all['sum'] : $all['sum'],
            'bottom' => sprintf(__('今年收款：%s', 'zib_language'), $thisyear['sum']),
        ),
        array(
            'top'    => __('总分成', 'zib_language'),
            'val'    => ($income['all'] > 10000) ? (int) $income['all'] : $income['all'],
            'bottom' => sprintf(__('未提现:%1$s · 本月:%2$s', 'zib_language'), $income['effective'], $income['thismonth']),
        ),
        array(
            'top'    => __('总佣金', 'zib_language'),
            'val'    => ($rebate['all'] > 10000) ? (int) $rebate['all'] : $rebate['all'],
            'bottom' => sprintf(__('未提现:%1$s · 本月:%2$s', 'zib_language'), $rebate['effective'], $rebate['thismonth']),
        ),
    );
    return $data;
}
