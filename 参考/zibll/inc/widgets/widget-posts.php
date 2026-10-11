<?php

add_action('widgets_init', 'widget_register_posts');
function widget_register_posts()
{
    register_widget('widget_ui_mian_posts');
    register_widget('widget_ui_mini_posts');
    register_widget('widget_ui_mini_tab_posts');
    register_widget('widget_ui_main_tab_posts');
}

class widget_ui_main_tab_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_main_tab_posts',
            'w_name'      => _name(__('多栏目文章(旧版即将删除)', 'zib_language')),
            'classname'   => '',
            'description' => __('旧版模块，已废弃，请使用多栏目文章(新)模块', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {

        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);

        $defaults = array(
            'show_thumb'  => '',
            'show_meta'   => '',
            'show_number' => '',
            'type'        => 'auto',
            'limit_day'   => '',
            'limit'       => 6,
            'tabs'        => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => __('热门文章', 'zib_language'),
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        echo '<div class="theme-box">';
        echo '<div class="index-tab">';
        echo '<ul class="list-inline scroll-x mini-scrollbar">';
        $_i  = 0;
        $nav = '';
        $con = '';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $nav_class = $_i == 0 ? 'active' : '';
                $id        = $this->get_field_id('tab_') . $_i;
                echo '<li class="' . $nav_class . '" ><a data-toggle="tab" href="#' . $id . '">' . $tabs['title'] . '</a></li>';
                $_i++;
            }
        }
        echo '</ul>';
        echo '</div>';
        $list_args = array(
            'type' => $instance['type'],
        );
        $_i2 = 0;

        echo '<div class="tab-content">';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $args = array(
                    'post_status'         => 'publish',
                    'cat'                 => $tabs['cat'],
                    'order'               => 'DESC',
                    'showposts'           => $instance['limit'],
                    'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
                    'ignore_sticky_posts' => 1,
                );
                $orderby = $tabs['orderby'];
                if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
                    $args['orderby'] = $orderby;
                } else {
                    $args['orderby']    = 'meta_value_num';
                    $args['meta_query'] = array(
                        array(
                            'key'   => $orderby,
                            'order' => 'DESC',
                        ),
                    );
                }
                if ($tabs['topics']) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'topics',
                            'terms'    => preg_split("/,|，|\s|\n/", $tabs['topics']),
                        ),
                    );
                }
                if ($instance['limit_day'] > 0) {
                    $current_time       = current_time('Y-m-d H:i:s');
                    $args['date_query'] = array(
                        array(
                            'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                            'before'    => $current_time,
                            'inclusive' => true,
                        ),
                    );
                }
                $con_class = $_i2 == 0 ? ' active in' : '';
                $id        = $this->get_field_id('tab_') . $_i2;
                echo '<div class="tab-pane fade' . $con_class . '" id="' . $id . '">';

                $the_query = new WP_Query($args);
                if ($instance['type'] == 'oneline_card') {
                    $list_args['type'] = 'card';
                    echo '<div class="swiper-container swiper-scroll" data-slideClass="posts-item">';
                    echo '<div class="posts-row swiper-wrapper">';
                    zib_posts_list($list_args, $the_query);
                    echo '</div>';
                    echo '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
                    echo '</div>';
                } else {
                    echo '<div>';
                    zib_posts_list($list_args, $the_query);
                    echo '</div>';
                }
                echo '</div>';
                $_i2++;
            }
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        if ($is_preview) {
            echo '</div>';
        }
    }
    public function form($instance)
    {
        $defaults = array(
            'type'      => 'auto',
            'limit'     => 6,
            'limit_day' => '',
            'tabs'      => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => __('热门文章', 'zib_language'),
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $img_html = '';
        $img_i    = 0;
        foreach ($instance['tabs'] as $category) {
            $_html_a = '<label>' . sprintf(esc_html__('栏目 %d-标题（必填）：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].title" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][title]" value="' . esc_attr($instance['tabs'][$img_i]['title']) . '" /></label>';

            $_html_b = '<label>' . sprintf(esc_html__('栏目 %d-分类限制：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].cat" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][cat]" value="' . esc_attr($instance['tabs'][$img_i]['cat']) . '" /></label>';
            $_html_b .= '<label>' . sprintf(esc_html__('栏目 %d-专题：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].topics" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][topics]" value="' . esc_attr($instance['tabs'][$img_i]['topics']) . '" /></label>';

            $_html_c = '<label>' . sprintf(esc_html__('栏目 %d-排序方式：', 'zib_language'), (int) ($img_i + 1)) . '
			<select style="width:100%;" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][orderby]">
			<option value="comment_count" ' . selected('comment_count', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('评论数', 'zib_language') . '</option>
			<option value="views" ' . selected('views', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('浏览量', 'zib_language') . '</option>
			<option value="like" ' . selected('like', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('点赞数', 'zib_language') . '</option>
			<option value="favorite" ' . selected('favorite', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('收藏数', 'zib_language') . '</option>
			<option value="date" ' . selected('date', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('发布时间', 'zib_language') . '</option>
			<option value="modified" ' . selected('modified', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('更新时间', 'zib_language') . '</option>
			<option value="rand" ' . selected('rand', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('随机排序', 'zib_language') . '</option>
			</select></label>';

            $_tt  = '<div class="panel"><h4 class="panel-title">' . sprintf(__('栏目 %1$d：%2$s', 'zib_language'), (int) ($img_i + 1), esc_html($instance['tabs'][$img_i]['title'])) . '</h4><div class="panel-hide panel-conter">';
            $_tt2 = '</div></div>';

            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_html_c . $_tt2 . '</div>';
            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . esc_attr($this->get_field_name('tabs')) . '" data-count="' . (int) $img_i . '" class="button add_button add_lists_button">' . esc_html__('添加栏目', 'zib_language') . '</button>';
        $add_b .= '<button type="button" data-name="' . esc_attr($this->get_field_name('tabs')) . '" data-count="' . (int) $img_i . '" class="button rem_lists_button">' . esc_html__('删除栏目', 'zib_language') . '</button>';
        $img_html .= $add_b;
        ?> <p>
			<div style="width:100%;font-size: 12px;color: #f63e98;"><?php echo wp_kses_post(sprintf(__('当前模块已在V8.0版本中弃用，请使用%1$s模块，功能更强大，性能更好', 'zib_language'), '<code>' . esc_html(__('多栏目文章(新)', 'zib_language')) . '</code>')); ?></div><br>
			<?php zib_cat_help()?>
			<?php zib_topics_help();
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        ?>
		</p>
		<p>
			<label>
				<?php esc_html_e('显示数目：', 'zib_language'); ?>
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo esc_attr($instance['limit']);
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e('限制时间（最近X天）：', 'zib_language'); ?>
				<input style="width:100%;" name="<?php echo esc_attr($this->get_field_name('limit_day')) ?>" type="number" value="<?php echo esc_attr($instance['limit_day']) ?>" size="24" />
			</label>
		</p>

		<p>
			<label>
				<?php esc_html_e('列表显示模式：', 'zib_language'); ?>
				<select style="width:100%;" id="<?php echo esc_attr($this->get_field_id('type'));
        ?>" name="<?php echo esc_attr($this->get_field_name('type'));
        ?>">
					<option value="auto" <?php selected('auto', $instance['type']);
        ?>><?php echo esc_html__('默认（自动跟随主题设置)', 'zib_language'); ?></option>
					<option value="card" <?php selected('card', $instance['type']);
        ?>><?php echo esc_html__('卡片模式', 'zib_language'); ?></option>
					<option value="oneline_card" <?php selected('oneline_card', $instance['type']);
        ?>><?php echo esc_html__('单行滚动卡片模式', 'zib_language'); ?></option>
					<option value="no_thumb" <?php selected('no_thumb', $instance['type']);
        ?>><?php echo esc_html__('无缩略图列表', 'zib_language'); ?></option>
					<option value="mult_thumb" <?php selected('mult_thumb', $instance['type']);
        ?>><?php echo esc_html__('多图模式', 'zib_language'); ?></option>
				</select>
			</label>
		</p>
		<?php echo $img_html; ?>
	<?php
}
}

//////---多栏目文章mini---////////
class widget_ui_mini_tab_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_mini_tab_posts',
            'w_name'      => _name(__('多栏目文章mini(旧版即将删除)', 'zib_language')),
            'classname'   => '',
            'description' => __('旧版模块，已废弃，请使用多栏目文章(新)模块', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);

        $defaults = array(
            'title'        => '',
            'in_affix'     => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url' => '',
            'show_thumb'   => '',
            'show_meta'    => '',
            'show_number'  => '',
            'limit'        => 6,
            'limit_day'    => '',
            'tabs'         => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => __('热门文章', 'zib_language'),
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $class    = '';
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop' . $class . '"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        echo $title;
        echo '<div class="box-body posts-mini-lists zib-widget">';
        echo '<ul class="list-inline scroll-x mini-scrollbar tab-nav-theme">';
        $_i      = 0;
        $id_base = 'post_mini_';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $nav_class = $_i == 0 ? 'active' : '';
                $id        = $id_base . $_i;
                echo '<li class="' . $nav_class . '" ><a class="post-tab-toggle" data-toggle="tab" href="javascript:;" tab-id="' . $id . '">' . $tabs['title'] . '</a></li>';
                $_i++;
            }
        }
        echo '</ul>';
        $list_args = array(
            'show_thumb'  => $instance['show_thumb'] ? true : false,
            'show_meta'   => $instance['show_meta'] ? true : false,
            'show_number' => $instance['show_number'] ? true : false,
        );
        $_i2 = 0;

        echo '<div class="tab-content">';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $args = array(
                    'post_status'         => 'publish',
                    'cat'                 => $tabs['cat'],
                    'order'               => 'DESC',
                    'showposts'           => $instance['limit'],
                    'ignore_sticky_posts' => 1,
                    'no_found_rows'       => true, //不查询分页需要的总数量
                );
                $orderby = $tabs['orderby'];
                if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
                    $args['orderby'] = $orderby;
                } else {
                    $args['orderby']    = 'meta_value_num';
                    $args['meta_query'] = array(
                        array(
                            'key'   => $orderby,
                            'order' => 'DESC',
                        ),
                    );
                }
                if ($tabs['topics']) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'topics',
                            'terms'    => preg_split("/,|，|\s|\n/", $tabs['topics']),
                        ),
                    );
                }
                if ($instance['limit_day'] > 0) {
                    $current_time       = current_time('Y-m-d H:i:s');
                    $args['date_query'] = array(
                        array(
                            'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                            'before'    => $current_time,
                            'inclusive' => true,
                        ),
                    );
                }
                $con_class = $_i2 == 0 ? ' active in' : '';
                $id        = $id_base . $_i2;
                echo '<div class="tab-pane fade' . $con_class . '" tab-id="' . $id . '">';
                $the_query = new WP_Query($args);
                zib_posts_mini_list($list_args, $the_query);
                echo '</div>';
                $_i2++;
            }
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        if ($is_preview) {
            echo '</div>';
        }
    }
    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'in_affix'     => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url' => '',
            'show_thumb'   => '',
            'show_meta'    => '',
            'show_number'  => '',
            'limit'        => 6,
            'limit_day'    => '',
            'tabs'         => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => __('热门文章', 'zib_language'),
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $img_html = '';
        $img_i    = 0;
        foreach ($instance['tabs'] as $category) {
            $_html_a = '<label>' . sprintf(esc_html__('栏目 %d-标题（必填）：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].title" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][title]" value="' . esc_attr($instance['tabs'][$img_i]['title']) . '" /></label>';

            $_html_b = '<label>' . sprintf(esc_html__('栏目 %d-分类限制：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].cat" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][cat]" value="' . esc_attr($instance['tabs'][$img_i]['cat']) . '" /></label>';
            $_html_b .= '<label>' . sprintf(esc_html__('栏目 %d-专题：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].topics" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][topics]" value="' . esc_attr($instance['tabs'][$img_i]['topics']) . '" /></label>';

            $_html_c = '<label>' . sprintf(esc_html__('栏目 %d-排序方式：', 'zib_language'), (int) ($img_i + 1)) . '
			<select style="width:100%;" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][orderby]">
			<option value="comment_count" ' . selected('comment_count', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('评论数', 'zib_language') . '</option>
			<option value="views" ' . selected('views', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('浏览量', 'zib_language') . '</option>
			<option value="like" ' . selected('like', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('点赞数', 'zib_language') . '</option>
			<option value="favorite" ' . selected('favorite', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('收藏数', 'zib_language') . '</option>
			<option value="date" ' . selected('date', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('发布时间', 'zib_language') . '</option>
			<option value="modified" ' . selected('modified', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('更新时间', 'zib_language') . '</option>
			<option value="rand" ' . selected('rand', $instance['tabs'][$img_i]['orderby'], false) . '>' . esc_html__('随机排序', 'zib_language') . '</option>
		</select></label>';
            $_tt  = '<div class="panel"><h4 class="panel-title">' . sprintf(__('栏目 %1$d：%2$s', 'zib_language'), (int) ($img_i + 1), esc_html($instance['tabs'][$img_i]['title'])) . '</h4><div class="panel-hide panel-conter">';
            $_tt2 = '</div></div>';

            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_html_c . $_tt2 . '</div>';

            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . esc_attr($this->get_field_name('tabs')) . '" data-count="' . (int) $img_i . '" class="button add_button add_lists_button">' . esc_html__('添加栏目', 'zib_language') . '</button>';
        $add_b .= '<button type="button" data-name="' . esc_attr($this->get_field_name('tabs')) . '" data-count="' . (int) $img_i . '" class="button rem_lists_button">' . esc_html__('删除栏目', 'zib_language') . '</button>';
        $img_html .= $add_b;
        $page_input[] = array(
            'name'  => __('标题：', 'zib_language'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zib_language'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zib_language'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zib_language'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => __('设置为任意链接', 'zib_language'),
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);

        ?> <p>
			<div style="width:100%;font-size: 12px;color: #f63e98;"><?php echo wp_kses_post(sprintf(__('当前模块已在V8.0版本中弃用，请使用%1$s模块，功能更强大，性能更好', 'zib_language'), '<code>' . esc_html(__('多栏目文章(新)', 'zib_language')) . '</code>')); ?></div>
			<?php zib_cat_help()?>
			<?php zib_topics_help()?>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> <?php esc_html_e('侧栏随动（仅在侧边栏有效）', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_thumb'], 'on'); ?> id="<?php echo $this->get_field_id('show_thumb'); ?>" name="<?php echo $this->get_field_name('show_thumb'); ?>"><?php esc_html_e('显示缩略图', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_number'], 'on'); ?> id="<?php echo $this->get_field_id('show_number'); ?>" name="<?php echo $this->get_field_name('show_number'); ?>"><?php esc_html_e('显示编号', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_meta'], 'on'); ?> id="<?php echo $this->get_field_id('show_meta'); ?>" name="<?php echo $this->get_field_name('show_meta'); ?>"><?php esc_html_e('显示作者、时间、点赞等信息', 'zib_language'); ?>
			</label>
		</p>

		<p>
			<label>
				<?php esc_html_e('显示数目：', 'zib_language'); ?>
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo esc_attr($instance['limit']);
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e('限制时间（最近X天）：', 'zib_language'); ?>
				<input style="width:100%;" name="<?php echo esc_attr($this->get_field_name('limit_day')) ?>" type="number" value="<?php echo esc_attr($instance['limit_day']) ?>" size="24" />
			</label>
		</p>

		<?php echo $img_html; ?>
	<?php
}
}

class widget_ui_mini_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_mini_posts',
            'w_name'      => _name(__('文章mini (旧版即将删除)', 'zib_language')),
            'classname'   => '',
            'description' => __('旧版模块，已废弃，请使用文章列表(新)模块', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }
        extract($args);

        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url' => '',
            'in_affix'     => '',
            'limit'        => 6,
            'limit_day'    => '',
            'cat'          => '',
            'topics'       => '',
            'orderby'      => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $orderby  = $instance['orderby'];

        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $class    = '';
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop' . $class . '"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        echo $title;
        //    echo '<pre>'.json_encode($instance).'</pre>';

        $args = array(
            'post_status'         => 'publish',
            'cat'                 => str_replace('，', ',', $instance['cat']),
            'order'               => 'DESC',
            'showposts'           => $instance['limit'],
            'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
            'ignore_sticky_posts' => 1,
        );

        if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
            $args['orderby'] = $orderby;
        } else {
            $args['orderby']    = 'meta_value_num';
            $args['meta_query'] = array(
                array(
                    'key'   => $orderby,
                    'order' => 'DESC',
                ),
            );
        }
        if ($instance['topics']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topics',
                    'terms'    => preg_split("/,|，|\s|\n/", $instance['topics']),
                ),
            );
        }
        if ($instance['limit_day'] > 0) {
            $current_time       = current_time('Y-m-d H:i:s');
            $args['date_query'] = array(
                array(
                    'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                    'before'    => $current_time,
                    'inclusive' => true,
                ),
            );
        }
        $list_args = array(
            'show_thumb'  => isset($instance['show_thumb']) ? true : false,
            'show_meta'   => isset($instance['show_meta']) ? true : false,
            'show_number' => isset($instance['show_number']) ? true : false,
        );
        echo '<div class="box-body posts-mini-lists zib-widget">';
        $the_query = new WP_Query($args);
        zib_posts_mini_list($list_args, $the_query);
        echo '</div>';
        echo '</div>';
        echo '</div>';
        if ($is_preview) {
            echo '</div>';
        }
    }
    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url' => '',
            'in_affix'     => '',
            'show_thumb'   => '',
            'show_meta'    => '',
            'show_number'  => '',
            'limit'        => 6, 'limit_day' => '',
            'topics'       => '',
            'cat'          => '',
            'orderby'      => 'views',
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $page_input[] = array(
            'name'  => __('标题：', 'zib_language'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zib_language'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zib_language'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zib_language'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => __('设置为任意链接', 'zib_language'),
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>
		<p>
        <div style="width:100%;font-size: 12px;color: #f63e98;"><?php echo wp_kses_post(sprintf(__('当前模块已在V8.0版本中弃用，请使用%1$s模块，功能更强大，性能更好', 'zib_language'), '<code>' . esc_html(__('文章列表(新)', 'zib_language')) . '</code>')); ?></div>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> <?php esc_html_e('侧栏随动（仅在侧边栏有效）', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_thumb'], 'on'); ?> id="<?php echo $this->get_field_id('show_thumb'); ?>" name="<?php echo $this->get_field_name('show_thumb'); ?>"><?php esc_html_e('显示缩略图', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_number'], 'on'); ?> id="<?php echo $this->get_field_id('show_number'); ?>" name="<?php echo $this->get_field_name('show_number'); ?>"><?php esc_html_e('显示编号', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_meta'], 'on'); ?> id="<?php echo $this->get_field_id('show_meta'); ?>" name="<?php echo $this->get_field_name('show_meta'); ?>"><?php esc_html_e('显示作者、时间、点赞等信息', 'zib_language'); ?>
			</label>
		</p>
		<p>
			<?php zib_cat_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('cat');
        ?>" name="<?php echo $this->get_field_name('cat');
        ?>" type="text" value="<?php echo esc_attr(str_replace('，', ',', $instance['cat']));
        ?>" size="24" />
		</p>
		<p>
			<?php zib_topics_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('topics');
        ?>" name="<?php echo $this->get_field_name('topics');
        ?>" type="text" value="<?php echo esc_attr($instance['topics']);
        ?>" size="24" />
		</p>
		<p>
			<label>
				<?php esc_html_e('显示数目：', 'zib_language'); ?>
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo esc_attr($instance['limit']);
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e('限制时间（最近X天）：', 'zib_language'); ?>
				<input style="width:100%;" name="<?php echo esc_attr($this->get_field_name('limit_day')) ?>" type="number" value="<?php echo esc_attr($instance['limit_day']) ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e('排序：', 'zib_language'); ?>
				<select style="width:100%;" id="<?php echo esc_attr($this->get_field_id('orderby'));
        ?>" name="<?php echo esc_attr($this->get_field_name('orderby'));
        ?>">
					<option value="comment_count" <?php selected('comment_count', $instance['orderby']);
        ?>><?php esc_html_e('评论数', 'zib_language'); ?></option>
					<option value="views" <?php selected('views', $instance['orderby']);
        ?>><?php esc_html_e('浏览量', 'zib_language'); ?></option>
					<option value="like" <?php selected('like', $instance['orderby']);
        ?>><?php esc_html_e('点赞数', 'zib_language'); ?></option>
					<option value="favorite" <?php selected('favorite', $instance['orderby']);
        ?>><?php esc_html_e('收藏数', 'zib_language'); ?></option>
                			<option value="comment_count" <?php selected('sales_volume', $instance['orderby']);
        ?>><?php esc_html_e('销售数量', 'zib_language'); ?></option>
					<option value="date" <?php selected('date', $instance['orderby']);
        ?>><?php esc_html_e('发布时间', 'zib_language'); ?></option>
					<option value="modified" <?php selected('modified', $instance['orderby']);
        ?>><?php esc_html_e('更新时间', 'zib_language'); ?></option>
					<option value="rand" <?php selected('rand', $instance['orderby']);
        ?>><?php esc_html_e('随机排序', 'zib_language'); ?></option>
				</select>
			</label>
		</p>
	<?php
}
}

class widget_ui_mian_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_mian_posts',
            'w_name'      => _name(__('文章列表 (旧版即将删除)', 'zib_language')),
            'classname'   => '',
            'description' => __('旧版模块，已废弃，请使用文章列表(新)模块', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }
        extract($args);

        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url' => '',
            'type'         => 'auto',
            'limit'        => 6, 'limit_day' => '',
            'cat'          => '',
            'topics'       => '',
            'orderby'      => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $orderby  = $instance['orderby'];

        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title = $instance['title'];
        $class = ' nobottom';
        if ($instance['type'] == 'card') {
            $class = '';
        }
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop clearfix' . $class . '"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        echo '<div class="theme-box">';
        echo $title;
        //    echo '<pre>'.json_encode($instance).'</pre>';

        $args = array(
            'post_status'         => 'publish',
            'cat'                 => str_replace('，', ',', $instance['cat']),
            'order'               => 'DESC',
            'showposts'           => $instance['limit'],
            'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
            'ignore_sticky_posts' => 1,
        );

        if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
            $args['orderby'] = $orderby;
        } else {
            $args['orderby']    = 'meta_value_num';
            $args['meta_query'] = array(
                array(
                    'key'   => $orderby,
                    'order' => 'DESC',
                ),
            );
        }
        if ($instance['topics']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topics',
                    'terms'    => preg_split("/,|，|\s|\n/", $instance['topics']),
                ),
            );
        }
        if ($instance['limit_day'] > 0) {
            $current_time = current_time('Y-m-d H:i:s');

            $args['date_query'] = array(
                array(
                    'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                    'before'    => $current_time,
                    'inclusive' => true,
                ),
            );
        }

        $list_args = array(
            'type' => $instance['type'],
        );

        $the_query = new WP_Query($args);
        echo '<div class="posts-row">';
        zib_posts_list($list_args, $the_query);
        echo '</div>';
        echo '</div>';
        echo '</div>';
        if ($is_preview) {
            echo '</div>';
        }
    }
    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url' => '',
            'limit'        => 6, 'limit_day' => '',
            'type'         => 'auto',
            'topics'       => '',
            'cat'          => '',
            'orderby'      => 'views',
        );
        $instance     = wp_parse_args((array) $instance, $defaults);
        $page_input[] = array(
            'name'  => __('标题：', 'zib_language'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zib_language'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zib_language'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zib_language'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => __('设置为任意链接', 'zib_language'),
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>
		<p>
        <div style="width:100%;font-size: 12px;color: #f63e98;"><?php echo wp_kses_post(sprintf(__('当前模块已在V8.0版本中弃用，请使用%1$s模块，功能更强大，性能更好', 'zib_language'), '<code>' . esc_html(__('文章列表(新)', 'zib_language')) . '</code>')); ?></div>
		</p>

		<p>

			<?php zib_cat_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('cat');
        ?>" name="<?php echo $this->get_field_name('cat');
        ?>" type="text" value="<?php echo esc_attr(str_replace('，', ',', $instance['cat']));
        ?>" size="24" />
		</p>
		<p>
			<?php zib_topics_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('topics');
        ?>" name="<?php echo $this->get_field_name('topics');
        ?>" type="text" value="<?php echo esc_attr($instance['topics']);
        ?>" size="24" />
		</p>
		<p>
			<label>
				<?php esc_html_e('显示数目：', 'zib_language'); ?>
				<input style="width:100%;" name="<?php echo esc_attr($this->get_field_name('limit')) ?>" type="number" value="<?php echo esc_attr($instance['limit']) ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e('限制时间（最近X天）：', 'zib_language'); ?>
				<input style="width:100%;" name="<?php echo esc_attr($this->get_field_name('limit_day')) ?>" type="number" value="<?php echo esc_attr($instance['limit_day']) ?>" size="24" />
			</label>
		</p>

		<p>
			<label>
				<?php esc_html_e('列表显示模式：', 'zib_language'); ?>
				<select style="width:100%;" id="<?php echo esc_attr($this->get_field_id('type'));
        ?>" name="<?php echo esc_attr($this->get_field_name('type'));
        ?>">
					<option value="auto" <?php selected('auto', $instance['type']);
        ?>><?php echo esc_html__('默认（自动跟随主题设置)', 'zib_language'); ?></option>
					<option value="card" <?php selected('card', $instance['type']);
        ?>><?php echo esc_html__('卡片模式', 'zib_language'); ?></option>
					<option value="no_thumb" <?php selected('no_thumb', $instance['type']);
        ?>><?php echo esc_html__('无缩略图列表', 'zib_language'); ?></option>
					<option value="mult_thumb" <?php selected('mult_thumb', $instance['type']);
        ?>><?php echo esc_html__('多图模式', 'zib_language'); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label>
				<?php esc_html_e('排序：', 'zib_language'); ?>
				<select style="width:100%;" id="<?php echo esc_attr($this->get_field_id('orderby'));
        ?>" name="<?php echo esc_attr($this->get_field_name('orderby'));
        ?>">
					<option value="comment_count" <?php selected('comment_count', $instance['orderby']);
        ?>><?php echo esc_html__('评论数', 'zib_language'); ?></option>
					<option value="views" <?php selected('views', $instance['orderby']);
        ?>><?php echo esc_html__('浏览量', 'zib_language'); ?></option>
					<option value="like" <?php selected('like', $instance['orderby']);
        ?>><?php echo esc_html__('点赞数', 'zib_language'); ?></option>
					<option value="favorite" <?php selected('favorite', $instance['orderby']);
        ?>><?php echo esc_html__('收藏数', 'zib_language'); ?></option>
                	<option value="comment_count" <?php selected('sales_volume', $instance['orderby']);
        ?>><?php echo esc_html__('销售数量', 'zib_language'); ?></option>
					<option value="date" <?php selected('date', $instance['orderby']);
        ?>><?php echo esc_html__('发布时间', 'zib_language'); ?></option>
					<option value="modified" <?php selected('modified', $instance['orderby']);
        ?>><?php echo esc_html__('更新时间', 'zib_language'); ?></option>
					<option value="rand" <?php selected('rand', $instance['orderby']);
        ?>><?php echo esc_html__('随机排序', 'zib_language'); ?></option>
				</select>
			</label>
		</p>
	<?php
}
}

