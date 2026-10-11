<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:37
 * @LastEditTime : 2026-08-26 22:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

zib_require(array(
    'main',
    'ajax',
    'user',
    'author',
    'sign_register',
    'comment',
    'documentnav',
    'media',
    'new_posts',
), false, 'action/');

/**设置验证码 */
function zib_get_captcha($counts = 6)
{
    $originalcode = '0,1,2,3,4,5,6,7,8,9';
    $originalcode = explode(',', $originalcode);
    $countdistrub = 10;
    $_dscode      = '';
    for ($j = 0; $j < $counts; $j++) {
        $dscode = $originalcode[rand(0, $countdistrub - 1)];
        $_dscode .= $dscode;
    }
    return strtolower($_dscode);
}

//重置验证码限制时间,减去多少秒
function zib_captcha_time($second = 40)
{
    @session_start();
    if (!empty($_SESSION['zib_captcha_time'])) {
        $_SESSION['zib_captcha_time'] = date('Y-m-d H:i:s', strtotime('-' . $second . ' second', strtotime($_SESSION['zib_captcha_time'])));
    }
}

/**
 * 获取验证码发送防刷配置
 *
 * @return array
 */
function zib_get_captcha_send_limit_option()
{
    $option = _pz('brush_limit_captcha_send');
    if (!is_array($option)) {
        $option = array();
    }

    return wp_parse_args($option, array(
        'cooldown'      => 60,
        'to_hour_1'     => 5,
        'to_day_1'      => 10,
        'ip_minutes_10' => 5,
        'ip_day_1'      => 20,
    ));
}

/**
 * 验证码发送是否应跳过 IP 维度限制
 *
 * @param string $ip IP 地址
 * @return bool
 */
function zib_captcha_send_skip_ip_limit($ip)
{
    return !$ip || preg_match('/^(?:127|172\.16|192\.168)\./', $ip);
}

/**
 * 验证码发送防刷：检查是否允许发送
 *
 * @param string $to 收件人邮箱或手机号
 * @param string $type email|phone
 * @return array error 为 0 表示通过
 */
function zib_captcha_send_limit_check($to, $type = 'email')
{
    $to     = strtolower(trim($to));
    $option = zib_get_captcha_send_limit_option();
    $now    = current_time('timestamp');
    $ip     = zib_get_remote_ip_addr();

    // 发送冷却：同一收件人
    $cooldown = max(0, (int) $option['cooldown']);
    if ($cooldown > 0 && $to) {
        $last = (int) get_transient('zib_captcha_cd_to_' . md5($type . '|' . $to));
        if ($last > 0) {
            $remaining = $cooldown - ($now - $last);
            if ($remaining > 0) {
                return array(
                    'error'          => 1,
                    'remaining_time' => $remaining,
                    'ys'             => 'danger',
                    'msg'            => sprintf(__('%s秒后可重新发送', 'zib_language'), $remaining),
                );
            }
        }
    }

    // 发送冷却：同一 IP
    if ($cooldown > 0 && !zib_captcha_send_skip_ip_limit($ip)) {
        $last_ip = (int) get_transient('zib_captcha_cd_ip_' . md5($ip));
        if ($last_ip > 0) {
            $remaining = $cooldown - ($now - $last_ip);
            if ($remaining > 0) {
                return array(
                    'error'          => 1,
                    'remaining_time' => $remaining,
                    'ys'             => 'danger',
                    'msg'            => sprintf(__('%s秒后可重新发送', 'zib_language'), $remaining),
                );
            }
        }
    }

    // 收件人：1 小时内
    $to_hour_1 = (int) $option['to_hour_1'];
    if ($to_hour_1 > 0 && $to) {
        $count = (int) get_transient('zib_captcha_to_h_' . md5($type . '|' . $to));
        if ($count >= $to_hour_1) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => __('该号码/邮箱发送过于频繁，请稍后再试', 'zib_language'));
        }
    }

    // 收件人：一天内
    $to_day_1 = (int) $option['to_day_1'];
    if ($to_day_1 > 0 && $to) {
        $count = (int) get_transient('zib_captcha_to_d_' . md5($type . '|' . $to));
        if ($count >= $to_day_1) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => __('该号码/邮箱今日发送已达上限，请明日再试', 'zib_language'));
        }
    }

    // IP：10 分钟内
    $ip_minutes_10 = (int) $option['ip_minutes_10'];
    if ($ip_minutes_10 > 0 && !zib_captcha_send_skip_ip_limit($ip)) {
        $count = (int) get_transient('zib_captcha_ip_m_' . md5($ip));
        if ($count >= $ip_minutes_10) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => __('您的操作过于频繁，请10分钟后再试', 'zib_language'));
        }
    }

    // IP：一天内
    $ip_day_1 = (int) $option['ip_day_1'];
    if ($ip_day_1 > 0 && !zib_captcha_send_skip_ip_limit($ip)) {
        $count = (int) get_transient('zib_captcha_ip_d_' . md5($ip));
        if ($count >= $ip_day_1) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => __('您的验证码发送已达今日上限，请明日再试', 'zib_language'));
        }
    }

    return array('error' => 0);
}

/**
 * 验证码发送成功后记录防刷计数
 *
 * @param string $to 收件人邮箱或手机号
 * @param string $type email|phone
 */
