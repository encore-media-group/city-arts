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
				<h2>NEWSLETTERS</h2>

				<!-- Begin MailChimp Signup Form -->
				<div id="mc_embed_signup-footer">
					<form action="//encoremediagroup.us4.list-manage.com/subscribe/post?u=21f8fede41bd5e2c87aa9504b&amp;id=7d8b6778a7" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				    <div id="mc_embed_signup_scroll">
							<div class="mc-field-group input-group">
								<ul>
									<li><label class="mb-0" for="mce-group[1]-1-1"><input checked class="mr-2" type="checkbox" value="2" name="group[1][2]" id="mce-group[1]-1-1">City Arts Events</label></li>
									<li><label class="mb-0" for="mce-group[1]-1-0"><input checked class="mr-2" type="checkbox" value="1" name="group[1][1]" id="mce-group[1]-1-0">New Issue Alert</label></li>
									<li><label class="mb-0" for="mce-group[1]-1-3"><input checked class="mr-2" type="checkbox" value="8" name="group[1][8]" id="mce-group[1]-1-3">See It This Week</label></li>
									<li><label class="mb-0" for="mce-group[1]-1-4"><input checked class="mr-2" type="checkbox" value="16" name="group[1][16]" id="mce-group[1]-1-4">Weekend Reads</label></li>
									<li><label class="mb-0" for="mce-group[1]-1-2"><input checked class="mr-2" type="checkbox" value="4" name="group[1][4]" id="mce-group[1]-1-2">Partner Offers</label></li>
								</ul>
							</div> <!-- .mc-field-group -->

							<div id="mce-responses-footer" class="clear">
								<div class="response" id="mce-error-response-footer" style="display:none"></div>
								<div class="response" id="mce-success-response-footer" style="display:none"></div>
							</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->

							<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_21f8fede41bd5e2c87aa9504b_7d8b6778a7" tabindex="-1" value=""></div>

							<div class="clear"><input type="submit" value="SIGN UP" name="subscribe" id="mc-embedded-subscribe-footer" class="button"></div>

							</div> <!-- #mc_embed_signup_scroll -->
					</form>
				</div> <!-- #mc_embed_signup -->

			</div>
			<div class="col-12 col-sm-auto">
				<h2>MAGAZINE</h2>
				<ul>
					<li><a href="<?= get_current_issue_link() ?>">This Month’s Issue</a></li>
					<li><a href="/issue">Archives</a></li>
					<li><a href="/subscribe">Subscribe</a></li>
					<li><a href="/subscribe">Magazine Locator</a></li>
					<li><a href="/advertise">Advertise</a></li>
					<li><a href="/advertise">Ad Specifications</a></li>
				</ul>
			</div>
			<div class="col-12 col-sm-auto">
				<h2>ABOUT</h2>
				<ul>
					<li><a href="/about">Masthead</a></li>
					<li><a href="/contact">Contact</a></li>
					<li><a href="/jobs">Jobs</a></li>
					<li><a href="/terms-of-use">Terms &amp; Conditions</a></li>
					<li><a href="/privacy">Privcay Policy</a></li>
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

