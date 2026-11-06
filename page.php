<?php get_header(); ?>



<section class="singlepage-section">




		<!--gellary open--->
		<div class="row">

			<div class="col-md-8 col-sm-8">

               <?php if ( have_posts() ) : ?>
               <?php while ( have_posts() ) : the_post(); ?>


               <div class="single_images"><?php
                           if ( has_post_thumbnail() ) {
                               // Page hero is the largest image above the fold.
                               the_post_thumbnail( 'post-thumbnail', array(
                                   'loading'       => 'eager',
                                   'fetchpriority' => 'high',
                                   'decoding'      => 'async',
                                   'sizes'         => '(max-width: 767px) 100vw, 750px',
                               ) );
                           }
                           ?>

                           <?php
                           $caption = has_post_thumbnail() ? wp_get_attachment_caption( get_post_thumbnail_id() ) : '';
                                if ( $caption ) : ?>
                                         <div class="caption"><?php echo esc_html( $caption ); ?> </div>
                                <?php endif; ?>
                       </div>

					<h3 class="single_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

					 <div class="page-content"><?php the_content(); ?></div>


                 <?php endwhile; ?>
                <?php endif; ?>

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



	<?php get_footer(); ?>
