<?php
/**
 * 浏览量统计与点赞（AJAX）
 *
 * @package ePiDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 获取浏览量
 *
 * @param int $post_id 文章 ID.
 */
function epidk_get_views( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (int) get_post_meta( $post_id, 'epidk_views', true );
}

/**
 * 获取点赞数
 *
 * @param int $post_id 文章 ID.
 */
function epidk_get_likes( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (int) get_post_meta( $post_id, 'epidk_likes', true );
}

/**
 * 文章页浏览量 +1
 */
function epidk_count_view() {
	if ( ! is_singular( 'post' ) || is_preview() ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	// 12 小时内同一访客只记一次
	if ( ! empty( $_COOKIE[ 'epidk_viewed_' . $post_id ] ) ) {
		return;
	}

	$views = (int) get_post_meta( $post_id, 'epidk_views', true );
	update_post_meta( $post_id, 'epidk_views', $views + 1 );

	if ( ! headers_sent() ) {
		setcookie( 'epidk_viewed_' . $post_id, '1', time() + 12 * HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/' );
	}
}
add_action( 'template_redirect', 'epidk_count_view' );

/**
 * AJAX 点赞 / 取消点赞
 */
function epidk_like_ajax() {
	check_ajax_referer( 'epidk_like', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	if ( ! $post_id || ! get_post( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'invalid post' ), 400 );
	}

	$cookie = 'epidk_liked_' . $post_id;
	$likes  = (int) get_post_meta( $post_id, 'epidk_likes', true );

	if ( ! empty( $_COOKIE[ $cookie ] ) ) {
		$likes = max( 0, $likes - 1 );
		update_post_meta( $post_id, 'epidk_likes', $likes );
		setcookie( $cookie, '', time() - HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/' );
		wp_send_json_success(
			array(
				'likes' => $likes,
				'liked' => false,
			)
		);
	}

	update_post_meta( $post_id, 'epidk_likes', $likes + 1 );
	setcookie( $cookie, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/' );
	wp_send_json_success(
		array(
			'likes' => $likes + 1,
			'liked' => true,
		)
	);
}
add_action( 'wp_ajax_epidk_like', 'epidk_like_ajax' );
add_action( 'wp_ajax_nopriv_epidk_like', 'epidk_like_ajax' );
