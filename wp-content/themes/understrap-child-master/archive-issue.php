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

$issue_page_sections = [
	'editors-note',
	'lifestyle',
	'epilogue',
	'feature',
	'poetry',
	'artwork',
	'review',
	'news-notes',
	'preview',
	'top_features',
	'qa'
];

$issue_page_content = array_fill_keys( $issue_page_sections, [ 'posts' => [], 'cats' => [] ] );

$date = explode( "-", $archive_slug );

$full_date_string = "1/" . date( 'm', strtotime( $date[0] ) ) . "/" . $date[1];
$issue_date = date_create_from_format('d/m/Y', $full_date_string);

$nav_links = get_prev_next_issue_slugs( $issue_date ) ;

if( count($nav_links['next']) > 0 ):
	$cover_slot_b = $nav_links['previous']['slug'];
	$cover_slot_c = $nav_links['next']['slug'];
else:
	$cover_slot_c = null;//$nav_links['previous-2']['slug'];
	$cover_slot_b = $nav_links['previous']['slug'];
endif;

$issue_query_slugs[] = $archive_slug;
$issue_query_slugs[] = $cover_slot_b;
$issue_query_slugs[] = $cover_slot_c;

$cover_story_query =  get_cover_stories( $issue_query_slugs );

$issue_page_content = array_fill_keys( ['cover_slot_a', 'cover_slot_b', 'cover_slot_c'] , [ 'posts' => [], 'cats' => [] ] );

while( $cover_story_query->have_posts() ) : $cover_story_query->the_post();
	if ( in_category( $archive_slug ) ) :
		$issue_page_content[ 'cover_slot_a' ]['posts'][] = map_post_obj_and_slug( get_post(), $archive_slug );
	elseif( in_category( $cover_slot_b ) ) :
		$issue_page_content[ 'cover_slot_b' ]['posts'][] = map_post_obj_and_slug( get_post(), $cover_slot_b );
	elseif( in_category( $cover_slot_c ) ) :
		$issue_page_content[ 'cover_slot_c' ]['posts'][] = map_post_obj_and_slug( get_post(), $cover_slot_c );
	endif;
endwhile;
wp_reset_postdata();

$this_issue_query = new WP_Query(array(
    'posts_per_page' => -1,
    'nopaging' => true,
    'post_status'=> 'publish',
    'ignore_sticky_posts' => true,
    'tax_query' => [
    	'relation' => 'AND',
	      [
	          'taxonomy' => 'category',
	          'field'    => 'slug',
	          'terms'    =>  array( $archive_slug ),
	          'include_children' => true,
	  				'operator' => 'IN'
	      ],
				[
          'taxonomy' => 'category',
          'field' => 'slug',
          'terms' => array( 'cover-story' ),
          'operator' => 'NOT IN'
        ]
    ],
));


	foreach ($issue_page_sections as $section) {
		$cats = get_term_children( get_cached_cat_id_by_slug( $section ), 'category' );
		array_push( $cats , get_cached_cat_id_by_slug( $section ) );
		$issue_page_content[ $section ]['posts'] = [];
		$issue_page_content[ $section ]['cats'] = $cats;
	}

	while( $this_issue_query->have_posts() ) : $this_issue_query->the_post();
		foreach( $issue_page_sections as $section ) :
			if( in_category( $issue_page_content[ $section ]['cats'] ) ) :
				$issue_page_content[ $section ]['posts'][] = map_post_obj_and_slug( get_post(), $section );
			endif;
		endforeach;
	endwhile;
  wp_reset_postdata();

  //grab the first two features
	$feature_count = 1;

	foreach( $issue_page_content['feature']['posts'] as $key => $feature_post) {
		if( $feature_count <= 2 ) {
			$issue_page_content['top_features']['posts'][] = $feature_post;
			unset( $issue_page_content['feature']['posts'][$key] );
		}
		$feature_count++;
	}


	//merge previews into all_reviews
 	$issue_page_content['review']['posts']  = array_merge(
 		$issue_page_content['review']['posts'],
 		$issue_page_content['preview']['posts']
 	);
	uasort($issue_page_content["review"]["posts"], "compare_by_post_date");

	//merge remaining features into news-notes
	$issue_page_content['news-notes']['posts']  = array_merge(
 		$issue_page_content['news-notes']['posts'],
 		$issue_page_content['feature']['posts'],
 		$issue_page_content['qa']['posts']
 	);
	uasort($issue_page_content["news-notes"]["posts"], "compare_by_post_date");
