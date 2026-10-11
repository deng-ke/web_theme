<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2026-05-24 21:05:20
 * @LastEditTime : 2026-06-22 11:12:35
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|前台 JS 国际化字符串
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 获取注入到 window._win.i18n 的全部 JS 文案
 *
 * @return array<string, string> key => translated string
 */
function zib_get_js_i18n_strings()
{
    $strings = array(
        // ── main.js ────────────────────────────────
        'countdown_end_time'          => __('结束时间：', 'zib_language'),
        'countdown_day'               => __('天', 'zib_language'),
        'countdown_hour'              => __('小时', 'zib_language'),
        'countdown_minute'            => __('分', 'zib_language'),
        'countdown_second'            => __('秒', 'zib_language'),
        'countdown_ended'             => __('已结束', 'zib_language'),
        'load_more'                   => __('加载更多', 'zib_language'),
        'loading'                     => __('加载中...', 'zib_language'),
        'confirm_unbind'              => __('确认要解除账号绑定吗？', 'zib_language'),
        'confirm_grant_badge'         => __('确认要授予此徽章吗？', 'zib_language'),
        'confirm_revoke_badge'        => __('确认要收回此徽章吗？', 'zib_language'),
        'confirm_clear_search'        => __('确认要清空全部搜索记录？', 'zib_language'),
        'like_already_post'           => __('已赞过此文章了！', 'zib_language'),
        'like_already_comment'        => __('已赞过此评论了！', 'zib_language'),
        'like_thanks'                 => __('已赞！感谢您的支持', 'zib_language'),
        'done'                        => __('处理完成', 'zib_language'),
        'ajax_error'                  => __('操作失败 %1$s %2$s，请刷新页面后重试', 'zib_language'),
        'ajax_fatal_error'            => __('网站遇到致命错误，请检查插件冲突或通过错误日志排除错误', 'zib_language'),
        'qrcode_failed'               => __('二维码获取失败，请稍后再试', 'zib_language'),
        'select_max'                  => __('最多可选择%1$s个', 'zib_language'),
        'input_max'                   => __('最大可输入1$', 'zib_language'),
        'input_min'                   => __('最小可输入1$', 'zib_language'),
        'search_min_chars'            => __('请至少输入%1$s个字符', 'zib_language'),
        'search_loading'              => __('正在搜索，请稍候...', 'zib_language'),
        'checkin_loading'             => __('正在签到，请稍后...', 'zib_language'),
        'please_wait'                 => __('请稍候', 'zib_language'),
        'processing'                  => __('正在处理请稍后...', 'zib_language'),
        'read_more'                   => __('展开阅读全文', 'zib_language'),

        // ── 通用（多文件复用）────────────────────────────────
        'confirm'                     => __('确认', 'zib_language'),
        'confirm_action'              => __('确认%1$s？', 'zib_language'),
        'processing_wait'             => __('正在处理请稍等...', 'zib_language'),
        'operation_success'           => __('操作成功', 'zib_language'),
        'network_error_retry'         => __('网络错误，请稍后重试', 'zib_language'),
        'ajax_retry_failed'           => __('操作失败，请刷新页面后重试', 'zib_language'),
        'enter_field'                 => __('请输入%1$s', 'zib_language'),
        'fill_field'                  => __('请填写%1$s', 'zib_language'),
        'select_field'                => __('请选择%1$s', 'zib_language'),
        'close'                       => __('关闭', 'zib_language'),
        'insert'                      => __('插入', 'zib_language'),
        'send'                        => __('发送', 'zib_language'),
        'uploading'                   => __('上传中', 'zib_language'),
        'upload_processing'           => __('处理中', 'zib_language'),
        'upload_preparing'            => __('准备中', 'zib_language'),
        'upload_preparing_dot'        => __('准备中...', 'zib_language'),
        'upload_done'                 => __('已上传', 'zib_language'),
        'upload_failed'               => __('上传失败', 'zib_language'),
        'permanent'                   => __('永久', 'zib_language'),
        'points'                      => __('积分', 'zib_language'),
        'currency_yuan'               => __('￥', 'zib_language'),

        // ── comment.js ───────────────────────────────────────
        'comment_confirm_delete'      => __('确认要删除此评论吗？', 'zib_language'),
        'comment_approve'             => __('批准', 'zib_language'),
        'comment_reject'              => __('驳回', 'zib_language'),
        'comment_pending_review'      => __('待审核', 'zib_language'),
        'comment_reply'               => __('回复', 'zib_language'),
        'comment_enter_name_email'    => __('请输入昵称和邮箱', 'zib_language'),
        'comment_email_invalid'       => __('邮箱格式错误', 'zib_language'),
        'comment_too_short'           => __('评论内容过少', 'zib_language'),
        'comment_hidden_after_review' => __('审核后通过后即可查看隐藏内容', 'zib_language'),
        'comment_edit_content'        => __('编辑此内容', 'zib_language'),
        'comment_fetching_content'    => __('正在获取内容，请稍后...', 'zib_language'),

        // ── sign-register.js ─────────────────────────────────
        'captcha_resend_in'           => __('%1$s秒后可重新发送', 'zib_language'),
        'agree_terms_first'           => __('请先阅读并同意用户协议', 'zib_language'),

        // ── message.js ───────────────────────────────────────
        'message_all_loaded'          => __('已加载全部', 'zib_language'),
        'message_load_more'           => __('继续加载', 'zib_language'),

        // ── input-expand.js ──────────────────────────────────
        'enter_code'                  => __('请输入代码', 'zib_language'),
        'enter_valid_image_url'       => __('请输入正确的图片地址', 'zib_language'),
        'enter_valid_link_url'        => __('请输入正确的链接地址', 'zib_language'),

        // ── mini-upload.js ───────────────────────────────────
        'upload_format_error'         => __('文件[%1$s]格式错误', 'zib_language'),
        'upload_size_exceeded'        => __('文件[%1$s]大小超过限制，最大%2$sM，请重新选择', 'zib_language'),
        'upload_count_exceeded'       => __('文件数量过多！最多可选择%1$s个文件', 'zib_language'),
        'upload_select_first'         => __('请先选择待上传的文件！', 'zib_language'),
        'upload_unsupported'          => __('当前浏览器不支持图片上传，请更换浏览器', 'zib_language'),

        // ── slidercaptcha.js / captcha.js ────────────────────
        'captcha_retry'               => __('请再试一次', 'zib_language'),
        'captcha_slide_hint'          => __('向右滑动填充拼图', 'zib_language'),
        'captcha_load_failed'         => __('加载失败', 'zib_language'),
        'captcha_verify_title'        => __('滑动以完成验证', 'zib_language'),
        'captcha_data_failed'         => __('滑块验证数据获取失败，网站疑似SSL或https设置有误，请对照浏览器报错进行排查', 'zib_language'),
        'captcha_click_refresh'       => __('点击刷新', 'zib_language'),

        // ── page-template.js ─────────────────────────────────
        'search_enter_keyword'        => __('请输入搜索关键词', 'zib_language'),
        'search_keyword_short'        => __('关键词太短，请重新输入', 'zib_language'),
        'preview_post'                => __('预览文章', 'zib_language'),
        'last_saved'                  => __('最后保存：', 'zib_language'),

        // ── page-navs.js ─────────────────────────────────────
        'save_success'                => __('保存成功！', 'zib_language'),

        // ── imgbox.js ────────────────────────────────────────
        'imgbox_download'             => __('下载图片', 'zib_language'),
        'imgbox_play'                 => __('播放图片', 'zib_language'),
        'imgbox_view_more'            => __('查看更多图片', 'zib_language'),
        'imgbox_toggle_zoom'          => __('切换图片缩放', 'zib_language'),
        'imgbox_toggle_full'          => __('切换全屏', 'zib_language'),

        // ── poster-share.js ──────────────────────────────────
        'week_prefix'                 => __('周', 'zib_language'),
        'week_sun'                    => __('日', 'zib_language'),
        'week_mon'                    => __('一', 'zib_language'),
        'week_tue'                    => __('二', 'zib_language'),
        'week_wed'                    => __('三', 'zib_language'),
        'week_thu'                    => __('四', 'zib_language'),
        'week_fri'                    => __('五', 'zib_language'),
        'week_sat'                    => __('六', 'zib_language'),
        'poster_load_failed'          => __('海报加载失败', 'zib_language'),
        'poster_generating'           => __('正在生成图片，请稍候...', 'zib_language'),

        // ── zibpay/assets/js/pay.js ──────────────────────────
        'pay_alipay'                  => __('支付宝', 'zib_language'),
        'pay_wechat'                  => __('微信支付', 'zib_language'),
        'pay_order_timeout'           => __('订单支付超时，请重新下单', 'zib_language'),
        'pay_enter_coupon'            => __('请输入优惠码', 'zib_language'),
        'pay_discount_reduce'         => __('优惠立减', 'zib_language'),
        'pay_discount_off'            => __('%1$s折优惠', 'zib_language'),
        'pay_discount'                => __('优惠', 'zib_language'),
        'pay_discount_fold'           => __('%1$s折', 'zib_language'),
        'pay_valid_until'             => __('有效期至', 'zib_language'),
        'pay_coupon_available'        => __('优惠码可用', 'zib_language'),
        'pay_initiating'              => __('正在发起支付，请稍后...', 'zib_language'),
        'pay_redirecting'             => __('正在跳转到支付页面', 'zib_language'),
        'pay_complete'                => __('请完成支付', 'zib_language'),
        'pay_scan_qrcode'             => __('请扫码支付，支付成功后会自动跳转', 'zib_language'),
        'pay_trade_closed'            => __('交易已关闭', 'zib_language'),
        'pay_success_redirect'        => __('支付成功，页面跳转中', 'zib_language'),
        'pay_vip_loading'             => __('加载中，请稍等...', 'zib_language'),
        'pay_select_vip_option'       => __('请选择会员选项', 'zib_language'),
        'pay_order_creating'          => __('正在生成订单，请稍候', 'zib_language'),

        // ── poster-share.js ──────────────────────────────────
        'bbs_vote_count'              => __('%1$s票', 'zib_language'),
        'bbs_vote_success'            => __('投票成功', 'zib_language'),
        'bbs_topic_posts'             => __('帖子:%1$s', 'zib_language'),

    );

    $strings['currency_yuan'] = function_exists('zibpay_get_pay_mark') ? zibpay_get_pay_mark() : (_pz('pay_mark') ?: '￥');

    /**
     * 扩展 JS i18n 字符串（插件或其它模块可在此追加）
     *
     * @param array $strings
     */
    return apply_filters('zib_js_i18n_strings', $strings);
}

