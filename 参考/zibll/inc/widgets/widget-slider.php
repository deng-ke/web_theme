<?php

add_action('widgets_init', 'widget_register_slider');
function widget_register_slider()
{
    register_widget('widget_ui_slider');
}

class widget_ui_slider extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_slider',
            'w_name'      => _name(__('幻灯片(老版即将删除)', 'zib_language')),
            'classname'   => '',
            'description' => __('老版幻灯片模块（！即将删除）', 'zib_language'),
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }

    public function form($instance)
    {
        $defaults = array(
            'in_affix'    => '',
            'class'       => 'slide-widget',
            'loop'        => 'on',
            'type'        => '',
            'effect'      => 'slide',
            'blank'       => '',
            'button'      => 'on',
            'pagination'  => 'on',
            'interval'    => 4,
            'null'        => '',
            'auto_height' => '',
            'pc_height'   => 400,
            'm_height'    => 180,
            'img_ids'     => array(),
        );

        $defaults['img_ids'][] = array(
            'title' => '',
            'dec'   => '',
            'href'  => '',
            'link'  => '',
        );

        $defaults['img_ids'][] = array(
            'title' => '',
            'dec'   => '',
            'href'  => '',
            'link'  => '',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $img_html = '';
        $img_i    = 0;

        foreach ($instance['img_ids'] as $category) {
            $_html_a = '<label>' . sprintf(__('幻灯片%d-标题', 'zib_language'), $img_i + 1) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].title" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][title]" value="' . $instance['img_ids'][$img_i]['title'] . '" /></label>';
            $_html_b = '<label>' . sprintf(__('幻灯片%d-简介', 'zib_language'), $img_i + 1) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].dec" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][dec]" value="' . $instance['img_ids'][$img_i]['dec'] . '" /></label>';
            $_html_b .= '<label>' . sprintf(__('幻灯片%d-链接', 'zib_language'), $img_i + 1) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].href" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][href]" value="' . $instance['img_ids'][$img_i]['href'] . '" /></label>';

            $_html_c = '<label>' . sprintf(__('幻灯片%d-图片', 'zib_language'), $img_i + 1) . '<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].link" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][link]" value="' . $instance['img_ids'][$img_i]['link'] . '" /></label>';

            $_html_d = '<div class="">' . $_html_c . '<button type="button" class="button ashu_upload_button">' . __('选择图片', 'zib_language') . '</button><button type="button" class="button delimg_upload_button">' . __('移除图片', 'zib_language') . '</button><div class="widget_ui_slider_box"><img src="' . $instance['img_ids'][$img_i]['link'] . '"></div></div>';

            $_tt  = '<div class="panel"><h4 class="panel-title">' . sprintf(__('幻灯片%d：', 'zib_language'), $img_i + 1) . $instance['img_ids'][$img_i]['title'] . '</h4><div class="panel-hide panel-conter">';
            $_tt2 = '</div></div>';

            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_html_d . $_tt2 . '</div>';
            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . $this->get_field_name('img_ids') . '" data-count="' . $img_i . '" class="button add_button add_slider_button">' . __('添加幻灯片', 'zib_language') . '</button>';
        $add_b .= '<button type="button" data-name="' . $this->get_field_name('img_ids') . '" data-count="' . $img_i . '" class="button rem_lists_button">' . __('删除幻灯片', 'zib_language') . '</button>';
        $img_html .= $add_b;
        //echo '<pre>' . json_encode($instance) . '</pre>';
        echo '<p style=" color: #f03131; ">' . __('此模块为旧版，下次更新会删除此模块，不再推荐使用！请使用更强大的：zibll幻灯片(新)替代此功能', 'zib_language') . '</p>';

        ?>
        <p>
            <label>
                <?php _e('播放速度（每张停留时间多少秒）：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('interval');
        ?>" name="<?php echo $this->get_field_name('interval');
        ?>" type="number" value="<?php echo $instance['interval'];
        ?>" size="24" />
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> <?php _e('侧栏随动（仅在侧边栏有效）', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['loop'], 'on'); ?> id="<?php echo $this->get_field_id('loop'); ?>" name="<?php echo $this->get_field_name('loop'); ?>"> <?php _e('循环播放', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['button'], 'on'); ?> id="<?php echo $this->get_field_id('button'); ?>" name="<?php echo $this->get_field_name('button'); ?>"> <?php _e('显示翻页按钮', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['pagination'], 'on'); ?> id="<?php echo $this->get_field_id('pagination'); ?>" name="<?php echo $this->get_field_name('pagination'); ?>"> <?php _e('显示指示器', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['blank'], 'on'); ?> id="<?php echo $this->get_field_id('blank'); ?>" name="<?php echo $this->get_field_name('blank'); ?>"> <?php _e('链接新窗口打开', 'zib_language'); ?>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['auto_height'], 'on'); ?> id="<?php echo $this->get_field_id('auto_height'); ?>" name="<?php echo $this->get_field_name('auto_height'); ?>"> <?php _e('自动高度', 'zib_language'); ?>
            </label>
        <div>
            <label>
                <i style="width:100%;color:#f60;"><?php _e('如果勾选了自动高度，下方的固定高度则不生效', 'zib_language'); ?></i>
            </label>
        </div>

        </p>
        <p>
            <label>
                <?php _e('（固定高度）电脑端高度：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('pc_height');
        ?>" name="<?php echo $this->get_field_name('pc_height');
        ?>" type="number" value="<?php echo $instance['pc_height'];
        ?>" size="24" />
            </label>
        </p>
        <p>
            <label>
                <?php _e('（固定高度）手机端高度：', 'zib_language'); ?>
                <input style="width:100%;" id="<?php echo $this->get_field_id('m_height');
        ?>" name="<?php echo $this->get_field_name('m_height');
        ?>" type="number" value="<?php echo $instance['m_height'];
        ?>" size="24" />
            </label>
        </p>

        <p>
            <label>
                <?php _e('切换动画：', 'zib_language'); ?>
                <select style="width:100%;" id="<?php echo $this->get_field_id('effect');
        ?>" name="<?php echo $this->get_field_name('effect');
        ?>">
                    <option value="slide" <?php selected('slide', $instance['effect']);
        ?>><?php _e('滑动', 'zib_language'); ?></option>
                    <option value="fade" <?php selected('fade', $instance['effect']);
        ?>><?php _e('淡出淡入', 'zib_language'); ?></option>
                    <option value="cube" <?php selected('cube', $instance['effect']);
        ?>><?php _e('3D方块', 'zib_language'); ?></option>
                    <option value="coverflow" <?php selected('coverflow', $instance['effect']);
        ?>><?php _e('3D滑入', 'zib_language'); ?></option>
                    <option value="flip" <?php selected('flip', $instance['effect']);
        ?>><?php _e('3D翻转', 'zib_language'); ?></option>
                </select>
            </label>
        </p>
        <div class="widget_ui_slider_lists">
            <?php echo $img_html; ?>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox hide" type="checkbox" <?php checked($instance['null'], 'on'); ?> id="<?php echo $this->get_field_id('null'); ?>" name="<?php echo $this->get_field_name('null'); ?>"><a class="button ok_button"><?php _e('应用', 'zib_language'); ?></a>
            </label>

        </div>
        <p>
            <label>
                <i style="width:100%;color:#f60;"><?php _e('由于WordPress小工具逻辑，会导致后插入的幻灯片偶尔无法保存，请多试几次即可', 'zib_language'); ?></i>
            </label>
        </p>

        <?php wp_enqueue_media(); ?>
    <?php
}

    public function widget($args, $instance)
    {

        extract($args);

        $defaults = array(
            'class'     => 'slide-widget',
            'in_affix'  => '',
            'type'      => '',
            'effect'    => 'slide',
            'interval'  => 4,
            'null'      => '',
            'pc_height' => 400,
            'm_height'  => 180,
            'img_ids'   => array(),
        );

        $defaults['img_ids'][] = array(
            'title' => '',
            'dec'   => '',
            'href'  => '',
            'link'  => '',
        );

        $defaults['img_ids'][] = array(
            'title' => '',
            'dec'   => '',
            'href'  => '',
            'link'  => '',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $sliders = array(
            'class'       => 'slide-widget',
            'loop'        => !empty($instance['loop']) ? true : false,
            'type'        => '',
            'button'      => !empty($instance['button']) ? true : false,
            'pagination'  => !empty($instance['pagination']) ? true : false,
            'pc_height'   => $instance['pc_height'],
            'm_height'    => $instance['m_height'],
            'effect'      => $instance['effect'],
            'auto_height' => !empty($instance['auto_height']) ? true : false,
            'interval'    => (int) $instance['interval'] * 1000,
        );

        foreach ($instance['img_ids'] as $slide_img) {
            $desc = '<p class="em14">' . $slide_img['title'] . '</p>' . $slide_img['dec'];
            if ($slide_img['link']) {
                $slide = array(
                    'href'  => $slide_img['href'],
                    'image' => $slide_img['link'],
                    'blank' => !empty($instance['blank']) ? true : false,
                    'desc'  => $desc,
                );
                $sliders['slides'][] = $slide;
            }
        }
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo $in_affix ? '<div' . $in_affix . '>' : '';

        // echo '<pre>'.json_encode($instance).'</pre>';
        zib_get_img_slider($sliders);
        echo $in_affix ? '</div>' : '';

        ?>

<?php
}
}

