<?php
namespace SiteGround_Optimizer\Memcache;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Helper\Helper;

/**
 * The class responsible for obejct cache.
 */
class Memcache {
	/**
	 * The memcache ip.
	 *
	 * @since 5.0.0
	 *
	 * @var string The ip number.
	 */
	const IP = '127.0.0.1';

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		if ( ! defined( 'WP_CLI' ) ) {
			add_action( 'wp_login', array( $this, 'status_healthcheck' ) );
			add_action( 'plugins_loaded', array( $this, 'run' ) );
		}
	}

	/**
	 * Initialize the class by hooking and running methods.
	 *
	 * @since 5.0.0
	 */
	public function run() {
		// Check if memcache is enabled, but the dropin doesn't exists.
		if ( ! $this->dropin_exists() && Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			// Try to create the memcache droping.
			Options::disable_option( 'siteground_optimizer_enable_memcached' );
			return;
		}

	}

	/**
	 * Check if the memcache connection is working
	 * and reinitialize the dropin if not.
	 *
	 * @since  5.0.0
	 */
	public function status_healthcheck() {
		if ( ! $this->is_connection_working() ) {
			Options::enable_option( 'siteground_optimizer_memcache_notice' );
			Options::disable_option( 'siteground_optimizer_enable_memcached' );
		}
	}

	/**
	 * Check if the object-cache.php dropin file exists (is readable).
	 *
	 * @since 5.0.0
	 *
	 * @return bool|string The file path if file exists and it's readable, false otherwise.
	 */
	public function dropin_exists() {
		$file = $this->get_object_cache_file();

		if (
			file_exists( $file ) &&
			is_readable( $file )
		) {
			return $file;
		}

		return false;
	}

	/**
	 * Get the path to where the object cache dropin should be.
	 *
	 * @since 5.0.0
	 */
	protected function get_object_cache_file() {
		return trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php';
	}

	/**
	 * Get the contents of a port file specific to an account.
	 *
	 * @since 5.0.0
	 *
	 * @return string|false Contents of the port file, or empty string if it couldn't be read.
	 */
	protected function get_port_file_contents() {
		// Get the account name.
		$account_name = defined( 'WP_CLI' ) ? $_SERVER['USER'] : get_current_user();

		// Generate the port file path.
		$port_file_path = "/home/{$account_name}/.SGCache/cache_status";

		// Bail if the file is not readable.
		if ( ! is_readable( $port_file_path ) ) {
			return '';
		}

		// Return the content of the file.
		return file_get_contents( $port_file_path );
	}

	/**
	 * Search a string for what looks like a Memcached port.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $string Any string, but likely the contents of a port file.
	 *
	 * @return string Port number, or empty string if it couldn't be determined.
	 */
	protected function get_memcached_port_from_string( $string ) {
		preg_match( '#memcache\|\|([0-9]+)#', $string, $matches );

		// Return empty string if there is no match.
		if ( empty( $matches[1] ) ) {
			return '';
		}

		// Return the port.
		return $matches[1];
	}

	/**
	 * Get the Memcached port for the current account.
	 *
	 * @since 5.0.0
	 *
	 * @return string Memcached port number, or empty string if error.
	 */
	public function get_memcached_port() {
		$port_file_content = $this->get_port_file_contents();

		if ( ! $port_file_content ) {
			if ( Helper::is_avalon() ) {
				return 11211;
			}

			return '';
		}

		return $this->get_memcached_port_from_string( $port_file_content );
	}

	/**
	 * Check if a Memcached connection is working by setting and immediately getting a value.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True on retrieving exactly the value set, false otherwise.
	 */
	protected function is_connection_working() {
		// Tyr to get the port.
		$port = $this->get_memcached_port();
		// Bail if the port doesn't exists.
		if ( empty( $port ) ) {
			return false;
		}

		$memcache = new \Memcached();
		$memcache->addServer( self::IP, $port );
		$memcache->set( 'SGCP_Memcached_Test', 'Test!1', 50 );

		if ( 'Test!1' === $memcache->get( 'SGCP_Memcached_Test' ) ) {
			$memcache->flush();
			return true;
		}

		return false;
	}

	/**
	 * Copy the Memcache template contents into object-cache.php, replacing IP and Port where needed.
	 *
	 * @since 5.0.0
	 *
	 * @return bool True if the template was successfully copied, false otherwise.
	 */
	public function create_memcached_dropin() {
		// Bail if the connection is not working.
		if ( ! $this->is_connection_working() ) {
			return false;
		}

		// The new object cache.
		$new_object_cache  = str_replace(
			array(
				'SG_OPTIMIZER_CACHE_KEY_SALT',
				'@changedefaults@',
			),
			array(
				str_replace(' ', '', wp_generate_password( 64, true, true ) ),
				self::IP . ':' . $this->get_memcached_port(),
			),
			file_get_contents( \SiteGround_Optimizer\DIR . '/templates/memcached.tpl' )
		);

		// Write the new obejct cache in the cache file.
		$result = file_put_contents(
			$this->get_object_cache_file(),
			$new_object_cache
		);

		return boolval( $result );
	}

	/**
	 * Remove the object-cache.php file.
	 *
	 * @since 5.0.0
	 */
	public function remove_memcached_dropin() {
		$dropin = $this->dropin_exists();

		// Enable the memcache if the file is not readable.
		if ( false !== $dropin ) {
			// Delete the file.
			$is_removed = unlink( $dropin );

			if ( false === $is_removed ) {
				// Enable memcache if the dropin cannot be removed.
				Options::enable_option( 'siteground_optimizer_enable_memcached' );

				return false;
			}
		}

		return true;
	}
}
