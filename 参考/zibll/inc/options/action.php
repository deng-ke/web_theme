<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2026-06-17 17:07:06
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//后台设置微信公众号的自定义菜单
function zib_weixin_gzh_create_menu()
{

    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $json = $_REQUEST['json'];
    if (!$json) {
        zib_send_json_error(__('输入自定义菜单的json配置代码', 'zib_language'));
    }

    $data = json_decode(wp_unslash(trim($json)), true);

    if (!$data || !is_array($data)) {
        zib_send_json_error(__('json格式错误', 'zib_language'));
    }

    $wxConfig = get_oauth_config('weixingzh');
    require_once get_theme_file_path('/oauth/sdk/weixingzh.php');
    if (!$wxConfig['appid'] || !$wxConfig['appkey']) {
        zib_send_json_error(__('微信公众号配置错误，请检查AppID或AppSecret', 'zib_language'));
    }

    try {
        $wxOAuth    = new \Weixin\GZH\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
        $CreateMenu = $wxOAuth->createMenu($data);

        if (isset($CreateMenu['errcode'])) {
            if (0 == $CreateMenu['errcode']) {
                zib_send_json_success(__('设置成功，5-10分钟后生效，请耐心等待', 'zib_language'));
            } else {
                zib_send_json_error(sprintf(__('设置失败，请对照一下错误检查', 'zib_language') . '<br>' . __('错误码：%1$s', 'zib_language') . '<br>' . __('错误消息：%2$s', 'zib_language'), $CreateMenu['errcode'], $CreateMenu['errmsg']));
            }
        }

    } catch (\Exception $e) {
        zib_send_json_error($e->getMessage());
    }

}
add_action('wp_ajax_weixin_gzh_menu', 'zib_weixin_gzh_create_menu');

//后台设置微信公众号：获取自定义菜单信息
function zib_admin_ajax_weixin_gzh_get_menu()
{

    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $wxConfig = get_oauth_config('weixingzh');
    require_once get_theme_file_path('/oauth/sdk/weixingzh.php');
    if (!$wxConfig['appid'] || !$wxConfig['appkey']) {
        zib_send_json_error(__('微信公众号配置错误，请检查AppID或AppSecret', 'zib_language'));
    }

    try {
        $wxOAuth = new \Weixin\GZH\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
        $menu    = $wxOAuth->getSelfMenu();

        if (isset($menu['selfmenu_info'])) {
            zib_send_json_success('<b>' . __('获取成功，菜单状态：', 'zib_language') . (!empty($menu['is_menu_open']) ? __('开启', 'zib_language') : __('关闭', 'zib_language')) . __('，菜单json数据：', 'zib_language') . '</b><pre style=" background: rgb(138 138 138 / 8%); overflow: scroll; padding: 10px; border-radius: 8px; border: 1px solid rgb(0 0 0 / 7%); ">' . wp_unslash(trim(json_encode($menu['selfmenu_info'], JSON_PRETTY_PRINT))) . '</pre>');
        } else {
            zib_send_json_error(__('获取失败：', 'zib_language') . wp_unslash(trim(json_encode($menu, JSON_UNESCAPED_UNICODE))));
        }

    } catch (\Exception $e) {
        zib_send_json_error($e->getMessage());
    }

}
add_action('wp_ajax_weixin_gzh_get_menu', 'zib_admin_ajax_weixin_gzh_get_menu');

