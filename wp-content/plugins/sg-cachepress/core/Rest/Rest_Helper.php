<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Php_Checker\Php_Checker;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Multisite\Multisite;
use SiteGround_Optimizer\Images_Optimizer\Images_Optimizer;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Analysis\Analysis;

/**
 * Rest Helper class that process all rest requests and provide json output for react app.
 */
class Rest_Helper {
	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->checker          = new Php_Checker();
		$this->options          = new Options();
		$this->memcache         = new Memcache();
		$this->ssl              = new Ssl();
		$this->htaccess         = new Htaccess();
		$this->multisite        = new Multisite();
	}

	/**
	 * Update excluded urls.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function update_excluded_urls( $request ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the urls are not set.
		if ( ! isset( $data['excluded_urls'] ) ) {
			wp_send_json_error();
		}

		// Convert the json urls to array.
		$urls = json_decode( $data['excluded_urls'], true );

		// Update the option.
		$result = update_option( 'siteground_optimizer_excluded_urls', $urls );

		// Purge the cache.
		Supercacher::purge_cache();

		wp_send_json(
			array(
				'success' => $result,
				'data'    => $urls,
			)
		);
	}

	/**
	 * Initialize images optimization
	 *
	 * @since  5.0.0
	 */
	public function optimize_images() {
		$this->images_optimizer = new Images_Optimizer();
		$this->images_optimizer->initialize();

		wp_send_json_success(
			array(
				'image_optimization_status'   => 0,
				'image_optimization_stopped'  => 0,
				'has_images_for_optimization' => get_option( 'siteground_optimizer_total_unoptimized_images', 0 ),
				'total_unoptimized_images'    => get_option( 'siteground_optimizer_total_unoptimized_images', 0 ),
			)
		);
	}

	/**
	 * Checks if the option key exists.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function enable_option_from_rest( $request ) {
		// Get the option key.
		$key        = $this->validate_and_get_option_value( $request, 'option_key' );
		$is_network = $this->validate_and_get_option_value( $request, 'is_multisite', false );
		$result     = Options::enable_option( $key, $is_network );

		// Enable the option.
		wp_send_json(
			array(
				'success' => $result,
				'data' => array(
					'message' => $this->options->get_response_message( $result, $key, true ),
				),
			)
		);
	}

	/**
	 * Checks if the option key exists.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 *
	 * @return string The option key.
	 */
	public function disable_option_from_rest( $request ) {
		// Get the option key.
		$key        = $this->validate_and_get_option_value( $request, 'option_key' );
		$is_network = $this->validate_and_get_option_value( $request, 'is_multisite', false );
		$result     = Options::disable_option( $key, $is_network );

		// Disable the option.
		return wp_send_json(
			array(
				'success' => $result,
				'data' => array(
					'message' => $this->options->get_response_message( $result, $key, false ),
				),
			)
		);
	}

	/**
	 * Checks if the `option_key` paramether exists in rest data.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 * @param  string $key     The option key.
	 * @param  bool   $bail    Whether to send json error or to return a response.
	 *
	 * @return string          The option value.
	 */
	private function validate_and_get_option_value( $request, $key, $bail = true ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the option key is not set.
		if ( ! isset( $data[ $key ] ) ) {
			return true === $bail ? wp_send_json_error() : false;
		}

		return $data[ $key ];
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  5.0.0
	 */
	public function fetch_options() {
		// Fetch the options.
		$options = $this->options->fetch_options();

		if ( is_multisite() ) {
			$options['sites_data'] = $this->multisite->get_sites_info();
		}

		$options['has_images']                  = $this->options->check_for_images();
		$options['has_images_for_optimization'] = $this->options->check_for_unoptimized_images();
		$options['has_images_for_conversion']   = $this->options->check_for_non_converted_images();
		$options['assets']                      = Front_End_Optimization::get_instance()->get_assets();

		// Send the options to react app.
		wp_send_json_success( $options );
	}

	/**
	 * Purge the cache and send json response
	 *
	 * @since  5.0.0
	 */
	public function purge_cache_from_rest() {
		Supercacher::purge_cache();
		// Disable the option.
		wp_send_json_success();
	}

	/**
	 * Test if url is cached.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function test_cache( $request ) {
		// Get the url.
		$url = $this->validate_and_get_option_value( $request, 'url' );

		// Check if the url is cached.
		$is_cached = Supercacher::test_cache( $url );
		// Send response to the app.
		wp_send_json_success( array( 'cached' => $is_cached ) );
	}

	/**
	 * Enable memcached.
	 *
	 * @since  5.0.0
	 */
	public function enable_memcache() {
		$port = $this->memcache->get_memcached_port();

		if ( empty( $port ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'SG Optimizer was unable to connect to the Memcached server and it was disabled. Please, check your SiteGround control panel and turn it on if disabled.', 'sg-cachepress' ),
				)
			);
		}

		// First enable the option.
		$result = Options::enable_option( 'siteground_optimizer_enable_memcached' );

		// Send success if the dropin has been created.
		if ( $result && $this->memcache->create_memcached_dropin() ) {
			wp_send_json_success(
				array(
					'message' => __( 'Memcached Enabled', 'sg-cachepress' ),
				)
			);
		} else {
			if ( 11211 === $port ) {
				wp_send_json_error(
					array(
						'message' => __( 'SG Optimizer was unable to connect to the Memcached server and it was disabled. Please, check your SiteGround control panel and turn it on if disabled.', 'sg-cachepress' ),
					)
				);
			}
		}

		// Dropin cannot be created.
		wp_send_json_error(
			array(
				'message' => __( 'Could Not Enable Memcache!', 'sg-cachepress' ),
			)
		);
	}

	/**
	 * Disable memcached.
	 *
	 * @since  5.0.0
	 */
	public function disable_memcache() {
		// First disable the option.
		$result = Options::disable_option( 'siteground_optimizer_enable_memcached' );

		// Send success if the option has been disabled and the dropin doesn't exist.
		if ( ! $this->memcache->dropin_exists() ) {
			wp_send_json_success(
				array(
					'message' => __( 'Memcache Disabled!', 'sg-cachepress' ),
				)
			);
		}

		// Try to remove the dropin.
		$is_dropin_removed = $this->memcache->remove_memcached_dropin();

		// Send success if the droping has been removed.
		if ( $is_dropin_removed ) {
			wp_send_json_success(
				array(
					'message' => __( 'Memcache Disabled!', 'sg-cachepress' ),
				)
			);
		}

		// The dropin cannot be removed.
		wp_send_json_error(
			array(
				'message' => __( 'Could Not Disable Memcache!', 'sg-cachepress' ),
			)
		);
	}

	/**
	 * Enable the ssl
	 *
	 * @param  object $request Request data.
	 *
	 * @since  5.0.0
	 */
	public function enable_ssl( $request ) {
		$key    = $this->validate_and_get_option_value( $request, 'option_key' );
		// Bail if the domain doens't nove ssl certificate.
		if ( ! $this->ssl->has_certificate() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please, install an SSL certificate first!', 'sg-cachepress' ),
				)
			);
		}

		$result = $this->ssl->enable();

		wp_send_json(
			array(
				'success' => $result,
				'data' => array(
					'message' => $this->options->get_response_message( $result, $key, true ),
				),
			)
		);
	}

	/**
	 * Disable the ssl.
	 *
	 * @param  object $request Request data.
	 *
	 * @since  5.0.0
	 */
	public function disable_ssl( $request ) {
		$key    = $this->validate_and_get_option_value( $request, 'option_key' );
		$result = $this->ssl->disable();

		wp_send_json(
			array(
				'success' => $result,
				'data' => array(
					'message' => $this->options->get_response_message( $result, $key, false ),
				),
			)
		);
	}

	/**
	 * Handle compatibility check.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function handle_compatibility_check( $request ) {
		// Get the php version.
		$php_version = $this->validate_and_get_option_value( $request, 'php_version' );

		// Add the background processes.
		$this->checker->initialize( $php_version );
		// Send successful response.
		wp_send_json_success();
	}

	/**
	 * Switch the current php version.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function switch_php( $request ) {
		// Get the php version.
		$php_version = $this->validate_and_get_option_value( $request, 'php_version' );

		$php_versions_request = wp_remote_get( 'https://updates.sgvps.net/supported-versions.json' );
		$php_versions = json_decode( wp_remote_retrieve_body( $php_versions_request ), true );
		// Add the new recommended php version.
		$php_versions['versions'][] = 'recommended-php';

		if ( ! in_array( $php_version, $php_versions['versions'], false ) ) {
			wp_send_json(
				array(
					'success' => false,
					'data'    => array(
						'message' => __( 'Cannot change PHP Version', 'sg-cachepress' ),
					),
				)
			);
		}

		$this->htaccess->disable( 'php' );
		$result = $this->htaccess->enable(
			'php',
			array(
				'search'  => 'recommended-php' === $php_version ? 'php_PHPVERSION_' : '_PHPVERSION_',
				'replace' => str_replace( '.', '', $php_version ),
			)
		);

		// Reset the compatibility.
		$this->checker->complete();
		update_option( 'siteground_optimizer_phpcompat_is_compatible', 0 );
		update_option( 'siteground_optimizer_phpcompat_result', array() );

		wp_send_json(
			array(
				'success' => $result,
				'data'    => array(
					'message' => $result ? __( 'PHP Version has been changed', 'sg-cachepress' ) : __( 'Cannot change PHP Version', 'sg-cachepress' ),
				),
			)
		);
	}

	/**
	 * Return the status of current compatibility check.
	 *
	 * @since  5.0.0
	 */
	public function handle_compatibility_status_check() {
		wp_send_json_success(
			array(
				'phpcompat_status'        => (int) get_option( 'siteground_optimizer_phpcompat_status', 0 ),
				'phpcompat_progress'      => (int) get_option( 'siteground_optimizer_phpcompat_progress', 1 ),
				'phpcompat_is_compatible' => (int) get_option( 'siteground_optimizer_phpcompat_is_compatible', 0 ),
				'phpcompat_result'        => get_option( 'siteground_optimizer_phpcompat_result' ),
			)
		);
	}

	/**
	 * Stops images optimization
	 *
	 * @since  5.0.8
	 */
	public function stop_images_optimization() {
		// Clear the scheduled cron after the optimization is completed.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_image_optimization_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_image_optimization_completed', 1, false );
		update_option( 'siteground_optimizer_image_optimization_status', 1, false );
		update_option( 'siteground_optimizer_image_optimization_stopped', 1, false );

		// Delete the lock.
		delete_option( 'siteground_optimizer_image_optimization_lock' );

		wp_send_json_success(
			array(
				'image_optimization_status'   => 1,
				'image_optimization_stopped'  => 1,
				'has_images_for_optimization' => $this->options->check_for_unoptimized_images(),
			)
		);
	}

	/**
	 * Return the status of current compatibility check.
	 *
	 * @since  5.0.0
	 */
	public function check_image_optimizing_status() {
		$unoptimized_images = $this->options->check_for_unoptimized_images();

		if ( 0 === $unoptimized_images ) {
			Images_Optimizer::complete();
		}

		$status = (int) get_option( 'siteground_optimizer_image_optimization_completed', 0 );

		wp_send_json_success(
			array(
				'image_optimization_status'   => $status,
				'has_images_for_optimization' => $unoptimized_images,
				'total_unoptimized_images'    => (int) get_option( 'siteground_optimizer_total_unoptimized_images' ),
			)
		);
	}

	/**
	 * Enable specific optimizations for a blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function enable_multisite_optimization( $request ) {
		$setting = $this->validate_and_get_option_value( $request, 'setting' );
		$blog_id = $this->validate_and_get_option_value( $request, 'blog_id' );

		foreach ( $blog_id as $id ) {
			$result = call_user_func( array( $this->multisite, $setting ), $id );
		}

		// Purge the cache.
		Supercacher::purge_cache();

		wp_send_json(
			array(
				'success' => $result,
			)
		);
	}

	/**
	 * Disable specific optimizations for a blog.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function disable_multisite_optimization( $request ) {
		$setting = $this->validate_and_get_option_value( $request, 'setting' );
		$blog_id = $this->validate_and_get_option_value( $request, 'blog_id' );

		foreach ( $blog_id as $id ) {
			$result = call_user_func( array( $this->multisite, $setting ), $id );
		}

		// Purge the cache.
		Supercacher::purge_cache();

		wp_send_json(
			array(
				'success' => $result,
			)
		);
	}

	/**
	 * Deletes images meta_key flag to allow reoptimization.
	 *
	 * @since  5.0.0
	 */
	public function reset_images_optimization() {
		Images_Optimizer::reset_image_optimization_status();

		wp_send_json_success();
	}

	/**
	 * Hide the rating box
	 *
	 * @since  5.0.12
	 */
	public function handle_hide_rating() {
		update_option( 'siteground_optimizer_hide_rating', 1 );
		update_site_option( 'siteground_optimizer_hide_rating', 1 );

		wp_send_json_success();
	}

	/**
	 * Update exclude list.
	 *
	 * @since  5.2.0
	 *
	 * @param  object $request Request data.
	 */
	public function update_exclude_list( $request ) {
		// List of predefined exclude lists.
		$exclude_lists = array(
			'minify_javascript_exclude',
			'async_javascript_exclude',
			'minify_css_exclude',
			'minify_html_exclude',
			'excluded_lazy_load_classes',
			'combine_css_exclude',
		);

		// Get the type and handles data from the request.
		$type   = $this->validate_and_get_option_value( $request, 'type' );
		$handle = $this->validate_and_get_option_value( $request, 'handle' );

		// Bail if the type is not listed in the predefined exclude list.
		if ( ! in_array( $type, $exclude_lists ) ) {
			wp_send_json_error();
		}

		$handles = get_option( 'siteground_optimizer_' . $type, array() );
		$key     = array_search( $handle, $handles );

		if ( false === $key ) {
			array_push( $handles, $handle );
		} else {
			unset( $handles[ $key ] );
		}

		$handles = array_values( $handles );

		if ( in_array( $type, array( 'minify_html_exclude', 'excluded_lazy_load_classes' ) ) ) {
			$handles = $handle;
		}

		// Update the option.
		$result = update_option( 'siteground_optimizer_' . $type, $handles );

		// Purge the cache.
		Supercacher::purge_cache();

		// Send response to the react app.
		wp_send_json(
			array(
				'success' => $result,
				'handles' => $handles,
			)
		);
	}


	/**
	 * Disable specific optimizations for a blog.
	 *
	 * @since  5.4.0
	 *
	 * @param  object $request Request data.
	 */
	public function run_analysis( $request ) {

		// Get the required params.
		$device = $this->validate_and_get_option_value( $request, 'device' );
		$url    = $this->validate_and_get_option_value( $request, 'url', false );

		// Bail if any of the parameters is empty.
		if ( empty( $device ) ) {
			wp_send_json_error();
		}

		$analysis = new Analysis();
		$result = $analysis->run_analysis_rest( $url, $device );

		// Send the response.
		wp_send_json_success( $result );
	}

}
