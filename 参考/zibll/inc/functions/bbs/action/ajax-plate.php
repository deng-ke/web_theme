<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:59:52
 * @LastEditTime : 2026-04-21 16:20:45
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|AJAX执行类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//edit选择版块的tab
function zib_bbs_ajax_plate_select_lists_tab()
{
    zib_ajax_send_ajaxpager(zib_bbs_edit::plate_select_lists_tab());
}
add_action('wp_ajax_plate_select_lists_tab', 'zib_bbs_ajax_plate_select_lists_tab');
add_action('wp_ajax_nopriv_plate_select_lists_tab', 'zib_bbs_ajax_plate_select_lists_tab');

//执行删除版块或帖子
function zib_bbs_ajax_plate_or_posts_delete()
{
    $plate_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $plate = get_post($plate_id);

    if (!$plate_id || !$plate) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => __('参数传入错误', 'zib_language'))));
        exit;
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('save_bbs');

    global $zib_bbs;

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;

    if ('plate_delete' == $action) {
        //执行删除版块
        $goto = zib_bbs_get_home_url();

        $name = $zib_bbs->plate_name;
        if (!zib_bbs_current_user_can('plate_delete', $plate_id)) {
            echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('您没有删除此%s的权限', 'zib_language'), $name))));
            exit;
        }

        $posts_action = isset($_REQUEST['posts_action']) ? $_REQUEST['posts_action'] : 'trash';
        if ('move' == $posts_action) {
            $new_plate_id = isset($_REQUEST['plate']) ? (int) $_REQUEST['plate'] : 0;
            if (!$new_plate_id || $new_plate_id == $plate_id) {
                zib_send_json_error(sprintf(__('请选择需要移动到的新%s', 'zib_language'), $name));
            }

            $new_obj = get_post($new_plate_id);
            if (empty($new_obj->ID)) {
                zib_send_json_error(sprintf(__('所选新%s不存在或已删除', 'zib_language'), $name));
            }
            if ('trash' === $new_obj->post_status) {
                zib_send_json_error(sprintf(__('所选新%s已删除，请重新选择', 'zib_language'), $name));
            }

            //执行批量移动版块
            zib_bbs_plates_move($new_plate_id, $plate_id);
        }
    } else {
        //执行删除帖子
        $goto = get_permalink(zib_bbs_get_plate_id($plate_id));

        $name = $zib_bbs->posts_name;
        if (!zib_bbs_current_user_can('posts_delete', $plate_id)) {
            echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('您没有删除此%s的权限', 'zib_language'), $name))));
            exit;
        }

        if (!empty($_REQUEST['msg_s']) && empty($_REQUEST['msg'])) {
            zib_send_json_error(__('请填写删除原因', 'zib_language'));
        }
    }

    //执行删除
    $post_data = wp_trash_post($plate_id);
    if (isset($post_data->ID)) {
        do_action('bbs_' . $action, $post_data);
    }

    echo(json_encode(array('error' => 0, 'ys' => '', 'reload' => true, 'goto' => $goto, 'msg' => sprintf(__('该%s已移至回收站', 'zib_language'), $name))));
    exit;
}
add_action('wp_ajax_plate_delete', 'zib_bbs_ajax_plate_or_posts_delete');
add_action('wp_ajax_posts_delete', 'zib_bbs_ajax_plate_or_posts_delete');

//编辑版块的弹窗
function zib_bbs_ajax_plate_edit_modal()
{
    $plate_id = isset($_REQUEST['plate_id']) ? (int) $_REQUEST['plate_id'] : 0;
    $cat_id   = isset($_REQUEST['cat_id']) ? (int) $_REQUEST['cat_id'] : 0;

    echo zib_bbs_edit::plate($plate_id, $cat_id);
    exit;
}
add_action('wp_ajax_plate_edit_modal', 'zib_bbs_ajax_plate_edit_modal');

