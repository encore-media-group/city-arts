<?php
/**
 * Single item display-wrtier-profile-image partial template.
 *
 * @package understrap
 */

?>
<?php


  $size = 'thumbnail';

  if( $image ) :
    $img_src = wp_get_attachment_image_url( $image['id'], $size );
    $img_srcset = wp_get_attachment_image_srcset( $image['id'], $size );
?>
  <img src="<?php echo esc_url( $img_src ); ?>"
    srcset="<?php echo esc_attr( $img_srcset ); ?>"
    class="rounded-circle"
    style="max-width: 150px;height:150px;"
    alt="">

<?php endif; ?>
