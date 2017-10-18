<?php
/**
 * Template Name: Contributor List Page Template
 *
 *
 * @package understrap
 */

get_header();
$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
$taxonomy_name = 'contributor';
$taxonomies = get_terms( array( 'taxonomy' => $taxonomy_name, 'hide_empty' => false, ) );

?>
<div class="wrapper" id="archive-wrapper">
  <main class="site-main" id="main">
    <div class="container" id="content" tabindex="-1"><!-- top container -->
        <div class="row pt-4">
           <div class="col">
              <header class="row page-header  w-md-75 mx-auto">
                <h2 class="col page-title pb-4 sidelines">Contributors </h2>
              </header><!-- .page-header -->
             <div class="row">
               <?php
                $count = 1;
                if  ($taxonomies) :
                  foreach ($taxonomies  as $taxonomy ) :
                      $term_id = $taxonomy->term_id;
                      $tax_name = $taxonomy->name;
                      $tax_slug = $taxonomy->slug;
                      $taxonomy_term = $taxonomy_name . '_' . $term_id;
                      $image = get_field('writer_image', $taxonomy_term);


                      echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3 my-2 text-center border-light">';
                      echo sprintf('<a href="%1$s">',$tax_slug );
                        if ( $image ) :
                          set_query_var ('image', $image );
                          get_template_part( 'item-templates/item', 'display-writer-profile-image' );
                        endif;
                        echo sprintf('<h2>%1$s</h2>', $tax_name );
                      echo '</a></div>';

                if ($count == 8 ) : ?>
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
            endforeach;
          endif;
          ?>
          </div><!-- row -->
        </div><!-- col -->
      </div><!-- row -->
    </div><!-- container -->
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-auto">
          <!-- The pagination component -->
          <?php understrap_pagination( ); ?>
        </div>
      </div>
    </div>
  </main>
</div><!-- Wrapper end -->

<?php get_footer(); ?>