//后台设置微信公众号：获取自定义菜单信息
function zib_admin_ajax_weixin_freepublish_batchget()
{

    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $wxConfig = get_oauth_config('weixingzh');
    require_once get_theme_file_path('/oauth/sdk/weixingzh.php');
    if (!$wxConfig['appid'] || !$wxConfig['appkey']) {
        zib_send_json_error(__('微信公众号配置错误，请检查AppID或AppSecret', 'zib_language'));
    }

    $paged      = !empty($_REQUEST['paged']) ? (int) $_REQUEST['paged'] : 1;
    $paged      = max($paged, 1);
    $paged_size = 20;

    try {
        $wxOAuth = new \Weixin\GZH\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
        $menu    = $wxOAuth->freepublishBatchget($paged, $paged_size);

        if (isset($menu['item'])) {

            $lists = '';
            foreach ($menu['item'] as $item) {
                $lists .= '<div class="badg" style=" text-align: left;display: block; ">
                    <div><b>' . __('标题：', 'zib_language') . '</b>' . ($item['content']['news_item'][0]['title'] ?? '') . '</div>
                    <div><b>article_id：</b>' . ($item['article_id'] ?? '') . '</div>
                    <div><b>' . __('时间：', 'zib_language') . '</b>' . (date('Y-m-d H:i:s', $item['update_time'])) . '</div>
                    </div>';
            }

            $total_count = $menu['total_count'];
            if ($total_count > $paged * $paged_size) {
                $lists .= '<div><a href="javascript:;" ajax-url="' . zib_get_admin_ajax_url('weixin_gzh_freepublish_batchget', ['paged' => $paged + 1]) . '" class="but mr6 ajax-get">' . __('下一页', 'zib_language') . '</a></div>';
            }

            zib_send_json_success('<b>' . sprintf(__('获取成功，总数：%s', 'zib_language'), $total_count) . '</b><div>' . $lists . '</div>');
        } else {
            zib_send_json_error(__('获取失败：', 'zib_language') . wp_unslash(trim(json_encode($menu, JSON_UNESCAPED_UNICODE))));
        }

    } catch (\Exception $e) {
        zib_send_json_error($e->getMessage());
    }

}
add_action('wp_ajax_weixin_gzh_freepublish_batchget', 'zib_admin_ajax_weixin_freepublish_batchget');

//后台配置ajax提交内容审核测试
function zib_audit_test()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $action     = $_REQUEST['action'];
    $option_key = 'audit_baidu_access_token';

    //刷新数据库保存的access_token
    update_option($option_key, false);

    switch ($action) {
        case 'text_audit_test':
            if (empty($_POST['content'])) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请输入需要测试的内容', 'zib_language'))));
                exit();
            }
            $rel = ZibAudit::text($_POST['content']);
            break;
        case 'img_audit_test':

            $test_img = get_theme_file_path('/inc/csf-framework/assets/images/audit_test.jpg');

            $rel = ZibAudit::image($test_img);
            break;
    }

    if (!empty($rel['error'])) {
        echo(json_encode(array('error' => $rel['error'], 'ys' => 'danger', 'msg' => $rel['msg'])));
        exit();
    }

    if (!empty($rel['conclusion'])) {
        $msg = sprintf(__('审核结果：%1$s', 'zib_language'), $rel['conclusion']) . '<br/>' . sprintf(__('结果代码：%1$s', 'zib_language'), $rel['conclusion_type']) . '<br/>' . sprintf(__('消息：%1$s', 'zib_language'), $rel['msg']);
        echo(json_encode(array('error' => 0, 'msg' => $msg, 'data' => $rel['data'])));
        exit();
    }

    echo(json_encode(array('error' => 0, 'msg' => $rel)));
    exit();
}
add_action('wp_ajax_text_audit_test', 'zib_audit_test');
add_action('wp_ajax_img_audit_test', 'zib_audit_test');

/**
 * @description: 后台AJAX发送测试邮件
 * @param {*}
 * @return {*}
 */
