<?php
/**
 * GitBook 主题函数定义
 *
 * @package GitBook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GITBOOK_VERSION', '1.0.0' );

if ( ! function_exists( 'gitbook_setup' ) ) :
	/**
	 * 主题基础设置。
	 */
	function gitbook_setup() {
		load_theme_textdomain( 'gitbook', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'custom-logo', array(
			'height'      => 48,
			'width'       => 160,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		) );

		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );

		register_nav_menus( array(
			'primary' => esc_html__( '侧边栏主菜单', 'gitbook' ),
			'footer'  => esc_html__( '底部菜单', 'gitbook' ),
		) );
	}
endif;
add_action( 'after_setup_theme', 'gitbook_setup' );

/**
 * 内容宽度。
 */
function gitbook_content_width() {
	$GLOBALS['content_width'] = 860;
}
add_action( 'after_setup_theme', 'gitbook_content_width', 0 );

/**
 * 加载样式与脚本。
 */
function gitbook_scripts() {
	wp_enqueue_style( 'gitbook-style', get_stylesheet_uri(), array(), GITBOOK_VERSION );
	wp_enqueue_style( 'gitbook-main', get_template_directory_uri() . '/assets/css/main.css', array( 'gitbook-style' ), GITBOOK_VERSION );

	wp_enqueue_script( 'gitbook-main', get_template_directory_uri() . '/assets/js/main.js', array(), GITBOOK_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'gitbook_scripts' );

/**
 * 小工具区域。
 */
function gitbook_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( '侧边栏小工具', 'gitbook' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( '显示在左侧导航底部。', 'gitbook' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'gitbook_widgets_init' );

/**
 * body class 补充。
 */
function gitbook_body_classes( $classes ) {
	if ( is_singular() ) {
		$classes[] = 'is-doc';
	}
	return $classes;
}
add_filter( 'body_class', 'gitbook_body_classes' );

/**
 * 文章元信息：作者、日期、阅读时长。
 */
function gitbook_post_meta() {
	$reading_time = gitbook_reading_time();
	echo '<span class="meta-item"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Zm1-13h-2v6l5.25 3.15 1-1.65-4.25-2.5Z"/></svg>' . esc_html( get_the_date() ) . '</span>';
	if ( $reading_time > 0 ) {
		echo '<span class="meta-item"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4a4 4 0 0 1 4 4 4 4 0 0 1-4 4 4 4 0 0 1-4-4 4 4 0 0 1 4-4Zm0 10c4.42 0 8 2 8 4v2H4v-2c0-2 3.58-4 8-4Z"/></svg>' . esc_html( get_the_author() ) . '</span>';
	}
}

/**
 * 估算阅读时长（分钟）。
 */
function gitbook_reading_time() {
	$content = get_post_field( 'post_content', get_the_ID() );
	$words   = mb_strlen( wp_strip_all_tags( $content ), 'UTF-8' );

	return max( 1, (int) ceil( $words / 400 ) );
}

/**
 * 页面层级树（用于左侧章节导航的自动生成）。
 *
 * @return array 页面树，含 children。
 */
function gitbook_page_tree() {
	$pages = get_pages( array(
		'parent'     => 0,
		'sort_column' => 'menu_order,post_title',
	) );

	$tree = array();
	foreach ( $pages as $page ) {
		$node = array(
			'id'        => $page->ID,
			'title'     => get_the_title( $page ),
			'url'       => get_permalink( $page ),
			'children'  => array(),
			'is_active' => gitbook_is_active_page( $page->ID ),
		);

		$children = get_pages( array(
			'parent'      => $page->ID,
			'sort_column' => 'menu_order,post_title',
		) );
		foreach ( $children as $child ) {
			$node['children'][] = array(
				'id'        => $child->ID,
				'title'     => get_the_title( $child ),
				'url'       => get_permalink( $child ),
				'is_active' => gitbook_is_active_page( $child->ID ),
			);
		}
		$tree[] = $node;
	}

	return $tree;
}

/**
 * 判断页面是否处于当前激活链上（含祖先）。
 */
function gitbook_is_active_page( $page_id ) {
	if ( is_page( $page_id ) ) {
		return true;
	}
	$current = get_queried_object_id();
	$ancestors = get_post_ancestors( $current );

	return in_array( $page_id, (array) $ancestors, true );
}

/**
 * 上下篇导航：页面按层级兄弟、文章按相邻文章。
 */
function gitbook_prev_next() {
	$output = array( 'prev' => false, 'next' => false );

	if ( is_page() ) {
		$current = get_queried_object_id();

		// 取当前层级兄弟页面；顶级页面则以所有顶级页面为兄弟。
		$siblings = get_pages( array(
			'parent'      => wp_get_post_parent_id( $current ),
			'sort_column' => 'menu_order,post_title',
		) );

		$ids = wp_list_pluck( $siblings, 'ID' );
		$pos = array_search( $current, $ids, true );

		if ( false !== $pos ) {
			if ( isset( $ids[ $pos - 1 ] ) ) {
				$output['prev'] = array( 'title' => get_the_title( $ids[ $pos - 1 ] ), 'url' => get_permalink( $ids[ $pos - 1 ] ) );
			}
			if ( isset( $ids[ $pos + 1 ] ) ) {
				$output['next'] = array( 'title' => get_the_title( $ids[ $pos + 1 ] ), 'url' => get_permalink( $ids[ $pos + 1 ] ) );
			}
		}
	} else {
		$prev = get_previous_post();
		$next = get_next_post();

		if ( $prev ) {
			$output['prev'] = array( 'title' => get_the_title( $prev ), 'url' => get_permalink( $prev ) );
		}
		if ( $next ) {
			$output['next'] = array( 'title' => get_the_title( $next ), 'url' => get_permalink( $next ) );
		}
	}

	return $output;
}

/**
 * 摘要长度。
 */
function gitbook_excerpt_length( $length ) {
	return is_admin() ? $length : 28;
}
add_filter( 'excerpt_length', 'gitbook_excerpt_length' );

/**
 * 摘要后缀。
 */
function gitbook_excerpt_more( $more ) {
	return is_admin() ? $more : '…';
}
add_filter( 'excerpt_more', 'gitbook_excerpt_more' );

/**
 * 自定义器：主题强调色。
 */
function gitbook_customize_register( $wp_customize ) {
	$wp_customize->add_setting( 'gitbook_accent', array(
		'default'           => '#6366f1',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'gitbook_accent', array(
		'label'   => esc_html__( '主题强调色', 'gitbook' ),
		'section' => 'colors',
	) ) );
}
add_action( 'customize_register', 'gitbook_customize_register' );

/**
 * 输出强调色 CSS 变量。
 */
function gitbook_accent_css() {
	$accent = get_theme_mod( 'gitbook_accent', '#6366f1' );
	echo '<style id="gitbook-accent">:root{--gb-accent:' . esc_attr( $accent ) . ';}</style>';
}
add_action( 'wp_head', 'gitbook_accent_css' );
