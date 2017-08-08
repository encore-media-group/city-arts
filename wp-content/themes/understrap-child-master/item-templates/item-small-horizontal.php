<?php
/**
 * Single item small horizontal partial template.
 *
 * @package understrap
 */

?>
<?php

  $recent_attractive_singles = new WP_Query(
    array(
      'posts_per_page' => 1,
      'meta_query' => array(array('key' => '_thumbnail_id' )),
      'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( 'singles' ),
            ],
        ],
      )
    );

while( $recent_attractive_singles->have_posts() ) : $recent_attractive_singles->the_post();
  $thumbnail_id = get_post_thumbnail_id( $post->ID );

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'medium' );
?>

<div class="row item-small-horizontal">
  <div class="col-4 p-4">
    <img src="<?php echo esc_url( $img_src ); ?>"
     srcset="<?php echo esc_attr( $img_srcset ); ?>"
     sizes="(max-width: 20em) 100vw, 300px"
     style="max-height:405px;"
     alt="">
  </div>
  <div class="col-8 p-4">
    <div>ATTRACTIVE SINGLES</div>
    <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
    <div class="contributors"> <?php echo get_contributors(); ?> </div>
  </div>
</div>
  <?php
    endwhile;
    wp_reset_postdata();
  ?>
