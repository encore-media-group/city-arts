<?php
/**
 * Single post partial template for article enhanced.
 *
 * @package understrap
 */

  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $image_attachment_metadata = wp_get_attachment_metadata($thumbnail_id);

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-2000x1333' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-2000x1333' );

?>
<div class="px-0 container-fluid" id="content" tabindex="-1">
  <div class="row mx-auto item-2000x1333-width">
    <div class="col px-0">
    <img
     src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     style="max-width:2000px;height:auto;"
     class="img-fluid"
     alt="">
   </div>
 </div><!-- end row -->

  <div><?php echo $thumbnail_caption ?></div>
</div>
<main class="site-main" id="main">
  <div class="container" id="content" tabindex="-1">
    <div class="row">
        <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">
          <div class="row">

            <header class="entry-header col text-center">
             <?php
              $cat_label = get_category_label();
              echo sprintf( '<a class="url fn n" href="%1$s"><h2 class="sidelines py-5 w-50 mx-auto">%2$s</h2></a>', $cat_label['url'], $cat_label['name'] );
              ?>

              <?php the_title( '<h1 class="entry-title my-4">', '</h1>' ); ?>
              <div class="entry-meta contributors"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
            </header><!-- .entry-header -->
          </div>
          <div class="row">
            <div class="col-12 col-sm-10 mx-auto  ">
            <?php
              echo set_first_letter_of_post( $post );
            ?>

            </div>
          </div><!-- row -->
          <footer class="entry-footer">
              <?php
              wp_link_pages( array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
                'after'  => '</div>',
              ) );
              ?>
              <br>
          </footer><!-- .entry-footer -->
        </article><!-- #post-## -->
    </div>
  </div>
</main><!-- #main -->
