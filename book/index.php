<?php
/**
 * 首页模板：文档卡片列表。
 *
 * @package GitBook
 */

get_header();

$tree = gitbook_page_tree();
?>

<div class="gb-archive-head">
	<h1 class="gb-archive-title"><?php bloginfo( 'name' ); ?></h1>
	<?php if ( get_bloginfo( 'description' ) ) : ?>
		<p class="gb-archive-desc"><?php bloginfo( 'description' ); ?></p>
	<?php endif; ?>
</div>

<?php if ( ! empty( $tree ) ) : ?>
	<h2 class="gb-section-title"><?php esc_html_e( '章节', 'gitbook' ); ?></h2>
	<div class="gb-card-grid">
		<?php foreach ( $tree as $node ) : ?>
			<a class="gb-card" href="<?php echo esc_url( $node['url'] ); ?>">
				<span class="gb-card-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24"><path d="M4 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 4v2h12V7H6Zm0 4v2h12v-2H6Zm0 4v2h8v-2H6Z"/></svg>
				</span>
				<span class="gb-card-body">
					<span class="gb-card-title"><?php echo esc_html( $node['title'] ); ?></span>
					<?php if ( ! empty( $node['children'] ) ) : ?>
						<span class="gb-card-sub"><?php echo esc_html( sprintf( /* translators: %d: 子页面数量。 */ _n( '%d 个子章节', '%d 个子章节', count( $node['children'] ), 'gitbook' ), count( $node['children'] ) ) ); ?></span>
					<?php endif; ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( have_posts() ) : ?>
	<h2 class="gb-section-title"><?php esc_html_e( '最新文章', 'gitbook' ); ?></h2>
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
<?php endif; ?>

<?php
get_footer();
