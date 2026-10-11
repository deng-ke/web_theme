<?php

function zib_widget_ui_user_is_show($show_class, $args, $instance)
{
    if (zib_is_close_sign()) {
        return false;
    }

    return $show_class;
}

function zib_widget_ui_user($args, $instance)
{
    $instance['user_id']      = get_current_user_id();
    $instance['show_posts']   = false;
    $instance['class']        = 'widget';
    $instance['show_checkin'] = true;

    if (!isset($instance['show_img_bg'])) {
        $instance['show_img_bg'] = true;
    }

    echo '<div class="mb20">';
    if ($instance['user_id']) {
        //已经登录
        echo zib_get_user_card_box($instance);
    } else {
        //未登录
        $loged_title = !empty($instance['loged_title']) ? $instance['loged_title'] : __('Hi！请登录', 'zib_language');
        $lazy_attr   = zib_is_lazy('lazy_other', true) ? 'class="lazyload fit-cover" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover"';
        $cover       = $instance['show_img_bg'] ? '<div class="user-cover graphic" style="padding-bottom: 50%;"><img ' . $lazy_attr . 'src="' . _pz('user_cover_img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg') . '"></div>' : '';
        $avatar      = '<span class="avatar-img avatar-lg"><img alt="' . esc_attr__('默认头像', 'zib_language') . '" class="fit-cover avatar" src="' . zib_default_avatar() . '"></span>';
        $html        = '<div class="user-card zib-widget widget">' . $cover . '
        <div class="card-content mt10">
            <div class="user-content">
                <div class="user-avatar">' . $avatar . '</div>
                <div class="user-info mt10">
                    ' . zib_get_user_singin_page_box('', $loged_title) . '
                </div>
            </div>
        </div>
    </div>';
        echo $html;
    }
    echo '</div>';
}

function zib_widget_ui_avatar_is_show($show_class, $args, $instance)
{
    global $post;
    if (!isset($post->post_author)) {
        return false;
    }
    return $show_class;
}

function zib_widget_ui_avatar($args, $instance)
{

    global $post;
    if (!isset($post->post_author)) {
        return;
    }
    $instance['user_id'] = $post->post_author;
    $instance['class']   = 'widget';
    if (!isset($instance['show_img_bg'])) {
        $instance['show_img_bg'] = true;
    }
    if (isset($instance['show_img'])) {
        $instance['post_style'] = $instance['show_img'] ? 'card' : 'mini';
    }

    echo '<div class="mb20">';
    echo zib_get_user_card_box($instance);
    echo '</div>';
}

//图标卡片

function zib_widget_ui_user_ranking($args, $instance)
{

    $html = zib_user_ranking_lists($instance);
    echo '<div class="zib-widget user-ranking-box">';
    if (!empty($instance['checkin_btn'])) {
        $class = _pz('checkin_header_user_option', 'c-yellow', 'class');
        $text  = _pz('checkin_header_user_option', __('签到领取今日奖励', 'zib_language'), 'text');

        $checkin_btn = zib_get_user_checkin_btn('but block mb20 padding-lg mini-radius ' . $class, '<i class="fa fa-calendar-check-o"></i> ' . $text, '<i class="fa fa-calendar-check-o"></i> ' . __('今日已签到', 'zib_language'));
        echo $checkin_btn;
    }
    echo $html;
    echo '</div>';
}

