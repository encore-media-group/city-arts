<?php
/**
 * Single item 160x107 partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

$img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-730-487' ); //we're using this big size because it has a flex height
$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-730-487' );

$show_category_label = isset($show_category_label) ? $show_category_label : true ;
$show_thumbnails = isset($show_thumbnails) ? $show_thumbnails : true;
$item_css = isset($item_css) ? $item_css : '';

?>
  <div class="row mb-4 px-lg-0 <?php echo $item_css ?> item-160x107">

    <div class="col-12">
      <?php
        if( $show_category_label) :
          get_template_part( 'item-templates/item', 'category-label' );
        endif;
      ?>
      <h4><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"><?php echo get_contributors() ?></div>
    </div>

    <?php if ( $show_thumbnails ) : ?>
    <div class="col-12 mt-4">
      <a href="<?php the_permalink() ?>" rel="bookmark">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="(max-width: 10em) 100vw, 600px"
       class="img-fluid"
       style="max-width: 100%;height:auto;"
       alt="">
     </a>
    </div>
  <?php endif; ?>
  </div>
