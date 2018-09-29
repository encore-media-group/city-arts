<?php
/**
 * Single item 255x170 template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-255x170' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-255x170' );


  $image_attachment_metadata = wp_get_attachment_metadata($thumbnail_id);
  $img_width = isset($image_attachment_metadata['width']) ? $image_attachment_metadata['width'] : 0;
  $img_height = isset($image_attachment_metadata['height']) ? $image_attachment_metadata['height'] : 0;

  $img_orientation = 'landscape';

if( $img_width < $img_height ) {
  $img_orientation = 'portrait';
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-255x240' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-255x340' );
} else {
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-255x170' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-255x170' );
}

  $show_excerpt = isset( $show_excerpt ) ? $show_excerpt : false ;

?>
  <!-- item 255x170 -->
  <div class="row item-255x170 text-center">
    <div class="col-12" style="overflow: hidden;max-height: 170px;">
      <a href="<?php the_permalink() ?>">
        <img
       src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 2000px) 100vw, 255px"
       style="max-width: 100%;height:auto;"
       class="img-fluid"
       alt="">
      </a>
    </div>
    <div class="col-12 py-4">
      <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
      <h3 class="mb-0 px-2"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
     <?php if( $show_excerpt ) : ?>
        <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      <?php endif; ?>
    </div>
  </div>
