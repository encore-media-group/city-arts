<?php
/**
 * Single item small partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

$img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-160x107' );
$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-160x107' );
?>
  <div class="row small-item">
    <div class="col-auto">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 10em) 100vw, 160px"
       class="img-fluid"
       style="max-width: 100%;height:auto;"
       alt=""><!-- height: 107px; width: 160px;  -->
    </div>
    <div class="col">
      <span class="category-label align-top  mb-2 pl-2 pr-2">Category</span>
      <h5><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h5>
      <div class="contributors"><?php echo get_contributors() ?></div>
    </div>
  </div>
