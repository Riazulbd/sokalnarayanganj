<?php
/**
 * SEO: page language, Bengali document titles, favicon fallback,
 * meta description, canonical (non-singular), Open Graph and Twitter cards.
 *
 * Everything is output through hooks; header.php only calls wp_head().
 *
 * @package bdithost21
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bengali digits, using the helper when it exists.
 *
 * @param int|string $n Number.
 * @return string
 */
function sokal_seo_bn( $n ) {
	return function_exists( 'bn_number' ) ? bn_number( (string) $n ) : (string) $n;
}

/**
 * True when a dedicated SEO plugin is handling meta tags.
 *
 * @return bool
 */
function sokal_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| class_exists( 'The_SEO_Framework\\Load' )
		|| defined( 'SLIM_SEO_VER' );
	return (bool) apply_filters( 'sokal_seo_plugin_active', $active );
}

/* -------------------------------------------------------------------------
 * 1. <html lang="bn-BD">
 * ---------------------------------------------------------------------- */
function sokal_seo_language_attributes( $output ) {
	$output = (string) $output;
	if ( preg_match( '/(^|\s)lang="[^"]*"/', $output ) ) {
		$output = preg_replace( '/(^|\s)lang="[^"]*"/', '$1lang="bn-BD"', $output );
	} else {
		$output = trim( 'lang="bn-BD" ' . $output );
	}
	if ( false !== strpos( $output, 'xml:lang=' ) ) {
		$output = preg_replace( '/xml:lang="[^"]*"/', 'xml:lang="bn-BD"', $output );
	}
	return $output;
}
add_filter( 'language_attributes', 'sokal_seo_language_attributes', 20 );

/* -------------------------------------------------------------------------
 * 2. Bengali document titles (theme declares title-tag support).
 * ---------------------------------------------------------------------- */
function sokal_seo_title_parts( $parts ) {
	if ( ! is_array( $parts ) ) {
		$parts = array();
	}

	$site = function_exists( 'sokal_site_name' ) ? sokal_site_name() : get_bloginfo( 'name', 'display' );

	if ( is_404() ) {
		$parts['title'] = 'পাতা পাওয়া যায়নি';
	} elseif ( is_search() ) {
		$parts['title'] = 'অনুসন্ধান: ' . get_search_query( false );
	}

	if ( is_front_page() && ! is_singular() ) {
		// Old wpdocs_hack_wp_title_for_home behaviour: "Site name – Tagline".
		$parts['title'] = $site;
		$tagline        = get_bloginfo( 'description', 'display' );
		if ( '' !== trim( (string) $tagline ) ) {
			$parts['tagline'] = $tagline;
		} else {
			unset( $parts['tagline'] );
		}
		unset( $parts['site'] );
	} elseif ( is_front_page() ) {
		// Static front page.
		$parts['title'] = $site;
		$tagline        = get_bloginfo( 'description', 'display' );
		if ( '' !== trim( (string) $tagline ) ) {
			$parts['tagline'] = $tagline;
		}
		unset( $parts['site'] );
	} elseif ( isset( $parts['site'] ) ) {
		$parts['site'] = $site;
	}

	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $paged >= 2 && ! is_404() ) {
		$parts['page'] = 'পাতা ' . sokal_seo_bn( $paged );
	} else {
		unset( $parts['page'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'sokal_seo_title_parts', 20 );

function sokal_seo_title_separator( $sep ) {
	return '|';
}
add_filter( 'document_title_separator', 'sokal_seo_title_separator', 20 );

/* -------------------------------------------------------------------------
 * 3. Favicon fallback when no WP Site Icon is set.
 * ---------------------------------------------------------------------- */
function sokal_seo_favicon() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return; // Core prints the site icon tags.
	}
	$url = function_exists( 'sokal_opt' ) ? (string) sokal_opt( 'favicon' ) : '';
	if ( '' === trim( $url ) ) {
		global $themesbazar;
		if ( is_array( $themesbazar ) && isset( $themesbazar['favicon']['url'] ) ) {
			$url = (string) $themesbazar['favicon']['url'];
		}
	}
	$url = trim( $url );
	if ( '' === $url ) {
		return;
	}
	echo '<link rel="icon" href="' . esc_url( $url ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $url ) . '">' . "\n";
}
add_action( 'wp_head', 'sokal_seo_favicon', 2 );

