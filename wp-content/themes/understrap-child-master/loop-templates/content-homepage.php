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

$recent_posts_medium_horiztonal = new WP_Query(array('posts_per_page' => 1, 'offset' => 12, 'meta_query' => array(array('key' => '_thumbnail_id' ))));

$recent_posts_see_it_this_week = new WP_Query(
    array(
      'posts_per_page' => 1,
      'offset' => 0,
      'meta_query' => array(array('key' => '_thumbnail_id' )),
      'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( 'see-it-this-week' ),
            ],
        ],
      )
    );

?>
?>
<div class="wrapper" id="page-wrapper">
  <main class="site-main" id="main">
    <div class="container mb-4 px-0" id="content" tabindex="-1">
      <div class="row pt-4">
        <div class="col-12 col-md-7 col-lg-8 px-0 content-area" id="primary">
          <div class="row">
          <?php
            while( $catquery->have_posts() ) : $catquery->the_post();
              get_template_part( 'item-templates/item', '730x487-vertical' );
            endwhile;
            wp_reset_postdata();
          ?>
          </div>
          <div class="row pt-4 justify-content-between">
          <?php  while( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
            <div class="col-lg-6 px-0 mb-4">
              <?php get_template_part( 'item-templates/item', '160x107' ); ?>
            </div>
          <?php endwhile;
            wp_reset_postdata();
          ?>
          </div>
        </div>
        <div class="col-12 col-md-5 col-lg-4" id="homepage-sidebar">
          <?php if ( is_active_sidebar( 'homepage-right-1' ) ) : ?>
            <div id="homepage-right-1" class="primary-sidebar widget-area" role="complementary">
              <?php dynamic_sidebar( 'homepage-right-1' ); ?>
            </div><!-- #homepage-right-1 -->
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="container-fluid ad-container my-4 px-0">
      <div class="row no-gutters">
        <div class="col-xl-12 py-2 text-center">
          <?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
        </div>
      </div>
    </div>
    <div class="container py-4 px-0">
      <div class="row">
        <div class="col-12 col-sm-6">
          <div class="row">
          <?php while( $recent_posts_medium_small->have_posts() ) : $recent_posts_medium_small->the_post(); ?>
            <div class="col-lg-6">
              <?php set_query_var( 'show_excerpt', true ); ?>
              <?php  get_template_part( 'item-templates/item', '255x170' ); ?>
             </div>
          <?php endwhile;
            wp_reset_postdata();
          ?>
          </div>
          <div class="row">
            <div class="col-auto mx-auto">
              <?php  get_template_part( 'item-templates/item', 'mailchimp' ); ?>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6">
          <?php
          while( $recent_posts_medium->have_posts() ) : $recent_posts_medium->the_post();
            get_template_part( 'item-templates/item', '540x360-vertical' );
          endwhile;
          wp_reset_postdata();
          ?>
        </div>
      </div>
    </div>
    <div class="container py-4 px-0">
      <div class="row d-flex justify-content-between">
        <?php while( $recent_posts_medium_small_bottom->have_posts() ) : $recent_posts_medium_small_bottom->the_post(); ?>
          <?php set_query_var( 'show_excerpt', false ); ?>
          <div class="col-12 col-sm-6 pl-sm-0 col-lg-3 mb-5"><?php  get_template_part( 'item-templates/item', '255x170' ); ?></div>
        <?php endwhile;
          wp_reset_postdata();
        ?>
      </div>
    </div>
    <div class="container py-4 px-0">
        <?php
          while( $recent_posts_medium_horiztonal->have_posts() ) : $recent_posts_medium_horiztonal->the_post();
            get_template_part( 'item-templates/item', '540x360-horizontal' );
          endwhile;
          wp_reset_postdata();
        ?>
    </div>
    <div class="container py-4 px-0">
      <div class="row">
        <div class="col-12 col-md-4 mr-sm-5 pb-sm-4">
           <?php get_template_part( 'item-templates/item', 'current' ); ?>
        </div>
        <div class="col">
          <div class="row see-it-this-week ">
            <?php
              while( $recent_posts_see_it_this_week->have_posts() ) : $recent_posts_see_it_this_week->the_post();
                get_template_part( 'item-templates/item', '730x487-vertical' );
              endwhile;
              wp_reset_postdata();
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="container py-4 px-0">
      <div class="row d-flex justify-content-between">
        <?php while( $recent_posts_medium_small_bottom->have_posts() ) : $recent_posts_medium_small_bottom->the_post(); ?>
          <?php set_query_var( 'show_excerpt', false ); ?>
          <div class="col-12 col-sm-6 pl-sm-0 col-lg-3 mb-5"><?php  get_template_part( 'item-templates/item', '255x170' ); ?></div>
        <?php endwhile;
          wp_reset_postdata();
        ?>
      </div>
    </div>
  </main>
</div>
