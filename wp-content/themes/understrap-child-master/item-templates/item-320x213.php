<?php
/**
 * Single item 320x213 partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-320x213' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-320x213' );
?>


  <div class="col-12 col-sm item-320x213">
    <div class="row">
      <div class="col-12 col-lg">
        <img src="<?php echo esc_url( $img_src ); ?>"
         srcset="<?php echo esc_attr( $img_srcset ); ?>"
         sizes="(max-width: 46em) 100vw, 320px"
         class="img-fluid"
         style="max-width: 100%;height:auto;"
         alt="">
      </div>
      <div class="col mt-2 mt-lg-0">
        <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
        <h4 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
        <div class="excerpt py-3"><?php echo $post->post_excerpt; ?></div>
        <div class="contributors"><?php echo get_contributors(); ?></div>
      </div>
    </div>
  </div>
