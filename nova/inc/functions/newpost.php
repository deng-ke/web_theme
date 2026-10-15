<?php
/*
 * Project       : Nova主题
 * Description   : 前端发布文章（投稿/编辑）
 */

if (!defined('ABSPATH')) exit;

/**
 * 发布文章页面地址
 */
function nova_newpost_url($edit_id = 0)
{
    $url = nova_get_setting('newpost_page');
    if (!$url) $url = home_url('/newposts');
    if ($edit_id) {
        $url = add_query_arg(array('edit' => $edit_id), $url);
    }
    return $url;
}

/**
 * 发布权限：是否开启前台投稿
 */
function nova_newpost_enabled()
{
    return (bool) nova_get_setting('new_post_s', '1');
}

/**
 * 获取当前编辑的文章（?edit=ID）
 *
 * @return WP_Post|false
 */
function nova_newpost_get_edit_post()
{
    $edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    if (!$edit_id) return false;
    $post = get_post($edit_id);
    if (empty($post->ID) || 'post' !== $post->post_type) return false;
    // 仅作者本人或管理员可编辑
    if ($post->post_author != get_current_user_id() && !current_user_can('edit_others_posts')) {
        return false;
    }
    return $post;
}

/**
 * 发布文章表单
 */
function nova_newpost_form_html()
{
    $edit_post = nova_newpost_get_edit_post();
    $is_edit   = !empty($edit_post);

    $html = '<form class="nova-newpost-form" id="nova-newpost-form">';
    $html .= '<div class="nova-newpost-head card">';
    $html .= '<input type="text" name="title" class="nova-newpost-title" placeholder="' . esc_attr__('请输入文章标题', 'nova') . '" value="' . esc_attr($is_edit ? $edit_post->post_title : '') . '" maxlength="120">';
    $html .= '<div class="nova-newpost-subhead muted-2-color">' . esc_html__('一个好标题更容易被搜索引擎收录', 'nova') . '</div>';
    $html .= '</div>';

    // 正文编辑区（简易工具条 + textarea）
    $content = $is_edit ? $edit_post->post_content : '';
    $html .= '<div class="nova-newpost-editor card">';
    $html .= '<div class="nova-newpost-toolbar">';
    $tools = array(
        'b'        => array('B', esc_attr__('加粗', 'nova')),
        'i'        => array('I', esc_attr__('斜体', 'nova')),
        'link'     => array('链接', esc_attr__('插入链接', 'nova')),
        'code'     => array('代码', esc_attr__('插入代码块', 'nova')),
        'h2'       => array('H2', esc_attr__('二级标题', 'nova')),
        'quote'    => array('引用', esc_attr__('引用块', 'nova')),
        'list'     => array('列表', esc_attr__('无序列表', 'nova')),
        'separator'=> array('分割线', esc_attr__('插入分割线', 'nova')),
    );
    foreach ($tools as $key => $tool) {
        $html .= '<button type="button" class="nova-newpost-tool" data-tool="' . esc_attr($key) . '" title="' . esc_attr($tool[1]) . '">' . esc_html($tool[0]) . '</button>';
    }
    $html .= '</div>';
    $html .= '<textarea name="content" class="nova-newpost-content" placeholder="' . esc_attr__('开始写作…（支持 Markdown 常用语法）', 'nova') . '">' . esc_textarea($content) . '</textarea>';
    $html .= '<div class="nova-newpost-editor-tip muted-2-color">' . esc_html__('提示：支持 图片外链、代码块，短代码（如 [nova_tip]、[nova_panel]、[nova_hide]）', 'nova') . '</div>';
    $html .= '</div>';

    // 发布设置区
    $html .= '<div class="nova-newpost-settings card">';
    $html .= '<h3 class="nova-newpost-settings-title">' . esc_html__('发布设置', 'nova') . '</h3>';

    // 分类
    $cats = get_categories(array('hide_empty' => false));
    $html .= '<div class="nova-newpost-field">';
    $html .= '<label>' . esc_html__('文章分类', 'nova') . '</label>';
    $html .= '<select name="cat">';
    foreach ($cats as $cat) {
        $sel = '';
        if ($is_edit) {
            $post_cats = wp_get_post_categories($edit_post->ID);
            $sel = in_array($cat->term_id, $post_cats) ? ' selected' : '';
        }
        $html .= '<option value="' . (int) $cat->term_id . '"' . $sel . '>' . esc_html($cat->name) . '</option>';
    }
    $html .= '</select></div>';

    // 标签
    $tags = $is_edit ? implode(',', wp_get_post_tags($edit_post->ID, array('fields' => 'names'))) : '';
    $html .= '<div class="nova-newpost-field">';
    $html .= '<label>' . esc_html__('文章标签', 'nova') . '</label>';
    $html .= '<input type="text" name="tags" placeholder="' . esc_attr__('多个标签用逗号分隔，如：SLAM,机器人', 'nova') . '" value="' . esc_attr($tags) . '">';
    $html .= '</div>';

    // 封面图
    $thumb_id = $is_edit ? (int) get_post_thumbnail_id($edit_post->ID) : 0;
    $html .= '<div class="nova-newpost-field">';
    $html .= '<label>' . esc_html__('封面图片', 'nova') . '</label>';
    $html .= '<div class="nova-newpost-thumb" id="nova-newpost-thumb">';
    if ($thumb_id) {
        $html .= '<img src="' . esc_url(wp_get_attachment_image_url($thumb_id, 'medium')) . '" alt="thumb">';
        $html .= '<button type="button" class="nova-thumb-remove" id="nova-thumb-remove"><i class="nova-icon-close"></i></button>';
    } else {
        $html .= '<button type="button" class="nova-thumb-upload" id="nova-thumb-upload"><i class="nova-icon-upload"></i>' . esc_html__('上传封面', 'nova') . '</button>';
    }
    $html .= '<input type="hidden" name="thumb_id" value="' . (int) $thumb_id . '">';
    $html .= '</div></div>';

    $html .= '</div>';

    // 提交区
    $html .= '<div class="nova-newpost-submit card">';
    $html .= '<input type="hidden" name="edit_id" value="' . (int) ($is_edit ? $edit_post->ID : 0) . '">';
    $html .= wp_nonce_field('nova_ajax_nonce', 'nonce', true, false);
    $html .= '<button type="submit" class="btn btn-primary nova-newpost-submit-btn">' . esc_html__('立即发布', 'nova') . '</button>';
    if ($is_edit) {
        $html .= '<a class="btn btn-outline" href="' . esc_url(get_permalink($edit_post->ID)) . '">' . esc_html__('查看文章', 'nova') . '</a>';
    }
    $html .= '</div>';
    $html .= '</form>';
    return $html;
}

