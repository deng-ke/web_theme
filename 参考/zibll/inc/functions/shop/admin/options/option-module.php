<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-02-01 12:38:24
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台商品配置项模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

class zib_shop_csf_module
{
    //商品排序方式
    public static function product_orderby_options($before = [])
    {

        return array_merge(
            array(
                'modified'       => __('更新时间', 'zib_language'),
                'date'           => __('发布时间', 'zib_language'),
                'views'          => __('浏览量', 'zib_language'),
                'comment_count'  => __('评论量', 'zib_language'),
                'favorite_count' => __('收藏数量', 'zib_language'),
                'zibpay_price'   => __('售价', 'zib_language'),
                'score'          => __('评分', 'zib_language'),
                'sales_volume'   => __('销量', 'zib_language'),
                'rand'           => __('随机', 'zib_language'),
            ), $before
        );
    }

    //售后
    public static function after_sale($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的售后政策 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置售后政策，选择默认则使用主题设置中的售后政策', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('选择默认时会依次调用分类、主题设置中的售后政策，在此处可为当前商品单独设置售后政策', 'zib_language');
        }

        $fields = array(
            array(
                'id'      => 'refund',
                'type'    => 'switcher',
                'title'   => __('仅退款', 'zib_language'),
                'desc'    => __('注意：未发货的商品都可以申请仅退款', 'zib_language'),
                'default' => false,
            ),
            array(
                'dependency' => array('refund', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('退款时效', 'zib_language'),
                'id'         => 'refund_max_day',
                'class'      => 'compact',
                'type'       => 'number',
                'desc'       => __('确认收货后多少天内可退款，单位：天', 'zib_language'),
                'default'    => 7,
                'min'        => 1,
                'step'       => 1,
                'unit'       => __('天', 'zib_language'),
                'type'       => 'spinner',
            ),
            array(
                'title'   => __('退货退款', 'zib_language'),
                'id'      => 'refund_return',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('refund_return', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('退货退款时效', 'zib_language'),
                'id'         => 'refund_return_max_day',
                'class'      => 'compact',
                'desc'       => __('确认收货后多少天内可退货退款，单位：天', 'zib_language'),
                'default'    => 15,
                'min'        => 1,
                'step'       => 1,
                'unit'       => __('天', 'zib_language'),
                'type'       => 'spinner',
            ),

            /**
             * 暂未使用

            array(
            'title'   => __('换货', 'zib_language'),
            'id'      => 'replacement',
            'type'    => 'switcher',
            'default' => true,
            ),
            array(
            'dependency' => array('replacement', '!=', ''),
            'title'      => ' ',
            'subtitle'   => __('换货时效', 'zib_language'),
            'id'         => 'replacement_max_day',
            'class'      => 'compact',
            'type'       => 'number',
            'desc'       => __('确认收货后多少天内可换货，单位：天', 'zib_language'),
            'default'    => 30,
            'min'        => 1,
            'step'       => 1,
            'unit'       => __('天', 'zib_language'),
            'type'       => 'spinner',
            ),

            array(
            'title'   => __('保修', 'zib_language'),
            'id'      => 'warranty',
            'type'    => 'switcher',
            'default' => false,
            ),
            array(
            'dependency' => array('warranty', '!=', ''),
            'title'      => ' ',
            'subtitle'   => __('保修时效', 'zib_language'),
            'id'         => 'warranty_max_day',
            'class'      => 'compact',
            'type'       => 'number',
            'desc'       => __('确认收货后多少天内可保修，单位：天', 'zib_language'),
            'default'    => 365,
            'min'        => 1,
            'step'       => 1,
            'unit'       => __('天', 'zib_language'),
            'type'       => 'spinner',
            ),

             */
            //保价
            array(
                'id'      => 'insured_price',
                'type'    => 'switcher',
                'title'   => __('保价', 'zib_language'),
                'default' => true,
            ),
            array(
                'dependency' => array('insured_price', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('保价时效', 'zib_language'),
                'id'         => 'insured_price_max_day',
                'class'      => 'compact',
                'type'       => 'number',
                'desc'       => __('确认收货后多少天内可保价，单位：天', 'zib_language'),
                'default'    => 7,
                'min'        => 1,
                'step'       => 1,
                'unit'       => __('天', 'zib_language'),
                'type'       => 'spinner',
            ),
            array(
                'id'         => 'desc',
                'type'       => 'textarea',
                'title'      => __('售后说明', 'zib_language'),
                'desc'       => __('售后政策的描述、说明、规则等（支持html，注意格式规范）', 'zib_language'),
                'sanitize'   => false,
                'default'    => '',
                'attributes' => array(
                    'rows' => 2,
                ),
            ),

        );

        if ($type !== 'admin') {
            $fields =
            array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'title'   => '',
                    'default' => '',
                    'options' => array(
                        ''       => __('默认', 'zib_language'),
                        'custom' => __('自定义', 'zib_language'),
                    ),
                ),
                array(
                    'dependency' => array('type', '==', 'custom'),
                    'sanitize'   => false,
                    'id'         => 'opt',
                    'type'       => 'fieldset',
                    'fields'     => $fields,
                ),
            );
        }

        return array(
            'title'    => __('售后政策', 'zib_language'),
            'sanitize' => false,
            'desc'     => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'id'       => $id_prefix . 'after_sale_opt',
            'type'     => 'fieldset',
            'fields'   => $fields,
        );
    }

