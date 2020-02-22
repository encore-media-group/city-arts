<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress class that help to split the logic in Supercacher.
 */
class Supercacher_Helper {

	/**
	 * Add the hooks when the headers and cookies have to be set.
	 *
	 * @since  5.0.0
	 */
	public function __construct() {
		add_action( 'wp_headers', array( $this, 'set_cache_headers' ) );
		add_action( 'wp_login', array( $this, 'set_bypass_cookie' ) );
		add_action( 'wp_logout', array( $this, 'remove_bypass_cookie' ) );
	}

	/**
	 * Set headers cookie.
	 *
	 * @since 5.0.0
	 */
	public function set_cache_headers( $headers ) {
		if ( defined( 'WP_CLI' ) || php_sapi_name() === 'cli' ) {
			return;
		}

		$is_cache_enabled = (int) get_option( 'siteground_optimizer_enable_cache', 0 );
		$vary_user_agent = (int) get_option( 'siteground_optimizer_user_agent_header', 0 );

		$url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		// Bail if the cache is not enabled or if the url is excluded from cache.
		if (
			0 === $is_cache_enabled ||
			self::is_url_excluded( $url )
		) {
			$headers['X-Cache-Enabled'] = 'False';
			return $headers;
		}

		// Add user agent header.
		if ( 1 === $vary_user_agent ) {
			$headers['Vary'] = 'User-Agent';
		}

		// Set cache header.
		$headers['X-Cache-Enabled'] = 'True';

		if ( \is_user_logged_in() ) {
			$this->set_bypass_cookie();
		} else {
			$this->remove_bypass_cookie();
		}

		return $headers;
	}

	/**
	 * Set the bypass cookie.
	 *
	 * @since  5.0.0
	 */
	public function set_bypass_cookie() {
		setcookie( 'wpSGCacheBypass', 1, time() + 100 * MINUTE_IN_SECONDS, '/' );
	}

	/**
	 * Remove the bypass cookie set on login.
	 *
	 * @since  5.0.0
	 */
	public function remove_bypass_cookie() {
		setcookie( 'wpSGCacheBypass', 0, time() - HOUR_IN_SECONDS, '/' );
	}

	/**
	 * Check if the current url has been excluded.
	 *
	 * @since  5.0.0
	 *
	 * @param string $url The url to test.
	 *
	 * @return boolean True if it was excluded, false otherwise.
	 */
	public static function is_url_excluded( $url ) {
		// Get excluded urls.
		$parts = \get_option( 'siteground_optimizer_excluded_urls' );

		// Bail if there are no excluded urls.
		if ( empty( $parts ) ) {
			return false;
		}

		// Prepare the url parts for being used as regex.
		$prepared_parts = array_map(
			function( $item ) {
				return str_replace( '\*', '.*', preg_quote( $item, '/' ) );
			}, $parts
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
		return ! empty( $matches ) ? true : false;
	}


}
