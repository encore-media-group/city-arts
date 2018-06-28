<?php
/**
 * The parent template for displaying the calendar child template.
 *
 * @package understrap
 */


$cats = get_query_var('cats');
$month = get_query_var('month');
$year = get_query_var('year');

$is_calendar_archive = get_query_var('is_calendar_archive');

$calendar = Calendar::get_calendar_posts( 10, $cats, $month, $year );

$see_it_this_week = ( ! $is_calendar_archive) ? DataHelper::get_see_it_this_week() : null;

$page_title = ( $is_calendar_archive ) ? get_the_title() . " Recommended Events" : "Recommended Events";
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
        <div class="row pt-4">
          <div class="col-12">
            <h2 class="page-title pb-lg-4 sidelines">Calendar</h2>
          </div>
          <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">
            <div class="row">
              <header class="entry-header col-12 col-lg-9">
                <?= sprintf('<h1 class="entry-title">%1$s</h1>', $page_title ); ?>
              </header><!-- .entry-header -->
            </div>
            <div class="row">
              <div class="col-12 col-md-7 col-lg-8 ">
                <div class="row">
                  <div class="col">
                    <img
                       src="<?php echo esc_url( $img_src ); ?>"
                       srcset="<?php echo esc_attr( $img_srcset ); ?>"
                       sizes="(max-width: 46em) 100vw, 730px"
                       style="max-width:100%;height:auto;"
                       class="img-fluid new-image"
                       alt="">
                    <div class="caption p-2">
                      <?php echo $thumbnail_caption ?>
                      <?php echo $thumbnail_description ?>
                    </div>
                    <div class="article-content">
                     <?php the_content(); ?>
                    </div> <!-- .article-content -->
                  </div>
                </div>
                <?php
                if( ! $is_calendar_archive ) :
                  while( $see_it_this_week->have_posts() ) : $see_it_this_week->the_post();
                    set_query_var( 'show_byline', true );
                    get_template_part( 'item-templates/item', '320x213-calendar' );
                  endwhile;
                  wp_reset_postdata();
                endif;
                ?>
                <div class="row px-3 px-md-0">
                  <div class="col">
                    <?php
                    // Display Calendar Posts
                    while( $calendar->have_posts() ) : $calendar->the_post();
                      set_query_var ('show_excerpt', true );
                      get_template_part( 'item-templates/item', '255x170-calendar' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-5 col-lg-4">
                <div class="entry-content sidebar">
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
