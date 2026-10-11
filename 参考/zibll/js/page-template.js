/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-06-09 16:35:09
 * @LastEditTime : 2026-03-14 15:30:52
 */

//文档导航页面
$('.document-search form').submit(function () {
    var _this = $(this);
    var _search = $('.document-search');

    var search = _this.find('[name="s"]').val();
    if (!search.length) {
        return notyf(zib__('search_enter_keyword'), 'warning'), !1;
    }
    if (search.length < 2) {
        return notyf(zib__('search_keyword_short'), 'warning'), !1;
    }

    _search.addClass('is-loading');
    var data = {
        search: search,
    };
    $('.cat-load').removeClass('is-loading active');
    return document_ajax(_search, data), !1;
});

_win.bd.on('click', '.cat-load', function () {
    $('.cat-load').removeClass('is-loading active');
    var _this = $(this);
    _this.addClass('is-loading active');
    var data = {
        cat_id: _this.attr('cat-id'),
    };
    return document_ajax(_this, data), !1;
});

_win.bd.on('click', '.document-search .search_keywords', function () {
    var _this = $(this);
    var text = _this.text();
    $('.document-search form [name="s"]').val(text).trigger('input');
    return $('.document-search form').submit(), !1;
});

//翻页
_win.bd.on('click', '.document-nav-container .pagenav a', function () {
    var _this = $(this);
    if (_this.hasClass('pag-jump')) {
        _this.addClass('ajax-next');
        return;
    }
    return document_ajax(_this, {}, _this.attr('href')), !1;
});

function document_ajax(_this, data, ajax_url) {
    var container = $('.document-nav-container').addClass('is-loading');
    data.one_cat = container.attr('one-cat');
    data.action = 'documentnav_posts';
    ajax_url = ajax_url || _win.ajax_url;
    $.ajax({
        type: 'POST',
        url: ajax_url,
        data: data,
        dataType: 'json',
        error: function (n) {
            var _msg = zib_sprintf('ajax_error', n.status, n.statusText);
            if (zib_is_fatal_error_response(n.responseText)) {
                _msg = zib__('ajax_fatal_error');
            }
            notyf(_msg, 'danger');
        },
        success: function (n) {
            if (n.error || !n.html) {
                notyf(zib__('ajax_retry_failed'), 'danger');
            }

            n.html && container.html(n.html);
            n.history_search && $('.history-search>div').html(n.history_search);
            setTimeout(function () {
                container.removeClass('is-loading');
            }, 20);
            setTimeout(function () {
                _this.removeClass('is-loading');
            }, 300);
        },
    });
}

//前台投稿
_win.bd.on('click', '.new-posts-submit', function () {
    var _this = $(this),
        type = _this.attr('action'),
        form = _this.parents('form'),
        data = form.serializeObject();
    data.action = type;
    data.post_content = tinyMCE.activeEditor.getContent();

    var featured = $('.featured-edit');
    if (featured.length) {
        var featured_data = featured.data('featured_data');
        data.featured_data = featured_data ? featured_data.data : false;
    }

    zib_ajax(_this, data, function (n) {
        n.url && $('.view-btn').html('<a target="_blank" href="' + n.url + '" class="but c-blue"><i class="fa fa-file-text-o"></i> ' + zib__('preview_post') + '</a>');
        n.time && $('.modified-time').html('<span class="badg">' + zib__('last_saved') + n.time + '</div>');
        n.ok && $('.form-control').val('');
        n.singin && $('.signin-loader:first').click();
        n.posts_id && form.find('input[name="posts_id"]').val(n.posts_id);
        window.post_is_save = true;
        if (n.open_url) {
            window.location.href = n.open_url;
            window.location.reload;
        }
    });
});

//链接列表页面模板
_win.bd.on('click', '.link-nav-box ul li a', function () {
    var _href = $($(this).attr('href'));
    scrollTopTo(_href);
    return false;
});

if ($('.link-nav-box').length) {
    _win.bd.scrollspy({
        target: '.link-nav-box',
        offset: 105,
    });

    if (_wid < 769) {
        _win.bd.on('activate.bs.scrollspy', '.link-nav-box .nav li', function () {
            $(this).closest('.nav').Tabcenter();
        });
    }
}
