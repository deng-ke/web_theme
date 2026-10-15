<?php
/*
 * Project       : Nova主题
 * Description   : 小工具类注册（热门/最新/随机/评论/标签/分类/搜索/关于）
 */

if (!defined('ABSPATH')) exit;

/* ============================================================
 * 通用小工具基类
 * ============================================================ */
class Nova_Widget_Base extends WP_Widget
{
    public function __construct($id_base, $name, $widget_options = array(), $control_options = array())
    {
        $widget_options = wp_parse_args($widget_options, array(
            'classname'                   => $id_base,
            'description'                 => $name,
            'customize_selective_refresh' => true,
        ));
        parent::__construct($id_base, $name, $widget_options, $control_options);
    }

    /**
     * 标题输出
     */
    protected function widget_title($instance)
    {
        $title = isset($instance['title']) ? $instance['title'] : '';
        if (!$title) return '';
        return '<h3 class="widget-title"><span>' . esc_html($title) . '</span></h3>';
    }

    /**
     * 数量字段表单
     */
    protected function form_num_field($instance, $label, $default = 5)
    {
        $value = isset($instance['num']) ? (int) $instance['num'] : $default;
        echo '<p><label for="' . esc_attr($this->get_field_id('num')) . '">' . esc_html($label) . '</label>';
        echo '<input class="widefat" id="' . esc_attr($this->get_field_id('num')) . '" name="' . esc_attr($this->get_field_name('num')) . '" type="number" min="1" max="20" value="' . esc_attr($value) . '"></p>';
    }

    /**
     * 标题表单
     */
    protected function form_title_field($instance)
    {
        $title = isset($instance['title']) ? $instance['title'] : '';
        echo '<p><label for="' . esc_attr($this->get_field_id('title')) . '">' . esc_html__('标题', 'nova') . '</label>';
        echo '<input class="widefat" id="' . esc_attr($this->get_field_id('title')) . '" name="' . esc_attr($this->get_field_name('title')) . '" type="text" value="' . esc_attr($title) . '"></p>';
    }

    /**
     * 更新
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        if (isset($new_instance['num'])) {
            $instance['num'] = (int) $new_instance['num'];
        }
        return $instance;
    }
}

/* ============================================================
 * 热门文章
 * ============================================================ */
class Nova_Widget_Hot_Posts extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_hot_posts', __('[Nova] 热门文章', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        $num  = isset($instance['num']) ? (int) $instance['num'] : 5;
        $days = isset($instance['days']) ? (int) $instance['days'] : 0;
        echo nova_widget_hot_posts_html($num, $days); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
        $this->form_num_field($instance, __('显示数量', 'nova'), 5);
        $days = isset($instance['days']) ? (int) $instance['days'] : 0;
        echo '<p><label for="' . esc_attr($this->get_field_id('days')) . '">' . esc_html__('时间范围（天，0为全部）', 'nova') . '</label>';
        echo '<input class="widefat" id="' . esc_attr($this->get_field_id('days')) . '" name="' . esc_attr($this->get_field_name('days')) . '" type="number" min="0" value="' . esc_attr($days) . '"></p>';
    }

    public function update($new_instance, $old_instance)
    {
        $instance = parent::update($new_instance, $old_instance);
        $instance['days'] = (int) $new_instance['days'];
        return $instance;
    }
}

/* ============================================================
 * 最新文章
 * ============================================================ */
class Nova_Widget_Latest_Posts extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_latest_posts', __('[Nova] 最新文章', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        $num = isset($instance['num']) ? (int) $instance['num'] : 5;
        echo nova_widget_latest_posts_html($num); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
        $this->form_num_field($instance, __('显示数量', 'nova'), 5);
    }
}

/* ============================================================
 * 随机文章
 * ============================================================ */
class Nova_Widget_Random_Posts extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_random_posts', __('[Nova] 随机文章', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        $num = isset($instance['num']) ? (int) $instance['num'] : 5;
        echo nova_widget_random_posts_html($num); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
        $this->form_num_field($instance, __('显示数量', 'nova'), 5);
    }
}

