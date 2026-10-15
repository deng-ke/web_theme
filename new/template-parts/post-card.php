<?php
/**
 * 文章卡片（主页 / 归档 / 搜索结果通用）
 *
 * @package ePiDK
 */

$cat = epidk_primary_category();
?>

<article <?php post_class( 'post-card' ); ?>>
	<a class="post-thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'epidk-card', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="thumb-placeholder"><?php echo epidk_icon( 'image', 30 ); ?></span>
		<?php endif; ?>
	</a>

	<div class="post-body">
		<h3 class="post-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p class="post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 55, '…' ) ); ?></p>

		<div class="post-meta">
			<?php if ( $cat ) : ?>
				<a class="post-cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo epidk_icon( 'folder', 12 ); ?><?php echo esc_html( $cat->name ); ?></a>
			<?php endif; ?>
			<span class="post-author"><?php echo get_avatar( get_the_author_meta( 'ID' ), 20 ); ?><?php the_author(); ?></span>
			<span class="post-time"><?php echo epidk_icon( 'clock', 12 ); ?><?php echo esc_html( epidk_time_ago() ); ?></span>
			<span class="post-stats"><?php epidk_post_stats( get_the_ID() ); ?></span>
		</div>
	</div>
</article>
