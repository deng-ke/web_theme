<?php
/**
 * 页面模板：与文章页共用文档样式。
 *
 * @package GitBook
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'gb-article' ); ?>>
		<header class="gb-article-header">
			<h1 class="gb-article-title"><?php the_title(); ?></h1>
		</header>

		<div class="gb-article-body entry-content" data-toc-root>
			<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="gb-page-links">' . esc_html__( '分页：', 'gitbook' ),
				'after'  => '</div>',
			) );
			?>
		</div>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;

get_footer();
