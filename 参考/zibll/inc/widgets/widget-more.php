<?php

add_action('widgets_init', 'widget_register_more');
function widget_register_more()
{
    register_widget('widget_ui_yiyan');
    register_widget('widget_ui_posts_navs');
    //   register_widget('widget_ui_notice');
    register_widget('widget_ui_search');
}

/**
 *搜索小工具
 */
class widget_ui_search extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_search',
            'w_name'      => _name(__('搜索框', 'zib_language')),
            'classname'   => '',
            'description' => __('显示一个搜索框，多种显示效果', 'zib_language'),
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
            'title'            => __('搜索', 'zib_language'),
            'mini_title'       => '',

            'more_but'         => '',
            'more_but_url'     => '',
            'in_affix'         => '',

            'show_history'     => '',

            'class'            => '',
            'show_keywords'    => '',
            'keywords_title'   => __('热门搜索', 'zib_language'),
            'placeholder'      => __('开启精彩搜索', 'zib_language'),
            'show_input_cat'   => '',
            'show_more_cat'    => '',
            'in_cat'           => '',
            'more_cats'        => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $attr  = '';
        $class = 'theme-box';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';

        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';

        $title = apply_filters('zib_widget_title', $instance);
        echo $title;
        echo '<div class="zib-widget widget-search">';

        $args = array(
            'class'          => '',
            'show_keywords'  => $instance['show_keywords'],
            'show_history'   => $instance['show_history'],
            'keywords_title' => $instance['keywords_title'],
            'placeholder'    => $instance['placeholder'],
            'show_input_cat' => $instance['show_input_cat'],
            'show_more_cat'  => $instance['show_more_cat'],
            'in_cat'         => $instance['in_cat'],
        );
        if ($instance['more_cats']) {
            $args['more_cats'] = preg_split("/,|，|\s|\n/", $instance['more_cats']);
        }
        zib_get_search_box($args, true);

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
            'title'            => __('搜索', 'zib_language'),
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'more_but_url'     => '',

            'class'            => '',
            'show_history'     => '',
            'show_keywords'    => '',
            'keywords_title'   => __('热门搜索', 'zib_language'),
            'placeholder'      => __('开启精彩搜索', 'zib_language'),
            'show_input_cat'   => '',
            'show_more_cat'    => '',
            'in_cat'           => '',
            'in_affix'         => '',
            'more_cats'        => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance   = wp_parse_args((array) $instance, $defaults);
        $page_input = array();

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

        $page_input[] = array(
            'name'    => __('入场动画', 'zib_language'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zib_language'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> <?php esc_html_e('侧栏随动（仅在侧边栏有效）', 'zib_language'); ?>
            </label>
        </p>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_keywords'], 'on'); ?> id="<?php echo $this->get_field_id('show_keywords'); ?>" name="<?php echo $this->get_field_name('show_keywords'); ?>"> <?php esc_html_e('显示热门搜索关键词', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_history'], 'on'); ?> id="<?php echo $this->get_field_id('show_history'); ?>" name="<?php echo $this->get_field_name('show_history'); ?>"> <?php esc_html_e('显示用户搜索历史', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <?php esc_html_e('热门搜索-标题：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('keywords_title');
        ?>" name="<?php echo $this->get_field_name('keywords_title');
        ?>" type="text" value="<?php echo $instance['keywords_title'];
        ?>" />
            </label>
        </p>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_input_cat'], 'on'); ?> id="<?php echo $this->get_field_id('show_input_cat'); ?>" name="<?php echo $this->get_field_name('show_input_cat'); ?>"> <?php esc_html_e('显示分类', 'zib_language'); ?>
            </label>
        </p>

        <p>
            <label>
                <?php esc_html_e('默认已选择的分类：', 'zib_language'); ?>
                <select style="width:100%;" name="<?php echo $this->get_field_name('in_cat'); ?>">
                    <?php echo zib_widget_option('cat', $instance['in_cat']); ?>
                </select>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_more_cat'], 'on'); ?> id="<?php echo $this->get_field_id('show_more_cat'); ?>" name="<?php echo $this->get_field_name('show_more_cat'); ?>"> <?php esc_html_e('显示更多分类选择框', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <?php esc_html_e('更多分类的ID（默认为全部分类，如需自定义则将分类的ID填入下方，多个ID用逗号隔开）：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('more_cats');
        ?>" name="<?php echo $this->get_field_name('more_cats');
        ?>" type="text" value="<?php echo $instance['more_cats'];
        ?>" />
            </label>
        </p>
    <?php
}
}

