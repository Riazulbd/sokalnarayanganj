<?php
/**
 * Theme Settings: native admin screen that replaces the Redux framework.
 *
 * - Every setting lives in ONE autoloaded option, `sokal_settings` (see settings-api.php).
 * - The old Redux array (option `themesbazar`) is copied ONCE into sokal_settings['legacy'],
 *   merged with the Redux field defaults listed in sokal_legacy_fields().
 * - global $themesbazar is rebuilt from that copy at include time, so every template and
 *   css/dynamic-css.php keeps working without Redux.
 *
 * Owner: settings agent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------
 * 1. Legacy (Redux) field list, extracted from the old lib/sample/config.php.
 *
 * tab: where the field is edited ('hidden' = superseded by a new setting and kept in
 * sync on save, see sokal_settings_legacy_map()). Types: legacy_image (array url/id),
 * multitext (array of sub-keys), typography (array), sorter (Show/Hide), legacy_cat.
 * '{TPL}' in a default is replaced with the theme URL.
 * ------------------------------------------------------------------ */
function sokal_legacy_fields() {
	return array(
		'site-content' => array('type' => 'select','label' => 'Site Content','default' => '2','tab' => 'general','group' => 'Header and layout','options' => array(1 => 'Bangla',2 => 'English')),
		'logo_upload' => array('type' => 'legacy_image','label' => 'Logo Uploader','default' => array('url' => '{TPL}/images/logo.gif'),'tab' => 'hidden','group' => ''),
		'favicon' => array('type' => 'legacy_image','label' => 'Favicon Uploader','default' => array('url' => '{TPL}/images/favicon.png'),'tab' => 'hidden','group' => ''),
		'bannar_upload' => array('type' => 'legacy_image','label' => 'Bannar Uploader','default' => array('url' => '{TPL}/images/bannar.gif'),'tab' => 'general','group' => 'Header and layout'),
		'bannar-link' => array('type' => 'multitext','label' => 'Bannar Link','default' => array('bannar-url' => 'http://#.com/'),'tab' => 'general','group' => 'Header and layout','subs' => array('bannar-url' => 'Bannar Url'),'url' => true),
		'placeholder' => array('type' => 'text','label' => 'Search placeholder','default' => 'Write Here..','tab' => 'general','group' => 'Labels and text'),
		'search' => array('type' => 'text','label' => 'Search Button Text','default' => 'Search','tab' => 'general','group' => 'Labels and text'),
		'social-link' => array('type' => 'multitext','label' => 'Social Profiles','default' => array('facebook-url' => 'https://www.facebook.com//','twitter-url' => 'https://twitter.com/','googleplus-url' => 'https://plus.google.com/u/0/','youtube-url' => 'https://www.youtube.com/channel/','android-url' => '#'),'tab' => 'footer','group' => 'Old footer text','subs' => array('facebook-url' => 'Facebook Link','twitter-url' => 'Twitter Link','googleplus-url' => 'Googleplus Link','youtube-url' => 'Youtube Link','android-url' => 'Android Link'),'url' => true,'only' => array('googleplus-url','android-url')),
		'top_scroll' => array('type' => 'select','label' => 'Top Scroll','default' => '2','tab' => 'home','group' => 'Old scrolling bars','options' => array(1 => 'Show',2 => 'Hide')),
		'top_scroll_set' => array('type' => 'select','label' => 'Header Scrool Setting','default' => '1','tab' => 'home','group' => 'Old scrolling bars','options' => array(1 => 'Latest',2 => 'Category',3 => 'Custom')),
		'top_scroll_title' => array('type' => 'text','label' => 'Header Scroll Title','default' => 'Title : ','tab' => 'hidden','group' => ''),
		'top_scroll_cat' => array('type' => 'legacy_cat','label' => 'Select Scrool Category','default' => '1','tab' => 'hidden','group' => ''),
		'top_scroll_content' => array('type' => 'html','label' => 'Header Scroll Content','default' => 'Welcome To Our Website...','tab' => 'home','group' => 'Old scrolling bars'),
		'how_top_scroll' => array('type' => 'number','label' => 'How Many Post','default' => 10,'tab' => 'hidden','group' => '','min' => 1,'max' => 20),
		'header-scrool-icon' => array('type' => 'text','label' => 'Scrool Icon','default' => 'fa fa-square','tab' => 'general','group' => 'Labels and text'),
		'bottom_scroll' => array('type' => 'select','label' => 'Bottom Scroll','default' => '1','tab' => 'home','group' => 'Old scrolling bars','options' => array(1 => 'Show',2 => 'Hide')),
		'bottom_scroll_set' => array('type' => 'select','label' => 'Bottom Scroll Setting','default' => '1','tab' => 'home','group' => 'Old scrolling bars','options' => array(1 => 'Latest',2 => 'Category',3 => 'Custom')),
		'bottom_scroll_title' => array('type' => 'text','label' => 'Bottom Scroll Title','default' => 'Title : ','tab' => 'home','group' => 'Old scrolling bars'),
		'bottom_scroll_cat' => array('type' => 'legacy_cat','label' => 'Select Scrool Category','default' => '1','tab' => 'home','group' => 'Old scrolling bars'),
		'bottom_scroll_content' => array('type' => 'html','label' => 'Bottom Scroll Content','default' => 'Welcome To Our Website...','tab' => 'home','group' => 'Old scrolling bars'),
		'how_bottom_scroll' => array('type' => 'number','label' => 'Bottom scroll: how many posts','default' => 10,'tab' => 'home','group' => 'Old scrolling bars','min' => 1,'max' => 20),
		'editorial' => array('type' => 'html','label' => 'Editorial Text','default' => 'Office : Dhaka,Bngkadesh. Mobile : 01700-000000, Email : Sales@gmail.com','tab' => 'footer','group' => 'Old footer text'),
		'editorial-tow' => array('type' => 'html','label' => 'Address Text','default' => 'Office : Dhaka,Bngkadesh. Mobile : 01700-000000, Email : Sales@gmail.com','tab' => 'footer','group' => 'Old footer text'),
		'copyright' => array('type' => 'html','label' => 'Copyright Text','default' => '&copy; All rights reserved &copy; 2019 Breaking News','tab' => 'hidden','group' => ''),
		'homepage-section' => array('type' => 'sorter','label' => 'Homepage Layout Manager','default' => array('Show' => array('section-one' => 'Section One','section-two' => 'Section Two'),'Hide' => array()),'tab' => 'home','group' => 'Old home sections','options' => array('section-one' => 'Section One','section-two' => 'Section Two')),
		'catagory-style' => array('type' => 'select','label' => 'Select Catagory Style','default' => '1','tab' => 'home','group' => 'Old home sections','options' => array(1 => 'Style One',2 => 'Style Two',3 => 'Style Three',4 => 'Style Four')),
		'section-one' => array('type' => 'select','label' => 'section-one','default' => '1','tab' => 'hidden','group' => '','options' => array(1 => 'Section One')),
		'cat-one' => array('type' => 'legacy_cat','label' => '1st Category','default' => '1','tab' => 'hidden','group' => ''),
		'excerpt-cat-one' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 40,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 200),
		'section-two' => array('type' => 'select','label' => 'section-two','default' => '1','tab' => 'hidden','group' => '','options' => array(1 => 'Section Two')),
		'cat-two' => array('type' => 'legacy_cat','label' => '2nd Category','default' => '1','tab' => 'hidden','group' => ''),
		'excerpt-cat-two' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 20,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 200),
		'how_post_two' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-three' => array('type' => 'legacy_cat','label' => '3rd Category','default' => '1','tab' => 'hidden','group' => ''),
		'excerpt-cat-three' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 20,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 200),
		'how_post_three' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-four' => array('type' => 'legacy_cat','label' => '4th Category','default' => '1','tab' => 'hidden','group' => ''),
		'excerpt-cat-four' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 20,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 200),
		'how_post_four' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-five' => array('type' => 'legacy_cat','label' => '5th Category','default' => '1','tab' => 'hidden','group' => ''),
		'excerpt-cat-five' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 20,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 200),
		'how_post_five' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-six' => array('type' => 'legacy_cat','label' => '6th Category','default' => '1','tab' => 'hidden','group' => ''),
		'excerpt-cat-six' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 20,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 200),
		'how_post_six' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-seven' => array('type' => 'legacy_cat','label' => '7th Category','default' => '1','tab' => 'hidden','group' => ''),
		'how_post_seven' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-eight' => array('type' => 'legacy_cat','label' => '8th Category','default' => '1','tab' => 'hidden','group' => ''),
		'how_post_eight' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-nine' => array('type' => 'legacy_cat','label' => '9th Category','default' => '1','tab' => 'hidden','group' => ''),
		'how_post_nine' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-ten' => array('type' => 'legacy_cat','label' => '10th Category','default' => '1','tab' => 'hidden','group' => ''),
		'how_post_ten' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'cat-eleven' => array('type' => 'legacy_cat','label' => '11th Category','default' => '1','tab' => 'home','group' => 'Old home sections'),
		'how_post_eleven' => array('type' => 'number','label' => 'How Post Show in Category','default' => 5,'tab' => 'home','group' => 'Old home sections','min' => 1,'max' => 20),
		'full-body-website' => array('type' => 'select','label' => 'Full and Box Website','default' => '2','tab' => 'general','group' => 'Header and layout','options' => array(1 => 'Full',2 => 'Box')),
		'last' => array('type' => 'text','label' => 'Last Update Title','default' => 'Last Update','tab' => 'general','group' => 'Labels and text'),
		'lastpost' => array('type' => 'number','label' => 'Latest posts box: how many posts','default' => 10,'tab' => 'home','group' => 'Sidebar boxes','min' => 1,'max' => 20),
		'popular' => array('type' => 'text','label' => 'PopularPost Title','default' => 'Popular Post','tab' => 'general','group' => 'Labels and text'),
		'more_news' => array('type' => 'text','label' => 'More Category Text','default' => 'More News..','tab' => 'general','group' => 'Labels and text'),
		'facebook' => array('type' => 'select','label' => 'Facebook','default' => '1','tab' => 'home','group' => 'Sidebar boxes','options' => array(1 => 'Facebook Show',2 => 'Facebook Hide')),
		'facebook-title' => array('type' => 'text','label' => 'Facebook Title','default' => 'Our Like Page','tab' => 'general','group' => 'Labels and text'),
		'facebook-link' => array('type' => 'multitext','label' => 'Facebook Link','default' => array('face-url' => 'https://www.facebook.com/wordpress/'),'tab' => 'home','group' => 'Sidebar boxes','subs' => array('face-url' => 'Facebook Url'),'url' => true),
		'facebook-width' => array('type' => 'number','label' => 'Facebook width','default' => 390,'tab' => 'home','group' => 'Sidebar boxes','min' => 100,'max' => 1000),
		'facebook-height' => array('type' => 'number','label' => 'Facebook Height','default' => 300,'tab' => 'home','group' => 'Sidebar boxes','min' => 100,'max' => 2000),
		'acrive-calender' => array('type' => 'select','label' => 'Archive Calender','default' => '1','tab' => 'home','group' => 'Sidebar boxes','options' => array(1 => 'Calender Show',2 => 'Calender Hide')),
		'calender-title' => array('type' => 'text','label' => 'Archive Title Text','default' => 'Archive','tab' => 'general','group' => 'Labels and text'),
		'select-date' => array('type' => 'text','label' => 'Date Title text','default' => 'Select Your Date','tab' => 'general','group' => 'Labels and text'),
		'date-search' => array('type' => 'text','label' => 'Search Title text','default' => 'Search','tab' => 'general','group' => 'Labels and text'),
		'home' => array('type' => 'text','label' => 'Home Text','default' => 'Home','tab' => 'articles','group' => 'Single post'),
		'update' => array('type' => 'text','label' => 'Update Time Text','default' => 'Update Time :','tab' => 'articles','group' => 'Single post'),
		'view-tab' => array('type' => 'select','label' => 'view Count','default' => '1','tab' => 'articles','group' => 'Single post','options' => array(1 => 'Time View Show',2 => 'Time View Hide')),
		'count' => array('type' => 'text','label' => 'Post Count Text','default' => 'Time View','tab' => 'articles','group' => 'Single post'),
		'social_share' => array('type' => 'select','label' => 'Social Share','default' => '1','tab' => 'articles','group' => 'Single post','options' => array(1 => 'Social Icon Show',2 => 'Social Icon Hide')),
		'social_title' => array('type' => 'text','label' => 'Social Title','default' => 'Please Share This Post in Your Social Media','tab' => 'articles','group' => 'Single post'),
		'coment' => array('type' => 'select','label' => 'Coment Box','default' => '1','tab' => 'articles','group' => 'Single post','options' => array(1 => 'Coment Box Show',2 => 'Coment Box Hide')),
		'more-news-category' => array('type' => 'text','label' => 'More News Category Text','default' => 'More News Of This Category','tab' => 'articles','group' => 'Single post'),
		'word-archive' => array('type' => 'number','label' => 'How Many Word Show in Content','default' => 30,'tab' => 'articles','group' => 'Category and archive pages','min' => 1,'max' => 200),
		'read-more-archive' => array('type' => 'text','label' => 'Read More Text','default' => 'read more','tab' => 'articles','group' => 'Category and archive pages'),
		'body' => array('type' => 'color','label' => 'Body Background','default' => '#F2F2F2','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'body-font' => array('type' => 'typography','label' => 'Body Font Size','default' => array('font-size' => '17px'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'hscrool-background' => array('type' => 'color','label' => 'Scrool Background','default' => '#8B0708','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'hscrool-font' => array('type' => 'typography','label' => 'Header Scrool Title font color and Font Size','default' => array('text-align' => 'left','color' => '#fff'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'menu-background' => array('type' => 'color','label' => 'Menu Background','default' => '#4C4B4B','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'menu-font' => array('type' => 'typography','label' => 'Menu Color and Font Size','default' => array('font-size' => '16px','color' => '#fff'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_one-title-background' => array('type' => 'color','label' => 'catagory Title One Color','default' => '#006699','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_one-title-font' => array('type' => 'typography','label' => 'Catagory Title One Font','default' => array('color' => '#fff','font-weight' => '400','font-size' => '18px'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_two-title-background' => array('type' => 'color','label' => 'catagory Title Two Color','default' => '#8B0708','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_two-title-font' => array('type' => 'typography','label' => 'Catagory Title Two Font','default' => array('color' => '#fff','font-weight' => '400','font-size' => '18px'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_three-title-background' => array('type' => 'color','label' => 'catagory Title Three Color','default' => '#f7f7f7','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_three-title-font' => array('type' => 'typography','label' => 'Catagory Title Three Font','default' => array('color' => '#000','font-weight' => '400','font-size' => '18px'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_four-title-background' => array('type' => 'color','label' => 'catagory Title Four Color','default' => '#01284F','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'cat_four-title-font' => array('type' => 'typography','label' => 'Catagory Title Four Font','default' => array('color' => '#fff','font-weight' => '400','font-size' => '17px'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'heading-01-font' => array('type' => 'typography','label' => 'Heading font 1 (lead headlines)','default' => array('color' => '#000','font-weight' => '400','font-size' => '22px','line-height' => 'auto'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'heading-02-font' => array('type' => 'typography','label' => 'Heading font 2','default' => array('color' => '#000','font-weight' => '400','font-size' => '20px','line-height' => 'auto'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'heading-03-font' => array('type' => 'typography','label' => 'Heading font 3 (small cards)','default' => array('color' => '#000','font-weight' => '400','font-size' => '17px','line-height' => 'auto'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'more-news' => array('type' => 'typography','label' => 'More News Font','default' => array('color' => '#15520F','font-weight' => '400','font-size' => '15px'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'footer-background' => array('type' => 'color','label' => 'Footer Background','default' => '#05668A','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'footer-font' => array('type' => 'typography','label' => 'Footer Font Color and Font Size','default' => array('color' => '#fff'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
		'btm-footer-background' => array('type' => 'color','label' => 'Bottom Footer Background','default' => '#088FC1','tab' => 'colours','group' => 'Old theme colours and fonts'),
		'root-font' => array('type' => 'typography','label' => 'Copyrigh font color','default' => array('color' => '#fff'),'tab' => 'colours','group' => 'Old theme colours and fonts'),
	);
}

