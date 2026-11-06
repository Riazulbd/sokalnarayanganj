<?php
/**
 * Site header. Owner: header agent.
 *
 * <title>, favicon, meta description and OG tags are printed by include/seo.php via hooks.
 */
global $themesbazar;
if ( ! is_array( $themesbazar ) ) {
	$themesbazar = array();
}

/* ---------- Date (server fallback; js/components/clock.js adds live time) ---------- */
$sokal_hdr_lang = ( isset( $themesbazar['site-content'] ) && 1 == $themesbazar['site-content'] ) ? 'bn' : 'en';
$sokal_hdr_tz   = new DateTimeZone( 'Asia/Dhaka' );
if ( 'bn' === $sokal_hdr_lang ) {
	$sokal_hdr_date = bn1_date( wp_date( 'l, j F Y', null, $sokal_hdr_tz ) );
} else {
	$sokal_hdr_date = wp_date( 'l, F j, Y', null, $sokal_hdr_tz );
}

/* ---------- Logo ---------- */
$sokal_logo_url = (string) sokal_opt( 'header_logo' );
$sokal_logo_w   = 0;
$sokal_logo_h   = 0;
if ( '' !== $sokal_logo_url ) {
	$sokal_logo_id = absint( sokal_opt( 'header_logo_id', 0 ) );
	if ( $sokal_logo_id ) {
		$sokal_logo_src = wp_get_attachment_image_src( $sokal_logo_id, 'full' );
		if ( $sokal_logo_src ) {
			$sokal_logo_w = (int) $sokal_logo_src[1];
			$sokal_logo_h = (int) $sokal_logo_src[2];
		}
	}
} elseif ( ! empty( $themesbazar['logo_upload']['url'] ) ) {
	$sokal_logo_url = (string) $themesbazar['logo_upload']['url'];
	$sokal_logo_w   = isset( $themesbazar['logo_upload']['width'] ) ? (int) $themesbazar['logo_upload']['width'] : 0;
	$sokal_logo_h   = isset( $themesbazar['logo_upload']['height'] ) ? (int) $themesbazar['logo_upload']['height'] : 0;
}
$sokal_logo_url = trim( $sokal_logo_url );

/* ---------- Banner (legacy settings) ---------- */
$sokal_ban_url  = ! empty( $themesbazar['bannar_upload']['url'] ) ? trim( (string) $themesbazar['bannar_upload']['url'] ) : '';
$sokal_ban_w    = isset( $themesbazar['bannar_upload']['width'] ) ? (int) $themesbazar['bannar_upload']['width'] : 0;
$sokal_ban_h    = isset( $themesbazar['bannar_upload']['height'] ) ? (int) $themesbazar['bannar_upload']['height'] : 0;
$sokal_ban_link = ! empty( $themesbazar['bannar-link']['bannar-url'] ) ? (string) $themesbazar['bannar-link']['bannar-url'] : '';

/* ---------- Social links: new settings first, then legacy ---------- */
$sokal_legacy_social = ( isset( $themesbazar['social-link'] ) && is_array( $themesbazar['social-link'] ) ) ? $themesbazar['social-link'] : array();
$sokal_svg_x         = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18.9 1.2h3.7l-8 9.2 9.4 12.4h-7.4l-5.8-7.6-6.6 7.6H.5l8.6-9.8L0 1.2h7.6l5.2 6.9 6.1-6.9zm-1.3 19.4h2L6.5 3.2H4.3l13.3 17.4z"/></svg>';
$sokal_svg_tg        = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 0a12 12 0 1 0 0 24 12 12 0 0 0 0-24zm5.6 8.2-1.9 9c-.1.6-.5.8-1.1.5l-3-2.2-1.4 1.4c-.2.2-.3.3-.6.3l.2-3.1 5.6-5.1c.2-.2 0-.3-.4-.1l-6.9 4.4-3-.9c-.6-.2-.7-.6.1-.9l11.6-4.5c.5-.2 1 .1.8 1.2z"/></svg>';
$sokal_socials       = array(
	// Same icons and colours as the original header (fa icon, inline colour).
	'facebook'  => array( 'social_facebook', 'facebook-url', 'ফেসবুক', '<i class="sokal-hdr-logo" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28" focusable="false"><circle cx="12" cy="12" r="12" fill="#0866FF"/><path fill="#fff" d="M15.12 15.47l.53-3.47h-3.33V9.75c0-.95.47-1.88 1.95-1.88h1.51V4.92s-1.37-.23-2.68-.23c-2.74 0-4.53 1.66-4.53 4.66V12H5.53v3.47h3.04V24a12.1 12.1 0 0 0 3.75 0v-8.53h2.8z"/></svg></i>' ),
	'x'         => array( 'social_x', 'twitter-url', 'এক্স (টুইটার)', '<i class="sokal-hdr-svg" aria-hidden="true" style="color:#5DA7DA;">' . $sokal_svg_x . '</i>' ),
	'youtube'   => array( 'social_youtube', 'youtube-url', 'ইউটিউব', '<i class="sokal-hdr-logo" aria-hidden="true"><svg viewBox="0 0 28 20" width="34" height="24" focusable="false"><rect width="28" height="20" rx="5.5" fill="#FF0000"/><path fill="#fff" d="M11.2 5.6v8.8L18.6 10z"/></svg></i>' ),
	'whatsapp'  => array( 'social_whatsapp', '', 'হোয়াটসঅ্যাপ', '<i class="fa fa-whatsapp" aria-hidden="true" style="color:#25D366;"></i>' ),
	'instagram' => array( 'social_instagram', '', 'ইনস্টাগ্রাম', '<i class="fa fa-instagram" aria-hidden="true" style="color:#E4405F;"></i>' ),
	'telegram'  => array( 'social_telegram', '', 'টেলিগ্রাম', '<i class="fa fa-paper-plane" aria-hidden="true" style="color:#26A5E4;"></i>' ),
	'android'   => array( '', 'android-url', 'অ্যান্ড্রয়েড অ্যাপ', '<i class="fa fa-android" aria-hidden="true" style="color:#A5D11C;"></i>' ),
);
$sokal_social_links  = array();
foreach ( $sokal_socials as $sokal_net => $sokal_def ) {
	$sokal_url = $sokal_def[0] ? trim( (string) sokal_opt( $sokal_def[0] ) ) : '';
	if ( '' === $sokal_url && $sokal_def[1] && ! empty( $sokal_legacy_social[ $sokal_def[1] ] ) ) {
		$sokal_url = trim( (string) $sokal_legacy_social[ $sokal_def[1] ] );
	}
	if ( '' === $sokal_url || '#' === $sokal_url ) {
		continue;
	}
	$sokal_social_links[ $sokal_net ] = array( $sokal_url, $sokal_def[2], $sokal_def[3] );
}