function zib_captcha_send_limit_record($to, $type = 'email')
{
    $to     = strtolower(trim($to));
    $option = zib_get_captcha_send_limit_option();
    $now    = current_time('timestamp');
    $ip     = zib_get_remote_ip_addr();
    $cooldown = max(0, (int) $option['cooldown']);

    if ($cooldown > 0 && $to) {
        set_transient('zib_captcha_cd_to_' . md5($type . '|' . $to), $now, $cooldown + 30);
    }

    if ($cooldown > 0 && !zib_captcha_send_skip_ip_limit($ip)) {
        set_transient('zib_captcha_cd_ip_' . md5($ip), $now, $cooldown + 30);
    }

    if ($to) {
        $to_hour_1 = (int) $option['to_hour_1'];
        if ($to_hour_1 > 0) {
            $key = 'zib_captcha_to_h_' . md5($type . '|' . $to);
            set_transient($key, (int) get_transient($key) + 1, HOUR_IN_SECONDS);
        }

        $to_day_1 = (int) $option['to_day_1'];
        if ($to_day_1 > 0) {
            $key = 'zib_captcha_to_d_' . md5($type . '|' . $to);
            set_transient($key, (int) get_transient($key) + 1, DAY_IN_SECONDS);
        }
    }

    if (!zib_captcha_send_skip_ip_limit($ip)) {
        $ip_minutes_10 = (int) $option['ip_minutes_10'];
        if ($ip_minutes_10 > 0) {
            $key = 'zib_captcha_ip_m_' . md5($ip);
            set_transient($key, (int) get_transient($key) + 1, 10 * MINUTE_IN_SECONDS);
        }

        $ip_day_1 = (int) $option['ip_day_1'];
        if ($ip_day_1 > 0) {
            $key = 'zib_captcha_ip_d_' . md5($ip);
            set_transient($key, (int) get_transient($key) + 1, DAY_IN_SECONDS);
        }
    }
}

/**发送验证码 */
function zib_send_captcha($to, $type = 'email')
{
    @session_start();

    $limit = zib_captcha_send_limit_check($to, $type);
    if (!empty($limit['error'])) {
        return $limit;
    }

    $code = zib_get_captcha(6);
    /**保存验证码到session */
    $_SESSION['zib_captcha']         = $code;
    $_SESSION['zib_verification_to'] = $to;

    $_SESSION['zib_captcha_time']        = current_time('mysql');
    $_SESSION['zib_captcha_check_count'] = 1;
    $_SESSION['zib_captcha_check_time']  = 0;

    switch ($type) {
        case 'email':
            $result = false;
            if (is_email($to)) {
                $title   = __('收到验证码', 'zib_language');
                $message = __("您正在本站进行验证操作，如非您本人操作，请忽略此邮件。\r\n\r\n验证码30分钟内有效，如果超时请重新获取", 'zib_language');
                $message .= "\r\n\r\n";
                $message .= sprintf(__('您的邮箱为：%s', 'zib_language'), $to) . "\r\n\r\n";
                $message .= __('您的验证码为：', 'zib_language');
                $message .= '<p style="font-size:34px;color:#3095f1;"><span style="border-bottom: 1px dashed #ccc; z-index: 1; position: static;">' . $code . '</span></p>';
                $result = @wp_mail($to, $title, $message);
            }
            if ($result) {
                zib_captcha_send_limit_record($to, $type);
                return array('error' => 0, 'result' => true, 'msg' => __('验证码已发送至您的邮箱', 'zib_language'));
            } else {
                return array('error' => 1, 'ys' => 'danger', 'msg' => __('验证码发送失败', 'zib_language'));
            }
            break;
        case 'phone':
            $result = ZibSMS::send($to, $code);
            if (!empty($result['result'])) {
                zib_captcha_send_limit_record($to, $type);
                $result['msg'] = __('验证码短信已发送', 'zib_language');
            }
            return $result;
            break;
    }
}

//保存用户的验证成功时间、状态
function zib_set_verify_user($type, $user_id)
{
    @session_start();

    $_SESSION['verify_user_' . $type . '_' . $user_id] = current_time('mysql');
    zib_captcha_time();
}

function zib_ajax_is_verify_user($type, $user_id, $msg = '')
{
    $verify_user = zib_is_verify_user($type, $user_id, $msg);
    if ($verify_user['error']) {
        echo json_encode($verify_user);
        exit();
    }
    return true;
}

//校验用户验证是否通过
function zib_is_verify_user($type, $user_id, $msg = '')
{
    @session_start();

    if (empty($_SESSION['verify_user_' . $type . '_' . $user_id])) {
        return array('error' => 1, 'ys' => 'danger', 'msg' => $msg . __('验证失败', 'zib_language'));
    } else {
        $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['verify_user_' . $type . '_' . $user_id]);
        if ($time_x > 1800) {
            //30分钟有效
            return array('error' => 1, 'ys' => 'danger', 'msg' => $msg . __('验证已过期，请重新验证', 'zib_language'));
        }
        return array('error' => 0, 'result' => true, 'msg' => $msg . __('验证成功', 'zib_language'));
    }
}

/**
 * @description: 错误频率限制，避免恶意攻击，包含邮箱短信验证，密码验证
 * @param {*} $type
 * @param {*} $user_id
 * @param {*} $msg
 * @return {*}
 */
function zib_is_error_frequency_limit($key, $msg = '')
{
    if (!_pz('error_frequency_limit', false)) {
        return array('error' => 0);
    }

    @session_start();
    //频率限制，前5次不限制，5-10次限制30秒一次，后面5分钟一次
    $count        = $_SESSION['zib_check_count_' . $key] ?? 1;
    $last_time    = $_SESSION['zib_check_time_' . $key] ?? 0;
    $current_time = current_time('timestamp');

    //第6-10次，需间隔30秒
    if ($count > 5 && $count <= 10 && (($last_time + 30) - $current_time) > 0) {
        $_SESSION['zib_check_count_' . $key] = $count + 1;
        return array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('错误次数过多，请%s秒后再试', 'zib_language'), (($last_time + 30) - $current_time)));
    }

    //第11次后，需间隔300秒
    if ($count > 10 && (($last_time + 180) - $current_time) > 0) {
        $_SESSION['zib_check_count_' . $key] = $count + 1;
        return array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('错误次数超限，请%s秒后再试', 'zib_language'), (($last_time + 180) - $current_time)));
    }

    $_SESSION['zib_check_count_' . $key] = $count + 1;
    $_SESSION['zib_check_time_' . $key]  = $current_time;
    return array('error' => 0);
}

