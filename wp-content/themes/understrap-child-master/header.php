<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package understrap
 */

$container = get_theme_mod( 'understrap_container_type' );

$current_cover = get_current_issue_image();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-3120102-3"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-3120102-3');
	</script>
	<!--end GA -->
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?php bloginfo( 'name' ); ?> - <?php bloginfo( 'description' ); ?>">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
  <script src="https://use.typekit.net/faz4bwi.js"></script>
  <script>try{Typekit.load({ async: true });}catch(e){}</script>
	<?php wp_head(); ?>
	<!-- google ad script -->
	<script src="<?= get_stylesheet_directory_uri() ?>/assets/slideout.min.js"></script>

	<script async='async' src='https://www.googletagservices.com/tag/js/gpt.js'></script>
	<script>
		var googletag = googletag || {};
		googletag.cmd = googletag.cmd || [];

		googletag.cmd.push(function() {
			var leaderboard_mapLeader = googletag.sizeMapping().
		  	addSize([640, 480], [728, 90]).
		  	addSize([0, 0], [320, 50]).
		  	build();

			var halfpage_mapLeader = googletag.sizeMapping().
		  	addSize([640, 480], [300, 600]).
		  	addSize([0, 0], [300, 250]).
		  	build();

    googletag.defineSlot('/21626118154/cityarts_leaderboards_bt', [[728, 90], [320, 50]], 'div-gpt-ad-1507566484755-1').defineSizeMapping(leaderboard_mapLeader).addService(googletag.pubads());
    googletag.defineSlot('/21626118154/cityarts_leaderboards', [[728, 90], [320, 50]], 'div-gpt-ad-1507566484755-2').defineSizeMapping(leaderboard_mapLeader).addService(googletag.pubads());

		googletag.defineSlot('/21626118154/cityarts_mediumrectangle', [300, 250], 'div-gpt-ad-1506555798490-1').addService(googletag.pubads());
		googletag.defineSlot('/21626118154/cityarts_halfpage', [[300, 250], [300, 600]], 'div-gpt-ad-1507525983729-0').defineSizeMapping(halfpage_mapLeader).addService(googletag.pubads());
		googletag.pubads().enableSingleRequest();
		googletag.enableServices();
		});
	</script>
	<!-- end google ad script -->
	<script type="text/javascript">
		jQuery(function() {
			var $hamburger = jQuery(".hamburger");
		  $hamburger.on("click", function(e) {
		    $hamburger.toggleClass("is-active");
		  });

			var $wrappeNavbar = jQuery('#wrapper-navbar');
			jQuery(window).scroll(function() {
			  if (jQuery(document).scrollTop() > 2) {
			    $wrappeNavbar.addClass('shrink');
			  } else {
			    $wrappeNavbar.removeClass('shrink');
			  }
			});

			var $search_button = jQuery(".search-nav-button");
			$search_button.on("click", function() {
			  jQuery( ".search-input-wrapper" ).slideToggle( "fast", function() {
			  });
			});
		});
	</script>
	<script type="text/javascript">
		jQuery(function() {
      var slideout = new Slideout({
        'panel': document.getElementById('page'),
        'menu': document.getElementById('menu'),
        'padding': 256,
        'tolerance': 70
      });

      jQuery('.slideout-menu').removeAttr('style');

      // Toggle button
      document.querySelector('.toggle-button').addEventListener('click', function() {
        slideout.toggle();
      });

      var fixed = document.querySelector('.fixed');

			slideout.on('translate', function(translated) {
			  fixed.style.transform = 'translateX(' + translated + 'px)';
			});

			slideout.on('beforeopen', function () {
			  fixed.style.transition = 'transform 300ms ease';
			  fixed.style.transform = 'translateX(256px)';
			});

			slideout.on('beforeclose', function () {
			  fixed.style.transition = 'transform 300ms ease';
			  fixed.style.transform = 'translateX(0px)';
			});

			slideout.on('open', function () {
			  fixed.style.transition = '';
			});

			slideout.on('close', function () {
			  fixed.style.transition = '';
			});


    });
</script>
</head>
<body <?php body_class(); ?>>

	<!-- the menu -->
	<div id="menu" class="container" style="display:none;">
		<div class="row">
			<div class="col pt-4 pb-5">
			<?php
				$menu_cover_section = '<div class="menu-cover-image mt-4">%1$s<div class="title">%2$s</div></div>';
				$menu_cover_section = sprintf($menu_cover_section, $current_cover['image'], $current_cover['link'] );
				$menu_cover_section .= '<div class="menu-bottom-section my-3"></div>';

				$sub_menu = wp_nav_menu( array(
					'theme_location' => 'sidebar-submenu',
					'menu_class'      => 'sidebar-submenu-nav navbar-nav',
					'menu_id' => 'sidebar-submenu',
					'echo' => false
					)
				);

			//The WordPress Menu goes here
			wp_nav_menu(
				array(
					'theme_location'  => 'primary',
					'container_class' => 'px-4',
					'container_id'    => 'navbarNavDropdown',
					'menu_class'      => 'navbar-nav',
					'fallback_cb'     => '',
					'menu_id'         => 'main-menu',
					'walker'          => new WP_Bootstrap_Navwalker(),
					'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>' . $menu_cover_section . $sub_menu  ,

				)
			);

			?>
			</div>
		</div>
	</div><!-- end of the menu -->

	<!--start of the nav-->
	<div class="wrapper-fluid wrapper-navbar fixed" id="wrapper-navbar">

		<a class="skip-link screen-reader-text sr-only" href="#content"><?php esc_html_e( 'Skip to content',
		'understrap' ); ?></a>

		<nav  class="container navbar">
			<div class="row">
				<div class="col-3 px-sm-0">
					<button class="hamburger hamburger--spin toggle-button " type="button" data-toggle="slide-collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
						<span class="hamburger-box">
						<span class="hamburger-inner"></span>
						</span>
					</button>

				</div>
				<div class="col text-center">
					<a class="navbar-brand my-0 w-100" rel="home" href="/" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ) ?>"><img src="<?= get_stylesheet_directory_uri() ?>/assets/cityarts-logo.svg" id="cityarts-header-logo"></a>
				</div>

				<div class="col-3">
					<div class="search-nav-button float-right" role="button"><i class="fa fa-search fa-lg" aria-hidden="true"></i></div>
				</div>
			</div><!-- end row -->
			<div class="row search-input-wrapper">
				<div class="col-12">
			      <?= get_search_form(false); ?>
			   </div>
			</div>

		</nav><!-- .site-navigation -->
	</div><!-- .wrapper-navbar end -->
	<div class="hfeed site" id="page">




