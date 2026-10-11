<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2026-05-19 19:02:16
 * @LastEditTime : 2026-07-18 21:22:19
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2026 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

/**
 * 解析 WordPress 小工具占位 ID，得到 id_base 与实例号。
 *
 * @param string $widget_id
 * @return array|null { id_base: string, number: int }
 */
function zib_wie_parse_widget_id($widget_id)
{
    if (!is_string($widget_id) || $widget_id === '') {
        return null;
    }
    if (!preg_match('/^(.+)-(\d+)$/', $widget_id, $m)) {
        return null;
    }

    return array(
        'id_base' => $m[1],
        'number'  => (int) $m[2],
    );
}

/**
 * 下一个可用的数字实例键（排除 _multiwidget）。
 *
 * @param array $option_arr
 * @return int
 */
function zib_wie_next_widget_instance_number($option_arr)
{
    $max = 0;
    foreach ($option_arr as $k => $v) {
        if (is_numeric($k)) {
            $max = max($max, (int) $k);
        }
    }

    return $max + 1;
}

/**
 * 当前站点已注册的小工具 id_base 集合。
 *
 * @return array<string, true>
 */
function zib_wie_get_registered_id_bases()
{
    global $wp_widget_factory;
    $bases = array();
    if (!isset($wp_widget_factory->widgets) || !is_array($wp_widget_factory->widgets)) {
        return $bases;
    }
    foreach ($wp_widget_factory->widgets as $widget_obj) {
        if (is_object($widget_obj) && isset($widget_obj->id_base)) {
            $bases[$widget_obj->id_base] = true;
        }
    }

    return $bases;
}

/**
 * 构建单栏导出数据。
 *
 * @param string $sidebar_id
 * @return array|WP_Error
 */
function zib_wie_build_export($sidebar_id)
{
    $sidebar_id = sanitize_key($sidebar_id);
    if ($sidebar_id === '') {
        return new WP_Error('zib_wie_empty', __('未指定侧栏。', 'zib_language'));
    }
    $sidebars = wp_get_sidebars_widgets();
    if (!isset($sidebars[$sidebar_id])) {
        return new WP_Error('zib_wie_sidebar', __('该侧栏不存在或尚未注册。', 'zib_language'));
    }
    $widget_list = array();
    foreach ((array) $sidebars[$sidebar_id] as $wid) {
        if ($wid !== '' && is_string($wid)) {
            $widget_list[] = $wid;
        }
    }
    global $wp_registered_sidebars;
    $sidebar_name = '';
    if (isset($wp_registered_sidebars[$sidebar_id]['name'])) {
        $sidebar_name = $wp_registered_sidebars[$sidebar_id]['name'];
    }
    $widget_options = array();
    foreach ($widget_list as $widget_id) {
        $parsed = zib_wie_parse_widget_id($widget_id);
        if (!$parsed) {
            continue;
        }
        $id_base = $parsed['id_base'];
        $num     = $parsed['number'];
        if (!isset($widget_options[$id_base])) {
            $widget_options[$id_base] = array();
        }
        $all = get_option('widget_' . $id_base, array());
        if (array_key_exists($num, $all)) {
            $widget_options[$id_base][$num] = $all[$num];
        }
    }
    foreach (array_keys($widget_options) as $id_base) {
        $all = get_option('widget_' . $id_base, array());
        if (isset($all['_multiwidget'])) {
            $widget_options[$id_base]['_multiwidget'] = $all['_multiwidget'];
        }
    }
    $theme = wp_get_theme();

    return array(
        'export_version'      => 1,
        'exported_at'         => gmdate('c'),
        'source_sidebar_id'   => $sidebar_id,
        'source_sidebar_name' => $sidebar_name,
        'theme_name'          => $theme->get('Name'),
        'theme_version'       => $theme->get('Version'),
        'widgets'             => $widget_list,
        'widget_options'      => $widget_options,
    );
}

/**
 * 导入 JSON 数据到目标侧栏。
 *
 * @param array  $data
 * @param string $target_sidebar_id
 * @param string $mode  replace：清空该侧栏后仅保留导入项；append：在现有小工具列表末尾追加导入项。
 * @return array|WP_Error { imported: int, missing_bases: string[], skipped: string[] }
 */
function zib_wie_run_import($data, $target_sidebar_id, $mode = 'replace')
{
    if (!is_array($data)) {
        return new WP_Error('zib_wie_format', __('数据格式无效。', 'zib_language'));
    }
    if (empty($data['widgets']) || !is_array($data['widgets'])) {
        return new WP_Error('zib_wie_widgets', __('JSON 中未包含 widgets 列表。', 'zib_language'));
    }
    if (empty($data['widget_options']) || !is_array($data['widget_options'])) {
        return new WP_Error('zib_wie_options', __('JSON 中未包含 widget_options。', 'zib_language'));
    }
    $target_sidebar_id = sanitize_key($target_sidebar_id);
    if ($target_sidebar_id === '') {
        return new WP_Error('zib_wie_target', __('未指定目标侧栏。', 'zib_language'));
    }
    $mode = is_string($mode) ? strtolower(trim($mode)) : 'replace';
    if (!in_array($mode, array('replace', 'append'), true)) {
        $mode = 'replace';
    }
    $registered = zib_wie_get_registered_id_bases();
    $missing    = array();
    foreach ($data['widget_options'] as $id_base => $_blob) {
        if (!isset($registered[$id_base])) {
            $missing[] = $id_base;
        }
    }
    $id_map_by_base = array();
    foreach ($data['widget_options'] as $id_base => $instances) {
        if (!is_array($instances)) {
            continue;
        }
        if (!is_string($id_base) || $id_base === '') {
            continue;
        }
        $existing                 = get_option('widget_' . $id_base, array());
        $multi                    = isset($instances['_multiwidget']) ? $instances['_multiwidget'] : null;
        $id_map_by_base[$id_base] = array();
        foreach ($instances as $old_num => $settings) {
            if ($old_num === '_multiwidget') {
                continue;
            }
            if (!is_numeric($old_num)) {
                continue;
            }
            $old_num                            = (int) $old_num;
            $new_num                            = zib_wie_next_widget_instance_number($existing);
            $existing[$new_num]                 = $settings;
            $id_map_by_base[$id_base][$old_num] = $new_num;
        }
        if ($multi !== null) {
            $existing['_multiwidget'] = $multi;
        }
        update_option('widget_' . $id_base, $existing);
    }
    $new_widget_ids = array();
    $skipped        = array();
    foreach ($data['widgets'] as $widget_id) {
        if (!is_string($widget_id) || $widget_id === '') {
            continue;
        }
        $parsed = zib_wie_parse_widget_id($widget_id);
        if (!$parsed) {
            $skipped[] = $widget_id;
            continue;
        }
        $ib = $parsed['id_base'];
        $on = $parsed['number'];
        if (!isset($id_map_by_base[$ib][$on])) {
            $skipped[] = $widget_id;
            continue;
        }
        $new_widget_ids[] = $ib . '-' . $id_map_by_base[$ib][$on];
    }
    $sidebars = wp_get_sidebars_widgets();
    if (!is_array($sidebars)) {
        $sidebars = array();
    }
    if ($mode === 'append') {
        $prev = isset($sidebars[$target_sidebar_id]) ? (array) $sidebars[$target_sidebar_id] : array();
        $prev = array_values(
            array_filter(
                $prev,
                function ($id) {
                    return is_string($id) && $id !== '';
                }
            )
        );
        $sidebars[$target_sidebar_id] = array_merge($prev, $new_widget_ids);
    } else {
        $sidebars[$target_sidebar_id] = $new_widget_ids;
    }
    wp_set_sidebars_widgets($sidebars);

    return array(
        'imported'      => count($new_widget_ids),
        'missing_bases' => $missing,
        'skipped'       => $skipped,
        'mode'          => $mode,
    );
}

/**
 * 判断模板中的 widgets 是否为「紧凑格式」：每项为 array，含 id_base 与 instance。
 *
 * @param mixed $widgets
 * @return bool
 */
function zib_wie_is_compact_template_widgets($widgets)
{
    if (empty($widgets) || !is_array($widgets)) {
        return false;
    }
    $first = reset($widgets);
    if (!is_array($first)) {
        return false;
    }

    return isset($first['id']) && array_key_exists('instance', $first);
}

/**
 * 将紧凑模板（widgets: [{ id => id_base, instance => [] }, …]）转为 zib_wie_run_import 所需结构。
 *
 * @param array $def 含 widgets 键的模板定义
 * @return array|WP_Error
 */
function zib_wie_compact_template_to_import_data($def)
{
    if (empty($def['widgets']) || !is_array($def['widgets'])) {
        return new WP_Error('zib_wie_tpl_compact', __('模板缺少 widgets。', 'zib_language'));
    }

    $placeholders    = array();
    $widget_options  = array();
    $counter_by_base = array();

    foreach ($def['widgets'] as $item) {
        if (!is_array($item) || empty($item['id']) || !is_string($item['id'])) {
            continue;
        }
        $id_base  = $item['id'];
        $instance = [];
        if (is_array($item['instance'])) {
            $instance = $item['instance'];
        } elseif (is_string($item['instance']) && !empty($item['instance'])) {
            $instance = @json_decode($item['instance'], true) ?? [];
        }

        if (!isset($counter_by_base[$id_base])) {
            $counter_by_base[$id_base] = 0;
        }
        $counter_by_base[$id_base]++;
        $num = $counter_by_base[$id_base];

        if (!isset($widget_options[$id_base])) {
            $widget_options[$id_base] = array();
        }
        $widget_options[$id_base][$num] = $instance;
        $placeholders[]                 = $id_base . '-' . $num;
    }

    if (empty($placeholders) || empty($widget_options)) {
        return new WP_Error('zib_wie_tpl_compact', __('模板 widgets 无有效项。', 'zib_language'));
    }

    foreach (array_keys($widget_options) as $id_base) {
        $widget_options[$id_base]['_multiwidget'] = 1;
    }

    return array(
        'export_version' => 1,
        'widgets'        => $placeholders,
        'widget_options' => $widget_options,
    );
}

/**
 * 按模板 id 在列表中查找定义（支持数字下标数组，亦兼容以 id 为键的关联数组）。
 *
 * @param string $template_id sanitize_key 后的 id
 * @return array|null
 */
function zib_wie_find_import_template_def($template_id)
{
    if ($template_id === '') {
        return null;
    }
    $templates = zib_wie_get_import_templates();
    if (isset($templates[$template_id]) && is_array($templates[$template_id])) {
        return $templates[$template_id];
    }

    foreach ($templates as $tpl) {
        if (!is_array($tpl) || empty($tpl['id'])) {
            continue;
        }
        if (sanitize_key((string) $tpl['id']) === $template_id) {
            return $tpl;
        }
    }

    return null;
}

/**
 * 按模板 ID 解析出可交给 zib_wie_run_import 的数据数组。
 *
 * @param string $template_id
 * @return array|WP_Error
 */
function zib_wie_get_template_import_data($template_id)
{
    $template_id = sanitize_key($template_id);
    if ($template_id === '') {
        return new WP_Error('zib_wie_tpl', __('未指定模板。', 'zib_language'));
    }
    $def = zib_wie_find_import_template_def($template_id);
    if ($def === null) {
        return new WP_Error('zib_wie_tpl', __('模板不存在或已被移除。', 'zib_language'));
    }

    $data = zib_wie_compact_template_to_import_data($def);

    return $data;
}

/**
 * 把小工具 id_base 转为后台可读标题（与「可用小工具」名称尽量一致）。
 *
 * @param string $id_base
 * @return string
 */
function zib_wie_widget_title_for_id_base($id_base)
{
    if (!is_string($id_base) || $id_base === '') {
        return '';
    }
    global $wp_widget_factory;
    error_log(print_r($wp_widget_factory->widgets, true));

    if (!empty($wp_widget_factory->widgets[$id_base]->name)) {
        return $wp_widget_factory->widgets[$id_base]->name;
    }

    if (isset($wp_widget_factory->widgets) && is_array($wp_widget_factory->widgets)) {
        foreach ($wp_widget_factory->widgets as $widget_obj) {
            if (!is_object($widget_obj) || !isset($widget_obj->id_base)) {
                continue;
            }
            if ((string) $widget_obj->id_base !== $id_base) {
                continue;
            }
            if (isset($widget_obj->name) && is_string($widget_obj->name) && $widget_obj->name !== '') {
                return $widget_obj->name;
            }
            if (isset($widget_obj->widget_options['description']) && is_string($widget_obj->widget_options['description'])) {
                return $widget_obj->widget_options['description'];
            }
            break;
        }
    }

    return $id_base;
}

