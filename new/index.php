<?php
/**
 * 兜底模板：文章列表
 *
 * @package ePiDK
 */

get_header();
?>

<main class="site-main container list-page">
	<div class="page-banner">
		<h1><?php bloginfo( 'name' ); ?></h1>
		<?php $desc = get_bloginfo( 'description' ); ?>
		<?php if ( $desc ) : ?>
			<p><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
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
