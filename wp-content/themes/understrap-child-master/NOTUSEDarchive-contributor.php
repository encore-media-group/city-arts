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

?>
<div class="wrapper" id="archive-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">

				<?php
				if ( have_posts() ) : ?>

					<header class="page-header">
						<?php if ( $paged < 2 ) : ?>
							<h1 class="page-title text-center py-4"> <?php single_cat_title() ?> </h1>
						<?php endif; ?>

						<div class="row justify-content-center">
							<?php if ( $paged < 2 ) : ?>
								<?php the_archive_description( '<div class="col-12 col-lg-10 pb-4 taxonomy-description">', '</div>' ); ?>
							<?php endif; ?>
						</div><!-- end row -->

					</header><!-- .page-header -->

					<?php
						$count = 1;
					  echo '<div class="row">';
					while ( have_posts() ) : the_post();
						?>
						<div class="col-12 col-sm-6 col-lg-3">
							<?php get_template_part( 'item-templates/item', '255x170' ); ?>
						</div>
						<?php
							if ($count > 4 && $count % 4 == 0) : echo '<div class="m-100"></div>'; endif;
							$count++;
							endwhile;
							wp_reset_postdata();
							echo "</div><!-- end row -->";
					else :
						get_template_part( 'loop-templates/content', 'none' );
					endif;
					?>

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
