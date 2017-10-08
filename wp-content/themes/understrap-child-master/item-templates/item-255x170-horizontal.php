<?php
/**
 * Single item 255x170 template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-255x170' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-255x170' );

  $show_excerpt = isset( $show_excerpt ) ? $show_excerpt : false ;

?>
  <!-- item 255x170 -->
  <div class="row item-255x170 text-left">
    <div class="col-12 col-sm-auto col-lg-6">
      <a href="<?php the_permalink() ?>" rel="bookmark">
      <img
       src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
       sizes="
       (max-width:577px) 730px,
       (max-width:768px) 160px,
       (min-width:769px) 255px,
       255px"
       style="max-width: 100%;height:auto;"
       class="img-fluid"
       alt="">
     </a>
    </div>
    <div class="col-12 col-sm col-md-6 mt-4 mt-sm-1 px-sm-0">
      <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
      <h3 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
     <?php if( $show_excerpt ) : ?>
        <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      <?php endif; ?>
    </div>
  </div>