//分类、专题图文模块

function zib_widget_ui_term_card_is_show($show_class, $args, $instance)
{
    if (empty($instance['term_id'][0])) {
        return false;
    }

    return $show_class;
}
function zib_widget_ui_term_card($args, $instance)
{
    $defaults = array(
        'term_id'          => array(),
        'pc_row'           => 2,
        'm_row'            => 1,
        'orderby'          => 'modified',
        'order'            => 'desc',
        'count'            => 4,
        'target_blank'     => false,
        'obs_animation'    => '',
        'animation_repeat' => false,
        'type'             => 'style-1',
        'height_scale'     => 60,
        'mask_opacity'     => 10,
    );
    $instance = wp_parse_args($instance, $defaults);

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $terms = get_terms(array(
        'include' => $instance['term_id'],
        'orderby' => 'include',
    ));
    $is_row       = count($terms) > 1;
    $target_blank = !empty($instance['target_blank']) ? '_blank' : '';
    $html         = '';
    if ($terms) {
        foreach ($terms as $term) {
            $default_img = '';
            if ($term->taxonomy == 'category') {
                $default_img = _pz('cat_default_cover');
                $icon        = '<i class="fa fa-folder-open-o mr6" aria-hidden="true"></i>';
            } elseif ($term->taxonomy == 'topics') {
                $default_img = _pz('topics_default_cover');
                $icon        = '<i class="fa fa-cube mr6" aria-hidden="true"></i>';
            }
            $img         = zib_get_taxonomy_img_url($term->term_id, null, $default_img);
            $name        = zib_str_cut($term->name, 0, 16, '...');
            $count       = (int) $term->count ? (int) $term->count : 0;
            $description = zib_str_cut($term->description, 0, 24, '...');

            $href = get_term_link($term);
            $card = array(
                'type'         => $instance['type'],
                'class'        => 'mb10',
                'img'          => $img,
                'alt'          => $name . '-' . $description,
                'link'         => array(
                    'url'    => $href,
                    'target' => $target_blank,
                ),
                'text1'        => $name,
                'text2'        => $description,
                'text3'        => $icon . $count . __('篇文章', 'zib_language'),
                'lazy'         => true,
                'height_scale' => $instance['height_scale'],
                'mask_opacity' => $instance['mask_opacity'],
            );

            if ($instance['type'] == 'style-2') {
                $card['text1'] = $name;
                $card['text2'] = $description;
                $card['text3'] = '<item data-toggle="tooltip" title="' . esc_attr(sprintf(__('共%d篇文章', 'zib_language'), $count)) . '">' . $icon . $count . '</item>';
            } elseif ($instance['type'] == 'style-3') {
                $card['text1'] = $icon . $name;
                $card['text2'] = $description;
                $card['text3'] = '<i class="fa mr6 fa-file-text-o"></i>' . $count . __('篇文章', 'zib_language');
            } elseif ($instance['type'] == 'style-4') {
                $card['text1'] = $icon . $name;
                $card['text2'] = $description;
                $card['text3'] = '<item data-toggle="tooltip" title="' . esc_attr(sprintf(__('共%d篇文章', 'zib_language'), $count)) . '">' . $icon . $count . '</item>';
            }

            $attr      = '';
            $col_class = '';
            if ($instance['obs_animation']) {
                $col_class .= ' obs-animate ani-' . $instance['obs_animation'];
                $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
                if ($instance['animation_repeat']) {
                    $attr .= ' data-animation-repeat="true"';
                }
            }
            if ($is_row) {
                $col_class .= ' ' . $row_class;
            }

            $html .= '<div class="' . $col_class . '" ' . $attr . '>';
            $html .= zib_graphic_card($card);
            $html .= '</div>';
        }
    }

    echo '<div class="' . ($is_row ? 'mb10' : 'mb20') . '">';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo '</div>';
}

