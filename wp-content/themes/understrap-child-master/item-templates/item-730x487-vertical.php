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

  $alt_version = isset($alt_version) ? $alt_version : false;

?>
  <div class="col-12 px-0 col-sm-auto item-730x487-vertical">
    <div class="row mx-0">
      <div class="col-12 px-0 ">
        <a href="<?php the_permalink(); ?>">
          <img src="<?php echo esc_url( $img_src ); ?>"
           srcset="<?php echo esc_attr( $img_srcset ); ?>"
            sizes="(max-width: 46em) 100vw, 730px"
          class="img-fluid"
          style="max-width: 100%;height:auto;"
          alt="">
      </a>
      </div>

      <?php if( $alt_version ) :?>
      <div class="col py-4 px-4 item-content-container item-730x487-width item-730x487-alt">
        <div class="row">
          <div class="col col-auto pl-3">
            <div class="cat-label"><?php get_template_part( 'item-templates/item', 'category-label' ); ?></div>
          </div>
          <div class="col">
            <div class="contributors"> <?php echo understrap_posted_on( true ); ?></div>
          </div>
        </div>
        <h4> <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a> </h4>
        <?php else: ?>
        <div class="col py-4 px-4 item-content-container item-730x487-width">
          <div class="cat-label"><?php get_template_part( 'item-templates/item', 'category-label' ); ?></div>
          <h1> <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a> </h1>
          <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
          <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
