<?php
/**
 * Single item 730x487 partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-730-487' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-730-487' );

?>
  <div class="col item-730x487-horizontal">
    <div class="row no-gutters">
      <div class="col-12 col-lg">
        <img src="<?php echo esc_url( $img_src ); ?>"
         srcset="<?php echo esc_attr( $img_srcset ); ?>"
         sizes="(max-width: 46em) 100vw, 730px"
        class="img-fluid"
        style="max-width: 100%;height:auto;"
        alt="">
      </div>

      <div class="col item-content-container py-4 pl-4">
        <div class="caption"><?php echo get_post($thumbnail_id)->post_excerpt; ?></div>
        <div>
            <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
        </div>
        <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
        <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
        <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      </div>
    </div>
  </div>
