<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-05-25 13:53:47
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|后台功能文章meta配置
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

add_action('after_setup_theme', 'zib_bbs_admin_extend_metabox_csf');
function zib_bbs_admin_extend_metabox_csf()
{
    $zib_bbs = zib_bbs();

    //forum_post
    CSF::createMetabox('forum_extend', array(
        'title'     => sprintf(__('%s选项', 'zib_language'), $zib_bbs->posts_name),
        'post_type' => array('forum_post'),
        'context'   => 'side',
        'priority'  => 'high',
        'data_type' => 'unserialize',
    ));

    CSF::createSection('forum_extend', array(
        'fields' => array(
            zib_bbs_admin_warning_csf(),
            array(
                'title'      => $zib_bbs->plate_name,
                'id'         => 'plate_id',
                'desc'       => __('选择发布的', 'zib_language') . $zib_bbs->plate_name,
                'default'    => '',
                'options'    => 'post',
                'query_args' => array(
                    'post_type'      => 'plate',
                    'posts_per_page' => -1,
                ),
                'settings'   => array(
                    'min_length' => 2,
                ),
                'type'       => 'select',
            ),
            array(
                'content' => '<a href="' . esc_url(admin_url('post-new.php?post_type=plate')) . '" class="but jb-blue"><i class="fa fa-plus"></i>' . sprintf(__('创建新%s', 'zib_language'), $zib_bbs->plate_name) . '</a>',
                'style'   => 'warning',
                'type'    => 'content',
                'class'   => 'compact',
            ),
            array(
                'title'   => __('内容置顶', 'zib_language'),
                'id'      => 'topping',
                'default' => 0,
                'options' => zib_bbs_get_posts_topping_options(),
                'type'    => 'select',
            ),
            array(
                'title'   => __('精华', 'zib_language'),
                'label'   => __('将此帖子标记为精华内容', 'zib_language'),
                'id'      => 'essence',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'title'   => sprintf(__('%s类型', 'zib_language'), $zib_bbs->posts_name),
                'id'      => 'bbs_type',
                'default' => '',
                'desc'    => __('为此', 'zib_language') . $zib_bbs->posts_name . '设置类型，不同类型将会在列表显示不同风格的样式',
                'type'    => 'radio',
                'options' => 'zib_bbs_get_posts_type_options',
            ),
            array(
                'title'   => __('阅读量', 'zib_language'),
                'id'      => 'views',
                'default' => 0,
                'type'    => 'number',
                'options' => 'zib_bbs_get_posts_type_options',
            ),
        ),
    ));

    CSF::createMetabox('forum_allow_view', array(
        'title'     => __('阅读权限', 'zib_language'),
        'post_type' => array('forum_post'),
        'context'   => 'side',
        'priority'  => 'high',
        'data_type' => 'unserialize',
    ));

    CSF::createSection('forum_allow_view', array(
        'fields' => array(zib_bbs_admin_allow_view_csf(false),
            zib_bbs_admin_allow_view_csf(true),
            array(
                'dependency' => array('allow_view', 'any', 'pay,points'),
                'title'      => __('支付参数', 'zib_language'),
                'id'         => 'posts_zibpay',
                'type'       => 'fieldset',
                'class'      => 'compact',
                'fields'     => array(
                    array(
                        'dependency' => array('allow_view', '==', 'points', 'all'),
                        'id'         => 'points_price',
                        'title'      => __('积分售价', 'zib_language'),
                        'class'      => '',
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => __('积分', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('allow_view', '==', 'points', 'all'),
                        'title'      => _pz('pay_user_vip_1_name') . __('积分售价', 'zib_language'),
                        'id'         => 'vip_1_points',
                        'class'      => 'compact',
                        'subtitle'   => __('填0则为', 'zib_language') . _pz('pay_user_vip_1_name') . __('免费', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => __('积分', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('allow_view', '==', 'points', 'all'),
                        'title'      => _pz('pay_user_vip_2_name') . __('积分售价', 'zib_language'),
                        'id'         => 'vip_2_points',
                        'class'      => 'compact',
                        'subtitle'   => __('填0则为', 'zib_language') . _pz('pay_user_vip_1_name') . __('免费', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => __('积分', 'zib_language'),
                        'desc'       => __('会员价格不能高于售价', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('allow_view', '!=', 'points', 'all'),
                        'id'         => 'pay_price',
                        'title'      => __('执行价', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => zibpay_get_currency_unit(),
                    ),
                    array(
                        'dependency' => array('allow_view', '!=', 'points', 'all'),
                        'id'         => 'pay_original_price',
                        'title'      => __('原价', 'zib_language'),
                        'class'      => 'compact',
                        'subtitle'   => __('显示在执行价格前面，并划掉', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => zibpay_get_currency_unit(),
                    ),
                    array(
                        'dependency' => array('allow_view|pay_original_price', '!=|!=', 'points|', 'all'),
                        'title'      => ' ',
                        'subtitle'   => __('促销标签', 'zib_language'),
                        'class'      => 'compact',
                        'id'         => 'promotion_tag',
                        'sanitize'   => false,
                        'type'       => 'textarea',
                        'default'    => '',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'dependency' => array('allow_view', '!=', 'points', 'all'),
                        'title'      => _pz('pay_user_vip_1_name') . __('价格', 'zib_language'),
                        'id'         => 'vip_1_price',
                        'class'      => 'compact',
                        'subtitle'   => __('填0则为', 'zib_language') . _pz('pay_user_vip_1_name') . __('免费', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => zibpay_get_currency_unit(),
                    ),
                    array(
                        'dependency' => array('allow_view', '!=', 'points', 'all'),
                        'title'      => _pz('pay_user_vip_2_name') . __('价格', 'zib_language'),
                        'id'         => 'vip_2_price',
                        'class'      => 'compact',
                        'subtitle'   => __('填0则为', 'zib_language') . _pz('pay_user_vip_1_name') . __('免费', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => zibpay_get_currency_unit(),
                        'desc'       => __('会员价格不能高于执行价', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('allow_view', '!=', 'points', 'all'),
                        'title'      => __('推广折扣', 'zib_language'),
                        'id'         => 'pay_rebate_discount',
                        'class'      => 'compact',
                        'subtitle'   => __('通过推广链接购买，额外优惠的金额', 'zib_language'),
                        'desc'       => __('1.需开启推广返佣功能  2.注意此金不能超过实际购买价，避免出现负数', 'zib_language'),
                        'default'    => '',
                        'type'       => 'number',
                        'unit'       => zibpay_get_currency_unit(),
                    ),
                    array(
                        'title'    => __('销量浮动', 'zib_language'),
                        'id'       => 'pay_cuont',
                        'subtitle' => __('为真实销量增加或减少的数量', 'zib_language'),
                        'default'  => '',
                        'type'     => 'number',
                    ),
                    array(
                        'title'   => __('优惠码', 'zib_language'),
                        'label'   => __('允许使用优惠码', 'zib_language'),
                        'desc'    => __('开启后请在优惠码管理中添加优惠码 ', 'zib_language') . '<a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">' . __('【去管理】', 'zib_language') . '</a>' . '<div class="c-yellow">' . __('由于php特性，此功能有一定风险可能会出现优惠码被多个订单同时使用的情况，建议仅在特殊活动时，短时间开启', 'zib_language') . '</div>',
                        'id'      => 'coupon_s',
                        'default' => false,
                        'type'    => 'switcher',
                    ),
                    array(
                        'dependency' => array('coupon_s', '!=', ''),
                        'title'      => ' ',
                        'subtitle'   => __('优惠券默认说明', 'zib_language'),
                        'class'      => 'compact',
                        'id'         => 'coupon_desc',
                        'default'    => '',
                        'desc'       => __('用户填写优惠码时，展示的提醒内容，支持html代码，请注意代码规范', 'zib_language'),
                        'sanitize'   => false,
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'      => __('内容摘要', 'zib_language'),
                        'id'         => 'pay_doc',
                        'desc'       => __('填写内容摘要有助于用户了解该付费的大致内容', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                ),
            ),
            array(
                'dependency' => array('allow_view', 'any', 'pay,points'),
                'label'      => __('只隐藏部分内容', 'zib_language'),
                'desc'       => __('默认会隐藏全部内容，开启后则只会隐藏部分内容，请在内容中添加【隐藏内容-付费可见】内容', 'zib_language'),
                'id'         => 'pay_hide_part',
                'default'    => false,
                'type'       => 'switcher',
            ),
        ),
    ));

    //版块选项
    CSF::createMetabox('plate_extend', array(
        'title'     => sprintf(__('%s选项', 'zib_language'), $zib_bbs->plate_name),
        'post_type' => array('plate'),
        'context'   => 'side',
        'priority'  => 'high',
        'data_type' => 'unserialize',
    ));
    CSF::createSection('plate_extend', array(
        'fields' => array(
            array(
                'content' => sprintf(__('%s的创建、修改都推荐在前台进行！以避免逻辑错误', 'zib_language'), $zib_bbs->plate_name),
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            /**
            array(
            'title'   => sprintf(__('%s类型', 'zib_language'), $zib_bbs->plate_name),
            'id'      => 'plate_type',
            'default' => "",
            'desc'    => __('为此', 'zib_language') . $zib_bbs->plate_name . '设置类型，不同类型将会在列表显示不同风格的样式',
            'type'    => "radio",
            'options' => 'zib_bbs_get_plate_type_options',
            ),
             */
            array(
                'title'       => $zib_bbs->plate_moderator_name,
                'id'          => 'moderator',
                'class'       => 'compact',
                'options'     => 'user',
                'default'     => array(),
                'placeholder' => __('输入用户名、昵称等关键词以搜索用户', 'zib_language'),
                'desc'        => __('输入用户名、昵称等关键词以搜索用户', 'zib_language') . '<br/>' . sprintf(__('您可以在主题设置中管理%s权限', 'zib_language'), $zib_bbs->plate_moderator_name) . '<br/>' . __('请勿将管理员、分区版本、版块创建者设置为版主！', 'zib_language'),
                'chosen'      => true,
                'multiple'    => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'type'        => 'select',
            ),
            array(
                'id'      => 'add_limit',
                'title'   => __('发帖限制', 'zib_language'),
                'desc'    => __('设置一个限制选项，设置后会根据对应选项的限制规则判断是否允许创建版块', 'zib_language'),
                'default' => 0,
                'options' => zib_bbs_get_add_limit_options('posts'),
                'type'    => 'radio',
            ),
        ),
    ));

//版块TAB栏目
    CSF::createMetabox('plate_tab', array(
        'title'     => __('页面配置', 'zib_language'),
        'post_type' => array('plate'),
        'context'   => 'advanced',
        'priority'  => 'high',
        'data_type' => 'unserialize',
    ));
    CSF::createSection('plate_tab', array(
        'fields' => array(
            array(
                'title'   => __('单独配置Tab栏目', 'zib_language'),
                'label'   => __('如需单独配置Tab栏目，请开启此项目', 'zib_language'),
                'id'      => 'plate_tab_alone_s',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'dependency'   => array('plate_tab_alone_s', '!=', ''),
                'title'        => __('版块帖子栏目', 'zib_language'),
                'subtitle'     => __('版块页面主要内容', 'zib_language'),
                'desc'         => __('在版块页面显示的栏目内容，请至少保证有两个栏目', 'zib_language') . '<br>' . __('会自动在第一个栏目内显示置顶文章(置顶文章只会显示为简约模式)', 'zib_language') . '<br>' . __('每一个tab栏目均独立的地址，地址结尾添加?index=tab序号即可', 'zib_language'),
                'button_title' => __('添加栏目', 'zib_language'),
                'min'          => 2,
                'id'           => 'plate_tab',
                'type'         => 'group',
                'default'      => array(
                    array(
                        'show'    => array('pc_s', 'm_s'),
                        'title'   => '全部',
                        'style'   => 'mini',
                        'orderby' => 'modified',
                    ),
                    array(
                        'show'    => array('pc_s', 'm_s'),
                        'style'   => 'detail',
                        'title'   => '推荐',
                        'orderby' => 'dynamic_score',
                    ),
                    array(
                        'show'    => array('pc_s', 'm_s'),
                        'style'   => 'detail',
                        'title'   => '最新回复',
                        'orderby' => 'last_reply',
                    ),
                    array(
                        'show'    => array('pc_s', 'm_s'),
                        'title'   => '热门',
                        'style'   => 'detail',
                        'orderby' => 'views',
                    ),
                    array(
                        'show'    => array('pc_s', 'm_s'),
                        'style'   => 'detail',
                        'title'   => '精华',
                        'filter'  => 'essence',
                        'orderby' => 'modified',
                    ),
                ),
                'fields'       => BBS_CFS_Module::plate_tab(),
            ),
            array(
                'dependency' => array('plate_tab_alone_s', '!=', ''),
                'title'      => __('栏目默认显示', 'zib_language'),
                'subtitle'   => __('默认显示第几个栏目TAB', 'zib_language'),
                'id'         => 'tab_active_index',
                'default'    => 2,
                'type'       => 'spinner',
                'step'       => 1,
            ),
        ),
    ));

    //为版块分类添加参数
    CSF::createTaxonomyOptions('plate_cat_extend', array(
        'title'     => sprintf(__('%s分类选项', 'zib_language'), $zib_bbs->plate_name),
        'taxonomy'  => 'plate_cat',
        'data_type' => 'unserialize',
    ));
    CSF::createSection('plate_cat_extend', array(
        'fields' => array(
            array(
                'title'       => $zib_bbs->cat_moderator_name,
                'id'          => 'moderator',
                'options'     => 'user',
                'default'     => array(),
                'placeholder' => __('输入用户名、昵称等关键词以搜索用户', 'zib_language'),
                'desc'        => __('输入用户名、昵称等关键词以搜索用户', 'zib_language') . '<br/>' . sprintf(__('您可以在主题设置中管理[%s]的权限', 'zib_language'), $zib_bbs->cat_moderator_name),
                'chosen'      => true,
                'multiple'    => true,
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'type'        => 'select',
            ),
            array(
                'id'      => 'add_limit',
                'title'   => sprintf(__('%s创建限制', 'zib_language'), $zib_bbs->plate_name),
                'desc'    => __('设置一个限制选项，设置后会根据对应选项的限制规则判断是否允许创建版块', 'zib_language'),
                'default' => 0,
                'class'   => 'button-mini',
                'options' => zib_bbs_get_add_limit_options('plate'),
                'type'    => 'radio',
            ),
        ),
    ));

}

//forum_post - 封面
function zib_bbs_meta_box_forum_cover($post)
{
    $fields = array(
        array(
            'content' => __('封面优先级为：视频>幻灯片>图片', 'zib_language') . '<br>' . __('设置视频封面时候，请再设置一张图片封面以作为视频的首图封面', 'zib_language'),
            'style'   => 'warning',
            'type'    => 'submessage',
        ),
        array(
            'title'   => __('视频', 'zib_language'),
            'id'      => 'featured_video',
            'type'    => 'upload',
            'preview' => false,
            'library' => 'video',
            'default' => false,
        ),
        array(
            'title'       => __('幻灯片', 'zib_language'),
            'id'          => 'featured_slide',
            'type'        => 'gallery',
            'add_title'   => __('添加图像', 'zib_language'),
            'edit_title'  => __('编辑图像', 'zib_language'),
            'clear_title' => __('清空图像', 'zib_language'),
            'default'     => false,
        ),
        array(
            'title'   => __('图片', 'zib_language'),
            'id'      => 'cover_image',
            'library' => 'image',
            'type'    => 'upload',
            'default' => false,
            'desc'    => __('在文章页顶部显示封面图', 'zib_language'),
        ),
    );

    $value = array();
    if (!empty($post->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('post_meta');
        $zib_meta         = get_post_meta($post->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_post_meta($post->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }

    $csf_args = array(
        'class'  => 'csf-profile-options',
        'value'  => $value,
        'form'   => false,
        'nonce'  => true,
        'fields' => $fields,
    );

    ZCSF::instance('post_meta', $csf_args);
}

function zib_bbs_save_meta_box_forum_cover($post_id)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    $meta_fields = array('featured_video', 'featured_slide', 'cover_image');
    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_post_meta($post_id, $field, $_POST[$field]);
        }
    }

    if (isset($_POST['forum_allow_view']['allow_view'])) {
        zib_update_post_meta($post_id, 'pay_hide_part', !empty($_POST['forum_allow_view']['pay_hide_part'])  ?true : false);
    }
}

function zib_bbs_add_meta_box_forum_cover()
{
    global $zib_bbs;
    add_meta_box('forum_cover', sprintf(__('%s封面', 'zib_language'), $zib_bbs->posts_name), 'zib_bbs_meta_box_forum_cover', array('forum_post'), 'side', 'high');
}

add_action('add_meta_boxes', 'zib_bbs_add_meta_box_forum_cover');
add_action('save_post', 'zib_bbs_save_meta_box_forum_cover');

function zib_bbs_admin_warning_csf()
{
    $post_id = !empty($_REQUEST['post']) ? $_REQUEST['post'] : 0;
    global $zib_bbs;
    return array(
        'content' => sprintf(__('%1$s的发布、修改都推荐在%2$s进行！以避免逻辑错误', 'zib_language'), $zib_bbs->posts_name, zib_bbs_get_posts_add_page_link(array('id' => $post_id), 'c-blue', __('前台编辑器', 'zib_language'))),
        'style'   => 'warning',
        'type'    => 'submessage',
    );
}

function zib_bbs_admin_allow_view_csf($is_roles = true)
{
    $vip = array();
    if (_pz('pay_user_vip_1_s', true)) {
        if (_pz('pay_user_vip_2_s', true)) {
            $vip = array(
                1 => _pz('pay_user_vip_1_name') . __('及以上会员可查看', 'zib_language'),
                2 => _pz('pay_user_vip_2_name') . __('可查看', 'zib_language'),
            );
        } else {
            $vip = array(
                1 => _pz('pay_user_vip_1_name') . __('可查看', 'zib_language'),
            );
        }
    }
    $vip = $vip ? array(
        '' => __('不限制会员角色', 'zib_language'),
    ) + $vip : false;

    $level_max = _pz('user_level_max', 10);
    $level     = array();
    for ($i = 1; $i <= $level_max; $i++) {
        $level[$i] = _pz('user_level_opt', 'LV' . $i, 'name_' . $i);
    }
    $level = $level ? array(
        '' => __('不限制等级角色', 'zib_language'),
    ) + $level : false;

    $allow_view_roles = array();
    if ($vip) {
        $allow_view_roles[] = array(
            'title'   => __('会员阅读权限', 'zib_language'),
            'id'      => 'vip',
            'default' => '',
            'type'    => 'radio',
            'options' => $vip,
        );
    }
    if ($level) {
        $allow_view_roles[] = array(
            'title'   => __('会员阅读权限', 'zib_language'),
            'id'      => 'level',
            'default' => '',
            'type'    => 'radio',
            'options' => $level,
        );
    }
    if (_pz('user_auth_s', true)) {
        $allow_view_roles[] = array(
            'label'   => __('允许认证用户查看', 'zib_language'),
            'id'      => 'auth',
            'default' => false,
            'type'    => 'switcher',
        );
    }

    if ($is_roles && $allow_view_roles) {
        return array(
            'dependency' => array('allow_view', '==', 'roles'),
            'title'      => __('可查看用户组设置', 'zib_language'),
            'id'         => 'allow_view_roles',
            'type'       => 'fieldset',
            'class'      => 'compact',
            'fields'     => $allow_view_roles,
        );
    }
    $options = array(
        ''        => __('公开', 'zib_language'),
        'signin'  => __('登录后可查看', 'zib_language'),
        'comment' => __('评论后可查看', 'zib_language'),
    );
    if ($allow_view_roles) {
        $options['roles'] = __('部分用户可查看', 'zib_language');
    }
    $options['pay']    = __('付费查看', 'zib_language');
    $options['points'] = __('支付积分后查看', 'zib_language');
    return array(
        'title'   => __('阅读权限', 'zib_language'),
        'id'      => 'allow_view',
        'default' => '',
        'type'    => 'radio',
        'options' => $options,
    );

}

//forum_post - 封面
function zib_bbs_meta_box_plate_cover($post)
{
    $fields = array(
        array(
            'title'   => __('图片', 'zib_language'),
            'id'      => 'thumbnail_url',
            'library' => 'image',
            'type'    => 'upload',
            'default' => false,
        ),
    );

    $value = array();
    if (!empty($post->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('post_meta');
        $zib_meta         = get_post_meta($post->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_post_meta($post->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }
    $csf_args = array(
        'class'  => '',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );

    ZCSF::instance('post_meta', $csf_args);
}

function zib_bbs_save_meta_box_plate_cover($post_id)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    $meta_fields = array('thumbnail_url');
    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}

function zib_bbs_add_meta_box_plate_cover()
{
    global $zib_bbs;
    add_meta_box('plate_cover', sprintf(__('%s图像', 'zib_language'), $zib_bbs->plate_name), 'zib_bbs_meta_box_plate_cover', array('plate'), 'side', 'high');
}

add_action('add_meta_boxes', 'zib_bbs_add_meta_box_plate_cover');
add_action('save_post', 'zib_bbs_save_meta_box_plate_cover');

//帖子批量编辑
//帖子批量编辑和快速编辑
if (!empty($_GET['post_type']) && $_GET['post_type'] === 'forum_post') {
    add_action('bulk_edit_custom_box', array('zib_bbs_posts_bulk_edit', 'edit_box'), 10, 2);
    add_action('quick_edit_custom_box', array('zib_bbs_posts_bulk_edit', 'edit_box'), 10, 2);
}
add_action('save_post', array('zib_bbs_posts_bulk_edit', 'save'), 10, 3);
class zib_bbs_posts_bulk_edit
{
    public static $permissible_posts_type = ['forum_post'];
    public static $column_name            = 'taxonomy-forum_tag';
    public static $bulk_id                = 'forum_post';
    public static $screen                 = 'edit-forum_post';

    public static function edit_box($column_name, $post_type)
    {

        if (!in_array($post_type, self::$permissible_posts_type) || $column_name !== self::$column_name) {
            return;
        }

        $zib_bbs = zib_bbs();

        $plate_query  = zib_bbs_edit::plate_query(0, 'views', 200);
        $plate_option = array();
        $plate_desc   = '';

        if (!empty($plate_query->posts)) {
            foreach ($plate_query->posts as $posts) {
                $plate_option[$posts->ID] = $posts->post_title;
            }
            if ($plate_query->found_posts > 200) {
                $plate_desc = '<div>' . sprintf(__('共计%1$s个%2$s，仅显示200个', 'zib_language'), $plate_query->found_posts, $zib_bbs->plate_name) . '</div>';
            }
        }

        $fields_args = array(
            array(
                'title'   => $zib_bbs->plate_name,
                'id'      => 'plate_id',
                'default' => '',
                'options' => $plate_option,
                'type'    => 'select',
            ),
            array(
                'title'   => __('置顶', 'zib_language'),
                'id'      => 'topping',
                'default' => 0,
                'options' => zib_bbs_get_posts_topping_options(),
                'type'    => 'select',
            ),
            array(
                'title'   => sprintf(__('%s类型', 'zib_language'), $zib_bbs->posts_name),
                'id'      => 'bbs_type',
                'default' => '',
                'desc'    => __('为此', 'zib_language') . $zib_bbs->posts_name . '设置类型，不同类型将会在列表显示不同风格的样式',
                'type'    => 'select',
                'options' => zib_bbs_get_posts_type_options(),
            ),
            array(
                'title'   => __('精华', 'zib_language'),
                'label'   => __('将此帖子标记为精华内容', 'zib_language'),
                'id'      => 'essence',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'id'    => 'views',
                'type'  => 'number',
                'title' => __('阅读量', 'zib_language'),
            ),
            array(
                'title'   => __('查看权限', 'zib_language'),
                'id'      => 'allow_view',
                'default' => '',
                'remind'  => '',
                'type'    => 'radio',
                'options' => array(
                    ''        => __('公开', 'zib_language'),
                    'signin'  => __('登录后可查看', 'zib_language'),
                    'comment' => __('评论后可查看', 'zib_language'),
                ),
            ),
        );

        echo zib_get_quick_edit_custom_input($fields_args, self::$bulk_id);

    }

    public static function save($post_id, $post, $update)
    {
        if (!$update || empty($_REQUEST['zib_bulk_edit'][self::$bulk_id]) || !in_array($post->post_type, self::$permissible_posts_type) || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== self::$screen) {
            return;
        }

        $zibpay_bulk_edit = $_REQUEST['zib_bulk_edit'][self::$bulk_id];
        foreach ($zibpay_bulk_edit as $field_id => $field_value) {
            switch ($field_id) {
                case 'views':
                case 'like':
                    $operation = $field_value['operation'];
                    if ($operation !== 'ignore' && is_numeric($field_value['val'])) {

                        $old_val = get_post_meta($post_id, $field_id, true);
                        $val     = (float) $field_value['val'];

                        switch ($operation) {
                            case 'set': //统一设置为
                                $new_val = $val;
                                break;
                            case 'plus':
                                $new_val = round($old_val + $val, 2);
                                break;
                            case 'subtract':
                                $new_val = round($old_val - $val, 2);
                                break;
                            case 'multiply':
                                $new_val = round($old_val * $val, 2);
                                break;
                            case 'division':
                                if ($val != 0 && $old_val != 0) {
                                    $new_val = round($old_val / $val, 2);
                                }
                                break;
                        }
                        $new_val = (int) $new_val;
                        $new_val = $new_val < 0 ? 0 : $new_val;
                        update_post_meta($post_id, $field_id, $new_val);
                    }

                    break;
                case 'plate_id':
                case 'topping':
                case 'essence':
                case 'bbs_type':
                case 'allow_view':

                    if ($field_value !== 'ignore') {
                        update_post_meta($post_id, $field_id, $field_value);
                    }

                    break;
            }
        }

    }
}
