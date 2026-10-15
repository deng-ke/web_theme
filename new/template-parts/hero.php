<?php
/**
 * 主页 Hero：背景大图 + 标题 + 搜索框
 *
 * @package ePiDK
 */

$hero_bg    = get_theme_mod( 'epidk_hero_bg' );
$hero_bg    = $hero_bg ? $hero_bg : EPIDK_URI . 'assets/img/hero.jpg';
$title      = get_theme_mod( 'epidk_hero_title', '更优雅的WordPress主题' );
$subtitle   = get_theme_mod( 'epidk_hero_subtitle', '这是导航栏幻灯片多功能组件，可在后台进行配置' );
$ph         = get_theme_mod( 'epidk_hero_search_placeholder', '开启精彩搜索' );
$tags_raw   = get_theme_mod( 'epidk_hero_tags', '&type=post,' . gmdate( 'Y' ) );
$hero_tags  = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
?>

<section class="hero" style="background-image:url('<?php echo esc_url( $hero_bg ); ?>')">
	<div class="hero-mask"></div>
	<div class="hero-content container">
		<h1 class="hero-title"><?php echo esc_html( $title ); ?></h1>
		<p class="hero-subtitle"><?php echo esc_html( $subtitle ); ?></p>

		<form class="hero-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php echo esc_attr( $ph ); ?>" autocomplete="off">
			<button type="submit" aria-label="<?php esc_attr_e( '搜索', 'epidk' ); ?>"><?php echo epidk_icon( 'search', 18 ); ?></button>
		</form>

		<?php if ( $hero_tags ) : ?>
			<div class="hero-tags">
				<?php foreach ( $hero_tags as $tag ) : ?>
					<a class="hero-tag" href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( $tag ) ) ); ?>"><?php echo esc_html( $tag ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
