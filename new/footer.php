<?php
/**
 * 站点底部
 *
 * @package ePiDK
 */

?>

<footer class="site-footer">
	<div class="container footer-inner">
		<div class="footer-copy">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php
			$icp = get_option( 'epidk_icp', '' );
			if ( $icp ) {
				echo ' · ' . esc_html( $icp ); }
			?>
			<span class="footer-powered"> · Powered by WordPress · Theme ePiDK</span>
		</div>
	</div>
</footer>

</div><!-- #page -->

<div class="float-actions">
	<button type="button" id="back-to-top" class="float-btn" aria-label="<?php esc_attr_e( '回到顶部', 'epidk' ); ?>">
		<?php echo epidk_icon( 'arrow-up', 18 ); ?>
	</button>
</div>

<?php wp_footer(); ?>
</body>
</html>