/* -------------------------------------------------------------------------
 * 4. Meta description, canonical, Open Graph, Twitter card.
 * ---------------------------------------------------------------------- */

/**
 * Plain text, tags/shortcodes stripped, whitespace collapsed, ~160 chars.
 *
 * @param string $text Raw text/HTML.
 * @param int    $len  Max length.
 * @return string
 */
function sokal_seo_clean_text( $text, $len = 160 ) {
	$text = strip_shortcodes( (string) $text );
	$text = wp_strip_all_tags( $text, true );
	$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( '' === $text ) {
		return '';
	}
	$length = function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : strlen( $text );
	if ( $length > $len ) {
		$cut  = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $len, 'UTF-8' ) : substr( $text, 0, $len );
		$last = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ', 0, 'UTF-8' ) : strrpos( $cut, ' ' );
		if ( false !== $last && $last > $len * 0.6 ) {
			$cut = function_exists( 'mb_substr' ) ? mb_substr( $cut, 0, $last, 'UTF-8' ) : substr( $cut, 0, $last );
		}
		$text = rtrim( $cut, " \t\n\r\0\x0B,.;:-–—।" ) . '…';
	}
	return $text;
}

/**
 * Link for page N of an archive base URL.
 *
 * @param string $base  Base URL.
 * @param int    $paged Page number.
 * @return string
 */
function sokal_seo_paged_url( $base, $paged ) {
	if ( $paged < 2 || '' === $base ) {
		return $base;
	}
	if ( get_option( 'permalink_structure' ) && false === strpos( $base, '?' ) ) {
		return trailingslashit( $base ) . user_trailingslashit( 'page/' . (int) $paged, 'paged' );
	}
	return add_query_arg( 'paged', (int) $paged, $base );
}

/**
 * Canonical URL for the current non-singular view, or '' when none applies.
 *
 * @return string
 */
function sokal_seo_archive_url() {
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	$base  = '';

	if ( is_front_page() || is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$base       = ( is_home() && ! is_front_page() && $posts_page ) ? get_permalink( $posts_page ) : home_url( '/' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			$base = is_wp_error( $link ) ? '' : $link;
		}
	} elseif ( is_author() ) {
		$base = get_author_posts_url( (int) get_queried_object_id() );
	} elseif ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( get_query_var( 'post_type' ) );
		$base = $link ? $link : '';
	} elseif ( is_day() ) {
		$base = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
	} elseif ( is_month() ) {
		$base = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
	} elseif ( is_year() ) {
		$base = get_year_link( get_query_var( 'year' ) );
	}

	return $base ? sokal_seo_paged_url( (string) $base, $paged ) : '';
}

/**
 * Collect the tags for the current request.
 *
 * @return array List of array( attr, key, value ).
 */