/** Redux defaults for every legacy key ('{TPL}' resolved). */
function sokal_legacy_defaults() {
	static $d = null;
	if ( null === $d ) {
		$tpl = get_template_directory_uri();
		$d   = array();
		foreach ( sokal_legacy_fields() as $id => $f ) {
			$v = $f['default'];
			if ( is_string( $v ) ) {
				$v = str_replace( '{TPL}', $tpl, $v );
			} elseif ( is_array( $v ) ) {
				array_walk_recursive(
					$v,
					function ( &$item ) use ( $tpl ) {
						if ( is_string( $item ) ) {
							$item = str_replace( '{TPL}', $tpl, $item );
						}
					}
				);
			}
			$d[ $id ] = $v;
		}
	}
	return $d;
}

/**
 * Merge saved legacy values over the defaults. Array fields (media, typography,
 * multitext) are merged one level deep so a missing sub-key falls back to its
 * default; the sorter is taken as saved (a hidden section must stay hidden).
 */
function sokal_legacy_merge( $defaults, $saved ) {
	$out    = $defaults;
	$fields = sokal_legacy_fields();
	foreach ( (array) $saved as $k => $v ) {
		$is_sorter = isset( $fields[ $k ] ) && 'sorter' === $fields[ $k ]['type'];
		if ( ! $is_sorter && is_array( $v ) && isset( $out[ $k ] ) && is_array( $out[ $k ] ) ) {
			$out[ $k ] = array_merge( $out[ $k ], $v );
		} else {
			$out[ $k ] = $v;
		}
	}
	return $out;
}