function zib_test_send_mail()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    if (empty($_POST['email'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请输入邮箱账号', 'zib_language'))));
        exit();
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo(json_encode(array('error' => 1, 'msg' => __('邮箱格式错误', 'zib_language'))));
        exit();
    }
    $blog_name = get_bloginfo('name');
    $blog_url  = get_bloginfo('url');
    $title     = __('邮件发送测试', 'zib_language');

    $message = __('您好！', 'zib_language') . ' <br />';
    $message .= sprintf(__('这是一封来自%s[%s]的测试邮件', 'zib_language'), $blog_name, $blog_url) . '<br />';
    $message .= __('该邮件由网站后台发出，如果非您本人操作，请忽略此邮件', 'zib_language') . ' <br />';
    $message .= current_time('Y-m-d H:i:s');

    // wp_mail 内部会捕获 PHPMailer 异常并触发 wp_mail_failed，再返回 false
    $mail_error     = '';
    $on_mail_failed = function ($wp_error) use (&$mail_error) {
        if (is_wp_error($wp_error)) {
            $mail_error = $wp_error->get_error_message();
        }
    };
    add_action('wp_mail_failed', $on_mail_failed);

    try {
        $test = wp_mail($_POST['email'], $title, $message);
    } catch (\Exception $e) {
        $mail_error = $e->getMessage();
        $test       = false;
    }

    remove_action('wp_mail_failed', $on_mail_failed);

    if ($test) {
        echo(json_encode(array('error' => 0, 'msg' => __('后台已操作', 'zib_language'))));
    } else {
        $msg = __('发送失败', 'zib_language');
        if ($mail_error) {
            $msg .= '<br>' . sprintf(__('失败原因：%s', 'zib_language'), esc_html($mail_error));
        }
        echo(json_encode(array('error' => 1, 'msg' => $msg)));
    }
    exit();
}
add_action('wp_ajax_test_send_mail', 'zib_test_send_mail');

function zib_test_send_sms()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    if (empty($_POST['phone_number'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请输入手机号码', 'zib_language'))));
        exit();
    }

    echo json_encode(ZibSMS::send($_POST['phone_number'], '888888'));
    exit();
}
add_action('wp_ajax_test_send_sms', 'zib_test_send_sms');

//重置用户徽章数据
function zib_ajax_reset_user_medal()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    global $wpdb;

    $sql   = "UPDATE `$wpdb->usermeta` SET `meta_value` = replace(meta_value, '\"medal_details\"', '\"medal_detnull\"') WHERE `meta_key` = 'zib_other_data'";
    $query = $wpdb->query($sql);

    //刷新所有缓存
    wp_cache_flush();

    echo(json_encode(array('error' => 0, 'query' => $query, 'last_query' => $wpdb->last_query, 'msg' => __('已重置全部用户的徽章数据', 'zib_language'))));
    exit();
}
add_action('wp_ajax_reset_user_medal', 'zib_ajax_reset_user_medal');

//导入主题设置
function zib_ajax_options_import()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $data = !empty($_REQUEST['import_data']) ? $_REQUEST['import_data'] : '';

    if (!$data) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请粘贴需导入配置的json代码', 'zib_language'))));
        exit();
    }

    $import_data = json_decode(wp_unslash(trim($data)), true);

    if (empty($import_data) || !is_array($import_data)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('json代码格式错误，无法导入', 'zib_language'))));
        exit();
    }

    zib_options_backup('导入配置 自动备份');

    $prefix = 'zibll_options';
    update_option($prefix, $import_data);
    echo(json_encode(array('error' => 0, 'reload' => 1, 'msg' => __('主题设置已导入，请刷新页面', 'zib_language'))));
    exit();
}
add_action('wp_ajax_options_import', 'zib_ajax_options_import');

//备份主题设置
function zib_ajax_options_backup()
{

    $type   = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '手动备份';
    $backup = zib_options_backup($type);
    echo(json_encode(array('error' => 0, 'reload' => 1, 'msg' => __('当前配置已经备份', 'zib_language'))));
    exit();
}
add_action('wp_ajax_options_backup', 'zib_ajax_options_backup');

