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

?>

  <div class="col pl-0 pr-2 item-320x213  item-320x213-ordered">
    <div class="row">
      <div class="col-2 my-auto px-2 text-center"><h3><?echo $row_num ?>.</h3></div>
      <div class="col px-0">
        <img src="<?php echo esc_url( $img_src ); ?>"
         srcset="<?php echo esc_attr( $img_srcset ); ?>"
         sizes="(max-width: 46em) 100vw, 320px"
         class="img-fluid"
         style="max-width: 100%;height:auto;"
         alt="">
      </div>
    </div>
    <div class="row">
      <div class="col offset-2 px-0 py-3">
        <h3 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
        <div class="contributors"><?php echo get_contributors(); ?></div>
      </div>
    </div>
  </div>
