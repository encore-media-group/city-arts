<?php
/**
 * Single item very small partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

$img_src = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'thumbnail' );
?>
  <div class="row mb-3 small-item">
    <div class="col-auto mr-3">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(min-width: 100px) 100vw, 150px"
       style="min-width:100px;width:100px;height: 100px;"
       alt="">
    </div>
    <div class="col px-0 pt-0">
            <?php get_template_part( 'item-templates/item', 'category-label' ); ?>

      <h6><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h6>
      <div class="contributors"><?php echo get_contributors() ?></div>
    </div>
  </div>