function zib_ajax_options_backup_delete()
{

    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }
    if (empty($_REQUEST['key'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('参数传入错误', 'zib_language'))));
        exit();
    }

    $prefix = 'zibll_options';
    if ('options_backup_delete_all' == $_REQUEST['action']) {
        update_option($prefix . '_backup', false);
        echo(json_encode(array('error' => 0, 'reload' => 1, 'msg' => __('已删除全部备份数据', 'zib_language'))));
        exit();
    }

    $options_backup = get_option($prefix . '_backup');

    if ('options_backup_delete_surplus' == $_REQUEST['action']) {
        if ($options_backup) {
            $options_backup = array_reverse($options_backup);
            update_option($prefix . '_backup', array_reverse(array_slice($options_backup, 0, 3)));
            echo(json_encode(array('error' => 0, 'reload' => 1, 'msg' => __('已删除多余备份数据，仅保留最新3份', 'zib_language'))));
            exit();
        }
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('暂无可删除的数据', 'zib_language'))));
    }

    if (isset($options_backup[$_REQUEST['key']])) {
        unset($options_backup[$_REQUEST['key']]);

        update_option($prefix . '_backup', $options_backup);
        echo(json_encode(array('error' => 0, 'reload' => 1, 'msg' => __('所选备份已删除', 'zib_language'))));
    } else {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('此备份已删除', 'zib_language'))));
    }
    exit();
}
add_action('wp_ajax_options_backup_delete', 'zib_ajax_options_backup_delete');
add_action('wp_ajax_options_backup_delete_all', 'zib_ajax_options_backup_delete');
add_action('wp_ajax_options_backup_delete_surplus', 'zib_ajax_options_backup_delete');

function zib_ajax_options_backup_restore()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }
    if (empty($_REQUEST['key'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('参数传入错误', 'zib_language'))));
        exit();
    }

    $prefix         = 'zibll_options';
    $options_backup = get_option($prefix . '_backup');
    if (isset($options_backup[$_REQUEST['key']]['data'])) {
        update_option($prefix, $options_backup[$_REQUEST['key']]['data']);
        echo(json_encode(array('error' => 0, 'reload' => 1, 'msg' => sprintf(__('主题设置已恢复到所选备份[%s]', 'zib_language'), $_REQUEST['key']))));
    } else {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('备份恢复失败，未找到对应数据', 'zib_language'))));
    }
    exit();
}
add_action('wp_ajax_options_backup_restore', 'zib_ajax_options_backup_restore');

