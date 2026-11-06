<?php
/**
 * Footer template: one centred column (categories, logo, contact, follow,
 * copyright). Content comes from sokal_footer_content() in include/footer-content.php.
 */
?>

			<footer class="themesbazar_footer_sec sokal-footer2" style="--sokal-flogo:<?php echo (int) sokal_int( sokal_opt( 'footer_logo_height', 90 ), 30, 200 ); ?>px">
				<?php
				if ( function_exists( 'sokal_footer_content' ) ) {
					sokal_footer_content();
				}
				?>
			</footer>

		<?php
		get_template_part( 'include/root' );
		wp_footer();
		?>

	</body>
</html>
