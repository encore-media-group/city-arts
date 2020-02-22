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
			<div class="row px-3 d-flex justify-content-around">
				<div class="col-12 col-sm-auto">
					<h2>MAGAZINE</h2>
					<ul>
						<li><a href="/issue">Archives</a></li>
						<li><a href="/advertise">Advertise</a></li>
					</ul>
				</div>
				<div class="col-12 col-sm-auto">
					<h2>ABOUT</h2>
					<ul>
						<li><a href="/about">About</a></li>
						<li><a href="/contact">Contact</a></li>
						<li><a href="/terms-of-use">Terms &amp; Conditions</a></li>
						<li><a href="/privacy">Privacy Policy</a></li>
					</ul>
				</div>
				<div class="col-12 col-sm-auto">
					<h2>FOLLOW</h2>
					<ul class="social-list">
						<li class="pr-2"><a href="https://twitter.com/city_arts" rel="noopener"><i class="fa fa-twitter fa-2x" aria-hidden="true"></i></a></li>
						<li class="px-2"><a href="https://www.facebook.com/cityartsmagazine/" rel="noopener"><i class="fa fa-facebook fa-2x" aria-hidden="true"></i></a></li>
						<li class="px-2"><a href="https://www.instagram.com/city_arts_magazine" rel="noopener"><i class="fa fa-instagram fa-2x" aria-hidden="true"></i></a></li>
						<li class="px-2"><a href="https://soundcloud.com/cityartsmagazine" rel="noopener"><i class="fa fa-soundcloud fa-2x" aria-hidden="true"></i></a></li>
					</ul>
				</div>
			</div>

			<div class="row">

				<div class="col-12">

					<footer class="site-footer" id="colophon">

						<div class="site-info text-right">
									&copy; <?php echo date("Y"); ?>  City Arts Media

						</div><!-- .site-info -->

					</footer><!-- #colophon -->

				</div><!--col end -->

			</div><!-- row end -->
		</div><!-- container end -->

	</div><!-- wrapper end -->

</div><!-- #page -->


<?php
 wp_footer();
?>

</body>

</html>