    //服务
    public static function service($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的服务保障 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置服务保障，留空则使用主题设置中的服务保障', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('留空时会依次调用分类、主题设置中的服务保障，在此处可为当前商品单独设置服务保障', 'zib_language');
        }

        return array(
            'id'           => $id_prefix . 'service',
            'type'         => 'group',
            'title'        => __('自定义服务保障', 'zib_language'),
            'desc'         => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'class'        => '',
            'button_title' => __('添加服务保障', 'zib_language'),
            'fields'       => array(
                array(
                    'id'    => 'name',
                    'class' => 'mini-input',
                    'type'  => 'text',
                    'title' => __('名称(必填)', 'zib_language'),
                ),
                array(
                    'id'         => 'desc',
                    'type'       => 'textarea',
                    'title'      => __('描述说明', 'zib_language'),
                    'desc'       => __('服务保障的描述、说明、规则等（支持html，注意格式规范）', 'zib_language'),
                    'sanitize'   => false,
                    'default'    => '',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                ),
                array(
                    'id'    => 'image',
                    'type'  => 'upload',
                    'title' => __('图标图片', 'zib_language'),
                    'desc'  => __('自定义服务保障的图标图片(可选，必须为正方形)', 'zib_language'),
                ),
            ),
        );
    }

    //限购
    public static function limit_buy($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的限购规则 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置限购规则，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的限购规则，在此处可为当前商品单独设置', 'zib_language');
        }
        $options = array(
            'off' => __('不限购', 'zib_language'),
            'on'  => __('开启限购', 'zib_language'),
        );
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        return array(
            'id'     => $id_prefix . 'limit_buy',
            'type'   => 'fieldset',
            'desc'   => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'title'  => __('商品限购', 'zib_language'),
            'fields' => array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'title'   => '',
                    'default' => $type === 'admin' ? 'off' : '',
                    'options' => $options,
                ),
                array(
                    'dependency' => array('type', '==', 'on'),
                    'title'      => ' ',
                    'subtitle'   => __('普通用户限购', 'zib_language'),
                    'id'         => 'all',
                    'default'    => 1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => __('件', 'zib_language'),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type|all', '==|>', 'on|-1'),
                    'id'         => 'auth',
                    'class'      => 'compact',
                    'title'      => ' ',
                    'subtitle'   => __('认证用户限购', 'zib_language'),
                    'default'    => -1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => __('件', 'zib_language'),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type|all', '==|>', 'on|-1'),
                    'id'         => 'vip_1',
                    'class'      => 'compact',
                    'title'      => ' ',
                    'subtitle'   => _pz('pay_user_vip_1_name', 'VIP1') . __('限购', 'zib_language'),
                    'default'    => -1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => __('件', 'zib_language'),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type|all', '==|>', 'on|-1'),
                    'id'         => 'vip_2',
                    'class'      => 'compact',
                    'title'      => ' ',
                    'subtitle'   => _pz('pay_user_vip_2_name', 'VIP2') . __('限购', 'zib_language'),
                    'desc'       => sprintf(__('-1为不限购，0为不允许购买，%1$s可购买数量需大于%2$s', 'zib_language'), _pz('pay_user_vip_2_name', 'VIP2'), _pz('pay_user_vip_1_name', 'VIP2')) . '<br>' . __('多身份用户取可购买的最大值', 'zib_language'),
                    'default'    => -1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => __('件', 'zib_language'),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'on'),
                    'id'         => 'desc',
                    'type'       => 'text',
                    'title'      => __('限购说明', 'zib_language'),
                    'desc'       => __('开启限购后，在商品详情页会显示此说明', 'zib_language'),
                    'default'    => '',
                ),
            ),
        );
    }

    //商品详情页底部内容
    public static function content_after($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的详情页底部内容 (ps:分类可单独设置)支持html代码，注意格式规范', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一详情页底部追加内容，留空则使用主题设置中的配置', 'zib_language');
        }
        return array(
            'id'         => $id_prefix . 'content_after',
            'type'       => 'textarea',
            'title'      => __('详情页底部内容', 'zib_language'),
            'desc'       => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'sanitize'   => false,
            'default'    => '',
            'attributes' => array(
                'rows' => 3,
            ),
        );
    }

    //推广返佣
    public static function rebate($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的推广返佣规则 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置推广返佣规则，选择默认则使用主题设置中的配置', 'zib_language');
        }

        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的的推广返佣规则，在此处可为当前商品单独设置（注意：积分商品没有佣金）', 'zib_language');
        }

        $options = array(
            'off'   => __('不参与', 'zib_language'),
            'ratio' => __('按金额比例返佣', 'zib_language'),
            'fixed' => __('固定金额返佣', 'zib_language'),
        );
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        return array(
            'id'     => $id_prefix . 'rebate',
            'type'   => 'fieldset',
            'desc'   => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'title'  => __('推广返佣', 'zib_language'),
            'fields' => array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'default' => $type === 'admin' ? 'off' : '',
                    'options' => $options,
                ),
                array(
                    'dependency' => array('type', '==', 'ratio'),
                    'id'         => 'all_ratio',
                    'title'      => __('普通用户佣金比例', 'zib_language'),
                    'default'    => 0,
                    'min'        => 0,
                    'max'        => 100,
                    'step'       => 2,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'ratio'),
                    'id'         => 'vip_1_ratio',
                    'title'      => _pz('pay_user_vip_1_name', 'VIP1') . __('佣金比例', 'zib_language'),
                    'default'    => 0,
                    'class'      => 'compact',
                    'min'        => 0,
                    'max'        => 100,
                    'step'       => 2,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'ratio'),
                    'id'         => 'vip_2_ratio',
                    'class'      => 'compact',
                    'title'      => _pz('pay_user_vip_2_name', 'VIP2') . __('佣金比例', 'zib_language'),
                    'default'    => 0,
                    'min'        => 0,
                    'max'        => 100,
                    'step'       => 2,
                    'unit'       => '%',
                    'type'       => 'spinner',
                    'desc'       => __('需开启推广返佣功能 ', 'zib_language') . '<a href="' . zib_get_admin_csf_url('支付付费/推广返佣') . '">' . __('【去开启】', 'zib_language') . '</a>',
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'all_fixed',
                    'title'      => __('普通用户佣金金额', 'zib_language'),
                    'default'    => 0,
                    'min'        => 0,
                    'step'       => 5,
                    'unit'       => zibpay_get_currency_unit(),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'vip_1_fixed',
                    'title'      => _pz('pay_user_vip_1_name', 'VIP1') . __('佣金金额', 'zib_language'),
                    'default'    => 0,
                    'class'      => 'compact',
                    'min'        => 0,
                    'step'       => 5,
                    'unit'       => zibpay_get_currency_unit(),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'vip_2_fixed',
                    'class'      => 'compact',
                    'title'      => _pz('pay_user_vip_2_name', 'VIP2') . __('佣金金额', 'zib_language'),
                    'default'    => 0,
                    'min'        => 0,
                    'step'       => 5,
                    'unit'       => zibpay_get_currency_unit(),
                    'desc'       => __('需开启推广返佣功能 ', 'zib_language') . '<a href="' . zib_get_admin_csf_url('支付付费/推广返佣') . '">' . __('【去开启】', 'zib_language') . '</a>',
                    'type'       => 'spinner',
                ),
            ),
        );
    }

    //运费
    public static function shipping_fee($type = 'admin', $dependency = [])
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的运费规则 (ps:分类及商品可单独设置)', 'zib_language');
        $class     = '';
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置运费规则，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的的运费规则，在此处可为当前商品单独设置', 'zib_language');
            $class = '';
        }

        $options = array(
            'free'   => __('免运费', 'zib_language'),
            'fixed'  => __('固定收费', 'zib_language'),
            'amount' => __('按付款金额收费', 'zib_language'),
        );
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }
        return array(
            'title'      => __('运费配置', 'zib_language'),
            'sanitize'   => false,
            'dependency' => $dependency,
            'desc'       => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'id'         => $id_prefix . 'shipping_fee_opt',
            'type'       => 'fieldset',
            'class'      => $class,
            'fields'     => array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'default' => $type === 'admin' ? 'free' : '',
                    'options' => $options,
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'fixed_fee',
                    'type'       => 'number',
                    'title'      => __('运费金额', 'zib_language'),
                    'default'    => '12',
                ),
                //按照付款金额阶梯收费
                array(
                    'dependency' => array('type', '==', 'amount'),
                    'id'         => 'amount_fee',
                    'type'       => 'fieldset',
                    'fields'     => array(
                        array(
                            'id'       => 'fee',
                            'type'     => 'number',
                            'title'    => ' ',
                            'subtitle' => __('基础运费', 'zib_language'),
                            'default'  => '12',
                        ),
                        array(
                            'id'       => 'free_amount',
                            'type'     => 'number',
                            'title'    => ' ',
                            'subtitle' => __('满多少免运费', 'zib_language'),
                            'desc'     => __('只针对于单一商品的单笔订单总金额', 'zib_language'),
                            'default'  => '6000',
                        ),
                    ),
                ),
                array(
                    'title'   => __('配送说明', 'zib_language'),
                    'desc'    => __('简短几个字描述配送说明，例如：24小时极速发货、顺丰包邮等等', 'zib_language'),
                    'id'      => 'desc',
                    'type'    => 'text',
                    'default' => '',
                ),
            ),
        );
    }

    //商品评论
    public static function comment($type = 'admin')
    {

        //暂未启用

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的评论规则 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置评论规则，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的的评论规则，在此处可为当前商品单独设置', 'zib_language');
        }

        $options = array(
            'off' => __('关闭', 'zib_language'),
            'on'  => __('开启', 'zib_language'),
        );

        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        return array(
            'id'      => $id_prefix . 'comment_s',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => __('商品评价', 'zib_language'),
            'desc'    => __('是否启用评论评价功能', 'zib_language') . '<div style="color: #c0826d; ">' . $desc . '</div>',
            'default' => $type === 'admin' ? 'on' : '',
            'options' => $options,
        );
    }

    //类表的显示样式配置
    public static function list_style($sub_title = '')
    {
        return array(
            'title'    => __('列表UI样式', 'zib_language'),
            'subtitle' => $sub_title,
            'id'       => 'list_style',
            'class'    => '',
            'type'     => 'fieldset',
            'fields'   => array(
                array(
                    'id'      => 'style',
                    'title'   => __('列表显示样式', 'zib_language'),
                    'type'    => 'radio',
                    'inline'  => true,
                    'options' => array(
                        ''      => __('竖向大卡片', 'zib_language'),
                        'small' => __('横向小卡片', 'zib_language'),
                    ),
                    'default' => '',
                ),
                array(
                    'dependency' => array('style', '!=', 'small'),
                    'id'         => 'thumb_scale',
                    'title'      => __('商品主图长宽比例', 'zib_language'),
                    'default'    => 100,
                    'max'        => 300,
                    'min'        => 20,
                    'step'       => 10,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
                array(
                    'id'      => 'thumb_fit',
                    'title'   => __('商品主图填充方式', 'zib_language'),
                    'default' => '',
                    'type'    => 'radio',
                    'inline'  => true,
                    'options' => array(
                        ''        => __('cover 铺满', 'zib_language'),
                        'contain' => __('contain 等比例缩放', 'zib_language'),
                    ),
                ),
                array(
                    'id'      => 'title_one_line', //截断显示
                    'title'   => __('标题只显示一行', 'zib_language'),
                    'type'    => 'switcher',
                    'default' => false,
                    'desc'    => __('关闭则显示为两行，如果大多数商品标题字数少于10个字，则建议开启', 'zib_language'),
                ),
                array(
                    'id'      => 'show_desc',
                    'title'   => __('显示商品描述', 'zib_language'),
                    'desc'    => __('样式为横向小卡片时，只有开启标题只显示一行后才会显示描述', 'zib_language'),
                    'type'    => 'switcher',
                    'default' => false,
                ),
                array(
                    'id'      => 'show_price',
                    'title'   => __('显示商品价格', 'zib_language'),
                    'type'    => 'switcher',
                    'default' => true,
                ),
                array(
                    'id'      => 'show_discount',
                    'title'   => __('显示折扣标签', 'zib_language'),
                    'type'    => 'switcher',
                    'default' => true,
                ),
                array(
                    'title'   => __('销量显示', 'zib_language'),
                    'id'      => 'show_sales',
                    'type'    => 'radio',
                    'inline'  => true,
                    'options' => array(
                        ''    => __('显示', 'zib_language'),
                        'off' => __('不显示', 'zib_language'),
                        'min' => __('超量显示', 'zib_language'),
                    ),
                    'default' => '',
                ),
                array(
                    'title'      => ' ',
                    'dependency' => array('show_sales', '==', 'min'),
                    'subtitle'   => __('销量超过多少显示', 'zib_language'),
                    'id'         => 'show_sales_min',
                    'class'      => 'compact',
                    'default'    => 10,
                    'min'        => 0,
                    'step'       => 10,
                    'unit'       => __('件', 'zib_language'),
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('style', '!=', 'small'),
                    'id'         => 'text_center',
                    'title'      => __('内容居中显示', 'zib_language'),
                    'desc'       => __('标题、价格、简介等信息居中显示', 'zib_language'),
                    'default'    => true,
                    'type'       => 'switcher',
                ),

            ),
        );
    }

    //商品详情页内容布局
    public static function content_layout($type = 'admin')
    {
        $options = array(
            'full' => __('全屏', 'zib_language'),
            'box'  => __('适应内容宽度', 'zib_language'),
            'side' => __('适应内容宽度+侧边栏', 'zib_language'),
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        $desc = __('为所有商品添加默认的布局样式(ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置布局样式，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的的布局样式，在此处可为当前商品单独设置', 'zib_language');
        }

        $fields = array(
            'id'      => $id_prefix . 'content_layout',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => __('商品详情页布局', 'zib_language'),
            'default' => $type === 'admin' ? 'full' : '',
            'desc'    => __('商品详情页宽度填充方式，用于布局显示 ', 'zib_language') . '<a target="_blank" href="https://www.zibll.com/39221.html">' . __('【查看官方教程】', 'zib_language') . '</a>' . '<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        return $fields;
    }

    //商品详情页内容布局
    public static function content_show_bg($type = 'admin')
    {
        $options = array(
            'on'  => __('显示', 'zib_language'),
            'off' => __('隐藏', 'zib_language'),
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        $desc = __('为所有商品添加默认的背景样式 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置背景样式，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的的背景样式，在此处可为当前商品单独设置', 'zib_language');
        }

        $fields = array(
            'id'      => $id_prefix . 'content_show_bg',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => __('商品详情背景盒子', 'zib_language'),
            'default' => $type === 'admin' ? 'on' : '',
            'desc'    => __('商品详情页的内容部分是否显示背景盒子，常用图片作为商品介绍，推荐隐藏，如果选择了带侧边栏布局则建议开启', 'zib_language') . '<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        return $fields;
    }

    //虚拟商品email填写
    public static function email_fill($type = 'admin')
    {
        $options = array(
            'required' => __('必填', 'zib_language'),
            'fill'     => __('选填', 'zib_language'),
            'off'      => __('关闭', 'zib_language'),
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        $desc = __('自动发货的虚拟商品是否需要用户填写email (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的配置，在此处可为当前商品单独设置', 'zib_language');
        }

        $fields = array(
            'id'      => $id_prefix . 'email_fill',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => __('自动发货商品email填写', 'zib_language'),
            'default' => $type === 'admin' ? 'required' : '',
            'desc'    => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        if ($type == 'product') {
            $fields['dependency'] = array('shipping_type', '==', 'auto');
            $fields['title']      = __('email邮箱信息', 'zib_language');
        }

        return $fields;
    }

    //虚拟商品免登录购买
    public static function guest_buy($type = 'admin')
    {
        $options = array(
            'on'  => __('允许', 'zib_language'),
            'off' => __('禁止', 'zib_language'),
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => __('默认', 'zib_language')), $options);
        }

        $desc = __('自动发货的虚拟商品是否允许免登陆购买 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的配置，在此处可为当前商品单独设置', 'zib_language');
        }

        $fields = array(
            'id'      => $id_prefix . 'guest_buy_s',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => __('自动发货商品免登陆购买', 'zib_language'),
            'default' => $type === 'admin' ? 'off' : '',
            'desc'    => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        if ($type == 'product') {
            $fields['dependency'] = array('shipping_type', '==', 'auto');
            $fields['title']      = __('免登陆购买', 'zib_language');
        }

        return $fields;
    }

    //商品TAB栏目
    public static function single_tab($type = 'admin')
    {
        $fields    = array();
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc = __('为所有商品添加默认的TAB栏目 (ps:分类及商品可单独设置)', 'zib_language');
        if ($type == 'cat') {
            $desc = __('为当前分类下的商品统一设置TAB栏目，选择默认则使用主题设置中的配置', 'zib_language');
        }
        if ($type == 'product') {
            $desc = __('默认会使用依次调用分类、主题设置中的的TAB栏目，在此处可为当前商品单独设置', 'zib_language');
        }

        $options = array(
            'disable' => __('关闭', 'zib_language'),
            'custom'  => __('自定义', 'zib_language'),
        );
        if ($type !== 'admin') {
            $options  = array_merge(array('' => __('默认', 'zib_language')), $options);
            $fields[] = array(
                'id'      => 'type',
                'type'    => 'radio',
                'inline'  => true,
                'title'   => '',
                'default' => $type === 'admin' ? 'disable' : '',
                'options' => $options,
            );
        }
        $fields[] = array(
            'dependency'   => $type !== 'admin' ? array('type', '==', 'custom') : false,
            'id'           => 'tabs',
            'type'         => 'group',
            'button_title' => __('添加栏目', 'zib_language'),
            'title'        => $type !== 'admin' ? __('自定义栏目', 'zib_language') : '',
            'sanitize'     => false,
            'fields'       => array(
                array(
                    'id'    => 'title',
                    'type'  => 'text',
                    'class' => 'mini-input',
                    'title' => __('名称（必填）', 'zib_language'),
                ),
                array(
                    'sanitize' => false,
                    'id'       => 'content',
                    'type'     => 'wp_editor',
                ),
            ),
        );

        return array(
            'title'    => __('详情页TAB栏目', 'zib_language'),
            'subtitle' => $type === 'product' ? __('添加更多自定义tab栏目', 'zib_language') : '',
            'sanitize' => false,
            'desc'     => __('TAB栏目会显示在商品详情页面，通常用来添加商品的“产品参数”、“安装须知”、“常见问题”等内容', 'zib_language') . '<div style="color: #c0826d; ">' . $desc . '</div>',
            'id'       => $id_prefix . 'single_tabs',
            'type'     => 'fieldset',
            'fields'   => $fields,
        );
    }
}
