<?php
/**
 * Single post partial template for article enhanced.
 *
 * @package understrap
 */

  $thumbnail_id = get_post_thumbnail_id( $post->ID );
  $thumbnail_caption = get_post($thumbnail_id)->post_excerpt;
  $thumbnail_description = get_post($thumbnail_id)->post_content;

  $hero_image['src'] = wp_get_attachment_image_url( $thumbnail_id, 'ca-2000x1333' );
  $hero_image['srcset'] = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-2000x1333' );
  $hero_image['style'] = ' max-width:2000px;height:auto; ';

  $feature_image = build_img_tag( $hero_image );
?>
<div class="container-fluid px-0" tabindex="-1">
  <div class="row mx-auto item-2000x1333-width">
    <div class="col text-center px-0">
      <?= $feature_image ?>
      <div class="caption text-left p-2">
        <?php echo $thumbnail_caption ?>
        <?php echo $thumbnail_description ?>
      </div>
    </div>
  </div><!-- end row -->


</div>
<main class="site-main" id="main">
  <div class="container" id="content" tabindex="-1">
    <div class="row">
        <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">
          <div class="row">
            <header class="entry-header col text-center mb-4">
             <?php
              $cat_label = get_category_label();
              echo sprintf( '<a class="url fn n" href="%1$s"><h2 class="sidelines pt-5 pb-2 w-50 mx-auto">%2$s</h2></a>', $cat_label['url'], $cat_label['name'] );
              ?>

              <?php the_title( '<h1 class="entry-title my-4">', '</h1>' ); ?>
              <?php if ( has_excerpt() ) : ?>
                <h3 class="entry-excerpt mt-2 mb-3 w-75 mx-auto"><?= $post->post_excerpt; ?></h3>
              <?php endif; ?>
              <div class="entry-meta contributors mb-5"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
            </header><!-- .entry-header -->
          </div>
          <div class="row">
            <div class="col-12 col-sm-10 mx-auto  ">
            <div class="article-content">
            <?php
              echo set_first_letter_of_post( $post );
            ?>
				    </div> <!-- .article-content -->
          </div>
        </div><!-- row -->
      </article><!-- #post-## -->
    </div>
  </div>
</main><!-- #main -->
