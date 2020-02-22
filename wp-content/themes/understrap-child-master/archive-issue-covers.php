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

$page_title = 'Archives';


$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;


$the_query = new WP_Query(array(
    'posts_per_page' => 16,
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
  <main class="site-main" id="main">
    <div class="container" id="content" tabindex="-1"><!-- top container -->
  		  <div class="row pt-4">
  			   <div class="col">
      				<?php if ( have_posts() ) : ?>
      					<header class="row page-header  w-md-75 mx-auto">
      						<h2 class="col page-title pb-4 sidelines"> <?= $page_title; ?> </h2>
      					</header><!-- .page-header -->
      				<?php endif; ?>
  					 <div class="row">
  					   <?php
    						$count = 1;
                while( $the_query->have_posts() ) : $the_query->the_post();

    							echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">';
    							echo insert_cover_story_shortcode( [ 'show_article_list' => false ] );
    							echo '</div>';

  							if ($count == 8 ): ?>
              </div><!-- end row -->
            </div><!--col-->
          </div><!--row-->
        </div><!--top container-->
    <div class="container-fluid ad-container">
        <?php echo ad_728xlandscape_shortcode(); ?>
    </div>
    <div class="container">
      <div class="row pt-4">
        <div class="col">
          <div class="row">
            <?php
							endif;
							$count++;
						endwhile;
						wp_reset_postdata();
					?>
					</div><!-- row -->
				</div><!-- col -->
			</div><!-- row -->
		</div><!-- container -->
    <div class="container">
    	<div class="row justify-content-center">
    		<div class="col-auto">
      		<!-- The pagination component -->
      		<?php understrap_pagination( $the_query ); ?>
    		</div>
    	</div>
    </div>
  </main>
</div><!-- Wrapper end -->

<?php get_footer(); ?>
