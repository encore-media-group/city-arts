<?php
/**
 * Single item medium partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'medium-540x405' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'medium-540x405' );
?>

  <div class="item-medium">
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 540px) 100vw, 540px"
     style="max-height:405px;"
     alt="">
    <div class="item-content-container p-4">
      <div class="caption"><?php echo $thumbnail_caption ?></div>
      <div>
        <span class="category-label mb-2 pl-2 pr-2">Category</span>
      </div>
      <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
      <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
    </div>
  </div>
