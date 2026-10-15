<?php
/**
 * 页脚模板：输出页内目录、上下篇导航与底部信息。
 *
 * @package GitBook
 */

?>
			</main>

			<?php if ( is_singular() ) : ?>
				<aside class="gb-toc" id="gb-toc" aria-label="<?php esc_attr_e( '本页目录', 'gitbook' ); ?>">
					<div class="gb-toc-inner">
						<p class="gb-toc-title"><?php esc_html_e( '本页内容', 'gitbook' ); ?></p>
						<nav class="gb-toc-list" id="gb-toc-list">
							<a class="gb-toc-link gb-toc-empty" href="#gb-content"><?php esc_html_e( '加载中…', 'gitbook' ); ?></a>
						</nav>
					</div>
				</aside>
			<?php endif; ?>
		</div>

		<footer class="gb-footer">
			<div class="gb-footer-inner">
				<?php if ( is_singular() ) : ?>
					<div class="gb-prev-next">
						<?php
						$links = gitbook_prev_next();
						if ( $links['prev'] ) :
							?>
							<a class="gb-pn-card" href="<?php echo esc_url( $links['prev']['url'] ); ?>">
								<span class="gb-pn-label"><?php esc_html_e( '上一篇', 'gitbook' ); ?></span>
								<span class="gb-pn-title"><?php echo esc_html( $links['prev']['title'] ); ?></span>
							</a>
						<?php else : ?>
							<span class="gb-pn-card is-empty"></span>
						<?php endif; ?>

						<?php if ( $links['next'] ) : ?>
							<a class="gb-pn-card is-next" href="<?php echo esc_url( $links['next']['url'] ); ?>">
								<span class="gb-pn-label"><?php esc_html_e( '下一篇', 'gitbook' ); ?></span>
								<span class="gb-pn-title"><?php echo esc_html( $links['next']['title'] ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="gb-footer-bar">
					<p class="gb-copyright">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> · <?php esc_html_e( '由 WordPress 与 GitBook 主题驱动', 'gitbook' ); ?></p>
					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'gb-footer-menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						) );
					}
					?>
				</div>
			</div>
		</footer>

	</div><!-- .gb-main -->
</div><!-- .gb-app -->

<?php wp_footer(); ?>
</body>
</html>
