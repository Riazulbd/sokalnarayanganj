<?php
/**
 * Home page sections and category picking. Owner: home agent.
 *
 * Provides:
 *   sokal_home_sections()   prints the cached home page (index.php calls it).
 *   sokal_home_categories() returns the categories each home block uses.
 *   sokal_home_legacy()     reads an old Redux value from global $themesbazar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Old Redux value with a default (works when $themesbazar is missing). */
function sokal_home_legacy( $key, $default = null ) {
	global $themesbazar;
	if ( is_array( $themesbazar ) && isset( $themesbazar[ $key ] ) && '' !== $themesbazar[ $key ] ) {
		return $themesbazar[ $key ];
	}
	return $default;
}

/** Category id if it names an existing category, else 0. */
function sokal_home_valid_cat( $id ) {
	$id = absint( $id );
	if ( ! $id ) {
		return 0;
	}
	$term = get_term( $id, 'category' );
	return ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0;
}

/** Id of the "Uncategorized" (default) category. */
function sokal_home_uncategorized() {
	$id = (int) get_option( 'default_category' );
	return $id ? $id : 1;
}

/**
 * Busiest categories (by post count), cached for an hour.
 * The list excludes Uncategorized; callers drop the ones already in use.
 */
function sokal_home_busiest_categories() {
	$key = 'sokal_home_busiest';
	$ids = get_transient( $key );
	if ( is_array( $ids ) ) {
		return array_map( 'intval', $ids );
	}
	$terms = get_terms(
		array(
			'taxonomy'               => 'category',
			'orderby'                => 'count',
			'order'                  => 'DESC',
			'hide_empty'             => true,
			'number'                 => 30,
			'fields'                 => 'ids',
			'exclude'                => array( sokal_home_uncategorized() ),
			'update_term_meta_cache' => false,
		)
	);
	$ids = is_wp_error( $terms ) ? array() : array_map( 'intval', $terms );
	set_transient( $key, $ids, HOUR_IN_SECONDS );
	return $ids;
}

/** Category changes can change which categories are busiest. */
function sokal_home_flush_busiest() {
	delete_transient( 'sokal_home_busiest' );
}
add_action( 'created_category', 'sokal_home_flush_busiest' );
add_action( 'edited_category', 'sokal_home_flush_busiest' );
add_action( 'delete_category', 'sokal_home_flush_busiest' );

/**
 * Categories for every home block.
 *
 * @return array {
 *     lead  => int  section one (0 = latest posts),
 *     grid  => int  first section-two block (old cat-two), 0 = skip,
 *     slots => int[8] next eight blocks (old cat-three..cat-ten), 0 = skip,
 *     extra => int  last block (old cat-eleven), 0 = skip,
 *     auto  => int[] ids that were auto-picked,
 * }
 */
