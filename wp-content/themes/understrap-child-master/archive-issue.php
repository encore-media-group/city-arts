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

$date = explode( "-", $archive_slug );
$nmonth = date( 'm', strtotime( $date[0] ) );
$full_date = "1/" . $nmonth . "/" . $date[1];

$issue_date =  date('d/m/Y', strtotime($full_date));
$date_now = new DateTime();

if ($date_now > $issue_date) {
        echo 'greater than';
    }else{
        echo 'Less than';
    }
/*
convert slug to time data

if slug = current month, then build slugs for past two issues.
if slug < current month, then grab past and next monht.
*/
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
//var_dump($cover_story_query);

$photo_essays_query = new WP_Query(array(
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
	        'terms'    =>  array( 'feature' ),
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
  <main class="site-main" id="main">
		<?php if ( have_posts() ) : ?>
			<header class="page-header">
				<h1 class="page-title text-center py-4"> <?php single_cat_title() ?> </h1>
			</header><!-- .page-header -->
		<?php endif; ?>

   	<div class="container mb-4" id="content" tabindex="-1">
      <div class="row pt-4">
        <div class="col-12 px-0 px-sm col-md-auto" id="primary">
          <div class="row mx-0">
						<?php
	  					while( $cover_story_query->have_posts() ) : $cover_story_query->the_post();
			 					get_template_part( 'item-templates/item', '730x487-vertical' );
			 					$cover_image =  get_field('cover_image');
			 					$issue_publish_date = get_field('issue_publish_date');
			 					var_dump($issue_publish_date);
			 			?>
					</div>
				</div><!-- col -->
				<div class="col">
			 		<img src="<?php echo esc_url(  $cover_image['url'] ); ?>"
	        class="img-fluid"
	        style="max-width: 350px;max-height:454px"
	        alt="">
          <?php
        		endwhile;
            wp_reset_postdata();
          ?>
          </div>
			</div><!--row-->
		</div><!-- Container end -->

		<div class="container-fluid ad-container mb-4">
		  <div class="row no-gutters">
		    <div class="col-xl-12 py-2 text-center">
		      <?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
		    </div>
		  </div>
		</div>
		<div class="container mb-4">
			<div class="row">
	          photos
	          <?php
	          while( $photo_essays_query->have_posts() ) : $photo_essays_query->the_post();
						?>
							<div class="col-12 col-lg-6">
								<?php
			            get_template_part( 'item-templates/item', '540x360-vertical' );
			          ?>
			          end photos
		        	</div>

	          <?php
	          endwhile;
	          wp_reset_postdata();
	          ?>
	      </div>
		</div>
		<div class="container mb-4">
			<?php
		    while( $the_query->have_posts() ) : $the_query->the_post();

		 		get_template_part( 'item-templates/item', '320x213' );

				endwhile;
				wp_reset_postdata();
			?>
		</div>

	</main><!-- #main -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
