/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime : 2026-05-29 22:59:46
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台UI-JavaScript
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

(function ($, document) {
    function _zib__(key) {
        var map = window.zib_admin_man_var && window.zib_admin_man_var.i18n;
        if (map && Object.prototype.hasOwnProperty.call(map, key)) {
            return map[key];
        }
        return key;
    }

    function _zib_sprintf(key) {
        var args = Array.prototype.slice.call(arguments, 1);
        var str = _zib__(key);
        var autoIndex = 0;
        return str.replace(/%(\d+\$)?s/g, function (match, num) {
            var idx = num ? parseInt(num, 10) - 1 : autoIndex++;
            return args[idx] !== undefined && args[idx] !== null ? args[idx] : match;
        });
    }

    function cssTransition(n, t, o, i, e) {
        var r, a, s;
        i && ((t += 'px'), (o += 'px'), (r = 'translate3D(' + t + ',' + o + ' , 0)'), (a = {}), (s = cssT_Support()), (a[s + 'transform'] = r), (a[s + 'transition'] = s + 'transform 0s linear'), (a.cursor = e), 'null' == i && ((a[s + 'transform'] = ''), (a[s + 'transition'] = '')), n.css(a));
    }

    function cssT_Support() {
        var n = document.body || document.documentElement;
        n = n.style;
        return '' == n.WebkitTransition ? '-webkit-' : '' == n.MozTransition ? '-moz-' : '' == n.OTransition ? '-o-' : '' == n.transition ? '' : void 0;
    }
    $.fn.minitouch = function (n) {
        n = $.extend(
            {
                direction: 'bottom',
                selector: '',
                depreciation: 50,
                onStart: !1,
                onEnd: !1,
            },
            n
        );
        var t = $(this),
            o = ($('body'), n.depreciation),
            i = 0,
            e = 0,
            r = 0,
            a = 0,
            s = 0,
            c = 0,
            u = 0,
            l = !1;
        t.on('touchstart pointerdown MSPointerDown', n.selector, function (n) {
            (i = 0), (e = 0), (r = 0), (a = 0), (s = 0), (c = 0), (u = 0), (i = n.originalEvent.pageX || n.originalEvent.touches[0].pageX), (e = n.originalEvent.pageY || n.originalEvent.touches[0].pageY), (l = !0);
        })
            .on('touchmove pointermove MSPointerMove', n.selector, function (t) {
                (r = t.originalEvent.pageX || t.originalEvent.touches[0].pageX), (a = t.originalEvent.pageY || t.originalEvent.touches[0].pageY), (c = r - i), (u = a - e), (s = (180 * Math.atan2(u, c)) / Math.PI), 'right' == n.direction && ((u = 0), (c = s > -40 && s < 40 && c > 0 ? c : 0)), 'left' == n.direction && ((u = 0), (c = (s > 150 || s < -150) && 0 > c ? c : 0)), 'top' == n.direction && ((c = 0), (u = s > -130 && s < -50 && 0 > u ? u : 0)), 'bottom' == n.direction && ((c = 0), (u = s > 50 && s < 130 && u > 0 ? u : 0)), (0 === c && 0 === u) || (t.preventDefault(), cssTransition($(this), c, u, l, 'grab'));
            })
            .on('touchend touchcancel pointerup MSPointerUp', n.selector, function () {
                (Math.abs(c) > o || Math.abs(u) > o) && 0 != n.onEnd && n.onEnd(t), cssTransition($(this), 0, 0, 'null', ''), (l = !1), (i = 0), (e = 0), (r = 0), (a = 0), (s = 0), (c = 0), (u = 0);
            });
    };

    $(document).ready(function ($) {
        //循环替换后台菜单名称
        var $shop_menu = $('#toplevel_page_zibpay_page');
        if ($shop_menu.length && $('html').attr('lang') !== 'zh-Hans') {
            $shop_menu.find('.toplevel_page_zibpay_page .wp-menu-name').text(_zib__('admin_menu_title_shop_center'));
            var $sub_menu = $shop_menu.find('.wp-submenu');
            $sub_menu.find('a.wp-first-item').text(_zib__('admin_menu_title_shop_center'));
            $sub_menu.find('a').each(function () {
                var url = $(this).attr('href');
                //匹配以zibpay_page结尾
                if (url.endsWith('zibpay_page')) {
                    $(this).text(_zib__('admin_menu_title_shop_center'));
                }

                if (url.endsWith('zibpay_page#/order')) {
                    $(this).text(_zib__('admin_menu_title_order'));
                }

                if (url.endsWith('zibpay_page#/shipping')) {
                    $(this).text(_zib__('admin_menu_title_shipping'));
                }

                if (url.endsWith('zibpay_page#/after-sale')) {
                    $(this).text(_zib__('admin_menu_title_after_sale'));
                }

                if (url.endsWith('zibpay_income_page')) {
                    $(this).text(_zib__('admin_menu_title_income'));
                }

                if (url.endsWith('zibpay_rebate_page')) {
                    $(this).text(_zib__('admin_menu_title_card'));
                }

                if (url.endsWith('zibpay_product_page')) {
                    $(this).text(_zib__('admin_menu_title_product'));
                }

                if (url.endsWith('zibpay_charge_card_page')) {
                    $(this).text(_zib__('admin_menu_title_card'));
                }

                if (url.endsWith('zibpay_coupon_page')) {
                    $(this).text(_zib__('admin_menu_title_coupon'));
                }

                if (url.endsWith('zibpay_withdraw')) {
                    $(this).text(_zib__('admin_menu_title_withdraw'));
                }

                if (url.endsWith('users.php')) {
                    $(this).text(_zib__('admin_menu_title_vip'));
                }

                if (url.endsWith('zibpay_order_page')) {
                    $(this).text(_zib__('admin_menu_title_order_old'));
                }
            });
        }

        var _body = $('body');

        if (typeof $.fn.serializeObject !== 'function') {
            $.fn.serializeObject = function () {
                var o = {};
                var a = this.serializeArray();
                $.each(a, function () {
                    if (o[this.name] !== undefined) {
                        if (!o[this.name].push) {
                            o[this.name] = [o[this.name]];
                        }
                        o[this.name].push(this.value || '');
                    } else {
                        o[this.name] = this.value || '';
                    }
                });
                return o;
            };
        }

        if (_body.width() < 783) {
            $('#adminmenuwrap').minitouch({
                direction: 'left',
                onEnd: function () {
                    $('#wpwrap').removeClass('wp-responsive-open');
                },
            });
        }

        //系统通知
        function notyf(str, ys, time, id) {
            $('.notyn').length || _body.append('<div class="notyn"></div>');
            ys = ys || 'success';
            time = time || 5000;
            time = time < 100 ? time * 1000 : time;
            var id_attr = id ? ' id="' + id + '"' : '';
            var _html = $('<div class="noty1"' + id_attr + '><div class="notyf ' + ys + '">' + str + '</div></div>');
            var is_close = !id;
            if (id && $('#' + id).length) {
                $('#' + id)
                    .find('.notyf')
                    .removeClass()
                    .addClass('notyf ' + ys)
                    .html(str);
                _html = $('#' + id);
                is_close = true;
            } else {
                $('.notyn').append(_html);
            }
            is_close &&
                setTimeout(function () {
                    notyf_close(_html);
                }, time);
        }

        function notyf_close(_e) {
            _e.addClass('notyn-out');
            setTimeout(function () {
                _e.remove();
            }, 1000);
        }
        _body.on('click', '.noty1', function () {
            notyf_close($(this));
        });

        //点击复制
        function copyText(text, success, error, _this) {
            // 数字没有 .length 不能执行selectText 需要转化成字符串
            var textString = text.toString();
            var input = document.querySelector('#copy-input');
            if (!input) {
                input = document.createElement('input');
                input.id = 'copy-input';
                input.readOnly = 'readOnly'; // 防止ios聚焦触发键盘事件
                input.style.position = 'fixed';
                input.style.left = '-2000px';
                input.style.zIndex = '-1000';
                _this.parentNode.appendChild(input);
            }

            input.value = textString;
            // ios必须先选中文字且不支持 input.select();
            selectText(input, 0, textString.length);
            if (document.execCommand('copy')) {
                typeof success === 'function' && success();
            } else {
                typeof error === 'function' && error();
            }
            input.blur();

            // input自带的select()方法在苹果端无法进行选择，所以需要自己去写一个类似的方法
            // 选择文本。createTextRange(setSelectionRange)是input方法
            function selectText(textbox, startIndex, stopIndex) {
                if (textbox.createTextRange) {
                    //ie
                    var range = textbox.createTextRange();
                    range.collapse(true);
                    range.moveStart('character', startIndex); //起始光标
                    range.moveEnd('character', stopIndex - startIndex); //结束光标
                    range.select(); //不兼容苹果
                } else {
                    //firefox/chrome
                    textbox.setSelectionRange(startIndex, stopIndex);
                    textbox.select();
                }
            }
        }

        _body.on('click', '[data-clipboard-text]', function () {
            var _this = $(this);
            var text = _this.attr('data-clipboard-text');
            var tag = _this.attr('data-clipboard-tag') || _zib__('admin_content');

            copyText(
                text,
                function () {
                    notyf(_zib_sprintf('admin_copied', tag));
                },
                function () {
                    notyf(_zib_sprintf('admin_copy_failed', tag), 'danger');
                },
                this
            );
        });

        //---------------------------------------------------------------
        //每次都刷新的模态框
        _body.on('click', '[data-toggle="RefreshModal"]', function () {
            var _this = $(this);
            var dataclass = _this.attr('data-class') || 'modal-mini';
            var remote = _this.attr('data-remote');
            var height = _this.attr('data-height') || 200;
            var mobile_bottom = _this.attr('mobile-bottom') && $(window).width() < 769 ? ' bottom' : '';
            var modal_class = 'zib-modal flex jc fade' + mobile_bottom;
            var id = 'refresh_modal';
            var is_new = _this.attr('new');
            id += is_new ? parseInt((Math.random() + 1) * Math.pow(10, 4)) : '';
            var _id = '#' + id;

            if (!remote) {
                var action = _this.attr('data-action');
                var ajax_url = _this.attr('ajax-url') || (wp && wp.ajax && wp.ajax.settings && wp.ajax.settings.url) || '/wp-admin/admin-ajax.php';
                remote = ajax_url + '?action=' + action;
            }

            dataclass += ' zib-modal-dialog';
            var modal_html =
                '<div class="' +
                modal_class +
                '" id="' +
                id +
                '" tabindex="-1" role="dialog" aria-hidden="false">\
                        <div class="zib-modal-backdrop"></div><div class="' +
                dataclass +
                '" role="document"><div class="hide-btn dashicons dashicons-no-alt"></div>\
                            <div class="zib-modal-content"></div>\
                        </div>\
                            </div>';

            var loading = '<div class="zib-modal-body" style="display:none;"></div><div class="flex jc loading-mask absolute main-bg radius8"><div class="em2x opacity5"><i class="rotate-loading"></i></div></div>';

            var _modal = $(_id);
            if (_modal.length) {
                if (_modal.hasClass('in')) modal_class += ' in';
                _modal.removeClass().addClass(modal_class);
                _modal.find('.zib-modal-dialog').removeClass().addClass(dataclass);
                _modal.find('.loading-mask').fadeIn(200);
                _modal
                    .find('.zib-modal-content')
                    .css({
                        overflow: 'hidden',
                    })
                    .animate({
                        height: height,
                    });
            } else {
                _body.append(modal_html);
                _modal = $(_id);
                if (is_new) {
                    _modal.on('hide.modal', function () {
                        $(this).remove();
                    });
                }
                _modal.find('.zib-modal-content').html(loading).css({
                    height: height,
                    overflow: 'hidden',
                });
            }

            _modal.zib_modal('show');

            $.get(remote, null, function (data) {
                _modal
                    .find('.zib-modal-body')
                    .html(data)
                    .slideDown(200, function () {
                        _modal.trigger('loaded.modal').find('.loading-mask').fadeOut(200);
                        var b_height = $(this).outerHeight();
                        _modal.find('.zib-modal-content').animate(
                            {
                                height: b_height,
                            },
                            200,
                            'swing',
                            function () {
                                _modal.find('.zib-modal-content').css({
                                    height: '',
                                    overflow: '',
                                    transition: '',
                                });
                            }
                        );
                    });
            });

            return false;
        });

        $.fn.zib_modal = function ($action) {
            var _this = $(this);
            switch ($action) {
                case 'show': {
                    show(_this);
                    break;
                }
                case 'hide': {
                    hide(_this);
                    break;
                }
            }

            function show(_this) {
                _this.css('display', 'flex');
                setTimeout(function () {
                    _this.addClass('in').trigger('show.modal');
                }, 10);
            }

            function hide(_this) {
                _this.removeClass('in').trigger('hide.modal');
                setTimeout(function () {
                    _this.css('display', 'none');
                }, 300);
            }

            if (!_body.data('zib-modal-is-on')) {
                _body.on('click', '.zib-modal-backdrop,.hide-btn', function () {
                    $($(this).parents('.zib-modal.in')[0]).zib_modal('hide');
                });
                _body.data('zib-modal-is-on', true);
            }
        };

        //----------------------TAB-栏目--------------------------
        _body.on('click', '.zib-tab-toggle', function () {
            var _this = $(this);
            var tab_id = _this.attr('tab-id');
            if (_this.parent().hasClass('active')) return;
            var _con = _this
                .parent()
                .addClass('active')
                .siblings()
                .removeClass('active')
                .parent()
                .parent()
                .find('[tab-id="' + tab_id + '"]');
            _con.siblings().removeClass('in');
            setTimeout(function () {
                _con.addClass('active').siblings().removeClass('active');
            }, 150);
            setTimeout(function () {
                _con.addClass('in');
            }, 160);
        });

        //佣金确认
        _body.on('click', '.process-submit', function () {
            return confirm(_zib__('admin_confirm_process'));
        });

        //--------------------为后台设置：修改网站URL地方添加说明------------------------
        var admin_options_url_input = $('.options-general-php input#siteurl');
        if (admin_options_url_input.length) {
            var html = '<div class="flex ac admin-url-set-warning" style="color: #e13535;background: #fbedea;padding: 10px;border-radius: 6px;border: 1px solid #ffbdbd;margin-top: 6px;"><span class="mb6 em2x mr20 dashicons dashicons-warning"></span><div>' + _zib__('admin_url_warning') + '<br><a target="_blank" href="https://www.zibll.com/18629.html">' + _zib__('admin_url_tutorial') + '</a> | <a target="_blank" href="https://www.zibll.com/19369.html">' + _zib__('admin_url_plugin') + '</a></div></div>';
            admin_options_url_input.after(html);
        }

        /*-------------------后台首页设置文案修改-------- */
        var show_on_front_input = $('input[name="show_on_front"]:eq(0)');
        if (show_on_front_input.length) {
            var parent = show_on_front_input.parent();
            parent.html(parent.html().replace('您的最新文章', _zib__('admin_home_latest_posts')));

            var page_for_posts = $('select[name="page_for_posts"]:eq(0)');
            if (page_for_posts.length) {
                var page_for_posts_parent = page_for_posts.parent();
                page_for_posts_parent
                    .html(page_for_posts_parent.html().replace('文章页：', _zib__('admin_home_posts_page')))
                    .parent()
                    .append('<p class="description em09">' + _zib__('admin_home_posts_page_desc') + '</p>');
            }

            var posts_per_page = $('[for="posts_per_page"]:eq(0)');
            if (posts_per_page.length) {
                posts_per_page
                    .html(posts_per_page.html().replace('博客页面至多显示', _zib__('admin_posts_per_page')))
                    .parent()
                    .next()
                    .append('<p class="description em09">' + _zib__('admin_posts_per_page_desc') + '</p>');
            }
        }

        //--------------------为后台菜单：添加说明------------------------
        var menu_edit_instructions = $('.menu-edit .drag-instructions');
        if (menu_edit_instructions.length) {
            var menu_edit_desc = '<div class="c-yellow">' + _zib__('admin_menu_pc_warning') + '</div><div class="">' + _zib__('admin_menu_mobile_hint') + '</div>';
            menu_edit_instructions.append(menu_edit_desc);
        }

        //----------后台固定链接配置：添加说明-----------
        var permalink_tags_box = $('.permalink-structure .available-structure-tags');
        if (permalink_tags_box.length) {
            permalink_tags_box.append('<div class="mt10 c-blue">' + _zib__('admin_permalink_recommend') + '</div><div class="mt10 c-yellow mb10">' + _zib__('admin_permalink_rewrite') + '</div><button type="submit" style="cursor: pointer;" class="but jb-blue permalink-structure-auto-btn">' + _zib__('admin_permalink_auto_save') + '</button>');
        }

        _body.on('click', '.permalink-structure-auto-btn', function () {
            $('.permalink-structure [name="permalink_structure"]').val('/%post_id%.html').click();
        });

        console.log('\n' + ' %c Zibll Theme %c https://zibll.com ' + '\n', 'color: #fadfa3; background: #030307; padding:3px; font-size:12px;', 'color: #2abd4e;background: #16171a; padding: 3px; font-size: 12px;');
    });
})(jQuery, document);
