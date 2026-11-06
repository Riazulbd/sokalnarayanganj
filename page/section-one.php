<?php
/**
 * Home section one: lead story, two side stories, Latest/Popular tabs, two ad slots.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sokal_cats    = sokal_home_categories();
$sokal_lead_q  = array(
	'post_type'              => 'post',
	'posts_per_page'         => 3,
	'post_status'            => 'publish',
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_term_cache' => false,
);
if ( $sokal_cats['lead'] ) {
	$sokal_lead_q['cat'] = $sokal_cats['lead'];
}
$sokal_lead_q   = new WP_Query( $sokal_lead_q );
update_post_thumbnail_cache( $sokal_lead_q );
$sokal_posts    = $sokal_lead_q->posts;
$sokal_excerpt  = sokal_int( sokal_home_legacy( 'excerpt-cat-one', 40 ), 0, 200 );
$sokal_more     = (string) sokal_home_legacy( 'read-more-archive', '' );
global $post;
?>

        <!--================================
				   Leadnews Section Start
			==================================-->


			<div class="leadnews_sec">
				<div class="row">
					<div class="col-md-5 col-sm-5">

						<?php foreach ( array_slice( $sokal_posts, 0, 1 ) as $post ) : setup_postdata( $post ); ?>

						 <div class="themesbazar_leadnews">
	                        <a href="<?php the_permalink(); ?>"> <?php sokal_thumb( 'post-thumbnail', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 470px' ) ); ?> </a>
	                        <h4 class="themesbazar_hadding_1"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
	                        <div class="content_dtails">
	                            <?php excerpt( $sokal_excerpt ); ?><span style="text-align:right"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_more ); ?></a></span>
	                        </div>
	                    </div>

						<?php endforeach; ?>

					</div>
					<div class="col-md-3 col-sm-3">

						<?php foreach ( array_slice( $sokal_posts, 1, 2 ) as $post ) : setup_postdata( $post ); ?>

						<div class="themesbazar_leadnews">
	                        <a href="<?php the_permalink(); ?>"> <?php sokal_thumb( 'post-thumbnail', array( 'loading' => 'eager', 'fetchpriority' => 'low', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 270px' ) ); ?> </a>
	                        <h4 class="themesbazar_hadding_2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
	                    </div>

	                    <?php endforeach; ?>
						<?php wp_reset_postdata(); ?>

					</div>
					<div class="col-md-4 col-sm-4">
						<?php if ( function_exists( 'sokal_sidebar_tabs' ) ) { sokal_sidebar_tabs(); } ?>
					</div>
				</div>

				<?php if ( is_active_sidebar( 'widget_area_01' ) || is_active_sidebar( 'widget_area_02' ) ) : ?>
				<!--------------add option start-------------->

                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <?php if ( is_active_sidebar( 'widget_area_01' ) ) : ?>
                                <div class="add sokal-ad">
                                    <?php dynamic_sidebar( 'widget_area_01' ); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <?php if ( is_active_sidebar( 'widget_area_02' ) ) : ?>
                                <div class="add sokal-ad">
                                    <?php dynamic_sidebar( 'widget_area_02' ); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

              		<!--------------add option close---------------->
				<?php endif; ?>

			</div>
