<?php
/**
 * 文章标题解析与目录（TOC）生成
 *
 * @package ePiDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 给正文的 h2/h3 标题添加锚点 id，并把标题收集到全局供右侧目录使用
 *
 * @param string $content 正文 HTML.
 */
function epidk_filter_content_headings( $content ) {
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() || is_feed() ) {
		return $content;
	}

	$GLOBALS['epidk_toc'] = array();
	$index                = 0;

	return preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$index ) {
			$index++;
			$level = (int) $m[1];
			$id    = 'toc-' . $index;

			$GLOBALS['epidk_toc'][] = array(
				'level' => $level,
				'text'  => wp_strip_all_tags( $m[3] ),
				'id'    => $id,
			);

			// 移除已有 id，避免重复
			$attrs = preg_replace( '/\s+id=("|\')[^"\']*\1/i', '', $m[2] );

			return sprintf( '<h%d%s id="%s">%s</h%d>', $level, $attrs, $id, $m[3], $level );
		},
		$content
	);
}
add_filter( 'the_content', 'epidk_filter_content_headings', 12 );

/**
 * 根据收集到的标题生成嵌套目录 HTML
 *
 * @param array $items 标题数组.
 */
function epidk_build_toc( $items ) {
	if ( empty( $items ) ) {
		return '';
	}

	$out   = '';
	$stack = 0;

	foreach ( $items as $it ) {
		$lvl = ( 2 === (int) $it['level'] ) ? 1 : 2;

		// 防止层级跳跃（例如没有 h2 直接出现 h3）
		if ( $lvl > $stack + 1 ) {
			$lvl = $stack + 1;
		}

		if ( $lvl > $stack ) {
			$out  .= '<ul class="toc-l' . $lvl . '">';
			$stack = $lvl;
		} elseif ( $lvl < $stack ) {
			while ( $stack > $lvl ) {
				$out .= '</li></ul>';
				$stack--;
			}
			$out .= '</li>';
		} else {
			$out .= '</li>';
		}

		$out .= '<li><a href="#' . esc_attr( $it['id'] ) . '">' . esc_html( $it['text'] ) . '</a>';
	}

	while ( $stack > 0 ) {
		$out  .= '</li></ul>';
		$stack--;
	}

	return $out;
}

/**
 * 输出右侧目录卡片（若正文没有标题则不输出）
 */
function epidk_render_toc_card() {
	$toc = isset( $GLOBALS['epidk_toc'] ) ? $GLOBALS['epidk_toc'] : array();
	if ( empty( $toc ) ) {
		return;
	}
	?>
	<div class="toc-card" id="toc-card">
		<div class="toc-card-title"><?php echo epidk_icon( 'list', 15 ); ?><span><?php esc_html_e( '目录', 'epidk' ); ?></span></div>
		<nav class="toc-nav" aria-label="<?php esc_attr_e( '文章目录', 'epidk' ); ?>">
			<?php echo epidk_build_toc( $toc ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</nav>
	</div>
	<?php
}
