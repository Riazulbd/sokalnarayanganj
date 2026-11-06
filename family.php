<?php /* Template Name: Famaily */ ?>
<?php get_header();

$sokal_family_per_page = 60;
$sokal_family_paged    = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );

if ( ! function_exists( 'sokal_family_list_render' ) ) {
	/**
	 * Echo one page of the Family (team) grid.
	 *
	 * @param int $paged    Page number.
	 * @param int $per_page Profiles per page.
	 */
	function sokal_family_list_render( $paged, $per_page ) {
		$family = new WP_Query( array(
			'post_type'              => 'family',
			'post_status'            => 'publish',
			'posts_per_page'         => function_exists( 'sokal_int' ) ? sokal_int( $per_page, 1, 60 ) : min( 60, max( 1, (int) $per_page ) ),
			'paged'                  => max( 1, (int) $paged ),
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		) );
		while ( $family->have_posts() ) : $family->the_post(); ?>

				<div class="col-md-3">
					<div class="profile_news">
						<a href="<?php the_permalink(); ?>"><?php sokal_thumb( 'post-thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 991px) 100vw, 270px' ) ); ?></a>
						<div class="family_border">
							<h4 class="family"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
							<h4 class="family_deg"><?php echo esc_html( (string) get_post_meta( get_the_ID(), 'reporter_deg', true ) ); ?></h4>
						</div>
					</div>
				</div>

		<?php endwhile;
		wp_reset_postdata();
	}
}

$sokal_family_render = function () use ( $sokal_family_paged, $sokal_family_per_page ) {
	sokal_family_list_render( $sokal_family_paged, $sokal_family_per_page );
};

// Page count from the cached post counts, so pagination stays outside the fragment.
$sokal_family_counts = wp_count_posts( 'family' );
$sokal_family_pages  = (int) ceil( ( isset( $sokal_family_counts->publish ) ? (int) $sokal_family_counts->publish : 0 ) / $sokal_family_per_page );
?>



<section class="archive-section">



<!---news option******************--->
			<div class="row">
			    <?php
			    if ( function_exists( 'sokal_fragment' ) ) {
			        echo sokal_fragment( 'family_list_' . $sokal_family_paged, HOUR_IN_SECONDS, $sokal_family_render ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built.
			    } else {
			        call_user_func( $sokal_family_render );
			    }
			    ?>

			</div>

			<?php if ( $sokal_family_pages > 1 ) : ?>
			<div class="row"><div class="col-md-12 col-sm-12"><?php wp_bootstrap_pagination( array( 'custom_query' => (object) array( 'max_num_pages' => $sokal_family_pages ) ) ); ?></div></div>
			<?php endif; ?>
	</section>



	<?php get_footer(); ?>