/**
 * 从模板定义解析「模块列表」预览（顺序与导入顺序一致；仅 id_base + 展示标题）。
 *
 * @param array $tpl 模板定义
 * @return array 形如 array( array( 'id_base' => string, 'title' => string ), ... )
 */
function zib_wie_get_template_modules_preview($tpl)
{
    if (!is_array($tpl)) {
        return array();
    }

    $items = array();
    foreach ($tpl['widgets'] as $item) {
        if (!is_array($item) || empty($item['id']) || !is_string($item['id'])) {
            continue;
        }
        $id_base = $item['id'];
        $items[] = array(
            'id_base' => $id_base,
            'title'   => zib_wie_widget_title_for_id_base($id_base),
        );
    }

    return $items;
}

/**
 * AJAX：列出导入模板（元信息 + 模块预览列表）
 */
function zib_wie_ajax_list_templates()
{
    check_ajax_referer('zib_wie_io', 'nonce');
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(array('message' => __('权限不足', 'zib_language')), 403);
    }
    $list = array();
    foreach (zib_wie_get_import_templates() as $id => $tpl) {
        $list[] = array(
            'id'      => (string) $id,
            'title'   => isset($tpl['title']) ? (string) $tpl['title'] : (string) $tpl['id'],
            'desc'    => isset($tpl['desc']) ? (string) $tpl['desc'] : '',
            'remind'  => isset($tpl['remind']) ? (string) $tpl['remind'] : '',
            'modules' => zib_wie_get_template_modules_preview($tpl),
        );
    }
    wp_send_json_success(array('templates' => $list));
}
add_action('wp_ajax_zib_wie_list_templates', 'zib_wie_ajax_list_templates');

/**
 * AJAX：将已注册模板导入当前侧栏
 */
function zib_wie_ajax_import_template()
{
    check_ajax_referer('zib_wie_io', 'nonce');
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(array('message' => __('权限不足', 'zib_language')), 403);
    }
    $target = isset($_POST['sidebar_id']) ? sanitize_text_field(wp_unslash($_POST['sidebar_id'])) : '';
    $tpl_id = isset($_POST['template_id']) ? sanitize_key(wp_unslash($_POST['template_id'])) : '';

    $import_mode = isset($_POST['import_mode']) ? sanitize_key(wp_unslash($_POST['import_mode'])) : 'replace';
    if (!in_array($import_mode, array('replace', 'append'), true)) {
        $import_mode = 'replace';
    }

    $data = zib_wie_get_template_import_data($tpl_id);
    if (is_wp_error($data)) {
        wp_send_json_error(array('message' => $data->get_error_message()));
    }

    $result = zib_wie_run_import($data, $target, $import_mode);
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    $warnings = array();
    if (!empty($result['skipped'])) {
        $warnings[] = __('部分占位符已跳过：', 'zib_language') . implode(', ', array_slice($result['skipped'], 0, 15));
        if (count($result['skipped']) > 15) {
            $warnings[] = sprintf(__('…共 %d 项', 'zib_language'), count($result['skipped']));
        }
    }
    if ($import_mode === 'append') {
        $message = sprintf(
            __('已从模板向侧栏「%s」末尾追加 %d 个小工具。请刷新页面后查看效果。', 'zib_language'),
            $target,
            (int) $result['imported']
        );
    } else {
        $message = sprintf(
            __('已用模板覆盖侧栏「%s」并导入 %d 个小工具。请刷新页面后查看效果。', 'zib_language'),
            $target,
            (int) $result['imported']
        );
    }

    wp_send_json_success(
        array(
            'imported'    => (int) $result['imported'],
            'sidebar_id'  => $target,
            'import_mode' => $import_mode,
            'template_id' => $tpl_id,
            'warnings'    => $warnings,
            'message'     => $message,
        )
    );
}
add_action('wp_ajax_zib_wie_import_template', 'zib_wie_ajax_import_template');

/**
 * AJAX：导出为 JSON 文本
 */
function zib_wie_ajax_export()
{
    check_ajax_referer('zib_wie_io', 'nonce');
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(array('message' => __('权限不足', 'zib_language')), 403);
    }
    $sidebar_id = isset($_POST['sidebar_id']) ? sanitize_text_field(wp_unslash($_POST['sidebar_id'])) : '';
    $payload    = zib_wie_build_export($sidebar_id);
    if (is_wp_error($payload)) {
        wp_send_json_error(array('message' => $payload->get_error_message()));
    }
    $json_text = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    wp_send_json_success(
        array(
            'json_text'  => $json_text,
            'sidebar_id' => $sidebar_id,
        )
    );
}
add_action('wp_ajax_zib_wie_export_sidebar', 'zib_wie_ajax_export');

/**
 * AJAX：从文本域 JSON 导入
 */
function zib_wie_ajax_import()
{
    check_ajax_referer('zib_wie_io', 'nonce');
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(array('message' => __('权限不足', 'zib_language')), 403);
    }
    $target = isset($_POST['sidebar_id']) ? sanitize_text_field(wp_unslash($_POST['sidebar_id'])) : '';
    $raw    = isset($_POST['json']) ? wp_unslash($_POST['json']) : '';
    $raw    = is_string($raw) ? trim($raw) : '';
    if ($raw === '') {
        wp_send_json_error(array('message' => __('请粘贴 JSON 内容。', 'zib_language')));
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        wp_send_json_error(array('message' => __('JSON 解析失败，请检查格式。', 'zib_language')));
    }

    $import_mode = isset($_POST['import_mode']) ? sanitize_key(wp_unslash($_POST['import_mode'])) : 'replace';
    if (!in_array($import_mode, array('replace', 'append'), true)) {
        $import_mode = 'replace';
    }

    $result = zib_wie_run_import($data, $target, $import_mode);
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }
    $warnings = array();
    if (!empty($result['missing_bases'])) {
        // $warnings[] = '以下 id_base 在当前站点未注册：' . implode(', ', $result['missing_bases']);
    }
    if (!empty($result['skipped'])) {
        $warnings[] = __('部分占位符已跳过：', 'zib_language') . implode(', ', array_slice($result['skipped'], 0, 15));
        if (count($result['skipped']) > 15) {
            $warnings[] = sprintf(__('…共 %d 项', 'zib_language'), count($result['skipped']));
        }
    }
    if ($import_mode === 'append') {
        $message = sprintf(
            __('已向侧栏「%s」末尾追加 %d 个小工具。请刷新页面后查看效果。', 'zib_language'),
            $target,
            (int) $result['imported']
        );
    } else {
        $message = sprintf(
            __('已覆盖侧栏「%s」并导入 %d 个小工具。请刷新页面后查看效果。', 'zib_language'),
            $target,
            (int) $result['imported']
        );
    }

    wp_send_json_success(
        array(
            'imported'    => (int) $result['imported'],
            'sidebar_id'  => $target,
            'import_mode' => $import_mode,
            'warnings'    => $warnings,
            'message'     => $message,
        )
    );
}
add_action('wp_ajax_zib_wie_import_sidebar', 'zib_wie_ajax_import');


//获取文章数量最多的分类IDS
function zib_wie_get_most_posts_term_ids($num = 10)
{
    $terms = get_terms(array(
        'taxonomy'   => 'category',
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => $num,
        'hide_empty' => true,
    ));

    if (!is_wp_error($terms) && !empty($terms)) {
        $term_ids = array();
        foreach ($terms as $term) {
            $term_ids[] = $term->term_id;
        }
        return $term_ids;
    }

    return array();
}

/**
 * 侧栏导入模板注册表。
 *
 * @return array<int|string, array<string, mixed>>
 */
