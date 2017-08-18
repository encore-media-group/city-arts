<?php
/**
 * The template for displaying archive pages.
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
?>

<div class="wrapper" id="archive-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">

				<?php if ( have_posts() ) : ?>

					<header class="page-header">
						<h3 class="page-title sidelines sidebar py-4"> <?php single_cat_title() ?> </h3>
						<div class="row justify-content-center">
					<?php if ( $paged < 2 ) : ?>
						<?php the_archive_description( '<div class="col-12 col-sm-10 pb-4 taxonomy-description">', '</div>' ); ?>
					<?php endif; ?>

						</div>
					</header><!-- .page-header -->

					<div class="row">

					<?php while ( have_posts() ) : the_post(); ?>
						<div class="col-12 col-sm-6 col-lg-3">
						<?php
						 get_template_part( 'item-templates/item', 'medium-small' );
						?>
						</div>
					<?php endwhile; ?>

				<?php else : ?>

					<?php get_template_part( 'loop-templates/content', 'none' ); ?>

				<?php endif; ?>

				</div><!--row-->
			</main><!-- #main -->

		</div><!-- #primary -->

	</div> <!-- .row -->

</div><!-- Container end -->

<divi class="container">
	<div class="row justify-content-center">
		<div class="col-auto">
		<!-- The pagination component -->
		<?php understrap_pagination(); ?>
		</div>
	</div>
</divi>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
