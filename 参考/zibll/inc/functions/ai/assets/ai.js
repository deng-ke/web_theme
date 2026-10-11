/**
 * zibll AI
 */

(function ($) {
    'use strict';

    if (!window.zib_ai) {
        return;
    }

    const ZibAi = window.zib_ai;
    const $body = $('body');

    //写通用函数
    //发起请求函数
    $.fn.sendRequest = function (data, success) {
        var $this = $(this);
        if ($this.attr('is-loading')) {
            return;
        }

        $this.attr('is-loading', true);

        data._wpnonce = ZibAi.nonce;
        $.ajax({
            type: 'POST',
            url: ZibAi.ajax_url,
            data: data,
            success: function (res) {
                //处理错误
                if (res && res.error) {
                    $this.notify(res.error, 'error');
                    $this.removeAttr('is-loading');
                    return;
                }
                //处理成功
                success(res);
                $this.removeAttr('is-loading');
            },
            error: function (xhr, textStatus) {
                var msg = ZibAi.i18n.request_failed || '网络失败';
                if (textStatus === 'timeout') {
                    msg += '（请求超时，请稍后重试）';
                }
                $this.notify(msg, 'error');
                $this.removeAttr('is-loading');
            },
        });
    };

    //通知函数
    $.fn.notify = function (msg, type) {
        var _msg = '';
        //如果是对象，则循环添加到msg中
        if (typeof msg === 'object') {
            $.each(msg, function (key, value) {
                //不重复
                if (_msg.indexOf(value) === -1) {
                    _msg += value + '<br>';
                }
            });
        } else {
            _msg = msg;
        }

        _msg = $('<div class="ai-notify-msg ' + type + '"><div class="ai-notify-msg-content">' + _msg + '</div><a href="javascript:void(0)" class="ai-notify-msg-close"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path></svg></a></div>');
        _msg.find('.ai-notify-msg-close').on('click', function () {
            _msg.fadeOut(300, function () {
                _msg.remove();
            });
        });

        $(this).after(_msg);
        setTimeout(function () {
            _msg.fadeOut(300, function () {
                _msg.remove();
            });
        }, 200000);
    };

    //绑定按钮点击事件
    $body.on('click', '[ai-text-generate]', function (e) {
        e.preventDefault();
        var $this = $(this);
        var action = $this.attr('ai-action');
        var data = $this.attr('ai-data') || {};
        if (!action) {
            return;
        }
        $this.sendRequest(data, function (res) {
            console.log(res);
        });
    });

    //seo生成
    $body.on('click', '.ai-seo-generate', function (e) {
        e.preventDefault();
        var $this = $(this);
        var type = $this.attr('ai-type');
        var id = $this.attr('ai-id');
        if (!type) {
            return;
        }

        var data = {
            action: 'ai_seo_generate',
            type: type,
            id: id,
        };

        $this.sendRequest(data, function (res) {
            var errors = [];
            $.each(res, function (key, value) {
                if (value.content) {
                    var $postbox = type === 'post' ? $this.closest('.postbox') : $this.closest('form');
                    if ($postbox.length > 0) {
                        var $input = type === 'post' ? $postbox.find('[name="' + key + '"]') : $postbox.find('.term-seo-' + key);
                        if ($input.length > 0) {
                            $input.val(value.content);
                        }
                    }
                }

                //如果有错错
                if (value.error) {
                    errors.push(value.error);
                }
            });

            if (errors.length > 0) {
                $this.notify(errors, 'error');
            }

            console.log(res);
        });
    });
})(jQuery);
