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


  $item_css = isset($item_css) ? $item_css : '  ';

  $show_byline = isset($show_byline) ? $show_byline : false;
  $show_byline_only = isset($show_byline_only) ? $show_byline_only : false;
  $show_byline_date = isset($show_byline_date) ? $show_byline_date : false;

?>

<div class="row item-320x213 mb-2 <?= $item_css ?> ">
  <div class="col px-0">
    <div class="row m-3 see-it-this-week">
      <div class="col-12 col-lg mb-3" style="overflow: hidden;max-height: 213px;">
        <div class="item-list-div" style="background: url('<?= esc_url( $img_src )?>') no-repeat center center;background-size:contain;"></div>
      </div>
      <div class="col mt-3">
        <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
        <h4 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
        <?php if( $show_byline ):?>
          <div class="contributors ml-1"><?php echo understrap_posted_on( $show_byline_date ); ?></div>
        <? endif; ?>
        <?php if( $show_byline_only ):?>
          <div class="contributors ml-1"><?php echo understrap_posted_on( false, true ); ?></div>
        <? endif; ?>
        <?php if( $show_byline_date):?>
          <div class="contributors ml-1"><?php echo understrap_posted_on( $show_byline_date ); ?></div>
        <? endif; ?>

        <div class="excerpt py-3"><?php echo $post->post_excerpt; ?></div>
      </div>
    </div>
  </div>
</div>
