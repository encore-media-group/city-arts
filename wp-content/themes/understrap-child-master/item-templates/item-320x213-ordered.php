<?php
/**
 * Single item 320x213 ordered partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-320x213' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-320x213' );

  $row_num = isset($row_num) ? $row_num : 0;
  $show_numbers = isset($show_numbers) ? $show_numbers : false;
  $show_thumbnails = isset($show_thumbnails) ? $show_thumbnails : false;
  $center_text = isset($center_text) ? $center_text : false;

  $col_0_class = '';
  $col_1_class = '';
  $col_2_class = ' px-0 ';

  if( $show_thumbnails ) :
    $col_0_class = 'pl-0 pr-2 ';
    $col_1_class = ' my-auto ';
    $col_2_class .= ' offset-2 py-3 ';
  endif;

  if( !$show_numbers ): //i.e. hide numbers..
    $col_0_class = '';
    $col_1_class .= ' d-none';
    $col_2_class = '';
  endif;

  if( $center_text ):
    $col_2_class .= ' text-center';
  endif;
?>

  <div class="col <?= $col_0_class ?> item-320x213  item-320x213-ordered">
    <div class="row py-2">

      <div class="col-2 px-2 text-center <?= $col_1_class ?>"><h3><?echo $row_num ?>.</h3></div>
      <?if ( $show_thumbnails ) : ?>
      <div class="col <?= $col_0_class ?> ">
        <img src="<?php echo esc_url( $img_src ); ?>"
         srcset="<?php echo esc_attr( $img_srcset ); ?>"
         sizes="(max-width: 46em) 100vw, 320px"
         class="img-fluid"
         style="max-width: 100%;height:auto;"
         alt="">
      </div>
    </div>
    <div class="row">
    <? endif; ?>
      <div class="col <?php echo $col_2_class ?>">
        <h3 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
        <div class="contributors"><?php echo get_contributors(); ?></div>
      </div>
    </div>
  </div>
