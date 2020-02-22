<?php
namespace SiteGround_Optimizer\Supercacher;

use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
/**
 * SG CachePress main plugin class
 */
class Supercacher {

	/**
	 * Child classes that have to be initialized.
	 *
	 * @var array
	 *
	 * @since 5.0.0
	 */
	public static $children = array(
		'themes',
		'plugins',
		'posts',
		'terms',
		'comments',
		// 'postmeta',
	);

	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Supercacher The singleton instance.
	 */
	private static $instance;

	/**
	 * Create a {@link Supercacher} instance.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		self::$instance = $this;

		// Run the supercachers if the autoflush is enabled.
		if ( 1 === (int) get_option( 'siteground_optimizer_autoflush_cache', 0 ) ) {
			$this->run();
		}
	}

	/**
	 * Run the hooks when we have to purge everything.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		add_action( 'automatic_updates_complete', array( $this, 'purge_everything' ) );
		add_action( '_core_updated_successfully', array( $this, 'purge_everything' ) );
		add_action( 'update_option_permalink_structure', array( $this, 'purge_everything' ) );
		add_action( 'update_option_tag_base', array( $this, 'purge_everything' ) );
		add_action( 'update_option_category_base', array( $this, 'purge_everything' ) );
		add_action( 'wp_update_nav_menu', array( $this, 'purge_everything' ) );
		add_action( 'wp_ajax_widgets-order', array( $this, 'purge_everything' ), 1 );
		add_action( 'wp_ajax_save-widget', array( $this, 'purge_everything' ), 1 );
		add_action( 'woocommerce_create_refund', array( $this, 'purge_everything' ), 1 );
		add_action( 'wp_ajax_delete-selected', array( $this, 'purge_everything' ), 1 );
		add_action( 'wp_ajax_edit-theme-plugin-file', array( $this, 'purge_everything' ), 1 );
		add_action( 'update_option_siteground_optimizer_enable_cache', array( $this, 'purge_everything' ) );
		add_action( 'update_option_siteground_optimizer_autoflush_cache', array( $this, 'purge_everything' ) );
		add_action( 'update_option_siteground_optimizer_enable_memcached', array( $this, 'purge_everything' ) );
		add_action( 'update_option_siteground_optimizer_combine_css', array( $this, 'delete_assets' ) );

		// Delete assets (minified js and css files) every 30 days.
		add_action( 'siteground_delete_assets', array( $this, 'delete_assets' ) );
		add_filter( 'cron_schedules', array( $this, 'add_siteground_cron_schedule' ) );

		// Schedule a cron job that will delete all assets (minified js and css files) every 30 days.
		if ( ! wp_next_scheduled( 'siteground_delete_assets' ) ) {
			wp_schedule_event( time(), 'siteground_monthly', 'siteground_delete_assets' );
		}

		$this->purge_on_other_events();
		$this->purge_on_options_save();

		$this->init_cachers();
	}

	/**
	 * Create a new supercacher of type $type
	 *
	 * @since 5.0.0
	 *
	 * @param string $type The type of the supercacher.
	 *
	 * @throws Exception if the type is not supported.
	 */
	public static function factory( $type ) {
		$type = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $type ) ) );

		$class = __NAMESPACE__ . '\\Supercacher_' . $type;

		if ( ! class_exists( $class ) ) {
			throw new \Exception( 'Unknown supercacher type "' . $type . '".' );
		}

		$cacher = new $class();

		$cacher->run();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @return \Supercacher The singleton instance.
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Init supercacher children.
	 *
	 * @since  5.0.0
	 */
	public static function init_cachers() {
		foreach ( self::$children as $child ) {
			self::factory( $child );
		}
	}

	/**
	 * Purge the dynamic cache.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function purge_cache() {
		return Supercacher::get_instance()->purge_everything();
	}

	/**
	 * Purge everything from cache.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function purge_everything() {
		return $this->purge_cache_request( get_home_url( '/' ) );
	}

	/**
	 * Purge index.php from cache.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function purge_index_cache() {
		return $this->purge_cache_request( get_home_url( '/' ), false );
	}

	/**
	 * Perform a delete request.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $url                 The url to purge.
	 * @param  bool   $include_child_paths Whether to purge child paths too.
	 *
	 * @return bool True if the cache is deleted, false otherwise.
	 */
	public static function purge_cache_request( $url, $include_child_paths = true ) {
		// Bail if the url is empty.
		if ( empty( $url ) ) {
			return;
		}

		$hostname            = str_replace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$cache_server_socket = @fsockopen( $hostname, 80, $errno, $errstr, 2 );

		if ( ! $cache_server_socket ) {
			$hostname = '127.0.0.1';
			$cache_server_socket = @fsockopen( $hostname, 80, $errno, $errstr, 2 );
		}

		if ( ! $cache_server_socket ) {
			return false;
		}

		$parsed_url = parse_url( $url );
		$main_path  = parse_url( $url, PHP_URL_PATH );

		if ( empty( $main_path ) ) {
			$main_path = '/';
		}

		// Bail if the url has get params, but it matches the home url.
		// We don't want to purge the entire cache.
		if (
			isset( $parsed_url['query'] ) &&
			parse_url( home_url( '/' ), PHP_URL_PATH ) === $main_path
		) {
			return;
		}

		// Change the regex if we have to delete the child paths.
		if ( true === $include_child_paths ) {
			$main_path .= '(.*)';
		}

		$request = sprintf(
			"PURGE %s HTTP/1.0\r\nHost: %s\r\nConnection: Close\r\n\r\n",
			$main_path,
			$hostname
		);

		fwrite( $cache_server_socket, $request );

		$response = fgets( $cache_server_socket );

		fclose( $cache_server_socket );

		do_action( 'siteground_optimizer_flush_cache', $url );

		return preg_match( '/200/', $response );
	}

	/**
	 * Flush Memcache or Memcached.
	 *
	 * @since 5.0.0
	 */
	public static function flush_memcache() {
		return wp_cache_flush();
	}

	/**
	 * Purge the cache when the options are saved.
	 *
	 * @since  5.0.0
	 */
	private function purge_on_options_save() {

		if (
			isset( $_POST['action'] ) && // WPCS: CSRF ok.
			isset( $_POST['option_page'] ) && // WPCS: CSRF ok.
			'update' === $_POST['action'] // WPCS: CSRF ok.
		) {
			$this->purge_everything();
		}
	}

	/**
	 * Purge the cache for other events.
	 *
	 * @since  5.0.0
	 */
	private function purge_on_other_events() {
		if (
			isset( $_POST['save-header-options'] ) || // WPCS: CSRF ok.
			isset( $_POST['removeheader'] ) || // WPCS: CSRF ok.
			isset( $_POST['skip-cropping'] ) || // WPCS: CSRF ok.
			isset( $_POST['remove-background'] ) || // WPCS: CSRF ok.
			isset( $_POST['save-background-options'] ) || // WPCS: CSRF ok.
			( isset( $_POST['submit'] ) && 'Crop and Publish' == $_POST['submit'] ) || // WPCS: CSRF ok.
			( isset( $_POST['submit'] ) && 'Upload' == $_POST['submit'] ) // WPCS: CSRF ok.
		) {
			$this->purge_everything();
		}
	}

	/**
	 * Check if cache header is enabled for url.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $url           The url to test.
	 * @param  bool   $maybe_dynamic Wheather to make additional request to check the cache again.
	 *
	 * @return bool                  True if the cache is enabled, false otherwise.
	 */
	public static function test_cache( $url, $maybe_dynamic = true ) {
		// Bail if the url is empty.
		if ( empty( $url ) ) {
			return;
		}

		// Add slash at the end of the url.
		$url = trailingslashit( $url );

		// Bail if the url is excluded.
		if ( SuperCacher_Helper::is_url_excluded( $url ) ) {
			return false;
		}

		// Make the request.
		$response = wp_remote_get( $url );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Get response headers.
		$headers = wp_remote_retrieve_headers( $response );

		if ( empty( $headers ) ) {
			return false;
		}

		// Check if the url has a cache header.
		if (
			isset( $headers['x-proxy-cache'] ) &&
			'HIT' === strtoupper( $headers['x-proxy-cache'] )
		) {
			return true;
		}

		if ( $maybe_dynamic ) {
			return self::test_cache( $url, false );
		}

		// The header doesn't exists.
		return false;
	}

	/**
	 * Adds custom cron schdule.
	 *
	 * @since 5.1.0
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 */
	public function add_siteground_cron_schedule( $schedules ) {

		if ( ! array_key_exists( 'siteground_monthly', $schedules ) ) {
			$schedules['siteground_monthly'] = array(
				'interval' => 2635200,
				'display' => __( 'Monthly', 'sg-cachepress' ),
			);
		}
		return $schedules;
	}

	/**
	 * Delete plugin assets
	 *
	 * @since  5.1.0
	 */
	public static function delete_assets() {
		$assets_dir = Front_End_Optimization::get_instance()->assets_dir;
		$files = scandir( $assets_dir );

		foreach ( $files as $filename ) {
			// Build the filepath.
			$maybe_file = trailingslashit( $assets_dir ) . $filename;

			// Bail if the file is not a file.
			if ( ! is_file( $maybe_file ) ) {
				continue;
			}

			// Delete the file.
			unlink( $maybe_file );
		}
	}
}
