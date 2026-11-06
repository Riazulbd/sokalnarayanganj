<?php
get_header();
global $themesbazar, $wp_query;

$sokal_tb   = is_array( $themesbazar ) ? $themesbazar : array();
$sokal_word = isset( $sokal_tb['word-archive'] ) && '' !== $sokal_tb['word-archive'] ? absint( $sokal_tb['word-archive'] ) : 30;
$sokal_more = ! empty( $sokal_tb['read-more-archive'] ) ? $sokal_tb['read-more-archive'] : 'বিস্তারিত';

// Breadcrumb label: works for categories, tags, custom taxonomies and authors.
$sokal_obj = get_queried_object();
if ( $sokal_obj instanceof WP_Term ) {
	$sokal_archive_name = $sokal_obj->name;
} elseif ( $sokal_obj instanceof WP_User ) {
	$sokal_archive_name = $sokal_obj->display_name;
} else {
	$sokal_archive_name = wp_strip_all_tags( get_the_archive_title() );
}

// Small story photo: column is about a third of the 8-column content area.
$sokal_card_attrs = array(
	'loading'  => 'lazy',
	'decoding' => 'async',
	'sizes'    => '(max-width: 767px) 100vw, 240px',
);

?>

         <!--==================================
               archive-page-section start
         ===================================-->

         <section class="archive-page-section">



                    <div class="row">

                        <div class="col-md-8 col-sm-8">
<div class="category_info">
<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa fa-home" aria-hidden="true"></i>
							</a> <i class="fa fa-chevron-right"></i> <?php echo esc_html( $sokal_archive_name ); ?></div>
							<?php
			    if ( is_paged() ) {

			        if ( have_posts() ) :
                     while ( have_posts() ) : the_post(); ?>
                      <div class="archive_page archive_back">
				<div class="col-md-4 col-sm-4">
				    <!-- Post Image Code Start-->
					<?php sokal_thumb( 'card-thumb', $sokal_card_attrs ); ?>
					<!-- Post Image Code Close-->
					</div>

					<div class="col-md-8 col-sm-8">
				    <h3 class="archive_title01"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a></h3>
                       <?php excerpt( $sokal_word ); ?>
                       <h4 class="archvie_more"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_more ); ?></a></h4>
				</div></div>
                <?php endwhile;
                    endif;

			    } else {
			    /*
			     * Page 1: render the 1 / 2 / 3 / rest groups from the main query
			     * instead of running four extra queries.
			     */
			    $sokal_posts = is_array( $wp_query->posts ) ? $wp_query->posts : array();
			    if ( $sokal_posts && function_exists( 'update_post_thumbnail_cache' ) ) {
			        update_post_thumbnail_cache( $wp_query );
			    }
			    $sokal_g1 = array_slice( $sokal_posts, 0, 1 );
			    $sokal_g2 = array_slice( $sokal_posts, 1, 2 );
			    $sokal_g3 = array_slice( $sokal_posts, 3, 3 );
			    $sokal_g4 = array_slice( $sokal_posts, 6 );
			    global $post;
			    ?>

		<?php if ( $sokal_g1 || $sokal_g2 ) : ?>
		<div class="row archive_page">
		    <div class="col-md-8 col-sm-8">
		         <?php
foreach ( $sokal_g1 as $post ) :
	setup_postdata( $post );
	// Lead story is the largest image above the fold.
	sokal_thumb( 'post-thumbnail', array(
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'decoding'      => 'async',
		'sizes'         => '(max-width: 767px) 100vw, 500px',
	) );
	?>

					<h3 class="archive_title01"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a></h3>
                       <?php excerpt( $sokal_word ); ?> <span style="text-align:right"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_more ); ?></a></span>
     <?php endforeach; ?>
		    </div>
		    <div class="col-md-4 col-sm-4">

		        <?php
foreach ( $sokal_g2 as $post ) :
	setup_postdata( $post );
	sokal_thumb( 'card-thumb', $sokal_card_attrs );
	?>


					<h3 class="archive_title02"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a></h3>

     <?php endforeach; ?>


		    </div>
		</div>
		<?php endif; ?>

		<?php if ( $sokal_g3 ) : ?>
			<div class="row archive_page">
		 <?php
foreach ( $sokal_g3 as $post ) :
	setup_postdata( $post );
	?>

		    <div class="col-md-4 col-sm-4">
		        <!-- Post Image Code Start-->
					<?php sokal_thumb( 'card-thumb', $sokal_card_attrs ); ?>
					<!-- Post Image Code Close-->
				   <h3 class="archive_title02"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a></h3>
		    </div>

	 <?php endforeach; ?>

	 	</div>
		<?php endif; ?>

		 <?php
foreach ( $sokal_g4 as $post ) :
	setup_postdata( $post );
	?>

		<div class="archive_page archive_back">
		    <div class="col-md-4 col-sm-4">
		        <!-- Post Image Code Start-->
					<?php sokal_thumb( 'card-thumb', $sokal_card_attrs ); ?>
					<!-- Post Image Code Close-->

		    </div>
		    <div class="col-md-8 col-sm-8">
		        <h3 class="archive_title01"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a></h3>

                       <?php excerpt( $sokal_word ); ?> <h4 class="archvie_more"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_more ); ?></a></h4>

		    </div>
		</div>

	 <?php endforeach;
	 wp_reset_postdata();
	 ?>


                   <?php } ?>

                <div class="row"><div class="col-md-12 col-sm-12"><?php wp_bootstrap_pagination(); ?></div></div>
						</div>

                  <div class="col-md-4 col-sm-4">
                    <?php sokal_sidebar_tabs(); ?>

					<?php if ( is_active_sidebar( 'single_sidebar' ) ) : ?>
					<div class="add">
						 <?php dynamic_sidebar( 'single_sidebar' ); ?>
					</div>
					<?php endif; ?>

                </div>



                </div>

         </section>



        <!---------  body-section close --------->


		<?php get_footer(); ?>
