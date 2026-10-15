<?php
/**
 * 评论模板
 *
 * @package ePiDK
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area article-card">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$count = (int) get_comments_number();
			/* translators: %s: 评论数 */
			printf( esc_html__( '评论（%s）', 'epidk' ), esc_html( $count ) );
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'avatar_size' => 40,
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '‹',
				'next_text' => '›',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( '评论已关闭。', 'epidk' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'          => __( '发表评论', 'epidk' ),
			'title_reply_before'   => '<h2 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h2>',
			'class_submit'         => 'btn-submit',
			'comment_notes_before' => '',
		)
	);
	?>

</div>
