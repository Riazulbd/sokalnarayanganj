<?php
/**
 * Related stories on the article page. Owner: article agent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------- Related stories (article page) ---------------- */

function sokal_related_card() {
	?>
                        <div class="col-sm-4 col-md-4">
                            <div class="Name-again box-shadow">
                                <div class="image-again">
									<a href="<?php the_permalink(); ?>">
									<?php
									sokal_thumb(
										'post-thumbnail',
										array(
											'loading'       => 'lazy',
											'fetchpriority' => 'low',
											'decoding'      => 'async',
											'sizes'         => '(max-width: 767px) 100vw, (max-width: 1199px) 22vw, 240px',
										)
									);
									?>
									</a>
                                    <h4 class="sgl-hadding"> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </h4>
                                </div>
                            </div>
                        </div>
	<?php
}

function sokal_related_posts( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return;
	}
	echo sokal_fragment( 'related_' . $post_id, HOUR_IN_SECONDS, function () use ( $post_id ) { // phpcs:ignore WordPress.Security.EscapeOutput -- fragment HTML is escaped when built.
		$cats  = wp_get_post_categories( $post_id );
		$posts = array();
		if ( $cats ) {
			// One query for both rows (originally two queries: first 3, then offset 3).
			$q = new WP_Query( array(
				'category__in'           => array_map( 'intval', $cats ),
				'post__not_in'           => array( $post_id ),
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => function_exists( 'sokal_int' ) ? sokal_int( 6, 1, 6 ) : 6,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false, // no terms are printed
				// Post meta cache stays on: _thumbnail_id is post meta.
			) );
			$posts = $q->posts;
			if ( $posts ) {
				update_post_thumbnail_cache( $q ); // prime attachment posts + their meta in one go
			}
		}
		if ( ! $posts ) {
			return;
		}
		global $post;
		foreach ( array( array_slice( $posts, 0, 3 ), array_slice( $posts, 3, 3 ) ) as $row ) {
			if ( ! $row ) {
				continue;
			}
			echo '<div class="row">';
			foreach ( $row as $post ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				setup_postdata( $post );
				sokal_related_card();
			}
			echo '</div>';
		}
		wp_reset_postdata();
	}, false ); // unversioned: relies on its 1-hour TTL, not on site-wide edits
}
