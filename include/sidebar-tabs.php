<?php
/**
 * "Latest / Popular" sidebar tabs, shared by every template and cached.
 * Markup is unchanged from the original templates. Owner: caching agent.
 *
 * The Popular list (top 10 of the current month by views) is computed by a
 * WP-Cron job every 15 minutes and stored in the sokal_popular_ids option
 * (autoload off), so the tab itself only runs a cheap post__in query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------
 * Popular IDs: cron job + option
 * ------------------------------------------------------------------ */

add_filter( 'cron_schedules', function ( $schedules ) {
	if ( ! isset( $schedules['sokal_15min'] ) ) {
		$schedules['sokal_15min'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => 'Every 15 minutes (Sokal popular posts)',
		);
	}
	return $schedules;
} );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'sokal_popular_cron' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'sokal_15min', 'sokal_popular_cron' );
	}
} );

add_action( 'sokal_popular_cron', 'sokal_popular_refresh' );

add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'sokal_popular_cron' );
} );

/**
 * Recompute the top-10 most-viewed published posts of the current month
 * (site time) with one query and store them.
 *
 * @return int[] Post IDs, most viewed first.
 */
function sokal_popular_refresh() {
	global $wpdb;
	$month = current_time( 'Y-m' );
	$start = $month . '-01 00:00:00';

	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'post_views_count'
		WHERE p.post_type = 'post' AND p.post_status = 'publish' AND p.post_date >= %s
		ORDER BY CAST(m.meta_value AS UNSIGNED) DESC, p.post_date DESC
		LIMIT %d",
		$start,
		20
	) );
	$ids = array_slice( array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) ), 0, 10 );

	update_option( 'sokal_popular_ids', array(
		'ids'   => $ids,
		'month' => $month,
		'time'  => time(),
	), false );

	return $ids;
}

/**
 * Stored popular IDs; computed inline on first use, at month rollover, or
 * when the cron job hasn't run for 2 hours.
 *
 * @return int[]
 */
function sokal_popular_ids() {
	$o = get_option( 'sokal_popular_ids' );
	if ( is_array( $o ) && isset( $o['ids'], $o['month'], $o['time'] )
		&& current_time( 'Y-m' ) === $o['month']
		&& (int) $o['time'] > time() - 2 * HOUR_IN_SECONDS ) {
		return array_map( 'absint', (array) $o['ids'] );
	}
	return sokal_popular_refresh();
}

/* ------------------------------------------------------------------
 * Markup
 * ------------------------------------------------------------------ */

/** One story row inside a tab (thumbnail + headline). Call inside the loop. */
function sokal_sidebar_tabs_item() {
	?>
										<div class="images_title">
	                                        <a href="<?php echo esc_url( get_permalink() ); ?>"> <?php
												sokal_thumb( 'card-thumb', array(
													'loading'       => 'lazy',
													'fetchpriority' => 'low',
													'decoding'      => 'async',
													'sizes'         => '(max-width: 767px) 35vw, 130px',
												) );
											?> </a>
	                                        <h4 class="themesbazar_hadding_3"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?> </a></h4>
	                                    </div>
	<?php
}

function sokal_sidebar_tabs_render() {
	global $themesbazar;
	$tb        = is_array( $themesbazar ) ? $themesbazar : array();
	$label_new = ! empty( $tb['last'] ) ? $tb['last'] : 'সর্বশেষ';
	$label_pop = ! empty( $tb['popular'] ) ? $tb['popular'] : 'জনপ্রিয়';
	$count     = sokal_int( isset( $tb['lastpost'] ) ? $tb['lastpost'] : 5, 1, 20 );
	?>
						<div class="tab-header">
	                                            <!-- Nav tabs -->
	                        <ul class="nav nav-tabs nav-justified" role="tablist">
								<li role="presentation" class="active"><a href="#tab21" aria-controls="tab21" role="tab" data-toggle="tab" aria-expanded="false"><?php echo esc_html( $label_new ); ?></a></li>
								<li role="presentation" ><a href="#tab22" aria-controls="tab22" role="tab" data-toggle="tab" aria-expanded="true"><?php echo esc_html( $label_pop ); ?></a></li>
							</ul>

	                        <!-- Tab panes -->
	                        <div class="tab-content">
	                            <div role="tabpanel" class="tab-pane in active" id="tab21">
	                                <div class="news-title">
										<?php
										$lastnews = new WP_Query( array(
											'post_type'              => 'post',
											'post_status'            => 'publish',
											'posts_per_page'         => $count,
											'ignore_sticky_posts'    => true,
											'no_found_rows'          => true,
											'update_post_term_cache' => false,
										) );
										while ( $lastnews->have_posts() ) :
											$lastnews->the_post();
											sokal_sidebar_tabs_item();
										endwhile;
										wp_reset_postdata();
										?>
	                                </div>
	                            </div>
	                            <div role="tabpanel" class="tab-pane fade" id="tab22">
	                                <div class="news-title">
										<?php
										$ids = sokal_popular_ids();
										if ( $ids ) { // empty post__in would return every post
											$popular = new WP_Query( array(
												'post_type'              => 'post',
												'post_status'            => 'publish',
												'post__in'               => $ids,
												'orderby'                => 'post__in',
												'posts_per_page'         => count( $ids ),
												'ignore_sticky_posts'    => true,
												'no_found_rows'          => true,
												'update_post_term_cache' => false,
											) );
											while ( $popular->have_posts() ) :
												$popular->the_post();
												sokal_sidebar_tabs_item();
											endwhile;
											wp_reset_postdata();
										}
										?>
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	<?php
}

function sokal_sidebar_tabs() {
	echo sokal_fragment( 'sidebar_tabs', 5 * MINUTE_IN_SECONDS, 'sokal_sidebar_tabs_render' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped at render time
}
