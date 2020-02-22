<?php
namespace SiteGround_Optimizer\Cli;

use SiteGround_Optimizer\Ssl\Ssl;
use SiteGround_Optimizer\Options\Options;
/**
 * WP-CLI: wp sg memcached enable/disable.
 *
 * Run the `wp sg memcached enable/disable` command to enable/disable specific plugin functionality.
 *
 * @since 5.0.0
 * @package Cli
 * @subpackage Cli/Cli_Https
 */

/**
 * Define the {@link Cli_Https} class.
 *
 * @since 5.0.0
 */
class Cli_Https {
	/**
	 * Allow you to enable/disable https.
	 *
	 * ## OPTIONS
	 * <action>
	 * : The action: enable\disable\fix.
	 * Whether to enable or disable the https.
	 * Use fix to apply fix for insecure content
	 * <fix_action>
	 * : Whether to enable or disable the insecure content fix.
	 */
	public function __invoke( $args, $assoc_args ) {
		$this->ssl            = new Ssl();
		$this->option_service = new Options();

		if ( empty( $args[0] ) ) {
			return \WP_CLI::error( 'Please provide action: enable/disable or add the subcommand `fix`' );
		}

		switch ( $args[0] ) {
			case 'fix':
				if ( empty( $args[1] ) ) {
					return \WP_CLI::error( 'Please provide action: enable/disable' );
				}
				return $this->apply_https_fix( $args[1] );
			case 'enable':
				$result = $this->ssl->enable();
				true === $result ? Options::enable_option( 'siteground_optimizer_ssl_enabled' ) : '';
				$type = true;
				break;

			case 'disable':
				$result = $this->ssl->disable();
				true === $result ? Options::disable_option( 'siteground_optimizer_ssl_enabled' ) : '';
				$type = false;
				break;
			default:
				\WP_CLI::error( 'Please specify action' );
				break;
		}

		$message = $this->option_service->get_response_message( $result, 'siteground_optimizer_ssl_enabled', $type );

		return true === $result ? \WP_CLI::success( $message ) : \WP_CLI::error( $message );
	}

	public function apply_https_fix( $action ) {
		if ( 'enable' === $action ) {
			$result = Options::enable_option( 'siteground_optimizer_fix_insecure_content' );
			$type = true;
		} else {
			$result = Options::disable_option( 'siteground_optimizer_fix_insecure_content' );
			$type = false;
		}

		$message = $this->option_service->get_response_message( $result, 'siteground_optimizer_fix_insecure_content', $type );

		return true === $result ? \WP_CLI::success( $message ) : \WP_CLI::error( $message );
	}
}