/**
 * AJAX：提交发布/编辑文章
 */
function nova_ajax_submit_post()
{
    check_ajax_referer('nova_ajax_nonce', 'nonce');
    $user_id = get_current_user_id();
    if (!$user_id) nova_ajax_error('请先登录');
    if (!nova_newpost_enabled()) nova_ajax_error('前台投稿功能未开启');

    $title   = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
    $cat     = isset($_POST['cat']) ? (int) $_POST['cat'] : 0;
    $tags    = isset($_POST['tags']) ? sanitize_text_field(wp_unslash($_POST['tags'])) : '';
    $thumb   = isset($_POST['thumb_id']) ? (int) $_POST['thumb_id'] : 0;
    $edit_id = isset($_POST['edit_id']) ? (int) $_POST['edit_id'] : 0;

    if (mb_strlen($title) < 2) nova_ajax_error('标题至少 2 个字');
    if (mb_strlen(wp_strip_all_tags($content)) < 5) nova_ajax_error('内容至少 5 个字');

    $args = array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => current_user_can('publish_posts') ? 'publish' : 'pending',
        'post_category'=> $cat ? array($cat) : array(),
        'tags_input'   => $tags,
    );

    if ($edit_id) {
        $post = get_post($edit_id);
        if (empty($post->ID) || 'post' !== $post->post_type) nova_ajax_error('文章不存在');
        if ($post->post_author != $user_id && !current_user_can('edit_others_posts')) nova_ajax_error('无权编辑此文章');
        $args['ID'] = $edit_id;
        // 管理员编辑保持原状态；作者重新编辑自己文章时保持原状态
        $args['post_status'] = $post->post_status;
        $post_id = wp_update_post($args, true);
        $msg = '更新成功';
    } else {
        $args['post_author'] = $user_id;
        $post_id = wp_insert_post($args, true);
        $msg = current_user_can('publish_posts') ? '发布成功' : '投稿成功，等待审核';
    }

    if (is_wp_error($post_id)) nova_ajax_error($post_id->get_error_message());

    // 封面
    if ($thumb && get_post($thumb) && 'attachment' === get_post($thumb)->post_type) {
        set_post_thumbnail($post_id, $thumb);
    } elseif (!$thumb && $edit_id) {
        delete_post_thumbnail($post_id);
    }

    // 审核中的文章编辑后跳转用户中心，已发布跳文章页
    $post = get_post($post_id);
    $redirect = ('publish' === $post->post_status) ? get_permalink($post_id) : nova_user_center_url('posts');
    nova_ajax_success(array('redirect' => $redirect, 'id' => $post_id), $msg);
}
add_action('wp_ajax_nova_submit_post', 'nova_ajax_submit_post');
