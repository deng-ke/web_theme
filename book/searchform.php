<?php
/**
 * 搜索表单。
 *
 * @package GitBook
 */

$gb_unique_id = wp_unique_id( 'gb-search-' );
?>
<form role="search" method="get" class="gb-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $gb_unique_id ); ?>"><?php esc_html_e( '搜索', 'gitbook' ); ?></label>
	<svg class="gb-search-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3a6.5 6.5 0 1 0 3.84 11.65l4.9 4.9 1.42-1.41-4.9-4.9A6.5 6.5 0 0 0 9.5 3Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z"/></svg>
	<input type="search" id="<?php echo esc_attr( $gb_unique_id ); ?>" class="gb-search-input" placeholder="<?php esc_attr_e( '搜索文档…', 'gitbook' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<kbd class="gb-search-kbd" aria-hidden="true">/</kbd>
</form>
