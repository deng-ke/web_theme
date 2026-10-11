<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-05-25 16:52:38
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|页面UI模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

//前台加载css和js文件
function zib_shop_enqueue_script()
{
    wp_enqueue_script('shop', ZIB_SHOP_ASSETS_URI . '/js/main.min.js', array('loader_js'), THEME_VERSION, true);
    wp_enqueue_style('shop', ZIB_SHOP_ASSETS_URI . '/css/main.min.css', array(), THEME_VERSION, 'all');
}

//只要开启了商城功能就加载
if (!is_admin()) {
    add_action('wp_enqueue_scripts', 'zib_shop_enqueue_script');
}

/**
 * @description: 骨架屏模板
 * @param {*}
 * @return {*}
 */
function zib_shop_get_lists_card_placeholder($args = array(), $i = 4)
{
    $defaults = [
        'class'          => '',
        'show_desc'      => true,
        'show_discount'  => true,
        'show_price'     => true,
        'text_center'    => true,
        'style'          => '',
        'thumb_scale'    => 100,
        'title_one_line' => false,
    ];
    $args = wp_parse_args($args, $defaults);
    $args['class'] .= $args['style'] ? ' style-' . $args['style'] : '';
    $thumb_attr = $args['thumb_scale'] && $args['thumb_scale'] != 100 ? ' style="--t-s:' . $args['thumb_scale'] . '%;"' : '';

    $html = '<posts class="product-item posts-item card ' . $args['class'] . '"' . $thumb_attr . '>
                <div class="item-thumbnail placeholder"></div>
                <div class="item-body product-item-body' . ($args['text_center'] ? ' text-center' : '') . '">
                    <h2 class="item-heading placeholder' . ($args['title_one_line'] ? ' text-ellipsis' : '') . '"></h2>';
    if ($args['show_desc']) {
        $html .= '<div class="item-excerpt placeholder k2"></div>';
    }
    if ($args['show_discount']) {
        $html .= '<div class="item-discount-badge scroll-x no-scrollbar"><i class="placeholder s1 mr6" style="height: 20px;border-radius: 100px;"></i><i class="placeholder s1 mr6" style="height: 20px;border-radius: 100px;"></i></div>';
    }
    if ($args['show_price']) {
        $html .= '<div class="item-price product-price"><div class="placeholder s1" style="height: 18px;"></div></div>';
    }

    $html .= '</div></posts>';

    $placeholder = str_repeat($html, $i);

    return $placeholder;
}

//输出页面模板主要内容
function zib_shop_term_page_template($type = 'home')
{
    do_action('shop_locate_template');
    do_action('shop_locate_template_' . $type);
    add_filter('zib_is_show_sidebar', '__return_false'); //不显示侧边栏
    add_filter('zib_frontend_set_input_array', 'zib_shop_term_frontend_set_input_array', 10, 3); //添加前台设置

    get_header();
    echo '<main id="shop">';
    echo '<div class="container">';
    do_action('shop_' . $type . '_page_content');
    echo '</div>';
    echo '</main>';
    get_footer();
}

