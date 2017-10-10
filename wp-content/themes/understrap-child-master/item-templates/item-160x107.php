<?php
/**
 * Single item 160x107 partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );

$img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-160x107' );
$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-160x107' );

$show_category_label = isset($show_category_label) ? $show_category_label : true ;
$show_thumbnails = isset($show_thumbnails) ? $show_thumbnails : true;
$item_css = isset($item_css) ? $item_css : '';

//col-auto pl-4 pl-sm-0
//col pr-2 pl-lg-3
?>
  <div class="row pb-2 pb-xl-0 px-lg-0 <? echo $item_css ?> item-160x107">
    <?php if ( $show_thumbnails ) : ?>
    <div class="col-12 col-sm-auto pl-0 ">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="
       (max-width:577px) 730px,
       160px"
       class="img-fluid"
       style="max-width: 100%;height:auto;"
       alt=""><!-- height: 107px; width: 160px;  -->
    </div>
  <?php endif; ?>
    <div class="col-12 mt-2 mt-sm col-sm pr-0 pr-sm-2 pl-0 pl-lg-3">
      <?php
        if( $show_category_label) :
          get_template_part( 'item-templates/item', 'category-label' );
        endif;
      ?>
      <h4><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"><?php echo get_contributors() ?></div>
    </div>
  </div>
