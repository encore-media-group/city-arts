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

$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

$the_query = new WP_Query(array(
    'posts_per_page' => 15,
    'cat' => get_cat_ID('music'),
    'meta_query' => array(array('key' => '_thumbnail_id' )),
    'paged' => $paged
));

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
						</div><!-- end row -->
					</header><!-- .page-header -->

					<?php
						$count = 1;
					  echo '<div class="row">';
            while( $the_query->have_posts() ) : $the_query->the_post();
							if ( $count == 1 ) :
						 		get_template_part( 'item-templates/item', '730x487' );
						 		echo '</div><!-- end row --><div class="row pt-4">';
							elseif ($count == 2):
						 		get_template_part( 'item-templates/item', '320x213' );
							elseif ($count == 3):
						 		get_template_part( 'item-templates/item', '320x213' );
						 		echo '</div><!-- end row --><div class="row">';
							else:
						?>
						<div class="col-12 col-sm-6 col-lg-3">
							<?php get_template_part( 'item-templates/item', '255x170' ); ?>
						</div>
						<?php if ($count > 4 && $count % 4 == 0) : echo '<div class="m-100"></div>'; endif; ?>
						<?php
								endif;
							$count++;
							endwhile;

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
