/**
 * CSF 小工具：自定义器/小工具页模板占位，按需 AJAX 加载完整表单
 */
(function ($) {
    'use strict';
    var opt = window.zib_csf_widget_form_var;
    // WP 后台会挂载全局 `_`（underscore/lodash）与本地化 l10n/自定义器 API，这里仅做 ESLint 识别用。
    var _ = window._;
    if (!opt) {
        return;
    }

    function parseWidgetNumber($context) {
        var $input = $context.find('input[name^="widget-"]').first();
        var name = $input.attr('name') || '';
        var match = name.match(/\[(\d+)\]/);
        if (match) {
            return match[1];
        }

        var widgetId = $context.attr('id') || '';
        match = widgetId.match(/-(\d+)$/);
        return match ? match[1] : '';
    }

    function initCsfFields($container) {
        var $fields = $container.find('.csf-fields').first();
        if (!$fields.length) {
            $fields = $container;
        }
        if ($fields.length && $.fn.csf_reload_script) {
            $fields.csf_reload_script();
        }
    }

    function setStatus($holder, type, message) {
        var html = '<div class="t-notice t-' + type + '">' + message + '</div>';
        $holder.find('.zib-csf-widget-form-status').html(html);
    }

    window.zibCsfLoadWidgetForm = function ($context, force) {
        if (!$context || !$context.length) {
            return;
        }

        var $holder = $context.find('.zib-csf-widget-form-ajax').first();
        if (!$holder.length) {
            return;
        }

        if ($holder.data('zib-csf-loaded') && !force) {
            return;
        }

        if ($holder.data('zib-csf-loading')) {
            return;
        }

        var idBase = $holder.attr('data-id-base');
        var ajaxUrl = opt.ajax_url;
        var nonce = opt.nonce;
        if (!idBase || !ajaxUrl || !nonce) {
            return;
        }

        var widgetNumber = parseWidgetNumber($context);
        if (!widgetNumber) {
            widgetNumber = $holder.attr('data-widget-number');
        }

        // 模板占位（-1 / __i__）在克隆出真实编号前不请求
        if (!widgetNumber || !/^\d+$/.test(String(widgetNumber))) {
            return;
        }

        $holder.data('zib-csf-loading', true);
        setStatus($holder, 'loading', opt.loading_text);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'zib_csf_widget_form',
                nonce: nonce,
                id_base: idBase,
                widget_number: widgetNumber,
            },
        })
            .done(function (res) {
                if (res && res.success && res.data && res.data.html) {
                    $holder.html(res.data.html);
                    $holder.data('zib-csf-loaded', true);
                    initCsfFields($holder);
                } else {
                    var msg = (res && res.data && res.data.message) || opt.error_text;
                    setStatus($holder, 'error', msg);
                }
            })
            .fail(function () {
                setStatus($holder, 'error', opt.error_text);
            })
            .always(function () {
                $holder.data('zib-csf-loading', false);
            });
    };

    // 让后续代码能直接引用（避免 ESLint 报未定义）。
    var zibCsfLoadWidgetForm = window.zibCsfLoadWidgetForm;

    $(document).on('click', '.widget-top', function () {
        var $widget = $(this).closest('.widget, .customize-control-widget_form');
        setTimeout(function () {
            zibCsfLoadWidgetForm($widget);
        }, 20);
    });

    $(document).on('widget-added widget-updated', function (event, widget) {
        zibCsfLoadWidgetForm($(widget));
    });

    if (window.wp && wp.customize) {
        wp.customize.bind('ready', function () {
            $('#customize-theme-controls').on('expanded', function (event) {
                var section = event && event.section;
                if (!section || section.indexOf('sidebar-widgets') === -1) {
                    return;
                }
                setTimeout(function () {
                    $('.customize-control-widget_form .zib-csf-widget-form-ajax').each(function () {
                        zibCsfLoadWidgetForm($(this).closest('.customize-control-widget_form'));
                    });
                }, 100);
            });
        });
    }

    // ---- 自定义器 widget 表单输入触发的整页 refresh 防抖补丁（主题侧猴子补丁）----
    // WordPress 核心在 wp-admin/js/customize-widgets.js 内部使用：
    //   updateWidgetDebounced = _.debounce(function(){ self.updateWidget(); }, 1050);
    // 当用户在 widget_form 中输入时，会触发 refresh（transport: refresh），造成“输入/resize 就整页刷新”的卡顿。
    // 这里在不修改 WordPress 核心文件的前提下，延长这个 debounce 时间。
    if (window.wp && window.wp.customize && window.wp.customize.Widgets && window.wp.customize.Widgets.WidgetControl) {
        wp.customize.bind('ready', function () {
            var WidgetControl = wp.customize.Widgets.WidgetControl;
            if (!WidgetControl || !WidgetControl.prototype || WidgetControl.prototype.__zibllDebouncePatched) {
                return;
            }

            var proto = WidgetControl.prototype;
            var originalSetupUpdateUI = proto._setupUpdateUI;

            // 取一个更长的 debounce（可选：后续你也可以把它从后端本地化参数里读取）
            var debounceWait = 1050;

            var debounce =
                typeof _ !== 'undefined' && _.debounce
                    ? _.debounce
                    : function (fn, wait) {
                          var t;
                          return function () {
                              var ctx = this,
                                  args = arguments;
                              clearTimeout(t);
                              t = setTimeout(function () {
                                  fn.apply(ctx, args);
                              }, wait);
                          };
                      };

            proto._setupUpdateUI = function () {
                // 先保持 WordPress 原逻辑
                originalSetupUpdateUI.apply(this, arguments);

                try {
                    var self = this;
                    var $widgetContent = this.container && this.container.find ? this.container.find('.widget:first .widget-content:first') : $();

                    if (!$widgetContent || !$widgetContent.length) {
                        return;
                    }

                    // 移除核心添加的“输入/变更 -> debounced updateWidget”监听
                    $widgetContent.off('change input propertychange', ':input');

                    // 重新绑定，并使用更长的 debounce
                    var updateWidgetDebounced = debounce(function () {
                        self.updateWidget();
                    }, debounceWait);

                    $widgetContent.on('change input propertychange', ':input', function (e) {
                        if (!self.liveUpdateMode) {
                            return;
                        }

                        // 保持与核心一致的触发条件
                        if (e.type === 'change' || (this.checkValidity && this.checkValidity())) {
                            updateWidgetDebounced();
                        }
                    });
                } catch (err) {}
            };

            proto.__zibllDebouncePatched = true;
        });
    }
})(jQuery);
