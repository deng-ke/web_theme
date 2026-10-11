<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-12-21 22:54:02
 * @LastEditTime : 2026-05-05 22:02:37
 */

if (!is_super_admin()) {
    wp_die(__('您不能访问此页面', 'zib_language'), __('权限不足', 'zib_language'));
    exit;
}
if (!_pz('message_s', true)) {
    wp_die(__('此功能已关闭', 'zib_language'), __('错误', 'zib_language'));
    exit;
}

$this_url = esc_url(admin_url('users.php?page=user_messags'));
$tab      = !empty($_GET['tab']) ? $_GET['tab'] : '';
$action   = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$s        = !empty($_REQUEST['s']) ? esc_sql($_REQUEST['s']) : '';
$msg_id   = !empty($_REQUEST['msg_id']) ? (int) $_REQUEST['msg_id'] : 0;

if ($action) {
    switch ($action) {
        case 'delete':
            if (!$msg_id) {
                zib_admin_page_notice(__('错误！', 'zib_language'), __('参数传入错误', 'zib_language'), 'error');
                break;
            }

            $delete = ZibMsg::delete(array('id' => $msg_id));
            if ($delete) {
                zib_admin_page_notice(__('删除成功！', 'zib_language'), __('消息已删除', 'zib_language'));
            } else {
                zib_admin_page_notice(__('消息已删除', 'zib_language'), '', 'warning');
            }
            break;

        case 'add_msg':
            $is_error  = false;
            $error_msg = '';

            if (empty($_POST['title']) || zib_new_strlen($_POST['title']) < 5) {
                $is_error = true;
                $error_msg .= __('标题过短！', 'zib_language') . '<br/>';
            }
            if (empty($_POST['content']) || zib_new_strlen($_POST['content']) < 5) {
                $is_error = true;
                $error_msg .= __('内容太少！', 'zib_language') . '<br/>';
            }
            if (empty($_POST['zcsf_nonce']) || !wp_verify_nonce($_POST['zcsf_nonce'], 'zcsf_nonce')) {
                $is_error = true;
                $error_msg .= __('安全验证失败！', 'zib_language') . '<br/>';
            }

            if ($is_error) {
                zib_admin_page_notice(__('错误！', 'zib_language'), $error_msg, 'error');
                break;
            }

            $receive_user = $_POST['receive_user'];
            if ($receive_user == 'customize') {
                $receive_user = !empty($_POST['receive_user_id']) ? $_POST['receive_user_id'] : 'all';
            }
            $new_msg = array(
                'send_user'    => 'admin',
                'receive_user' => $receive_user,
                'type'         => $_POST['type'],
                'title'        => $_POST['title'],
                'content'      => $_POST['content'],
            );
            if (!empty($_POST['customize_icon']['url'])) {
                $new_msg['meta']['customize_icon'] = !empty($_POST['customize_icon']['thumbnail']) ? $_POST['customize_icon']['thumbnail'] : $_POST['customize_icon']['url'];
            }

            $edit_id = !empty($_REQUEST['edit_id']) ? (int) $_REQUEST['edit_id'] : false;
            if ($edit_id) {

                $edit_action = ZibMsg::update($new_msg);
                if (is_array($new_msg['receive_user'])) {
                    foreach ($new_msg['receive_user'] as $k => $v) {
                        if ($k == 0) {
                            $new_msg['id'] = $edit_id;
                        }
                        $_new_msg                 = $new_msg;
                        $_new_msg['receive_user'] = $v;
                        $new_action               = ZibMsg::update($_new_msg);
                    }
                } else {
                    $new_msg['id'] = $edit_id;
                    $new_action    = ZibMsg::update($new_msg);
                }

                if ($edit_action) {
                    $edit_action = (array) $edit_action;
                    zib_admin_page_notice(__('消息修改成功！', 'zib_language'), __('消息修改成功！', 'zib_language') . '<br>' . esc_attr($edit_action['title']));
                } else {
                    zib_admin_page_notice(__('修改失败！', 'zib_language'), __('修改失败，请稍候再试！', 'zib_language'), 'error');
                }
            } else {

                if (is_array($new_msg['receive_user'])) {
                    foreach ($new_msg['receive_user'] as $v) {
                        $_new_msg                 = $new_msg;
                        $_new_msg['receive_user'] = $v;
                        $new_action               = ZibMsg::add($_new_msg);
                    }
                } else {
                    $new_action = ZibMsg::add($new_msg);
                }

                if ($new_action) {
                    $new_action = (array) $new_action;
                    zib_admin_page_notice(__('提交成功！', 'zib_language'), __('消息已推送！', 'zib_language') . '<br>' . esc_attr($new_action['title']));
                } else {
                    zib_admin_page_notice(__('提交失败！', 'zib_language'), __('提交失败，请稍候再试！', 'zib_language'), 'error');
                }
            }

            $tab = '';
            break;
    }
}

