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

  $image_attachment_metadata = wp_get_attachment_metadata($thumbnail_id);
  $img_width = isset($image_attachment_metadata['width']) ? $image_attachment_metadata['width'] : 0;
  $img_height = isset($image_attachment_metadata['height']) ? $image_attachment_metadata['height'] : 0;

  $img_orientation = 'landscape';

if( $img_width < $img_height ) {
  $img_orientation = 'portrait';
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-320x426' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-320x426' );
} else {
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-320x213' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-320x213' );
}


  $item_css = isset($item_css) ? $item_css : ' col-12 col-sm ';

  $show_byline = isset($show_byline) ? $show_byline : false;
  $show_byline_date = isset($show_byline_date) ? $show_byline_date : false;

?>

  <div class="<? echo $item_css ?> item-320x213">
    <div class="row">
      <div class="col-12 col-lg" style="overflow: hidden;max-height: 213px;">
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
        <?php if( $show_byline ):?>
          <div class="contributors ml-1"><?php echo understrap_posted_on( $show_byline_date ); ?></div>
        <? endif; ?>
        <?php if( $show_byline_date):?>
          <div class="contributors ml-1"><?php echo understrap_posted_on( $show_byline_date ); ?></div>
        <? endif; ?>

        <div class="excerpt py-3"><?php echo $post->post_excerpt; ?></div>
      </div>
    </div>
  </div>