//用户列表小工具
function zib_user_ranking_lists($args)
{
    $defaults = array(
        'orderby'   => 'checkin_all_day',
        'number'    => 6,
        'exclude'   => '',
        'desc'      => 'desc',
        'top_badge' => false,
    );

    $args = wp_parse_args($args, $defaults);

    $users_args = array(
        'exclude'    => $args['exclude'],
        'order'      => 'DESC',
        'number'     => $args['number'],
        'orderby'    => 'meta_value_num',
        'meta_key'   => $args['orderby'],
        'meta_query' => array(
            'relation' => 'OR', //排除禁封用户
            array(
                'key'     => 'banned',
                'value'   => array(1, 2),
                'compare' => 'NOT IN',
            ),
            array(
                'key'     => 'banned',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );

    $users = get_users($users_args);

    $lists   = '';
    $posts_i = 1;
    if ($users) {
        foreach ($users as $user) {
            $user_id           = $user->ID;
            $desc              = get_user_desc($user_id);
            $avatar_box        = zib_get_avatar_box($user_id);
            $display_name_link = zib_get_user_name("id=$user_id");
            $top_bagd_class    = array('', 'jb-red', 'jb-yellow');
            $top_bagd          = $args['top_badge'] ? '<badge class="img-badge left hot ' . (isset($top_bagd_class[$posts_i - 1]) ? $top_bagd_class[$posts_i - 1] : 'b-gray') . '"><i>TOP' . $posts_i . '</i></badge>' : '';

            switch ($args['desc']) {
                case 'desc':
                    $desc = get_user_desc($user_id);
                    break;
                case 'user_registered':
                    $desc = zib_get_user_join_day_desc($user_id, '');
                    break;
                case 'last_login':

                    $last_login = get_user_meta($user->ID, 'last_login', true);
                    $desc       = sprintf(__('%s登录', 'zib_language'), zib_get_time_ago($last_login));
                    break;
                case 'last_checkin':
                    $last_checkin_time = zib_get_user_last_checkin_time($user_id, true);
                    $desc              = $last_checkin_time ? sprintf(__('%s已签到', 'zib_language'), $last_checkin_time) : __('未签到', 'zib_language');
                    break;

            }

            switch ($args['orderby']) {
                case 'checkin_all_day':
                    $num         = zib_get_user_checkin_all_day($user_id);
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="' . esc_attr(sprintf(__('累计签到%d天', 'zib_language'), $num)) . '"><i class="fa fa-calendar-check-o"></i><span class="ml6">' . $num . '</span></span>';
                    break;
                case 'checkin_continuous_day':
                    $num         = zib_get_user_checkin_continuous_day($user_id);
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="' . esc_attr(sprintf(__('连续签到%d天', 'zib_language'), $num)) . '"><i class="fa fa-calendar-check-o"></i><span class="ml6">' . $num . '</span></span>';
                    break;
                case 'points':
                    $num         = _cut_count(zibpay_get_user_points($user_id));
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="' . esc_attr(sprintf(__('积分：%s', 'zib_language'), $num)) . '">' . zib_get_svg('points') . '<span class="ml6">' . $num . '</span></span>';
                    break;
                case 'followed-user-count':
                    $num         = get_user_followed_count($user_id);
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="' . esc_attr(sprintf(__('共%d个粉丝', 'zib_language'), $num)) . '"><i class="fa fa-heart"></i><span class="ml6">' . $num . '</span></span>';

                    break;
            }

            $lists .= '<div class="user-ranking-item relative' . ($posts_i > 1 ? ' mt20' : '') . '">
                ' . $top_bagd . '
                <div class="user-info flex ac">
                    ' . $avatar_box . '
                    <div class="user-right flex flex1 ac jsb ml10">
                        <div class="flex1">
                            <div class="font-bold">' . $display_name_link . '</div>
                            <div class="mt3 em09 muted-color text-ellipsis">' . $desc . '</div>
                        </div>
                        <div class="ml20 flex0">' . $right_badge . '</div>
                    </div>
                </div></div>';

            $posts_i++;

        }
    }

    return $lists;
}

function zib_widget_ui_user_lists($args, $instance)
{

    $users_args = array(
        'order'   => $instance['order'],
        'orderby' => $instance['orderby'],
        'number'  => $instance['number'],
        'include' => $instance['include'],
        'exclude' => $instance['exclude'],
    );

    $class = !$instance['hide_box'] ? ' zib-widget' : 'mb20';

    echo '<div class="' . $class . '">';
    echo '<div class="text-center user_lists">';
    echo zib_get_user_card_lists($users_args) ?: esc_html__('未找到用户', 'zib_language');
    echo '</div>';
    echo '</div>';
}
add_action('after_setup_theme', 'zib_widget_register_cfs_user');

function zib_widget_register_cfs_user()
{
    Zib_CFSwidget::create('widget_ui_user', array(
        'title'       => __('用户个人信息', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'callback'    => 'zib_widget_ui_user',
        'size'        => 'mini',
        'description' => __('未登录时候显示登录注册按钮，登录后显示登录用户的个人信息', 'zib_language'),
        'fields'      => array(
            array(
                'title'   => __('显示封面', 'zib_language'),
                'id'      => 'show_img_bg',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'title'   => __('未登录的文案', 'zib_language'),
                'id'      => 'loged_title',
                'type'    => 'text',
                'default' => 'HI！请登录',
            ),
            array(
                'title'   => __('显示按钮', 'zib_language'),
                'desc'    => __('登录后才会显示', 'zib_language'),
                'id'      => 'show_button',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'id'         => 'button_1',
                'default'    => 'post',
                'title'      => __('按钮1：', 'zib_language'),
                'subtitle'   => __('按钮类型', 'zib_language'),
                'inline'     => true,
                'type'       => 'radio',
                'options'    => 'zib_new_add_btns_options',
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('按钮颜色', 'zib_language'),
                'id'         => 'button_1_class',
                'class'      => 'compact skin-color',
                'default'    => 'jb-pink',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(),
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'class'      => 'compact mini-input',
                'subtitle'   => __('按钮文字', 'zib_language'),
                'id'         => 'button_1_text',
                'type'       => 'text',
                'default'    => '发布文章',
            ),

            array(
                'dependency' => array('show_button', '!=', ''),
                'id'         => 'button_2',
                'default'    => 'center',
                'title'      => __('按钮2：', 'zib_language'),
                'subtitle'   => __('按钮类型', 'zib_language'),
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'home'   => __('个人主页', 'zib_language'),
                    'center' => __('用户中心', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('按钮颜色', 'zib_language'),
                'id'         => 'button_2_class',
                'class'      => 'compact skin-color',
                'default'    => 'jb-blue',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(),
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'class'      => 'compact mini-input',
                'subtitle'   => __('按钮文字', 'zib_language'),
                'id'         => 'button_2_text',
                'type'       => 'text',
                'default'    => '用户中心',
            ),
        ),
    ));

    Zib_CFSwidget::create('widget_ui_avatar', array(
        'title'          => __('文章作者信息', 'zib_language'),
        'zib_title'      => true,
        'zib_affix'      => true,
        'zib_show'       => true,
        'callback'       => 'zib_widget_ui_avatar',
        'is_show_filter' => 'zib_widget_ui_avatar_is_show',
        'size'           => 'mini',
        'description'    => __('显示当前文章作者的个人信息，只会在文章页、帖子页显示', 'zib_language'),
        'fields'         => array(
            array(
                'title'   => __('显示封面', 'zib_language'),
                'id'      => 'show_img_bg',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'title'   => __('显示文章', 'zib_language'),
                'id'      => 'show_posts',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('show_posts', '!=', ''),
                'id'         => 'post_type',
                'default'    => 'post',
                'class'      => 'compact',
                'title'      => ' ',
                'subtitle'   => __('文章类型', 'zib_language'),
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'post'       => __('文章', 'zib_language'),
                    'forum_post' => __('论坛帖子', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('show_posts', '!=', ''),
                'id'         => 'post_style',
                'default'    => 'post',
                'class'      => 'compact',
                'title'      => ' ',
                'subtitle'   => __('显示样式', 'zib_language'),
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'mini' => __('简约风格', 'zib_language'),
                    'card' => __('图文卡片', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('show_posts', '!=', ''),
                'id'         => 'limit',
                'class'      => 'compact',
                'title'      => ' ',
                'subtitle'   => __('显示数量', 'zib_language'),
                'default'    => 6,
                'max'        => 12,
                'min'        => 2,
                'step'       => 1,
                'unit'       => __('篇', 'zib_language'),
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array('show_posts', '!=', ''),
                'id'         => 'orderby',
                'default'    => 'date',
                'class'      => 'compact',
                'title'      => ' ',
                'subtitle'   => __('排序方式', 'zib_language'),
                'inline'     => true,
                'type'       => 'select',
                'options'    => array(
                    'modified'       => __('最近更新', 'zib_language'),
                    'date'           => __('最新发布', 'zib_language'),
                    'views'          => __('最多浏览', 'zib_language'),
                    'like'           => __('最多点赞[文章]', 'zib_language'),
                    'comment_count'  => __('最多评论', 'zib_language'),
                    'favorite'       => __('最多收藏[文章]', 'zib_language'),
                    'favorite_count' => __('最多收藏[帖子]', 'zib_language'),
                    'score'          => __('评分最高[帖子]', 'zib_language'),
                    'rand'           => __('随机', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('显示按钮', 'zib_language'),
                'desc'    => __('当登录用户就是文章作者的时候才会显示', 'zib_language'),
                'id'      => 'show_button',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'id'         => 'button_1',
                'default'    => 'post',
                'title'      => __('按钮1：', 'zib_language'),
                'subtitle'   => __('按钮类型', 'zib_language'),
                'inline'     => true,
                'type'       => 'radio',
                'options'    => 'zib_new_add_btns_options',
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('按钮颜色', 'zib_language'),
                'id'         => 'button_1_class',
                'class'      => 'compact skin-color',
                'default'    => 'jb-pink',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(),
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'class'      => 'compact mini-input',
                'subtitle'   => __('按钮文字', 'zib_language'),
                'id'         => 'button_1_text',
                'type'       => 'text',
                'default'    => '发布文章',
            ),

            array(
                'dependency' => array('show_button', '!=', ''),
                'id'         => 'button_2',
                'default'    => 'center',
                'title'      => __('按钮2：', 'zib_language'),
                'subtitle'   => __('按钮类型', 'zib_language'),
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'home'   => __('个人主页', 'zib_language'),
                    'center' => __('用户中心', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('按钮颜色', 'zib_language'),
                'id'         => 'button_2_class',
                'class'      => 'compact skin-color',
                'default'    => 'jb-blue',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(),
            ),
            array(
                'dependency' => array('show_button', '!=', ''),
                'title'      => ' ',
                'class'      => 'compact mini-input',
                'subtitle'   => __('按钮文字', 'zib_language'),
                'id'         => 'button_2_text',
                'type'       => 'text',
                'default'    => '用户中心',
            ),
        ),
    ));

    Zib_CFSwidget::create('zib_widget_ui_user_ranking', array(
        'title'       => __('用户排行榜', 'zib_language'),
        'zib_title'   => true,
        'zib_affix'   => true,
        'zib_show'    => true,
        'description' => __('根据不同项目对对用户进行排名，生成排行榜', 'zib_language'),
        'fields'      => array(
            array(
                'id'      => 'orderby',
                'default' => 'checkin_all_day',
                'title'   => __('榜单类型', 'zib_language'),
                'type'    => 'radio',
                'options' => array(
                    'checkin_all_day'        => __('累计签到天数', 'zib_language'),
                    'checkin_continuous_day' => __('连续签到天数', 'zib_language'),
                    'points'                 => __('用户积分', 'zib_language'),
                    'followed-user-count'    => __('粉丝数量', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'number',
                'title'   => __('最大显示数量', 'zib_language'),
                'default' => 6,
                'max'     => 20,
                'min'     => 1,
                'step'    => 1,
                'unit'    => __('个', 'zib_language'),
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'desc',
                'default' => 'desc',
                'title'   => __('附加显示', 'zib_language'),
                'type'    => 'radio',
                'options' => array(
                    'desc'            => __('用户签名', 'zib_language'),
                    'user_registered' => __('加入本站多少天', 'zib_language'),
                    'last_login'      => __('最后登录时间', 'zib_language'),
                    'last_checkin'    => __('最后签到时间', 'zib_language'),
                ),
            ),
            array(
                'label'   => __('显示排行徽章', 'zib_language'),
                'id'      => 'top_badge',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'label'   => __('显示签到按钮', 'zib_language'),
                'id'      => 'checkin_btn',
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));

    Zib_CFSwidget::create('widget_ui_user_lists', array(
        'title'            => __('用户列表', 'zib_language'),
        'zib_title'        => true,
        'zib_affix'        => true,
        'zib_show'         => true,
        'zib_animation_in' => true,
        'callback'         => 'zib_widget_ui_user_lists',
        'description'      => __('显示网站注册用户列表', 'zib_language'),
        'fields'           => array(
            array(
                'id'      => 'number',
                'title'   => __('显示数量', 'zib_language'),
                'type'    => 'spinner',
                'default' => 8,
                'min'     => 1,
                'max'     => 50,
                'step'    => 1,
            ),
            array(
                'title' => __('包含的用户ID', 'zib_language'),
                'id'    => 'include',
                'type'  => 'text',
                'desc'  => __('填写需要包含的用户ID，用逗号分割，例如：55,66,100', 'zib_language'),
            ),
            array(
                'title' => __('排除的用户ID', 'zib_language'),
                'id'    => 'exclude',
                'type'  => 'text',
                'desc'  => __('填写需要排除的用户ID，用逗号分割，例如：55,66,100', 'zib_language'),
            ),
            array(
                'title'   => __('排序方式', 'zib_language'),
                'id'      => 'orderby',
                'type'    => 'select',
                'options' => array(
                    'display_name'        => __('呢称', 'zib_language'),
                    'user_registered'     => __('注册时间', 'zib_language'),
                    'post_count'          => __('文章数量', 'zib_language'),
                    'last_login'          => __('最后登录时间', 'zib_language'),
                    'followed-user-count' => __('粉丝数', 'zib_language'),
                ),
                'default' => 'user_registered',
            ),
            array(
                'id'      => 'order',
                'type'    => 'radio',
                'inline'  => true,
                'default' => 'DESC',
                'class'   => 'compact',
                'options' => array(
                    'ASC'  => __('升序', 'zib_language'),
                    'DESC' => __('降序', 'zib_language'),
                ),
            ),
            array(
                'id'      => 'hide_box',
                'label'   => __('不显示背景盒子', 'zib_language'),
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));
}
