/*
 * Project       : Nova主题
 * Description   : 编辑器短代码插入工具（TinyMCE 下拉菜单 + QuickTags 按钮）
 */
(function () {
    'use strict';

    if (typeof nova_shortcodes === 'undefined') return;
    var list = nova_shortcodes.list || {};

    function template(key) {
        var t = {
            nova_hide: '[nova_hide type="reply" desc=""]隐藏内容[/nova_hide]',
            nova_reply: '[nova_reply]回复可见内容[/nova_reply]',
            nova_postsbox: '[nova_postsbox post_id="1"]',
            nova_postslists: '[nova_postslists count="6" cat="" orderby="date" style="list"]',
            nova_download: '[nova_download href="https://example.com/file.zip" name="文件名称" price="" points=""]',
            nova_panel: '[nova_panel title="详情"]折叠内容[/nova_panel]',
            nova_tip: '[nova_tip type="info"]提示内容[/nova_tip]',
            nova_btn: '[nova_btn href="" color="primary"]按钮文字[/nova_btn]'
        };
        return t[key] || '[' + key + ']';
    }

    function insert(editor, key) {
        var code = template(key);
        if (editor && typeof editor.insertContent === 'function') {
            editor.insertContent(code);
        } else {
            QTags.insertContent(code);
        }
    }

    function buildMenuItems(editor) {
        var items = [];
        Object.keys(list).forEach(function (key) {
            items.push({
                text: list[key],
                onclick: function () { insert(editor, key); }
            });
        });
        return items;
    }

    // TinyMCE 工具栏下拉
    if (typeof tinymce !== 'undefined' && tinymce) {
        tinymce.PluginManager.add('nova_shortcodes', function (editor) {
            editor.ui.registry.addMenuButton('nova_shortcodes', {
                text: '短代码',
                icon: 'editor-code',
                fetch: function (callback) {
                    callback(buildMenuItems(editor));
                }
            });
            return {};
        });
    }

    // QuickTags（HTML 编辑器）按钮
    var qtReady = function () {
        if (typeof QTags === 'undefined' || !QTags) return;
        Object.keys(list).forEach(function (key) {
            QTags.addButton('nova_qt_' + key, list[key], function () {
                QTags.insertContent(template(key));
            }, '', '', list[key]);
        });
    };
    if (typeof QTags !== 'undefined') {
        qtReady();
    } else {
        document.addEventListener('DOMContentLoaded', qtReady);
    }
})();
