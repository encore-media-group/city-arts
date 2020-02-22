<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Php_Checker\Php_Checker;
use SiteGround_Optimizer\Htaccess\Htaccess;
/**
 * WP-CLI: wp sg memcached enable/disable.
 *
 * Run the `wp sg memcached enable/disable` command to enable/disable specific plugin functionality.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Php_Checker
 */

/**
 * Define the {@link Cli_Php_Checker} class.
 *
 * @since 5.0.0
 */
class Cli_Php_Checker {
	/**
	 * Check PHP version compatibility or switch to php version.
	 *
	 * ## OPTIONS
	 *
	 * <action>
	 * : The action: check\change.
	 * Use `change` along with `version` param, to switch to php version.
	 * Use `check` to check if site plugins and themes are compatible with the current php version.
	 * ---
	 * options:
	 *   - check
	 *   - change
	 * ---
	 *
	 * [--version=<version>]
	 * : Php version to switch to:
	 * ---
	 * options:
	 *   - 5.5
	 *   - 5.6
	 *   - 7.0
	 *   - 7.1
	 *   - 7.2
	 *   - 7.3
	 * ---
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->htaccess = new Htaccess();
		$this->php_checker = new Php_Checker();
		$version = $this->set_recommended_version( $assoc_args );

		if ( 'change' === $args[0] ) {
			return $this->switch_php( $version );
		} else {
			return $this->check_compatibility( $version );
		}
	}

	public function switch_php( $php_version ) {
		$this->htaccess->disable( 'php' );
		$result = $this->htaccess->enable(
			'php',
			array(
				'search'  => '_PHPVERSION_',
				'replace' => str_replace( '.', '', $php_version ),
			)
		);

		// Reset the compatibility.
		update_option( 'siteground_optimizer_phpcompat_is_compatible', 0 );

		true === $result ? \WP_CLI::success( 'PHP Version has been changed' ) : \WP_CLI::error( 'Cannot change PHP Version' );
	}

	public function check_compatibility( $version ) {
		\WP_CLI::log( 'Testing compatibility with PHP ' . $version . '.' );
		// Add empty line.
		\WP_CLI::log( '' );

		$directories = $this->php_checker->generate_directory_list( $version );

		$results = '';

		$progress = \WP_CLI\Utils\make_progress_bar( 'Processing directories', count( $directories ) );

		foreach ( $directories as $dir ) {
			$progress->tick();
			$dir_results = $this->php_checker->process_dir( $dir['path'], $version );

			if ( ! empty( $dir_results ) ) {
				$results .= $dir_results . "\n";
			}
		}

		$progress->finish();

		\WP_CLI::log( $results );
		if ( preg_match( '/(\d*) ERRORS?/i', $results ) ) {
			\WP_CLI::error( 'Your WordPress install is not compatible.' );
		} else {
			\WP_CLI::success( 'Your WordPress install is compatible.' );
		}
	}

	public function set_recommended_version( $maybe_version ) {
		if ( ! empty( $maybe_version['version'] ) ) {
			return $maybe_version['version'];
		}

		$versions = $this->php_checker->get_supported_versions();

		if ( ! empty( $versions['recommended'] ) ) {
			return $versions['recommended'];
		}
	}
}