?>

<div class="wrapper" id="archive-wrapper">
  <main class="site-main" id="main">
		<?php if ( have_posts() ) : ?>
			<header class="page-header">
				<h1 class="page-title text-center py-2 py-sm-4 m-0"> <?php single_cat_title() ?> </h1>
			</header><!-- .page-header -->
		<?php endif; ?>

   	<div class="container mb-4" id="content" tabindex="-1">
      <div class="row">
        <div class="col-12 px-0 px-sm col-md" id="primary">
          <div class="row mx-0">
							<?php
							$args = [
	        			'template' => [ 'path' => 'item-templates/item', 'file'=>'730x487-vertical' ] ];

							issue_display_posts( $issue_page_content['cover_slot_a']['posts'], $args );
	     			?>
	     		</div>
	     	</div>
	 			<div class="col-12 col-md-5 col-lg-4 mt-4 mt-md-0	">
	 				<div class="row mx-md-auto">
	 					<div class="col px-md-0 px-sm cover-story-350-wrapper">
		 					<?php
	          	$args = [
	          		'template' => [ 'path' => 'item-templates/item', 'file'=>'display-cover-story-350x454' ]
	          	];

	          		issue_display_posts( $issue_page_content['cover_slot_a']['posts'], $args );
				 			?>
		 				</div>
					</div>
					<div class="row d-flex justify-content-between mt-4">
						<div class="col-6 text-left">
							<?php
	          	$args = [
	          		'query_vars' => [ [ 'var' =>'direction', 'val' => 'left' ] ],
	          		'template' => [ 'path' => 'item-templates/item', 'file'=>'display-cover-story-154x200' ] ];

	          		issue_display_posts( $issue_page_content['cover_slot_b']['posts'], $args );
	          	?>
	        	</div>
						<div class="col-6 text-right">
							<?php
							$args = [
	        			'query_vars' => [ [ 'var' =>'direction', 'val' => 'right' ] ],
	        			'template' => [ 'path' => 'item-templates/item', 'file'=>'display-cover-story-154x200' ] ];

							issue_display_posts( $issue_page_content['cover_slot_c']['posts'], $args );
							?>
						</div>
					</div>
				</div>
			</div><!--row-->
		</div><!-- container -->
		<div class="container-fluid ad-container mb-4">
		  <div class="row no-gutters">
		    <div class="col-xl-12 py-2 text-center">
		      <?= ad_728xlandscape_shortcode(); ?>
		    </div>
		  </div>
		</div><!-- container -->
		<div class="container mb-4">
			<div class="row">
					<?php
          	$args = [
          		'query_vars' => [ [ 'var' =>'item_css', 'val' => 'col-12 col-md-6' ] ],
          		'template' => [ 'path' => 'item-templates/item', 'file'=>'540x360-vertical' ] ];

						issue_display_posts( $issue_page_content['top_features']['posts'], $args );
          ?>
			</div>
		</div><!-- container -->
		<div class="container mb-4">
			<div class="row">
				<div class="col-12 col-md-6 col-lg-8">
					<h2 class="sidelines sidebar">News + Notes</h2>
					<div class="row">
					<?php
						$args = [
							'query_vars' => [
								[ 'var' => 'item_css', 'val' => 'col-12 mb-4' ],
								[ 'var' => 'show_byline_only', 'val' => true ]
						],
							'template' => [ 'path' => 'item-templates/item', 'file'=>'320x213' ]
						];

						issue_display_posts( $issue_page_content['news-notes']['posts'], $args );

					 ?>
					</div>
				</div>
				<div class="col-12 col-md-6 col-lg-4">
					<?php echo get_template_part( 'item-templates/item', 'ad-300x250' ); ?>
					<?php if( count( $issue_page_content['editors-note']['posts'] ) > 0 ): ?>
					<div class="row">
						<div class="col">
							<h2 class="sidelines sidebar">Editor's Note</h2>
							<?php
							$args_for_sidebar = [
							'query_vars' => [
									[ 'var' =>'show_category_label', 'val' => false ],
									[ 'var' =>'item_css', 'val' => ' text-center ' ],
									[ 'var' =>'show_thumbnails', 'val' => false ]
								],
							'template' => [ 'path' => 'item-templates/item', 'file'=>'160x107' ]
							];
						issue_display_posts( $issue_page_content['editors-note']['posts'], $args_for_sidebar );
						?>
						</div>
					</div>
					<? endif;?>
					<?php if( count( $issue_page_content['poetry']['posts'] ) > 0 ): ?>
					<div class="row mt-4">
						<div class="col">
							<h2 class="sidelines sidebar">Poetry</h2>
								<?php issue_display_posts( $issue_page_content['poetry']['posts'], $args_for_sidebar ); ?>
						</div>
					</div>
					<? endif;?>
					<?php if( count( $issue_page_content['epilogue']['posts'] ) > 0 ): ?>
					<div class="row mt-4">
						<div class="col">
							<h2 class="sidelines sidebar">Epilogue</h2>
								<?php issue_display_posts( $issue_page_content['epilogue']['posts'], $args_for_sidebar ); ?>
						</div>
					</div>
					<? endif; ?>
					<?php if( count( $issue_page_content['artwork']['posts'] ) > 0 ): ?>
					<div class="row mt-4">
						<div class="col">
							<h2 class="sidelines sidebar">Artwork</h2>
								<?php
								$args_for_sidebar = [
								'query_vars' => [
										[ 'var' =>'show_category_label', 'val' => false ],
										[ 'var' =>'item_css', 'val' => ' text-center ' ],
										[ 'var' =>'show_thumbnails', 'val' => true ]
									],
								'template' => [ 'path' => 'item-templates/item', 'file'=>'160x107-vertical' ]
								];
								issue_display_posts( $issue_page_content['artwork']['posts'], $args_for_sidebar ); ?>
						</div>
					</div>
					<? endif; ?>
				</div>
			</div>
		</div><!--container-->
		<div class="container mb-4">
			<div class="row">
				<div class="col-12">
					<h2 class="sidelines sidebar">Lifestyle</h2>
					<div class="row d-flex justify-content-between">

					<?php
						$args = [
							'before' => '<div class="col-12 col-sm-6 pl-sm-0 col-lg-3 mb-4 mb-md-0">',
							'after' => '</div>',
							'template' => [ 'path' => 'item-templates/item', 'file'=>'255x170' ],
							'query_vars' => [ [ 'var' =>'show_excerpt', 'val' => false ] ]
						];

						issue_display_posts( $issue_page_content['lifestyle']['posts'], $args );

					 ?>
					</div>
				</div>
			</div>
		</div><!--container-->
		<div class="container mb-4">
			<div class="row">
				<div class="col-12">
					<h2 class="sidelines sidebar">Recommendations and Reviews</h2>
					<div class="row d-flex justify-content-between">
						<?php

						//$archive_slug = strtolower( ( new DateTime() )->format('F-Y') );
						$current_calendar = Calendar::get_calendar_page( [ 'issue' => $archive_slug , 'return_array' => true ] );

						if($current_calendar) :
							$cal_obj = map_post_obj_and_slug( $current_calendar, "calendar");
							array_unshift( $issue_page_content['review']['posts'], $cal_obj );
						endif;

						issue_display_posts( $issue_page_content['review']['posts'], $args )

						?>
					</div>
				</div>
			</div>
		</div><!--container-->
		<div class="container mb-4">
			<div class="row">
				<div class="col-12">
						<nav aria-label="...">
						  <ul class="pagination justify-content-center">
						    <li class="page-item">
						      <a class="page-link" href="/issue/<?= $nav_links['previous']['slug'] ?>" tabindex="-1"><?= $nav_links['previous']['name'] ?></a>
						    </li>
						    <li class="page-item active">
						      <a class="page-link" href="/issue/<?= $nav_links['current']['slug'] ?>" tabindex="-1"><?= $nav_links['current']['name'] ?></a>
						    </li>
						    <?php
						    if( count($nav_links['next']) > 0 ): ?>
						    <li class="page-item">
						      <a class="page-link" href="/issue/<?= $nav_links['next']['slug'] ?>" tabindex="-1"><?= $nav_links['next']['name'] ?></a>
						    </li>
						  <?php endif; ?>
						  </ul>
						</nav>
				</div>
			</div>
		</div><!--container-->
	</main><!-- #main -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
