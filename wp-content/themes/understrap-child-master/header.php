<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package understrap
 */

$container = get_theme_mod( 'understrap_container_type' );
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
			var mapLeader = googletag.sizeMapping().
		  	addSize([640, 480], [728, 90]).
		  	addSize([0, 0], [320, 50]).
		  	build();

		googletag.defineSlot('/21626118154/cityarts_leaderboards', [[728, 90], [320, 50]], 'div-gpt-ad-1506555798490-0').defineSizeMapping(mapLeader).addService(googletag.pubads());
		googletag.defineSlot('/21626118154/cityarts_mediumrectangle', [300, 250], 'div-gpt-ad-1506555798490-1').addService(googletag.pubads());
		googletag.pubads().enableSingleRequest();
		googletag.enableServices();
		});
	</script>
	<!-- end google ad script -->

</head>

<body <?php body_class(); ?>>

<div class="hfeed site" id="page">
