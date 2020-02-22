<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress class that handle plugin activation/decativation and purge the cache.
 */
class Supercacher_Plugins extends Supercacher {

	/**
	 * Add the hooks when the cache has to be purged.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		add_action( 'deactivate_plugin', array( $this, 'purge_everything' ) );
		add_action( 'activate_plugin', array( $this, 'purge_everything' ) );
		add_action( 'upgrader_process_complete', array( $this, 'purge_everything' ) );
	}

}
