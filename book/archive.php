<?php
/**
 * 归档模板：分类 / 标签 / 作者 / 日期。
 *
 * @package GitBook
 */

get_header();
?>

<header class="gb-archive-head">
	<?php
	the_archive_title( '<h1 class="gb-archive-title">', '</h1>' );
	the_archive_description( '<div class="gb-archive-desc">', '</div>' );
	?>
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
	<p class="gb-empty"><?php esc_html_e( '暂无内容。', 'gitbook' ); ?></p>
<?php endif; ?>

<?php
get_footer();
