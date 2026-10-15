<?php
/**
 * 模板辅助函数：图标、时间、数字格式化、菜单兜底、知识库树
 *
 * @package ePiDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 输出线性 SVG 图标
 *
 * @param string $name 图标名.
 * @param int    $size 尺寸.
 */
function epidk_icon( $name, $size = 16 ) {
	$icons = array(
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
		'moon'     => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'comment'  => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.9 8.9 0 0 1-3.8-.9L3 20l1-4.9a8.4 8.4 0 1 1 17-3.6z"/>',
		'eye'      => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
		'like'     => '<path d="M7 10v11H4a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h3zm0 0 4-7a2.4 2.4 0 0 1 2.3 3l-.7 3H19a2 2 0 0 1 2 2.4l-1.3 6A2 2 0 0 1 17.7 21H7"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'folder'   => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
		'chevron'  => '<path d="m6 9 6 6 6-6"/>',
		'arrow-up' => '<path d="M12 19V5m-7 7 7-7 7 7"/>',
		'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'    => '<path d="M18 6 6 18M6 6l12 12"/>',
		'sort'     => '<path d="m7 4 0 16m0 0-3-3m3 3 3-3M17 20V4m0 0-3 3m3-3 3 3"/>',
		'pen'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
		'list'     => '<path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
		'image'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 15-5-5L5 19"/>',
		'dot'      => '<circle cx="12" cy="12" r="4"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="icon icon-%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$icons[ $name ]
	);
}

/**
 * 人性化时间：x分钟前 / x个月前
 *
 * @param int $from 时间戳.
 */
function epidk_time_ago( $from = null ) {
	$from = $from ? (int) $from : get_the_time( 'U' );
	$diff = current_time( 'timestamp' ) - $from;

	if ( $diff < MINUTE_IN_SECONDS ) {
		return __( '刚刚', 'epidk' );
	}
	if ( $diff < HOUR_IN_SECONDS ) {
		return floor( $diff / MINUTE_IN_SECONDS ) . __( '分钟前', 'epidk' );
	}
	if ( $diff < DAY_IN_SECONDS ) {
		return floor( $diff / HOUR_IN_SECONDS ) . __( '小时前', 'epidk' );
	}
	if ( $diff < WEEK_IN_SECONDS ) {
		return floor( $diff / DAY_IN_SECONDS ) . __( '天前', 'epidk' );
	}
	if ( $diff < 30 * DAY_IN_SECONDS ) {
		return floor( $diff / WEEK_IN_SECONDS ) . __( '周前', 'epidk' );
	}
	if ( $diff < YEAR_IN_SECONDS ) {
		return floor( $diff / ( 30 * DAY_IN_SECONDS ) ) . __( '个月前', 'epidk' );
	}
	return floor( $diff / YEAR_IN_SECONDS ) . __( '年前', 'epidk' );
}

/**
 * 数字格式化：过万显示 x.xw
 *
 * @param int $num 数字.
 */
function epidk_format_num( $num ) {
	$num = (int) $num;
	if ( $num >= 10000 ) {
		return round( $num / 10000, 1 ) . 'w';
	}
	return (string) $num;
}

/**
 * 文章所属的第一个分类
 *
 * @param int $post_id 文章 ID.
 */
function epidk_primary_category( $post_id = null ) {
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) || is_wp_error( $cats ) ) {
		return null;
	}
	return $cats[0];
}

/**
 * 顶部菜单兜底：未分配菜单时显示 首页 + 知识库（分类下拉）
 */
function epidk_menu_fallback() {
	echo '<ul class="nav-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( '首页', 'epidk' ) . '</a></li>';
	echo '<li class="menu-item-has-children"><a href="#">' . esc_html__( '知识库', 'epidk' ) . '</a><ul class="sub-menu">';
	wp_list_categories(
		array(
			'title_li' => '',
			'number'   => 10,
			'orderby'  => 'count',
			'order'    => 'DESC',
		)
	);
	echo '</ul></li>';
	echo '</ul>';
}

/**
 * 文章页左侧「知识库树」：
 * 以当前文章分类的顶级分类为根，展开其子分类及子分类下的文章列表。
 */
