<?php
/**
 * Single See It This Week partial template.
 *
 * @package understrap
 */

$recent_posts_see_it_this_week = new WP_Query(
    array(
      'posts_per_page' => 1,
      'offset' => 0,
      'meta_query' => array(array('key' => '_thumbnail_id' )),
      'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( 'see-it-this-week' ),
            ],
        ],
      )
    );

?>
<div class="row see-it-this-week-container p-0">
  <div class="col px-0">
<?php

  while( $recent_posts_see_it_this_week->have_posts() ) : $recent_posts_see_it_this_week->the_post();
    $thumbnail_id = get_post_thumbnail_id( $post->ID );
    $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

    $img_src = wp_get_attachment_image_url( $thumbnail_id, 'full' );
    $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'full' );

?>
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 50em) 100vw, 768px"
     style="width:100%;"
     alt="">
    <h4 class="sidelines mx-4">SEE IT THIS WEEK</h4>
    <div class="caption"><?php echo $thumbnail_caption ?></div>
    <div class="excerpt py-3"><?php echo $post->post_excerpt; ?></div>

  <?php
    endwhile;
    wp_reset_postdata();
  ?>
  </div>
</div>
