<?php
/**
 * The template for displaying contriubtor archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

get_header();
?>

<?php
$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );

$queried_object = get_queried_object(); // you need this for ACF to access the custom properties of a tax object!
$taxonomy = $queried_object->taxonomy;
$term_id = $queried_object->term_id;

$taxonomy_term = $taxonomy . '_' . $term_id;
?>
<div class="wrapper" id="writer-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">

					<header class="row page-header">
							<div class="col-auto pl-3">
								<h1 class="page-title text-left py-4"> <?= single_cat_title('', false) ?> </h1>
							</div>
							<div class="col my-auto">
								<?php
								$twitter_slug = get_field('writer_twitter_slug', $queried_object );
								if( !empty( $twitter_slug ) ) {
									echo ( isset( $twitter_slug ) ) ? sprintf( '<a href="https://twitter.com/%1$s" class="social-handle"><i class="fa fa-twitter fa-2x" aria-hidden="true"></i> @%1$s</a>', $twitter_slug ) : '';
								}
								?>

							</div>
					</header><!-- .page-header -->

					<div class="row">
						<?php

								$image = get_field('writer_image', $taxonomy_term);
								if ( $image ) :
								?>
								<div class="col-12 col-md-auto text-center text-md-left">
									<?php
										set_query_var ('image', $image );
										get_template_part( 'item-templates/item', 'display-writer-profile-image' );
									?>
								</div>
						<?php
							endif;
							the_archive_description( '<div class="col-12 col-md-8 pb-4 writer-description">', '</div>' );
						?>
					</div><!-- end row -->

					<div class="row mx-auto pb-4">
						<div class="col px-0">
							<h2 class="page-title sidelines">Recent Articles</h2>
						</div>
					</div>
					<div class="row">
					  <div class="col-12 col-md-7 col-lg-8 ">
					  	<?php
								if ( have_posts() ) :
									while ( have_posts() ) : the_post();
									?>
									<div class="row mb-4">
										<?php
											set_query_var( 'show_byline_date', true );
											get_template_part( 'item-templates/item', '320x213' );
										?>
									</div>
									<?php
									endwhile;
									wp_reset_postdata();
								endif; ?>
						</div>
					 	<div class="col-12 col-md-5 col-lg-4">

 	                <?php if ( is_active_sidebar( 'article-right-1' ) ) : ?>
                  <div id="article-right-sidebar" class="primary-sidebar widget-area" role="complementary">
                    <?php dynamic_sidebar( 'article-right-1' ); ?>
                  </div><!-- #primary-sidebar -->
                <?php endif; ?>
						</div>
          </div><!-- row -->
				</div><!--row-->
			</main><!-- #main -->

		</div><!-- #primary -->

	</div> <!-- .row -->

</div><!-- Container end -->

<div class="container">
	<div class="row justify-content-center">
		<div class="col-auto">
		<!-- The pagination component -->
		<?php understrap_pagination(); ?>
		</div>
	</div>
</div>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
