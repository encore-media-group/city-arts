<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );
?>

<div class="wrapper" id="single-wrapper">
	<div class="<?php echo esc_html( $container ); ?>" id="content" tabindex="-1">
		<div class="row">
      <div class="col-9">
			 <main class="site-main" id="main">
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'loop-templates/content', 'contributor' ); ?>

				<?php endwhile; ?>
        </main><!-- #main -->
      </div>
    </div><!-- .row -->
  </div><!-- Container end -->

  <div class="container">
    <div class="row">
      <div class="col ">
        <h3 class="sidelines">Recent Articles</h3>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-lg">
        <?php

          $posts = get_field('relationship');

          if( $posts ):
            foreach( $posts as $post):
              setup_postdata($post);
              echo '<div class="row">';
              get_template_part( 'item-templates/item', '320x213' );
              echo "</div>";
            endforeach;
            wp_reset_postdata();
          endif;
        ?>
      </div>
      <div class="col-12 col-lg">
        <div class="row">
          <!-- Do the right sidebar check -->
          <?php if ( 'right' === $sidebar_pos || 'both' === $sidebar_pos ) : ?>
            <?php get_sidebar( 'right' ); ?>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- end row -->
  </div>
</div><!-- Wrapper end -->

<?php get_footer(); ?>
