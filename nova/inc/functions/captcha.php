<?php
/*
 * Project       : Nova主题
 * Description   : 验证码系统（图形验证码+发送限制）
 */

if (!defined('ABSPATH')) exit;

/**
 * 生成图形验证码图片（AJAX 输出）
 */
function nova_captcha_img()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $code = nova_get_captcha_code();
    @session_start();
    $_SESSION['nova_captcha'] = $code;
    session_write_close();

    $w = 120;
    $h = 40;
    $img = imagecreatetruecolor($w, $h);
    //背景
    $bg = imagecolorallocate($img, 245, 246, 248);
    imagefilledrectangle($img, 0, 0, $w, $h, $bg);

    //噪点
    for ($i = 0; $i < 200; $i++) {
        $color = imagecolorallocate($img, rand(150, 220), rand(150, 220), rand(150, 220));
        imagesetpixel($img, rand(0, $w), rand(0, $h), $color);
    }
    //干扰线
    for ($i = 0; $i < 5; $i++) {
        $color = imagecolorallocate($img, rand(120, 200), rand(120, 200), rand(120, 200));
        imageline($img, rand(0, $w), rand(0, $h), rand(0, $w), rand(0, $h), $color);
    }

    //文字
    $len = strlen($code);
    for ($i = 0; $i < $len; $i++) {
        $color = imagecolorallocate($img, rand(30, 100), rand(30, 100), rand(30, 100));
        $x = 15 + $i * 18 + rand(-3, 3);
        $y = rand(12, 22);
        imagestring($img, 5, $x, $y, $code[$i], $color);
    }

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    imagepng($img);
    imagedestroy($img);
    exit;
}
add_action('wp_ajax_nova_captcha_img', 'nova_captcha_img');
add_action('wp_ajax_nopriv_nova_captcha_img', 'nova_captcha_img');

/**
 * 生成验证码字符串
 */
function nova_get_captcha_code($count = 5)
{
    $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
    $code  = '';
    $max   = strlen($chars) - 1;
    for ($i = 0; $i < $count; $i++) {
        $code .= $chars[rand(0, $max)];
    }
    return $code;
}

/**
 * 校验验证码
 */
function nova_verify_captcha($code)
{
    if (!nova_get_setting('captcha_s', '1')) return true;
    if (!$code) return false;
    @session_start();
    $session_code = isset($_SESSION['nova_captcha']) ? $_SESSION['nova_captcha'] : '';
    unset($_SESSION['nova_captcha']);
    session_write_close();
    if (!$session_code) return false;
    return strtolower($code) === strtolower($session_code);
}

/**
 * 发送验证码防刷检查
 * @param string $type 类型 sms|email
 * @return true|WP_Error
 */
function nova_captcha_send_check($type = 'sms')
{
    $ip = nova_get_ip();
    $now = current_time('U');
    $key = 'nova_send_' . $type . '_' . md5($ip);

    $record = get_transient($key);
    if (!$record) $record = array('count' => 0, 'first' => $now);

    //10分钟内最多5次
    if ($now - $record['first'] < 600) {
        if ($record['count'] >= 5) {
            return new WP_Error('nova_limit', '发送过于频繁，请10分钟后再试');
        }
    } else {
        $record = array('count' => 0, 'first' => $now);
    }

    //60秒内只能发一次
    $last = get_transient($key . '_last');
    if ($last && $now - $last < 60) {
        return new WP_Error('nova_limit', '请等待60秒后重新发送');
    }

    $record['count']++;
    set_transient($key, $record, 600);
    set_transient($key . '_last', $now, 60);
    return true;
}

/**
 * 保存验证码并设置过期时间
 */
function nova_set_verify_code($type, $target, $code, $expire = 300)
{
    $key = 'nova_code_' . $type . '_' . md5($target);
    set_transient($key, strtolower($code), $expire);
    return $key;
}

/**
 * 校验并销毁验证码
 */
function nova_check_verify_code($type, $target, $code)
{
    $key = 'nova_code_' . $type . '_' . md5($target);
    $saved = get_transient($key);
    if (!$saved) return false;
    delete_transient($key);
    return strtolower($code) === strtolower($saved);
}

/**
 * 获取图形验证码 HTML
 */
function nova_captcha_html()
{
    if (!nova_get_setting('captcha_s', '1')) return '';
    $html = '<div class="nova-captcha-field">';
    $html .= '<input type="text" class="nova-captcha-input" name="captcha" placeholder="' . esc_attr__('图形验证码', 'nova') . '" autocomplete="off">';
    $html .= '<img class="nova-captcha-img" src="' . esc_url(add_query_arg(array('action' => 'nova_captcha_img', 'nonce' => wp_create_nonce('nova_ajax_nonce')), admin_url('admin-ajax.php'))) . '" alt="captcha" title="' . esc_attr__('点击刷新', 'nova') . '">';
    $html .= '</div>';
    return $html;
}
