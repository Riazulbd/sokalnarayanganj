<?php
/**
 * Share picture: a branded photo card (headline, date, logo) built in the
 * browser by js/share-picture.js. The server only prints data-attributes, so
 * the markup is cache-safe (no per-user data).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available share-picture styles (key => admin label).
 *
 * @return array
 */
function sokal_share_picture_styles() {
	return array(
		'card'       => 'Photo card – framed photo, headline, date, link',
		'panel'      => 'Photo card – photo + headline panel',
		'photostrip' => 'Photo + logo strip (wide)',
		'slogan'     => 'Photo + slogan band (wide)',
		'classic' => 'Classic',
		'dark'    => 'Dark',
		'light'   => 'Light',
		'band'    => 'Colour band',
		'strip'   => 'Wide strip',
	);
}

/** Register the script on both front end and admin so the settings page can enqueue it for its live preview. */
function sokal_share_picture_register() {
	$file = get_template_directory() . '/js/share-picture.js';
	$ver  = file_exists( $file ) ? (string) filemtime( $file ) : null;
	if ( ! wp_script_is( 'sokal-share-picture', 'registered' ) ) {
		wp_register_script(
			'sokal-share-picture',
			get_template_directory_uri() . '/js/share-picture.js',
			array(),
			$ver,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'sokal_share_picture_register', 5 );
add_action( 'admin_enqueue_scripts', 'sokal_share_picture_register', 5 );

/** Enqueue on single posts only. */
function sokal_share_picture_enqueue() {
	if ( is_single() ) {
		wp_enqueue_script( 'sokal-share-picture' );
	}
}
add_action( 'wp_enqueue_scripts', 'sokal_share_picture_enqueue', 20 );

/**
 * Validate a hex colour, falling back to $default.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Fallback.
 * @return string
 */
function sokal_share_picture_hex( $value, $default ) {
	$hex = sanitize_hex_color( (string) $value );
	return $hex ? $hex : $default;
}

/**
 * Header logo URL: new setting first, then the legacy Redux value.
 *
 * @return string
 */
function sokal_share_picture_logo() {
	$logo = function_exists( 'sokal_opt' ) ? (string) sokal_opt( 'header_logo', '' ) : '';
	if ( '' === $logo ) {
		global $themesbazar;
		if ( is_array( $themesbazar ) && ! empty( $themesbazar['logo_upload']['url'] ) ) {
			$logo = (string) $themesbazar['logo_upload']['url'];
		}
	}
	return $logo;
}

/**
 * Print the "ছবি ডাউনলোড" / "ছবি শেয়ার" buttons for a post.
 *
 * @param int|WP_Post|null $post Post (defaults to current).
 */
function sokal_share_picture_buttons( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}

	$opt = function_exists( 'sokal_opt' ) ? 'sokal_opt' : null;

	$styles = sokal_share_picture_styles();
	$style  = $opt ? (string) sokal_opt( 'share_style', 'classic' ) : 'classic';
	if ( ! isset( $styles[ $style ] ) ) {
		$style = 'classic';
	}

	$image = '';
	$thumb = get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$src = wp_get_attachment_image_src( $thumb, 'large' );
		if ( $src ) {
			$image = $src[0];
		}
	}

	$title = wp_strip_all_tags( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) );

	// English, untranslated date run through the theme's Bengali converter.
	$raw  = get_post_time( 'l, j F Y', false, $post, false );
	$date = function_exists( 'bn1_date' ) ? bn1_date( $raw ) : get_the_date( 'l, j F Y', $post );

	$slug = urldecode( (string) $post->post_name );
	$slug = sanitize_file_name( '' !== $slug ? $slug : 'news-' . $post->ID );

	$site   = function_exists( 'sokal_site_name' ) ? sokal_site_name() : get_bloginfo( 'name' );
	$bottom = $opt ? (string) sokal_opt( 'share_bottom_line', 'বিস্তারিত কমেন্টে' ) : 'বিস্তারিত কমেন্টে';

	$attrs = array(
		'data-image'       => esc_url_raw( $image ),
		'data-title'       => $title,
		'data-date'        => $date,
		'data-logo'        => esc_url_raw( sokal_share_picture_logo() ),
		'data-site-name'   => $site,
		'data-style'       => $style,
		'data-bg'          => sokal_share_picture_hex( $opt ? sokal_opt( 'share_bg' ) : '', '#ffffff' ),
		'data-text'        => sokal_share_picture_hex( $opt ? sokal_opt( 'share_text' ) : '', '#111111' ),
		'data-accent'      => sokal_share_picture_hex( $opt ? sokal_opt( 'share_accent' ) : '', '#c4161c' ),
		'data-bottom-line' => $bottom,
		'data-site-url'    => (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ),
		'data-tagline'     => (string) get_bloginfo( 'description' ),
		'data-slug'        => $slug,
	);

	echo '<div class="sokal-sp"';
	foreach ( $attrs as $k => $v ) {
		echo ' ' . $k . '="' . esc_attr( $v ) . '"';
	}
	echo '>';
	printf(
		'<button type="button" class="sokal-sp__btn sokal-sp__btn--download" data-sokal-sp="download">%1$s<span>%2$s</span></button>',
		function_exists( 'sokal_share_icon' ) ? sokal_share_icon( 'download' ) : '', // static SVG.
		esc_html( 'ছবি ডাউনলোড' )
	);
	printf(
		'<button type="button" class="sokal-sp__btn sokal-sp__btn--share" data-sokal-sp="share" hidden>%1$s<span>%2$s</span></button>',
		function_exists( 'sokal_share_icon' ) ? sokal_share_icon( 'share' ) : '', // static SVG.
		esc_html( 'ছবি শেয়ার' )
	);
	echo '<span class="sokal-sp__status" role="status" aria-live="polite"></span>';
	echo '</div>';
}
