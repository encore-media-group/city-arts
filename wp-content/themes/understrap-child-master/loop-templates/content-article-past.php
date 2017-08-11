<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

?>
<?php
$container   = get_theme_mod( 'understrap_container_type' );


$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail = wp_get_attachment_image_src( $thumbnail_id, "full" );

$thumbnail_url = $thumbnail[0];
$thumbnail_width = $thumbnail[1];
$thumbnail_height = $thumbnail[2];

$thumbnail_caption = get_post($thumbnail_id)->post_excerpt; ?>
<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

    <div class="row">

      <main class="site-main" id="main">

        <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">

          <div class="row">

            <header class="entry-header col-12">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
              <div class="entry-meta"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
              <?php the_category( ' | ' ); ?>
            </header><!-- .entry-header -->

          </div>

          <div class="row">
            <div class="col-12 col-md-7 col-lg-8">
              <div class="single-post-image-hero " style="padding-bottom: <?php echo $thumbnail_height; ?>px;background-image: url('<?php echo $thumbnail_url; ?>');"></div>
              <div><?php echo $thumbnail_caption ?></div>
              <?php the_content(); ?>
            </div>
            <div class="col-12 col-md-5 col-lg-4 ">
              <div class="entry-content">

                      <?php if ( is_active_sidebar( 'article-right-1' ) ) : ?>
                        <div id="article-right-sidebar" class="primary-sidebar widget-area" role="complementary">
                          <?php dynamic_sidebar( 'article-right-1' ); ?>
                        </div><!-- #primary-sidebar -->
                      <?php endif; ?>

                  <div class="attached-images">
                    <ul>
                    <?php
                    $images = get_attached_media('image', $post->ID);

                    foreach($images as $image) {
                        echo var_dump($image);
                        ?>
                        <li><img src="<?php echo wp_get_attachment_image_src($image->ID,'medium')[0]; ?>" /></li>
                    <?php } ?>
                    </ul>
                  </div>
              <?php
              wp_link_pages( array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
                'after'  => '</div>',
              ) );
              ?>
              </div><!-- .entry-content -->
            </div><!--end col-lg-6-->
          </div><!-- row -->
          <footer class="entry-footer">

          <?php understrap_entry_footer(); ?>

          </footer><!-- .entry-footer -->
        </article><!-- #post-## -->
      </main><!-- #main -->
    </div><!-- #primary -->
  </div><!-- .row -->
</div><!-- Container end -->
