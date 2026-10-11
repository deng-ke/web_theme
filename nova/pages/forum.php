<?php
/*
 * Project       : Nova主题
 * Description   : 论坛（板块首页 + 发帖/编辑）
 */

if (!defined('ABSPATH')) exit;

if (!nova_get_setting('forum_s', '1')) {
    wp_safe_redirect(home_url());
    exit;
}

$action = get_query_var('nova_tab');
if (!$action && isset($_GET['tab'])) {
    $action = sanitize_text_field(wp_unslash($_GET['tab'])); // phpcs:ignore
}
$post_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

get_header();
?>
<div class="nova-container">
<?php
if ('edit' === $action) {
    //发帖/编辑页
    nova_user_page_permission();
    $plate_id = isset($_GET['plate']) ? (int) $_GET['plate'] : 0;
    $editing = null;
    if ($post_id) {
        $editing = get_post($post_id);
        $plates = wp_get_post_terms($post_id, 'forum_plate');
        $plate_id = !empty($plates) ? $plates[0]->term_id : $plate_id;
    }
    $plates = nova_bbs_get_plates();
    ?>
    <div class="nova-bbs-edit-page">
        <div class="nova-bbs-edit-form card">
            <h3 class="nova-block-title"><span><?php echo $editing ? esc_html__('编辑帖子', 'nova') : esc_html__('发布帖子', 'nova'); ?></span></h3>
            <div class="nova-form-group">
                <label><?php esc_html_e('选择板块', 'nova'); ?></label>
                <select class="nova-bbs-plate-select">
                    <?php foreach ($plates as $plate) : ?>
                        <option value="<?php echo esc_attr($plate->term_id); ?>" <?php selected($plate_id, $plate->term_id); ?>><?php echo esc_html($plate->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="nova-form-group">
                <label><?php esc_html_e('帖子标题', 'nova'); ?></label>
                <input type="text" class="nova-bbs-title" maxlength="80" placeholder="<?php esc_attr_e('请输入标题（2-80字）', 'nova'); ?>" value="<?php echo $editing ? esc_attr($editing->post_title) : ''; ?>">
            </div>
            <div class="nova-form-group">
                <label><?php esc_html_e('帖子内容', 'nova'); ?></label>
                <textarea class="nova-bbs-content" rows="10" placeholder="<?php esc_attr_e('请输入帖子内容（不少于5字）', 'nova'); ?>"><?php echo $editing ? esc_textarea($editing->post_content) : ''; ?></textarea>
            </div>
            <div class="nova-bbs-edit-actions">
                <button class="btn btn-primary nova-bbs-publish-btn" <?php echo $post_id ? 'data-id="' . esc_attr($post_id) . '"' : ''; ?>><?php esc_html_e('发布帖子', 'nova'); ?></button>
            </div>
        </div>
    </div>
    <?php
} else {
    echo nova_bbs_index_html(); // phpcs:ignore
}
?>
</div>
<?php
get_footer();
