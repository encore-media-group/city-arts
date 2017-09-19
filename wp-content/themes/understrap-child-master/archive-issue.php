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
//example: july-2017
$archive_slug =  get_queried_object()->slug;

$date = explode( "-", $archive_slug );
$nmonth = date( 'm', strtotime( $date[0] ) );
$full_date = "1/" . $nmonth . "/" . $date[1];

$issue_date =  date('d/m/Y', strtotime($full_date));
$date_now = new DateTime();

$issue_year_month = date('Y-m', strtotime($full_date));
$date_now_year_month = $date_now->format('Y-m');


	$issue_query_slugs[] = $archive_slug;

	if ( $issue_year_month == $date_now_year_month ) {
		echo 'build slugs for past two issues.';
	  $cover_slot_b = strtolower(date("F-Y", strtotime("-2 months " . $issue_date)));
		$cover_slot_c = strtolower(date("F-Y", strtotime("-1 months " . $issue_date)));
  } elseif( $issue_date < $date_now)  {
	  echo 'build prior and following month';
	  $cover_slot_b = strtolower(date("F-Y", strtotime("-1 months " . $issue_date)));
		$cover_slot_c = strtolower(date("F-Y", strtotime("+1 months " . $issue_date)));
	}

	$issue_query_slugs[] = $cover_slot_b;
	$issue_query_slugs[] = $cover_slot_c;

	$cover_story_query =  new WP_Query(array(
	    'posts_per_page' => 3,
	    'nopaging' => true,
	    'post_status'=> 'publish',
	    'ignore_sticky_posts' => true,
	    'tax_query' => [
				'relation' => 'AND',
					[
		        'taxonomy' => 'category',
		        'field'    => 'slug',
		        'terms'    =>  $issue_query_slugs,
					],
		    	[
		        'taxonomy' => 'category',
		        'field'    => 'slug',
		        'terms'    =>  array( 'cover-story' ),
					],
	     ],
	));
			$cover_story_post_1 = '';
			$cover_story_post_2 = '';
			$cover_story_post_3 = '';

   	while( $cover_story_query->have_posts() ) : $cover_story_query->the_post();
				if ( in_category( $archive_slug ) ) :
					$cover_story_post_1 = map_cover_story_to_post( get_post(), $archive_slug);
				elseif( in_category( $cover_slot_b ) ) :
					$cover_story_post_2 = map_cover_story_to_post( get_post(), $cover_slot_b);
				elseif( in_category( $cover_slot_c ) ) :
					$cover_story_post_3 = map_cover_story_to_post( get_post(), $cover_slot_c);
				endif;
		endwhile;
  	wp_reset_postdata();
/*
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
)); */
/*
$this_issue_query = new WP_Query(array(
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
)); */

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
	        		global $post;
	        		$post = $cover_story_post_1['the_post'];
	        		setup_postdata( $post );

		 					$cover_slug = $cover_story_post_1['the_slug'];
	 						$cover_image_src =  get_field('cover_image')['url'];
		 					$issue_publish_date = get_field('issue_publish_date');

		 					get_template_part( 'item-templates/item', '730x487-vertical' );

	     				wp_reset_postdata();
	     			?>
	     			<div class="col">
	     				<div class="row">
	     					<div class="col">
				 					<?php
				 					echo "<a href=\"/" . $cover_slug . "\">";
						 			echo '<img src="' . esc_url(  $cover_image_src ) . '" class="img-fluid" style="max-width: 350px;max-height:454px" alt="">';
						 			echo '</a>';
						 			?>
				 				</div>
     					</div>
     					<div class="row">
     						<div class="col"><?php display_cover_story( $cover_story_post_2 ); ?></div>
     						<div class="col"><?php display_cover_story( $cover_story_post_3 ); ?></div>
     					</div>
					</div>
				</div><!-- col -->
				<div class="row">

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
	         // while( $photo_essays_query->have_posts() ) : $photo_essays_query->the_post();
						?>
							<div class="col-12 col-lg-6">
								<?php
			       //     get_template_part( 'item-templates/item', '540x360-vertical' );
			          ?>
			          end photos
		        	</div>

	          <?php
	        //  endwhile;
	        //  wp_reset_postdata();
	          ?>
	      </div>
		</div>
		<div class="container mb-4">
			<?php
		  //  while( $this_issue_query>have_posts() ) : $this_issue_query->the_post();

		 //		get_template_part( 'item-templates/item', '320x213' );

		///		endwhile;
			//	wp_reset_postdata();
			?>
		</div>

	</main><!-- #main -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
