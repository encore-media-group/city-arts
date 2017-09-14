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

$archive_slug =  get_queried_object()->slug;

$cover_story_query = new WP_Query(array(
    'posts_per_page' => 1,
    'tax_query' => [
			'relation' => 'AND',
				[
	        'taxonomy' => 'category',
	        'field'    => 'slug',
	        'terms'    =>  array( $archive_slug ),
					'operator' => 'IN' ],
	    	[
	        'taxonomy' => 'category',
	        'field'    => 'slug',
	        'terms'    =>  array( 'cover-story' ),
					'operator' => 'IN' ],
     ],
));

$the_query = new WP_Query(array(
    'posts_per_page' => $posts_per_page,
  //  'meta_query' => array( array('key' => '_thumbnail_id' ) ),
    'paged' => $paged,
    'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( $archive_slug ),
                'include_children' => true,
        				'operator' => 'IN'
            ],
        ],
));

?>

<div class="wrapper" id="archive-wrapper">

	<div class="container px-0 m-0 mx-md-auto mb-4" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">

				<?php if ( have_posts() ) : ?>

					<header class="page-header">
							<h1 class="page-title text-center py-4"> <?php single_cat_title() ?> </h1>
					</header><!-- .page-header -->

				<?php
				endif;
	        while( $cover_story_query->have_posts() ) : $cover_story_query->the_post();
							echo '<div class="row">';

						 		get_template_part( 'item-templates/item', '730x487-vertical' );
						 		$cover_image =  get_field('cover_image');

								echo '<div class="col">';
						 		?>
						 		<img src="<?php echo esc_url(  $cover_image['url'] ); ?>"
				        class="img-fluid"
				        style="max-width: 350px;max-height:454px"
				        alt="">
			      	</div><!--row-->
					<?php
          endwhile;
					?>

				</div><!--row-->

			</main><!-- #main -->

		</div><!-- #primary -->

		</div> <!-- .row -->

	</div><!-- Container end -->

	<div class="container-fluid ad-container mb-4">
	  <div class="row no-gutters">
	    <div class="col-xl-12 py-2 text-center">
	      <?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
	    </div>
	  </div>
	</div>
	<div class="container mb-4">
		<?php
	    while( $the_query->have_posts() ) : $the_query->the_post();

				get_template_part( 'item-templates/item', 'landscape-ad' );

	 		get_template_part( 'item-templates/item', '540x360' );

	 		get_template_part( 'item-templates/item', '540x360' );

	 		get_template_part( 'item-templates/item', '320x213' );

			endwhile;
			wp_reset_postdata();
	?>
	</div>

	<div class="container">
		<div class="row justify-content-center">
			<div class="col-auto">
			<!-- The pagination component -->
			<?php understrap_pagination(); ?>
			</div>
		</div>
	</divi>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
