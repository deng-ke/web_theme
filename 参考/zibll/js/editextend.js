(function ($, window) {
    tbquire = window.tbquire || function () {};
    var zib = (window.zib = window.zib || {});
    var _win = (window._win = window._win || {});
    zib.ajax_url = zib.ajax_url || _win.ajax_url;
    zib.user_upload_nonce = zib.user_upload_nonce || _win.user_upload_nonce;
    zib.is_split_upload = zib.is_split_upload || _win.is_split_upload || 0;
    zib.split_minimum_size = zib.split_minimum_size || _win.split_minimum_size || 10;

    function mce__(key) {
        var map = window.mce && window.mce.i18n;
        if (map && Object.prototype.hasOwnProperty.call(map, key)) {
            return map[key];
        }
        return key;
    }

    function mce_sprintf(key) {
        var args = Array.prototype.slice.call(arguments, 1);
        var str = mce__(key);
        var autoIndex = 0;
        return str.replace(/%(\d+\$)?s/g, function (match, num) {
            var idx = num ? parseInt(num, 10) - 1 : autoIndex++;
            return args[idx] !== undefined && args[idx] !== null ? args[idx] : match;
        });
    }

    function parseEmbedSrc(value) {
        value = (value || '').trim();
        if (!value || value.indexOf('<') === -1) {
            return value;
        }
        var nodes = $.parseHTML(value, document, true);
        if (!nodes || !nodes.length) {
            return value;
        }
        var $nodes = $(nodes).filter('*');
        var src = $nodes.filter('[src]').first().attr('src');
        if (!src) {
            src = $nodes.find('[src]').first().attr('src');
        }
        return src || value;
    }

    //提前加载，提高效率，可删除
    if (_win.is_split_upload) {
        tbquire(['spark-md5']);
    }

    zib.media = function (args) {
        /*
         * @Author: Qinver
         * @Url: zibll.com
         * @Date: 2022-04-10 12:53:46
         * @LastEditTime: 2023-10-19 13:55:43
         * 子比主题的媒体管理工具，只能用于前台
         *
         * 依赖函数：
         * minitouch()：触摸滑动函数
         * spark-md5: 计算文件分片的md5
         * tbquire: 动态加载函数
         */

        this.file_data = {};
        this.upload_ing = {};
        this.new_upload = [];
        this.active_lists = [];
        this.input_lists = [];
        this.option = $.extend(
            {
                iframe: true, //嵌入
                search: true, //搜索
                is_upload: false, //允许上传
                is_input: true, //允许输入内容
                upload_multiple: false, //批量上传的限制选择的数量
                upload_size: 30,
                upload_ext: '',
                multiple: 0, //限制允许选择的数量，0为不限制
                text: {},
                is_split_upload: false, //允许大文件分片上传
                split_minimum_size: 20, //分片上传的大小最小值
                split_chunk_size: 2, //分片上传的切片大小
                is_breakpoint: true, //是否启用断点续传
            },
            args
        );

        this.type_names = {
            image: mce__('media_type_image'),
            video: mce__('media_type_video'),
            audio: mce__('media_type_audio'),
            file: mce__('media_type_file'),
        };

        this.type = this.option.type || 'image';
        this.type_name = this.type_names[this.type];
        this.refresh_lists = true;
        this.ajax_url = zib.ajax_url;
        this.upload_nonce = zib.user_upload_nonce;
        this.upload_action = 'user_upload';
        this.split_upload_action = 'user_split_upload';
        this.split_upload_merge_action = 'user_split_upload_merge';
        this.split_uploaded_chunk_action = 'user_split_uploaded_chunk';

        if (zib.is_split_upload !== undefined) {
            this.option.is_split_upload = zib.is_split_upload;
        }
        if (zib.split_minimum_size >= 4) {
            this.option.split_minimum_size = ~~zib.split_minimum_size;
        }

        var ing_key = 'upload_ing';
        this.init = function () {
            this.initDOM(); //创建DOM
        };

        this.getMy = function () {
            var option = this.option;
            if (!option.is_upload) return; //判断是否允许上传

            var upload_title = option.text.upload_title || mce_sprintf('media_upload_title_max', option.upload_size) + (option.upload_multiple > 1 ? mce_sprintf('media_upload_title_batch', option.upload_multiple) : '') + mce__('media_upload_title_drag');
            var search = option.search ? '<div class="relative"><input class="form-control search-input" type="text" placeholder="' + mce__('media_search') + '" name="search" class="form-control"><div class="abs-right muted-3-color"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-search"></use></svg></div></div>' : '';
            var upload_input = '<input class="upload-input" style="display: none;" ' + (option.upload_multiple != 1 ? ' multiple="multiple"' : '') + ' type="file" accept="' + get_type_accept(this.type) + '">';
            var upload_btn = '<div class="flex0 flex ac"><i data-toggle="tooltip" title="' + upload_title + '" class="muted-2-color mr10 fa fa-question-circle-o"></i><label style="margin: 0;">' + upload_input + '<botton class="nowave but c-blue upload-btn" type="button"><i aria-hidden="true" class="fa fa-cloud-upload"></i>' + mce__('media_upload') + '</botton></label></div>';
            var header = '<div class="my-header box-body"><div class="flex ac jsb">' + search + upload_btn + '</div></div>';

            return '<div class="mini-media-my-box">' + header + '<div class="type-' + this.type + ' notop box-body mini-media-my-lists mini-scrollbar scroll-y max-vh5"></div><div class="modal-buts but-average"><a style="display: none;" class="but c-blue my-submit" href="javascript:;">' + mce__('media_confirm') + '</a></div><div class="drop-mask">' + mce__('media_drag_upload') + '</div></div>';
        };

        this.getInput = function () {
            if (!this.option.is_input) return; //判断是否允许手动输入

            var is_multiple = this.option.multiple != 1; //允许多选
            var title = this.option.text.input_title || mce_sprintf('media_input_url_title', this.type_name, is_multiple ? mce__('media_input_url_batch') : mce__('media_input_url_colon'));
            return '<div class="box-body input-input-box"><p>' + title + '</p><textarea autoheight="true" maxheight="200" tabindex="1" class="form-control input-input" style="min-height:84px;" placeholder="https://..."></textarea></div><div class="modal-buts but-average"><a class="but c-blue input-submit" href="javascript:;">' + mce__('media_confirm') + '</a></div>';
        };

        this.getIframe = function () {
            return '<div class="box-body iframe-input-box"><p>' + mce__('media_iframe_prompt') + '</p><textarea autoheight="true" maxheight="200" tabindex="1" class="iframe-input form-control input-textarea" style="min-height:84px;" placeholder="&lt;iframe src=&quot;https://....&quot;&gt;&lt;/iframe&gt;"></textarea></div><div class="modal-buts but-average"><a class="but c-blue iframe-submit" href="javascript:;">' + mce__('media_iframe_confirm') + '</a></div>';
        };

        this.createContent = function () {
            //标题
            var title = this.option.text.title ? '<div class="font-bold modal-title">' + this.option.text.title + '</div>' : '';
            var subtitle = this.option.text.subtitle ? '<div class="em09 mt6 muted-2-color">' + this.option.text.subtitle + '</div>' : '';
            var title_html = subtitle || title ? '<div class="ml20 mt20" style="margin-right: 40px;">' + title + subtitle + '</div>' : '';

            var content_html = '';
            //主要内容区域
            var input_html = this.getInput();
            var get_my = this.getMy();
            var create_args = [];

            switch (this.type) {
                case 'image':
                    if (get_my) {
                        create_args.push({
                            btn: mce__('media_my_image'),
                            con: get_my,
                        });
                    }
                    if (input_html) {
                        create_args.push({
                            btn: mce__('media_external_image'),
                            con: input_html,
                        });
                    }

                    break;

                case 'video':
                    if (get_my) {
                        create_args.push({
                            btn: mce__('media_my_video'),
                            con: get_my,
                        });
                    }
                    if (input_html) {
                        create_args.push({
                            btn: mce__('media_external_video'),
                            con: input_html,
                        });
                    }
                    if (this.option.iframe) {
                        create_args.push({
                            btn: mce__('media_embed_video'),
                            con: this.getIframe(),
                        });
                    }

                    break;

                case 'file':
                    if (get_my) {
                        create_args.push({
                            btn: mce__('media_my_file'),
                            con: get_my,
                        });
                    }
                    if (input_html) {
                        create_args.push({
                            btn: mce__('media_enter_url'),
                            con: input_html,
                        });
                    }

                    break;
            }

            content_html = this.createTab(create_args);

            return title_html + content_html;
        };

        this.initDOM = function () {
            if (this.$el) return;

            var that = this;
            that.this_id = that.getRandom();
            var id = 'mini-media-modal-' + that.this_id; //设置唯一ID
            var content_html = that.createContent();
            var is_phone = $(window).width() < 769;

            var modal_html =
                '<div class="modal flex jc fade mini-media-modal' +
                (is_phone ? ' bottom' : '') +
                '" id="' +
                id +
                '" tabindex="-1" role="dialog" aria-hidden="false" style="display: none;">\
            <div class="modal-mini full-sm modal-dialog" role="document">\
            <div class="modal-content"><button data-dismiss="modal" class="mr10 mt10 close"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="mini-media-content">' +
                content_html +
                '</div></div>' +
                (is_phone ? '<div class="touch-close"></div>' : '') +
                '\
            </div>\
            </div>';

            $('body').append(modal_html);
            that.$el = $('#' + id);
            that.$content = $('#' + id + ' .mini-media-content');
            that.$mylists = $('#' + id + ' .mini-media-my-lists');

            if (is_phone) {
                that.$el.on('show.bs.modal', function () {
                    var $this = $(this);
                    $this.minitouch({
                        direction: 'bottom',
                        selector: '.modal-dialog',
                        start_selector: '.touch-close',
                        onEnd: function () {
                            $this.modal('hide');
                        },
                    });
                });
            }

            //执行绑定

            //自己关闭时候，如果还有文件在继续上传，则给予提示
            that.$el.on('hidden.bs.modal', function () {
                that.$mylists.find('.list-item.upload-ing').length && that.notify(mce__('media_upload_continue'), 'warning');
            });

            //加载下一页
            that.$content.on('click', 'a[next-paged]', function (e) {
                e.preventDefault();
                var _this = $(this);
                var next = ~~_this.attr('next-paged');
                _this.prop('outerHTML', '<a><i class="loading mr10"></i>' + mce__('media_loading') + '</a>');
                that.loadLists(next);
                return false;
            });

            //上传文件
            that.$content.on('change', '.upload-input', function (e) {
                e.preventDefault();
                var files = this.files || e.dataTransfer.files;
                if (!files || !files.length) return false;
                that.uploadChange($(this), files);
            });

            //拖拽文件上传
            var dragover_class = 'drag-over';
            var my_box_class = '.mini-media-my-box';
            var dragover_timer, dragover_ractive;

            that.$content.on('dragover dragenter', my_box_class, function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (dragover_timer) {
                    clearTimeout(dragover_timer);
                }
                if (dragover_ractive) {
                    return;
                }
                $(this).addClass(dragover_class);
            });

            that.$content.on('dragleave', my_box_class, function (e) {
                e.preventDefault();
                e.stopPropagation();
                var _this = $(this);
                dragover_timer = setTimeout(function () {
                    dragover_ractive = false;
                    _this.removeClass(dragover_class);
                }, 10);
            });

            //拖拽上传
            that.$content.on('drop', my_box_class, function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass(dragover_class);
                var files = e.originalEvent.dataTransfer.files;
                if (!files || !files.length) return false;
                that.uploadChange($(this), files);
            });

            //搜索内容
            that.$content.on(
                'input',
                '.search-input',
                that.debounce(function (e) {
                    e.preventDefault();
                    that.searchChange($(this));
                }, 600)
            );

            //搜索内容-清空
            that.$content.on('click', '.search-close', function () {
                if ($(this).attr('disabled')) {
                    return false;
                }
                var _input = that.$content.find('.search-input');
                _input.val('');
                that.searchChange(_input);
            });

            //选择列表
            that.$mylists.on('click', '.list-item:not(.upload-ing)', function () {
                that.activeLists($(this));
            });

            //列表选择确认
            that.$content.on('click', '.my-submit', function () {
                var SelectData = that.getListData(that.active_lists);
                that.$el.trigger('select_submit').trigger('lists_submit', {
                    data: SelectData,
                    ids: that.active_lists,
                });
                that.close();
            });

            //手动输入地址确认
            that.$content.on('click', '.input-submit', function () {
                var _input = that.$content.find('.input-input');
                var val = that.enCode(_input.val());
                if (!val) {
                    return that.notify(mce_sprintf('media_enter_type_url', that.type_name), 'warning'); //警告
                }

                var multiple = that.option.multiple;
                val = val.split(/[(\r\n)\r\n]+/);
                val.forEach((item, index) => {
                    if (!item) {
                        val.splice(index, 1); //删除空项
                    }
                });

                if (multiple && val.length > multiple) {
                    that.notify(mce_sprintf('media_max_urls', multiple, that.type_name), 'warning');
                }

                that.setInputVals(val);
                that.$el.trigger('select_submit').trigger('input_submit', {
                    vals: val,
                });
                that.close();
            });

            //iframe-submit
            that.$content.on('click', '.iframe-submit', function () {
                var _iframe = that.$content.find('.iframe-input');
                var src = _iframe.val();

                var parsed = parseEmbedSrc(src);
                if (src.indexOf('<') !== -1 && parsed === src) {
                    src = that.enCode(src);
                } else {
                    src = parsed;
                }

                that.setIframeVal(src);
                that.$el.trigger('select_submit').trigger('iframe_submit', that.iframe_data);
                that.close();
            });
        };

        //重置iframe的内容
        this.resetIframeVal = function () {
            this.setIframeVal();
        };

        //设置iframe的内容
        this.setIframeVal = function (src, ratio) {
            this.iframe_data = {
                src: src || '',
                ratio: ratio || 56,
            };
            this.$content.find('.iframe-input').val(this.iframe_data.src || '');
        };

        //重置手动输入框的内容
        this.resetInputVals = function () {
            this.setInputVals([]);
        };

        //设置手动输入框的内容
        this.setInputVals = function (vals) {
            var multiple = this.option.multiple;
            if (multiple > 0) vals.splice(multiple, vals.length + 1);
            this.input_lists = vals;
            this.$content.find('.input-input').val(vals.join('\n'));
        };

        //重置已选择的列表数据
        this.resetActiveLists = function () {
            this.setActiveLists([]);
        };

        //设置已选择的列表数据
        this.setActiveLists = function (ids) {
            //保存数据
            this.active_lists = ids;
            var active_calss = 'active';
            var _active_index_calss = 'active-index';
            var multiple = this.option.multiple;
            var is_only_one = multiple == 1;

            //执行循环显示
            this.$mylists
                .find('.list-item.' + active_calss)
                .removeClass(active_calss)
                .find('.' + _active_index_calss)
                .remove();
            for (let key in ids) {
                this.$mylists
                    .find('.list-item[data-file-id="' + ids[key] + '"]')
                    .addClass(active_calss)
                    .append('<div class="' + _active_index_calss + '">' + (is_only_one ? '<i class="fa fa-check em09"></i>' : ~~key + 1) + '</div>');
            }

            //切换是否还允许继续选择
            var _other = this.$mylists.find('.list-item:not(.' + active_calss + ')');
            if (multiple > 1 && ids.length >= multiple) {
                _other.attr('disabled', true);
            } else {
                _other.attr('disabled', false);
            }

            //切换显示插入按钮
            var _my_submit = this.$content.find('.my-submit');
            if (ids.length && !is_only_one) {
                _my_submit.show();
            } else {
                _my_submit.hide();
            }

            if (is_only_one && ids.length) {
                _my_submit.click();
            }
        };

        this.activeLists = function (_this) {
            var file_id = ~~_this.attr('data-file-id');
            if (!file_id) return;
            if (_this.attr('disabled')) {
                return;
            }

            var ids = this.option.multiple == 1 ? [] : this.active_lists; //单选模式判断
            var _active_index_calss = 'active-index';
            var _active_index = _this.find('.' + _active_index_calss);

            if (!_active_index.length) {
                //添加
                ids.push(file_id);
                //多选限制
            } else {
                //移除
                var active_index = ~~_active_index.html() - 1;
                ids.splice(active_index, 1);
            }

            this.setActiveLists(ids);
        };

        //根据文件ID获取文件数据
        this.getListData = function (ids) {
            if (!Array.isArray(ids)) ids = [ids];
            var data = [];
            for (let key in ids) {
                var file_id = ids[key];
                this.file_data[file_id] && data.push(this.file_data[file_id]);
            }
            return data;
        };

        this.getSelectUrl = function () {
            this.active_lists;
        };

        //触发搜索
        this.searchChange = function (_this) {
            var val = $.trim(_this.val());
            var _icon_box = _this.siblings('.abs-right');

            //提醒box
            var remind_class = 'limit-warning';
            var remind = '<div class="' + remind_class + ' top">' + mce__('media_min_chars') + '</div>';

            var search_icon = '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-search"></use></svg>';
            var loading_icon = '<i class="loading em12"></i>';

            //移除提醒
            _this.siblings('.' + remind_class).remove();
            //恢复图标
            _icon_box.html(search_icon);

            //恢复到正常状态
            if (!val) {
                //移除已经上传的数据
                this.new_upload = [];
            } else if (val.length < 2) {
                //提醒字数不足
                _this.after(remind);
                return;
            }

            //显示动画图标
            val && _icon_box.html(loading_icon);

            this.search_val = val;
            this.loadLists();
        };

        function get_type_accept(type) {
            var accept_names = {
                image: 'image/gif,image/jpeg,image/jpg,image/png,image/bmp',
                video: 'video/*',
                audio: 'audio/*',
                file: '*',
            };
            return accept_names[type];
        }

        //创建tab
        this.createTab = function (args, _k) {
            var btn = '';
            var con = '';
            _k = _k || 'main';

            var i = 0;
            for (let key in args) {
                i++;
                let id_k = this.this_id + '-tab-' + _k + '-' + key;
                btn += '<li' + (key == 0 ? ' class="active"' : '') + '><a href="#' + id_k + '" data-toggle="tab">' + args[key].btn + '</a></li>';
                con += '<div class="tab-pane fade' + (key == 0 ? ' in active' : '') + '" id="' + id_k + '">' + args[key].con + '</div>';
            }

            return (i > 1 ? '<ul class="mt20 text-center list-inline tab-nav-theme">' + btn + '</ul>' : this.option.text.subtitle || this.option.text.title ? '' : '<div style="margin: 35px;"></div>') + '<div class="tab-content">' + con + '</div>';
        };

        //ajax请求
        this.ajax = function (data, fun, error_fun) {
            var that = this;
            $.ajax({
                type: 'POST',
                url: this.ajax_url,
                data: data,
                dataType: 'json',
                error: function (n) {
                    ajaxErrorNotice(n, that);
                    typeof error_fun === 'function' && error_fun(n);
                },
                success: function (n) {
                    ajaxSuccessNotice(n, that);
                    fun(n);
                },
            });
        };

        function ajaxSuccessNotice(n, that) {
            if (n.msg) {
                var ys = n.ys ? n.ys : n.error ? 'danger' : '';
                that.notify(n.msg, ys);
            }
        }

        function ajaxErrorNotice(n, that) {
            var _msg = mce_sprintf('media_ajax_error', n.status, n.statusText);
            if (n.responseText && n.responseText.indexOf('致命错误') > -1) {
                _msg = mce__('media_fatal_error');
            }
            console.error('ajax请求错误，错误信息如下：', n);
            that.notify(_msg, 'danger');
        }

        //获取文件后缀
        this.getFileExt = function (name) {
            return name.substring(name.lastIndexOf('.') + 1);
        };

        this.createImageList = function (data) {
            return '<img src="' + (data.thumbnail_url || data.medium_url || '') + '" data-full-url="' + (data.url || '') + '" alt="' + (data.title || this.type) + '">';
        };

        this.createVideoList = function (data) {
            var name = data.filename || data.name;
            var desc_1 = data.fileLength ? '<i class="fa fa-play-circle-o mr3"></i>' + data.fileLength : '';
            var desc_2 = data.filesizeInBytes ? '<i class=""></i>' + this.formatSize(data.filesizeInBytes) : '';
            desc_2 = desc_2 || '<i class=""></i>' + this.formatSize(data.size);

            return '<div class="px12 flex1 flex xx padding-6 padding-h6"><div class="px12 text-ellipsis-2"><i class="fa fa-file-video-o mr6"></i>' + name + '</div><div class="flex1 flex ab jsb muted-2-color"><span class="">' + desc_1 + '</span><span class="">' + desc_2 + '</span></div></div>';
        };

        //构建列表html
        this.createList = function (data, add_class) {
            var list_html;

            /**
             * lastModified: 1645513972407
                name: "超能陆战队-中国预告片2-1.mp4"
                size: 11357061
                type: "video/mp4"
             */

            switch (this.type) {
                case 'image':
                    list_html = this.createImageList(data);
                    break;

                case 'video':
                    list_html = this.createVideoList(data);
                    break;

                default:
                    var name = data.filename || data.name;
                    var desc_1 = this.getFileExt(name);
                    var desc_2 = data.filesizeInBytes ? this.formatSize(data.filesizeInBytes) : '';

                    var icon_html = '<div class="list-item-icon-box flex jc" ><i class="fa fa-file-text-o em12"></i></div>';

                    switch (data.type) {
                        case 'video':
                            icon_html = '<div class="list-item-icon-box flex jc" ><i class="fa fa-file-video-o em12"></i></div>';
                            break;

                        case 'image':
                            icon_html = '<div style="padding-bottom: 100%!important;border-radius: 4px;" class="graphic "><img class="fit-cover" src="' + (data.thumbnail_url || data.medium_url || data.url) + '" alt="' + (data.title || 'image') + '"></div>';
                            break;
                    }

                    list_html = '<div class="px12 flex1 flex xx padding-6 padding-h6"><div class="flex ac"><div class="mr6 flex0"><div style="width: 33px;">' + icon_html + '</div></div><div class="text-ellipsis-2">' + name + '</div></div><div class="flex1 flex ab jsb muted-2-color"><span class="">' + desc_1 + '</span><span class="">' + desc_2 + '</span></div></div>';
            }

            return '<div class="' + (add_class ? add_class + ' ' : '') + 'list-item" data-file-id="' + (data.id || 0) + '" data-file-type="' + this.type + '"><div class="list-box">' + list_html + '</div></div>';
        };

        //构建列表html
        this.createLists = function (lists_data) {
            var lists_html = '';
            var that = this;

            if (lists_data) {
                $.each(lists_data, function (i, data) {
                    lists_html += that.createList(data);

                    //记录文件数据
                    that.file_data[data.id] = data;
                });
            }

            return lists_html;
        };

        //加载列表
        this.loadLists = function (paged, ajax_data) {
            var that = this;
            if (!that.$mylists || !that.$mylists.length) return;
            var loading_class = 'loading-box';

            paged = paged || 1;
            var _lists_box = that.$mylists;
            var loading = '<div style="height: 140px;width: 100%;" class="flex jc loading-box"><div class="em14 opacity5"><i class="loading"></i></div></div>';

            ajax_data = ajax_data || {};
            ajax_data.action = 'current_user_attachments';
            ajax_data.type = that.type;
            ajax_data.paged = paged;
            ajax_data.orderby = 'ID';
            if (that.search_val) {
                ajax_data.search = that.search_val;
            }

            if (paged == 1) {
                //如果是第一页则加载动画
                _lists_box.html(loading);
            } else {
                ajax_data.exclude = that.new_upload; //排除
            }

            that.ajax(ajax_data, function (n) {
                var null_class = 'null-box';
                var pagination_class = 'theme-pagination';

                var lists_html = '';
                if (n.lists) {
                    lists_html = that.createLists(n.lists);
                }

                if (lists_html && n.all_pages > paged) {
                    //加载下一页
                    lists_html += '<div class="text-center ' + pagination_class + '" all-count="' + n.all_count + '" all-pages="' + n.all_pages + '"><div class="ajax-next"><a href="" next-paged="' + (paged + 1) + '"><i class="fa fa-angle-right"></i>' + mce__('media_load_more') + '</a></div></div>';
                }

                //添加元素
                var _last = _lists_box.find('.list-item').last();
                _lists_box.find('.' + pagination_class + ',.' + null_class + ',.' + loading_class).remove(); //删除下一页

                if (_last.length) {
                    _last.after(lists_html);
                } else {
                    lists_html = lists_html || '<div class="flex1 opacity5 flex jc ' + null_class + '" style="height: 140px;">' + mce__('media_no_content') + '</div>';
                    _lists_box.html(lists_html);
                }

                if (ajax_data.search) {
                    var close_icon = '<a class="search-close" href="#"><svg aria-hidden="true"><use xlink:href="#icon-close"></use></svg></a>'; //关闭按钮

                    that.$content.find('.search-input+.abs-right').html(close_icon);
                }

                that.refresh_lists = false; //重置状态
            });
        };

        //上传前先插入预览
        this.uploadBeforeDOM = function (_list) {
            var _lists_box = this.$mylists;
            var _first = _lists_box.find('.list-item').first();
            _lists_box.find('.null-box').remove();

            if (_first.length) {
                _first.before(_list);
            } else {
                _lists_box.prepend(_list);
            }
        };

        //计算文件分片的md5
        this.getFileMd5 = function (chunk, callback) {
            tbquire(
                ['spark-md5'],
                function () {
                    var fileReader = new FileReader();

                    fileReader.onload = function (e) {
                        var chunkData = e.target.result;
                        var spark = new SparkMD5.ArrayBuffer();
                        spark.append(chunkData);
                        var md5 = spark.end();
                        callback(md5);
                    };

                    fileReader.readAsArrayBuffer(chunk);
                },
                function () {
                    callback('');
                }
            );
        };

        //执行上传-接受到单个文件
        this.uploadFile = function (file, _input, _preview_list, _SuccessFun, _ErrorFun) {
            var that = this;
            var upload_id = that.getRandom();
            var file_size = file.size;
            //准备分片上传数据
            var split_chunk_size = that.option.split_chunk_size * 1024 * 1024;
            var split_chunks_count = Math.ceil(file_size / split_chunk_size);
            var split_current_chunk = 0;
            var split_start = 0;
            var split_end = split_chunk_size;
            var is_split_upload = file.slice && that.option.is_split_upload && file_size > that.option.split_minimum_size * 1048567 && split_chunks_count > 1; //是否分片上传

            //准备上传DOM元素
            var _progress_text = _preview_list.find('.progress-text');
            var _progress = _preview_list.find('.progress');
            var _progress_bar = _preview_list.find('.progress .progress-bar');
            var _search_input = that.$content.find('.search-input');
            var _search_close = that.$content.find('.search-close');

            //上传完成后执行函数
            function uploadFileToOver() {
                delete that.upload_ing[upload_id];
                _progress.remove();
                if (Object.keys(that.upload_ing).length == 0) {
                    _input.data(ing_key, false).attr('disabled', false);
                    _search_input.attr('disabled', false);
                    _search_close.attr('disabled', false);
                }
            }

            //上传失败函数
            function uploadFileToError() {
                uploadFileToOver();
                setPercent('error');
                _preview_list.css({
                    'box-shadow': '0 0 2px 2px #ff372c',
                });

                _ErrorFun && _ErrorFun();

                setTimeout(function () {
                    _preview_list.remove();
                }, 2000);
            }

            //上传成功
            function uploadFileToSuccess(n) {
                if (!n.id) return false;
                //设置进度条
                setPercent('success');

                //记录文件数据
                that.file_data[n.id] = n;
                that.new_upload.push(n.id);

                _SuccessFun && _SuccessFun(n);

                setTimeout(function () {
                    _preview_list.prop('outerHTML', that.createList(n));
                    uploadFileToOver();
                    that.$el.trigger('upload', n);
                }, 400);
            }

            //判断分片的MD5是否已经上传过
            var breakpoint_uploaded_chunks = {};
            function getUploadedChunkMd5(chunk_i) {
                return breakpoint_uploaded_chunks['chunk_' + chunk_i];
            }

            //添加单个breakpoint_uploaded_chunks
            function addMd5Uploaded(current_chunk, md5) {
                breakpoint_uploaded_chunks['chunk_' + current_chunk] = md5;
            }

            //重置breakpoint_uploaded_chunks
            function resetMd5Uploaded(data) {
                if (!$.isPlainObject(data)) {
                    data = {};
                }
                breakpoint_uploaded_chunks = data;
            }

            //设置进度条
            var upload_percent = 0;
            var split_uploaded_size = 0;
            function setPercent(loaded, total) {
                total = total || file_size;
                loaded = loaded || 0;

                if (loaded == 'success') {
                    //全部文件上传结束
                    _progress_text.html(mce__('media_uploaded')).css({
                        background: 'rgb(92, 184, 92)',
                    });
                    return _progress_bar.css('width', '100%');
                }

                if (loaded == 'error') {
                    _progress_text.html(mce__('media_upload_failed')).css({
                        background: 'rgba(255, 55, 44, 0.8)',
                        bottom: 0,
                    });
                    return _progress_bar.css('width', '100%');
                }

                var percent = (loaded / total) * 100;
                if (is_split_upload) {
                    total = file_size;
                    percent = ((split_uploaded_size + loaded) / total) * 100;
                }

                //防止并发上传时，进度条回退
                if (percent > upload_percent) {
                    upload_percent = percent;

                    if (total > 10 * 1024 * 1024) {
                        percent = percent.toFixed(1);
                    } else {
                        percent = percent.toFixed(0);
                    }
                    _progress_text.find('text').html(percent >= 100 ? mce__('media_processing') : percent + '%');
                    _progress_bar.css('width', percent + '%');
                }
            }

            //分片上传-继续上传
            var is_split_upload_continue = false;
            function splitUploadContinue(concurrent_i) {
                split_current_chunk++;
                split_start = split_end;
                split_end = split_start + split_chunk_size;

                if (!is_split_upload_continue && Object.keys(breakpoint_uploaded_chunks).length === split_chunks_count) {
                    split_uploaded_size = file_size;
                    setPercent();
                    return splitUploadMerge();
                }

                if (split_current_chunk < split_chunks_count) {
                    onUpload(concurrent_i);
                    return true;
                }
                return false;
            }

            //生成ajax FormData
            function GetFormData(callback) {
                var formData = new FormData();
                formData.append('_wpnonce', that.upload_nonce);
                formData.append('file_type', that.type);
                formData.append('file_size', file_size);
                formData.append('file_name', file.name);

                if (is_split_upload) {
                    var file_chunk = file.slice(split_start, split_end);
                    formData.append('action', that.split_upload_action);
                    formData.append('split_chunks_count', split_chunks_count);
                    formData.append('split_current_chunk', split_current_chunk);
                    formData.append('split_chunk_size', that.option.split_chunk_size);
                    formData.append('file', file_chunk);

                    // 断点续传
                    if (that.option.is_breakpoint && getUploadedChunkMd5(split_current_chunk)) {
                        that.getFileMd5(file_chunk, function (md5) {
                            formData.append('chunk_md5', md5);
                            callback(formData);
                        });
                    } else {
                        callback(formData);
                    }
                } else {
                    formData.append('file', file, file.name);
                    formData.append('action', that.upload_action);
                    callback(formData);
                }
            }

            //查询已上传的分片
            function splitUploadedChunk() {
                var ajax_data = {
                    action: that.split_uploaded_chunk_action,
                    file_name: file.name,
                    split_chunks_count: split_chunks_count,
                    _wpnonce: that.upload_nonce,
                };
                that.ajax(
                    ajax_data,
                    function (n) {
                        resetMd5Uploaded(n.uploaded_chunks);
                        //开始上传
                        onUpload();
                    },
                    uploadFileToError
                );
            }

            //通知后台合并分片
            function splitUploadMerge() {
                var ajax_data = {
                    action: that.split_upload_merge_action,
                    file_type: that.type,
                    file_size: file_size,
                    file_name: file.name,
                    split_chunks_count: split_chunks_count,
                    uploaded_chunks: JSON.stringify(breakpoint_uploaded_chunks),
                    _wpnonce: that.upload_nonce,
                };

                //设置为已请求合并
                is_split_upload_continue = true;

                that.ajax(
                    ajax_data,
                    function (n) {
                        ajaxSuccessNotice(n, that);

                        if (n.error) {
                            return uploadFileToError();
                        }

                        //如果后台返回分片验证失败，则重新从头上传
                        if (n.chunk_loss) {
                            //重置进度条
                            upload_percent = 0;
                            split_uploaded_size = n.progress;
                            setPercent();

                            //重置已上传的分片MD5数据
                            resetMd5Uploaded(n.uploaded_chunks);

                            //重置分片上传数据
                            split_current_chunk = 0;
                            split_start = 0;
                            split_end = split_chunk_size;

                            //重置并发计数
                            concurrent_index = 1;

                            //重置是否合并请求过
                            is_split_upload_continue = false;

                            //重新开始上传
                            return onUpload();
                        }

                        if (n.id) {
                            return uploadFileToSuccess(n);
                        }
                        return uploadFileToError();
                    },
                    uploadFileToError
                );
            }

            //并发上传
            var concurrent_max = 4;
            var concurrent_index = 1;

            function onUpload(concurrent_i) {
                //准备上传数据formData
                GetFormData(function (formData) {
                    //断点续传
                    if (is_split_upload && that.option.is_breakpoint) {
                        var split_current_chunk_md5 = getUploadedChunkMd5(formData.get('split_current_chunk'));
                        if (split_current_chunk_md5 && split_current_chunk_md5 === formData.get('chunk_md5')) {
                            //如果已经上传过，则继续上传其它分片
                            split_uploaded_size += split_chunk_size;
                            splitUploadContinue();
                            return setPercent();
                        }
                    }

                    //分片上传使用并发上传
                    if (is_split_upload && concurrent_index < concurrent_max) {
                        setTimeout(function () {
                            splitUploadContinue(concurrent_index);
                        }, concurrent_index * 200);
                        concurrent_index++;
                    }

                    var xhr_end = 0;
                    $.ajax({
                        type: 'POST',
                        url: that.ajax_url,
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        error: function (n) {
                            ajaxErrorNotice(n, that);
                            return uploadFileToError();
                        },
                        xhr: jqXhr(function (e) {
                            split_uploaded_size += e.loaded - xhr_end;
                            xhr_end = e.loaded;
                            setPercent(is_split_upload ? 0 : e.loaded, e.total);
                        }),
                        success: function (n) {
                            ajaxSuccessNotice(n, that);

                            if (n.error) {
                                return uploadFileToError();
                            }

                            if (n.id) {
                                uploadFileToSuccess(n);
                            } else {
                                if (is_split_upload) {
                                    //分片上传继续上传其它的分片
                                    if (n.file_md5) {
                                        addMd5Uploaded(n.current_chunk, n.file_md5);
                                    }

                                    //继续上传其它分片
                                    if (n.uploaded_size) {
                                        split_uploaded_size = n.uploaded_size;
                                    } else {
                                        split_uploaded_size += split_chunk_size;
                                    }

                                    setPercent(); //设置进度
                                    splitUploadContinue(concurrent_i);
                                } else {
                                    return uploadFileToError();
                                }
                            }
                            return false;
                        },
                    });
                });
            }

            function onInit() {
                //判断是否是分片上传，且需要断点续传，分片数量大于2
                if (is_split_upload && that.option.is_breakpoint && split_chunks_count > 2) {
                    splitUploadedChunk();
                } else {
                    onUpload();
                }
            }

            //开始执行上传
            //禁止其他操作
            _input.data(ing_key, true).attr('disabled', true);
            _search_input.attr('disabled', true);
            _search_close.attr('disabled', true);

            //显示上传动画
            _progress_text.html('<i class="loading mr6"></i><text>' + mce__('media_preparing') + '</text>');
            that.upload_ing[upload_id] = true;

            //队列上传
            onInit();
        };

        //上传前预览
        this.uploadChange = function (_this, files) {
            var that = this;
            var multiple = that.option.upload_multiple; //允许多选的数量，0为不限制
            var is_multiple = multiple != 1; //允许多选
            var upload_size = that.option.upload_size;

            //没有文件退出
            if (!files[0] || _this.data(ing_key)) return false;

            var progress = '<div class="progress progress-striped active progress-abs-bottom"><div class="progress-bar progress-bar-success"></div></div><div class="progress-text abs-center conter-bottom">' + mce__('media_preparing_dots') + '</div>';

            //进入循环，批量上传
            var m_i = 1;
            var ready_files = []; //准备上传的文件
            for (var i = 0; i < files.length; i++) {
                //禁止多选
                if (!is_multiple && m_i > 1) {
                    break;
                }

                //超过单次最大数
                if (is_multiple && multiple && m_i > multiple) {
                    that.notify(mce_sprintf('media_max_batch', multiple), 'warning');
                    break;
                }

                var file = files[i];

                //文件类型判断
                if (that.type !== 'file' && -1 == file.type.indexOf(that.type)) {
                    //如果限制文件格式
                    that.notify(mce__('media_format_denied'), 'danger');
                    continue;
                } else {
                    //格式判断
                    var upload_ext = that.option.upload_ext;
                    if (upload_ext) {
                        upload_ext = '|' + upload_ext + '|';
                        var file_ext = '|' + that.getFileExt(file.name) + '|';
                        if (-1 == upload_ext.indexOf(file_ext)) {
                            //如果限制文件格式
                            that.notify(mce__('media_format_denied'), 'danger');
                            continue;
                        }
                    }
                }

                //文件大小判断
                if (file.size > upload_size * 1048567) {
                    that.notify(mce_sprintf('media_file_size_limit', file.name, upload_size), 'warning');
                    continue;
                }

                //判断结束
                //进度条
                var _preview_list = $(that.createList(file, 'upload-ing'));
                _preview_list.append(progress);

                //图片预览
                that.type === 'image' && that.setImgPreview(file, _preview_list);

                //插入预览及进度DOM
                that.uploadBeforeDOM(_preview_list);

                ready_files.push({ file: file, preview: _preview_list });
                m_i++;
            }

            //顺序上传文件
            function sequentialUpload() {
                var readyed = ready_files.shift();
                readyed && that.uploadFile(readyed['file'], _this, readyed['preview'], sequentialUpload, sequentialUpload);
            }

            //并发上传
            var b_max = 4; //并发数
            for (var b_i = 0; b_i < b_max; b_i++) {
                setTimeout(function () {
                    sequentialUpload();
                }, b_i * 200);
            }
        };

        this.getRandom = function () {
            return parseInt((Math.random() + 1) * Math.pow(10, 4));
        };

        //渲染图片元素
        this.setImgPreview = function (file, box) {
            var r = new FileReader();
            r.readAsDataURL(file),
                (r.onload = function (e) {
                    box.find('img').attr({
                        alt: file.name,
                        src: e.target.result,
                    });
                });
        };

        this.enCode = function (value) {
            return $('<div />').html($.trim(value)).text();
        };

        this.formatSize = function (size, pointLength, units) {
            var unit;
            units = units || ['B', 'K', 'M', 'G', 'TB'];
            while ((unit = units.shift()) && size > 1024) {
                size = size / 1024;
            }
            return (unit === 'B' ? size : size.toFixed(pointLength === undefined ? 1 : pointLength)) + unit;
        };

        //绑定上传进度
        function jqXhr(fun) {
            jqXhr.onprogress = fun;
            //使用闭包实现监听绑
            return function () {
                //通过$.ajaxSettings.xhr();获得XMLHttpRequest对象
                var xhr = $.ajaxSettings.xhr();
                //如果有监听函数并且xhr对象支持绑定时就把监听函数绑定上去
                if (xhr.upload) {
                    xhr.upload.onprogress = jqXhr.onprogress;
                }
                return xhr;
            };
        }

        this.debounce = function (callback, delay, immediate) {
            var timeout;
            return function () {
                var context = this,
                    args = arguments;
                var later = function () {
                    timeout = null;
                    if (!immediate) {
                        callback.apply(context, args);
                    }
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, delay);
                if (callNow) {
                    callback.apply(context, args);
                }
            };
        };

        //发送通知消息
        this.notify = function (str, ys, time, id) {
            //定义全局变量
            var _this_id = str + ys + id;
            if (window.notice_ing && window.notice_ing[_this_id]) return;
            window.notice_ing = window.notice_ing || {};
            window.notice_ing[_this_id] = true;
            $('.notyn').length || _win.bd.append('<div class="notyn"></div>');
            ys = ys || 'success';
            time = time || 5000;
            time = time < 100 ? time * 1000 : time;
            var id_attr = id ? ' id="' + id + '"' : '';
            var _html = $('<div class="noty1"' + id_attr + '><div class="notyf ' + ys + '">' + str + '</div></div>');
            _html.data('notice_this_id', _this_id);
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
        };

        function notyf_close(_e) {
            _e.addClass('notyn-out');
            var _this_id = _e.data('notice_this_id');
            setTimeout(function () {
                _e.remove();
                window.notice_ing[_this_id] = false;
            }, 3000);
        }

        //打开弹窗
        this.open = function () {
            this.$el.modal('show');
            this.refresh_lists && this.loadLists(); //刷新列表
        };

        //关闭弹窗
        this.close = function () {
            this.$el.modal('hide');
        };

        this.init();

        return this;
    };

    /********************前台设置付费下载****************************************************************** */
    $.fn.PayDawnloadEdit = function () {
        function mainInit(element) {
            var $el = $(element);
            var $lists_box = $el.find('.lists-box');
            var $modal;
            var $modal_body;

            init();

            //编辑内容模态框
            function init() {
                if ($modal) return;
                var modal_id = 'pay-dawnload-edit-modal'; //设置唯一ID
                var is_phone = $(window).width() < 769;

                var title = '<div class="mb20"><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-download"></i></span><span class="pay-download-title">' + mce__('editor_download_resource') + '</span></b></div>';

                var input = '<div class="mb20"><div class="muted-color mb6">' + mce__('editor_download_url') + '</div><div class="flex ac"><input type="text" class="form-control" tabindex="1" value="" name="link" placeholder="' + mce__('editor_enter_download_url') + '"><botton type="button" class="flex0 ml6 pay-upload-btn but hollow c-blue"><i class="fa fa-upload"></i>' + mce__('editor_upload_file') + '</botton></div><div class="px12 muted-3-color mt6">' + mce__('editor_paste_link_hint') + '</div></div>';

                input += '<div class="mb20"><div class="muted-color mb6">' + mce__('editor_resource_note') + '</div><input type="text" class="form-control" tabindex="1" value="" name="more" placeholder="' + mce__('editor_enter_more') + '"><div class="px12 muted-3-color mt6">' + mce__('editor_resource_note_hint') + '</div></div>';

                input += '<div class="mb20"><div class="muted-color mb6">' + mce__('editor_click_copy') + '</div><div class="flex ac"><input type="text" tabindex="1" value="" name="copy_key" class="form-control mr10" placeholder="' + mce__('editor_copy_name') + '"><input type="text" class="form-control" tabindex="1" value="" name="copy_val" placeholder="' + mce__('editor_copy_content') + '"></div><div class="px12 muted-3-color mt6">' + mce__('editor_copy_hint') + '</div></div>';

                input += '<div class="mb6"><div class="muted-color mb6">' + mce__('editor_custom_btn') + '</div><div class="flex ac"><input type="text" tabindex="1" value="" name="name" class="form-control mr10" placeholder="' + mce__('editor_custom_btn_ph') + '"></div></div>';

                var class_args = ['c-red', 'c-yellow', 'c-cyan', 'c-blue', 'c-green', 'c-purple', 'b-red', 'b-yellow', 'b-cyan', 'b-blue', 'b-green', 'b-purple', 'jb-red', 'jb-pink', 'jb-yellow', 'jb-cyan', 'jb-blue', 'jb-green', 'jb-purple', 'jb-vip1', 'jb-vip2'];

                var class_btns = '<botton type="button" class="class-choice badg b-theme" data-val=""></botton>';
                class_args.map(function (v) {
                    class_btns += '<botton type="button" class="class-choice badg ' + v + '" data-val="' + v + '"></botton>';
                });

                input += '<div class="mb20"><div class="muted-2-color mb6 px12">' + mce__('editor_select_btn_style') + '</div><div class="flex ac hh">' + class_btns + '</div><input type="hidden" name="class" value=""></div>';

                var content_html = title + '<div class="mini-scrollbar scroll-y max-vh7" style="padding:0 3px;">' + input + '</div><div class="modal-buts but-average"><a class="but c-blue pay-dawnload-submit" href="javascript:;">' + mce__('media_confirm') + '</a></div>';

                var modal_html = '<div class="modal flex jc fade pay-dawnload-edit-modal' + (is_phone ? ' bottom' : '') + '" id="' + modal_id + '" tabindex="-1" role="dialog" aria-hidden="false" style="display: none;"><div class="modal-mini full-sm modal-dialog" role="document"><div class="modal-content"><button data-dismiss="modal" class="mr10 mt10 close"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="pay-dawnload-edit-content modal-body">' + content_html + '</div></div>' + (is_phone ? '<div class="touch-close"></div>' : '') + '</div></div>';

                $('body').append(modal_html);
                $modal = $('#' + modal_id);
                $modal_body = $('#' + modal_id + ' .pay-dawnload-edit-content');

                if (is_phone) {
                    $modal.on('show.bs.modal', function () {
                        var $this = $(this);
                        $this.minitouch({
                            direction: 'bottom',
                            selector: '.modal-dialog',
                            start_selector: '.touch-close',
                            onEnd: function () {
                                $this.modal('hide');
                            },
                        });
                    });
                }

                var args = {
                    type: 'file',
                    is_upload: true,
                    is_input: false,
                    upload_multiple: 1,
                    upload_size: _win.upload_file_size || 30,
                    upload_ext: _win.upload_ext || '',
                    multiple: 1,
                    text: {
                        input_title: '',
                        title: mce__('editor_select_attachment'),
                    },
                };
                var file_media = new zib.media(args);

                file_media.$el.on('lists_submit upload', function (e, data) {
                    data = data.data ? data.data[0] : data;
                    file_media.close();
                    if (data.url) {
                        $modal_body.find('[name="link"]').val(data.url);
                        submitBtnSwitch();
                    }
                });

                $modal.on('click', '.pay-dawnload-submit', function () {
                    setData();
                });

                $modal.on('input', '[name="link"]', submitBtnSwitch);
                //粘贴事件
                $modal.on('paste', '[name="link"]', function (event) {
                    var $this = $(this);
                    var _val = event.originalEvent.clipboardData.getData('text');
                    if (_val) {
                        //链接判断
                        var urlRegex = '(https?://.*?)[\r\n 提取]';
                        var url_matches = _val.match(urlRegex);

                        if (url_matches && url_matches[1]) {
                            event.preventDefault();
                            $this.val(url_matches[1]);
                        }

                        //提取码
                        var codeRegex = '[提取码|密码][:|：]( |)([a-zA-Z0-9]{4,6})';
                        var code_matches = _val.match(codeRegex);
                        if (code_matches && code_matches[2]) {
                            var code = code_matches[2];
                            var parents = $modal;
                            parents.find('input[name="more"]').val(mce__('editor_extract_code_colon') + code);
                            parents.find('input[name="copy_key"]').val(mce__('editor_extract_code'));
                            parents.find('input[name="copy_val"]').val(code);
                        }

                        submitBtnSwitch();
                    }
                });

                $modal.on('click', '.pay-upload-btn', function () {
                    file_media.resetActiveLists();
                    file_media.open();
                });

                $modal.on('click', '.class-choice', function () {
                    classChoice($(this).attr('data-val'));
                });

                $el.on('click', '.pay-dawnload-add', function () {
                    $modal.data('edit_lists', false);
                    modalOpen({});
                });

                $el.on('click', '.pay-dawnload-add-edit', function () {
                    var data = {};
                    var list = $(this).parent();
                    list.find('[data-depend-id]').each(function () {
                        var $this = $(this);
                        var _k = $this.attr('data-depend-id');
                        data[_k] = $this.val();
                    });
                    $modal.data('edit_lists', list);
                    modalOpen(data);
                });

                $el.on('click', '.pay-dawnload-add-delete', function () {
                    $($(this).parents('.lists-item')[0]).remove();
                    hiddenNameRefresh();
                });
            }

            function encode(str) {
                str = $.trim(str.replace(/<[^<>]+?>/g, ''));
                if (str.length == 0) return '';
                str = str.replace(/</g, '&lt;');
                str = str.replace(/>/g, '&gt;');
                str = str.replace(/'/g, '&#39;');
                str = str.replace(/"/g, '&quot;');
                return str;
            }

            function setData() {
                var data = {};

                $.each(['link', 'more', 'name', 'copy_key', 'copy_val', 'class'], function (i, name) {
                    data[name] = encode($modal_body.find('[name="' + name + '"]').val());
                });

                if (!data.link) return;

                var $edit = $modal.data('edit_lists');

                if (!$edit) {
                    $edit = $('<div class="flex ac jsb lists-item"><a href="javascript:;" class="flex1 name-link pay-dawnload-add-edit"></a><a href="javascript:;" class="pay-dawnload-add-delete em12 ml10 hollow but cir c-red"><i title="fa fa-trash-o" class="fa fa-trash-o"></i></a><div class="hidden-input"></div></div>');
                    $lists_box.append($edit);
                }

                $.each(data, function (k, v) {
                    var _input = $edit.find('[data-depend-id="' + k + '"]');
                    if (_input.length) {
                        _input.val(v);
                    } else {
                        $edit.find('.hidden-input').append('<input type="hidden" data-depend-id="' + k + '" value="' + v + '">');
                    }
                });

                $edit.find('.name-link').html(data.link);
                hiddenNameRefresh();
                $modal.modal('hide');
            }

            //刷新hidden_name
            function hiddenNameRefresh() {
                var qz = 'posts_zibpay[pay_download]';
                $lists_box.find('.lists-item').each(function (index) {
                    $(this)
                        .find('[data-depend-id]')
                        .each(function () {
                            var $this = $(this);
                            $this.attr('name', qz + '[' + index + '][' + $this.attr('data-depend-id') + ']');
                        });
                });
            }

            function modalOpen(data) {
                data = $.extend(
                    {
                        link: '',
                        more: '',
                        name: '',
                        class: '',
                        copy_key: '',
                        copy_val: '',
                    },
                    data
                );

                var title = data.link ? mce__('editor_edit_resource') : mce__('editor_download_resource');
                $modal_body.find('.pay-download-title').text(title);

                $.each(data, function (k, v) {
                    $modal_body.find('[name="' + k + '"]').val(v);
                });

                submitBtnSwitch();
                classChoice(data.class);
                $modal.modal('show');
            }

            function classChoice(val) {
                val = val || '';

                $modal_body.find('[name="class"]').val(val);
                $modal_body
                    .find('.class-choice[data-val="' + val + '"]')
                    .addClass('is-active')
                    .siblings()
                    .removeClass('is-active');
            }

            //切换
            function submitBtnSwitch() {
                var $submit = $modal_body.find('.modal-buts');
                if (encode($modal_body.find('[name="link"]').val())) {
                    $submit.show();
                } else {
                    $submit.hide();
                }
            }
        }

        return this.each(function () {
            var $this = $(this);
            var data = $this.data('pay_dawnload_data');
            if (!data) $this.data('pay_dawnload_data', new mainInit($(this)));
        });
    };

    tbquire(['main'], function () {
        $('.pay-dawnload-edit').PayDawnloadEdit();
    });

    /*********************特色图像、封面编辑***************************************************************** */

    //特色图像、封面编辑
    $.fn.featuredEdit = function () {
        function mainInit(element) {
            this.$el = $(element);
            this.options = {
                video: true,
                slide: true,
                video_ratio: 46, //比例
                slide_ratio: 46, //比例
                image_ratio: 50, //比例
                video_upload_size: _win.upload_video_size || 30,
                image_upload_size: _win.upload_img_size || 4,
                image_upload_multiple: ~~_win.img_upload_multiple || 6,
            };
            this.data = {};
            var featured_text = 'featured';
            this.is_init || init(this);

            //首次初始化
            function init(that) {
                var box_c = featured_text + '-box';
                var btns_c = featured_text + '-btns';
                var input_c = featured_text + '-input';
                var dom = '<div class="' + box_c + '"></div><div class="' + btns_c + '"></div><div class="hide ' + input_c + '"></div>';

                that.$el.html(dom);
                that.$con = that.$el.find('.' + box_c);
                that.$btn = that.$el.find('.' + btns_c);
                that.$input = that.$el.find('.' + input_c);

                var args = getInitArgs(that.$el);
                //加载配置
                if (args.options) {
                    that.options = $.extend(that.options, args.options);
                }

                //加载按钮
                loadBtns(that);

                //渲染内容
                if (args.data) {
                    if (args.type === 'video') {
                        //视频
                        loadDplayer(args.data, that);
                    } else if (args.type === 'slide') {
                        //幻灯片
                        loadSwiper(args.data, that);
                    } else {
                        //图片
                        loadImage(args.data, that);
                    }
                }
                that.is_init = true;
            }

            function getInitArgs($this) {
                var args = $this.attr('featured-args');
                try {
                    args = JSON.parse(args);
                } catch (e) {}
                $this.removeAttr('featured-args');

                return args;
            }

            //加载视频
            function loadDplayer(args, that) {
                var data = that.data;
                var this_data = data.data || {};

                if (args.pic) {
                    this_data.pic = args.pic;
                } else if (!this_data.pic && data.type == 'image' && this_data.url) {
                    this_data.pic = this_data.url;
                }

                data.type = 'video';
                this_data.url = args.url || this_data.url || '';
                data.data = this_data;

                var video_ratio = ~~that.options.video_ratio;
                var html = $('<div class="new-dplayer ' + (video_ratio ? ' dplayer-scale-height' : '') + '" video-url="' + this_data.url + '" video-pic="' + this_data.pic + '"' + (video_ratio ? '  style="--scale-height:' + video_ratio + '%;"' : '') + '><div class="graphic" style="padding-bottom:' + video_ratio + '%;"><div class="abs-center text-center"><i class="fa fa-play-circle fa-4x muted-3-color opacity5" aria-hidden="true"></i></div></div></div>');

                that.data = data;
                that.$con.html(html);
                that.$el.addClass(featured_text + '-loaded video');
                resetMedia(that, 'image');
                resetMedia(that, 'video_pic');
                get_new_dplayer(html);
            }

            //加载幻灯片
            function loadSwiper(args, that) {
                var slides = '';
                var ids = [];
                $.each(args, function (index, val) {
                    var src = val.url;
                    ids.push(val.id);
                    slides += '<div class="swiper-slide"><img src="' + src + '" alt="' + mce__('featured_cover_slide') + '"></div>';
                });

                var swiper = '<div class="new-swiper scale-height" data-loop="true" data-autoplay="true" data-interval="2000" style="--scale-height:' + that.options.slide_ratio + '%"><div class="swiper-wrapper">' + slides + '</div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-pagination"></div></div>';

                var data = {
                    type: 'slide',
                    data: {
                        ids: ids,
                    },
                };
                that.data = data;
                that.$con.html(swiper);
                that.$el.addClass(featured_text + '-loaded slide');
                resetMedia(that, 'video');
                new_swiper();
            }

            //加载图片封面
            function loadImage(args, that) {
                var src = args.url;
                var html = '<div class="graphic" style="padding-bottom:' + that.options.image_ratio + '%;"><img class="fit-cover" src="' + src + '" alt="' + mce__('featured_cover_image') + '"></div>';

                var data = {
                    type: 'image',
                    data: {
                        url: src,
                        id: args.id || 0,
                    },
                };
                that.data = data;
                that.$con.html(html);
                that.$el.addClass(featured_text + '-loaded image');
                resetMedia(that, 'video');
            }

            //移除全部的内容
            function loadDelete(that) {
                var data = {
                    type: 'close', //关闭
                };
                that.data = data;
                that.$el.removeClass(featured_text + '-loaded image slide video'); //移除class
                that.$con.html(''); //移除内容
                that.media.image.resetActiveLists(); //重置media的已选择数据
                that.media.video && that.media.video.resetActiveLists(); //重置media的已选择数据
                resetMedia(that, 'video');
                resetMedia(that, 'image');
            }

            //加载按钮
            function loadBtns(that) {
                var btns = '';
                var option = that.options;
                var video_class = 'btn-video';
                var video_pic_class = 'btn-video-pic';
                var image_class = 'btn-image';
                var delete_class = 'btn-delete'; //删除
                btns += '<botton type="button" class="but cir ' + image_class + '" title="' + mce__('featured_btn_image') + '" data-toggle="tooltip"><i class="em09 fa fa-camera"></i></botton>';
                btns += option.video ? '<botton type="button" class="but cir ' + video_pic_class + '" title="' + mce__('featured_set_video_cover') + '" data-toggle="tooltip"><i class="em09 fa fa-image"></i></botton>' : '';
                btns += option.video ? '<botton type="button" class="but cir ' + video_class + '" title="' + mce__('featured_btn_video') + '" data-toggle="tooltip"><i class="em09 fa fa-video-camera"></i></botton>' : '';
                btns += '<botton type="button" class="but cir ' + delete_class + '" title="' + mce__('featured_btn_remove') + '" data-toggle="tooltip"><i class="em09 fa fa-trash"></i></botton>';

                var btns_full = '<div class="btns-full flex jc">' + btns + '</div>';
                that.$btn.html(btns_full);

                //加载行为
                that.media = that.media || {};

                //视频首图封面
                var media_video_pic_args = {
                    type: 'image',
                    is_upload: true,
                    upload_multiple: 1,
                    upload_size: option.image_upload_size,
                    multiple: 1,
                    text: {
                        input_title: mce__('featured_fill_image_url'),
                        title: mce__('featured_set_video_cover_title'),
                    },
                };

                var video_pic_media = (that.media.video_pic = new zib.media(media_video_pic_args));

                that.$el.on('click', '.' + video_pic_class, function () {
                    video_pic_media.open();
                });

                video_pic_media.$el.on('lists_submit upload', function (e, data) {
                    video_pic_media.close();
                    var video_pic_data = data.data ? data.data[0] : data;
                    loadDplayer(
                        {
                            pic: video_pic_data.url,
                        },
                        that
                    );
                });

                video_pic_media.$el.on('input_submit', function (e, data) {
                    loadDplayer(
                        {
                            pic: data.vals[0],
                        },
                        that
                    );
                });

                //视频设置弹窗
                if (option.video) {
                    var media_video_args = {
                        type: 'video',
                        is_upload: true,
                        iframe: false,
                        upload_multiple: 1,
                        upload_size: option.video_upload_size,
                        multiple: 1,
                    };

                    var video_media = (that.media.video = new zib.media(media_video_args));
                    that.$el.on('click', '.' + video_class, function () {
                        video_media.open();
                    });

                    video_media.$el.on('lists_submit upload', function (e, data) {
                        video_media.close();
                        var video = data.data ? data.data[0] : data;
                        loadDplayer(video, that);
                        if (!that.data.data || !that.data.data.pic) {
                            video_pic_media.open();
                        }
                    });

                    video_media.$el.on('input_submit', function (e, data) {
                        loadDplayer(
                            {
                                url: data.vals[0],
                            },
                            that
                        );
                    });
                }

                //图片
                var media_image_args = {
                    type: 'image',
                    is_upload: true,
                    upload_multiple: option.slide ? option.image_upload_multiple : 1,
                    upload_size: option.image_upload_size,
                    multiple: option.slide ? 30 : 1,
                    text: {
                        input_title: mce__('featured_fill_image_url'),
                    },
                };

                var image_media = (that.media.image = new zib.media(media_image_args));
                that.$el.on('click', '.' + image_class, function () {
                    image_media.open();
                });

                image_media.$el.on('lists_submit', function (e, data) {
                    var lists = data.data;
                    if (lists.length > 1) {
                        loadSwiper(lists, that);
                    } else {
                        loadImage(lists[0], that);
                    }
                });

                image_media.$el.on('input_submit', function (e, data) {
                    loadImage(
                        {
                            url: data.vals[0],
                        },
                        that
                    );
                });

                //删除
                that.$el.on('click', '.' + delete_class, function () {
                    loadDelete(that);
                });
            }
        }

        function resetMedia(that, type) {
            var media = that.media[type];
            if (media) {
                media.resetActiveLists();
                media.resetInputVals();
            }
        }

        return this.each(function () {
            var $this = $(this);
            var data = $this.data('featured_data');
            if (!data) $this.data('featured_data', new mainInit($(this)));
        });
    };

    tbquire(['main'], function () {
        $('.featured-edit').featuredEdit();
    });

    /************************************************************************************** */

    var PluginManagerAdd = (window.tinymce && window.tinymce.PluginManager.add) || function () {};
    if (mce.hide_btn) {
        PluginManagerAdd('zib_hide', function (editor) {
            var menu = [
                {
                    text: mce__('hide_reply_to_view'),
                    onclick: function () {
                        setContent('reply');
                    },
                },
                {
                    text: mce__('hide_login_to_view'),
                    onclick: function () {
                        setContent('logged');
                    },
                },
                {
                    text: mce__('hide_vip_to_view'),
                    onclick: function () {
                        setContent('vip1');
                    },
                },
            ];

            if (mce.hide_pay) {
                menu.push({
                    text: mce__('hide_pay_to_view'),
                    onclick: function (event) {
                        setContent('payshow', event);
                    },
                });
            }

            function setContent(type) {
                var type_desc = {
                    payshow: mce__('hide_paid_read'),
                    vip1: mce__('hide_vip_to_view'),
                    logged: mce__('hide_login_to_view'),
                    reply: mce__('hide_reply_after_view'),
                };

                var getNode = editor.selection.getNode();
                var val = $.trim(editor.selection.getContent()) || '';
                var suffix = '<p></p>';
                val = '<p>' + val + '</p>';
                if (is_own(getNode)) {
                    suffix = '';
                    val = $(getNode).find('[contenteditable="true"]').html();
                } else if (getNode.nodeName === 'P') {
                    var outerHTML = $.trim(getNode.outerHTML);
                    if (outerHTML) {
                        editor.dom.remove(getNode);
                        val = outerHTML;
                    }
                }
                editor.insertContent('<div class="tinymce-hide" contenteditable="false"><p class="hide-before">[hidecontent type="' + type + '" desc="' + mce__('hide_content_prefix') + type_desc[type] + '"]</p><div contenteditable="true">' + val + '</div><p class="hide-after">[/hidecontent]</p></div>' + suffix);
            }

            function is_own(element) {
                return element.className === 'tinymce-hide';
            }

            editor.addButton('zib_hide', {
                text: '',
                icon: 'preview',
                tooltip: mce__('hide_content'),
                type: 'menubutton',
                stateSelector: '.tinymce-hide',
                menu: menu,
            });

            editor.on('wptoolbar', function (event) {
                if (is_own(event.element) && editor.wp) {
                    event.toolbar = editor.wp._createToolbar(['zib_hide', 'dom_remove'], true);
                }
            });
        });
    }

    //--------------------------引言------------------------------------
    PluginManagerAdd('zib_quote', function (editor) {
        editor.on('init', function () {
            //页面刷新时提醒
            window.addEventListener('beforeunload', (event) => {
                // 只有在需要阻止时才触发确认
                var shouldWarn = !editor.isHidden() && editor.isDirty() && $(tinyMCE.activeEditor.getContent()).text().length > 10;
                if (shouldWarn && !window.post_is_save && !$('body.wp-admin').length) {
                    event.preventDefault(); // 必须调用（Chrome/Edge/Firefox 2025+ 要求）
                    event.returnValue = mce__('editor_unsaved_leave'); // 必须设置（兼容旧浏览器 + 触发确认框）
                    return mce__('editor_unsaved_leave');
                }
            });

            let label = new Label();
            onBlur();

            tinymce.DOM.bind(label.el, 'click', onFocus);
            editor.on('focus', onFocus);
            editor.on('blur', onBlur);
            editor.on('change', onBlur);
            editor.on('setContent', onBlur);
            editor.on('keydown', onKeydown);

            function onFocus() {
                if (!editor.settings.readonly === true) {
                    label.hide();
                }
                editor.execCommand('mceFocus', false);
            }

            function onBlur() {
                if (editor.getContent() == '') {
                    label.show();
                } else {
                    label.hide();
                }
            }

            function onKeydown() {
                label.hide();
            }
        });

        let Label = function () {
            let placeholder_text = editor.getElement().getAttribute('placeholder') || editor.settings.placeholder || mce__('editor_enter_content');
            let placeholder_attrs = editor.settings.placeholder_attrs || {
                style: {
                    position: 'absolute',
                    top: this.get_top(),
                    left: 0,
                    color: 'rgba(136, 136, 136, 0.6)',
                    padding: '1%',
                    width: '98%',
                    overflow: 'hidden',
                    'white-space': 'pre-wrap',
                    'font-size': '16px',
                },
            };
            let contentAreaContainer = editor.getContentAreaContainer();

            // Create label el
            this.el = tinymce.DOM.add(contentAreaContainer, editor.settings.placeholder_tag || 'label', placeholder_attrs, placeholder_text);
        };

        Label.prototype.get_top = function () {
            return tinymce.DOM.$(editor.getContainer()).find('.mce-top-part')[0].clientHeight + 11;
        };

        Label.prototype.hide = function () {
            tinymce.DOM.setStyle(this.el, 'display', 'none');
        };

        Label.prototype.show = function () {
            tinymce.DOM.setStyle(this.el, 'display', '');
            tinymce.DOM.setStyle(this.el, 'top', this.get_top());
        };

        editor.addButton('zib_quote', {
            text: '',
            icon: 'blockquote',
            tooltip: mce__('editor_quote'),
            type: 'menubutton',
            menu: [
                {
                    text: mce__('editor_quote_gray'),
                    onclick: function () {
                        setContent('');
                    },
                },
                {
                    text: mce__('editor_quote_red'),
                    onclick: function () {
                        setContent('qe_wzk_c-red');
                    },
                },
                {
                    text: mce__('editor_quote_blue'),
                    onclick: function () {
                        setContent('qe_wzk_lan');
                    },
                },
                {
                    text: mce__('editor_quote_green'),
                    onclick: function () {
                        setContent('qe_wzk_lv');
                    },
                },
            ],
        });

        function setContent(type) {
            var val = $.trim(editor.selection.getContent()) || '';
            var suffix = '<p></p>';
            val = '<p>' + val + '</p>';
            var getNode = editor.selection.getNode();
            if (is_own(getNode)) {
                suffix = '';
                val = $(getNode).find('[contenteditable="true"]').html();
            } else if (getNode.nodeName === 'P') {
                var outerHTML = $.trim(getNode.outerHTML);
                if (outerHTML) {
                    editor.dom.remove(getNode);
                    val = outerHTML;
                }
            }
            editor.insertContent('<div class="quote_q quote-mce ' + type + '" contenteditable="false"><div contenteditable="true">' + val + '</div></div>' + suffix);
        }

        function is_own(element) {
            return $(element).hasClass('quote_q');
        }

        editor.on('wptoolbar', function (event) {
            if (is_own(event.element) && editor.wp) {
                event.toolbar = editor.wp._createToolbar(['zib_quote', 'dom_remove'], true);
            }
        });
    });

    if (!mce.is_admin) {
        //--------------------------上传文件-------------------------------
        PluginManagerAdd('zib_file', function (editor) {
            var args = {
                type: 'file',
                is_upload: true,
                is_input: true,
                upload_multiple: 1,
                upload_size: _win.upload_file_size || 30,
                upload_ext: _win.upload_ext || '',
                multiple: 1,
                text: {
                    //   input_title: '',
                    //  title: '请选择附件',
                    // subtitle: '上传或选择附件以插入'
                },
            };

            var media = new zib.media(args);

            media.$el.on('lists_submit upload', function (e, data) {
                media.close();

                var file_data = data.data ? data.data[0] : data;

                var name = file_data.filename || file_data.name;
                var desc_1 = mce_sprintf('editor_file_ext', getFileExt(name));
                var desc_2 = file_data.filesizeInBytes ? media.formatSize(file_data.filesizeInBytes) : '';

                var icon = '';
                if (file_data.type === 'image' && file_data.thumbnail_url) {
                    icon = '<img class="fit-cover no-imgbox" src="' + file_data.thumbnail_url + '" alt="' + mce__('editor_download_icon_alt') + '">';
                }
                setContent(file_data.id, '', name, icon, desc_1, desc_2);
            });

            media.$el.on('input_submit', function (e, data) {
                if (!data.vals || !data.vals[0]) return;
                setContent(0, data.vals[0]);
            });

            function getFileExt(name) {
                return name.substring(name.lastIndexOf('.') + 1);
            }

            function setContent(file_id, href, filename, icon, desc_1, desc_2) {
                if (!file_id && !href) return;
                filename = filename || mce__('editor_enter_filename');
                icon = icon || '';
                var desc = desc_1 || desc_2 ? '<div class="mt10"><div class="flex ab jsb muted-2-color file-download-desc em09"><div class="desc-left">' + desc_1 + '</div><div class="desc-right">' + desc_2 + '</div></div></div>' : '';
                href = href || 'javascript:;';
                file_id = file_id ? 'data-download-file="' + file_id + '"' : '';

                var suffix = '<p></p>';
                var html =
                    '<div class="mb20 muted-box file-download-box" contenteditable="false"><div class="flex ac jsb">\
            <div class="flex ac">\
            <div class="mr10"><div class="file-download-icon">' +
                    icon +
                    '</div></div>\
    <div class="text-ellipsis-2 file-download-name" contenteditable="true">' +
                    filename +
                    '</div>\
        </div>\
            <div class="flex0 ml20">\
                <a href="' +
                    href +
                    '" data-mce-href="' +
                    href +
                    '" ' +
                    file_id +
                    ' class="but c-blue file-download-btn"><i class="fa fa-download"></i>' +
                    mce__('editor_download') +
                    '</a>\
            </div>\
        </div>\
        ' +
                    desc +
                    '</div>';

                editor.insertContent(html + suffix);
            }

            function open() {
                media.open();
                media.resetActiveLists();
                media.resetInputVals();
            }

            editor.addButton('zib_file', {
                text: '',
                icon: 'upload',
                tooltip: mce__('editor_attachment'),
                onclick: function () {
                    is_mobile() || open();
                },
                onTouchEnd: function () {
                    is_mobile() && open();
                },
            });
        });

        //--------------------------上传图片-------------------------------
        PluginManagerAdd('zib_img', function (editor) {
            var args = {
                type: 'image',
                is_upload: mce.img_allow_upload,
                upload_multiple: ~~_win.img_upload_multiple,
                upload_size: _win.upload_img_size || 4,
                multiple: 100,
            };

            var media = new zib.media(args);

            function open() {
                media.open();
            }

            media.$el.on('lists_submit', function (e, data) {
                var lists = data.data;
                var html = '';
                for (let k in lists) {
                    var full = lists[k].url;
                    var src = lists[k].large_url;
                    html += GetInsertContent(src, full, lists[k].id, lists[k].title);
                }

                editor.insertContent(html + '<p></p>');

                //重置media的已选择数据
                media.resetActiveLists();
            });

            //上传后自动被选择
            media.$el.on('upload', function (e, data) {
                media.active_lists.push(data.id);
                //重置media的已选择数据
                media.setActiveLists(media.active_lists);
            });

            //手动输入图片地址
            media.$el.on('input_submit', function (e, data) {
                var lists = data.vals;
                var html = '';
                for (let k in lists) {
                    html += GetInsertContent(lists[k]);
                }
                editor.insertContent(html + '<p></p>');

                //重置media的已选择数据
                media.resetInputVals();
            });

            function GetInsertContent(src, full, id, alt) {
                var id_attr = id ? ' data-edit-file-id="' + id + '"' : '';
                var alt_attr = alt ? ' alt="' + alt + '"' : '';
                var full_attr = full ? ' data-full-url="' + full + '"' : '';

                return '<p><img src="' + src + '"' + id_attr + alt_attr + full_attr + '></p>';
            }

            //粘贴上传
            if (mce.img_allow_upload) {
                editor.on('paste', function (event) {
                    var files = (event.clipboardData || window.clipboardData).files;
                    if (files[0] && files[0].type && -1 !== files[0].type.indexOf('image')) {
                        event.preventDefault();
                        media.open(); //开启弹窗
                        media.resetActiveLists(); //重置已选择的数据
                        media.uploadChange($(this), files); //上传文件
                    }
                });
            }

            editor.addButton('zib_img', {
                text: '',
                icon: 'image',
                tooltip: mce__('media_type_image'),
                onclick: function () {
                    is_mobile() || open();
                },
                onTouchEnd: function () {
                    is_mobile() && open();
                },
            });
        });

        //--------------------------上传视频-------------------------------
        PluginManagerAdd('zib_video', function (editor) {
            var args = {
                type: 'video',
                is_upload: mce.video_allow_upload,
                iframe: mce.video_allow_iframe,
                upload_multiple: 1,
                upload_size: _win.upload_video_size || 30,
                multiple: 1,
            };

            var media = new zib.media(args);

            function open() {
                media.open();
            }

            media.$el.on('lists_submit upload', function (e, data) {
                media.close();
                var video = data.data ? data.data[0] : data;
                insertContent(video.url, video.filename, video.id);
                media.resetActiveLists();
            });

            media.$el.on('input_submit', function (e, data) {
                insertContent(data.vals[0]);
                media.resetInputVals();
            });

            media.$el.on('iframe_submit', function (e, data) {
                editor.insertContent('<div contenteditable="false" class="wp-block-embed is-type-video mb20"><div class="iframe-absbox" style="padding-bottom:' + (data.ratio || 56) + '%;"><iframe src="' + data.src + '" allowfullscreen="allowfullscreen" framespacing="0" border="0" width="100%" frameborder="no"></iframe></div></div><p></p>');
                media.resetIframeVal();
            });

            function insertContent(url, filename, id) {
                var attr = ' data-video-url="' + url + '"';
                attr += ' data-video-name="' + (filename || url) + '"';
                attr += id ? ' data-edit-file-id="' + id + '"' : '';

                editor.insertContent('<div contenteditable="false"' + attr + ' class="new-dplayer post-dplayer dplayer"></div><p></p>');
            }

            editor.addButton('zib_video', {
                text: '',
                tooltip: mce__('media_type_video'),
                icon: 'media',
                onclick: function () {
                    is_mobile() || open();
                },
                onTouchEnd: function () {
                    is_mobile() && open();
                },
            });
        });
    }

    PluginManagerAdd('precode', function (editor) {
        if (mce.float_toolbar) {
            editor.on('wptoolbar', function (event) {
                if (is_show_all_toolbar(event)) {
                    var all_toolbar = get_all_toolbar(event, editor.selection.getContent());
                    if (all_toolbar) {
                        event.toolbar = all_toolbar;
                    }
                }
            });
        }

        function is_show_all_toolbar(event) {
            if (event.toolbar) {
                return false;
            }

            if ($(tinyMCE.activeEditor.getContent()).length <= 4) {
                return false;
            }
            return true;
        }

        function get_all_toolbar(event, getContent) {
            var bars = [];
            var nodeName = event.element.nodeName;
            var innerText = $.trim(event.element.innerText);

            if ($.inArray(nodeName, ['P', 'H1', 'H2', 'H3', 'H4', 'H5', 'UL', 'LI', 'STRONG', 'SPAN', 'CODE']) !== -1) {
                var parentElement = event.element.parentElement;
                if (parentElement.id === 'tinymce' || ($.inArray(nodeName, ['SPAN']) !== -1 && parentElement && parentElement.parentElement.id === 'tinymce')) {
                    bars.push('zib_h2', 'zib_h3');
                }

                if (innerText) {
                    //有内容了
                    bars.push('bullist', 'numlist', 'aligncenter');
                    if ($.inArray(nodeName, ['P']) !== -1 && parentElement && parentElement.id === 'tinymce') {
                        bars.push('zib_quote', 'zib_hide');
                    }
                    bars.push('dom_remove');
                } else {
                    bars.push('bullist', 'zib_quote', 'zib_hide');
                    if (!mce.is_admin) {
                        //后台
                        bars.push('zib_img', 'zib_video');
                    }
                    bars.push('precode');
                }

                if (getContent) {
                    //选中了部分文字
                    bars = [];
                    if ($.inArray(nodeName, ['H1', 'H2', 'H3', 'H4', 'H5']) === -1) {
                        bars = ['bold', 'link', 'wp_code', 'code'];
                    }
                    bars.push('forecolor');
                    if ($.inArray(nodeName, ['P']) !== -1) {
                        bars.push('zib_quote', 'zib_hide');
                    }
                    bars.push('remove');
                }
            }

            if (!bars[0] && $.inArray(nodeName, ['DIV', 'PRE']) !== -1 && !$(event.element).hasClass('file-download-name')) {
                bars.push('dom_remove');
            }

            return bars[0] ? editor.wp._createToolbar(bars) : false;
        }

        function toggleFormat(fmt) {
            editor.formatter.toggle(fmt);
            editor.nodeChanged();
        }

        editor.addButton('zib_h2', {
            title: 'Heading 2',
            icon: 'c-svg heading2',
            stateSelector: 'h2',
            onclick: function () {
                toggleFormat('h2');
            },
            onPostRender: function () {
                $('.mce-i-c-svg.heading2').replaceWith('<svg viewBox="0 0 1024 1024" style="width: 21px;height: 21px;fill: currentColor;"><path d="M143.616 219.648v228.864h278.016V219.648h89.856V768H421.632v-242.688H143.616V768H53.76V219.648h89.856z m660.48-10.752c52.992 0 96.768 15.36 131.328 46.08 33.792 30.72 50.688 69.888 50.688 119.04 0 47.616-18.432 90.624-53.76 129.792-16.554667 17.706667-43.093333 39.082667-78.933333 64.426667l-22.613334 15.701333-12.117333 8.192c-52.309333 34.389333-85.248 64.810667-99.413333 91.178667l-2.730667 5.589333h270.336V768h-382.464c0-56.064 17.664-104.448 54.528-145.92 8.746667-10.069333 21.76-22.186667 38.912-36.352l15.786667-12.586667c5.589333-4.352 11.52-8.874667 17.834666-13.568l19.84-14.506666 21.888-15.488 11.690667-8.106667c35.328-24.576 59.904-45.312 75.264-61.44 23.808-26.88 36.096-56.064 36.096-86.784 0-29.952-8.448-52.224-23.808-66.816-16.128-14.592-39.936-21.504-71.424-21.504-33.792 0-59.136 11.52-76.032 34.56-15.36 19.541333-24.362667 48.64-27.050667 86.058667l-0.597333 11.477333h-89.856c0.768-61.44 18.432-110.592 53.76-148.224 36.096-39.936 83.712-59.904 142.848-59.904z"></path></svg>');
            },
        });

        editor.addButton('zib_h3', {
            title: 'Heading 3',
            icon: 'c-svg heading3',
            stateSelector: 'h3',
            onclick: function () {
                toggleFormat('h3');
            },
            onPostRender: function () {
                $('.mce-i-c-svg.heading3').replaceWith('<svg viewBox="0 0 1024 1024" style="width: 21px;height: 21px;fill: currentColor;"><path d="M801.024 208.896c55.296 0 100.608 13.056 134.4 39.936 33.024 26.88 49.92 63.744 49.92 111.36 0 59.904-30.72 99.84-91.392 119.808 32.256 9.984 57.6 24.576 74.496 44.544 18.432 20.736 27.648 47.616 27.648 79.872 0 50.688-17.664 92.16-52.992 124.416-36.864 33.024-85.248 49.92-145.152 49.92-56.832 0-102.912-14.592-137.472-43.776-38.4-32.256-59.904-79.872-64.512-141.312h91.392c1.536 35.328 12.288 62.976 33.792 82.176 19.2 17.664 44.544 26.88 76.032 26.88 34.56 0 62.208-9.984 82.176-29.184 17.664-17.664 26.88-39.168 26.88-65.28 0-31.488-9.984-54.528-28.416-69.12-18.432-15.36-45.312-22.272-80.64-22.272h-38.4V449.28h38.4c32.256 0 56.832-6.912 73.728-20.736 16.128-13.824 24.576-34.56 24.576-61.44 0-26.88-7.68-46.848-22.272-60.672-16.128-13.824-39.936-20.736-71.424-20.736-32.256 0-56.832 7.68-74.496 23.808-18.432 16.128-29.184 40.704-32.256 73.728h-88.32c4.608-55.296 24.576-98.304 61.44-129.024 34.56-30.72 79.104-45.312 132.864-45.312z m-657.408 10.752v228.864h278.016V219.648h89.856V768H421.632v-242.688H143.616V768H53.76V219.648h89.856z"></path></svg>');
            },
        });

        editor.addButton('dom_remove', {
            tooltip: 'Remove',
            icon: 'dashicon dashicons-no-alt',
            onclick: function () {
                remove(editor.selection.getNode());
            },
        });

        //------------下方是高亮代码--------------
        var is_edit = false;
        var precode_toolbar;

        editor.on('wptoolbar', function (event) {
            if (is_precode(event.element)) {
                event.toolbar = precode_toolbar;
            }
        });

        editor.once('preinit', function () {
            if (editor.wp && editor.wp._createToolbar) {
                precode_toolbar = editor.wp._createToolbar(['precode_edit', 'dom_remove'], true);
            }
        });

        function remove(node) {
            editor.dom.remove(node);
            editor.nodeChanged();
            editor.undoManager.add();
        }

        function is_precode(e) {
            return e.lastChild && 'CODE' == e.lastChild.nodeName;
        }

        function get_val(e) {
            if (!is_precode(e)) return false;
            return {
                codeyy: e.lastChild.getAttribute('data-enlighter-language') || 'generic',
                theme: e.lastChild.getAttribute('data-enlighter-theme') || 'qj',
                codenr: e.lastChild.innerText || '',
            };
        }

        function on_submit(e) {
            var codenr = $.trim(tinymce.html.Entities.encodeAllRaw(e.data.codenr.replace(/\r\n/gim, '\n'))),
                tm = e.data.theme == 'qj' ? '' : '&nbsp;data-enlighter-theme="' + e.data.theme + '"',
                yy = e.data.codeyy == 'generic' ? '' : '&nbsp;data-enlighter-language="' + e.data.codeyy + '"';

            if (codenr) {
                editor.insertContent('<pre contenteditable="false" class="enlighter-pre"><code class="gl"' + yy + tm + '>' + codenr + '</code></pre>' + (is_edit ? '' : '<p></p>'));
            }
        }

        function on_click() {
            var getNode = editor.selection.getNode();
            var getval = get_val(getNode);
            var vals = {
                codeyy: 'generic',
                theme: 'qj',
                codenr: '',
            };

            is_edit = !!getval;

            open(getval || vals);
        }

        function open(vals) {
            var w = Math.min(window.innerWidth);
            var h = Math.min(window.innerHeight);
            if (w > 800) {
                w = w * 0.5;
            } else if (w > 640 && w < 801) {
                w = w * 0.7;
            } else if (w < 641) {
                w = w - 20;
            }

            var window_title = (is_edit ? mce__('precode_edit') : mce__('precode_insert')) + mce__('precode_title');

            editor.windowManager.open({
                title: window_title,
                width: w,
                height: h * 0.6,
                body: [
                    {
                        type: 'listbox',
                        name: 'codeyy',
                        label: mce__('precode_select_lang'),
                        value: vals.codeyy,
                        values: [
                            {
                                text: 'yaml',
                                value: 'yaml',
                            },
                            {
                                text: 'xml/html',
                                value: 'xml',
                            },
                            {
                                text: 'visualbasic',
                                value: 'visualbasic',
                            },
                            {
                                text: 'vhdl',
                                value: 'vhdl',
                            },
                            {
                                text: 'typescript',
                                value: 'typescript',
                            },
                            {
                                text: 'swift',
                                value: 'swift',
                            },
                            {
                                text: 'squirrel',
                                value: 'squirrel',
                            },
                            {
                                text: 'sql',
                                value: 'sql',
                            },
                            {
                                text: 'shell',
                                value: 'shell',
                            },
                            {
                                text: 'scss/sass',
                                value: 'scss',
                            },
                            {
                                text: 'rust',
                                value: 'rust',
                            },
                            {
                                text: 'ruby',
                                value: 'ruby',
                            },
                            {
                                text: 'raw',
                                value: 'raw',
                            },
                            {
                                text: 'python',
                                value: 'python',
                            },
                            {
                                text: 'prolog',
                                value: 'prolog',
                            },
                            {
                                text: 'powershell',
                                value: 'powershell',
                            },
                            {
                                text: 'php',
                                value: 'php',
                            },
                            {
                                text: 'nsis',
                                value: 'nsis',
                            },
                            {
                                text: 'matlab',
                                value: 'matlab',
                            },
                            {
                                text: 'markdown',
                                value: 'markdown',
                            },
                            {
                                text: 'lua',
                                value: 'lua',
                            },
                            {
                                text: 'less',
                                value: 'less',
                            },
                            {
                                text: 'kotlin',
                                value: 'kotlin',
                            },
                            {
                                text: 'json',
                                value: 'json',
                            },
                            {
                                text: 'javascript',
                                value: 'javascript',
                            },
                            {
                                text: 'java',
                                value: 'java',
                            },
                            {
                                text: 'ini/conf',
                                value: 'ini',
                            },
                            {
                                text: 'groovy',
                                value: 'groovy',
                            },
                            {
                                text: 'go/golang',
                                value: 'go',
                            },
                            {
                                text: 'docker',
                                value: 'dockerfile',
                            },
                            {
                                text: 'diff',
                                value: 'diff',
                            },
                            {
                                text: 'cordpro',
                                value: 'cordpro',
                            },
                            {
                                text: 'cython',
                                value: 'cython',
                            },
                            {
                                text: 'css',
                                value: 'css',
                            },
                            {
                                text: 'csharp',
                                value: 'csharp',
                            },
                            {
                                text: 'Cpp/C++/C',
                                value: 'cpp',
                            },
                            {
                                text: 'avrassembly',
                                value: 'avrassembly',
                            },
                            {
                                text: 'assembly',
                                value: 'assembly',
                            },
                            {
                                text: mce__('precode_general'),
                                value: 'generic',
                            },
                        ],
                    },
                    {
                        type: 'listbox',
                        name: 'theme',
                        label: mce__('precode_theme'),
                        value: vals.theme,
                        values: [
                            {
                                text: 'enlighter',
                                value: 'enlighter',
                            },
                            {
                                text: 'classic',
                                value: 'classic',
                            },
                            {
                                text: 'beyond',
                                value: 'beyond',
                            },
                            {
                                text: 'mowtwo',
                                value: 'mowtwo',
                            },
                            {
                                text: 'eclipse',
                                value: 'eclipse',
                            },
                            {
                                text: 'droide',
                                value: 'droide',
                            },
                            {
                                text: 'minimal',
                                value: 'minimal',
                            },
                            {
                                text: 'atomic',
                                value: 'atomic',
                            },
                            {
                                text: 'dracula',
                                value: 'dracula',
                            },
                            {
                                text: 'bootstrap4',
                                value: 'bootstrap4',
                            },
                            {
                                text: 'rowhammer',
                                value: 'rowhammer',
                            },
                            {
                                text: 'godzilla',
                                value: 'godzilla',
                            },
                            {
                                text: mce__('precode_follow_global'),
                                value: 'qj',
                            },
                        ],
                    },
                    {
                        type: 'textbox',
                        name: 'codenr',
                        label: mce__('precode_code_label'),
                        value: vals.codenr,
                        multiline: true,
                        minHeight: h * 0.6 - 115,
                    },
                ],
                onsubmit: on_submit,
            });
        }

        $.each(['precode', 'precode_edit'], function (i, k) {
            editor.addButton(k, {
                title: (k === 'precode' ? '' : mce__('precode_edit')) + mce__('precode_title'), //标题自拟
                icon: 'c-svg zib-precode',
                onPostRender: function () {
                    $('.mce-i-c-svg.zib-precode').replaceWith('<svg viewBox="0 0 1024 1024" style="width: 20px;height: 20px;fill: currentColor;"><path d="M902.4 454.4l-144-144a40.704 40.704 0 0 0-57.6 57.6L844.8 512l-144 144a40.704 40.704 0 0 0 57.6 57.6L902.4 569.6a81.472 81.472 0 0 0 0-115.2zM265.6 310.4L121.6 454.4a81.472 81.472 0 0 0 0 115.2l144 144a40.704 40.704 0 0 0 57.6-57.6L179.2 512l144-144a40.704 40.704 0 0 0-57.6-57.6z m109.568 544.064L570.24 147.904a40.704 40.704 0 0 1 78.528 21.632l-195.072 706.56a40.704 40.704 0 0 1-78.528-21.696z"></path></svg>');
                },
                stateSelector: 'pre',
                onclick: on_click,
            });
        });
    });

    //判断是否是移动端
    function is_mobile() {
        return /Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent);
    }
})(jQuery, window);
