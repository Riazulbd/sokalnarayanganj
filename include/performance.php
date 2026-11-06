<?php
/**
 * Speed layer: fragment cache, cache invalidation, Cloudflare purge, query
 * tuning, edge-cache headers, view counter endpoint, head cleanup, WebP,
 * prefetch. Owner: caching agent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------
 * Fragment cache
 *
 * Each fragment is stored as array( html, exp, v ). When it goes stale
 * (expired, or the site version was bumped by an edit) exactly one request
 * takes a short lock and rebuilds it; every other request keeps getting the
 * stale copy, so a traffic spike can't pile up rebuilds.
 * ------------------------------------------------------------------ */

function sokal_cache_version() {
	$v = (int) get_option( 'sokal_cache_ver', 0 );
	if ( ! $v ) {
		$v = 1;
		add_option( 'sokal_cache_ver', $v, '', true );
	}
	return $v;
}

/** Mark every versioned fragment stale. Called whenever content or settings change. */
function sokal_flush_cache() {
	static $done = false; // one bump per request is enough
	if ( $done ) {
		return;
	}
	$done = true;
	update_option( 'sokal_cache_ver', sokal_cache_version() + 1, true );
}

/**
 * Take the rebuild lock for $key. Atomic with or without a persistent object cache.
 *
 * @return bool True when this request now holds the lock.
 */
function sokal_cache_lock( $key ) {
	$lock = 'sokal_lock_' . md5( $key );
	if ( wp_using_ext_object_cache() ) {
		return (bool) wp_cache_add( $lock, 1, 'sokal', 30 );
	}

	global $wpdb;
	$now = time();

	// INSERT IGNORE is atomic: only one request can create the row.
	$rows = $wpdb->query( $wpdb->prepare(
		"INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')",
		$lock,
		(string) $now
	) );
	if ( 1 === (int) $rows ) {
		return true;
	}

	// Lock exists. Take it over only if it is abandoned (older than 30s);
	// the conditional UPDATE lets exactly one request win the takeover.
	$rows = $wpdb->query( $wpdb->prepare(
		"UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND CAST(option_value AS UNSIGNED) < %d",
		(string) $now,
		$lock,
		$now - 30
	) );
	return 1 === (int) $rows;
}

function sokal_cache_unlock( $key ) {
	$lock = 'sokal_lock_' . md5( $key );
	if ( wp_using_ext_object_cache() ) {
		wp_cache_delete( $lock, 'sokal' );
		return;
	}
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s", $lock ) );
}

/** Run a render callable and return what it echoed. */
function sokal_fragment_render( $render ) {
	ob_start();
	call_user_func( $render );
	$html = ob_get_clean();
	wp_reset_postdata();
	return $html;
}

/**
 * Return a cached HTML fragment, building it with $render (which echoes) when needed.
 *
 * Logged-in users never write to the cache (their output may contain drafts,
 * private posts or edit links): they get a fresh cached copy if one exists,
 * otherwise a live render that is not stored.
 *
 * @param string   $key       Unique fragment name.
 * @param int      $ttl       Seconds the fragment is fresh.
 * @param callable $render    Outputs the fragment HTML.
 * @param bool     $versioned True (default): any content edit makes the fragment stale.
 *                            False: ignore the global version and rely on the TTL only
 *                            (for per-post fragments such as related posts).
 * @return string
 */
