<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */


  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;
  $thumbnail_description = get_post($thumbnail_id)->post_content;

  $image_attachment_metadata = wp_get_attachment_metadata($thumbnail_id);
  $img_width = isset($image_attachment_metadata['width']) ? $image_attachment_metadata['width'] : 0;
  $img_height = isset($image_attachment_metadata['height']) ? $image_attachment_metadata['height'] : 0;

  $img_orientation = 'landscape';

if( $img_width < $img_height ) {
  $img_orientation = 'portrait';
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-730xauto' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-730xauto' );
} else {
  $img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-730-487' );
  $img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-730-487' );
}

//echo $img_orientation . ' ' . $img_width . 'x' . $img_height . "<br>" ;

?>

<div class="px-md-0 container" id="content" tabindex="-1">
  <div class="row">
    <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">

      <div class="row">

        <header class="entry-header col col-md-9">
          <?php get_template_part( 'item-templates/item', 'category-label' ); ?>

          <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
          <div class="entry-meta contributors"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
        </header><!-- .entry-header -->

      </div>

      <div class="row">
        <div class="col-12 col-md-7 col-lg-8 ">
          <img
           src="<?php echo esc_url( $img_src ); ?>"
           srcset="<?php echo esc_attr( $img_srcset ); ?>"
           sizes="(max-width: 46em) 100vw, 730px"
           style="max-width:100%;height:auto;"
           class="img-fluid"
           alt="">

          <div class="caption p-2">
            <?php echo $thumbnail_caption ?>
            <?php echo $thumbnail_description ?>
          </div>
    			<div class="article-content">
              <?php the_content(); ?>
    			</div> <!-- .article-content -->
        </div>
        <div class="col-12 col-md-5 col-lg-4">
          <div class="entry-content">

            <?php if ( is_active_sidebar( 'article-right-1' ) ) : ?>
              <div id="article-right-sidebar" class="primary-sidebar widget-area" role="complementary">
                <?php dynamic_sidebar( 'article-right-1' ); ?>
              </div><!-- #primary-sidebar -->
            <?php endif; ?>

          </div><!-- .entry-content -->
        </div>
      </div><!-- row -->
    </article><!-- #post-## -->
  </div><!-- .row -->
</div><!-- Container end -->
