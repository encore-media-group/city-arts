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

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-255x170' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-255x170' );
?>
  <!-- medium small -->
  <div class="row item-medium-small pb-4">
    <div class="col-12 text-center">
      <img
       src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 2000px) 100vw, 255px"
       style="height: 170px;"
       class="img-fluid"
       alt="">
    </div>
    <div class="col-12 text-center pt-4">
      <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
      <h4 class="mb-0 px-2"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
    </div>
  </div>