function zib_shop_v_cart_template()
{
    ob_start();
    ?>
<div class="cart-header-bar flex ac jsb mb10">
    <div class="index-tab rectangular relative" v-if="user_data.favorite_ajax_url">
        <ul class="list-inline scroll-x no-scrollbar" :class="!is_mobile ? 'em12' : ''">
            <li class="active"><a data-toggle="tab" href="#shop_cart_cart"><?php esc_html_e('购物车', 'zib_language'); ?></a></li>
            <li class=""><a data-ajax="" data-toggle="tab" href="#shop_cart_favorite"><?php esc_html_e('收藏夹', 'zib_language'); ?></a></li>
        </ul>
    </div>
    <div class="em12 muted-color" v-else><?php esc_html_e('我的购物车', 'zib_language'); ?></div>

    <div class="steps-box cart-steps" v-if="!is_mobile">
        <div class="step active">
            <div class="step-label"><?php esc_html_e('选择商品', 'zib_language'); ?></div>
        </div>
        <div class="step">
            <div class="step-label"><?php esc_html_e('核对订单', 'zib_language'); ?></div>
        </div>
        <div class="step">
            <div class="step-label"><?php esc_html_e('提交订单', 'zib_language'); ?></div>
        </div>
    </div>
</div>

<div class="tab-content">
    <div class="tab-pane fade in active" id="shop_cart_cart">
        <div class="cart-list-item cart-header zib-widget" v-if="!is_mobile">
            <div class="cart-list-header flex ac jsb">
                <div class="cart-col-checkbox flex ac" @click="checkAll">
                    <div class="mr10 div-checkbox" :class="total_data.checked_status"></div>
                    <div class=""><?php esc_html_e('全选', 'zib_language'); ?></div>
                </div>
                <div class="cart-col-right">
                    <div class="item-thumbnail product-graphic"></div>
                    <div class="cart-col-main">
                        <div class="cart-col-product"><?php esc_html_e('商品', 'zib_language'); ?></div>
                        <div class="cart-col-spinner-price">
                            <div class="cart-col-price"><?php esc_html_e('单价', 'zib_language'); ?></div>
                            <div class="cart-col-spinner"><?php esc_html_e('计数', 'zib_language'); ?></div>
                            <div class="cart-col-price"><?php esc_html_e('小计', 'zib_language'); ?></div>
                        </div>
                        <div class="cart-col-action"><?php esc_html_e('操作', 'zib_language'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="abs right-top" v-else>
            <div class="but" @click="config.is_edit = !config.is_edit">{{config.is_edit ? '<?php echo esc_js(__('完成', 'zib_language')); ?>' : '<?php echo esc_js(__('管理', 'zib_language')); ?>'}}</div>
        </div>
        <div class="cart-list-content mb20" :class="!config.author_show ? 'cart-group zib-widget full-widget-sm' : ''">
            <div :class="config.author_show ? 'cart-group zib-widget mb10 full-widget-sm' : ''" v-for="( product_items , author_id ) in cart_data" :key="'author-' + author_id" :data-author-id="author_id">
                <div class="cart-list-author cart-list-item" v-if="config.author_show">
                    <div class="cart-list-header flex ac jsb">
                        <div class="cart-col-checkbox" @click="checkAuthor(author_id)">
                            <div class="div-checkbox" :class="author_data[author_id].checked_status"></div>
                        </div>
                        <div class="cart-col-right">
                            <a class="cart-col-author ellipsis-box" :href="author_data[author_id].url">
                                <div class="mr10" v-html="author_data[author_id].avatar"></div>
                                <div class="text-ellipsis">{{ author_data[author_id].name }}</div>
                                <i class="ml6 fa fa-angle-right em12"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="cart-product" v-for="( product_opt , product_id ) in product_items" :key="'product-' + product_id" :data-product-id="product_id">
                    <div class="cart-list-item" v-for="( opt_data , opt_key ) in product_opt" :key="'item-' + author_id + '-' + product_id + '-' + opt_key" :data-optkey="opt_key">
                        <div class="cart-list-header flex ac jsb">
                            <div class="cart-col-checkbox" @click="checkItem(opt_data)">
                                <div class="div-checkbox" :class="{ 'is-disabled': opt_data.options_active_error || opt_data.stock_all == 0, 'checked': opt_data.checked }"></div>
                            </div>
                            <div class="cart-col-right">
                                <div class="item-thumbnail product-graphic gradient-bg nowave" data-opacity="0.1"><img :src="opt_data.main_image_url" :alt="product_data[product_id].title"></div>
                                <div class="cart-col-main">
                                    <div class="cart-col-product">
                                        <div class="product-title text-ellipsis-2">
                                            <span v-if="product_data[product_id].tags.important.name" :class="product_data[product_id].tags.important.class" class="badg badg-sm">{{ product_data[product_id].tags.important.name }}</span>
                                            <a :href="product_data[product_id].url">{{ product_data[product_id].title }}</a>
                                        </div>
                                        <botton class="but px12-sm p2-10 product-option-btn flex ac ellipsis-box" v-if="product_data[product_id].product_options.length > 0" @click.prevent="cartModal(opt_data)">
                                            <span class="text-ellipsis">{{ opt_data.options_active_name }}</span>
                                            <i class="ml6 fa fa-angle-right em12"></i>
                                        </botton>
                                        <div v-discount-badge="product_data[product_id].discount" data-hit-discount="opt_data.discount_hit" class="product-discount-box scroll-x mini-scrollbar" @click="discountModal(product_data[product_id].discount)"></div>
                                        <div class="product-tags-box scroll-x mini-scrollbar" v-if="!$.isEmptyObject(product_data[product_id].tags.tags)">
                                            <span v-for="tag in product_data[product_id].tags.tags" :key="'tag-' + tag.id">
                                                <span class="badg badge-tag badg-sm" :class="tag.class">{{ tag.name }}</span>
                                            </span>
                                        </div>
                                        <div class="cart-col-spinner-price-sm flex ac jsb" v-if="is_mobile">
                                            <div class="cart-col-price">
                                                <div class="price-box" :class="product_data[product_id].pay_modo == 'points' ? 'c-yellow' : 'c-red'">
                                                    <div class="inflex abl">
                                                        <span class="pay-mark" v-html="product_data[product_id].show_mark"></span>
                                                        <b class="price-str" v-price="opt_data.prices.unit_discount_price"></b>
                                                    </div>
                                                    <div class="original-price muted-2-color ml6" v-if="opt_data.prices.unit_price > opt_data.prices.unit_discount_price">
                                                        <span class="pay-mark" v-html="product_data[product_id].show_mark"></span>
                                                        <span v-price="opt_data.prices.unit_price"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cart-col-remove" v-if="config.is_edit && is_mobile">
                                                <botton class="cart-remove-btn c-red but cir" @click="removeItem(author_id,product_id,opt_key,true)"><i title="fa fa-trash-o" class="fa fa-trash-o"></i></botton>
                                            </div>
                                            <div v-else>
                                                <div class="cart-col-spinner" v-spinner="opt_data.selected_count" @change="listCountChange(opt_data)" min="1" :max="spinnerMax(opt_data)"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cart-col-spinner-price" v-if="!is_mobile">
                                        <div class="cart-col-price">
                                            <div class="price-box" :class="product_data[product_id].pay_modo == 'points' ? 'c-yellow' : 'c-red'">
                                                <div class="inflex abl">
                                                    <span class="pay-mark" v-html="product_data[product_id].show_mark"></span>
                                                    <b class="price-str" v-price="opt_data.prices.unit_discount_price"></b>
                                                </div>
                                                <div class="original-price muted-2-color ml6" v-if="opt_data.prices.unit_price > opt_data.prices.unit_discount_price">
                                                    <span class="pay-mark" v-html="product_data[product_id].show_mark"></span>
                                                    <span v-price="opt_data.prices.unit_price"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cart-col-spinner">
                                            <div class="" v-spinner="opt_data.selected_count" @change="listCountChange(opt_data)" min="1" :max="spinnerMax(opt_data)"></div>
                                            <div class="flex em09">
                                                <div class="stock-desc muted-color mt6" v-effect="$el.innerHTML = stockAllText(opt_data.stock_all)"></div>
                                                <div class="limit-buy-desc c-yellow ml6 mt6" v-limit-buy="opt_data.limit_buy"></div>
                                            </div>
                                        </div>
                                        <div class="cart-col-price cart-col-total-price">
                                            <div class="price-box" :class="product_data[product_id].pay_modo == 'points' ? 'c-yellow' : 'c-red'">
                                                <div class="inflex abl">
                                                    <span class="pay-mark" v-html="product_data[product_id].show_mark"></span>
                                                    <b class="price-str" v-price="opt_data.prices.total_discount_price"></b>
                                                </div>
                                                <div class="badg badg-sm c-yellow pointer ml6" @click="showItemDiscountHitModal(opt_data)" v-if="opt_data.prices.total_discount"><?php esc_html_e('省', 'zib_language'); ?> {{ opt_data.prices.total_discount }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cart-col-action" v-if="!is_mobile">
                                        <botton class="but c-red p2-10" @click="removeItem(author_id,product_id,opt_key,true)"><?php esc_html_e('删除', 'zib_language'); ?></botton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-desc"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cart-list-item cart-footer" :class="is_mobile ? 'footer-tabbar' : 'zib-widget'">
            <div class="cart-list-header flex ac jsb">
                <div class="cart-col-checkbox flex ac" @click="checkAll">
                    <div class="div-checkbox mr10" :class="total_data.checked_status"></div>
                    <div class=""><?php esc_html_e('全选', 'zib_language'); ?></div>
                </div>
                <div class="cart-col-right">
                    <div class="cart-col-main">
                        <div class="cart-col-remove">
                            <botton class="cart-remove-btn but p2-10" v-if="!is_mobile" @click="removeChecked" :class="total_data.count > 0 ? '' : 'is-disabled'">{{ is_mobile ? '<?php echo esc_js(__('移出', 'zib_language')); ?>' : '<?php echo esc_js(__('移出选中商品', 'zib_language')); ?>' }}</botton>
                        </div>
                        <div class="cart-col-settlement flex ac" v-if="!config.is_edit || !is_mobile">
                            <div class="cart-col-total-info">
                                <div class="total-price">
                                    <div class="flex ab">
                                        <span class="em09">
                                            <span class="opacity8 em09" v-if="total_data.count > 0">{{total_data.count + '<?php echo esc_js(__('件', 'zib_language')); ?>' + (is_mobile ? '' : '<?php echo esc_js(__('商品', 'zib_language')); ?>') + ' · '}}</span>
                                            <span class="mr3"><?php esc_html_e('合计', 'zib_language'); ?></span>
                                        </span>
                                        <span :class="total_data.pay_modo == 'points' ? 'c-yellow' : 'c-red'" class="price-box inflex">
                                            <span class="pay-mark px12" v-html="total_data.show_mark"></span>
                                            <span class="em12" v-price="total_data.pay_modo == 'points' ? total_data.discount_points : total_data.discount_price"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="discount-str c-yellow inflex" v-if="total_data.pay_modo === 'points'" :class="total_data.points <= total_data.discount_points ? 'hide' : ''" @click="showDiscountHitModal">
                                    <span class="mr3"><?php esc_html_e('共计优惠', 'zib_language'); ?></span>
                                    <span class="pay-mark px12" v-html="total_data.show_mark"></span>
                                    <span class="price-str" v-price="total_data.points-total_data.discount_points"></span>
                                    <i class="fa fa-angle-up em12"></i>
                                </div>
                                <div class="discount-str c-red inflex abl" v-else :class="total_data.price <= total_data.discount_price ? 'hide' : ''" @click="showDiscountHitModal">
                                    <span class="mr3"><?php esc_html_e('共计优惠', 'zib_language'); ?></span>
                                    <span class="pay-mark px12" v-html="total_data.show_mark"></span>
                                    <span class="price-str" v-price="total_data.price - total_data.discount_price"></span>
                                    <i class="fa fa-angle-up em12 ml3 muted-2-color"></i>
                                </div>
                            </div>
                            <botton class="but jb-red settle-btn" :class="!total_data.is_can_pay ? 'is-disabled' : ''" @click.prevent="goConfirm"><?php esc_html_e('结算', 'zib_language'); ?></botton>
                        </div>
                        <div v-else class="cart-col-settlement">
                            <div class="cart-col-remove">
                                <botton class="cart-remove-btn but c-red" @click="removeChecked" :class="total_data.count > 0 ? '' : 'is-disabled'"><?php esc_html_e('移出', 'zib_language'); ?></botton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ajaxpager tab-pane fade product-lists-row" id="shop_cart_favorite" v-if="user_data.favorite_ajax_url">
        <span class="post_ajax_trigger hide"><a :href="user_data.favorite_ajax_url" class="ajax_load ajax-next ajax-open"></a></span>
        <div class="post_ajax_loader" style="display: none" v-html="config.lists_placeholder"></div>
    </div>
</div>
<?php
    return ob_get_clean();
}

function zib_shop_v_confirm_modal_template()
{
    ob_start();
    ?>
<div class="mini-scrollbar scroll-y">
    <div class="order-address-box zib-widget mb10-sm" v-if="shipping_has_express">
        <div class="address-box-body" v-if="user_data.address_data.id">
            <div class="flex ac jsb pointer" @click="showAddressModal">
                <span class="address-icon badg cir jb-yellow mr10">
                    <i class="fa fa-map-marker"></i>
                </span>
                <div class="address-content flex1 mr10">
                    <div class="address-detail muted-color flex ac">
                        <div class="name-phone mb6">
                            <span class="name">{{ user_data.address_data.name }}</span>
                            <span class="phone ml6">{{ user_data.address_data.phone }}</span>
                            <span class="badg badg-sm c-blue ml6" v-if="user_data.address_data.tag">{{ user_data.address_data.tag }}</span>
                            <span class="default-badge badg badg-sm c-red ml3" v-if="user_data.address_data.is_default"><?php esc_html_e('默认', 'zib_language'); ?></span>
                        </div>
                    </div>
                    <div class="muted-2-color em09">{{ user_data.address_data.county + user_data.address_data.address }}</div>
                </div>
                <i class="fa fa-angle-right em12"></i>
            </div>
        </div>
        <div class="address-box-body" v-else>
            <div class="flex muted-color jsb ac pointer" @click="showAddAddressModal">
                <div class="icon-header mr20 shrink0">
                    <i class="fa fa-map-marker mr6"></i>
                    <span class=""><?php esc_html_e('请先添加收货地址', 'zib_language'); ?></span>
                </div>
                <div class="flex ac overflow-hidden">
                    <div class="text-ellipsis muted-3-color"><?php esc_html_e('添加', 'zib_language'); ?></div>
                    <i class="fa fa-angle-right em12 ml6"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 自动发货 -->
    <div class="order-address-box zib-widget mb10-sm" v-if="shipping_has_auto && email_fill !== 'off'">
        <div class="flex ac jsb pointer" @click="user_data.email_edit = true;$nextTick(() => { $refs.user_email_input.focus() })">
            <span class="address-icon badg cir jb-yellow mr10">
                <i class="fa fa-envelope-o"></i>
            </span>
            <div class="address-box-body flex-auto">
                <div class="address-item">
                    <input v-if="user_data.email_edit" type="text" class="form-control" placeholder="<?php echo esc_attr(__('请填写接受资料的邮箱', 'zib_language')); ?>" v-model="user_data.email" ref="user_email_input" />
                    <div v-else>
                        {{ user_data.email || '<?php echo esc_js(__('请先填写', 'zib_language')); ?>' }}
                        <span class="badg badg-sm c-blue ml6"><?php esc_html_e('收货邮箱', 'zib_language'); ?></span>
                        <span class="badg badg-sm c-green ml6" v-if="email_fill === 'fill'"><?php esc_html_e('选填', 'zib_language'); ?></span>
                    </div>
                </div>
                <div class="address-desc muted-3-color px12 mt6"><?php esc_html_e('付款后，系统会自动将资料发送到您填写的邮箱', 'zib_language'); ?></div>
            </div>
            <i class="fa fa-angle-right em12 ml6" v-if="!user_data.email_edit"></i>
        </div>
    </div>

    <!-- 商品明细 -->
    <div class="order-confirm-product">
        <div class="order-product-lists-box">
            <div class="confirm-group" :class="config.author_show ? 'zib-widget mb10-sm' : ''" v-for="( product_items , author_id ) in item_data" :key="'author-' + author_id">
                <div class="cart-list-author flex ac cart-list-item" v-if="config.author_show">
                    <div class="cart-col-right">
                        <div class="cart-col-author">
                            <div class="mr10" v-html="author_data[author_id].avatar"></div>
                            {{ author_data[author_id].name }}
                        </div>
                    </div>
                </div>

                <div class="confirm-product-item" v-for="( opt_items , product_id ) in product_items" :key="'item-' + author_id + '-' + product_id">
                    <div class="cart-list-item confirm-list-item" :class="!config.author_show ? 'zib-widget mb10-sm' : ''" v-for="( opt_data , opt_key ) in opt_items" :key="'item-' + author_id + '-' + product_id + '-' + opt_key">
                        <div class="cart-list-header flex ac jsb list-header">
                            <div class="item-thumbnail product-graphic nowave"><img imgbox-no-cache="true" class="alone-imgbox-img" :src="opt_data.product_image" :alt="product_data[product_id].title"></div>
                            <div class="flex1 flex">
                                <div class="cart-col-product flex1 mr10">
                                    <div class="product-title text-ellipsis" v-html="product_data[opt_data.product_id].title"></div>
                                    <span class="em09 muted-color" v-if="opt_data.options_active_name">{{ opt_data.options_active_name }}</span>
                                    <div v-discount-badge="product_data[opt_data.product_id].discount" data-hit-discount="opt_data.discount_hit" class="product-discount-box scroll-x mini-scrollbar mt10" @click="discountModal(discount_data,opt_data.discount_hit)"></div>
                                </div>
                                <div class="flex-shrink0 text-right flex xx ab">
                                    <div class="price-box muted-color">
                                        <div class="flex abl" :class="opt_data.pay_mode === 'points' ? 'c-yellow' : 'c-red'">
                                            <span class="pay-mark px12" v-html="product_data[opt_data.product_id].show_mark"></span>
                                            <span class="em12" v-price="opt_data.prices.unit_price"></span>
                                        </div>
                                    </div>
                                    <span class="muted-color mt6">
                                        <span class="">x</span>
                                        <span class="number-input em12">{{ opt_data.count }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="list-desc mt10">
                            <div class="desc-item mt10">
                                <div class="product-shipping-box">
                                    <div class="flex muted-color jsb">
                                        <div class="icon-header mr20 shrink0">
                                            <i class="fa fa-truck mr6 fa-fw"></i>
                                            <span class="muted-2-color"><?php esc_html_e('发货', 'zib_language'); ?></span>
                                        </div>
                                        <div class="flex ab xx">
                                            <div class="flex ac shipping-title muted-color" v-html="product_data[opt_data.product_id].shipping_title"></div>
                                            <div class="shipping-auto-desc em09 muted-3-color" v-if="product_data[opt_data.product_id].shipping_desc" v-html="product_data[opt_data.product_id].shipping_desc"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="desc-item mt10" v-show="!$.isEmptyObject(opt_data.gift_data)">
                                <div class="flex muted-color jsb ac pointer" @click="showGiftModal(opt_data)">
                                    <div class="icon-header mr20 shrink0">
                                        <i class="fa fa-gift mr6 fa-fw"></i>
                                        <span class="muted-2-color"><?php esc_html_e('赠品', 'zib_language'); ?></span>
                                    </div>
                                    <div class="flex ac overflow-hidden">
                                        <div class="text-ellipsis" v-effect="$el.textContent = giftDesc(opt_data.gift_data)"></div>
                                        <i class="fa fa-angle-right em12 ml6"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="desc-item mt10">
                                <div class="flex muted-color jsb ac pointer" @click="showRemarkModal(opt_data)">
                                    <div class="icon-header mr20 shrink0">
                                        <i class="fa fa-comment mr6 fa-fw"></i>
                                        <span class="muted-2-color"><?php esc_html_e('备注', 'zib_language'); ?></span>
                                    </div>
                                    <div class="flex ac overflow-hidden">
                                        <div class="text-ellipsis" :class="!opt_data.remark ? 'muted-3-color' : ''">{{ opt_data.remark || '<?php echo esc_js(__('无备注', 'zib_language')); ?>' }}</div>
                                        <i class="fa fa-angle-right em12 ml6"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="desc-item mt10" v-for="user_required_item in opt_data.user_required" :key="'item-' + author_id + '-' + opt_data.product_id + '-' + opt_key + '-' + user_required_item.key">
                                <div class="flex muted-color jsb ac pointer" @click="showUserRequiredModal(opt_data)">
                                    <div class="icon-header mr20 shrink0">
                                        <i class="fa fa-edit mr6 fa-fw"></i>
                                        <span class="muted-2-color">{{ user_required_item.name }}</span>
                                    </div>
                                    <div class="flex ac overflow-hidden">
                                        <div class="text-ellipsis" :class="!userRequiredValue(user_required_item.name,opt_data) ? 'c-red' : ''">{{ userRequiredValue(user_required_item.name,opt_data) || '<?php echo esc_js(__('必填', 'zib_language')); ?>' }}</div>
                                        <i class="fa fa-angle-right em12 ml6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 订单价格信息 -->
    <div class="order-info-box zib-widget mb10-sm">
        <div class="order-info-body">
            <div class="order-info-item flex at jsb mb10">
                <div class="item-label muted-color">
                    <?php esc_html_e('商品总价', 'zib_language'); ?>
                    <span class="muted-3-color px12"><?php esc_html_e('共', 'zib_language'); ?>{{total_data.count}}<?php esc_html_e('件', 'zib_language'); ?></span>
                </div>
                <div class="item-value">
                    <div class="order-pay-prices-box flex abl xx">
                        <div class="order-pay-prices-item flex abl" v-if="total_data.points_count">
                            <span class="pay-mark px12" v-html="total_data.points_mark"></span>
                            <span class="price-str" v-price="total_data.points"></span>
                        </div>
                        <div class="order-pay-prices-item flex abl" v-if="total_data.price_count">
                            <span class="pay-mark px12" v-html="total_data.pay_mark"></span>
                            <span class="price-str" v-price="total_data.price"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="order-info-item flex ac jsb mb10" v-if="shipping_has_express">
                <div class="item-label muted-color"><?php esc_html_e('运费', 'zib_language'); ?></div>
                <div class="item-value flex abl">
                    <span class="pay-mark px12" v-html="total_data.pay_mark"></span>
                    <span class="price-str" v-price="total_data.shipping_fee"></span>
                </div>
            </div>
            <div class="order-info-item flex ac jsb mb10 pointer" @click="showDiscountHitModal" v-if="total_data.price - total_data.discount_price > 0 || total_data.points-total_data.discount_points >0">
                <div class="item-label muted-color"><?php esc_html_e('优惠', 'zib_language'); ?></div>
                <div class="item-value flex ac jsb">
                    <div class="order-pay-prices-box flex abl xx">
                        <div class="order-pay-prices-item flex abl c-yellow" v-if="total_data.points_count">
                            <span class="mr3">-</span>
                            <span class="pay-mark px12" v-html="total_data.points_mark"></span>
                            <span class="price-str" v-price="total_data.points-total_data.discount_points"></span>
                        </div>
                        <div class="order-pay-prices-item flex abl c-red" v-if="total_data.price_count">
                            <span class="mr3">-</span>
                            <span class="pay-mark px12" v-html="total_data.pay_mark"></span>
                            <span class="price-str" v-price="total_data.price - total_data.discount_price"></span>
                        </div>
                    </div>
                    <i class="fa fa-angle-right em12 ml6 muted-color" v-if="!$.isEmptyObject(total_data.discount_hit)"></i>
                </div>
            </div>
            <div class="order-info-item flex ac jsb">
                <div class="item-label muted-color"><?php esc_html_e('合计', 'zib_language'); ?></div>
                <div class="item-value">
                    <div class="order-pay-prices-box flex abl xx">
                        <div class="order-pay-prices-item flex abl c-yellow em12" v-if="total_data.points_count">
                            <span class="pay-mark px12" v-html="total_data.points_mark"></span>
                            <span class="price-str" v-price="total_data.pay_points"></span>
                        </div>
                        <div class="order-pay-prices-item flex abl c-red em12" v-if="total_data.price_count">
                            <span class="pay-mark px12" v-html="total_data.pay_mark"></span>
                            <span class="price-str" v-price="total_data.pay_price"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="zib-widget mb10-sm" v-if="0">
        <div class="flex jsb ac">
            <span class="muted-color mr10 flex1"><?php esc_html_e('订单号', 'zib_language'); ?></span>
            <div class="">{{ payment_data.order_num }}</div>
        </div>
        <div class="flex jsb ac mt10">
            <span class="muted-color mr10 flex1"><?php esc_html_e('下单时间', 'zib_language'); ?></span>
            <div class="">{{ payment_data.create_time }}</div>
        </div>
    </div>

    <!-- 支付方式 -->
    <div class="order-paytype-box mb20" v-if="total_data.discount_price > 0 || total_data.discount_points > 0">
        <div class="zib-widget mb10-sm" v-if="!$.isEmptyObject(pay_data.pay_methods)">
            <div class="payment-methods" v-for="(method, key) in pay_data.pay_methods" :key="'payment-method-' + key" @click="paymentMethodChange(key)">
                <div class="flex jsb ac">
                    <div class="flex ac">
                        <div class="mr6 payment-icon" v-html="method.img"></div>
                        <div class="muted-color">{{ method.name }}</div>
                    </div>
                    <div class="flex ac">
                        <div class="mr10 flex abl" v-if="key === 'balance'">
                            <span class="muted-2-color px12"><?php esc_html_e('剩余：', 'zib_language'); ?></span>
                            <span class="px12" v-html="total_data.pay_mark"></span>
                            <span class="" v-price="pay_data.user_balance"></span>
                        </div>
                        <div class="mr10 flex abl" v-if="key === 'points'">
                            <span class="muted-2-color px12"><?php esc_html_e('剩余：', 'zib_language'); ?></span>
                            <span class="px12" v-html="total_data.points_mark"></span>
                            <span class="" v-price="pay_data.user_points"></span>
                        </div>
                        <div class="cart-col-checkbox">
                            <div class="div-checkbox" :class="{'checked': pay_data.pay_methods_active === key}"></div>
                        </div>
                    </div>
                </div>
                <div class="muted-box flex jsb ac mt10 padding-10" v-if="pay_data.user_balance < total_data.discount_price && key === 'balance' && pay_data.pay_methods_active === 'balance'">
                    <span class="c-red">
                        <i class="fa fa-info-circle"></i>
                        <span><?php esc_html_e('余额不足', 'zib_language'); ?></span>
                    </span>
                    <div>
                        <a target="_blank" class="but c-blue" :href="pay_data.user_balance_url"><?php esc_html_e('我的余额', 'zib_language'); ?></a>
                        <span v-html="pay_data.balance_charge_link"></span>
                    </div>
                </div>
                <div class="muted-box flex jsb ac mt10 padding-10" v-if="pay_data.pay_methods_active === 'points' && key === 'points' && pay_data.user_points < total_data.discount_points">
                    <span class="c-red">
                        <i class="fa fa-info-circle"></i>
                        <span><?php esc_html_e('积分不足', 'zib_language'); ?></span>
                    </span>
                    <div>
                        <a target="_blank" class="but c-blue" :href="pay_data.user_points_url"><?php esc_html_e('我的积分', 'zib_language'); ?></a>
                        <span v-html="pay_data.points_pay_link"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 支付按钮 -->
<div class="order-pay-btn mt10" v-show="!payment_data.order_num" v-if="!$.isEmptyObject(pay_data.pay_methods)">
    <button class="but jb-red padding-lg radius btn-block order-submit-btn" @click.prevent="submitOrInitiatePay">
        <span class="btn-text"><?php esc_html_e('提交订单', 'zib_language'); ?></span>
        <span class="em09 opacity8 ml10">
            <span class="" v-if="total_data.count > 1 "><?php esc_html_e('共', 'zib_language'); ?>{{ total_data.count }}<?php esc_html_e('件', 'zib_language'); ?></span>
            <span class="pay-price-text" v-if="total_data.points_count">
                <span class="px12 pay-mark" v-html="total_data.points_mark"></span>
                <span class="actual-price-number em12" v-price="total_data.pay_points"></span>
            </span>
            <span class="pay-price-text" v-if="total_data.price_count">
                <span class="px12 pay-mark" v-html="total_data.pay_mark"></span>
                <span class="actual-price-number em12" v-price="total_data.pay_price"></span>
            </span>
        </span>
    </button>
</div>
<div v-else v-html="pay_data.error_msg || '<?php echo esc_js(__('没有收款接口，暂时无法购买', 'zib_language')); ?>'"></div>
<form id="shop_zibpay_form" ref="zibpay_form" v-if="payment_data.order_num">
    <input type="hidden" name="payment_id" :value="payment_data.id" />
    <input type="hidden" name="payment_method" :value="pay_data.pay_methods_active" />
    <button class="but jb-red padding-lg radius initiate-pay mt10 btn-block">
        <span class="btn-text mr10"><?php esc_html_e('立即支付', 'zib_language'); ?></span>
        <span class="px12 pay-mark" v-html=" pay_modo === 'points' ? total_data.points_mark : total_data.pay_mark"></span>
        {{ payment_data.price }}
    </button>
</form>
<?php
    return ob_get_clean();
}
