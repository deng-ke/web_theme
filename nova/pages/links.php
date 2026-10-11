<?php
/*
 * Project       : Nova主题
 * Description   : 友情链接页（使用 WP 自带链接管理器）
 */

if (!defined('ABSPATH')) exit;

get_header();

$bookmarks = get_bookmarks(array('orderby' => 'rating', 'order' => 'DESC'));
?>
<div class="nova-container">
    <div class="nova-links-page">
        <?php echo nova_page_header_html(get_the_title() ? get_the_title() : __('友情链接', 'nova'), __('欢迎与本站交换友情链接', 'nova')); // phpcs:ignore ?>
        <?php if (empty($bookmarks)) : ?>
            <?php echo nova_empty_box(__('暂无友链', 'nova'), __('站长还未添加友情链接', 'nova')); // phpcs:ignore ?>
        <?php else : ?>
            <div class="nova-links-grid">
                <?php foreach ($bookmarks as $bookmark) : ?>
                    <a class="nova-link-item card" href="<?php echo esc_url($bookmark->link_url); ?>" target="_blank" rel="noopener">
                        <?php if ($bookmark->link_image) : ?>
                            <img class="nova-link-avatar" src="<?php echo esc_url($bookmark->link_image); ?>" alt="<?php echo esc_attr($bookmark->link_name); ?>">
                        <?php else : ?>
                            <div class="nova-link-avatar nova-link-avatar-text"><?php echo esc_html(mb_substr($bookmark->link_name, 0, 1)); ?></div>
                        <?php endif; ?>
                        <div class="nova-link-info">
                            <b><?php echo esc_html($bookmark->link_name); ?></b>
                            <?php if ($bookmark->link_description) : ?>
                                <span class="muted-2-color"><?php echo esc_html($bookmark->link_description); ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
get_footer();
