<?php
/**
 * Single item 540x360-vertical partial template.
 *
 * @package understrap
 */

?>
<?php

  $cover_image =  get_field('cover_image');
  $issue_publish_date = get_field('issue_publish_date');
  $issue_slug = isset($issue_slug) ? $issue_slug : '';
  $direction = isset($direction) ? $direction : null;
  $issue_name = !empty($issue_slug) ? get_cat_name( get_cached_cat_id_by_slug( $issue_slug ) ) : '';
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
<a href="/issue/<?= $issue_slug ?>"><img src="<?= esc_url(  $cover_image['url'] ) ?>" class="img-fluid" style="max-width:154px;max-height:200px" alt=""></a>
