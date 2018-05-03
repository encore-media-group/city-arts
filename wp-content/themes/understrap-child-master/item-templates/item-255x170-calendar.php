<?php
/**
 * Single item 255x170 Horizontal template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-255x170' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-255x170' );

  $show_excerpt = isset( $show_excerpt ) ? $show_excerpt : false ;

  $cat_label = [];
  $cat_label['url'] = "/calendar";
  $cat_label['name'] = "City Arts";

  $post_cats = get_the_category( $post->ID);
  $disciplines = get_disciplines();

  foreach ($post_cats as $post_cat) :
    if( in_array($post_cat->slug, $disciplines )) :
      $cat_label['url'] = "/calendar/" . $post_cat->slug;
      $cat_label['name'] = $post_cat->name;
      break;
    endif;
  endforeach;
?>
<!-- item 255x170 Horizontal-->

<div class="row item-255x170 text-left mb-4">
  <div class="col-12 col-sm-auto col-lg-4 px-0 mr-3 mx-md-3">
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
  <div class="col-12 col-sm px-0 pt-2 pt-sm-0">
    <?php
      $label = '<span class="category-label calendar-item align-middle text-center mb-2 px-2 py-1">%2$s</span>';
      echo sprintf( $label, $cat_label['url'], $cat_label['name'] );
    ?>
      <h4 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
     <?php if( $show_excerpt ) : ?>
        <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      <?php endif; ?>
  </div>
</div>