/**
 * 重置错误频率限制
 * @param {*} $key
 * @return {*}
 */
function zib_reset_error_frequency_limit($key)
{
    @session_start();
    //验证成功
    if (isset($_SESSION['zib_check_count_' . $key])) {
        unset($_SESSION['zib_check_count_' . $key]);
    }
    if (isset($_SESSION['zib_check_time_' . $key])) {
        unset($_SESSION['zib_check_time_' . $key]);
    }
}

/**
 * 邮箱、手机验证码校验
 *
 */
function zib_is_captcha($to, $code, $msg_prefix = '')
{

    //错误频率限制
    $is_captcha = zib_is_error_frequency_limit('captcha');
    if ($is_captcha['error']) {
        return $is_captcha;
    }

    if (empty($_SESSION['zib_captcha']) || $_SESSION['zib_captcha'] != $code || empty($_SESSION['zib_verification_to']) || $_SESSION['zib_verification_to'] != $to) {
        return array('error' => 1, 'ys' => 'danger', 'msg' => $msg_prefix . __('验证码错误', 'zib_language'));
    } else {
        if (!empty($_SESSION['zib_captcha_time'])) {
            $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['zib_captcha_time']);
            if ($time_x > 1800) {
                //30分钟有效
                return array('error' => 1, 'ys' => 'danger', 'msg' => $msg_prefix . __('验证码已过期', 'zib_language'));
            }
        }
    }

    //重置错误频率限制
    zib_reset_error_frequency_limit('captcha');

    return array('error' => 0, 'result' => true, 'msg' => $msg_prefix . __('验证码效验成功', 'zib_language'));
}

/**
 * @description: AJAX验证码判断，判断错误直接退出ajax
 * @param {*} $to
 * @param {*} $code
 * @return {*}
 */
function zib_ajax_is_captcha($to_name = 'email', $code_name = 'captch')
{
    $type_name = '';
    if ('email' == $to_name) {
        $type_name = __('邮箱', 'zib_language');
    } elseif ('phone' == $to_name) {
        $type_name = __('短信', 'zib_language');
    }
    if (empty($_REQUEST[$code_name])) {
        echo(json_encode(array('error' => 1, 'msg' => sprintf(__('请输入%s验证码', 'zib_language'), $type_name))));
        exit();
    }
    if (empty($_REQUEST[$to_name])) {
        echo(json_encode(array('error' => 1, 'msg' => __('缺少验证参数', 'zib_language'))));
        exit();
    }
    $is_captcha = zib_is_captcha($_REQUEST[$to_name], $_REQUEST[$code_name]);
    if ($is_captcha['error']) {
        echo json_encode($is_captcha);
        exit();
    }

    return true;
}

/**前端AJAX发送验证码 */
function zib_ajax_send_captcha($captcha_type, $to, $judgment = true)
{
    if ($judgment) {
        //人机验证
        $captcha      = zib_ajax_captcha_form_judgment($captcha_type, $to);
        $captcha_type = $captcha['type'];
        $to           = $captcha['to'];
    }
    $send = zib_send_captcha($to, $captcha_type);

    if (empty($send['error']) && empty($send['msg'])) {
        $send['msg'] = __('验证码已发送', 'zib_language');
    }
    echo json_encode($send);
    exit;
}

/**
 * @description: 执行用户隐私协议勾选检测 同意协议
 * @param {*} $id
 * @return {*}
 */
function zib_ajax_agree_agreement_judgment($name = 'user_agreement')
{
    if (zib_get_agreement_input() && empty($_REQUEST[$name])) {
        echo(json_encode(array('error' => 1, 'msg' => __('请先阅读并同意用户协议', 'zib_language'))));
        exit();
    }
}

//wp-ajax安全验证
function zib_ajax_wp_verify_nonce($action = null, $name = '_wpnonce')
{

    if (!$action) {
        $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : -1;
    }

    if (!isset($_REQUEST[$name]) || !wp_verify_nonce($_REQUEST[$name], $action)) {
        zib_send_json_error(__('环境异常，请刷新页面后稍候再试', 'zib_language'), 'warning');
    }
}

/**
 * @description: AJAX验证方式判断，已做了AJAX返回
 * @param {*} $captcha_type
 * @param {*} $input
 * @return 错误直接退出，正确返回 $captcha_type 验证方式
 */
function zib_ajax_captcha_form_judgment($captcha_type = 'email', $input = '')
{

    $captcha_type = $captcha_type ? $captcha_type : (!empty($_REQUEST['captcha_type']) ? $_REQUEST['captcha_type'] : '');
    $input        = $input ? $input : (!empty($_REQUEST[$captcha_type]) ? $_REQUEST[$captcha_type] : '');
    $input        = esc_sql(trim($input));
    if (!$captcha_type) {
        echo(json_encode(array('error' => 1, 'msg' => __('参数传入错误', 'zib_language'))));
        exit();
    }

    if ('email' == $captcha_type) {
        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            echo(json_encode(array('error' => 1, 'msg' => __('邮箱格式错误', 'zib_language'))));
            exit();
        }
    } elseif ('phone' == $captcha_type) {
        if (!ZibSMS::is_phonenumber($input)) {
            echo(json_encode(array('error' => 1, 'phone' => $input, 'msg' => __('手机号码格式有误', 'zib_language'))));
            exit();
        }
    } else {
        if (!$input) {
            echo(json_encode(array('error' => 1, 'msg' => __('请输入邮箱或手机号', 'zib_language'))));
            exit();
        }
        if (ZibSMS::is_phonenumber($input)) {
            $captcha_type = 'phone';
        } elseif (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $captcha_type = 'email';
        } else {
            echo(json_encode(array('error' => 1, 'msg' => __('手机号或邮箱格式错误', 'zib_language'))));
            exit();
        }
    }
    return array('type' => $captcha_type, 'to' => $input);
}

