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

$img_src = wp_get_attachment_image_url( $thumbnail_id, 'small' );
$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'small' );
?>
  <div class="row small-item">
    <div class="col-md-5 image">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 10em) 100vw, 160px" alt=""><!-- width: 160px; height: 120px; -->
    </div>
    <div class="col-md-7 pl-0 pr-0">
      <div class="">
        <span class="category-label mb-2 pl-2 pr-2">Category</span>
      </div>
      <h5><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h5>
      <div class="contributors"><?php echo get_contributors() ?></div>
    </div>
  </div>
