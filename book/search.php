<?php
/**
 * 搜索结果模板。
 *
 * @package GitBook
 */

get_header();
?>

<header class="gb-archive-head">
	<h1 class="gb-archive-title">
		<?php
		/* translators: %s: 搜索关键词。 */
		printf( esc_html__( '“%s” 的搜索结果', 'gitbook' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
		?>
	</h1>
	<p class="gb-archive-desc">
		<?php
		global $wp_query;
		/* translators: %d: 结果数量。 */
		printf( esc_html( _n( '共找到 %d 个结果', '共找到 %d 个结果', (int) $wp_query->found_posts, 'gitbook' ) ), (int) $wp_query->found_posts );
		?>
	</p>
</header>

<?php if ( have_posts() ) : ?>
	<div class="gb-post-list">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<a class="gb-post-card" href="<?php the_permalink(); ?>">
				<span class="gb-post-date"><?php echo esc_html( get_the_date() ); ?></span>
				<span class="gb-post-title"><?php the_title(); ?></span>
				<span class="gb-post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></span>
			</a>
			<?php
		endwhile;
		?>
	</div>

	<div class="gb-pagination">
		<?php
		the_posts_pagination( array(
			'mid_size'  => 2,
			'prev_text' => esc_html__( '‹ 上一页', 'gitbook' ),
			'next_text' => esc_html__( '下一页 ›', 'gitbook' ),
		) );
		?>
	</div>
<?php else : ?>
	<p class="gb-empty"><?php esc_html_e( '未找到相关内容，换个关键词试试。', 'gitbook' ); ?></p>
<?php endif; ?>

<?php
get_footer();
