<?php
/**
 * Single Album of the Month partial template.
 *
 * @package understrap
 */

$recent_album_of_the_month = new WP_Query(
    array(
      'posts_per_page' => 1,
      'meta_query' => array(array('key' => '_thumbnail_id' )),
      'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( 'album-of-the-month' ),
            ],
        ],
      )
    );
?>
<div class="row album-of-the-month-container mx-auto p-4">
  <div class="col text-center px-0">
  <h4><strong>ALBUM OF THE MONTH</strong></h4>
<?php
  while( $recent_album_of_the_month->have_posts() ) : $recent_album_of_the_month->the_post();
    $thumbnail_id = get_post_thumbnail_id( $post->ID );
    $img_src = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
    $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'medium' );
?>
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 46em) 100vw, 600px"
     style="max-height:175px;max-width:175px;min-width: 175px;"
     alt="">
  <?php
    endwhile;
    wp_reset_postdata();
  ?>
  </div>
</div>
