<?php
namespace SiteGround_Optimizer\Lazy_Load;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load {

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {

		if (
			! Options::is_enabled( 'siteground_optimizer_lazyload_images' ) &&
			! Options::is_enabled( 'siteground_optimizer_lazyload_iframes' ) &&
			! Options::is_enabled( 'siteground_optimizer_lazyload_videos' )
		) {
			return;
		}

		if ( Front_End_Optimization::is_mobile() && ! Options::is_enabled( 'siteground_optimizer_lazyload_mobile' ) ) {
			return;
		}

		new Lazy_Load_Images();

		if ( Options::is_enabled( 'siteground_optimizer_lazyload_iframes' ) ) {
			new Lazy_Load_Iframes();
		}

		if ( Options::is_enabled( 'siteground_optimizer_lazyload_videos' ) ) {
			new Lazy_Load_Videos();
		}

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

	}

	/**
	 * Load the scripts.
	 *
	 * @since  5.0.0
	 */
	public function load_scripts() {
		// Load the main script.
		wp_enqueue_script(
			'siteground-optimizer-lazy-sizes-js',
			\SiteGround_Optimizer\URL . '/assets/js/lazysizes.min.js',
			array( 'jquery' ), // Dependencies.
			\SiteGround_Optimizer\VERSION,
			true
		);
	}
}
