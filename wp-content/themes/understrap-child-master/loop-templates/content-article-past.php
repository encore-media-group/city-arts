<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;

  $img_original_src = wp_get_attachment_image_url( $thumbnail_id, 'full' );

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

  $show_in_calendar_field = get_field("show_in_calendar");
  $show_in_calendar = ( $show_in_calendar_field ) ? true : false;

  $events = get_field('events');
  $events_html = Calendar::list_events( $events );
?>
<div class="px-md-0 container" id="content" tabindex="-1">

<?php if( $img_original_src ) : ?>
  <style type="text/css">
    img[src*="<?php echo basename($img_original_src) ?>"]:not(.new-image) {
      display: none;
    }
    .article-content > img:first-of-type { display: none; }
  </style>
<?php endif; ?>
  <div class="row">
    <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">

      <div class="row">

        <header class="entry-header col-9">
          <?php
          if( ! $show_in_calendar ) :
            get_template_part( 'item-templates/item', 'category-label' );
          endif;
          ?>
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
           class="img-fluid new-image"
           alt="">

          <div class="caption p-2">
            <?php echo $thumbnail_caption ?>
            <?php //echo $thumbnail_description ?>
          </div>
          <?php
            if( $show_in_calendar ) :
              $calendar_disciplines = get_calendar_disciplines();
              echo '<ul class="pagination flex-wrap">';
              foreach ($calendar_disciplines as $calendar_discipline) :
                echo sprintf('<li class="page-item"><a href="/calendar/%1$s" class="page-link">%2$s</a></li>', $calendar_discipline['slug'], $calendar_discipline['name'] );
              endforeach;
              echo '</ul>';
            endif;
          ?>
          <div class="article-content">
            <?php the_content(); ?>
            <?= $events_html ?>
          </div>
          <?php get_template_part( 'item-templates/content', 'promo' ); ?>
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
      <footer class="entry-footer">
      </footer><!-- .entry-footer -->
    </article><!-- #post-## -->
  </div><!-- .row -->
</div><!-- Container end -->