$sokal_container = ( isset( $themesbazar['full-body-website'] ) && 2 == $themesbazar['full-body-website'] ) ? 'container' : 'container-fluid';
?>
<?php /* No DOCTYPE on purpose: the original theme had none, and its layout was built for that browser rendering mode. */ ?>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

	<section class="body-section">
		<div class="<?php echo esc_attr( $sokal_container ); ?> main_wbsite">

			<!--========================== phone header (≤767px) ==========================-->

			<div class="sokal-mhead" style="--sokal-mlogo:<?php echo (int) sokal_int( sokal_opt( 'mobile_logo_height', 56 ), 24, 120 ); ?>px">
				<div class="sokal-mhead__date">
					<?php echo sokal_today_html( true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
				</div>
				<div class="sokal-mhead__bar">
					<button type="button" class="sokal-mhead__btn sokal-mhead__menu" aria-controls="sokal-mdrawer" aria-expanded="false" aria-label="<?php echo esc_attr( 'মেনু' ); ?>">
						<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path d="M3 6h18M3 12h18M3 18h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
					<a class="sokal-mhead__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php if ( '' !== $sokal_logo_url ) : ?>
						<img src="<?php echo esc_url( $sokal_logo_url ); ?>" alt="<?php echo esc_attr( sokal_site_name() ); ?>"<?php if ( $sokal_logo_w && $sokal_logo_h ) : ?> width="<?php echo (int) $sokal_logo_w; ?>" height="<?php echo (int) $sokal_logo_h; ?>"<?php endif; ?> decoding="async">
						<?php else : ?>
						<span class="sokal-site-name"><?php echo esc_html( sokal_site_name() ); ?></span>
						<?php endif; ?>
					</a>
					<div class="sokal-mhead__actions">
						<?php if ( function_exists( 'sokal_mode_toggle' ) ) { sokal_mode_toggle(); } ?>
						<button type="button" class="sokal-mhead__btn sokal-mhead__search-btn" aria-controls="sokal-msearch" aria-expanded="false" aria-label="<?php echo esc_attr( 'অনুসন্ধান' ); ?>">
							<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M15.5 15.5 21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						</button>
					</div>
				</div>
				<form id="sokal-msearch" class="sokal-mhead__search" method="get" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" hidden>
					<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( ! empty( $themesbazar['placeholder'] ) ? $themesbazar['placeholder'] : 'খুঁজুন...' ); ?>" aria-label="<?php echo esc_attr( 'অনুসন্ধান' ); ?>">
					<button type="submit"><?php echo esc_html( ! empty( $themesbazar['search'] ) ? $themesbazar['search'] : 'খুঁজুন' ); ?></button>
				</form>
				<nav class="sokal-mhead__nav" aria-label="<?php echo esc_attr( 'বিভাগ' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'main-menu',
							'menu_class'     => 'sokal-mhead__navlist',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => 'default_main_menu',
						)
					);
					?>
				</nav>
				<div id="sokal-mdrawer" class="sokal-mdrawer" hidden>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'main-menu',
							'menu_class'     => 'sokal-mdrawer__list',
							'container'      => false,
							'depth'          => 2,
							'fallback_cb'    => 'default_main_menu',
						)
					);
					?>
				</div>
				<div class="sokal-mhead__ticker row">
					<?php sokal_ticker( 'top' ); ?>
				</div>
			</div>

			<!--========================== top bar: ticker + today (desktop/tablet) ==========================-->

			<div class="row sokal-dhead sokal-topbar">
				<div class="sokal-topbar__ticker">
					<?php sokal_ticker( 'top' ); ?>
				</div>
				<div class="sokal-topbar__today">
					<svg class="sokal-topbar__cal" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 14h2M11 14h2M15 14h2M7 17h2M11 17h2" stroke="currentColor" stroke-width="2"/></svg>
					<?php echo sokal_today_html( true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
				</div>
			</div>

			<!--========================== header: logo & banner ==========================-->

			<?php $sokal_ads = function_exists( 'sokal_ads_on' ) && sokal_ads_on(); ?>
			<div class="row sokal-brand-row" style="--sokal-dlogo:<?php echo (int) sokal_int( sokal_opt( 'header_logo_height', 110 ), 40, 250 ); ?>px">
				<div class="<?php echo $sokal_ads ? 'col-md-4 col-sm-4 logo' : 'col-md-12 col-sm-12 logo sokal-logo-center'; ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php if ( '' !== $sokal_logo_url ) : ?>
						<img src="<?php echo esc_url( $sokal_logo_url ); ?>" alt="<?php echo esc_attr( sokal_site_name() ); ?>"<?php if ( $sokal_logo_w && $sokal_logo_h ) : ?> width="<?php echo (int) $sokal_logo_w; ?>" height="<?php echo (int) $sokal_logo_h; ?>"<?php endif; ?> fetchpriority="high" decoding="async">
						<?php else : ?>
						<span class="sokal-site-name"><?php echo esc_html( sokal_site_name() ); ?></span>
						<?php endif; ?>
					</a>
				</div>
				<div class="top-hdr-social sokal-brand-tools">
					<ul>
						<?php foreach ( $sokal_social_links as $sokal_net => $sokal_link ) : ?>
						<li><a href="<?php echo esc_url( $sokal_link[0] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $sokal_link[1] ); ?>"> <?php echo $sokal_link[2]; // phpcs:ignore WordPress.Security.EscapeOutput -- static markup defined above. ?></a></li>
						<?php endforeach; ?>
						<?php if ( function_exists( 'sokal_mode_toggle' ) ) : ?>
						<li class="sokal-hdr-mode"><?php sokal_mode_toggle(); ?></li>
						<?php endif; ?>
					</ul>
				</div>
				<?php if ( $sokal_ads ) : // banner is an ad: shown only when "Show ads" is on ?>
				<div class="col-md-8 col-sm-8 bannar">
					<?php if ( '' !== $sokal_ban_url ) : ?>
						<?php if ( '' !== $sokal_ban_link ) : ?><a href="<?php echo esc_url( $sokal_ban_link ); ?>" target="_blank" rel="noopener"><?php endif; ?>
						<img src="<?php echo esc_url( $sokal_ban_url ); ?>" alt="<?php echo esc_attr( 'বিজ্ঞাপন' ); ?>"<?php if ( $sokal_ban_w && $sokal_ban_h ) : ?> width="<?php echo (int) $sokal_ban_w; ?>" height="<?php echo (int) $sokal_ban_h; ?>"<?php endif; ?> decoding="async">
						<?php if ( '' !== $sokal_ban_link ) : ?></a><?php endif; ?>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<!--========================== menu ==========================-->

			<div class="row sokal-dhead">
				<div class="col-xs-12 col-md-12 col-sm-12">
					<div id="menu-area" class="menu_area">
						<div class="menu_bottom">
							<nav role="navigation" class="navbar navbar-default mainmenu">
								<!-- Brand and toggle get grouped for better mobile display -->
								<div class="navbar-header">
									<button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle" aria-controls="navbarCollapse" aria-expanded="false">
										<span class="sr-only"><?php echo esc_html( 'মেনু' ); ?></span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
									</button>
									<?php if ( function_exists( 'sokal_mode_toggle' ) ) : ?>
										<div class="sokal-nav-mode"><?php sokal_mode_toggle(); ?></div>
									<?php endif; ?>
								</div>
								<!-- Collection of nav links and other content for toggling -->
								<div id="navbarCollapse" class="collapse navbar-collapse">
									<?php
									wp_nav_menu(
										array(
											'theme_location' => 'main-menu',
											'menu_class'     => 'nav navbar-nav',
											'fallback_cb'    => 'default_main_menu',
											'walker'         => new wp_bootstrap_navwalker(),
										)
									);
									?>
								</div>
							</nav>
						</div><!-- /.menu_bottom -->
					</div>
				</div>
			</div>

			<!--========================== bottom ticker ==========================-->

			<div class="row sokal-dhead">
				<?php if ( ! sokal_opt( 'ticker_enabled' ) ) { sokal_ticker( 'bottom' ); } // only one ticker: the bottom bar shows only when the main ticker is off ?>
			</div>