/** The legacy array exactly as templates expect it in global $themesbazar. */
function sokal_legacy_values() {
	$s = sokal_settings();
	return sokal_bangla_labels( sokal_legacy_merge( sokal_legacy_defaults(), is_array( $s['legacy'] ) ? $s['legacy'] : array() ) );
}

/**
 * The whole site is Bengali: labels still on their English Redux default are
 * shown in Bengali (custom text the admin typed is left alone), and dates and
 * numbers always use Bengali (site-content = 1).
 */
function sokal_bangla_labels( $v ) {
	$map = array(
		'placeholder'           => array( 'Write Here..', 'এখানে লিখুন...' ),
		'search'                => array( 'Search', 'খুঁজুন' ),
		'top_scroll_title'      => array( 'Title :', 'শিরোনাম :' ),
		'bottom_scroll_title'   => array( 'Title :', 'শিরোনাম :' ),
		'top_scroll_content'    => array( 'Welcome To Our Website...', 'আমাদের ওয়েবসাইটে স্বাগতম...' ),
		'bottom_scroll_content' => array( 'Welcome To Our Website...', 'আমাদের ওয়েবসাইটে স্বাগতম...' ),
		'last'                  => array( 'Last Update', 'সর্বশেষ আপডেট' ),
		'popular'               => array( 'Popular Post', 'জনপ্রিয় সংবাদ' ),
		'more_news'             => array( 'More News..', 'আরও সংবাদ' ),
		'facebook-title'        => array( 'Our Like Page', 'আমাদের ফেসবুক পেজ' ),
		'calender-title'        => array( 'Archive', 'আর্কাইভ' ),
		'select-date'           => array( 'Select Your Date', 'তারিখ নির্বাচন করুন' ),
		'date-search'           => array( 'Search', 'খুঁজুন' ),
		'home'                  => array( 'Home', 'প্রচ্ছদ' ),
		'update'                => array( 'Update Time :', 'আপডেট :' ),
		'count'                 => array( 'Time View', 'বার পড়া হয়েছে' ),
		'social_title'          => array( 'Please Share This Post in Your Social Media', 'শেয়ার করুন' ),
		'more-news-category'    => array( 'More News Of This Category', 'এই বিভাগের আরও সংবাদ' ),
		'read-more-archive'     => array( 'read more', 'বিস্তারিত' ),
	);
	foreach ( $map as $key => $pair ) {
		$cur = isset( $v[ $key ] ) && is_string( $v[ $key ] ) ? trim( $v[ $key ] ) : '';
		if ( '' === $cur || 0 === strcasecmp( rtrim( $cur, ' .:' ), rtrim( $pair[0], ' .:' ) ) ) {
			$v[ $key ] = $pair[1];
		}
	}
	$v['site-content'] = '1'; // Bengali dates and digits everywhere.
	return $v;
}

/** Read a nested value: sokal_legacy_get( $arr, array( 'logo_upload', 'url' ) ). */
function sokal_legacy_get( $arr, $path ) {
	foreach ( (array) $path as $p ) {
		if ( ! is_array( $arr ) || ! array_key_exists( $p, $arr ) ) {
			return null;
		}
		$arr = $arr[ $p ];
	}
	return $arr;
}

function sokal_legacy_set( &$arr, $path, $value ) {
	$path = (array) $path;
	$k    = array_shift( $path );
	if ( ! $path ) {
		$arr[ $k ] = $value;
		return;
	}
	if ( ! isset( $arr[ $k ] ) || ! is_array( $arr[ $k ] ) ) {
		$arr[ $k ] = array();
	}
	sokal_legacy_set( $arr[ $k ], $path, $value );
}

/**
 * New key => legacy path, for values that exist in both places.
 * 'kind': string (copied only when non-empty), exact (always copied), int.
 */
function sokal_settings_legacy_map() {
	$map = array(
		'header_logo'     => array( array( 'logo_upload', 'url' ), 'url' ),
		'header_logo_id'  => array( array( 'logo_upload', 'id' ), 'id' ),
		'favicon'         => array( array( 'favicon', 'url' ), 'url' ),
		'favicon_id'      => array( array( 'favicon', 'id' ), 'id' ),
		'copyright'       => array( array( 'copyright' ), 'html' ),
		'social_facebook' => array( array( 'social-link', 'facebook-url' ), 'link' ),
		'social_x'        => array( array( 'social-link', 'twitter-url' ), 'link' ),
		'social_youtube'  => array( array( 'social-link', 'youtube-url' ), 'link' ),
		'ticker_label'    => array( array( 'top_scroll_title' ), 'text' ),
		'ticker_count'    => array( array( 'how_top_scroll' ), 'count' ),
		'lead_cat'        => array( array( 'cat-one' ), 'cat' ),
		'grid_cat'        => array( array( 'cat-two' ), 'cat' ),
	);
	return $map;
}

/** Legacy category keys that feed home_slots[0..7]. */
function sokal_settings_slot_keys() {
	return array( 'cat-three', 'cat-four', 'cat-five', 'cat-six', 'cat-seven', 'cat-eight', 'cat-nine', 'cat-ten' );
}

/* ------------------------------------------------------------------
 * 2. One-time migration from Redux.
 * ------------------------------------------------------------------ */
function sokal_settings_migrate() {
	$saved = get_option( 'sokal_settings', false );
	if ( false !== $saved && get_option( 'sokal_migrated' ) ) {
		return;
	}

	$settings = is_array( $saved ) ? $saved : array();
	$old      = get_option( 'themesbazar', array() );
	$old      = is_array( $old ) ? $old : array();
	$defaults = sokal_legacy_defaults();
	$legacy   = sokal_legacy_merge( $defaults, $old );
	if ( ! empty( $settings['legacy'] ) && is_array( $settings['legacy'] ) ) {
		$legacy = sokal_legacy_merge( $legacy, $settings['legacy'] );
	}

	if ( $old ) {
		// New keys the admin already saved win over the copied values.
		foreach ( sokal_settings_legacy_map() as $key => $m ) {
			if ( array_key_exists( $key, $settings ) ) {
				continue;
			}
			$v = sokal_legacy_get( $old, $m[0] );
			if ( null === $v || is_array( $v ) ) {
				continue;
			}
			$d = sokal_legacy_get( $defaults, $m[0] );
			switch ( $m[1] ) {
				case 'url':
				case 'link':
					$v = trim( (string) $v );
					if ( '' !== $v && $v !== $d ) {
						$settings[ $key ] = esc_url_raw( $v );
					}
					break;
				case 'html':
				case 'text':
					$v = (string) $v;
					if ( '' !== trim( $v ) && $v !== $d ) {
						$settings[ $key ] = 'html' === $m[1] ? wp_kses_post( $v ) : sanitize_text_field( $v );
					}
					break;
				case 'id':
					$settings[ $key ] = absint( $v );
					break;
				case 'count':
					$settings[ $key ] = sokal_int( $v, 1, 20 );
					break;
				case 'cat':
					$settings[ $key ] = absint( $v );
					break;
			}
		}
		if ( empty( $settings['header_logo'] ) ) {
			unset( $settings['header_logo_id'] );
		}
		if ( empty( $settings['favicon'] ) ) {
			unset( $settings['favicon_id'] );
		}
		// Keep the old top-ticker on/off choice (only if the site had actually saved it).
		$sokal_raw = get_option( 'themesbazar' );
		if ( ! array_key_exists( 'ticker_enabled', $settings ) && is_array( $sokal_raw ) && isset( $sokal_raw['top_scroll'] ) ) {
			$settings['ticker_enabled'] = ( 1 === (int) $sokal_raw['top_scroll'] ) ? 1 : 0;
		}
		if ( ! array_key_exists( 'ticker_cat', $settings ) && isset( $old['top_scroll_set'] ) && 2 === (int) $old['top_scroll_set'] && ! empty( $old['top_scroll_cat'] ) ) {
			$settings['ticker_cat'] = absint( $old['top_scroll_cat'] );
		}
		if ( ! array_key_exists( 'home_slots', $settings ) ) {
			$slots = array();
			foreach ( sokal_settings_slot_keys() as $k ) {
				$slots[] = isset( $old[ $k ] ) ? absint( $old[ $k ] ) : 0;
			}
			$settings['home_slots'] = $slots;
		}
	}

	$settings['legacy'] = $legacy;
	update_option( 'sokal_settings', $settings, true );
	update_option( 'sokal_migrated', 1, true );

	// Leftovers from Redux (audit 1.12).
	foreach ( array( 'themesbazar-transients', 'redux-framework-tracking', 'r_notice_data', 'redux_blast' ) as $opt ) {
		delete_option( $opt );
	}
}

/* ------------------------------------------------------------------
 * 3. global $themesbazar shim (built at include time, before any template).
 * ------------------------------------------------------------------ */
sokal_settings_migrate();

/**
 * One-time correction (v2.0.1). Under Redux, a setting the site never saved
 * was simply absent, so templates treated it as "off"/empty. The first
 * migration filled those gaps with Redux defaults, which switched on things
 * the site never showed (e.g. the bottom ticker with "Title :", the white
 * square icons). Restore the original behaviour for keys missing from the
 * site's saved Redux option: show/hide switches -> Hide, text -> empty.
 */
