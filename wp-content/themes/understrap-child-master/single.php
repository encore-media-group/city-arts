<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();
$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );

// determine what article view type to display
// the list of article formats is in the city arts plugin
$term_list = wp_get_post_terms($post->ID, 'article_format', array("fields" => "all"));
$article_format = "";
$template = "single";

if(is_array($term_list)) {
	$template = $term_list[0]->slug;
}


?>


<div class="wrapper" id="single-wrapper">
	<div class="container ad-container px-0">
	  <div class="row no-gutters">
	    <div class="col-xl-12 py-2 text-center">
	      <?php get_template_part( 'item-templates/item', 'landscape-ad' ); ?>
	    </div>
	  </div>
	</div>
	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="site-main" id="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'loop-templates/content', $template ); ?>

						<?php understrap_post_nav(); ?>

				<?php endwhile; // end of the loop. ?>

			</main><!-- #main -->

		</div><!-- #primary -->

		<?php if ( is_active_sidebar( 'article-right-1' ) ) : ?>
				<div id="article-right-sidebar" class="primary-sidebar widget-area" role="complementary">
					<?php dynamic_sidebar( 'article-right-1' ); ?>
				</div><!-- #primary-sidebar -->
			<?php endif; ?>
		<!-- Do the right sidebar check -->
		<?php //if ( 'right' === $sidebar_pos || 'both' === $sidebar_pos ) : ?>

			<?php// get_sidebar( 'right' ); ?>

		<?php// endif; ?>

	</div><!-- .row -->

</div><!-- Container end -->

</div><!-- Wrapper end -->

<?php get_footer(); ?>
