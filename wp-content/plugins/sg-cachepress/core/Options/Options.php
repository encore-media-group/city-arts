<?php
namespace SiteGround_Optimizer\Options;

use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Supercacher\Supercacher;

/**
 * Handle PHP compatibility checks.
 */
class Options {

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		$this->htaccess_service = new Htaccess();

		add_filter(
			'pre_update_option_siteground_optimizer_enable_gzip_compression',
			array( $this, 'toogle_gzip_comporession' ),
			10,
			2
		);
		add_filter(
			'pre_update_option_siteground_optimizer_enable_browser_caching',
			array( $this, 'toogle_browser_caching' ),
			10,
			2
		);
	}

	/**
	 * Check if a single boolean setting is enabled.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $key          Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return boolean True if the setting is enabled, false otherwise.
	 */
	public static function is_enabled( $key, $is_multisite = false ) {
		$value = false === $is_multisite ? get_option( $key ) : get_site_option( $key );

		if ( 1 === (int) $value ) {
			return true;
		}

		return false;
	}

	/**
	 * Enable a single boolean setting.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $key          Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function enable_option( $key, $is_multisite = false ) {
		// Don't try to enable already enabled option.
		if ( self::is_enabled( $key, $is_multisite ) ) {
			return true;
		}

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, 1 ) : update_site_option( $key, 1 );
		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Disable a single boolean setting.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $key Setting field key.
	 * @param  bool   $is_multisite Whether to check multisite option or regular option.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function disable_option( $key, $is_multisite = false ) {
		// Don't try to disable already disabled option.
		if ( ! self::is_enabled( $key, $is_multisite ) ) {
			return true;
		}

		// Update the option.
		$result = false === $is_multisite ? update_option( $key, 0 ) : update_site_option( $key, 0 );

		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Check if a single boolean setting is enabled for single site in a network.
	 *
	 * @since 5.0.0
	 *
	 * @param  int    $blog_id The blog id.
	 * @param  string $key     Setting field key.
	 *
	 * @return boolean True if the setting is enabled, false otherwise.
	 */
	public static function is_mu_enabled( $blog_id, $key ) {
		$value = get_blog_option( $blog_id, $key );

		if ( 1 === (int) $value ) {
			return true;
		}

		return false;
	}

	/**
	 * Enable a single boolean setting for single site in a network.
	 *
	 * @since 5.0.0
	 *
	 * @param  int    $blog_id The blog id.
	 * @param  string $key     Setting field key.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function enable_mu_option( $blog_id, $key ) {
		// Don't try to enable already enabled option.
		if ( self::is_mu_enabled( $blog_id, $key ) ) {
			return true;
		}

		// Update the option.
		$result = update_blog_option( $blog_id, $key, 1 );
		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Disable a single boolean setting for single site in a network.
	 *
	 * @since 5.0.0
	 *
	 * @param  int    $blog_id The blog id.
	 * @param  string $key     Setting field key.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function disable_mu_option( $blog_id, $key ) {
		// Don't try to disable already disabled option.
		if ( ! self::is_mu_enabled( $blog_id, $key ) ) {
			return true;
		}

		// Update the option.
		$result = update_blog_option( $blog_id, $key, 0 );

		// Purge the cache.
		Supercacher::purge_cache();
		// Return the result.
		return $result;
	}

	/**
	 * Handle enable/disable gzip compression.
	 *
	 * @since  5.0.0
	 *
	 * @param  mixed $value     The new value.
	 * @param  mixed $old_value The old value.
	 *
	 * @return mixed            The new or old value, depending of the result.
	 */
	public function toogle_gzip_comporession( $value, $old_value ) {
		if ( 1 === $value ) {
			$result = $this->htaccess_service->enable( 'gzip' );
		} else {
			$result = $this->htaccess_service->disable( 'gzip' );
		}

		return true === $result ? $value : $old_value;
	}

	/**
	 * Handle enable/disable browser caching.
	 *
	 * @since  5.0.0
	 *
	 * @param  mixed $value     The new value.
	 * @param  mixed $old_value The old value.
	 *
	 * @return mixed            The new or old value, depending of the result.
	 */
	public function toogle_browser_caching( $value, $old_value ) {
		if ( 1 === $value ) {
			$result = $this->htaccess_service->enable( 'browser-caching' );
		} else {
			$result = $this->htaccess_service->disable( 'browser-caching' );
		}

		return true === $result ? $value : $old_value;
	}

	/**
	 * Checks if the `option_key` paramether exists in rest data.
	 *
	 * @since  5.0.0
	 *
	 * @param  object $request Request data.
	 *
	 * @return string          The option key.
	 */
	private function validate_key( $request ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the option key is not set.
		if ( empty( $data['option_key'] ) ) {
			wp_send_json_error();
		}

		return $data['option_key'];
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  5.0.0
	 */
	public function fetch_options() {
		global $wpdb;
		global $blog_id;

		$prefix = $wpdb->get_blog_prefix( $blog_id );

		$options = array();

		$site_options = $wpdb->get_results(
			"
			SELECT REPLACE( option_name, 'siteground_optimizer_', '' ) AS name, option_value AS value
			FROM {$prefix}options
			WHERE option_name LIKE '%siteground_optimizer_%'
		"
		);

		if ( is_multisite() ) {
			$sitemeta_options = $wpdb->get_results(
				"
				SELECT REPLACE( meta_key, 'siteground_optimizer_', '' ) AS name, meta_value AS value
				FROM $wpdb->sitemeta 
				WHERE meta_key LIKE '%siteground_optimizer_%'
			"
			);

			$site_options = array_merge(
				$site_options,
				$sitemeta_options
			);
		}

		foreach ( $site_options as $option ) {
			// Try to unserialize the value.
			$value = maybe_unserialize( $option->value );

			if ( ! is_array( $value ) && null !== filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
				$value = intval( $value );
			}

			$options[ $option->name ] = $value;
		}

		return $options;
	}

	/**
	 * Checks if there are unoptimized images.
	 *
	 * @since  5.0.0
	 *
	 * @return int The count of unoptimized images.
	 */
	public static function check_for_unoptimized_images() {
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					// Skip optimized images.
					array(
						'key'     => 'siteground_optimizer_is_optimized',
						'compare' => 'NOT EXISTS',
					),
					// Also skip failed optimizations.
					array(
						'key'     => 'siteground_optimizer_optimization_failed',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		return count( $images );
	}

	/**
	 * Checks if there are non converted images.
	 *
	 * @since  5.4.0
	 *
	 * @return int The count of non converted images.
	 */
	public static function check_for_non_converted_images() {
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					// Skip optimized images.
					array(
						'key'     => 'siteground_optimizer_is_converted_to_webp',
						'compare' => 'NOT EXISTS',
					),
					// Also skip failed optimizations.
					array(
						'key'     => 'siteground_optimizer_webp_conversion_failed',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		return count( $images );
	}

	/**
	 * Checks if there are any images in the library.
	 *
	 * @since  5.3.5
	 *
	 * @return int 1 if thre are any images in the lib, 0 otherwise.
	 */
	public function check_for_images() {
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
			)
		);

		return count( $images );
	}

	/**
	 * Prepare response message for react app.
	 *
	 * @since  5.0.0
	 *
	 * @param  bool   $status The result of operation.
	 * @param  string $key    The option key.
	 * @param  bool   $type   True for enable, false for disable option.
	 *
	 * @return string       The response message.
	 */
	public function get_response_message( $status, $key, $type ) {
		$messages = array(
			'siteground_optimizer_enable_cache'              => __( 'Dynamic Cache', 'sg-cachepress' ),
			'siteground_optimizer_autoflush_cache'           => __( 'Autoflush', 'sg-cachepress' ),
			'siteground_optimizer_user_agent_header'         => __( 'Browser-Specific Caching', 'sg-cachepress' ),
			'siteground_optimizer_enable_memcached'          => __( 'Memcache', 'sg-cachepress' ),
			'siteground_optimizer_ssl_enabled'               => __( 'HTTPS', 'sg-cachepress' ),
			'siteground_optimizer_fix_insecure_content'      => __( 'Insecure Content Fix', 'sg-cachepress' ),
			'siteground_optimizer_enable_gzip_compression'   => __( 'GZIP Compression', 'sg-cachepress' ),
			'siteground_optimizer_enable_browser_caching'    => __( 'Browser Caching', 'sg-cachepress' ),
			'siteground_optimizer_optimize_html'             => __( 'HTML Minification', 'sg-cachepress' ),
			'siteground_optimizer_optimize_javascript'       => __( 'JavaScript Minification', 'sg-cachepress' ),
			'siteground_optimizer_optimize_javascript_async' => __( 'Defer Render-blocking JS', 'sg-cachepress' ),
			'siteground_optimizer_optimize_css'              => __( 'CSS Minification', 'sg-cachepress' ),
			'siteground_optimizer_combine_css'               => __( 'CSS Combination', 'sg-cachepress' ),
			'siteground_optimizer_combine_google_fonts'      => __( 'Google Fonts Combination', 'sg-cachepress' ),
			'siteground_optimizer_remove_query_strings'      => __( 'Query Strings Removal', 'sg-cachepress' ),
			'siteground_optimizer_disable_emojis'            => __( 'Emoji Removal Filter', 'sg-cachepress' ),
			'siteground_optimizer_optimize_images'           => __( 'New Images Optimization', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_images'           => __( 'Lazy Loading Images', 'sg-cachepress' ),
			'siteground_optimizer_webp_support'              => __( 'WebP Generation for New Images', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_gravatars'        => __( 'Lazy Loading Gravatars', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_thumbnails'       => __( 'Lazy Loading Thumbnails', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_responsive'       => __( 'Lazy Loading Responsive Images', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_textwidgets'      => __( 'Lazy Loading Widgets', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_mobile'           => __( 'Lazy Load for Mobile', 'sg-cachepress' ),
			'siteground_optimizer_lazyload_woocommerce'      => __( 'Lazy Load for Product Images', 'sg-cachepress' ),
			'siteground_optimizer_supercacher_permissions'   => __( 'Can Config SuperCacher', 'sg-cachepress' ),
			'siteground_optimizer_frontend_permissions'      => __( 'Can Optimize Frontend', 'sg-cachepress' ),
			'siteground_optimizer_images_permissions'        => __( 'Can Optimize Images', 'sg-cachepress' ),
			'siteground_optimizer_environment_permissions'   => __( 'Can Optimize Environment', 'sg-cachepress' ),
		);

		// Get the option name. Fallback to `Option` if the option key doens't exists in predefined messages.
		$option = ! array_key_exists( $key, $messages ) ? __( 'Option', 'sg-cachepress' ) : $messages[ $key ];

		if ( true === $status ) {
			if ( true === $type ) {
				return sprintf( __( '%s Enabled', 'sg-cachepress' ), $option );
			}

			return sprintf( __( '%s Disabled', 'sg-cachepress' ), $option );

		}

		if ( true === $type ) {
			return sprintf( __( 'Could not enable %s', 'sg-cachepress' ), $option );
		}

		return sprintf( __( 'Could not disable %s', 'sg-cachepress' ), $option );
	}
}