//分类、专题聚合模块

function zib_widget_ui_term_lists_card_is_show($show_class, $args, $instance)
{
    if (empty($instance['term_id'][0])) {
        return false;
    }

    return $show_class;
}
//
function zib_widget_ui_term_lists_card($args, $instance)
{
    $defaults = array(
        'term_id'          => array(),
        'pc_row'           => 2,
        'm_row'            => 1,
        'orderby'          => 'modified',
        'order'            => 'desc',
        'count'            => 4,
        'target_blank'     => false,
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args($instance, $defaults);

    //准备栏目
    $pc_row = (int) $instance['pc_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= ' col-xs-12';

    $is_row       = count($instance['term_id']) > 1;
    $target_blank = !empty($instance['target_blank']) ? '_blank' : '';
    $html         = '';
    if ($instance['term_id']) {
        foreach ($instance['term_id'] as $key => $term_id) {
            $term_args = array(
                'term_id'      => $term_id,
                'class'        => '',
                'target_blank' => $target_blank,
                'orderby'      => $instance['orderby'],
                'order'        => isset($instance['order']) && $instance['order'] === 'asc' ? 'ASC' : 'DESC',
                'count'        => $instance['count'],
            );

            $attr      = '';
            $col_class = '';
            if ($instance['obs_animation']) {
                $col_class .= ' obs-animate ani-' . $instance['obs_animation'];
                $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
                $attr .= ' style=" --delay: ' . strval($key * 0.15) . 's; "';
                if ($instance['animation_repeat']) {
                    $attr .= ' data-animation-repeat="true"';
                }
            }

            if ($is_row) {
                $col_class .= ' ' . $row_class;
            }

            $html .= '<div class="' . $col_class . '" ' . $attr . '>';
            $html .= zib_term_aggregation($term_args);
            $html .= '</div>';
        }
    }

    echo '<div class="clearfix mb6">';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo '</div>';
}

//热榜文章

//
function zib_widget_ui_hot_posts($args, $instance)
{
    echo zib_hot_posts($instance);
}

//付费商品

function zib_widget_ui_posts_pay_is_show($show_class, $args, $instance)
{
    $html = zibpay_get_widget_box($instance);
    if (!$html) {
        return false;
    }

    return $show_class;
}

function zib_widget_ui_posts_pay($args, $instance)
{
    echo zibpay_get_widget_box($instance);
}

//文章列表-新

function zib_widget_ui_main_post($args, $instance)
{
    $style = $instance['style'] ? $instance['style'] : 'list';
    $class = 'widget-main-post mb20 style-' . $style;

    $main_html = '';
    $widget_id = $args['widget_id'];
    $id_base   = 'zib_widget_ui_main_post';
    $index     = str_replace($id_base . '-', '', $widget_id);

    if (isset($instance['load_mode']) && $instance['load_mode'] === 'ajax') {
        $placeholder = ''; //
        if ($style == 'mini') {
            $placeholder = str_repeat('<div class="posts-mini"><div class="placeholder k1"></div></div>', $instance['count']);
            $mini_opt    = $instance['mini_opt'] ? $instance['mini_opt'] : array();
            if (in_array('show_thumb', $mini_opt)) {
                $placeholder = str_repeat('<div class="posts-mini "><div class="mr10"><div class="item-thumbnail placeholder"></div></div><div class="posts-mini-con flex xx flex1 jsb"><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div>', $instance['count']);
            }
        } else {
            $placeholder_type = $style == 'card' ? 'card' : 'lists';
            $placeholder      = zib_get_post_placeholder($placeholder_type, $instance['count']);
        }

        $ias_args = array(
            'type'            => 'ias',
            'id'              => '',
            'class'           => '',
            'loader'          => $placeholder, // 加载动画
            'ajaxpager_class' => 'widget-ajaxpager',
            'query'           => array(
                'action' => 'ajax_widget_ui',
                'id'     => $id_base,
                'index'  => $index,
            ),
        );
        $main_html = zib_get_ias_ajaxpager($ias_args);
    } else {
        $main_html = zib_widget_ui_main_post_ajax($instance, true, add_query_arg(array(
            'action' => 'ajax_widget_ui',
            'id'     => $id_base,
            'index'  => $index,
        ), admin_url('/admin-ajax.php')));
    }

    //开始输出
    echo '<div class="' . $class . '">';
    echo $style == 'mini' ? '<div class="zib-widget posts-mini-lists">' : '';
    echo $main_html;
    echo $style == 'mini' ? '</div>' : '';
    echo '</div>';
}

function zib_widget_ui_main_post_ajax($instance, $no_ajax = false, $ajax_url = null)
{
    $paged      = zib_get_the_paged();
    $style      = $instance['style'] ? $instance['style'] : 'list';
    $paginate   = $instance['paginate'] ? $instance['paginate'] : '';
    $paged_size = $instance['count'];
    $ajax_url   = $ajax_url ?: zib_get_current_url();

    $posts_args = array(
        'cat'         => $instance['cat'],
        'topics'      => $instance['topics'],
        'zibpay_type' => $instance['zibpay_type'],
        'orderby'     => $instance['orderby'],
        'order'       => isset($instance['order']) && $instance['order'] === 'asc' ? 'ASC' : 'DESC',
        'count'       => $instance['count'],
        'limit_day'   => isset($instance['limit_day']) ? (int) $instance['limit_day'] : 0,
    );

    //不需要翻页
    if (!$paginate) {
        $posts_args['no_found_rows'] = true;
        $paged                       = 1;
    }

    $posts_query = zib_get_posts_query($posts_args);
    $lists       = '';
    $mini_number = $paged * $paged_size - $paged_size;

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()): $posts_query->the_post();
            if ($style == 'card') {
                $lists .= zib_posts_mian_list_card(array());
            } elseif ($style == 'mini') {
            $mini_opt = $instance['mini_opt'] ? $instance['mini_opt'] : array();

            $mini_args = array(
                'class'       => 'ajax-item',
                'show_thumb'  => in_array('show_thumb', $mini_opt),
                'show_meta'   => in_array('show_meta', $mini_opt),
                'show_number' => in_array('show_number', $mini_opt),
                'echo'        => false,
            );
            $mini_number++;
            $lists .= zib_posts_mini_while($mini_args, $mini_number);
        } else {
            $lists .= zib_posts_mian_list_list(array('is_mult_thumb' => 'disable', 'is_no_thumb' => 'disable'));
        }

        endwhile;
        wp_reset_query();
    }
    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null(__('暂无内容', 'zib_language'), 10);
    }

    //分页paginate
    if ($paginate === 'ajax') {
        $lists .= zib_get_ajax_next_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no', '.widget-ajaxpager');
    } elseif ($paginate === 'number') {
        $lists .= zib_get_ajax_number_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'paged', '.widget-ajaxpager');
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    if ($no_ajax) {
        return '<div class="widget-ajaxpager">' . $lists . '</div>';
    }
    zib_ajax_send_ajaxpager($lists, false, 'widget-ajaxpager');
}

