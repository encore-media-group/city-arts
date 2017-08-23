<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */


  $container   = get_theme_mod( 'understrap_container_type' );

  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-730-487' );

  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-730-487' );

  $image_attachment_metadata = wp_get_attachment_metadata($thumbnail_id);
  $img_width = isset($image_attachment_metadata['width']) ? $image_attachment_metadata['width'] : 0;
  $img_height = isset($image_attachment_metadata['height']) ? $image_attachment_metadata['height'] : 0;

  $img_orientation = 'landscape';

if( $img_width < $img_height ) { $img_orientation = 'portrait'; }

echo $img_orientation;

?>
<div class="px-0 <?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

    <div class="row">

      <main class="site-main" id="main">

        <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">

          <div class="row">

            <header class="entry-header col-9">
              <?php get_template_part( 'item-templates/item', 'category-label' ); ?>

              <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
              <div class="entry-meta contributors"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
            </header><!-- .entry-header -->

          </div>

          <div class="row">
            <div class="col-12 col-md-7 col-lg-8 ">
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
            <div class="col-12 col-md-5 col-lg-4">
              <div class="entry-content">

                <?php if ( is_active_sidebar( 'article-right-1' ) ) : ?>
                  <div id="article-right-sidebar" class="primary-sidebar widget-area" role="complementary">
                    <?php dynamic_sidebar( 'article-right-1' ); ?>
                  </div><!-- #primary-sidebar -->
                <?php endif; ?>

<!--
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
                  </div>-->
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






    </div><!-- #primary -->
  </div><!-- .row -->
</div><!-- Container end -->
