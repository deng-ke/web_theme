<?php
/**
 * 独立页面
 *
 * @package ePiDK
 */

get_header();
?>

<main class="site-main container page-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'article-card' ); ?>>
			<h1 class="article-title"><?php the_title(); ?></h1>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( '分页：', 'epidk' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>
		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