//文章列表-新

function zib_widget_ui_tab_post_is_show($show_class, $args, $instance)
{
    if (empty($instance['tabs'])) {
        return false;
    }

    return $show_class;
}

function zib_widget_ui_tab_post($args, $instance)
{

    $style = $instance['style'] ? $instance['style'] : 'list';
    $class = 'widget-tab-post style-' . $style;
    $class .= $style == 'mini' ? ' posts-mini-lists zib-widget' : ' index-tab relative-h';

    $main_html = '';
    $widget_id = $args['widget_id'];
    $id_base   = 'zib_widget_ui_tab_post';
    $index     = str_replace($id_base . '-', '', $widget_id);

    $placeholder = ''; //
    if ($style == 'mini') {
        $placeholder = str_repeat('<div class="posts-mini"><div class="placeholder k1"></div></div>', $instance['count']);
        $mini_opt    = $instance['mini_opt'] ? $instance['mini_opt'] : array();
        if (in_array('show_thumb', $mini_opt)) {
            $placeholder = str_repeat('<div class="posts-mini "><div class="mr10"><div class="item-thumbnail placeholder"></div></div><div class="posts-mini-con flex xx flex1 jsb"><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div>', $instance['count']);
        }
    } else {
        $placeholder_type = $style == 'card' ? 'card' : 'lists';
        $placeholder      = zib_get_post_placeholder($placeholder_type, $instance['count']);
    }

    $tabs_con  = '';
    $tabs_nav  = '';
    $tabs_i    = 1;
    $tabs      = $instance['tabs'];
    $ajax_href = add_query_arg(array(
        'action' => 'ajax_widget_ui',
        'id'     => $id_base,
        'index'  => $index,
    ), admin_url('/admin-ajax.php'));

    foreach ($instance['tabs'] as $tabs_key => $tabs) {
        if (empty($tabs['title'])) {
            continue;
        }
        $tab_id    = $widget_id . '-' . $tabs_i;
        $nav_class = $tabs_i == 1 ? 'active' : '';
        $con_class = $tabs_i == 1 ? ' active in' : '';

        $con_html = '';
        if ($tabs_i == 1) {
            $con_html = zib_widget_ui_tab_post_ajax($instance, true, add_query_arg('tab', $tabs_key, $ajax_href), $tabs_key);
        } else {
            $con_html .= '<span class="post_ajax_trigger hide"><a ajaxpager-target=".widget-ajaxpager" href="' . add_query_arg('tab', $tabs_key, $ajax_href) . '" class="ajax_load ajax-next ajax-open" no-scroll="true"></a></span>';
        }
        $con_html .= '<div class="post_ajax_loader" style="display: none;">' . $placeholder . '</div>';

        $tabs_nav .= '<li class="' . $nav_class . '"><a' . ($tabs_i !== 1 ? ' data-ajax' : '') . ' data-toggle="tab" href="#' . $tab_id . '">' . $tabs['title'] . '</a></li>';
        $tabs_con .= '<div class="tab-pane fade' . $con_class . '" id="' . $tab_id . '"><div class="widget-ajaxpager">' . $con_html . '</div></div>';

        $tabs_i++;
    }

    if (!$tabs_nav) {
        return;
    }

    $main_html = '
        <div class="relative' . ($style == 'card' ? ' mb20' : '') . '">
        <ul class="list-inline scroll-x no-scrollbar' . ($style == 'mini' ? ' tab-nav-theme' : '') . '">
            ' . $tabs_nav . '
        </ul>
        </div>
        <div class="tab-content">
            ' . $tabs_con . '
        </div>';

    //开始输出
    echo '<div class="' . $class . '">';
    echo $main_html;
    echo '</div>';
}

