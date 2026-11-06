<?php
/**
 * Settings contract shared by every part of the theme.
 *
 * All Theme Settings live in ONE option, `sokal_settings` (autoloaded, small).
 * Read values with sokal_opt( 'key' ). Never read the option directly.
 * Owner: settings agent (may ADD keys; must not rename or remove these).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sokal_defaults() {
	return array(
		// General
		'site_name'          => '',          // '' = WordPress Site Title
		'header_logo'        => '',          // image URL
		'header_logo_id'     => 0,
		'footer_logo'        => '',
		'footer_logo_id'     => 0,
		'favicon'            => '',
		'favicon_id'         => 0,           // attachment id of the favicon (added by settings agent)
		'ads_enabled'        => 0,           // 0 = no ads anywhere (banner + all ad widget areas)
		'header_logo_height' => 110,         // px, desktop header logo height (centred when ads are off)
		'mobile_logo_height' => 56,
		'footer_logo_height' => 90,          // px, footer logo height          // px, phone header logo height
		'card_title_size'    => 0,           // px for all story-card headlines; 0 = keep theme sizes

		// Colours and mode
		'mode'               => 'auto',      // auto | light | dark
		'light_start'        => 6,           // hour (0-23) light mode starts, visitor local time
		'light_end'          => 18,          // hour dark mode starts
		'header_bg_light'    => '',
		'header_text_light'  => '',          // '' = picked automatically from bg for contrast
		'header_bg_dark'     => '',
		'header_text_dark'   => '',
		'footer_bg_light'    => '',
		'footer_text_light'  => '',
		'footer_bg_dark'     => '',
		'footer_text_dark'   => '',

		// Home page
		'ticker_enabled'     => 1,
		'ticker_label'       => 'শিরোনাম',
		'ticker_count'       => 10,
		'ticker_speed'       => 40,          // seconds for one full scroll
		'ticker_cat'         => 0,           // 0 = latest posts
		'lead_cat'           => 0,           // top lead story category (0 = latest)
		'grid_cat'           => 0,           // top-news grid category (0 = latest)
		'home_slots'         => array( 0, 0, 0, 0, 0, 0, 0, 0 ), // up to 8 section categories, 0 = auto-pick
		'auto_sections'      => 1,           // fill empty slots from busiest categories

		// Articles and sharing
		'fb_app_id'          => '',          // enables Messenger share on desktop
		'share_style'        => 'card',   // classic | dark | light | band | strip
		'share_bg'           => '#ffffff',
		'share_text'         => '#111111',
		'share_accent'       => '#c4161c',
		'share_bottom_line'  => 'বিস্তারিত কমেন্টে',

		// Footer
		'company_name'       => '',
		'company_desc'       => '',
		'contact_lines'      => '',          // one per line; "Label: value" -> bold label; emails linked
		'follow_heading'     => 'ফলো করুন',
		'app_google_play'    => '',          // URL
		'app_store'          => '',          // URL
		'copyright'          => '© {year} {site}। সর্বস্বত্ব সংরক্ষিত।',
		'social_facebook'    => '',
		'social_x'           => '',
		'social_whatsapp'    => '',
		'social_youtube'     => '',
		'social_instagram'   => '',
		'social_telegram'    => '',

		// Speed and cache
		'edge_ttl'           => 120,         // Cloudflare s-maxage seconds
		'cf_zone_id'         => '',          // optional: purge Cloudflare on publish
		'cf_api_token'       => '',

		// Old Redux values (copied once). Templates read these through global $themesbazar.
		'legacy'             => array(),
	);
}

/** All settings merged with defaults (cached per request). */
function sokal_settings() {
	static $s = null;
	if ( null === $s || did_action( 'update_option_sokal_settings' ) ) {
		$saved = get_option( 'sokal_settings', array() );
		$s     = array_merge( sokal_defaults(), is_array( $saved ) ? $saved : array() );
	}
	return $s;
}

function sokal_opt( $key, $fallback = null ) {
	$s = sokal_settings();
	if ( array_key_exists( $key, $s ) && '' !== $s[ $key ] && null !== $s[ $key ] ) {
		return $s[ $key ];
	}
	return null !== $fallback ? $fallback : ( isset( $s[ $key ] ) ? $s[ $key ] : null );
}

/** Site name: Theme Settings value, else WordPress Site Title. */
function sokal_site_name() {
	$n = trim( (string) sokal_opt( 'site_name' ) );
	return '' !== $n ? $n : get_bloginfo( 'name' );
}

/** Clamp a numeric setting to a safe range (prevents posts_per_page = -1). */
function sokal_int( $value, $min = 1, $max = 20 ) {
	return max( $min, min( $max, absint( $value ) ) );
}

/** Black or white text, whichever reads better on $hex. */
function sokal_contrast( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return '';
	}
	$lum = 0;
	foreach ( array( 0.2126, 0.7152, 0.0722 ) as $i => $w ) {
		$c    = hexdec( substr( $hex, $i * 2, 2 ) ) / 255;
		$c    = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		$lum += $w * $c;
	}
	return $lum > 0.179 ? '#111111' : '#ffffff';
}
