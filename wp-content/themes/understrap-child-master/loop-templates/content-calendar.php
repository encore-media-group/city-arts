<?php
/**
 * The parent template for displaying the calendar child template.
 *
 * @package understrap
 */


$cats = get_query_var('cats');
$calendar = Calendar::get_calendar_posts( 10, $cats );

?>

<div class="wrapper" id="single-wrapper">
  <main class="site-main" id="main">
      <?php
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
      ?>
      <div class="px-md-0 container" id="content" tabindex="-1">
        <div class="row">
          <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">
            <div class="row">
              <header class="entry-header col col-md-9">
                <?php the_title( '<h1 class="entry-title">', ' recommended events</h1>' ); ?>
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
                <?php
                // Display Calendar Posts
                while( $calendar->have_posts() ) : $calendar->the_post();
                  set_query_var ('show_excerpt', true );
                  get_template_part( 'item-templates/item', '255x170-bg-image-horizontal' );
                endwhile;
                wp_reset_postdata();
                ?>
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
  	<div class="container-fluid ad-container">
  		<?= ad_728xlandscape_bottom_shortcode(); ?>
  	</div>

  	<!-- RELATED ARTICLES -->
    <div class="container mb-4">
      <?php set_query_var ('post_id', $post->ID ); ?>
      <?php get_template_part( 'item-templates/item', 'related-articles' ); ?>
  	</div><!-- RELATED ARTICLES END -->
  </main>
</div><!-- Wrapper end -->

<?php get_footer(); ?>
