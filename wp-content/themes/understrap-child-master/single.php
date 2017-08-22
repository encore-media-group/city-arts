<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );

// determine what article view type to display
// the list of article formats is in the city arts plugin
$term_list = wp_get_post_terms($post->ID, 'article_format', array("fields" => "all"));
$article_format = "";
$template = "article-past";

if(is_array($term_list)) {
//	$template = $term_list[0]->slug;
}

?>


<div class="wrapper" id="single-wrapper">
	<div class="container-fluid ad-container">
		<?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
	</div>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'loop-templates/content', $template ); ?>

		<?php // understrap_post_nav(); ?>

	<?php endwhile; // end of the loop. ?>

	<div class="container-fluid ad-container">
		<?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
	</div>

	<!-- RELATED ARTICLES -->
  <div class="container mb-4">
      <div class="row">
        <div class="col-12">
          <h3 class="sidelines sidebar py-4">RELATED ARTICLES</h3>
          <div class="row px-4">
          <?php
          $genre_cat = get_category_by_slug('genre');
		      $genre_cat_id = $genre_cat->term_id;

		      $categories = get_the_category($post->ID);
		      $category_ids = array();
		      if ( $categories ) {
		          foreach ( $categories as $individual_category ) {
		            if( ($individual_category->term_id) == $genre_cat_id) {
		              $category_ids[] = $individual_category->term_id;
		            }
		          }
		        }

		      $recent_posts_medium_small = new WP_Query(array(
		        'posts_per_page' => 6,
		        'offset' => 0,
		        'category__in' => $category_ids,
		        'post__not_in' => array($post->ID),
		        'ignore_sticky_posts' => 1,
		        'meta_query' => array(array('key' => '_thumbnail_id' ))
		        )
		      );
          $count = 0;

          while( $recent_posts_medium_small->have_posts() ) : $recent_posts_medium_small->the_post();
          	$count++;
          ?>
            <div class="col-lg-4 mb-2">
              <?php get_template_part( 'item-templates/item', 'small' ); ?>
            </div>
            <?php if($count == 3) { echo '<div class="w-100"></div>'; } ?>
          <?php endwhile;
            wp_reset_postdata();
            $count = 0;
          ?>
          </div>
        </div>
      </div>
	</div><!-- RELATED ARTICLES END -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
