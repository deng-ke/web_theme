<?php
/**
 * 主页：Hero + 图标卡片 + 最新发布列表
 *
 * @package ePiDK
 */

get_header();

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/icon-cards' );

// 排序与分页
$sort  = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'date';
$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ), isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );

$args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => (int) get_option( 'posts_per_page', 10 ),
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
);

switch ( $sort ) {
	case 'views':
		$args['meta_key'] = 'epidk_views';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
		break;
	case 'likes':
		$args['meta_key'] = 'epidk_likes';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
		break;
	case 'comments':
		$args['orderby'] = 'comment_count';
		$args['order']   = 'DESC';
		break;
	default:
		$sort           = 'date';
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
}

$epidk_query = new WP_Query( $args );
$total       = (int) wp_count_posts( 'post' )->publish;

$sort_tabs = array(
	'date'     => __( '更新', 'epidk' ),
	'views'    => __( '浏览', 'epidk' ),
	'likes'    => __( '点赞', 'epidk' ),
	'comments' => __( '评论', 'epidk' ),
);
?>

<main class="site-main home-main container">

	<div class="list-head">
		<div class="list-head-top">
			<span class="badge-new"><?php esc_html_e( '最新发布', 'epidk' ); ?></span>
			<span class="list-count"><?php echo esc_html( $total ); ?></span>
		</div>
		<div class="list-head-sort">
			<span class="sort-label"><?php echo epidk_icon( 'sort', 14 ); ?><?php esc_html_e( '排序', 'epidk' ); ?></span>
			<?php foreach ( $sort_tabs as $key => $label ) : ?>
				<a class="sort-tab<?php echo $sort === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'sort' => $key, 'paged' => 1 ), home_url( '/' ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( $epidk_query->have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( $epidk_query->have_posts() ) :
				$epidk_query->the_post();
				get_template_part( 'template-parts/post-card' );
			endwhile;
			?>
		</div>

		<?php
		$pagination = paginate_links(
			array(
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'current'   => $paged,
				'total'     => $epidk_query->max_num_pages,
				'prev_text' => '‹',
				'next_text' => '›',
				'type'      => 'list',
			)
		);
		if ( $pagination ) {
			echo '<nav class="pagination">' . $pagination . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>

	<?php else : ?>
		<?php get_template_part( 'template-parts/none' ); ?>
	<?php endif; ?>

</main>

<?php
wp_reset_postdata();
get_footer();
