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

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'full' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'full' );
?>

  <div class="item-large mb-4">large
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 46em) 100vw, 440px"
     style="width:100%;"
     alt="">
    <div class="item-content-container p-4">
      <div class="caption"><?php echo $thumbnail_caption ?></div>
      <div>
        <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
      </div>
      <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
      <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
    </div>
  </div>
