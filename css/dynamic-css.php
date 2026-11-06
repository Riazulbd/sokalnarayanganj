<?php
/**
 * Colours and sizes from the theme options (legacy Redux array), as CSS.
 * Owner: assets agent.
 *
 * include/assets.php attaches sokal_dynamic_css() with wp_add_inline_style()
 * after the theme CSS. header.php may still include this file directly: when
 * the CSS has already been attached that include is a no-op, otherwise it
 * prints the CSS itself.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sokal_dynamic_css' ) ) {

	/** Read $themesbazar[ $key ] or $themesbazar[ $key ][ $sub ]. */
	function sokal_dyn_raw( $key, $sub = null ) {
		global $themesbazar;
		if ( ! is_array( $themesbazar ) || ! isset( $themesbazar[ $key ] ) ) {
			return '';
		}
		$v = $themesbazar[ $key ];
		if ( null !== $sub ) {
			$v = ( is_array( $v ) && isset( $v[ $sub ] ) ) ? $v[ $sub ] : '';
		}
		return is_scalar( $v ) ? trim( (string) $v ) : '';
	}

	/** Hex colour (or "transparent"), else the fallback. */
	function sokal_dyn_color( $key, $sub, $fallback ) {
		$v = sokal_dyn_raw( $key, $sub );
		if ( 'transparent' === strtolower( $v ) ) {
			return 'transparent';
		}
		$hex = sanitize_hex_color( $v );
		return $hex ? $hex : $fallback;
	}

	/** Pixel size ("17px" or "17"), else the fallback. */
	function sokal_dyn_px( $key, $sub, $fallback ) {
		$n = absint( sokal_dyn_raw( $key, $sub ) );
		return ( $n > 0 && $n < 200 ) ? $n . 'px' : $fallback;
	}

	/** Font weight 100-900, else the fallback. */
	function sokal_dyn_weight( $key, $sub, $fallback ) {
		$n = absint( sokal_dyn_raw( $key, $sub ) );
		return ( $n >= 100 && $n <= 900 ) ? (string) $n : $fallback;
	}

	/**
	 * Optional declaration: only printed when the option holds a valid pixel
	 * value (Redux stores "auto" for line-height, which browsers ignored).
	 */
	function sokal_dyn_opt_px( $prop, $key, $sub ) {
		$raw = sokal_dyn_raw( $key, $sub );
		$n   = absint( $raw );
		if ( '' === $raw || $n <= 0 || $n >= 200 || ! preg_match( '/^\d+(\.\d+)?(px)?$/', $raw ) ) {
			return '';
		}
		return $prop . ':' . $n . 'px;';
	}

	/** The full dynamic stylesheet. */
	function sokal_dynamic_css() {
		$align = strtolower( sokal_dyn_raw( 'hscrool-font', 'text-align' ) );
		if ( ! in_array( $align, array( 'left', 'right', 'center', 'justify' ), true ) ) {
			$align = 'left';
		}

		$vars = array(
			'--sokal-body-bg'        => sokal_dyn_color( 'body', null, '#F2F2F2' ),
			'--sokal-body-size'      => sokal_dyn_px( 'body-font', 'font-size', '17px' ),
			'--sokal-scroll-bg'      => sokal_dyn_color( 'hscrool-background', null, '#8B0708' ),
			'--sokal-scroll-color'   => sokal_dyn_color( 'hscrool-font', 'color', '#fff' ),
			'--sokal-cat1-bg'        => sokal_dyn_color( 'cat_one-title-background', null, '#006699' ),
			'--sokal-cat1-color'     => sokal_dyn_color( 'cat_one-title-font', 'color', '#fff' ),
			'--sokal-cat1-weight'    => sokal_dyn_weight( 'cat_one-title-font', 'font-weight', '400' ),
			'--sokal-cat1-size'      => sokal_dyn_px( 'cat_one-title-font', 'font-size', '18px' ),
			'--sokal-cat2-bg'        => sokal_dyn_color( 'cat_two-title-background', null, '#8B0708' ),
			'--sokal-cat2-color'     => sokal_dyn_color( 'cat_two-title-font', 'color', '#fff' ),
			'--sokal-cat2-weight'    => sokal_dyn_weight( 'cat_two-title-font', 'font-weight', '400' ),
			'--sokal-cat2-size'      => sokal_dyn_px( 'cat_two-title-font', 'font-size', '18px' ),
			'--sokal-cat3-bg'        => sokal_dyn_color( 'cat_three-title-background', null, '#f7f7f7' ),
			'--sokal-cat3-color'     => sokal_dyn_color( 'cat_three-title-font', 'color', '#000' ),
			'--sokal-cat3-weight'    => sokal_dyn_weight( 'cat_three-title-font', 'font-weight', '400' ),
			'--sokal-cat3-size'      => sokal_dyn_px( 'cat_three-title-font', 'font-size', '18px' ),
			'--sokal-cat4-bg'        => sokal_dyn_color( 'cat_four-title-background', null, '#01284F' ),
			'--sokal-cat4-color'     => sokal_dyn_color( 'cat_four-title-font', 'color', '#fff' ),
			'--sokal-cat4-weight'    => sokal_dyn_weight( 'cat_four-title-font', 'font-weight', '400' ),
			'--sokal-cat4-size'      => sokal_dyn_px( 'cat_four-title-font', 'font-size', '17px' ),
			'--sokal-h1-color'       => sokal_dyn_color( 'heading-01-font', 'color', '#000' ),
			'--sokal-h1-weight'      => sokal_dyn_weight( 'heading-01-font', 'font-weight', '400' ),
			'--sokal-h1-size'        => sokal_dyn_px( 'heading-01-font', 'font-size', '22px' ),
			'--sokal-h2-color'       => sokal_dyn_color( 'heading-02-font', 'color', '#000' ),
			'--sokal-h2-weight'      => sokal_dyn_weight( 'heading-02-font', 'font-weight', '400' ),
			'--sokal-h2-size'        => sokal_dyn_px( 'heading-02-font', 'font-size', '20px' ),
			'--sokal-h3-color'       => sokal_dyn_color( 'heading-03-font', 'color', '#000' ),
			'--sokal-h3-weight'      => sokal_dyn_weight( 'heading-03-font', 'font-weight', '400' ),
			'--sokal-h3-size'        => sokal_dyn_px( 'heading-03-font', 'font-size', '17px' ),
			'--sokal-more-color'     => sokal_dyn_color( 'more-news', 'color', '#15520F' ),
			'--sokal-more-weight'    => sokal_dyn_weight( 'more-news', 'font-weight', '400' ),
			'--sokal-more-size'      => sokal_dyn_px( 'more-news', 'font-size', '15px' ),
			'--sokal-footer-bg'      => sokal_dyn_color( 'footer-background', null, '#05668A' ),
			'--sokal-footer-color'   => sokal_dyn_color( 'footer-font', 'color', '#fff' ),
			'--sokal-root-bg'        => sokal_dyn_color( 'btm-footer-background', null, '#088FC1' ),
			'--sokal-root-color'     => sokal_dyn_color( 'root-font', 'color', '#fff' ),
			'--sokal-menu-bg'        => sokal_dyn_color( 'menu-background', null, '#4C4B4B' ),
			'--sokal-menu-color'     => sokal_dyn_color( 'menu-font', 'color', '#fff' ),
			'--sokal-menu-size'      => sokal_dyn_px( 'menu-font', 'font-size', '16px' ),
		);

		$root = '';
		foreach ( $vars as $name => $value ) {
			$root .= $name . ':' . $value . ';';
		}

		$h1_lh = sokal_dyn_opt_px( 'line-height', 'heading-01-font', 'line-height' );
		$h2_lh = sokal_dyn_opt_px( 'line-height', 'heading-02-font', 'line-height' );
		$h3_lh = sokal_dyn_opt_px( 'line-height', 'heading-03-font', 'line-height' );

		$cat1_font = 'color:var(--sokal-cat1-color);font-weight:var(--sokal-cat1-weight);font-size:var(--sokal-cat1-size);';
		$cat4_font = 'color:var(--sokal-cat4-color);font-weight:var(--sokal-cat4-weight);font-size:var(--sokal-cat4-size);';

		$css  = ':root{' . $root . '}';
		$css .= 'body{background-color:var(--sokal-body-bg);font-size:var(--sokal-body-size);width:100%;font-family:\'SolaimanLipiNormal\',\'Noto Sans Bengali\',\'Vrinda\',\'Shonar Bangla\',sans-serif}';

		// Ticker label and strip.
		$css .= '.scrool_1,.scrool_2{padding:4px;font-size:18px;border-bottom:4px solid var(--sokal-scroll-bg)}';
		$css .= '.scrool_1{background-color:var(--sokal-scroll-bg);color:var(--sokal-scroll-color);text-align:' . $align . ';font-weight:400}';
		$css .= '.scrool_2{color:#9A1515;background-color:#F0ECEC}';

		// Category title, style one (also the sidebar / widget / archive titles).
		$css .= '.themesbazar_catagory_title{background-color:var(--sokal-cat1-bg);padding:7px;border-radius:5px 5px 0 0;margin-top:7px;margin-bottom:5px;' . $cat1_font . '}';
		$css .= '.themesbazar_catagory_title span a{' . $cat1_font . 'text-decoration:none;background:#7D0101;border-radius:0 50px 0 0;padding:7px 20px 7px 10px}';
		$css .= '.facebook_title,.archive_title,.widget_area h3{background:var(--sokal-cat1-bg);margin-bottom:10px;' . $cat1_font . 'padding:6px}';

		// Style two.
		$css .= '.cat_title_two{padding:7px;background:var(--sokal-cat2-bg);margin-bottom:10px}';
		$css .= '.cat_title_two a{color:var(--sokal-cat2-color);font-weight:var(--sokal-cat2-weight);font-size:var(--sokal-cat2-size);text-decoration:none}';

		// Style three.
		$css .= '.cat_title_three{background:var(--sokal-cat3-bg);padding:8px;border-left:5px solid #f90202;margin-top:10px;margin-bottom:8px}';
		$css .= '.cat_title_three a{color:var(--sokal-cat3-color);font-weight:var(--sokal-cat3-weight);font-size:var(--sokal-cat3-size);text-decoration:none}';

		// Style four and the arrow-shaped .pointer label.
		$css .= '.cat_title_four{background:#F0F0F0;' . $cat4_font . '}';
		$css .= '.pointer a{' . $cat4_font . 'text-decoration:none}';
		$css .= '.pointer{' . $cat4_font . 'width:180px;height:40px;position:relative;background:var(--sokal-cat4-bg);padding-top:10px;padding-left:10px;margin:10px 0 8px}';
		$css .= '.pointer:after{content:"";position:absolute;left:0;bottom:0;width:0}';
		$css .= '.pointer:before{content:"";position:absolute;right:-20px;bottom:0;width:0;height:0;border-left:20px solid var(--sokal-cat4-bg);border-top:20px solid transparent;border-bottom:20px solid transparent}';

		// Headlines.
		$css .= '.themesbazar_hadding_1 a{font-size:var(--sokal-h1-size);' . $h1_lh . 'font-weight:var(--sokal-h1-weight);color:var(--sokal-h1-color);text-decoration:none}';
		$css .= '.themesbazar_hadding_2 a{font-size:var(--sokal-h2-size);' . $h2_lh . 'font-weight:var(--sokal-h2-weight);color:var(--sokal-h2-color);text-decoration:none}';
		$css .= '.themesbazar_hadding_3 a{font-size:var(--sokal-h3-size);' . $h3_lh . 'font-weight:var(--sokal-h3-weight);color:var(--sokal-h3-color);text-decoration:none}';
		$css .= '.themesbazar_more_news a{font-size:var(--sokal-more-size);font-weight:var(--sokal-more-weight);color:var(--sokal-more-color);text-decoration:none}';

		$css .= '.archive_calender_sec{margin:15px 0 12px;overflow:hidden}';

		// Footer.
		$css .= '.themesbazar_footer_sec{background:var(--sokal-footer-bg);padding:20px;margin-top:20px}';
		$css .= '.editor-text,.address-text{font-size:17px;color:var(--sokal-footer-color);padding:10px}';
		$css .= '.editor-text{text-align:right}.address-text{text-align:left}';
		$css .= '.root{padding:10px;background-color:var(--sokal-root-bg)}';
		$css .= '.root_01,.root_02{font-size:16px;color:var(--sokal-root-color)}.root_02{text-align:right}';
		$css .= '.scrollToTop{width:40px;height:40px;padding:10px;background:var(--sokal-cat1-bg);position:fixed;right:25px;bottom:50px;border-radius:50px;cursor:pointer}';

		// Main menu.
		$css .= '.menu_bottom{background:var(--sokal-menu-bg)}';
		$css .= '.menu_area .menu_bottom .mainmenu a,.navbar-default .navbar-nav>li>a{font-size:var(--sokal-menu-size);color:var(--sokal-menu-color);text-transform:capitalize;padding:10px 16px;border-right:1px solid #5B5A50}';
		$css .= '.mainmenu .collapse ul ul,.mainmenu .collapse ul ul .dropdown-menu,.mainmenu .collapse ul ul ul,.mainmenu .collapse ul ul ul .dropdown-menu{background:var(--sokal-menu-bg)}';
		$css .= '.navbar-default .navbar-nav>.active>a,.navbar-default .navbar-nav>.active>a:hover,.navbar-default .navbar-nav>.active>a:focus{color:#fff!important;margin:0;background-color:#8B0708}';

		/**
		 * Filter the dynamic CSS before it is printed.
		 *
		 * @param string $css Generated CSS.
		 */
		return (string) apply_filters( 'sokal_dynamic_css', $css );
	}
}

// Loaded by include/assets.php as a library: define only.
if ( defined( 'SOKAL_DYNAMIC_CSS_LIB' ) && ! defined( 'SOKAL_DYNAMIC_CSS_LOADED' ) ) {
	define( 'SOKAL_DYNAMIC_CSS_LOADED', true );
	return;
}

// Legacy include from header.php: no-op once assets.php attached the CSS.
if ( did_action( 'sokal_dynamic_css_done' ) ) {
	return;
}

// Fallback: print it here (the CSS is built from sanitised values only).
echo '<style id="sokal-dynamic-css">' . wp_strip_all_tags( sokal_dynamic_css() ) . '</style>' . "\n";
