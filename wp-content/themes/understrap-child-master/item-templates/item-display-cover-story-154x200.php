<?php
/**
 * Single item 154x200-vertical partial template.
 *
 * @package understrap
 */

?>
<?php

  $issue_publish_date = get_field('issue_publish_date');
  $issue_slug = isset($issue_slug) ? $issue_slug : '';
  $direction = isset($direction) ? $direction : null;
  $issue_name = !empty($issue_slug) ? get_cat_name( get_cached_cat_id_by_slug( $issue_slug ) ) : '';

  $image = get_field('cover_image');
  $size = 'ca-154x200';

  if( $image ) {
    $img_src = wp_get_attachment_image_url( $image['id'], $size );
    $img_srcset = wp_get_attachment_image_srcset( $image['id'], $size );
  }
?>

<a href="/issue/<?= $issue_slug ?>">
  <?php
  if( $direction == 'left' ) :
    echo '<i class="fa fa-arrow-left fa-1" aria-hidden="true"></i>';
  endif;
  ?>
  <?= $issue_name ?>
  <?php
  if( $direction == 'right' ) :
    echo '<i class="fa fa-arrow-right fa-1" aria-hidden="true"></i>';
  endif;
  ?>

</a>
<a href="/issue/<?= $issue_slug ?>">
  <img src="<?php echo esc_url( $img_src ); ?>"
    srcset="<?php echo esc_attr( $img_srcset ); ?>"
    class="img-fluid"
    sizes=""
    style="max-width: 100%;height:auto;"
    alt="">

</a>