function zib_ajax_test_wechat_template_test()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }
    if (empty($_REQUEST['type'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请选择需要测试的模板', 'zib_language'))));
        exit();
    }

    if (!_pz('wechat_template_msg_s')) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('微信公众号模板消息暂未开启，请开启并保存主题设置后，再次进行测试', 'zib_language'))));
        exit();
    }

    $_pz = _pz('wechat_template_ids');
    if (empty($_pz[$_REQUEST['type'] . '_s']) || empty($_pz[$_REQUEST['type']])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('未开启该类型消息或未设置该类型消息的模板ID', 'zib_language'))));
        exit();
    }

    $wxConfig = get_oauth_config('weixingzh');
    if (!$wxConfig['appid'] || !$wxConfig['appkey']) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('依赖的微信公众号登录功能未开启，或微信公众号的appid或appkey参数错误', 'zib_language'))));
        exit();
    }

    $current_time = current_time('Y-m-d H:i:s');

    switch ($_REQUEST['type']) {
        case 'shop_auto_delivery_fail':
            $data = array(
                'status' => __('自动发货失败，请联系客服', 'zib_language'),
                'name'   => __('测试商品', 'zib_language'),
                'num'    => '1234567890',
                'time'   => $current_time,
            );
            break;

        case 'shop_notify_shipping_to_author':
            $data = array(
                'name' => __('测试商品', 'zib_language'),
                'num'  => '1234567890',
                'time' => $current_time,
                'desc' => __('测试发货说明', 'zib_language'),
            );
            break;

        case 'shop_express_shipping':
            $data = array(
                'name'    => __('测试商品', 'zib_language'),
                'num'     => '1234567890',
                'time'    => $current_time,
                'express' => __('测试快递公司', 'zib_language'),
                'number'  => '1234567890',
            );
            break;

        case 'shop_after_sale_to_author':
            $data = array(
                'name' => __('测试商品', 'zib_language'),
                'num'  => '1234567890',
                'time' => $current_time,
                'type' => __('测试售后类型', 'zib_language'),
                'user' => __('测试用户', 'zib_language'),
            );
            break;
        case 'shop_after_sale_wait_user_return':
            $data = array(
                'name' => __('测试商品', 'zib_language'),
                'num'  => '1234567890',
                'time' => $current_time,
                'type' => __('测试售后类型', 'zib_language'),
                'desc' => __('测试处理说明', 'zib_language'),
            );
            break;
        case 'shop_after_sale_end':
            $data = array(
                'name'   => __('测试商品', 'zib_language'),
                'num'    => '1234567890',
                'time'   => $current_time,
                'type'   => __('测试售后类型', 'zib_language'),
                'end'    => $current_time,
                'status' => __('测试处理结果说明', 'zib_language'),
            );
            break;

        case 'payment_order':
            $data = array(
                'name'  => __('付费阅读-测试商品', 'zib_language'),
                'price' => zibpay_format_local_price_text(88.5),
                'time'  => $current_time,
                'num'   => '2022010154541288888888',
            );
            break;
        case 'payment_order_admin':
            $data = array(
                'name'  => __('付费阅读-测试商品', 'zib_language'),
                'price' => '88.5',
                'user'  => __('张三', 'zib_language'),
                'time'  => $current_time,
                'num'   => '2022010154541288888888',
            );
            break;

        case 'payment_order_to_income':
            $data = array(
                'name'  => __('您发布的付费内容[XXX]有订单已支付，并获得一笔创作分成', 'zib_language'),
                'price' => zibpay_format_local_price_text(100),
                'time'  => $current_time,
                'num'   => '2022010154541288888888',
            );
            break;

        case 'payment_order_to_referrer':
            $data = array(
                'name'  => __('恭喜您！获得一笔推广佣金！', 'zib_language'),
                'price' => zibpay_format_local_price_text(100),
                'time'  => $current_time,
                'num'   => '2022010154541288888888',
            );
            break;

        case 'apply_withdraw_admin':
            $data = array(
                'user'  => __('张三', 'zib_language'),
                'price' => zibpay_format_local_price_text(800),
                'time'  => $current_time,
            );
            break;

        //            'withdraw_process'                 => ['提现处理后通知提现用户', 'create_time=申请时间&process_time=处理时间&status=处理结果&price=提现金额'],

        case 'withdraw_process':
            $data = array(
                'create_time'    => $current_time,
                'process_time'   => $current_time,
                'status'         => __('已处理完成', 'zib_language'),
                'price'          => '888',
                'received_price' => '880',
                'service_price'  => '8',
            );
            break;

        case 'auth_apply_admin':
            //            'auth_apply_admin'                 => ['用户提交身份认证通知管理员', 'name=认证名称&time=认证时间&user=申请用户'],
            $data = array(
                'user' => __('张三', 'zib_language'),
                'name' => __('xx企业', 'zib_language'),
                'time' => $current_time,
            );
            break;

        case 'auth_apply_process':
            //['身份认证处理后通知用户', 'status=审核结果&name=认证名称&desc=认证简介&time=处理时间']
            $data = array(
                'status' => __('已通过', 'zib_language'),
                'name'   => __('xx企业', 'zib_language'),
                'desc'   => __('认证简介', 'zib_language'),
                'time'   => $current_time,
            );
            break;

        case 'report_user_admin':
            //            'report_user_admin'                => ['收到举报后通知管理员', 'user=被举报用户&time=举报时间&reason=举报原因&desc=举报详情'],
            $data = array(
                'user'   => __('张三', 'zib_language'),
                'time'   => $current_time,
                'reason' => __('发送恶意广告', 'zib_language'),
                'desc'   => __('举报详情', 'zib_language'),
            );

            break;

        case 'report_process':
            //            'report_process'                   => ['处理用户举报后通知举报人', 'reason=举报原因&time=举报时间&desc=处理结果'],
            $data = array(
                'reason' => __('发送恶意广告', 'zib_language'),
                'time'   => $current_time,
                'desc'   => __('已封禁该用户账号', 'zib_language'),
            );

            break;

        case 'bind_phone':
            $data = array(
                'name' => __('张三', 'zib_language'),
                'time' => $current_time,
                'num'  => '1380****000',
            );
            break;

        case 'bind_email':
            $data = array(
                'name' => __('张三', 'zib_language'),
                'time' => $current_time,
                'num'  => '1234****@qq.com',
            );
            break;

        case 'comment_to_postauthor':
            $data = array(
                'name'    => __('张三', 'zib_language'),
                'time'    => $current_time,
                'content' => __('您好，世界！', 'zib_language'),
                'post'    => __('这是一篇测试帖子', 'zib_language'),
            );
            break;

        case 'comment_to_parent':
            $data = array(
                'name'    => __('张三', 'zib_language'),
                'content' => __('您好，世界！', 'zib_language'),
                'time'    => $current_time,
            );
            break;
    }

    $send = zib_wechat_template_send(get_current_user_id(), $_REQUEST['type'], $data, home_url());
    echo(json_encode($send));
    exit();
}
add_action('wp_ajax_test_wechat_template_test', 'zib_ajax_test_wechat_template_test');

