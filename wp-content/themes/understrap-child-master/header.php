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

</head>

<body <?php body_class(); ?>>

<div class="hfeed site" id="page">

<!-- ******************* The Navbar Area ******************* -->
	<div class="wrapper-fluid wrapper-navbar fixed-top" id="wrapper-navbar">

		<a class="skip-link screen-reader-text sr-only" href="#content"><?php esc_html_e( 'Skip to content',
		'understrap' ); ?></a>

			<nav class="container navbar navbar-expand-md navbar-dark bg-dark">
				<div class="row justify-content-center">
					<div class='col-auto pl-3'>
						<button class="hamburger hamburger--spin " type="button" data-toggle="slide-collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
							<span class="hamburger-box">
							<span class="hamburger-inner"></span>
							</span>
						</button>

					</div>
					<div class='col-auto mx-auto my-auto'>
						<a class="navbar-brand my-0 w-100" rel="home" href="/" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ) ?>"><img src="/wp-content/themes/understrap-child-master/assets/cityarts-logo.svg" id="cityarts-header-logo"></a>
					</div>

					<div class='col-auto pr-3'>
						<div class="search-nav-button" role="button"><i class="fa fa-search fa-lg" aria-hidden="true"></i></div>
					</div>
				</div><!-- end row -->
				<div class="search-input-wrapper">
					<form>
				    <div class="input-group">
				      <?= get_search_form(false); ?>
				    </div>
				  </form>
				</div>


			<script type="text/javascript">
			var $hamburger = jQuery(".hamburger");

			  $hamburger.on("click", function(e) {
			    $hamburger.toggleClass("is-active");
			    $navMenuCont = jQuery(jQuery(this).data('target'));
			    $navMenuCont.animate({'width':'toggle'}, 250);
			  });

				jQuery(window).scroll(function() {
				  if (jQuery(document).scrollTop() > 2) {
				    jQuery('#wrapper-navbar').addClass('shrink');
				  } else {
				    jQuery('#wrapper-navbar').removeClass('shrink');
				  }
				});

			var $search_button = jQuery(".search-nav-button");

				$search_button.on("click", function() {
				  jQuery( ".search-input-wrapper" ).slideToggle( "fast", function() {
				    console.log('done');
				  });
				});
			</script>
			<div class="row">
				<div class="col">
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
						'container_class' => 'collapse navbar-collapse px-4',
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
		</nav><!-- .site-navigation -->
	</div><!-- .wrapper-navbar end -->