function zib_admin_page_notice($title = '', $msg = '', $type = 'success')
{
    $html = '';
    $html .= $title ? '<h3>' . $title . '</h3>' : '';
    $html .= $msg ? '<p>' . $msg . '</p>' : '';

    if ($html) {
        echo '<div class="notice notice-' . $type . '">' . $html . '</div>';
    }
}

$page_title = __('推送消息管理', 'zib_language');
$head_but   = '<a href="' . add_query_arg('tab', 'new', $this_url) . '" class="page-title-action">' . __('添加推送消息', 'zib_language') . '</a>';
$sub_but    = array();

//准备查询参数
$msg_type    = !empty($_REQUEST['msg_type']) ? $_REQUEST['msg_type'] : 0;
$user_id     = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
$orderby     = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'modified_time';
$paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
$ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : 15;
$desc        = !empty($_REQUEST['desc']) ? $_REQUEST['desc'] : 'DESC';
$offset      = $ice_perpage * ($paged - 1);

$count_all = 0;
$db_msg    = false;
$csf_args  = false;
$table     = false;
$pagenavi  = false;
$search    = false;
$page_html = false;

if ($tab == 'manage') {
    //管理消息-统计
    $page_title = __('消息管理及统计', 'zib_language');
    $head_but .= '<a href="' . $this_url . '" class="page-title-action">' . __('返回列表', 'zib_language') . '</a>';

    $statistics = '';

    $page_html = '<div class="metabox-holder" id="dashboard-widgets">
        <div class="postbox-container">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <h2 class="hndle ui-sortable-handle">' . __('消息统计', 'zib_language') . '</h2>
                    <div class="inside">
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="postbox-container">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <h2 class="hndle ui-sortable-handle">' . __('消息管理', 'zib_language') . '</h2>
                    <div class="inside">
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>';
} elseif ($tab == 'new') {
    $page_title = __('新增推送消息', 'zib_language');
    $zcsf_value = array();

    $sub_but    = false;
    $head_but   = '<a href="' . $this_url . '" class="page-title-action">' . __('返回列表', 'zib_language') . '</a>';
    $csf_hidden = array(
        array(
            'name'  => 'action',
            'value' => 'add_msg',
        ),
    );
    if ($msg_id) {
        $edit_db = ZibMsg::get_row(array('id' => $msg_id));
        if ($edit_db) {
            $page_title = __('编辑消息', 'zib_language');
            zib_admin_page_notice('', __('正在编辑消息！', 'zib_language'), 'warning');
            $zcsf_value = (array) $edit_db;
            if (!empty($zcsf_value['meta']['customize_icon'])) {
                $zcsf_value['customize_icon']['url']       = $zcsf_value['meta']['customize_icon'];
                $zcsf_value['customize_icon']['thumbnail'] = $zcsf_value['meta']['customize_icon'];
            }

            if (!in_array($zcsf_value['receive_user'], array('all', 'admin', 'vip1', 'vip2', 'vip', 'new_user', 'new_vip1', 'new_vip2'))) {
                $zcsf_value['receive_user']    = 'customize';
                $zcsf_value['receive_user_id'] = array((string) $edit_db->receive_user); //
            }
            $csf_hidden[] = array(
                'name'  => 'edit_id',
                'value' => $zcsf_value['id'],
            );
            $head_but .= '<a href="' . add_query_arg('tab', 'new', $this_url) . '" class="page-title-action">' . __('添加推送消息', 'zib_language') . '</a>';
        }
    }

    $csf_args = array(
        'class'  => 'csf-profile-options',
        'method' => 'post',
        'value'  => $zcsf_value,
        'hidden' => $csf_hidden,
    );
    $csf_fields[] = array(
        'content' => __('在此给用户推送系统消息', 'zib_language'),
        'type'    => 'content',
    );
    $csf_fields[] = array(
        'id'       => 'receive_user',
        'type'     => 'select',
        'title'    => __('推送给', 'zib_language'),
        'subtitle' => __('选择接收消息的用户类型', 'zib_language'),
        'options'  => array(
            'all'       => __('所有用户', 'zib_language'),
            'vip'       => __('所有VIP会员', 'zib_language'),
            'vip1'      => _pz('pay_user_vip_1_name', '一级会员'),
            'vip2'      => _pz('pay_user_vip_2_name', '二级会员'),
            'customize' => __('选择用户', 'zib_language'),
            'admin'     => __('网站管理员', 'zib_language'),
            /**
            'new_user'  => __('新注册用户', 'zib_language'),
            'new_vip1'  => '新' . _pz('pay_user_vip_1_name', '一级会员'),
            'new_vip2'  => '新' . _pz('pay_user_vip_2_name', '二级会员'),
            'customize'  => __('自定义用户', 'zib_language'),
             */
        ),
        'default'  => 'all',
    );
    $csf_fields[] = array(
        'dependency'  => array('receive_user', '==', 'customize'),
        'id'          => 'receive_user_id',
        'type'        => 'select',
        'title'       => __('选择用户', 'zib_language'),
        'subtitle'    => __('选择接收消息的用户明细', 'zib_language'),
        'options'     => 'user',
        'placeholder' => __('输入用户昵称、邮箱以搜索用户', 'zib_language'),
        'ajax'        => true,
        'multiple'    => true,
        'chosen'      => true,
        'default'     => '',
        'settings'    => array(
            'min_length' => 2,
        ),
    );
    $csf_fields[] = array(
        'id'      => 'type',
        'type'    => 'select',
        'title'   => __('消息类型', 'zib_language'),
        'options' => array(
            'system'    => __('系统消息', 'zib_language'),
            'vip'       => __('会员消息', 'zib_language'),
            'promotion' => __('活动消息', 'zib_language'),
        ),
        'default' => 'all',
    );
    $csf_fields[] = array(
        'id'          => 'customize_icon',
        'type'        => 'media',
        'placeholder' => __('选择自定义图标图像', 'zib_language'),
        'title'       => __('自定义图标', 'zib_language'),
        'subtitle'    => __('如需自定义消息图标则在此选择图像', 'zib_language'),
        'default'     => '',
    );
    $csf_fields[] = array(
        'id'          => 'title',
        'type'        => 'text',
        'placeholder' => __('请输入消息标题', 'zib_language'),
        'title'       => __('消息标题', 'zib_language'),
        'subtitle'    => __('标题建议在20-30个字符之间', 'zib_language'),
        'default'     => '',
    );
    $csf_fields[] = array(
        'id'          => 'content',
        'type'        => 'wp_editor',
        'placeholder' => __('请输入消息内容', 'zib_language'),
        'title'       => __('消息内容', 'zib_language'),
        'sanitize'    => false,
        'default'     => '',
    );
    $csf_fields[] = array(
        'title'   => ' ',
        'type'    => 'content',
        'content' => '<button type="submit" class="but jb-blue">' . __('提交推送', 'zib_language') . '</button>',
    );

    $csf_args['fields'] = $csf_fields;
} else {
    $pagenavi = true;
    //$zib_get_msg_cat = zib_get_msg_cat();
    $cat_type = $msg_type ? $msg_type : array('system', 'promotion', 'vip');

    if ($s) {
        $head_but .= '<div><div class="update-nag" style="margin: 10px 0 0;">' . sprintf(__('搜索 “%s” 的内容', 'zib_language'), esc_html($s)) . '</div></div>';
    }

    if ($user_id) {
        $sub_but[] = array(
            'name' => __('管理推送消息', 'zib_language'),
            'href' => $this_url,
        );
        $user_data  = get_userdata($user_id);
        $page_title = __('管理用户消息', 'zib_language');
        zib_admin_page_notice('', sprintf(__('正在查看用户 “%s” 消息！', 'zib_language'), esc_html($user_data->display_name)), 'warning');

        $desc            = $desc == 'DESC' ? 'DESC' : '';
        $vip             = zib_get_user_vip_level($user_id);
        $receive_user_in = [$user_id, 'all'];
        if ($vip) {
            $receive_user_in[] = 'vip';
            $receive_user_in[] = 'vip' . $vip;
        }
        $where = [
            [
                'relation' => 'or',
                ['send_user', '=', $user_id],
                ['receive_user', 'in', $receive_user_in],
            ],
        ];

        if ($s) {
            $where[] = [
                'relation' => 'or',
                ['title', 'like', '%' . $s . '%'],
                ['content', 'like', '%' . $s . '%'],
                ['meta', 'like', '%' . $s . '%'],
            ];
        }

        $count_all = ZibMsg::get_count($where);
        $db_msg    = ZibMsg::get($where, $orderby, $offset, $ice_perpage, $desc);

    } else {
        $sub_but[] = array(
            'name' => __('全部系统消息', 'zib_language'),
            'href' => $this_url,
        );
        $sub_but[] = array(
            'name' => __('活动消息', 'zib_language'),
            'href' => add_query_arg('msg_type', 'promotion', $this_url),
        );
        $sub_but[] = array(
            'name' => __('订单消息', 'zib_language'),
            'href' => add_query_arg('msg_type', 'pay', $this_url),
        );
        $sub_but[] = array(
            'name' => __('会员消息', 'zib_language'),
            'href' => add_query_arg('msg_type', 'vip', $this_url),
        );

        $where = array(
            'type' => $cat_type,
        );

        if ($s) {
            $where[] = [
                'relation' => 'or',
                ['title', 'like', '%' . $s . '%'],
                ['content', 'like', '%' . $s . '%'],
                ['meta', 'like', '%' . $s . '%'],
            ];
        }

        $count_all = ZibMsg::get_count($where);
        $db_msg    = ZibMsg::get($where, $orderby, $offset, $ice_perpage, $desc);
    }

    $table = '<thead><tr><td style="color: #ff4a4a;text-align: center;">' . __('未找到对应消息，或暂无消息', 'zib_language') . '</td></tr></thead>';
    if ($db_msg) {
        $table    = '';
        $theads[] = array('width' => '8%', 'orderby' => 'type', 'name' => __('类型', 'zib_language'));
        $theads[] = array('width' => '8%', 'orderby' => 'send_user', 'name' => __('发件人', 'zib_language'));
        $theads[] = array('width' => '9%', 'orderby' => 'receive_user', 'name' => __('收件人', 'zib_language'));
        $theads[] = array('width' => '15%', 'orderby' => '', 'name' => __('已读用户', 'zib_language'));
        $theads[] = array('width' => '10%', 'orderby' => 'create_time', 'name' => __('创建时间', 'zib_language'));
        $theads[] = array('width' => '20%', 'orderby' => 'title', 'name' => __('消息', 'zib_language'));
        $theads[] = array('width' => '20%', 'orderby' => '', 'name' => __('内容', 'zib_language'));

        $thead_th = '';
        foreach ($theads as $thead) {
            $orderby = '';
            if ($thead['orderby']) {
                $orderby_url = add_query_arg('orderby', $thead['orderby']);
                $orderby .= '<a title="' . esc_attr__('降序', 'zib_language') . '" href="' . add_query_arg('desc', 'ASC', $orderby_url) . '"><span class="dashicons dashicons-arrow-up"></span></a>';
                $orderby .= '<a title="' . esc_attr__('升序', 'zib_language') . '" href="' . add_query_arg('desc', 'DESC', $orderby_url) . '"><span class="dashicons dashicons-arrow-down"></span></a>';
                $orderby = '<span class="orderby-but">' . $orderby . '</span>';
            }
            $thead_th .= '<th class="" width="' . $thead['width'] . '">' . $thead['name'] . $orderby . '</th>';
        }
        $table .= '<thead><tr>' . $thead_th . '</tr></thead>';

        $tbody = '';
        foreach ($db_msg as $msg) {
            $msg  = (array) $msg;
            $meta = @maybe_unserialize($msg['meta']);

            $title             = $msg['title'];
            $create_time       = $msg['create_time'];
            $send_user         = zib_get_msg_send_user_text($msg['send_user']);
            $receive_user      = zib_get_msg_receive_user_text($msg['receive_user']);
            $readed_user_count = ZibMsg::get_readed_user($msg);
            $readed_user       = $readed_user_count ? sprintf(__('%d人已读', 'zib_language'), $readed_user_count['count']) : __('未读', 'zib_language');
            $type_text         = zib_get_msg_type_text($msg['type']);

            $content = zib_str_cut(strip_tags($msg['content']), 0, 30, '...');

            $row_actions = '';
            if (!in_array($msg['type'], array('withdraw_reply', 'withdraw'))) {
                $row_actions .= '<span><a href="' . add_query_arg(array('tab' => 'new', 'msg_id' => $msg['id']), $this_url) . '">' . __('编辑', 'zib_language') . '</a> | </span>';
                $row_actions .= '<span><a onclick="return confirm(\'' . esc_js(__('确认删除此内容?  删除后数据不可恢复!', 'zib_language')) . '\')" href="' . add_query_arg(array('action' => 'delete', 'msg_id' => $msg['id']), $this_url) . '">' . __('删除', 'zib_language') . '</a></span>';
                $row_actions = '<div class="row-actions">' . $row_actions . '</div>';
            }
            $tbody .= '<tr>';
            $tbody .= "<td>$type_text$row_actions</td>";
            $tbody .= "<td>$send_user</td>";
            $tbody .= "<td>$receive_user</td>";
            $tbody .= "<td>$readed_user</td>";
            $tbody .= "<td>$create_time</td>";
            $tbody .= "<td>$title</td>";
            $tbody .= "<td>$content</td>";

            $tbody .= '</tr>';
        }
        $table .= '<tbody>' . $tbody . '</tbody>';
    }

    $search = '<form class="form-inline form-order" method="post">
                    <div class="form-group" style="float: right;">
                        <input type="text" class="form-control" name="s" placeholder="' . esc_attr__('搜索消息', 'zib_language') . '">
                        <button type="submit" class="button">' . __('提交', 'zib_language') . '</button>
                    </div>
                </form>';
}

