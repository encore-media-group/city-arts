<?php
/**
 * Homepage partial template.
 *
 * @package understrap
 */

$cat_idObj = get_term_by( 'slug', 'homepage-feature', 'category' );
$catquery = new WP_Query( 'cat=' . ($cat_idObj->term_id)  . '&posts_per_page=1' );

$recent_posts = new WP_Query(array('posts_per_page' => 2,'meta_query' => array(array('key' => '_thumbnail_id' ))));

$recent_posts_medium = new WP_Query(array('posts_per_page' => 1, 'offset' => 3, 'meta_query' => array(array('key' => '_thumbnail_id' ))));

$recent_posts_medium_small = new WP_Query(array('posts_per_page' => 2, 'offset' => 4, 'meta_query' => array(array('key' => '_thumbnail_id' ))));

$recent_posts_medium_small_bottom = new WP_Query(array('posts_per_page' => 4, 'offset' => 6, 'meta_query' => array(array('key' => '_thumbnail_id' ))));

$recent_posts_medium_horiztonal = new WP_Query(array('posts_per_page' => 1, 'offset' => 10, 'meta_query' => array(array('key' => '_thumbnail_id' ))));

?>
<div class="wrapper" id="page-wrapper">
  <main class="site-main" id="main">
    <div class="container" id="content" tabindex="-1">
      <div class="row no-gutters">
        <div class="col-sm-8 content-area" id="primary">
            <div class="main-article">
              <?php
                while( $catquery->have_posts() ) : $catquery->the_post();
                  get_template_part( 'item-templates/item', 'large' );
                endwhile;
                wp_reset_postdata();
              ?>
              <div class="row no-gutters">
              <?php  while( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
                <div class="col-md-6 pb-2">
                  <?php get_template_part( 'item-templates/item', 'small' ); ?>
                </div>
              <?php endwhile;
                wp_reset_postdata();
              ?>
              </div>
            </div>
      </div>
      <div class="col-sm-4" id="homepage-sidebar">
      <?php if ( is_active_sidebar( 'homepage-right-1' ) ) : ?>
        <div id="homepage-right-1" class="primary-sidebar widget-area" role="complementary">
          <?php dynamic_sidebar( 'homepage-right-1' ); ?>
        </div><!-- #homepage-right-1 -->
      <?php endif; ?>
      </div>
    </div>
    <div class="container ad-container mt-4 px-0">
    <div class="row no-gutters">
      <div class="col-xl-12 py-2 text-center">
        <?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
      </div>
    </div>
    </div>
    <!-- section 3 -->
    <div class="container mt-4 px-0">
      <div class="row">
        <div class="col-12 col-sm-6">
          <?php get_template_part( 'item-templates/item', 'current' ); ?>
          <div class="row mt-4">
          <?php while( $recent_posts_medium_small->have_posts() ) : $recent_posts_medium_small->the_post(); ?>
            <div class="col-lg-6 mb-4">
              <?php  get_template_part( 'item-templates/item', 'medium-small' ); ?>
             </div>
          <?php endwhile;
            wp_reset_postdata();
          ?>
          </div>
        </div>
        <div class="col-12 col-sm-6">
          <?php
          while( $recent_posts_medium->have_posts() ) : $recent_posts_medium->the_post();
            get_template_part( 'item-templates/item', 'medium' );
          endwhile;
          wp_reset_postdata();
          ?>
        </div>
      </div>
    </div>
    <!--row of medium small -->
    <div class="container mt-4 px-0">
      <div class="row px-3">
        <?php while( $recent_posts_medium_small_bottom->have_posts() ) : $recent_posts_medium_small_bottom->the_post(); ?>
          <div class="col-12 col-sm-6 pl-sm-0 col-lg-3 mb-5"><?php  get_template_part( 'item-templates/item', 'medium-small' ); ?></div>
        <?php endwhile;
          wp_reset_postdata();
        ?>
      </div>
    </div>
    <!--from the magazine -->
    <div class="container mt-4 px-0">
        <?php
          while( $recent_posts_medium_horiztonal->have_posts() ) : $recent_posts_medium_horiztonal->the_post();
            get_template_part( 'item-templates/item', 'medium-horizontal' );
          endwhile;
          wp_reset_postdata();
        ?>
    </div>
    <!-- bottom row -->
    <div class="container mt-4 px-0">
      <div class="row">
        <div class="col"><h3>SEE IT THIS WEEK</h3></div>
        <div class="col">
          <div class="row">
            <div class="col">Album of the Month"</div>
            <div class="col">300x250ad</div>
          </div>
          <div class="row">Attractive Singles</div>
        </div>
      </div>
    </div>
  </main>
</div>
