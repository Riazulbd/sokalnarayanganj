<?php



function metabox_family(array $product_meta){
	
	$product_meta[] = array(
	
			'id' => 'second-section',
			'title' => 'Extra Fields',
			'object_types' => array('family'),
			'fields' => array(
					
					array(
						'id' => 'reporter_deg',
						'name' => 'Reporter Designation Text',
						'type' => 'text',
						
					),
					
					
					
			)

	);
	
	return $product_meta;	
}
add_filter('cmb2_meta_boxes','metabox_family');



/*
function metabox_product(array $product_meta){
	
	$product_meta[] = array(
	
			'id' => 'first-section',
			'title' => 'Fist Metabox',
			'object_types' => array('product', 'post'),
			'fields' => array(
					array(
						'id' => 'developer',
						'name' => 'Developer Version',
						'type' => 'text',
						
					),
			)

	);
	
	return $product_meta;
}
add_filter('cmb2_meta_boxes','metabox_product');


Output=========
<?php echo get_post_meta(get_the_ID(),'developer', true); ?>

*/