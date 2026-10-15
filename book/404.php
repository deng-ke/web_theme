<?php
/**
 * 404 模板。
 *
 * @package GitBook
 */

get_header();
?>

<div class="gb-404">
	<p class="gb-404-code">404</p>
	<h1 class="gb-404-title"><?php esc_html_e( '页面走丢了', 'gitbook' ); ?></h1>
	<p class="gb-404-desc"><?php esc_html_e( '你要找的页面可能已被移动或删除。可以回到首页，或使用搜索。', 'gitbook' ); ?></p>
	<div class="gb-404-actions">
		<a class="gb-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '返回首页', 'gitbook' ); ?></a>
		<?php get_search_form(); ?>
	</div>
</div>

<?php
get_footer();
