<?php
/**
 * Single article. Owner: article agent.
 */
get_header();

global $themesbazar;
$sokal_tb  = is_array( $themesbazar ) ? $themesbazar : array();
$sokal_opt = function ( $key, $fallback = '' ) use ( $sokal_tb ) {
	return ( isset( $sokal_tb[ $key ] ) && '' !== $sokal_tb[ $key ] ) ? $sokal_tb[ $key ] : $fallback;
};
$sokal_bn_digits = ( 1 === (int) $sokal_opt( 'site-content', 1 ) );
?>

         <!--==================================
               national-section start
         ===================================-->

         <section class="singlepage-section">
            <div class="row">
                <div class="col-md-8 col-sm-8">

					<?php if ( is_active_sidebar( 'single_top' ) ) : ?>
					<div class="widget sokal-ad-single-top">
						<?php dynamic_sidebar( 'single_top' ); ?>
					</div>
					<?php endif; ?>

                    <?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>

                    <div class="single-cat-info">
                        <div class="single-cat-home">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa fa-home" aria-hidden="true"></i> <?php echo esc_html( $sokal_opt( 'home', 'প্রচ্ছদ' ) ); ?> </a>
                        </div>
                        <div class="single-cat-cate">
                            <i class="fa fa-bars" aria-hidden="true"></i>  <?php the_category( ', ' ); ?>
                        </div>
                    </div>

                    <div class="single-title">
                        <h3><?php the_title(); ?></h3>
                    </div>

                    <div class="sgl-page-views-count">
                        <ul>
                            <li>
							<?php echo esc_html( $sokal_opt( 'update', 'আপডেট সময় :' ) ); ?>
							<?php
							if ( $sokal_bn_digits ) {
								$sokal_en_date = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' );
								$sokal_bn_date = array( '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০', 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার' );
								echo esc_html( str_replace( $sokal_en_date, $sokal_bn_date, get_the_time( 'l, j F, Y' ) ) );
							} else {
								echo esc_html( get_the_time( 'l, F j, Y' ) );
							}
							?>
							</li>

						<?php if ( 1 === (int) $sokal_opt( 'view-tab', 1 ) && function_exists( 'getPostViews' ) ) : ?>
                        <li class="active">
							<?php
							$sokal_views = (string) getPostViews( get_the_ID() );
							?>
							<span class="js-view-count" data-post="<?php echo esc_attr( get_the_ID() ); ?>"><?php echo esc_html( $sokal_bn_digits ? bn_number( $sokal_views ) : $sokal_views ); ?></span>
							<?php echo esc_html( $sokal_opt( 'count', 'বার পড়া হয়েছে' ) ); ?>
						</li>
						<?php endif; ?>

                        </ul>
                    </div>

                    <?php if ( function_exists( 'sharethis_inline_buttons' ) ) : ?>
                    <div class="sokal-sharethis sokal-sharethis--top"><?php echo sharethis_inline_buttons(); // phpcs:ignore WordPress.Security.EscapeOutput -- ShareThis plugin markup. ?></div>
                    <?php endif; ?>

                    <div class="single-img">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail(
								'single-hero',
								array(
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
									'sizes'         => '(max-width: 767px) 100vw, (max-width: 1199px) 62vw, 750px',
								)
							);
						}
						$sokal_caption = has_post_thumbnail() ? wp_get_attachment_caption( get_post_thumbnail_id() ) : '';
						if ( $sokal_caption ) :
							?>
							<div class="caption">
								<?php echo wp_kses_post( $sokal_caption ); ?>
							</div>
						<?php endif; ?>
                    </div>

                    <?php if ( function_exists( 'sokal_share_buttons' ) ) : ?>
                    <div class="sgl-page-social sokal-share-under-image">
                        <?php
                        sokal_share_buttons();
                        if ( function_exists( 'sokal_share_picture_buttons' ) ) {
                            sokal_share_picture_buttons();
                        }
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="single-dtls">
                        <?php the_content(); ?>
                    </div>

                    <?php if ( function_exists( 'sharethis_inline_buttons' ) ) : ?>
                    <div class="sokal-sharethis sokal-sharethis--bottom"><?php echo sharethis_inline_buttons(); // phpcs:ignore WordPress.Security.EscapeOutput -- ShareThis plugin markup. ?></div>
                    <?php endif; ?>

					<?php if ( is_active_sidebar( 'single_middle' ) ) : ?>
					<div class="add">
						<?php dynamic_sidebar( 'single_middle' ); ?>
					</div>
					<?php endif; ?>


                    <div class="sgl-page-social-title">
                        <h4><?php echo esc_html( $sokal_opt( 'social_title', 'শেয়ার করুন' ) ); ?></h4>
                    </div>

                    <div class="sgl-page-social">
						<?php
						if ( function_exists( 'sokal_share_buttons' ) ) {
							sokal_share_buttons();
						} else {
							printf(
								'<a href="%s" class="facebook" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook" aria-hidden="true"></i> Facebook</a>',
								esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_permalink() ) )
							);
						}
						if ( function_exists( 'sokal_share_picture_buttons' ) ) {
							sokal_share_picture_buttons();
						}
						?>
						<button type="button" class="print js-print" data-sokal-print><i class="fa fa-print" aria-hidden="true"></i> প্রিন্ট</button>
                    </div>


					<?php if ( 1 === (int) $sokal_opt( 'coment', 1 ) ) : ?>
						<?php comments_template(); ?>
					<?php endif; ?>

					<?php endwhile; ?>
					<?php endif; ?>

                    <div class="sgl-cat-tittle">
                       <?php echo esc_html( $sokal_opt( 'more-news-category', 'এই বিভাগের আরো খবর' ) ); ?>
                    </div>
                    <?php sokal_related_posts( get_queried_object_id() ); ?>

					<?php if ( is_active_sidebar( 'single_bottom' ) ) : ?>
					<div class="add">
						<?php dynamic_sidebar( 'single_bottom' ); ?>
					</div>
					<?php endif; ?>

                </div>
                <div class="col-md-4 col-sm-4">
					<?php
					if ( function_exists( 'sokal_sidebar_tabs' ) ) {
						sokal_sidebar_tabs();
					}
					?>

					<?php if ( is_active_sidebar( 'single_sidebar' ) ) : ?>
					<div class="add">
						<?php dynamic_sidebar( 'single_sidebar' ); ?>
					</div>
					<?php endif; ?>

                </div>
            </div>
         </section>

<?php get_footer(); ?>