function sokal_settings_legacy_fix() {
	// Superseded by sokal_settings_legacy_fix2() (Redux did fill missing keys
	// with defaults, so this correction was wrong). Never run it again.
	if ( true || get_option( 'sokal_legacy_fix' ) ) {
		return;
	}
	$raw = get_option( 'themesbazar' );
	$s   = get_option( 'sokal_settings' );
	if ( is_array( $raw ) && $raw && is_array( $s ) && isset( $s['legacy'] ) && is_array( $s['legacy'] ) ) {
		$toggles = array( 'top_scroll', 'bottom_scroll', 'facebook', 'acrive-calender', 'view-tab', 'social_share', 'coment' );
		foreach ( sokal_legacy_fields() as $key => $f ) {
			if ( array_key_exists( $key, $raw ) ) {
				continue;
			}
			if ( in_array( $key, $toggles, true ) ) {
				$s['legacy'][ $key ] = '2';
			} elseif ( isset( $f['type'] ) && 'text' === $f['type'] ) {
				$s['legacy'][ $key ] = '';
			}
		}
		update_option( 'sokal_settings', $s, true );
		// Mark every cached page part stale (performance.php isn't loaded yet).
		update_option( 'sokal_cache_ver', (int) get_option( 'sokal_cache_ver', 1 ) + 1, true );
	}
	update_option( 'sokal_legacy_fix', 1, true );
}
sokal_settings_legacy_fix();

/**
 * v2.0.2: undo the v2.0.1 correction. Redux (save_defaults = true) filled any
 * missing field with its default on admin_init, so the original site behaved
 * as if those defaults were set. For keys missing from the saved Redux option,
 * put the Redux default back (exactly the keys v2.0.1 blanked or hid).
 */
function sokal_settings_legacy_fix2() {
	if ( get_option( 'sokal_legacy_fix2' ) ) {
		return;
	}
	$raw = get_option( 'themesbazar' );
	$s   = get_option( 'sokal_settings' );
	if ( get_option( 'sokal_legacy_fix' ) && is_array( $raw ) && $raw && is_array( $s ) && isset( $s['legacy'] ) && is_array( $s['legacy'] ) ) {
		$defaults = sokal_legacy_defaults();
		foreach ( sokal_legacy_fields() as $key => $f ) {
			if ( ! array_key_exists( $key, $raw ) && array_key_exists( $key, $defaults ) ) {
				$s['legacy'][ $key ] = $defaults[ $key ];
			}
		}
		update_option( 'sokal_settings', $s, true );
		update_option( 'sokal_cache_ver', (int) get_option( 'sokal_cache_ver', 1 ) + 1, true );
	}
	update_option( 'sokal_legacy_fix2', 1, true );
}
sokal_settings_legacy_fix2();

$GLOBALS['themesbazar'] = sokal_legacy_values();

/** Keep the shim fresh and drop cached fragments whenever settings change. */
function sokal_settings_changed() {
	$GLOBALS['themesbazar'] = sokal_legacy_values();
	if ( function_exists( 'sokal_flush_cache' ) ) {
		sokal_flush_cache();
	}
}
add_action( 'update_option_sokal_settings', 'sokal_settings_changed', 10, 0 );
add_action( 'add_option_sokal_settings', 'sokal_settings_changed', 10, 0 );

/* ------------------------------------------------------------------
 * 4. Schema for the admin screen.
 * ------------------------------------------------------------------ */
function sokal_settings_tabs() {
	return array(
		'general'  => 'General',
		'colours'  => 'Colours and mode',
		'home'     => 'Home page',
		'articles' => 'Articles',
		'share'    => 'Share picture',
		'footer'   => 'Footer',
		'speed'    => 'Speed and cache',
	);
}

function sokal_settings_share_styles() {
	if ( function_exists( 'sokal_share_picture_styles' ) ) {
		$styles = sokal_share_picture_styles();
		if ( is_array( $styles ) && $styles ) {
			return $styles;
		}
	}
	return array(
		'classic' => 'Classic',
		'dark'    => 'Dark',
		'light'   => 'Light',
		'band'    => 'Colour band',
		'strip'   => 'Wide strip',
	);
}

/** tab => list of groups( title, desc, fields( key => field ) ). */
function sokal_settings_schema() {
	$colour_auto = 'Leave empty to pick black or white automatically for contrast.';
	$schema      = array(
		'general'  => array(
			array(
				'title'  => 'Site identity',
				'fields' => array(
					'site_name'       => array( 'type' => 'text', 'label' => 'Site name', 'desc' => 'Empty = the WordPress Site Title.' ),
					'header_logo'     => array( 'type' => 'image', 'label' => 'Header logo', 'id_key' => 'header_logo_id' ),
					'favicon'         => array( 'type' => 'image', 'label' => 'Favicon', 'id_key' => 'favicon_id', 'desc' => 'Square PNG, at least 512x512.' ),
					'ads_enabled' => array( 'type' => 'checkbox', 'label' => 'Show ads', 'desc' => 'Header banner and all ad widget areas. Off = no ads anywhere on the site.' ),
					'header_logo_height' => array( 'type' => 'number', 'label' => 'Desktop logo height (px)', 'min' => 40, 'max' => 250, 'desc' => 'Centred header logo on computers and tablets. Default 110.' ),
					'footer_logo_height' => array( 'type' => 'number', 'label' => 'Footer logo height (px)', 'min' => 30, 'max' => 200, 'desc' => 'Default 90.' ),
					'mobile_logo_height' => array( 'type' => 'number', 'label' => 'Mobile logo height (px)', 'min' => 24, 'max' => 120, 'desc' => 'Logo height in the phone header. Default 56.' ),
					'card_title_size' => array( 'type' => 'number', 'label' => 'Story card headline size (px)', 'min' => 0, 'max' => 60, 'desc' => '0 = keep the theme sizes.' ),
				),
			),
		),
		'colours'  => array(
			array(
				'title'  => 'Light and dark mode',
				'fields' => array(
					'mode'        => array( 'type' => 'select', 'label' => 'Mode', 'options' => array( 'auto' => 'Automatic (by visitor time)', 'light' => 'Always light', 'dark' => 'Always dark' ) ),
					'light_start' => array( 'type' => 'number', 'label' => 'Light mode starts at (hour)', 'min' => 0, 'max' => 23, 'desc' => 'Visitor local time, 0-23. Used in automatic mode.' ),
					'light_end'   => array( 'type' => 'number', 'label' => 'Dark mode starts at (hour)', 'min' => 0, 'max' => 23 ),
				),
			),
			array(
				'title'  => 'Header colours',
				'fields' => array(
					'header_bg_light'   => array( 'type' => 'color', 'label' => 'Header background (light)' ),
					'header_text_light' => array( 'type' => 'color', 'label' => 'Header text (light)', 'desc' => $colour_auto ),
					'header_bg_dark'    => array( 'type' => 'color', 'label' => 'Header background (dark)' ),
					'header_text_dark'  => array( 'type' => 'color', 'label' => 'Header text (dark)', 'desc' => $colour_auto ),
				),
			),
			array(
				'title'  => 'Footer colours',
				'fields' => array(
					'footer_bg_light'   => array( 'type' => 'color', 'label' => 'Footer background (light)' ),
					'footer_text_light' => array( 'type' => 'color', 'label' => 'Footer text (light)', 'desc' => $colour_auto ),
					'footer_bg_dark'    => array( 'type' => 'color', 'label' => 'Footer background (dark)' ),
					'footer_text_dark'  => array( 'type' => 'color', 'label' => 'Footer text (dark)', 'desc' => $colour_auto ),
				),
			),
		),
		'home'     => array(
			array(
				'title'  => 'Breaking news ticker',
				'fields' => array(
					'ticker_enabled' => array( 'type' => 'checkbox', 'label' => 'Show the ticker' ),
					'ticker_label'   => array( 'type' => 'text', 'label' => 'Ticker label' ),
					'ticker_count'   => array( 'type' => 'number', 'label' => 'Number of headlines', 'min' => 1, 'max' => 20 ),
					'ticker_speed'   => array( 'type' => 'number', 'label' => 'Scroll time (seconds)', 'min' => 5, 'max' => 300, 'desc' => 'Seconds for one full scroll. Higher = slower.' ),
					'ticker_cat'     => array( 'type' => 'category', 'label' => 'Ticker category', 'none' => 'Latest posts' ),
				),
			),
			array(
				'title'  => 'Home sections',
				'desc'   => 'Choose a category for each block. "Auto" uses the latest posts or, for the slots, the busiest categories.',
				'fields' => array(
					'lead_cat'      => array( 'type' => 'category', 'label' => 'Lead story category', 'none' => 'Auto / latest' ),
					'grid_cat'      => array( 'type' => 'category', 'label' => 'Top news grid category', 'none' => 'Auto / latest' ),
					'home_slots'    => array( 'type' => 'slots', 'label' => 'Section categories (8 slots)' ),
					'auto_sections' => array( 'type' => 'checkbox', 'label' => 'Fill empty slots from the busiest categories' ),
				),
			),
		),
		'articles' => array(
			array(
				'title'  => 'Sharing',
				'fields' => array(
					'fb_app_id' => array( 'type' => 'text', 'label' => 'Facebook App ID', 'desc' => 'Enables the Messenger share button on desktop.' ),
				),
			),
		),
		'share'    => array(
			array(
				'title'  => 'Share picture',
				'desc'   => 'The downloadable news card made from an article (headline, photo, date and logo).',
				'fields' => array(
					'share_style'       => array( 'type' => 'select', 'label' => 'Style', 'options' => sokal_settings_share_styles() ),
					'share_bg'          => array( 'type' => 'color', 'label' => 'Background colour' ),
					'share_text'        => array( 'type' => 'color', 'label' => 'Text colour' ),
					'share_accent'      => array( 'type' => 'color', 'label' => 'Accent colour' ),
					'share_bottom_line' => array( 'type' => 'text', 'label' => 'Bottom line text' ),
				),
			),
		),
		'footer'   => array(
			array(
				'title'  => 'Footer content',
				'fields' => array(
					'footer_logo'     => array( 'type' => 'image', 'label' => 'Footer logo', 'id_key' => 'footer_logo_id' ),
					'company_name'    => array( 'type' => 'text', 'label' => 'Company name' ),
					'company_desc'    => array( 'type' => 'textarea', 'label' => 'Company description' ),
					'contact_lines'   => array( 'type' => 'textarea', 'label' => 'Contact lines', 'desc' => 'One per line. "Label: value" shows a bold label; email addresses are linked.' ),
					'follow_heading'  => array( 'type' => 'text', 'label' => '"Follow us" heading' ),
					'app_google_play' => array( 'type' => 'url', 'label' => 'Google Play app link' ),
					'app_store'       => array( 'type' => 'url', 'label' => 'App Store app link' ),
					'copyright'       => array( 'type' => 'html', 'label' => 'Copyright', 'desc' => '{year} and {site} are replaced automatically. Basic HTML allowed.' ),
				),
			),
			array(
				'title'  => 'Social links',
				'desc'   => 'Leave empty to hide an icon.',
				'fields' => array(
					'social_facebook'  => array( 'type' => 'url', 'label' => 'Facebook' ),
					'social_x'         => array( 'type' => 'url', 'label' => 'X (Twitter)' ),
					'social_whatsapp'  => array( 'type' => 'url', 'label' => 'WhatsApp' ),
					'social_youtube'   => array( 'type' => 'url', 'label' => 'YouTube' ),
					'social_instagram' => array( 'type' => 'url', 'label' => 'Instagram' ),
					'social_telegram'  => array( 'type' => 'url', 'label' => 'Telegram' ),
				),
			),
		),
		'speed'    => array(
			array(
				'title'  => 'Cloudflare',
				'fields' => array(
					'edge_ttl'     => array( 'type' => 'number', 'label' => 'Edge cache time (seconds)', 'min' => 0, 'max' => 86400, 'desc' => 'Sent as s-maxage on public pages. 0 = no edge caching.' ),
					'cf_zone_id'   => array( 'type' => 'text', 'label' => 'Cloudflare Zone ID', 'desc' => 'Optional. With a token, changed pages are purged on publish.' ),
					'cf_api_token' => array( 'type' => 'secret', 'label' => 'Cloudflare API token', 'desc' => 'Needs only the "Zone > Cache Purge" permission.' ),
				),
			),
		),
	);

	// Old theme options, grouped onto the tabs they belong to.
	$groups = array();
	foreach ( sokal_legacy_fields() as $id => $f ) {
		if ( 'hidden' === $f['tab'] ) {
			continue;
		}
		$f['legacy'] = true;
		$groups[ $f['tab'] ][ $f['group'] ][ $id ] = $f;
	}
	foreach ( $groups as $tab => $list ) {
		foreach ( $list as $title => $fields ) {
			$schema[ $tab ][] = array(
				'title'  => $title,
				'desc'   => 'Old theme options, still used by the templates.',
				'legacy' => true,
				'fields' => $fields,
			);
		}
	}
	return $schema;
}