function zib_wie_get_import_templates()
{
    $templates = array();

    $templates['post_home_card_01'] = array(
        'title'   => __('简约风格首页文章模板', 'zib_language'),
        'desc'    => __('类似zibll.com的首页，由幻灯片、文章等模块组合，适合没有侧边栏的首页样式，建议放置在首页顶部全宽度', 'zib_language'),
        'widgets' => array(
            array(
                'id'       => 'zib_widget_ui_slider',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "slides": [ { "background": "\/wp-content\/themes\/zibll\/img\/slider-bg.jpg", "background_video": "", "hide": "", "link": { "url": "https:\/\/www.zibll.com\/", "text": "", "target": "_blank" }, "image_layer": [ { "image": "\/wp-content\/themes\/zibll\/img\/slider-layer-1.png", "free_size": "1", "align": "center", "parallax": "-100", "parallax_scale": "100", "parallax_opacity": "100" }, { "image": "\/wp-content\/themes\/zibll\/img\/slider-layer-2.png", "free_size": "1", "align": "center", "parallax": "-50", "parallax_scale": "50", "parallax_opacity": "30" } ], "text": { "title": "", "desc": "", "text_align": "left-bottom", "text_size_pc": "30", "text_size_m": "20", "parallax": "40" } }, { "background": "https:\/\/picsum.photos\/1000\/400", "background_video": "", "hide": "", "link": { "url": "", "text": "", "target": "" }, "text": { "title": "", "desc": "", "text_align": "left-bottom", "text_size_pc": "30", "text_size_m": "20", "parallax": "40" } }, { "background": "https:\/\/picsum.photos\/1000\/400", "background_video": "", "hide": "", "link": { "url": "", "text": "", "target": "" }, "text": { "title": "", "desc": "", "text_align": "left-bottom", "text_size_pc": "30", "text_size_m": "20", "parallax": "40" } }, { "background": "https:\/\/picsum.photos\/1000\/400", "background_video": "", "hide": "", "link": { "url": "", "text": "", "target": "" }, "text": { "title": "", "desc": "", "text_align": "left-bottom", "text_size_pc": "30", "text_size_m": "20", "parallax": "40" } } ], "option": { "direction": "horizontal", "loop": "1", "button": "1", "pagination": "1", "effect": "slide", "scale_height": "1", "scale": "37", "auto_height": "", "max_height": "500", "min_height": "180", "pc_height": "400", "m_height": "240", "spacebetween": "15", "speed": "0", "autoplay": "1", "interval": "4" }, "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "", "animation_repeat": "", "pc_row": "6", "m_row": "3", "mask_opacity": "0", "height_scale": "45", "font_size_pc": "16", "font_size_m": "14", "font_bold": "", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "", "link": { "url": "#", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_main_post',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "load_mode": "detail", "cat": "", "topics": "", "limit_day": "0", "orderby": "date", "order": "desc", "style": "card", "count": "12", "paginate": "ajax", "show_id_type": "", "zibpay_type": "", "mini_opt": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_main_post',
                'instance' => '{ "title": "文章精选", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "#", "text": "更多<i class=\"fa fa-angle-right em12 ml6\"><\/i>", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "load_mode": "ajax", "cat": "", "topics": "", "limit_day": "0", "orderby": "modified", "order": "desc", "style": "mini", "mini_opt": [ "show_thumb", "show_meta" ], "count": "12", "paginate": "number", "show_id_type": "", "zibpay_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_term_lists_card',
                'instance' => [
                    'pc_row'       => 2,
                    'term_id'      => zib_wie_get_most_posts_term_ids(4),
                    'orderby'      => 'modified',
                    'order'        => 'desc',
                    'count'        => '4',
                    'target_blank' => '',
                    'show_id_type' => '',
                ],
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "", "animation_repeat": "", "pc_row": "1", "m_row": "1", "mask_opacity": "10", "height_scale": "16", "font_size_pc": "30", "font_size_m": "16", "font_bold": "1", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/1000\/200", "video": "", "hide": "", "title": "<div class=\"em12\">专心做好一件事<\/div>\r\n打造更优雅的WordPress主题", "link": { "url": "", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
        ),
    );

    $templates['link_lists_01'] = array(
        'title'   => __('链接列表组合模板', 'zib_language'),
        'desc'    => __('多种样式的链接列表模块组合，适用于友情链接、网址导航、合作合伴展示等场景', 'zib_language') . zib_get_zibll_theme_tutorial_link(39214, '', __('【查看教程】', 'zib_language')),
        'widgets' => array(
            array(
                'id'       => 'widget_ui_links_lists_2',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "links_limit": "10", "links_orderby": "rand", "links_order": "ASC", "show_box": "", "go_link": "1", "nofollow": "1", "blank": "", "alignment": "", "type": "card", "show_id_type": "", "links_cats": "" }',
            ),
            array(
                'id'       => 'widget_ui_links_lists_2',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "links_limit": "12", "links_orderby": "name", "links_order": "ASC", "show_box": "", "go_link": "1", "nofollow": "1", "blank": "", "alignment": "", "type": "bigcard", "show_id_type": "", "links_cats": "" }',
            ),
            array(
                'id'       => 'widget_ui_links_lists_2',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "links_limit": "12", "links_orderby": "rand", "links_order": "ASC", "show_box": "on", "go_link": "on", "nofollow": "1", "blank": "", "alignment": "", "type": "bigcard", "show_id_type": "", "links_cats": "" }',
            ),
            array(
                'id'       => 'widget_ui_links_lists_2',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "links_limit": "28", "links_orderby": "rand", "links_order": "DESC", "show_box": "1", "go_link": "on", "nofollow": "1", "blank": "", "alignment": "center", "type": "image", "show_id_type": "", "links_cats": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_main_post',
                'instance' => '{ "title": "", "subtitle": "", "title_link": { "url": "", "text": "", "target": "" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "load_mode": "detail", "cat": "", "topics": "", "limit_day": "0", "orderby": "rand", "order": "desc", "style": "mini", "mini_opt": [ "show_thumb", "show_meta" ], "count": "12", "paginate": "", "show_id_type": "", "zibpay_type": "" }',
            ),
            array(
                'id'       => 'widget_ui_links_lists_2',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "links_limit": "10", "links_orderby": "name", "links_order": "ASC", "show_box": "on", "go_link": "1", "nofollow": "1", "blank": "", "alignment": "center", "type": "simple", "show_id_type": "", "links_cats": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_partners_scroll',
                'instance' => '{ "title": "", "subtitle": "", "title_link": { "url": "", "text": "", "target": "" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "rows": "3", "m_rows": "3", "style_type": "card", "card_width": "200", "card_height": "60", "card_bg": "1", "card_radius": "16", "speed": "3", "direction": "alternate", "pause_hover": "1", "show_widget_bg": "", "go_link": "1", "end_fade": "1", "link_type": "link", "link_limit": "50", "link_orderby": "rand", "page_links_order": "ASC", "show_id_type": "", "link_cats": "", "partners": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_partners_scroll',
                'instance' => '{ "title": "", "subtitle": "", "title_link": { "url": "", "text": "", "target": "" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "rows": "3", "m_rows": "3", "style_type": "chip", "card_width": "200", "card_height": "60", "card_bg": "0", "card_radius": "16", "speed": "3", "direction": "alternate", "pause_hover": "1", "show_widget_bg": "0", "go_link": "1", "end_fade": "1", "link_type": "link", "link_limit": "50", "link_orderby": "rand", "page_links_order": "ASC", "show_id_type": "", "link_cats": "", "partners": "" }',
            ),
        ),
    );

    $templates['post_lists_01'] = array(
        'title'   => __('文章相关模块组合', 'zib_language'),
        'desc'    => __('常用的文章类模块组合，适合快速添加文章相关模块', 'zib_language'),
        'widgets' => array(
            array(
                'id'       => 'zib_widget_ui_main_post',
                'instance' => '{ "title": "最新文章", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "load_mode": "ajax", "cat": "", "topics": "", "limit_day": "0", "orderby": "modified", "order": "desc", "style": "list", "count": "6", "paginate": "number", "show_id_type": "", "zibpay_type": "", "mini_opt": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_main_post',
                'instance' => '{ "title": "文章卡片", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "load_mode": "ajax", "cat": "", "topics": "", "limit_day": "0", "orderby": "modified", "order": "desc", "style": "card", "count": "12", "paginate": "ajax", "show_id_type": "", "zibpay_type": "", "mini_opt": "" }',
            ),
            array(
                'id'       => 'widget_ui_oneline_posts',
                'instance' => '{ "title": "左右滚动文章", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "cat": "", "topics": "", "orderby": "modified", "order": "desc", "style": "card", "count": "8", "show_id_type": "", "zibpay_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_tab_post',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "style": "mini", "mini_opt": [ "show_thumb", "show_meta" ], "count": "6", "paginate": "number", "tabs": [ { "title": "热门推荐", "cat": "", "topics": "", "limit_day": "", "orderby": "views", "order": "asc" }, { "title": "最近更新", "cat": "", "topics": "", "limit_day": "", "orderby": "modified", "order": "asc" }, { "title": "最新发布", "cat": "", "topics": "", "limit_day": "", "orderby": "date", "order": "asc" }, { "title": "猜你喜欢", "cat": "", "topics": "", "limit_day": "", "orderby": "rand" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_term_lists_card',
                'instance' => [
                    'pc_row'       => 2,
                    'term_id'      => zib_wie_get_most_posts_term_ids(4),
                    'orderby'      => 'modified',
                    'order'        => 'desc',
                    'count'        => '4',
                    'target_blank' => '',
                    'show_id_type' => '',
                ],
            ),
        ),
    );

    $templates['shop_home_01'] = array(
        'title'   => __('商城首页模板', 'zib_language'),
        'desc'    => __('由商品相关的模块组成，适合做商城页面时使用！', 'zib_language') . zib_get_zibll_theme_tutorial_link(39214, '', __('【查看教程】', 'zib_language')),
        'widgets' => array(
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "6", "m_row": "3", "mask_opacity": "10", "height_scale": "38", "font_size_pc": "24", "font_size_m": "18", "font_bold": "0", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "手机专区", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "配件专区", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "穿戴专区", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "电脑专区", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "智能专区", "link": { "url": "#", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/300\/150", "video": "", "hide": "", "title": "维修专区", "link": { "url": "#", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "手机", "subtitle": "请自行更换封面图或视频", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "1", "m_row": "1", "mask_opacity": "0", "height_scale": "36", "font_size_pc": "18", "font_size_m": "14", "font_bold": "", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/1300\/50", "video": "", "hide": "", "title": "", "link": { "url": "", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_shop_widget_ui_oneline_product_lists',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "rand", "count": "8", "list_style": { "style": "", "thumb_scale": "100", "thumb_fit": "", "title_one_line": "1", "show_desc": "1", "show_price": "1", "show_discount": "1", "show_sales": "", "show_sales_min": "10", "text_center": "1" }, "show_id_type": "", "exclude_cat": "", "include_cat": "", "include_dis": "", "include_tag": "" }',
            ),
            array(
                'id'       => 'zib_shop_widget_ui_product_lists',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "only_pc", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "views", "count": "8", "paginate": "", "list_style": { "style": "small", "thumb_scale": "100", "thumb_fit": "", "title_one_line": "1", "show_desc": "0", "show_price": "1", "show_discount": "1", "show_sales": "", "show_sales_min": "10", "text_center": "1" }, "show_id_type": "", "exclude_cat": "", "include_cat": "", "include_dis": "", "include_tag": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "电脑", "subtitle": "请自行更换封面图或视频", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "1", "m_row": "1", "mask_opacity": "0", "height_scale": "35", "font_size_pc": "18", "font_size_m": "14", "font_bold": "", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/1800\/80", "video": "", "hide": "", "title": "", "link": { "url": "", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_shop_widget_ui_product_lists',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "views", "count": "4", "paginate": "", "list_style": { "style": "", "thumb_scale": "100", "thumb_fit": "", "title_one_line": "0", "show_desc": "0", "show_price": "1", "show_discount": "0", "show_sales": "", "show_sales_min": "10", "text_center": "1" }, "show_id_type": "", "exclude_cat": "", "include_cat": "", "include_dis": "", "include_tag": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "穿戴", "subtitle": "请自行更换封面图或视频", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "#", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "1", "m_row": "1", "mask_opacity": "0", "height_scale": "35", "font_size_pc": "18", "font_size_m": "14", "font_bold": "", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/1800\/800", "video": "", "hide": "", "title": "", "link": { "url": "https:\/\/demo.zibll.com\/shop\/2456.html", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_shop_widget_ui_oneline_product_lists',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "rand", "count": "8", "list_style": { "style": "", "thumb_scale": "100", "thumb_fit": "", "title_one_line": "1", "show_desc": "1", "show_price": "1", "show_discount": "1", "show_sales": "", "show_sales_min": "10", "text_center": "1" }, "show_id_type": "", "exclude_cat": "", "include_cat": "", "include_dis": "", "include_tag": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "1", "m_row": "1", "mask_opacity": "0", "height_scale": "35", "font_size_pc": "18", "font_size_m": "14", "font_bold": "", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/1800\/800", "video": "", "hide": "", "title": "", "link": { "url": "https:\/\/demo.zibll.com\/shop_cat\/points-mall", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),

            array(
                'id'       => 'zib_shop_widget_ui_tab_product',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "only_pc", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "count": "8", "paginate": "number", "list_style": { "style": "small", "thumb_scale": "100", "thumb_fit": "", "title_one_line": "0", "show_desc": "0", "show_price": "1", "show_discount": "1", "show_sales": "", "show_sales_min": "10", "text_center": "1" }, "tabs": [ { "title": "商城热卖", "orderby": "rand" }, { "title": "最近上新", "orderby": "rand" }, { "title": "官方补贴", "orderby": "rand" }, { "title": "限时优惠", "orderby": "rand" }, { "title": "推荐商品", "orderby": "rand" }, { "title": "随便逛逛", "orderby": "rand" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_graphic_cover',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "4", "m_row": "2", "mask_opacity": "10", "height_scale": "150", "font_size_pc": "20", "font_size_m": "14", "font_bold": "", "font_color": "", "covers": [ { "image": "https:\/\/picsum.photos\/500\/1000", "video": "", "hide": "", "title": "<div style=\"text-align: left; padding-left: 4%; margin-top: 110%;\"><b class=\"em12\" >系统 <\/b><div>自然流畅，超有Al<\/div><\/div>", "link": { "url": "", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/500\/1000", "video": "", "hide": "", "title": "<div style=\"text-align: left; padding-left: 4%; margin-top: 110%;\"><b class=\"em12\" >性能 <\/b><div>\r\n探索自由连接的2030\r\n<\/div><\/div>", "link": { "url": "", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/500\/1000", "video": "", "hide": "", "title": "<div style=\"text-align: left; padding-left: 4%; margin-top: 110%;\"><b class=\"em12\" >系统 <\/b><div>自然流畅，超有Al<\/div><\/div>", "link": { "url": "", "text": "", "target": "" } }, { "image": "https:\/\/picsum.photos\/500\/1000", "video": "", "hide": "", "title": "<div style=\"text-align: left; padding-left: 4%; margin-top: 110%;\"><b class=\"em12\" >影像 <\/b><div>同享影像创作的愉悦<\/div><\/div>", "link": { "url": "", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),

            array(
                'id'       => 'zib_widget_ui_icon_cover_card',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "pc_row": "4", "m_row": "2", "show_widget_bg": "0", "icon_radius4": "1", "cards": [ { "title": "24小时极速发货", "desc": "", "icon": "zibsvg-transit", "customize_icon": "", "icon_class": "b-cyan", "link": { "url": "", "text": "", "target": "" } }, { "title": "7天退货 15天换货", "desc": "", "icon": "zibsvg-return", "customize_icon": "", "icon_class": "b-blue", "link": { "url": "", "text": "", "target": "" } }, { "title": "官方售后 全国联保", "desc": "", "icon": "zibsvg-check-circle", "customize_icon": "", "icon_class": "b-yellow", "link": { "url": "", "text": "", "target": "" } }, { "title": "全场满69包邮", "desc": "", "icon": "zibsvg-gift", "customize_icon": "", "icon_class": "b-green", "link": { "url": "", "text": "", "target": "" } } ], "show_id_type": "" }',
            ),
        ),
    );

    $templates['post_sidebar_01'] = array(
        'title'   => __('文章页-侧边栏模板', 'zib_language'),
        'desc'    => '',
        'widgets' => array(
            array(
                'id'       => 'zib_widget_ui_posts_pay',
                'instance' => '{ "sidebar_affix": "1", "theme": "jb-vip1" }',
            ),
            array(
                'id'       => 'widget_ui_posts_navs',
                'instance' => '{ "title": "文章目录", "mini_title": "", "in_affix": "on" }',
            ),
            array(
                'id'       => 'zib_widget_ui_hot_posts',
                'instance' => '{"orderby": "views","count": "6"}',
            ),
            array(
                'id'       => 'zib_widget_ui_tab_post',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "style": "mini", "count": "6", "paginate": "", "tabs": [ { "title": "热门推荐", "cat": "", "topics": "", "limit_day": "", "orderby": "views" }, { "title": "最新发布", "cat": "", "topics": "", "limit_day": "", "orderby": "date" }, { "title": "最近更新", "cat": "", "topics": "", "limit_day": "", "orderby": "modified" }, { "title": "猜你喜欢", "cat": "", "topics": "", "limit_day": "", "orderby": "rand" } ], "show_id_type": "", "mini_opt": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_user_ranking',
                'instance' => '{ "title": "签到排行", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "checkin_all_day", "number": "5", "desc": "user_registered", "top_badge": "1", "checkin_btn": "1", "show_id_type": "" }',
            ),
            array(
                'id'       => 'widget_ui_tag_cloud',
                'instance' => '{ "title": "热门标签", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "taxonomy": "category", "number": "12", "orderby": "count", "order": "ASC", "color": "rand", "fixed_width": "1", "blank": "", "show_count": "0", "show_id_type": "" }',
            ),
            array(
                'id'       => 'widget_ui_new_comment',
                'instance' => '{ "title": "最新评论", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "limit": "5", "outer": "1", "outpost": "", "show_id_type": "" }',
            ),
        ),
    );

    $templates['bbs_home_sidebar_01'] = array(
        'title'   => __('论坛首页-侧边栏模板', 'zib_language'),
        'desc'    => '',
        'widgets' => array(
            array(
                'id'       => 'widget_ui_user',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "1", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "show_img_bg": "1", "loged_title": "HI！请登录", "show_button": "1", "button_1": "bbs_posts", "button_1_class": "jb-pink", "button_1_text": "发布帖子", "button_2": "home", "button_2_class": "jb-cyan", "button_2_text": "我的主页", "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_posts_lists',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "0", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "current_plate": "", "filter": [ "topping", "essence" ], "orderby": "rand", "style": "minimalism", "alone": "", "paged_size": "6", "paginate": "none", "show_id_type": "", "include_plate": "", "exclude_plate": "", "include_topic": "", "include_tag": "", "bbs_type": "", "allow_view": "" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_plate_lists',
                'instance' => '{ "title": "推荐板块", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "only_pc", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "filter": "", "orderby": "views", "style": "card", "showposts": "6", "show_id_type": "", "cat": "" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_tab_posts',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "style": "mini", "alone": "", "paged_size": "6", "paginate": "default", "tabs": [ { "title": "热门推荐", "current_plate": "", "orderby": "views" }, { "title": "热点疑问", "current_plate": "", "orderby": "score" }, { "title": "精华教程", "current_plate": "", "filter": [ "topping", "essence", "is_hot" ], "orderby": "date" }, { "title": "随便看看", "current_plate": "", "orderby": "rand" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_topic_lists',
                'instance' => '{ "title": "热门话题", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "views", "hide_empty": "1", "paged_size": "6", "show_id_type": "", "include": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_user_ranking',
                'instance' => '{ "title": "活跃用户", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "orderby": "followed-user-count", "number": "6", "desc": "desc", "top_badge": "1", "checkin_btn": "1", "show_id_type": "" }',
            ),
        ),
    );

    $templates['bbs_plate_sidebar_01'] = array(
        'title'   => __('论坛板块页面-侧边栏模板', 'zib_language'),
        'desc'    => '',
        'widgets' => array(
            array(
                'id'       => 'zib_bbs_widget_ui_plate_info',
                'instance' => '{ "sidebar_affix": "1", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } } }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_plate_moderator',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "title_text": "本版版主", "apply_btn": "1", "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_posts_lists',
                'instance' => '{ "title": "本版热门", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "current_plate": "1", "orderby": "views", "style": "minimalism", "alone": "", "paged_size": "8", "paginate": "none", "show_id_type": "", "include_plate": "", "exclude_plate": "", "include_topic": "", "include_tag": "", "bbs_type": "", "allow_view": "", "filter": "" }',
            ),
        ),
    );

    $templates['bbs_post_sidebar_01'] = array(
        'title'   => __('论坛帖子页面-侧边栏模板', 'zib_language'),
        'desc'    => '',
        'widgets' => array(
            array(
                'id'       => 'widget_ui_posts_navs',
                'instance' => '{ "title": "文章目录", "mini_title": "", "in_affix": "on" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_plate_info',
                'instance' => '{ "sidebar_affix": "1", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } } }',
            ),
            array(
                'id'       => 'widget_ui_user',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "1", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "show_img_bg": "1", "loged_title": "HI！请登录", "show_button": "1", "button_1": "bbs_posts", "button_1_class": "jb-pink", "button_1_text": "发布帖子", "button_2": "home", "button_2_class": "jb-cyan", "button_2_text": "我的主页", "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_bbs_widget_ui_tab_posts',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "", "animation_repeat": "", "layout_pin_pc": { "top": "", "bottom": "" }, "layout_pin_m": { "top": "", "bottom": "" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "style": "minimalism", "alone": "", "paged_size": "6", "paginate": "default", "tabs": [ { "title": "热门推荐", "current_plate": "", "orderby": "views" }, { "title": "热点疑问", "current_plate": "", "orderby": "score" }, { "title": "精华教程", "current_plate": "", "filter": [ "topping", "essence", "is_hot" ], "orderby": "date" }, { "title": "随便看看", "current_plate": "", "orderby": "rand" } ], "show_id_type": "" }',
            ),
        ),
    );

    $templates['page_pay_zibll'] = array(
        'title'   => __('产品销售页面模板', 'zib_language'),
        'desc'    => __('仿zibll销售页面的模块模板，适合创建单一产品介绍的页面，或独立VIP购买页面等场景，只能放置到空白页面的顶部全宽度！', 'zib_language') . zib_get_zibll_theme_tutorial_link(48023, '', __('【查看教程】', 'zib_language')),
        'widgets' => array(
            array(
                'id'       => 'zib_widget_ui_tilt_cases',
                'instance' => '{ "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "80", "bottom": "80" }, "layout_pin_m": { "top": "30", "bottom": "10" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "fixed", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "0", "show_box": "", "intro_badge_text": "专为社区而生", "intro_badge_icon": "fa fa-users", "intro_badge_color": "c-green", "intro_title": "zibll主题\r\n更优雅的全能型主题", "intro_title_highlight": "全能型主题", "intro_title_highlight_color": "cg-red", "intro_subtitle": "集成会员系统、付费功能、商城、论坛等强大功能于一体助力您快速搭建内容、社区与商业化兼具的专业网站", "intro_features": [ { "icon": "fa fa-refresh", "text": "持续迭代更新" }, { "icon": "fa fa-wrench", "text": "专业技术支持" }, { "icon": "fa fa-magic", "text": "高度自由化" } ], "intro_buttons": [ { "btn": { "type": "link", "post_id": "21347", "post_text": "立即购买", "post_paid_text": "去查看", "vip_level": "1", "vip_text": "立即开通", "vip_upgrade_text": "升级会员", "vip_already_text": "我的会员", "link": { "url": "#", "text": "立即购买", "target": "_blank" } }, "icon": "fa fa-key", "color": "jb-pink" }, { "btn": { "type": "link", "post_id": "", "post_text": "立即购买", "post_paid_text": "去查看", "vip_level": "1", "vip_text": "立即开通", "vip_upgrade_text": "升级会员", "vip_already_text": "我的会员", "link": { "url": "#", "text": "查看演示", "target": "" } }, "icon": "zibsvg-shopping-cart", "color": "c-yellow" } ], "intro_trust_badges": [ { "icon": "fa fa-check-circle", "text": "一次购买，终身使用" }, { "icon": "fa fa-shield", "text": "30天无理由退款保障" } ], "intro_showcase_images": [ { "image": "https:\/\/picsum.photos\/800\/1000", "image_dark": "", "offset_pc": { "top": "", "left": "20", "unit": "%" }, "offset_m": { "top": "", "left": "", "unit": "px" }, "scale": "120", "z_index": "1", "depth": "20" }, { "image": "https:\/\/picsum.photos\/200\/400", "image_dark": "", "offset_pc": { "top": "30", "left": "60", "unit": "%" }, "offset_m": { "top": "", "left": "", "unit": "px" }, "scale": "80", "z_index": "1", "depth": "120" }, { "image": "https:\/\/picsum.photos\/600\/450", "image_dark": "", "offset_pc": { "top": "20", "left": "-30", "unit": "%" }, "offset_m": { "top": "", "left": "", "unit": "px" }, "scale": "60", "z_index": "3", "depth": "60" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_pricing_plans',
                'instance' => '{ "title": "选择适合您的购买方案", "subtitle": "多种购买方案，让您的选择更安心", "title_style": "big", "title_highlight": "购买方案", "title_highlight_color": "cg-red", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "lists": [ { "name": "基础套餐", "desc": "入门首选，更实惠", "tag": "", "is_featured": "", "color": "blue", "intro": "3个域名授权名额\r\n域名永久免费更换\r\n域名更换不限次数\r\n赠送永久代理会员\r\n永久授权使用\r\n永久免费更新\r\n正版用户认证\r\n千人正版用户交流群\r\n永久配置指导服务", "price": "199", "show_price": "2", "mark": "￥", "promotion_tag": "", "btn": { "type": "link", "post_id": "", "post_text": "立即购买", "post_paid_text": "去查看", "vip_level": "1", "vip_text": "立即开通", "vip_upgrade_text": "升级会员", "vip_already_text": "我的会员", "link": { "url": "#", "text": "查看说明", "target": "" } } }, { "name": "PLUS套餐", "desc": "多人选择", "tag": "推荐", "is_featured": "1", "color": "pink", "intro": "3个域名授权名额\r\n域名永久免费更换\r\n域名更换不限次数\r\n赠送永久代理会员\r\n永久授权使用\r\n永久免费更新\r\n正版用户认证\r\n千人正版用户交流群\r\n永久配置指导服务", "price": "699", "show_price": "999", "mark": "￥", "promotion_tag": "限时优惠", "btn": { "type": "link", "post_id": "", "post_text": "立即购买", "post_paid_text": "去查看", "vip_level": "1", "vip_text": "立即开通", "vip_upgrade_text": "升级会员", "vip_already_text": "我的会员", "link": { "url": "#", "text": "立即购买", "target": "_blank" } } }, { "name": "MAX套餐", "desc": "企业首选", "tag": "企业", "is_featured": "", "color": "purple", "intro": "3个域名授权名额\r\n域名永久免费更换\r\n域名更换不限次数 \r\n赠送永久代理会员\r\n永久授权使用\r\n永久免费更新\r\n正版用户认证\r\n千人正版用户交流群\r\n永久配置指导服务", "price": "1999", "show_price": "", "mark": "￥", "promotion_tag": "", "btn": { "type": "link", "post_id": "", "post_text": "立即购买", "post_paid_text": "去查看", "vip_level": "2", "vip_text": "立即开通<badge class=\"ml10 c-red\">永久<\/badge>", "vip_upgrade_text": "升级会员", "vip_already_text": "我的会员", "link": { "url": "#", "text": "立即购买", "target": "_blank" } } } ], "trust_items": [ { "icon": "fa fa-shield", "title": "正版授权", "desc": "官方授权 · 可验真" }, { "icon": "fa fa-gift", "title": "终身使用", "desc": "一次购买长期可用" }, { "icon": "fa fa-headphones", "title": "专业支持", "desc": "工单与更新通道" }, { "icon": "fa fa-refresh", "title": "持续更新", "desc": "主题与模块迭代" }, { "icon": "fa fa-lock", "title": "数据安全", "desc": "站点数据您掌控" }, { "icon": "fa fa-lock", "title": "数据安全", "desc": "站点数据您掌控" } ], "foot_note": "所有方案均包含 30 天无理由退款保障，让您购买无忧 ", "foot_icon": "fa fa-shield", "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_feature_intro',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "rgba(204,173,173,0.08)", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "media_position": "right", "media_ratio": "70", "show_box": "", "heading": "模块化布局 灵活多变", "description": "采用模块化布局设计，像拼积木一样自由搭配组合。随心调整，无需繁琐操作，即可快速构建个性化、高自由度的网站页面，让设计更简单，功能更灵活", "show_stat": "1", "stat_number": "150", "stat_suffix": "+", "stat_label": "模块数量", "browser_chrome": "1", "imgbox": "1", "slides": [ { "tab_label": "文章模块", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" }, { "tab_label": "布局模块", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" }, { "tab_label": "商品模块", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_feature_intro',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "media_position": "left", "media_ratio": "70", "show_box": "", "heading": "全场景覆盖的商城系统", "description": "支持实物商城、知识付费、自动发卡等全类型商品，还有VIP、分销、分成、促销等功能， 再加上丝滑流畅的UI交互，功能全面，安全稳定， 助力您轻松打造属于自己的商业平台。", "show_stat": "1", "stat_number": "300", "stat_suffix": "+", "stat_label": "功能数量", "browser_chrome": "1", "imgbox": "1", "slides": [ { "tab_label": "知识付费", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" }, { "tab_label": "自动发卡", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" }, { "tab_label": "实物商城", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_feature_intro',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "rgba(175,206,205,0.08)", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "media_position": "right", "media_ratio": "70", "show_box": "", "heading": "强大的用户功能体系", "description": "完整且强大的用户功能体系，从登录注册、社交账号登录、手机号登录，到用户发布、权限分级、VIP会员、站内消息、用户等级、积分、余额、徽章、签到等功能一应俱全，让站长仅需简单配置，即可轻松搭建高互动性的社区生态平台", "show_stat": "1", "stat_number": "600", "stat_suffix": "+", "stat_label": "特色功能", "browser_chrome": "1", "imgbox": "1", "slides": [ { "tab_label": "1", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_feature_intro',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "media_position": "left", "media_ratio": "70", "show_box": "", "heading": "完善的社区论坛生态", "description": "完整且强大的社区论坛生态系统，深度结合用户权限、知识付费、投票互动、问答悬赏等丰富功能，并提供高度自由化的模块与功能配置。无论是搭建完整的社区论坛、轻量化交流圈子，还是专业问答平台，都能轻松实现，充分满足不同场景下的社区运营需求", "show_stat": "1", "stat_number": "100", "stat_suffix": "+", "stat_label": "论坛功能", "browser_chrome": "1", "imgbox": "1", "slides": [ { "tab_label": "1", "media_type": "image", "image": "https:\/\/picsum.photos\/1000\/700", "image_dark": "", "video_url": "", "video_poster": "" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_highlights',
                'instance' => '{ "title": "强大且丰富的生态功能", "subtitle": "一个主题即可轻松搭建内容资讯、社区交流、知识付费、商城商业等多种类型网站", "title_style": "big", "title_highlight": "生态功能", "title_highlight_color": "cg-red", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "rgba(204,173,173,0.08)", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "large_cards": [ { "color": "pink", "icon": "fa fa-shopping-cart", "title": "商城系统", "desc": "完整的电商解决方案，助力内容变现", "features": "商品管理 & 订单系统\r\n优惠券 & 营销活动\r\n多种支付方式支持\r\n库存管理 & 物流支持", "image": "", "image_dark": "", "button_text": "了解更多", "button_url": "#" }, { "color": "blue", "icon": "fa fa-comments", "title": "社区论坛系统", "desc": "专业的社区互动平台，增强用户粘性", "features": "多板块 & 子论坛\r\n话题讨论 & 回复\r\n用户等级 & 积分\r\n私信 & @ 提醒", "image": "", "image_dark": "", "button_text": "了解更多", "button_url": "#" } ], "medium_cards": [ { "color": "green", "icon": "fa fa-th-large", "title": "模块化布局", "desc": "自由灵活的页面构建", "features": "可视化拖拽布局\r\n多种模块组合\r\n自定义样式设计", "image": "", "image_dark": "", "button_text": "", "button_url": "" }, { "color": "orange", "icon": "fa fa-user-circle", "title": "会员系统", "desc": "强大的会员管理体系", "features": "多级会员 & 权限\r\n成长值 & 积分系统\r\n会员专属内容", "image": "", "image_dark": "", "button_text": "", "button_url": "" }, { "color": "purple", "icon": "fa fa-credit-card", "title": "支付集成", "desc": "多种支付方式支持", "features": "微信 & 支付宝\r\nZibPay 支付方案\r\n自动发货 & 退款", "image": "", "image_dark": "", "button_text": "", "button_url": "" } ], "small_cards": [ { "title": "资源下载", "desc": "专业的资源管理系统", "icon": "fa fa-download", "color": "blue" }, { "title": "内容保护", "desc": "全方位内容保护机制", "icon": "fa fa-shield", "color": "purple" }, { "title": "付费内容", "desc": "多样化内容变现方式", "icon": "fa fa-gift", "color": "pink" }, { "title": "AI 集成", "desc": "智能化内容创作助手", "icon": "fa fa-android", "color": "blue" }, { "title": "个性定制", "desc": "高度可定制化设置", "icon": "fa fa-paint-brush", "color": "red" } ], "mobile_image": "shrink", "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_combo_info_card',
                'instance' => '{ "title": "为什么大家都选择Zibll主题", "subtitle": "从视觉设计、功能生态到性能优化与运营能力，打磨每一处细节。无论是内容社区、资源站、知识付费还是商城网站，都能轻松实现高品质搭建与运营", "title_style": "big", "title_highlight": "大家都选择", "title_highlight_color": "cg-red", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "fixed", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "pc_row": "4", "m_row": "2", "pc_gap": "10", "m_gap": "5", "card_radius": "18", "top_band": "1", "top_band_height": "150", "go_link": "1", "cta_text": "查看演示", "cta_color": "c-red", "cards": [ { "title": "功能丰富且强大", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M516.5568 512.8704m-432.3328 0a432.3328 432.3328 0 1 0 864.6656 0 432.3328 432.3328 0 1 0-864.6656 0Z\" fill=\"#ED5DF9\" p-id=\"5118\"><\/path><path d=\"M291.84 418.7136a256.3072 256.3072 0 0 0-207.36 105.1136 432.4352 432.4352 0 0 0 285.952 396.032A256.768 256.768 0 0 0 291.84 418.7136z\" fill=\"#F27DFF\" p-id=\"5119\"><\/path><path d=\"M352.512 482.9696L301.7216 680.96a65.536 65.536 0 0 0 47.1552 79.7696l240.64 61.7472a65.4848 65.4848 0 0 0 79.7696-47.1552l78.6432-306.176c-71.0656 2.4576-247.6032 9.0112-395.4176 13.824z\" fill=\"#F8CAFF\" p-id=\"5120\"><\/path><path d=\"M757.76 452.608L727.7568 307.2a65.4848 65.4848 0 0 0-50.8416-50.7904l-145.4592-30.3104a65.5872 65.5872 0 0 0-59.648 17.8176L223.7952 491.9808a65.4336 65.4336 0 0 0 0 92.6208L399.36 760.2688a65.4336 65.4336 0 0 0 92.6208 0l248.064-248.0128A65.4848 65.4848 0 0 0 757.76 452.608z\" fill=\"#FFFFFF\" p-id=\"5121\"><\/path><path d=\"M613.632 370.432m-46.08 0a46.08 46.08 0 1 0 92.16 0 46.08 46.08 0 1 0-92.16 0Z\" fill=\"#F27DFF\" p-id=\"5122\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "商城", "color": "jb-cyan" }, { "text": "社区", "color": "" }, { "text": "资讯", "color": "" } ], "desc": "集成商城、社区论坛、资讯内容、用户生态等丰富功能，轻松满足多种类型网站的搭建需求", "link": { "url": "https:\/\/demo.zibll.com\/", "text": "", "target": "_blank" }, "cta_text": "查看演示", "cta_color": "", "band_color": "purple" }, { "title": "漂亮的UI设计", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M511.4368 512m-432.3328 0a432.3328 432.3328 0 1 0 864.6656 0 432.3328 432.3328 0 1 0-864.6656 0Z\" fill=\"#FF6161\" p-id=\"5465\"><\/path><path d=\"M286.72 417.8432a256.256 256.256 0 0 0-207.36 105.0624 432.3328 432.3328 0 0 0 285.952 396.032A256.768 256.768 0 0 0 286.72 417.8432z\" fill=\"#FF7D7D\" p-id=\"5466\"><\/path><path d=\"M342.6304 427.9808h6.7584v293.632h-6.7584a65.3824 65.3824 0 0 1-65.3824-65.3824V493.312a65.3824 65.3824 0 0 1 65.3824-65.3312z\" fill=\"#FFCAC7\" p-id=\"5467\"><\/path><path d=\"M710.656 390.9632h-136.7552c10.5984-34.9696 24.8832-106.8032-25.088-137.1648C525.312 239.616 469.9136 229.632 471.04 291.84s-2.2528 130.4064-81.1008 136.192v293.632h280.2688a66.8672 66.8672 0 0 0 65.4848-53.4528l40.448-197.1712a66.816 66.816 0 0 0-65.4848-80.0768z\" fill=\"#FFFFFF\" p-id=\"5468\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "优雅", "color": "jb-cyan" }, { "text": "交互", "color": "" } ], "desc": "每一处细节都经过反复打磨，只为带来更加出色的视觉体验与交互感受", "link": { "url": "https:\/\/demo.zibll.com\/shop", "text": "", "target": "_blank" }, "cta_text": "立即体验", "cta_color": "", "band_color": "yellow" }, { "title": "极致性能优化", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M511.6928 514.2016m-450.816 0a450.816 450.816 0 1 0 901.632 0 450.816 450.816 0 1 0-901.632 0Z\" fill=\"#E9F4FF\" p-id=\"34415\"><\/path><path d=\"M789.7088 733.3376H742.4V256.8192a67.8912 67.8912 0 0 0-69.0688-66.56H349.9008a67.8912 67.8912 0 0 0-69.0688 66.56v476.5184h-47.1552a28.672 28.672 0 1 0 0 57.344h556.032a28.672 28.672 0 1 0 0-57.344z m-217.0368-203.0592a29.7472 29.7472 0 0 1 27.4432 17.664 27.904 27.904 0 0 1-6.4 31.232l-112.1792 108.8a30.72 30.72 0 0 1-42.0352 0 28.0064 28.0064 0 0 1 0-40.5504l61.7472-59.904H450.56a29.696 29.696 0 0 1-27.1872-17.152 27.904 27.904 0 0 1 5.12-30.72L497.664 467.3024a30.72 30.72 0 0 1 42.0352-1.6384 28.0064 28.0064 0 0 1 1.7408 40.4992l-22.9888 24.1152zM367.0528 297.3696a40.96 40.96 0 0 1 41.6768-40.192h205.9264a40.96 40.96 0 0 1 41.6768 40.192v87.9104a40.96 40.96 0 0 1-41.6768 40.192H408.7296a40.96 40.96 0 0 1-41.6768-40.192z\" fill=\"#2595E8\" p-id=\"34416\"><\/path><path d=\"M426.496 314.5728h170.3424V368.128H426.496z\" fill=\"#59ADF8\" p-id=\"34417\"><\/path><path d=\"M742.4 549.2224V256.8192a67.8912 67.8912 0 0 0-69.0688-66.56H349.9008a67.8912 67.8912 0 0 0-69.0688 66.56v476.5184h-47.1552a28.672 28.672 0 1 0 0 57.344h248.2176A452.0448 452.0448 0 0 0 742.4 549.2224z m-375.5008-163.84V297.3696a40.96 40.96 0 0 1 41.6768-40.192h205.9264a40.96 40.96 0 0 1 41.6768 40.192v87.9104a40.96 40.96 0 0 1-41.6768 40.192H408.7296a40.96 40.96 0 0 1-41.6768-40.192z m72.448 302.7968a28.0064 28.0064 0 0 1 0-40.5504l61.7472-59.904H450.56a29.696 29.696 0 0 1-27.1872-17.152 27.904 27.904 0 0 1 5.12-30.72L497.664 467.3024a30.72 30.72 0 0 1 42.0352-1.6384 28.0064 28.0064 0 0 1 1.7408 40.4992l-22.9888 24.1152h54.2208a29.7472 29.7472 0 0 1 27.4432 17.664 27.904 27.904 0 0 1-6.4 31.232l-112.1792 108.8a30.72 30.72 0 0 1-42.0352 0z\" fill=\"#3A9CED\" p-id=\"34418\"><\/path><path d=\"M447.4368 587.4176a29.5936 29.5936 0 0 1-23.9104-16.9472 27.904 27.904 0 0 1 5.12-30.72L497.664 467.3024a30.72 30.72 0 0 1 42.0352-1.6384 28.0064 28.0064 0 0 1 1.7408 40.4992l-22.9888 24.1152h1.792a452.9152 452.9152 0 0 0 80.7936-104.8064H408.7296a40.96 40.96 0 0 1-41.6768-40.192V297.3696a40.96 40.96 0 0 1 41.6768-40.192h205.9264a41.7792 41.7792 0 0 1 37.376 22.5792 456.8576 456.8576 0 0 0 6.0416-73.9328c0-5.12 0-10.496-0.256-15.6672H349.9008a67.8912 67.8912 0 0 0-69.0688 66.56v393.8304a448.8704 448.8704 0 0 0 166.6048-63.1296z\" fill=\"#59ADF8\" p-id=\"34419\"><\/path><path d=\"M370.8928 401.9712a39.1168 39.1168 0 0 1-3.84-16.6912V297.3696a40.96 40.96 0 0 1 41.6768-40.192h83.6608a447.488 447.488 0 0 0 28.8768-67.0208H349.9008a67.8912 67.8912 0 0 0-69.0688 66.56v199.68a453.5808 453.5808 0 0 0 90.0608-54.4256z\" fill=\"#6BC2FC\" p-id=\"34420\"><\/path><path d=\"M456.192 314.5728h-29.696v35.84a454.0416 454.0416 0 0 0 29.696-35.84z\" fill=\"#6BC2FC\" p-id=\"34421\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "快！更快", "color": "jb-cyan" } ], "desc": "针对WordPress进行深度性能优化，大幅提升页面加载效率，轻松实现 100ms 级极速响应", "link": { "url": "https:\/\/www.zibll.com\/1997.html", "text": "", "target": "_blank" }, "cta_text": "了解详情", "cta_color": "", "band_color": "cyan" }, { "title": "配置简单易上手", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M516.6592 510.5664m-445.4912 0a445.4912 445.4912 0 1 0 890.9824 0 445.4912 445.4912 0 1 0-890.9824 0Z\" fill=\"#F5F3FF\" p-id=\"18010\"><\/path><path d=\"M524.7488 530.432l-23.808-30.4128a13.4656 13.4656 0 0 1 2.304-18.944l58.9312-46.08a12.544 12.544 0 0 0-2.2528-21.1456 113.6128 113.6128 0 0 0-162.304 114.6368l-138.24 109.9776a63.5392 63.5392 0 0 0-13.2096 86.3744A62.3104 62.3104 0 0 0 335.616 737.28l141.3632-112.64a113.7152 113.7152 0 0 0 145.152-130.56 12.4928 12.4928 0 0 0-19.968-7.2192l-58.4704 45.7216a13.4656 13.4656 0 0 1-18.944-2.1504z\" fill=\"#8574FA\" p-id=\"18011\"><\/path><path d=\"M851.6608 453.7344l-85.3504-76.1856V258.4576a14.4896 14.4896 0 0 0-14.4896-14.4384h-63.2832a14.4896 14.4896 0 0 0-14.4896 14.4384v36.7104l-137.216-122.5216a35.84 35.84 0 0 0-48.1792 0l-308.1216 276.48a30.72 30.72 0 0 0 20.48 53.9136h72.6528v68.0448l79.9232-63.3344a163.1232 163.1232 0 0 1 4.352-29.2864 157.184 157.184 0 0 1 220.928-103.8848 55.7568 55.7568 0 0 1 31.3856 44.544 57.6512 57.6512 0 0 1-1.8432 21.6064 56.32 56.32 0 0 1 56.32 45.056 157.3376 157.3376 0 0 1-149.8624 187.5968 159.3344 159.3344 0 0 1-27.136-1.5872l-124.9792 99.5328a106.24 106.24 0 0 1-40.192 19.7632 55.6032 55.6032 0 0 0 6.5536 0.4096H701.44a55.4496 55.4496 0 0 0 55.4496-55.4496V503.296h75.6224a28.416 28.416 0 0 0 19.1488-49.5616z\" fill=\"#7666F8\" p-id=\"18012\"><\/path><path d=\"M781.1584 390.8096l-14.848-13.2608V258.4576a14.4896 14.4896 0 0 0-14.4896-14.4384h-63.2832a14.4896 14.4896 0 0 0-14.4896 14.4384v36.7104l-137.216-122.5216a35.84 35.84 0 0 0-48.1792 0l-308.1216 276.48a30.72 30.72 0 0 0 20.48 53.9136h72.6528v68.0448l79.9232-63.3344a163.1232 163.1232 0 0 1 4.352-29.2864 157.184 157.184 0 0 1 220.928-103.8848 55.7568 55.7568 0 0 1 31.3856 44.544 57.6512 57.6512 0 0 1-1.8432 21.6064 56.32 56.32 0 0 1 56.32 45.056 157.3376 157.3376 0 0 1-149.8624 187.5968 159.3344 159.3344 0 0 1-27.136-1.5872l-79.104 63.0272a391.3216 391.3216 0 0 0 367.5648-312.7296c2.048-10.4448 3.7376-20.8896 4.9664-31.2832z\" fill=\"#8574FA\" p-id=\"18013\"><\/path><path d=\"M688.5376 244.0192a14.4896 14.4896 0 0 0-14.4896 14.4384v36.7104l-137.216-122.5216a35.84 35.84 0 0 0-48.1792 0l-308.1216 276.48a30.72 30.72 0 0 0 20.48 53.9136h72.6528v68.0448l79.9232-63.3344a163.1232 163.1232 0 0 1 4.352-29.2864 157.184 157.184 0 0 1 220.928-103.8848 55.7568 55.7568 0 0 1 31.3856 44.544 57.6512 57.6512 0 0 1-1.8432 21.6064 47.36 47.36 0 0 1 8.4992 0.512 445.44 445.44 0 0 0 98.816-197.2224z\" fill=\"#9E8BFE\" p-id=\"18014\"><\/path><path d=\"M520.3456 524.8512l-19.4048-24.832a13.4656 13.4656 0 0 1 2.304-18.944l58.9312-46.08a12.544 12.544 0 0 0-2.2528-21.1456 113.6128 113.6128 0 0 0-162.304 114.6368L314.368 594.688a448 448 0 0 0 205.9776-69.8368z\" fill=\"#9E8BFE\" p-id=\"18015\"><\/path><path d=\"M178.0224 452.0448a442.9312 442.9312 0 0 0 220.5184-46.08 157.44 157.44 0 0 1 65.6384-39.8848 444.8256 444.8256 0 0 0 126.8736-145.0496l-54.2208-48.384a35.84 35.84 0 0 0-48.1792 0l-308.1216 276.48c-0.9216 1.1264-1.6896 1.9968-2.5088 2.9184z\" fill=\"#AE9BFF\" p-id=\"18016\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "易用", "color": "jb-cyan" }, { "text": "简单", "color": "" } ], "desc": "后台配置清晰易懂，新手也能快速完成网站搭建", "link": { "url": "https:\/\/www.zibll.com\/1251.html", "text": "", "target": "_blank" }, "cta_text": "了解详情", "cta_color": "", "band_color": "blue" }, { "title": "活跃的社区生态", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M516.1984 513.024m-436.2752 0a436.2752 436.2752 0 1 0 872.5504 0 436.2752 436.2752 0 1 0-872.5504 0Z\" fill=\"#F8D2FF\" p-id=\"15493\"><\/path><path d=\"M821.6064 509.7984A314.3168 314.3168 0 1 0 507.2896 824.32h202.496a17.0496 17.0496 0 0 0 17.0496-17.0496v-72.6016a313.4464 313.4464 0 0 0 94.7712-224.8704z\" fill=\"#DB90F6\" p-id=\"15494\"><\/path><path d=\"M507.2896 271.616a314.3168 314.3168 0 0 0-311.9616 276.48 314.368 314.368 0 0 0 311.9616 276.48h202.496a17.0496 17.0496 0 0 0 17.0496-17.0496v-72.8576A313.5488 313.5488 0 0 0 819.2 547.84a314.3168 314.3168 0 0 0-311.9104-276.224z\" fill=\"#D186F7\" p-id=\"15495\"><\/path><path d=\"M507.2896 347.6992a314.4704 314.4704 0 0 0-304.9984 238.2336A314.4704 314.4704 0 0 0 507.2896 824.32h202.496a17.0496 17.0496 0 0 0 17.0496-17.0496v-72.6016a314.2656 314.2656 0 0 0 85.4016-148.7872 314.368 314.368 0 0 0-304.9472-238.1824z\" fill=\"#C276F6\" p-id=\"15496\"><\/path><path d=\"M507.2896 423.8336a314.4192 314.4192 0 0 0-292.864 200.1408A314.368 314.368 0 0 0 507.2896 824.32h202.496a17.0496 17.0496 0 0 0 17.0496-17.0496v-72.6016a314.8288 314.8288 0 0 0 73.2672-110.6944 314.2656 314.2656 0 0 0-292.8128-200.1408z\" fill=\"#AF64F6\" p-id=\"15497\"><\/path><path d=\"M507.2896 499.9168a314.2144 314.2144 0 0 0-274.9952 162.0992A314.1632 314.1632 0 0 0 507.2896 824.32h202.496a17.0496 17.0496 0 0 0 17.0496-17.0496v-72.6016a315.1872 315.1872 0 0 0 55.4496-72.6528 314.2656 314.2656 0 0 0-274.9952-162.0992z\" fill=\"#A25BF4\" p-id=\"15498\"><\/path><path d=\"M507.2896 641.536A161.8944 161.8944 0 0 1 345.6 479.8464a30.72 30.72 0 0 1 61.44 0 100.2496 100.2496 0 1 0 200.448 0 30.72 30.72 0 0 1 61.44 0 161.8432 161.8432 0 0 1-161.6384 161.6896z\" fill=\"#FFFFFF\" p-id=\"15499\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "论坛", "color": "jb-cyan" }, { "text": "zibll附加插件", "color": "" } ], "desc": "上万名站长正在zibll社区交流分享建站经验，还有众多第三方开发者持续为 zibll 生态开发丰富的附加插件", "link": { "url": "https:\/\/www.zibll.com\/forums", "text": "", "target": "_blank" }, "cta_text": "查看社区", "cta_color": "", "band_color": "auto" }, { "title": "模块化布局", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M513.6384 511.6928m-445.4912 0a445.4912 445.4912 0 1 0 890.9824 0 445.4912 445.4912 0 1 0-890.9824 0Z\" fill=\"#E9FFF2\" p-id=\"43562\"><\/path><path d=\"M369.7152 228.6592c-73.2672 0-132.9152 59.5968-132.9152 132.9152 0 73.2672 59.5968 132.9152 132.9152 132.9152h94.4128c21.248 0 38.5024-17.2544 38.5024-38.5024V361.5744c-0.0512-73.2672-59.648-132.9152-132.9152-132.9152z\" fill=\"#5FFFA7\" p-id=\"43563\"><\/path><path d=\"M464.0768 528.128H369.7152c-73.2672 0-132.9152 59.5968-132.9152 132.9152 0 73.2672 59.5968 132.9152 132.9152 132.9152 73.2672 0 132.9152-59.5968 132.9152-132.9152v-94.4128c-0.0512-21.1968-17.3056-38.5024-38.5536-38.5024zM669.696 528.128h-94.4128c-21.248 0-38.5024 17.2544-38.5024 38.5024v94.4128c0 73.2672 59.5968 132.9152 132.9152 132.9152 73.2672 0 132.9152-59.5968 132.9152-132.9152-0.0512-73.2672-59.648-132.9152-132.9152-132.9152z\" fill=\"#42E59B\" p-id=\"43564\"><\/path><path d=\"M575.2832 494.4896h94.4128c73.2672 0 132.9152-59.5968 132.9152-132.9152 0-73.2672-59.5968-132.9152-132.9152-132.9152-73.2672 0-132.9152 59.5968-132.9152 132.9152v94.4128c0 21.1968 17.2544 38.5024 38.5024 38.5024zM464.0768 528.128H369.7152c-73.2672 0-132.9152 59.5968-132.9152 132.9152 0 15.7184 2.7648 30.7712 7.7824 44.7488 24.6784 10.3936 50.688 18.5856 77.9264 24.1152a410.0096 410.0096 0 0 0 160.256 0.8192c12.544-20.2752 19.8144-44.1344 19.8144-69.6832v-94.4128c0-21.1968-17.2544-38.5024-38.5024-38.5024zM747.52 553.472c-21.9136-15.872-48.7936-25.2928-77.8752-25.2928h-94.4128c-21.248 0-38.5024 17.2544-38.5024 38.5024v94.4128c0 18.2784 3.7376 35.7376 10.4448 51.5584 81.408-30.2592 152.0128-85.8624 200.3456-159.1808z\" fill=\"#54EFA5\" p-id=\"43565\"><\/path><path d=\"M256.5632 591.4624c84.1216 5.0176 165.6832-12.7488 237.6192-48.7424a38.42048 38.42048 0 0 0-30.1056-14.5408H369.7152c-47.7696-0.0512-89.7536 25.344-113.152 63.2832zM737.4336 247.296c-19.8656-11.8272-43.008-18.6368-67.7376-18.6368-73.2672 0-132.9152 59.5968-132.9152 132.9152v94.4128c0 19.9168 15.2064 36.352 34.56 38.2976 78.3872-60.416 138.1376-145.664 166.0928-246.9888z\" fill=\"#5FFFA7\" p-id=\"43566\"><\/path><path d=\"M369.7152 228.6592c-73.2672 0-132.9152 59.5968-132.9152 132.9152 0 28.4672 9.0112 54.8352 24.32 76.4928 88.6272-11.1616 171.264-47.616 238.7968-103.2192-12.4416-60.5184-66.0992-106.1888-130.2016-106.1888z\" fill=\"#7BFFBA\" p-id=\"43567\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "可视化", "color": "jb-cyan" }, { "text": "自由", "color": "" }, { "text": "灵活", "color": "" } ], "desc": "模块化页面设计，灵活自由，轻松搭建不同风格的网站", "link": { "url": "https:\/\/www.zibll.com\/1816.html", "text": "", "target": "_blank" }, "cta_text": "了解详情", "cta_color": "", "band_color": "green" }, { "title": "搜索引擎友好", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M512 515.1744m-436.2752 0a436.2752 436.2752 0 1 0 872.5504 0 436.2752 436.2752 0 1 0-872.5504 0Z\" fill=\"#D7D4FF\" p-id=\"24837\"><\/path><path d=\"M481.792 482.7648m-288.4096 0a288.4096 288.4096 0 1 0 576.8192 0 288.4096 288.4096 0 1 0-576.8192 0Z\" fill=\"#AD9DFF\" p-id=\"24838\"><\/path><path d=\"M196.2496 523.1616a285.5424 248.0128 0 1 0 571.0848 0 285.5424 248.0128 0 1 0-571.0848 0Z\" fill=\"#9F89FF\" p-id=\"24839\"><\/path><path d=\"M204.9024 563.6096a276.8896 207.616 0 1 0 553.7792 0 276.8896 207.616 0 1 0-553.7792 0Z\" fill=\"#8C6CFA\" p-id=\"24840\"><\/path><path d=\"M220.0576 604.0064a261.7344 167.2192 0 1 0 523.4688 0 261.7344 167.2192 0 1 0-523.4688 0Z\" fill=\"#805AF9\" p-id=\"24841\"><\/path><path d=\"M296.192 512.768A30.0032 30.0032 0 0 1 266.24 482.7648a215.9104 215.9104 0 0 1 215.552-215.6544 30.0544 30.0544 0 1 1 0 60.0576 155.7504 155.7504 0 0 0-155.5968 155.5968 30.0032 30.0032 0 0 1-30.0032 30.0032z\" fill=\"#FFFFFF\" p-id=\"24842\"><\/path><path d=\"M778.24 742.912l-75.1104-75.1616a290.2016 290.2016 0 0 0 17.6128-23.3472 288.512 288.512 0 0 0-477.7984 0 288.3584 288.3584 0 0 0 400.7424 77.1072l77.9776 77.9776A39.9872 39.9872 0 0 0 778.24 742.912z\" fill=\"#764BF6\" p-id=\"24843\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "SEO优化", "color": "jb-cyan" } ], "desc": "针对百度、Google 等搜索引擎进行深度 SEO 优化，提升页面收录质量与搜索表现", "link": { "url": "https:\/\/www.baidu.com\/s?wd=site%3Awww.zibll.com", "text": "", "target": "_blank" }, "cta_text": "查看演示", "cta_color": "", "band_color": "blue" }, { "title": "高效商业转化", "icon": "customsvg-<svg viewBox=\"0 0 1024 1024\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" width=\"200\" height=\"200\"><path d=\"M513.0752 511.1296m-445.4912 0a445.4912 445.4912 0 1 0 890.9824 0 445.4912 445.4912 0 1 0-890.9824 0Z\" fill=\"#FFDEF5\" p-id=\"17659\"><\/path><path d=\"M840.9088 661.9136a22.784 22.784 0 0 0-29.7472-12.2368l-45.1072 18.7904V303.0528c0-35.84-32.8192-65.024-73.2672-65.024H336.7424c-40.4992 0-73.3184 29.1328-73.3184 65.024v366.1312l-44.8-19.4048a22.7328 22.7328 0 1 0-18.0736 41.728l283.9552 122.88a62.464 62.464 0 0 0 24.6784 5.12 63.1808 63.1808 0 0 0 23.9104-4.7104l295.5776-122.88a22.784 22.784 0 0 0 12.2368-30.0032zM404.48 486.9632a22.7328 22.7328 0 0 1 0-45.4656h56.32l-48.0256-43.52a22.7328 22.7328 0 1 1 30.72-33.6896l71.0656 64.512 71.68-64.5632a22.7328 22.7328 0 1 1 30.4128 33.792L568.32 441.4976h56.6272a22.7328 22.7328 0 0 1 0 45.4656H537.6v36.5568h87.4496a22.7328 22.7328 0 0 1 0 45.4656H537.6v66.2528a22.7328 22.7328 0 1 1-45.4656 0v-66.2528H404.48a22.7328 22.7328 0 1 1 0-45.4656h87.552v-36.5568z\" fill=\"#FF279E\" p-id=\"17660\"><\/path><path d=\"M766.0544 460.8V303.0528c0-35.84-32.8192-65.024-73.2672-65.024H336.7424c-40.4992 0-73.3184 29.1328-73.3184 65.024v366.1312l-44.8-19.4048a22.7328 22.7328 0 1 0-18.0736 41.728l114.3808 49.5104A419.84 419.84 0 0 0 766.0544 460.8z m-141.1072 62.976a22.7328 22.7328 0 0 1 0 45.4656H537.6v66.2528a22.7328 22.7328 0 1 1-45.4656 0v-66.5088H404.48a22.7328 22.7328 0 1 1 0-45.4656h87.552v-36.5568H404.48a22.7328 22.7328 0 0 1 0-45.4656h56.32l-48.0256-43.52a22.7328 22.7328 0 1 1 30.72-33.6896l71.0656 64.512 71.68-64.5632a22.7328 22.7328 0 1 1 30.4128 33.792L568.32 441.4976h56.6272a22.7328 22.7328 0 0 1 0 45.4656H537.6v36.5568z\" fill=\"#FF40B2\" p-id=\"17661\"><\/path><path d=\"M263.424 303.0528v292.096a478.6688 478.6688 0 0 0 139.9808-26.2656 22.6816 22.6816 0 0 1 1.1776-45.3632H492.032v-36.5568H404.48a22.7328 22.7328 0 0 1 0-45.4656h56.32l-48.0256-43.52a22.7328 22.7328 0 1 1 30.72-33.6896l71.0656 64.512 71.68-64.5632a22.7328 22.7328 0 1 1 30.4128 33.792L568.32 441.4976h30.1056a477.6448 477.6448 0 0 0 111.5136-201.5744 82.2784 82.2784 0 0 0-17.152-1.8944H336.7424c-40.4992 0-73.3184 29.1328-73.3184 65.024z\" fill=\"#FF5AC4\" p-id=\"17662\"><\/path><path d=\"M537.6 496.64c4.1984-3.2256 8.3456-6.5024 12.3904-9.8304H537.6z\" fill=\"#FF4DC9\" p-id=\"17663\"><\/path><path d=\"M406.4256 374.5792a22.528 22.528 0 0 1 4.8128-8.704A22.8352 22.8352 0 0 1 431.36 358.4a477.7472 477.7472 0 0 0 122.3168-120.576H336.7424c-40.4992 0-73.3184 29.1328-73.3184 65.024v128a477.0304 477.0304 0 0 0 143.0016-56.2688z\" fill=\"#FF6CD9\" p-id=\"17664\"><\/path><\/svg>", "icon_image": "", "tags": [ { "text": "论坛", "color": "jb-cyan" }, { "text": "zibll附加插件", "color": "" } ], "desc": "商城、会员、积分、分销等功能，打造完整网站盈利体系，无论是企业还是站长都能实现网站盈利", "link": { "url": "https:\/\/www.zibll.com\/forums", "text": "", "target": "_blank" }, "cta_text": "查看社区", "cta_color": "", "band_color": "pink" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_ui_icon_cover_card',
                'instance' => '{ "title": "更多精彩页面演示", "subtitle": "丰富的页面风格与功能场景，全面展示 zibll 主题的设计与生态能力", "title_style": "big", "title_highlight": "页面演示", "title_highlight_color": "cg-red", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "sidebar_affix": "", "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "rgba(252,229,229,0.11)", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "pc_row": "4", "m_row": "2", "show_widget_bg": "1", "icon_radius4": "1", "cards": [ { "title": "官方演示站<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "综合性zibll演示网站", "icon": "customsvg-<svg xmlns:dc=\"http:\/\/purl.org\/dc\/elements\/1.1\/\" xmlns:cc=\"http:\/\/creativecommons.org\/ns#\" xmlns:rdf=\"http:\/\/www.w3.org\/1999\/02\/22-rdf-syntax-ns#\" xmlns:svg=\"http:\/\/www.w3.org\/2000\/svg\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" xmlns:sodipodi=\"http:\/\/sodipodi.sourceforge.net\/DTD\/sodipodi-0.dtd\" xmlns:inkscape=\"http:\/\/www.inkscape.org\/namespaces\/inkscape\" inkscape:version=\"1.0 (4035a4fb49, 2020-05-01)\" sodipodi:docname=\"ZIBLL-LOGO-smail.svg\" id=\"svg8\" width=\"80.863312\" height=\"92.399582\" viewBox=\"0 0 80.863312 92.399582\">  <metadata id=\"metadata14\">    <rdf:rdf>      <cc:work rdf:about=\"\">        <dc:format>image\/svg+xml<\/dc:format>        <dc:type rdf:resource=\"http:\/\/purl.org\/dc\/dcmitype\/StillImage\"><\/dc:type>        <dc:title><\/dc:title>      <\/cc:work>    <\/rdf:rdf>  <\/metadata>  <defs id=\"defs12\"><\/defs>  <sodipodi:namedview inkscape:current-layer=\"svg8\" inkscape:window-maximized=\"1\" inkscape:window-y=\"-6\" inkscape:window-x=\"-6\" inkscape:cy=\"46.199791\" inkscape:cx=\"150\" inkscape:zoom=\"3.6666667\" fit-margin-bottom=\"0\" fit-margin-right=\"0\" fit-margin-left=\"0\" fit-margin-top=\"0\" showgrid=\"false\" id=\"namedview10\" inkscape:window-height=\"1018\" inkscape:window-width=\"1920\" inkscape:pageshadow=\"2\" inkscape:pageopacity=\"0\" guidetolerance=\"10\" gridtolerance=\"10\" objecttolerance=\"10\" borderopacity=\"1\" bordercolor=\"#666666\" pagecolor=\"#ffffff\"><\/sodipodi:namedview>  <g id=\"Page-1\" stroke=\"none\" stroke-width=\"5.05424\" transform=\"scale(0.19785394)\">    <path id=\"path2\" d=\"m 408.68862,333.8044 0.009,0.59315 0.004,0.29795 c 0.18694,71.91774 -59.58485,130.93907 -134.53126,132.29044 -0.85543,0.0154 -1.71097,0.0231 -2.56654,0.0231 H 21.034693 C 11.007191,467.00905 2.5761396,460.42672 0.14656613,451.50411 L 338.63176,215.013 c 45.60925,25.97569 68.96153,65.57282 70.05686,118.7914 z M 231.25625,0 C 278.01331,0 321.188,23.909861 344.73248,62.682399 L 0,303.53751 V 20.793975 C 0,9.3097796 9.6674353,0 21.592821,0 Z\" fill=\"#1fbc45\" style=\"stroke-width:5.05424\"><\/path>  <\/g><\/svg>", "customize_icon": "", "icon_class": "c-cyan", "link": { "url": "https:\/\/demo.zibll.com\/", "text": "", "target": "_blank" } }, { "title": "zibll官网<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "同样使用zibll主题驱动", "icon": "customsvg-<svg xmlns:dc=\"http:\/\/purl.org\/dc\/elements\/1.1\/\" xmlns:cc=\"http:\/\/creativecommons.org\/ns#\" xmlns:rdf=\"http:\/\/www.w3.org\/1999\/02\/22-rdf-syntax-ns#\" xmlns:svg=\"http:\/\/www.w3.org\/2000\/svg\" xmlns=\"http:\/\/www.w3.org\/2000\/svg\" xmlns:sodipodi=\"http:\/\/sodipodi.sourceforge.net\/DTD\/sodipodi-0.dtd\" xmlns:inkscape=\"http:\/\/www.inkscape.org\/namespaces\/inkscape\" inkscape:version=\"1.0 (4035a4fb49, 2020-05-01)\" sodipodi:docname=\"ZIBLL-LOGO-smail.svg\" id=\"svg8\" width=\"80.863312\" height=\"92.399582\" viewBox=\"0 0 80.863312 92.399582\">  <metadata id=\"metadata14\">    <rdf:rdf>      <cc:work rdf:about=\"\">        <dc:format>image\/svg+xml<\/dc:format>        <dc:type rdf:resource=\"http:\/\/purl.org\/dc\/dcmitype\/StillImage\"><\/dc:type>        <dc:title><\/dc:title>      <\/cc:work>    <\/rdf:rdf>  <\/metadata>  <defs id=\"defs12\"><\/defs>  <sodipodi:namedview inkscape:current-layer=\"svg8\" inkscape:window-maximized=\"1\" inkscape:window-y=\"-6\" inkscape:window-x=\"-6\" inkscape:cy=\"46.199791\" inkscape:cx=\"150\" inkscape:zoom=\"3.6666667\" fit-margin-bottom=\"0\" fit-margin-right=\"0\" fit-margin-left=\"0\" fit-margin-top=\"0\" showgrid=\"false\" id=\"namedview10\" inkscape:window-height=\"1018\" inkscape:window-width=\"1920\" inkscape:pageshadow=\"2\" inkscape:pageopacity=\"0\" guidetolerance=\"10\" gridtolerance=\"10\" objecttolerance=\"10\" borderopacity=\"1\" bordercolor=\"#666666\" pagecolor=\"#ffffff\"><\/sodipodi:namedview>  <g id=\"Page-1\" stroke=\"none\" stroke-width=\"5.05424\" transform=\"scale(0.19785394)\">    <path id=\"path2\" d=\"m 408.68862,333.8044 0.009,0.59315 0.004,0.29795 c 0.18694,71.91774 -59.58485,130.93907 -134.53126,132.29044 -0.85543,0.0154 -1.71097,0.0231 -2.56654,0.0231 H 21.034693 C 11.007191,467.00905 2.5761396,460.42672 0.14656613,451.50411 L 338.63176,215.013 c 45.60925,25.97569 68.96153,65.57282 70.05686,118.7914 z M 231.25625,0 C 278.01331,0 321.188,23.909861 344.73248,62.682399 L 0,303.53751 V 20.793975 C 0,9.3097796 9.6674353,0 21.592821,0 Z\" fill=\"#1fbc45\" style=\"stroke-width:5.05424\"><\/path>  <\/g><\/svg>", "customize_icon": "", "icon_class": "c-cyan", "link": { "url": "https:\/\/www.zibll.com\/", "text": "", "target": "_blank" } }, { "title": "商城首页<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "实物商城首页演示", "icon": "fa fa-shopping-cart", "customize_icon": "", "icon_class": "c-red", "link": { "url": "https:\/\/demo.zibll.com\/", "text": "", "target": "_blank" } }, { "title": "知识付费<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "付费阅读、付费下载等", "icon": "fa fa-paypal", "customize_icon": "", "icon_class": "c-yellow", "link": { "url": "https:\/\/demo.zibll.com\/%e5%ad%90%e6%af%94%e4%b8%bb%e9%a2%98", "text": "", "target": "_blank" } }, { "title": "社区论坛<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "社区论坛、圈子、板块", "icon": "fa fa-comments", "customize_icon": "", "icon_class": "c-green-2", "link": { "url": "https:\/\/www.zibll.com\/forums", "text": "", "target": "_blank" } }, { "title": "聚合页面<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "纯模块布局页面演示", "icon": "fa fa-newspaper-o", "customize_icon": "", "icon_class": "c-purple-2", "link": { "url": "https:\/\/www.zibll.com\/%e9%a1%b5%e9%9d%a2%e5%b8%83%e5%b1%80%e6%bc%94%e7%a4%ba", "text": "", "target": "_blank" } }, { "title": "网址导航<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "有情链接、链接列表", "icon": "fa fa-link", "customize_icon": "", "icon_class": "c-cyan", "link": { "url": "https:\/\/www.zibll.com\/%e7%bd%91%e5%9d%80%e5%af%bc%e8%88%aa%e7%a4%ba%e4%be%8b%e2%91%a1", "text": "", "target": "_blank" } }, { "title": "文档导航<span class=\"fa fa-angle-right em12 ml10\"><\/span>", "desc": "适合做文档的页面", "icon": "fa fa-list", "customize_icon": "", "icon_class": "c-blue", "link": { "url": "https:\/\/www.zibll.com\/%e4%b8%bb%e9%a2%98%e6%96%87%e6%a1%a3", "text": "", "target": "_blank" } } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_reviews',
                'instance' => '{ "title": "超10000+用户高度好评", "subtitle": "众多 WordPress 建站用户的共同选择，已有超千位站长推荐使用 zibll 主题", "title_style": "big", "title_highlight": "高度好评", "title_highlight_color": "cg-red", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "desktop_per_view": "4", "mobile_per_view": "1.5", "swiper_autoplay": "4", "swiper_loop": "1", "reviews": [ { "name": "站长小李", "role": "资深下载站站长", "avatar": "", "rating": "5", "url": "", "content": "使用 Zibll 接建的资源站，会员和付费功能非常完善，大大提升了我的变现效率，强烈推荐！" }, { "name": "小美的博客", "role": "内容创作者", "avatar": "", "rating": "5", "url": "", "content": "主题设计干净美观，模块化布局让我可以自由组合，打造独一无二的博客网站！" }, { "name": "老张", "role": "社区论坛站长", "avatar": "", "rating": "5", "url": "", "content": "社区论坛系统太强大了，用户体验很好、互动性强，运营起来非常轻松！" }, { "name": "科技派", "role": "技术博主", "avatar": "", "rating": "5", "url": "", "content": "更新频率非常高，每次都能解决我开发日常常用需求，技术支持也很及时！" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_stats',
                'instance' => '{ "title": "", "subtitle": "", "title_style": "", "title_highlight": "", "title_highlight_color": "focus-color", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "animation_in": "slideup", "animation_repeat": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "https:\/\/picsum.photos\/1000\/500", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "fixed", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "fixed", "background-size": "cover", "background-origin": "" } }, "layout": "split", "intro_badge_icon": "fa fa-shield", "intro_badge_text": "值得信赖的选择", "intro_title": "超 3,000+ 站长的共同选择", "intro_title_highlight": "3,000+", "intro_subtitle": "Zibll 主题以强大的功能、稳定的性能和优质的服务，赢得了广大站长的信赖。\r\n我们持续迭代更新，只为您提供更好的产品和服务体验。", "intro_trust_badges": [ { "icon": "fa fa-shield", "title": "持续迭代更新", "desc": "定期更新，功能不断完善" }, { "icon": "fa fa-heart", "title": "专业技术支持", "desc": "快速响应解决问题" } ], "split_card_columns": "4", "card_style": "plain", "number_color": "pink", "mobile_columns": "2", "stats": [ { "label": "活跃用户", "color": "pink", "icon": "fa fa-users", "number": "3000", "unit": "+", "prefix": "" }, { "label": "网站使用", "color": "purple", "icon": "fa fa-window-restore", "number": "10000", "unit": "+", "prefix": "" }, { "label": "好评率", "color": "orange", "icon": "fa fa-thumbs-up", "number": "99", "unit": "%", "prefix": "" }, { "label": "用户评分", "color": "blue", "icon": "fa fa-star", "number": "4.9", "unit": "\/5", "prefix": "" } ], "show_id_type": "" }',
            ),
            array(
                'id'       => 'zib_widget_faq',
                'instance' => '{ "title": "常见问题解答", "subtitle": "我们提供丰富的文档教程与交流社区，帮助您快速解决配置与使用中的各种问题", "title_style": "big", "title_highlight": "常见问题", "title_highlight_color": "cg-red", "title_link": { "url": "", "text": "", "target": "" }, "title_link2": { "url": "", "text": "", "target": "" }, "title_link3": { "url": "", "text": "", "target": "" }, "title_link_style": { "color_1": "jb-blue", "color_2": "jb-yellow", "color_3": "jb-green" }, "show_type": "all", "show_ids": "", "layout_pin_pc": { "top": "60", "bottom": "40" }, "layout_pin_m": { "top": "40", "bottom": "20" }, "layout_bg": { "img_white": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" }, "img_dark": { "background-color": "", "background-image": "", "background-position": "", "background-repeat": "no-repeat", "background-attachment": "scroll", "background-size": "cover", "background-origin": "" } }, "obs_animation": "slideup", "animation_repeat": "", "search_s": "1", "search_placeholder": "搜索问题，例如：安装、授权、更新…", "search_btn_text": "搜索", "faqs": [ { "question": "购买后可以使用在多个网站吗?", "answer": "默认授权 1 个域名，可联系客服扩展授权数。" }, { "question": "购买后如何获取主题?", "answer": "支付完成后可在用户中心立即下载，永久免费。" }, { "question": "是否提供安装服务?", "answer": "提供有偿安装与配置服务，详情联系客服。" }, { "question": "是否支持免费更新?", "answer": "终身免费更新，购买后所有版本都可下载。" }, { "question": "是否支持退款?", "answer": "虚拟商品默认不支持退款，详情请阅读购买说明。" }, { "question": "遇到问题如何获得支持?", "answer": "可在官方论坛提问，或联系客服 1 对 1 协助。" }, { "question": "主题是否支持二次开发?", "answer": "完全开放代码，支持二次开发，提供详细的开发文档。" }, { "question": "主题是否兼容最新的 WordPress 版本?", "answer": "持续跟进 WordPress 最新版本，保证良好兼容。" }, { "question": "是否有详细的使用文档?", "answer": "官方提供完整中文文档与视频教程。" } ], "doc_title": "教程与文档", "doc_desc": "从入门到进阶，手把手带您玩转 Zibll", "doc_icon": "fa fa-book", "doc_links": [ { "title": "新手指南", "icon": "fa fa-graduation-cap", "desc": "安装与基础配置", "url": "#", "tag": "热门", "btn_text": "" }, { "title": "主题设置", "icon": "fa fa-cog", "desc": "后台选项详解", "url": "#", "tag": "", "btn_text": "" }, { "title": "功能手册", "icon": "fa fa-list-alt", "desc": "各模块使用说明", "url": "#", "tag": "进阶", "btn_text": "" }, { "title": "开发文档", "icon": "fa fa-code", "desc": "钩子与二次开发", "url": "#", "tag": "", "btn_text": "" } ], "help_title": "更多帮助与支持", "help_subtitle": "工单与在线客服，随时为您解答", "help_icon": "fa fa-mortar-board", "help_btn_text": "联系客服", "help_btn_url": "# ", "show_id_type": "" }',
            ),
        ),
    );

    return apply_filters('widget_import_templates', $templates);
}
