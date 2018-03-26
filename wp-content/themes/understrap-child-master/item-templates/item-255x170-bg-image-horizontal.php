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
    <div class="col-12 col-sm-auto col-lg-4">
      <a href="<?php the_permalink() ?>" rel="bookmark">
        <div class="item-list-div" style="background: url('<?= esc_url( $img_src )?>') no-repeat center center;background-size:cover;"></div>
      </a>
    </div>
    <div class="col-12 col-sm mt-4 mt-sm-1 px-sm-0">
    <?php
      $yellow_label = '<a class="url fn n" href="%1$s"><span class="category-label align-middle text-center mb-2 px-2 py-1">%2$s</span></a>';
      echo sprintf( $yellow_label, $cat_label['url'], $cat_label['name'] );
    ?>
      <h4 class="mb-0"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
     <?php if( $show_excerpt ) : ?>
        <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      <?php endif; ?>
    </div>
  </div>
