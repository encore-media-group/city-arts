<?php
/**
 * Homepage partial template.
 *
 * @package understrap
 */
?>

<div class="wrapper" id="page-wrapper">
  <div class="container" id="content" tabindex="-1">
    <div class="row">
      <div class="col-sm-8 content-area" id="primary">
          <main class="site-main" id="main">
            <div class="main-article">
              <?php
                $cat_idObj = get_term_by( 'slug', 'homepage-feature', 'category' );
                $catquery = new WP_Query( 'cat=' . ($cat_idObj->term_id)  . '&posts_per_page=1' );

                while( $catquery->have_posts() ) : $catquery->the_post();
                  get_template_part( 'item-templates/item', 'large' );
                endwhile;
                wp_reset_postdata();
              ?>
              <div class="row no-gutters">
              <?php
                $recent_posts = new WP_Query(array(
                  'posts_per_page' => 2,
                  'meta_query' => array(array('key' => '_thumbnail_id' ))
                  ));

                while( $recent_posts->have_posts() ) : $recent_posts->the_post();
              ?>
                  <div class="col-md-6 pb-2">
                    <?php get_template_part( 'item-templates/item', 'small' ); ?>
                  </div>
                <?php
                endwhile;
                wp_reset_postdata();
                ?>
              </div>
            </div>
          </main>
      </div>
      <div class="col-sm-4" id="homepage-sidebar"></div>
    </div>
  </div>
  <div class="container mt-4">
    <div class="row">
      <div class="col-xl-12 text-center">
        <?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
      </div>
    </div>
  </div>
  <div class="container mt-4">
    <div class="row">
      <div class="col-sm-6">
        <?php get_template_part( 'item-templates/item', 'current' ); ?>
      </div>
      <div class="col-sm-6">
      <?php
        $recent_posts = new WP_Query(array(
          'posts_per_page' => 1,
          'offset' => 3,
          'meta_query' => array(array('key' => '_thumbnail_id' ))
          ));

        while( $recent_posts->have_posts() ) : $recent_posts->the_post();
          get_template_part( 'item-templates/item', 'medium' );
        endwhile;
        wp_reset_postdata();
        ?>
      </div>
    </div>
  </div>
</div>
<?php

the_content();
