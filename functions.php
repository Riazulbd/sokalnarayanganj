<?php
global $themesbazar;

add_theme_support( 'automatic-feed-links' );
add_theme_support('title-tag');
load_theme_textdomain( 'newsfresh', get_template_directory() . '/languages' );
add_theme_support( 'post-formats',  array ( 'aside', 'gallery', 'quote', 'image', 'video' ) );

/* --------------Post Thumbnails Add-------------- */
    
add_theme_support( 'post-thumbnails');
set_post_thumbnail_size( 600, 337, true );
add_image_size( 'single-thumbnail', 600, 337, true );
add_image_size( 'card-thumb', 240, 135, true );   // small story cards
add_image_size( 'single-hero', 1200, 675, true ); // article hero



/* All theme code lives in include/. Each file has one owner; see the header comment in each. */
$sokal_includes = array(
	'include/settings-api.php',   // sokal_opt() + defaults: shared contract
	'include/settings.php',       // Theme Settings screen, migration, $themesbazar shim
	'include/helpers.php',
	'include/performance.php',    // fragment cache, headers, cleanup
	'include/views.php',          // most-read counter
	'include/assets.php',         // CSS/JS delivery
	'include/mode.php',           // light/dark mode, header/footer colours
	'include/seo.php',            // language, titles, meta/OG
	'include/ticker.php',
	'include/home.php',           // home page sections
	'include/sidebar-tabs.php',
	'include/related.php',
	'include/share.php',          // share buttons
	'include/share-picture.php',  // share picture
	'include/footer-content.php',
	'include/menu.php',
	'include/wp-bootstrap-navwalker.php',
	'include/widget.php',
	'metabox/init.php',
	'metabox/functions.php',
);
foreach ( $sokal_includes as $sokal_file ) {
	if ( file_exists( get_template_directory() . '/' . $sokal_file ) ) {
		require_once get_template_directory() . '/' . $sokal_file;
	}
}
unset( $sokal_includes, $sokal_file );
