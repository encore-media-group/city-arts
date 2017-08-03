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
      <div class="col-md-8 content-area" id="primary">
          <main class="site-main" id="main">
            <div class="main-article" style="background-color: white">
              <?php
                $cat_idObj = get_term_by( 'slug', 'homepage-feature', 'category' );

                $catquery = new WP_Query( 'cat=' . ($cat_idObj->term_id)  . '&posts_per_page=1' );

                while( $catquery->have_posts() ) : $catquery->the_post();

                 get_template_part( 'item-templates/item', 'large' );

              ?>



              <?php
                endwhile;
                wp_reset_postdata();
              ?>
            </div>
          </main>
      </div>
      <div class="col-md-4" id="homepage-sidebar">
      </div>
    </div>
  </div>
</div>
<?php

the_content();
