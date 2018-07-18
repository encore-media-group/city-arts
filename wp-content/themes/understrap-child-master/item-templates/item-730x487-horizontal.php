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
    <div class="row">
      <div class="col-12 col-md-auto px-0 item-730x487-width">
        <a href="<?php the_permalink() ?>">
          <img src="<?php echo esc_url( $img_src ); ?>"
         srcset="<?php echo esc_attr( $img_srcset ); ?>"
          class="img-fluid"
          style="max-width: 100%;height:auto;"
        alt="">
        </a>
      </div>

      <div class="col item-content-container p-4">
        <div>
            <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
        </div>
        <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
        <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
        <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      </div>
    </div>
  </div>
