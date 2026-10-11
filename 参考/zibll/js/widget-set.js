/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime : 2026-05-27
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */
console.log('Zibll Widget');

(function ($, document) {
    function _zib__(key) {
        var map = window.zib_widget_set_var && window.zib_widget_set_var.i18n;
        if (map && Object.prototype.hasOwnProperty.call(map, key)) {
            return map[key];
        }
        return key;
    }

    /** 带图片上传的小工具 repeater 行（幻灯片 / 友情链接等） */
    function buildMediaRepeaterRow(labelPrefix, index, nameAttr, labels) {
        var i1 = index + 1;
        var titleLabel = labelPrefix + i1 + (labels.title || '-标题:');
        var decLabel = labelPrefix + i1 + (labels.dec || '-简介');
        var hrefLabel = labelPrefix + i1 + (labels.href || '-链接');
        var linkLabel = labelPrefix + i1 + (labels.link || '-图片');
        return (
            '<div class="widget_ui_slider_g">' +
            '<div class="panel"><h4 class="panel-title">' +
            labelPrefix +
            i1 +
            '</h4><div class="panel-conter">' +
            '<label>' +
            titleLabel +
            '<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][title]" value=""></label>' +
            '<label>' +
            decLabel +
            '<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][dec]" value=""></label>' +
            '<label>' +
            hrefLabel +
            '<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][href]" value=""></label>' +
            '<div class=""><label>' +
            linkLabel +
            '<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][link]" value=""></label>' +
            '<button type="button" class="button ashu_upload_button">' +
            _zib__('admin_select_image') +
            '</button>' +
            '<button type="button" class="button delimg_upload_button">' +
            _zib__('admin_remove_image') +
            '</button>' +
            '<div class="widget_ui_slider_box"></div></div></div></div></div>'
        );
    }

    /** 消息 repeater 行 */
    function buildNoticeRepeaterRow(index, nameAttr) {
        var i1 = index + 1;
        return (
            '<div class="widget_ui_slider_g">' +
            '<div class="panel"><h4 class="panel-title">消息' +
            i1 +
            '</h4><div class="panel-conter">' +
            '<label>消息' +
            i1 +
            '-内容（必填）:<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][title]" value=""></label>' +
            '<label>消息' +
            i1 +
            '-图标<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][icon]" value=""></label>' +
            '<label>消息' +
            i1 +
            '-链接<input style="width:100%;" type="text" name="' +
            nameAttr +
            '[' +
            index +
            '][href]" value=""></label>' +
            '</div></div></div>'
        );
    }

    /** WIE：导入模式单选 HTML */
    function getImportModeHtml(radioName, replaceSpanClass, appendSpanClass) {
        return (
            '<div class="zib-wie-modal__import-mode">' +
            '<div class="zib-wie-modal__import-mode-title description"></div>' +
            '<div class="flex ac hh">' +
            '<label class="zib-wie-modal__import-mode-row"><input type="radio" name="' +
            radioName +
            '" value="replace" /> <span class="' +
            replaceSpanClass +
            '"></span></label>' +
            '<label class="zib-wie-modal__import-mode-row"><input type="radio" name="' +
            radioName +
            '" value="append" checked="checked" /> <span class="' +
            appendSpanClass +
            '"></span></label>' +
            '</div></div>'
        );
    }

    /** WIE：填充导入模式文案 */
    function bindImportModeI18n($root, replaceSelector, appendSelector) {
        $root.find('.zib-wie-modal__import-mode-title').text(_zib__('importModeLabel'));
        $root.find(replaceSelector).text(_zib__('importModeReplace'));
        $root.find(appendSelector).text(_zib__('importModeAppend'));
    }

    /** WIE：admin-ajax POST */
    function wiePost(zibConfig, action, data) {
        return $.ajax({
            url: zibConfig.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: $.extend(
                {
                    action: action,
                    nonce: zibConfig.nonce,
                },
                data || {}
            ),
        });
    }

    /** WIE：解析服务端 message */
    function getWieMessage(res, fallbackKey) {
        return (res.data && res.data.message) || _zib__(fallbackKey);
    }

    /** WIE：清空结果区样式 */
    function clearWieResult($result) {
        return $result.removeClass('is-ok is-warn is-err').text('');
    }

    /** WIE：显示错误 */
    function showWieError($result, message) {
        $result.removeClass('is-ok is-warn').addClass('is-err').text(message);
    }

    /** WIE：网络错误 */
    function showNetworkError($result) {
        showWieError($result, _zib__('admin_network_error'));
    }

    /** WIE：导入成功后的刷新按钮 */
    function getReloadBtnHtml() {
        return (
            '<p class="zib-wie-modal__reload-wrap"><a href="' +
            window.location.href +
            '" class="button button-primary zib-wie-modal__reload">' +
            (_zib__('reloadPage') || '刷新页面') +
            '</a></p>'
        );
    }

    /**
     * WIE：渲染导入成功/失败结果
     * @param {jQuery} $result
     * @param {object} res - WP ajax 响应
     * @param {object} [opts]
     * @param {string} [opts.failKey]
     * @param {boolean} [opts.autoReload]
     * @param {Function} [opts.onSuccess]
     */
    function renderImportResult($result, res, opts) {
        opts = opts || {};
        var failKey = opts.failKey || 'admin_import_failed';
        clearWieResult($result);
        if (res.success && res.data) {
            var reloadBtn = getReloadBtnHtml();
            var lines = [res.data.message || _zib__('admin_import_success')];
            if (res.data.warnings && res.data.warnings.length) {
                lines = lines.concat(res.data.warnings);
                $result.addClass('is-warn').html(lines.join('<br>') + reloadBtn);
            } else {
                $result.addClass('is-ok').html(lines[0] + reloadBtn);
            }
            if (opts.onSuccess) {
                opts.onSuccess(res);
            }
            if (opts.autoReload) {
                forceRefreshPage();
            }
            return true;
        }
        showWieError($result, getWieMessage(res, failKey));
        return false;
    }

    /** WIE：提交按钮 loading */
    function withButtonLoading($btn, loadingText, idleText, requestFn) {
        $btn.prop('disabled', true).text(loadingText);
        var jq = requestFn();
        if (!jq || !jq.always) {
            $btn.prop('disabled', false).text(idleText);
            return jq;
        }
        return jq.always(function () {
            $btn.prop('disabled', false).text(idleText);
        });
    }

    /** WIE：强制刷新页面 */
    function forceRefreshPage() {
        setTimeout(function () {
            window.location.reload();
        }, 200);
    }

    /**
     * WIE：弹层工厂（导出/导入、模板选择共用外壳）
     * @param {object} opts
     * @returns {{ ensure: Function, close: Function, open: Function, $result: Function, $el: Function, setImportModeDefault: Function }}
     */
    function createWieModal(opts) {
        var $modal = null;

        function ensure() {
            if ($modal && $modal.length) {
                return $modal;
            }

            var shell =
                '<div id="' +
                opts.id +
                '" class="zib-wie-modal' +
                (opts.extraClass ? ' ' + opts.extraClass : '') +
                '" aria-hidden="true">' +
                '<div class="zib-wie-modal__backdrop" ' +
                opts.closeAttr +
                '="1"></div>' +
                '<div class="zib-wie-modal__dialog" role="dialog" aria-modal="true">' +
                '<button type="button" class="zib-wie-modal__close' +
                (opts.closeBtnClass ? ' ' + opts.closeBtnClass : '') +
                '" aria-label="关闭">&times;</button>' +
                (opts.bodyHtml || '') +
                (opts.actionsHtml || '') +
                '<p class="zib-wie-modal__result' +
                (opts.resultExtraClass ? ' ' + opts.resultExtraClass : '') +
                '"></p>' +
                '</div></div>';

            $modal = $(shell).appendTo('body');

            if (opts.importMode) {
                bindImportModeI18n($modal, opts.importMode.replaceSelector, opts.importMode.appendSelector);
            }

            if (opts.onInit) {
                opts.onInit($modal);
            }

            $modal.find('.zib-wie-modal__close, ' + (opts.cancelSelector || '')).on('click', close);
            $modal.on('click', '[' + opts.closeAttr + ']', close);
            $modal.find('.zib-wie-modal__dialog').on('click', function (e) {
                e.stopPropagation();
            });

            return $modal;
        }

        function close() {
            if ($modal && $modal.length) {
                $modal.removeClass('is-open').attr('aria-hidden', 'true');
            }
        }

        function open() {
            ensure();
            clearWieResult($result());
            $modal.addClass('is-open').attr('aria-hidden', 'false');
        }

        function $result() {
            return ensure().find(opts.resultSelector || '.zib-wie-modal__result');
        }

        function setImportModeDefault(radioName, value) {
            ensure().find('input[name="' + radioName + '"][value="' + (value || 'append') + '"]').prop('checked', true);
        }

        return {
            ensure: ensure,
            close: close,
            open: open,
            $result: $result,
            $el: function () {
                return ensure();
            },
            setImportModeDefault: setImportModeDefault,
        };
    }

    /** WIE：渲染模板列表项 */
    function appendTemplateListItem($list, template) {
        var id = template.id || '';
        var $item = $('<div class="zib-wie-template-item"/>');
        var $row = $('<div class="zib-wie-template-item-row"/>');
        var $label = $('<label class="zib-wie-template-item-label"/>');
        $label.append($('<input type="radio" name="zib-wie-template-id" />').val(id));
        var $meta = $('<span class="zib-wie-template-item-meta"/>');
        $meta.append($('<span class="zib-wie-template-item-title"/>').text(template.title || id));
        if (template.desc) {
            $meta.append($('<span class="zib-wie-template-item-desc"/>').html(template.desc));
        }
        if (template.remind) {
            $meta.append($('<span class="zib-wie-template-item-remind"/>').html('<i class="fa fa-fw fa-info-circle"></i>' + template.remind));
        }
        $label.append($meta);
        $row.append($label);
        $item.append($row);
        $list.append($item);
    }

    $(document).ready(function () {
        var _body = $('body');

        var img_ss = '';

        $('body').on('click', '.add_lists_button', function () {
            var _s_i = parseInt($(this).attr('data-count'));
            var _s_m = $(this).attr('data-name');

            var _s_html =
                '<div class="widget_ui_slider_g">\
        <div class="panel"><h4 class="panel-title">栏目' +
                (_s_i + 1) +
                '</h4><div class="panel-conter">\
        <label>栏目' +
                (_s_i + 1) +
                '-标题（必填）：\
        <input style="width:100%;" type="text" name="' +
                _s_m +
                '[' +
                _s_i +
                '][title]" value="">\
        </label>\
        <label>栏目' +
                (_s_i + 1) +
                '-分类限制：\
        <input style="width:100%;" type="text" name="' +
                _s_m +
                '[' +
                _s_i +
                '][cat]" value="">\
        </label>\
        <label>栏目' +
                (_s_i + 1) +
                '-专题限制：\
        <input style="width:100%;" type="text" name="' +
                _s_m +
                '[' +
                _s_i +
                '][topics]" value="">\
        </label>\
        <label>栏目' +
                (_s_i + 1) +
                '-排序方式：\
            <select style="width:100%;" name="' +
                _s_m +
                '[' +
                _s_i +
                '][orderby]">\
            <option value="comment_count">评论数</option>\
            <option value="views" selected="selected">浏览量</option>\
            <option value="like">点赞数</option>\
            <option value="favorite">收藏数</option>\
            <option value="date">发布时间</option>\
            <option value="modified">更新时间</option>\
            <option value="rand">随机排序</option>\
        </select>\
        </label></div></div></div>';
            $(this)
                .attr('data-count', _s_i + 1)
                .before(_s_html);
        });
        $('body').on('click', '.rem_lists_button', function () {
            $(this).prev().prev('.widget_ui_slider_g').remove();
            var add_b = $(this).siblings('.add_button');
            var _s_i = parseInt(add_b.attr('data-count'));
            add_b.attr('data-count', _s_i - 1);
        });

        var ashu_upload_frame;
        img_ss = '';
        $('body').on('click', '.delimg_upload_button', function () {
            $(this).siblings('div').html('');
            $(this).siblings('label').find('input').val('');
        });
        $('body').on('click', '.add_slider_button', function () {
            var _s_i = parseInt($(this).attr('data-count'));
            var _s_i1 = _s_i + 1;
            var _s_m = $(this).attr('data-name');
            var _s_html = buildMediaRepeaterRow('幻灯片', _s_i, _s_m, {
                title: '-标题:',
                dec: '-简介',
                href: '-链接',
                link: '-图片',
            });
            $(this).attr('data-count', _s_i1).before(_s_html);
        });
        $('body').on('click', '.add_links_button', function () {
            var _s_i = parseInt($(this).attr('data-count'));
            var _s_i1 = _s_i + 1;
            var _s_m = $(this).attr('data-name');
            var _s_html = buildMediaRepeaterRow('链接', _s_i, _s_m, {
                title: '-名称（必填）:',
                dec: '-简介',
                href: '-链接（必填）',
                link: '-图片',
            });
            $(this).attr('data-count', _s_i1).before(_s_html);
        });
        $('body').on('click', '.add_notice_button', function () {
            var _s_i = parseInt($(this).attr('data-count'));
            var _s_i1 = _s_i + 1;
            var _s_m = $(this).attr('data-name');
            $(this).attr('data-count', _s_i1).before(buildNoticeRepeaterRow(_s_i, _s_m));
        });
        $('body').on('click', '.ashu_upload_button', function (event) {
            var _his = $(this);
            event.preventDefault();
            if (ashu_upload_frame) {
                ashu_upload_frame.open();
                return;
            }
            ashu_upload_frame = wp.media({
                title: _zib__('admin_select_image_slide'),
                button: {
                    text: _zib__('confirm'),
                },
                multiple: true,
            });
            ashu_upload_frame.on('select', function () {
                var attachment = ashu_upload_frame.state().get('selection').first().toJSON();

                img_ss = '<img src="' + attachment.url + '">';
                _his.siblings('div').html(img_ss);
                _his.siblings('label').find('input').val(attachment.url);
            });
            ashu_upload_frame.open();
        });
        $('body').on('click', '.cat-help-button', function () {
            $(this).siblings('.cat-help-con').slideToggle();
        });
        $('body').on('click', '.panel-title', function () {
            $(this).siblings('.panel-conter').slideToggle();
        });

        /**
         * 侧栏小工具：在侧栏描述下注入导出/导入，弹层内 JSON 文本域
         */
        (function () {
            var _zib = window.zib_widget_set_var;
            if (!_zib || !_zib.ajaxUrl) {
                return;
            }

            var state = { mode: 'export', sidebarId: '' };
            var tplState = { sidebarId: '' };

            var ioModal = createWieModal({
                id: 'zib-wie-modal',
                closeAttr: 'data-zib-wie-close',
                cancelSelector: '.zib-wie-modal__cancel',
                importMode: {
                    replaceSelector: '.zib-wie-import-txt-replace',
                    appendSelector: '.zib-wie-import-txt-append',
                },
                bodyHtml:
                    '<h3 class="zib-wie-modal__title"></h3>' +
                    '<p class="zib-wie-modal__hint description"></p>' +
                    '<textarea class="zib-wie-modal__textarea" spellcheck="false"></textarea>' +
                    getImportModeHtml('zib-wie-import-mode', 'zib-wie-import-txt-replace', 'zib-wie-import-txt-append'),
                actionsHtml:
                    '<p class="zib-wie-modal__actions">' +
                    '<button type="button" class="button zib-wie-modal__copy"></button>' +
                    '<button type="button" class="button button-primary zib-wie-modal__submit"></button>' +
                    '<button type="button" class="button zib-wie-modal__cancel"></button>' +
                    '</p>',
                onInit: function ($modal) {
                    $modal.find('.zib-wie-modal__copy').on('click', function () {
                        var ta = $modal.find('.zib-wie-modal__textarea')[0];
                        if (!ta) {
                            return;
                        }
                        ta.select();
                        ta.setSelectionRange(0, ta.value.length);
                        try {
                            document.execCommand('copy');
                            $(this).text(_zib__('copyDone'));
                        } catch (err) {
                            alert(_zib__('copyFail'));
                        }
                    });
                    $modal.find('.zib-wie-modal__submit').on('click', doImportSubmit);
                },
            });

            var tplModal = createWieModal({
                id: 'zib-wie-template-modal',
                extraClass: 'zib-wie-template-layer',
                closeAttr: 'data-zib-wie-tpl-close',
                closeBtnClass: 'zib-wie-tpl-close',
                cancelSelector: '.zib-wie-template-cancel',
                resultExtraClass: 'zib-wie-template-result',
                importMode: {
                    replaceSelector: '.zib-wie-tpl-txt-replace',
                    appendSelector: '.zib-wie-tpl-txt-append',
                },
                bodyHtml:
                    '<h3 class="zib-wie-modal__title zib-wie-template-head-title"></h3>' +
                    '<p class="zib-wie-modal__hint description zib-wie-template-head-hint"></p>' +
                    '<div class="zib-wie-template-list-wrap"><p class="zib-wie-template-list-status description"></p><div class="zib-wie-template-list"></div></div>' +
                    getImportModeHtml('zib-wie-template-import-mode', 'zib-wie-tpl-txt-replace', 'zib-wie-tpl-txt-append').replace(
                        'zib-wie-modal__import-mode',
                        'zib-wie-modal__import-mode zib-wie-template-import-mode'
                    ),
                actionsHtml:
                    '<p class="zib-wie-modal__actions">' +
                    '<button type="button" class="button button-primary zib-wie-template-submit"></button>' +
                    '<button type="button" class="button zib-wie-template-cancel"></button>' +
                    '</p>',
                onInit: function ($modal) {
                    $modal.find('.zib-wie-template-head-title').text(_zib__('templateModalTitle'));
                    $modal.find('.zib-wie-template-head-hint').html(_zib__('templateHint'));
                    $modal.find('.zib-wie-template-submit').text(_zib__('templateSubmit'));
                    $modal.find('.zib-wie-template-cancel').text(_zib__('close'));
                    var $tplDialog = $modal.find('.zib-wie-modal__dialog');
                    $modal.find('.zib-wie-template-submit').on('click', doTemplateImportSubmit);
                    $tplDialog.on('click', '.zib-wie-template-item-expand', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var $btn = $(this);
                        var expanded = $btn.hasClass('is-expanded');
                        var $item = $btn.closest('.zib-wie-template-item');
                        var $mods = $item.find('.zib-wie-template-item-modules');
                        if (expanded) {
                            $btn.removeClass('is-expanded').attr('aria-expanded', 'false');
                            $btn.attr('aria-label', _zib__('templateExpandModules'));
                            $mods.hide();
                        } else {
                            $btn.addClass('is-expanded').attr('aria-expanded', 'true');
                            $btn.attr('aria-label', _zib__('templateCollapseModules'));
                            $mods.show();
                        }
                    });
                },
            });

            function setModeUi(mode) {
                var $modal = ioModal.$el();
                var $copy = $modal.find('.zib-wie-modal__copy');
                var $sub = $modal.find('.zib-wie-modal__submit');
                if (mode === 'export') {
                    $copy.show().text(_zib__('copy'));
                    $sub.hide();
                    $modal.find('.zib-wie-modal__import-mode').hide();
                } else {
                    $copy.hide();
                    $sub.show().text(_zib__('submitImport')).prop('disabled', false);
                    $modal.find('.zib-wie-modal__import-mode').show();
                }
                $modal.find('.zib-wie-modal__cancel').text(_zib__('close'));
            }

            function openModal(mode, sidebarId) {
                state.mode = mode;
                state.sidebarId = sidebarId;
                var $modal = ioModal.ensure();
                clearWieResult(ioModal.$result());
                $modal
                    .find('.zib-wie-modal__textarea')
                    .prop('readonly', mode === 'export')
                    .val('');
                if (mode === 'export') {
                    $modal.find('.zib-wie-modal__title').text(_zib__('exportTitle'));
                    $modal.find('.zib-wie-modal__hint').text(_zib__('exportHint'));
                    setModeUi('export');
                    fetchExport();
                } else {
                    $modal.find('.zib-wie-modal__title').text(_zib__('importTitle'));
                    $modal.find('.zib-wie-modal__hint').text(_zib__('importHint'));
                    ioModal.setImportModeDefault('zib-wie-import-mode', 'append');
                    setModeUi('import');
                }
                ioModal.open();
            }

            function fetchExport() {
                var $modal = ioModal.$el();
                var $ta = $modal.find('.zib-wie-modal__textarea');
                $ta.val(_zib__('loadingExport'));
                wiePost(_zib, 'zib_wie_export_sidebar', {
                    sidebar_id: state.sidebarId,
                })
                    .done(function (res) {
                        if (res.success && res.data && res.data.json_text) {
                            $ta.val(res.data.json_text);
                        } else {
                            $ta.val('');
                            showWieError(ioModal.$result(), getWieMessage(res, 'admin_export_failed'));
                        }
                    })
                    .fail(function () {
                        $ta.val('');
                        showNetworkError(ioModal.$result());
                    });
            }

            function doImportSubmit() {
                var $modal = ioModal.$el();
                var importMode = $modal.find('input[name="zib-wie-import-mode"]:checked').val() || 'replace';
                var confirmMsg =
                    importMode === 'append'
                        ? _zib__('confirmImportAppend') || _zib__('confirmImport')
                        : _zib__('confirmImportReplace') || _zib__('confirmImport');
                if (!window.confirm(confirmMsg)) {
                    return;
                }
                var raw = $.trim($modal.find('.zib-wie-modal__textarea').val());
                if (!raw) {
                    showWieError(ioModal.$result(), _zib__('admin_paste_json_first'));
                    return;
                }
                var $sub = $modal.find('.zib-wie-modal__submit');
                withButtonLoading($sub, _zib__('loadingImport'), _zib__('submitImport'), function () {
                    return wiePost(_zib, 'zib_wie_import_sidebar', {
                        sidebar_id: state.sidebarId,
                        json: raw,
                        import_mode: importMode,
                    })
                        .done(function (res) {
                            if (res.success && res.data) {
                                $modal.find('.zib-wie-modal__textarea').val('');
                            }
                            renderImportResult(ioModal.$result(), res);
                        })
                        .fail(function () {
                            showNetworkError(ioModal.$result());
                        });
                });
            }

            function openTemplatePicker(sidebarId) {
                tplState.sidebarId = sidebarId;
                var $modal = tplModal.ensure();
                tplModal.setImportModeDefault('zib-wie-template-import-mode', 'append');
                var $list = $modal.find('.zib-wie-template-list').empty();
                var $st = $modal.find('.zib-wie-template-list-status').text(_zib__('templateLoadingList'));
                tplModal.open();
                wiePost(_zib, 'zib_wie_list_templates', {})
                    .done(function (res) {
                        $st.text('');
                        if (!res.success || !res.data || !res.data.templates || !res.data.templates.length) {
                            $list.empty();
                            $st.text(_zib__('templateEmpty'));
                            return;
                        }
                        res.data.templates.forEach(function (t) {
                            appendTemplateListItem($list, t);
                        });
                    })
                    .fail(function () {
                        $st.text(_zib__('admin_network_error'));
                    });
            }

            function doTemplateImportSubmit() {
                var $modal = tplModal.$el();
                var tid = $modal.find('input[name="zib-wie-template-id"]:checked').val();
                if (!tid) {
                    alert(_zib__('templatePick'));
                    return;
                }
                var importMode = $modal.find('input[name="zib-wie-template-import-mode"]:checked').val() || 'replace';
                var titleText =
                    $.trim(
                        $modal
                            .find('input[name="zib-wie-template-id"]:checked')
                            .closest('.zib-wie-template-item')
                            .find('.zib-wie-template-item-title')
                            .first()
                            .text()
                    ) || tid;
                var modeLabel = importMode === 'append' ? _zib__('importModeAppend') || '追加' : _zib__('importModeReplace') || '覆盖';
                var cmsg = (_zib__('confirmTemplate') || '确定导入？').replace('{title}', titleText).replace('{mode}', modeLabel);
                if (!window.confirm(cmsg)) {
                    return;
                }
                var $sub = $modal.find('.zib-wie-template-submit');
                withButtonLoading($sub, _zib__('loadingImport'), _zib__('templateSubmit'), function () {
                    return wiePost(_zib, 'zib_wie_import_template', {
                        sidebar_id: tplState.sidebarId,
                        template_id: tid,
                        import_mode: importMode,
                    })
                        .done(function (res) {
                            renderImportResult(tplModal.$result(), res, { autoReload: true });
                        })
                        .fail(function () {
                            showNetworkError(tplModal.$result());
                        });
                });
            }

            function injectToolbarAfter($anchor, sidebarId) {
                if (!$anchor || !$anchor.length || !sidebarId) {
                    return;
                }
                if ($anchor.next('.zib-wie-toolbar').length) {
                    return;
                }
                var $tb = $(
                    '<div class="zib-wie-toolbar"><button type="button" class="button button-small zib-wie-btn-export">' +
                        _zib__('admin_export_modules') +
                        '</button>' +
                        '<button type="button" class="button button-small zib-wie-btn-import">' +
                        _zib__('admin_import_modules') +
                        '</button>' +
                        '<button type="button" class="button button-small zib-wie-btn-template">' +
                        _zib__('admin_import_template') +
                        '</button></div>'
                );
                $tb.find('.zib-wie-btn-export').data('zibWieSid', sidebarId);
                $tb.find('.zib-wie-btn-import').data('zibWieSid', sidebarId);
                $tb.find('.zib-wie-btn-template').data('zibWieSid', sidebarId);
                $anchor.after($tb);
            }

            function scanWidgetsScreen() {
                $('.widgets-holder-wrap:not(#available-widgets):not(.inactive-sidebar):not(.is-wid-load)').each(function () {
                    var $wrap = $(this);
                    var $sort = $wrap.find('.widgets-sortables').first();
                    var sid = $sort.attr('id');
                    if (!sid) {
                        return;
                    }
                    var $desc = $sort.find('.sidebar-description .description').first();
                    var $anchor = $desc.length ? $desc : $sort.find('.description').first();
                    $wrap.addClass('is-wid-load');
                    injectToolbarAfter($anchor, sid);
                });
            }

            function scanCustomize() {
                $('.control-section-sidebar:not(.zib-is-load)').each(function () {
                    var $sec = $(this);
                    var accId = $sec.attr('id') || '';
                    var prefix = 'sub-accordion-section-sidebar-widgets-';
                    if (accId.indexOf(prefix) !== 0) {
                        return;
                    }
                    var sid = accId.slice(prefix.length);
                    if (!sid) {
                        return;
                    }
                    $sec.addClass('zib-is-load');
                    var $desc = $sec.find('.section-meta .description').first();
                    var $anchor = $desc.length ? $desc : $sec.find('.accordion-section-title, .customize-section-title').first();
                    injectToolbarAfter($anchor, sid);
                });

                $('.customize-control-widget_form:not(.zib-is-load)').each(function () {
                    var $form = $(this);
                    var $nav = $form.find('.widget-reorder-nav');
                    if ($nav.length) {
                        var $delete = $('<span class="zib-wie-btn_delete dashicons-trash" tabindex="0">' + _zib__('admin_remove_module') + '</span>');
                        $form.find('.widget-reorder-nav').append($delete);
                        $form.addClass('zib-is-load');
                    }
                });
            }

            _body.on('click', '.zib-wie-btn_delete', function () {
                var $this = $(this);
                var $form = $this.closest('.customize-control-widget_form');
                $form.find('.widget-control-remove').click();
            });

            _body.on('click', '.zib-wie-modal__reload', function () {
                window.location.reload();
            });

            _body.on('click', '.zib-wie-btn-export', function () {
                var sid = $(this).data('zibWieSid');
                if (sid) {
                    openModal('export', sid);
                }
            });

            _body.on('click', '.zib-wie-btn-import', function () {
                var sid = $(this).data('zibWieSid');
                if (sid) {
                    openModal('import', sid);
                }
            });

            _body.on('click', '.zib-wie-btn-template', function () {
                var sid = $(this).data('zibWieSid');
                if (sid) {
                    openTemplatePicker(sid);
                }
            });

            if (_body.hasClass('widgets-php')) {
                scanWidgetsScreen();
            }

            if (_body.hasClass('wp-customizer')) {
                if (typeof wp !== 'undefined' && wp.customize) {
                    wp.customize.bind('ready', function () {
                        setTimeout(scanCustomize, 300);
                    });
                }

                _body.on('click', '.control-panel-widgets .control-section-sidebar', function () {
                    setTimeout(scanCustomize, 100);
                });
            }
        })();
    });
})(jQuery, document);