/**
 * @description: 空白内容
 * @param {*}
 * @return {*}
 */
function zib_get_ajax_error_html($args = array())
{

    $defaults = array(
        'error'  => 1,
        'class'  => '',
        'ys'     => 'danger',
        'margin' => '50',
        'id'     => '',
        'msg'    => null,
    );
    $args = wp_parse_args((array) $args, $defaults);

    if (null === $args['msg']) {
        $args['msg'] = __('内容获取出错！', 'zib_language');
    }

    $id  = $args['id'] ? ' id="' . $args['id'] . '"' : '';
    $con = sprintf(__('错误：%1$s，错误代码：%2$s', 'zib_language'), $args['msg'], $args['error']);

    $con = '<div class="ajax-item text-center text-' . $args['ys'] . ' ' . $args['class'] . '" style="padding:' . $args['margin'] . 'px 0;">' . $con . '</div>';
    $con .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    $html = '<div class="ajaxpager"' . $id . '>' . $con . '</div>';
    $html = '<body><main>' . $html . '</main></body>';

    return $html;
}

/**
 * @description: 判断是否是重复昵称
 * @param {*} $name
 * @return {*}
 */
function zib_is_repetition_username($name)
{
    $name    = esc_sql(trim($name));
    $db_name = false;
    if ($name) {
        global $wpdb;
        $db_name = zibDB::table($wpdb->users)->whereOr(['user_nicename' => $name, 'display_name' => $name])->count('1');
    }

    return $db_name;
}

/**
 * 用户邮件AJAX验证|错误直接结束ajax
 * @param {*} $email 邮箱
 * @return {*}
 */
function zib_ajax_email_judgment($email)
{

    if (is_disable_email($email)) {
        echo json_encode(array('error' => 1, 'msg' => __('邮箱非法或为禁止的邮箱格式', 'zib_language')));
        exit();
    }

    return true;
}

/**
 * @description: 用户名AJAX验证|错误直接结束ajax
 * @param {*} $name
 * @return {*}
 */
function zib_ajax_username_judgment($name, $simple = false)
{
    $user_name = !empty($_REQUEST[$name]) ? wp_slash(trim($_REQUEST[$name])) : '';

    $is = zib_is_username_judgment($user_name, $simple);
    if ($is['error']) {
        echo(json_encode($is));
        exit();
    }

    return true;
}

function zib_is_username_judgment($user_name, $logn_in = false)
{

    if (!$user_name) {
        return array('error' => 1, 'msg' => __('请输入用户名', 'zib_language'));
    }

    if (zib_new_strlen($user_name) < 2) {
        return array('error' => 1, 'msg' => __('用户名太短', 'zib_language'));
    }
    if (zib_new_strlen($user_name) > 16) {
        return array('error' => 1, 'msg' => __('用户名太长', 'zib_language'));
    }
    if (!$logn_in) {
        if (is_numeric($user_name)) {
            return array('error' => 1, 'msg' => __('用户名不能为纯数字', 'zib_language'));
        }
        if (filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
            return array('error' => 1, 'msg' => __('请勿使用邮箱账号作为用户名', 'zib_language'));
        }
        if (is_disable_username($user_name)) {
            return array('error' => 1, 'msg' => __('昵称含保留或非法字符', 'zib_language'));
        }
        //重复昵称判断
        if (_pz('no_repetition_name', true) && zib_is_repetition_username($user_name)) {
            return array('error' => 1, 'msg' => __('昵称已存在，请换一个试试', 'zib_language'));
        }
    }

    return array('error' => 0);
}

//注册阅读量
function zib_post_views_record()
{

    $post_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    if ($post_id) {
        $post_views = (int) get_post_meta($post_id, 'views', true);
        $new        = $post_views + 1;
        update_post_meta($post_id, 'views', $new);

        $term_views_objs = zib_get_term_post_views_objs($post_id);
        if ($term_views_objs) {
            foreach ($term_views_objs as $term) {
                wp_cache_set($term->id, $term->views, 'term_views_count');
                update_term_meta($term->id, 'views', $term->views);
                $ancestors = get_ancestors($term->id, $term->taxonomy, 'taxonomy');
                if ($ancestors) {
                    foreach ($ancestors as $ancestors_id) {
                        $count = zib_get_term_posts_meta_sum($ancestors_id, 'views');
                        wp_cache_set($ancestors_id, $count, 'term_views_count');
                        update_term_meta($ancestors_id, 'views', $count);
                        $term_views_objs[] = array(
                            'id'         => $ancestors_id,
                            'views'      => $count,
                            'taxonomy'   => $term->taxonomy,
                            'is_parent ' => true,
                        );
                    }
                }
            }
        }
        do_action('posts_views_record', $post_id, $new);
        wp_send_json_success(array('post_id' => $post_id, 'views' => $new, 'term' => $term_views_objs, 'num_queries' => get_num_queries(), 'timer_stop' => timer_stop(0, 6) * 1000 . 'ms'));
    }
    wp_send_json_error(array('error' => __('阅读量记录失败', 'zib_language')));
}
add_action('wp_ajax_views_record', 'zib_post_views_record');
add_action('wp_ajax_nopriv_views_record', 'zib_post_views_record');

/**
 * @description: AJAX获取登录用户的数据
 * @param {*}
 * @return {*}
 */
