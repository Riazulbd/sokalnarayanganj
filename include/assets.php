<?php
/**
 * Front-end CSS and JS. Owner: assets agent.
 *
 * Styles, in cascade order:
 *   sokal-bootstrap    css/bootstrap.min.css     (file, cacheable)
 *   sokal-font-awesome css/font-awesome.min.css  (file, cacheable)
 *   sokal-style        inline: responsive.css, menu.css, style.css,
 *                      css/components/*.css (alphabetical), then the
 *                      dynamic option CSS from css/dynamic-css.php.
 *
 * Scripts (footer, defer, no jQuery):
 *   sokal-main         js/main.js, preceded by window.sokal = { view, home, i18n }
 *   sokal-c-<name>     every js/components/<name>.js, depends on sokal-main
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SOKAL_DYNAMIC_CSS_LIB' ) ) {
	define( 'SOKAL_DYNAMIC_CSS_LIB', true );
}
if ( file_exists( get_template_directory() . '/css/dynamic-css.php' ) ) {
	require_once get_template_directory() . '/css/dynamic-css.php';
}

/** filemtime() that never warns; falls back to the theme version. */
function sokal_asset_ver( $file ) {
	$t = @filemtime( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	return $t ? (string) $t : (string) wp_get_theme()->get( 'Version' );
}

/** Theme CSS files that are inlined, in cascade order: path => URL of its directory. */
function sokal_inline_css_files() {
	$dir   = get_template_directory();
	$uri   = get_template_directory_uri();
	$files = array(
		$dir . '/css/responsive.css' => $uri . '/css/',
		$dir . '/css/menu.css'       => $uri . '/css/',
		$dir . '/style.css'          => $uri . '/',
	);
	$components = glob( $dir . '/css/components/*.css' );
	if ( $components ) {
		sort( $components, SORT_STRING );
		foreach ( $components as $file ) {
			$files[ $file ] = $uri . '/css/components/';
		}
	}
	return $files;
}

/** Resolve a relative url() against the URL of the directory holding the CSS file. */
function sokal_css_abs_url( $path, $base ) {
	$path = trim( $path );
	if ( '' === $path || preg_match( '#^(?:[a-z][a-z0-9+.-]*:|//|/|\#)#i', $path ) ) {
		return $path; // data:, http(s):, protocol-relative, root-relative or fragment.
	}
	$parts  = wp_parse_url( $base );
	$origin = ( isset( $parts['scheme'] ) ? $parts['scheme'] . ':' : '' ) . '//' . ( isset( $parts['host'] ) ? $parts['host'] : '' ) . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
	$segs   = explode( '/', trim( isset( $parts['path'] ) ? $parts['path'] : '', '/' ) );
	$query  = '';
	if ( preg_match( '/^([^?#]*)([?#].*)$/', $path, $m ) ) {
		$path  = $m[1];
		$query = $m[2];
	}
	foreach ( explode( '/', $path ) as $seg ) {
		if ( '..' === $seg ) {
			array_pop( $segs );
		} elseif ( '.' !== $seg && '' !== $seg ) {
			$segs[] = $seg;
		}
	}
	$segs = array_filter( $segs, 'strlen' );
	return $origin . '/' . implode( '/', $segs ) . $query;
}

/** Rewrite url()s to absolute and minify lightly. */
function sokal_prepare_css( $css, $base ) {
	// Drop the theme header and all other comments.
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	$css = preg_replace_callback(
		'/url\(\s*([\'"]?)(.*?)\1\s*\)/i',
		function ( $m ) use ( $base ) {
			$q = $m[1] ? $m[1] : '';
			return 'url(' . $q . sokal_css_abs_url( $m[2], $base ) . $q . ')';
		},
		$css
	);
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{};,])\s*/', '$1', $css );
	$css = str_replace( ';}', '}', $css );
	// Remove empty rules such as ".tab-header .tab-content{}".
	$css = preg_replace( '/(^|[{}])[^{}@]+\{\}/', '$1', $css );
	return trim( $css );
}