function zib_widget_ui_tab_post_ajax($instance, $no_ajax = false, $ajax_url = null, $tab = 0)
{

    $paged      = zib_get_the_paged();
    $style      = $instance['style'] ? $instance['style'] : 'list';
    $paginate   = $instance['paginate'] ? $instance['paginate'] : '';
    $paged_size = $instance['count'];
    $ajax_url   = $ajax_url ?: zib_get_current_url();
    $tab        = $tab ? $tab : (isset($_REQUEST['tab']) ? (int) $_REQUEST['tab'] : 0);

    $posts_args = array(
        'cat'         => $instance['tabs'][$tab]['cat'] ?? '',
        'topics'      => $instance['tabs'][$tab]['topics'] ?? '',
        'zibpay_type' => isset($instance['tabs'][$tab]['zibpay_type']) ? $instance['tabs'][$tab]['zibpay_type'] : '',
        'orderby'     => $instance['tabs'][$tab]['orderby'] ?? '',
        'order'       => isset($instance['tabs'][$tab]['order']) && $instance['tabs'][$tab]['order'] === 'asc' ? 'ASC' : 'DESC',
        'count'       => $instance['count'] ?? 6,
        'limit_day'   => isset($instance['tabs'][$tab]['limit_day']) ? (int) $instance['tabs'][$tab]['limit_day'] : 0,
    );

    //不需要翻页
    if (!$paginate) {
        $posts_args['no_found_rows'] = true;
        $paged                       = 1;
    }

    $posts_query = zib_get_posts_query($posts_args);
    $lists       = '';
    $mini_number = $paged * $paged_size - $paged_size;

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()): $posts_query->the_post();
            if ($style == 'card') {
                $lists .= zib_posts_mian_list_card(array());
            } elseif ($style == 'mini') {
            $mini_opt = $instance['mini_opt'] ? $instance['mini_opt'] : array();

            $mini_args = array(
                'class'       => 'ajax-item',
                'show_thumb'  => in_array('show_thumb', $mini_opt),
                'show_meta'   => in_array('show_meta', $mini_opt),
                'show_number' => in_array('show_number', $mini_opt),
                'echo'        => false,
            );
            $mini_number++;
            $lists .= zib_posts_mini_while($mini_args, $mini_number);
        } else {
            $lists .= zib_posts_mian_list_list(array('is_mult_thumb' => 'disable', 'is_no_thumb' => 'disable'));
        }

        endwhile;
        wp_reset_query();
    }
    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null(__('暂无内容', 'zib_language'), 10);
    }

    //分页paginate
    if ($paginate === 'ajax') {
        $lists .= zib_get_ajax_next_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no', '.widget-ajaxpager');
    } elseif ($paginate === 'number') {
        $lists .= zib_get_ajax_number_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'paged', '.widget-ajaxpager');
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    if ($no_ajax) {
        return $lists;
    }
    zib_ajax_send_ajaxpager($lists, false, 'widget-ajaxpager');

}


