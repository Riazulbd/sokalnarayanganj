<?php
/**
 * Shared template helpers (pagination, Bengali numbers/dates, excerpt, Family post type).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* --------------Post Pagination----------------- */

function wp_bootstrap_pagination( $args = array() ) {

    $defaults = array(
        'range'           => 4,
        'custom_query'    => FALSE,
        'previous_string' => __( '<i class="fa fa-backward" aria-hidden="true"></i>
', 'text-domain' ),
        'next_string'     => __( '<i class="fa fa-forward" aria-hidden="true"></i>
', 'text-domain' ),
        'before_output'   => '<div class="post-nav"><ul class="pager">',
        'after_output'    => '</ul></div>'
    );
    
    $args = wp_parse_args( 
        $args, 
        apply_filters( 'wp_bootstrap_pagination_defaults', $defaults )
    );
    
    $args['range'] = (int) $args['range'] - 1;
    if ( !$args['custom_query'] )
        $args['custom_query'] = @$GLOBALS['wp_query'];
    $count = (int) $args['custom_query']->max_num_pages;
    $page  = intval( get_query_var( 'paged' ) );
    $ceil  = ceil( $args['range'] / 2 );
    
    if ( $count <= 1 )
        return FALSE;
    
    if ( !$page )
        $page = 1;
    
    if ( $count > $args['range'] ) {
        if ( $page <= $args['range'] ) {
            $min = 1;
            $max = $args['range'] + 1;
        } elseif ( $page >= ($count - $ceil) ) {
            $min = $count - $args['range'];
            $max = $count;
        } elseif ( $page >= $args['range'] && $page < ($count - $ceil) ) {
            $min = $page - $ceil;
            $max = $page + $ceil;
        }
    } else {
        $min = 1;
        $max = $count;
    }
    
    $echo = '';
    $previous = intval($page) - 1;
    $previous = esc_attr( get_pagenum_link($previous) );
    
    $firstpage = esc_attr( get_pagenum_link(1) );
    if ( $firstpage && (1 != $page) )
        $echo .= '<li class="previous"><a href="' . $firstpage . '">' . __( '<i class="fa fa-fast-backward" aria-hidden="true"></i>
', 'ThemesBazar' ) . '</a></li>';
    if ( $previous && (1 != $page) )
        $echo .= '<li><a href="' . $previous . '" title="' . __( 'previous', 'ThemesBazar') . '">' . $args['previous_string'] . '</a></li>';
    
    if ( !empty($min) && !empty($max) ) {
        for( $i = $min; $i <= $max; $i++ ) {
            if ($page == $i) {
                $echo .= '<li class="active"><span class="active">' . str_pad( (int)$i, 2, '0', STR_PAD_LEFT ) . '</span></li>';
            } else {
                $echo .= sprintf( '<li><a href="%s">%002d</a></li>', esc_attr( get_pagenum_link($i) ), $i );
            }
        }
    }
    
    $next = intval($page) + 1;
    $next = esc_attr( get_pagenum_link($next) );
    if ($next && ($count != $page) )
        $echo .= '<li><a href="' . $next . '" title="' . __( 'next', 'text-domain') . '">' . $args['next_string'] . '</a></li>';
    
    $lastpage = esc_attr( get_pagenum_link($count) );
    if ( $lastpage ) {
        $echo .= '<li class="next"><a href="' . $lastpage . '">' . __( '<i class="fa fa-fast-forward" aria-hidden="true"></i>
', 'ThemesBazar' ) . '</a></li>';
    }
    if ( isset($echo) )
        echo $args['before_output'] . $echo . $args['after_output'];
}





/* --------------Our Family----------------- */

if(function_exists('register_post_type')) {
		register_post_type('family', array(
			'labels' => array(
				'name' => __('Family', 'news'),
				'menu_name' => __('Our Family', 'news'),
				'add_new' => __('Add New Member', 'news'),
				'add_new_item' => __('Add New Member', 'news'),
			),
			'public' => true,
			'menu_icon' => 'dashicons-format-gallery',
			'supports' => array('title','editor', 'thumbnail')
		   ));
	    }
	    
	    
	    
function bn_number($str)
 {
     $en = array(1,2,3,4,5,6,7,8,9,0);
    $bn = array('১','২','৩','৪','৫','৬','৭','৮','৯','০');
    $str = str_replace($en, $bn, $str);
     return $str;
 }



function bn1_date($str)
 {
     $en = array(1,2,3,4,5,6,7,8,9,0);
    $bn = array('১','২','৩','৪','৫','৬','৭','৮','৯','০');
    $str = str_replace($en, $bn, $str);
    $en = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
    $en_short = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
    $bn = array( 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'অগাস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
    $str = str_replace( $en, $bn, $str );
    $str = str_replace( $en_short, $bn, $str );
    $en = array('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday');
     $en_short = array('Sat','Sun','Mon','Tue','Wed','Thu','Fri');
     $bn_short = array('শনিবার', 'রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার');
     $bn = array('শনিবার','রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার');
     $str = str_replace( $en, $bn, $str );
     $str = str_replace( $en_short, $bn_short, $str );
     $en = array( 'am', 'pm' );
    $bn = array( 'পূর্বাহ্ন', 'অপরাহ্ন' );
    $str = str_replace( $en, $bn, $str );
     return $str;
 }


function excerpt($num) {
	$limit = $num + 1;
	$excerpt = explode(' ', get_the_excerpt(), $limit);
	array_pop($excerpt);
	echo implode(' ', $excerpt) . ' ';
}


/**
 * Story photo with a correctly sized placeholder fallback.
 * Always pass explicit attributes from cached templates so output doesn't
 * depend on which page built the cache.
 *
 * @param string $size  'card-thumb' (small cards), 'post-thumbnail' (600x337), 'single-hero'.
 * @param array  $attrs e.g. array( 'loading' => 'lazy', 'fetchpriority' => 'low', 'sizes' => '...' ).
 */
function sokal_thumb( $size = 'post-thumbnail', $attrs = array() ) {
	$attrs = wp_parse_args( $attrs, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, $attrs );
		return;
	}
	$extra = '';
	foreach ( array( 'loading', 'fetchpriority', 'decoding' ) as $k ) {
		if ( ! empty( $attrs[ $k ] ) ) {
			$extra .= ' ' . $k . '="' . esc_attr( $attrs[ $k ] ) . '"';
		}
	}
	echo '<img src="' . esc_url( get_template_directory_uri() . '/images/noimage.gif' ) . '" width="400" height="225" style="width:100%;height:auto"' . $extra . ' alt="">';
}

/**
 * archive.php renders page 1 as groups of 1 + 2 + 3 + 4 posts from the main
 * query, so taxonomy and author archives always load 10 posts per page.
 */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( ( $q->is_category() || $q->is_tag() || $q->is_tax() || $q->is_author() ) && ! $q->is_date() ) {
		$q->set( 'posts_per_page', 10 );
	}
} );


/* ------------------------------------------------------------------
 * Ads: off unless Theme Settings → General → "Show ads" is ticked.
 * Every widget area in this theme is an ad slot (.add wrappers).
 * ------------------------------------------------------------------ */
function sokal_ads_on() {
	return (bool) sokal_opt( 'ads_enabled', 0 );
}

function sokal_ad_sidebars() {
	return array(
		'widget_area_01', 'widget_area_02', 'widget_area_03', 'widget_area_04',
		'widget_area_05', 'widget_area_06', 'widget_area_07',
		'single_top', 'single_middle', 'single_bottom',
		'sidebar_widget', 'single_sidebar',
	);
}

add_filter( 'sidebars_widgets', function ( $widgets ) {
	if ( is_admin() || sokal_ads_on() || ! is_array( $widgets ) ) {
		return $widgets;
	}
	foreach ( sokal_ad_sidebars() as $id ) {
		if ( isset( $widgets[ $id ] ) ) {
			$widgets[ $id ] = array();
		}
	}
	return $widgets;
} );

// v2.0.7 removed the ads: drop cached page parts once so no cached ad survives.
add_action( 'init', function () {
	if ( ! get_option( 'sokal_flush_207' ) && function_exists( 'sokal_flush_cache' ) ) {
		sokal_flush_cache();
		update_option( 'sokal_flush_207', 1, true );
	}
} );

/* ------------------------------------------------------------------
 * Today line: Bengali calendar date | English date | time (Asia/Dhaka).
 * js/components/today.js keeps it live in the browser (pages are cached).
 * ------------------------------------------------------------------ */

/**
 * Bengali (Bangladesh revised) calendar date for a Gregorian Y-m-d.
 * Year starts 14 April (১ বৈশাখ); বৈশাখ–আশ্বিন 31 days, কার্তিক–মাঘ 30,
 * ফাল্গুন 29 (30 when the following February is in a leap year), চৈত্র 30.
 *
 * @return array( day, month name, year ) — numbers in ASCII.
 */
function sokal_bangla_date( $y, $m, $d ) {
	$months = array( 'বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র' );
	$start_year = ( $m > 4 || ( 4 === $m && $d >= 14 ) ) ? $y : $y - 1;
	$offset     = (int) floor( ( gmmktime( 0, 0, 0, $m, $d, $y ) - gmmktime( 0, 0, 0, 4, 14, $start_year ) ) / DAY_IN_SECONDS );
	$feb_year   = $start_year + 1;
	$leap       = ( 0 === $feb_year % 4 && 0 !== $feb_year % 100 ) || 0 === $feb_year % 400;
	$lengths    = array( 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, $leap ? 30 : 29, 30 );
	$i          = 0;
	while ( $i < 11 && $offset >= $lengths[ $i ] ) {
		$offset -= $lengths[ $i ];
		$i++;
	}
	return array( $offset + 1, $months[ $i ], $start_year - 593 );
}

/** Markup for the today line (server fallback text; today.js updates it). */
function sokal_today_html( $with_time = true ) {
	$tz   = new DateTimeZone( 'Asia/Dhaka' );
	$now  = new DateTime( 'now', $tz );
	$y    = (int) $now->format( 'Y' );
	$m    = (int) $now->format( 'n' );
	$d    = (int) $now->format( 'j' );
	$days = array( 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার' );
	$gm   = array( 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	$b    = sokal_bangla_date( $y, $m, $d );
	$bn   = 'আজ ' . $days[ (int) $now->format( 'w' ) ] . ', ' . bn_number( $b[0] ) . ' ' . $b[1] . ', ' . bn_number( $b[2] );
	$en   = bn_number( $d ) . ' ' . $gm[ $m - 1 ] . ', ' . bn_number( $y );
	$h    = (int) $now->format( 'G' );
	$time = bn_number( ( $h % 12 ) ? $h % 12 : 12 ) . ':' . bn_number( $now->format( 'i' ) ) . ' ' . ( $h < 12 ? 'পূর্বাহ্ন' : 'অপরাহ্ন' );

	$out  = '<span class="sokal-today" data-sokal-today>';
	$out .= '<span class="sokal-today__bn">' . esc_html( $bn ) . '</span>';
	$out .= '<span class="sokal-today__sep"> | </span>';
	$out .= '<span class="sokal-today__en">' . esc_html( $en ) . '</span>';
	if ( $with_time ) {
		$out .= '<span class="sokal-today__sep"> | </span>';
		$out .= '<span class="sokal-today__time">' . esc_html( $time ) . '</span>';
	}
	$out .= '</span>';
	return $out;
}
