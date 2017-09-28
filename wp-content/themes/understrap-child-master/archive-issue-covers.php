<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

get_header();
?>

<?php

$page_title = 'The Covers';


$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;


$the_query = new WP_Query(array(
    'posts_per_page' => 16,
  //  'meta_query' => array( array('key' => '_thumbnail_id' ) ),
    'paged' => $paged,
    'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( 'cover-story' ),
        				'operator' => 'IN'
            ],
        ],
));

?>

<div class="wrapper" id="archive-wrapper">

	<div class="container" id="content" tabindex="-1">

		<div class="row pt-4">

			<main class="col site-main" id="main">

				<?php if ( have_posts() ) : ?>
					<header class="page-header row w-md-75 mx-auto">
						<h2 class="col page-title pb-4 sidelines"> <?= $page_title; ?> </h2>
					</header><!-- .page-header -->
				<? endif; ?>

					<div class="row">
					<?php
						$count = 1;
            while( $the_query->have_posts() ) : $the_query->the_post();

							echo '<div class="col--2 col-md-3 mb-3">';
							echo insert_cover_story_shortcode();
							echo '</div>';

							if ($count == 8 ):
						 		echo '</div><!-- end row -->';
				 				get_template_part( 'item-templates/item', 'landscape-ad' );
						 		echo '<div class="row">';
							endif;
						 	if ($count >= 4 && $count % 4 == 0) :
						 		echo '<div class="m-100"></div>';
						 	endif;

							$count++;
						endwhile;
						wp_reset_postdata();
					?>
					</div><!-- end row -->
				</div><!--row-->
			</main><!-- #main -->

		</div><!-- #primary -->

	</div> <!-- .row -->

</div><!-- Container end -->

<divi class="container">
	<div class="row justify-content-center">
		<div class="col-auto">
		<!-- The pagination component -->
		<?php understrap_pagination(); ?>
		</div>
	</div>
</divi>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