function zib_ajax_get_current_user()
{
    $data = array(
        'id'           => 0,
        'is_logged_in' => false,
        'user_data'    => null,
    );

    $user_data = wp_get_current_user();
    if (isset($user_data->ID)) {
        $_data = (array) $user_data->data;
        unset($_data['user_pass']);
        $data = array(
            'id'           => $user_data->ID,
            'is_logged_in' => true,
            'user_data'    => $_data,
        );
    }

    do_action('ajax_get_current_user', $data['id'], $data);
    $data = apply_filters('ajax_get_current_user_data', $data);

    $data['num_queries'] = get_num_queries();
    $data['timer_stop']  = timer_stop(0, 6) * 1000 . 'ms';
    zib_send_json_success($data);
}
add_action('wp_ajax_get_current_user', 'zib_ajax_get_current_user');
add_action('wp_ajax_nopriv_get_current_user', 'zib_ajax_get_current_user');

/**
 * @description: 查询term的阅读量总和
 * @param {*} $post_id
 * @return {*}
 */
function zib_get_term_post_views_objs($post_id)
{

    $post_id = (int) $post_id;
    if (!$post_id) {
        return array();
    }

    global $wpdb;
    $sql_postmeta = $wpdb->postmeta;
    $sql_posts    = $wpdb->posts;
    $sql_term_rel = $wpdb->term_relationships;
    $sql_taxonomy = $wpdb->term_taxonomy;

    $sql = "SELECT $sql_taxonomy.term_id as id,$sql_taxonomy.taxonomy as taxonomy,SUM($sql_postmeta.meta_value) as views FROM $sql_postmeta
    INNER JOIN $sql_term_rel ON $sql_term_rel.term_taxonomy_id in (SELECT term_taxonomy_id FROM $sql_term_rel WHERE $sql_term_rel.object_id = $post_id )
    INNER JOIN $sql_taxonomy ON $sql_taxonomy.term_taxonomy_id = $sql_term_rel.term_taxonomy_id
    INNER JOIN $sql_posts ON $sql_posts.ID = $sql_term_rel.object_id AND $sql_posts.post_status = 'publish'
    WHERE $sql_term_rel.object_id = $sql_postmeta.post_id and $sql_postmeta.meta_key = 'views'
    GROUP BY $sql_taxonomy.term_id";

    $query = $wpdb->get_results($sql);
    return $query;
}

//获取文章分享的数据
function zib_ajax_poster_share_data()
{

    $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;

    if (stristr($id, 'term')) {
        //分类的分享
        $id = (int) str_replace('term_', '', $id);

        $term = get_term($id);
        if (empty($term->term_id)) {
            zib_send_json_error(__('参数错误，或内容已删除', 'zib_language'));
        }
        $obj = $term;

        $title      = trim(strip_tags($term->name));
        $desc       = zib_str_cut($term->description, 0, 160, '...');
        $banner_url = zib_get_taxonomy_img_url($term->term_id, 'full', '');

        $url = get_term_link($term);

        $tags = '';
        $tags .= $term->count ? sprintf(__('%s篇文章', 'zib_language'), $term->count) : '';
    } elseif (stristr($id, 'rebate')) {
        //用户推荐返利的分享
        $user_id = (int) str_replace('rebate_', '', $id);
        $user    = get_userdata($user_id);
        if (empty($user->ID)) {
            zib_send_json_error(__('参数错误，或用户已删除', 'zib_language'));
        }
        $obj        = $user;
        $url        = zibpay_get_rebate_link($user_id);
        $banner_url = _pz('pay_rebate_poster_img');
        $title      = _pz('pay_rebate_poster_title');
        $desc       = _pz('pay_rebate_poster_desc');
        $tags       = __('推荐人: ', 'zib_language') . esc_attr($user->display_name);

    } elseif (stristr($id, 'user')) {
        //用户推荐返利的分享
        $user_id = (int) str_replace('user_', '', $id);
        $user    = get_userdata($user_id);
        if (empty($user->ID)) {
            zib_send_json_error(__('参数错误，或用户已删除', 'zib_language'));
        }
        $obj        = $user;
        $url        = zibpay_get_rebate_link($user_id);
        $banner_url = _pz('pay_rebate_poster_img');
        $title      = _pz('pay_rebate_poster_title');
        $desc       = _pz('pay_rebate_poster_desc');
        $tags       = __('作者: ', 'zib_language') . esc_attr($user->display_name);

    } else {
        $post = get_post($id);
        if (empty($post->ID)) {
            zib_send_json_error(__('参数错误，或内容已删除', 'zib_language'));
        }
        $obj      = $post;
        $subtitle = trim(strip_tags(zib_get_post_meta($post->ID, 'subtitle', true)));
        $title    = trim(strip_tags($post->post_title)) . $subtitle;
        $title    = zib_str_cut($title, 0, 32);

        $desc       = zib_get_excerpt(70, '...', $post);
        $url        = get_permalink($post);
        $banner_url = (zib_post_thumbnail('full', '', true, $post));

        $tags = '';
        if ('post' == $post->post_type) {
            $author = get_userdata($post->post_author)->display_name;
            $tags   = __('作者: ', 'zib_language') . esc_attr($author);
            $cat    = get_the_category($post->ID);
            $tags .= !empty($cat[0]) ? __(' · 分类: ', 'zib_language') . $cat[0]->cat_name : '';
        }
    }

    //返利链接
    $user_id = get_current_user_id();
    if (_pz('pay_rebate_s') && $user_id) {
        $url = zibpay_get_rebate_link($user_id, $url);
    }

    $data = array(
        'url'            => esc_url($url),
        'qrcode'         => zib_get_qrcode_base64($url),
        'banner'         => esc_url($banner_url),
        'banner_default' => esc_url(_pz('share_img_byimg')),
        'banner_spare'   => esc_url(ZIB_TEMPLATE_DIRECTORY_URI . '/img/share_img.jpg'),
        'logo'           => esc_url(_pz('share_logo')),
        'title'          => $title,
        'content'        => $desc,
        'tags'           => $tags,
        'description'    => _pz('share_desc', '扫描二维码阅读全文 '),
    );
    $data = apply_filters('poster_share_data', $data, $obj, $id);

    if (_pz('share_img_compatible_s')) {
        $data['banner']         = zib_get_img_auto_base64($data['banner']);
        $data['banner_default'] = zib_get_img_auto_base64($data['banner_default']);
        $data['logo']           = zib_get_img_auto_base64($data['logo']);
    }

    zib_send_json_success($data);
}
add_action('wp_ajax_poster_share_data', 'zib_ajax_poster_share_data');
add_action('wp_ajax_nopriv_poster_share_data', 'zib_ajax_poster_share_data');

