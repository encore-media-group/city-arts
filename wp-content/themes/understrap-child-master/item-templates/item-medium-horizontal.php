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

  //check if this is "from the magazine"..
  $flag_from_magazine = true;
?>

<div class="row item-medium-horizontal no-gutters">
  <div class="col-md-6">
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 45em) 100vw, 540px"
     style="width:100%;"
     alt="">
  </div>
  <div class="col-md-6 p-4">
    <div class="caption"><?php echo $thumbnail_caption ?></div>
    <?php if( $flag_from_magazine ):?>
      <div>FROM THE MAGAZINE</div>
    <?php endif; ?>
    <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
    <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
    <div class="contributors"> <?php echo understrap_posted_on(); ?> </div>
    <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
    <?php if( $flag_from_magazine ):?>
      <div>MORE FROM THIS ISSUE -></div>
    <?php endif; ?>
  </div>
</div>
