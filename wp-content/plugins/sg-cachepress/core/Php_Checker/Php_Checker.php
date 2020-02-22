<?php
namespace SiteGround_Optimizer\Php_Checker;

use SiteGround_Optimizer\SiteGround_Optimizer;
use SiteGround_Optimizer\Rest\Rest;

/**
 * Handle PHP compatibility checks.
 */
class Php_Checker {

	/**
	 * Link to json containing SiteGround whitelisted plugins.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var string Link to SiteGround whitelisted plugins.
	 */
	private $whitelist = 'http://updates.sgvps.net/plugins_whitelist.json';

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_siteground_optimizer_start_test', array( $this, 'start' ) );
		add_action( 'siteground_optimizer_start_test_cron', array( $this, 'start' ) );
	}

	/**
	 * Starts the version compatibility.
	 *
	 * @since  5.0.0
	 *
	 * @param int $php_version The php version.
	 */
	public function initialize( $php_version ) {
		// Get the directories list.
		$directories = $this->generate_directory_list( $php_version );

		// Calculate the step.
		$step = 100 / count( $directories );

		// Store the step value in option so that the progress can be updated from checker.
		update_option( 'siteground_optimizer_phpcompat_step', round( $step ) - 1 );

		// Loop through all directories and create separate bg process for each directory.
		foreach ( $directories as $dir ) {
			wp_insert_post(
				array(
					'post_title'   => $dir['name'],
					'post_content' => $dir['path'],
					'post_excerpt' => $php_version,
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_type'    => 'sg_optimizer_job',
				)
			);
		}

		// Reset the errors.
		$this->reset();

		// Fork the process and close the connection.
		$this->fork();
	}

	/**
	 * Reset the statuses before start the scan.
	 *
	 * @since  5.0.0
	 */
	public function reset() {
		// Reset the status.
		update_option( 'siteground_optimizer_phpcompat_is_compatible', 0 );

		// Reset the progress.
		update_option( 'siteground_optimizer_phpcompat_progress', 1 );

		// Update the status to running.
		update_option( 'siteground_optimizer_phpcompat_status', 0, false );

		// Reset the option.
		delete_option( 'siteground_optimizer_phpcompat_result' );

		delete_option( 'siteground_optimizer_lock' );
	}

	/**
	 * Fork the process to close the connection.
	 *
	 * @since  5.0.0
	 */
	private function fork() {
		// Fork the process in background.
		$args = array(
			'timeout'  => 0.01,
			'blocking' => false,
			'body'     => array(
				'action' => 'siteground_optimizer_start_test',
			),
			'cookies'  => $_COOKIE,
		);

		$response = wp_remote_post( esc_url_raw( admin_url( 'admin-ajax.php' ) ), $args );
	}

	/**
	 * Start.
	 *
	 * @since 5.0.0
	 */
	public function start() {
		/**
		 * Allow users to change the default timeout.
		 * On SiteGround servers the default timeout is 120 seconds
		 *
		 * @since 5.0.0
		 *
		 * @param int $timeout The timeout in seconds.
		 */
		$timeout = apply_filters( 'siteground_optimizer_php_checker_timeout', MINUTE_IN_SECONDS );

		// Try to lock the process if there is a timeout.
		if ( false === $this->maybe_lock( $timeout ) ) {
			return;
		}

		// Get all directories that shoul be scanned.
		$directories = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => 'sg_optimizer_job',
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		// Scan directories if there are directories to scan!
		if ( $directories ) {
			// Schedule next event right after the current one is completed.
			if ( 0 !== $timeout ) {
				wp_schedule_single_event( time() + $timeout, 'siteground_optimizer_start_test_cron' );
			}

			// Set the timelimit.
			set_time_limit( ( $timeout > 5 ? $timeout - 5 : $timeout ) );

			// Scan directories.
			$this->scan_directories( $directories );
		}

		// Clear the scheduled cron and update the checker status option.
		$this->complete();
	}

	/**
	 * Complete the scan by updating the status
	 * and cancelling all other scan cron jobs.
	 *
	 * @since  5.0.0
	 */
	public function complete() {
		// Clear the scheduled cron after the scan is completed.
		wp_clear_scheduled_hook( 'siteground_optimizer_start_test_cron' );

		// Update the status to finished.
		update_option( 'siteground_optimizer_phpcompat_status', 1, false );

		update_option( 'siteground_optimizer_phpcompat_progress', 100 );

		$result = get_option( 'siteground_optimizer_phpcompat_result', array() );

		if ( empty( $result ) ) {
			update_option( 'siteground_optimizer_phpcompat_is_compatible', 1 );
		}

		// Delete the lock.
		delete_option( 'siteground_optimizer_lock' );
	}

	/**
	 * Scan directories.
	 *
	 * @since  5.0.0
	 *
	 * @param  array $directories Array of posts that represent directories.
	 */
	private function scan_directories( $directories ) {
		// Get the result option.
		$status = get_option( 'siteground_optimizer_phpcompat_result', array() );

		// Scan each directory.
		foreach ( $directories as $directory ) {
			// Keep track of the number of times we've attempted to scan the plugin.
			$count = (int) get_post_meta( $directory->ID, 'count', true );

			if ( $count > 1 ) {
				wp_delete_post( $directory->ID );
				// $this->delete_dir_and_update_progress( $directory->ID );
				continue;
			}

			update_post_meta( $directory->ID, 'count', $count + 1 );

			// Process the dir.
			$report = $this->process_dir( $directory->post_content, $directory->post_excerpt );

			if ( ! empty( $report ) ) {
				$status[] = array(
					'report' => $report,
					'name'   => $directory->post_title,
				);

				update_option( 'siteground_optimizer_phpcompat_result', $status );
			}

			$this->delete_dir_and_update_progress( $directory->ID );
		}
	}

	/**
	 * Delete directory from scan list and update the progress
	 *
	 * @since  5.0.0
	 *
	 * @param  int $id The directory id.
	 */
	private function delete_dir_and_update_progress( $id ) {
		$step     = get_option( 'siteground_optimizer_phpcompat_step', 0 );
		$progress = get_option( 'siteground_optimizer_phpcompat_progress', 0 );

		wp_delete_post( $id );

		if ( $progress < 90 ) {
			// Update the progress.
			update_option( 'siteground_optimizer_phpcompat_progress', $progress + $step );
		}
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
		$lock_result = add_option( 'siteground_optimizer_lock', time(), '', 'no' );

		if ( ! $lock_result ) {
			$lock_result = get_option( 'siteground_optimizer_lock' );

			// Bail if we were unable to create a lock, or if the existing lock is still valid.
			if ( ! $lock_result || ( $lock_result > ( time() - $timeout ) ) ) {

				$timestamp = wp_next_scheduled( 'siteground_optimizer_start_test_cron' );

				if ( false === (bool) $timestamp ) {
					wp_schedule_single_event( time() + $timeout, 'siteground_optimizer_start_test_cron' );
				}
				return false;
			}
		}

		update_option( 'siteground_optimizer_lock', time(), false );

		return true;
	}

	/**
	 * Generate list of directories to scan:
	 *   1. Active plugins
	 *   2. Parent and child theme
	 *
	 * @since  5.0.0
	 *
	 * @param int $php_version The php version to check.
	 *
	 * @return array Array of directories to scan.
	 */
	public function generate_directory_list( $php_version ) {
		$list = array_merge(
			$this->get_plugin_dirs(),
			$this->get_theme_dirs()
		);

		$whitelist = $this->get_list( 'whitelist' );
		$keys = array_keys( $whitelist );

		foreach ( $list as $key => $plugin ) {

			$whitelist_key = array_search( $plugin['name'], array_column( $whitelist, 0 ) );

			if ( false !== $whitelist_key ) {
				$whitelist[ $keys[ $whitelist_key ] ][1]; // Version.
				$whitelist[ $keys[ $whitelist_key ] ][2]; // PHP version.

				if (
					version_compare( $plugin['version'], (int) $whitelist[ $keys[ $whitelist_key ] ][1], '>=' ) ||
					version_compare( $php_version, (int) $whitelist[ $keys[ $whitelist_key ] ][2], '>=' )
				) {
					unset( $list[ $key ] );
				}
			}
		}

		/**
		 * Filters the directories list.
		 *
		 * @since 5.0.0
		 *
		 * @param array $list Array containing all directories that will be scanned.
		 */
		return apply_filters( 'siteground_optimizer_directory_list', $list );
	}

	/**
	 * Return list of all active plugin dirs along with plugin versions and names.
	 *
	 * @since  5.0.0
	 *
	 * @return array List of all active plugin paths.
	 */
	public function get_plugin_dirs() {
		// Include plugin.php if `get_plugins` doens't exist.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// The list with all active plugins paths.
		$dirs    = array();
		// Scan all plugins for multisite installations.
		$plugins = get_plugins();

		// Scan only active plugins for single site.
		if ( ! is_multisite() ) {
			// Get all data for all active plugins.
			$plugins = array_intersect_key(
				get_plugins(), // Get all plugins.
				array_flip( // Exchanges all numberic keys with their associated values( the plugins file paths ).
					get_option( 'active_plugins' ) // Get only active plugins.
				)
			);
		}

		// Hard exclude our own plugin from the list.
		unset( $plugins['sg-cachepress/sg-cachepress.php'] );

		// Loop through all active plugins and add plugin data to dirs list.
		foreach ( $plugins as $key => $plugin ) {
			$path = WP_CONTENT_DIR . '/plugins/' . plugin_dir_path( $key );

			// Bail if the dir doesn't exist.
			if ( ! is_dir( $path ) ) {
				continue;
			}

			// Add the plugin data.
			array_push(
				$dirs,
				array(
					'version' => $plugin['Version'], // Plugin version.
					'path'    => $path, // Path to the plugin.
					'name'    => $plugin['Name'], // The plugin name.
				)
			);
		}

		// Return the plugin dirs.
		return $dirs;
	}

	/**
	 * Returns a list of active theme and parent theme if the active is a child theme.
	 *
	 * @since  5.0.0
	 *
	 * @return array List of active themes(parent & child).
	 */
	private function get_theme_dirs() {
		// Get current theme data.
		$themes = is_multisite() ? wp_get_themes() : array( wp_get_theme() );

		foreach ( $themes as $theme ) {
			$dirs[] = array(
				'version' => $theme->get( 'Version' ), // Theme version.
				'path'    => WP_CONTENT_DIR . '/themes/' . $theme->get_stylesheet(), // Theme path.
				'name'    => $theme->get( 'Name' ), // Theme name.
			);

			// Add the parent theme to the list if the active theme is a child.
			if ( ! is_multisite() && is_child_theme() ) {
				$parent = $theme->parent();
				array_push(
					$dirs,
					array(
						'version' => $parent->get( 'Version' ), // Theme version.
						'path'    => get_template_directory(), // Theme path.
						'name'    => $parent->get( 'Name' ), // Theme name.
					)
				);
			}
		}

		// Return the directory/directories list.
		return $dirs;
	}

	/**
	 * Process dir.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $dir         The directory to test.
	 * @param  string $php_version The php version to check.
	 *
	 * @return string $report The plugin compatibility report.
	 */
	public function process_dir( $dir, $php_version ) {
		if ( class_exists( 'PHPCompatibility\PHPCSHelper' ) ) {
			call_user_func( array( 'PHPCompatibility\PHPCSHelper', 'setConfigData' ), 'testVersion', $php_version, true );
		} else {
			\PHP_CodeSniffer::setConfigData( 'testVersion', $php_version, true );
		}

		$codesniffer_cli = new \PHP_CodeSniffer_CLI();

		ob_start();

		$codesniffer_cli->process(
			array(
				'files'           => $dir,
				'testVersion'     => $php_version,
				'standard'        => 'PHPCompatibilityWP',
				'reportWidth'     => '9999',
				'extensions'      => array( 'php' ),
				'warningSeverity' => 0,
				'ignored'         => array(
					'*/tests/*', // No reason to scan tests.
					'*/test/*', // Another common test directory.
					'*/node_modules/*', // Commonly used for development but not in production.
					'*/tmp/*', // Temporary files.
				),
			)
		);

		$report = ob_get_clean();

		return $report;
	}

	/**
	 * Get a list from sgvps server(ignore & whitelist)
	 *
	 * @since  5.0.0
	 *
	 * @param  string $list The list name.
	 */
	public function get_list( $list ) {
		// Bail if the list name is not defined.
		if ( empty( $list ) ) {
			return;
		}

		ini_set( 'default_socket_timeout', 10 );

		// Try to get the list content.
		$content = file_get_contents( $this->$list );

		// Bail if the content if empty or if an error occured.
		if ( false === $content ) {
			return array();
		}

		// Convert the json to array.
		$list = json_decode( file_get_contents( $this->$list ), true );

		// Check for json errors and return empty array
		// if there are such or list content on success.
		return json_last_error() === JSON_ERROR_NONE ? $list : array();
	}

	/**
	 * Get SiteGround supported versions.
	 *
	 * @since  5.0.0
	 *
	 * @return array Array of supported php versions.
	 */
	public function get_supported_versions() {
		$response = wp_remote_get( 'https://updates.sgvps.net/supported-versions.json' );

		// Bail.
		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}
}