function sokal_fragment( $key, $ttl, $render, $versioned = true ) {
	// Previews and the Customizer must always show live output.
	if ( is_preview() || is_customize_preview() ) {
		return sokal_fragment_render( $render );
	}

	$ttl    = max( 1, (int) $ttl );
	$tkey   = 'sokal_f_' . md5( $key );
	$cached = get_transient( $tkey );
	$ver    = $versioned ? sokal_cache_version() : 0;
	$valid  = is_array( $cached ) && isset( $cached['html'], $cached['exp'], $cached['v'] );

	if ( $valid && (int) $cached['v'] === $ver && (int) $cached['exp'] > time() ) {
		return $cached['html'];
	}

	// Logged in: never store. Render live.
	if ( is_user_logged_in() ) {
		return sokal_fragment_render( $render );
	}

	$locked = sokal_cache_lock( $tkey );

	// Stale copy exists and someone else is already rebuilding: serve it.
	if ( $valid && ! $locked ) {
		return $cached['html'];
	}

	// First build (no copy at all) always renders, lock or not.
	$html = sokal_fragment_render( $render );

	// Keep the copy past its TTL so there is always something stale to serve.
	$grace = $ttl <= HOUR_IN_SECONDS ? HOUR_IN_SECONDS : DAY_IN_SECONDS;
	set_transient( $tkey, array( 'html' => $html, 'exp' => time() + $ttl, 'v' => $ver ), $ttl + $grace );

	if ( $locked ) {
		sokal_cache_unlock( $tkey );
	}

	return $html;
}

/* ------------------------------------------------------------------
 * Invalidation: only real content (posts and pages), never revisions or
 * autosaves. Published-post changes also purge Cloudflare.
 * ------------------------------------------------------------------ */

function sokal_is_cache_post( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
		return false;
	}
	return in_array( $post->post_type, array( 'post', 'page' ), true );
}

add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( ( 'publish' === $new || 'publish' === $old ) && sokal_is_cache_post( $post ) ) {
		sokal_flush_cache();
		sokal_cf_queue_post( $post );
	}
}, 10, 3 );

add_action( 'save_post', function ( $id, $post ) {
	if ( $post && 'publish' === $post->post_status && sokal_is_cache_post( $post ) ) {
		sokal_flush_cache();
	}
}, 10, 2 );

// Remember the public URL of a published post before it is changed or deleted,
// so an unpublish/trash/slug change purges the URL visitors actually had.
add_action( 'pre_post_update', function ( $id ) {
	$post = get_post( $id );
	if ( $post && 'publish' === $post->post_status && sokal_is_cache_post( $post ) ) {
		sokal_cf_queue( array( get_permalink( $post ) ) );
	}
}, 10, 1 );

add_action( 'before_delete_post', function ( $id ) {
	$post = get_post( $id );
	if ( $post && 'publish' === $post->post_status && sokal_is_cache_post( $post ) ) {
		sokal_cf_queue_post( $post );
	}
}, 10, 1 );

add_action( 'deleted_post', function ( $id, $post = null ) {
	if ( $post instanceof WP_Post ) {
		if ( 'publish' === $post->post_status && ! wp_is_post_revision( $post ) && in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			sokal_flush_cache();
		}
		return;
	}
	sokal_flush_cache(); // WP < 5.5 passes no post object: play safe.
}, 10, 2 );

foreach ( array( 'created_term', 'edited_term', 'delete_term', 'wp_update_nav_menu', 'switch_theme' ) as $sokal_hook ) {
	add_action( $sokal_hook, 'sokal_flush_cache' );
}
unset( $sokal_hook );

add_action( 'updated_option', function ( $option ) {
	if ( 'themesbazar' === $option || 'sokal_settings' === $option || 'sidebars_widgets' === $option || 0 === strpos( $option, 'widget_' ) ) {
		sokal_flush_cache();
	}
} );

/* ------------------------------------------------------------------
 * Cloudflare purge (optional: needs cf_zone_id and cf_api_token).
 * ------------------------------------------------------------------ */

/**
 * Purge URLs from Cloudflare's cache. Non-blocking; a no-op when the zone
 * ID or API token is not set. At most 30 URLs per call (Cloudflare limit).
 *
 * @param array $urls Absolute URLs.
 * @return bool True when a request was sent.
 */
