<?php
/**
 * Footer content: company block, contact lines, social links, app badges, copyright.
 *
 * Owner: footer agent.
 * Provides: sokal_footer_content(), sokal_footer_copyright().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Setting keys that switch the footer from the legacy HTML to the new layout. */
function sokal_footer_keys() {
	return array(
		'footer_logo',
		'company_name',
		'company_desc',
		'contact_lines',
		'social_facebook',
		'social_x',
		'social_whatsapp',
		'social_youtube',
		'social_instagram',
		'social_telegram',
		'app_google_play',
		'app_store',
	);
}

/**
 * True when the new company details are filled in. Social links and app
 * buttons alone don't count: they are added under the original footer text.
 */
function sokal_footer_has_new() {
	foreach ( array( 'footer_logo', 'company_name', 'company_desc', 'contact_lines' ) as $key ) {
		if ( '' !== trim( (string) sokal_opt( $key, '' ) ) ) {
			return true;
		}
	}
	return false;
}

/** Bengali digits to ASCII (for tel: hrefs). */
function sokal_footer_ascii_digits( $str ) {
	$bn = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );
	return str_replace( $bn, array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), (string) $str );
}

/** Escape a contact value, turning email addresses into mailto: and phone numbers into tel: links. */
function sokal_footer_linkify( $text ) {
	$pattern = '/([A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,})|(\+?[0-9০-৯][0-9০-৯ \-().]{5,}[0-9০-৯])/u';
	$parts   = preg_split( $pattern, (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
	if ( false === $parts ) {
		return esc_html( $text );
	}
	$out = '';
	foreach ( $parts as $part ) {
		if ( is_email( $part ) ) {
			$out .= '<a href="' . esc_url( 'mailto:' . $part ) . '">' . esc_html( $part ) . '</a>';
			continue;
		}
		if ( preg_match( '/^\+?[0-9০-৯][0-9০-৯ \-().]{5,}[0-9০-৯]$/u', $part ) ) {
			$tel = preg_replace( '/[^0-9+]/', '', sokal_footer_ascii_digits( $part ) );
			if ( strlen( ltrim( $tel, '+' ) ) >= 7 ) {
				$out .= '<a href="' . esc_url( 'tel:' . $tel, array( 'tel' ) ) . '">' . esc_html( $part ) . '</a>';
				continue;
			}
		}
		$out .= esc_html( $part );
	}
	return $out;
}

/** One contact line: "Label: value" -> <strong>Label:</strong> value. */
function sokal_footer_contact_line( $line ) {
	$pos = strpos( $line, ':' );
	// Treat the text before the first colon as a label only if it is short and not part of a URL.
	if ( false !== $pos && $pos > 0 && $pos <= 60 && '//' !== substr( $line, $pos + 1, 2 ) ) {
		$label = trim( substr( $line, 0, $pos ) );
		$value = trim( substr( $line, $pos + 1 ) );
		return '<strong>' . esc_html( $label ) . ':</strong> ' . sokal_footer_linkify( $value );
	}
	return sokal_footer_linkify( $line );
}

/** Social networks: key => array( label, svg inner markup ). */
function sokal_footer_socials() {
	return array(
		'facebook'  => array(
			'Facebook',
			'<path fill="currentColor" d="M13.5 21v-7.5H16l.4-3h-2.9V8.6c0-.87.24-1.46 1.49-1.46h1.6V4.46a21 21 0 0 0-2.28-.12c-2.25 0-3.8 1.37-3.8 3.9v2.26H8v3h2.51V21z"/>',
		),
		'x'         => array(
			'X',
			'<path fill="currentColor" transform="translate(4 4) scale(.667)" d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>',
		),
		'whatsapp'  => array(
			'WhatsApp',
			'<path fill="currentColor" transform="translate(4 4) scale(.667)" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>',
		),
		'youtube'   => array(
			'YouTube',
			'<path fill="currentColor" transform="translate(4 4) scale(.667)" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
		),
		'instagram' => array(
			'Instagram',
			'<rect x="4.5" y="4.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3.6" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="16.4" cy="7.6" r="1.1" fill="currentColor"/>',
		),
		'telegram'  => array(
			'Telegram',
			'<path fill="currentColor" d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>',
		),
	);
}

/** Social link list (only networks with a URL). */
function sokal_footer_social_html() {
	$items = '';
	foreach ( sokal_footer_socials() as $key => $data ) {
		$url = trim( (string) sokal_opt( 'social_' . $key, '' ) );
		if ( '' === $url ) {
			continue;
		}
		$items .= '<li><a class="sokal-social sokal-social--' . esc_attr( $key ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $data[0] ) . '" title="' . esc_attr( $data[0] ) . '">'
			. '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">' . $data[1] . '</svg>'
			. '</a></li>';
	}
	return '' !== $items ? '<ul class="sokal-social-list">' . $items . '</ul>' : '';
}

/** Google Play / App Store badge buttons (only those set). */
function sokal_footer_apps_html() {
	$apps = array(
		'app_google_play' => array(
			'class' => 'sokal-app--play',
			'small' => 'ডাউনলোড করুন',
			'big'   => 'Google Play',
			'label' => 'Google Play থেকে অ্যাপ ডাউনলোড করুন',
			'svg'   => '<path fill="#00d7fe" d="M3.6 2l9.9 10-9.9 10c-.3-.3-.5-.8-.5-1.4V3.4c0-.6.2-1.1.5-1.4z"/><path fill="#00f076" d="M3.6 2c.4-.4 1.1-.4 1.8 0l11.4 6.6L13.5 12z"/><path fill="#ff3a44" d="M3.6 22l9.9-10 3.3 3.4L5.4 22c-.7.4-1.4.4-1.8 0z"/><path fill="#ffce00" d="M16.8 8.6l3.8 2.2c1.1.6 1.1 1.8 0 2.4l-3.8 2.2-3.3-3.4z"/>',
		),
		'app_store'       => array(
			'class' => 'sokal-app--ios',
			'small' => 'ডাউনলোড করুন',
			'big'   => 'App Store',
			'label' => 'App Store থেকে অ্যাপ ডাউনলোড করুন',
			'svg'   => '<path fill="currentColor" d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.56-1.701"/>',
		),
	);
	$out = '';
	foreach ( $apps as $key => $app ) {
		$url = trim( (string) sokal_opt( $key, '' ) );
		if ( '' === $url ) {
			continue;
		}
		$out .= '<a class="sokal-app ' . esc_attr( $app['class'] ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $app['label'] ) . '">'
			. '<svg class="sokal-app__logo" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">' . $app['svg'] . '</svg>'
			. '<span class="sokal-app__text"><small>' . esc_html( $app['small'] ) . '</small><b>' . esc_html( $app['big'] ) . '</b></span>'
			. '</a>';
	}
	return '' !== $out ? '<div class="sokal-apps">' . $out . '</div>' : '';
}

/** Footer logo <img>, or ''. */
function sokal_footer_logo_html() {
	$url = trim( (string) sokal_opt( 'footer_logo', '' ) );
	if ( '' === $url ) {
		return '';
	}
	$size = '';
	$id   = absint( sokal_opt( 'footer_logo_id', 0 ) );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src && ! empty( $src[1] ) && ! empty( $src[2] ) ) {
			$size = ' width="' . absint( $src[1] ) . '" height="' . absint( $src[2] ) . '"';
		}
	}
	return '<div class="sokal-footer-logo"><a href="' . esc_url( home_url( '/' ) ) . '">'
		. '<img src="' . esc_url( $url ) . '"' . $size . ' alt="' . esc_attr( sokal_site_name() ) . '" loading="lazy" decoding="async"></a></div>';
}

