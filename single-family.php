<?php
/**
 * Single "Our Family" member. Owner: article agent.
 */
get_header();
?>

		<div class="row">
					<div class="psingle_page">

						<div class="col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1">

						<?php if ( have_posts() ) : ?>
						<?php while ( have_posts() ) : the_post(); ?>

							<div class="ppage_dtails">
								<h4 class="ppage_hadding"><?php the_title(); ?></h4>
								<?php $sokal_deg = get_post_meta( get_the_ID(), 'reporter_deg', true ); ?>
								<h4 class="ppage_deg"><?php echo esc_html( is_scalar( $sokal_deg ) ? (string) $sokal_deg : '' ); ?></h4>

								<?php if ( has_post_thumbnail() ) : ?>
								<div class="row">
									<div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3">
										<div class="single-family-img">
											<?php
											the_post_thumbnail(
												'post-thumbnail',
												array(
													'loading'       => 'eager',
													'fetchpriority' => 'high',
													'decoding'      => 'async',
													'sizes'         => '(max-width: 767px) 100vw, 50vw',
												)
											);
											?>
										</div>
									</div>
								</div>
								<?php endif; ?>

								<div class="single-dtlss">
									<?php the_content(); ?>
								</div>

							</div>

						<?php endwhile; ?>
						<?php endif; ?>
						</div>

					</div>
				</div>

<?php get_footer(); ?>