//获取设置发布限制的模态框
function zib_bbs_ajax_set_add_limit_modal()
{
    $id   = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';

    //权限检查
    if (!zib_bbs_current_user_can($type . '_set_add_limit', $id)) {
        zib_ajax_notice_modal('danger', __('您没有此操作权限', 'zib_language'));
    }

    //发帖权限设置
    $add_limit_html = zib_bbs_edit::add_limit_modal($type, $id, true);

    if (!$add_limit_html) {
        zib_ajax_notice_modal('warning', __('没有可配置的选项', 'zib_language'));
    }

    echo $add_limit_html;
    exit;
}
add_action('wp_ajax_set_add_limit_modal', 'zib_bbs_ajax_set_add_limit_modal');

//保存发布权限
function zib_bbs_ajax_save_add_limit()
{
    $id        = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $type      = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';
    $add_limit = isset($_REQUEST['add_limit']) ? (string) $_REQUEST['add_limit'] : '0';

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //权限检查
    if (!zib_bbs_current_user_can($type . '_set_add_limit', $id)) {
        zib_send_json_error(__('您没有此操作权限', 'zib_language'));
    }

    //执行保存设置
    if ('plate_cat' === $type) {
        update_term_meta($id, 'add_limit', $add_limit);
    } else {
        update_post_meta($id, 'add_limit', $add_limit);

        if ($add_limit === 'follow') {
            zib_bbs_ajax_plate_edit_follow_pay($id, 0);
        }
    }

    zib_send_json_success(array('msg' => __('设置成功', 'zib_language'), 'hide_modal' => true));
    exit;
}
add_action('wp_ajax_save_add_limit', 'zib_bbs_ajax_save_add_limit');