/** Category links for the top of the footer: "Footer menu" if assigned, else busiest categories. */
function sokal_footer_cats_html() {
	if ( has_nav_menu( 'footer-menu' ) ) {
		$items = wp_get_nav_menu_items( get_nav_menu_locations()['footer-menu'] );
		$links = array();
		foreach ( (array) $items as $item ) {
			if ( empty( $item->menu_item_parent ) ) {
				$links[] = '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
			}
		}
	} else {
		$terms = get_terms( array(
			'taxonomy'   => 'category',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => 15,
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		) );
		$links = array();
		foreach ( is_array( $terms ) ? $terms : array() as $t ) {
			$links[] = '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
		}
	}
	if ( ! $links ) {
		return '';
	}
	return '<nav class="sokal-f-cats" aria-label="বিভাগসমূহ"><ul><li>' . implode( '</li><li>', $links ) . '</li></ul></nav>';
}

/** Footer logo: footer logo, else header logo. */
function sokal_footer_brand_html() {
	$html = sokal_footer_logo_html();
	if ( '' !== $html ) {
		return $html;
	}
	global $themesbazar;
	$url = trim( (string) sokal_opt( 'header_logo', '' ) );
	if ( '' === $url && ! empty( $themesbazar['logo_upload']['url'] ) ) {
		$url = (string) $themesbazar['logo_upload']['url'];
	}
	if ( '' === $url ) {
		return '<div class="sokal-footer-logo sokal-footer-logo--text"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( sokal_site_name() ) . '</a></div>';
	}
	return '<div class="sokal-footer-logo"><a href="' . esc_url( home_url( '/' ) ) . '"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( sokal_site_name() ) . '" loading="lazy" decoding="async"></a></div>';
}

