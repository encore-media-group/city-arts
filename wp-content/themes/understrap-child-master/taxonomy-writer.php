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

<div class="wrapper" id="writer-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">
				<?php
				if ( have_posts() ) : ?>

					<header class="page-header">
						<?php if ( $paged < 2 ) : ?>
							<h1 class="page-title text-left py-4"> <?php single_cat_title() ?> </h1>
						<?php endif; ?>

						<div class="row justify-content-center">
							<?php if ( $paged < 2 ) : ?>
								<?php the_archive_description( '<div class="col-12 col-lg-10 pb-4 taxonomy-description">', '</div>' ); ?>
							<?php endif; ?>
						</div><!-- end row -->
					</header><!-- .page-header -->

<div class="row mx-auto">
	<h2 class="col page-title pb-4 sidelines">Recent Articles</h2>
</div>

<div class="row">
  <div class="col-12 col-md-7 col-lg-8 ">

					<?php
					while ( have_posts() ) : the_post();
					?>
					<div class="row">
					<?php
						$args = [
							'query_vars' => [ [ 'var' =>'item_css', 'val' => 'col-12 mb-4' ] ],
							'template' => [ 'path' => 'item-templates/item', 'file'=>'320x213' ]
						];

						get_template_part( 'item-templates/item', '320x213' );

					 ?>
					</div>
					<?php
							endwhile;
							wp_reset_postdata();
					endif;

					?>
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