//编辑或者添加版块
function zib_bbs_ajax_save_plate()
{
    global $zib_bbs;
    $name = $zib_bbs->plate_name;

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('save_bbs');

    //用户基本权限验证
    $plate_id = isset($_REQUEST['plate_id']) ? (int) $_REQUEST['plate_id'] : 0;
    if ($plate_id && !zib_bbs_current_user_can('plate_edit', $plate_id)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('您没有编辑此%s的权限', 'zib_language'), $name))));
        exit;
    }

    if (!$plate_id && !zib_bbs_current_user_can('plate_add')) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('您没有创建%s的权限', 'zib_language'), $name))));
        exit;
    }

    $cat = !empty($_POST['cat']) ? (int) $_POST['cat'] : false;
    if (!$cat) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('您未选择%s分类或暂无权限', 'zib_language'), $name))));
        exit();
    }

    if (!zib_bbs_current_user_can('select_plate_cat', $cat, $plate_id)) {
        zib_send_json_error(sprintf(__('您没有在此分类创建%s的权限，请重新选择%s分类', 'zib_language'), $name, $name));
    }

    $title   = !empty($_POST['title']) ? strip_tags(trim($_POST['title'])) : false;
    $content = !empty($_POST['desc']) ? strip_tags(trim($_POST['desc'])) : false;

    //标题验证
    if (!$title) {
        echo(json_encode(array('error' => 1, 'ys' => 'warning', 'msg' => sprintf(__('请输入%s标题', 'zib_language'), $name))));
        exit();
    }
    if (zib_new_strlen($title) > 10) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('标题太长，不能超过10个字', 'zib_language'))));
        exit();
    }
    if (zib_new_strlen($title) < 2) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('标题太短！', 'zib_language'))));
        exit();
    }

    //简介验证
    if (!$content) {
        echo(json_encode(array('error' => 1, 'ys' => 'warning', 'msg' => sprintf(__('请输入%s简介', 'zib_language'), $name))));
        exit();
    }
    if (zib_new_strlen($content) > 60) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('简介太长，不能超过60个字', 'zib_language'))));
        exit();
    }
    if (zib_new_strlen($content) < 6) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => __('简介太短！', 'zib_language'))));
        exit();
    }

    //类型权限判断
    $type = !empty($_POST['type']) ? $_POST['type'] : '';
    if ($type && !isset(zib_bbs_get_user_can_plate_type($plate_id)[$type])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('您暂无选择该%s类型的权限', 'zib_language'), $name))));
        exit();
    }

    //图像判断:如果没有ID则必须要有图片
    if (empty($_FILES['file']) && !$plate_id) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => sprintf(__('请选择%s图像', 'zib_language'), $name))));
        exit();
    }

    //图片api审核
    if (!empty($_FILES['file']) && _pz('audit_upload_img')) {
        ZibAudit::ajax_image('file');
    }

    //文字API审核
    if (_pz('audit_bbs_plate')) {
        $is_audit = ZibAudit::ajax_text($title . $content);
    }

    //版块状态，版块无需审核
    $post_status = 'publish';

    $post_args = array(
        'ID'           => $plate_id,
        'post_type'    => 'plate',
        'post_title'   => $title,
        'post_status'  => $post_status,
        'post_content' => '',
        'post_excerpt' => $content,
        'meta_input'   => array(
            'plate_type' => $type,
        ),
    );

    if (!$plate_id) {
        //新建时候，添加作者
        $cuid                     = get_current_user_id();
        $post_args['post_author'] = $cuid;
    } else {
        $post_obj = get_post($plate_id, ARRAY_A);
        if (isset($post_obj['ID'])) {
            $post_args = array_merge($post_obj, $post_args);
        }
    }

    //添加保存前的挂钩
    do_action('zib_pre_insert_post', $post_args);

    $in_id = wp_insert_post($post_args, 1);
    if (is_wp_error($in_id)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $in_id->get_error_message())));
        exit();
    }

    //执行保存分类
    wp_set_post_terms($in_id, (array) $cat, 'plate_cat');

    //图片处理
    $image_url = '';
    if (!empty($_FILES['file']) && $in_id) {
        //开始上传图像
        $img_id = zib_php_upload('file', $in_id, false);
        if (!empty($img_id['error'])) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $img_id['msg'])));
            exit();
        }
        $image_urls = wp_get_attachment_image_src($img_id, 'medium');
        $image_url  = isset($image_urls[0]) ? $image_urls[0] : '';
        zib_update_post_meta($in_id, 'thumbnail_url', $image_url);
    }

    //执行保存发布限制
    if (isset($_POST['add_limit'])) {
        update_post_meta($in_id, 'add_limit', (string) $_POST['add_limit']);
    }

    //执行保存阅读权限
    zib_bbs_ajax_plate_edit_allow_view($in_id, 0);

    //执行保存付费关注
    zib_bbs_ajax_plate_edit_follow_pay($in_id, 0);

    $text = $plate_id ? __('修改', 'zib_language') : __('创建', 'zib_language');
    $goto = get_permalink($in_id);

    $data = array(
        'image_url'  => $image_url,
        'url'        => $goto,
        'msg'        => sprintf(__('%1$s%2$s成功', 'zib_language'), $name, $text),
        'post'       => get_post($in_id),
        'id'         => $in_id,
        'type'       => ($plate_id ? 'update' : 'add'),
        'hide_modal' => true,
    );
    zib_send_json_success($data);
}
add_action('wp_ajax_save_plate', 'zib_bbs_ajax_save_plate');

