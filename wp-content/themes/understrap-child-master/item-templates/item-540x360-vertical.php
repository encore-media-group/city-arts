<?php
/**
 * Single item 540x360-vertical partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-540x360' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-540x360' );

  $item_css = isset($item_css) ? $item_css : ' col ';

?>
<div class="<?php echo $item_css ?> item-540x360-vertical">
  <div class="row pb-md pb-4">
    <div class="col">
      <div class="row">
        <div class="col-12">
          <a href="<?php the_permalink() ?>">
            <img src="<?php echo esc_url( $img_src ); ?>"
           srcset="<?php echo esc_attr( $img_srcset ); ?>"
           sizes="
           (max-width:577px) 730px,
           (max-width:768px) 1024px,
           (min-width:769px) 540px,
           540px"
          class="img-fluid"
          style="max-width: 100%;height:auto;"
          alt="">
          </a>
          <div class="inner-item-wrapper p-4">
            <div><?php get_template_part( 'item-templates/item', 'category-label' ); ?></div>
            <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
            <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
            <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
