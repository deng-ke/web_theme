<?php
/*
 * Project       : Nova主题
 * Description   : 邮件发送（SMTP）
 */

if (!defined('ABSPATH')) exit;

/**
 * 使用 SMTP 发送邮件
 */
function nova_send_mail($to, $subject, $content, $is_html = true)
{
    $host = nova_get_setting('msg_smtp_host');
    $port = (int) nova_get_setting('msg_smtp_port', 465);
    $user = nova_get_setting('msg_smtp_user');
    $pass = nova_get_setting('msg_smtp_pass');
    $from_name = nova_get_setting('msg_smtp_from', get_bloginfo('name'));

    if ($host && $user) {
        add_action('phpmailer_init', function ($phpmailer) use ($host, $port, $user, $pass, $from_name) {
            $phpmailer->isSMTP();
            $phpmailer->Host       = $host;
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Port       = $port;
            $phpmailer->Username   = $user;
            $phpmailer->Password   = $pass;
            $phpmailer->SMTPSecure = '465' == $port ? 'ssl' : 'tls';
            $phpmailer->CharSet    = 'UTF-8';
            $phpmailer->From       = $user;
            $phpmailer->FromName   = $from_name;
        });
    }

    $headers = array('Content-Type: text/html; charset=UTF-8');
    return wp_mail($to, $subject, $content, $headers);
}

/**
 * 邮件模板
 */
function nova_mail_template($title, $body_html)
{
    $site_name = get_bloginfo('name');
    $color = nova_get_setting('theme_color', '#1e80ff');
    $html = '<div style="max-width:600px;margin:0 auto;font-family:Arial,Microsoft YaHei,sans-serif;background:#f5f6f8;padding:20px 0;">';
    $html .= '<div style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);">';
    $html .= '<div style="background:' . $color . ';color:#fff;padding:20px 30px;font-size:18px;font-weight:bold;">' . esc_html($site_name) . '</div>';
    $html .= '<div style="padding:30px;">';
    $html .= '<h2 style="margin:0 0 16px;font-size:16px;color:#333;">' . esc_html($title) . '</h2>';
    $html .= $body_html;
    $html .= '</div>';
    $html .= '<div style="padding:16px 30px;color:#999;font-size:12px;border-top:1px solid #eee;">' . esc_html__('此邮件由系统自动发送，请勿直接回复', 'nova') . '</div>';
    $html .= '</div></div>';
    return $html;
}

/**
 * 发送验证码邮件
 */
function nova_send_code_email($email, $code, $scene = 'register')
{
    $scenes = array(
        'register' => __('注册验证码', 'nova'),
        'reset'    => __('找回密码验证码', 'nova'),
        'bind'     => __('绑定邮箱验证码', 'nova'),
    );
    $scene_name = isset($scenes[$scene]) ? $scenes[$scene] : __('验证码', 'nova');
    $body = '<div style="background:#f0f6ff;border-radius:8px;padding:20px;text-align:center;margin:16px 0;">';
    $body .= '<div style="font-size:12px;color:#888;margin-bottom:8px;">' . esc_html__('您的验证码是', 'nova') . '</div>';
    $body .= '<div style="font-size:32px;font-weight:bold;letter-spacing:6px;color:#333;">' . esc_html($code) . '</div>';
    $body .= '<div style="font-size:12px;color:#888;margin-top:8px;">' . esc_html__('验证码5分钟内有效，请勿泄露给他人', 'nova') . '</div>';
    $body .= '</div>';
    $title = $scene_name . ' - ' . get_bloginfo('name');
    return nova_send_mail($email, $title, nova_mail_template($title, $body));
}

/**
 * 发送通知邮件
 */
function nova_send_notice_email($email, $title, $content)
{
    $body = '<div style="color:#555;line-height:1.8;">' . wp_kses_post($content) . '</div>';
    return nova_send_mail($email, $title, nova_mail_template($title, $body));
}