function sokal_seo_collect() {
	$site   = function_exists( 'sokal_site_name' ) ? sokal_site_name() : get_bloginfo( 'name' );
	$site   = html_entity_decode( wp_strip_all_tags( (string) $site ), ENT_QUOTES, 'UTF-8' );
	$tags   = array();
	$desc   = '';
	$title  = '';
	$url    = '';
	$image  = null;
	$type   = 'website';

	if ( is_singular() && ! is_front_page() ) {
		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return array();
		}
		$type  = 'article';
		$title = html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' );
		$raw   = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
		$desc  = sokal_seo_clean_text( $raw );
		$url   = get_permalink( $post );

		$thumb_id = (int) get_post_thumbnail_id( $post );
		if ( $thumb_id ) {
			$src = wp_get_attachment_image_src( $thumb_id, 'single-hero' );
			if ( ! $src ) {
				$src = wp_get_attachment_image_src( $thumb_id, 'large' );
			}
			if ( $src ) {
				$alt   = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
				$image = array(
					'url' => $src[0],
					'w'   => (int) $src[1],
					'h'   => (int) $src[2],
					'alt' => '' !== $alt ? $alt : $title,
				);
			}
		}
	} else {
		if ( is_search() || is_404() ) {
			// Description only; no canonical or OG for thin pages.
			$desc = sokal_seo_clean_text( get_bloginfo( 'description' ) );
			return '' !== $desc ? array( array( 'name', 'description', $desc ) ) : array();
		}
		$title = html_entity_decode( wp_get_document_title(), ENT_QUOTES, 'UTF-8' );
		if ( is_category() || is_tag() || is_tax() ) {
			$desc = sokal_seo_clean_text( term_description() );
		} elseif ( is_author() ) {
			$desc = sokal_seo_clean_text( get_the_author_meta( 'description', (int) get_queried_object_id() ) );
		}
		if ( '' === $desc ) {
			$desc = sokal_seo_clean_text( get_bloginfo( 'description' ) );
		}
		$url = sokal_seo_archive_url();
		if ( is_front_page() && is_singular() ) {
			$url = get_permalink( get_queried_object_id() );
		}
		$logo = function_exists( 'sokal_opt' ) ? trim( (string) sokal_opt( 'header_logo' ) ) : '';
		if ( '' !== $logo ) {
			$image = array( 'url' => $logo, 'w' => 0, 'h' => 0, 'alt' => $site );
		}
	}

	if ( '' !== $desc ) {
		$tags[] = array( 'name', 'description', $desc );
	}

	$tags[] = array( 'property', 'og:locale', 'bn_BD' );
	$tags[] = array( 'property', 'og:type', $type );
	$tags[] = array( 'property', 'og:site_name', $site );
	if ( '' !== $title ) {
		$tags[] = array( 'property', 'og:title', $title );
	}
	if ( '' !== $desc ) {
		$tags[] = array( 'property', 'og:description', $desc );
	}
	if ( $url ) {
		$tags[] = array( 'property', 'og:url', $url );
	}
	if ( $image ) {
		$tags[] = array( 'property', 'og:image', $image['url'] );
		if ( 0 === strpos( $image['url'], 'https://' ) ) {
			$tags[] = array( 'property', 'og:image:secure_url', $image['url'] );
		}
		if ( $image['w'] && $image['h'] ) {
			$tags[] = array( 'property', 'og:image:width', (string) $image['w'] );
			$tags[] = array( 'property', 'og:image:height', (string) $image['h'] );
		}
		if ( '' !== $image['alt'] ) {
			$tags[] = array( 'property', 'og:image:alt', $image['alt'] );
		}
	}

	if ( 'article' === $type ) {
		$post   = get_queried_object();
		$tags[] = array( 'property', 'article:published_time', get_post_time( 'c', true, $post ) );
		$tags[] = array( 'property', 'article:modified_time', get_post_modified_time( 'c', true, $post ) );
		$cats   = get_the_category( $post->ID );
		if ( ! empty( $cats ) ) {
			$tags[] = array( 'property', 'article:section', $cats[0]->name );
		}
	}

	$app_id = function_exists( 'sokal_opt' ) ? trim( (string) sokal_opt( 'fb_app_id' ) ) : '';
	if ( '' !== $app_id ) {
		$tags[] = array( 'property', 'fb:app_id', $app_id );
	}

	$tags[] = array( 'name', 'twitter:card', $image && 'article' === $type ? 'summary_large_image' : 'summary' );
	if ( '' !== $title ) {
		$tags[] = array( 'name', 'twitter:title', $title );
	}
	if ( '' !== $desc ) {
		$tags[] = array( 'name', 'twitter:description', $desc );
	}
	if ( $image ) {
		$tags[] = array( 'name', 'twitter:image', $image['url'] );
	}

	return apply_filters( 'sokal_seo_tags', $tags );
}

function sokal_seo_head() {
	if ( is_feed() || is_robots() || sokal_seo_plugin_active() ) {
		return;
	}

	echo "\n<!-- sokal seo -->\n";

	// Core prints rel=canonical on singular views; add it elsewhere.
	if ( ! is_singular() && ! is_search() && ! is_404() ) {
		$canonical = sokal_seo_archive_url();
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		}
	}

	foreach ( sokal_seo_collect() as $tag ) {
		if ( ! is_array( $tag ) || 3 !== count( $tag ) || '' === (string) $tag[2] ) {
			continue;
		}
		$attr = 'property' === $tag[0] ? 'property' : 'name';
		$val  = $tag[2];
		if ( in_array( $tag[1], array( 'og:url', 'og:image', 'og:image:secure_url', 'twitter:image' ), true ) ) {
			$val = esc_url( $val );
		} else {
			$val = esc_attr( $val );
		}
		echo '<meta ' . $attr . '="' . esc_attr( $tag[1] ) . '" content="' . $val . '">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- $attr is a fixed literal, $val escaped above.
	}
}
add_action( 'wp_head', 'sokal_seo_head', 3 );