/** The inlined theme CSS, cached in a transient keyed by file mtimes. */
function sokal_inline_css() {
	$files = sokal_inline_css_files();
	$sig   = get_template_directory_uri();
	foreach ( $files as $file => $base ) {
		$sig .= '|' . $file . ':' . sokal_asset_ver( $file );
	}
	$key   = md5( $sig );
	$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

	if ( ! $debug ) {
		$cached = get_transient( 'sokal_inline_css' );
		if ( is_array( $cached ) && isset( $cached['key'], $cached['css'] ) && $cached['key'] === $key ) {
			return (string) $cached['css'];
		}
	}

	$out = '';
	foreach ( $files as $file => $base ) {
		if ( ! is_readable( $file ) ) {
			continue;
		}
		$css = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$out .= sokal_prepare_css( $css, $base ) . "\n";
	}
	// Nothing inside a <style> element may close it.
	$out = str_ireplace( '</style', '<\/style', $out );

	if ( ! $debug ) {
		set_transient( 'sokal_inline_css', array( 'key' => $key, 'css' => $out ), WEEK_IN_SECONDS );
	}
	return $out;
}

/** Visitor-facing strings for the front-end scripts (Bengali). */
function sokal_js_i18n() {
	return array(
		'copied'    => 'লিংক কপি হয়েছে',
		'copyFail'  => 'কপি করা যায়নি',
		'loading'   => 'লোড হচ্ছে…',
		'error'     => 'কিছু একটা সমস্যা হয়েছে',
		'backToTop' => 'উপরে যান',
		'menu'      => 'মেনু',
		'close'     => 'বন্ধ করুন',
	);
}

function sokal_enqueue_assets() {
	$uri = get_template_directory_uri();
	$dir = get_template_directory();

	/* ---------- Styles ---------- */
	wp_enqueue_style( 'sokal-bootstrap', $uri . '/css/bootstrap.min.css', array(), sokal_asset_ver( $dir . '/css/bootstrap.min.css' ) );
	wp_enqueue_style( 'sokal-font-awesome', $uri . '/css/font-awesome.min.css', array( 'sokal-bootstrap' ), sokal_asset_ver( $dir . '/css/font-awesome.min.css' ) );

	// No src: WordPress prints only the inline CSS for this handle.
	wp_register_style( 'sokal-style', false, array( 'sokal-bootstrap', 'sokal-font-awesome' ), sokal_asset_ver( $dir . '/style.css' ) );
	wp_enqueue_style( 'sokal-style' );
	wp_add_inline_style( 'sokal-style', sokal_inline_css() );

	if ( function_exists( 'sokal_dynamic_css' ) ) {
		$dynamic = str_ireplace( '</style', '<\/style', wp_strip_all_tags( sokal_dynamic_css() ) );
		wp_add_inline_style( 'sokal-style', $dynamic );
		// header.php's legacy include of css/dynamic-css.php becomes a no-op.
		do_action( 'sokal_dynamic_css_done' );
	}

	/* ---------- Scripts ---------- */
	$args = array(
		'in_footer' => true,
		'strategy'  => 'defer',
	);
	wp_enqueue_script( 'sokal-main', $uri . '/js/main.js', array(), sokal_asset_ver( $dir . '/js/main.js' ), $args );

	$view = '';
	if ( is_singular( 'post' ) && function_exists( 'sokal_view_url' ) ) {
		$view = (string) sokal_view_url( get_queried_object_id() );
	}
	$data = array(
		'view' => $view ? esc_url_raw( $view ) : '',
		'home' => esc_url_raw( home_url( '/' ) ),
		'i18n' => sokal_js_i18n(),
	);
	wp_add_inline_script( 'sokal-main', 'window.sokal=' . wp_json_encode( $data ) . ';', 'before' );

	$components = glob( $dir . '/js/components/*.js' );
	if ( $components ) {
		sort( $components, SORT_STRING );
		foreach ( $components as $file ) {
			$name = sanitize_key( basename( $file, '.js' ) );
			wp_enqueue_script( 'sokal-c-' . $name, $uri . '/js/components/' . basename( $file ), array( 'sokal-main' ), sokal_asset_ver( $file ), $args );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'sokal_enqueue_assets' );

/** Front end never needs jQuery from this theme; drop the old datepicker if anything re-adds it. */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_script( 'jquery-ui-datepicker' );
}, 99 );
