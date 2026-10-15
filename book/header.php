<?php
/**
 * 页头模板：输出左侧章节导航与顶部工具栏。
 *
 * @package GitBook
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

<a class="skip-link screen-reader-text" href="#gb-content"><?php esc_html_e( '跳至正文', 'gitbook' ); ?></a>

<div class="gb-app">

	<aside class="gb-sidebar" id="gb-sidebar" aria-label="<?php esc_attr_e( '章节导航', 'gitbook' ); ?>">
		<div class="gb-sidebar-head">
			<?php if ( has_custom_logo() ) : ?>
				<div class="gb-logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a class="gb-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="gb-brand-mark" aria-hidden="true">
						<svg viewBox="0 0 24 24"><path d="M4 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 4v2h12V7H6Zm0 4v2h12v-2H6Zm0 4v2h8v-2H6Z"/></svg>
					</span>
					<span class="gb-brand-name"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>

			<button class="gb-sidebar-close" type="button" data-sidebar-close aria-label="<?php esc_attr_e( '关闭导航', 'gitbook' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.4 5 5 6.4 10.6 12 5 17.6 6.4 19 12 13.4 17.6 19 19 17.6 13.4 12 19 6.4 17.6 5 12 10.6Z"/></svg>
			</button>
		</div>

		<?php get_search_form(); ?>

		<nav class="gb-nav" aria-label="<?php esc_attr_e( '目录', 'gitbook' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'gb-nav-list',
					'fallback_cb'    => false,
				) );
			} else {
				$tree = gitbook_page_tree();
				?>
				<ul class="gb-nav-list">
					<?php if ( ! empty( $tree ) ) : ?>
						<?php foreach ( $tree as $node ) : ?>
							<li class="gb-nav-item<?php echo $node['is_active'] ? ' is-active' : ''; ?>">
								<a class="gb-nav-link" href="<?php echo esc_url( $node['url'] ); ?>"><?php echo esc_html( $node['title'] ); ?></a>
								<?php if ( ! empty( $node['children'] ) ) : ?>
									<ul class="gb-nav-sublist">
										<?php foreach ( $node['children'] as $child ) : ?>
											<li class="gb-nav-subitem<?php echo $child['is_active'] ? ' is-active' : ''; ?>">
												<a class="gb-nav-link" href="<?php echo esc_url( $child['url'] ); ?>"><?php echo esc_html( $child['title'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php
					$recent = get_posts( array(
						'numberposts' => 5,
						'post_status' => 'publish',
					) );
					if ( $recent ) :
						?>
						<li class="gb-nav-group"><?php esc_html_e( '最新文章', 'gitbook' ); ?></li>
						<?php foreach ( $recent as $post_item ) : ?>
							<li class="gb-nav-item<?php echo ( get_queried_object_id() === $post_item->ID ) ? ' is-active' : ''; ?>">
								<a class="gb-nav-link" href="<?php echo esc_url( get_permalink( $post_item ) ); ?>"><?php echo esc_html( get_the_title( $post_item ) ); ?></a>
							</li>
						<?php endforeach; ?>
						<?php
						wp_reset_postdata();
					endif;
					?>
				</ul>
				<?php
			}
			?>
		</nav>

		<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
			<div class="gb-sidebar-widgets">
				<?php dynamic_sidebar( 'sidebar-1' ); ?>
			</div>
		<?php endif; ?>
	</aside>

	<div class="gb-sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>

	<div class="gb-main">

		<header class="gb-header">
			<button class="gb-menu-toggle" type="button" data-sidebar-open aria-label="<?php esc_attr_e( '打开导航', 'gitbook' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v2H3Zm0 5h18v2H3Zm0 5h18v2H3Z"/></svg>
			</button>

			<?php if ( ! has_custom_logo() && is_singular() ) : ?>
				<span class="gb-header-crumb"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>

			<button class="gb-header-search" type="button" data-search-open aria-label="<?php esc_attr_e( '搜索', 'gitbook' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3a6.5 6.5 0 1 0 3.84 11.65l4.9 4.9 1.42-1.41-4.9-4.9A6.5 6.5 0 0 0 9.5 3Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z"/></svg>
			</button>

			<div class="gb-header-actions">
				<button class="gb-icon-btn" type="button" data-theme-toggle aria-label="<?php esc_attr_e( '切换深浅色模式', 'gitbook' ); ?>">
					<span class="gb-theme-icon gb-theme-icon--moon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.1 22a10 10 0 1 1 9.3-14.3 8 8 0 0 0-1.1.13A8 8 0 0 0 13.9 20.9a8.1 8.1 0 0 0 .13 1.1A10 10 0 0 1 12.1 22Z"/></svg></span>
					<span class="gb-theme-icon gb-theme-icon--sun"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm-1-12h2v3h-2Zm0 17h2v3h-2Zm8.49-17.49 1.41 1.41-2.12 2.12-1.41-1.41ZM3.1 5.51l1.42-1.41 2.12 2.12-1.42 1.41ZM20 11v2h-3v-2ZM7 11v2H4v-2Zm10.37 7.37 2.12 2.12-1.41 1.41-2.12-2.12ZM5.51 20.49 3.1 19.08l2.12-2.12 1.41 1.41Z"/></svg></span>
				</button>
			</div>

			<div class="gb-reading-progress" aria-hidden="true"><span id="gb-progress-bar"></span></div>
		</header>

		<div class="gb-body">
			<main class="gb-content" id="gb-content">
