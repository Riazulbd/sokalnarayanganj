<?php
function newsstar_widgets_areas(){ 

    
	register_sidebar(array(
        'name' => __( 'Sidebar Widget', 'Themesbazar.com' ),
		'id' => 'sidebar_widget',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
	register_sidebar(array(
        'name' => __( 'Widget Area 01', 'Themesbazar.com' ),
		'id' => 'widget_area_01',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
    register_sidebar(array(
        'name' => __( 'Widget Area 02', 'Themesbazar.com' ),
		'id' => 'widget_area_02',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
    register_sidebar(array(
        'name' => __( 'Widget Area 03', 'Themesbazar.com' ),
		'id' => 'widget_area_03',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
    register_sidebar(array(
        'name' => __( 'Widget Area 04', 'Themesbazar.com' ),
		'id' => 'widget_area_04',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
    register_sidebar(array(
        'name' => __( 'Widget Area 05', 'Themesbazar.com' ),
		'id' => 'widget_area_05',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    )); 
    register_sidebar(array(
        'name' => __( 'Widget Area 06', 'Themesbazar.com' ),
		'id' => 'widget_area_06',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    )); 
    register_sidebar(array(
        'name' => __( 'Widget Area 07', 'Themesbazar.com' ),
		'id' => 'widget_area_07',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    )); 
    
	
    register_sidebar(array(
        'name' => __( 'Single Page Top', 'Themesbazar.com' ),
		'id' => 'single_top',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
	register_sidebar(array(
        'name' => __( 'Single Page Middle', 'Themesbazar.com' ),
		'id' => 'single_middle',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));
	register_sidebar(array(
        'name' => __( 'Single Page Bottom', 'Themesbazar.com' ),
		'id' => 'single_bottom',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));

	 register_sidebar(array(
        'name' => __( 'Single Sidebar', 'Themesbazar.com' ),
		'id' => 'single_sidebar',
        'description'   => esc_html__( 'Add widgets here.', 'themes' ),
		'before_widget' => '<div class="widget_area">',
		'after_widget' => '</div>',
	    'before_title' => '<h3>',
	    'after_title' => '</h3>',
    ));

    
    
}
add_action('widgets_init', 'newsstar_widgets_areas');





























































function root(){
	$txt = "কারিগরি সহযোগিতায়: <a href='https://www.facebook.com/bdithost' target='_blank' rel='noopener'>BD IT HOST</a>";
	echo $txt;
}

