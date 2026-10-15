<?php
/**
 * ePiDK 主题入口
 *
 * @package ePiDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EPIDK_VERSION', '1.0.0' );
define( 'EPIDK_DIR', trailingslashit( get_template_directory() ) );
define( 'EPIDK_URI', trailingslashit( get_template_directory_uri() ) );

require_once EPIDK_DIR . 'inc/template-tags.php';
require_once EPIDK_DIR . 'inc/toc.php';
require_once EPIDK_DIR . 'inc/meta-actions.php';
require_once EPIDK_DIR . 'inc/customizer.php';

if ( ! function_exists( 'epidk_setup' ) ) :
	/**
	 * 主题基础能力注册
	 */
	function epidk_setup() {
		load_theme_textdomain( 'epidk', EPIDK_DIR . 'languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 240,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		add_image_size( 'epidk-card', 360, 240, true );

		register_nav_menus(
			array(
				'primary' => __( '顶部主导航', 'epidk' ),
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'epidk_setup' );

/**
 * 加载前端资源
 */
function epidk_scripts() {
	wp_enqueue_style( 'epidk-main', EPIDK_URI . 'assets/css/main.css', array(), EPIDK_VERSION );
	wp_enqueue_script( 'epidk-main', EPIDK_URI . 'assets/js/main.js', array(), EPIDK_VERSION, true );
	wp_localize_script(
		'epidk-main',
		'epidk_ajax',
		array(
			'url'   => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'epidk_like' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'epidk_scripts' );

/**
 * 在 head 最前面写入主题模式，避免页面闪烁
 */
function epidk_theme_mode_inline_script() {
	?>
<script>(function(){try{var t=localStorage.getItem('epidk-theme');if(!t){t=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}document.documentElement.setAttribute('data-theme',t);}catch(e){document.documentElement.setAttribute('data-theme','light');}})();</script>
	<?php
}
add_action( 'wp_head', 'epidk_theme_mode_inline_script', 1 );

/**
 * body 附加类
 */
function epidk_body_classes( $classes ) {
	if ( ! get_theme_mod( 'epidk_auto_heading_number', true ) ) {
		$classes[] = 'no-heading-numbers';
	}
	if ( is_front_page() ) {
		$classes[] = 'has-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'epidk_body_classes' );

/**
 * 摘要长度
 */
function epidk_excerpt_length( $length ) {
	return 60;
}
add_filter( 'excerpt_length', 'epidk_excerpt_length' );

function epidk_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'epidk_excerpt_more' );
