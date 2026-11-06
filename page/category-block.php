<?php
/**
 * One home category block (section two).
 *
 * Usage: get_template_part( 'page/category-block', null, array( 'cat' => 12, 'n' => 'two', 'layout' => 'wide' ) );
 *   cat    category id (block is skipped when it isn't a valid category)
 *   n      legacy key suffix: reads how_post_{n} and excerpt-cat-{n}
 *   layout 'wide'   = lead story + small cards (col-md-9 column)
 *          'narrow' = small cards only (col-md-3 column)
 *
 * Markup is the same as the original copy-pasted blocks, except id="pointer" is now class="pointer".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sokal_b    = isset( $args ) && is_array( $args ) ? $args : array();
$sokal_cat  = isset( $sokal_b['cat'] ) ? absint( $sokal_b['cat'] ) : 0;
$sokal_n    = isset( $sokal_b['n'] ) ? sanitize_key( $sokal_b['n'] ) : '';
$sokal_wide = isset( $sokal_b['layout'] ) && 'wide' === $sokal_b['layout'];
$sokal_term = $sokal_cat ? get_term( $sokal_cat, 'category' ) : null;

if ( ! $sokal_term || is_wp_error( $sokal_term ) ) {
	return;
}

$sokal_name  = $sokal_term->name;
$sokal_link  = get_term_link( $sokal_term );
$sokal_link  = is_wp_error( $sokal_link ) ? '' : $sokal_link;
$sokal_style = (int) sokal_home_legacy( 'catagory-style', 1 );
$sokal_more  = (string) sokal_home_legacy( 'more_news', '' );
$sokal_read  = (string) sokal_home_legacy( 'read-more-archive', '' );
$sokal_count = sokal_int( sokal_home_legacy( 'how_post_' . $sokal_n, 5 ), 1, 20 );
$sokal_words = sokal_int( sokal_home_legacy( 'excerpt-cat-' . $sokal_n, 20 ), 0, 200 );

// One query for the lead story and the small cards.
$sokal_q = new WP_Query(
	array(
		'post_type'              => 'post',
		'cat'                    => $sokal_cat,
		'posts_per_page'         => $sokal_count,
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	)
);
update_post_thumbnail_cache( $sokal_q );

$sokal_lead  = $sokal_wide ? array_slice( $sokal_q->posts, 0, 1 ) : array();
$sokal_cards = $sokal_wide ? array_slice( $sokal_q->posts, 1 ) : $sokal_q->posts;
$sokal_h     = $sokal_wide ? 'themesbazar_hadding_2' : 'themesbazar_hadding_3';
$sokal_sizes = $sokal_wide ? '(max-width: 767px) 32vw, 130px' : '(max-width: 767px) 32vw, 90px';

global $post;
?>
						<?php if ( 1 === $sokal_style ) : ?>
								<div class="themesbazar_catagory_title">
									<span><a href="<?php echo esc_url( $sokal_link ); ?>">  <?php echo esc_html( $sokal_name ); ?></a> </span>
								</div>
						<?php elseif ( 2 === $sokal_style ) : ?>
									<div class="cat_title_two">
										<a href="<?php echo esc_url( $sokal_link ); ?>"> <i class="fa fa-bars" aria-hidden="true"></i>  <?php echo esc_html( $sokal_name ); ?> </a>
									</div>
						<?php elseif ( 3 === $sokal_style ) : ?>
								<div class="cat_title_three">
									<a href="<?php echo esc_url( $sokal_link ); ?>"> <i class="fa fa-newspaper-o" aria-hidden="true"></i>  <?php echo esc_html( $sokal_name ); ?> </a>
								</div>
						<?php elseif ( 4 === $sokal_style ) : ?>
								<div class="cat_title_four">
									<div class="pointer">
										<a href="<?php echo esc_url( $sokal_link ); ?>"><i class="fa fa-newspaper-o" aria-hidden="true"></i> <?php echo esc_html( $sokal_name ); ?> </a>
									</div>
								</div>
						<?php endif; ?>
<?php if ( $sokal_wide ) : ?>

						<div class="row">

							<div class="col-md-6 col-sm-6">
								<?php foreach ( $sokal_lead as $post ) : setup_postdata( $post ); ?>

								<div class="themesbazar_leadnews">
									<h4 class="themesbazar_hadding_1"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
			                        <a href="<?php the_permalink(); ?>"> <?php sokal_thumb( 'post-thumbnail', array( 'loading' => 'lazy', 'fetchpriority' => 'low', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 400px' ) ); ?> </a>
			                        <div class="content_dtails">
			                           <?php excerpt( $sokal_words ); ?><span style="text-align:right"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_read ); ?></a></span>
			                        </div>
			                    </div>

								<?php endforeach; ?>
							</div>
							<div class="col-md-6 col-sm-6">
<?php endif; ?>
								<?php foreach ( $sokal_cards as $post ) : setup_postdata( $post ); ?>

								<div class="small_images border">
                                    <a href="<?php the_permalink(); ?>"> <?php sokal_thumb( 'card-thumb', array( 'loading' => 'lazy', 'fetchpriority' => 'low', 'decoding' => 'async', 'sizes' => $sokal_sizes ) ); ?> </a>
                                    <h4 class="<?php echo esc_attr( $sokal_h ); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                </div>

								<?php endforeach; ?>
								<?php wp_reset_postdata(); ?>

								<div class="row">
                                    <div class="col-md-12">
                                        <div class="themesbazar_more_news">
                                            <a href="<?php echo esc_url( $sokal_link ); ?>"> <?php echo esc_html( $sokal_more ); ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i>  </a>
                                        </div>
                                    </div>
                                </div>
<?php if ( $sokal_wide ) : ?>

							</div>
						</div>
<?php endif; ?>
