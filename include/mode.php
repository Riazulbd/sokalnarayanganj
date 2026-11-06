<?php
/**
 * Light / dark mode, header and footer colours, story-card headline size.
 *
 * Pages are edge-cached, so the mode is picked in the browser: a tiny blocking
 * script printed first in <head> sets <html data-mode="light|dark"> from the
 * visitor's LOCAL clock (light from light_start to light_end, else dark), the
 * forced `mode` setting, or a manual choice kept in localStorage until the next
 * automatic switch. Output depends only on theme settings, never on the
 * visitor, so it is safe in page caches.
 *
 * Provides: sokal_mode_toggle().
 * Styles: css/components/mode.css. Clicks: js/components/mode-toggle.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a mode-related setting (new settings only; these keys are new).
 *
 * @param string $key      Setting key.
 * @param mixed  $fallback Default.
 * @return mixed
 */
function sokal_mode_opt( $key, $fallback = '' ) {
	if ( function_exists( 'sokal_opt' ) ) {
		$v = sokal_opt( $key, $fallback );
		return null === $v ? $fallback : $v;
	}
	return $fallback;
}

/**
 * Sanitised hex colour ('' when empty or invalid).
 *
 * @param string $key Setting key.
 * @return string
 */
function sokal_mode_color( $key ) {
	$v = sanitize_hex_color( trim( (string) sokal_mode_opt( $key, '' ) ) );
	return $v ? $v : '';
}

/**
 * Background + text pair for an area/mode. Text falls back to sokal_contrast().
 *
 * @param string $area header|footer.
 * @param string $mode light|dark.
 * @return array { bg: string, fg: string } ('' = not set).
 */
function sokal_mode_pair( $area, $mode ) {
	$bg = sokal_mode_color( $area . '_bg_' . $mode );
	$fg = sokal_mode_color( $area . '_text_' . $mode );
	if ( '' === $fg && '' !== $bg && function_exists( 'sokal_contrast' ) ) {
		$fg = (string) sokal_contrast( $bg );
	}
	return array(
		'bg' => $bg,
		'fg' => $fg,
	);
}

/**
 * Settings passed to the browser.
 *
 * @return array
 */
function sokal_mode_config() {
	$mode = (string) sokal_mode_opt( 'mode', 'auto' );
	if ( ! in_array( $mode, array( 'auto', 'light', 'dark' ), true ) ) {
		$mode = 'auto';
	}
	$start = (int) sokal_mode_opt( 'light_start', 6 );
	$end   = (int) sokal_mode_opt( 'light_end', 18 );
	$start = max( 0, min( 23, $start ) );
	$end   = max( 0, min( 23, $end ) );

	$hl = sokal_mode_pair( 'header', 'light' );
	$hd = sokal_mode_pair( 'header', 'dark' );

	return array(
		'm'  => $mode,
		's'  => $start,
		'e'  => $end,
		'k'  => 'sokal_mode',
		'tl' => '' !== $hl['bg'] ? $hl['bg'] : '#ffffff',
		'td' => '' !== $hd['bg'] ? $hd['bg'] : '#121212',
	);
}

/**
 * Blocking head script + theme-color metas. Runs before any CSS paints.
 */
