<?php
/**
 * 站点头部
 *
 * @package ePiDK
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header id="site-header" class="site-header">
	<div class="header-inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>

		<nav class="main-nav" id="main-nav" aria-label="<?php esc_attr_e( '主导航', 'epidk' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-menu',
					'fallback_cb'    => 'epidk_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="header-search">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( '搜索内容', 'epidk' ); ?>" autocomplete="off">
				<button type="submit" aria-label="<?php esc_attr_e( '搜索', 'epidk' ); ?>"><?php echo epidk_icon( 'search', 16 ); ?></button>
			</form>
		</div>

		<div class="header-actions">
			<?php if ( is_user_logged_in() ) : ?>
				<a class="header-avatar" href="<?php echo esc_url( admin_url() ); ?>" title="<?php esc_attr_e( '进入后台', 'epidk' ); ?>">
					<?php echo get_avatar( get_current_user_id(), 32 ); ?>
				</a>
			<?php endif; ?>

			<button type="button" id="theme-toggle" class="icon-btn" aria-label="<?php esc_attr_e( '切换深浅色', 'epidk' ); ?>">
				<span class="icon-moon"><?php echo epidk_icon( 'moon', 18 ); ?></span>
				<span class="icon-sun"><?php echo epidk_icon( 'sun', 18 ); ?></span>
			</button>

			<?php
			$publish_url = is_user_logged_in() ? admin_url( 'post-new.php' ) : wp_login_url( admin_url( 'post-new.php' ) );
			?>
			<a class="btn-publish" href="<?php echo esc_url( $publish_url ); ?>"><?php echo epidk_icon( 'pen', 14 ); ?><span><?php esc_html_e( '发布', 'epidk' ); ?></span></a>

			<button type="button" id="menu-toggle" class="icon-btn menu-toggle" aria-label="<?php esc_attr_e( '菜单', 'epidk' ); ?>" aria-expanded="false">
				<?php echo epidk_icon( 'menu', 20 ); ?>
			</button>
		</div>
	</div>
</header>

<div id="page" class="site">
