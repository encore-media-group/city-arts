<?php
namespace SiteGround_Optimizer\Helper;

use SiteGround_Optimizer;
use SiteGround_Optimizer\Admin\Admin;
use SiteGround_Optimizer\Rest\Rest;
use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Supercacher\Supercacher_Helper;
use SiteGround_Optimizer\Install_Service\Install_Service;
use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Php_Checker\Php_Checker;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Cli\Cli;
use SiteGround_Optimizer\Config\Config;

/**
 * Helper functions and main initialization class.
 */
class Helper {

	/**
	 * Create a new helper.
	 */
	public function __construct() {
		// Load the plugin textdomain.
		add_action( 'after_setup_theme', array( $this, 'load_textdomain' ), 9999 );
		add_action( 'init', array( new Install_Service(), 'install' ) );
		add_action( 'plugins_loaded', array( $this, 'is_plugin_installed' ) );
		add_action( 'init', array( $this, 'hide_warnings_in_rest_api' ) );
		add_filter( 'xmlrpc_login_error', array( $this, 'filter_xmlrpc_login_error' ), 10, 2 );

		set_error_handler( array( $this, 'error_handler' ) );

		// Run the plugin functionality.
		$this->run();
	}

	/**
	 * Run the plugin functionality.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		// Initialize dashboard page.
		new Admin();

		// Initialize the rest api endpoints.
		new Rest();

		// Init the supercacher.
		$this->supercacher = new Supercacher();

		// Init the memcacher.
		new Memcache();

		// Init and run the helper class that set cache headers and bypass cookies.
		new Supercacher_Helper();

		// Init the php version checker.
		new Php_Checker();

		// Init the main class responsible for front-end optionmization.
		new Front_End_Optimization();

		// Init the CLI commands.
		new Cli();

		// Init the config class.
		new Config();
	}

	/**
	 * Load the plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'sg-cachepress',
			false,
			'sg-cachepress/languages'
		);
	}

	/**
	 * Check if the plugin is installed.
	 *
	 * @since  5.0.0
	 */
	public function is_plugin_installed() {
		if (
			isset( $_GET['sgCacheCheck'] ) &&
			md5( 'wpCheck' ) === $_GET['sgCacheCheck']
		) {
			die( 'OK' );
		}
	}

	/**
	 * Load the global wp_filesystem.
	 *
	 * @since  5.0.0
	 *
	 * @return object The {@link Siteground_Migrator_Api_Service} instance.
	 */
	public static function setup_wp_filesystem() {
		global $wp_filesystem;

		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		return $wp_filesystem;
	}


	/**
	 * Check if wp cron is disabled and send error message.
	 *
	 * @since  5.0.0
	 */
	public static function is_cron_disabled() {
		if ( defined( 'DISABLE_WP_CRON' ) && true == DISABLE_WP_CRON ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Get i18n strings as a JSON-encoded string
	 *
	 * @since 5.0.0
	 *
	 * @return string The locale as JSON
	 */
	public static function get_i18n_data_json() {
		$i18n_json = ABSPATH . 'wp-content/plugins/sg-cachepress/languages/json/sg-cachepress-' . get_user_locale() . '.json';

		if ( is_file( $i18n_json ) && is_readable( $i18n_json ) ) {

			$locale_data = @file_get_contents( $i18n_json );
			if ( $locale_data ) {
				return $locale_data;
			}
		}

		// Return valid empty Jed locale.
		return json_encode(
			array(
				'' => array(
					'domain' => 'sg-cachepress',
					'lang'   => is_admin() ? get_user_locale() : get_locale(),
				),
			)
		);
	}

	/**
	 * Hide warnings in rest api.
	 *
	 * @since  5.0.0
	 */
	public function hide_warnings_in_rest_api() {
		if ( self::is_rest() ) {
			error_reporting( E_ERROR | E_PARSE );
		}
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @since 5.0.0
	 *
	 * @return bool True if it's rest request, false otherwise.
	 */
	public static function is_rest() {
		$prefix = rest_get_url_prefix();

		if (
			defined( 'REST_REQUEST' ) && REST_REQUEST ||
			(
				isset( $_GET['rest_route'] ) &&
				0 === strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 )
			)
		) {
			return true;
		}

		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );

		return 0 === @strpos( $current_url['path'], $rest_url['path'], 0 );
	}

	/**
	 * Our custom error handler
	 *
	 * @since 5.0.8
	 *
	 * @param int    $errno        The first parameter, errno, contains the level of the error raised.
	 * @param string $errstr    The second parameter, errstr, contains the error message.
	 * @param string $errfile   The third parameter is optional, errfile, which contains the
	 *                          filename that the error was raised in.
	 * @param int    $errline      The fourth parameter is optional, errline, which contains the line
	 *                             number the error was raised at.
	 * @param array  $errcontext The fifth parameter is optional, errcontext that contains an array
	 *                           of every variable that existed in the scope the error was triggered
	 *                           in. User error handler must not modify error context.
	 * @return bool             True if error is within /plugins, false otherwise.
	 */
	public function error_handler( $errno, $errstr, $errfile, $errline, $errcontext = array() ) {
		// Path to error file.
		$error_file = str_replace( '\\', '/', $errfile );

		// Path to plugins.
		$vendor = str_replace( '\\', '/', SiteGround_Optimizer\DIR . '/vendor' );

		// Do nothing for errors inside of the plugins directory.
		if ( strpos( $error_file, $vendor ) !== false ) {
			return true;
		}

		// Default error handler otherwise.
		return false;
	}

	/**
	 * Some plugins like WPML for example are overwriting the home url.
	 *
	 * @since  5.0.10
	 *
	 * @return string The real home url.
	 */
	public static function get_home_url() {
		$url = get_option( 'home' );

		$scheme = is_ssl() ? 'https' : parse_url( $url, PHP_URL_SCHEME );

		$url = set_url_scheme( $url, $scheme );

		return trailingslashit( $url );
	}

	/**
	 * Send notification to SiteGround on login error
	 *
	 * @since  5.2.4
	 *
	 * @param  string $this_error The XML-RPC error message.
	 * @param  object $user       WP_User object.
	 *
	 * @return string             The XML-RPC error message.
	 */
	public function filter_xmlrpc_login_error( $this_error, $user ) {
		if ( function_exists( 'c74ce9b9ffdebe0331d8e43e97206424_notify' ) ) {
			c74ce9b9ffdebe0331d8e43e97206424_notify( 'wpxmlrpc', getcwd(), 'UNKNOWN' );
		}

		return $this_error;
	}

	/**
	 * Checks if the plugin run on the new SiteGround interface.
	 *
	 * @since  5.3.0
	 *
	 * @return boolean True/False.
	 */
	public static function is_avalon() {
		$response = shell_exec( 'test -f /etc/yum.repos.d/baseos.repo && echo Avalon' );

		// The post for our new server is aloways the same.
		if ( 'Avalon' === trim( $response ) ) {
			return 1;
		}

		return 0;
	}
}