?>
<div class="wrap">
    <style>
        .orderby-but {
            position: relative;
        }

        .orderby-but>a {
            opacity: .4;
            position: absolute;
            transform: translateY(-3px);
            transition: .3s;
        }

        .orderby-but>a+a {
            transform: translateY(6px);
        }

        .orderby-but:hover a {
            opacity: .6;
        }

        .orderby-but>a:hover {
            opacity: 1;
        }
    </style>
    <h1 class="wp-heading-inline"><?php echo $page_title; ?></h1>
    <?php echo $head_but; ?>
    <?php
if ($sub_but) {
    $but_html = '';
    foreach ($sub_but as $but) {
        $but_html .= '<li><a href="' . $but['href'] . '">' . $but['name'] . '</a></li> | ';
    }
    if ($but_html) {
        echo '<div class="order-header"><ul class="subsubsub">' . substr($but_html, 0, -2) . '</ul></div>';
    }
}

if ($search) {
    echo $search;
}
if ($table) {
    echo '<p class="search-box">';
    echo '<div style="overflow-y: auto;width: 100%;">';
    echo '<table class="widefat fixed striped posts table table-bordered" style="min-width: 1000px;">';
    echo $table;
    echo '</table>';
    echo '</div>';
    echo '</p><div class="clear"></div>';
} elseif ($csf_args) {
    ZCSF::instance('add_msg', $csf_args);
}
if ($page_html) {
    echo $page_html;
}
if ($pagenavi) {
    zibpay_admin_pagenavi($count_all, $ice_perpage);
}

?>


</div>