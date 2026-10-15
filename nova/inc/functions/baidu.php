<?php
/*
 * Project       : Nova主题
 * Description   : 百度收录推送（文章发布自动推送 + 后台手动推送）
 */

if (!defined('ABSPATH')) exit;

/**
 * 推送 URL 到百度搜索资源平台
 *
 * @param array|string $urls 单个 URL 或 URL 数组
 * @param string       $type 推送类型：normal 普通收录 / daily 快速收录
 * @return array|false 推送结果（success/remain/not_same_site/not_valid 等）
 */
function nova_baidu_push_urls($urls, $type = 'normal')
{
    if (!is_array($urls)) {
        $urls = array($urls);
    }
    $urls = array_filter(array_map('esc_url_raw', $urls));
    if (empty($urls)) return false;

    $token = nova_get_setting('baidu_token');
    if (!$token) return false;

    $api = 'http://data.zz.baidu.com/urls?site=' . rawurlencode(home_url()) . '&token=' . rawurlencode($token);
    if ('daily' === $type) {
        $api .= '&type=daily';
    }

    $response = wp_remote_post($api, array(
        'timeout' => 10,
        'headers' => array('Content-Type' => 'text/plain'),
        'body'    => implode("\n", $urls),
    ));

    if (is_wp_error($response)) {
        return array(
            'success'       => 0,
            'remain'        => 0,
            'error_message' => $response->get_error_message(),
        );
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body)) {
        return array(
            'success'       => 0,
            'remain'        => 0,
            'error_message' => '响应解析失败：' . wp_remote_retrieve_body($response),
        );
    }
    return $body;
}

/**
 * 推送单篇文章（含快速收录，视设置而定）
 */
function nova_baidu_push_post($post_id)
{
    $post = get_post($post_id);
    if (!$post || 'publish' !== $post->post_status) return false;
    if (!nova_get_setting('baidu_push_s', '1')) return false;

    //已推送过的不重复推（编辑更新时不重复推送）
    $pushed = get_post_meta($post_id, 'nova_baidu_pushed', true);
    if ($pushed) return false;

    $url = get_permalink($post_id);
    $result = nova_baidu_push_urls($url, 'normal');

    //快速收录（配额用尽时自动降级，仅普通收录也可）
    if (nova_get_setting('baidu_daily_s', '1')) {
        $daily = nova_baidu_push_urls($url, 'daily');
        if (!empty($daily['success'])) {
            $result['daily'] = true;
        }
    }

    if (!empty($result['success']) || !empty($result['daily'])) {
        update_post_meta($post_id, 'nova_baidu_pushed', current_time('timestamp'));
        update_post_meta($post_id, 'nova_baidu_result', $result);
    }
    return $result;
}

/**
 * 文章发布时自动推送百度
 */
function nova_baidu_push_on_publish($new_status, $old_status, $post)
{
    if ('publish' !== $new_status || 'publish' === $old_status) return;
    if ('post' !== $post->post_type) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    nova_baidu_push_post($post->ID);
}
add_action('transition_post_status', 'nova_baidu_push_on_publish', 10, 3);

/* ============================================================
 * 后台：文章列表手动推送
 * ============================================================ */

/**
 * 文章列表行操作
 */
function nova_baidu_row_actions($actions, $post)
{
    if ('post' === $post->post_type && current_user_can('edit_post', $post->ID)) {
        $pushed = get_post_meta($post->ID, 'nova_baidu_pushed', true);
        $label  = $pushed ? __('重新推送百度', 'nova') : __('推送百度', 'nova');
        $actions['nova_baidu_push'] = '<a href="javascript:void(0);" class="nova-baidu-push" data-id="' . (int) $post->ID . '">' . esc_html($label) . '</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'nova_baidu_row_actions', 10, 2);

/**
 * 手动推送 AJAX（管理员）
 */
function nova_ajax_baidu_push()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    if (!current_user_can('manage_options')) nova_ajax_error('无权限');

    $post_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if (!$post_id) nova_ajax_error('参数错误');

    $url = get_permalink($post_id);
    $result = nova_baidu_push_urls($url, 'normal');
    if (false === $result) nova_ajax_error('未配置百度推送 token，请在「外观-自定义-页面设置」中填写');

    if (!empty($result['success'])) {
        update_post_meta($post_id, 'nova_baidu_pushed', current_time('timestamp'));
        update_post_meta($post_id, 'nova_baidu_result', $result);
        nova_ajax_success(array('remain' => isset($result['remain']) ? $result['remain'] : 0), sprintf(__('推送成功，今日剩余配额 %s 条', 'nova'), isset($result['remain']) ? $result['remain'] : '-'));
    }

    $err = isset($result['error']) ? $result['error'] : (isset($result['message']) ? $result['message'] : '');
    nova_ajax_error('推送失败' . ($err ? '：' . $err : ''));
}
add_action('wp_ajax_nova_baidu_push', 'nova_ajax_baidu_push');

/**
 * 后台加载推送按钮 JS
 */
function nova_baidu_admin_js()
{
    if (!is_admin()) return;
    ?>
    <script>
    (function ($) {
        'use strict';
        $(document).on('click', '.nova-baidu-push', function () {
            var $a = $(this);
            var id = $a.data('id');
            $a.text('推送中…');
            $.post(ajaxurl, {
                action: 'nova_baidu_push',
                nonce: '<?php echo esc_js(wp_create_nonce('nova_ajax_nonce')); ?>',
                id: id
            }, function (res) {
                $a.text('重新推送百度');
                if (res && res.success) {
                    alert(res.data && res.data.msg ? res.data.msg : '推送成功');
                } else {
                    alert(res && res.data && res.data.msg ? res.data.msg : '推送失败');
                }
            }).fail(function () {
                $a.text('重新推送百度');
                alert('网络请求失败');
            });
        });
    })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'nova_baidu_admin_js');