/**
 * 获取后台 JS 文案（注入 window._zib.i18n）
 *
 * @return array<string, string>
 */
function zib_get_admin_js_i18n_strings()
{
    $strings = array(
        // ── js/admin-main.js ─────────────────────────────────
        'confirm'                        => __('确认', 'zib_language'),
        'admin_content'                  => __('内容', 'zib_language'),
        'admin_copied'                   => __('%1$s已复制', 'zib_language'),
        'admin_copy_failed'              => __('%1$s复制失败，请手动复制', 'zib_language'),
        'admin_confirm_process'          => __('确认处理此申请？', 'zib_language'),
        'admin_url_warning'              => __('直接修改WordPress地址或站点地址会导致网站严重错误，推荐根据zibll官网教程以及专用插件进行修改', 'zib_language'),
        'admin_url_tutorial'             => __('查看详细教程', 'zib_language'),
        'admin_url_plugin'               => __('下载一键换域名插件', 'zib_language'),
        'admin_home_latest_posts'        => __('子比首页[最新文章]', 'zib_language'),
        'admin_home_posts_page'          => __('子比首页：', 'zib_language'),
        'admin_home_posts_page_desc'     => __('将网站主页更改为其他页面后，您可以新建一个页面并将其设置为原本的子比首页', 'zib_language'),
        'admin_posts_per_page'           => __('文章每页显示', 'zib_language'),
        'admin_posts_per_page_desc'      => __('第一页的置顶文章不在此数量内，您可以在主题设置->文章列表中设置翻页模式 ', 'zib_language') . '<a href="/wp-admin/admin.php?page=zibll_options#tab=%e6%96%87%e7%ab%a0%e5%88%97%e8%a1%a8/%e6%96%87%e7%ab%a0%e5%88%97%e8%a1%a8">' . __('【去设置】', 'zib_language') . '</a>',
        'admin_menu_pc_warning'          => __('PC端菜单请勿添加过多的一级菜单，过多会导致PC端导航栏显示不下，将自动折叠显示', 'zib_language'),
        'admin_menu_mobile_hint'         => __('移动端最多显示两级菜单，请合理使用高级子菜单以达到最佳效果，查看官方教程 ', 'zib_language') . '| <a target="_blank" href="https://www.zibll.com/1012.html">' . __('【查看官方教程】', 'zib_language') . '</a>',
        'admin_permalink_recommend'      => __('推荐选择自定义结构，并设置为', 'zib_language') . '<code class="">/%post_id%.html</code>',
        'admin_permalink_rewrite'        => __('注意：请务必配置好服务器的伪静态功能，否则页面会出现404错误！查看教程 ', 'zib_language') . '<a target="_blank" href="https://www.zibll.com/3025.html">' . __('【查看教程】', 'zib_language') . '</a>',
        'admin_permalink_auto_save'      => __('自动填入推荐结构并保存', 'zib_language'),

        // ── inc/csf-framework/assets/js/main.js ───────────────
        'csf_processing_wait'            => __('正在处理，请稍候...', 'zib_language'),
        'csf_please_wait'                => __('请稍候...', 'zib_language'),
        'csf_ajax_error'                 => __('ajax请求错误，请排除插件和子主题，或通过错误日志进行排查！错误信息已输出至浏览器控制台，请对照分析。 %1$s|%2$s', 'zib_language'),
        'csf_fatal_error_keyword'        => __('致命错误', 'zib_language'),
        'csf_fatal_error'                => __('网站遇到致命错误，请检查插件冲突或通过错误日志排除错误！错误信息已输出至浏览器控制台，请对照分析。 ', 'zib_language'),
        'csf_ajax_error_console'         => __('ajax请求发生错误，以下是错误信息', 'zib_language'),
        'csf_operation_failed'           => __('操作失败', 'zib_language'),
        'csf_continue_processing'        => __('正在继续处理，请稍后...', 'zib_language'),
        'csf_online_update'              => __('在线更新', 'zib_language'),
        'csf_update_confirm_countdown'   => __('我已阅读更新提醒，确认更新 （%1$s）', 'zib_language'),
        'csf_update_remind_title'        => __('更新提醒：', 'zib_language'),
        'csf_update_remind_backup'       => __('更新主题会覆盖全部主题文件，如有修改过主题源码请自行备份', 'zib_language'),
        'csf_update_remind_no_refresh'   => __('更新途中请勿刷新页面，避免出现文件损坏等意外', 'zib_language'),
        'csf_update_remind_server'       => __('在线更新需要连接官方服务器，如连接失败请稍候再试', 'zib_language'),
        'csf_update_remind_cdn'          => __('如您的网站套有CDN，请将CDN回源超时设置为60秒以上', 'zib_language'),
        'csf_update_remind_manual'       => __('如果在线更新失败，也可以使用手动更新哦，一样很简单', 'zib_language'),
        'csf_update_remind_white_screen' => __('涉及到文件替换，更新成功后可能会有几秒的白屏或者异常报错情况，稍等几秒后刷新即可', 'zib_language'),
        'csf_update_remind_risk'         => __('更新主题涉及到各种兼容及未知问题，有极小概率会出现致命错误，请确认已知晓并接受此风险', 'zib_language'),
        'csf_update_remind_confirm'      => __('确认更新请再次点击下方按钮', 'zib_language'),
        'csf_fetching_update'            => __('正在获取更新文件，请稍候...', 'zib_language'),
        'csf_svg_invalid'                => __('SVG代码有误，请检查', 'zib_language'),
        'csf_extract_code_label'         => __('提取码：', 'zib_language'),
        'csf_extract_code'               => __('提取码', 'zib_language'),

        //商城中心菜单的名称
        'admin_menu_title_shop_center'   => __('zibll商城中心', 'zib_language'),
        'admin_menu_title_order'         => __('订单明细', 'zib_language'),
        'admin_menu_title_shipping'      => __('发货及物流', 'zib_language'),
        'admin_menu_title_after_sale'    => __('售后管理', 'zib_language'),
        'admin_menu_title_order_old'     => __('订单明细(旧版)', 'zib_language'),
        'admin_menu_title_coupon'        => __('优惠码管理', 'zib_language'),
        'admin_menu_title_card'          => __('卡密管理', 'zib_language'),
        'admin_menu_title_income'        => __('分成明细', 'zib_language'),
        'admin_menu_title_rebate'        => __('佣金明细', 'zib_language'),
        'admin_menu_title_withdraw'      => __('提现管理', 'zib_language'),
        'admin_menu_title_vip'           => __('会员管理', 'zib_language'),
        'admin_menu_title_product'       => __('商品管理', 'zib_language'),
    );

    /**
     * 扩展后台 JS i18n 字符串
     *
     * @param array $strings
     */
    return apply_filters('zib_admin_js_i18n_strings', $strings);
}