function sokal_home_categories() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$auto_on = (bool) sokal_opt( 'auto_sections', 1 );
	$uncat   = sokal_home_uncategorized();

	// Legacy values that equal the Redux default (Uncategorized) count as empty when auto-pick is on.
	$legacy = function ( $key ) use ( $auto_on, $uncat ) {
		$id = absint( sokal_home_legacy( $key, 0 ) );
		if ( $auto_on && $id === $uncat ) {
			return 0;
		}
		return sokal_home_valid_cat( $id );
	};

	$lead = sokal_home_valid_cat( sokal_opt( 'lead_cat', 0 ) );
	if ( ! $lead ) {
		$lead = $legacy( 'cat-one' );
	}

	$grid = sokal_home_valid_cat( sokal_opt( 'grid_cat', 0 ) );
	if ( ! $grid ) {
		$grid = $legacy( 'cat-two' );
	}

	$saved      = sokal_opt( 'home_slots', array() );
	$saved      = is_array( $saved ) ? array_values( $saved ) : array();
	$legacy_ids = array( 'cat-three', 'cat-four', 'cat-five', 'cat-six', 'cat-seven', 'cat-eight', 'cat-nine', 'cat-ten' );
	$slots      = array();
	foreach ( $legacy_ids as $i => $legacy_key ) {
		$id = isset( $saved[ $i ] ) ? sokal_home_valid_cat( $saved[ $i ] ) : 0;
		if ( ! $id ) {
			$id = $legacy( $legacy_key );
		}
		$slots[ $i ] = $id;
	}

	$extra = 0;
	$auto  = array();

	if ( $auto_on ) {
		// Each category appears once: a repeated slot is refilled by auto-pick below.
		$seen = array_filter( array( $lead, $grid ) );
		foreach ( $slots as $i => $id ) {
			if ( $id && in_array( $id, $seen, true ) ) {
				$slots[ $i ] = 0;
			} elseif ( $id ) {
				$seen[] = $id;
			}
		}
		$used = array_filter( array_merge( array( $lead, $grid ), $slots ) );
		$pool = array_values( array_diff( sokal_home_busiest_categories(), $used, array( $uncat ) ) );
		$next = function () use ( &$pool, &$auto ) {
			while ( $pool ) {
				$id = sokal_home_valid_cat( array_shift( $pool ) );
				if ( $id ) {
					$auto[] = $id;
					return $id;
				}
			}
			return 0;
		};
		if ( ! $grid ) {
			$grid = $next();
		}
		foreach ( $slots as $i => $id ) {
			if ( ! $id ) {
				$slots[ $i ] = $next();
			}
		}
		$extra = $next();
	}
	if ( ! $extra ) {
		$extra = sokal_home_valid_cat( sokal_home_legacy( 'cat-eleven', 0 ) );
	}

	$cache = array(
		'lead'  => $lead,
		'grid'  => $grid,
		'slots' => $slots,
		'extra' => $extra,
		'auto'  => $auto,
	);
	return $cache;
}

/**
 * Section-two blocks in print order. Each: cat id, layout (wide = lead + cards,
 * narrow = cards only), legacy key suffix for counts/excerpt, and the ad area after it.
 */
function sokal_home_blocks() {
	$c = sokal_home_categories();
	$s = $c['slots'];
	return array(
		'main' => array(
			array( 'cat' => $c['grid'], 'n' => 'two', 'ad' => 'widget_area_03' ),
			array( 'cat' => $s[0], 'n' => 'three', 'ad' => 'widget_area_04' ),
			array( 'cat' => $s[1], 'n' => 'four', 'ad' => 'widget_area_05' ),
			array( 'cat' => $s[2], 'n' => 'five', 'ad' => 'widget_area_06' ),
			array( 'cat' => $s[3], 'n' => 'six', 'ad' => 'widget_area_07' ),
		),
		'side' => array(
			array( 'cat' => $s[4], 'n' => 'seven' ),
			array( 'cat' => $s[5], 'n' => 'eight' ),
			array( 'cat' => $s[6], 'n' => 'nine' ),
			array( 'cat' => $s[7], 'n' => 'ten' ),
			array( 'cat' => $c['extra'], 'n' => 'eleven' ),
		),
	);
}

/** Ad slot: prints nothing when the widget area is empty. */
function sokal_home_ad( $sidebar ) {
	if ( ! is_active_sidebar( $sidebar ) ) {
		return;
	}
	echo '<div class="row"><div class="col-md-12 col-sm-12"><div class="add sokal-ad">';
	dynamic_sidebar( $sidebar );
	echo '</div></div></div>';
}

/* ---------------- Home page sections ---------------- */

function sokal_home_sections_render() {
	$layout = sokal_home_legacy( 'homepage-section', array() );
	$layout = ( is_array( $layout ) && isset( $layout['Show'] ) && is_array( $layout['Show'] ) ) ? $layout['Show'] : array();
	if ( ! $layout ) {
		return;
	}
	foreach ( $layout as $key => $value ) {
		switch ( $key ) {
			case 'section-one':
				get_template_part( 'page/section-one' );
				break;
			case 'section-two':
				get_template_part( 'page/section-two' );
				break;
		}
	}
}

function sokal_home_sections() {
	if ( function_exists( 'sokal_fragment' ) ) {
		echo sokal_fragment( 'home_sections', 5 * MINUTE_IN_SECONDS, 'sokal_home_sections_render' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the templates.
		return;
	}
	sokal_home_sections_render();
}
