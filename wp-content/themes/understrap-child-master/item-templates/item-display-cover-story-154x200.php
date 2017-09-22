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
  $issue_slug= isset($issue_slug) ? $issue_slug : '';

?>

<a href="/issue/<?= $issue_slug ?>">
<?= $issue_slug ?></a>
<a href="/issue/<?= $issue_slug ?>"><img src="<?= esc_url(  $cover_image['url'] ) ?>" class="img-fluid" style="max-width:154px;max-height:200px" alt=""></a>
