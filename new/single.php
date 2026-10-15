<?php
/**
 * 文章页：左侧知识库树 + 正文 + 右侧目录
 *
 * @package ePiDK
 */

get_header();
?>

<main class="site-main single-main container-wide">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<!-- 左栏：知识库树 -->
		<aside class="single-left" aria-label="<?php esc_attr_e( '知识库导航', 'epidk' ); ?>">
			<div class="sticky-box">
				<?php epidk_category_tree(); ?>
			</div>
		</aside>

		<!-- 中栏：正文 -->
		<div class="single-center">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'article-card' ); ?>>

				<h1 class="article-title"><?php the_title(); ?></h1>

				<div class="article-meta">
					<span class="article-author">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
						<span class="author-info">
							<b><?php the_author(); ?></b>
							<small><?php echo esc_html( epidk_time_ago( get_the_modified_time( 'U' ) ) ); ?><?php esc_html_e( '更新', 'epidk' ); ?></small>
						</span>
					</span>
					<span class="article-stats"><?php epidk_post_stats( get_the_ID(), true ); ?></span>
				</div>

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

				<?php
				$tags = get_the_tags();
				if ( $tags ) :
					?>
					<div class="article-tags">
						<?php foreach ( $tags as $tag ) : ?>
							<a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>"># <?php echo esc_html( $tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="article-actions">
					<button type="button" class="btn-like epidk-like<?php echo isset( $_COOKIE[ 'epidk_liked_' . get_the_ID() ] ) ? ' is-liked' : ''; ?>" data-id="<?php the_ID(); ?>">
						<?php echo epidk_icon( 'like', 18 ); ?>
						<span><?php esc_html_e( '点赞', 'epidk' ); ?></span>
						<i class="like-count"><?php echo esc_html( epidk_format_num( epidk_get_likes() ) ); ?></i>
					</button>
				</div>

				<nav class="post-nav">
					<div class="post-nav-item prev">
						<?php previous_post_link( '%link', '‹ ' . esc_html__( '上一篇', 'epidk' ) . ' %title' ); ?>
					</div>
					<div class="post-nav-item next">
						<?php next_post_link( '%link', esc_html__( '下一篇', 'epidk' ) . ' %title ›' ); ?>
					</div>
				</nav>

			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>

		<!-- 右栏：目录 -->
		<aside class="single-right" aria-label="<?php esc_attr_e( '文章目录', 'epidk' ); ?>">
			<div class="sticky-box">
				<?php epidk_render_toc_card(); ?>
			</div>
		</aside>

	<?php endwhile; ?>

</main>

<?php
get_footer();
