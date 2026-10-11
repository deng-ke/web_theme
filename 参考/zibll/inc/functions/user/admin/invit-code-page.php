<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-12-21 22:54:02
 * @LastEditTime : 2026-05-05 11:35:17
 */

if (!is_super_admin()) {
    wp_die(__('您不能访问此页面', 'zib_language'), __('权限不足', 'zib_language'));
    exit;
}

$this_url = esc_url(admin_url('users.php?page=invit_code'));
$tab      = !empty($_GET['tab']) ? $_GET['tab'] : '';
$action   = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$s        = !empty($_REQUEST['s']) ? esc_sql($_REQUEST['s']) : '';

$invit_code = (array) _pz('invit_code_s'); //邀请码注册
if (!$invit_code || $invit_code === 'close') {
    echo '<div class="notice notice-warning"><p style="color:#fb590a;">' . __('注意：邀请码功能暂未启用。您可以在', 'zib_language') . sprintf('<a href="%1$s">%2$s</a>', esc_url(zib_get_admin_csf_url('用户互动/注册登录')), esc_html__('用户互动/注册登录', 'zib_language')) . __('中启用此功能', 'zib_language') . '</p></div>';
}

if ($action) {
    switch ($action) {
        case 'add':
            $add_type = !empty($_REQUEST['add_type']) ? $_REQUEST['add_type'] : 'auto';
            if ($add_type === 'import') {
                $import_data     = !empty($_REQUEST['import_data']) ? $_REQUEST['import_data'] : '';
                $import_division = !empty($_REQUEST['import_division']) ? $_REQUEST['import_division'] : ' ';

                if (!$import_data) {
                    zib_admin_page_notice(__('错误！', 'zib_language'), __('请粘贴您需要导入的数据', 'zib_language'), 'error');
                    break;
                }

                $import_data_array = explode("\r\n", $import_data);

                if (!$import_data_array) {
                    zib_admin_page_notice(__('错误！', 'zib_language'), __('请输入需要生成的数量', 'zib_language'), 'error');
                    break;
                }

                $success_i = 0;
                $error_i   = 0;
                foreach ($import_data_array as $v) {
                    $v = explode($import_division, $v);
                    if (!empty($v[0])) {
                        $success_i++;
                        $_reward = !empty($v[1]) ? $v[1] : '';
                        ZibCardPass::add(array(
                            'password' => $v[0],
                            'type'     => 'invit_code',
                            'status'   => '0', //正常
                            'meta'     => array('reward' => wp_parse_args($_reward)),
                            'other'    => !empty($v[2]) ? $v[2] : '',
                        ));
                    } else {
                        $error_i++;
                    }
                }

                if ($success_i) {
                    zib_admin_page_notice(__('导入完成', 'zib_language'), sprintf(__('成功导入%d个充值卡', 'zib_language'), $success_i) . ($error_i ? sprintf(__('，%d个导入失败', 'zib_language'), $error_i) : ''));
                    break;
                } else {
                    zib_admin_page_notice(__('导入失败', 'zib_language'), __('数据格式错误', 'zib_language'), 'error');
                    break;
                }

            } else {
                $auto_num = !empty($_REQUEST['auto_num']) ? (int) $_REQUEST['auto_num'] : 0;

                if (!$auto_num) {
                    zib_admin_page_notice(__('错误！', 'zib_language'), __('请输入需要生成的数量', 'zib_language'), 'error');
                    break;
                }

                //生成充值卡
                $rand_password = 8;
                if (!empty($_REQUEST['auto_top_s'])) {
                    $rand_password = !empty($_REQUEST['auto_rand_password_limit']) ? (int) $_REQUEST['auto_rand_password_limit'] : 8;
                }
                $remarks = !empty($_REQUEST['auto_remarks']) ? $_REQUEST['auto_remarks'] : '';
                $reward  = !empty($_REQUEST['auto_reward']) ? $_REQUEST['auto_reward'] : '';

                zib_generate_invit_code($auto_num, $rand_password, $reward, $remarks);

                zib_admin_page_notice(__('完成！', 'zib_language'), sprintf(__('已自动生成%d个邀请码', 'zib_language'), $auto_num));
                break;
            }

            zib_admin_page_notice(__('错误！', 'zib_language'), __('参数传入错误', 'zib_language'), 'error');

            break;

        case 'delete':
            $delete_ids = !empty($_REQUEST['action_id']) ? $_REQUEST['action_id'] : 0;
            if (!$delete_ids) {
                zib_admin_page_notice(__('错误！', 'zib_language'), __('未选择需要删除的内容', 'zib_language'), 'error');
                break;
            }
            $delete_i = ZibCardPass::delete(array(
                'id'   => $delete_ids,
                'type' => 'invit_code',
            ));

            zib_admin_page_notice(__('删除完成', 'zib_language'), sprintf(__('已删除%d个邀请码', 'zib_language'), $delete_i));
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

$page_title = __('邀请码管理', 'zib_language');
$head_but   = '<a href="' . add_query_arg('tab', 'add', $this_url) . '" class="page-title-action">' . __('添加邀请码', 'zib_language') . '</a>';
$sub_but    = array();

//准备查询参数
$orderby     = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'modified_time';
$paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
$ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : 30;
$desc        = !empty($_REQUEST['desc']) ? $_REQUEST['desc'] : 'DESC';
$offset      = $ice_perpage * ($paged - 1);

$count_all = 0;
$db_data   = false;
$csf_args  = false;
$table     = false;
$pagenavi  = false;
$search    = false;
$page_html = false;

switch ($tab) {

    case 'add':
        $page_title = __('添加邀请码', 'zib_language');
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">' . __('返回列表', 'zib_language') . '</a>';

        $csf_fields = array();

        $csf_fields[] = array(
            'content' => '<p><b>' . __('在此添加邀请码', 'zib_language') . '</b></p>
            <li>' . __('如果您已经准备好了邀请码资料，请选择导入的方式添加', 'zib_language') . '</li>
            <li>' . __('您也可以采用系统生成的方式，自动批量添加邀请码', 'zib_language') . '</li>',
            'style'   => 'warning',
            'type'    => 'submessage',
        );

        $csf_fields[] = array(
            'id'      => 'add_type',
            'type'    => 'button_set',
            'title'   => __('添加方式', 'zib_language'),
            'inline'  => true,
            'options' => array(
                'auto'   => __('系统自动生成', 'zib_language'),
                'import' => __('导入邀请码', 'zib_language'), //导入
            ),
            'default' => 'auto',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => __('生成数量', 'zib_language'),
            'id'         => 'auto_num',
            'default'    => 20,
            'min'        => 1,
            'max'        => 1000,
            'step'       => 10,
            'unit'       => __('个', 'zib_language'),
            'desc'       => __('需要生成多少个邀请码（单次生成数量太多可能会对服务器性能造成影响）', 'zib_language'),
            'type'       => 'spinner',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => __('标识', 'zib_language'),
            'desc'       => __('对生成的邀请码做标记标识，方便后期查找管理', 'zib_language'),
            'id'         => 'auto_remarks',
            'default'    => 'invita_' . current_time('YmdHis'),
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => __('使用奖励', 'zib_language'),
            'subtitle'   => '',
            'id'         => 'auto_reward',
            'type'       => 'fieldset',
            'fields'     => CFS_Module::invit_code_reward(),
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => __('高级选项', 'zib_language'),
            'id'         => 'auto_top_s',
            'default'    => false,
            'type'       => 'switcher',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type|auto_top_s', '==|!=', 'auto|'),
            'title'      => __('自定义位数', 'zib_language'),
            'class'      => 'compact',
            'id'         => 'auto_rand_password_limit',
            'default'    => 8,
            'min'        => 1,
            'max'        => 50,
            'step'       => 2,
            'unit'       => __('位数', 'zib_language'),
            'desc'       => __('自定义自动生成的长度（不能太短，太短可能会出现重复）', 'zib_language'),
            'type'       => 'spinner',
        );

        //导入邀请码
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'content'    => '<p><b>' . __('导入邀请码', 'zib_language') . '</b></p>
            <li>' . __('一行一个邀请码，单行格式为：', 'zib_language') . '<code>' . __('邀请码 奖励 标识', 'zib_language') . '</code></li>
            <li>' . __('邀请码、奖励、标识奖励默认使用空格分割，您可以在下方自定义分割符号，与您的数据对应即可', 'zib_language') . '</li>
            <li>' . __('奖励格式为：', 'zib_language') . '<code>level_integral=10&points=20&balance=30&vip=1&vip_time=5</code>，' . __('无需奖励填', 'zib_language') . ' <code>' . __('无', 'zib_language') . '</code></li>
            <li>' . __('单次导入数量太多可能会对服务器性能造成影响', 'zib_language') . '</li>
            <li>' . __('如果使用的是单密码模式，卡号部分随便填什么都行，但不能留空', 'zib_language') . '</li>',
            'style'      => 'warning',
            'type'       => 'submessage',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'content'    => '<p><b>' . __('数据示例', 'zib_language') . '</b></p>
            93fW0XAy ' . __('无', 'zib_language') . ' ' . __('没有奖励', 'zib_language') . '<br>
            P86NpWki level_integral=100 ' . __('奖励100经验值', 'zib_language') . '<br>
            P86NpWki points=10 ' . __('奖励10积分', 'zib_language') . '<br>
            P86NpWki balance=10 ' . sprintf(__('奖励%1$s余额', 'zib_language'), zibpay_format_local_price_text(10)) . '<br>
            P86NpWki balance=10&points=20 ' . sprintf(__('奖励%1$s余额和20积分', 'zib_language'), zibpay_format_local_price_text(10)) . '<br>
            auF9D2b4 balance=30&vip=1&vip_time=5 ' . __('奖励余额30和5天1级会员', 'zib_language'),
            'style'      => 'warning',
            'type'       => 'submessage',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'title'      => __('邀请码数据', 'zib_language'),
            'id'         => 'import_data',
            'default'    => '',
            'attributes' => array(
                'rows'  => 10,
                'style' => 'resize: both;max-width: none;',
            ),
            'sanitize'   => false,
            'type'       => 'textarea',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'id'         => 'import_division', //分割
            'title'      => __('自定义分隔符号', 'zib_language'),
            'subtitle'   => '',
            'class'      => 'mini-input',
            'default'    => ' ',
            'desc'       => __('卡号和密码之间分割符号（默认为空格分割）', 'zib_language'),
            'type'       => 'text',
        );
        $csf_fields[] = array(
            'title'   => ' ',
            'type'    => 'content',
            'content' => '<button type="submit" class="but jb-blue">' . __('确认提交', 'zib_language') . '</button>',
        );

        $csf_args = array(
            'class'  => 'csf-profile-options',
            'method' => 'post',
            'value'  => array(),
            'hidden' => array(
                array(
                    'name'  => 'action',
                    'value' => 'add',
                ),
            ),
            'fields' => $csf_fields,
        );

        break;

    case 'export':

        $page_title = __('导出邀请码', 'zib_language');
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">' . __('返回列表', 'zib_language') . '</a>';

        $csf_fields = array();

        $csf_fields[] = array(
            'id'      => 'status',
            'type'    => 'radio',
            'title'   => __('选择状态', 'zib_language'),
            'inline'  => true,
            'options' => array(
                'all'  => __('全部', 'zib_language'),
                '0'    => __('未使用', 'zib_language'), //导入
                'used' => __('已使用', 'zib_language'), //导入
            ),
            'default' => 'all',
        );
        $csf_fields[] = array(
            'id'      => 'export_format',
            'type'    => 'radio',
            'title'   => __('导出格式', 'zib_language'),
            'inline'  => true,
            'options' => array(
                'text' => __('文本文档', 'zib_language'),
                'xls'  => __('Excel表格', 'zib_language'), //导入
            ),
            'default' => 'xls',
        );

        $csf_fields[] = array(
            'dependency' => array('export_format', '==', 'text'),
            'id'         => 'text_division', //分割
            'title'      => ' ',
            'subtitle'   => __('分隔符号', 'zib_language'),
            'class'      => 'mini-input',
            'default'    => ' ',
            'desc'       => __('卡号和密码之间分割符号（默认为空格分割）', 'zib_language'),
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'title'   => ' ',
            'type'    => 'content',
            'content' => '<button type="submit" class="but jb-blue">' . __('确认提交', 'zib_language') . '</button>',
        );

        $csf_args = array(
            'class'  => 'csf-profile-options',
            'method' => 'post',
            'action' => admin_url('admin-ajax.php'),
            'value'  => array(),
            'hidden' => array(
                array(
                    'name'  => 'action',
                    'value' => 'card_pass_export',
                ),
                array(
                    'name'  => 'type',
                    'value' => 'invit_code',
                ),
            ),
            'fields' => $csf_fields,
        );

        break;

    default: //文章类型的
        //默认页面，展示邀请码列表
        $pagenavi  = true;
        $sub_but[] = array(
            'name' => __('全部', 'zib_language'),
            'href' => $this_url,
        );

        $head_but .= '<a href="' . add_query_arg(['tab' => 'export'], $this_url) . '" class="page-title-action">' . __('导出邀请码', 'zib_language') . '</a>';

        if ($s) {
            $head_but .= '<div><div class="update-nag" style="margin: 10px 0 0;">' . sprintf(__('搜索 “%s” 的内容', 'zib_language'), $s) . '</div></div>';
        } else {
            $sub_but[] = array(
                'name' => __('未使用', 'zib_language'),
                'href' => add_query_arg('status', '0', $this_url),
            );

            $sub_but[] = array(
                'name' => __('已使用', 'zib_language'),
                'href' => add_query_arg('status', 'used', $this_url),
            );
        }

        $where = array(
            'type' => 'invit_code', //余额充值
        );
        if (isset($_GET['status'])) {
            $where['status'] = $_GET['status'];
        }

        $db = ZibDB::name(ZibCardPass::$table_name);
        $db->where($where)->order($orderby, $desc)->page($paged, $ice_perpage);
        if ($s) {
            $db->whereLike(['card', 'password', 'other', 'meta'], $s);
        }

        $db_data   = $db->select()->result();
        $count_all = $db->count();

        $table = '<thead><tr><td style="color: #ff4a4a;text-align: center;">' . __('未找到对应内容，或暂无内容', 'zib_language') . '</td></tr></thead>';
        if ($db_data) {
            $table    = '';
            $theads[] = array('width' => '6%', 'orderby' => 'password', 'name' => __('邀请码', 'zib_language'));
            $theads[] = array('width' => '10%', 'orderby' => '', 'name' => __('使用奖励', 'zib_language'));
            //  $theads[] = array('width' => '10%', 'orderby' => '', 'name' => '邀请奖励');

            $theads[] = array('width' => '10%', 'orderby' => 'create_time', 'name' => __('创建时间', 'zib_language'));
            $theads[] = array('width' => '10%', 'orderby' => 'modified_time', 'name' => __('更新时间', 'zib_language'));
            $theads[] = array('width' => '6%', 'orderby' => 'status', 'name' => __('状态', 'zib_language'));
            $theads[] = array('width' => '12%', 'orderby' => 'other', 'name' => __('标识', 'zib_language'));

            $thead_th = '<td id="cb" class="manage-column column-cb check-column" style="width: 2%;"><label class="screen-reader-text" for="cb-select-all-1">' . __('全选', 'zib_language') . '</label><input id="cb-select-all-1" type="checkbox"></td>';
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
            foreach ($db_data as $msg) {
                $meta        = maybe_unserialize($msg->meta);
                $card_price  = zibpay_get_recharge_card_price($msg);
                $status_html = '<span style="color: #3d7ffd;">' . __('未使用', 'zib_language') . '</span>';

                if ($msg->status === 'used') {
                    $order_link_url = add_query_arg('page', 'zibpay_order_page', admin_url('admin.php')); //前缀
                    $status_html    = '<span style="color: #f93b3b;">' . __('已使用', 'zib_language') . '</span>';
                    if (!empty($meta['user_id'])) {
                        $status_html .= '<br><a target="_blank" href="' . zib_get_user_home_url($meta['user_id']) . '">' . get_the_author_meta('display_name', $meta['user_id']) . '</a>';
                    }
                }

                //正则匹配“自定义-005_shipped_848”，shipped_后后面的数字也是订单ID
                if (!empty($meta['shipped_order_id']) || preg_match('/shipped_(\d+)/', $msg->other, $matches)) {

                    $shipped_order_id = !empty($meta['shipped_order_id']) ? $meta['shipped_order_id'] : (int) $matches[1];
                    $status_html .= '<br>' . __('已售', 'zib_language') . '<a style="color: #ca7f5a;" target="_blank" href="' . zibpay_get_admin_shop_url('shipping', 'id=' . $shipped_order_id) . '"> [' . __('查看', 'zib_language') . ']</a>';
                }

                $other_a = '';
                if ($msg->other) {
                    $other_a = '<a href="' . add_query_arg('other', $msg->other, $this_url) . '">' . $msg->other . '</a>';
                }

                //奖励
                $reward_html          = __('无', 'zib_language');
                $referrer_reward_html = __('无', 'zib_language'); //推荐人

                if (!empty($meta['reward'])) {
                    $reward_html = '<div style="font-size: 12px;">' . zib_get_invit_code_reward_text($meta['reward'], '<br>') . '</div>';
                }

                if (!empty($meta['referrer_reward'])) {
                    $referrer_reward_html = '<div style="font-size: 11px;line-height: 1.3;">' . zib_get_invit_code_reward_text($meta['referrer_reward'], '<br>') . '</div>';
                }

                $tbody .= '<tr>';
                $tbody .= '<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-232">' . __('选择', 'zib_language') . '</label>
                <input id="cb-select-232" type="checkbox" name="action_id[]" value="' . $msg->id . '">
                    </th>';
                $tbody .= "<td><span data-clipboard-text='$msg->password' data-clipboard-tag='" . esc_attr__('邀请码', 'zib_language') . "'>$msg->password</span></td>";

                $tbody .= "<td>$reward_html</td>";
                // $tbody .= "<td>$referrer_reward_html</td>";

                $tbody .= "<td>$msg->create_time</td>";
                $tbody .= "<td>$msg->modified_time</td>";
                $tbody .= "<td>$status_html</td>";
                $tbody .= "<td>$other_a</td>";
                $tbody .= '</tr>';
            }
            $table .= '<tbody>' . $tbody . '</tbody>';
        }

        $search = '<form class="form-inline form-order" method="post">
                    <div class="form-group" style="float: right;">
                        <input type="text" class="form-control" name="s" placeholder="' . esc_attr__('搜索邀请码', 'zib_language') . '">
                        <button type="submit" class="button">' . __('提交', 'zib_language') . '</button>
                    </div>
                </form>';

        break;
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
$but_html = '';
if ($sub_but) {
    foreach ($sub_but as $but) {
        $but_html .= '<li><a href="' . $but['href'] . '">' . $but['name'] . '</a></li> | ';
    }
}

echo '<div class="order-header"><ul class="subsubsub">' . substr($but_html, 0, -2) . '</ul>' . $search . '</div>';

if ($table) {

    echo '<div class="clear"></div>';
    echo '<form class="" method="post">';
    echo '<div class="bulkactions" style="margin: 10px 0;">
			<label for="bulk-action-selector-top" class="screen-reader-text">' . __('选择批量操作', 'zib_language') . '</label><select name="action" id="bulk-action-selector-top">
                <option value="-1">' . __('批量操作', 'zib_language') . '</option>
                    <option value="delete">' . __('删除', 'zib_language') . '</option>
                </select>
                <input type="submit" class="button action" value="' . esc_attr__('应用', 'zib_language') . '">
		</div>';

    echo '<div style="overflow-y: auto;width: 100%;">';
    echo '<table class="widefat fixed striped posts table table-bordered" style="min-width: 1000px;">';
    echo $table;
    echo '</table>';
    echo '</div>';
    echo '</form>';
    echo '<div class="clear"></div>';

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