function zib_ajax_share_modal()
{
    $id   = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'post';

    switch ($type) {
        case 'post':
            $post = get_post($id);
            if (empty($post->ID)) {
                zib_ajax_notice_modal('danger', __('内容不存在或参数传入错误', 'zib_language'));
            }

            //横排显示
            $html = zib_get_share('horizontal', $post);
            break;
        case 'term':
            $term = get_term($id);
            if (empty($term->term_id)) {
                zib_ajax_notice_modal('danger', __('内容不存在或参数传入错误', 'zib_language'));
            }

            //横排显示
            $html = zib_get_term_share('horizontal', $term);
            break;
    }

    $dasc   = apply_filters('share_modal_header_text', __('分享内容', 'zib_language'));
    $dasc   = $dasc ? '<span class="em09">' . $dasc . '</span>' : '';
    $header = zib_get_modal_colorful_header('jb-vip1', zib_get_svg('share', null, 'em14'), $dasc);
    echo $header . $html;
    exit;
}
add_action('wp_ajax_share_modal', 'zib_ajax_share_modal');
add_action('wp_ajax_nopriv_share_modal', 'zib_ajax_share_modal');

function zib_ajax_menu_search()
{

    $args = array(
        'show_form'     => false,
        'show_keywords' => _pz('search_popular_key', true),
        'show_history'  => _pz('search_history', true),
        'show_posts'    => _pz('search_posts', true),
    );

    $search = zib_get_search_box($args);

    if ($search) {
        $html = '<div class="dropdown-menu hover-show-con">';
        $html .= $search;
        $html .= '</div>';
        echo $html;
        exit();
    }
    exit('0');
}
add_action('wp_ajax_menu_search', 'zib_ajax_menu_search');
add_action('wp_ajax_nopriv_menu_search', 'zib_ajax_menu_search');

//使用ajax做安全检查
function zib_ajax_verify_nonce($action = null, $name = '_wpnonce')
{
    zib_ajax_wp_verify_nonce($action, $name);
}

/**
 * @description: ajax人机验证判断
 * @param {*} $captcha_type
 * @param {*} $input
 * @return {*}
 */
function zib_ajax_man_machine_verification($id = 0)
{
    @session_start();
    $_SESSION['machine_verification']      = false;
    $_SESSION['machine_verification_time'] = '';
    if (!$id) {
        $id = !empty($_REQUEST['action']) ? $_REQUEST['action'] : -1;
    }

    if (!empty($_REQUEST['captcha']) && is_string($_REQUEST['captcha'])) {
        @$_captcha = json_decode(stripslashes($_REQUEST['captcha']), true);
        if (!empty($_captcha)) {
            $_REQUEST['captcha'] = $_captcha;
        }
    }

    $type = _pz('user_verification_type', 'slider');
    if ('image' === $type) {
        if (empty($_REQUEST['canvas_yz']) || strlen($_REQUEST['canvas_yz']) < 4) {
            echo(json_encode(array('error' => 1, 'msg' => __('请输入图形验证码', 'zib_language'))));
            exit();
        }

        if (empty($_SESSION['machine_img_code_' . $id]) || empty($_SESSION['machine_img_time_' . $id])) {
            echo(json_encode(array('error' => 1, 'msg' => __('环境异常，请重新获取图形验证码', 'zib_language'))));
            exit();
        }

        if ($_SESSION['machine_img_code_' . $id] !== strtolower($_REQUEST['canvas_yz'])) {
            echo(json_encode(array('error' => 1, 'msg' => __('图形验证码错误', 'zib_language'))));
            exit();
        }

        //300秒有效期
        if (($_SESSION['machine_img_time_' . $id] + 300) < time()) {

            unset($_SESSION['machine_img_code_' . $id]);
            unset($_SESSION['machine_img_time_' . $id]);

            echo(json_encode(array('error' => 1, 'msg' => __('图形验证码已过期，请重新获取图形验证码', 'zib_language'))));
            exit();
        }
    }

    if ('slider' === $type) {
        if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['randstr']) || empty($_REQUEST['captcha']['spliced']) || empty($_REQUEST['captcha']['check'])) {
            echo(json_encode(array('error' => 1, 'msg' => __('环境异常，请刷新后重新提交', 'zib_language'))));
            exit();
        }
        if (!zib_slider_captcha_verification($_REQUEST['captcha']['ticket'], $_REQUEST['captcha']['randstr'])) {
            echo(json_encode(array('error' => 1, 'msg' => __('环境异常，人机验证失败', 'zib_language'))));
            exit();
        }
    }

    //腾讯云人机验证
    if ('tcaptcha' === $type) {
        $option = _pz('tcaptcha_option');
        if (!empty($option['api_secret_id']) && !empty($option['api_secret_key']) && !empty($option['appid']) && !empty($option['secret_key'])) {

            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['ticket'])) {
                echo(json_encode(array('error' => 1, 'msg' => __('人机验证失败，请重试', 'zib_language'))));
                exit();
            }

            $verification = zib_tcaptcha_verification($_REQUEST['captcha']['ticket'], $_REQUEST['captcha']['randstr']);
            if ($verification['error']) {
                echo(json_encode(array('error' => 1, 'msg' => !empty($verification['msg']) ? $verification['msg'] : __('环境异常，人机验证失败', 'zib_language'))));
                exit();
            }
        }
    }

    //极验行为验验证人机验证
    if ('geetest' === $type) {
        $option = _pz('geetest_option');
        if (!empty($option['id']) && !empty($option['key'])) {

            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['lot_number'])) {
                echo(json_encode(array('error' => 1, 'msg' => __('人机验证失败，请重试', 'zib_language'))));
                exit();
            }

            $verification = zib_geetest_verification($_REQUEST['captcha']);
            if ($verification['error']) {
                echo(json_encode(array('error' => 1, 'msg' => !empty($verification['msg']) ? $verification['msg'] : __('环境异常，人机验证失败', 'zib_language'))));
                exit();
            }
        }
    }

    $_SESSION['machine_verification']      = true;
    $_SESSION['machine_verification_time'] = current_time('Y-m-d H:i:s');

    return true;
}

