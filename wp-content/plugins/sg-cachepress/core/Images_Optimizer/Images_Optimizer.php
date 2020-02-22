<?php
namespace SiteGround_Optimizer\Images_Optimizer;

use SiteGround_Optimizer\Supercacher\Supercacher;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Helper\Helper;
/**
 * SG Images_Optimizer main plugin class
 */
class Images_Optimizer {
	/**
	 * The batch limit.
	 *
	 * @since 5.0.0
	 *
	 * @var int The batch limit.
	 */
	const BATCH_LIMIT = 200;

	/**
	 * The png image size limit. Bigger images won't be optimized.
	 *
	 * @since 5.0.0
	 *
	 * @var int The png image size limit.
	 */
	const PNGS_SIZE_LIMIT = 500000;

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_siteground_optimizer_start_image_optimization', array( $this, 'start_optimization' ) );
		add_action( 'siteground_optimizer_start_image_optimization_cron', array( $this, 'start_optimization' ) );

		new Images_Optimizer_Webp();

		// Optimize newly uploaded images.
		if (
			Options::is_enabled( 'siteground_optimizer_optimize_images' ) &&
			0 === Helper::is_cron_disabled()
		) {
			add_action( 'wp_generate_attachment_metadata', array( $this, 'optimize_new_image' ), 10, 2 );
		} else {
			add_action( 'wp_generate_attachment_metadata', array( $this, 'maybe_update_total_unoptimized_images' ) );
		}
	}

	/**
	 * Start the optimization.
	 *
	 * @since  5.0.0
	 */
	public function initialize() {
		// Reset the status.
		update_option( 'siteground_optimizer_image_optimization_completed', 0, false );
		update_option( 'siteground_optimizer_image_optimization_status', 0, false );
		update_option( 'siteground_optimizer_image_optimization_stopped', 0, false );
		update_option( 'siteground_optimizer_total_unoptimized_images', Options::check_for_unoptimized_images(), false );

		// Fork the process in background.
		$args = array(
			'timeout'   => 0.01,
			'blocking'  => false,
			'cookies'   => $_COOKIE,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		);

		$response = wp_remote_post(
			add_query_arg( 'action', 'siteground_optimizer_start_image_optimization', admin_url( 'admin-ajax.php' ) ),
			$args
		);

		// Return the error message if the request failed.
		if ( is_wp_error( $response ) ) {
			error_log( 'Image optimization start failed: ' . $response->get_error_message() );
		}

	}

	/**
	 * Get images batch.
	 *
	 * @since  5.0.0
	 *
	 * @return array Array containing all images ids that are not optimized.
	 */
	public function get_batch() {
		// Flush the cache before prepare a new batch.
		wp_cache_flush();
		// Get the images.
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => self::BATCH_LIMIT,
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

		return $images;
	}

	/**
	 * Optimize the images.
	 *
	 * @since  5.0.0
	 */
	public function start_optimization() {
		$started = time();
		// Get image ids.
		$ids = $this->get_batch();
		// There are no more images to process, so complete the optimization.
		if ( empty( $ids ) ) {
			// Clear the scheduled cron and update the optimization status.
			$this->complete();
			return;
		}

		/**
		 * Allow users to change the default timeout.
		 * On SiteGround servers the default timeout is 120 seconds
		 *
		 * @since 5.0.0
		 *
		 * @param int $timeout The timeout in seconds.
		 */
		$timeout = apply_filters( 'siteground_optimizer_image_optimization_timeout', 120 );

		// Try to lock the process if there is a timeout.
		if ( false === $this->maybe_lock( $timeout ) ) {
			return;
		}

		// Schedule next event right after the current one is completed.
		if ( 0 !== $timeout ) {
			wp_schedule_single_event( time() + $timeout, 'siteground_optimizer_start_image_optimization_cron' );
		}

		// Loop through all images and optimize them.
		foreach ( $ids as $id ) {
			// Keep track of the number of times we've attempted to optimize the image.
			$count = (int) get_post_meta( $id, 'siteground_optimizer_optimization_attempts', true );

			if ( $count > 1 ) {
				update_post_meta( $id, 'siteground_optimizer_optimization_failed', 1 );
				continue;
			}

			update_post_meta( $id, 'siteground_optimizer_optimization_attempts', $count + 1 );

			// Get attachment metadata.
			$metadata = wp_get_attachment_metadata( $id );

			// Optimize the main image and the other image sizes.
			$status = $this->optimize_image( $id, $metadata );

			// Mark image if the optimization failed.
			if ( false === $status ) {
				update_post_meta( $id, 'siteground_optimizer_optimization_failed', 1 );
			}

			// Mark the image as optimized.
			update_post_meta( $id, 'siteground_optimizer_is_optimized', 1 );

			// Break script execution before we hit the max execution time.
			if ( ( $started + $timeout - 5 ) < time() ) {
				break;
			}
		}
	}

	/**
	 * Delete the scheduled cron and update the status of optimization.
	 *
	 * @since  5.0.0
	 */
	public static function complete() {
		// Clear the scheduled cron after the optimization is completed.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_image_optimization_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_image_optimization_completed', 1, false );
		update_option( 'siteground_optimizer_image_optimization_status', 1, false );
		update_option( 'siteground_optimizer_image_optimization_stopped', 0, false );

		// Delete the lock.
		delete_option( 'siteground_optimizer_image_optimization_lock' );
		delete_option( 'siteground_optimizer_total_unoptimized_images' );

		// Finally purge the cache.
		Supercacher::purge_cache();
	}

	/**
	 * Optimize the image
	 *
	 * @since  5.0.0
	 *
	 * @param  int   $id       The image id.
	 * @param  array $metadata The image metadata.
	 *
	 * @return bool     True on success, false on failure.
	 */
	public function optimize_image( $id, $metadata ) {
		// Load the uploads dir.
		$upload_dir = wp_get_upload_dir();
		// Get path to main image.
		$main_image = get_attached_file( $id );
		// Get the basename.
		$basename   = basename( $main_image );

		// Get the command placeholder. It will be used by main image and to optimize the different image sizes.
		$status = $this->execute_optimization_command( $main_image );

		// Optimization failed.
		if ( true === boolval( $status ) ) {
			update_post_meta( $id, 'siteground_optimizer_optimization_failed', 1 );
			return false;
		}

		// Loop through all image sizes and optimize them as well.
		foreach ( $metadata['sizes'] as $size ) {
			// Replace main image with the cropped image and run the optimization command.
			$status = $this->execute_optimization_command( str_replace( $basename, $size['file'], $main_image ) );

			// Optimization failed.
			if ( true === boolval( $status ) ) {
				update_post_meta( $id, 'siteground_optimizer_optimization_failed', 1 );
				return false;
			}
		}


		// Everything ran smoothly.
		update_post_meta( $id, 'siteground_optimizer_is_optimized', 1 );
		return true;
	}

	/**
	 * Check if image exists and perform optimiation.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $filepath The path to the file.
	 *
	 * @return bool             False on success, true on failure.
	 */
	private function execute_optimization_command( $filepath ) {
		// Bail if the file doens't exists.
		if ( ! file_exists( $filepath ) ) {
			return true;
		}

		// Get image type.
		$type = exif_imagetype( $filepath );

		switch ( $type ) {
			case IMAGETYPE_GIF:
				$placeholder = 'gifsicle -O3 --careful -o %1$s %1$s 2>&1';
				break;

			case IMAGETYPE_JPEG:
				$placeholder = 'jpegoptim -m85 %s 2>&1';
				break;

			case IMAGETYPE_PNG:
				// Bail if the image is bigger than 500k.
				// PNG usage is not recommended and images bigger than 500kb
				// hit the limits.
				if ( filesize( $filepath ) > self::PNGS_SIZE_LIMIT ) {
					return true;
				}
				$placeholder = 'optipng -o2 %s 2>&1';
				break;

			default:
				// Bail if the image type is not supported.
				return true;
		}

		// Optimize the image.
		exec(
			sprintf(
				$placeholder, // The command.
				$filepath // Image path.
			),
			$output,
			$status
		);

		// Create webp copy of the webp is enabled.
		if ( Options::is_enabled( 'siteground_optimizer_webp_support' ) ) {
			Images_Optimizer_Webp::generate_webp_file( $filepath );
		}

		return $status;
	}

	/**
	 * Lock the currently running process if the timeout is set.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $timeout The max_execution_time value.
	 *
	 * @return bool         True if the timeout is not set or if the lock has been created.
	 */
	private function maybe_lock( $timeout ) {
		// No reason to lock if there's no timeout.
		if ( 0 === $timeout ) {
			return true;
		}

		// Try to lock.
		$lock_result = add_option( 'siteground_optimizer_image_optimization_lock', time(), '', 'no' );

		if ( ! $lock_result ) {

			$lock_result = get_option( 'siteground_optimizer_image_optimization_lock' );


			// Bail if we were unable to create a lock, or if the existing lock is still valid.
			if ( ! $lock_result || ( $lock_result > ( time() - $timeout ) ) ) {
				$timestamp = wp_next_scheduled( 'siteground_optimizer_start_image_optimization_cron' );


				if ( false === (bool) $timestamp ) {
					$response = wp_schedule_single_event( time() + $timeout, 'siteground_optimizer_start_image_optimization_cron' );

				}
				return false;
			}
		}

		update_option( 'siteground_optimizer_image_optimization_lock', time(), false );

		return true;
	}

	/**
	 * Optimize newly uploaded images.
	 *
	 * @since  5.0.0
	 *
	 * @param  array $data          Array of updated attachment meta data.
	 * @param  int   $attachment_id Attachment post ID.
	 */
	public function optimize_new_image( $data, $attachment_id ) {
		// Optimize the image.
		$this->optimize_image( $attachment_id, $data );

		// Return the attachment data.
		return $data;
	}

	/**
	 * Update the total unoptimized images count.
	 *
	 * @since  5.4.0
	 *
	 * @param  array $data          Array of updated attachment meta data.
	 */
	public function maybe_update_total_unoptimized_images( $data ) {
		if ( 1 === get_option( 'siteground_optimizer_image_optimization_status', 0 ) ) {
			return $data;
		}

		update_option(
			'siteground_optimizer_total_unoptimized_images',
			get_option( 'siteground_optimizer_total_unoptimized_images', 0 ) + 1
		);

		// Return the attachment data.
		return $data;
	}

	/**
	 * Deletes images meta_key flag to allow reoptimization.
	 *
	 * @since  5.0.0
	 */
	public static function reset_image_optimization_status() {
		global $wpdb;

		$wpdb->query(
			"
				DELETE FROM $wpdb->postmeta
				WHERE `meta_key` = 'siteground_optimizer_is_optimized'
				OR `meta_key` = 'siteground_optimizer_optimization_attempts'
				OR `meta_key` = 'siteground_optimizer_optimization_failed'
			"
		);
	}
}