/* ============================================================
 * 最近评论
 * ============================================================ */
class Nova_Widget_Comments extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_comments', __('[Nova] 最近评论', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        $num = isset($instance['num']) ? (int) $instance['num'] : 5;
        echo nova_widget_comments_html($num); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
        $this->form_num_field($instance, __('显示数量', 'nova'), 5);
    }
}

/* ============================================================
 * 标签云
 * ============================================================ */
class Nova_Widget_Tags extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_tags', __('[Nova] 标签云', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        $num = isset($instance['num']) ? (int) $instance['num'] : 20;
        echo nova_widget_tags_html($num); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
        $this->form_num_field($instance, __('显示数量', 'nova'), 20);
    }
}

/* ============================================================
 * 分类
 * ============================================================ */
class Nova_Widget_Cats extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_cats', __('[Nova] 分类列表', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        $num = isset($instance['num']) ? (int) $instance['num'] : 10;
        echo nova_widget_cats_html($num); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
        $this->form_num_field($instance, __('显示数量', 'nova'), 10);
    }
}

/* ============================================================
 * 搜索框
 * ============================================================ */
class Nova_Widget_Search extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_search', __('[Nova] 搜索框', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo '<div class="nova-widget-search">';
        echo '<form role="search" method="get" action="' . esc_url(home_url()) . '">';
        echo '<input type="text" name="s" class="nova-widget-search-input" placeholder="' . esc_attr__('搜索感兴趣的内容…', 'nova') . '" value="' . esc_attr(get_search_query()) . '">';
        echo '<button type="submit" class="nova-widget-search-btn"><i class="nova-icon-search"></i></button>';
        echo '</form>';
        echo '</div>';
        echo $args['after_widget']; // phpcs:ignore
    }
}

/* ============================================================
 * 关于本站
 * ============================================================ */
class Nova_Widget_About extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_about', __('[Nova] 关于本站', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        echo nova_widget_about_html(); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
    }
}

/* ============================================================
 * 网站数据
 * ============================================================ */
class Nova_Widget_Site_Data extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_site_data', __('[Nova] 网站数据', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        echo nova_widget_site_data_html(); // phpcs:ignore
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
    }
}

/* ============================================================
 * 用户信息
 * ============================================================ */
class Nova_Widget_User extends Nova_Widget_Base
{
    public function __construct()
    {
        parent::__construct('nova_widget_user', __('[Nova] 用户信息', 'nova'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget']; // phpcs:ignore
        echo $this->widget_title($instance); // phpcs:ignore
        if (is_user_logged_in()) {
            echo nova_widget_user_card_html(); // phpcs:ignore
        } else {
            echo '<div class="nova-widget-user-guest"><p class="muted-2-color">' . esc_html__('登录后查看更多内容', 'nova') . '</p>';
            echo '<a class="btn btn-primary nova-block" href="' . esc_url(nova_signin_url()) . '">' . esc_html__('立即登录', 'nova') . '</a>';
            echo '<a class="btn btn-outline nova-block" href="' . esc_url(nova_signup_url()) . '">' . esc_html__('注册账号', 'nova') . '</a></div>';
        }
        echo $args['after_widget']; // phpcs:ignore
    }

    public function form($instance)
    {
        $this->form_title_field($instance);
    }
}

/* ============================================================
 * 注册所有小工具
 * ============================================================ */
function nova_register_widgets()
{
    register_widget('Nova_Widget_Hot_Posts');
    register_widget('Nova_Widget_Latest_Posts');
    register_widget('Nova_Widget_Random_Posts');
    register_widget('Nova_Widget_Comments');
    register_widget('Nova_Widget_Tags');
    register_widget('Nova_Widget_Cats');
    register_widget('Nova_Widget_Search');
    register_widget('Nova_Widget_About');
    register_widget('Nova_Widget_Site_Data');
    register_widget('Nova_Widget_User');
}
add_action('widgets_init', 'nova_register_widgets');
