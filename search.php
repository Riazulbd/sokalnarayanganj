<?php get_header();
global $themesbazar;

$sokal_tb   = is_array( $themesbazar ) ? $themesbazar : array();
$sokal_word = isset( $sokal_tb['word-archive'] ) && '' !== $sokal_tb['word-archive'] ? absint( $sokal_tb['word-archive'] ) : 30;
$sokal_more = ! empty( $sokal_tb['read-more-archive'] ) ? $sokal_tb['read-more-archive'] : 'বিস্তারিত';
?>




	<!-- Section 06 (top two div) #################################-->
	<section class="search-page-padding">


		<!--gellary open--->
		<div class="row">
			<div class="col-md-8 col-sm-7">

    <?php if ( have_posts() ) : ?>
    <!-- the loop -->


    <?php while ( have_posts() ) : the_post(); ?>
				<div class="archive_page archive_back">
		    <div class="col-md-4">
		        <!-- Post Image Code Start-->
					<?php sokal_thumb( 'card-thumb', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 240px' ) ); ?>
					<!-- Post Image Code Close-->

		    </div>
		    <div class="col-md-8">
		        <h3 class="archive-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a></h3>

                       <?php excerpt( $sokal_word ); ?> <h4 class="archvie_more"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $sokal_more ); ?></a></h4>

		    </div>
		</div>
    <?php endwhile; ?>

    <!-- end of the loop -->

    <div class="row"><div class="col-md-12 col-sm-12"><?php wp_bootstrap_pagination(); ?></div></div>

<?php else : ?>
    <p><?php echo esc_html( 'দুঃখিত, কোনো খবর পাওয়া যায়নি। আবার চেষ্টা করুন।' ); ?></p>
<?php endif; ?>

			</div>


			<div class="col-md-4 col-sm-5">
                    <?php sokal_sidebar_tabs(); ?>


                </div>
		</div>

		</section>



	<?php get_footer(); ?>
