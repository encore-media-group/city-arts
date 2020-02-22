<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Supercacher\Supercacher;
/**
 * WP-CLI: wp sg purge.
 *
 * Run the `wp sg purge` command to purge all the cache.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Purge
 */

/**
 * Define the {@link Cli_Purge} class.
 *
 * @since 5.0.0
 */
class Cli_Purge {
	/**
	 * Purge all caches - static, dynamic, memcached and PHP opcache
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->supercacher = new Supercacher();

		if ( empty( $args[0] ) ) {
			return $this->purge_everything();
		}

		if ( 'memcached' === $args[0] ) {
			return $this->purge_memcached();
		}

		if ( filter_var( $args[0], FILTER_VALIDATE_URL ) ) {
			return $this->purge_url( $args[0] );
		}

		\WP_CLI::error( 'Incorrect URL!' );
	}

	/**
	 * Purges all cache.
	 *
	 * @since  5.0.0
	 */
	public function purge_everything() {
		$response = $this->supercacher->purge_everything();

		if ( true == $response ) {
			return \WP_CLI::success( 'Cache Successfully Purged' );
		}

		return \WP_CLI::error( 'Unable to Purge Cache.' );
	}

	/**
	 * Purge memcache.
	 *
	 * @since  5.0.0
	 */
	public function purge_memcached() {
		$response = $this->supercacher->flush_memcache();

		if ( true == $response ) {
			return \WP_CLI::success( 'Memcached Successfully Purged' );
		}

		return \WP_CLI::error( 'Unable to Purge Memcached.' );
	}

	/**
	 * Purge url cache.
	 *
	 * @since  5.0.0
	 */
	public function purge_url( $url ) {
		$response = $this->supercacher->purge_cache_request( $url, true );

		if ( true == $response ) {
			return \WP_CLI::success( 'URL Cache Successfully Purged' );
		}

		return \WP_CLI::error( 'Unable to Purge Cache.' );
	}
}