/** Sub-fields shown for a legacy typography field. */
function sokal_settings_typo_subs( $f ) {
	$subs = array( 'font-size', 'line-height', 'font-weight', 'color' );
	if ( is_array( $f['default'] ) && isset( $f['default']['text-align'] ) ) {
		$subs[] = 'text-align';
	}
	return $subs;
}

/* ------------------------------------------------------------------
 * 5. Sanitizing.
 * ------------------------------------------------------------------ */
function sokal_settings_css_size( $v, $unitless, $current ) {
	$v = strtolower( trim( (string) $v ) );
	if ( '' === $v ) {
		return '';
	}
	if ( in_array( $v, array( 'auto', 'normal', 'inherit' ), true ) ) {
		return $v;
	}
	if ( preg_match( '/^\d{1,3}(\.\d{1,3})?$/', $v ) ) {
		return $unitless ? $v : $v . 'px';
	}
	if ( preg_match( '/^\d{1,3}(\.\d{1,3})?(px|em|rem|%|pt)$/', $v ) ) {
		return $v;
	}
	return is_string( $current ) ? $current : '';
}

function sokal_settings_sanitize( $f, $raw, $current ) {
	$scalar = is_scalar( $raw ) ? trim( (string) $raw ) : '';
	$legacy = ! empty( $f['legacy'] );

	switch ( $f['type'] ) {
		case 'text':
			return sanitize_text_field( $scalar );

		case 'url':
			return esc_url_raw( $scalar );

		case 'textarea':
			return sanitize_textarea_field( is_scalar( $raw ) ? (string) $raw : '' );

		case 'html':
			return wp_kses_post( is_scalar( $raw ) ? (string) $raw : '' );

		case 'number':
			$min = isset( $f['min'] ) ? (int) $f['min'] : 0;
			$max = isset( $f['max'] ) ? (int) $f['max'] : PHP_INT_MAX;
			$n   = is_numeric( $scalar ) ? (int) $scalar : (int) $current;
			return max( $min, min( $max, $n ) );

		case 'checkbox':
			return empty( $raw ) ? 0 : 1;

		case 'select':
			foreach ( array_keys( $f['options'] ) as $opt ) {
				if ( (string) $opt === $scalar ) {
					return $scalar;
				}
			}
			return is_scalar( $current ) ? (string) $current : '';

		case 'color':
			$c = sanitize_hex_color( $scalar );
			return $c ? $c : '';

		case 'image':
			return esc_url_raw( $scalar );

		case 'category':
		case 'legacy_cat':
			$id = absint( $scalar );
			if ( $id && ! term_exists( $id, 'category' ) ) {
				$id = 0;
			}
			return $legacy ? (string) $id : $id;

		case 'slots':
			$out = array();
			for ( $i = 0; $i < 8; $i++ ) {
				$id    = is_array( $raw ) && isset( $raw[ $i ] ) ? absint( $raw[ $i ] ) : 0;
				$out[] = ( $id && term_exists( $id, 'category' ) ) ? $id : 0;
			}
			return $out;

		case 'secret':
			$raw = is_array( $raw ) ? $raw : array();
			if ( ! empty( $raw['clear'] ) ) {
				return '';
			}
			$v = isset( $raw['value'] ) && is_scalar( $raw['value'] ) ? sanitize_text_field( trim( (string) $raw['value'] ) ) : '';
			return '' !== $v ? $v : (string) $current; // empty box = keep the stored token

		case 'legacy_image':
			$raw = is_array( $raw ) ? $raw : array();
			$url = isset( $raw['url'] ) && is_scalar( $raw['url'] ) ? esc_url_raw( trim( (string) $raw['url'] ) ) : '';
			$id  = '' !== $url && isset( $raw['id'] ) ? absint( $raw['id'] ) : 0;
			$cur = is_array( $current ) ? $current : array();
			if ( isset( $cur['url'] ) && $cur['url'] === $url ) {
				$cur['id'] = $id ? $id : ( isset( $cur['id'] ) ? $cur['id'] : '' );
				return $cur;
			}
			return array( 'url' => $url, 'id' => $id ? $id : '' );

		case 'multitext':
			$raw  = is_array( $raw ) ? $raw : array();
			$out  = is_array( $current ) ? $current : array();
			$subs = ! empty( $f['only'] ) ? $f['only'] : array_keys( $f['subs'] );
			foreach ( $subs as $sub ) {
				$v           = isset( $raw[ $sub ] ) && is_scalar( $raw[ $sub ] ) ? trim( (string) $raw[ $sub ] ) : '';
				$out[ $sub ] = ! empty( $f['url'] ) ? esc_url_raw( $v ) : sanitize_text_field( $v );
			}
			return $out;

		case 'typography':
			$raw = is_array( $raw ) ? $raw : array();
			$out = is_array( $current ) ? $current : array();
			foreach ( sokal_settings_typo_subs( $f ) as $sub ) {
				$v   = isset( $raw[ $sub ] ) && is_scalar( $raw[ $sub ] ) ? trim( (string) $raw[ $sub ] ) : '';
				$cur = isset( $out[ $sub ] ) ? $out[ $sub ] : '';
				switch ( $sub ) {
					case 'color':
						$c           = sanitize_hex_color( $v );
						$out[ $sub ] = $c ? $c : '';
						break;
					case 'font-weight':
						$out[ $sub ] = in_array( $v, array( '', '100', '200', '300', '400', '500', '600', '700', '800', '900', 'normal', 'bold' ), true ) ? $v : $cur;
						break;
					case 'text-align':
						$out[ $sub ] = in_array( $v, array( 'left', 'center', 'right', 'justify' ), true ) ? $v : $cur;
						break;
					default:
						$out[ $sub ] = sokal_settings_css_size( $v, 'line-height' === $sub, $cur );
				}
			}
			return $out;

		case 'sorter':
			$raw   = is_array( $raw ) ? $raw : array();
			$show  = isset( $raw['show'] ) && is_array( $raw['show'] ) ? $raw['show'] : array();
			$order = isset( $raw['order'] ) && is_array( $raw['order'] ) ? $raw['order'] : array();
			$items = array();
			$i     = 0;
			foreach ( $f['options'] as $k => $label ) {
				$items[] = array( $k, $label, ! empty( $show[ $k ] ), isset( $order[ $k ] ) ? (int) $order[ $k ] : 99, $i++ );
			}
			usort(
				$items,
				function ( $a, $b ) {
					return $a[3] === $b[3] ? $a[4] - $b[4] : $a[3] - $b[3];
				}
			);
			$out = array( 'Show' => array(), 'Hide' => array() );
			foreach ( $items as $it ) {
				$out[ $it[2] ? 'Show' : 'Hide' ][ $it[0] ] = $it[1];
			}
			return $out;
	}
	return $current;
}

