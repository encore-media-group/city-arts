<?php
namespace SiteGround_Optimizer\Config;

use SiteGround_Optimizer;
use SiteGround_Optimizer\Helper\Helper;

/**
 * Config functions and main initialization class.
 */
class Config {

	/**
	 * List of all optimization that we want to keep in the config.
	 *
	 * @since 5.3.6
	 *
	 * @access public
	 *
	 * @var array $main_options List of all options.
	 */
	public $main_options = array(
		'version',
		'enable_cache',
		'autoflush_cache',
		'user_agent_header',
		'enable_memcached',
		'ssl_enabled',
		'fix_insecure_content',
		'enable_gzip_compression',
		'enable_browser_caching',
		'optimize_html',
		'optimize_javascript',
		'optimize_javascript_async',
		'optimize_css',
		'combine_css',
		'combine_google_fonts',
		'remove_query_strings',
		'disable_emojis',
		'optimize_images',
		'lazyload_images',
		'lazyload_gravatars',
		'lazyload_thumbnails',
		'lazyload_responsive',
		'lazyload_textwidgets',
		'lazyload_mobile',
		'lazyload_woocommerce',
	);

	/**
	 * The config filename.
	 *
	 * @var string
	 */
	public $config_name = '/sg-config.json';

	/**
	 * WordPress filesystem.
	 *
	 * @since 5.3.6
	 *
	 * @var object|null WordPress filesystem.
	 */
	public $wp_filesystem = null;

	/**
	 * Create a new helper.
	 */
	public function __construct() {

		// Setup wp filesystem.
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper::setup_wp_filesystem();
		}

		add_action( 'wp_login', array( $this, 'update_config' ) );
	}

	/**
	 * Create the config.
	 *
	 * @since  5.3.6
	 *
	 * @return bool True if the config exists or if it was successfully create, false otherwise.
	 */
	public function create_config() {
		// Build the config path.
		$filename = SiteGround_Optimizer\DIR . $this->config_name;

		// Bail if the config already exists.
		if ( $this->wp_filesystem->exists( $filename ) ) {
			return true;
		}

		// Create the config.
		return $this->wp_filesystem->touch( $filename );
	}

	/**
	 * Update the config.
	 *
	 * @since  5.3.6
	 */
	public function update_config() {
		// Bail if we are unable to create the config file.
		if ( false === $this->create_config() ) {
			return;
		}

		// Build the config from database.
		$content = $this->build_config_content();

		// Add the new content into the file.
		$this->wp_filesystem->put_contents(
			SiteGround_Optimizer\DIR . $this->config_name,
			json_encode( $content )
		);
	}

	/**
	 * Build the default config content using the option values from database.
	 *
	 * @since  5.3.6
	 *
	 * @return array The config content.
	 */
	public function build_config_content() {
		// Init the data array.
		$data = array();

		// Loop through all options and add the value to the data array.
		foreach ( $this->main_options as $option ) {
			// Get the optin value.
			$value = get_option( 'siteground_optimizer_' . $option, 0 );
			// Add the value to database. Only the plugin version needs to be a string.
			$data[ $option ] = 'version' === $option ? $value : intval( $value );
		}

		// Return the data.
		return $data;
	}
}
