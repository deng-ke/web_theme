<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-06-16 13:01:46
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台商品配置项
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

add_action('after_setup_theme', 'zib_shop_admin_product_metabox');
function zib_shop_admin_product_metabox()
{
    //页面限制：新增商品、编辑商品
    if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') === false && strpos($_SERVER['SCRIPT_NAME'], 'post.php') === false) {
        return;
    }

    $prefix = 'product_config';
    CSF::createMetabox($prefix, array(
        'title'     => __('商品配置', 'zib_language'),
        'post_type' => array('shop_product'),
        'context'   => 'normal',
        'priority'  => 'high',
        'theme'     => 'light',
        'data_type' => 'serialize',
    ));

    CSF::createSection($prefix, array(
        'title'  => __('商品详情', 'zib_language'),
        'fields' => array(
            //简介desc
            array(
                'title'      => __('简介', 'zib_language'),
                'desc'       => __('一句话介绍商品，内容不宜过多', 'zib_language'),
                'id'         => 'desc',
                'type'       => 'textarea',
                'sanitize'   => false,
                'default'    => '',
                'attributes' => array(
                    'rows' => 1,
                ),
            ),
            array(
                'title'       => __('封面图片', 'zib_language'),
                'id'          => 'cover_images',
                'type'        => 'gallery',
                'add_title'   => __('添加图像', 'zib_language'),
                'edit_title'  => __('编辑图像', 'zib_language'),
                'clear_title' => __('清空图像', 'zib_language'),
                'default'     => false,
                'desc'        => __('选择多张图片作为商品封面图 (正方形图片效果最佳)', 'zib_language'),
            ),
            array(
                'title'        => __('封面视频', 'zib_language'),
                'id'           => 'cover_videos',
                'class'        => '',
                'type'         => 'repeater',
                'button_title' => __('添加视频', 'zib_language'),
                'desc'         => __('封面视频会和封面图片同步显示，同时视频封面和图片封面的顺序保持一致，所以必须先设置图片封面，且图片数量必须大于等于视频数量', 'zib_language'),
                'min'          => 0,
                'fields'       => array(
                    array(
                        'id'          => 'url',
                        'type'        => 'upload',
                        'preview'     => false,
                        'library'     => 'video',
                        'placeholder' => __('选择视频或填写视频地址', 'zib_language'),
                        'default'     => '',
                    ),
                ),
            ),
            array(
                'title'        => __('商品参数', 'zib_language'),
                'id'           => 'params',
                'class'        => 'mini-flex-repeater',
                'type'         => 'repeater',
                'button_title' => __('添加商品参数', 'zib_language'),
                'desc'         => __('显示在商品详情页的参数，例如：规格、材质、尺寸等。你可以在主题设置->商城商品/商品参数中配置默认参数，每次新建商品时候，此处会自动调用 ', 'zib_language') . '<a target="_blank" href="' . zib_get_admin_csf_url('商城商品/商品参数') . '">' . __('【去配置】', 'zib_language') . '</a>',
                'min'          => 0,
                'default'      => _pz('shop_product_params_default', array()),
                'fields'       => array(
                    array(
                        'title' => __('参数名称', 'zib_language'),
                        'id'    => 'name',
                        'type'  => 'text',
                    ),
                    array(
                        'title' => __('参数值', 'zib_language'),
                        'id'    => 'value',
                        'type'  => 'text',
                    ),
                ),
            ),
            array(
                'title'       => __('自定义主图', 'zib_language'),
                'desc'        => __('默认会使用第一张封面图作为主图，如需要单独设置与第一张封面图片不同的主图，则在此自定义', 'zib_language'),
                'id'          => 'main_image',
                'type'        => 'upload',
                'preview'     => true,
                'library'     => 'image',
                'placeholder' => __('选择图片或填写图片地址(正方形图片效果最佳)', 'zib_language'),
                'default'     => '',
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('价格&选项', 'zib_language'),
        'fields' => array(
            array(
                'id'      => 'pay_modo',
                'title'   => __('价格类型', 'zib_language'),
                'default' => '0',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    '0'      => __('普通商品（金钱购买）', 'zib_language'),
                    'points' => __('积分商品（积分兑换，依赖于用户积分功能）', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'start_price',
                'title'   => __('起始价格', 'zib_language'),
                'default' => '0',
                'desc'    => __('起始价格为商品的默认价格，实际售价系统会根据商品的选项、优惠等自动调整(如果有多个商品选项，建议为不同规格的最低价)', 'zib_language'),
                'type'    => 'number',
            ),
            array(
                'id'           => 'product_options',
                'type'         => 'repeater',
                'title'        => __('商品选项', 'zib_language'),
                'subtitle'     => __('添加商品选项类型', 'zib_language'),
                'button_title' => __('添加选项类型', 'zib_language'),
                'desc'         => '<div class="c-yellow">' . __('注意：如果设置了', 'zib_language') . '<code>' . __('按选项配置库存/按选项发货', 'zib_language') . '</code>' . __('功能，每次修改选项后，必须', 'zib_language') . '<code class="c-red">' . __('保存后并刷新页面', 'zib_language') . '</code>' . __('再重新配置', 'zib_language') . '<code>' . __('按选项配置库存/按选项发货', 'zib_language') . '</code>' . __('功能，否则数据会出错', 'zib_language') . '</div>',
                'default'      => array(),
                'fields'       => array(
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'class' => 'mini-input',
                        'title' => __('选项名称(必填)', 'zib_language'),
                    ),
                    array(
                        'id'      => 'view_mode',
                        'type'    => 'button_set',
                        'desc'    => __('注意：如果设置为图片，请确保当前选项下的选项值都有图片', 'zib_language'),
                        'title'   => __('默认显示样式', 'zib_language'),
                        'default' => 'list',
                        'options' => array(
                            'list' => __('列表', 'zib_language'),
                            'img'  => __('图片', 'zib_language'),
                        ),
                    ),
                    array(
                        'id'           => 'opts',
                        'type'         => 'group',
                        'title'        => ' ',
                        'subtitle'     => __('选项项目', 'zib_language'),
                        'class'        => '',
                        'button_title' => __('添加当前选项值', 'zib_language'),
                        'fields'       => array(
                            array(
                                'id'    => 'name',
                                'type'  => 'text',
                                'title' => __('名称(必填)', 'zib_language'),
                            ),
                            array(
                                'id'    => 'price_change',
                                'type'  => 'number',
                                'title' => __('价格变化', 'zib_language'),
                                'desc'  => __('当前选项价格相对于', 'zib_language') . '<code>' . __('起始价格', 'zib_language') . '</code>' . __('的变化值，正数为增加，负数为减少', 'zib_language'),
                            ),
                            array(
                                'id'    => 'image',
                                'type'  => 'upload',
                                'title' => __('图片', 'zib_language'),
                                'desc'  => __('如果需要设置选项图片，请确保当前选项下都设置了图片（正方形图片效果最佳）', 'zib_language'),
                            ),
                        ),
                    ),
                ),
            ),
            zib_shop_csf_module::rebate('product'),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('库存&限购', 'zib_language'),
        'fields' => array(
            zib_shop_csf_module::limit_buy('product'),
            array(
                'id'      => 'stock_type',
                'type'    => 'radio',
                'inline'  => true,
                'title'   => __('库存配置类型', 'zib_language'),
                'desc'    => __('卡密类型的自动发货内容可开启', 'zib_language') . '<code>' . __('自动获取库存', 'zib_language') . '</code>' . __('功能，开启后会覆盖此处配置', 'zib_language'),
                'default' => 'all',
                'options' => array(
                    'all'  => __('统一总库存', 'zib_language'),
                    'opts' => __('按选项设置库存', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('stock_type', '==', 'all'),
                'id'         => 'stock_all',
                'type'       => 'number',
                'title'      => __('库存总数量', 'zib_language'),
                'desc'       => __('统一总库存则不区分商品选项，-1为无限库存，0为无库存无法购买', 'zib_language'),
                'default'    => -1,
                'min'        => -1,
                'step'       => 1,
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array('stock_type', '==', 'opts'),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<b>' . __('重要提醒：', 'zib_language') . '</b>' . __('如果您修改了商品选项，请务必先', 'zib_language') . '<code class="c-red">' . __('保存后并刷新页面', 'zib_language') . '</code>' . __('再重新配置下方库存，', 'zib_language') . '<code class="c-red">' . __('否则会出错', 'zib_language') . '</code>',
            ),
            array(
                'title'      => __('库存数量', 'zib_language'),
                'dependency' => array('stock_type', '==', 'opts'),
                'id'         => 'stock_opts',
                'type'       => 'fieldset',
                'desc'       => __('-1为无限库存，0为无库存无法购买', 'zib_language'),
                'fields'     => zib_shop_get_metabox_product_opts_cfs_fields([
                    'id_prefix' => '',
                    'class'     => 'title-auto-width',
                    'default'   => -1,
                    'min'       => -1,
                    'step'      => 1,
                    'type'      => 'spinner',
                ]),
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('发货&物流', 'zib_language'),
        'fields' => array(

            array(
                'title'        => __('用户必留信息', 'zib_language'),
                'id'           => 'user_required',
                'class'        => 'mini-flex-repeater',
                'type'         => 'repeater',
                'button_title' => __('添加必留类型', 'zib_language'),
                'desc'         => __('用户下单时必须要填写的信息，例如话费充值要求用户必留手机号等', 'zib_language'),
                'min'          => 0,
                'default'      => [],
                'fields'       => array(
                    array(
                        'title' => __('必留*', 'zib_language'),
                        'id'    => 'name',
                        'type'  => 'text',
                    ),
                    array(
                        'title' => __('说明文字', 'zib_language'),
                        'id'    => 'desc',
                        'type'  => 'text',
                    ),
                ),
            ),

            array(
                'id'      => 'shipping_type',
                'title'   => __('发货类型', 'zib_language'),
                'default' => _pz('shop_product_shipping_type', 'express'),
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'express' => __('物流快递发货', 'zib_language'),
                    'auto'    => __('自动发货(虚拟商品)', 'zib_language'),
                    'manual'  => __('手动发货(虚拟商品)', 'zib_language'),
                ),
            ),
            array(
                'dependency' => [array('shipping_type', '==', 'express'), array('pay_modo', '==', 'points', 'all')],
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => __('当前商品为积分兑换，运费只能为0，如果您设置了运费，则无效', 'zib_language'),
            ),
            zib_shop_csf_module::guest_buy('product'),
            zib_shop_csf_module::email_fill('product'),
            array(
                'dependency'  => array('shipping_type', '!=', 'express'),
                'id'          => 'shipping_delivery_desc',
                'title'       => __('发货标题', 'zib_language'),
                'subtitle'    => '',
                'placeholder' => __('简短几个字描述发货说明，例如24小时发货、预计3-5天发货等', 'zib_language'),
                'type'        => 'text',
                'default'     => '',
            ),
            array(
                'dependency' => array('shipping_type', '==', 'auto'),
                'title'      => ' ',
                'subtitle'   => __('自动发货配置', 'zib_language'),
                'sanitize'   => false,
                'desc'       => '',
                'id'         => 'auto_delivery',
                'type'       => 'fieldset',
                'class'      => 'compact',
                'fields'     => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'button_set',
                        'default' => 'fixed',
                        'options' => array(
                            'fixed'      => __('固定内容', 'zib_language'),
                            'invit_code' => __('邀请码', 'zib_language'),
                            'card_pass'  => __('卡密', 'zib_language'),
                            'opts'       => __('按选项分别配置', 'zib_language'),
                        ),
                    ),
                    array(
                        'dependency'  => array('type', '==', 'fixed'),
                        'id'          => 'fixed_content',
                        'type'        => 'textarea',
                        'placeholder' => __('请输入需要发送给用户的内容，支持html代码，注意格式规范', 'zib_language'),
                        'sanitize'    => false,
                        'default'     => '',
                        'attributes'  => array(
                            'rows' => 3,
                        ),
                    ),
                    array(
                        'dependency' => array('type', '==', 'invit_code'),
                        'id'         => 'invit_code_mode',
                        'type'       => 'radio',
                        'inline'     => true,
                        'default'    => '',
                        'options'    => array(
                            ''         => __('选择已创建的邀请码', 'zib_language'),
                            'auto_add' => __('自动创建邀请码并发货', 'zib_language'),
                        ),
                    ),
                    array(
                        'dependency' => array('type', 'any', 'invit_code,card_pass'),
                        'id'         => 'auto_stock',
                        'type'       => 'switcher',
                        'default'    => true,
                        'title'      => __('自动获取库存', 'zib_language'),
                        'label'      => __('自动获取库存数量，关闭后则以设置的库存数量为准', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('type|invit_code_mode', '==|==', 'invit_code|'),
                        'id'         => 'invit_code_key',
                        'type'       => 'text',
                        'title'      => __('邀请码标识', 'zib_language'),
                        'subtitle'   => __('根据标识筛选邀请码', 'zib_language'),
                        'desc'       => __('如还未创建邀请码，请先创建邀请后在此处输入邀请码的备注 ', 'zib_language') . '<a target="_blank" href="' . admin_url('users.php?page=invit_code') . '">' . __('【去管理】', 'zib_language') . '</a>|<a target="_blank" href="https://www.zibll.com/47298.html">' . __('查看官网教程', 'zib_language') . '</a>',
                        'default'    => '',
                    ),
                    array(
                        'dependency' => array('type|invit_code_mode', '==|==', 'invit_code|auto_add'),
                        'id'         => 'invit_code_auto_add_opts',
                        'type'       => 'fieldset',
                        'fields'     => array(
                            array(
                                'title'    => __('使用奖励', 'zib_language'),
                                'subtitle' => '',
                                'id'       => 'auto_reward',
                                'type'     => 'fieldset',
                                'fields'   => CFS_Module::invit_code_reward(),
                            ),
                            array(
                                'title'   => __('标识前缀', 'zib_language'),
                                'desc'    => __('对自动生成的邀请码做标记标识，方便后期查找管理', 'zib_language'),
                                'id'      => 'auto_remarks',
                                'default' => '',
                                'type'    => 'text',
                            ),
                        ),
                    ),
                    array(
                        'dependency' => array('type', '==', 'card_pass'),
                        'id'         => 'card_pass_key',
                        'type'       => 'text',
                        'title'      => __('卡密标识   ', 'zib_language'),
                        'subtitle'   => __('根据标识筛选卡密', 'zib_language'),
                        'desc'       => __('如还未创建，请先创建后在此处输入卡密的标识 ', 'zib_language') . '<a target="_blank" href="' . admin_url('admin.php?page=zibpay_charge_card_page') . '">' . __('【管理卡密】', 'zib_language') . '</a>' . ' | ' . '<a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">' . __('【管理优惠码】', 'zib_language') . '</a> | <a target="_blank" href="https://www.zibll.com/47298.html">' . __('查看官网教程', 'zib_language') . '</a>',
                        'default'    => '',
                    ),
                    array(
                        'dependency' => array('type', '==', 'opts'),
                        'type'       => 'submessage',
                        'style'      => 'warning',
                        'content'    => '<b>' . __('重要提醒：', 'zib_language') . '</b>' . __('如果您修改了商品选项，请务必先', 'zib_language') . '<code class="c-red">' . __('保存后并刷新页面', 'zib_language') . '</code>' . __('再重新配置下方参数，', 'zib_language') . '<code class="c-red">' . __('否则会出错', 'zib_language') . '</code>',
                    ),
                    array(
                        'dependency' => array('type', '==', 'opts'),
                        'id'         => 'opts',
                        'type'       => 'fieldset',
                        'fields'     => zib_shop_get_metabox_product_opts_cfs_fields([
                            'sanitize' => false,
                            'desc'     => '',
                            'type'     => 'fieldset',
                            'fields'   => array(
                                array(
                                    'id'      => 'opts_type',
                                    'type'    => 'button_set',
                                    'inline'  => true,
                                    'default' => 'fixed',
                                    'options' => array(
                                        'fixed'      => __('固定内容', 'zib_language'),
                                        'invit_code' => __('邀请码', 'zib_language'),
                                        'card_pass'  => __('卡密', 'zib_language'),
                                    ),
                                ),
                                array(
                                    'dependency'  => array('opts_type', '==', 'fixed'),
                                    'id'          => 'fixed_content',
                                    'type'        => 'textarea',
                                    'placeholder' => __('请输入发货内容，支持html代码，注意格式规范', 'zib_language'),
                                    'sanitize'    => false,
                                    'default'     => '',
                                    'attributes'  => array(
                                        'rows' => 3,
                                    ),
                                ),
                                array(
                                    'dependency' => array('opts_type', '==', 'invit_code'),
                                    'id'         => 'opts_invit_code_mode',
                                    'type'       => 'radio',
                                    'inline'     => true,
                                    'default'    => '',
                                    'options'    => array(
                                        ''         => __('选择已创建的邀请码', 'zib_language'),
                                        'auto_add' => __('自动创建邀请码并发货', 'zib_language'),
                                    ),
                                ),
                                array(
                                    'dependency' => array('opts_type', 'any', 'invit_code,card_pass'),
                                    'id'         => 'auto_stock',
                                    'type'       => 'switcher',
                                    'default'    => true,
                                    'title'      => __('自动获取库存', 'zib_language'),
                                    'label'      => __('自动获取库存数量，关闭后则以设置的库存数量为准', 'zib_language'),
                                ),
                                array(
                                    'dependency' => array('opts_type|opts_invit_code_mode', '==|==', 'invit_code|'),
                                    'id'         => 'invit_code_key',
                                    'type'       => 'text',
                                    'title'      => __('邀请码标识', 'zib_language'),
                                    'subtitle'   => __('根据标识筛选邀请码', 'zib_language'),
                                    'desc'       => __('如还未创建邀请码，请先创建邀请后在此处输入邀请码的备注 ', 'zib_language') . '<a target="_blank" href="' . admin_url('users.php?page=invit_code') . '">' . __('【去管理】', 'zib_language') . ' | <a target="_blank" href="https://www.zibll.com/47298.html">' . __('查看官网教程', 'zib_language') . '</a></a>',
                                    'default'    => '',
                                ),
                                array(
                                    'dependency' => array('opts_type|opts_invit_code_mode', '==|==', 'invit_code|auto_add'),
                                    'id'         => 'invit_code_auto_add_opts',
                                    'type'       => 'fieldset',
                                    'fields'     => array(
                                        array(
                                            'title'    => __('使用奖励', 'zib_language'),
                                            'subtitle' => '',
                                            'id'       => 'auto_reward',
                                            'type'     => 'fieldset',
                                            'fields'   => CFS_Module::invit_code_reward(),
                                        ),
                                        array(
                                            'title'   => __('标识前缀', 'zib_language'),
                                            'desc'    => __('对自动生成的邀请码做标记标识，方便后期查找管理', 'zib_language'),
                                            'id'      => 'auto_remarks',
                                            'default' => '',
                                            'type'    => 'text',
                                        ),
                                    ),
                                ),
                                array(
                                    'dependency'  => array('opts_type', '==', 'card_pass'),
                                    'id'          => 'card_pass_key',
                                    'type'        => 'text',
                                    'default'     => '',
                                    'placeholder' => __('请输入卡密标识', 'zib_language'),
                                ),
                            ),
                        ]),
                    ),
                    array(
                        'id'          => 'extra_delivery_content',
                        'type'        => 'textarea',
                        'title'       => __('额外发货内容', 'zib_language'),
                        'placeholder' => __('额外的发货内容(可选)，支持 HTML 代码，请注意代码规范', 'zib_language'),
                        'desc'        => __('当选择为卡密时，额外发货内容就十分有效。例如，可以将使用教程、下载地址等消息同时发送给用户。', 'zib_language'),
                        'sanitize'    => false,
                        'default'     => '',
                        'attributes'  => array(
                            'rows' => 3,
                        ),
                    ),
                ),
            ),
            array(
                'dependency' => array('shipping_type', '==', 'auto'),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    =>
                    '<code>' . __('自动发货(虚拟内容)', 'zib_language') . '</code>' . __('主要用于文本内容自动发送，用户付款后会将设置的信息通过私信和邮件的方式自动发送给用户', 'zib_language') . '
                <div class="c-yellow"><b>' . __('注意事项：', 'zib_language') . '</b>
                    <li>' . __('1.如果选择固定内容，请配合限购功能使用，因为用户即使购买多份，发货内容也是相同的，容易出现投诉问题', 'zib_language') . '</li>
                    <li>' . __('2.如果选择卡密，填写对应的标识以搜索并发送给给用户，如未创建卡密，则需先创建', 'zib_language') . '</li>
                    <li>' . __('3.选择卡密时，同时支持卡密和优惠码，选择邀请码时，支持自动创建或填写标识来选择已创建的邀请码', 'zib_language') . '</li>
                    <li>' . __('4.请正确的配置库存，已避免自动发货失败', 'zib_language') . '</li>
                    <li><a target="_blank" href="' . admin_url('users.php?page=invit_code') . '">' . __('管理邀请码', 'zib_language') . '</a> | <a target="_blank" href="' . admin_url('admin.php?page=zibpay_charge_card_page') . '">' . __('管理卡密', 'zib_language') . '</a> | <a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">' . __('管理优惠码', 'zib_language') . '</a></li>
                </div>',
            ),
            array(
                'dependency' => array('shipping_type', '==', 'manual'),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    =>
                    '<code>' . __('手动发货(虚拟内容)', 'zib_language') . '</code>' . __('用于需要商家手动处理的虚拟商品，例话费充值、软件定制、技术服务等', 'zib_language') . '
                <div class="c-yellow"><b>' . __('注意事项：', 'zib_language') . '</b>
                    <li>' . __('此方式不会要求用户填写收货地址和邮箱', 'zib_language') . '</li>
                     <li>' . __('可以配合', 'zib_language') . '<code>' . __('用户必留信息', 'zib_language') . '</code>' . __('功能使用，例如话费充值要求用户必留手机号', 'zib_language') . '</li>
                </div>',
            ),
            zib_shop_csf_module::shipping_fee('product', array('shipping_type', '==', 'express')),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('售后&服务', 'zib_language'),
        'fields' => array(
            zib_shop_csf_module::after_sale('product'),
            zib_shop_csf_module::service('product'),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('UI&样式', 'zib_language'),
        'fields' => array(
            zib_shop_csf_module::single_tab('product'),
            zib_shop_csf_module::content_layout('product'),
            zib_shop_csf_module::content_show_bg('product'),
        ),
    ));
}

if (!empty($_GET['post_type']) && $_GET['post_type'] === 'shop_product') {
    add_action('bulk_edit_custom_box', 'zib_shop_bulk_edit_custom_box_product', 50, 2);
    add_action('quick_edit_custom_box', 'zib_shop_bulk_edit_custom_box_product', 50, 2);
}
add_action('save_post', 'zib_shop_bulk_edit_save_post_product', 10, 3);
function zib_shop_bulk_edit_custom_box_product($column_name, $post_type)
{
    $permissible_posts_type = ['shop_product'];
    $_id                    = 'shop';

    if (!in_array($post_type, $permissible_posts_type) || $column_name !== 'taxonomy-shop_tag') {
        return;
    }

    $fields = array(
        array(
            'title'   => __('优惠活动', 'zib_language'),
            'id'      => 'shop_discount',
            'options' => zib_shop_get_discount_meta_options(),
            'type'    => 'checkbox',
            'inline'  => true,
            'desc'    => __('最多显示200个优惠活动', 'zib_language') . ' ' . '<a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=shop_discount&post_type=shop_product') . '">' . __('管理优惠活动', 'zib_language') . '</a>' . ' | ' . '<a target="_blank" href="' . admin_url('post-new.php?post_type=shop_product') . '">' . __('添加优惠活动', 'zib_language') . '</a>',
        ),
    );
    echo zib_get_quick_edit_custom_input($fields, $_id);
}

function zib_shop_bulk_edit_save_post_product($post_ID, $post, $update)
{

    $permissible_posts_type = ['shop_product'];
    $_id                    = 'shop';
    $screen                 = 'edit-shop_product';
    if (!$update || !in_array($post->post_type, $permissible_posts_type) || empty($_REQUEST['zib_bulk_edit'][$_id]) || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== $screen) {
        return;
    }

    $zibpay_bulk_edit = $_REQUEST['zib_bulk_edit'][$_id];
    foreach ($zibpay_bulk_edit as $field_id => $field_value) {
        if ($field_value === 'ignore' || (isset($field_value['operation']) && $field_value['operation'] === 'ignore')) {
            continue;
        }

        switch ($field_id) {
            case 'shop_discount':
                $discount_ids = !empty($field_value['val']) ? array_map('intval', (array) $field_value['val']) : array();
                wp_set_post_terms($post_ID, $discount_ids, 'shop_discount', false);
                break;
        }
    }

}

//为商品添加优惠活动选择
function zib_shop_meta_box_product_discount($post)
{
    //获取商品的优惠活动
    $discount     = get_the_terms($post->ID, 'shop_discount');
    $discount_ids = array();
    if ($discount) {
        foreach ($discount as $item) {
            $discount_ids[] = $item->term_id;
        }
    }

    $fields = array(
        array(
            'id'      => 'meta_box_product_discount',
            'options' => zib_shop_get_discount_meta_options(),
            'type'    => 'checkbox',
            'inline'  => true,
            'default' => $discount_ids,
            'desc'    => __('最多显示200个优惠活动', 'zib_language') . '<br>' . '<a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=shop_discount&post_type=shop_product') . '">' . __('管理优惠活动', 'zib_language') . '</a>',
        ),
    );

    $csf_args = array(
        'class'  => '',
        'value'  => [],
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
        'hidden' => array(
            array(
                'name'  => 'meta_box_save_product_discount', //必要，用于isset判断
                'value' => 'on',
            ),
        ),
    );

    ZCSF::instance('product_discount', $csf_args);
}

function zib_shop_get_discount_meta_options()
{
    //获取所有的优惠活动
    $terms = get_terms(array(
        'taxonomy'   => 'shop_discount',
        'hide_empty' => false,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'number'     => 200, //最多显示200个优惠活动
    ));
    if (is_wp_error($terms)) {
        return array();
    }
    $options = array();
    foreach ($terms as $term) {
        $discount_data = zib_shop_get_discount_data($term);
        $name          = $discount_data['name'];
        if (!empty($discount_data['discount_error'])) {
            switch ($discount_data['discount_error']) {
                case 'config_error':
                    $name .= '[' . __('配置无效', 'zib_language') . ']';
                    break;
                case 'time_limit_start':
                    $name .= '[' . __('未开始', 'zib_language') . ']';
                    break;
                case 'time_limit_end':
                    $name .= '[' . __('已结束', 'zib_language') . ']';
                    break;
                default:
                    $name .= '[' . __('失效', 'zib_language') . ']';
                    break;
            }
        } elseif (!empty($discount_data['discount_type'])) {
            switch ($discount_data['discount_type']) {
                case 'reduction':
                    $discount_text = sprintf(__('立减%s', 'zib_language'), $discount_data['reduction_amount']);
                    break;
                case 'discount':
                    $discount_text = sprintf(__('%s折', 'zib_language'), $discount_data['discount_amount']);
                    break;
                case 'gift':
                    $discount_text = __('赠品', 'zib_language');
                    break;
            }

            if ($discount_text !== $discount_data['name']) {
                $name .= '[' . $discount_text . ']';
            }
        }

        if (!empty($discount_data['is_important'])) {
            $name .= '[' . __('重点', 'zib_language') . ']';
        }

        $options[$term->term_id] = $name;
    }

    return $options;
}

function zib_shop_add_meta_box_product_discount()
{
    add_meta_box('shop_product_discount', __('优惠活动', 'zib_language'), 'zib_shop_meta_box_product_discount', array('shop_product'), 'side', 'high');
}
add_action('add_meta_boxes', 'zib_shop_add_meta_box_product_discount');

function zib_shop_save_meta_box_product($post_id)
{
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    if (!empty($_POST['meta_box_save_product_discount'])) {
        $discount_ids = !empty($_POST['meta_box_product_discount']) ? array_map('intval', (array) $_POST['meta_box_product_discount']) : array();
        wp_set_post_terms($post_id, $discount_ids, 'shop_discount', false);
    }
}
add_action('save_post', 'zib_shop_save_meta_box_product');

function zib_shop_get_metabox_product_opts_cfs_fields($args = array())
{

    //页面限制：新增商品、编辑商品
    if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') === false && strpos($_SERVER['SCRIPT_NAME'], 'post.php') === false) {
        return;
    }

    $post_id = !empty($_GET['post']) ? (int) $_GET['post'] : null;

    if (!$post_id) {
        return array(array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => __('您当前正在新建文章，请先配置好商品选项，', 'zib_language') . '<code>' . __('保存并刷新页面后', 'zib_language') . '</code>' . __('再配置此处', 'zib_language'),
        ));
    }

    $product_options = zib_shop_get_product_config($post_id, 'product_options') ?: array();
    $stock_opts      = array();

    // 获取所有选项组合
    $option_names  = array();
    $option_values = array();
    $split_symbol  = '$|$'; //定义一个特殊分割符号，避免选项名称中包含

    foreach ($product_options as $key => $option) {
        if (!isset($option['opts'][0]['name'])) {
            continue;
        }
        $option_names[] = $option['name'] ?? '';
        $values         = array();
        foreach ($option['opts'] as $key_2 => $opt) {
            $values[] = $key . $split_symbol . $key_2 . $split_symbol . $opt['name'];
        }
        $option_values[] = $values;
    }

    if (empty($option_values[0])) {
        return array(array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => __('未找到商品选项，如需设置此处，请先配置好商品选项，', 'zib_language') . '<code>' . __('保存并刷新页面后', 'zib_language') . '</code>' . __('再配置此处', 'zib_language'),
        ));
    }

    // 生成所有组合
    $combinations = array(array()); // 初始化为包含一个空数组
    foreach ($option_values as $key => $values) {
        $temp = array();
        foreach ($combinations as $combination) {
            foreach ($values as $value) {
                $temp[] = array_merge($combination, array($value));
            }
        }
        $combinations = $temp; // 更新组合为新生成的组合
    }

    // 构建库存选项
    foreach ($combinations as $combination) {
        $title = '';
        $id    = $args['id_prefix'] ?? ''; //前缀
        foreach ($combination as $value) {
            //分割成数组
            $value = explode($split_symbol, $value);
            $title .= '|' . $value[2];
            $id .= zib_shop_product_options_key_splicing($value[0], $value[1]);
        }

        $stock_opts[] = array_merge(array(
            'id'       => $id,
            'type'     => 'text',
            'title'    => ' ',
            'subtitle' => trim($title, '|'),
        ), $args);
    }

    return $stock_opts;
}
