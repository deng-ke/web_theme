<?php
/**
 * 404 页面
 *
 * @package ePiDK
 */

get_header();
?>

<main class="site-main container">
	<div class="not-found article-card">
		<div class="not-found-code">404</div>
		<p><?php esc_html_e( '页面走丢了，试试搜索或返回首页吧。', 'epidk' ); ?></p>
		<form class="not-found-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php esc_attr_e( '搜索内容', 'epidk' ); ?>">
			<button type="submit"><?php esc_html_e( '搜索', 'epidk' ); ?></button>
		</form>
		<a class="btn-home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '返回首页', 'epidk' ); ?></a>
	</div>
</main>

<?php
get_footer();