//版块设置查看权限的模态框
function zib_bbs_ajax_plate_allow_view_set_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    global $zib_bbs;
    $name = $zib_bbs->plate_name;

    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', __('内容不存在或参数传入错误', 'zib_language'));
    }
    if (!zib_bbs_current_user_can('plate_set_allow_view', $id)) {
        zib_ajax_notice_modal('danger', __('权限不足', 'zib_language'));
    }
    $allow_view_set = zib_bbs_edit::plate_allow_view_set_content($id, 'mt20', true);
    $header         = zib_get_modal_colorful_header('jb-yellow', '<i class="fa fa-eye-slash"></i>', sprintf(__('%s查看限制', 'zib_language'), $name));
    $hidden_html    = '';
    $hidden_html .= '<input type="hidden" name="action" value="plate_edit_allow_view">';
    $hidden_html .= '<input type="hidden" name="plate_id" value="' . $id . '">';

    $footer = '<div class="mt20 but-average">';
    $footer .= $hidden_html;
    $footer .= '<button class="but jb-yellow padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>' . __('确认提交', 'zib_language') . '</button>';
    $footer .= '</div>';

    echo '<form class="dependency-box">';
    echo $header;
    echo '<div class="mini-scrollbar scroll-y max-vh5"><div class="em09 muted-2-color mb20">' . sprintf(__('为%s设置阅读限制，只有满足条件的用户才能查看此版块的%s内容', 'zib_language'), $name, $zib_bbs->posts_name) . '</div>' . $allow_view_set . '</div>';
    echo $footer;
    echo '</form>';
    exit;
}
add_action('wp_ajax_plate_allow_view_set_modal', 'zib_bbs_ajax_plate_allow_view_set_modal');

//保存阅读权限
function zib_bbs_ajax_plate_edit_allow_view($plate_id = 0, $echo_success = true)
{
    global $zib_bbs;
    $name = $zib_bbs->plate_name;

    if (!isset($_REQUEST['allow_view']) && !isset($_REQUEST['allow_view_roles'])) {
        return;
    }

    $plate_id = $plate_id ? $plate_id : (!empty($_REQUEST['plate_id']) ? $_REQUEST['plate_id'] : 0);

    $allow_view       = !empty($_REQUEST['allow_view']) ? $_REQUEST['allow_view'] : '';
    $allow_view_roles = !empty($_REQUEST['allow_view_roles']) ? (array) $_REQUEST['allow_view_roles'] : array();
    $allow_view_roles = array_filter($allow_view_roles);

    if ('roles' == $allow_view) {
        if (!$allow_view_roles) {
            zib_send_json_error(__('请至少选择一项允许查看的用户类型', 'zib_language'));
        }
    }

    update_post_meta($plate_id, 'allow_view', $allow_view);
    update_post_meta($plate_id, 'allow_view_roles', $allow_view_roles);

    if ($allow_view === 'follow') {
        //执行保存付费关注 
        zib_bbs_ajax_plate_edit_follow_pay($plate_id, 0);
    }

    if ($echo_success) {
        zib_send_json_success(array('msg' => sprintf(__('%s查看权限已保存', 'zib_language'), $name), 'reload' => 1, 'allow_view_roles' => $allow_view_roles, 'allow_view' => $allow_view, 'plate_id' => $plate_id));
    }
}
add_action('wp_ajax_plate_edit_allow_view', 'zib_bbs_ajax_plate_edit_allow_view');

//版块设置付费关注的模态框
function zib_bbs_ajax_plate_follow_pay_set_modal()
{
    $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    global $zib_bbs;
    $name = $zib_bbs->plate_name;

    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', __('内容不存在或参数传入错误', 'zib_language'));
    }
    if (!zib_bbs_current_user_can('plate_set_follow_pay', $id)) {
        zib_ajax_notice_modal('danger', __('权限不足', 'zib_language'));
    }
    $follow_pay_set = zib_bbs_edit::plate_follow_pay_set_content($id);
    $header         = zib_get_modal_colorful_header('jb-yellow', '<i class="fa fa-money"></i>', sprintf(__('%1$s付费%2$s', 'zib_language'), $name, $zib_bbs->plate_follow_name));
    $hidden_html    = '';
    $hidden_html .= '<input type="hidden" name="action" value="plate_edit_follow_pay">';
    $hidden_html .= '<input type="hidden" name="plate_id" value="' . $id . '">';
    $footer = '<div class="mt20 but-average">';
    $footer .= $hidden_html;
    $footer .= '<button class="but jb-yellow padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>' . __('确认提交', 'zib_language') . '</button>';
    $footer .= '</div>';
    echo '<form class="dependency-box">';
    echo $header;
    echo '<div class="mini-scrollbar scroll-y max-vh5"><div class="mb10 font-bold">' . sprintf(__('为%1$s[%2$s]设置付费%3$s', 'zib_language'), $name, $post->post_title, $zib_bbs->plate_follow_name) . '</div><div class="em09 muted-2-color mb6">' . sprintf(__('启用后，用户需付费后才能%1$s此%2$s，同时建议与发布权限、查看权限配置中【%3$s%2$s】选项配合使用', 'zib_language'), $zib_bbs->plate_follow_name, $name, $zib_bbs->plate_follow_name) . '</div>' . $follow_pay_set . '</div>';
    echo $footer;
    echo '</form>';
    exit;
}
add_action('wp_ajax_plate_follow_pay_set_modal', 'zib_bbs_ajax_plate_follow_pay_set_modal');