//极验行为验验证人机验证
function zib_geetest_verification($data)
{
    $option = _pz('geetest_option');
    if (empty($option['id']) || empty($option['key'])) {
        return array('error' => 1, 'msg' => __('后台参数错误，请与客服联系', 'zib_language'));
    }

    if (empty($data['ticket']) || empty($data['lot_number']) || empty($data['gen_time']) || empty($data['captcha_output'])) {
        return array('error' => 1, 'msg' => __('人机验证参数异常，请重新验证', 'zib_language'));
    }

    //准备参数
    $api_server     = 'http://gcaptcha4.geetest.com/validate?captcha_id=' . $option['id'];
    $captcha_key    = $option['key'];
    $lot_number     = $data['lot_number'];
    $captcha_output = $data['captcha_output'];
    $pass_token     = $data['ticket'];
    $gen_time       = $data['gen_time'];

    //生成签名
    $sign_token = hash_hmac('sha256', $lot_number, $captcha_key);

    $query = array(
        'lot_number'     => $lot_number,
        'captcha_output' => $captcha_output,
        'pass_token'     => $pass_token,
        'gen_time'       => $gen_time,
        'sign_token'     => $sign_token,
    );

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->post($api_server, $query);
    $result   = $response->json(true);

    if (!isset($result['result'])) {
        return array('error' => 1, 'msg' => __('极验人机验证服务链接失败', 'zib_language'));
    }

    if ($result['result'] === 'success') {
        return array('error' => 0);
    }

    return array('error' => 1, 'msg' => __('极验人机验证失败', 'zib_language') . ((!empty($result['reason']) ? '：' . $result['reason'] : '')) . ((!empty($result['msg']) ? '：' . $result['msg'] : '')));
}

//滑动拼图验证
function zib_slider_captcha_verification($Ticket, $Randstr)
{

    if (empty($_SESSION['machine_slider_x']) || empty($_SESSION['machine_slider_rand_str'])) {
        return false;
    }

    $machine_slider_x        = $_SESSION['machine_slider_x'];
    $machine_slider_rand_str = $_SESSION['machine_slider_rand_str'];

    $T_a = (int) substr($Ticket, 0, 2);
    $T_b = (int) substr($Ticket, -2);
    $T_x = (int) substr($Ticket, $T_a + 2, $T_b - 2);

    if (absint($T_x - $machine_slider_x) > 8) {
        return false;
    }

    $R_a = (int) substr($Randstr, 0, 1);
    $R_b = (int) substr($Randstr, -2);
    $R_x = substr($machine_slider_rand_str, $R_a, $R_b - $R_a);
    if ($R_a . $R_x . $R_b !== $Randstr) {
        return false;
    }

    return true;
}

//腾讯验证码验证
function zib_tcaptcha_verification($Ticket, $Randstr)
{

    $UserIP = ZibPay::get_ip();
    $option = _pz('tcaptcha_option');
    if (empty($option['api_secret_id']) || empty($option['api_secret_key']) || empty($option['appid']) || empty($option['secret_key'])) {
        return array('error' => 1, 'msg' => __('后台参数错误，请与客服联系', 'zib_language'));
    }

    $params = array(
        'CaptchaType'  => 9,
        'Ticket'       => $Ticket,
        'UserIp'       => $UserIP,
        'Randstr'      => $Randstr,
        'CaptchaAppId' => (int) $option['appid'],
        'AppSecretKey' => $option['secret_key'],
    );
    $cred = new TxSDK_Send($option['api_secret_id'], $option['api_secret_key']);
    $send = $cred->send($params);

    if (!isset($send['Response'])) {
        return array('error' => 1, 'msg' => __('腾讯云人机验证服务链接失败', 'zib_language'));
    }

    $resp = $send['Response'];

    if (!empty($resp['Error']['Message'])) {
        return array('error' => 1, 'error_data' => $resp, 'msg' => __('腾讯智能验证：', 'zib_language') . $resp['Error']['Message']);
    }

    if (isset($resp['CaptchaMsg'])) {
        if ($resp['CaptchaCode'] === 1 || strtolower($resp['CaptchaMsg']) === 'ok') {
            return array('error' => 0);
        } elseif ($resp['CaptchaMsg']) {
            return array('error' => 1, 'msg' => __('人机验证失败：', 'zib_language') . $resp['CaptchaMsg']);
        }
    }
    return array('error' => 1);
}

