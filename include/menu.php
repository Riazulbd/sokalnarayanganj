<?php 

if(function_exists('register_nav_menus')){
		
		register_nav_menus(array(
		  'main-menu' => __('Main menu','news'),
		  'footer-menu' => 'ফুটার মেনু (বিভাগের লিংক)',
		
		));
		
	}
	
	function default_main_menu(){
		echo "<ul class=\"nav navbar-nav\">";
		
		
		
			if(is_user_logged_in()){
			echo '<li><a href="'.esc_url(home_url()).'/wp-admin/nav-menus.php">মেনু তৈরি করুন</a></li>';
		}
		else{
			echo '<li><a href="'.esc_url(home_url()).'">প্রচ্ছদ</a></li>';
		}
		

		echo "</ul>";
	}
	
	
	
	function default_footer_men(){
		echo "<ul class=\"list-inline\">";
		if(is_user_logged_in()){
			echo '<li><a href="'.esc_url(home_url()).'/wp-admin/nav-menus.php">মেনু তৈরি করুন</a></li>';
		}
		else{
			echo '<li><a href="'.esc_url(home_url()).'">প্রচ্ছদ</a></li>';
		}
		

		echo "</ul>";
	}

?>