//保存付费关注
function zib_bbs_ajax_plate_edit_follow_pay($plate_id = 0, $echo_success = true)
{
    global $zib_bbs;
    $name = $zib_bbs->plate_name;

    if (!isset($_REQUEST['follow_pay'])) {
        return;
    }

    $plate_id = $plate_id ? $plate_id : (!empty($_REQUEST['plate_id']) ? $_REQUEST['plate_id'] : 0);

    $follow_pay_s         = !empty($_REQUEST['follow_pay_s']) ? 1 : 0;
    $follow_pay           = !empty($_REQUEST['follow_pay']) ? $_REQUEST['follow_pay'] : array();
    $plate_follow_pay_opt = _pz('bbs_plate_follow_pay_opt');
    $vip_input_s          = !empty($plate_follow_pay_opt['vip_price_s']) ? true : false;
    $price_limit          = !empty($plate_follow_pay_opt['price_limit']) ? $plate_follow_pay_opt['price_limit'] : [];

    $data = array(
        's'            => $follow_pay_s,
        'pay_modo'     => '0',
        'pay_price'    => 0,
        'vip_1_price'  => 0,
        'vip_2_price'  => 0,
        'points_price' => 0,
        'vip_1_points' => 0,
        'vip_2_points' => 0,
    );
    $data = $follow_pay_s ? array_merge($data, $follow_pay) : $data;

    if ($data['s']) {
        if ($data['pay_modo'] === 'points') {
            //积分价格
            $data['points_price'] = (int) $data['points_price'];
            if ($data['points_price'] <= 0) {
                zib_send_json_error(__('请设置积分价格', 'zib_language'));
            }
            //价格区间
            $points_price_limit = !empty($price_limit['points']) ? $price_limit['points'] : false;
            if ($points_price_limit) {
                $points_price_limit_min = !empty($points_price_limit['min']) ? $points_price_limit['min'] : 0;
                $points_price_limit_max = !empty($points_price_limit['max']) ? $points_price_limit['max'] : 0;
                if ($points_price_limit_min && $data['points_price'] < $points_price_limit_min) {
                    zib_send_json_error(sprintf(__('积分价格不能低于：%s', 'zib_language'), $points_price_limit_min));
                }
                if ($points_price_limit_max && $data['points_price'] > $points_price_limit_max) {
                    zib_send_json_error(sprintf(__('积分价格不能高于：%s', 'zib_language'), $points_price_limit_max));
                }
            }

            //会员价
            if ($vip_input_s) {
                $data['vip_1_points'] = (int) $data['vip_1_points'];
                $data['vip_2_points'] = (int) $data['vip_2_points'];
                if ($data['vip_1_points'] > $data['points_price']) {
                    zib_send_json_error(__('会员价不能高于普通价', 'zib_language'));
                }
                if ($data['vip_2_points'] > $data['vip_1_points']) {
                    zib_send_json_error(sprintf(__('%1$s价格不能高于%2$s价格', 'zib_language'), _pz('pay_user_vip_2_name'), _pz('pay_user_vip_1_name')));
                }
            } else {
                $data['vip_1_points'] = (int) ($data['points_price'] * $plate_follow_pay_opt['vip_1_discount'] / 100);
                $data['vip_2_points'] = (int) ($data['points_price'] * $plate_follow_pay_opt['vip_2_discount'] / 100);
            }

            //不能小于0
            $data['vip_1_points'] = $data['vip_1_points'] < 0 ? 0 : $data['vip_1_points'];
            $data['vip_2_points'] = $data['vip_2_points'] < 0 ? 0 : $data['vip_2_points'];
            //不能大于正常价
            $data['vip_1_points'] = $data['vip_1_points'] > $data['points_price'] ? $data['points_price'] : $data['vip_1_points'];
            $data['vip_2_points'] = $data['vip_2_points'] > $data['points_price'] ? $data['points_price'] : $data['vip_2_points'];

        } else {
            //现金价格
            $data['pay_price'] = round((float) $data['pay_price'], 2);
            if ($data['pay_price'] <= 0) {
                zib_send_json_error(__('请设置现金价格', 'zib_language'));
            }
            //价格区间
            $price_limit = !empty($price_limit['price']) ? $price_limit['price'] : false;
            if ($price_limit) {
                $price_limit_min = !empty($price_limit['min']) ? $price_limit['min'] : 0;
                $price_limit_max = !empty($price_limit['max']) ? $price_limit['max'] : 0;

                if ($price_limit_min && $data['pay_price'] < $price_limit_min) {
                    zib_send_json_error(sprintf(__('价格不能低于：%s', 'zib_language'), $price_limit_min));
                }
                if ($price_limit_max && $data['pay_price'] > $price_limit_max) {
                    zib_send_json_error(sprintf(__('价格不能高于：%s', 'zib_language'), $price_limit_max));
                }
            }
            //会员价
            if ($vip_input_s) {
                $data['vip_1_price'] = (float) $data['vip_1_price'];
                $data['vip_2_price'] = (float) $data['vip_2_price'];
                if ($data['vip_1_price'] > $data['pay_price']) {
                    zib_send_json_error(__('会员价不能高于普通价', 'zib_language'));
                }
                if ($data['vip_2_price'] > $data['vip_1_price']) {
                    zib_send_json_error(sprintf(__('%1$s价格不能高于%2$s价格', 'zib_language'), _pz('pay_user_vip_2_name'), _pz('pay_user_vip_1_name')));
                }
            } else {
                $data['vip_1_price'] = (float) ($data['pay_price'] * $plate_follow_pay_opt['vip_1_discount']) / 100;
                $data['vip_2_price'] = (float) ($data['pay_price'] * $plate_follow_pay_opt['vip_2_discount']) / 100;
            }
            //不能小于0
            $data['vip_1_price'] = $data['vip_1_price'] < 0 ? 0 : $data['vip_1_price'];
            $data['vip_2_price'] = $data['vip_2_price'] < 0 ? 0 : $data['vip_2_price'];
            //不能大于正常价
            $data['vip_1_price'] = $data['vip_1_price'] > $data['pay_price'] ? $data['pay_price'] : $data['vip_1_price'];
            $data['vip_2_price'] = $data['vip_2_price'] > $data['pay_price'] ? $data['pay_price'] : $data['vip_2_price'];

            //金额格式化
            $data['pay_price']   = zib_floatval_round($data['pay_price']);
            $data['vip_1_price'] = zib_floatval_round($data['vip_1_price']);
            $data['vip_2_price'] = zib_floatval_round($data['vip_2_price']);
        }
    }

    //执行保存
    update_post_meta($plate_id, 'follow_pay', $follow_pay_s);
    zib_update_post_meta($plate_id, 'follow_pay_args', $data);

    if ($echo_success) {
        zib_send_json_success(array('msg' => sprintf(__('%1$s%2$s付费%3$s', 'zib_language'), ($follow_pay_s ? __('已开启', 'zib_language') : __('已关闭', 'zib_language')), $name, $zib_bbs->plate_follow_name), 'hide_modal' => true, 'follow_pay_s' => $follow_pay_s, 'plate_id' => $plate_id));
    }
}
add_action('wp_ajax_plate_edit_follow_pay', 'zib_bbs_ajax_plate_edit_follow_pay');