//横向滚动文章(新)

function zib_widget_ui_oneline_posts($args, $instance)
{
    $style = $instance['style'] ? $instance['style'] : 'card';
    $class = 'widget-main-post mb20 style-' . $style;
    $posts_args = array(
        'cat'         => $instance['cat'],
        'topics'      => $instance['topics'],
        'zibpay_type' => $instance['zibpay_type'],
        'orderby'     => $instance['orderby'],
        'order'       => isset($instance['order']) && $instance['order'] === 'asc' ? 'ASC' : 'DESC',
        'count'       => $instance['count'],
        'limit_day'   => isset($instance['limit_day']) ? (int) $instance['limit_day'] : 0,
        'no_found_rows' => true,
    );

    $posts_query = zib_get_posts_query($posts_args);
    $lists = '';
    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()): $posts_query->the_post();
            if ($style == 'card') {
                $lists .= zib_posts_mian_list_card(array());
            } elseif ($style == 'mini') {

            $mini_args = array(
                'class'       => 'posts-item',
                'show_thumb'  => true,
                'show_meta'   => true,
                'show_number' => false,
                'echo'        => false,
            );
            $lists .= zib_posts_mini_while($mini_args);
        } else {
            $lists .= zib_posts_mian_list_list(array('is_mult_thumb' => 'disable', 'is_no_thumb' => 'disable'));
        }

        endwhile;
        wp_reset_query();
    }


    echo '<div class="' . $class . '">';
    echo '<div class="swiper-container swiper-scroll" data-slideClass="posts-item">';
    echo '<div class="swiper-wrapper">';

    echo $lists;
    echo '</div>';
    echo '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
    echo '</div>';
    echo '</div>';
}


