<?php
/**
 * Social share buttons: Facebook, WhatsApp, Messenger, Telegram, copy link.
 *
 * Output depends only on the post and theme settings (no per-user data), so it
 * is safe inside page/fragment caches. Copy-link behaviour lives in
 * js/components/share.js (auto-loaded with defer).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icons (24x24 viewBox, currentColor).
 *
 * @param string $name facebook|whatsapp|messenger|telegram|link|download|share.
 * @return string Trusted static SVG markup.
 */
function sokal_share_icon( $name ) {
	$paths = array(
		'facebook'  => 'M13.5 21.9v-7.4h2.5l.4-2.9h-2.9V9.8c0-.8.2-1.4 1.4-1.4h1.6V5.8c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.1H7.8v2.9h2.6v7.4C5.6 21.2 2 17 2 12 2 6.5 6.5 2 12 2s10 4.5 10 10c0 5-3.7 9.2-8.5 9.9z',
		'whatsapp'  => 'M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1-.7-.3-1.5-.7-2.3-1.5-.6-.5-1-1.2-1.2-1.4-.1-.2 0-.4.1-.5l.4-.4.2-.4c.1-.2 0-.3 0-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.7.3-.2.2-.9.9-.9 2.2s.9 2.6 1.1 2.7c.1.2 1.8 2.8 4.4 3.9 1.6.7 2.3.8 3.1.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.6-.3z',
		'messenger' => 'M12 2C6.4 2 2 6.1 2 11.7c0 2.9 1.2 5.5 3.2 7.3v3l3-1.6c1.2.3 2.5.5 3.8.5 5.6 0 10-4.1 10-9.7S17.6 2 12 2zm1 13l-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7L13 15z',
		'telegram'  => 'M21.9 4.3l-3 14.3c-.2 1-.8 1.3-1.7.8l-4.6-3.4-2.2 2.1c-.2.2-.5.5-1 .5l.3-4.7 8.6-7.8c.4-.3-.1-.5-.6-.2L7.1 12.6 2.5 11.2c-1-.3-1-1 .2-1.5l18-6.9c.8-.3 1.6.2 1.2 1.5z',
		'link'      => 'M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zM8.5 19.1a4 4 0 0 1-2.8-6.8l2.1-2.1a1 1 0 1 1 1.4 1.4l-2.1 2.1a2 2 0 0 0 2.8 2.8l2.1-2.1a1 1 0 1 1 1.4 1.4l-2.1 2.1a4 4 0 0 1-2.8 1.2zm7.1-5.6a1 1 0 0 1-.7-1.7l2.1-2.1a2 2 0 0 0-2.8-2.8l-2.1 2.1a1 1 0 1 1-1.4-1.4l2.1-2.1a4 4 0 0 1 5.7 5.7l-2.1 2.1a1 1 0 0 1-.8.2z',
		'download'  => 'M12 3a1 1 0 0 1 1 1v8.6l2.3-2.3a1 1 0 1 1 1.4 1.4l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4l2.3 2.3V4a1 1 0 0 1 1-1zM5 18a1 1 0 0 1 1 1v1h12v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1z',
		'share'     => 'M18 16a3 3 0 0 0-2.4 1.2l-6.7-3.4a3 3 0 0 0 0-1.6l6.7-3.4A3 3 0 1 0 15 7c0 .3 0 .5.1.8L8.4 11.2a3 3 0 1 0 0 3.6l6.7 3.4A3 3 0 1 0 18 16z',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="sokal-share__icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . $paths[ $name ] . '"/></svg>';
}

/**
 * Print the share buttons for a post (defaults to the current post).
 *
 * @param int|WP_Post|null $post Post to share.
 */
function sokal_share_buttons( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}

	$url    = get_permalink( $post );
	$title  = wp_strip_all_tags( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) );
	$u      = rawurlencode( $url );
	$t      = rawurlencode( $title );
	$app_id = function_exists( 'sokal_opt' ) ? trim( (string) sokal_opt( 'fb_app_id', '' ) ) : '';
	$app_id = preg_replace( '/[^0-9]/', '', $app_id );

	$links = array(
		array(
			'net'   => 'facebook',
			'href'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $u,
			'label' => 'ফেসবুক',
			'aria'  => 'ফেসবুকে শেয়ার করুন',
		),
		array(
			'net'   => 'whatsapp',
			'href'  => 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ),
			'label' => 'হোয়াটসঅ্যাপ',
			'aria'  => 'হোয়াটসঅ্যাপে শেয়ার করুন',
		),
		array(
			'net'   => 'messenger',
			'class' => 'sokal-share__btn--mobile',
			'href'  => 'fb-messenger://share?link=' . $u,
			'label' => 'মেসেঞ্জার',
			'aria'  => 'মেসেঞ্জারে শেয়ার করুন',
			'self'  => true,
		),
	);
	if ( '' !== $app_id ) {
		$links[] = array(
			'net'   => 'messenger',
			'class' => 'sokal-share__btn--desktop',
			'href'  => 'https://www.facebook.com/dialog/send?app_id=' . rawurlencode( $app_id ) . '&link=' . $u . '&redirect_uri=' . $u,
			'label' => 'মেসেঞ্জার',
			'aria'  => 'মেসেঞ্জারে শেয়ার করুন',
		);
	}
	$links[] = array(
		'net'   => 'telegram',
		'href'  => 'https://t.me/share/url?url=' . $u . '&text=' . $t,
		'label' => 'টেলিগ্রাম',
		'aria'  => 'টেলিগ্রামে শেয়ার করুন',
	);

	echo '<div class="sokal-share" role="group" aria-label="' . esc_attr( 'শেয়ার করুন' ) . '">';
	foreach ( $links as $l ) {
		$class  = 'sokal-share__btn sokal-share__btn--' . $l['net'] . ( isset( $l['class'] ) ? ' ' . $l['class'] : '' );
		$target = empty( $l['self'] ) ? ' target="_blank"' : '';
		printf(
			'<a class="%1$s" href="%2$s"%3$s rel="noopener noreferrer nofollow" aria-label="%4$s" title="%4$s">%5$s<span class="sokal-share__label">%6$s</span></a>',
			esc_attr( $class ),
			esc_url( $l['href'], array( 'http', 'https', 'fb-messenger' ) ),
			$target, // static string.
			esc_attr( $l['aria'] ),
			sokal_share_icon( $l['net'] ), // static SVG.
			esc_html( $l['label'] )
		);
	}
	printf(
		'<button type="button" class="sokal-share__btn sokal-share__btn--copy" data-sokal-copy="%1$s" aria-label="%2$s" title="%2$s">%3$s<span class="sokal-share__label">%4$s</span></button>',
		esc_url( $url ),
		esc_attr( 'লিংক কপি করুন' ),
		sokal_share_icon( 'link' ), // static SVG.
		esc_html( 'লিংক কপি' )
	);
	echo '<span class="sokal-share__status" role="status" aria-live="polite"></span>';
	echo '</div>';
}