//关注版块
function zib_bbs_ajax_follow_plate()
{
    global $zib_bbs;
    $id      = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['msg' => __('请先登录', 'zib_language'), 'code' => 'no_logged']);
    }

    $post = get_post($id);
    if (empty($post->ID)) {
        wp_send_json_error(['msg' => __('内容不存在或参数传入错误', 'zib_language'), 'code' => 'no_post']);
    }

    $is_follow    = zib_get_user_meta($user_id, 'follow_plate', true);
    $is_follow    = $is_follow ? $is_follow : array();
    $is_follow_ed = in_array($id, $is_follow);

    //获取收费关注参数
    $follow_pay_opt = zib_bbs_get_plate_follow_pay_options($id);
    if ($follow_pay_opt['s']) {

        if (!$is_follow_ed) {
            //未关注：如果要付费，则弹出支付弹窗
            $modal_content = zib_bbs_get_plate_follow_pay_cashier_modal($id);
        } else {
            //已经关注：显示确认取关的确认弹窗
            $modal_content = zib_bbs_get_plate_follow_cancel_modal($id);
        }

        wp_send_json_success([
            'modal'        => $modal_content,
            'show_modal'   => true,
            'modal_config' => [
                'class'              => 'modal-mini',
                'height'             => $is_follow_ed ? 260 : 300,
                'mobile_from_bottom' => true,
                'touch_close'        => true,
            ],
        ]);
    }

    //关注板块
    wp_send_json_success(zib_bbs_follow_plate_toggle($id, $user_id));
}
add_action('wp_ajax_follow_plate', 'zib_bbs_ajax_follow_plate');
add_action('wp_ajax_nopriv_follow_plate', 'zib_bbs_ajax_follow_plate');