add_action('after_setup_theme', 'zib_widget_register_cfs_posts');
function zib_widget_register_cfs_posts()
{
    Zib_CFSwidget::create('zib_widget_ui_term_card', array(
        'title'            => __('分类图文卡片', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'description'      => __('将分类、专题显示为图文卡片', 'zib_language'),
        'zib_animation_in' => false,
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
            array(
                'id'          => 'term_id',
                'title'       => __('添加分类、专题', 'zib_language'),
                'desc'        => __('选择并排序需要的分类、专题，如选择的分类(专题)下没有文章则不会显示', 'zib_language'),
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy' => array('topics', 'category'),
                    'orderby'  => 'taxonomy',
                ),
                'placeholder' => __('输入关键词以搜索分类或专题', 'zib_language'),
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
                'type'        => 'select',
            ),
            array(
                'title'   => __('卡片样式', 'zib_language'),
                'id'      => 'type',
                'type'    => 'radio',
                'default' => 'style-1',
                'options' => array(
                    'style-1' => __('简单样式', 'zib_language'),
                    'style-2' => __('样式二', 'zib_language'),
                    'style-3' => __('样式三', 'zib_language'),
                    'style-4' => __('样式四', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'height_scale',
                'title'   => __('卡片长宽比例', 'zib_language'),
                'default' => 60,
                'max'     => 300,
                'min'     => 20,
                'step'    => 5,
                'unit'    => '%',
                'type'    => 'spinner',
            ),
            array(
                'id'       => 'pc_row',
                'title'    => __('排列布局', 'zib_language'),
                'subtitle' => __('PC端单行排列数量', 'zib_language'),
                'default'  => 4,
                'class'    => 'button-mini',
                'default'  => 2,
                'options'  => array(
                    1  => __('1个', 'zib_language'),
                    2  => __('2个', 'zib_language'),
                    3  => __('3个', 'zib_language'),
                    4  => __('4个', 'zib_language'),
                    6  => __('6个', 'zib_language'),
                    12 => __('12个', 'zib_language'),
                ),
                'type'     => 'button_set',
            ),
            array(
                'id'       => 'm_row',
                'title'    => ' ',
                'subtitle' => __('移动端单行排列数量', 'zib_language'),
                'decs'     => __('请根据此模块放置位置的宽度合理调整单行数量，避免显示不佳', 'zib_language'),
                'default'  => 2,
                'class'    => 'compact button-mini',
                'default'  => 2,
                'options'  => array(
                    1  => __('1个', 'zib_language'),
                    2  => __('2个', 'zib_language'),
                    3  => __('3个', 'zib_language'),
                    4  => __('4个', 'zib_language'),
                    6  => __('6个', 'zib_language'),
                    12 => __('12个', 'zib_language'),
                ),
                'type'     => 'button_set',
            ),
            array(
                'id'      => 'mask_opacity',
                'title'   => __('遮罩透明度', 'zib_language'),
                'help'    => __('图片上显示的黑色遮罩层的透明度', 'zib_language'),
                'default' => 10,
                'max'     => 90,
                'min'     => 0,
                'step'    => 1,
                'unit'    => '%',
                'type'    => 'slider',
            ),
    
            array(
                'title'   => __('新窗口打开', 'zib_language'),
                'id'      => 'target_blank',
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_widget_ui_term_lists_card', array(
        'title'            => __('专题&分类聚合卡片', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('将分类、专题以及文字内容显示为卡片', 'zib_language'),
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
                'options' => array(
                    ''           => __('无动画', 'zib_language'),
                    'fade'       => __('淡入', 'zib_language'),
                    'slideup'    => __('从下往上滑出', 'zib_language'),
                    'slidedown'  => __('从上往下滑出', 'zib_language'),
                    'slideright' => __('从左往右滑出', 'zib_language'),
                    'slideleft'  => __('从右往左滑出', 'zib_language'),
                    'zoomin'     => __('由小变大', 'zib_language'),
                    'zoomout'    => __('由大变小', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('obs_animation', '!=', ''),
                'title'      => __('重复动画', 'zib_language'),
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
    
            array(
                'id'      => 'pc_row',
                'title'   => __('单行布局', 'zib_language'),
                'desc'    => __('请根据此模块放置位置的宽度合理调整单行数量', 'zib_language'),
                'default' => 2,
                'max'     => 2,
                'min'     => 1,
                'step'    => 1,
                'unit'    => __('个', 'zib_language'),
                'type'    => 'slider',
            ),
            array(
                'id'          => 'term_id',
                'title'       => __('添加分类、专题', 'zib_language'),
                'desc'        => __('选择并排序需要的分类、专题，如选择的分类(专题)下没有文章则不会显示', 'zib_language'),
                'options'     => 'categories',
                'query_args'  => array(
                    'taxonomy' => array('topics', 'category'),
                    'orderby'  => 'taxonomy',
                ),
                'placeholder' => __('输入关键词以搜索分类或专题', 'zib_language'),
                'ajax'        => true,
                'settings'    => array(
                    'min_length' => 2,
                ),
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
                'type'        => 'select',
            ),
            array(
                'dependency' => array('term_id', '!=', ''),
                'id'         => 'orderby',
                'default'    => 'modified',
                'title'      => __('排序方式', 'zib_language'),
                'type'       => 'select',
                'options'    => CFS_Module::posts_orderby(),
            ),
            array(
                'dependency' => array('term_id', '!=', ''),
                'id'         => 'order',
                'default'    => 'desc',
                'class'      => 'compact',
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'desc' => __('升序', 'zib_language'),
                    'asc'  => __('降序', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('term_id', '!=', ''),
                'id'         => 'count',
                'title'      => __('最大文章数量', 'zib_language'),
                'desc'       => '<div class="c-yellow">' . esc_html__('请确保所选的分类或专题内的文章数量均超过此数量，否则会出现布局错位的问题！', 'zib_language') . '</div>',
                'default'    => 4,
                'max'        => 20,
                'min'        => 1,
                'step'       => 1,
                'unit'       => __('篇', 'zib_language'),
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array('term_id', '!=', ''),
                'title'      => __('新窗口打开', 'zib_language'),
                'id'         => 'target_blank',
                'type'       => 'switcher',
                'default'    => false,
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_widget_ui_hot_posts', array(
        'title'       => __('热榜文章', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'size'        => 'mini',
        'description' => __('显示文章榜单排名，此模块适合放置在侧边栏或移动菜单内', 'zib_language'),
        'fields'      => array(
            array(
                'id'      => 'orderby',
                'default' => 'views',
                'title'   => __('榜单类型', 'zib_language'),
                'type'    => 'radio',
                'options' => array(
                    'views'         => __('热门榜单(按阅读量排序)', 'zib_language'),
                    'like'          => __('超赞榜单(按点赞量排序)', 'zib_language'),
                    'comment_count' => __('话题榜单(按评论量排序)', 'zib_language'),
                    'favorite'      => __('收藏榜单(按收藏量排序)', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'limit_day',
                'title'   => __('限制时间(最近X天)', 'zib_language'),
                'desc'    => __('设置多少天内发布的文章有效，为0则不限制时间', 'zib_language'),
                'default' => 0,
                'max'     => 999999,
                'min'     => 0,
                'step'    => 1,
                'unit'    => __('天', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'count',
                'title'   => __('最大显示数量', 'zib_language'),
                'default' => 6,
                'max'     => 20,
                'min'     => 1,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'title'   => __('新窗口打开', 'zib_language'),
                'id'      => 'target_blank',
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_widget_ui_posts_pay', array(
        'title'       => __('付费购买', 'zib_language'),
        'zib_title'   => false,
        'zib_affix'   => true,
        'zib_show'    => false,
        'size'        => 'mini',
        'description' => __('显示当前文章的付费购买模块，推荐放置在侧边栏', 'zib_language'),
        'reminder'    => __('只能添加到文章或帖子页面的侧边栏', 'zib_language'),
        'fields'      => array(
            array(
                'id'      => 'theme',
                'title'   => __('色彩主题', 'zib_language'),
                'class'   => 'skin-color',
                'default' => 'jb-red',
                'type'    => 'palette',
                'options' => array(
                    'jb-red'    => array('linear-gradient(135deg, #ffbeb4 10%, #f61a1a 100%)'),
                    'jb-yellow' => array('linear-gradient(135deg, #ffd6b2 10%, #ff651c 100%)'),
                    'jb-blue'   => array('linear-gradient(135deg, #b6e6ff 10%, #198aff 100%)'),
                    'jb-green'  => array('linear-gradient(135deg, #ccffcd 10%, #52bb51 100%)'),
                    'jb-purple' => array('linear-gradient(135deg, #fec2ff 10%, #d000de 100%)'),
                    'jb-vip1'   => array('linear-gradient(25deg, #eab869 10%, #fbecd4 60%, #ffe0ae 100%)'),
                    'jb-vip2'   => array('linear-gradient(317deg, #4d4c4c 30%, #878787 70%, #5f5c5c 100%)'),
                ),
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_widget_ui_main_post', array(
        'title'       => __('文章列表(新)', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('通过各种筛选、排序显示文章列表，支持多种显示模式', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('模块加载方式', 'zib_language'),
                'id'      => 'load_mode',
                'default' => 'ajax',
                'type'    => 'radio',
                'inline'  => true,
                'desc'    => __('ajax懒加载：当页面加载完后，根据用户需要在加载当前模块内容，可提高页面渲染效率', 'zib_language'),
                'options' => array(
                    'detail' => __('直接加载', 'zib_language'),
                    'ajax'   => __('ajax懒加载', 'zib_language'),
                ),
            ),
            array(
                'id'    => 'cat',
                'title' => __('分类限制', 'zib_language'),
                'type'  => 'text',
            ),
            array(
                'id'    => 'topics',
                'title' => __('专题限制', 'zib_language'),
                'desc'  => __('分类或专题限制请填写对应的ID，多个ID请用英文逗号隔开。如：1,2,3。支持负数进行排除，例如：-1,-2,-3。（在后台分类、专题列表中可查看ID）', 'zib_language'),
                'type'  => 'text',
            ),
            array(
                'title'       => __('商品类型筛选', 'zib_language'),
                'id'          => 'zibpay_type',
                'default'     => [],
                'inline'      => true,
                'type'        => 'checkbox',
                'placeholder' => __('不做其它筛选', 'zib_language'),
                'options'     => array(
                    '1' => __('付费阅读', 'zib_language'),
                    '2' => __('付费下载', 'zib_language'),
                    '5' => __('付费图片', 'zib_language'),
                    '6' => __('付费视频', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('发布时间限制', 'zib_language'),
                'desc'    => __('仅显示最近多少天发布的文章，为0则不限制', 'zib_language'),
                'id'      => 'limit_day',
                'class'   => '',
                'default' => 0,
                'min'     => 0,
                'step'    => 5,
                'unit'    => __('天', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'       => 'orderby',
                'type'     => 'select',
                'default'  => '',
                'title'    => __('排序方式', 'zib_language'),
                'subtitle' => '',
                'options'  => CFS_Module::posts_orderby(),
            ),
            array(
                'id'      => 'order',
                'default' => 'desc',
                'class'   => 'compact',
                'inline'  => true,
                'type'    => 'radio',
                'options' => array(
                    'desc' => __('升序', 'zib_language'),
                    'asc'  => __('降序', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('列表样式', 'zib_language'),
                'id'      => 'style',
                'default' => 'list',
                'type'    => 'radio',
                'desc'    => '<div class="c-yellow">' . esc_html__('注意：不同样式尺寸不同，请根据放置的位置合理选择。例如：放在宽度较小的侧边栏，则需选择mini样式，否则显示效果不佳', 'zib_language') . '</div>',
                'inline'  => true,
                'options' => array(
                    'list' => __('列表样式', 'zib_language'),
                    'card' => __('卡片样式', 'zib_language'),
                    'mini' => __('mini列表', 'zib_language'),
                ),
            ),
            array(
                'dependency'  => array('style', '==', 'mini'),
                'title'       => __('mini列表配置', 'zib_language'),
                'id'          => 'mini_opt',
                'default'     => [],
                'inline'      => true,
                'type'        => 'checkbox',
                'placeholder' => __('不做其它筛选', 'zib_language'),
                'options'     => array(
                    'show_thumb'  => __('显示缩略图', 'zib_language'),
                    'show_number' => __('显示编号（开启翻页后，只在第一页有效）', 'zib_language'),
                    'show_meta'   => __('显示作者、时间、点赞等信息', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'count',
                'class'   => '',
                'default' => 12,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'paginate',
                'title'   => __('翻页按钮', 'zib_language'),
                'default' => '',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    ''       => __('不翻页', 'zib_language'),
                    'ajax'   => __('AJAX追加列表翻页', 'zib_language'),
                    'number' => __('数字翻页按钮', 'zib_language'),
                ),
            ),
        ),
    ));
    
    Zib_CFSwidget::create('zib_widget_ui_tab_post', array(
        'title'       => __('多栏目文章(新)', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('多个TAB栏目切换显示文章，支持各种筛选、排序、多种显示模式、翻页等功能', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('列表样式', 'zib_language'),
                'id'      => 'style',
                'default' => 'mini',
                'type'    => 'radio',
                'inline'  => true,
                'desc'    => '<div class="c-yellow">' . esc_html__('注意：不同样式尺寸不同，请根据放置的位置合理选择。例如：放在宽度较小的侧边栏，则需选择mini样式，否则显示效果不佳', 'zib_language') . '</div>',
                'options' => array(
                    'list' => __('列表样式', 'zib_language'),
                    'card' => __('卡片样式', 'zib_language'),
                    'mini' => __('mini列表', 'zib_language'),
                ),
            ),
            array(
                'dependency'  => array('style', '==', 'mini'),
                'title'       => __('mini列表配置', 'zib_language'),
                'id'          => 'mini_opt',
                'default'     => [],
                'inline'      => true,
                'type'        => 'checkbox',
                'placeholder' => __('不做其它筛选', 'zib_language'),
                'options'     => array(
                    'show_thumb'  => __('显示缩略图', 'zib_language'),
                    'show_number' => __('显示编号（开启翻页后，只在第一页有效）', 'zib_language'),
                    'show_meta'   => __('显示作者、时间、点赞等信息', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'count',
                'class'   => '',
                'default' => 6,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'paginate',
                'title'   => __('翻页按钮', 'zib_language'),
                'default' => '',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    ''       => __('不翻页', 'zib_language'),
                    'ajax'   => __('AJAX追加列表翻页', 'zib_language'),
                    'number' => __('数字翻页按钮', 'zib_language'),
                ),
            ),
            array(
                'id'                     => 'tabs',
                'type'                   => 'group',
                'accordion_title_number' => true,
                'button_title'           => __('添加栏目', 'zib_language'),
                'sanitize'               => false,
                'title'                  => __('栏目', 'zib_language'),
                'default'                => array(
                    array(
                        'title'   => '热门推荐',
                        'orderby' => 'views',
                    ),
                    array(
                        'title'   => '最近更新',
                        'orderby' => 'modified',
                    ),
                    array(
                        'title'   => '猜你喜欢',
                        'orderby' => 'rand',
                    ),
                ),
                'fields'                 => array(
                    array(
                        'id'         => 'title',
                        'title'      => __('标题（必填）', 'zib_language'),
                        'desc'       => __('栏目显示的标题，支持HTML代码，注意代码规范', 'zib_language'),
                        'attributes' => array(
                            'rows' => 1,
                        ),
                        'sanitize'   => false,
                        'type'       => 'textarea',
                    ),
                    array(
                        'id'    => 'cat',
                        'title' => __('分类限制', 'zib_language'),
                        'type'  => 'text',
                    ),
                    array(
                        'id'    => 'topics',
                        'title' => __('专题限制', 'zib_language'),
                        'desc'  => __('分类或专题限制请填写对应的ID，多个ID请用英文逗号隔开。如：1,2,3。支持负数进行排除，例如：-1,-2,-3。（在后台分类、专题列表中可查看ID）', 'zib_language'),
                        'type'  => 'text',
                    ),
                    array(
                        'title'       => __('商品类型筛选', 'zib_language'),
                        'id'          => 'zibpay_type',
                        'default'     => [],
                        'inline'      => true,
                        'type'        => 'checkbox',
                        'placeholder' => __('不做其它筛选', 'zib_language'),
                        'options'     => array(
                            '1' => __('付费阅读', 'zib_language'),
                            '2' => __('付费下载', 'zib_language'),
                            '5' => __('付费图片', 'zib_language'),
                            '6' => __('付费视频', 'zib_language'),
                        ),
                    ),
                    array(
                        'title'   => __('发布时间限制', 'zib_language'),
                        'desc'    => __('仅显示最近多少天发布的文章，为0则不限制', 'zib_language'),
                        'id'      => 'limit_day',
                        'class'   => '',
                        'default' => 0,
                        'min'     => 0,
                        'step'    => 5,
                        'unit'    => __('天', 'zib_language'),
                        'type'    => 'spinner',
                    ),
                    array(
                        'id'       => 'orderby',
                        'type'     => 'select',
                        'default'  => '',
                        'title'    => __('排序方式', 'zib_language'),
                        'subtitle' => '',
                        'options'  => CFS_Module::posts_orderby(),
                    ),
                    array(
                        'id'      => 'order',
                        'default' => 'desc',
                        'class'   => 'compact',
                        'inline'  => true,
                        'type'    => 'radio',
                        'options' => array(
                            'desc' => __('升序', 'zib_language'),
                            'asc'  => __('降序', 'zib_language'),
                        ),
                    ),
                ),
            ),
        ),
    ));
    
    Zib_CFSwidget::create('widget_ui_oneline_posts', array(
        'title'       => __('横向滚动文章(新)', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'callback'    => 'zib_widget_ui_oneline_posts',
        'description' => __('显示文章列表，只显示一行，左右滚动', 'zib_language'),
        'fields'      => array(
            array(
                'id'    => 'cat',
                'title' => __('分类限制', 'zib_language'),
                'type'  => 'text',
            ),
            array(
                'id'    => 'topics',
                'title' => __('专题限制', 'zib_language'),
                'desc'  => __('分类或专题限制请填写对应的ID，多个ID请用英文逗号隔开。如：1,2,3。支持负数进行排除，例如：-1,-2,-3。（在后台分类、专题列表中可查看ID）', 'zib_language'),
                'type'  => 'text',
            ),
            array(
                'title'       => __('商品类型筛选', 'zib_language'),
                'id'          => 'zibpay_type',
                'default'     => [],
                'inline'      => true,
                'type'        => 'checkbox',
                'placeholder' => __('不做其它筛选', 'zib_language'),
                'options'     => array(
                    '1' => __('付费阅读', 'zib_language'),
                    '2' => __('付费下载', 'zib_language'),
                    '5' => __('付费图片', 'zib_language'),
                    '6' => __('付费视频', 'zib_language'),
                ),
            ),
            array(
                'id'       => 'orderby',
                'type'     => 'select',
                'default'  => '',
                'title'    => __('排序方式', 'zib_language'),
                'subtitle' => '',
                'options'  => CFS_Module::posts_orderby(),
            ),
            array(
                'id'      => 'order',
                'default' => 'desc',
                'class'   => 'compact',
                'inline'  => true,
                'type'    => 'radio',
                'options' => array(
                    'desc' => __('升序', 'zib_language'),
                    'asc'  => __('降序', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('列表样式', 'zib_language'),
                'id'      => 'style',
                'default' => 'card',
                'type'    => 'radio',
                'desc'    => '<div class="c-yellow">' . esc_html__('注意：不同样式尺寸不同，请根据放置的位置合理选择。例如：放在宽度较小的侧边栏，则需选择mini样式，否则显示效果不佳', 'zib_language') . '</div>',
                'inline'  => true,
                'options' => array(
                    'list' => __('列表样式', 'zib_language'),
                    'card' => __('卡片样式', 'zib_language'),
                    'mini' => __('mini列表', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('显示数量', 'zib_language'),
                'id'      => 'count',
                'class'   => '',
                'default' => 12,
                'max'     => 20,
                'min'     => 4,
                'step'    => 1,
                'unit'    => __('篇', 'zib_language'),
                'type'    => 'spinner',
            ),
        )
    ));
}