function sokal_cf_purge( array $urls ) {
	if ( ! function_exists( 'sokal_opt' ) ) {
		return false;
	}
	$zone  = trim( (string) sokal_opt( 'cf_zone_id', '' ) );
	$token = trim( (string) sokal_opt( 'cf_api_token', '' ) );
	if ( '' === $zone || '' === $token ) {
		return false;
	}
	$urls = array_values( array_slice( array_unique( array_filter( array_map( 'esc_url_raw', $urls ) ) ), 0, 30 ) );
	if ( ! $urls ) {
		return false;
	}
	wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/' . rawurlencode( $zone ) . '/purge_cache', array(
		'blocking' => false,
		'timeout'  => 3,
		'headers'  => array(
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
		),
		'body'     => wp_json_encode( array( 'files' => $urls ) ),
	) );
	return true;
}

/** Collect URLs during the request; one purge call is sent on shutdown. */
function sokal_cf_queue( array $urls = array(), $send = false ) {
	static $queue = array();
	static $hooked = false;
	if ( $send ) {
		$out   = $queue;
		$queue = array();
		return $out;
	}
	foreach ( $urls as $u ) {
		if ( is_string( $u ) && '' !== $u ) {
			$queue[] = $u;
		}
	}
	if ( ! $hooked && $queue ) {
		$hooked = true;
		add_action( 'shutdown', function () {
			$urls = sokal_cf_queue( array(), true );
			if ( $urls ) {
				sokal_cf_purge( $urls );
			}
		} );
	}
	return $queue;
}

/** Queue a post's permalink, the home page, its category pages and the feed. */
function sokal_cf_queue_post( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}
	$urls = array( home_url( '/' ), get_feed_link() );
	if ( 'publish' === $post->post_status ) {
		$urls[] = get_permalink( $post );
	}
	if ( 'post' === $post->post_type ) {
		foreach ( (array) get_the_category( $post->ID ) as $cat ) {
			$urls[] = get_category_link( $cat->term_id );
		}
	}
	sokal_cf_queue( $urls );
}

/* ------------------------------------------------------------------
 * Query tuning
 * ------------------------------------------------------------------ */

// index.php never shows the main loop, so /page/N/ of the home page is a
// duplicate of the home page. Send it there.
add_action( 'template_redirect', function () {
	if ( is_home() && is_paged() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() ) {
		return;
	}
	if ( $q->is_main_query() ) {
		// The home page only needs the first post (header og:image). Fetch just that one.
		if ( $q->is_home() && ! $q->is_paged() ) {
			$q->set( 'posts_per_page', 1 );
			$q->set( 'no_found_rows', true );
			$q->set( 'update_post_term_cache', false );
		}
		return;
	}
	// Side queries that don't paginate: skip the SQL_CALC_FOUND_ROWS count,
	// unless the caller decided for itself.
	$caller = is_array( $q->query ) ? $q->query : array();
	if ( array_key_exists( 'no_found_rows', $caller ) || '' !== $q->get( 'no_found_rows' ) ) {
		return;
	}
	if ( $q->get( 'offset' ) || $q->get( 'page' ) || $q->get( 'paged' ) ) {
		return;
	}
	$q->set( 'no_found_rows', true );
} );

// Load all story photos of a loop in one query instead of one per post.
add_action( 'loop_start', function ( $q ) {
	if ( ! is_admin() && $q instanceof WP_Query && $q->post_count > 1 ) {
		update_post_thumbnail_cache( $q );
	}
} );

/* ------------------------------------------------------------------
 * Most-read counter: the browser POSTs once per article per session
 * (js/main.js), so counting keeps working when pages are edge-cached.
 *
 * Cloudflare: add a Rate Limiting rule so bots can't inflate counts, e.g.
 *   (http.request.uri.path wildcard "/wp-json/sokal/v1/view/*" and
 *    http.request.method eq "POST")
 *   -> 10 requests per 60 seconds per IP, action: Block for 60 seconds.
 * The object-cache throttle below is only a light second line.
 * ------------------------------------------------------------------ */