//确认付费关注
function zib_bbs_ajax_follow_plate_confirm()
{
    global $zib_bbs;
    $id      = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(['msg' => __('请先登录', 'zib_language'), 'code' => 'no_logged']);
    }

    $post = get_post($id);
    if (empty($post->ID)) {
        zib_send_json_error(['msg' => __('内容不存在或参数传入错误', 'zib_language'), 'code' => 'no_post']);
    }

    $is_follow    = zib_get_user_meta($user_id, 'follow_plate', true);
    $is_follow    = $is_follow ? $is_follow : array();
    $is_follow_ed = in_array($id, $is_follow);

    //获取收费关注参数
    $follow_pay_opt = zib_bbs_get_plate_follow_pay_options($id);
    if ($follow_pay_opt['s']) {
        if (!$is_follow_ed) {
            //判断是否是免费
            $price = zib_bbs_get_plate_follow_pay_price($id, $user_id);
            if ($price > 0) {
                zib_send_json_error(['msg' => sprintf(__('当前%1$s需要付费%2$s，请刷新页面后重试', 'zib_language'), $zib_bbs->plate_name, $zib_bbs->plate_follow_name)]);
            }
        } else {
            //已经关注：取消关注

        }
    }

    $data           = zib_bbs_follow_plate_toggle($id, $user_id);
    $data['reload'] = true;

    //关注板块
    zib_send_json_success($data);
}
add_action('wp_ajax_follow_plate_confirm', 'zib_bbs_ajax_follow_plate_confirm');
add_action('wp_ajax_nopriv_follow_plate_confirm', 'zib_bbs_ajax_follow_plate_confirm');
