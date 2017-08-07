<?php
/**
 * Single See It This Week partial template.
 *
 * @package understrap
 */

?>
<div class="row see-it-this-week-container p-4">
  <div class="col px-0">
    <h2><strong>SEE IT THIS WEEK</strong></h3>
    <span>Looking for something to do? We've got you Covered.</span> <!--this could go into a widget -->

<?php
  $recent_posts_see_it_this_week = new WP_Query(array('posts_per_page' => 1, 'offset' => 11, 'meta_query' => array(array('key' => '_thumbnail_id' ))));

  while( $recent_posts_see_it_this_week->have_posts() ) : $recent_posts_see_it_this_week->the_post();
    $thumbnail_id = get_post_thumbnail_id( $post->ID );
    $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

    $img_src = wp_get_attachment_image_url( $thumbnail_id, 'medium-540x405' );
    $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'medium-540x405' );

?>
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 540px) 100vw, 540px"
     style="max-height:405px;"
     alt="">
    <div class="caption"><?php echo $thumbnail_caption ?></div>
    <div class="excerpt py-3"><?php echo $post->post_excerpt; ?></div>
    <div class="contributors"> <?php echo get_contributors(); ?> </div>
  <?php
    endwhile;
    wp_reset_postdata();
  ?>
  </div>
</div>