/**
 * 前后台语言分离时，让部分后台 JS 文案使用前台语言
 *
 * @param array $strings
 * @return array
 */
function zib_get_admin_js_i18n_strings_to_frontend($strings)
{
    if (!_pz('locale_split_s') || !function_exists('switch_to_locale')) {
        return $strings;
    }

    $frontend_locale = _pz('locale_frontend', 'en_US');
    if (!$frontend_locale) {
        return $strings;
    }

    switch_to_locale($frontend_locale);
    $strings['csf_extract_code_label'] = __('提取码：', 'zib_language');
    $strings['csf_extract_code']       = __('提取码', 'zib_language');
    restore_current_locale();

    return $strings;
}
add_filter('zib_admin_js_i18n_strings', 'zib_get_admin_js_i18n_strings_to_frontend', 99);

function zib_get_mce_i18n_strings()
{
    return [

        // ── precode.js（TinyMCE 前台编辑器）──────────────────
        'precode_edit'                   => __('编辑', 'zib_language'),
        'precode_insert'                 => __('插入', 'zib_language'),
        'precode_title'                  => __('高亮代码', 'zib_language'),
        'precode_select_lang'            => __('选择语言', 'zib_language'),
        'precode_general'                => __('通用高亮', 'zib_language'),
        'precode_theme'                  => __('主题', 'zib_language'),
        'precode_follow_global'          => __('跟随全局设置', 'zib_language'),
        'precode_code_label'             => __('代码：', 'zib_language'),

        // ── editextend.js（TinyMCE 前台编辑器，节选高频）──────
        'media_type_image'               => __('图片', 'zib_language'),
        'media_type_video'               => __('视频', 'zib_language'),
        'media_type_audio'               => __('音频', 'zib_language'),
        'media_type_file'                => __('附件', 'zib_language'),
        'media_upload'                   => __('上传', 'zib_language'),
        'media_my_image'                 => __('我的图片', 'zib_language'),
        'media_external_image'           => __('外链图片', 'zib_language'),
        'media_my_video'                 => __('我的视频', 'zib_language'),
        'media_external_video'           => __('外链视频', 'zib_language'),
        'media_embed_video'              => __('嵌入视频', 'zib_language'),
        'media_my_file'                  => __('我的文件', 'zib_language'),
        'media_enter_url'                => __('输入地址', 'zib_language'),
        'media_search'                   => __('搜索', 'zib_language'),
        'media_drag_upload'              => __('拖拽文件至此以上传', 'zib_language'),
        'media_no_content'               => __('暂无相应内容', 'zib_language'),
        'media_upload_continue'          => __('继续上传中，请勿刷新页面', 'zib_language'),
        'media_max_urls'                 => __('最多可输入%1$s个%2$s地址', 'zib_language'),
        'media_min_chars'                => __('至少输入两个字符', 'zib_language'),
        'media_max_batch'                => __('单次最多可上传%1$s个内容', 'zib_language'),
        'media_format_denied'            => __('不允许上传此格式文件', 'zib_language'),
        'media_file_size_limit'          => __('文件[%1$s]大小超过限制，最大%2$sM', 'zib_language'),
        'media_confirm'                  => __('确认', 'zib_language'),
        'media_upload_title_max'         => __('最大支持%1$sM', 'zib_language'),
        'media_upload_title_batch'       => __('，单次可上传%1$s个文件', 'zib_language'),
        'media_upload_title_drag'        => __('，支持拖文件上传', 'zib_language'),
        'media_input_url_title'          => __('请填写%1$s地址%2$s', 'zib_language'),
        'media_input_url_batch'          => __('，支持批量输入，一行一个链接', 'zib_language'),
        'media_input_url_colon'          => __('：', 'zib_language'),
        'media_iframe_prompt'            => __('请输入嵌入地址或者直接粘贴iframe嵌入代码：', 'zib_language'),
        'media_iframe_confirm'           => __('确认嵌入', 'zib_language'),
        'media_loading'                  => __('加载中', 'zib_language'),
        'media_load_more'                => __('加载更多', 'zib_language'),
        'media_enter_type_url'           => __('请输入%1$s地址', 'zib_language'),
        'media_ajax_error'               => __('操作失败 %1$s %2$s，请刷新页面后重试', 'zib_language'),
        'media_fatal_error'              => __('网站遇到致命错误，请检查插件冲突或通过错误日志排除错误', 'zib_language'),
        'media_uploaded'                 => __('已上传', 'zib_language'),
        'media_upload_failed'            => __('上传失败', 'zib_language'),
        'media_processing'               => __('处理中', 'zib_language'),
        'media_preparing'                => __('准备中', 'zib_language'),
        'media_preparing_dots'           => __('准备中...', 'zib_language'),
        'editor_download_url'            => __('下载地址', 'zib_language'),
        'editor_enter_download_url'      => __('请输入下载地址', 'zib_language'),
        'editor_upload_file'             => __('上传文件', 'zib_language'),
        'editor_paste_link_hint'         => __('部分网盘分享链接可直接粘贴，可自动识别链接及提取码', 'zib_language'),
        'editor_resource_note'           => __('资源备注', 'zib_language'),
        'editor_enter_more'              => __('请输入更多内容', 'zib_language'),
        'editor_resource_note_hint'      => __('显示在下载按钮旁边的内容，建议为提取码、解压密码等', 'zib_language'),
        'editor_click_copy'              => __('点击复制', 'zib_language'),
        'editor_copy_name'               => __('复制名称', 'zib_language'),
        'editor_copy_content'            => __('复制内容', 'zib_language'),
        'editor_copy_hint'               => __('为"资源备注"按钮添加点击复制功能，建议为提取码、解压密码等', 'zib_language'),
        'editor_custom_btn'              => __('个性化下载按钮', 'zib_language'),
        'editor_custom_btn_ph'           => __('自定义按钮名称', 'zib_language'),
        'editor_select_btn_style'        => __('选择按钮风格', 'zib_language'),
        'editor_select_attachment'       => __('请选择或上传附件', 'zib_language'),
        'editor_edit_resource'           => __('编辑资源', 'zib_language'),
        'featured_cover_slide'           => __('封面幻灯片', 'zib_language'),
        'featured_cover_image'           => __('图片封面', 'zib_language'),
        'featured_btn_image'             => __('图像', 'zib_language'),
        'featured_set_video_cover'       => __('设置视频首图封面', 'zib_language'),
        'featured_btn_video'             => __('视频', 'zib_language'),
        'featured_btn_remove'            => __('移除', 'zib_language'),
        'featured_fill_image_url'        => __('请填写图片地址：', 'zib_language'),
        'featured_set_video_cover_title' => __('为视频设置首图封面', 'zib_language'),
        'hide_reply_after_view'          => __('评论后查看', 'zib_language'),
        'hide_content_prefix'            => __('隐藏内容：', 'zib_language'),
        'editor_quote_gray'              => __('灰色', 'zib_language'),
        'editor_quote_red'               => __('红色', 'zib_language'),
        'editor_quote_blue'              => __('蓝色', 'zib_language'),
        'editor_quote_green'             => __('绿色', 'zib_language'),
        'editor_file_ext'                => __('%1$s文件', 'zib_language'),
        'editor_enter_filename'          => __('请输入文件名称', 'zib_language'),
        'editor_download'                => __('下载', 'zib_language'),
        'editor_download_icon_alt'       => __('下载图标', 'zib_language'),
        'hide_reply_to_view'             => __('评论后可查看', 'zib_language'),
        'hide_login_to_view'             => __('登录后可查看', 'zib_language'),
        'hide_vip_to_view'               => __('会员可查看', 'zib_language'),
        'hide_pay_to_view'               => __('付费后可查看', 'zib_language'),
        'hide_paid_read'                 => __('付费阅读', 'zib_language'),
        'hide_content'                   => __('隐藏内容', 'zib_language'),
        'editor_unsaved_leave'           => __('当前内容暂未发布，请确认要离开吗？', 'zib_language'),
        'editor_enter_content'           => __('请输入内容', 'zib_language'),
        'editor_quote'                   => __('引言', 'zib_language'),
        'editor_attachment'              => __('附件', 'zib_language'),
        'editor_download_resource'       => __('添加资源', 'zib_language'),
        'editor_extract_code'            => __('提取码', 'zib_language'),
        'editor_extract_code_colon'      => __('提取码：', 'zib_language'),
    ];
}