function zib_test_ip_addr_sdk()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $sdk = !empty($_REQUEST['sdk']) ? $_REQUEST['sdk'] : '';
    $ip  = !empty($_REQUEST['ip']) ? $_REQUEST['ip'] : zib_get_remote_ip_addr();

    if (!$ip || strstr($ip, '0.0.0.') || strstr($ip, '192.168.') || strstr($ip, '127.0.')) {
        echo(json_encode(array('error' => 1, 'msg' => sprintf(__('您当前的IP[%s]无法查询地理位置', 'zib_language'), $ip))));
        exit;
    }

    switch ($sdk) {
        case 'qq':
            $ip_addr_sdk_qq = _pz('ip_addr_sdk_qq');
            if (empty($ip_addr_sdk_qq['appkey'])) {
                echo(json_encode(array('error' => 1, 'msg' => __('腾讯接口配置错误：请先配置好接口并保存主题设置', 'zib_language'))));
                exit;
            }

            $test = zib_get_geographical_position_by_qq($ip, $ip_addr_sdk_qq['appkey'], $ip_addr_sdk_qq['secretkey'], true);

            if (!$test) {
                echo(json_encode(array('error' => 1, 'msg' => __('网络数据获取失败，请确保服务器网络畅通', 'zib_language'))));
                exit;
            }

            if (!isset($test['nation'])) {
                $test['ip'] = $ip;
                echo(json_encode(array('error' => 1, 'msg' => __('腾讯接口请求失败，错误信息：', 'zib_language') . '<br>' . json_encode($test, JSON_UNESCAPED_UNICODE))));
                exit;
            }

            echo(json_encode(array('error' => 0, 'msg' => __('腾讯接口请求成功', 'zib_language') . '<br>' . json_encode($test, JSON_UNESCAPED_UNICODE))));
            exit;
            break;

        case 'amap':
            $ip_addr_sdk = _pz('ip_addr_sdk_amap');
            if (empty($ip_addr_sdk['appkey'])) {
                echo(json_encode(array('error' => 1, 'msg' => __('高德接口配置错误：请先配置好接口并保存主题设置', 'zib_language'))));
                exit;
            }

            $test = zib_get_geographical_position_by_amap($ip, $ip_addr_sdk['appkey'], $ip_addr_sdk['secretkey'], true);

            if (!$test) {
                echo(json_encode(array('error' => 1, 'msg' => __('网络数据获取失败，请确保服务器网络畅通', 'zib_language'))));
                exit;
            }

            if (!isset($test['nation'])) {
                $test['ip'] = $ip;
                echo(json_encode(array('error' => 1, 'msg' => __('高德接口请求失败，错误信息：', 'zib_language') . '<br>' . json_encode($test, JSON_UNESCAPED_UNICODE))));
                exit;
            }

            echo(json_encode(array('error' => 0, 'msg' => __('高德接口请求成功', 'zib_language') . '<br>' . json_encode($test, JSON_UNESCAPED_UNICODE))));
            exit;
            break;

        default:

            $test = zib_get_geographical_position_by_pconline($ip, true);

            if (!$test) {
                echo(json_encode(array('error' => 1, 'msg' => __('网络数据获取失败，请确保服务器网络畅通', 'zib_language'))));
                exit;
            }

            if (!isset($test['nation'])) {
                $test['ip'] = $ip;
                echo(json_encode(array('error' => 1, 'msg' => __('太平洋公共接口请求失败，错误信息：', 'zib_language') . '<br>' . json_encode($test, JSON_UNESCAPED_UNICODE))));
                exit;
            }

            echo(json_encode(array('error' => 0, 'msg' => __('太平洋公共接口请求成功', 'zib_language') . '<br>' . json_encode($test, JSON_UNESCAPED_UNICODE))));
            exit;
    }
    exit();
}
add_action('wp_ajax_test_ip_addr_sdk', 'zib_test_ip_addr_sdk');

