<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-05-25 16:06:10
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统|翻译函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 获取商城 JS 文案
 *
 * @return array<string, string>
 */
function zib_shop_get_js_i18n_strings($data = [])
{
    $strings = array(
        // 评分
        'shop_rating_very_bad'              => __('非常差', 'zib_language'),
        'shop_rating_bad'                   => __('较差', 'zib_language'),
        'shop_rating_normal'                => __('一般', 'zib_language'),
        'shop_rating_good'                  => __('较好', 'zib_language'),
        'shop_rating_very_good'             => __('非常好', 'zib_language'),

        // 商品选项 / 库存
        'shop_view_list'                    => __('列表', 'zib_language'),
        'shop_view_image'                   => __('图片', 'zib_language'),
        'shop_quantity'                     => __('数量', 'zib_language'),
        'shop_out_of_stock'                 => __('缺货', 'zib_language'),
        'shop_stock'                        => __('库存: %1$s', 'zib_language'),

        // 模态框 / 表单
        'shop_confirm_order'                => __('确认订单', 'zib_language'),
        'shop_remark'                       => __('备注', 'zib_language'),
        'shop_remark_placeholder'           => __('请输入备注', 'zib_language'),
        'shop_fill_required'                => __('请填写必要信息', 'zib_language'),
        'shop_confirm'                      => __('确认', 'zib_language'),
        'shop_address_edit'                 => __('编辑收货地址', 'zib_language'),
        'shop_address_add'                  => __('添加收货地址', 'zib_language'),
        'shop_address_name_ph'              => __('收货人姓名', 'zib_language'),
        'shop_address_phone_ph'             => __('手机号码', 'zib_language'),
        'shop_address_province_ph'          => __('请输入省份', 'zib_language'),
        'shop_address_city_ph'              => __('请输入城市', 'zib_language'),
        'shop_address_county_ph'            => __('请输入区县', 'zib_language'),
        'shop_address_detail_ph'            => __('详细地址，如街道、门牌号等', 'zib_language'),
        'shop_address_tag_custom_ph'        => __('自定义标签 最多5个字', 'zib_language'),
        'shop_custom'                       => __('自定义', 'zib_language'),
        'shop_tag_home'                     => __('家', 'zib_language'),
        'shop_tag_company'                  => __('公司', 'zib_language'),
        'shop_tag_school'                   => __('学校', 'zib_language'),

        // 地址校验
        'shop_enter_name'                   => __('请输入收货人姓名', 'zib_language'),
        'shop_enter_phone'                  => __('请输入手机号码', 'zib_language'),
        'shop_phone_invalid'                => __('请输入正确的手机号码', 'zib_language'),
        'shop_region_incomplete'            => __('地区信息不完整', 'zib_language'),
        'shop_enter_address'                => __('请输入详细地址', 'zib_language'),
        'shop_enter_email'                  => __('请输入邮箱', 'zib_language'),
        'shop_select_address'               => __('请选择收货地址', 'zib_language'),
        'shop_delete_address'               => __('确定要删除这个地址吗？', 'zib_language'),
        'shop_delete_success'               => __('删除成功', 'zib_language'),
        'shop_delete_failed'                => __('删除失败', 'zib_language'),

        // 商品 / 购物车
        'shop_product_params'               => __('商品参数', 'zib_language'),
        'shop_product_service'              => __('商品服务', 'zib_language'),
        'shop_discount_detail'              => __('优惠详情', 'zib_language'),
        'shop_gift_detail'                  => __('赠品详情', 'zib_language'),
        'shop_discount_info'                => __('优惠信息', 'zib_language'),
        'shop_select_option'                => __('请选择商品选项', 'zib_language'),
        'shop_remove_selected'              => __('确定要移出选中商品吗？', 'zib_language'),
        'shop_remove_item'                  => __('确定要移出该商品吗？', 'zib_language'),
        'shop_stock_insufficient'           => __('商品库存不足', 'zib_language'),
        'shop_select_quantity'              => __('请先选择购买数量', 'zib_language'),
        'shop_mixed_pay_mode'               => __('请勿同时选择积分和现金商品', 'zib_language'),
        'shop_stock_low'                    => __('库存不足', 'zib_language'),
        'shop_limit_buy'                    => __('当前商品限购%1$s件', 'zib_language'),
        'shop_limit_cannot_buy'             => __('当前商品限购，无法购买', 'zib_language'),
        'shop_select_product_option'        => __('请选择商品[%1$s]的商品选项', 'zib_language'),
        'shop_product_stock_low'            => __('商品[%1$s]库存不足，请调整选择', 'zib_language'),
        'shop_product_limit_pieces'         => __('商品[%1$s]限购%2$s件，请调整选择', 'zib_language'),
        'shop_product_limit_none'           => __('商品[%1$s]限购，无法购买，请调整选择', 'zib_language'),
        'shop_update_cart_failed'           => __('更新购物车数据失败', 'zib_language'),
        'shop_select_field'                 => __('请选择%1$s', 'zib_language'),
        'shop_enter_field'                  => __('请输入%1$s', 'zib_language'),
        'shop_fill_fields'                  => __('请填写%1$s', 'zib_language'),
        'shop_network_error'                => __('网络错误，请稍后重试', 'zib_language'),

        // 优惠 / 赠品
        'shop_gift_auth'                    => __('认证资格', 'zib_language'),
        'shop_gift_exp'                     => __('经验值', 'zib_language'),
        'shop_gift_points'                  => __('积分', 'zib_language'),
        'shop_gift_product'                 => __('商品', 'zib_language'),
        'shop_permanent'                    => __('永久', 'zib_language'),
        'shop_day_unit'                     => __('天', 'zib_language'),
        'shop_discount_reduce'              => __('立减', 'zib_language'),
        'shop_discount_fold'                => __('%1$s折', 'zib_language'),
        'shop_discount_off'                 => __('%1$s折优惠', 'zib_language'),
        'shop_limit_single'                 => __('单价', 'zib_language'),
        'shop_limit_product'                => __('商品', 'zib_language'),
        'shop_limit_store'                  => __('店铺', 'zib_language'),
        'shop_limit_cross'                  => __('跨店', 'zib_language'),
        'shop_limit_full'                   => __('%1$s满%2$s可用', 'zib_language'),
        'shop_vip_available'                => __('VIP可用', 'zib_language'),
        'shop_vip2_available'               => __('VIP2可用', 'zib_language'),
        'shop_auth_user_available'          => __('认证用户可用', 'zib_language'),
        'shop_activity_remaining'           => __('活动仅剩', 'zib_language'),
        'shop_countdown_ended'              => __('已结束', 'zib_language'),
        'shop_time_start'                   => __('开始', 'zib_language'),
        'shop_time_end'                     => __('结束', 'zib_language'),
        'shop_gift_section'                 => __('赠品', 'zib_language'),

        // 限购说明
        'shop_limit_label'                  => __('限购：%1$s', 'zib_language'),
        'shop_limit_purchased'              => __('已限购', 'zib_language'),
        'shop_limit_pieces'                 => __('限购%1$s件', 'zib_language'),
        'shop_limit_no_limit'               => __('不限购', 'zib_language'),
        'shop_limit_unavailable'            => __('无法购买', 'zib_language'),
        'shop_limit_rules_title'            => __('商品限购', 'zib_language'),
        'shop_limit_rules_hint'             => __('查看限购规则', 'zib_language'),
        'shop_limit_bought'                 => __('此商品您已下单%1$s件，%2$s', 'zib_language'),
        'shop_limit_can_buy_more'           => __('还可购买%1$s件', 'zib_language'),
        'shop_limit_cannot_buy_more'        => __('已无法购买', 'zib_language'),
        'shop_limit_can_buy'                => __('此商品您可购买%1$s件', 'zib_language'),

        // 数量输入
        'shop_input_quantity'               => __('请输入数量', 'zib_language'),
        'shop_qty_min_warning'              => __('最少1$件', 'zib_language'),
        'shop_qty_max_warning'              => __('最多1$件', 'zib_language'),
        'shop_max_purchase'                 => __('最多可购买%1$s件', 'zib_language'),
        'shop_max_qty_exceed'               => __('最大数量不能超过%1$s', 'zib_language'),
        'shop_min_qty_below'                => __('最小数量不能低于%1$s', 'zib_language'),
        'shop_collapse_info'                => __('收起更多信息', 'zib_language'),
        'shop_expand_info'                  => __('展开全部信息', 'zib_language'),

        // 地址列表 / 表单 UI
        'shop_address_title'                => __('收货地址', 'zib_language'),
        'shop_address_default'              => __('默认', 'zib_language'),
        'shop_address_set_default'          => __('设为默认', 'zib_language'),
        'shop_address_edit_btn'             => __('编辑', 'zib_language'),
        'shop_address_delete_btn'           => __('删除', 'zib_language'),
        'shop_address_empty'                => __('您还没有添加收货地址', 'zib_language'),
        'shop_address_add_now'              => __('立即添加', 'zib_language'),
        'shop_address_add_new'              => __('添加新地址', 'zib_language'),
        'shop_select_province'              => __('选择省份', 'zib_language'),
        'shop_select_city'                  => __('选择城市', 'zib_language'),
        'shop_select_county'                => __('选择区县', 'zib_language'),
        'shop_address_tag_label'            => __('地址标签：', 'zib_language'),
        'shop_address_set_default_checkbox' => __('设为默认收货地址', 'zib_language'),
        'shop_cancel'                       => __('取消', 'zib_language'),
        'shop_save'                         => __('保存', 'zib_language'),
        'shop_total_discount'               => __('共计优惠', 'zib_language'),
        'shop_discount_fold_unit'           => __('折', 'zib_language'),
        'shop_save_prefix'                  => __('省', 'zib_language'),
    );

    return array_merge($strings, $data);
}
add_filter('zib_js_i18n_strings', 'zib_shop_get_js_i18n_strings');
