<?php
/**
 * Single item 350x454-vertical partial template.
 *
 * @package understrap
 */

?>
<?php

  $image = get_field('cover_image');
  $size = 'ca-350x454';

  if( $image ) {
    $img_src = wp_get_attachment_image_url( $image['id'], $size );
    $img_srcset = wp_get_attachment_image_srcset( $image['id'], $size );
  }
?>
  <img src="<?php echo esc_url( $img_src ); ?>"
    srcset="<?php echo esc_attr( $img_srcset ); ?>"
    class="img-fluid"
    sizes=""
    style="max-width: 100%;height:auto;"
    alt="">
