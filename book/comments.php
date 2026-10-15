<?php
/**
 * 评论模板。
 *
 * @package GitBook
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="gb-comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="gb-comments-title">
			<?php
			$gb_comment_count = get_comments_number();
			if ( '1' === $gb_comment_count ) {
				esc_html_e( '1 条评论', 'gitbook' );
			} else {
				/* translators: %s: 评论数量。 */
				printf( esc_html( _n( '%s 条评论', '%s 条评论', $gb_comment_count, 'gitbook' ) ), esc_html( number_format_i18n( $gb_comment_count ) ) );
			}
			?>
		</h2>

		<ol class="gb-comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size' => 44,
			) );
			?>
		</ol>

		<?php
		the_comments_navigation();
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="gb-comments-closed"><?php esc_html_e( '评论已关闭。', 'gitbook' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form( array(
		'title_reply'        => esc_html__( '发表评论', 'gitbook' ),
		'title_reply_before' => '<h2 class="gb-comments-title">',
		'title_reply_after'  => '</h2>',
		'comment_notes_before' => '',
		'class_submit'       => 'gb-btn',
		'label_submit'       => esc_html__( '提交评论', 'gitbook' ),
	) );
	?>

</div>
