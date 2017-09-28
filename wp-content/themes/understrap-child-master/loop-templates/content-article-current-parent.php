<?php
/**
 * The parent template for displaying current article posts.
 *
 * @package understrap
 */


?>

<div class="wrapper" id="single-wrapper">
	<div class="container-fluid ad-container">
		<?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
	</div>
	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'loop-templates/content', 'article-current' ); ?>

		<?php // understrap_post_nav(); ?>

	<?php endwhile; // end of the loop.
  ?>

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
              <?php get_template_part( 'item-templates/item', '160x107' ); ?>
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