/** Copy overlapping new values back into the legacy array, so old templates agree. */
function sokal_settings_sync_legacy( $s, &$legacy ) {
	$defaults = sokal_defaults();
	foreach ( sokal_settings_legacy_map() as $key => $m ) {
		if ( ! isset( $s[ $key ] ) ) {
			continue;
		}
		$v = $s[ $key ];
		switch ( $m[1] ) {
			case 'url':
			case 'html':
			case 'text':
				// Empty or untouched default = templates keep falling back to the old value.
				if ( '' !== trim( (string) $v ) && ( ! isset( $defaults[ $key ] ) || $v !== $defaults[ $key ] ) ) {
					sokal_legacy_set( $legacy, $m[0], (string) $v );
				}
				break;
			case 'id':
				if ( ! empty( $s[ str_replace( '_id', '', $key ) ] ) ) {
					sokal_legacy_set( $legacy, $m[0], $v ? (int) $v : '' );
				}
				break;
			case 'link':
				sokal_legacy_set( $legacy, $m[0], (string) $v );
				break;
			case 'count':
				sokal_legacy_set( $legacy, $m[0], sokal_int( $v, 1, 20 ) );
				break;
			case 'cat':
				sokal_legacy_set( $legacy, $m[0], (string) absint( $v ) );
				break;
		}
	}
	if ( ! empty( $s['ticker_cat'] ) ) {
		$legacy['top_scroll_cat'] = (string) absint( $s['ticker_cat'] );
	}
	if ( isset( $s['home_slots'] ) && is_array( $s['home_slots'] ) ) {
		foreach ( sokal_settings_slot_keys() as $i => $k ) {
			$legacy[ $k ] = (string) ( isset( $s['home_slots'][ $i ] ) ? absint( $s['home_slots'][ $i ] ) : 0 );
		}
	}
}

/* ------------------------------------------------------------------
 * 6. Admin screen.
 * ------------------------------------------------------------------ */
add_action( 'admin_menu', 'sokal_settings_menu' );
function sokal_settings_menu() {
	add_menu_page( 'Theme Settings', 'Theme Settings', 'manage_options', 'sokal-settings', 'sokal_settings_page', 'dashicons-admin-generic', 61 );
}

add_action( 'admin_enqueue_scripts', 'sokal_settings_assets' );
function sokal_settings_assets( $hook ) {
	if ( 'toplevel_page_sokal-settings' !== $hook ) {
		return;
	}
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	$ver = function ( $rel ) use ( $dir ) {
		return file_exists( $dir . $rel ) ? (string) filemtime( $dir . $rel ) : null;
	};

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'sokal-settings', $uri . '/css/admin/settings.css', array( 'wp-color-picker' ), $ver( '/css/admin/settings.css' ) );

	$deps = array( 'jquery', 'wp-color-picker' );
	if ( file_exists( $dir . '/js/share-picture.js' ) ) {
		wp_register_script( 'sokal-share-picture', $uri . '/js/share-picture.js', array(), $ver( '/js/share-picture.js' ), true );
		$deps[] = 'sokal-share-picture';
	}
	wp_enqueue_script( 'sokal-settings', $uri . '/js/admin/settings.js', $deps, $ver( '/js/admin/settings.js' ), true );

	$logo = (string) sokal_opt( 'header_logo', '' );
	if ( '' === $logo ) {
		$legacy = sokal_legacy_values();
		$logo   = isset( $legacy['logo_upload']['url'] ) ? (string) $legacy['logo_upload']['url'] : '';
	}
	wp_localize_script(
		'sokal-settings',
		'sokalSettings',
		array(
			'preview' => array(
				'image'    => '' !== $logo ? esc_url_raw( $logo ) : $uri . '/images/noimage.gif',
				'logo'     => esc_url_raw( $logo ),
				'title'    => 'রাজধানীতে আজ থেকে নতুন ট্রাফিক ব্যবস্থা চালু, কমবে যানজট',
				'date'     => date_i18n( 'j F Y' ),
				'siteName' => sokal_site_name(),
			),
			'i18n'    => array(
				'choose'      => 'Choose image',
				'use'         => 'Use this image',
				'unavailable' => 'Preview unavailable: the share picture script is not loaded.',
				'failed'      => 'Preview could not be drawn.',
			),
		)
	);
}

function sokal_settings_current_tab() {
	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification
	$tabs = sokal_settings_tabs();
	return isset( $tabs[ $tab ] ) ? $tab : 'general';
}

function sokal_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$tab    = sokal_settings_current_tab();
	$schema = sokal_settings_schema();
	$s      = sokal_settings();
	$legacy = sokal_legacy_values();
	$base   = admin_url( 'admin.php?page=sokal-settings' );

	echo '<div class="wrap sokal-settings">';
	echo '<h1>' . esc_html__( 'Theme Settings' ) . '</h1>';

	// phpcs:disable WordPress.Security.NonceVerification
	if ( isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Settings saved. The theme cache was cleared.</p></div>';
	}
	if ( isset( $_GET['cleared'] ) ) {
		$rows = absint( wp_unslash( $_GET['cleared'] ) );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( 'Theme cache cleared (%d stored rows removed).', max( 0, $rows - 1 ) ) ) . '</p></div>';
	}
	// phpcs:enable

	echo '<nav class="nav-tab-wrapper">';
	foreach ( sokal_settings_tabs() as $id => $label ) {
		printf(
			'<a href="%s" class="nav-tab%s">%s</a>',
			esc_url( add_query_arg( 'tab', $id, $base ) ),
			$id === $tab ? ' nav-tab-active' : '',
			esc_html( $label )
		);
	}
	echo '</nav>';

	if ( 'speed' === $tab ) {
		sokal_settings_speed_status();
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="sokal-settings-form">';
	echo '<input type="hidden" name="action" value="sokal_save_settings">';
	echo '<input type="hidden" name="sokal_tab" value="' . esc_attr( $tab ) . '">';
	wp_nonce_field( 'sokal_save_settings' );

	if ( 'share' === $tab ) {
		echo '<div class="sokal-share-preview"><h2>Live preview</h2>';
		echo '<canvas id="sokal-share-canvas" width="1200" height="630"></canvas>';
		echo '<div class="notice notice-warning inline sokal-share-unavailable" hidden><p>Preview unavailable.</p></div>';
		echo '</div>';
	}

	foreach ( $schema[ $tab ] as $group ) {
		echo '<div class="sokal-group' . ( ! empty( $group['legacy'] ) ? ' sokal-group-legacy' : '' ) . '">';
		echo '<h2>' . esc_html( $group['title'] ) . '</h2>';
		if ( ! empty( $group['desc'] ) ) {
			echo '<p class="description">' . esc_html( $group['desc'] ) . '</p>';
		}
		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( $group['fields'] as $key => $f ) {
			if ( ! empty( $f['legacy'] ) ) {
				$value = isset( $legacy[ $key ] ) ? $legacy[ $key ] : $f['default'];
			} else {
				$value = isset( $s[ $key ] ) ? $s[ $key ] : '';
			}
			sokal_settings_row( $key, $f, $value, $s );
		}
		echo '</tbody></table></div>';
	}

	submit_button( 'Save changes' );
	echo '</form>';

	if ( 'speed' === $tab ) {
		sokal_settings_cloudflare_help();
	}
	echo '</div>';
}

