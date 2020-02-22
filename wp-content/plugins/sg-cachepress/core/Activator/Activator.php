<?php
namespace SiteGround_Optimizer\Activator;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Memcache\Memcache;
use SiteGround_Optimizer\Options\Options;

class Activator {
	/**
	 * Run on plugin activation.
	 *
	 * @since 5.0.9
	 */
	public function activate() {
		if ( ! file_exists( "/Z" ) ) {
			echo '<div class="notice notice-error">' . esc_html__( 'The SG Optimizer plugin is designed to work only on SiteGround Servers. We\'ve deactivated it because it may render your site blank if used on another environment.', 'sg-cachpress' ) . '</div>';

			// Adding @ before will prevent XDebug output.
			@trigger_error( esc_html__( 'The SG Optimizer plugin is designed to work only on SiteGround Servers.', 'sg-cachpress' ), E_USER_ERROR );
		}

		$this->maybe_create_memcache_dropin();
	}

	/**
	 * Check if memcache options was enabled and create the memcache dropin.
	 *
	 * @since  5.0.9
	 */
	public function maybe_create_memcache_dropin() {
		if ( Options::is_enabled( 'siteground_optimizer_enable_memcached' ) ) {
			$memcached = new Memcache();
			$memcached->remove_memcached_dropin();
			$memcached->create_memcached_dropin();
		}
	}

}
