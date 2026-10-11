<?php
/*
* @Author: Qinver
* @Url: zibll.com
* @Date: 2025-02-16 21:10:36
 * @LastEditTime : 2026-06-23 23:49:48
* @Email: 770349780@qq.com
* @Project: Zibll子比主题
* @Description: 商城功能 - 后台配置文件
* Copyright (c) 2025 by Qinver, All Rights Reserved.
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
*/

function zib_shop_csf_admin_options()
{

    $prefix = 'zibll_options';

    //页面限制：后台主题配置
    //非自身保存的ajax不执行
    $no_create = ((wp_doing_ajax() && (empty($_POST['action']) || !strstr($_POST['action'], 'csf_' . $prefix))) || (!wp_doing_ajax() && (empty($_GET['page']) || $_GET['page'] !== $prefix)));
    if ($no_create) {
        return;
    }

    $new_badge = zib_get_csf_option_new_badge();
    $imagepath = get_template_directory_uri() . '/img/';

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => __('实物商城', 'zib_language'),
        'icon'        => 'fa fa-fw fa-cart-arrow-down',
        'description' => '',
        'fields'      => array(
            array(
                'content' =>
                '<p><b>' . __('商城系统', 'zib_language') . '</b>' . __('是独立的商品发布系统，支持实物、虚拟等任意商品，支持发布、优惠、下单、发货、售后、评价等全流程功能，UI设计精美，购物体验对标淘宝京东等大厂商城APP', 'zib_language') . '</p>
            <li>' . __('销售商品需先配置好收款接口 ', 'zib_language') . '<a href="' . zib_get_admin_csf_url('支付付费/收款接口') . '">' . __('【去配置】', 'zib_language') . '</a></li>
            <li>' . sprintf(__('依赖于用户登录注册功能，如果关闭了注册登录功能，则请同时关闭此功能 ', 'zib_language') . '<a href="%s">' . __('【去查看】', 'zib_language') . '</a>', zib_get_admin_csf_url('用户互动/注册登录')) . '</li>
            <li><a target="_blank" href="https://www.zibll.com/zibll_word/%e5%95%86%e5%9f%8e%e5%8a%9f%e8%83%bd">' . __('【查看官方教程】', 'zib_language') . '</a></li>',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            array(
                'title'   => __('商城系统', 'zib_language'),
                'label'   => __('启用实物商城功能', 'zib_language'),
                'id'      => 'shop_s',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'title'       => __('商城首页链接', 'zib_language'),
                'id'          => 'shop_home_url',
                'default'     => '',
                'desc'        => __('面包屑导航等功能依赖于此处配置，您可以新建一个页面，作为商城首页，并在此处填写对应链接。 ', 'zib_language') . '<a target="_blank" href="https://www.zibll.com/39214.html">' . __('【查看官方教程】', 'zib_language') . '</a>',
                'type'        => 'text',
                'placeholder' => 'https://',
            ),
            array(
                'title'    => __('商品列表配置', 'zib_language'),
                'subtitle' => __('分类页/搜索页/用户页的列表默认样式，小工具模块可单独设置样式', 'zib_language'),
                'id'       => 'shop_list_opt',
                'type'     => 'fieldset',
                'fields'   => array(
                    array(
                        'title'   => __('默认排序方式', 'zib_language'),
                        'id'      => 'orderby',
                        'options' => zib_shop_csf_module::product_orderby_options(),
                        'type'    => 'select',
                        'default' => 'date',
                    ),
                    array(
                        'title'   => __('单页显示数量', 'zib_language'),
                        'id'      => 'count',
                        'class'   => '',
                        'default' => 12,
                        'max'     => 20,
                        'min'     => 4,
                        'step'    => 1,
                        'unit'    => __('个', 'zib_language'),
                        'type'    => 'spinner',
                    ),
                    array(
                        'id'      => 'paginate',
                        'title'   => __('翻页模式', 'zib_language'),
                        'default' => 'ajax',
                        'type'    => 'radio',
                        'inline'  => true,
                        'options' => array(
                            'ajax'   => __('AJAX追加列表翻页', 'zib_language'),
                            'number' => __('数字翻页按钮', 'zib_language'),
                        ),
                    ),
                    array(
                        'dependency' => array('paginate', '==', 'ajax'),
                        'title'      => ' ',
                        'subtitle'   => __('AJAX翻页自动加载', 'zib_language'),
                        'class'      => 'compact',
                        'id'         => 'ias_s',
                        'type'       => 'switcher',
                        'label'      => __('页面滚动到列表尽头时，自动加载下一页', 'zib_language'),
                        'default'    => true,
                    ),
                    array(
                        'dependency' => array('paginate|ias_s', '==|!=', 'ajax|'),
                        'title'      => ' ',
                        'subtitle'   => __('自动加载页数', 'zib_language'),
                        'desc'       => __('AJAX翻页自动加载最多加载几页（为0则不限制，直到加载全部商品）', 'zib_language'),
                        'id'         => 'ias_max',
                        'class'      => 'compact',
                        'default'    => 3,
                        'max'        => 10,
                        'min'        => 0,
                        'step'       => 1,
                        'unit'       => __('页', 'zib_language'),
                        'type'       => 'spinner',
                    ),
                    zib_shop_csf_module::list_style('默认商品列表UI配置'),
                ),
            ),

            array(
                'title'    => __('客服配置', 'zib_language'),
                'subtitle' => '',
                'id'       => 'shop_author_contact_opt',
                'type'     => 'fieldset',
                'desc'     => __('商城的联系客服按钮可以添加多种联系方式，但是至少需要添加一个', 'zib_language') . '<br>' . __('私信功能需要开启站内通知和私信功能 ', 'zib_language') . '<a href="' . zib_get_admin_csf_url('用户互动/消息通知') . '">' . __('【去开启】', 'zib_language') . '</a>',
                'fields'   => array(
                    array(
                        'title'   => __('站内私信', 'zib_language'),
                        'id'      => 'msg_s',
                        'type'    => 'switcher',
                        'default' => true,
                    ),
                    array(
                        'dependency' => array('msg_s', '!=', ''),
                        'id'         => 'msg_name',
                        'class'      => 'compact',
                        'title'      => ' ',
                        'subtitle'   => __('显示名称', 'zib_language'),
                        'default'    => '立即联系',
                        'type'       => 'text',
                    ),
                    array(
                        'dependency' => array('msg_s', '!=', ''),
                        'id'         => 'msg_desc',
                        'class'      => 'compact',
                        'title'      => ' ',
                        'subtitle'   => __('说明或备注', 'zib_language'),
                        'type'       => 'textarea',
                        'sanitize'   => false,
                        'default'    => '7*24小时在线，专业为您服务',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'        => ' ',
                        'subtitle'     => __('客服联系方式', 'zib_language'),
                        'id'           => 'more',
                        'type'         => 'group',
                        'desc'         => __('注意：如需要删除此处所有联系方式，仅保留私信，请在此项中仅保留一个联系方式并将名称留空即可', 'zib_language'),
                        'button_title' => __('添加客服联系方式', 'zib_language'),
                        'min'          => 0,
                        'default'      => array(
                            array(
                                'name' => '微信客服',
                                'desc' => '工作日9:00-18:00在线',
                                'icon' => 'fa fa-weixin',
                                'link' => '',
                                'img'  => $imagepath . 'qrcode.png',
                            ),
                            array(
                                'name' => 'QQ客服',
                                'desc' => '工作日9:00-18:00在线',
                                'icon' => 'fa fa-qq',
                                'link' => 'https://wpa.qq.com/msgrd?v=3&site=qq&menu=yes&uin=1234567788',
                                'img'  => '',
                            ),
                            array(
                                'name' => '电话联系',
                                'desc' => '400-888-8888转88',
                                'icon' => 'fa fa-phone',
                                'link' => 'tel://10086',
                                'img'  => '',
                            ),
                        ),
                        'fields'       => array(
                            array(
                                'title' => __('名称(必填)', 'zib_language'),
                                'id'    => 'name',
                                'type'  => 'text',
                            ),
                            array(
                                'title'      => __('说明或备注', 'zib_language'),
                                'id'         => 'desc',
                                'sanitize'   => false,
                                'type'       => 'textarea',
                                'class'      => 'compact',
                                'default'    => '',
                                'attributes' => array(
                                    'rows' => 1,
                                ),
                            ),
                            array(
                                'id'           => 'icon',
                                'class'        => 'compact',
                                'type'         => 'icon',
                                'title'        => __('图标', 'zib_language'),
                                'button_title' => __('选择图标', 'zib_language'),
                                'default'      => 'fa fa-heart',
                            ),
                            array(
                                'id'    => 'link',
                                'type'  => 'text',
                                'title' => __('跳转链接', 'zib_language'),
                            ),
                            array(
                                'id'      => 'img',
                                'class'   => 'compact',
                                'title'   => __('二维码图片', 'zib_language'),
                                'default' => '',
                                'library' => 'image',
                                'type'    => 'upload',
                            ),
                        ),
                    ),
                ),
            ),

        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => __('商品详情页', 'zib_language'),
        'icon'        => 'fa fa-fw fa-bookmark-o',
        'description' => '',
        'fields'      => array(
            array(
                'title'   => __('面包屑导航', 'zib_language'),
                'id'      => 'shop_breadcrumbs_s',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('shop_breadcrumbs_s', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('显示网站首页', 'zib_language'),
                'id'         => 'shop_breadcrumbs_home',
                'class'      => 'compact',
                'type'       => 'switcher',
                'default'    => true,
            ),
            array(
                'dependency' => array('shop_breadcrumbs_s', '!=', ''),
                'title'      => ' ',
                'label'      => __('如果您将商城首页设置为网站首页，那么请关闭此处', 'zib_language'),
                'subtitle'   => __('显示商城首页', 'zib_language'),
                'id'         => 'shop_breadcrumbs_shop_home',
                'class'      => 'compact',
                'type'       => 'switcher',
                'default'    => false,
            ),
            array(
                'dependency' => array('shop_breadcrumbs_s|shop_breadcrumbs_shop_home', '!=|!=', '|'),
                'id'         => 'shop_breadcrumbs_shop_home_name',
                'desc'       => __('请确保已经创建商城首页并设置好商城首页链接 ', 'zib_language') . '<a href="' . zib_get_admin_csf_url('商城商品/实物商城') . '">' . __('【去设置】', 'zib_language') . '</a>',
                'class'      => 'compact mini-input',
                'title'      => ' ',
                'subtitle'   => __('商城首页显示名称', 'zib_language'),
                'default'    => '商城',
                'type'       => 'text',
            ),
            zib_shop_csf_module::content_layout('admin'),
            zib_shop_csf_module::content_show_bg('admin'),
            zib_shop_csf_module::single_tab('admin'),
            zib_shop_csf_module::content_after('admin'),
            array(
                'title'   => __('详情页相关推荐', 'zib_language'),
                'id'      => 'shop_single_related_s',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('shop_single_related_s', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('相关推荐参数配置', 'zib_language'),
                'id'         => 'shop_single_related_opt',
                'class'      => 'compact',
                'type'       => 'fieldset',
                'fields'     => array(
                    array(
                        'title'   => __('标题', 'zib_language'),
                        'id'      => 'title',
                        'default' => '推荐',
                        'type'    => 'text',
                    ),
                    array(
                        'title'   => __('关联类型', 'zib_language'),
                        'id'      => 'type',
                        'type'    => 'checkbox',
                        'inline'  => true,
                        'default' => ['cat', 'discount', 'tag'],
                        'options' => array(
                            'cat'      => __('分类', 'zib_language'),
                            'discount' => __('活动', 'zib_language'),
                            'tag'      => __('标签', 'zib_language'),
                        ),
                    ),
                    array(
                        'title'   => __('排序方式', 'zib_language'),
                        'id'      => 'orderby',
                        'options' => zib_shop_csf_module::product_orderby_options(),
                        'type'    => 'select',
                        'default' => 'views',
                    ),
                    array(
                        'title'   => __('显示数量', 'zib_language'),
                        'id'      => 'count',
                        'class'   => '',
                        'default' => 12,
                        'max'     => 20,
                        'min'     => 4,
                        'step'    => 1,
                        'unit'    => __('篇', 'zib_language'),
                        'type'    => 'spinner',
                    ),
                    array(
                        'id'      => 'paginate',
                        'title'   => __('翻页按钮', 'zib_language'),
                        'default' => '',
                        'type'    => 'radio',
                        'inline'  => true,
                        'options' => array(
                            ''       => __('不翻页', 'zib_language'),
                            'ajax'   => __('AJAX追加列表翻页', 'zib_language'),
                            'number' => __('数字翻页按钮', 'zib_language'),
                        ),
                    ),
                    zib_shop_csf_module::list_style('详情页相关推荐列表UI配置'),
                ),
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => __('商品参数', 'zib_language'),
        'icon'        => 'fa fa-fw fa-trello',
        'description' => '',
        'fields'      => array(
            array(
                'content' => __('商品的大多数参数均支持', 'zib_language') . '<b>' . __('参数继承', 'zib_language') . '</b>' . __('，系统获取配置时候会自动按照商品、商品的分类、主题设置的参数依次获取 ', 'zib_language') . '<a target="_blank" href="https://www.zibll.com/39217.html">' . __('【查看官方教程】', 'zib_language') . '</a>',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            array(
                'title'       => __('商品默认主图', 'zib_language'),
                'id'          => 'shop_main_image_default',
                'default'     => '',
                'preview'     => true,
                'library'     => 'image',
                'placeholder' => __('选择图片或填写图片地址(正方形图片效果最佳)', 'zib_language'),
                'type'        => 'upload',
            ),
            array(
                'title'        => __('商品参数模板', 'zib_language'),
                'id'           => 'shop_product_params_default',
                'class'        => 'mini-flex-repeater',
                'type'         => 'repeater',
                'button_title' => __('添加商品参数', 'zib_language'),
                'desc'         => __('默认商品参数模板，每次新建商品时候，会自动调用', 'zib_language'),
                'min'          => 0,
                'default'      => array(),
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
            zib_shop_csf_module::limit_buy('admin'),
            zib_shop_csf_module::shipping_fee('admin'),
            zib_shop_csf_module::guest_buy('admin'),
            zib_shop_csf_module::email_fill('admin'),
            zib_shop_csf_module::rebate('admin'),
            zib_shop_csf_module::after_sale('admin'),
            zib_shop_csf_module::service('admin'),
        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => __('物流&发货', 'zib_language'),
        'icon'        => 'fa fa-fw fa-truck',
        'description' => '',
        'fields'      => array(
            //是否启用手动填写地址
            array(
                'title'   => __('手动填写收货地址', 'zib_language'),
                'id'      => 'shop_manual_address',
                'type'    => 'switcher',
                'default' => false,
                'desc'    => __('主题自带了中国大陆地区地址库，如果您需要在除了中国以外的地方使用，请开启此功能，用户即可手动填写地址（备注：当网站语言不为中文时，默认启用）', 'zib_language'),
            ),
            array(
                'id'      => 'express_api_sdk',
                'default' => 'kuaidi100',
                'title'   => __('快递查询接口', 'zib_language'),
                'desc'    => '<a target="_blank" href="https://www.zibll.com/39206.html">' . __('【查看官方教程】', 'zib_language') . '</a>',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'kuaidi100' => __('快递100', 'zib_language'),
                    'aliyun'    => __('阿里云', 'zib_language'),
                    'kdniao'    => __('快递鸟', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('快递查询间隔', 'zib_language'),
                'desc'    => __('通过接口查询快递信息，间隔时间越短，查询越频繁', 'zib_language'),
                'id'      => 'shop_express_query_interval',
                'default' => 240,
                'max'     => 100000000,
                'min'     => 10,
                'step'    => 10,
                'unit'    => __('分钟', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'         => 'express_kdniao_opt',
                'type'       => 'accordion',
                'title'      => __('快递鸟', 'zib_language'),
                'subtitle'   => __('接口配置', 'zib_language'),
                'accordions' => array(
                    array(
                        'title'  => __('快递鸟接口配置', 'zib_language'),
                        'fields' => array(
                            array(
                                'title'   => __('用户ID', 'zib_language'),
                                'id'      => 'appid',
                                'default' => '',
                                'type'    => 'text',
                            ),
                            array(
                                'title'   => 'API KEY',
                                'id'      => 'apikey',
                                'default' => '',
                                'type'    => 'text',
                                'desc'    => __('获取地址：快递鸟 ', 'zib_language') . '<a href="https://biz.kdniao.com/account-center/api" target="_blank">' . __('【点击跳转】', 'zib_language') . '</a>',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'id'         => 'express_kuaidi100_opt',
                'type'       => 'accordion',
                'title'      => __('快递100', 'zib_language'),
                'subtitle'   => __('接口配置', 'zib_language'),
                'accordions' => array(
                    array(
                        'title'  => __('快递100接口配置', 'zib_language'),
                        'fields' => array(
                            array(
                                'title'   => __('授权key', 'zib_language'),
                                'id'      => 'key',
                                'default' => '',
                                'type'    => 'text',
                            ),
                            array(
                                'title'   => 'customer',
                                'id'      => 'customer',
                                'default' => '',
                                'type'    => 'text',
                                'desc'    => __('申请地址：快递100 ', 'zib_language') . '<a href="https://api.kuaidi100.com/manager/v2/query/overview" target="_blank">' . __('【点击跳转】', 'zib_language') . '</a>',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'id'         => 'express_aliyun_opt',
                'type'       => 'accordion',
                'title'      => __('阿里云快递', 'zib_language'),
                'subtitle'   => __('接口配置', 'zib_language'),
                'accordions' => array(
                    array(
                        'title'  => __('阿里云全球快递物流查询接口配置', 'zib_language'),
                        'fields' => array(
                            array(
                                'title'   => 'AppCode',
                                'id'      => 'appcode',
                                'default' => '',
                                'type'    => 'text',
                                'desc'    => __('申请地址：全球快递物流查询 ', 'zib_language') . '<a href="https://market.aliyun.com/apimarket/detail/cmapi023201#sku=yuncode17201000019" target="_blank">' . __('【点击跳转】', 'zib_language') . '</a>',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'title'   => __('物流公司', 'zib_language'),
                'id'      => 'shop_shipping_company',
                'type'    => 'textarea',
                'desc'    => __('添加允许发货的物流公司，用于发货时选择，用逗号隔开', 'zib_language'),
                'default' => '顺丰快递,圆通快递,中通快递,韵达快递,申通快递,菜鸟快递,邮政快递,极兔速递,德邦快递,京东快递,EMS,百世快递',
            ),
            array(
                'content' => '<div><b>快递接口测试</b>
                <br/>输入快递单号，在此发送测试快递接口</div>
                <ajaxform class="ajax-form">
                <div class="">
                    <input class="mt6 mr10" type="text" style="max-width:400px;" ajax-name="express_number" placeholder="快递单号">
                </div>
                 <div class="">
                    <input class="mt6 mr10" type="text" style="max-width:400px;" ajax-name="phone" placeholder="手机号">
                    <div class="c-yellow">(选填)部分快递需要传入收件人手机号才能查询</div>
                </div>
                <a href="javascript:;" class="but jb-yellow ajax-submit mt6"><i class="fa fa-paper-plane-o"></i>查询快递</a>
                <div class="ajax-notice mt10"></div>
                <input type="hidden" ajax-name="action" value="test_express_query">
                </ajaxform>',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),

        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => __('其它设置', 'zib_language'),
        'icon'        => 'fa fa-fw fa-life-ring',
        'description' => '',
        'fields'      => array(
            array(
                'title'   => __('销量显示', 'zib_language'),
                'id'      => 'shop_sales_show',
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
                'dependency' => array('shop_sales_show', '==', 'min'),
                'title'      => ' ',
                'subtitle'   => __('销量超过多少显示', 'zib_language'),
                'id'         => 'shop_sales_show_min',
                'class'      => 'compact',
                'default'    => 10,
                'min'        => 0,
                'step'       => 10,
                'unit'       => __('件', 'zib_language'),
                'type'       => 'spinner',
            ),

            array(
                'title'   => __('确认收货时效', 'zib_language'),
                'id'      => 'order_receipt_max_day',
                'type'    => 'number',
                'desc'    => __('发货后，用户最大确认收货时间，超过后将自动确认收货', 'zib_language'),
                'default' => 15,
                'min'     => 1,
                'step'    => 1,
                'unit'    => __('天', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'title'   => __('评价时效', 'zib_language'),
                'id'      => 'shop_comment_max_day',
                'type'    => 'number',
                'desc'    => __('用户确认收货后，多少天以内可以评价，超时则自动好评', 'zib_language'),
                'default' => 15,
                'min'     => 1,
                'step'    => 1,
                'unit'    => __('天', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'title'   => __('评价内容占位符', 'zib_language'),
                'id'      => 'shop_comment_placeholder',
                'class'   => 'compact',
                'type'    => 'text',
                'default' => '展开说说对商品的看法吧~',
            ),
            //评论允许上传的图片数量，填0则不允许上传图片
            array(
                'title'   => __('评价上传图片', 'zib_language'),
                'desc'    => __('评价时允许上传的图片数量，填0则不允许上传图片', 'zib_language'),
                'class'   => 'compact',
                'id'      => 'shop_comment_img_num',
                'type'    => 'spinner',
                'default' => 6,
                'min'     => 0,
                'max'     => 20,
                'step'    => 1,
            ),
            array(
                'title'   => __('售后退货时效', 'zib_language'),
                'id'      => 'order_after_sale_return_express_max_day',
                'type'    => 'number',
                'desc'    => __('用户售后退货的最大发货时间，超时则自动取消售后', 'zib_language'),
                'default' => 7,
                'min'     => 1,
                'step'    => 1,
                'unit'    => __('天', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'title'      => __('售后可选原因', 'zib_language'),
                'subtitle'   => __('申请售后时用户可以选择的原因', 'zib_language'),
                'id'         => 'after_sale_reason',
                'sanitize'   => false,
                'type'       => 'accordion',
                'accordions' => array(
                    array(
                        'title'  => __('仅退款原因', 'zib_language'),
                        'fields' => array(
                            array(
                                'id'           => 'refund',
                                'class'        => 'mini-flex-repeater',
                                'type'         => 'repeater',
                                'button_title' => __('添加原因', 'zib_language'),
                                'default'      => array(
                                    ['t' => '与商家协商一致'],
                                    ['t' => '质量问题'],
                                    ['t' => '商品不喜欢'],
                                    ['t' => '物流问题'],
                                    ['t' => '退运费'],
                                    ['t' => '其他原因'],
                                ),
                                'fields'       => array(
                                    array(
                                        'default' => '',
                                        'id'      => 't',
                                        'type'    => 'text',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'title'  => __('退货退款原因', 'zib_language'),
                        'fields' => array(
                            array(
                                'id'           => 'refund_return',
                                'class'        => 'mini-flex-repeater',
                                'type'         => 'repeater',
                                'button_title' => __('添加原因', 'zib_language'),
                                'default'      => array(
                                    ['t' => '不想要了'],
                                    ['t' => '与商家协商一致'],
                                    ['t' => '质量问题'],
                                    ['t' => '商品不喜欢'],
                                    ['t' => '物流问题'],
                                    ['t' => '其他原因'],
                                ),
                                'fields'       => array(
                                    array(
                                        'default' => '',
                                        'id'      => 't',
                                        'type'    => 'text',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            //商家模式
            array(
                'id'      => 'shop_author_show',
                'class'   => '',
                'title'   => __('显示商家信息', 'zib_language'),
                'desc'    => __('如果网站有多个管理员在后台发布商品，则推荐打开（暂不支持非管理员发布）', 'zib_language') . '<br>' . __('如需在商家的个人主页显示商品列表，请在页面显示/用户主页中设置是否启用及排序 ', 'zib_language') . '<a href="' . zib_get_admin_csf_url('页面显示/用户主页') . '">' . __('【去设置】', 'zib_language') . '</a>',
                'default' => true,
                'type'    => 'switcher',
            ),
            //固定链接设置
            array(
                'id'      => 'shop_rewrite_suffix_html_s',
                'class'   => '',
                'title'   => __('链接URL后缀.html', 'zib_language'),
                'desc'    => __('商品详情页URL将以.html结尾，有利于SEO', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'title'      => __('链接URL别名', 'zib_language'),
                'subtitle'   => __('商品页面URL别名', 'zib_language'), //产品
                'id'         => 'shop_product_rewrite_slug',
                'default'    => 'shop',
                'class'      => 'mini-input',
                'attributes' => array(
                    'data-readonly-id' => 'shop_slug',
                    'readonly'         => 'readonly',
                ),
                'type'       => 'text',
            ),
            array(
                'title'      => ' ',
                'subtitle'   => __('购物车页面URL别名', 'zib_language'), //产品
                'id'         => 'shop_cart_rewrite_slug',
                'default'    => 'cart',
                'class'      => 'compact mini-input',
                'attributes' => array(
                    'data-readonly-id' => 'shop_slug',
                    'readonly'         => 'readonly',
                ),
                'type'       => 'text',
                'desc'       => __('URL别名为开启固定链接之后对应网址的地址目录', 'zib_language') . '<div style="color:#ff4021;"><i class="fa fa-fw fa-info-circle fa-fw"></i>' . __('如非必要，请勿修改', 'zib_language') . '</div>
                <br><a href="javascript:;" class="but jb-yellow remove-readonly" readonly-id="shop_slug">' . __('我要修改', 'zib_language') . '</a>',
            ),
        ),
    ));
}
add_action('after_setup_theme', 'zib_shop_csf_admin_options');