function epidk_category_tree() {
	$current_id = get_the_ID();
	$cat        = epidk_primary_category( $current_id );
	$groups     = array();

	if ( $cat ) {
		$ancestors = array_reverse( get_ancestors( $cat->term_id, 'category' ) );
		$root_id   = $ancestors ? $ancestors[0] : $cat->term_id;

		$groups = get_categories(
			array(
				'parent'     => $root_id,
				'hide_empty' => true,
				'orderby'    => 'term_order',
			)
		);

		// 根分类本身没有子分类时，把自己作为唯一分组
		if ( empty( $groups ) ) {
			$groups = array( get_category( $root_id ) );
		}
	}

	if ( empty( $groups ) ) {
		// 文章无分类：退化为最新文章列表
		$recent = get_posts( array( 'numberposts' => 10 ) );
		if ( ! $recent ) {
			return;
		}
		echo '<div class="tree-card"><div class="tree-group is-open">';
		echo '<div class="tree-group-title">' . esc_html__( '最新文章', 'epidk' ) . '</div><ul class="tree-list">';
		foreach ( $recent as $p ) {
			$active = ( $p->ID === $current_id ) ? ' class="is-active"' : '';
			echo '<li' . $active . '><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>';
		}
		echo '</ul></div></div>';
		return;
	}

	echo '<div class="tree-card">';
	foreach ( $groups as $group ) {
		$posts = get_posts(
			array(
				'category'    => $group->term_id,
				'numberposts' => 50,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
		if ( empty( $posts ) ) {
			continue;
		}

		$has_current = wp_list_filter( $posts, array( 'ID' => $current_id ) );
		$open_class  = $has_current ? ' is-open has-active' : ' is-open';

		echo '<div class="tree-group' . esc_attr( $open_class ) . '">';
		echo '<button type="button" class="tree-group-title" aria-expanded="true">';
		echo '<span class="tree-group-name">' . esc_html( $group->name ) . '</span>';
		echo epidk_icon( 'chevron', 14 );
		echo '</button>';
		echo '<ul class="tree-list">';
		foreach ( $posts as $p ) {
			$active = ( $p->ID === $current_id ) ? ' class="is-active"' : '';
			echo '<li' . $active . '><a href="' . esc_url( get_permalink( $p ) ) . '" title="' . esc_attr( get_the_title( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>';
		}
		echo '</ul></div>';
	}
	echo '</div>';
}

/**
 * 文章统计行（评论 / 浏览 / 点赞）
 *
 * @param int  $post_id 文章 ID.
 * @param bool $like_btn 是否输出可点击点赞按钮.
 */
function epidk_post_stats( $post_id = null, $like_btn = false ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$likes   = epidk_get_likes( $post_id );
	?>
	<span class="stat" title="<?php esc_attr_e( '评论', 'epidk' ); ?>"><?php echo epidk_icon( 'comment', 14 ); ?><i><?php echo esc_html( epidk_format_num( get_comments_number( $post_id ) ) ); ?></i></span>
	<span class="stat" title="<?php esc_attr_e( '浏览', 'epidk' ); ?>"><?php echo epidk_icon( 'eye', 14 ); ?><i><?php echo esc_html( epidk_format_num( epidk_get_views( $post_id ) ) ); ?></i></span>
	<?php if ( $like_btn ) : ?>
	<button type="button" class="stat epidk-like<?php echo isset( $_COOKIE[ 'epidk_liked_' . $post_id ] ) ? ' is-liked' : ''; ?>" data-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( '点赞', 'epidk' ); ?>">
		<?php echo epidk_icon( 'like', 14 ); ?><i class="like-count"><?php echo esc_html( epidk_format_num( $likes ) ); ?></i>
	</button>
	<?php else : ?>
	<span class="stat" title="<?php esc_attr_e( '点赞', 'epidk' ); ?>"><?php echo epidk_icon( 'like', 14 ); ?><i><?php echo esc_html( epidk_format_num( $likes ) ); ?></i></span>
	<?php endif; ?>
	<?php
}
