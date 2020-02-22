<?php
namespace SiteGround_Optimizer\Minifier;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Optimizer\Supercacher\Supercacher;
/**
 * SG Minifier main plugin class
 */
class Minifier {
	/**
	 * WordPress filesystem.
	 *
	 * @since 5.0.0
	 *
	 * @var object|null WordPress filesystem.
	 */
	private $wp_filesystem = null;

	/**
	 * The dir where the minified styles and scripts will be saved.
	 *
	 * @since 5.0.0
	 *
	 * @var string|null Path to assets dir.
	 */
	private $assets_dir = null;

	/**
	 * Javascript files that should be ignored.
	 *
	 * @since 5.0.0
	 *
	 * @var array Array of all js files that should be ignored.
	 */
	private $js_ignore_list = array(
		'jquery',
		'jquery-core',
		'ai1ec_requirejs',
	);

	/**
	 * Stylesheet files that should be ignored.
	 *
	 * @since 5.0.0
	 *
	 * @var array Array of all css files that should be ignored.
	 */
	private $css_ignore_list = array();

	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Minifier The singleton instance.
	 */
	private static $instance;

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		// Bail if it's admin page.
		if ( is_admin() ) {
			return;
		}
		// Setup wp filesystem.
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper::setup_wp_filesystem();
		}

		$this->assets_dir = Front_End_Optimization::get_instance()->assets_dir;

		self::$instance = $this;

		if (
			Options::is_enabled( 'siteground_optimizer_optimize_html' ) &&
			! defined( 'WP_CLI' )
		) {
			// Add the hooks that we will use t ominify the html.
			add_action( 'init', array( $this, 'start_html_minifier_buffer' ) );
			add_action( 'shutdown', array( $this, 'end_html_minifier_buffer' ) );
		}

		if ( Options::is_enabled( 'siteground_optimizer_optimize_javascript' ) ) {
			// Minify the js files.
			add_action( 'wp_print_scripts', array( $this, 'minify_scripts' ), PHP_INT_MAX - 1 );
			add_action( 'wp_print_footer_scripts', array( $this, 'minify_scripts' ), 9.999999 );
		}

		if ( Options::is_enabled( 'siteground_optimizer_optimize_css' ) ) {
			// Minify the css files.
			add_action( 'wp_print_styles', array( $this, 'minify_styles' ), 11 );
			add_action( 'wp_print_footer_scripts', array( $this, 'minify_styles' ), 11 );
		}

		$this->js_ignore_list = array_merge(
			$this->js_ignore_list,
			get_option( 'siteground_optimizer_minify_javascript_exclude', array() )
		);

		$this->css_ignore_list = array_merge(
			$this->css_ignore_list,
			get_option( 'siteground_optimizer_minify_css_exclude', array() )
		);
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.1.0
	 *
	 * @return \Minifier The singleton instance.
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Minify scripts included in footer and header.
	 *
	 * @since  5.0.0
	 */
	public function minify_scripts() {
		global $wp_scripts;

		// Bail if the scripts object is empty.
		if ( ! is_object( $wp_scripts ) || null === $this->assets_dir ) {
			return;
		}

		$scripts = wp_clone( $wp_scripts );
		$scripts->all_deps( $scripts->queue );

		$excluded_scripts = apply_filters( 'sgo_js_minify_exclude', $this->js_ignore_list );

		// Get groups of handles.
		foreach ( $scripts->to_do as $handle ) {
			// Skip scripts.
			if (
				stripos( $wp_scripts->registered[ $handle ]->src, '.min.js' ) !== false || // If the file is minified already.
				false === $wp_scripts->registered[ $handle ]->src || // If the source is empty.
				in_array( $handle, $excluded_scripts ) || // If the file is ignored.
				@strpos( Helper::get_home_url(), parse_url( $wp_scripts->registered[ $handle ]->src, PHP_URL_HOST ) ) === false // Skip all external sources.
			) {
				continue;
			}

			$original_filepath = Front_End_Optimization::get_original_filepath( $wp_scripts->registered[ $handle ]->src );

			// Build the minified version filename.
			$filename = $this->assets_dir . $handle . '.min.js';

			// Check for original file modifications and create the minified copy.
			$is_minified_file_ok = $this->check_and_create_file( $filename, $original_filepath );

			// Check that everythign with minified file is ok.
			if ( $is_minified_file_ok ) {
				// Replace the script src with the minified version.
				$wp_scripts->registered[ $handle ]->src = str_replace( ABSPATH, Helper::get_home_url(), $filename );
			}
		}
	}

	/**
	 * Check if the original file is modified and create minified version.
	 * It will create minified version if the new file doesn't exists.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $new_file_path     The new filename.
	 * @param  string $original_filepath The original file.
	 *
	 * @return bool             True if the file is created, false on failure.
	 */
	private function check_and_create_file( $new_file_path, $original_filepath ) {
		// Bail if the original file doesn't exists.
		if ( ! is_file( $original_filepath ) ) {
			return false;
		}

		// First remove the query strings.
		$original_filepath = Front_End_Optimization::remove_query_strings( preg_replace( '/\?.*/', '', $original_filepath ) );
		$new_file_path     = Front_End_Optimization::remove_query_strings( preg_replace( '/\?.*/', '', $new_file_path ) );

		// Gets file modification time.
		$original_file_timestamp = file_exists( $original_filepath ) ? filemtime( $original_filepath ) : true;
		$minified_file_timestamp = file_exists( $new_file_path ) ? filemtime( $new_file_path ) : false;

		// Compare the original and new file timestamps.
		// This check will fail if the minified file doens't exists
		// and it will be created in the code below.
		if ( $original_file_timestamp === $minified_file_timestamp ) {
			return true;
		}

		// The minified file doens't exists or the original file has been modified.
		// Minify the file then.
		exec(
			sprintf(
				'minify %s --output=%s',
				$original_filepath,
				$new_file_path
			),
			$output,
			$status
		);

		// Return false if the minification fails.
		if ( 1 === intval( $status ) || ! file_exists( $new_file_path ) ) {
			return false;
		}

		// Set the minified file last modification file equla to original file.
		$this->wp_filesystem->touch( $new_file_path, $original_file_timestamp );

		// Flush the cache for our new resource.
		$new_file_url = str_replace( untrailingslashit( ABSPATH ), get_option( 'home' ), dirname( $new_file_path ) );
		Supercacher::get_instance()->purge_cache_request( $new_file_url );

		return true;

	}

	/**
	 * Minify styles included in header and footer
	 *
	 * @since  5.0.0
	 */
	public function minify_styles() {
		global $wp_styles;

		// Bail if the scripts object is empty.
		if ( ! is_object( $wp_styles ) || null === $this->assets_dir ) {
			return;
		}

		$styles = wp_clone( $wp_styles );
		$styles->all_deps( $styles->queue );

		$excluded_styles = apply_filters( 'sgo_css_minify_exclude', $this->css_ignore_list );

		// Get groups of handles.
		foreach ( $styles->to_do as $handle ) {
			// Skip styles.
			if (
				stripos( $wp_styles->registered[ $handle ]->src, '.min.css' ) !== false || // If the file is minified already.
				false === $wp_styles->registered[ $handle ]->src || // If the source is empty.
				in_array( $handle, $excluded_styles ) || // If the file is ignored.
				@strpos( Helper::get_home_url(), parse_url( $wp_styles->registered[ $handle ]->src, PHP_URL_HOST ) ) === false // Skip all external sources.
			) {
				continue;
			}

			$original_filepath = Front_End_Optimization::get_original_filepath( $wp_styles->registered[ $handle ]->src );

			$parsed_url = parse_url( $wp_styles->registered[ $handle ]->src );

			// Build the minified version filename.
			$filename = dirname( $original_filepath ) . '/' . $wp_styles->registered[ $handle ]->handle . '.min.css';

			if ( ! empty( $parsed_url['query'] ) ) {
				$filename = $filename . '?' . $parsed_url['query'];
			}

			// Check for original file modifications and create the minified copy.
			$is_minified_file_ok = $this->check_and_create_file( $filename, $original_filepath );

			// Check that everythign with minified file is ok.
			if ( $is_minified_file_ok ) {
				// Replace the script src with the minified version.
				$wp_styles->registered[ $handle ]->src = str_replace( ABSPATH, Helper::get_home_url(), $filename );
			}
		}
	}

	/**
	 * Minify the html output.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $buffer The page content.
	 *
	 * @return string         Minified content.
	 */
	public function minify_html( $buffer ) {
		$content = Minify_Html::minify( $buffer );
		return $content;
	}

	/**
	 * Start buffer.
	 *
	 * @since  5.0.0
	 */
	public function start_html_minifier_buffer() {
		// Do not minify the html if the current url is excluded.
		if ( $this->is_url_excluded() ) {
			return;
		}

		ob_start( array( $this, 'minify_html' ) );
	}

	/**
	 * End the buffer.
	 *
	 * @since  5.0.0
	 */
	public function end_html_minifier_buffer() {
		if ( ob_get_length() ) {
			ob_end_flush();
		}
	}

	/**
	 * Check if the current url has params that are excluded.
	 *
	 * @since  5.1.0
	 *
	 * @return boolean True if the url is excluded, false otherwise.
	 */
	public function is_url_excluded() {
		$protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';

		// Build the current url.
		$url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Get excluded urls.
		$excluded_urls = apply_filters( 'sgo_html_minify_exclude_urls', get_option( 'siteground_optimizer_minify_html_exclude', array() ) );

		// Prepare the url parts for being used as regex.
		$prepared_parts = array_map(
			function( $item ) {
				return str_replace( '\*', '.*', preg_quote( str_replace( home_url(), '', $item ), '/' ) );
			}, $excluded_urls
		);

		// Build the regular expression.
		$regex = sprintf(
			'/%s(%s)$/i',
			preg_quote( home_url(), '/' ), // Add the home url in the beginning of the regex.
			implode( '|', $prepared_parts ) // Then add each part.
		);

		// Check if the current url matches any of the excluded urls.
		preg_match( $regex, $url, $matches );

		// The url is excluded if matched the regular expression.
		if ( ! empty( $matches ) ) {
			return true;
		}

		// If there are no params we don't need to check the query params.
		if ( ! isset( $_REQUEST ) ) {
			return false;
		}

		// Get excluded params.
		$excluded_params = apply_filters( 'sgo_html_minify_exclude_params', array( 'pdf-catalog', 'tve' ) );

		// Check if any of the excluded params exists in the request.
		foreach ( $excluded_params as $param ) {
			if ( array_key_exists( $param, $_REQUEST ) ) {
				return true;
			}
		}

		return false;
	}
}
