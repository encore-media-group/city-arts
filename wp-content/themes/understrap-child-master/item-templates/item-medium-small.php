<?php
/**
 * Single item medium partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'medium' );
?>
  <!-- medium small -->
  <div class="row item-medium-small">
    <div class="col-12 text-center">
      <img
       src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 255px) 100vw, 255px"
       style="height: 191px;"
       class="img-fluid"
       alt="">
    </div>
    <div class="col-12 text-center">
      <span class="category-label my-2 px-2">Category</span>
      <h4 class="mb-0 px-4 mx-4"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"> <?php echo get_contributors(); ?></div>
    </div>
  </div>