//小工具 JS 国际化字符串
function zib_get_widget_js_i18n_strings()
{
    return array(
        'confirmImportReplace'     => __('将覆盖当前容器中已有的全部模块，导入内容将替换该侧栏；确定继续？', 'zib_language'),
        'confirmImportAppend'      => __('将在当前容器已有模块列表末尾追加导入内容，不会删除原有模块；确定继续？', 'zib_language'),
        'confirmImport'            => __('将覆盖当前容器中已有的全部模块，导入内容将替换该侧栏；确定继续？', 'zib_language'),
        'importModeLabel'          => __('导入方式', 'zib_language'),
        'importModeReplace'        => __('覆盖（替换现有）', 'zib_language'),
        'importModeAppend'         => __('追加（保留现有）', 'zib_language'),
        'loadingExport'            => __('正在生成 JSON…', 'zib_language'),
        'loadingImport'            => __('正在导入…', 'zib_language'),
        'copyDone'                 => __('已复制', 'zib_language'),
        'copyFail'                 => __('复制失败，请手动全选复制', 'zib_language'),
        'exportTitle'              => __('导出 JSON（可复制）', 'zib_language'),
        'importTitle'              => __('导入 JSON（粘贴后确认）', 'zib_language'),
        'importHint'               => __('将导出得到的 JSON 粘贴到下方文本框以导入。注意：不同主题版本或非同站的的模块导入后可能会出现错误，此时可以重新配置一下有错误的模块，即可恢复正常', 'zib_language'),
        'exportHint'               => __('以下为当前容器模块数据，可直接全选复制保存。', 'zib_language'),
        'close'                    => __('关闭', 'zib_language'),
        'copy'                     => __('复制全部', 'zib_language'),
        'submitImport'             => __('确认导入', 'zib_language'),
        'reloadPage'               => __('刷新页面', 'zib_language'),
        'templateBtn'              => __('导入模板', 'zib_language'),
        'templateModalTitle'       => __('从模板导入', 'zib_language'),
        'templateHint'             => __('使用模板可快速配置网站布局，选择对应的模板点击导入即可。', 'zib_language') . '<div class="c-yellow em09 mb10"><b class="c-yellow">' . __('注意事项：', 'zib_language') . '</b>' . __('1.导入模板必须对容器位置对应，例如不能将侧边栏模板导入到全宽度中！', 'zib_language') . '<br>' . __('2.导入模板需已经有对应的内容，例如：导入文章相关的模板，那么至少已经有多篇文章和文章分类', 'zib_language') . '</div>',
        'templateLoadingList'      => __('正在加载模板列表…', 'zib_language'),
        'templateEmpty'            => __('暂无可用模板。', 'zib_language'),
        'templateSubmit'           => __('导入所选模板', 'zib_language'),
        'confirmTemplate'          => __('确定导入模板「{title}」吗？导入方式：{mode}', 'zib_language'),
        'templatePick'             => __('请选择一个模板。', 'zib_language'),
        'templateExpandModules'    => __('展开模块列表', 'zib_language'),
        'templateCollapseModules'  => __('收起模块列表', 'zib_language'),
        // ── js/widget-set.js（侧栏导出/导入 fallback）──────────
        'admin_export_failed'      => __('导出失败', 'zib_language'),
        'admin_import_success'     => __('导入成功', 'zib_language'),
        'admin_import_failed'      => __('导入失败', 'zib_language'),
        'admin_paste_json_first'   => __('请先粘贴 JSON', 'zib_language'),
        'admin_export_modules'     => __('导出模块', 'zib_language'),
        'admin_import_modules'     => __('导入模块', 'zib_language'),
        'admin_import_template'    => __('模板导入', 'zib_language'),
        'admin_remove_module'      => __('移出模块', 'zib_language'),
        'admin_select_image'       => __('选择图片', 'zib_language'),
        'admin_remove_image'       => __('移除图片', 'zib_language'),
        'admin_select_image_slide' => __('选择图片插入到幻灯片', 'zib_language'),
        'admin_network_error'      => __('网络错误，请刷新页面后重试', 'zib_language'),
    );
}