//测试快递查询
function zib_test_express_query()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $express_number = !empty($_REQUEST['express_number']) ? $_REQUEST['express_number'] : '';
    $company        = !empty($_REQUEST['company']) ? $_REQUEST['company'] : '';
    $phone          = !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : '';

    if (empty($express_number)) {
        echo(json_encode(array('error' => 1, 'msg' => __('请输入快递单号', 'zib_language'))));
        exit;
    }

    $result = ZibExpress::query($express_number, $phone, $company);

    echo(json_encode(array('error' => $result['error'], 'msg' => ($result['msg'] ?? '') . '<br>' . json_encode($result, JSON_UNESCAPED_UNICODE))));
    exit;
}
add_action('wp_ajax_test_express_query', 'zib_test_express_query');

function zib_ajax_search_ms_rebuild()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $search_ms_opt = zib_get_search_ms_opt();
    if (empty($search_ms_opt['api_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请填写Meilisearch智能搜索的API地址并保存主题配置', 'zib_language'))));
        exit();
    }

    $search = new zib_ms($search_ms_opt);
    $paged  = ($_POST['data']['paged'] ?? 0) + 1;

    if ($paged === 1) {
        try {
            //清楚所有数据
            $search->deleteAll();
            $search->updatePrimaryKey();
        } catch (\Exception $e) {
            echo json_encode(array('error' => 1, 'msg' => sprintf(__('处理失败，错误信息：%s', 'zib_language'), $e->getMessage())));
            exit;
        }
    }

    //导入数据
    $result = $search->import($paged);
    if ($result['error']) {
        echo json_encode(array('error' => 1, 'msg' => $result['msg']));
        exit;
    }

    $send_data = [];
    if ($result['remaining'] > 0) {
        $send_data['msg']  = sprintf(__('索引重建: 导入%1$s条posts数据，剩余%2$s条数据待处理', 'zib_language'), $result['count'], $result['remaining']);
        $send_data['data'] = [
            'paged' => $paged,
        ];
        $send_data['result'] = $result;
        $send_data['status'] = 'continue';
    } else {
        $send_data['result'] = $result;
        $send_data['msg']    = sprintf(__('索引重建任务导入完成，共计导入%1$s条posts数据，请等待几秒后再查询状态', 'zib_language'), $result['total']);
        $send_data['status'] = 'over';
    }

    echo json_encode($send_data);
    exit;
}
add_action('wp_ajax_search_ms_rebuild', 'zib_ajax_search_ms_rebuild');

