/*
 * Project       : Nova主题
 * Description   : 主交互脚本（原创）
 */
(function ($) {
    'use strict';

    var Nova = {
        js: (typeof nova_js !== 'undefined') ? nova_js : {},
        init: false,

        /**
         * 统一 AJAX 请求
         */
        ajax: function (action, data, success, error, options) {
            options = options || {};
            var payload = $.extend({
                action: action,
                nonce: Nova.js.nonce || ''
            }, data || {});

            return $.ajax({
                url: Nova.js.ajax_url || '',
                type: 'POST',
                data: payload,
                dataType: 'json',
                timeout: options.timeout || 30000
            }).done(function (res) {
                if (res && res.success) {
                    if (typeof success === 'function') success(res.data || {});
                } else {
                    var msg = (res && res.data && res.data.msg) ? res.data.msg : '操作失败，请重试';
                    if (typeof error === 'function') {
                        error(msg);
                    } else {
                        Nova.toast(msg, 'error');
                    }
                }
            }).fail(function () {
                if (typeof error === 'function') {
                    error('网络请求失败');
                } else {
                    Nova.toast('网络请求失败', 'error');
                }
            });
        },

        /**
         * Toast 提示
         */
        toast: function (msg, type) {
            var $t = $('.nova-toast');
            if (!$t.length) {
                $t = $('<div class="nova-toast"></div>').appendTo('body');
            }
            $t.removeClass('nova-success nova-error nova-show');
            if (type) $t.addClass('nova-' + type);
            $t.html(msg).addClass('nova-show');
            clearTimeout($t.data('timer'));
            $t.data('timer', setTimeout(function () {
                $t.removeClass('nova-show');
            }, 2400));
        },

        /**
         * 弹窗
         */
        modal: function (options) {
            options = options || {};
            var mask = $('<div class="nova-modal-mask"><div class="nova-modal">' +
                '<div class="nova-modal-title"><span>' + (options.title || '') + '</span><button type="button" class="nova-modal-close"><i class="nova-icon-close"></i></button></div>' +
                '<div class="nova-modal-body">' + (options.content || '') + '</div>' +
                '</div></div>');

            mask.on('click', function (e) {
                if (e.target === this || $(e.target).hasClass('nova-modal-close') || $(e.target).closest('.nova-modal-close').length) {
                    mask.removeClass('nova-show');
                    setTimeout(function () { mask.remove(); }, 200);
                }
            });
            $('body').append(mask);
            setTimeout(function () { mask.addClass('nova-show'); }, 10);
            return mask;
        },

        /**
         * 确认弹窗
         */
        confirm: function (msg, ok, cancel) {
            var $m = Nova.modal({
                title: Nova.i18n('提示'),
                content: '<p style="margin-bottom:18px">' + msg + '</p>' +
                    '<div style="text-align:right"><button type="button" class="btn btn-outline nova-modal-cancel">' + Nova.i18n('取消') + '</button> ' +
                    '<button type="button" class="btn btn-primary nova-modal-ok">' + Nova.i18n('确定') + '</button></div>'
            });
            $m.find('.nova-modal-ok').on('click', function () {
                $m.removeClass('nova-show');
                setTimeout(function () { $m.remove(); }, 200);
                if (typeof ok === 'function') ok();
            });
            $m.find('.nova-modal-cancel').on('click', function () {
                $m.removeClass('nova-show');
                setTimeout(function () { $m.remove(); }, 200);
                if (typeof cancel === 'function') cancel();
            });
        },

        i18n: function (zh) {
            return zh;
        },

        /**
         * 登录检测
         */
        requireLogin: function (callback) {
            if (parseInt(Nova.js.is_login || 0, 10) === 1) {
                if (typeof callback === 'function') callback();
                return true;
            }
            Nova.toast('请先登录');
            setTimeout(function () {
                window.location.href = (Nova.js.home_url || '/') + '/signin?redirect_to=' + encodeURIComponent(window.location.href);
            }, 800);
            return false;
        },

        /**
         * 页面加载进度条
         */
        pageLoader: function () {
            var $bar = $('.nova-loader-bar');
            if (!$bar.length) return;
            var w = 0;
            var timer = setInterval(function () {
                w += Math.random() * 22 + 6;
                if (w >= 92) { w = 92; clearInterval(timer); }
                $bar.css('width', w + '%');
            }, 220);
            $(window).on('load', function () {
                clearInterval(timer);
                $bar.css('width', '100%');
                setTimeout(function () {
                    $('.nova-page-loader').fadeOut(300);
                }, 300);
            });
        },

        /**
         * 移动端导航
         */
        mobileNav: function () {
            $('#nova-nav-toggle').on('click', function () {
                $(this).toggleClass('nova-open');
                $('#nova-mobile-nav').toggleClass('nova-show');
            });
            $(document).on('click', function (e) {
                var $nav = $('#nova-mobile-nav');
                if ($nav.hasClass('nova-show') &&
                    !$(e.target).closest('#nova-mobile-nav').length &&
                    !$(e.target).closest('#nova-nav-toggle').length) {
                    $nav.removeClass('nova-show');
                    $('#nova-nav-toggle').removeClass('nova-open');
                }
            });
        },

        /**
         * 图片懒加载
         */
        lazyLoad: function () {
            var imgs = document.querySelectorAll('img[data-src]');
            if (!imgs.length) return;
            if (!('IntersectionObserver' in window)) {
                $(imgs).each(function () { Nova.loadImg(this); });
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        Nova.loadImg(entry.target);
                        io.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '120px' });
            imgs.forEach(function (img) { io.observe(img); });
        },

        loadImg: function (img) {
            var $img = $(img);
            var src = $img.attr('data-src');
            if (!src) return;
            $img.addClass('nova-lazy');
            var tmp = new Image();
            tmp.onload = function () {
                $img.attr('src', src).removeAttr('data-src').addClass('nova-loaded');
                setTimeout(function () { $img.removeClass('nova-lazy'); }, 100);
            };
            tmp.src = src;
        },

        /**
         * 回到顶部
         */
        backTop: function () {
            var $bt = $('.nova-backtop');
            if (!$bt.length) return;
            $(window).on('scroll', function () {
                if ($(window).scrollTop() > 400) {
                    $bt.addClass('nova-show');
                } else {
                    $bt.removeClass('nova-show');
                }
            });
            $bt.find('[data-top]').on('click', function (e) {
                e.preventDefault();
                $('html,body').animate({ scrollTop: 0 }, 400);
            });
        },

        /**
         * 首页 Tab 切换
         */
        indexTabs: function () {
            $(document).on('click', '.nova-index-tabs .nova-tab-link', function () {
                var $btn = $(this);
                if ($btn.hasClass('nova-active')) return;
                var tab = $btn.data('tab') || 'new';

                $('.nova-index-tabs .nova-tab-link').removeClass('nova-active');
                $btn.addClass('nova-active');

                var $list = $('.nova-posts-list');
                if (!$list.length) return;

                var loading = Nova.loadingHtml();
                $list.html(loading);

                Nova.ajax('nova_tab_posts', { tab: tab, paged: 1 }, function (res) {
                    $list.html(res.html || '');
                    Nova.refreshLoadmore(tab, res.max || 1, 1);
                    Nova.lazyLoad();
                });
            });
        },

        refreshLoadmore: function (tab, max, paged) {
            var $box = $('.nova-loadmore-wrap, .nova-loadmore-box');
            var $btn = $box.find('.nova-ajax-loadmore, .nova-loadmore-btn');
            if (max <= 1) {
                $box.html('<div class="nova-loadmore-done">' + Nova.i18n('没有更多了') + '</div>');
                return;
            }
            $btn.attr('data-tab', tab || '').attr('data-max', max).attr('data-paged', paged || 1);
        },

        loadingHtml: function () {
            return '<div class="nova-posts-loading" style="text-align:center;padding:50px 0"><i class="nova-icon-loading"></i></div>';
        },

        /**
         * 加载更多
         */
        loadMore: function () {
            $(document).on('click', '.nova-ajax-loadmore, .nova-loadmore-btn', function () {
                var $btn = $(this);
                if ($btn.hasClass('btn-disabled')) return;
                var max = parseInt($btn.attr('data-max') || '1', 10);
                var paged = parseInt($btn.attr('data-paged') || '1', 10);
                var tab = $btn.attr('data-tab') || '';
                var taxonomy = $btn.attr('data-taxonomy') || '';
                var term_id = $btn.attr('data-term') || '';
                var keyword = $btn.attr('data-keyword') || '';

                if (paged >= max) return;
                var old = $btn.html();
                $btn.addClass('btn-disabled').html(Nova.i18n('加载中…'));

                Nova.ajax('nova_load_more', {
                    paged: paged,
                    tab: tab,
                    taxonomy: taxonomy,
                    term_id: term_id,
                    keyword: keyword
                }, function (res) {
                    var $list = $('.nova-posts-list');
                    if ($list.length) {
                        $list.append(res.html || '');
                    }
                    var newPaged = parseInt(res.paged || (paged + 1), 10);
                    if (newPaged >= (res.max || max)) {
                        $btn.closest('.nova-loadmore-wrap, .nova-loadmore-box').html('<div class="nova-loadmore-done">' + Nova.i18n('没有更多了') + '</div>');
                    } else {
                        $btn.attr('data-paged', newPaged).attr('data-max', res.max || max);
                        $btn.removeClass('btn-disabled').html(old);
                    }
                    Nova.lazyLoad();
                }, function () {
                    $btn.removeClass('btn-disabled').html(old);
                });
            });
        },

        /**
         * 文章点赞
         */
        like: function () {
            $(document).on('click', '.nova-like-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    var id = $btn.data('id');
                    Nova.ajax('nova_like', { id: id }, function (res) {
                        var $count = $btn.find('.nova-like-count');
                        var num = res.likes || 0;
                        $count.html(Nova.i18n('点赞') + ' ' + Nova.formatNum(num));
                        if (res.is_like) {
                            $btn.addClass('nova-liked').removeClass('btn-outline');
                        } else {
                            $btn.removeClass('nova-liked').addClass('btn-outline');
                        }
                        Nova.toast(res.is_like ? '点赞成功' : '已取消点赞', 'success');
                    });
                });
            });
        },

        /**
         * 分享
         */
        share: function () {
            $(document).on('click', '.nova-share-btn', function () {
                var $box = $(this).closest('.nova-post-action-bar').find('.nova-share-box');
                $box.toggleClass('nova-show');
            });
            //复制链接
            $(document).on('click', '[data-copy]', function () {
                var text = $(this).attr('data-copy');
                Nova.copyText(text);
            });
        },

        copyText: function (text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function () {
                    Nova.toast('复制成功', 'success');
                });
                return;
            }
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                Nova.toast('复制成功', 'success');
            } catch (e) {
                Nova.toast('复制失败');
            }
            document.body.removeChild(ta);
        },

        /**
         * 打赏
         */
        reward: function () {
            $(document).on('click', '.nova-reward-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    var postId = $btn.data('id');
                    var $m = Nova.modal({
                        title: Nova.i18n('打赏作者'),
                        content: '<div class="nova-form-group"><label>' + Nova.i18n('打赏金额（元）') + '</label>' +
                            '<div class="nova-input-wrap"><i class="nova-icon-money"></i><input type="number" class="nova-reward-money" min="0.1" step="0.1" placeholder="' + Nova.i18n('请输入金额') + '"></div></div>' +
                            '<div class="nova-form-group"><label>' + Nova.i18n('支付方式') + '</label>' +
                            '<select class="nova-reward-pay" style="width:100%;padding:10px;border:1px solid #e3e6ea;border-radius:5px">' +
                            '<option value="alipay">' + Nova.i18n('支付宝') + '</option>' +
                            '<option value="wechat">' + Nova.i18n('微信支付') + '</option>' +
                            '<option value="bank">' + Nova.i18n('线下转账') + '</option>' +
                            '</select></div>' +
                            '<button type="button" class="btn btn-primary btn-block nova-reward-submit">' + Nova.i18n('立即打赏') + '</button>'
                    });
                    $m.find('.nova-reward-submit').on('click', function () {
                        var money = parseFloat($m.find('.nova-reward-money').val());
                        var payType = $m.find('.nova-reward-pay').val();
                        if (!money || money <= 0) {
                            Nova.toast('请输入有效金额');
                            return;
                        }
                        Nova.ajax('nova_reward_post', { id: postId, money: money, pay_type: payType }, function (res) {
                            window.location.href = (Nova.js.home_url || '/') + '/pay?order_id=' + res.order_id;
                        });
                    });
                });
            });
        },

        /**
         * 签到
         */
        sign: function () {
            $(document).on('click', '.nova-sign-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    $btn.addClass('btn-disabled');
                    Nova.ajax('nova_sign', {}, function (res) {
                        Nova.toast('签到成功，+' + (res.points || 0) + ' 积分', 'success');
                        setTimeout(function () { window.location.reload(); }, 1200);
                    }, function () {
                        $btn.removeClass('btn-disabled');
                    });
                });
            });
        },

        /**
         * 图形验证码刷新
         */
        captcha: function () {
            $(document).on('click', '.nova-captcha-img', function () {
                var $img = $(this);
                var src = $img.attr('src');
                var sep = src.indexOf('?') > -1 ? '&' : '?';
                $img.attr('src', src.split('?')[0] + sep + 'r=' + Math.random());
            });
        },

        /**
         * 邮箱验证码发送（倒计时）
         */
        sendCode: function () {
            $(document).on('click', '.nova-code-btn', function () {
                var $btn = $(this);
                if ($btn.hasClass('btn-disabled')) return;
                var type = $btn.attr('data-type') || 'email';
                var scene = $btn.attr('data-scene') || 'register';
                var target = '';
                if (scene === 'register' || scene === 'reset') {
                    target = $('#nova-signup-form input[name="email"], #nova-lostpwd-form input[name="email"]').val() || '';
                } else if (scene === 'bind') {
                    target = $btn.closest('.nova-form-group').siblings().find('.nova-email-new').val() ||
                        $('.nova-email-new').val() || '';
                }
                if (!target) {
                    Nova.toast('请先填写邮箱');
                    return;
                }
                Nova.ajax('nova_send_code', { type: type, scene: scene, target: target }, function () {
                    Nova.toast('验证码已发送', 'success');
                    var left = 60;
                    $btn.addClass('btn-disabled').text(left + 's');
                    var timer = setInterval(function () {
                        left--;
                        if (left <= 0) {
                            clearInterval(timer);
                            $btn.removeClass('btn-disabled').text(Nova.i18n('获取验证码'));
                        } else {
                            $btn.text(left + 's');
                        }
                    }, 1000);
                });
            });
        },

        /**
         * 关注
         */
        follow: function () {
            $(document).on('click', '.nova-follow-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    var uid = $btn.data('user');
                    Nova.ajax('nova_follow', { user: uid }, function (res) {
                        if (res.is_follow) {
                            $btn.text(Nova.i18n('已关注')).addClass('followed');
                        } else {
                            $btn.text(Nova.i18n('+ 关注')).removeClass('followed');
                        }
                        Nova.toast(res.is_follow ? '关注成功' : '已取消关注', 'success');
                    });
                });
            });
        },

        /**
         * 登录/注册/找回密码
         */
        authForms: function () {
            $(document).on('submit', '#nova-signin-form', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('.nova-auth-submit');
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_signin', {
                    username: $form.find('[name="username"]').val(),
                    password: $form.find('[name="password"]').val(),
                    remember: $form.find('[name="remember"]').is(':checked') ? 1 : 0
                }, function () {
                    Nova.toast('登录成功', 'success');
                    var redirect = Nova.getParam('redirect_to');
                    setTimeout(function () {
                        window.location.href = redirect || (Nova.js.home_url || '/') + '/user';
                    }, 800);
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });

            $(document).on('submit', '#nova-signup-form', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('.nova-auth-submit');
                var p1 = $form.find('[name="password"]').val();
                var p2 = $form.find('[name="password2"]').val();
                if (p1 !== p2) {
                    Nova.toast('两次密码不一致');
                    return;
                }
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_signup', {
                    username: $form.find('[name="username"]').val(),
                    email: $form.find('[name="email"]').val(),
                    email_code: $form.find('[name="email_code"]').val(),
                    password: p1
                }, function () {
                    Nova.toast('注册成功，正在登录…', 'success');
                    setTimeout(function () { window.location.reload(); }, 1000);
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });

            $(document).on('submit', '#nova-lostpwd-form', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('.nova-auth-submit');
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_lostpwd', {
                    email: $form.find('[name="email"]').val(),
                    email_code: $form.find('[name="email_code"]').val(),
                    password: $form.find('[name="password"]').val()
                }, function () {
                    Nova.toast('密码重置成功，请登录', 'success');
                    setTimeout(function () {
                        window.location.href = (Nova.js.home_url || '/') + '/signin';
                    }, 1000);
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });
        },

        getParam: function (name) {
            var m = new RegExp('[?&]' + name + '=([^&]+)').exec(window.location.search);
            return m ? decodeURIComponent(m[1]) : '';
        },

        /**
         * 资料保存
         */
        profile: function () {
            $(document).on('click', '.nova-profile-save', function () {
                var $btn = $(this);
                var fields = {
                    nova_name: $('.nova-profile-name').val() || '',
                    nova_sign_text: $('.nova-profile-sign').val() || '',
                    user_url: $('.nova-profile-site').val() || ''
                };
                $btn.addClass('btn-disabled');
                var keys = Object.keys(fields);
                var idx = 0;
                var done = 0;
                keys.forEach(function (key) {
                    Nova.ajax('nova_update_profile', { field: key, value: fields[key] }, function () {
                        done++;
                        if (done >= keys.length) {
                            $btn.removeClass('btn-disabled');
                            Nova.toast('资料已保存', 'success');
                        }
                    }, function () {
                        $btn.removeClass('btn-disabled');
                    });
                });
            });

            //修改密码
            $(document).on('click', '.nova-password-save', function () {
                var $btn = $(this);
                var old = $('.nova-pwd-old').val();
                var n1 = $('.nova-pwd-new').val();
                var n2 = $('.nova-pwd-new2').val();
                if (!old || !n1) {
                    Nova.toast('请填写完整');
                    return;
                }
                if (n1 !== n2) {
                    Nova.toast('两次新密码不一致');
                    return;
                }
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_change_password', { old_password: old, new_password: n1 }, function () {
                    $btn.removeClass('btn-disabled');
                    Nova.toast('密码修改成功', 'success');
                    $('.nova-pwd-old,.nova-pwd-new,.nova-pwd-new2').val('');
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });

            //头像上传
            $(document).on('click', '.nova-avatar-upload-btn', function () {
                $(this).closest('.nova-profile-avatar-row').find('.nova-avatar-file').trigger('click');
            });
            $(document).on('change', '.nova-avatar-file', function () {
                var $input = $(this);
                var file = $input[0].files && $input[0].files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    Nova.toast('图片不能超过 2MB');
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (ev) {
                    Nova.ajax('nova_upload_avatar', { file: ev.target.result }, function (res) {
                        Nova.toast('头像已更新', 'success');
                        $('.nova-profile-avatar-row .nova-avatar-lg img, .nova-uc-user img').attr('src', res.url);
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        /**
         * 论坛发帖
         */
        bbsPublish: function () {
            $(document).on('click', '.nova-bbs-publish-btn', function () {
                var $btn = $(this);
                var id = $btn.attr('data-id') || 0;
                var title = $('.nova-bbs-title').val();
                var content = $('.nova-bbs-content').val();
                var plate = $('.nova-bbs-plate-select').val() || $('.nova-bbs-plate-select').attr('data-plate') || 0;
                if (!title || title.length < 2) {
                    Nova.toast('标题至少2个字');
                    return;
                }
                if (!content || content.replace(/<[^>]+>/g, '').trim().length < 5) {
                    Nova.toast('内容至少5个字');
                    return;
                }
                $btn.addClass('btn-disabled').text(Nova.i18n('提交中…'));
                Nova.ajax('nova_bbs_publish', { id: id, plate: plate, title: title, content: content }, function (res) {
                    Nova.toast('发布成功', 'success');
                    setTimeout(function () {
                        window.location.href = res.url || (Nova.js.home_url || '/') + '/forum';
                    }, 800);
                }, function () {
                    $btn.removeClass('btn-disabled').text(Nova.i18n('发布帖子'));
                });
            });

            //帖子管理（置顶/精华/删除）
            $(document).on('click', '.nova-bbs-manage-btn', function () {
                var $btn = $(this);
                var id = $btn.attr('data-id');
                var act = $btn.attr('data-act');
                Nova.ajax('nova_bbs_manage', { id: id, act: act }, function () {
                    Nova.toast('操作成功', 'success');
                    setTimeout(function () { window.location.reload(); }, 800);
                });
            });
        },

        /**
         * 商城
         */
        shop: function () {
            //加入购物车
            $(document).on('click', '.nova-add-cart-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    var qty = parseInt($btn.closest('.nova-product-buy').find('.nova-qty-input').val() || '1', 10);
                    Nova.ajax('nova_add_cart', { id: $btn.data('id'), qty: qty }, function () {
                        Nova.toast('已加入购物车', 'success');
                    });
                });
            });

            //立即购买 → 加入购物车后直达结算
            $(document).on('click', '.nova-buy-now-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    var qty = parseInt($btn.closest('.nova-product-buy').find('.nova-qty-input').val() || '1', 10);
                    Nova.ajax('nova_add_cart', { id: $btn.data('id'), qty: qty }, function () {
                        window.location.href = (Nova.js.home_url || '/') + '/checkout';
                    });
                });
            });

            //数量加减
            $(document).on('click', '.nova-qty-minus, .nova-qty-plus', function () {
                var $btn = $(this);
                var $input = $btn.siblings('.nova-qty-input');
                var val = parseInt($input.val() || '1', 10);
                var isPlus = $btn.hasClass('nova-qty-plus');
                val = isPlus ? val + 1 : val - 1;
                if (val < 1) val = 1;
                var max = parseInt($input.attr('max') || '999', 10);
                if (val > max) val = max;
                $input.val(val).trigger('change');
            });

            //购物车数量变更
            $(document).on('change', '.nova-cart-item .nova-qty-input', function () {
                var $input = $(this);
                var id = $input.closest('.nova-cart-item').data('id');
                Nova.ajax('nova_update_cart', { id: id, qty: parseInt($input.val() || '1', 10) }, function () {
                    Nova.toast('已更新', 'success');
                });
            });

            //删除购物车项
            $(document).on('click', '.nova-cart-del', function () {
                var $btn = $(this);
                Nova.confirm(Nova.i18n('确定删除该商品吗？'), function () {
                    Nova.ajax('nova_update_cart', { id: $btn.data('id'), qty: 0 }, function () {
                        Nova.toast('已删除', 'success');
                        setTimeout(function () { window.location.reload(); }, 500);
                    });
                });
            });

            //结算下单
            $(document).on('click', '.nova-checkout-submit', function () {
                var $btn = $(this);
                var name = $('.nova-checkout-name').val();
                var phone = $('.nova-checkout-phone').val();
                var address = $('.nova-checkout-address').val();
                var remark = $('.nova-checkout-remark').val() || '';
                if (!name || !phone || !address) {
                    Nova.toast('请填写完整的收货信息');
                    return;
                }
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_create_shop_order', {
                    name: name,
                    phone: phone,
                    address: address,
                    remark: remark
                }, function (res) {
                    window.location.href = (Nova.js.home_url || '/') + '/pay?order_id=' + res.order_id;
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });
        },

        /**
         * 支付页
         */
        pay: function () {
            var $payPage = $('.nova-pay-page');
            if (!$payPage.length) return;
            var orderId = $payPage.attr('data-order');

            //通道切换
            $(document).on('click', '.nova-pay-channel', function () {
                var $ch = $(this);
                var channel = $ch.attr('data-channel');
                $('.nova-pay-channel').removeClass('nova-active');
                $ch.addClass('nova-active');
                $('.nova-pay-action-alipay, .nova-pay-action-wechat, .nova-pay-action-bank').hide();
                $('.nova-pay-action-' + channel).show();
                if (channel === 'wechat') {
                    Nova.wechatPay(orderId);
                }
            });

            //支付宝
            $(document).on('click', '.nova-pay-action-alipay .nova-pay-trigger', function () {
                window.location.href = (Nova.js.home_url || '/') + '/nova-pay-gateway?order_id=' + orderId + '&pay_type=alipay';
            });

            //余额支付
            $(document).on('click', '.nova-pay-balance-btn', function () {
                var $btn = $(this);
                Nova.confirm(Nova.i18n('确认使用余额支付？'), function () {
                    $btn.addClass('btn-disabled');
                    Nova.ajax('nova_pay_balance', { order_id: orderId }, function () {
                        Nova.toast('支付成功', 'success');
                        setTimeout(function () { window.location.reload(); }, 1200);
                    }, function () {
                        $btn.removeClass('btn-disabled');
                    });
                });
            });

            //银行转账凭证
            $(document).on('click', '.nova-bank-submit-btn', function () {
                var $btn = $(this);
                var remark = $('.nova-bank-remark').val() || '';
                if (!remark) {
                    Nova.toast('请填写转账备注');
                    return;
                }
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_pay_bank_submit', { order_id: orderId, remark: remark }, function () {
                    Nova.toast('凭证已提交，等待管理员确认', 'success');
                    setTimeout(function () { window.location.reload(); }, 1200);
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });
        },

        /**
         * 微信支付二维码 + 轮询
         */
        wechatPay: function (orderId) {
            var $box = $('.nova-wechat-qrcode');
            if (!$box.length || $box.data('loaded')) return;
            var url = $box.attr('data-url') || '';
            if (!url) return;
            $box.data('loaded', 1);
            Nova.ajax('nova_qrcode', { url: url }, function (res) {
                $box.html('<img src="' + res.qrcode + '" alt="qr"><p>' + Nova.i18n('请使用微信扫码支付，支付完成后将自动刷新') + '</p>');
                Nova.pollPayStatus(orderId);
            }, function () {
                $box.data('loaded', 0);
            });
        },

        pollPayStatus: function (orderId) {
            var times = 0;
            var timer = setInterval(function () {
                times++;
                if (times > 120) { clearInterval(timer); return; } // 5分钟超时
                Nova.ajax('nova_pay_status', { order_id: orderId }, function (res) {
                    if (res.status && parseInt(res.status, 10) === 1) {
                        clearInterval(timer);
                        Nova.toast('支付成功', 'success');
                        setTimeout(function () { window.location.reload(); }, 1200);
                    }
                }, function () {
                    clearInterval(timer);
                });
            }, 2500);
        },

        /**
         * 充值卡兑换
         */
        card: function () {
            $(document).on('click', '.nova-card-submit', function () {
                var $btn = $(this);
                var num = $('.nova-card-num').val() || '';
                var pass = $('.nova-card-pass').val() || '';
                if (!num || !pass) {
                    Nova.toast('请输入卡号和卡密');
                    return;
                }
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_use_card', { card_num: num, card_pass: pass }, function () {
                    Nova.toast('兑换成功', 'success');
                    setTimeout(function () { window.location.reload(); }, 1000);
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });
        },

        /**
         * 提现申请
         */
        withdraw: function () {
            $(document).on('click', '.nova-withdraw-submit', function () {
                var $btn = $(this);
                var amount = parseFloat($('.nova-withdraw-amount').val() || '0');
                var account = $('.nova-withdraw-account').val() || '';
                var name = $('.nova-withdraw-name').val() || '';
                var type = $('.nova-withdraw-type').val() || 'alipay';
                if (!amount || amount <= 0) {
                    Nova.toast('请输入有效金额');
                    return;
                }
                if (!account || !name) {
                    Nova.toast('请填写收款账号和姓名');
                    return;
                }
                $btn.addClass('btn-disabled');
                Nova.ajax('nova_withdraw', {
                    amount: amount,
                    account: account,
                    name: name,
                    type: type
                }, function () {
                    Nova.toast('申请已提交，等待审核', 'success');
                    setTimeout(function () { window.location.reload(); }, 1000);
                }, function () {
                    $btn.removeClass('btn-disabled');
                });
            });
        },

        /**
         * 消息中心
         */
        message: function () {
            //回复私信
            $(document).on('click', '.nova-msg-reply-btn', function () {
                var $btn = $(this);
                Nova.requireLogin(function () {
                    var to = $btn.attr('data-user');
                    var $m = Nova.modal({
                        title: Nova.i18n('回复私信'),
                        content: '<div class="nova-form-group">' +
                            '<textarea class="nova-msg-content-input" rows="5" maxlength="1000" style="width:100%;border:1px solid #e3e6ea;border-radius:5px;padding:10px" placeholder="' + Nova.i18n('请输入回复内容') + '"></textarea></div>' +
                            '<button type="button" class="btn btn-primary btn-block nova-msg-send">' + Nova.i18n('发送') + '</button>'
                    });
                    $m.find('.nova-msg-send').on('click', function () {
                        var content = $m.find('.nova-msg-content-input').val() || '';
                        if (!content) {
                            Nova.toast('请输入内容');
                            return;
                        }
                        Nova.ajax('nova_send_message', { to: to, content: content }, function () {
                            $m.removeClass('nova-show');
                            setTimeout(function () { $m.remove(); }, 200);
                            Nova.toast('发送成功', 'success');
                        });
                    });
                });
            });

            //删除消息
            $(document).on('click', '.nova-msg-del-btn', function () {
                var $btn = $(this);
                Nova.ajax('nova_delete_message', { id: $btn.attr('data-id') }, function () {
                    $btn.closest('.nova-msg-item').fadeOut(300, function () { $(this).remove(); });
                });
            });

            //全部已读
            $(document).on('click', '.nova-msg-all-read', function () {
                Nova.ajax('nova_read_message', { id: 0 }, function () {
                    $('.nova-msg-item').removeClass('nova-unread');
                    Nova.toast('已全部标记为已读', 'success');
                });
            });
        },

        /**
         * 搜索建议
         */
        searchSuggest: function () {
            var $input = $('.nova-header-search .nova-search-input');
            if (!$input.length) return;
            var timer = null;
            $input.on('input', function () {
                var kw = $.trim($input.val());
                if (kw.length < 2) {
                    $('.nova-search-drop').hide();
                    return;
                }
                clearTimeout(timer);
                timer = setTimeout(function () {
                    Nova.ajax('nova_search_suggest', { keyword: kw }, function (res) {
                        var $drop = $('.nova-search-drop');
                        if (!$drop.length) {
                            $drop = $('<div class="nova-search-drop"></div>').appendTo('.nova-header-search');
                        }
                        var list = res;
                        var html = '';
                        if ($.isArray(list)) {
                            if (!list.length) {
                                html = '<div style="padding:10px 16px;font-size:13px;color:#999">' + Nova.i18n('未找到相关内容') + '</div>';
                            } else {
                                $.each(list, function (i, item) {
                                    html += '<a href="' + item.url + '">' + item.title + '</a>';
                                });
                            }
                        }
                        $drop.html(html).show();
                    });
                }, 300);
            });
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.nova-header-search').length) {
                    $('.nova-search-drop').hide();
                }
            });
        },

        formatNum: function (num) {
            num = parseInt(num || 0, 10);
            if (num >= 10000) {
                return (num / 10000).toFixed(1) + 'w';
            }
            return num;
        },

        /**
         * 初始化
         */
        init: function () {
            if (Nova.init) return;
            Nova.init = true;
            Nova.pageLoader();
            Nova.mobileNav();
            Nova.lazyLoad();
            Nova.backTop();
            Nova.indexTabs();
            Nova.loadMore();
            Nova.like();
            Nova.share();
            Nova.reward();
            Nova.sign();
            Nova.captcha();
            Nova.sendCode();
            Nova.follow();
            Nova.authForms();
            Nova.profile();
            Nova.bbsPublish();
            Nova.shop();
            Nova.pay();
            Nova.card();
            Nova.withdraw();
            Nova.message();
            Nova.searchSuggest();
        }
    };

    $(document).ready(function () {
        Nova.init();
    });

    window.Nova = Nova;
})(jQuery);