/**前端AJAX链接提交 */
function zib_ajax_frontend_links_submit()
{

    //人机验证
    if (_pz('verification_links_s')) {
        zib_ajax_man_machine_verification();
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (isset($_COOKIE['zib_links_submit_time'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('操作过于频繁，请稍候再试', 'zib_language'))));
        exit();
    }
    if (empty($_POST['link_name'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请填写网站名称', 'zib_language'))));
        exit();
    }
    if (empty($_POST['link_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('请填写链接地址', 'zib_language'))));
        exit();
    }

    if (!zib_is_url($_POST['link_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('网站地址格式错误', 'zib_language'))));
        exit();
    }

    //logo图像
    $logo_img_id = zib_php_upload();
    if (!empty($logo_img_id['error'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $logo_img_id['msg'])));
        exit();
    }
    $logo_image_url = wp_get_attachment_image_src($logo_img_id, 'medium')[0];

    /**准备数据 */
    $linkdata = array(
        'link_name'        => esc_attr($_POST['link_name']),
        'link_url'         => esc_url($_POST['link_url']),
        'link_description' => !empty($_POST['link_description']) ? esc_attr($_POST['link_description']) : '',
        'link_image'       => $logo_image_url,
        'link_visible'     => is_super_admin() ? 'Y' : 'N',
        'link_category'    => !empty($_POST['link_category']) ? $_POST['link_category'] : array(),
    );

    $linkdata = wp_unslash(sanitize_bookmark($linkdata, 'db'));

    //禁止重复提交
    global $wpdb;
    $is_repeat = zibDB::table($wpdb->links)->where('link_url', $linkdata['link_url'])->count('1');
    if ($is_repeat) {
        zib_send_json_error(__('您的链接已提交，请勿重复提交', 'zib_language'));
    }

    /**添加链接 */
    $links_id = wp_insert_link($linkdata);
    if (is_wp_error($links_id)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $links_id->get_error_message())));
        exit();
    }

    //设置浏览器缓存限制提交的间隔时间
    $expire = time() + 30;
    setcookie('zib_links_submit_time', time(), $expire, '/', '', false);

    $send_msg = $linkdata['link_visible'] === 'Y' ? __('提交成功', 'zib_language') : __('提交成功，等待管理员处理', 'zib_language');
    echo(json_encode(array('msg' => $send_msg, 'reload' => true)));
    /**添加执行挂钩 */
    do_action('zib_ajax_frontend_links_submit_success', $linkdata);
    exit();
}
add_action('wp_ajax_frontend_links_submit', 'zib_ajax_frontend_links_submit');
add_action('wp_ajax_nopriv_frontend_links_submit', 'zib_ajax_frontend_links_submit');

/**
 * ajax 函数节流 ,防止高并发带来的问题
 * @param string $key 键名
 * @param string $args 参数
 * @param int $time 时间
 * @param string $msg 提示信息
 * @return bool
 */
function zib_ajax_debounce($key, $args, $time = 3, $msg = null)
{

    if (null === $msg) {
        $msg = __('操作过于频繁，请%time%秒后再试', 'zib_language');
    }

    $is_throttle = zib_fun_throttle($time, $key, $args);
    if (is_wp_error($is_throttle) && is_super_admin()) {
        zib_send_json_error(['msg' => sprintf(__('节流函数异常，可能会导致数据错误，请及时处理！错误信息：%1$s（此消息仅管理员可见）', 'zib_language'), $is_throttle->get_error_message()), 'error_msg' => $is_throttle->get_error_message()]);
    }

    if (!$is_throttle) {
        $msg = strstr($msg, '%time%') ? str_replace('%time%', $time, $msg) : $msg;
        zib_send_json_error(['msg' => $msg, 'key' => $key, 'args' => $args]);
    }

    return true;
}

//查询文章
function zib_ajax_query_posts_lists()
{
    $posts_per_page = isset($_GET['count']) ? (int) $_GET['count'] : 12;
    $paged          = zib_get_the_paged();
    $style          = isset($_GET['style']) ? $_GET['style'] : 'text';
    $paginate       = isset($_GET['paginate']) ? $_GET['paginate'] : '';
    $blank          = isset($_GET['blank']) ? $_GET['blank'] : '';
    $ajax_url       = zib_get_current_url();

    $args = array(
        'paged' => $paged,
    );
    //不需要分页
    $paginate = isset($_GET['paginate']) ? $_GET['paginate'] : '';
    if (!$paginate) {
        $args['no_found_rows'] = true; //不统计总数
    }

    //查询文章
    $new_query = zib_get_posts_query($args);

    $list_args = array(
        'show_thumb'  => $style === 'minicard',
        'show_meta'   => $style === 'minicard',
        'show_number' => false,
        'echo'        => false,
        'class'       => 'ajax-item',
        'blank'       => $blank,
    );
    $posts_lists = '';
    if ($new_query->have_posts()) {
        while ($new_query->have_posts()): $new_query->the_post();
            $posts_lists .= zib_posts_mini_while($list_args, $new_query);
        endwhile;
    }
    wp_reset_query();

    if (1 == $paged && !$posts_lists) {
        $posts_lists = zib_get_ajax_null(__('暂无内容', 'zib_language'), 10) . json_encode($new_query);
    }

    if ($paginate === 'ajax') {
        $posts_lists .= zib_get_ajax_next_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no');
    } elseif ($paginate === 'number') {
        $posts_lists .= zib_get_ajax_number_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url);
    } else {
        $posts_lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    zib_ajax_send_ajaxpager($posts_lists);
}
add_action('wp_ajax_query_posts_lists', 'zib_ajax_query_posts_lists');
add_action('wp_ajax_nopriv_query_posts_lists', 'zib_ajax_query_posts_lists');

//文章归档页 AJAX 分页
function zib_ajax_archives_posts_lists()
{
    zib_ajax_send_ajaxpager(zib_get_archives_posts_lists());
}
add_action('wp_ajax_archives_posts_lists', 'zib_ajax_archives_posts_lists');
add_action('wp_ajax_nopriv_archives_posts_lists', 'zib_ajax_archives_posts_lists');