/** REST URL of the view counter for a post ('' when not a published post). */
function sokal_view_url( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_queried_object_id();
	if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		return '';
	}
	return esc_url_raw( rest_url( 'sokal/v1/view/' . $post_id ) );
}

function sokal_view_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' ) as $h ) {
		if ( ! empty( $_SERVER[ $h ] ) ) {
			$ip = filter_var( wp_unslash( $_SERVER[ $h ] ), FILTER_VALIDATE_IP );
			if ( $ip ) {
				return $ip;
			}
		}
	}
	return '';
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sokal/v1', '/view/(?P<id>\d+)', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // public by design; the route pattern validates id
		'args'                => array(
			'id' => array(
				'sanitize_callback' => 'absint',
			),
		),
		'callback'            => function ( $req ) {
			$id   = absint( $req['id'] );
			$post = $id ? get_post( $id ) : null;
			if ( ! $post || 'publish' !== $post->post_status || 'post' !== $post->post_type ) {
				return new WP_Error( 'sokal_bad_post', 'Not found', array( 'status' => 404 ) );
			}

			$res = new WP_REST_Response( null, 204 );
			$res->header( 'Cache-Control', 'no-store' );

			// Light throttle: one count per IP per article per 30 seconds.
			if ( wp_using_ext_object_cache() ) {
				$ip = sokal_view_client_ip();
				if ( $ip && ! wp_cache_add( 'v_' . md5( $ip . '|' . $id ), 1, 'sokal_views', 30 ) ) {
					return $res;
				}
			}

			global $wpdb;
			$rows = $wpdb->query( $wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = 'post_views_count'",
				$id
			) );
			if ( ! $rows ) {
				add_post_meta( $id, 'post_views_count', 1, true );
			}
			wp_cache_delete( $id, 'post_meta' );

			return $res;
		},
	) );
} );

/* ------------------------------------------------------------------
 * Font preload (one mechanism: the HTTP Link header, which Cloudflare
 * turns into a 103 Early Hint).
 * ------------------------------------------------------------------ */

/** The body font file: woff2 when it exists, else woff. Returns array( url, type ). */
function sokal_font() {
	static $font = null;
	if ( null === $font ) {
		$dir  = get_template_directory() . '/fonts/';
		$uri  = get_template_directory_uri() . '/fonts/';
		$font = file_exists( $dir . 'solaiman-lipi.woff2' )
			? array( 'url' => $uri . 'solaiman-lipi.woff2', 'type' => 'font/woff2' )
			: array( 'url' => $uri . 'solaiman-lipi.woff', 'type' => 'font/woff' );
	}
	return $font;
}

/** URL of the body font file (woff2 if present, else woff). */
function sokal_font_url() {
	$f = sokal_font();
	return $f['url'];
}

/** MIME type matching sokal_font_url(): 'font/woff2' or 'font/woff'. */
function sokal_font_type() {
	$f = sokal_font();
	return $f['type'];
}

