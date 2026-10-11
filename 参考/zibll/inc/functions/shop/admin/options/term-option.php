<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-05-25 13:54:37
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商品分类配置项
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

function zib_shop_admin_term_extend_metabox()
{
    //页面限制：分类、标签、优惠
    if (strpos($_SERVER['SCRIPT_NAME'], 'edit-tags.php') === false
        && strpos($_SERVER['SCRIPT_NAME'], 'term.php') === false
        && (empty($_REQUEST['action']) || empty($_REQUEST['taxonomy']))
    ) {
        return;
    }

    $cat_prefix      = 'shop_cat_config';
    $discount_prefix = 'shop_discount_config';
    $tag_prefix      = 'shop_tag_config';

    CSF::createTaxonomyOptions($cat_prefix, array(
        'title'     => __('分类选项', 'zib_language'),
        'taxonomy'  => 'shop_cat',
        'data_type' => 'serialize',
    ));

    CSF::createTaxonomyOptions($discount_prefix, array(
        'title'     => __('优惠配置', 'zib_language'),
        'taxonomy'  => 'shop_discount',
        'data_type' => 'serialize',
    ));

    CSF::createTaxonomyOptions($tag_prefix, array(
        'title'     => __('标签配置', 'zib_language'),
        'taxonomy'  => 'shop_tag',
        'data_type' => 'serialize',
    ));

    //分类配置
    CSF::createSection($cat_prefix, array(
        'title'  => __('商品统一参数配置', 'zib_language'),
        'fields' => array(
            zib_shop_csf_module::limit_buy('cat'),
            zib_shop_csf_module::shipping_fee('cat'),
            zib_shop_csf_module::guest_buy('cat'),
            zib_shop_csf_module::email_fill('cat'),
            zib_shop_csf_module::after_sale('cat'),
            zib_shop_csf_module::rebate('cat'),
            zib_shop_csf_module::service('cat'),
            zib_shop_csf_module::single_tab('cat'),
            zib_shop_csf_module::content_after('cat'),
            zib_shop_csf_module::content_layout('cat'),
            zib_shop_csf_module::content_show_bg('cat'),
        ),
    ));

    //分类配置
    CSF::createSection($cat_prefix, array(
        'title'  => __('分类页面UI配置', 'zib_language'),
        'fields' => array(
            array(
                'id'      => 'list_style_style',
                'title'   => __('列表显示样式', 'zib_language'),
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'default' => __('默认(尊循主题配置)', 'zib_language'),
                    ''        => __('竖向大卡片', 'zib_language'),
                    'small'   => __('横向小卡片', 'zib_language'),
                ),
                'default' => 'default',
            ),
        ),
    ));

    //标签
    CSF::createSection($tag_prefix, array(
        'title'  => __('标签配置', 'zib_language'),
        'fields' => array( //商品金额限制
            array(
                'title'   => __('优先级设置', 'zib_language'),
                'id'      => 'priority',
                'desc'    => '<div class="c-yellow">' . __('同一种商品有多个标签时，优先级将影响显示顺序', 'zib_language') . '<br>' . __('数值越小，优先级越高，则先显示（相同优先级，则按标签创建的时间排序，先创建的先显示）', 'zib_language') . '</div>',
                'type'    => 'spinner',
                'default' => 50,
            ),
            array(
                'title'   => __('标签颜色', 'zib_language'),
                'id'      => 'class',
                'class'   => 'compact skin-color',
                'default' => '',
                'type'    => 'palette',
                'options' => CFS_Module::zib_palette([
                    '' => array('rgb(213, 213, 213)'),
                ]),
            ),
            array(
                'title'   => __('重点标签', 'zib_language'),
                'id'      => 'is_important',
                'desc'    => __('重点标签将在重要位置着重显示，注意：一个商品选择了多个重点标签时，则按照优先级的第一个为准', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));

    //优惠优先级
    CSF::createSection($discount_prefix, array(
        'title'  => __('配置', 'zib_language'),
        'fields' => array( //商品金额限制
            array(
                'title'   => __('小标签', 'zib_language'),
                'id'      => 'small_badge',
                'desc'    => __('简短几个字的标签，描述优惠政策，例如：立减200、限时5折、买2送1等', 'zib_language'),
                'type'    => 'text',
                'default' => '',
            ),
            array(
                'title'   => __('优先级设置', 'zib_language'),
                'id'      => 'priority',
                'desc'    => '<div class="c-yellow">' . __('同一种商品参加了多个打折、立减活动，优先级就十分重要，直接影响优惠计算先后的顺序', 'zib_language') . '<br>' . __('数值越小，优先级越高，则先计算（相同优先级，则按优惠创建的时间排序，先创建的先计算）', 'zib_language') . '</div>',
                'type'    => 'spinner',
                'default' => 50,
            ),
            array(
                'title'   => __('重点活动', 'zib_language'),
                'id'      => 'is_important',
                'desc'    => __('重点活动将在商品金额位置着重显示，注意：一个商品选择了多个重点标签时，则按照优先级的第一个为准', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('重点活动背景色', 'zib_language'),
                'id'      => 'important_class',
                'class'   => 'compact skin-color',
                'default' => 'jb-red',
                'type'    => 'palette',
                'options' => CFS_Module::zib_palette([], ['b', 'jb']),
            ),
        ),
    ));

    //优惠配置
    CSF::createSection($discount_prefix, array(
        'title'  => __('优惠政策', 'zib_language'),
        'fields' => array(
            array(
                'type'    => 'submessage',
                'style'   => 'danger',
                'content' => '<b>' . __('重要提醒：', 'zib_language') . '</b>' . __('请合理配置优惠幅度和参与限制，避免出现因优惠配置问题而导致下单金额异常的情况', 'zib_language'),
            ),
            array(
                'title'   => __('优惠计算范围', 'zib_language'),
                'id'      => 'discount_scope',
                'type'    => 'button_set',
                'options' => array(
                    'item'    => __('单件商品', 'zib_language'),
                    'product' => __('商品', 'zib_language'),
                    'author'  => __('店铺', 'zib_language'),
                    'order'   => __('订单', 'zib_language'),
                ),
                'default' => 'item',
            ),
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '<b>' . __('范围配置说明：只影响立减优惠和金额限制范围', 'zib_language') . '</b>
                <ul>
                <li><code>' . __('单件商品', 'zib_language') . '</code>：' . sprintf(__('针对每一件商品（例如：立减%1$s，如果用户一个商品同时下单了5件，则每件优惠%2$s，一共优惠%3$s）', 'zib_language'), zibpay_format_local_price_text(10), zibpay_format_local_price_text(10), zibpay_format_local_price_text(50)) . '</li>
                <li><code>' . __('商品', 'zib_language') . '</code>：' . sprintf(__('针对商品总量（例如：立减%1$s，如果用户一个商品同时下单了5件，则一共优惠%2$s，每件优惠%3$s）', 'zib_language'), zibpay_format_local_price_text(10), zibpay_format_local_price_text(10), zibpay_format_local_price_text(2)) . '</li>
                <li><code>' . __('店铺', 'zib_language') . '</code>：' . sprintf(__('针对店铺总量（例如：立减%1$s，如果用户在同一家店铺里购买了多件商品，则这些商品一共优惠%2$s），类似于店铺优惠', 'zib_language'), zibpay_format_local_price_text(10), zibpay_format_local_price_text(10)) . '</li>
                <li><code>' . __('订单', 'zib_language') . '</code>：' . sprintf(__('针对订单总量（例如：立减%1$s，用户单笔订单不管多少件商品，也不管在哪家店铺购买，都一共优惠%2$s）类似于：跨店优惠、平台优惠', 'zib_language'), zibpay_format_local_price_text(10), zibpay_format_local_price_text(10)) . '</li>
                <li>' . __('注意：同理，如果设置了金额限制，则也是按照相同范围计算是否达标，打折方式的优惠计算，是按照单个商品的优惠金额计算的', 'zib_language') . '</li>
                <li>' . __('单件商品和商品的区别：主要针对于同一个商品单笔下单多件时进行区分', 'zib_language') . '</li>
                </ul>
                ',
            ),
            array(
                'title'   => __('优惠类型', 'zib_language'),
                'id'      => 'discount_type',
                'type'    => 'button_set',
                'options' => array(
                    'reduction' => __('立减', 'zib_language'),
                    'discount'  => __('打折', 'zib_language'),
                    'gift'      => __('买赠', 'zib_language'),
                ),
                'default' => 'reduction',
            ),
            array(
                'dependency' => array('discount_type', '==', 'reduction'),
                'title'      => __('立减金额', 'zib_language'),
                'id'         => 'reduction_amount',
                'type'       => 'number',
                'default'    => 0,
            ),
            array(
                'dependency' => array('discount_type', '==', 'discount'),
                'title'      => __('几点几折', 'zib_language'),
                'desc'       => __('必须在0.01-9.99之间，例如：5.5折，输入5.5', 'zib_language'),
                'id'         => 'discount_amount',
                'type'       => 'number',
                'default'    => 0,
                'unit'       => __('折', 'zib_language'),
            ),
            array(
                'dependency'   => array('discount_type', '==', 'gift'),
                'id'           => 'gift_config',
                'type'         => 'group',
                'button_title' => __('添加赠品', 'zib_language'),
                'desc'         => __('注意：除了其它物品，其他相同类型的赠品只能添加一个', 'zib_language'),
                'fields'       => array(
                    array(
                        'title'   => __('类型', 'zib_language'),
                        'id'      => 'gift_type',
                        'inline'  => true,
                        'type'    => 'radio',
                        'options' => array(
                            'vip_1'          => _pz('pay_user_vip_1_name'),
                            'vip_2'          => _pz('pay_user_vip_2_name'),
                            'auth'           => __('认证用户', 'zib_language'),
                            'level_integral' => __('经验值', 'zib_language'),
                            'points'         => __('积分', 'zib_language'),
                            'other'          => __('其它物品', 'zib_language'),
                            //   'product'        => '商品'  //暂未启用
                        ),
                        'default' => 'product',
                    ),
                    array(
                        'dependency' => array('gift_type', 'any', 'vip_1,vip_2'),
                        'title'      => __('会员赠送时长', 'zib_language'),
                        'desc'       => __('单位为天，填', 'zib_language') . '<code>Permanent</code>' . __('为永久', 'zib_language') . '<br>' . __('注意：1.已经是会员用户，不会降级赠送', 'zib_language') . '<br>' . __('2.已经是永久会员的，也不会赠送', 'zib_language'),
                        'id'         => 'vip_time',
                        'default'    => '',
                        'class'      => 'compact',
                        'type'       => 'text',
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'level_integral'),
                        'title'      => __('经验值', 'zib_language'),
                        'id'         => 'level_integral',
                        'default'    => 0,
                        'type'       => 'number',
                        'unit'       => __('经验值', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'points'),
                        'title'      => __('积分', 'zib_language'),
                        'id'         => 'points',
                        'default'    => 0,
                        'type'       => 'number',
                        'unit'       => __('积分', 'zib_language'),
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'product'),
                        'title'      => __('商品ID', 'zib_language'),
                        'desc'       => __('输入需要赠送的商品ID，建议选择没有商品选项的商品，如果有商品选项，则自动获取第一个选项', 'zib_language'),
                        'id'         => 'product_id',
                        'default'    => 0,
                        'type'       => 'number',
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'auth'),
                        'desc'       => __('如果用户已经是认证用户，则不参与赠送', 'zib_language'),
                        'id'         => 'auth_info',
                        'type'       => 'fieldset',
                        'class'      => 'compact',
                        'fields'     => array(
                            array(
                                'title' => __('认证名称', 'zib_language'),
                                'id'    => 'name',
                                'type'  => 'text',
                            ),
                            array(
                                'title' => __('认证简介', 'zib_language'),
                                'class' => 'compact',
                                'id'    => 'desc',
                                'type'  => 'text',
                            ),
                        ),
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'other'),
                        'desc'       => '',
                        'id'         => 'other_info',
                        'type'       => 'fieldset',
                        'class'      => 'compact',
                        'fields'     => array(
                            array(
                                'title' => __('物品名称', 'zib_language'),
                                'id'    => 'name',
                                'type'  => 'text',
                            ),
                            array(
                                'title' => __('赠送说明', 'zib_language'),
                                'class' => 'compact',
                                'id'    => 'desc',
                                'type'  => 'text',
                            ),
                        ),
                    ),
                ),
            ),
        )));

    CSF::createSection($discount_prefix, array(
        'title'  => __('优惠限制', 'zib_language'),
        'fields' => array( //商品金额限制
            array(
                'title'   => __('金额限制', 'zib_language'),
                'desc'    => __('根据设置的范围，计算', 'zib_language') . '<code>' . __('总原价', 'zib_language') . '</code>' . __('是否达标', 'zib_language'),
                'id'      => 'price_limit',
                'type'    => 'number',
                'default' => 0,
            ),
            array(
                'title'   => __('用户身份限制', 'zib_language'),
                'id'      => 'user_limit',
                'type'    => 'radio',
                'inline'  => true,
                'desc'    => __('哪些用户可参与优惠', 'zib_language'),
                'options' => array(
                    ''      => __('不限制', 'zib_language'),
                    'vip'   => __('VIP1及以以上', 'zib_language'),
                    'vip_2' => 'VIP2',
                    'auth'  => __('认证用户', 'zib_language'),
                ),
                'default' => '',
            ),
            array(
                'title'   => __('限时优惠', 'zib_language'),
                'id'      => 'time_limit',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('time_limit', '!=', ''),
                'id'         => 'time_limit_config',
                'type'       => 'fieldset',
                'fields'     => array(
                    //开始时间
                    array(
                        'title'    => ' ',
                        'subtitle' => __('开始时间(可选)', 'zib_language'),
                        'id'       => 'start',
                        'type'     => 'date',
                        'settings' => array(
                            'dateFormat'  => 'yy-mm-dd 00:00:00',
                            'changeMonth' => true,
                            'changeYear'  => true,
                        ),
                        'default'  => '',
                    ),
                    //结束时间
                    array(
                        'title'    => ' ',
                        'subtitle' => __('结束时间(必填)', 'zib_language'),
                        'class'    => 'compact',
                        'id'       => 'end',
                        'type'     => 'date',
                        'settings' => array(
                            'dateFormat'  => 'yy-mm-dd 23:59:59',
                            'changeMonth' => true,
                            'changeYear'  => true,
                        ),
                        'default'  => '',
                    ),
                    array(
                        'title'   => __('倒计时', 'zib_language'),
                        'id'      => 'countdown',
                        'desc'    => __('启用后，会在醒目位置显示倒计时', 'zib_language'),
                        'type'    => 'switcher',
                        'default' => false,
                    ),
                ),
            ),

        ),
    ));

}
add_action('after_setup_theme', 'zib_shop_admin_term_extend_metabox');