function sokal_settings_row( $key, $f, $value, $s ) {
	$legacy = ! empty( $f['legacy'] );
	$name   = ( $legacy ? 'sokal_legacy' : 'sokal' ) . '[' . $key . ']';
	$id     = ( $legacy ? 'sokal-legacy-' : 'sokal-' ) . sanitize_html_class( $key );
	$multi  = in_array( $f['type'], array( 'slots', 'multitext', 'typography', 'sorter', 'image', 'legacy_image', 'checkbox' ), true );

	echo '<tr><th scope="row">';
	if ( $multi ) {
		echo esc_html( $f['label'] );
	} else {
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . '</label>';
	}
	if ( $legacy ) {
		echo '<br><code class="sokal-key">' . esc_html( $key ) . '</code>';
	}
	echo '</th><td>';
	sokal_settings_field( $f, $name, $id, $value, $s );
	if ( ! empty( $f['desc'] ) ) {
		echo '<p class="description">' . esc_html( $f['desc'] ) . '</p>';
	}
	echo '</td></tr>';
}

function sokal_settings_cat_dropdown( $name, $id, $selected, $none ) {
	echo wp_dropdown_categories( // phpcs:ignore WordPress.Security.EscapeOutput -- core builds escaped markup.
		array(
			'name'              => $name,
			'id'                => $id,
			'selected'          => absint( $selected ),
			'show_option_none'  => $none,
			'option_none_value' => 0,
			'hide_empty'        => 0,
			'hierarchical'      => 1,
			'orderby'           => 'name',
			'echo'              => 0,
		)
	);
}

function sokal_settings_field( $f, $name, $id, $value, $s ) {
	switch ( $f['type'] ) {
		case 'text':
		case 'url':
			printf(
				'<input type="%s" class="regular-text" id="%s" name="%s" value="%s">',
				'url' === $f['type'] ? 'url' : 'text',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( is_scalar( $value ) ? (string) $value : '' )
			);
			break;

		case 'textarea':
		case 'html':
			printf(
				'<textarea class="large-text%s" rows="4" id="%s" name="%s">%s</textarea>',
				'html' === $f['type'] ? ' code' : '',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( is_scalar( $value ) ? (string) $value : '' )
			);
			if ( 'html' === $f['type'] && empty( $f['desc'] ) ) {
				echo '<p class="description">Basic HTML allowed.</p>';
			}
			break;

		case 'number':
			printf(
				'<input type="number" class="small-text" id="%s" name="%s" value="%s" min="%d" max="%d" step="1">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) (int) $value ),
				isset( $f['min'] ) ? (int) $f['min'] : 0,
				isset( $f['max'] ) ? (int) $f['max'] : 999999
			);
			if ( isset( $f['min'], $f['max'] ) ) {
				echo ' <span class="description">' . esc_html( sprintf( '%d to %d', $f['min'], $f['max'] ) ) . '</span>';
			}
			break;

		case 'checkbox':
			printf(
				'<label><input type="checkbox" id="%s" name="%s" value="1"%s> %s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( ! empty( $value ), true, false ),
				esc_html__( 'Enabled' )
			);
			break;

		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( $f['options'] as $k => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( (string) $k ), selected( (string) $value, (string) $k, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'color':
			printf(
				'<input type="text" class="sokal-color" id="%s" name="%s" value="%s" data-default-color="%s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( is_scalar( $value ) ? (string) $value : '' ),
				esc_attr( isset( $f['default'] ) && is_string( $f['default'] ) ? $f['default'] : '' )
			);
			break;

		case 'image':
		case 'legacy_image':
			if ( 'image' === $f['type'] ) {
				$url      = is_scalar( $value ) ? (string) $value : '';
				$img_id   = isset( $s[ $f['id_key'] ] ) ? absint( $s[ $f['id_key'] ] ) : 0;
				$url_name = $name;
				$id_name  = 'sokal[' . $f['id_key'] . ']';
			} else {
				$url      = is_array( $value ) && isset( $value['url'] ) ? (string) $value['url'] : '';
				$img_id   = is_array( $value ) && isset( $value['id'] ) ? absint( $value['id'] ) : 0;
				$url_name = $name . '[url]';
				$id_name  = $name . '[id]';
			}
			echo '<div class="sokal-image">';
			printf( '<input type="url" class="regular-text sokal-image-url" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $url_name ), esc_attr( $url ) );
			printf( '<input type="hidden" class="sokal-image-id" name="%s" value="%s">', esc_attr( $id_name ), esc_attr( $img_id ? (string) $img_id : '' ) );
			echo ' <button type="button" class="button sokal-image-pick">Choose</button>';
			echo ' <button type="button" class="button-link sokal-image-remove">Remove</button>';
			printf( '<div class="sokal-image-preview">%s</div>', '' !== $url ? '<img src="' . esc_url( $url ) . '" alt="">' : '' );
			echo '</div>';
			break;

		case 'category':
		case 'legacy_cat':
			sokal_settings_cat_dropdown( $name, $id, $value, isset( $f['none'] ) ? $f['none'] : '— None —' );
			break;

		case 'slots':
			$value = is_array( $value ) ? array_values( $value ) : array();
			echo '<ol class="sokal-slots">';
			for ( $i = 0; $i < 8; $i++ ) {
				echo '<li>';
				sokal_settings_cat_dropdown( $name . '[' . $i . ']', $id . '-' . $i, isset( $value[ $i ] ) ? $value[ $i ] : 0, 'Auto-pick' );
				echo '</li>';
			}
			echo '</ol>';
			break;

		case 'secret':
			$v    = is_scalar( $value ) ? (string) $value : '';
			$mask = '' !== $v ? str_repeat( '•', 8 ) . substr( $v, -4 ) : '';
			printf(
				'<input type="password" class="regular-text" id="%s" name="%s" value="" autocomplete="new-password" placeholder="%s">',
				esc_attr( $id ),
				esc_attr( $name . '[value]' ),
				esc_attr( '' !== $mask ? $mask : 'Not set' )
			);
			if ( '' !== $v ) {
				echo '<p class="description">Saved: <code>' . esc_html( $mask ) . '</code>. Leave the box empty to keep it.</p>';
				printf( '<label><input type="checkbox" name="%s" value="1"> Remove the saved token</label>', esc_attr( $name . '[clear]' ) );
			}
			break;

		case 'multitext':
			$subs = ! empty( $f['only'] ) ? $f['only'] : array_keys( $f['subs'] );
			foreach ( $subs as $sub ) {
				$v = is_array( $value ) && isset( $value[ $sub ] ) ? (string) $value[ $sub ] : '';
				printf(
					'<p><label>%s<br><input type="%s" class="regular-text" name="%s" value="%s"></label></p>',
					esc_html( isset( $f['subs'][ $sub ] ) ? $f['subs'][ $sub ] : $sub ),
					! empty( $f['url'] ) ? 'url' : 'text',
					esc_attr( $name . '[' . $sub . ']' ),
					esc_attr( $v )
				);
			}
			break;

		case 'typography':
			$labels = array(
				'font-size'   => 'Size',
				'line-height' => 'Line height',
				'font-weight' => 'Weight',
				'color'       => 'Colour',
				'text-align'  => 'Align',
			);
			echo '<div class="sokal-typo">';
			foreach ( sokal_settings_typo_subs( $f ) as $sub ) {
				$v = is_array( $value ) && isset( $value[ $sub ] ) ? (string) $value[ $sub ] : '';
				$n = $name . '[' . $sub . ']';
				echo '<label class="sokal-typo-' . esc_attr( $sub ) . '"><span>' . esc_html( $labels[ $sub ] ) . '</span>';
				if ( 'color' === $sub ) {
					printf( '<input type="text" class="sokal-color" name="%s" value="%s">', esc_attr( $n ), esc_attr( $v ) );
				} elseif ( 'font-weight' === $sub ) {
					echo '<select name="' . esc_attr( $n ) . '"><option value="">Default</option>';
					foreach ( array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ) as $w ) {
						printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $w ), selected( $v, $w, false ) );
					}
					echo '</select>';
				} elseif ( 'text-align' === $sub ) {
					echo '<select name="' . esc_attr( $n ) . '">';
					foreach ( array( 'left', 'center', 'right', 'justify' ) as $a ) {
						printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $a ), selected( $v, $a, false ) );
					}
					echo '</select>';
				} else {
					printf( '<input type="text" class="small-text" name="%s" value="%s" placeholder="%s">', esc_attr( $n ), esc_attr( $v ), 'font-size' === $sub ? '17px' : 'auto' );
				}
				echo '</label>';
			}
			echo '</div>';
			break;

		case 'sorter':
			$show  = is_array( $value ) && isset( $value['Show'] ) && is_array( $value['Show'] ) ? array_keys( $value['Show'] ) : array();
			$order = array_merge( $show, array_diff( array_keys( $f['options'] ), $show ) );
			echo '<ul class="sokal-sorter">';
			foreach ( $order as $pos => $k ) {
				if ( ! isset( $f['options'][ $k ] ) ) {
					continue;
				}
				printf(
					'<li><label><input type="checkbox" name="%s" value="1"%s> %s</label> <label>Order <input type="number" class="small-text" min="1" max="20" name="%s" value="%d"></label></li>',
					esc_attr( $name . '[show][' . $k . ']' ),
					checked( in_array( $k, $show, true ), true, false ),
					esc_html( $f['options'][ $k ] ),
					esc_attr( $name . '[order][' . $k . ']' ),
					(int) $pos + 1
				);
			}
			echo '</ul><p class="description">Ticked sections show on the home page, in this order.</p>';
			break;
	}
}

