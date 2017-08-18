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
		<div class="row px-3 justify-content-center">
			<div class="col-12 col-sm-auto">
				<h2>NEWSLETTERS</h2>
				<ul>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
				</ul>
			</div>
			<div class="col-12 col-sm-auto">
				<h2>MAGAZINE</h2>
				<ul>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
				</ul>
			</div>
			<div class="col-12 col-sm-auto">
				<h2>ABOUT</h2>
				<ul>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
					<li><a href="">See it this week</a></li>
				</ul>
			</div>
			<div class="col-12 col-sm-auto">
				<h2>FOLLOW</h2>
				<ul>
					<li><a href="">twitter icon</a></li>
					<li><a href="">face</a></li>
					<li><a href="">instagram</a></li>
					<li><a href="">soundcloud</a></li>
				</ul>

			</div>
		</div>

		<div class="row">

			<div class="col-12">

				<footer class="site-footer" id="colophon">

					<div class="site-info text-center">
								&copy; <?php echo date("Y"); ?>  Encore Media Group

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