function zib_widget_ui_slider($args, $instance)
{
    if (empty($instance['slides'])) {
        if (is_super_admin()) {
            echo '<div class="c-red muted-box mb20"><b>' . sprintf(__('[%s]幻灯片模块：', 'zib_language'), __('幻灯片(新)', 'zib_language')) . '</b>' . __('当前配置下没有可显示的内容，请正确配置或移出该模块', 'zib_language') . '</div>';
        }
        return;
    }

    $header_slider        = $instance['slides'];
    $header_slider_option = $instance['option'];

    //判断配置是否为空
    if (!is_array($header_slider) || !is_array($header_slider_option) || empty($header_slider[0])) {
        return;
    }

    $header_slider_option['class']  = 'slide-widget mb20';
    $header_slider_option['slides'] = $header_slider;

    zib_new_slider($header_slider_option);
}
add_action('after_setup_theme', 'zib_widget_register_cfs_slider');

function zib_widget_register_cfs_slider()
{
    $imagepath = get_template_directory_uri() . '/img/';

    Zib_CFSwidget::create('zib_widget_ui_slider', array(
        'title'       => __('幻灯片(新)', 'zib_language'),
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('多张图片轮训播放的幻灯片模块，支持视频、图片、文字等内容的展示', 'zib_language'),
        'fields'      => array(
            array(
                'id'                     => 'slides',
                'type'                   => 'group',
                'min'                    => '1',
                'accordion_title_number' => true,
                'accordion_title_auto'   => false,
                'accordion_title_prefix' => __('幻灯片', 'zib_language'),
                'button_title'           => __('添加幻灯片', 'zib_language'),
                'title'                  => __('幻灯片内容', 'zib_language'),
                'subtitle'               => __('添加幻灯片', 'zib_language'),
                'desc'                   => __('注意：当添加的幻灯片内容只有一个的时候，则不会以幻灯片的形式展示（不能滑动切换，相当于单个图片或视频）', 'zib_language') . '<div class="c-yellow">' . __('由于移动端多数浏览器不支持视频背景功能，所以移动端不会显示视频！', 'zib_language') . '</div>',
                'default'                => array(
                    array(
                        'background'    => $imagepath . 'slider-bg.jpg',
                        'image_layer'   => array(
                            array(
                                'image'            => $imagepath . 'slider-layer-1.png',
                                'align'            => 'center',
                                'free_size'        => true,
                                'parallax'         => -100,
                                'parallax_scale'   => 100,
                                'parallax_opacity' => 100,
                            ),
                            array(
                                'image'            => $imagepath . 'slider-layer-2.png',
                                'align'            => 'center',
                                'free_size'        => true,
                                'parallax'         => -50,
                                'parallax_scale'   => 50,
                                'parallax_opacity' => 30,
                            ),
                        ),
                        'link'          => array(
                            'url'    => 'https://www.zibll.com/',
                            'target' => '_blank',
                        ),
                        'desc'          => '',
                        'title'         => '',
                        'text_align'    => 'left-bottom',
                        'text_parallax' => 30,
                        'text_size_m'   => 20,
                        'text_size_pc'  => 30,
                    ),
                ),
                'fields'                 => CFS_Module::add_slider(),
            ),
            array(
                'id'         => 'option',
                'type'       => 'accordion',
                'title'      => __('幻灯片设置', 'zib_language'),
                'default'    => array(
                    'direction'    => 'horizontal',
                    'button'       => true,
                    'pagination'   => true,
                    'effect'       => 'slide',
                    'auto_height'  => false,
                    'pc_height'    => 400,
                    'm_height'     => 240,
                    'spacebetween' => 15,
                    'speed'        => 0,
                    'interval'     => 4,
                ),
                'accordions' => array(
                    array(
                        'title'  => __('幻灯片设置', 'zib_language'),
                        'icon'   => 'fa fa-fw fa-angle-right',
                        'fields' => CFS_Module::slide(),
                    ),
                ),
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_slider_graphic_cover', array(
        'title'            => __('幻灯片加图文视频卡片', 'zib_language'),
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => false,
        'size'             => 'big',
        'description'      => __('左侧幻灯片与右侧图文视频封面卡片横向组合展示', 'zib_language'),
        'fields'           => array(
            array(
                'title'   => __('入场动画', 'zib_language'),
                'id'      => 'obs_animation',
                'type'    => 'select',
                'default' => '',
                'desc'    => __('页面滚动到封面区域时触发的入场动画[注意:需合理配置适当启用]', 'zib_language'),
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
                'desc'       => __('每次滚动到封面区域都触发动画[注意：有性能消耗，部分老旧浏览器会有闪烁问题]', 'zib_language'),
            ),
            array(
                'type'    => 'subheading',
                'content' => __('PC端布局配置', 'zib_language'),
            ),
            array(
                'id'      => 'layout_ratio',
                'title'   => __('幻灯片宽度占比', 'zib_language'),
                'default' => '60',
                'type'    => 'slider',
                'max'     => 100,
                'min'     => 0,
                'step'    => 1,
                'unit'    => '%',
            ),
            array(
                'id'      => 'slide_scale',
                'title'   => __('幻灯片长宽比', 'zib_language'),
                'default' => 50,
                'max'     => 500,
                'min'     => 0,
                'step'    => 5,
                'unit'    => '%',
                'class'   => 'compact',
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'pc_row',
                'title'   => __('右侧卡片列数', 'zib_language'),
                'default' => 2,
                'options' => array(
                    1 => __('1列', 'zib_language'),
                    2 => __('2列', 'zib_language'),
                    3 => __('3列', 'zib_language'),
                    4 => __('4列', 'zib_language'),
                ),
                'type'    => 'button_set',
                'class'   => 'button-mini',
            ),
            array(
                'id'      => 'pc_col',
                'title'   => __('右侧卡片行数', 'zib_language'),
                'class'   => 'compact',
                'default' => 2,
                'options' => array(
                    1 => __('1行', 'zib_language'),
                    2 => __('2行', 'zib_language'),
                    3 => __('3行', 'zib_language'),
                    4 => __('4行', 'zib_language'),
                ),
                'type'    => 'button_set',
                'class'   => 'button-mini',
            ),
            array(
                'type'    => 'subheading',
                'content' => __('移动端布局方式', 'zib_language'),
            ),
            array(
                'id'      => 'm_layout_type',
                'default' => 'vertical',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'default'    => __('保持PC端布局', 'zib_language'),
                    'vertical'   => __('上下布局', 'zib_language'),
                    'hide_slide' => __('隐藏幻灯片', 'zib_language'),
                    'hide_cover' => __('隐藏卡片', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('m_layout_type', 'any', 'hide_slide,vertical'),
                'id'         => 'm_row',
                'title'      => __('移动端卡片单行数量', 'zib_language'),
                'class'      => 'button-mini',
                'default'    => 2,
                'options'    => array(
                    1 => __('1个', 'zib_language'),
                    2 => __('2个', 'zib_language'),
                    3 => __('3个', 'zib_language'),
                    4 => __('4个', 'zib_language'),
                    6 => __('6个', 'zib_language'),
                ),
                'type'       => 'button_set',
            ),
            array(
                'dependency' => array('m_layout_type', 'any', 'hide_slide,vertical'),
                'id'         => 'm_cover_ratio',
                'title'      => __('移动端卡片长宽比', 'zib_language'),
                'default'    => 45,
                'max'        => 300,
                'min'        => 5,
                'step'       => 5,
                'unit'       => '%',
                'type'       => 'spinner',
            ),

            array(
                'type'    => 'subheading',
                'content' => __('左侧幻灯片', 'zib_language'),
            ),
            array(
                'id'                     => 'slides',
                'type'                   => 'group',
                'min'                    => '1',
                'accordion_title_number' => true,
                'accordion_title_auto'   => false,
                'accordion_title_prefix' => __('幻灯片', 'zib_language'),
                'button_title'           => __('添加幻灯片', 'zib_language'),
                'desc'                   => __('注意：当添加的幻灯片内容只有一个的时候，则不会以幻灯片的形式展示（不能滑动切换，相当于单个图片或视频）', 'zib_language') . '<div class="c-yellow">' . __('由于移动端多数浏览器不支持视频背景功能，所以移动端不会显示视频！', 'zib_language') . '</div>',
                'default'                => array(
                    array(
                        'background' => $imagepath . 'slider-bg.jpg',
                        'text'       => array(
                            'title'        => '更优雅的 WordPress 主题：子比主题',
                            'desc'         => '',
                            'text_align'   => 'left-bottom',
                            'text_size_m'  => 16,
                            'text_size_pc' => 24,
                            'parallax'     => 0,
                        ),
                    ),
                    array(
                        'background' => $imagepath . 'slider-bg.jpg',
                        'text'       => array(
                            'title'        => '更优雅的 WordPress 主题 子比主题',
                            'desc'         => '',
                            'text_align'   => 'left-bottom',
                            'text_size_m'  => 16,
                            'text_size_pc' => 24,
                            'parallax'     => 0,
                        ),
                    ),
                ),
                'fields'                 => CFS_Module::add_slider(),
            ),
            array(
                'id'         => 'option',
                'type'       => 'accordion',
                'title'      => __('幻灯片设置', 'zib_language'),
                'default'    => array(
                    'button'       => true,
                    'pagination'   => true,
                    'effect'       => 'slide',
                    'spacebetween' => 20,
                    'speed'        => 0,
                    'interval'     => 4,
                ),
                'accordions' => array(
                    array(
                        'title'  => __('幻灯片设置', 'zib_language'),
                        'icon'   => 'fa fa-fw fa-angle-right',
                        'fields' => array(
                            array(
                                'title'   => __('循环切换', 'zib_language'),
                                'class'   => 'compact',
                                'id'      => 'loop',
                                'default' => true,
                                'type'    => 'switcher',
                            ),
                            array(
                                'title'   => __('显示翻页按钮', 'zib_language'),
                                'class'   => 'compact',
                                'id'      => 'button',
                                'default' => false,
                                'type'    => 'switcher',
                            ),
                            array(
                                'title'   => __('显示指示器', 'zib_language'),
                                'type'    => 'switcher',
                                'id'      => 'pagination',
                                'class'   => 'compact',
                                'default' => false,
                                'type'    => 'switcher',
                            ),
                            array(
                                'id'      => 'effect',
                                'default' => 'slide',
                                'class'   => 'compact',
                                'title'   => __('切换动画', 'zib_language'),
                                'type'    => 'select',
                                'options' => array(
                                    'slide'     => __('滑动', 'zib_language'),
                                    'fade'      => __('淡出淡入', 'zib_language'),
                                    'cube'      => __('3D方块', 'zib_language'),
                                    'coverflow' => __('3D滑入', 'zib_language'),
                                    'flip'      => __('3D翻转', 'zib_language'),
                                ),
                            ),
                            array(
                                'id'      => 'spacebetween',
                                'title'   => __('幻灯片间距', 'zib_language'),
                                'default' => 15,
                                'max'     => 500,
                                'min'     => 0,
                                'step'    => 5,
                                'unit'    => 'PX',
                                'type'    => 'spinner',
                            ),
                            array(
                                'id'       => 'speed',
                                'title'    => __('切换速度', 'zib_language'),
                                'subtitle' => __('切换过程的时间(越小越快)', 'zib_language'),
                                'desc'     => __('设置为“0”，则为自动模式：根据幻灯片大小自动设置最佳速度', 'zib_language'),
                                'class'    => 'compact',
                                'default'  => 0,
                                'max'      => 3000,
                                'min'      => 0,
                                'step'     => 100,
                                'unit'     => __('毫秒', 'zib_language'),
                                'type'     => 'spinner',
                            ),
                            array(
                                'title'   => __('自动播放', 'zib_language'),
                                'type'    => 'switcher',
                                'id'      => 'autoplay',
                                'class'   => 'compact',
                                'default' => true,
                                'type'    => 'switcher',
                            ),
                            array(
                                'dependency' => array('autoplay', '!=', ''),
                                'id'         => 'interval',
                                'title'      => __('停顿时间', 'zib_language'),
                                'subtitle'   => __('自动切换的时间间隔(越小越快)', 'zib_language'),
                                'class'      => 'compact',
                                'default'    => 4,
                                'max'        => 20,
                                'min'        => 0,
                                'step'       => 1,
                                'unit'       => __('秒', 'zib_language'),
                                'type'       => 'spinner',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'type'    => 'subheading',
                'content' => __('右侧图文视频卡片', 'zib_language'),
            ),
            array(
                'id'           => 'covers',
                'type'         => 'group',
                'button_title' => __('添加卡片', 'zib_language'),
                'default'      => array(
                    array(
                        'image' => $imagepath . 'user_t.jpg',
                        'title' => '',
                    ),
                    array(
                        'image' => $imagepath . 'user_t.jpg',
                        'title' => '',
                    ),
                    array(
                        'image' => $imagepath . 'user_t.jpg',
                        'title' => '',
                    ),
                    array(
                        'image' => $imagepath . 'user_t.jpg',
                        'title' => '',
                    ),
                ),
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
            array(
                'type'    => 'subheading',
                'content' => __('卡片样式配置', 'zib_language'),
            ),
            array(
                'id'      => 'mask_opacity',
                'title'   => __('遮罩透明度', 'zib_language'),
                'help'    => __('图片上显示的黑色遮罩层的透明度', 'zib_language'),
                'default' => 0,
                'max'     => 90,
                'min'     => 0,
                'step'    => 1,
                'unit'    => '%',
                'type'    => 'slider',
            ),
            array(
                'id'       => 'font_size_pc',
                'title'    => __('文字样式', 'zib_language'),
                'subtitle' => __('PC端字体大小', 'zib_language'),
                'default'  => 16,
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
        ),
    ));
}

//左幻灯片右侧图文视频卡片
function zib_widget_ui_slider_graphic_cover($args, $instance)
{
    $has_slider = !empty($instance['slides'][0]);
    $has_covers = !empty($instance['covers'][0]['image']) || !empty($instance['covers'][0]['video']);

    $slider_html = '';
    if ($has_slider && is_array($instance['slides']) && is_array($instance['option'])) {
        $slider_option                 = $instance['option'];
        $slider_option['class']        = 'slide-widget';
        $slider_option['slides']       = $instance['slides'];
        $slider_option['direction']    = 'horizontal';
        $slider_option['scale_height'] = true;
        $slider_option['scale']        = $instance['slide_scale'];
        $slider_html                   = zib_new_slider($slider_option, false);
    }

    /**
     * 自动计算cover长宽比例
     * 右侧的整体长宽比例：也就是说右侧只有一个卡片时候
     * （layout_ratio * slide_scale）/ (100% - gap10) * (100% - layout_ratio)
     *
     * 右侧有两行卡片时候：
     *
     *
     *
     */

    $m_layout_type   = $instance['m_layout_type'];
    $layout_ratio    = (int) $instance['layout_ratio'];
    $layout_ratio    = $layout_ratio >= 0 ? $layout_ratio : 60;
    $slide_scale     = (int) $instance['slide_scale'];
    $slide_scale     = $slide_scale >= 0 ? $slide_scale : 37;
    $cover_row_count = (int) $instance['pc_row'];
    $cover_col_count = (int) $instance['pc_col'];
    $cover_row_count = $cover_row_count >= 1 ? $cover_row_count : 1;
    $cover_col_count = $cover_col_count >= 1 ? $cover_col_count : 1;
    $cover_gap       = 3;

    $cover_base_ratio = (($layout_ratio * $slide_scale) / (100 - $layout_ratio));
    $cover_ratio      = (($cover_base_ratio - ($cover_col_count - 1) * $cover_gap) / $cover_col_count) * $cover_row_count;

    $cover_m_row   = (int) $instance['m_row'];
    $cover_m_row   = $cover_m_row >= 1 ? $cover_m_row : 1;
    $m_cover_ratio = (int) $instance['m_cover_ratio'];
    $m_cover_ratio = $m_cover_ratio >= 0 ? $m_cover_ratio : $cover_ratio;

    $style_attr = '';
    $style_attr .= ' --layout-ratio:' . $instance['layout_ratio'] . '%;';
    $style_attr .= ' --cover-ratio:' . $cover_ratio . '%;';
    $style_attr .= ' --m-cover-ratio:' . $m_cover_ratio . '%;';

    //拦截echo
    $cover_instance                 = $instance;
    $cover_instance['height_scale'] = 0;
    if ($m_layout_type === 'default') {
        $cover_instance['m_row'] = $cover_row_count;
    }

    ob_start();
    zib_widget_ui_graphic_cover($args, $cover_instance);
    $cover_html = ob_get_clean();


    add_filter('wp_footer', 'zib_widget_ui_slider_graphic_cover_js');

    $animation_class = Zib_CFSwidget::animation_class($instance, true);
    $class = 'flex widget-slider-graphic';
    $class .= ' m-' . $m_layout_type;

    echo '<div class="' . $class . '"  style="' . $style_attr . '" data-row="' . $cover_row_count . '" data-col="' . $cover_col_count . '">';
    if ($slider_html) {
        echo '<div class="slider-col mb20 ' . $animation_class . '">' . $slider_html . '</div>';
    } elseif (is_super_admin()) {
        echo '<div class="slider-col mb20"><div class="alert alert-warning">' . __('当前模块下没有可显示的幻灯片，请添加幻灯片或移除此模块', 'zib_language') . '</div></div>';
    }
    if ($cover_html) {
        echo '<div class="cover-col mb10">' . $cover_html . '</div>';
    } elseif (is_super_admin()) {
        echo '<div class="cover-col mb10"><div class="alert alert-warning">' . __('当前模块下没有可显示的卡片，请添加卡片或移除此模块', 'zib_language') . '</div></div>';
    }
    echo '</div>';
}

//幻灯片右侧图文视频卡片JS
function zib_widget_ui_slider_graphic_cover_js()
{
    //只加载一次
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    //获取当前模块的DOM
    echo '<script>
        //PC端处理
        if(window.innerWidth > 0){
            $(".widget-slider-graphic").each(function(){
                var $this = $(this);
                var row = ~~$this.data("row");
                var col = ~~$this.data("col");
                var gap = 10;
                var $covers = $this.find(".cover-col");
                var height = $this.find(".zib-slider").height();
                var width =  $covers.width();
                var ratio = ((height - (col - 1) * gap) / col)/((width - (row - 1) * gap) / row)  * 100;
                $covers.css("cssText", "--cover-ratio: " + ratio + "%;");
            });
        }
    </script>';
}

function zib_widget_ui_slider_graphic_cover_is_show($show_class, $args, $instance)
{
    $has_slider = !empty($instance['slides'][0]['background']) || !empty($instance['slides'][0]['background_video']);
    $has_covers = !empty($instance['covers'][0]['image']) || !empty($instance['covers'][0]['video']);

    if (!$has_slider && !$has_covers) {
        return false;
    }

    return $show_class;
}

function zib_widget_slider_graphic_cover_html($instance)
{
    if (empty($instance['covers']) || !is_array($instance['covers'])) {
        return '';
    }

    $defaults = array(
        'pc_row'           => 2,
        'm_row'            => 2,
        'font_size_pc'     => 16,
        'font_size_m'      => 14,
        'font_bold'        => false,
        'font_color'       => '',
        'height_scale'     => 45,
        'mask_opacity'     => 0,
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    $pc_row = max(1, min(3, (int) $instance['pc_row']));
    $m_row  = max(1, min(3, (int) $instance['m_row']));

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $style = '';
    $style .= $instance['font_size_pc'] && 14 != $instance['font_size_pc'] ? '--font-size:' . ((int) $instance['font_size_pc']) . 'px;' : '';
    $style .= $instance['font_size_m'] && 14 != $instance['font_size_m'] ? '--font-size-sm:' . ((int) $instance['font_size_m']) . 'px;' : '';
    $style .= $instance['font_bold'] ? '--font-weight:bold;--font-weight-sm:bold;' : '';
    $style .= $instance['font_color'] ? '--color:' . $instance['font_color'] . ';--color-sm:' . $instance['font_color'] . ';' : '';
    $style_attr = $style ? ' style="' . esc_attr($style) . '"' : '';

    $animation_class = Zib_CFSwidget::animation_class($instance, true);
    $is_row          = count($instance['covers']) > 1;
    $html            = '';

    foreach ($instance['covers'] as $key => $cover) {
        if (empty($cover['image']) && empty($cover['video'])) {
            continue;
        }

        if (isset($cover['hide'])) {
            $is_mobile = wp_is_mobile();
            if ((!$is_mobile && $cover['hide'] === 'pc') || ($is_mobile && $cover['hide'] === 'm')) {
                continue;
            }
        }

        $more = !empty($cover['title']) ? '<div class="abs-center text-center graphic-text this-font">' . $cover['title'] . '</div>' : '';
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

        $col_class = $animation_class;
        if ($is_row) {
            $col_class .= ' ' . $row_class;
        }

        $html .= '<div class="' . esc_attr(trim($col_class)) . '">';
        $html .= zib_graphic_card($card);
        $html .= '</div>';
    }

    if ($html === '') {
        return '';
    }

    $wrap = '<div class="widget-graphic-cover"' . $style_attr . '>';
    $wrap .= $is_row ? '<div class="row gutters-5">' : '';
    $wrap .= $html;
    $wrap .= $is_row ? '</div>' : '';
    $wrap .= '</div>';

    return $wrap;
}
