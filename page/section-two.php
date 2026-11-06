<?php
/**
 * Home section two: five wide category blocks (col-md-9) with ad slots between,
 * and five narrow blocks (col-md-3) with the Facebook page box and date archive.
 * Each block is page/category-block.php; categories come from sokal_home_categories().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sokal_blocks = sokal_home_blocks();

// Load every block's category term in one query.
$sokal_ids = array_filter( array_merge( wp_list_pluck( $sokal_blocks['main'], 'cat' ), wp_list_pluck( $sokal_blocks['side'], 'cat' ) ) );
if ( $sokal_ids && function_exists( '_prime_term_caches' ) ) {
	_prime_term_caches( array_unique( array_map( 'intval', $sokal_ids ) ), false );
}

$sokal_fb      = (int) sokal_home_legacy( 'facebook', 1 );
$sokal_fb_link = sokal_home_legacy( 'facebook-link', array() );
$sokal_fb_url  = is_array( $sokal_fb_link ) && ! empty( $sokal_fb_link['face-url'] ) ? $sokal_fb_link['face-url'] : '';
if ( '' === $sokal_fb_url ) {
	$sokal_fb_url = (string) sokal_opt( 'social_facebook', '' );
}
$sokal_fb_w = sokal_int( sokal_home_legacy( 'facebook-width', 390 ), 180, 500 );
$sokal_fb_h = sokal_int( sokal_home_legacy( 'facebook-height', 300 ), 70, 2000 );
$sokal_cal  = (int) sokal_home_legacy( 'acrive-calender', 1 );
?>

			<!--================================
				   Section Two  Start
			==================================-->

			<div class="row">
				<div class="col-md-9 col-sm-9">
					<?php
					foreach ( $sokal_blocks['main'] as $sokal_block ) {
						get_template_part( 'page/category-block', null, array( 'cat' => $sokal_block['cat'], 'n' => $sokal_block['n'], 'layout' => 'wide' ) );
						sokal_home_ad( $sokal_block['ad'] );
					}
					?>
				</div>


				<div class="col-md-3 col-sm-3">
					<?php
					foreach ( $sokal_blocks['side'] as $sokal_i => $sokal_block ) {
						$sokal_block_args = array( 'cat' => $sokal_block['cat'], 'n' => $sokal_block['n'], 'layout' => 'narrow' );
						if ( 0 === $sokal_i ) {
							get_template_part( 'page/category-block', null, $sokal_block_args );
							?>

                    <!------------ Facebook & archive calender area ------------>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <?php if ( is_active_sidebar( 'sidebar_widget' ) ) : ?>
                                <div class="add">
                                    <?php dynamic_sidebar( 'sidebar_widget' ); ?>
                                </div>
                                <?php endif; ?>

								<!-- Facebook Start -->

							<?php if ( 1 === $sokal_fb && '' !== $sokal_fb_url ) : ?>

							<div class="facebook_title">
							<?php echo esc_html( sokal_home_legacy( 'facebook-title', '' ) ); ?>
							</div>

								<div class="fb-root">
									<div id="fb-root"></div>
									<div class="fb-page" data-href="<?php echo esc_url( $sokal_fb_url ); ?>" data-tabs="timeline" data-width="<?php echo esc_attr( $sokal_fb_w ); ?>" data-height="<?php echo esc_attr( $sokal_fb_h ); ?>" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
										<blockquote cite="<?php echo esc_url( $sokal_fb_url ); ?>" class="fb-xfbml-parse-ignore"><a href="<?php echo esc_url( $sokal_fb_url ); ?>" target="_blank" rel="noopener">ফেসবুকে আমাদের অনুসরণ করুন</a></blockquote>
									</div>
							   </div>

							<?php endif; ?>

						   <!-- archive Start -->

							<?php if ( 1 === $sokal_cal ) : ?>

								<div class="archive_calender_sec">
									<div class="archive_title"><?php echo esc_html( sokal_home_legacy( 'calender-title', '' ) ); ?></div>
									 <form class="example sokal-archive-date" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
										<input type="date" id="datepicker" autocomplete="off"
										placeholder="<?php echo esc_attr( sokal_home_legacy( 'select-date', '' ) ); ?>" aria-label="<?php echo esc_attr( sokal_home_legacy( 'select-date', 'তারিখ' ) ); ?>"
										value="" name="m" required />
										<button type="submit"><?php echo esc_html( sokal_home_legacy( 'date-search', '' ) ); ?></button>
									</form>
								</div>

							<?php endif; ?>

                            </div>
                        </div>

              		<!------------Facebook & archive calender area -------------->
							<?php
						} elseif ( $sokal_block['cat'] ) {
							// Blocks far below the fold: the browser skips their rendering until scrolled near.
							echo '<div class="sokal-cv">';
							get_template_part( 'page/category-block', null, $sokal_block_args );
							echo '</div>';
						}
					}
					?>
				</div>
			</div>
