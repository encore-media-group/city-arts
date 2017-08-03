<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

$the_theme = wp_get_theme();
$container = get_theme_mod( 'understrap_container_type' );
?>

<?php get_sidebar( 'footerfull' ); ?>

<div class="wrapper" id="wrapper-footer">

	<div class="<?php echo esc_attr( $container ); ?>">
		<div class="row">
			<div class="col-md-3">
				<h5>NEWSLETTERS</h5>
			</div>
			<div class="col-md-3">
				<h5>MAGAZINE</h5>
			</div>
			<div class="col-md-3">
				<h5>ABOUT</h5>
			</div>
			<div class="col-md-3">
				<h5>SOCIAL</h5>
			</div>
		</div>

		<div class="row">

			<div class="col-md-12">

				<footer class="site-footer" id="colophon">

					<div class="site-info">
								&copy; <?php echo date("Y"); ?> City Arts Magazine<span class="sep"> | </span> Encore Media

					</div><!-- .site-info -->

				</footer><!-- #colophon -->

			</div><!--col end -->

		</div><!-- row end -->

	</div><!-- container end -->

</div><!-- wrapper end -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>

