<?php

namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Install_Service\Install_5_0_0;
use SiteGround_Optimizer\Install_Service\Install_5_0_5;
use SiteGround_Optimizer\Install_Service\Install_5_0_6;
use SiteGround_Optimizer\Install_Service\Install_5_0_8;
use SiteGround_Optimizer\Install_Service\Install_5_0_9;
use SiteGround_Optimizer\Install_Service\Install_5_0_10;
use SiteGround_Optimizer\Install_Service\Install_5_0_12;
use SiteGround_Optimizer\Install_Service\Install_5_0_13;
use SiteGround_Optimizer\Install_Service\Install_5_2_0;
use SiteGround_Optimizer\Install_Service\Install_5_2_5;
use SiteGround_Optimizer\Install_Service\Install_5_3_0;
use SiteGround_Optimizer\Install_Service\Install_5_3_1;
use SiteGround_Optimizer\Install_Service\Install_5_3_2;
use SiteGround_Optimizer\Install_Service\Install_5_3_4;
use SiteGround_Optimizer\Install_Service\Install_5_3_6;
use SiteGround_Optimizer\Install_Service\Install_5_3_10;
use SiteGround_Optimizer\Install_Service\Install_5_4_0;
use SiteGround_Optimizer\Install_Service\Install_5_4_3;
use SiteGround_Optimizer\Supercacher\Supercacher;

/**
 * Define the Install interface.
 *
 * @since  5.0.0
 */
class Install_Service {

	/**
	 * Loop thought all versions and install the updates.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function install() {
		// Use a transient to avoid concurrent installation calls.
		if ( false === get_transient( '_siteground_optimizer_installing' ) ) {

			set_transient( '_siteground_optimizer_installing', true, 5 * MINUTE_IN_SECONDS );

			// Do the install.
			$this->do_install();

			// Delete the transient after the install.
			delete_transient( '_siteground_optimizer_installing' );
		}
	}

	/**
	 * Perform the actual installation.
	 *
	 * @since 5.0.0
	 */
	private function do_install() {

		// Get the install services.
		$installs = array(
			new Install_5_0_0(),
			new Install_5_0_5(),
			new Install_5_0_6(),
			new Install_5_0_8(),
			new Install_5_0_9(),
			new Install_5_0_10(),
			new Install_5_0_12(),
			new Install_5_0_13(),
			new Install_5_2_0(),
			new Install_5_2_5(),
			new Install_5_3_0(),
			new Install_5_3_1(),
			new Install_5_3_2(),
			new Install_5_3_4(),
			new Install_5_3_6(),
			new Install_5_3_10(),
			new Install_5_4_0(),
			new Install_5_4_3(),
		);

		$version = null;

		foreach ( $installs as $install ) {
			// Get the install version.
			$version = $install->get_version();

			if ( version_compare( $version, $this->get_current_version(), '>' ) ) {
				// Install version.
				$install->install();

				// Bump the version.
				update_option( 'siteground_optimizer_version', $version );

				update_option( 'siteground_optimizer_update_timestamp', time() );

				// Flush dynamic and memcache.
				Supercacher::purge_cache();
				Supercacher::flush_memcache();
			}
		}
	}

	/**
	 * Retrieve the current version.
	 *
	 * @return type
	 */
	private function get_current_version() {
		return get_option( 'siteground_optimizer_version', '0.0.0' );
	}
}