<?php get_header(); ?>
        <?php
        global $themesbazar;
        $sokal_tb   = is_array( $themesbazar ) ? $themesbazar : array();
        $sokal_word = isset( $sokal_tb['word-archive'] ) && '' !== $sokal_tb['word-archive'] ? absint( $sokal_tb['word-archive'] ) : 30;
        $sokal_more = ! empty( $sokal_tb['read-more-archive'] ) ? $sokal_tb['read-more-archive'] : 'বিস্তারিত';
        ?>



         <!--==================================
               archive-page-section start
         ===================================-->

         <section class="archive-section">



		<!--gellary open--->

		<div class="row">
			<div class="col-md-8 col-sm-8">


					<?php if ( have_posts() ) : ?>
                     <?php
                     while ( have_posts() ) : the_post(); ?>

				   <div class="archive-page-post">
                        <div class="col-md-4 col-sm-4">
                            <div class="archive-img">
                                <?php sokal_thumb( 'card-thumb', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 240px' ) ); ?>
                            </div>
                        </div>

                        <div class="col-md-8 col-sm-8">
                            <div class="archive-title">

                                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a>

                            </div>
                            <div class="archive-content">
                                <?php excerpt( $sokal_word ); ?> <a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_more ); ?></a>
                            </div>
                        </div>
                    </div>


						<?php endwhile; ?>
                    <?php endif; ?>

			    <div class="col-md-12"><?php wp_bootstrap_pagination(); ?></div>


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