function sokal_mode_head_script() {
	$c    = sokal_mode_config();
	$json = wp_json_encode( $c, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	printf( '<meta name="theme-color" content="%s" data-sokal-mode="light">' . "\n", esc_attr( $c['tl'] ) );
	printf( '<meta name="theme-color" content="%s" data-sokal-mode="dark">' . "\n", esc_attr( $c['td'] ) );

	// Self-contained; exposes window.sokalMode for js/components/mode-toggle.js.
	$js = <<<'JS'
(function(c,d,w){var h=d.documentElement,t;
function hr(n){return n.getHours()+n.getMinutes()/60+n.getSeconds()/3600;}
function auto(n){n=n||new Date();if(c.m==='light'||c.m==='dark')return c.m;if(c.s===c.e)return 'light';var x=hr(n);return(c.s<c.e?(x>=c.s&&x<c.e):(x>=c.s||x<c.e))?'light':'dark';}
function next(n){n=n||new Date();var b=[],i,j,v;for(i=0;i<2;i++){for(j=0;j<2;j++){v=new Date(n.getFullYear(),n.getMonth(),n.getDate()+i,j?c.e:c.s,0,0,0);if(v.getTime()>n.getTime())b.push(v.getTime());}}b.sort(function(a,z){return a-z;});return b[0]||n.getTime()+864e5;}
function read(){try{var r=JSON.parse(w.localStorage.getItem(c.k)||'null');if(r&&(r.m==='light'||r.m==='dark')&&r.x>Date.now())return r.m;if(r)w.localStorage.removeItem(c.k);}catch(e){}return null;}
function cur(){return read()||auto();}
function apply(m){h.setAttribute('data-mode',m);h.style.colorScheme=m;var a=d.querySelectorAll('meta[name="theme-color"][data-sokal-mode]'),i;for(i=0;i<a.length;i++){if(a[i].getAttribute('data-sokal-mode')===m)a[i].removeAttribute('media');else a[i].setAttribute('media','not all');}try{d.dispatchEvent(new CustomEvent('sokal:mode',{detail:{mode:m}}));}catch(e){}}
function set(m){if(m!=='light'&&m!=='dark')return;try{w.localStorage.setItem(c.k,JSON.stringify({m:m,x:next()}));}catch(e){}apply(m);}
function tick(){clearTimeout(t);var ms=next()-Date.now()+1e3;t=setTimeout(function(){apply(cur());tick();},Math.min(Math.max(ms,1e3),2147e6));}
w.sokalMode={config:c,auto:auto,next:next,current:cur,apply:apply,set:set};apply(cur());tick();
})(__SOKAL_MODE_CFG__,document,window);
JS;
	echo '<script id="sokal-mode-js">' . str_replace( '__SOKAL_MODE_CFG__', $json, $js ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- static JS + JSON-encoded, hex-escaped settings.
}
add_action( 'wp_head', 'sokal_mode_head_script', 1 );

/**
 * Header/footer colour rules for one mode, only for values that are set.
 *
 * Selector lists mirror css/components/mode.css; the prefixes here carry one
 * more class-level of specificity so a saved colour beats both mode.css (dark
 * defaults) and css/dynamic-css.php (printed after wp_head in header.php).
 *
 * @param string $p  Selector prefix incl. trailing space.
 * @param array  $hd Header pair.
 * @param array  $ft Footer pair.
 * @return string CSS.
 */
function sokal_mode_area_css( $p, $hd, $ft ) {
	$join = function ( $sels ) use ( $p ) {
		return $p . implode( ',' . $p, $sels );
	};
	$css = '';

	if ( '' !== $hd['bg'] ) {
		$css .= $join( array( '.top-hdr-border', '.sokal-brand-row', '.menu_area .menu_bottom', '.mainmenu .collapse ul ul', '.mainmenu .collapse ul ul ul' ) )
			. '{background:' . $hd['bg'] . '}'
			// Logo/banner row has no class: separate rule so a browser without :has() keeps the rest.
			. $p . '.main_wbsite>.row:has(>.logo){background:' . $hd['bg'] . '}';
	}
	if ( '' !== $hd['fg'] ) {
		$css .= $join( array( '.top-hdr-border', '.top-hdr-border .date', '.top-hdr-social ul li', '.sokal-clock', '.sokal-brand-row', '.sokal-site-name', '.sokal-mode-toggle', '.menu_area .menu_bottom .mainmenu a', '.menu_area .navbar-default .navbar-nav>li>a' ) )
			. '{color:' . $hd['fg'] . '}'
			. $join( array( '.menu_area .navbar-default .navbar-toggle' ) ) . '{border-color:' . $hd['fg'] . '}'
			. $join( array( '.menu_area .navbar-default .navbar-toggle .icon-bar' ) ) . '{background-color:' . $hd['fg'] . '}'
			// Keep the theme's red hover / active state readable.
			. $join( array( '.menu_area .menu_bottom .mainmenu li a:hover', '.menu_area .menu_bottom .mainmenu li a:focus', '.menu_area .menu_bottom .mainmenu .active>a' ) )
			. '{color:#fff}'
			. $p . '.main_wbsite>.row:has(>.logo){color:' . $hd['fg'] . '}';
	}
	if ( '' !== $ft['bg'] ) {
		$css .= $join( array( '.themesbazar_footer_sec', '.root' ) ) . '{background:' . $ft['bg'] . '}';
	}
	if ( '' !== $ft['fg'] ) {
		$css .= $join( array( '.themesbazar_footer_sec', '.themesbazar_footer_sec .editor-text', '.themesbazar_footer_sec .address-text', '.themesbazar_footer_sec a', '.root', '.root .root_01', '.root .root_02', '.root a' ) )
			. '{color:' . $ft['fg'] . '}';
	}
	return $css;
}

/**
 * Inline CSS in <head>: header/footer colours per mode and the card headline size.
 *
 * Nothing is printed for empty settings, so the default light look is untouched.
 */
function sokal_mode_inline_css() {
	// Light also covers the no-JS state (no data-mode attribute).
	$css = sokal_mode_area_css(
		'html:not([data-mode="dark"]) ',
		sokal_mode_pair( 'header', 'light' ),
		sokal_mode_pair( 'footer', 'light' )
	);
	// :root[..] = (0,2,0) beats mode.css's html[data-mode="dark"] = (0,1,1).
	$css .= sokal_mode_area_css(
		':root[data-mode="dark"] ',
		sokal_mode_pair( 'header', 'dark' ),
		sokal_mode_pair( 'footer', 'dark' )
	);

	$size = (int) sokal_mode_opt( 'card_title_size', 0 );
	$size = $size > 0 ? max( 10, min( 48, $size ) ) : 0;
	if ( $size ) {
		$css .= ':root{--sokal-card-title:' . $size . 'px}';
		$list = array();
		foreach ( sokal_card_title_selectors() as $s ) {
			$list[] = ':root ' . $s;
			$list[] = ':root ' . $s . ' a';
		}
		// The :root prefix beats dynamic-css.php and responsive.css (incl. media
		// queries) on specificity, so no !important is needed.
		$css .= implode( ',', $list ) . '{font-size:var(--sokal-card-title);line-height:1.4}';
	}

	if ( '' === $css ) {
		return;
	}
	echo '<style id="sokal-mode-css">' . wp_strip_all_tags( $css ) . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- built from sanitize_hex_color() values and integers.
}
add_action( 'wp_head', 'sokal_mode_inline_css', 2 );

/**
 * Story-card headline classes sized by card_title_size.
 *
 * @return string[]
 */
function sokal_card_title_selectors() {
	return apply_filters(
		'sokal_card_title_selectors',
		array( '.themesbazar_hadding_1', '.themesbazar_hadding_2', '.themesbazar_hadding_3', '.sgl-hadding' )
	);
}


/**
 * Echo the light/dark switch. Markup is the same for every visitor (cache
 * safe); js/components/mode-toggle.js syncs it to the active mode.
 *
 * aria-pressed="true" means dark mode is on. The accessible name stays
 * "রাতের মোড" so "pressed / not pressed" reads correctly; the visible tooltip
 * (title) names the action: "দিনের মোড চালু করুন" in dark, "রাতের মোড চালু করুন" in light.
 */
function sokal_mode_toggle() {
	?>
<button type="button" class="sokal-mode-toggle" aria-pressed="false" aria-label="রাতের মোড" title="রাতের মোড চালু করুন">
	<svg class="sokal-mode-toggle__icon sokal-mode-toggle__moon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
	<svg class="sokal-mode-toggle__icon sokal-mode-toggle__sun" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4.5" fill="currentColor"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 1.5v2.5M12 20v2.5M1.5 12H4M20 12h2.5M4.6 4.6l1.8 1.8M17.6 17.6l1.8 1.8M4.6 19.4l1.8-1.8M17.6 6.4l1.8-1.8"/></svg>
</button>
	<?php
}
