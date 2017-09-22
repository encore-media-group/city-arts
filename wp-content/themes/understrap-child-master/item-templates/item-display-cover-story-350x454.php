<?php
/**
 * Single item 350x454-vertical partial template.
 *
 * @package understrap
 */

?>
<?php

  $cover_image_src =  get_field('cover_image')['url'];

  echo '<img src="' . esc_url(  $cover_image_src ) . '" class="img-fluid" style="max-width: 100%;" alt="">';