/** True for normal HTML page views (not feeds, robots.txt, trackbacks, REST, favicon). */
function sokal_is_html_view() {
	if ( is_feed() || is_robots() || is_trackback() || ( function_exists( 'is_favicon' ) && is_favicon() ) ) {
		return false;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	return true;
}

/* ------------------------------------------------------------------
 * Edge cache headers (Cloudflare). Never for logged-in users, commenters,
 * password-protected posts, previews or non-GET/HEAD requests.
 * ------------------------------------------------------------------ */

add_action( 'template_redirect', function () {
	if ( headers_sent() ) {
		return;
	}
	if ( sokal_is_html_view() ) {
		header( 'Link: <' . esc_url_raw( sokal_font_url() ) . '>; rel=preload; as=font; type="' . sokal_font_type() . '"; crossorigin', false );
	}

	$method        = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
	$has_wp_cookie = false;
	foreach ( array_keys( $_COOKIE ) as $c ) {
		if ( 0 === strpos( $c, 'wordpress_logged_in_' ) || 0 === strpos( $c, 'comment_author_' ) || 0 === strpos( $c, 'wp-postpass_' ) ) {
			$has_wp_cookie = true;
			break;
		}
	}
	if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) || $has_wp_cookie || is_user_logged_in() || is_preview() || is_customize_preview() || ( is_singular() && post_password_required() ) ) {
		header( 'Cache-Control: private, no-store, max-age=0' );
		return;
	}
	// 404s and searches: a short edge copy absorbs bot floods; browsers always revalidate.
	if ( is_404() || is_search() ) {
		header( 'Cache-Control: public, max-age=0, s-maxage=60' );
		return;
	}
	$edge = function_exists( 'sokal_int' ) && function_exists( 'sokal_opt' ) ? sokal_int( sokal_opt( 'edge_ttl', 120 ), 0, DAY_IN_SECONDS ) : 120;
	// Browsers revalidate after 1 minute; Cloudflare keeps the page for edge_ttl seconds.
	header( 'Cache-Control: public, max-age=60, s-maxage=' . $edge . ', stale-while-revalidate=60, stale-if-error=86400' );
}, 99 );

/* ------------------------------------------------------------------
 * Remove unused WordPress output
 * ------------------------------------------------------------------ */

add_filter( 'should_load_separate_core_block_assets', '__return_true' );

add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
} );

add_action( 'wp_enqueue_scripts', function () {
	// Block styles are only needed where post content is shown.
	if ( ! is_singular() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}
}, 100 );

/* New uploads: generate WebP sub-sizes when the server can. */
add_filter( 'image_editor_output_format', function ( $formats ) {
	if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
	}
	return $formats;
} );

/* ------------------------------------------------------------------
 * Prefetch: speculation rules (core on WP 6.8+, our own otherwise).
 * Pagination is excluded: those URLs redirect or are rarely visited.
 * main.js adds a hover-prefetch fallback for other browsers.
 * ------------------------------------------------------------------ */

if ( function_exists( 'wp_get_speculation_rules_configuration' ) ) {
	add_filter( 'wp_speculation_rules_configuration', function ( $config ) {
		if ( is_array( $config ) ) {
			$config['eagerness'] = 'moderate';
		}
		return $config;
	} );
	add_filter( 'wp_speculation_rules_href_exclude_paths', function ( $paths ) {
		return array_merge( (array) $paths, array( '/page/*', '/*/page/*' ) );
	} );
} else {
	add_action( 'wp_footer', function () {
		if ( is_user_logged_in() ) {
			return;
		}
		$rules = array(
			'prefetch' => array(
				array(
					'source'    => 'document',
					'where'     => array(
						'and' => array(
							array( 'href_matches' => '/*' ),
							array( 'not' => array( 'href_matches' => array( '/wp-admin/*', '/wp-login.php*', '/wp-json/*', '/*\\?*', '/*.php*', '/page/*', '/*/page/*' ) ) ),
							array( 'not' => array( 'selector_matches' => 'a[rel~="nofollow"], a[target="_blank"]' ) ),
						),
					),
					'eagerness' => 'moderate',
				),
			),
		);
		echo '<script type="speculationrules">' . wp_json_encode( $rules ) . '</script>' . "\n";
	} );
}

/* ------------------------------------------------------------------
 * Admin bar readout: queries, page time, peak memory (admins only).
 * ------------------------------------------------------------------ */

add_action( 'admin_bar_menu', function ( $bar ) {
	if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$bar->add_node( array(
		'id'    => 'sokal-perf',
		'title' => esc_html( sprintf(
			'%d q · %ss · %sMB',
			get_num_queries(),
			timer_stop( 0, 2 ),
			number_format( memory_get_peak_usage() / 1048576, 1 )
		) ),
	) );
}, 9999 );
