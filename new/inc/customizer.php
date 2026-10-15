<?php
/**
 * 主题自定义设置（外观 → 自定义）
 *
 * @package ePiDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 图标卡片默认内容
 */
function epidk_default_icon_cards() {
	return implode(
		"\n",
		array(
			'👑|图标卡片|这是一个图标卡片示例||',
			'💎|原创作品|这是一个图标卡片示例||',
			'🔥|灵感来源|这是一个图标卡片示例|NEW|',
			'🛠️|系统工具|这是一个图标卡片示例|GO|',
		)
	);
}

/**
 * 注册自定义选项
 *
 * @param WP_Customize_Manager $wp_customize 自定义管理器.
 */
function epidk_customize_register( $wp_customize ) {

	$wp_customize->add_panel(
		'epidk_panel',
		array(
			'title'    => __( 'ePiDK 主题设置', 'epidk' ),
			'priority' => 30,
		)
	);

	/* ---------------- 主页 Hero ---------------- */
	$wp_customize->add_section(
		'epidk_hero',
		array(
			'title' => __( '主页大图（Hero）', 'epidk' ),
			'panel' => 'epidk_panel',
		)
	);

	$wp_customize->add_setting(
		'epidk_hero_bg',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'epidk_hero_bg',
			array(
				'label'       => __( '背景图', 'epidk' ),
				'description' => __( '不上传时使用主题内置默认图，建议尺寸 2560×1080。', 'epidk' ),
				'section'     => 'epidk_hero',
			)
		)
	);

	$hero_fields = array(
		'epidk_hero_title'             => array( __( '主标题', 'epidk' ), '更优雅的WordPress主题' ),
		'epidk_hero_subtitle'          => array( __( '副标题', 'epidk' ), '这是导航栏幻灯片多功能组件，可在后台进行配置' ),
		'epidk_hero_search_placeholder' => array( __( '搜索框占位文字', 'epidk' ), '开启精彩搜索' ),
		'epidk_hero_tags'              => array( __( '搜索框下方标签（英文逗号分隔）', 'epidk' ), '&type=post,' . gmdate( 'Y' ) ),
	);
	foreach ( $hero_fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field[1],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field[0],
				'section' => 'epidk_hero',
				'type'    => 'text',
			)
		);
	}

	/* ---------------- 图标卡片 ---------------- */
	$wp_customize->add_section(
		'epidk_cards',
		array(
			'title' => __( '图标卡片', 'epidk' ),
			'panel' => 'epidk_panel',
		)
	);

	$wp_customize->add_setting(
		'epidk_icon_cards',
		array(
			'default'           => epidk_default_icon_cards(),
			'sanitize_callback' => 'epidk_sanitize_textarea',
		)
	);
	$wp_customize->add_control(
		'epidk_icon_cards',
		array(
			'label'       => __( '卡片列表', 'epidk' ),
			'description' => __( '每行一张卡片，格式：图标emoji|标题|副标题|角标|链接。例如：🔥|灵感来源|这是一个图标卡片示例|NEW|https://example.com', 'epidk' ),
			'section'     => 'epidk_cards',
			'type'        => 'textarea',
		)
	);

	/* ---------------- 文章页 ---------------- */
	$wp_customize->add_section(
		'epidk_single',
		array(
			'title' => __( '文章页', 'epidk' ),
			'panel' => 'epidk_panel',
		)
	);

	$wp_customize->add_setting(
		'epidk_auto_heading_number',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'epidk_auto_heading_number',
		array(
			'label'       => __( '正文与目录标题自动编号（1、1.1）', 'epidk' ),
			'description' => __( '若你的文章标题已手写编号，请关闭此项。', 'epidk' ),
			'section'     => 'epidk_single',
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'epidk_customize_register' );

/**
 * 多行文本消毒
 *
 * @param string $input 输入.
 */
function epidk_sanitize_textarea( $input ) {
	$lines = array_map( 'trim', explode( "\n", (string) $input ) );
	$lines = array_filter( $lines );
	return implode( "\n", array_map( 'sanitize_text_field', $lines ) );
}
