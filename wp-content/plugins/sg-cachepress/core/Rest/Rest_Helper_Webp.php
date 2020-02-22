<?php
namespace SiteGround_Optimizer\Rest;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Images_Optimizer\Images_Optimizer_Webp;

/**
 * Rest Helper class that process all rest requests and provide json output for react app.
 */
class Rest_Helper_Webp {
	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->options               = new Options();
		$this->webp_images_optimizer = new Images_Optimizer_Webp();
	}

	/**
	 * Delete WebP files.
	 *
	 * @since  5.4.0
	 */
	public function delete_webp_files() {
		$result = $this->webp_images_optimizer->delete_webp_files();

		if ( ! empty( $result ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Initialize images optimization
	 *
	 * @since  5.4.0
	 */
	public function generate_webp_files() {
		$this->webp_images_optimizer->initialize();

		wp_send_json_success(
			array(
				'webp_conversion_status'     => 0,
				'has_images_for_conversion'  => get_option( 'siteground_optimizer_total_non_converted_images', 0 ),
				'total_non_converted_images' => get_option( 'siteground_optimizer_total_non_converted_images', 0 ),
			)
		);
	}

	/**
	 * Stops images optimization
	 *
	 * @since  5.0.8
	 */
	public function stop_webp_conversion() {
		// Clear the scheduled cron after the optimization is completed.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_webp_conversion_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_webp_conversion_completed', 1, false );
		update_option( 'siteground_optimizer_webp_conversion_status', 1, false );

		// Delete the lock.
		delete_option( 'siteground_optimizer_webp_conversion_lock' );

		wp_send_json_success(
			array(
				'webp_conversion_status'    => 1,
				'has_images_for_conversion' => $this->options->check_for_non_converted_images(),
			)
		);
	}

	/**
	 * Return the status of current compatibility check.
	 *
	 * @since  5.4.0
	 */
	public function check_webp_conversion_status() {
		$non_converted_images = $this->options->check_for_non_converted_images();

		if ( 0 === $non_converted_images ) {
			Images_Optimizer_Webp::complete();
		}

		$status = (int) get_option( 'siteground_optimizer_webp_conversion_completed', 0 );

		wp_send_json_success(
			array(
				'webp_conversion_status'     => $status,
				'has_images_for_conversion'  => $non_converted_images,
				'total_non_converted_images' => (int) get_option( 'siteground_optimizer_total_non_converted_images', 0 ),
			)
		);
	}
}
