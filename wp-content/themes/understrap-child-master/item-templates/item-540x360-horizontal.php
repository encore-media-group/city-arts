<?php
/**
 * Single item item-540x360-horizontal.php partial template.
 *
 * @package understrap
 */

?>
<?php
  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-540x360' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-540x360' );

  //check if this is "from the magazine"..
  $flag_from_magazine = true;
?>
<div class="col px-sm-0 item-540x360-horizontal">
  <div class="row no-gutters">
    <div class="col-12 col-lg-6">
      <img src="<?php echo esc_url( $img_src ); ?>"
       srcset="<?php echo esc_attr( $img_srcset ); ?>"
      class="img-fluid"
      style="max-width: 100%;height:auto;"
      alt="">
    </div>
    <?php // sizes="(max-width: 46em) 100vw, 540px"
    ?>

    <div class="col-12 col-lg item-content-container p-4">
      <?php if( $flag_from_magazine ):?>
        <div class="card-span-text mb-3">FROM THE MAGAZINE</div>
      <?php endif; ?>
      <div>
          <?php get_template_part( 'item-templates/item', 'category-label' ); ?>
      </div>
      <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
      <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
      <?php if( $flag_from_magazine ):?>
		<div class="card-span-text mt-3 small"><a href="<?= get_current_issue_link() ?>">MORE FROM THIS ISSUE <i class="fa fa-arrow-right fa-1 arrow-right" aria-hidden="true"></i></a></div>
      <?php endif; ?>
    </div>
  </div>
</div>