////---------公告栏--------、、、、、、、
class widget_ui_notice extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_notice',
            'w_name'      => _name(__('滚动公告', 'zib_language')),
            'in_affix'    => '',
            'classname'   => '',
            'description' => __('可做公告栏或者其他滚动显示内容', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function form($instance)
    {
        $defaults = array(
            'blank'            => '',
            'alignment'        => '',
            'radius'           => '',
            'null'             => '',
            'in_affix'         => '',
            'color'            => 'c-blue',
            'img_ids'          => array(),
            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $defaults['img_ids'][] = array(
            'title' => __('子比主题，更优雅的Wordpress主题', 'zib_language'),
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $defaults['img_ids'][] = array(
            'title' => __('更优雅的WordPress网站主题：子比主题！全面开启', 'zib_language'),
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $img_html = '';
        $img_i    = 0;

        foreach ($instance['img_ids'] as $category) {
            /* translators: 1: Message index number, 2: Message title text. */
            $_tt     = '<div class="panel"><h4 class="panel-title">' . sprintf(__('消息 %1$d：%2$s', 'zib_language'), (int) ($img_i + 1), esc_html($instance['img_ids'][$img_i]['title'])) . '</h4><div class="panel-hide panel-conter">';
            $_html_a = '<label>' . sprintf(esc_html__('消息 %d-内容（必填）：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].title" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][title]" value="' . esc_attr($instance['img_ids'][$img_i]['title']) . '" /></label>';
            $_html_b = '<label>' . sprintf(esc_html__('消息 %d-图标（填写FA图标class）：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].icon" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][icon]" value="' . esc_attr($instance['img_ids'][$img_i]['icon']) . '" /></label>';
            $_html_b .= '<label>' . sprintf(esc_html__('消息 %d-链接：', 'zib_language'), (int) ($img_i + 1)) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].href" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][href]" value="' . esc_attr($instance['img_ids'][$img_i]['href']) . '" /></label>';

            $_tt2 = '</div></div>';
            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_tt2 . '</div>';
            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . $this->get_field_name('img_ids') . '" data-count="' . $img_i . '" class="button add_button add_notice_button">' . esc_html__('添加栏目', 'zib_language') . '</button>';
        $add_b .= '<button type="button" data-name="' . $this->get_field_name('img_ids') . '" data-count="' . $img_i . '" class="button rem_lists_button">' . esc_html__('删除栏目', 'zib_language') . '</button>';
        $img_html .= $add_b;
        //echo '<pre>' . json_encode($instance) . '</pre>';
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        ?>
        <p>
            <?php esc_html_e('显示一个公告栏，多个消息滚动显示,请注意控制长度，否则在移动端显示不全', 'zib_language'); ?>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> <?php esc_html_e('侧栏随动（仅在侧边栏有效）', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['blank'], 'on'); ?> id="<?php echo $this->get_field_id('blank'); ?>" name="<?php echo $this->get_field_name('blank'); ?>"> <?php esc_html_e('链接新窗口打开', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['radius'], 'on'); ?> id="<?php echo $this->get_field_id('radius'); ?>" name="<?php echo $this->get_field_name('radius'); ?>"> <?php esc_html_e('两边显示为圆形', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <?php esc_html_e('主题色彩：', 'zib_language'); ?>
                <select style="width:100%;" name="<?php echo $this->get_field_name('color'); ?>">
                    <option value="c-red" <?php selected('c-red', $instance['color']); ?>><?php esc_html_e('透明粉红', 'zib_language'); ?></option>
                    <option value="c-yellow" <?php selected('c-yellow', $instance['color']); ?>><?php esc_html_e('透明黄', 'zib_language'); ?></option>
                    <option value="c-blue" <?php selected('c-blue', $instance['color']); ?>><?php esc_html_e('透明蓝', 'zib_language'); ?></option>
                    <option value="c-green" <?php selected('c-green', $instance['color']); ?>><?php esc_html_e('透明绿', 'zib_language'); ?></option>
                    <option value="c-purple" <?php selected('c-purple', $instance['color']); ?>><?php esc_html_e('透明紫', 'zib_language'); ?></option>
                    <option value="c-red-2" <?php selected('c-red', $instance['color']); ?>><?php esc_html_e('透明红', 'zib_language'); ?></option>
                    <option value="c-yellow-2" <?php selected('c-yellow', $instance['color']); ?>><?php esc_html_e('透明橘黄', 'zib_language'); ?></option>
                    <option value="c-blue-2" <?php selected('c-blue', $instance['color']); ?>><?php esc_html_e('透明深蓝', 'zib_language'); ?></option>
                    <option value="c-green-2" <?php selected('c-green', $instance['color']); ?>><?php esc_html_e('透明墨绿', 'zib_language'); ?></option>
                    <option value="c-purple-2" <?php selected('c-purple', $instance['color']); ?>><?php esc_html_e('透明深紫', 'zib_language'); ?></option>
                    <option value="b-theme sbg" <?php selected('b-theme', $instance['color']); ?>><?php esc_html_e('主题色', 'zib_language'); ?></option>
                    <option value="b-red sbg" <?php selected('b-red', $instance['color']); ?>><?php esc_html_e('红色', 'zib_language'); ?></option>
                    <option value="b-yellow sbg" <?php selected('b-yellow', $instance['color']); ?>><?php esc_html_e('黄色', 'zib_language'); ?></option>
                    <option value="b-blue sbg" <?php selected('b-blue', $instance['color']); ?>><?php esc_html_e('蓝色', 'zib_language'); ?></option>
                    <option value="b-green sbg" <?php selected('b-green', $instance['color']); ?>><?php esc_html_e('绿色', 'zib_language'); ?></option>
                    <option value="b-purple sbg" <?php selected('b-purple', $instance['color']); ?>><?php esc_html_e('紫色', 'zib_language'); ?></option>
                </select>
            </label>
        </p>
        <p>
            <label>
                <?php esc_html_e('对齐方式：', 'zib_language'); ?>
                <select style="width:100%;" name="<?php echo $this->get_field_name('alignment'); ?>">
                    <option value="" <?php selected('', $instance['alignment']); ?>><?php esc_html_e('靠左', 'zib_language'); ?></option>
                    <option value="text-center" <?php selected('text-center', $instance['alignment']); ?>><?php esc_html_e('居中', 'zib_language'); ?></option>
                    <option value="text-right" <?php selected('text-right', $instance['alignment']); ?>><?php esc_html_e('靠右', 'zib_language'); ?></option>
                </select>
            </label>
        </p>
        <div class="widget_ui_slider_lists">
            <?php echo $img_html; ?>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox hide" type="checkbox" <?php checked($instance['null'], 'on'); ?> id="<?php echo $this->get_field_id('null'); ?>" name="<?php echo $this->get_field_name('null'); ?>"><a class="button ok_button"><?php esc_html_e('应用', 'zib_language'); ?></a>
            </label>
        </div>
        <?php wp_enqueue_media(); ?>
    <?php
}

    public function widget($args, $instance)
    {

        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);

        $defaults = array(
            'blank'     => '',
            'alignment' => '',
            'radius'    => '',
            'null'      => '',
            'in_affix'  => '',
            'color'     => 'c-blue',
            'img_ids'   => array(),
        );

        $defaults['img_ids'][] = array(
            'title' => __('子比主题开始公测啦！正版授权，限时免费！', 'zib_language'),
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $defaults['img_ids'][] = array(
            'title' => __('更优雅的WordPress网站主题：子比主题！全面开启', 'zib_language'),
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $links = array(
            'class' => $instance['alignment'] . ' ' . $instance['color'] . ($instance['radius'] ? ' radius' : ' radius8'),
        );
        foreach ($instance['img_ids'] as $slide_img) {
            if ($slide_img['title']) {
                $slide = array(
                    'title' => $slide_img['title'],
                    'href'  => $slide_img['href'],
                    'blank' => $instance['blank'],
                    'icon'  => $slide_img['icon'],
                );
                $links['notice'][] = $slide;
            }
        }
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        echo '<div' . $in_affix . ' class="theme-box">';
        zib_notice($links);
        echo '</div>';
        echo '</div>';
        if ($is_preview) {
            echo '</div>';
        }

        //echo '<pre>'.json_encode($instance).'</pre>';
        ?>

    <?php
}
}

class widget_ui_posts_navs extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_posts_navs',
            'w_name'      => _name(__('文章目录树', 'zib_language')),
            'classname'   => '',
            'description' => __('显示文章的目录树，非文章、帖子页则不显示内容，同时标题超过3个才会显示', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }

    public function widget($args, $instance)
    {

        extract($args);
        $defaults = array(
            'title'      => '',
            'mini_title' => '',
            'in_affix'   => '',
        );
        $instance   = wp_parse_args((array) $instance, $defaults);
        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title = esc_html($instance['title']) . esc_html($mini_title);
        if ($title) {
            $title = ' data-title="' . $title . '"';
        }
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        echo '<div' . $in_affix . ' class="posts-nav-box"' . $title . '></div>';
        echo '</div>';
        if ($is_preview) {
            echo '</div>';
        }
    }

    public function form($instance)
    {
        $defaults = array(
            'title'      => __('文章目录', 'zib_language'),
            'in_affix'   => '',
            'mini_title' => '',
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        ?>
        <p>
            <label>
                <i style="width:100%;color:#f80;"><?php esc_html_e('显示文章的目录，添加在非文章/帖子页则不会显示任何内容。在实时预览添加此模块时，请注意查看是否在文章页。内容中标题超过3个才会显示', 'zib_language'); ?></i>
            </label>
        </p>
        <p>
            <label>
                <?php esc_html_e('标题：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('title');
        ?>" name="<?php echo $this->get_field_name('title');
        ?>" type="text" value="<?php echo $instance['title'];
        ?>" />
            </label>
        </p>
        <p>
            <label>
                <?php esc_html_e('副标题：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('mini_title');
        ?>" name="<?php echo $this->get_field_name('mini_title');
        ?>" type="text" value="<?php echo $instance['mini_title'];
        ?>" />
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> <?php esc_html_e('侧栏随动（仅在侧边栏有效）', 'zib_language'); ?>
            </label>
        </p>
<?php
}
}

/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
class widget_ui_yiyan extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_yiyan',
            'w_name'      => _name(__('一言', 'zib_language')),
            'classname'   => 'yiyan-box main-bg theme-box text-center box-body radius8 main-shadow',
            'description' => __('这是一个显示一言的小工具，每次页面刷新或者每隔30秒会自动更新内容', 'zib_language'),
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
            'title'            => '',
            'mini_title'       => '',
            'in_affix'         => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        $title    = apply_filters('zib_widget_title', $instance);

        $attr  = '';
        $class = 'theme-box';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        //如果是预览模式
        $is_preview = is_customize_preview();
        if ($is_preview) {
            echo '<div class="customize-preview-widget-box" id="' . ($args['widget_id'] ?? '') . '">';
            echo '<div class="customize-preview-widget-edit">' . esc_html__('编辑', 'zib_language') . '</div>';
        }
        echo '<div class="widget-container">';
        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';
        echo $title;
        echo '<div class="yiyan-box main-bg text-center box-body radius8 main-shadow">';
        echo '<div class="yiyan"></div>';
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
            'title'            => '',
            'mini_title'       => '',
            'in_affix'         => '',
            'more_but_url'     => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>' . __('更多', 'zib_language'),
            'obs_animation'    => '',
            'animation_repeat' => false,
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
        $page_input[] = array(
            'id'    => $this->get_field_name('in_affix'),
            'std'   => $instance['in_affix'],
            'desc'  => __('侧栏随动（仅在侧边栏有效）', 'zib_language'),
            'style' => 'margin: 15px auto;',
            'type'  => 'checkbox',
        );
        $page_input[] = array(
            'name'    => __('入场动画', 'zib_language'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => __('页面滚动到此模块时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zib_language'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => __('每次滚动到此模块都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));

        echo zib_edit_input_construct($page_input);
    }
}

//图标卡片

function zib_widget_ui_icon_card_is_show($show_class, $args, $instance)
{
    if (empty($instance['cards'][0])) {
        return false;
    }

    return $show_class;
}

//图标卡片
function zib_widget_ui_icon_card($args, $instance)
{

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $cards  = $instance['cards'];
    $is_row = count($cards) > 1;
    $html   = '';
    if ($cards) {
        foreach ($cards as $card) {
            $html .= $is_row ? '<div class="' . $row_class . '">' : '';
            $card['icon_size'] = isset($instance['size']) && 0 != $instance['size'] ? (16 + (int) $instance['size']) : '';
            if (!$instance['show_widget_bg']) {
                $card['class'] = 'mb20';
            }
            $html .= zib_icon_card($card);
            $html .= $is_row ? '</div>' : '';
        }
    }

    echo '<div class="clearfix">';
    echo $instance['show_widget_bg'] ? '<div class="zib-widget nobottom notop">' : '';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo $instance['show_widget_bg'] ? '</div>' : '';
    echo '</div>';
}

//视频

function zib_widget_ui_dplayer_is_show($show_class, $args, $instance)
{
    if (empty($instance['url'])) {
        return false;
    }

    return $show_class;
}

//视频
function zib_widget_ui_dplayer($args, $instance)
{

    $args = array(
        'class'        => '',
        'url'          => $instance['url'],
        'pic'          => $instance['pic'],
        'autoplay'     => $instance['autoplay'],
        'loop'         => $instance['loop'],
        'scale_height' => $instance['scale_height'],
        'volume'       => round(($instance['volume'] / 100), 2),
    );
    $dplayer = zib_new_dplayer($args, false);

    echo '<div class="relative-h radius8 mb20' . (!empty($instance['hide_controller']) ? ' controller-hide' : '') . '">';
    echo $dplayer;
    echo '</div>';
}

//超级嵌入

function zib_widget_ui_iframe_is_show($show_class, $args, $instance)
{
    if (empty($instance['url'])) {
        return false;
    }

    return $show_class;
}

//超级嵌入
function zib_widget_ui_iframe($args, $instance)
{

    $url = $instance['url'];
    if (stristr($url, '<iframe') && stristr($url, '</iframe>')) {
        $iframe = $url;
    } else {
        $iframe = '<iframe class="lazyload"' . (!empty($instance['allowfullscreen']) ? ' allowfullscreen="allowfullscreen"' : '') . ' framespacing="0" border="0" frameborder="no" data-src="' . esc_url($url) . '"></iframe>';
    }

    echo '<div class="mb20">';
    echo '<div class="wp-block-embed is-type-video relative-h radius8">';
    echo '<div style="padding-bottom:' . $instance['aspect'] . '%">';
    echo $iframe;
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

//图文封面

function zib_widget_ui_graphic_cover_is_show($show_class, $args, $instance)
{
    if (empty($instance['covers'][0]['image']) && empty($instance['covers'][0]['video'])) {
        return false;
    }

    return $show_class;
}

function zib_widget_ui_graphic_cover($args, $instance)
{
    $defaults = array(
        'pc_row'           => 4,
        'm_row'            => 1,
        'font_size_pc'     => 18,
        'font_size_m'      => 14,
        'font_bold'        => false,
        'font_color'       => '',
        'covers'           => array(),
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $is_row = count($instance['covers']) > 1;
    $html   = '';
    $style  = '';
    $style .= $instance['font_size_pc'] && 14 != $instance['font_size_pc'] ? '--font-size:' . ((int) $instance['font_size_pc']) . 'px;' : '';
    $style .= $instance['font_size_m'] && 14 != $instance['font_size_m'] ? '--font-size-sm:' . ((int) $instance['font_size_m']) . 'px;' : '';
    $style .= $instance['font_bold'] ? '--font-weight:bold;--font-weight-sm:bold;' : '';
    $style .= $instance['font_color'] ? '--color:' . $instance['font_color'] . ';--color-sm:' . $instance['font_color'] . ';' : '';
    $style = $style ? ' style="' . $style . '"' : '';

    $animation_class = Zib_CFSwidget::animation_class($instance, true);
    foreach ($instance['covers'] as $key => $cover) {

        //显示规则
        if (isset($cover['hide'])) {
            $is_mobile = wp_is_mobile();
            if ((!$is_mobile && $cover['hide'] === 'pc') || ($is_mobile && $cover['hide'] === 'm')) {
                continue;
            }
        }

        $more = $cover['title'] ? '<div class="abs-center text-center graphic-text this-font">' . $cover['title'] . '</div>' : '';
        $card = array(
            'class'        => 'noshadow mb10',
            'img'          => isset($cover['image']) ? $cover['image'] : '',
            'video'        => isset($cover['video']) ? $cover['video'] : '',
            'alt'          => strip_tags($cover['title']),
            'link'         => $cover['link'],
            'lazy'         => zib_is_lazy('lazy_cover'),
            'more'         => $more,
            'height_scale' => $instance['height_scale'],
            'mask_opacity' => $instance['mask_opacity'],
        );

        $attr      = '';
        $col_class = $animation_class;
        if ($is_row) {
            $col_class .= ' ' . $row_class;
        }

        $html .= '<div class="' . $col_class . '" ' . $attr . '>';
        $html .= zib_graphic_card($card);
        $html .= '</div>';
    }

    echo '<div class="widget-graphic-cover ' . ($is_row ? 'mb10' : 'mb20') . '">';
    echo '<div' . $style . '>';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo '</div>';

    echo '</div>';
}

//图标卡片

function zib_widget_ui_icon_cover_card_is_show($show_class, $args, $instance)
{
    if (empty($instance['cards'][0])) {
        return false;
    }

    return $show_class;
}
function zib_widget_ui_icon_cover_card($args, $instance)
{
    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $cards  = $instance['cards'];
    $is_row = count($cards) > 1;
    $html   = '';

    if ($cards) {
        foreach ($cards as $card) {
            $html .= $is_row ? '<div class="' . $row_class . '">' : '';
            $card['class'] = $instance['show_widget_bg'] ? 'padding-10' : 'zib-widget mb10';
            $card['icon_class'] .= $instance['icon_radius4'] ? ' radius4' : '';
            $html .= zib_icon_cover_card($card);
            $html .= $is_row ? '</div>' : '';
        }
    }

    echo $instance['show_widget_bg'] ? '<div class="zib-widget padding-10">' : '<div class="mb10">';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo '</div>';
}

//横向滚动的合作伙伴模块

/**
 * 横向滚动合作伙伴 - 渲染函数
 * 布局：将所有合作伙伴均匀分配到若干行；每行 track 复制一份以保证无缝循环
 */
function zib_widget_ui_partners_scroll($args, $instance)
{

    //合并默认值，避免新老数据结构差异导致 notice
    $defaults = array(
        'rows'           => 1,
        'm_rows'         => 1,
        'style_type'     => 'card',
        'card_width'     => 220,
        'card_height'    => 90,
        'speed'          => 3,
        'direction'      => 'alternate',
        'pause_hover'    => true,
        'show_widget_bg' => true,
        'card_bg'        => true,
        'card_radius'    => 10,
        'partners'       => array(),
        'link_type'      => 'link',
        'link_orderby'   => 'name',
        'link_order'     => 'ASC',
        'link_cats'      => array(),
        'link_limit'     => 50,
        'go_link'        => true,
        'end_fade'       => true,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    $is_mobile = wp_is_mobile();
    $rows      = max(1, (int) ($is_mobile ? $instance['m_rows'] : $instance['rows']));

    //通过链接获取合作伙伴
    if ($instance['link_type'] === 'link') {
        $args = array(
            'orderby' => $instance['link_orderby'] ? $instance['link_orderby'] : 'name', //排序方式
            'order'   => $instance['link_order'] ? $instance['link_order'] : 'ASC', //升序还是降序
            'limit'   => $instance['link_limit'] ? (int) $instance['link_limit'] : -1, //最多显示数量
        );

        if ($instance['link_cats']) {
            $args['category'] = $instance['link_cats'];
        }

        $partners  = array();
        $bookmarks = get_bookmarks($args);
        foreach ($bookmarks as $bookmark) {
            $partners[] = array(
                'title' => $bookmark->link_name,
                'desc'  => $bookmark->link_description,
                'image' => $bookmark->link_image,
                'link'  => [
                    'url'    => $bookmark->link_url,
                    'target' => $bookmark->link_target,
                ],
            );
        }
        $instance['partners'] = $partners;
    }

    if (empty($instance['partners'])) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . esc_html__('[合作伙伴]横向滚动合作伙伴模块：', 'zib_language') . '</b>' . esc_html__('当前配置下没有可显示的内容，请正确配置或移出该模块', 'zib_language') . '</div>';
        }
        return;
    }

    $partners = array_values($instance['partners']);
    if (empty($partners)) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . esc_html__('[合作伙伴]横向滚动合作伙伴模块：', 'zib_language') . '</b>' . esc_html__('当前配置下没有可显示的内容，请正确配置或移出该模块', 'zib_language') . '</div>';
        }
        return;
    }

    //将合作伙伴平均分配到每一行
    $rows_data = array_fill(0, $rows, array());
    foreach ($partners as $i => $partner) {
        $rows_data[$i % $rows][] = $partner;
    }

    //过滤掉空行
    $rows_data = array_values(array_filter($rows_data, function ($row) {
        return !empty($row);
    }));

    //模块外层样式变量
    $style_vars = array(
        '--zps-card-w:' . (int) $instance['card_width'] . 'px',
        '--zps-card-h:' . (int) $instance['card_height'] . 'px',
        '--zps-card-radius:' . (int) $instance['card_radius'] . 'px',
        '--zps-speed:' . max(1, (int) $instance['speed']),
    );
    $style_attr = ' style="' . esc_attr(implode(';', $style_vars)) . '"';

    $wrap_class = 'zib-partners-scroll';
    $wrap_class .= ' zps-style-' . $instance['style_type'];
    $wrap_class .= $instance['pause_hover'] ? ' zps-pause-hover' : '';
    $wrap_class .= $instance['card_bg'] ? ' zps-card-bg' : '';
    $wrap_class .= $instance['end_fade'] ? ' zps-end-fade' : '';

    //开始输出
    $html = '';
    foreach ($rows_data as $row_index => $row_partners) {
        //决定本行滚动方向
        switch ($instance['direction']) {
            case 'left':
                $dir = 'left';
                break;
            case 'right':
                $dir = 'right';
                break;
            default:
                $dir = ($row_index % 2 === 0) ? 'left' : 'right';
        }

        $html .= '<div class="zps-row zps-dir-' . $dir . '" data-zps-row>';
        $html .= '<div class="zps-track" data-zps-track>';

        //只输出 1 份种子卡片组，JS 会根据视口宽度按需克隆足够份数，保证无缝循环
        //.flex.shrink0 使 group 在 track 内作为不收缩的弹性子项
        $html .= '<div class="zps-group flex shrink0">';
        foreach ($row_partners as $partner) {
            $html .= zib_widget_partners_scroll_card($partner, $instance['style_type'], $instance['go_link']);
        }
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
    }

    if (!$html) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . esc_html__('[合作伙伴]横向滚动合作伙伴模块：', 'zib_language') . '</b>' . esc_html__('当前配置下没有可显示的内容，请正确配置或移出该模块', 'zib_language') . '</div>';
        }
        return;
    }

    echo '<div class="' . ($instance['show_widget_bg'] ? 'zib-widget' : 'mb20') . '">';
    echo '<div class="' . esc_attr($wrap_class) . '"' . $style_attr . '>';
    echo $html;
    echo '</div>';
    echo '</div>';
}

/**
 * 横向滚动合作伙伴 - 渲染单张卡片
 */
function zib_widget_partners_scroll_card($partner, $style_type = 'card', $go_link = false)
{
    $defaults = array(
        'image' => '',
        'link'  => array('url' => '', 'target' => ''),
        'title' => '',
        'desc'  => '',
    );
    $partner = wp_parse_args((array) $partner, $defaults);

    $title = trim($partner['title']);
    $desc  = trim($partner['desc']);
    $image = !empty($partner['image']) ? $partner['image'] : '';

    //没有图片也没有标题则跳过，避免渲染空卡片
    if (!$image && !$title) {
        return '';
    }

    $alt     = esc_attr($title ? $title : __('合作伙伴', 'zib_language'));
    $img_tag = $image ? '<img class="zps-img" src="' . esc_url($image) . '" alt="' . $alt . '" loading="lazy">' : '';

    //根据样式组合内容（所有 flex/间距/省略/颜色/字重都尽量走主题工具类）
    $inner = '';
    if ($style_type === 'logo') {
        //纯 Logo 模式：只显示图片
        if (!$image) {
            return '';
        }
        $inner .= $img_tag;
    } elseif ($style_type === 'chip') {
        //精简胶囊模式：小图标（仅在有图片时显示） + 名称
        if ($img_tag) {
            $inner .= '<span class="zps-chip-icon flex jc shrink0 mr6">' . $img_tag . '</span>';
        }
        $inner .= '<span class="zps-chip-title text-ellipsis">' . esc_html($title) . '</span>';
    } else {
        //图文卡片模式：左图 + 右文
        if ($img_tag) {
            $inner .= '<div class="zps-card-icon flex jc shrink0 mr10">' . $img_tag . '</div>';
        }
        $inner .= '<div class="zps-card-text flex xx flex1">';
        if ($title) {
            $inner .= '<div class="zps-card-title text-ellipsis font-bold">' . esc_html($title) . '</div>';
        }
        if ($desc) {
            $inner .= '<div class="zps-card-desc text-ellipsis muted-color em09 mt3">' . $desc . '</div>';
        }
        $inner .= '</div>';
    }

    //是否包裹链接
    $url    = !empty($partner['link']['url']) ? esc_url($partner['link']['url']) : 'javascript:void(0)';
    $target = !empty($partner['link']['target']) ? ' target="' . esc_attr($partner['link']['target']) . '" rel="noopener noreferrer"' : '';

    if ($go_link && $url !== 'javascript:void(0)') {
        $url = go_link($url, true);
    }

    //.flex.ac.shrink0 解决 display/对齐/不收缩，留给 CSS 只处理尺寸、圆角、过渡
    $base_class = 'zps-item flex ac shrink0';
    $tag_start  = $url ? '<a class="' . $base_class . '" href="' . $url . '"' . $target . '>' : '<div class="' . $base_class . '">';
    $tag_end    = $url ? '</a>' : '</div>';

    return $tag_start . $inner . $tag_end;
}

//复合信息卡片：图标 + 标题 + 标签 + 描述 + 行动链接

/**
 * 复合信息卡片 - 渲染函数
 */
function zib_widget_ui_combo_info_card_is_show($show_class, $args, $instance)
{
    if (empty($instance['cards'][0])) {
        return false;
    }

    return $show_class;
}
function zib_widget_ui_combo_info_card($args, $instance)
{
    $defaults = array(
        'pc_row'           => 4,
        'm_row'            => 1,
        'card_radius'      => 12,
        'top_band'         => true,
        'top_band_height'  => 70,
        'show_widget_bg'   => false,
        'cta_text'         => __('立即体验', 'zib_language'),
        'cards'            => array(),
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance  = wp_parse_args((array) $instance, $defaults);
    $is_mobile = wp_is_mobile();

    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    //栅格布局
    $col_class = 'col-sm-' . max(1, (int) (12 / $pc_row));
    $col_class .= ' col-xs-' . max(1, (int) (12 / $m_row));

    //组件级 CSS 变量
    $style_vars = array(
        '--zci-radius:' . (int) $instance['card_radius'] . 'px',
        '--zci-band-h:' . (int) $instance['top_band_height'] . 'px',
    );
    $style_attr = ' style="' . esc_attr(implode(';', $style_vars)) . '"';

    $wrap_class = 'zib-combo-info-card row gutters-' . (int) ($is_mobile ? $instance['m_gap'] : $instance['pc_gap']);
    $wrap_class .= $instance['top_band'] ? ' zci-has-band' : '';

    //默认 CTA 文案
    $default_cta       = $instance['cta_text'] !== '' ? $instance['cta_text'] : __('立即体验', 'zib_language');
    $default_cta_color = $instance['cta_color'] !== '' ? $instance['cta_color'] : '';

    //轮询分配"auto"色带颜色
    $auto_palette = array('blue', 'cyan', 'green', 'yellow', 'pink', 'purple');
    $auto_idx     = 0;

    $html = '';
    foreach ($instance['cards'] as $key => $card) {
        $card_html = zib_widget_combo_info_card_item($card, $default_cta, $default_cta_color, $auto_palette, $auto_idx, $instance['go_link']);
        if ($card_html === '') {
            continue;
        }

        $attr = '';
        if ($instance['obs_animation']) {
            $col_class .= ' obs-animate ani-' . $instance['obs_animation'];
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $attr .= ' style=" --delay: ' . strval($key * 0.15) . 's; "';
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $html .= '<div class="' . esc_attr($col_class) . '" ' . $attr . '>' . $card_html . '</div>';
    }

    if ($html === '') {
        return;
    }

    echo '<div class="mb20">';
    echo '<div class="' . esc_attr($wrap_class) . '"' . $style_attr . '>';
    echo $html;
    echo '</div>';
    echo '</div>';
}

/**
 * 复合信息卡片 - 渲染单张卡片
 * @param array  $card         单张卡片数据
 * @param string $default_cta  默认按钮文案
 * @param array  $auto_palette 自动色带配色序列
 * @param int    $auto_idx     自动色带当前索引（引用传递，渲染后自增）
 */
function zib_widget_combo_info_card_item($card, $default_cta, $default_cta_color, $auto_palette, &$auto_idx, $go_link = false)
{
    $defaults = array(
        'icon_image' => '',
        'icon'       => '',
        'title'      => '',
        'tags'       => array(),
        'desc'       => '',
        'link'       => array('url' => '', 'target' => ''),
        'cta_text'   => '',
        'band_color' => 'auto',
    );
    $card = wp_parse_args((array) $card, $defaults);

    $title = trim($card['title']);
    $desc  = trim($card['desc']);
    if (!$title && !$desc) {
        return '';
    }

    //色带颜色：auto 则按序轮询，否则使用指定色
    $band = $card['band_color'];
    if (!$band || $band === 'auto') {
        $band = $auto_palette[$auto_idx % count($auto_palette)];
        $auto_idx++;
    }

    //图标
    $icon_html = '';
    if (!empty($card['icon_image'])) {
        $alt       = esc_attr($title ? $title : __('图标', 'zib_language'));
        $icon_html = '<div class="zci-icon flex jc shrink0 mb20"><img src="' . esc_url($card['icon_image']) . '" alt="' . $alt . '" loading="lazy"></div>';
    } elseif (!empty($card['icon'])) {
        $icon_html = '<div class="zci-icon flex jc shrink0 mb20">' . zib_get_cfs_icon($card['icon'], 'em20') . '</div>';
    }

    //标签：直接复用主题的 .badg.radius + 调色盘 class（c-blue / c-green...）
    //.badg 已内建 padding/radius/bg/color 变量消费，几乎零额外 CSS
    $tags_html = '';
    if (!empty($card['tags']) && is_array($card['tags'])) {
        $tags_html .= '<div class="zci-tags mb10 text-ellipsis">';
        foreach ($card['tags'] as $tag) {
            $tag_text  = isset($tag['text']) ? trim($tag['text']) : '';
            $tag_color = !empty($tag['color']) ? $tag['color'] : '';
            if ($tag_text === '') {
                continue;
            }
            $tags_html .= '<span class="badg p2-10 radius px12 ' . esc_attr($tag_color) . '">' . $tag_text . '</span>';
        }
        $tags_html .= '</div>';
    }

    //描述：text-ellipsis-3 自带 4.2em 高度 + 3 行省略
    $desc_html = $desc ? '<div class="zci-desc text-ellipsis-3 muted-color">' . $desc . '</div>' : '';

    //CTA 按钮：inflex ac gap6 提供布局，CSS 只负责色彩和 hover 箭头动效
    $cta_text  = !empty($card['cta_text']) ? $card['cta_text'] : $default_cta;
    $cta_color = !empty($card['cta_color']) ? $card['cta_color'] : $default_cta_color;
    $url       = !empty($card['link']['url']) ? $card['link']['url'] : '';
    $target    = !empty($card['link']['target']) ? ' target="' . esc_attr($card['link']['target']) . '" rel="noopener noreferrer"' : '';
    //外链重定向
    if ($url && $go_link) {
        $url = go_link($url, true);
    }

    $cta_html = '';
    if ($cta_text && $url) {
        $cta_html = '<a class="zci-cta inflex ac gap6 ' . esc_attr($cta_color) . '" href="' . esc_url($url) . '"' . $target . '>'
        . '<span>' . esc_html($cta_text) . '</span>'
            . '<i class="fa fa-arrow-right zci-arrow" aria-hidden="true"></i>'
            . '</a>';
    }

    //外层卡片：flex xx 提供列向布局
    $card_tag_start = '<div class="zci-card flex xx zci-band-' . esc_attr($band) . '">';
    $card_tag_end   = '</div>';

    //内容区：flex xx flex1 撑满卡片高度，CTA 用 margin-top:auto 贴底
    $inner = '<div class="zci-body flex xx flex1">';
    $inner .= $icon_html;
    if ($title) {
        $inner .= '<div class="zci-title font-bold text-ellipsis mb10">' . esc_html($title) . '</div>';
    }
    $inner .= $tags_html;
    $inner .= $desc_html;
    $inner .= $cta_html;
    $inner .= '</div>';

    return $card_tag_start . $inner . $card_tag_end;
}
//自定义文字卡片

function zib_widget_ui_text_title_is_show($show_class, $args, $instance)
{
    if (empty($instance['title']) && empty($instance['desc'])) {
        return false;
    }

    return $show_class;
}

function zib_widget_ui_text_title($args, $instance)
{
    $defaults = array(
        'title_icon_width' => 0,
        'title'            => '',
        'desc'             => '',
        'title_icon_left'  => '',
        'title_icon_right' => '',
        'title_opts'       => array(),
        'desc_opts'        => array(),
        'align'            => 'center',
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    $title_style = '';
    $desc_style  = '';

    $title_class = '';
    $desc_class  = '';

    if (isset($instance['title_opts'])) {
        foreach ($instance['title_opts'] as $key => $value) {
            if (in_array($key, array('color')) || !is_array($value)) {
                continue;
            }

            foreach ($value as $_key => $_value) {
                $_unit = '';
                if (in_array($_key, array('font_size', 'margin_top', 'margin_bottom'))) {
                    $_unit = 'px';
                }

                $title_style .= '--' . $key . '-' . $_key . ':' . $_value . $_unit . ';';
            }
        }
    }
    if (isset($instance['desc_opts'])) {
        foreach ($instance['desc_opts'] as $key => $value) {
            if (in_array($key, array('color')) || !is_array($value)) {
                continue;
            }
            foreach ($value as $_key => $_value) {
                $_unit = '';
                if (in_array($_key, array('font_size', 'margin_top', 'margin_bottom'))) {
                    $_unit = 'px';
                }
                $desc_style .= '--' . $key . '-' . $_key . ':' . $_value . $_unit . ';';
            }
        }
    }

    $title = $instance['title'] ?? '';
    if ($title) {

        $title_icon_left  = $instance['title_icon_left'] ? zib_get_cfs_icon($instance['title_icon_left']) : '';
        $title_icon_right = $instance['title_icon_right'] ? zib_get_cfs_icon($instance['title_icon_right']) : '';
        $title_icon_width = $instance['title_icon_width'] ? 'style="--icon-width:' . $instance['title_icon_width'] . 'px;"' : '';

        $title_icon_left  = '<span class="text-title-icon mr6" ' . $title_icon_width . '>' . $title_icon_left . '</span>';
        $title_icon_right = '<span class="text-title-icon ml6" ' . $title_icon_width . '>' . $title_icon_right . '</span>';
    }

    $a_title = '';
    if ($instance['obs_animation'] && $title) {
        //如果开启了动画，将标题的每个文字拆分成span，每个span添加动画效果
        $t_class = ' obs-animate ani-' . $instance['obs_animation'];
        $t_attr  = '';
        $t_attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
        if ($instance['animation_repeat']) {
            $t_attr .= ' data-animation-repeat="true"';
        }

        if (!empty($instance['title_opts']['color']) && strpos($instance['title_opts']['color'], 'cg-') !== false) {
            $a_title                         = '<span class="' . $instance['title_opts']['color'] . ' ' . $t_class . '" ' . $t_attr . '">' . $title_icon_left . $title . $title_icon_right . '</span>';
            $instance['title_opts']['color'] = '';
        } else {
            $chars = zib_str_split($title);
            if ($title_icon_left) {
                $chars = array_merge(array($title_icon_left), $chars);
            }
            if ($title_icon_right) {
                $chars = array_merge($chars, array($title_icon_right));
            }

            foreach ($chars as $i => $char) {
                $a_title .= '<span class="' . $t_class . '" ' . $t_attr . ' data-delay="' . ($i * 0.1) . '" style=" --delay: ' . strval($i * 0.05) . 's; ">' . $char . '</span>';
            }
        }

        $title = $a_title;
    }

    if (!$instance['obs_animation']) {
        $title = $title_icon_left . $title . $title_icon_right;
    }

    $desc = $instance['desc'] ?? '';
    if ($instance['obs_animation'] && $desc) {
        $d_class = ' obs-animate ani-' . $instance['obs_animation'];
        $d_attr  = '';
        $d_attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
        if ($instance['animation_repeat']) {
            $d_attr .= ' data-animation-repeat="true"';
        }

        if ($title && strpos($a_title, '--delay:') !== false) {
            $d_attr .= ' style=" --delay: .6s; "';
        }

        if (!empty($instance['desc_opts']['color']) && strpos($instance['desc_opts']['color'], 'cg-') !== false) {
            $d_class .= ' ' . $instance['desc_opts']['color'];
            $instance['desc_opts']['color'] = '';
        }
        $desc = '<span class="' . $d_class . '" ' . $d_attr . '>' . $desc . '</span>';
    }

    $title = $title ? '<div class="text-title ' . $title_class . '" style="' . esc_attr($title_style) . '"><div class="text-title-inner inline-block ' . ($instance['title_opts']['color'] ?? '') . '">' . $title . '</div></div>' : '';
    $desc  = $desc ? '<div class="text-desc ' . $desc_class . '" style="' . esc_attr($desc_style) . '"><div class="text-desc-inner inline-block ' . ($instance['desc_opts']['color'] ?? '') . '">' . $desc . '</div></div>' : '';

    echo '<div class="widget-text-title mb20 text-' . $instance['align'] . '">' . $title . $desc . '</div>';
}

function zib_widget_ui_get_text_title_opts_fields($is_desc = false)
{

    $fields = array(
        //字体大小
        array(
            'id'       => 'font_size',
            'title'    => ' ',
            'subtitle' => __('字体大小', 'zib_language'),
            'type'     => 'spinner',
            'default'  => 24,
            'max'      => 60,
            'min'      => 12,
            'step'     => 2,
            'unit'     => 'px',
        ),
        //粗体
        array(
            'id'       => 'font_weight',
            'title'    => ' ',
            'subtitle' => __('字体粗细', 'zib_language'),
            'type'     => 'radio',
            'inline'   => true,
            'default'  => 'normal',
            'options'  => array(
                'normal'  => __('正常', 'zib_language'),
                'bold'    => __('粗体', 'zib_language'),
                'lighter' => __('细体', 'zib_language'),
            ),
        ),
    );
    if (!$is_desc) {
        $fields[] = array(
            'id'       => 'margin_top',
            'title'    => ' ',
            'subtitle' => __('上边距', 'zib_language'),
            'type'     => 'spinner',
            'default'  => 0,
            'max'      => 60,
            'min'      => 12,
            'step'     => 2,
            'unit'     => 'px',
        );
    }

    $fields[] = array(
        'id'       => 'margin_bottom',
        'title'    => ' ',
        'subtitle' => __('下边距', 'zib_language'),
        'type'     => 'spinner',
        'default'  => 20,
        'max'      => 60,
        'min'      => 12,
        'step'     => 2,
        'unit'     => 'px',
    );

    return $fields;
}

function zib_widget_ui_buttons_is_show($is_show, $args, $instance)
{
    if (empty($instance['items'][0])) {
        return false;
    }

    return $is_show;
}

function zib_widget_ui_buttons($args, $instance)
{
    $default = [
        'obs_animation'    => '',
        'animation_repeat' => false,
        'pc_width'         => 0,
        'm_width'          => 0,
        'pc_padding'       => 'large',
        'm_padding'        => 'medium',
        'pc_gap'           => 12,
        'm_gap'            => 6,
        'align'            => 'center',
        'radius'           => false,
        'hollow'           => false,
        'go_link'          => true,
        'items'            => [],
    ];
    $instance = wp_parse_args((array) $instance, $default);

    $but_class = 'but';
    if ($instance['radius']) {
        $but_class .= ' radius';
    }
    if ($instance['hollow']) {
        $but_class .= ' hollow';
    }

    $buts = '';
    foreach ($instance['items'] as $key => $item) {
        if (!$item['text']) {
            continue;
        }
        $but_item_class = $but_class;
        $icon_left      = $item['icon_left'] ? zib_get_cfs_icon($item['icon_left']) : '';
        $icon_right     = $item['icon_right'] ? '<span class="ml6">' . zib_get_cfs_icon($item['icon_right']) . '</span>' : '';
        $but_item_class .= ' ' . $item['color'];
        $url    = !empty($item['link']['url']) ? $item['link']['url'] : 'javascript:void(0)';
        $target = !empty($item['link']['target']) ? ' target="' . esc_attr($item['link']['target']) . '" rel="noopener noreferrer"' : '';
        if ($url && $instance['go_link']) {
            $url = go_link($url, true);
        }

        $attr   = '';
        $_class = '';
        if ($instance['obs_animation']) {
            $_class .= ' obs-animate ani-' . $instance['obs_animation'];
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $attr .= ' style=" --delay: ' . strval($key * 0.15) . 's; "';
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $buts .= '<div class="' . $_class . '" ' . $attr . '><a class="' . $but_item_class . '" href="' . $url . '"' . $target . '>' . $icon_left . $item['text'] . $icon_right . '</a></div>';
    }

    if (!$buts) {
        return;
    }

    $style_attr = '';
    if ($instance['pc_width']) {
        $style_attr .= '--pc-width:' . $instance['pc_width'] . 'px;';
    }
    if ($instance['m_width']) {
        $style_attr .= '--m-width:' . $instance['m_width'] . 'px;';
    }
    if ($instance['pc_gap']) {
        $style_attr .= '--pc-gap:' . $instance['pc_gap'] . 'px;';
    }
    if ($instance['m_gap']) {
        $style_attr .= '--m-gap:' . $instance['m_gap'] . 'px;';
    }

    echo '<div class="widget-buttons mb20 pc-size-' . $instance['pc_padding'] . ' m-size-' . $instance['m_padding'] . '">';
    echo '<div class="widget-buttons-wrap flex hh align-' . $instance['align'] . '" style="' . $style_attr . '">' . $buts . '</div>';
    echo '</div>';
}

//布局间隔

function zib_widget_ui_layout_gap($args, $instance)
{
    $default = [
        'pc_gap' => 10,
        'm_gap'  => 5,
    ];
    $instance = wp_parse_args((array) $instance, $default);

    $style_attr = '';
    if ($instance['pc_gap']) {
        $style_attr .= '--pc-gap:' . $instance['pc_gap'] . 'px;';
    }
    if ($instance['m_gap']) {
        $style_attr .= '--m-gap:' . $instance['m_gap'] . 'px;';
    }
    if (!$style_attr) {
        return;
    }

    echo '<div class="widget-layout-gap" style="' . $style_attr . '"></div>';
}

function zib_widget_ui_tag_get_taxonomies()
{
    $taxonomies = get_taxonomies(array('show_tagcloud' => true), 'object');
    foreach ($taxonomies as $taxonomy => $tax) {
        $options[$taxonomy] = $tax->labels->name;
    }
    return $options;
}

function zib_widget_ui_tag_cloud($args, $instance)
{

    //新窗口打开
    $blank = $instance['blank'] ? ' target="_blank"' : '';

    //开始生成标签
    $get_terms_args = array(
        'taxonomy'   => $instance['taxonomy'],
        'orderby'    => $instance['orderby'],
        'order'      => 'DESC',
        'number'     => $instance['number'],
        'hide_empty' => false,
        'count'      => true,
    );
    $tags = get_terms($get_terms_args);

    $tag_link     = '';
    $rand_color_i = rand(0, 10);
    if (!empty($tags) && !is_wp_error($tags)) {
        foreach ($tags as $key => $tag) {
            $url  = esc_url(get_term_link(($tag->term_id), $tag->taxonomy));
            $name = esc_attr($tag->name);
            $cls  = array('c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', '', 'c-blue-2', 'c-yellow-2', 'c-green-2', 'c-purple-2', 'c-red-2', '');
            if ($rand_color_i > 10) {
                $rand_color_i = 0;
            }

            $tag_class = 'but ' . ('rand' != $instance['color'] ? $instance['color'] : $cls[$rand_color_i]);
            $rand_color_i++;
            $count = $instance['show_count'] ? '<span class="em09 tag-count"> (' . esc_attr($tag->count) . ')</span>' : '';
            $tag_link .= '<a' . $blank . ' href="' . $url . '" class="text-ellipsis ' . $tag_class . '">' . $name . $count . '</a>';
        }
    }

    echo '<div class="zib-widget widget-tag-cloud author-tag' . ($instance['fixed_width'] ? ' fixed-width' : '') . '">';
    echo $tag_link;
    echo '</div>';
}

function zib_widget_ui_new_comment($args, $instance)
{

    echo '<div class="box-body comment-mini-lists zib-widget">';
    zib_widget_comments($instance['limit'], $instance['outpost'], $instance['outer'], $instance['style'] ?? '', $instance['show_source'] ?? false);
    echo '</div>';
}

function zib_widget_ui_links_lists($args, $instance)
{

    $links = array();

    $links_args = array(
        'orderby'  => $instance['links_orderby'], //排序方式
        'order'    => $instance['links_order'], //升序还是降序
        'limit'    => $instance['links_limit'] ? (int) $instance['links_limit'] : -1, //最多显示数量
        'category' => $instance['links_cats'], //以逗号分隔的类别ID列表
    );

    if (empty($instance['links_cats']) || $instance['links_cats'] == 'all') {
        unset($links_args['category']);
    }

    $links     = get_bookmarks($links_args);
    $nofollow  = !empty($instance['nofollow']);
    $alignment = $instance['alignment'] ? ' text-' . $instance['alignment'] : '';

    echo '<div class="mb20">';
    echo '<div class="links-widget links-box links-style-' . $instance['type'] . ($instance['show_box'] ? ' zib-widget' : '') . $alignment . '">';
    echo zib_links_box($links, $instance['type'], $nofollow, $instance['go_link'], $instance['blank']);
    echo '</div>';
    echo '</div>';
}

add_action('after_setup_theme', 'zib_widget_register_cfs_more');
function zib_widget_register_cfs_more()
{
    Zib_CFSwidget::create('zib_widget_ui_icon_card', array(
        'title'       => __('图标卡片[竖版]', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('图标与文案配合的特色卡片，文字在图标下方，更适合做移动端的图标按钮', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('模块背景', 'zib_language'),
                'type'    => 'switcher',
                'id'      => 'show_widget_bg',
                'label'   => __('显示模块背景', 'zib_language'),
                'default' => false,
                'type'    => 'switcher',
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
                'id'      => 'size',
                'title'   => __('图标尺寸微调', 'zib_language'),
                'default' => 0,
                'max'     => 10,
                'min'     => -10,
                'step'    => 1,
                'unit'    => '',
                'type'    => 'slider',
            ),
            array(
                'id'                     => 'cards',
                'title'                  => __('添加图标', 'zib_language'),
                'subtitle'               => sprintf(
                    '<div style="color:#ee5a5a;font-size: 12px;"><i class="fa fa-fw fa-info-circle fa-fw"></i> %s</div>',
                    esc_html__('文案属于可选项目，同一组模块文案的字数差距不能太大，否则会出现不整齐的现象', 'zib_language')
                ),
                'type'                   => 'group',
                'button_title'           => __('添加图标', 'zib_language'),
                'accordion_title_auto'   => false,
                'accordion_title_number' => true,
                'default'                => array(),
                'fields'                 => array(
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('选择图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => 'fa fa-magic',
                    ),
                    array(
                        'title'      => __('自定义图标', 'zib_language'),
                        'desc'       => __('如您想使用非自带图标，可以在此输入自定义图标代码', 'zib_language'),
                        'class'      => 'compact',
                        'id'         => 'customize_icon',
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'id'           => 'link',
                        'type'         => 'link',
                        'title'        => __('跳转链接', 'zib_language'),
                        'default'      => array(),
                        'add_title'    => __('添加链接', 'zib_language'),
                        'edit_title'   => __('编辑链接', 'zib_language'),
                        'remove_title' => __('删除链接', 'zib_language'),
                    ),
                    array(
                        'title'   => __('图标样式', 'zib_language'),
                        'type'    => 'switcher',
                        'id'      => 'icon_radius',
                        'label'   => __('显示图标背景', 'zib_language'),
                        'default' => false,
                        'type'    => 'switcher',
                    ),
                    array(
                        'dependency' => array('icon_radius', '!=', ''),
                        'title'      => ' ',
                        'subtitle'   => __('图标样式', 'zib_language'),
                        'id'         => 'icon_class',
                        'class'      => 'compact skin-color',
                        'default'    => 'c-yellow',
                        'type'       => 'palette',
                        'options'    => CFS_Module::zib_palette(
                            array(
                                'transparent' => array('rgba(114, 114, 114, 0.1)'),
                            )
                        ),
                    ),
                    array(
                        'dependency' => array('icon_radius|icon_custom_color', '==|==', '|'),
                        'title'      => ' ',
                        'subtitle'   => __('图标颜色', 'zib_language'),
                        'id'         => 'icon_color',
                        'class'      => 'compact skin-color',
                        'default'    => 'key-color',
                        'type'       => 'palette',
                        'options'    => array(
                            'key-color'  => array('#333'),
                            'c-red'      => array('rgba(255, 84, 115,1)'),
                            'c-red-2'    => array('rgba(194, 41, 46,1)'),
                            'c-yellow'   => array('rgba(255, 111, 6,1)'),
                            'c-yellow-2' => array('rgba(179, 103, 8,1)'),
                            'c-cyan'     => array('rgba(8, 196, 193, 1)'),
                            'c-blue'     => array('rgba(41, 151, 247,1)'),
                            'c-blue-2'   => array('rgba(77, 130, 249,1)'),
                            'c-green'    => array('rgba(18, 185, 40,1)'),
                            'c-green-2'  => array('rgba(72, 135, 24,1)'),
                            'c-purple'   => array('rgba(213, 72, 245,1)'),
                            'c-purple-2' => array('rgba(154, 72, 245,1)'),
                        ),
                    ),
                    array(
                        'dependency' => array('icon_radius', '==', ''),
                        'title'      => ' ',
                        'subtitle'   => __('自定义图标颜色（如需选择预置颜色，请清空此处）', 'zib_language'),
                        'id'         => 'icon_custom_color',
                        'class'      => 'compact',
                        'default'    => '',
                        'type'       => 'color',
                    ),
                    array(
                        'title'      => __('文案标题', 'zib_language'),
                        'id'         => 'title',
                        'desc'       => __('第一行文案，字体稍大一点', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'      => __('文案简介', 'zib_language'),
                        'id'         => 'desc',
                        'class'      => 'compact',
                        'desc'       => __('第二行文案，字体稍小一点', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 2,
                        ),
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_dplayer', array(
        'title'       => __('视频', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('显示视频的模块，支持本地视频以及m3u8、mpd、flv等流媒体格式', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('视频地址', 'zib_language'),
                'id'      => 'url',
                'type'    => 'upload',
                'library' => 'video',
                'preview' => false,
                'default' => '',
                'desc'    => __('输入视频地址或选择、上传本地视频', 'zib_language'),
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'title'      => __('视频封面', 'zib_language'),
                'id'         => 'pic',
                'type'       => 'upload',
                'library'    => 'image',
                'default'    => '',
                'desc'       => __('为视频添加图片封面(可选)', 'zib_language'),
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'title'      => __('自动播放', 'zib_language'),
                'id'         => 'autoplay',
                'type'       => 'switcher',
                'label'      => __('部分浏览器不兼容', 'zib_language'),
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'id'         => 'loop',
                'title'      => __('循环播放', 'zib_language'),
                'type'       => 'switcher',
                'label'      => __('部分浏览器不兼容', 'zib_language'),
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'id'         => 'volume',
                'title'      => __('初始音量', 'zib_language'),
                'default'    => 100,
                'max'        => 100,
                'min'        => 0,
                'step'       => 5,
                'unit'       => '%',
                'type'       => 'slider',
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'id'         => 'hide_controller',
                'title'      => __('隐藏播放控件', 'zib_language'),
                'type'       => 'switcher',
                'label'      => __('隐藏进度条及控制按钮', 'zib_language'),
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'id'         => 'scale_height',
                'title'      => __('固定长宽比例', 'zib_language'),
                'default'    => 0,
                'max'        => 200,
                'min'        => 0,
                'step'       => 5,
                'unit'       => '%',
                'type'       => 'slider',
                'desc'       => __('为0则不固定长宽比例', 'zib_language'),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_iframe', array(
        'title'       => __('超级嵌入', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('嵌入其他在线内容，通常用于嵌入其它网站的视频播放器或音乐播放器，也可以嵌入其它任意在线内容', 'zib_language'),
        'fields'      => array(
            array(
                'id'          => 'url',
                'title'       => __('嵌入地址', 'zib_language'),
                'placeholder' => __('请输入需要嵌入的链接，或者直接粘贴iframe嵌入代码', 'zib_language'),
                'desc'        => __('请输入需要嵌入的链接，或者直接粘贴iframe嵌入代码', 'zib_language'),
                'default'     => '',
                'attributes'  => array(
                    'rows' => 4,
                ),
                'sanitize'    => false,
                'type'        => 'textarea',
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'id'         => 'aspect',
                'title'      => __('长宽比例设置', 'zib_language'),
                'default'    => 55,
                'max'        => 300,
                'min'        => 20,
                'step'       => 5,
                'unit'       => '%',
                'desc'       => __('设置高度与宽度的占比，以保持对应的长宽比例', 'zib_language'),
                'type'       => 'slider',
            ),
            array(
                'dependency' => array('url', '!=', ''),
                'id'         => 'allowfullscreen',
                'type'       => 'switcher',
                'label'      => __('允许内容全屏显示', 'zib_language'),
            ),
        ),

    ));

    Zib_CFSwidget::create('zib_widget_ui_graphic_cover', array(
        'title'            => __('图文视频封面卡片', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => '',
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
                'id'       => 'pc_row',
                'title'    => __('排列布局', 'zib_language'),
                'subtitle' => __('PC端单行排列数量', 'zib_language'),
                'default'  => 4,
                'options'  => array(
                    1  => __('1个', 'zib_language'),
                    2  => __('2个', 'zib_language'),
                    3  => __('3个', 'zib_language'),
                    4  => __('4个', 'zib_language'),
                    6  => __('6个', 'zib_language'),
                    12 => __('12个', 'zib_language'),
                ),
                'type'     => 'button_set',
                'class'    => 'button-mini',
            ),
            array(
                'id'       => 'm_row',
                'title'    => ' ',
                'subtitle' => __('移动端单行排列数量', 'zib_language'),
                'decs'     => __('请根据此模块放置位置的宽度合理调整单行数量，避免显示不佳', 'zib_language'),
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
                'id'      => 'height_scale',
                'title'   => __('封面长宽比例', 'zib_language'),
                'default' => 30,
                'max'     => 300,
                'min'     => 5,
                'step'    => 5,
                'unit'    => '%',
                'type'    => 'spinner',
            ),
            array(
                'id'       => 'font_size_pc',
                'title'    => __('文字样式', 'zib_language'),
                'subtitle' => __('PC端字体大小', 'zib_language'),
                'default'  => 18,
                'max'      => 80,
                'min'      => 10,
                'step'     => 2,
                'unit'     => 'px',
                'type'     => 'spinner',
            ),
            array(
                'id'       => 'font_size_m',
                'title'    => ' ',
                'class'    => 'compact',
                'subtitle' => __('移动端字体大小', 'zib_language'),
                'default'  => 14,
                'max'      => 80,
                'min'      => 10,
                'step'     => 2,
                'unit'     => 'px',
                'type'     => 'spinner',
            ),
            array(
                'id'    => 'font_bold',
                'class' => 'compact',
                'type'  => 'switcher',
                'label' => __('粗体显示', 'zib_language'),
            ),
            array(
                'class'    => 'compact',
                'id'       => 'font_color',
                'title'    => ' ',
                'type'     => 'color',
                'subtitle' => __('文字颜色', 'zib_language'),
            ),
            array(
                'id'           => 'covers',
                'title'        => __('添加封面', 'zib_language'),
                'type'         => 'group',
                'button_title' => __('添加内容', 'zib_language'),
                'default'      => array(),
                'desc'         => '<div class="c-yellow">' . __('注意：由于移动端多数浏览器不支持视频背景功能，所以移动端不会显示视频！', 'zib_language') . '</div>',
                'fields'       => array(
                    array(
                        'title'   => __('图片背景', 'zib_language'),
                        'id'      => 'image',
                        'default' => '',
                        'preview' => true,
                        'library' => 'image',
                        'type'    => 'upload',
                    ),
                    array(
                        'title'   => __('视频背景', 'zib_language'),
                        'id'      => 'video',
                        'default' => '',
                        'class'   => 'compact',
                        'preview' => false,
                        'library' => 'video',
                        'type'    => 'upload',
                        'desc'    => __('（必填）图片背景、视频背景至少二选一，如果同时设置则PC端视频优先（PC端视频加载失败则显示图片），', 'zib_language') . '<span class="c-yellow">' . __('移动端只显示图片', 'zib_language') . '</span>',
                    ),
                    array(
                        'title'   => __('显示规则', 'zib_language') . zib_get_csf_option_new_badge()['7.1'],
                        'id'      => 'hide',
                        'type'    => 'radio',
                        'inline'  => true,
                        'options' => array(
                            ''   => __('全部显示', 'zib_language'),
                            'pc' => __('PC端不显示', 'zib_language'),
                            'm'  => __('移动端不显示', 'zib_language'),
                        ),
                        'default' => '',
                    ),
                    array(
                        'title'      => __('文字', 'zib_language'),
                        'id'         => 'title',
                        'default'    => '',
                        'desc'       => __('支持HTML代码', 'zib_language'),
                        'attributes' => array(
                            'rows' => 1,
                        ),
                        'type'       => 'textarea',
                    ),
                    array(
                        'id'           => 'link',
                        'type'         => 'link',
                        'title'        => __('跳转链接', 'zib_language'),
                        'default'      => array(),
                        'add_title'    => __('添加链接', 'zib_language'),
                        'edit_title'   => __('编辑链接', 'zib_language'),
                        'remove_title' => __('删除链接', 'zib_language'),
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_icon_cover_card', array(
        'title'       => __('图标卡片[横版]', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('图标与文案配合的特色卡片，文字在图标右侧', 'zib_language'),
        'fields'      => array(
            array(
                'id'       => 'pc_row',
                'title'    => __('排列布局', 'zib_language'),
                'subtitle' => __('PC端单行排列数量', 'zib_language'),
                'default'  => 4,
                'options'  => array(
                    1 => __('1个', 'zib_language'),
                    2 => __('2个', 'zib_language'),
                    3 => __('3个', 'zib_language'),
                    4 => __('4个', 'zib_language'),
                    6 => __('6个', 'zib_language'),
                ),
                'type'     => 'button_set',
                'class'    => 'button-mini',
            ),
            array(
                'id'       => 'm_row',
                'title'    => ' ',
                'subtitle' => __('移动端单行排列数量', 'zib_language'),
                'decs'     => __('请根据此模块放置位置的宽度合理调整单行数量，避免显示不佳', 'zib_language'),
                'class'    => 'compact button-mini',
                'default'  => 2,
                'options'  => array(
                    1 => __('1个', 'zib_language'),
                    2 => __('2个', 'zib_language'),
                ),
                'type'     => 'button_set',
            ),
            array(
                'title'   => __('模块背景', 'zib_language'),
                'type'    => 'switcher',
                'id'      => 'show_widget_bg',
                'label'   => __('整个模块显示背景，关闭后每个卡片都显示背景', 'zib_language'),
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'title'   => __('方形图标', 'zib_language'),
                'type'    => 'switcher',
                'id'      => 'icon_radius4',
                'label'   => __('图标显示为正方形，而不是圆形', 'zib_language'),
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'id'                     => 'cards',
                'title'                  => __('添加卡片', 'zib_language'),
                'type'                   => 'group',
                'button_title'           => __('添加内容', 'zib_language'),
                'accordion_title_auto'   => true,
                'accordion_title_number' => true,
                'default'                => array(),
                'fields'                 => array(
                    array(
                        'title'      => __('文案标题', 'zib_language'),
                        'id'         => 'title',
                        'desc'       => __('第一行文案，字体稍大一点', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'      => __('文案简介', 'zib_language'),
                        'id'         => 'desc',
                        'class'      => 'compact',
                        'desc'       => __('第二行文案，字体稍小一点（支持html，注意代码规范）', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 2,
                        ),
                    ),
                    array(
                        'id'           => 'icon',
                        'type'         => 'icon',
                        'title'        => __('选择图标', 'zib_language'),
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => 'fa fa-magic',
                    ),
                    array(
                        'title'      => __('自定义图标', 'zib_language'),
                        'desc'       => __('如您想使用非自带图标，可以在此输入自定义图标代码', 'zib_language'),
                        'class'      => 'compact',
                        'id'         => 'customize_icon',
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'    => ' ',
                        'subtitle' => __('图标颜色', 'zib_language'),
                        'id'       => 'icon_class',
                        'class'    => 'compact skin-color',
                        'default'  => 'c-yellow',
                        'type'     => 'palette',
                        'options'  => CFS_Module::zib_palette(
                            array(
                                'transparent' => array('rgba(114, 114, 114, 0.1)'),
                            )
                        ),
                    ),
                    array(
                        'id'           => 'link',
                        'type'         => 'link',
                        'title'        => __('跳转链接', 'zib_language'),
                        'default'      => array(),
                        'add_title'    => __('添加链接', 'zib_language'),
                        'edit_title'   => __('编辑链接', 'zib_language'),
                        'remove_title' => __('删除链接', 'zib_language'),
                    ),

                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_partners_scroll', array(
        'title'            => __('横向滚动合作伙伴', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => true,
        'description'      => __('横向自动滚动的合作伙伴/赞助商/案例列表，支持多行滚动、鼠标悬停暂停、支持 Logo 图片或字体图标', 'zib_language'),
        'fields'           => array(
            array(
                'id'       => 'rows',
                'title'    => __('显示行数', 'zib_language'),
                'subtitle' => __('PC端显示几行滚动', 'zib_language'),
                'default'  => 3,
                'options'  => array(
                    1 => __('1行', 'zib_language'),
                    2 => __('2行', 'zib_language'),
                    3 => __('3行', 'zib_language'),
                    4 => __('4行', 'zib_language'),
                    5 => __('5行', 'zib_language'),
                    6 => __('6行', 'zib_language'),
                ),
                'type'     => 'button_set',
                'class'    => 'button-mini',
            ),
            array(
                'id'       => 'm_rows',
                'title'    => ' ',
                'subtitle' => __('移动端显示几行滚动', 'zib_language'),
                'class'    => 'compact button-mini',
                'default'  => 3,
                'options'  => array(
                    1 => __('1行', 'zib_language'),
                    2 => __('2行', 'zib_language'),
                    3 => __('3行', 'zib_language'),
                    4 => __('4行', 'zib_language'),
                    5 => __('5行', 'zib_language'),
                    6 => __('6行', 'zib_language'),
                ),
                'type'     => 'button_set',
            ),
            array(
                'id'       => 'style_type',
                'title'    => __('卡片样式', 'zib_language'),
                'subtitle' => __('选择合作伙伴卡片的呈现方式', 'zib_language'),
                'default'  => 'card',
                'options'  => array(
                    'card' => __('图文卡片(适合正方形logo)', 'zib_language'),
                    'logo' => __('纯 Logo', 'zib_language'),
                    'chip' => __('精简胶囊', 'zib_language'),
                ),
                'type'     => 'radio',
                'inline'   => true,
            ),
            array(
                'id'         => 'card_width',
                'title'      => __('单卡片宽度', 'zib_language'),
                'help'       => __('PC端每张卡片的宽度（纯 Logo 模式下宽度会自适应图片，此项不生效）', 'zib_language'),
                'dependency' => array('style_type', '==', 'card'),
                'default'    => 200,
                'max'        => 500,
                'min'        => 80,
                'step'       => 10,
                'unit'       => 'px',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array('style_type', '!=', 'chip'),
                'id'         => 'card_height',
                'title'      => __('卡片高度', 'zib_language'),
                'help'       => __('每张卡片的高度，纯 Logo 模式建议 70-120', 'zib_language'),
                'default'    => 60,
                'max'        => 260,
                'min'        => 50,
                'step'       => 5,
                'unit'       => 'px',
                'type'       => 'spinner',
            ),
            array(
                'id'      => 'card_bg',
                'title'   => __('卡片背景', 'zib_language'),
                'label'   => __('显示每张合作伙伴卡片的背景', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'dependency' => array('card_bg', '!=', ''),
                'id'         => 'card_radius',
                'title'      => __('卡片圆角', 'zib_language'),
                'default'    => 16,
                'max'        => 60,
                'min'        => 0,
                'step'       => 1,
                'unit'       => 'px',
                'type'       => 'slider',
            ),
            array(
                'id'      => 'speed',
                'title'   => __('滚动速度(数越小越快)', 'zib_language'),
                'default' => 3,
                'max'     => 10,
                'min'     => 0.1,
                'step'    => 0.1,
                'unit'    => 's',
                'type'    => 'slider',
            ),
            array(
                'id'      => 'direction',
                'title'   => __('滚动方向', 'zib_language'),
                'default' => 'alternate',
                'options' => array(
                    'alternate' => __('奇数行左，偶数行右', 'zib_language'),
                    'left'      => __('全部向左', 'zib_language'),
                    'right'     => __('全部向右', 'zib_language'),
                ),
                'type'    => 'radio',
                'inline'  => true,
            ),
            array(
                'id'      => 'pause_hover',
                'label'   => __('鼠标悬停暂停', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'id'      => 'show_widget_bg',
                'label'   => __('模块背景', 'zib_language'),
                'help'    => __('显示整个模块外层背景', 'zib_language'),
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'id'      => 'go_link',
                'label'   => __('外链重定向', 'zib_language'),
                'help'    => __('开启后，将链接转为内部go跳转链接', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'id'      => 'end_fade',
                'label'   => __('两端渐隐', 'zib_language'),
                'help'    => __('开启后，卡片两端会渐隐，使卡片无缝连接', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),

            //添加链接获取方式，1.通过链接获取 2.手动添加
            array(
                'id'      => 'link_type',
                'title'   => __('合作伙伴获取方式', 'zib_language'),
                'default' => 'link',
                'options' => array(
                    'link'   => __('通过链接获取', 'zib_language'),
                    'manual' => __('手动添加', 'zib_language'),
                ),
                'type'    => 'radio',
                'inline'  => true,
            ),
            //选择链接分类
            array(
                'dependency'  => array('link_type', '==', 'link'),
                'id'          => 'link_cats',
                'title'       => __('选择链接分类', 'zib_language'),
                'placeholder' => __('选择分类', 'zib_language'),
                'default'     => [],
                'type'        => 'select',
                'options'     => 'categories',
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
                'query_args'  => array(
                    'taxonomy' => array('link_category'),
                    'orderby'  => 'taxonomy',
                ),
                'desc'        => __('需显示的链接分类，留空则为全部', 'zib_language')
            ),
            //选择链接排序方式
            array(
                'dependency' => array('link_type', '==', 'link'),
                'title'      => __('最大获取数量', 'zib_language'),
                'id'         => 'link_limit',
                'default'    => 50,
                'type'       => 'spinner',
                'min'        => 0,
                'step'       => 5,
                'unit'       => __('个', 'zib_language'),
            ),
            array(
                'dependency' => array('link_type', '==', 'link'),
                'id'         => 'link_orderby',
                'title'      => __('排序方式', 'zib_language'),
                'default'    => 'name',
                'type'       => 'select',
                'options'    => array(
                    'name'    => __('名称排序', 'zib_language'),
                    'updated' => __('更新时间', 'zib_language'),
                    'rating'  => __('链接评分', 'zib_language'),
                    'rand'    => __('随机排序', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('link_type', '==', 'link'),
                'id'         => 'page_links_order',
                'title'      => ' ',
                'subtitle'   => ' ',
                'default'    => 'ASC',
                'class'      => 'compact',
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'ASC'  => __('升序', 'zib_language'),
                    'DESC' => __('降序', 'zib_language'),
                ),
            ),
            array(
                'dependency'             => array('link_type', '==', 'manual'),
                'id'                     => 'partners',
                'title'                  => __('添加合作伙伴', 'zib_language'),
                'subtitle'               => '<div class="c-yellow px12"><i class="fa fa-fw fa-info-circle"></i> ' . __('建议每行至少添加10个合作伙伴以获得更好的滚动效果', 'zib_language') . '</div>',
                'type'                   => 'group',
                'button_title'           => __('添加合作伙伴', 'zib_language'),
                'accordion_title_auto'   => false,
                'accordion_title_number' => true,
                'default'                => array(),
                'fields'                 => array(
                    array(
                        'title'      => __('名称', 'zib_language'),
                        'id'         => 'title',
                        'desc'       => __('卡片第一行文案，建议简短', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'      => __('简介', 'zib_language'),
                        'id'         => 'desc',
                        'class'      => 'compact',
                        'desc'       => __('卡片第二行文案，支持 HTML（仅在图文卡片模式下显示）', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 2,
                        ),
                    ),
                    array(
                        'title'   => __('Logo图片', 'zib_language'),
                        'id'      => 'image',
                        'default' => '',
                        'preview' => true,
                        'library' => 'image',
                        'type'    => 'upload',
                        'desc'    => __('如果样式选择为图文卡片，则建议全部为正方形图片', 'zib_language'),
                    ),
                    array(
                        'id'           => 'link',
                        'type'         => 'link',
                        'title'        => __('跳转链接', 'zib_language'),
                        'default'      => array(),
                        'add_title'    => __('添加链接', 'zib_language'),
                        'edit_title'   => __('编辑链接', 'zib_language'),
                        'remove_title' => __('删除链接', 'zib_language'),
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_combo_info_card', array(
        'title'            => __('复合信息卡片', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('图标+标题+标签+简介+立即体验按钮的综合信息卡片，适合展示服务、应用、Agent 等内容', 'zib_language'),
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
                'id'       => 'pc_row',
                'title'    => __('排列布局', 'zib_language'),
                'subtitle' => __('PC端单行排列数量', 'zib_language'),
                'default'  => 4,
                'options'  => array(
                    2 => __('2个', 'zib_language'),
                    3 => __('3个', 'zib_language'),
                    4 => __('4个', 'zib_language'),
                    6 => __('6个', 'zib_language'),
                ),
                'type'     => 'button_set',
                'class'    => 'button-mini',
            ),
            array(
                'id'       => 'm_row',
                'title'    => ' ',
                'subtitle' => __('移动端单行排列数量', 'zib_language'),
                'class'    => 'compact button-mini',
                'default'  => 1,
                'options'  => array(
                    1 => __('1个', 'zib_language'),
                    2 => __('2个', 'zib_language'),
                ),
                'type'     => 'button_set',
            ),
            array(
                'id'       => 'pc_gap',
                'title'    => __('卡片间距', 'zib_language'),
                'subtitle' => __('PC端卡片间距', 'zib_language'),
                'default'  => 10,
                'options'  => array(
                    5  => '10px',
                    10 => '20px',
                ),
                'type'     => 'button_set',
            ),
            array(
                'id'       => 'm_gap',
                'title'    => ' ',
                'subtitle' => __('移动端卡片间距', 'zib_language'),
                'class'    => 'compact button-mini',
                'default'  => 5,
                'options'  => array(
                    5  => '10px',
                    10 => '20px',
                ),
                'type'     => 'button_set',
            ),
            array(
                'id'      => 'card_radius',
                'title'   => __('卡片圆角', 'zib_language'),
                'default' => 16,
                'max'     => 30,
                'min'     => 0,
                'step'    => 1,
                'unit'    => 'px',
                'type'    => 'slider',
            ),
            array(
                'id'      => 'top_band',
                'label'   => __('显示顶部渐变背景', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'id'         => 'top_band_height',
                'dependency' => array('top_band', '==', true),
                'title'      => __('色带高度', 'zib_language'),
                'default'    => 150,
                'max'        => 400,
                'min'        => 100,
                'step'       => 20,
                'unit'       => 'px',
                'type'       => 'slider',
            ),
            array(
                'id'      => 'go_link',
                'label'   => __('外链重定向', 'zib_language'),
                'help'    => __('开启后，将链接转为内部go跳转链接', 'zib_language'),
                'default' => true,
                'type'    => 'switcher',
            ),

            array(
                'id'      => 'cta_text',
                'title'   => __('默认按钮文案', 'zib_language'),
                'default' => '立即体验',
                'desc'    => __('每张卡片可单独覆盖', 'zib_language'),
                'type'    => 'text',
            ),
            //按钮颜色
            array(
                'id'      => 'cta_color',
                'title'   => __('默认按钮颜色', 'zib_language'),
                'class'   => 'skin-color',
                'default' => '',
                'type'    => 'palette',
                'options' => CFS_Module::zib_palette(
                    ['' => array('rgba(225, 225, 225, .8)')], ['c']
                ),
            ),

            array(
                'id'                     => 'cards',
                'title'                  => __('添加卡片', 'zib_language'),
                'type'                   => 'group',
                'button_title'           => __('添加卡片', 'zib_language'),
                'accordion_title_auto'   => true,
                'accordion_title_number' => true,
                'default'                => array(),
                'fields'                 => array(

                    array(
                        'title'      => __('卡片标题', 'zib_language'),
                        'id'         => 'title',
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'        => __('图标', 'zib_language'),
                        'id'           => 'icon',
                        'default'      => '',
                        'type'         => 'icon',
                        'button_title' => __('选择图标', 'zib_language'),
                    ),
                    array(
                        'title'   => __('图片图标', 'zib_language'),
                        'id'      => 'icon_image',
                        'default' => '',
                        'preview' => true,
                        'library' => 'image',
                        'type'    => 'upload',
                        'desc'    => __('图标和图片图标二选一，设置图片将覆盖图标', 'zib_language'),
                    ),
                    array(
                        'id'                     => 'tags',
                        'title'                  => __('标签', 'zib_language'),
                        'type'                   => 'group',
                        'button_title'           => __('添加标签', 'zib_language'),
                        'accordion_title_auto'   => true,
                        'accordion_title_number' => false,
                        'default'                => array(),
                        'fields'                 => array(
                            array(
                                'id'    => 'text',
                                'title' => __('标签文字', 'zib_language'),
                                'type'  => 'text',
                            ),
                            array(
                                'id'      => 'color',
                                'title'   => __('标签颜色', 'zib_language'),
                                'class'   => 'skin-color',
                                'default' => '',
                                'type'    => 'palette',
                                'options' => CFS_Module::zib_palette(
                                    ['' => array('rgba(225, 225, 225, 0.4)')]
                                ),
                            ),
                        ),
                    ),
                    array(
                        'title'      => __('简介描述', 'zib_language'),
                        'id'         => 'desc',
                        'desc'       => __('卡片描述文案，超出会自动省略（默认3行）', 'zib_language'),
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 3,
                        ),
                    ),
                    array(
                        'id'           => 'link',
                        'type'         => 'link',
                        'title'        => __('跳转链接', 'zib_language'),
                        'default'      => array(),
                        'add_title'    => __('添加链接', 'zib_language'),
                        'edit_title'   => __('编辑链接', 'zib_language'),
                        'remove_title' => __('删除链接', 'zib_language'),
                    ),
                    array(
                        'title'       => __('按钮文案（可选）', 'zib_language'),
                        'id'          => 'cta_text',
                        'type'        => 'text',
                        'placeholder' => __('留空则使用默认按钮文案', 'zib_language'),
                    ),
                    array(
                        'id'      => 'cta_color',
                        'title'   => __('按钮颜色（可选）', 'zib_language'),
                        'class'   => 'skin-color',
                        'default' => '',
                        'type'    => 'palette',
                        'options' => CFS_Module::zib_palette(
                            ['' => array('rgba(225, 225, 225, .8)')], ['c']
                        ),
                    ),
                    array(
                        'id'      => 'band_color',
                        'title'   => __('顶部色带配色', 'zib_language'),
                        'default' => 'auto',
                        'options' => array(
                            'auto'   => __('自动（按序循环）', 'zib_language'),
                            'blue'   => __('蓝紫', 'zib_language'),
                            'cyan'   => __('青蓝', 'zib_language'),
                            'green'  => __('青绿', 'zib_language'),
                            'yellow' => __('橙黄', 'zib_language'),
                            'pink'   => __('粉红', 'zib_language'),
                            'purple' => __('紫罗兰', 'zib_language'),
                            'gray'   => __('中性灰', 'zib_language'),
                        ),
                        'type'    => 'select',
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_text_title', array(
        'title'            => __('文字标题', 'zib_language'),
        'zib_title'        => false,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('显示标题、简介的文字模块，配置丰富，更加适合模块独立标题、简介的场景', 'zib_language'),
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
                'title'      => __('标题', 'zib_language'),
                'id'         => 'title',
                'type'       => 'textarea',
                'attributes' => array(
                    'rows' => 2,
                ),
                'desc'       => __('注意：如果开启了入场动画，则不能使用代码，只能为纯文字！', 'zib_language'),
            ),

            //标题前图标
            array(
                'title'        => ' ',
                'subtitle'     => __('标题前添加图标', 'zib_language'),
                'id'           => 'title_icon_left',
                'type'         => 'icon',
                'button_title' => __('添加图标', 'zib_language'),
                'default'      => '',
            ),
            array(
                'title'        => ' ',
                'subtitle'     => __('标题结尾添加图标', 'zib_language'),
                'id'           => 'title_icon_right',
                'class'        => 'compact',
                'type'         => 'icon',
                'button_title' => __('添加图标', 'zib_language'),
                'default'      => '',
            ),
            array(
                'id'       => 'title_icon_width',
                'title'    => ' ',
                'subtitle' => __('图标宽度(0为字体宽度，只对SVG图标有效)', 'zib_language'),
                'type'     => 'spinner',
                'class'    => 'compact',
                'default'  => 0,
                'max'      => 500,
                'min'      => 0,
                'step'     => 4,
                'unit'     => 'px',
            ),

            array(
                'title'      => __('简介', 'zib_language'),
                'id'         => 'desc',
                'type'       => 'textarea',
                'attributes' => array(
                    'rows' => 2,
                ),
                'desc'       => __('注意：如使用html代码，请注意代码规范！', 'zib_language'),
            ),

            array(
                'title'   => __('对齐方式', 'zib_language'),
                'id'      => 'align',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'center',
                'options' => array(
                    'left'   => __('左对齐', 'zib_language'),
                    'center' => __('居中', 'zib_language'),
                    'right'  => __('右对齐', 'zib_language'),
                ),
            ),

            array(
                'id'      => 'title_opts',
                'title'   => __('标题样式', 'zib_language'),
                'type'    => 'fieldset',
                'default' => array(),
                'fields'  => array(
                    array(
                        'id'      => 'color',
                        'title'   => __('颜色', 'zib_language'),
                        'class'   => 'skin-color',
                        'default' => 'key-color',
                        'type'    => 'palette',
                        'options' => CFS_Module::zib_palette(array('' => array('#4f5359')), array('text', 'cg')),
                    ),
                    array(
                        'id'      => 'pc',
                        'title'   => __('PC端样式', 'zib_language'),
                        'type'    => 'fieldset',
                        'default' => array(),
                        'fields'  => zib_widget_ui_get_text_title_opts_fields(),
                    ),
                    array(
                        'id'      => 'm',
                        'title'   => __('移动端样式', 'zib_language'),
                        'type'    => 'fieldset',
                        'default' => array(),
                        'fields'  => zib_widget_ui_get_text_title_opts_fields(),
                    ),
                ),
            ),

            array(
                'id'      => 'desc_opts',
                'title'   => __('简介样式', 'zib_language'),
                'type'    => 'fieldset',
                'default' => array(),
                'fields'  => array(
                    array(
                        'id'      => 'color',
                        'title'   => __('颜色', 'zib_language'),
                        'class'   => 'skin-color',
                        'default' => 'muted-color',
                        'type'    => 'palette',
                        'options' => CFS_Module::zib_palette(array('' => array('#4f5359')), array('text', 'cg')),
                    ),
                    array(
                        'id'      => 'pc',
                        'title'   => __('PC端样式', 'zib_language'),
                        'type'    => 'fieldset',
                        'default' => array(),
                        'fields'  => zib_widget_ui_get_text_title_opts_fields(true),
                    ),
                    array(
                        'id'      => 'm',
                        'title'   => __('移动端样式', 'zib_language'),
                        'type'    => 'fieldset',
                        'default' => array(),
                        'fields'  => zib_widget_ui_get_text_title_opts_fields(true),
                    ),
                ),
            ),

        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_buttons', array(
        'title'            => __('按钮组', 'zib_language'),
        'zib_title'        => false,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'description'      => __('显示单个或多个跳转按钮，支持多种样式显示', 'zib_language'),
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
                'id'       => 'pc_width',
                'title'    => __('按钮宽度', 'zib_language'),
                'subtitle' => __('PC端宽度（0为自动）', 'zib_language'),
                'type'     => 'spinner',
                'default'  => 0,
                'max'      => 500,
                'min'      => 0,
                'step'     => 4,
                'unit'     => 'px',
            ),
            array(
                'id'       => 'm_width',
                'title'    => ' ',
                'subtitle' => __('移动端宽度（0为自动）', 'zib_language'),
                'type'     => 'spinner',
                'class'    => 'compact',
                'default'  => 0,
                'max'      => 500,
                'min'      => 0,
                'step'     => 4,
                'unit'     => 'px',
            ),
            array(
                'id'       => 'pc_padding',
                'title'    => __('按钮尺寸', 'zib_language'),
                'subtitle' => __('PC端按钮尺寸', 'zib_language'),
                'type'     => 'radio',
                'inline'   => true,
                'default'  => 'large',
                'options'  => array(
                    'small'  => __('小尺寸', 'zib_language'),
                    'medium' => __('中尺寸', 'zib_language'),
                    'large'  => __('大尺寸', 'zib_language'),
                ),
            ),
            array(
                'id'       => 'm_padding',
                'title'    => ' ',
                'subtitle' => __('移动端按钮尺寸', 'zib_language'),
                'type'     => 'radio',
                'inline'   => true,
                'class'    => 'compact',
                'default'  => 'medium',
                'options'  => array(
                    'small'  => __('小尺寸', 'zib_language'),
                    'medium' => __('中尺寸', 'zib_language'),
                    'large'  => __('大尺寸', 'zib_language'),
                ),
            ),
            array(
                'id'       => 'pc_gap',
                'title'    => __('按钮间距', 'zib_language'),
                'subtitle' => __('PC端按钮间距', 'zib_language'),
                'type'     => 'spinner',
                'default'  => 12,
                'max'      => 60,
                'min'      => 0,
                'step'     => 4,
                'unit'     => 'px',
            ),
            array(
                'id'       => 'm_gap',
                'title'    => ' ',
                'subtitle' => __('移动端按钮间距', 'zib_language'),
                'type'     => 'spinner',
                'class'    => 'compact',
                'default'  => 6,
                'max'      => 60,
                'min'      => 0,
                'step'     => 4,
                'unit'     => 'px',
            ),

            array(
                'title'   => __('对齐方式', 'zib_language'),
                'id'      => 'align',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'center',
                'options' => array(
                    'left'   => __('左对齐', 'zib_language'),
                    'center' => __('居中', 'zib_language'),
                    'right'  => __('右对齐', 'zib_language'),
                ),
            ),

            array(
                'title'   => __('两端圆角', 'zib_language'),
                'id'      => 'radius',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('空心样式', 'zib_language'),
                'id'      => 'hollow',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('外链重定向', 'zib_language'),
                'id'      => 'go_link',
                'type'    => 'switcher',
                'default' => true,
                'help'    => __('开启后，将链接转为内部go跳转链接', 'zib_language'),
            ),

            array(
                'id'                     => 'items',
                'title'                  => __('添加按钮', 'zib_language'),
                'type'                   => 'group',
                'button_title'           => __('添加按钮', 'zib_language'),
                'accordion_title_auto'   => false,
                'accordion_title_number' => true,
                'default'                => array(),
                'fields'                 => array(
                    array(
                        'title' => __('按钮文字', 'zib_language'),
                        'id'    => 'text',
                        'type'  => 'text',
                    ),
                    array(
                        'title'        => ' ',
                        'subtitle'     => __('按钮前图标', 'zib_language'),
                        'class'        => 'compact',
                        'id'           => 'icon_left',
                        'type'         => 'icon',
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => '',
                    ),
                    array(
                        'title'        => ' ',
                        'class'        => 'compact',
                        'subtitle'     => __('按钮后图标', 'zib_language'),
                        'id'           => 'icon_right',
                        'type'         => 'icon',
                        'button_title' => __('选择图标', 'zib_language'),
                        'default'      => '',
                    ),
                    array(
                        'title'        => __('按钮链接', 'zib_language'),
                        'id'           => 'link',
                        'type'         => 'link',
                        'add_title'    => __('添加链接', 'zib_language'),
                        'edit_title'   => __('编辑链接', 'zib_language'),
                        'remove_title' => __('删除链接', 'zib_language'),
                    ),
                    array(
                        'id'      => 'color',
                        'title'   => __('按钮颜色', 'zib_language'),
                        'class'   => 'skin-color',
                        'default' => '',
                        'type'    => 'palette',
                        'desc'    => __('注意：开启空心样式后，不能选择渐变色', 'zib_language'),
                        'options' => CFS_Module::zib_palette(
                            ['' => array('rgba(225, 225, 225, 0.4)')]
                        ),
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_layout_gap', array(
        'title'            => __('布局-额外间距', 'zib_language'),
        'zib_title'        => false,
        'zib_affix'        => false,
        'zib_show'         => false,
        'zib_animation_in' => false,
        'description'      => __('用于增加模块上下之间的间距', 'zib_language'),
        'fields'           => array(
            array(
                'id'      => 'pc_gap',
                'title'   => __('PC端间隔', 'zib_language'),
                'type'    => 'spinner',
                'default' => 10,
                'max'     => 60,
                'min'     => 0,
                'step'    => 4,
                'unit'    => 'px',
            ),
            array(
                'id'      => 'm_gap',
                'title'   => __('移动端间隔', 'zib_language'),
                'type'    => 'spinner',
                'default' => 5,
                'max'     => 60,
                'min'     => 0,
                'step'    => 4,
                'unit'    => 'px',
            ),
        ),
    ));

    Zib_CFSwidget::create('widget_ui_tag_cloud', array(
        'title'            => __('标签云', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => true,
        'callback'         => 'zib_widget_ui_tag_cloud',
        'description'      => __('显示标签、分类、专题的标签云样式，颜色更丰富', 'zib_language'),
        'fields'           => array(
            array(
                'id'      => 'taxonomy',
                'title'   => __('分类法', 'zib_language'),
                'type'    => 'select',
                'options' => 'zib_widget_ui_tag_get_taxonomies',
            ),
            array(
                'id'      => 'number',
                'title'   => __('数量', 'zib_language'),
                'type'    => 'spinner',
                'default' => 20,
                'max'     => 100,
                'min'     => 1,
                'step'    => 1,
            ),
            array(
                'id'      => 'orderby',
                'title'   => __('排序方式', 'zib_language'),
                'type'    => 'select',
                'options' => array(
                    'name'    => __('名称', 'zib_language'),
                    'count'   => __('文章数量', 'zib_language'),
                    'term_id' => __('创建时间', 'zib_language'),
                ),
                'default' => 'name',
            ),
            array(
                'id'      => 'order',
                'type'    => 'radio',
                'inline'  => true,
                'class'   => 'compact',
                'options' => array(
                    'ASC'  => __('升序', 'zib_language'),
                    'DESC' => __('降序', 'zib_language'),
                ),
                'default' => 'ASC',
            ),
            array(
                'id'      => 'color',
                'title'   => __('颜色', 'zib_language'),
                'type'    => 'select',
                'options' => array(
                    'rand'       => __('随机颜色', 'zib_language'),
                    'c-hui'      => __('灰色', 'zib_language'),
                    'c-blue'     => __('蓝色', 'zib_language'),
                    'c-blue-2'   => __('深蓝色', 'zib_language'),
                    'c-cyan'     => __('青色', 'zib_language'),
                    'c-yellow'   => __('黄色', 'zib_language'),
                    'c-yellow-2' => __('橙黄色', 'zib_language'),
                    'c-green'    => __('绿色', 'zib_language'),
                    'c-green-2'  => __('墨绿色', 'zib_language'),
                    'c-purple'   => __('紫色', 'zib_language'),
                    'c-purple-2' => __('深紫色', 'zib_language'),
                    'c-red'      => __('粉红色', 'zib_language'),
                    'c-red-2'    => __('红色', 'zib_language'),
                ),
                'default' => 'rand',
            ),
            array(
                'id'      => 'fixed_width',
                'title'   => __('固定宽度', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'id'      => 'blank',
                'title'   => __('新窗口打开', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'id'      => 'show_count',
                'title'   => __('显示标签计数', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));

    Zib_CFSwidget::create('widget_ui_new_comment', array(
        'title'            => __('最近评论', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => true,
        'callback'         => 'zib_widget_ui_new_comment',
        'description'      => __('显示网站最新的评论', 'zib_language'),
        'fields'           => array(
            array(
                'id'      => 'limit',
                'title'   => __('显示数量', 'zib_language'),
                'type'    => 'spinner',
                'default' => 8,
                'min'     => 1,
                'max'     => 50,
                'step'    => 1,
            ),
            array(
                'title' => __('排除用户ID', 'zib_language'),
                'id'    => 'outer',
                'type'  => 'text',
                'desc'  => __('填写需要排除的用户ID，用逗号分割，例如：55,66,100', 'zib_language'),
            ),
            array(
                'title' => __('排除文章ID', 'zib_language'),
                'id'    => 'outpost',
                'type'  => 'text',
                'desc'  => __('填写需要排除的文章ID，用逗号分割，例如：55,66,100', 'zib_language'),
            ),
            array(
                'id'      => 'show_source',
                'title'   => __('显示来源', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'id'      => 'style',
                'title'   => __('显示样式', 'zib_language'),
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'card',
                'options' => array(
                    ''        => __('简约', 'zib_language'),
                    'style_2' => __('风格2', 'zib_language'),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('widget_ui_links_lists_2', array(
        'title'            => __('链接列表', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => true,
        'callback'         => 'zib_widget_ui_links_lists',
        'description'      => __('链接列表显示，适合友情链接、网址导航等场景', 'zib_language'),
        'fields'           => array(
            array(
                'id'          => 'links_cats',
                'title'       => __('选择链接分类', 'zib_language'),
                'placeholder' => __('选择分类', 'zib_language'),
                'default'     => [],
                'type'        => 'select',
                'options'     => 'categories',
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
                'query_args'  => array(
                    'taxonomy' => array('link_category'),
                    'orderby'  => 'taxonomy',
                ),
                'desc'        => __('选择要显示的链接分类，留空则为全部', 'zib_language'),
            ),
            array(
                'id'      => 'links_limit',
                'title'   => __('最大显示数量', 'zib_language'),
                'type'    => 'spinner',
                'default' => 12,
                'min'     => 0,
                'max'     => 100,
                'step'    => 1,
            ),
            array(
                'id'      => 'links_orderby',
                'title'   => __('排序方式', 'zib_language'),
                'type'    => 'radio',
                'inline'  => true,
                'default' => '',
                'options' => array(
                    'name'    => __('名称', 'zib_language'),
                    'updated' => __('更新时间', 'zib_language'),
                    'rating'  => __('评分', 'zib_language'),
                    'rand'    => __('随机', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'links_order',
                'type'    => 'radio',
                'class'   => 'compact',
                'inline'  => true,
                'default' => 'ASC',
                'options' => array(
                    'ASC'  => __('升序', 'zib_language'),
                    'DESC' => __('降序', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'show_box',
                'title'   => __('显示外框盒子', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),

            array(
                'id'      => 'go_link',
                'title'   => __('外部重定向', 'zib_language'),
                'type'    => 'switcher',
                'default' => true,
                'desc'    => __('外部链接转为内部go跳转链接', 'zib_language'),
            ),
            array(
                'id'      => 'nofollow',
                'title'   => __('添加nofollow标记', 'zib_language'),
                'type'    => 'switcher',
                'default' => true,
                'desc'    => __('添加nofollow标记，用于告知搜索引擎建议不抓取', 'zib_language'),
            ),
            array(
                'id'      => 'blank',
                'title'   => __('新窗口打开', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
                'desc'    => __('新窗口打开外部链接', 'zib_language'),
            ),

            array(
                'id'      => 'alignment',
                'title'   => __('对齐方式', 'zib_language'),
                'type'    => 'radio',
                'inline'  => true,
                'default' => '',
                'options' => array(
                    ''       => __('左对齐', 'zib_language'),
                    'center' => __('居中', 'zib_language'),
                    'right'  => __('右对齐', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'type',
                'title'   => __('显示样式', 'zib_language'),
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'card',
                'options' => array(
                    'card'    => __('图文模式', 'zib_language'),
                    'bigcard' => __('卡片模式', 'zib_language'),
                    'image'   => __('纯图模式', 'zib_language'),
                    'simple'  => __('极简模式', 'zib_language'),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('widget_ui_notice', array(
        'title'            => __('上下滚动公告消息', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => true,
        'callback'         => 'zib_widget_ui_notice',
        'description'      => __('上下自动滚动的信息列表，可作为公告栏或调用文章、帖子等内容展示', 'zib_language'),
        'fields'           => array(
            array(
                'id'      => 'blank',
                'label'   => __('链接新窗口打开', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'id'      => 'radius',
                'label'   => __('两边显示为圆形', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'id'      => 'interval',
                'title'   => __('滚动间隔', 'zib_language'),
                'type'    => 'spinner',
                'default' => 3,
                'min'     => 1,
                'max'     => 10,
                'step'    => 1,
                'unit'    => '秒',
            ),
            array(
                'id'      => 'color',
                'title'   => __('颜色', 'zib_language'),
                'class'   => 'skin-color',
                'default' => 'c-blue',
                'type'    => 'palette',
                'options' => CFS_Module::zib_palette(),
            ),
            array(
                'id'      => 'source',
                'title'   => __('内容来源', 'zib_language'),
                'type'    => 'radio',
                'inline'  => true,
                'default' => '',
                'options' => array(
                    ''       => __('自定义', 'zib_language'),
                    'posts'  => __('文章', 'zib_language'),
                    'forums' => __('论坛帖子', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('source', '!=', ''),
                'id'      => 'msg_icon',
                'title'   => __('消息图标', 'zib_language'),
                'type'    => 'icon',
                'default' => 'zibsvg-hot',
            ),
            array(
                'dependency' => array('source', '!=', ''),
                'id'      => 'count',
                'title'   => __('获取帖子或文章数量', 'zib_language'),
                'type'    => 'spinner',
                'default' => 8,
                'min'     => 1,
                'max'     => 50,
                'step'    => 1,
            ),
            array(
                'id'      => 'desc_s',
                'label'   => __('显示简介(关闭则只显示标题)', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('source', '==', 'forums'),
                'id'         => 'forums_opts',
                'title'      => __('论坛帖子筛选', 'zib_language'),
                'type'       => 'fieldset',
                'default'    => array(),
                'fields'     => array(
                    array(
                        'content' => __('注意：依赖于社区论坛功能，请确保已开启社区论坛', 'zib_language'),
                        'style' => 'warning',
                        'type'  => 'submessage',
                    ),
                    array(
                        'id'          => 'plate',
                        'title'       => __('包含版块', 'zib_language'),
                        'desc'        => __('仅显示所选版块的帖子，支持单选、多选。输入版块关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'post',
                        'query_args'  => array(
                            'post_type' => 'plate',
                        ),
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'placeholder' => __('输入关键词以搜索', 'zib_language'),
                        'chosen'      => true,
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'dependency'  => array('plate', '==', '', '', 'visible'),
                        'id'          => 'plate_exclude',
                        'title'       => __('排除版块', 'zib_language'),
                        'desc'        => __('排除所选版块的帖子，支持单选、多选。输入版块关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'post',
                        'query_args'  => array(
                            'post_type' => 'plate',
                        ),
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'placeholder' => __('输入关键词以搜索版块分类', 'zib_language'),
                        'chosen'      => true,
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'id'          => 'topic',
                        'title'       => __('包含话题', 'zib_language'),
                        'desc'        => __('仅显示所选话题的帖子，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy' => 'forum_topic',
                        ),
                        'placeholder' => __('输入关键词以搜索', 'zib_language'),
                        'chosen'      => true,
                        'multiple'    => true,
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'type'        => 'select',
                    ),
                    array(
                        'id'          => 'tag',
                        'title'       => __('包含标签', 'zib_language'),
                        'desc'        => __('仅显示所选标签的帖子，支持单选、多选。输入关键词搜索选择', 'zib_language'),
                        'default'     => '',
                        'options'     => 'categories',
                        'query_args'  => array(
                            'taxonomy' => 'forum_tag',
                        ),
                        'placeholder' => __('输入关键词以搜索', 'zib_language'),
                        'chosen'      => true,
                        'ajax'        => true,
                        'settings'    => array(
                            'min_length' => 2,
                        ),
                        'multiple'    => true,
                        'type'        => 'select',
                    ),
                    array(
                        'title'       => __('类型筛选', 'zib_language'),
                        'id'          => 'bbs_type',
                        'default'     => '',
                        'type'        => 'checkbox',
                        'placeholder' => __('限制帖子类型，支持单选、多选', 'zib_language'),
                        'inline'      => true,
                        'options'     => 'zib_bbs_get_posts_type_options',
                    ),
                    array(
                        'title'       => __('阅读权限筛选', 'zib_language'),
                        'id'          => 'allow_view',
                        'default'     => [],
                        'inline'      => true,
                        'type'        => 'checkbox',
                        'placeholder' => __('不做其它筛选', 'zib_language'),
                        'options'     => array(
                            'signin'  => __('登录后查看', 'zib_language'),
                            'comment' => __('评论后查看', 'zib_language'),
                            'pay'     => __('付费查看', 'zib_language'),
                            'points'  => __('支付积分查看', 'zib_language'),
                            'roles'   => __('部分用户可查看', 'zib_language'),
                        ),
                    ),
                    array(
                        'title'   => __('其它筛选', 'zib_language'),
                        'id'      => 'filter',
                        'default' => [],
                        'inline'  => true,
                        'type'    => 'checkbox',
                        'options' => array(
                            'topping'         => __('置顶帖子', 'zib_language'),
                            'vote'            => __('投票帖子', 'zib_language'),
                            'essence'         => __('精华帖子', 'zib_language'),
                            'question_status' => __('提问已解决', 'zib_language'),
                            'is_hot'          => __('热门帖子', 'zib_language'),
                        ),
                    ),
                    array(
                        'title'   => __('排序方式', 'zib_language'),
                        'id'      => 'orderby',
                        'default' => 'date',
                        'type'    => 'select',
                        'options' => zib_bbs_get_posts_order_options(),
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
            array(
                'dependency' => array('source', '==', 'posts'),
                'id'         => 'posts_opts',
                'title'      => __('文章筛选', 'zib_language'),
                'type'       => 'fieldset',
                'default'    => array(),
                'fields'     => array(
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
                ),
            ),

            array(
                'dependency' => array('source', '==', ''),
                'id'         => 'img_ids',
                'label'      => __('消息列表', 'zib_language'),
                'type'       => 'group',
                'default'    => [
                    [
                        'title' => __('子比主题，更优雅的Wordpress主题', 'zib_language'),
                        'icon'  => 'zibsvg-hot',
                        'href'  => 'https://zibll.com',
                    ],
                    [
                        'title' => __('更优雅的WordPress网站主题：子比主题！全面开启', 'zib_language'),
                        'icon'  => 'zibsvg-hot',
                        'href'  => 'https://zibll.com',
                    ],
                ],
                'fields'     => array(
                    array(
                        'id'          => 'title',
                        'placeholder' => __('消息内容', 'zib_language'),
                        'type'        => 'text',
                        'default'     => '',
                    ),
                    array(
                        'id'      => 'icon',
                        'title'   => __('图标', 'zib_language'),
                        'type'    => 'icon',
                        'default' => 'fa-home',
                    ),
                    array(
                        'id'      => 'href',
                        'title'   => __('跳转链接', 'zib_language'),
                        'type'    => 'text',
                        'default' => '',
                    ),
                ),
            ),
        ),
    ));

}

function zib_widget_ui_notice($args, $instance)
{

    $interval   = (int) $instance['interval'];
    $interval   = $interval ? ' data-interval="' . ($instance['interval'] * 1000) . '"' : '';
    $lists_data = $instance['img_ids'];
    $posts_query = false;
    if ($instance['source'] == 'forums' && _pz('bbs_s') && function_exists('zib_bbs_get_posts_query')) {
        $query_args = $instance['forums_opts'];
        $query_args['paged_size'] = $instance['count'];
        $query_args['paged'] = 1;
        $query_args['no_found_rows'] = true;
        $query_args['plate'] = $query_args['plate']??0;
        $posts_query = zib_bbs_get_posts_query($query_args);
    } elseif ($instance['source'] == 'posts') {
        $query_args = $instance['posts_opts'];
        $query_args['no_found_rows'] = true;
        $posts_query = zib_get_posts_query($query_args);
    }

    $msg_icon = '';
    if($posts_query) {
        $lists_data = array();
        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            global $post;
            $subtitle = trim(strip_tags(zib_get_post_meta($post->ID, 'subtitle', true)));
            $title = trim(strip_tags(get_the_title($post))) . $subtitle;
        
            if(!empty($instance['desc_s'])) {
                $title .= ': '.zib_get_excerpt(160, '...', $post);
            }

            $lists_data[] = array(
                'title' => $title,
                'href' => get_the_permalink(),
            );
        }
        wp_reset_postdata();
        $msg_icon = empty($instance['msg_icon']) ? '' : '<span class="relative bulletin-icon flex jc shrink0">' . zib_get_cfs_icon($instance['msg_icon']) . '</span>';
    }


    $i      = 0;
    $slides = '';
    $blank  = empty($instance['blank']) ? '' : ' target="_blank"';
    foreach ($lists_data as $notice) {
        if (!empty($notice['title'])) {
            $href    = empty($notice['href']) ? 'javascript:void(0);' : $notice['href'];
            $title   = empty($notice['title']) ? '' : $notice['title'];
            $icon    = empty($notice['icon']) ? '' : '<span class="relative bulletin-icon flex jc shrink0">' . zib_get_cfs_icon($notice['icon']) . '</span>';
            $s_class = ' notice-slide';
            $slides .= '<div class="swiper-slide' . ' ' . $s_class . '">
            <a class="flex ac"' . $blank . ($href ? ' href="' . $href . '"' : '') . '>
                '. $icon .'<span class="ml6 text-ellipsis"><span class="">'. $title . '</span></span>
            </a>
            </div>';
            $i++;
        }
    }

    $html = '<div class="new-swiper" ' . $interval . ' data-direction="vertical" data-loop="true" data-autoplay="1">
                <div class="swiper-wrapper">' . $slides . '</div>
            </div>';

    $class = 'swiper-bulletin mb20 ' . $instance['color'] . ($instance['radius'] ? ' radius' : ' main-radius') . ($posts_query ? ' has-posts flex ac' : '');
    

    echo '<div class="' . $class . '">' .$msg_icon. $html . '</div>';
}