/* ------------------------------------------------------------------
 * 7. Speed and cache tab.
 * ------------------------------------------------------------------ */
function sokal_settings_cat_label( $id ) {
	$id = absint( $id );
	if ( ! $id ) {
		return 'Latest posts';
	}
	$t = get_term( $id, 'category' );
	return ( $t && ! is_wp_error( $t ) ) ? $t->name : '#' . $id . ' (missing)';
}

function sokal_settings_speed_status() {
	global $wpdb;
	echo '<div class="sokal-box"><h2>Status</h2><table class="widefat striped sokal-status"><tbody>';

	echo '<tr><th>Home sections</th><td>';
	if ( function_exists( 'sokal_home_categories' ) ) {
		$c    = sokal_home_categories();
		$auto = isset( $c['auto'] ) && is_array( $c['auto'] ) ? array_map( 'absint', $c['auto'] ) : array();
		$tag  = function ( $id ) use ( $auto ) {
			return esc_html( sokal_settings_cat_label( $id ) ) . ( in_array( absint( $id ), $auto, true ) ? ' <em>(auto)</em>' : '' );
		};
		echo 'Lead: ' . $tag( isset( $c['lead'] ) ? $c['lead'] : 0 ) . '<br>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in $tag.
		echo 'Grid: ' . $tag( isset( $c['grid'] ) ? $c['grid'] : 0 ) . '<br>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo 'Sections: ';
		$names = array();
		foreach ( isset( $c['slots'] ) && is_array( $c['slots'] ) ? $c['slots'] : array() as $id ) {
			$names[] = $tag( $id );
		}
		echo $names ? implode( ', ', $names ) : 'none'; // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		echo 'Not available (the home module is not loaded).';
	}
	echo '</td></tr>';

	$frag = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_transient_sokal_f_' ) . '%' ) );
	$ext  = wp_using_ext_object_cache();
	echo '<tr><th>Persistent object cache</th><td>' . ( $ext ? 'Yes' : 'No (install Redis or Memcached object cache for best speed)' ) . '</td></tr>';
	echo '<tr><th>Cached fragments in database</th><td>' . esc_html( $ext ? 'Stored in the object cache' : (string) $frag ) . '</td></tr>';
	echo '<tr><th>PHP version</th><td>' . esc_html( PHP_VERSION ) . '</td></tr>';
	echo '<tr><th>WordPress version</th><td>' . esc_html( get_bloginfo( 'version' ) ) . '</td></tr>';
	echo '<tr><th>Speed targets</th><td>LCP under 2.5 s &middot; CLS under 0.1 &middot; INP under 200 ms &middot; home page under about 20 database queries when the cache is warm</td></tr>';
	echo '</tbody></table>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="sokal-clear-cache">';
	echo '<input type="hidden" name="action" value="sokal_clear_cache">';
	wp_nonce_field( 'sokal_clear_cache' );
	submit_button( 'Clear theme cache', 'secondary', 'submit', false );
	echo ' <span class="description">Rebuilds the home sections, ticker and sidebar on the next visit.</span>';
	echo '</form></div>';
}

function sokal_settings_cloudflare_help() {
	?>
	<div class="sokal-box sokal-cf">
		<h2>Cloudflare setup</h2>
		<ol>
			<li><strong>Caching &rarr; Cache Rules &rarr; Create rule</strong> for HTML pages: match your hostname, action <em>Eligible for cache</em>, Edge TTL <em>Use cache-control header if present</em> and Browser TTL <em>Respect origin</em>. The theme sends <code>s-maxage</code> from the "Edge cache time" setting above.</li>
			<li>In the same rule (or a rule placed above it with <em>Bypass cache</em>), bypass when:
				<ul>
					<li>the Cookie header contains <code>wordpress_logged_in_</code>, <code>wp-postpass_</code> or <code>comment_author_</code>;</li>
					<li>the URI path starts with <code>/wp-admin</code>, <code>/wp-login.php</code>, <code>/wp-json</code>, <code>/xmlrpc.php</code> or <code>/wp-cron.php</code>.</li>
				</ul>
			</li>
			<li><strong>Cache key</strong>: under the rule's cache key settings, exclude the query parameters <code>utm_*</code> (utm_source, utm_medium, utm_campaign, utm_term, utm_content), <code>fbclid</code>, <code>gclid</code>, <code>mc_cid</code> and <code>mc_eid</code>, so shared links hit the same cached page.</li>
			<li><strong>Speed &rarr; Optimization &rarr; Early Hints</strong>: turn on.</li>
			<li><strong>Security &rarr; WAF &rarr; Rate limiting rules</strong>: add a rule for URI path starting with <code>/wp-json/sokal/v1/view/</code> (for example 30 requests per 10 seconds per IP, then block), and one for search requests where the query string contains <code>s=</code> (for example 20 requests per minute per IP).</li>
			<li>Optional: fill in the Zone ID and an API token with <em>Zone &rarr; Cache Purge</em> permission above, so changed pages are purged as soon as they are published.</li>
		</ol>
	</div>
	<?php
}

/* ------------------------------------------------------------------
 * 8. Save and clear-cache handlers.
 * ------------------------------------------------------------------ */
add_action( 'admin_post_sokal_save_settings', 'sokal_settings_save' );
function sokal_settings_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.' ), 403 );
	}
	check_admin_referer( 'sokal_save_settings' );

	$tab    = isset( $_POST['sokal_tab'] ) ? sanitize_key( wp_unslash( $_POST['sokal_tab'] ) ) : 'general';
	$schema = sokal_settings_schema();
	if ( ! isset( $schema[ $tab ] ) ) {
		$tab = 'general';
	}

	$saved   = get_option( 'sokal_settings', array() );
	$saved   = is_array( $saved ) ? $saved : array();
	$current = sokal_settings();
	$legacy  = sokal_legacy_values();
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput -- every value goes through sokal_settings_sanitize().
	$in  = isset( $_POST['sokal'] ) && is_array( $_POST['sokal'] ) ? wp_unslash( $_POST['sokal'] ) : array();
	$lin = isset( $_POST['sokal_legacy'] ) && is_array( $_POST['sokal_legacy'] ) ? wp_unslash( $_POST['sokal_legacy'] ) : array();
	// phpcs:enable

	foreach ( $schema[ $tab ] as $group ) {
		foreach ( $group['fields'] as $key => $f ) {
			if ( ! empty( $f['legacy'] ) ) {
				$cur            = isset( $legacy[ $key ] ) ? $legacy[ $key ] : $f['default'];
				$legacy[ $key ] = sokal_settings_sanitize( $f, isset( $lin[ $key ] ) ? $lin[ $key ] : null, $cur );
				continue;
			}
			$cur           = isset( $current[ $key ] ) ? $current[ $key ] : '';
			$saved[ $key ] = sokal_settings_sanitize( $f, isset( $in[ $key ] ) ? $in[ $key ] : null, $cur );
			if ( 'image' === $f['type'] && ! empty( $f['id_key'] ) ) {
				$saved[ $f['id_key'] ] = '' !== $saved[ $key ] && isset( $in[ $f['id_key'] ] ) ? absint( $in[ $f['id_key'] ] ) : 0;
			}
		}
	}

	sokal_settings_sync_legacy( $saved, $legacy ); // only keys the admin (or the migration) actually stored
	$saved['legacy'] = $legacy;
	update_option( 'sokal_settings', $saved, true );

	if ( function_exists( 'sokal_flush_cache' ) ) {
		sokal_flush_cache();
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'sokal-settings', 'tab' => $tab, 'updated' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}

add_action( 'admin_post_sokal_clear_cache', 'sokal_settings_clear_cache' );
function sokal_settings_clear_cache() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.' ), 403 );
	}
	check_admin_referer( 'sokal_clear_cache' );
	global $wpdb;

	if ( function_exists( 'sokal_flush_cache' ) ) {
		sokal_flush_cache();
	}
	$rows = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_sokal_f_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_sokal_f_' ) . '%'
		)
	);
	if ( function_exists( 'sokal_cf_purge' ) ) {
		sokal_cf_purge( array( home_url( '/' ) ) );
	}

	// +1 so that "0 rows" still shows the notice.
	wp_safe_redirect( add_query_arg( array( 'page' => 'sokal-settings', 'tab' => 'speed', 'cleared' => (int) $rows + 1 ), admin_url( 'admin.php' ) ) );
	exit;
}
