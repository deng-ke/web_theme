<?php
/*
 * Project       : Nova主题
 * Description   : 主题设置面板（Customizer）
 */

if (!defined('ABSPATH')) exit;

/**
 * 主题设置统一存储
 * 所有设置存于 nova_theme_settings 选项
 */
function nova_settings_sanitize($input)
{
    return is_array($input) ? $input : array();
}

/**
 * 注册 Customizer 设置
 */
function nova_customize_register($wp_customize)
{
    //========== 基础设置 ==========
    $wp_customize->add_panel('nova_panel', array(
        'title'    => __('Nova 主题设置', 'nova'),
        'priority' => 10,
    ));

    // 全局外观
    $wp_customize->add_section('nova_section_base', array(
        'title' => __('基础设置', 'nova'),
        'panel' => 'nova_panel',
    ));

    $fields_base = array(
        'theme_color'      => array(__('主题色', 'nova'), '#1e80ff'),
        'theme_color_dark' => array(__('深色模式主题色', 'nova'), '#0a84ff'),
        'border_radius'    => array(__('圆角大小(px)', 'nova'), '8'),
        'page_width'       => array(__('页面宽度(px)', 'nova'), '1200'),
        'site_logo_text'   => array(__('Logo文字(未设置图片Logo时显示)', 'nova'), ''),
        'footer_text'      => array(__('页脚版权文字', 'nova'), ''),
        'beian_number'     => array(__('备案号', 'nova'), ''),
    );
    foreach ($fields_base as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_base',
            'type'    => 'text',
        ));
    }

    // 主题色
    $wp_customize->add_setting('nova_setting[theme_color]', array('default' => '#1e80ff', 'sanitize_callback' => 'sanitize_hex_color'));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nova_theme_color', array(
        'label'   => __('主题色', 'nova'),
        'section' => 'nova_section_base',
        'settings' => 'nova_setting[theme_color]',
    )));

    // 开关项
    $switches = array(
        'dark_mode_s'    => array(__('开启深色模式切换', 'nova'), '1'),
        'lazy_img_s'     => array(__('开启图片懒加载', 'nova'), '1'),
        'waterfall_s'    => array(__('文章列表瀑布流布局', 'nova'), '0'),
        'post_views_s'   => array(__('显示文章浏览数', 'nova'), '1'),
        'comment_s'      => array(__('开启评论', 'nova'), '1'),
        'sign_s'         => array(__('开启每日签到', 'nova'), '1'),
        'captcha_s'      => array(__('登录注册开启图形验证码', 'nova'), '1'),
        'register_s'     => array(__('开启用户注册', 'nova'), '1'),
        'forgot_pass_s'  => array(__('开启忘记密码(邮箱)', 'nova'), '1'),
        'pay_s'          => array(__('开启付费阅读功能', 'nova'), '1'),
        'forum_s'        => array(__('开启论坛功能', 'nova'), '1'),
        'shop_s'         => array(__('开启商城功能', 'nova'), '1'),
        'message_s'      => array(__('开启消息中心', 'nova'), '1'),
        'post_share_s'   => array(__('开启文章分享', 'nova'), '1'),
        'like_s'         => array(__('开启点赞', 'nova'), '1'),
        'seo_s'          => array(__('开启SEO优化', 'nova'), '1'),
        'baidu_push_s'   => array(__('开启百度推送', 'nova'), '0'),
        'user_verify_s'  => array(__('开启用户认证', 'nova'), '1'),
        'post_edit_s'    => array(__('前台允许用户投稿编辑文章', 'nova'), '0'),
    );
    foreach ($switches as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_base',
            'type'    => 'checkbox',
        ));
    }

    // 数字设置
    $numbers = array(
        'posts_per_page'      => array(__('文章列表每页数量', 'nova'), '12'),
        'excerpt_length'      => array(__('摘要字数', 'nova'), '100'),
        'avatar_quality'      => array(__('头像压缩质量', 'nova'), '80'),
        'upload_img_compress' => array(__('上传图片压缩质量(0不压缩)', 'nova'), '80'),
    );
    foreach ($numbers as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'absint',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_base',
            'type'    => 'number',
        ));
    }

    //========== SEO 设置 ==========
    $wp_customize->add_section('nova_section_seo', array(
        'title' => __('SEO设置', 'nova'),
        'panel' => 'nova_panel',
    ));
    $fields_seo = array(
        'seo_keywords'    => array(__('全站关键词', 'nova'), ''),
        'seo_description' => array(__('全站描述', 'nova'), ''),
        'seo_baidu_token' => array(__('百度搜索资源平台token', 'nova'), ''),
    );
    foreach ($fields_seo as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_seo',
            'type'    => 'text',
        ));
    }

    //========== 支付设置 ==========
    $wp_customize->add_section('nova_section_pay', array(
        'title' => __('支付设置', 'nova'),
        'panel' => 'nova_panel',
    ));
    $fields_pay = array(
        'pay_alipay_s'  => array(__('开启支付宝支付', 'nova'), '1'),
        'pay_wechat_s'  => array(__('开启微信支付', 'nova'), '1'),
        'pay_bank_s'    => array(__('开启银行卡/转账支付', 'nova'), '1'),
        'pay_balance_s' => array(__('开启余额支付', 'nova'), '1'),
        'paypoints_s'   => array(__('开启积分抵扣(1积分=1分钱)', 'nova'), '0'),
        'pay_alipay_appid'  => array(__('支付宝APPID', 'nova'), ''),
        'pay_alipay_private_key' => array(__('支付宝应用私钥', 'nova'), ''),
        'pay_alipay_public_key'  => array(__('支付宝公钥', 'nova'), ''),
        'pay_wechat_appid'  => array(__('微信支付APPID', 'nova'), ''),
        'pay_wechat_mch_id' => array(__('微信支付商户号', 'nova'), ''),
        'pay_wechat_key'    => array(__('微信支付API密钥', 'nova'), ''),
        'pay_bank_info'     => array(__('收款账号信息(转账支付时显示，支持HTML)', 'nova'), ''),
    );
    foreach ($fields_pay as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_pay',
            'type'    => ('1' === $field[1]) ? 'checkbox' : ('pay_alipay_private_key' === $key || 'pay_wechat_key' === $key ? 'password' : 'textarea'),
        ));
    }

    //========== 社交登录 ==========
    $wp_customize->add_section('nova_section_oauth', array(
        'title' => __('社交登录设置', 'nova'),
        'panel' => 'nova_panel',
    ));
    $fields_oauth = array(
        'oauth_qq_s'     => array(__('开启QQ登录', 'nova'), '1'),
        'oauth_qq_appid' => array(__('QQ AppID', 'nova'), ''),
        'oauth_qq_appkey'=> array(__('QQ AppKey', 'nova'), ''),
        'oauth_qq_appsecret' => array(__('QQ AppSecret', 'nova'), ''),
        'oauth_wechat_s' => array(__('开启微信登录(扫码)', 'nova'), '1'),
        'oauth_wechat_appid'   => array(__('微信开放平台AppID', 'nova'), ''),
        'oauth_wechat_appkey'  => array(__('微信开放平台AppKey', 'nova'), ''),
        'oauth_wechat_appsecret' => array(__('微信开放平台AppSecret', 'nova'), ''),
        'oauth_weibo_s'  => array(__('开启微博登录', 'nova'), '0'),
        'oauth_weibo_appid' => array(__('微博AppKey', 'nova'), ''),
        'oauth_weibo_appkey' => array(__('微博AppSecret', 'nova'), ''),
        'oauth_weibo_appsecret' => array(__('微博AppSecret', 'nova'), ''),
        'oauth_github_s' => array(__('开启Github登录', 'nova'), '0'),
        'oauth_github_appid' => array(__('Github ClientID', 'nova'), ''),
        'oauth_github_appkey' => array(__('Github ClientSecret', 'nova'), ''),
        'oauth_github_appsecret' => array(__('Github ClientSecret', 'nova'), ''),
    );
    foreach ($fields_oauth as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_oauth',
            'type'    => ('1' === $field[1]) ? 'checkbox' : ('oauth_qq_appsecret' === $key || 'oauth_wechat_appsecret' === $key || 'oauth_weibo_appsecret' === $key || 'oauth_github_appsecret' === $key ? 'password' : 'text'),
        ));
    }

    //========== 消息通知 ==========
    $wp_customize->add_section('nova_section_msg', array(
        'title' => __('消息通知设置', 'nova'),
        'panel' => 'nova_panel',
    ));
    $fields_msg = array(
        'msg_email_s'  => array(__('邮箱通知(注册/评论/订单)', 'nova'), '1'),
        'msg_smtp_host' => array(__('SMTP服务器', 'nova'), ''),
        'msg_smtp_port' => array(__('SMTP端口', 'nova'), '465'),
        'msg_smtp_user' => array(__('SMTP账号', 'nova'), ''),
        'msg_smtp_pass' => array(__('SMTP密码', 'nova'), ''),
        'msg_smtp_from' => array(__('发件人名称', 'nova'), ''),
    );
    foreach ($fields_msg as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_msg',
            'type'    => ('1' === $field[1]) ? 'checkbox' : ('msg_smtp_pass' === $key ? 'password' : 'text'),
        ));
    }

    //========== 积分成长与会员 ==========
    $wp_customize->add_section('nova_section_growth', array(
        'title' => __('积分成长与会员', 'nova'),
        'panel' => 'nova_panel',
    ));
    $fields_growth = array(
        'reg_points'        => array(__('注册奖励积分', 'nova'), '10', 'number'),
        'sign_points'       => array(__('签到奖励积分', 'nova'), '5', 'number'),
        'comment_points'    => array(__('评论奖励积分', 'nova'), '2', 'number'),
        'forum_post_points' => array(__('发帖奖励积分', 'nova'), '5', 'number'),
        'points_buy_rate'   => array(__('积分购买比例(1元=多少积分)', 'nova'), '10', 'number'),
        'post_sale_percent' => array(__('付费文章作者分成比例(%)', 'nova'), '80', 'number'),
        'vip_s'             => array(__('开启VIP会员', 'nova'), '1', 'checkbox'),
        'vip_levels'        => array(__('VIP等级配置(JSON数组，如[{"name":"月度VIP","days":30,"price":18},{"name":"年度VIP","days":365,"price":128}]，为空使用默认)', 'nova'), '', 'textarea'),
        'withdraw_s'        => array(__('开启提现功能', 'nova'), '1', 'checkbox'),
        'withdraw_min'      => array(__('最低提现金额(元)', 'nova'), '10', 'number'),
    );
    foreach ($fields_growth as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_growth',
            'type'    => $field[2],
        ));
    }

    //========== 页面与内容 ==========
    $wp_customize->add_section('nova_section_pages', array(
        'title' => __('页面与内容', 'nova'),
        'panel' => 'nova_panel',
    ));
    $fields_pages = array(
        'site_desc'       => array(__('站点副标题(首页SEO描述)', 'nova'), '', 'text'),
        'logo_url'        => array(__('自定义Logo图片URL(优先于媒体库Logo)', 'nova'), '', 'text'),
        'stats_code'      => array(__('页脚统计代码(HTML)', 'nova'), '', 'textarea'),
        'toc_s'           => array(__('文章显示目录(TOC)', 'nova'), '1', 'checkbox'),
        'related_s'       => array(__('文章显示相关推荐', 'nova'), '1', 'checkbox'),
        'copyright_s'     => array(__('文章显示版权声明', 'nova'), '1', 'checkbox'),
        'card_s'          => array(__('开启充值卡功能', 'nova'), '1', 'checkbox'),
        'loadmore_s'      => array(__('首页列表使用加载更多(否则分页)', 'nova'), '1', 'checkbox'),
        'page_loader_s'   => array(__('页面顶部加载进度条', 'nova'), '1', 'checkbox'),
        'forum_review_s'  => array(__('论坛发帖需审核', 'nova'), '0', 'checkbox'),
        'forum_page'      => array(__('论坛页面ID(留空自动创建)', 'nova'), '', 'number'),
        'shop_page'       => array(__('商城页面ID(留空自动创建)', 'nova'), '', 'number'),
        'signin_page'     => array(__('登录页面ID(留空自动创建)', 'nova'), '', 'number'),
        'signup_page'     => array(__('注册页面ID(留空自动创建)', 'nova'), '', 'number'),
        'lostpwd_page'    => array(__('找回密码页面ID(留空自动创建)', 'nova'), '', 'number'),
        'user_page'       => array(__('用户中心页面ID(留空自动创建)', 'nova'), '', 'number'),
        'home_notice'     => array(__('首页公告(留空不显示)', 'nova'), '', 'textarea'),
        'home_slides'     => array(__('首页轮播图(JSON数组，如[{"img":"图片URL","url":"链接","title":"标题"}]，留空不显示)', 'nova'), '', 'textarea'),
        'archive_bg_img'  => array(__('归档页顶部背景图URL(留空使用纯色)', 'nova'), '', 'text'),
        'author_bg_img'   => array(__('作者页顶部背景图URL(留空使用纯色)', 'nova'), '', 'text'),
    );
    foreach ($fields_pages as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_pages',
            'type'    => $field[2],
        ));
    }

    //========== 短信 ==========
    $wp_customize->add_section('nova_section_sms', array(
        'title' => __('短信验证码设置', 'nova'),
        'panel' => 'nova_panel',
    ));
    $wp_customize->add_setting('nova_setting[sms_provider]', array('default' => '', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('nova_setting[sms_provider]', array(
        'label'   => __('短信服务商', 'nova'),
        'section' => 'nova_section_sms',
        'type'    => 'select',
        'choices' => array(
            ''         => __('不开启', 'nova'),
            'aliyun'   => __('阿里云短信', 'nova'),
            'tencent'  => __('腾讯云短信', 'nova'),
        ),
    ));
    $fields_sms = array(
        'sms_aliyun_ak'   => array(__('阿里云AccessKeyId', 'nova'), ''),
        'sms_aliyun_sk'   => array(__('阿里云AccessKeySecret', 'nova'), ''),
        'sms_aliyun_sign' => array(__('阿里云签名', 'nova'), ''),
        'sms_aliyun_tpl'  => array(__('阿里云模板CODE', 'nova'), ''),
        'sms_tencent_id'  => array(__('腾讯云SecretId', 'nova'), ''),
        'sms_tencent_key' => array(__('腾讯云SecretKey', 'nova'), ''),
        'sms_tencent_appid' => array(__('腾讯云SDKAppID', 'nova'), ''),
        'sms_tencent_sign' => array(__('腾讯云签名', 'nova'), ''),
        'sms_tencent_tpl' => array(__('腾讯云模板ID', 'nova'), ''),
    );
    foreach ($fields_sms as $key => $field) {
        $wp_customize->add_setting('nova_setting[' . $key . ']', array(
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('nova_setting[' . $key . ']', array(
            'label'   => $field[0],
            'section' => 'nova_section_sms',
            'type'    => ('sms_aliyun_sk' === $key || 'sms_tencent_key' === $key) ? 'password' : 'text',
        ));
    }
}
add_action('customize_register', 'nova_customize_register');

/**
 * 输出主题 CSS 变量
 */
function nova_output_theme_vars()
{
    $color      = nova_get_setting('theme_color', '#1e80ff');
    $radius     = (int) nova_get_setting('border_radius', 8);
    $width      = (int) nova_get_setting('page_width', 1200);
    $css = ":root{--nova-color:{$color};--nova-radius:{$radius}px;--nova-container:{$width}px;}";
    echo '<style id="nova-vars">' . $css . '</style>' . "\n";
}
add_action('wp_head', 'nova_output_theme_vars', 8);

/**
 * 建表钩子
 */
function nova_setup_tables()
{
    if (!get_option('nova_db_version') || get_option('nova_db_version') !== NOVA_VERSION) {
        NovaDB::setup_tables();
        update_option('nova_db_version', NOVA_VERSION);
    }
}
add_action('init', 'nova_setup_tables');
