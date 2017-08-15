<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */


  $container   = get_theme_mod( 'understrap_container_type' );

  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'full' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'full' );

?>
<div class="px-0 <?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

    <div class="row">

      <main class="site-main" id="main">

        <article <?php post_class('col px-0'); ?> id="post-<?php the_ID(); ?>">

          <div class="row">

            <header class="entry-header col-12 px-0">
              <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
              <div class="entry-meta"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
            </header><!-- .entry-header -->

          </div>

          <div class="row">
            <div class="col-12 col-md-7 col-lg-8">
              <img
               src="<?php echo esc_url( $img_src ); ?>"
               srcset="<?php echo esc_attr( $img_srcset ); ?>"
               sizes="(max-width: 2000px) 100vw, 730px"
               style=""
               class="img-fluid"
               alt="">

                    <div><?php echo $thumbnail_caption ?></div>
              <?php the_content(); ?>
            </div>
            <div class="col-12 col-md-5 col-lg-4 ">
              <div class="entry-content">

                      <?php if ( is_active_sidebar( 'article-right-1' ) ) : ?>
                        <div id="article-right-sidebar" class="primary-sidebar widget-area" role="complementary">
                          <?php dynamic_sidebar( 'article-right-1' ); ?>
                        </div><!-- #primary-sidebar -->
                      <?php endif; ?>

                  <div class="attached-images">
                    <ul>
                    <?php
                    $images = get_attached_media('image', $post->ID);

                    foreach($images as $image) {
                        echo var_dump($image);
                        ?>
                        <li><img src="<?php echo wp_get_attachment_image_src($image->ID,'medium')[0]; ?>" /></li>
                    <?php } ?>
                    </ul>
                  </div>
              <?php
              wp_link_pages( array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
                'after'  => '</div>',
              ) );
              ?>
              </div><!-- .entry-content -->
            </div><!--end col-lg-6-->
          </div><!-- row -->
          <footer class="entry-footer">

          <?php understrap_entry_footer(); ?>

          </footer><!-- .entry-footer -->
        </article><!-- #post-## -->
      </main><!-- #main -->
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
      ?>
      <div class="row">
        <div class="col-12">
          <h3 class="sidelines">RELATED ARTICLES</h3>
          <div class="row no-gutters">
          <?php  while( $recent_posts_medium_small->have_posts() ) : $recent_posts_medium_small->the_post(); ?>
            <div class="col-md-4 pb-2">
              <?php get_template_part( 'item-templates/item', 'small' ); ?>
            </div>
          <?php endwhile;
            wp_reset_postdata();
          ?>
          </div>
        </div>
      </div>







    </div><!-- #primary -->
  </div><!-- .row -->
</div><!-- Container end -->
