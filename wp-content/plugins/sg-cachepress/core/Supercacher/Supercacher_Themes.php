<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress class that handle theme modifications and purge the cache.
 */
class Supercacher_Themes extends Supercacher {

	/**
	 * Add the hooks when the cache has to be purged.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		add_action( 'switch_theme', array( $this, 'purge_everything' ) );
		add_action( 'customize_save', array( $this, 'purge_everything' ) );
	}

}