function zib_get_gutenberg_i18n_strings()
{
    return array(
        'gutenberg_block_title'                   => __('Zibll主题模块', 'zib_language'),
        'gutenberg_editor_enhanced'               => __('子比主题：增强编辑器', 'zib_language'),
        'gutenberg_view_official_tutorial'        => __('查看官方教程', 'zib_language'),
        'gutenberg_view_official_tutorial_link'   => __('【查看官网教程】', 'zib_language'),
        // 文件下载
        'gutenberg_file_download'                 => __('文件下载', 'zib_language'),
        'gutenberg_file_download_desc'            => __('一个下载文件的模块', 'zib_language'),
        'gutenberg_select_or_upload_file'         => __('选择或上传文件', 'zib_language'),
        'gutenberg_ext_file'                      => __('%s文件', 'zib_language'),
        'gutenberg_select_or_upload'              => __('选择或上传', 'zib_language'),
        'gutenberg_replace'                       => __('替换', 'zib_language'),
        'gutenberg_file'                          => __('文件', 'zib_language'),
        'gutenberg_download_icon'                 => __('下载图标', 'zib_language'),
        'gutenberg_enter_file_name'               => __('请输入文件名称', 'zib_language'),
        'gutenberg_enter_download_url_or_file'    => __('请输入下载地址或选择文件：', 'zib_language'),
        'gutenberg_enter_download_url'            => __('请输入下载地址', 'zib_language'),
        'gutenberg_local_file_selected'           => __('已选择本地文件', 'zib_language'),
        'gutenberg_enter_url'                     => __('输入地址', 'zib_language'),
        'gutenberg_local_file'                    => __('本地文件', 'zib_language'),
        'gutenberg_download'                      => __('下载', 'zib_language'),
        'gutenberg_enter_file_attr'               => __('请输入文件属性或其他内容', 'zib_language'),
        'gutenberg_add_file_attr'                 => __('添加文件属性', 'zib_language'),
        'gutenberg_file_icon'                     => __('文件图标', 'zib_language'),
        'gutenberg_select_or_upload_file_icon'    => __('选择或上传文件图标', 'zib_language'),
        'gutenberg_add'                           => __('添加', 'zib_language'),
        'gutenberg_icon'                          => __('图标', 'zib_language'),
        'gutenberg_remove'                        => __('移除', 'zib_language'),
        'gutenberg_file_icon_square_hint'         => __('可选择一张正方形图片作为文件图标', 'zib_language'),
        // Tab 栏目
        'gutenberg_tab'                           => __('Tab栏目', 'zib_language'),
        'gutenberg_tab_desc'                      => __('在文章中添加多栏目的Tab', 'zib_language'),
        'gutenberg_tab_col_1'                     => __('栏目 1', 'zib_language'),
        'gutenberg_tab_col_2'                     => __('栏目 2', 'zib_language'),
        'gutenberg_tab_col_3'                     => __('栏目 3', 'zib_language'),
        'gutenberg_tab_col_n'                     => __('栏目%1$s', 'zib_language'),
        'gutenberg_enter_title'                   => __('输入标题...', 'zib_language'),
        'gutenberg_cannot_delete_tab_min_one'     => __('不能再删除！至少保留一个栏目', 'zib_language'),
        'gutenberg_add_plus'                      => __('+ 添加', 'zib_language'),
        'gutenberg_settings'                      => __('设置', 'zib_language'),
        'gutenberg_nav_position'                  => __('导航栏位置', 'zib_language'),
        'gutenberg_top'                           => __('顶部', 'zib_language'),
        'gutenberg_left'                          => __('左侧', 'zib_language'),
        'gutenberg_right'                         => __('右侧', 'zib_language'),
        // 视频剧集
        'gutenberg_video_series'                  => __('视频剧集', 'zib_language'),
        'gutenberg_video_series_desc'             => __('在文章中插入多剧集的视频，支持本地视频以及m3u8、mpd、flv等流媒体格式', 'zib_language'),
        'gutenberg_episode_title_placeholder'     => __('请输入剧集标题，默认为：第%1$s集', 'zib_language'),
        'gutenberg_enter_video_url_or_upload'     => __('输入视频地址或选择、上传本地视频', 'zib_language'),
        'gutenberg_select_or_upload_video'        => __('选择或上传视频', 'zib_language'),
        'gutenberg_local_video'                   => __('本地视频', 'zib_language'),
        'gutenberg_cannot_delete_episode_min_two' => __('不能再删除！至少保留两个剧集', 'zib_language'),
        'gutenberg_video_series_module'           => __('Zibll剧集视频模块', 'zib_language'),
        'gutenberg_video_series_hint'             => __('在文章中插入多剧集的视频，支持本地视频以及m3u8、mpd、flv等流媒体格式，如果仅需单个视频请使用“Zibll视频”模块', 'zib_language'),
        'gutenberg_add_episode'                   => __('添加剧集', 'zib_language'),
        'gutenberg_select_or_upload_poster'       => __('选择或上传视频海报图像', 'zib_language'),
        'gutenberg_poster_image'                  => __('海报图像', 'zib_language'),
        'gutenberg_autoplay'                      => __('自动播放', 'zib_language'),
        'gutenberg_loop'                          => __('循环播放', 'zib_language'),
        'gutenberg_hide_controller'               => __('隐藏进度条及播放控件', 'zib_language'),
        'gutenberg_initial_volume'                => __('初始音量', 'zib_language'),
        'gutenberg_fixed_aspect_ratio'            => __('固定长宽比例', 'zib_language'),
        'gutenberg_height_of_width'               => __('高度为宽度的%1$s%%', 'zib_language'),
        'gutenberg_height_ratio_auto'             => __('为0则为不固定，长宽比例自动与视频比例同步', 'zib_language'),
        'gutenberg_episode_n'                     => __('第%1$s集', 'zib_language'),
        // 超级嵌入 / 剧集嵌入
        'gutenberg_super_embed'                   => __('超级嵌入', 'zib_language'),
        'gutenberg_super_embed_desc'              => __('在文章中嵌入其他在线内容，通常用于嵌入其它网站的视频播放器或音乐播放器，也可以嵌入其它任意在线内容', 'zib_language'),
        'gutenberg_embed_online_content'          => __('嵌入在线内容', 'zib_language'),
        'gutenberg_embed_url_hint_multi'          => __('请输入需要嵌入的链接，或者直接粘贴iframe嵌入代码以自动提取链接（如需插入多个嵌入，请使用“zibll剧集嵌入”）', 'zib_language'),
        'gutenberg_enter_url_or_embed_code'       => __('请输入链接或粘贴嵌入代码', 'zib_language'),
        'gutenberg_aspect_ratio_settings'         => __('长宽比例设置', 'zib_language'),
        'gutenberg_ratio_landscape_4_1'           => __('横版-4:1', 'zib_language'),
        'gutenberg_ratio_landscape_3_1'           => __('横版-3:1', 'zib_language'),
        'gutenberg_ratio_landscape_5_2'           => __('横版-5:2', 'zib_language'),
        'gutenberg_ratio_landscape_2_1'           => __('横版-2:1', 'zib_language'),
        'gutenberg_ratio_landscape_16_9'          => __('横版-16:9', 'zib_language'),
        'gutenberg_ratio_landscape_5_3'           => __('横版-5:3', 'zib_language'),
        'gutenberg_ratio_landscape_4_3'           => __('横版-4:3', 'zib_language'),
        'gutenberg_ratio_landscape_5_4'           => __('横版-5:4', 'zib_language'),
        'gutenberg_ratio_landscape_8_7'           => __('横版-8:7', 'zib_language'),
        'gutenberg_ratio_square_1_1'              => __('正方形-1:1', 'zib_language'),
        'gutenberg_ratio_portrait_7_8'            => __('竖版-7:8', 'zib_language'),
        'gutenberg_ratio_portrait_4_5'            => __('竖版-4:5', 'zib_language'),
        'gutenberg_ratio_portrait_3_4'            => __('竖版-3:4', 'zib_language'),
        'gutenberg_ratio_portrait_3_5'            => __('竖版-3:5', 'zib_language'),
        'gutenberg_ratio_portrait_1_2'            => __('竖版-1:2', 'zib_language'),
        'gutenberg_ratio_portrait_2_5'            => __('竖版-2:5', 'zib_language'),
        'gutenberg_ratio_portrait_1_3'            => __('竖版-1:3', 'zib_language'),
        'gutenberg_allow_fullscreen'              => __('允许内容全屏', 'zib_language'),
        'gutenberg_embed_series'                  => __('剧集嵌入', 'zib_language'),
        'gutenberg_embed_series_desc'             => __('在文章中嵌入多个其他在线内容可切换显示，通常用于嵌入其它网站的视频播放器或音乐播放器，也可以嵌入其它任意在线内容', 'zib_language'),
        'gutenberg_enter_embed_url'               => __('请输入需要嵌入的链接，或者直接粘贴iframe嵌入代码以自动提取链接', 'zib_language'),
        'gutenberg_embed_series_module'           => __('Zibll剧集嵌入模块', 'zib_language'),
        'gutenberg_embed_series_hint'             => __('嵌入多剧集，支持其他网站的视频播放器或音乐播放器，也可为其它任意在线内容，如果仅需单个嵌入请使用“Zibll超级嵌入”模块', 'zib_language'),
        // 视频
        'gutenberg_video'                         => __('视频', 'zib_language'),
        'gutenberg_video_desc'                    => __('在文章中插入视频，支持本地视频以及m3u8、mpd、flv等流媒体格式', 'zib_language'),
        'gutenberg_video_module'                  => __('Zibll视频模块', 'zib_language'),
        'gutenberg_video_hint'                    => __('支持本地视频以及m3u8、mpd、flv等流媒体格式，如需插入多剧集的视频请使用“Zibll剧集视频”模块', 'zib_language'),
        'gutenberg_select_local_video'            => __('选择本地视频', 'zib_language'),
        // 亮点
        'gutenberg_feature'                       => __('亮点', 'zib_language'),
        'gutenberg_feature_desc'                  => __('包含图标和简介的亮点介绍，建议4个一组', 'zib_language'),
        'gutenberg_enter_icon_class'              => __('请输入图标class代码...', 'zib_language'),
        'gutenberg_enter_feature_title'           => __('请输入亮点标题...', 'zib_language'),
        'gutenberg_enter_feature_note'            => __('请输入亮点简介...', 'zib_language'),
        'gutenberg_enter_fa_icon_class'           => __('请输入FA图标class代码：', 'zib_language'),
        'gutenberg_icon_usage'                    => __('图标使用说明', 'zib_language'),
        'gutenberg_icon_usage_desc'               => __('图标使用Font Awesome图标库v4.7版本，你可以搜索Font Awesome或者在以下网站找到全部图标代码', 'zib_language'),
        'gutenberg_font_awesome'                  => __('Font Awesome图标库', 'zib_language'),
        'gutenberg_fa_icon_class'                 => __('FA图标class代码：', 'zib_language'),
        'gutenberg_select_icon_color'             => __('选择图标颜色：', 'zib_language'),
        // 设定颜色
        'gutenberg_set_color'                     => __('设定颜色', 'zib_language'),
        'gutenberg_custom_color'                  => __('自定义颜色', 'zib_language'),
        'gutenberg_color'                         => __('颜色', 'zib_language'),
        'gutenberg_bg_color'                      => __('背景色', 'zib_language'),
        'gutenberg_select_text_color'             => __('请选择文字颜色', 'zib_language'),
        'gutenberg_select_bg_color'               => __('请选择背景颜色', 'zib_language'),
        // 弹窗
        'gutenberg_modal'                         => __('弹窗', 'zib_language'),
        'gutenberg_modal_desc'                    => __('一个弹出框、模态框，默认不会显示，通过按钮让它弹出', 'zib_language'),
        'gutenberg_enter_title_dots'              => __('请输入标题...', 'zib_language'),
        'gutenberg_button_1'                      => __('按钮1', 'zib_language'),
        'gutenberg_button_2'                      => __('按钮2', 'zib_language'),
        'gutenberg_usage_tutorial'                => __('使用教程', 'zib_language'),
        'gutenberg_modal_usage_desc'              => __('模态框在页面中默认不会显示，需要一个触发按钮，将以下代码复制后填入任意链接的url中即可触发此模态框的弹出', 'zib_language'),
        'gutenberg_code_copied'                   => __('代码已复制', 'zib_language'),
        'gutenberg_click_copy_code'               => __('点击复制代码', 'zib_language'),
        'gutenberg_modal_settings'                => __('模态框设置', 'zib_language'),
        'gutenberg_width_select'                  => __('宽度选择', 'zib_language'),
        'gutenberg_width_default'                 => __('默认中等宽度', 'zib_language'),
        'gutenberg_width_xs'                      => __('超小宽度', 'zib_language'),
        'gutenberg_width_sm'                      => __('小型宽度', 'zib_language'),
        'gutenberg_width_lg'                      => __('更大宽度', 'zib_language'),
        'gutenberg_enable_button_1'               => __('开启按钮1', 'zib_language'),
        'gutenberg_enable_button_2'               => __('开启按钮2', 'zib_language'),
        // 折叠框
        'gutenberg_accordion'                     => __('折叠框', 'zib_language'),
        'gutenberg_accordion_desc'                => __('手风琴折叠框，可以插入任意内容，点击标题可切换内容显示和隐藏', 'zib_language'),
        'gutenberg_enter_accordion_title'         => __('请输入折叠框标题...', 'zib_language'),
        'gutenberg_default_expand'                => __('默认展开', 'zib_language'),
        'gutenberg_default_expanded'              => __('默认为展开状态', 'zib_language'),
        'gutenberg_default_collapsed'             => __('默认为折叠状态', 'zib_language'),
        // 高亮代码
        'gutenberg_code_highlight'                => __('高亮代码', 'zib_language'),
        'gutenberg_code_highlight_desc'           => __('输入代码，将自动高亮显示', 'zib_language'),
        'gutenberg_code'                          => __('代码', 'zib_language'),
        'gutenberg_set_code_language'             => __('设置代码语言', 'zib_language'),
        'gutenberg_auto_detect'                   => __('自动识别', 'zib_language'),
        'gutenberg_enter_code'                    => __('请输入代码...', 'zib_language'),
        'gutenberg_code_language'                 => __('代码语言', 'zib_language'),
        'gutenberg_code_settings'                 => __('代码设置', 'zib_language'),
        'gutenberg_select_theme'                  => __('选择主题', 'zib_language'),
        'gutenberg_follow_theme'                  => __('跟随主题设置', 'zib_language'),
        'gutenberg_show_line_numbers'             => __('显示行号', 'zib_language'),
        'gutenberg_show'                          => __('显示', 'zib_language'),
        'gutenberg_hide'                          => __('隐藏', 'zib_language'),
        'gutenberg_start_line_number'             => __('起始行号', 'zib_language'),
        'gutenberg_line_number_example'           => __('输入行号。例：12', 'zib_language'),
        'gutenberg_highlight_lines'               => __('高亮行号', 'zib_language'),
        'gutenberg_highlight_lines_format'        => __('格式：1,2,20-22', 'zib_language'),
        'gutenberg_code_group'                    => __('代码组', 'zib_language'),
        'gutenberg_code_group_desc'               => __('如果需要加入代码组，请填写下面设置，相同组ID的代码将合并为代码组显示', 'zib_language'),
        'gutenberg_code_title'                    => __('代码标题', 'zib_language'),
        'gutenberg_code_group_title'              => __('加入组之后显示的标题', 'zib_language'),
        'gutenberg_custom_group_id'               => __('自定义组id', 'zib_language'),
        'gutenberg_custom_group_id_placeholder'   => __('自定义组的id', 'zib_language'),
        'gutenberg_language_label'                => __('语言：', 'zib_language'),
        'gutenberg_theme_label'                   => __(' · 主题：', 'zib_language'),
        'gutenberg_follow_theme_short'            => __('跟随主题', 'zib_language'),
        // 标题
        'gutenberg_heading'                       => __('标题', 'zib_language'),
        'gutenberg_heading_desc'                  => __('和主题样式匹配的文章标题，可自定义颜色', 'zib_language'),
        'gutenberg_enter_heading'                 => __('请输入标题...', 'zib_language'),
        'gutenberg_heading_level'                 => __('标题等级', 'zib_language'),
        'gutenberg_heading_color_hint'            => __('默认颜色为主题高亮颜色，如需要自定义颜色，请在下方选择颜色', 'zib_language'),
        // 隐藏内容
        'gutenberg_hidden_content'                => __('隐藏内容', 'zib_language'),
        'gutenberg_hidden_content_desc'           => __('隐藏文章部分内容，多种隐藏可见模式(评论可见、付费阅读、登录可见、密码验证、会员可见)', 'zib_language'),
        'gutenberg_comment_visible'               => __('评论可见', 'zib_language'),
        'gutenberg_paid_reading'                  => __('付费阅读', 'zib_language'),
        'gutenberg_login_visible'                 => __('登录可见', 'zib_language'),
        'gutenberg_password_verify'               => __('密码验证', 'zib_language'),
        'gutenberg_all_vip_visible'               => __('所有会员可见', 'zib_language'),
        'gutenberg_vip2_visible'                  => __('二级会员可见', 'zib_language'),
        'gutenberg_hidden_mode_select'            => __('隐藏模式选择', 'zib_language'),
        'gutenberg_hidden_content_title'          => __('【 隐藏内容 】- 【 %1$s 】', 'zib_language'),
        'gutenberg_hidden_content_settings'       => __('隐藏内容设置', 'zib_language'),
        'gutenberg_hidden_visible_mode'           => __('隐藏可见模式', 'zib_language'),
        'gutenberg_paid_reading_hint'             => __('付费阅读：请配合底部 付费功能-付费阅读 功能使用', 'zib_language'),
        'gutenberg_set_password'                  => __('设置密码', 'zib_language'),
        'gutenberg_enter_password'                => __('请输入密码...', 'zib_language'),
        'gutenberg_remind_text'                   => __('提醒文案', 'zib_language'),
        'gutenberg_enter_remind_content'          => __('请输入提醒内容...', 'zib_language'),
        'gutenberg_remind_image'                  => __('提醒图片', 'zib_language'),
        'gutenberg_replace_remind_image'          => __('替换提醒图片', 'zib_language'),
        'gutenberg_add_remind_image'              => __('添加图片提醒', 'zib_language'),
        'gutenberg_password_remind_hint'          => __('通过提醒文案和提醒图片设置，可实现引导用户关注微信公众号获取密码引流等功能。', 'zib_language'),
        'gutenberg_password_unique_hint'          => __('注意：相同密码的块一篇文章只能添加一个，如需同一篇文章添加多个此模块，请设置不同密码', 'zib_language'),
        'gutenberg_hidden_no_nest_hint'           => __('注意：此模块不能嵌套使用', 'zib_language'),
        // 文章列表
        'gutenberg_post_list'                     => __('文章列表', 'zib_language'),
        'gutenberg_post_list_desc'                => __('以列表的形式显示多篇文章', 'zib_language'),
        'gutenberg_topics'                        => __('专题', 'zib_language'),
        'gutenberg_category'                      => __('分类', 'zib_language'),
        'gutenberg_taxonomy_id_select'            => __('输入ID以选择或排除%1$s', 'zib_language'),
        'gutenberg_taxonomy_limit'                => __('%1$s限制', 'zib_language'),
        'gutenberg_taxonomy_id_help'              => __('请填写对应的%1$sID，多个ID请用英文逗号隔开。如：1,2,3。支持负数进行排除，例如：-1,-2,-3。', 'zib_language'),
        'gutenberg_orderby'                       => __('排序方式', 'zib_language'),
        'gutenberg_post_title'                    => __('文章标题', 'zib_language'),
        'gutenberg_recent_update'                 => __('最近更新', 'zib_language'),
        'gutenberg_latest_publish'                => __('最新发布', 'zib_language'),
        'gutenberg_by_comments'                   => __('按评论数', 'zib_language'),
        'gutenberg_by_likes'                      => __('按点赞数', 'zib_language'),
        'gutenberg_by_views'                      => __('按浏览数', 'zib_language'),
        'gutenberg_by_favorites'                  => __('按收藏数', 'zib_language'),
        'gutenberg_sale_price'                    => __('销售价格', 'zib_language'),
        'gutenberg_points_price'                  => __('积分售价', 'zib_language'),
        'gutenberg_sales_volume'                  => __('销售数量', 'zib_language'),
        'gutenberg_random_order'                  => __('按随机排序', 'zib_language'),
        'gutenberg_list_style'                    => __('列表样式', 'zib_language'),
        'gutenberg_text_list'                     => __('文本列表', 'zib_language'),
        'gutenberg_mini_card'                     => __('迷你卡片', 'zib_language'),
        'gutenberg_per_page'                      => __('每页', 'zib_language'),
        'gutenberg_display_count'                 => __('显示数量', 'zib_language'),
        'gutenberg_enter_display_count'           => __('请输入显示数量', 'zib_language'),
        'gutenberg_pagination'                    => __('翻页按钮', 'zib_language'),
        'gutenberg_not_show'                      => __('不显示', 'zib_language'),
        'gutenberg_ajax_pagination'               => __('AJAX追加列表翻页', 'zib_language'),
        'gutenberg_number_pagination'             => __('数字翻页按钮', 'zib_language'),
        'gutenberg_post_list_settings'            => __('文章列表设置', 'zib_language'),
        // 文章卡片
        'gutenberg_post_card'                     => __('文章/帖子', 'zib_language'),
        'gutenberg_post_card_desc'                => __('以卡片的形式显示单个文章或帖子', 'zib_language'),
        'gutenberg_post_card_hint'                => __('以卡片的形式显示文章或帖子', 'zib_language'),
        'gutenberg_search_post_id'                => __('请输入文章ID或搜索文章', 'zib_language'),
        'gutenberg_post_id_invalid'               => __('文章ID无效', 'zib_language'),
        // 引言
        'gutenberg_quote'                         => __('引言', 'zib_language'),
        'gutenberg_quote_desc'                    => __('几种不同的引言框', 'zib_language'),
        'gutenberg_enter_content'                 => __('请输入内容...', 'zib_language'),
        'gutenberg_quote_color_hint'              => __('默认为主题颜色，如果需自定义请在下方选择颜色（引言默认透明度为70%，请不要选择较浅的颜色，并请注意深色主题的显示效果）', 'zib_language'),
        // 提醒框
        'gutenberg_alert'                         => __('提醒框', 'zib_language'),
        'gutenberg_alert_desc'                    => __('几种不同的提醒框，可选择关闭按钮', 'zib_language'),
        'gutenberg_alert_closable'                => __('提醒框可关闭', 'zib_language'),
        'gutenberg_alert_settings'                => __('提醒框设置', 'zib_language'),
        'gutenberg_alert_type'                    => __('提醒框类型', 'zib_language'),
        // 按钮组
        'gutenberg_buttons'                       => __('按钮组', 'zib_language'),
        'gutenberg_buttons_desc'                  => __('多种样式的按钮', 'zib_language'),
        'gutenberg_button'                        => __('按钮', 'zib_language'),
        'gutenberg_button_count'                  => __('按钮数量', 'zib_language'),
        'gutenberg_count_1'                       => __('1个', 'zib_language'),
        'gutenberg_count_2'                       => __('2个', 'zib_language'),
        'gutenberg_count_3'                       => __('3个', 'zib_language'),
        'gutenberg_count_4'                       => __('4个', 'zib_language'),
        'gutenberg_count_5'                       => __('5个', 'zib_language'),
        'gutenberg_button_settings'               => __('按钮设置', 'zib_language'),
        'gutenberg_button_radius'                 => __('按钮圆角', 'zib_language'),
        // 幻灯片
        'gutenberg_carousel'                      => __('幻灯片', 'zib_language'),
        'gutenberg_carousel_desc'                 => __('选择图片生成幻灯片', 'zib_language'),
        'gutenberg_switch_interval'               => __('切换时间（秒）', 'zib_language'),
        'gutenberg_limit_max_width'               => __('限制最大宽度', 'zib_language'),
        'gutenberg_center_display'                => __('居中显示', 'zib_language'),
        'gutenberg_max_width'                     => __('最大宽度', 'zib_language'),
        'gutenberg_switch_animation'              => __('切换动画', 'zib_language'),
        'gutenberg_slide'                         => __('滑动', 'zib_language'),
        'gutenberg_fade'                          => __('淡出淡入', 'zib_language'),
        'gutenberg_3d_cube'                       => __('3D方块', 'zib_language'),
        'gutenberg_3d_slide'                      => __('3D滑入', 'zib_language'),
        'gutenberg_3d_flip'                       => __('3D翻转', 'zib_language'),
        'gutenberg_carousel_settings'             => __('幻灯片设置', 'zib_language'),
        'gutenberg_keep_aspect_ratio'             => __('保持长宽比例', 'zib_language'),
        'gutenberg_disabled'                      => __('禁用', 'zib_language'),
        'gutenberg_carousel_ratio_hint'           => __('如果幻灯片内的图片尺寸不一致，建议开启限制最大宽度，再结合长宽比例能显示更好的效果', 'zib_language'),
        // 无缝图
        'gutenberg_seamless_image'                => __('无缝图', 'zib_language'),
        'gutenberg_seamless_image_desc'           => __('多张图片组成的无缝大图，更加适合于商品详情图片等布局', 'zib_language'),
        'gutenberg_width_full_container'          => __('宽度填满容器', 'zib_language'),
        'gutenberg_seamless_image_settings'       => __('无缝图设置', 'zib_language'),
        'gutenberg_seamless_image_full_hint'      => __('启用此配置后，图片宽度会填满容器，如果是在商城中使用，开启商城的内容全屏布局后，则宽度会填满屏幕', 'zib_language'),
        // 商品
        'gutenberg_product'                       => __('商品', 'zib_language'),
        'gutenberg_product_desc'                  => __('以卡片的形式显示商城的商品', 'zib_language'),
        'gutenberg_search_product_id'             => __('请输入商品ID或输入关键词搜索商品', 'zib_language'),
        'gutenberg_product_id_invalid'            => __('商品ID无效', 'zib_language'),
        'gutenberg_shop_product'                  => __('[商城]商品', 'zib_language'),
        'gutenberg_product_card_desc'             => __('以卡片的形式显示商城的商品（注意：如未开启商城功能，前台则不会显示）', 'zib_language'),
    );
}
