<?php
/**
 * 搜索结果页
 *
 * @package ePiDK
 */

get_header();
?>

<main class="site-main container list-page">
	<div class="page-banner">
		<h1>
			<?php
			/* translators: %s: 搜索关键词 */
			printf( esc_html__( '搜索：%s', 'epidk' ), '<span class="search-keyword">' . esc_html( get_search_query() ) . '</span>' );
			?>
		</h1>
		<p>
			<?php
			/* translators: %s: 结果数量 */
			printf( esc_html__( '共找到 %s 条结果', 'epidk' ), '<b>' . (int) $wp_query->found_posts . '</b>' );
			?>
		</p>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/post-card' );
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'prev_text' => '‹',
				'next_text' => '›',
			)
		);
		?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/none' ); ?>
	<?php endif; ?>
</main>

<?php
get_footer();