//查看搭建好Meilisearch的状态
function zib_ajax_search_ms_stats()
{

    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $search_ms_opt = zib_get_search_ms_opt();
    if (empty($search_ms_opt['api_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请填写Meilisearch智能搜索的API地址并保存主题配置', 'zib_language'))));
        exit();
    }

    $search = new zib_ms($search_ms_opt);
    $data   = $search->stats();

    if (!empty($data['error'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $data['msg'])));
        exit();
    }

    $timestamp  = strtotime(preg_replace('/\.\d+Z$/', 'Z', $data['lastUpdate']));
    $lastUpdate = wp_date('Y-m-d H:i:s', $timestamp);

    $html = '<div class="flex ac"><b style=" width: 100px; ">' . __('URL地址', 'zib_language') . '</b><span class="">' . $search_ms_opt['api_url'] . '<span class="badg badg-sm c-blue ml6">' . __('连接正常', 'zib_language') . '</span></span></div>';
    $html .= '<div class="flex ac"><b style=" width: 100px; ">' . __('服务端版本', 'zib_language') . '</b><span class="">V' . $data['version'] . '</span></div>';
    $html .= '<div class="flex ac"><b style=" width: 100px; ">' . __('数据总量', 'zib_language') . '</b><span class="">' . size_format($data['databaseSize'], 2) . '</span></div>';
    $html .= '<div class="flex ac"><b style=" width: 100px; ">' . __('最后更新时间', 'zib_language') . '</b><span class="">' . $lastUpdate . '</span></div>';

    $html .= '<div class="mt10 flex"><b style=" width: 100px; ">' . __('索引统计', 'zib_language') . '</b><div>';
    foreach ($data['indexes'] as $k => $v) {
        $html .= '<div class="flex ac"><span class="badg badg-sm mr6">' . $k . '</span><span class="">' . sprintf(__('%s个文档', 'zib_language'), $v['numberOfDocuments']) . '</span></div>';
    }
    $html .= '</div></div>';

    echo json_encode(['msg' => $html]);
    exit();
}
add_action('wp_ajax_search_ms_stats', 'zib_ajax_search_ms_stats');

//同步同义词
function zib_ajax_search_ms_sync_synonyms()
{
    if (!is_super_admin()) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作权限不足', 'zib_language'))));
        exit();
    }

    $search_ms_opt = zib_get_search_ms_opt();
    if (empty($search_ms_opt['api_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请填写Meilisearch智能搜索的API地址并保存主题配置', 'zib_language'))));
        exit();
    }

    $search   = new zib_ms($search_ms_opt);
    $synonyms = [];
    if (isset($search_ms_opt['synonyms']) && is_array($search_ms_opt['synonyms'])) {
        foreach ($search_ms_opt['synonyms'] as $_sy) {
            $v = preg_split('/,|，/', $_sy['val']);
            if (!empty($_sy['key']) && !empty($v)) {
                $synonyms[$_sy['key']] = $v;
            }
        }
    }

    try {
        $search->postIndex->updateSynonyms($synonyms);
    } catch (\Exception $e) {
        echo json_encode(array('error' => 1, 'msg' => sprintf(__('处理失败，错误信息：%s', 'zib_language'), $e->getMessage())));
        exit;
    }

    echo json_encode(array('error' => 0, 'msg' => __('同义词已同步：', 'zib_language') . json_encode($synonyms, JSON_UNESCAPED_UNICODE)));
    exit;
}
add_action('wp_ajax_search_ms_sync_synonyms', 'zib_ajax_search_ms_sync_synonyms');
