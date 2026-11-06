<?php
/**
 * Header tickers. Owner: header agent.
 *
 * Accessible CSS ticker (replaces <marquee>). Behaviour lives in
 * css/components/ticker.css and js/components/ticker.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the settings for one ticker.
 *
 * Top ticker: new settings (ticker_*), legacy title as label fallback.
 * Bottom ticker: legacy Redux settings (bottom_scroll_*).
 * Both: legacy "custom text" mode (scroll_set 3) is honoured.
 *
 * @param string $pos 'top' or 'bottom'.
 * @return array|false
 */
function sokal_ticker_config( $pos ) {
	global $themesbazar;
	$legacy = is_array( $themesbazar ) ? $themesbazar : array();
	$pos    = ( 'bottom' === $pos ) ? 'bottom' : 'top';
	$set    = isset( $legacy[ $pos . '_scroll_set' ] ) ? (int) $legacy[ $pos . '_scroll_set' ] : 1;
	$speed  = sokal_int( sokal_opt( 'ticker_speed', 40 ), 5, 300 );

	if ( 'top' === $pos ) {
		if ( ! (int) sokal_opt( 'ticker_enabled', 1 ) ) {
			return false;
		}
		$label = (string) sokal_opt( 'ticker_label', isset( $legacy['top_scroll_title'] ) ? $legacy['top_scroll_title'] : '' );
		$count = sokal_int( sokal_opt( 'ticker_count', 10 ), 1, 30 );
		$cat   = absint( sokal_opt( 'ticker_cat', 0 ) );
	} else {
		if ( empty( $legacy['bottom_scroll'] ) || 1 != $legacy['bottom_scroll'] ) {
			return false;
		}
		$label = isset( $legacy['bottom_scroll_title'] ) ? (string) $legacy['bottom_scroll_title'] : '';
		$count = sokal_int( isset( $legacy['how_bottom_scroll'] ) ? $legacy['how_bottom_scroll'] : 10, 1, 30 );
		$cat   = ( 2 === $set && isset( $legacy['bottom_scroll_cat'] ) ) ? absint( $legacy['bottom_scroll_cat'] ) : 0;
	}

	$custom = '';
	if ( 3 === $set && ! empty( $legacy[ $pos . '_scroll_content' ] ) ) {
		$custom = (string) $legacy[ $pos . '_scroll_content' ];
	}

	return array(
		'pos'    => $pos,
		'wrap'   => ( 'top' === $pos ) ? 'top_scrool' : 'scrool',
		'label'  => $label,
		'count'  => $count,
		'cat'    => $cat,
		'speed'  => $speed,
		'custom' => $custom,
		'icon'   => isset( $legacy['header-scrool-icon'] ) ? (string) $legacy['header-scrool-icon'] : '',
	);
}

/**
 * Print the <li> items once. $copy = true prints the aria-hidden duplicate.
 *
 * @param array $items Each: array( 'url' => '', 'title' => '' ) or array( 'html' => '' ).
 * @param array $cfg   Ticker config.
 * @param bool  $copy  Duplicate for the seamless loop.
 */
function sokal_ticker_items( $items, $cfg, $copy ) {
	foreach ( $items as $item ) {
		echo $copy ? '<li class="sokal-ticker__item" aria-hidden="true">' : '<li class="sokal-ticker__item">';
		if ( isset( $item['html'] ) ) {
			// Custom text: strip links from the hidden copy's tab order.
			$html = wp_kses_post( $item['html'] );
			if ( $copy ) {
				$html = preg_replace( '/<a\s/i', '<a tabindex="-1" ', $html );
			}
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- kses'd above.
		} else {
			if ( '' !== $cfg['icon'] ) {
				echo '<i class="' . esc_attr( $cfg['icon'] ) . '" aria-hidden="true"></i> ';
			}
			echo '<a href="' . esc_url( $item['url'] ) . '"' . ( $copy ? ' tabindex="-1"' : '' ) . '>' . esc_html( $item['title'] ) . '</a>';
		}
		echo '</li>';
	}
}

/**
 * @param array $cfg From sokal_ticker_config().
 */
function sokal_ticker_render( $cfg ) {
	$items = array();

	if ( '' !== $cfg['custom'] ) {
		$items[] = array( 'html' => $cfg['custom'] );
	} else {
		$args = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $cfg['count'],
			'ignore_sticky_posts'    => false,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);
		if ( $cfg['cat'] ) {
			$args['cat'] = $cfg['cat'];
		}
		$q = new WP_Query( $args );
		foreach ( $q->posts as $p ) {
			$items[] = array(
				'url'   => get_permalink( $p ),
				'title' => get_the_title( $p ),
			);
		}
		wp_reset_postdata();
	}

	if ( ! $items ) {
		return;
	}
	?>
				<div class="col-md-12 <?php echo esc_attr( $cfg['wrap'] ); ?>">
					<div class="col-md-2 scrool_1">
						<?php echo esc_html( $cfg['label'] ); ?>
					</div>
					<div class="col-md-10 scrool_2">
						<div class="sokal-ticker" style="--speed:<?php echo (int) $cfg['speed']; ?>s">
							<button type="button" class="sokal-ticker__pause" aria-pressed="false" aria-label="<?php echo esc_attr( 'শিরোনাম চলা থামান' ); ?>">&#x23F8;&#xFE0E;</button>
							<div class="sokal-ticker__viewport">
								<ul class="sokal-ticker__track">
									<?php
									sokal_ticker_items( $items, $cfg, false );
									sokal_ticker_items( $items, $cfg, true );
									?>
								</ul>
							</div>
						</div>
					</div>
				</div>
	<?php
}

/**
 * Contract function: print the 'top' or 'bottom' ticker (cached fragment).
 *
 * @param string $pos 'top' or 'bottom'.
 */
function sokal_ticker( $pos ) {
	$cfg = sokal_ticker_config( $pos );
	if ( ! $cfg ) {
		return;
	}
	$key = 'ticker_' . $cfg['pos'] . '_' . md5( wp_json_encode( $cfg ) );
	if ( function_exists( 'sokal_fragment' ) ) {
		echo sokal_fragment( $key, 5 * MINUTE_IN_SECONDS, function () use ( $cfg ) { // phpcs:ignore WordPress.Security.EscapeOutput -- escaped at render.
			sokal_ticker_render( $cfg );
		} );
	} else {
		sokal_ticker_render( $cfg );
	}
}
