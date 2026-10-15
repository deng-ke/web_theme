<?php
/**
 * 单篇文章模板：文档详情页。
 *
 * @package GitBook
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'gb-article' ); ?>>
		<header class="gb-article-header">
			<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
				<figure class="gb-article-thumb"><?php the_post_thumbnail( 'full' ); ?></figure>
			<?php endif; ?>

			<h1 class="gb-article-title"><?php the_title(); ?></h1>

			<div class="gb-article-meta">
				<?php gitbook_post_meta(); ?>
				<span class="meta-item"><?php echo esc_html( gitbook_reading_time() ); ?><?php esc_html_e( ' 分钟阅读', 'gitbook' ); ?></span>
			</div>
		</header>

		<div class="gb-article-body entry-content" data-toc-root>
			<?php
			the_content(
				sprintf(
					wp_kses( /* translators: %s: 文章标题。 */ __( '继续阅读<span class="screen-reader-text">"%s"</span>', 'gitbook' ), array( 'span' => array( 'class' => array() ) ) ),
					wp_kses_post( get_the_title() )
				)
			);

			wp_link_pages( array(
				'before' => '<div class="gb-page-links">' . esc_html__( '分页：', 'gitbook' ),
				'after'  => '</div>',
			) );
			?>
		</div>

		<footer class="gb-article-footer">
			<?php
			$tags = get_the_tags();
			if ( $tags ) :
				?>
				<div class="gb-tags">
					<?php foreach ( $tags as $tag ) : ?>
						<a class="gb-tag" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</footer>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;

get_footer();
