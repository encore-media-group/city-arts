<?php
namespace SiteGround_Optimizer\Admin;

use SiteGround_Optimizer\Supercacher\Supercacher;

/**
 * Add purge button functionality to admin bar.
 */
class Admin_Bar {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_purge' ), PHP_INT_MAX );
	}


	/**
	 * Adds a purge buttion in the admin bar menu.
	 *
	 * @param (WP_Admin_Bar) $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @since 5.0.0
	 */
	public function add_admin_bar_purge( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$args = array(
			'id'    => 'SG_CachePress_Supercacher_Purge',
			'title' => __( 'Purge SG Cache', 'sg-cachepress' ),
			'href'  => wp_nonce_url( admin_url( 'admin-ajax.php?action=admin_bar_purge_cache' ), 'sg-cachepress-purge' ),
			'meta'  => array( 'class' => 'sg-cachepress-admin-bar-purge' ),
		);

		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Purges the cache and redirects to referrer (admin bar button)
	 *
	 * @since 5.0.0
	 */
	public function purge_cache() {
		// Bail if the nonce is not set.
		if ( empty( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'sg-cachepress-purge' ) ) {
			return;
		}

		Supercacher::purge_cache();
		Supercacher::flush_memcache();
		Supercacher::delete_assets();

		wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
		exit;
	}
}
