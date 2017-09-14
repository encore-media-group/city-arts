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
$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );

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

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">

				<?php if ( have_posts() ) : ?>

					<header class="page-header">
							<h1 class="page-title text-center py-4"> <?php single_cat_title() ?> </h1>
					</header><!-- .page-header -->

					<?php
		        while( $cover_story_query->have_posts() ) : $cover_story_query->the_post();
								echo '<div class="row">';
									echo '<div class="col">';

							 		get_template_part( 'item-templates/item', '730x487-vertical' );
							 		$cover_image =  get_field('cover_image');

									echo '</div><div class="col">';
							 		?>
							 		<img src="<?php echo esc_url(  $cover_image['url'] ); ?>"
					        class="img-fluid"
					        style="max-width: 350px;max-height:454px"
					        alt="">
					      	</div><!--col -->
				      	</div><!--row-->
						<?php
            endwhile;

            while( $the_query->have_posts() ) : $the_query->the_post();

		 				get_template_part( 'item-templates/item', 'landscape-ad' );

				 		get_template_part( 'item-templates/item', '540x360' );

				 		get_template_part( 'item-templates/item', '540x360' );

				 		get_template_part( 'item-templates/item', '320x213' );

						endwhile;
						wp_reset_postdata();

					endif;
					?>

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
