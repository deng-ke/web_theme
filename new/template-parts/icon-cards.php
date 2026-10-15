<?php
/**
 * 主页图标卡片行（外观 → 自定义 → ePiDK 主题设置 → 图标卡片）
 *
 * @package ePiDK
 */

$raw   = get_theme_mod( 'epidk_icon_cards', epidk_default_icon_cards() );
$lines = array_filter( array_map( 'trim', explode( "\n", (string) $raw ) ) );

if ( empty( $lines ) ) {
	return;
}

// 预置的图标底色，按卡片顺序循环
$palettes = array(
	array( '#fff7e6', '#f59e0b' ),
	array( '#f3e8ff', '#8b5cf6' ),
	array( '#fee2e2', '#ef4444' ),
	array( '#d1fae5', '#10b981' ),
	array( '#dbeafe', '#3b82f6' ),
	array( '#fce7f3', '#ec4899' ),
);
?>

<section class="icon-cards container" aria-label="<?php esc_attr_e( '快捷入口', 'epidk' ); ?>">
	<div class="icon-cards-grid">
		<?php
		foreach ( $lines as $i => $line ) :
			$parts = array_map( 'trim', explode( '|', $line ) );
			list( $emoji, $title, $desc, $badge, $link ) = array_pad( $parts, 5, '' );
			$palette = $palettes[ $i % count( $palettes ) ];
			$badge_class = $badge ? ' badge-' . sanitize_html_class( strtolower( $badge ) ) : '';
			?>
		<a class="icon-card" href="<?php echo esc_url( $link ? $link : '#' ); ?>">
			<span class="icon-card-icon" style="background:<?php echo esc_attr( $palette[0] ); ?>;color:<?php echo esc_attr( $palette[1] ); ?>"><?php echo esc_html( $emoji ); ?></span>
			<span class="icon-card-body">
				<span class="icon-card-title">
					<?php echo esc_html( $title ); ?>
					<?php if ( $badge ) : ?>
						<em class="icon-card-badge<?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge ); ?></em>
					<?php endif; ?>
				</span>
				<span class="icon-card-desc"><?php echo esc_html( $desc ); ?></span>
			</span>
		</a>
		<?php endforeach; ?>
	</div>
</section>