/** Publisher / contact lines: new settings if filled, else the original footer text. */
function sokal_footer_info_html() {
	$out   = '';
	$name  = trim( (string) sokal_opt( 'company_name', '' ) );
	$desc  = trim( (string) sokal_opt( 'company_desc', '' ) );
	$lines = trim( (string) sokal_opt( 'contact_lines', '' ) );
	if ( '' !== $name || '' !== $desc || '' !== $lines ) {
		if ( '' !== $name ) {
			$out .= '<p class="sokal-footer-name">' . esc_html( $name ) . '</p>';
		}
		if ( '' !== $desc ) {
			$out .= '<p class="sokal-footer-desc">' . nl2br( esc_html( $desc ) ) . '</p>';
		}
		foreach ( preg_split( '/\r\n|\r|\n/', $lines ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$out .= '<p>' . sokal_footer_contact_line( $line ) . '</p>';
			}
		}
		return $out;
	}
	global $themesbazar;
	foreach ( array( 'editorial', 'editorial-tow' ) as $key ) {
		$v = isset( $themesbazar[ $key ] ) ? trim( (string) $themesbazar[ $key ] ) : '';
		if ( '' !== $v && false === stripos( $v, 'Dhaka,Bngkadesh' ) ) { // skip the English placeholder default
			$out .= '<div class="sokal-footer-text">' . wp_kses_post( wpautop( $v ) ) . '</div>';
		}
	}
	return $out;
}

/** The whole footer: one centred column (categories, logo, contact, follow, copyright). */
function sokal_footer_content() {
	$cats = function_exists( 'sokal_fragment' )
		? sokal_fragment( 'footer_cats', HOUR_IN_SECONDS, function () { echo sokal_footer_cats_html(); } ) // phpcs:ignore WordPress.Security.EscapeOutput
		: sokal_footer_cats_html();
	$heading = trim( (string) sokal_opt( 'follow_heading', '' ) );
	$social  = sokal_footer_social_html();
	$apps    = sokal_footer_apps_html();
	?>
	<div class="sokal-f">
		<?php if ( '' !== $cats ) : ?>
			<?php echo $cats; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in builder. ?>
			<hr class="sokal-f-rule">
		<?php endif; ?>
		<?php echo sokal_footer_brand_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in builder. ?>
		<div class="sokal-f-info"><?php echo sokal_footer_info_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in builder. ?></div>
		<?php if ( '' !== $social || '' !== $apps ) : ?>
			<div class="sokal-f-follow">
				<?php if ( '' !== $heading && '' !== $social ) : ?>
					<p class="sokal-footer-heading"><?php echo esc_html( $heading ); ?></p>
				<?php endif; ?>
				<?php echo $social . $apps; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in builders. ?>
			</div>
		<?php endif; ?>
		<hr class="sokal-f-rule">
		<p class="sokal-f-copy"><?php echo sokal_footer_copyright(); // phpcs:ignore WordPress.Security.EscapeOutput -- kses'd. ?></p>
	</div>
	<?php
}

/** Copyright line in Bengali with the current year ({year}, {site} placeholders). */
function sokal_footer_copyright() {
	$text = trim( (string) sokal_opt( 'copyright', '' ) );
	if ( '' === $text || '© {year} {site}' === $text ) {
		$text = '© {year} {site}। সর্বস্বত্ব সংরক্ষিত।';
	}
	$year = (string) current_time( 'Y' );
	if ( function_exists( 'bn_number' ) ) {
		$year = bn_number( $year );
	}
	return str_replace(
		array( '{year}', '{site}' ),
		array( esc_html( $year ), esc_html( sokal_site_name() ) ),
		wp_kses_post( $text )
	);
}
