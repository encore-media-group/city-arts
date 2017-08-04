<?php
/**
 * Single item large partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

$img_src = wp_get_attachment_image_url( $thumbnail_id, 'small' );
$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'small' );
?>
  <div class="row small-item">
    <div class="col-sm-6 image">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 10em) 100vw, 160px" alt=""><!-- width: 160px; height: 120px; -->
    </div>
    <div class="col-sm-6">
      <div class="category-label"><span>Category</span></div>
      <h4><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"><?php echo get_contributors() ?></div>
    </div>
  </div>
