<?php
/**
 * 归档页（分类 / 标签 / 作者 / 日期）
 *
 * @package ePiDK
 */

get_header();
?>

<main class="site-main container list-page">
	<div class="page-banner">
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<p>', '</p>' ); ?>
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
