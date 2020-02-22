<?php
namespace SiteGround_Optimizer\Ssl;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Htaccess\Htaccess;

class Ssl {
	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		$this->htaccess_service = new Htaccess();

		add_action( 'update_option_siteurl', array( $this, 'maybe_switch_rules' ), 10, 2 );

		// Replace unsecure links if the option is enabled.
		if ( Options::is_enabled( 'siteground_optimizer_fix_insecure_content' ) ) {
			add_action( 'init', array( $this, 'start_buffer' ) );
			add_action( 'shutdown', array( $this, 'end_buffer' ) );
		}
	}

	/**
	 * Enable the ssl when the siteurl is changed.
	 *
	 * @since  5.3.1
	 *
	 * @param  mixed $old The old option value.
	 * @param  mixed $new The new option value.
	 *
	 * @return mixed      The new option value.
	 */
	public function maybe_switch_rules( $old, $new ) {
		if ( Options::is_enabled( 'siteground_optimizer_ssl_enabled' ) ) {
			$this->enable();
		}

		return $new;
	}

	/**
	 * Check if the current domain has valis ssl certificate.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True is the domain has certificate, false otherwise.
	 */
	public function has_certificate() {
		// Get siteurl.
		$home_url = get_option( 'siteurl' );

		// Change siteurl protocol.
		if ( preg_match( '/^http\:/s', $home_url ) ) {
			$home_url = str_replace( 'http', 'https', $home_url );
		}

		$site_url = add_query_arg(
			'sgCacheCheck', // The key.
			'022870ae06716782ce17e4f6e7f69cc2', // The value.
			$home_url
		);

		ini_set( 'user_agent', 'SG-Optimizer 3.0.2;' );
		// Create a streams context.
		$stream = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
				),
			)
		);

		$read = @fopen( $site_url, 'rb', false, $stream );
		$cont = @stream_context_get_params( $read );

		return is_null( $cont['options']['ssl']['peer_certificate'] ) ? false : true;
	}

	/**
	 * Disable the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function disable() {

		// Switch the protocol in database.
		$protocol_switched = $this->switch_protocol();
		$disable_from_htaccess = true;

		// Remove the rule from htaccess for single sites.
		if ( ! is_multisite() ) {
			$disable_from_htaccess = $this->htaccess_service->disable( 'ssl' );
		}

		if (
			! $protocol_switched ||
			! $disable_from_htaccess
		) {
			return false;
		}

		// Disable the option.
		Options::disable_option( 'siteground_optimizer_ssl_enabled' );

		// Return success.
		return true;
	}

	/**
	 * Enable the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enable() {
		// Bail if the domain doesn't have certificate.
		if ( ! $this->has_certificate() ) {
			return false;
		}

		// Switch the protocol in database.
		$protocol_switched = $this->switch_protocol( true );
		$enable_from_htaccess = true;

		$replacements = array(
			'search'  => '{MAYBE_WWW}',
			'replace' => '',
		);

		// Add rule to htaccess for single sites.
		if ( ! is_multisite() ) {
			$parsed  = parse_url( get_option( 'siteurl' ) );

			if ( strpos( $parsed['host'], 'www.' ) === 0 ) {
				$replacements['replace'] = "RewriteCond %{HTTP_HOST} !^www\. [NC]\n    RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]";
			}

			$enable_from_htaccess = $this->htaccess_service->enable( 'ssl', $replacements );
		}

		if (
			! $protocol_switched ||
			! $enable_from_htaccess
		) {
			return false;
		}

		// Disable the option.
		Options::enable_option( 'siteground_optimizer_ssl_enabled' );

		// Return success.
		return true;
	}

	/**
	 * Chnage the url protocol.
	 *
	 * @since  5.0.0
	 *
	 * @param bool $ssl Whether to switch to https or not.
	 *
	 * @return bool     The result.
	 */
	private function switch_protocol( $ssl = false ) {
		$from     = true === $ssl ? 'http' : 'https';
		$to       = true === $ssl ? 'https' : 'http';

		// Strip the protocol from site url.
		$site_url_without_protocol = preg_replace( '#^https?#', '', get_option( 'siteurl' ) );

		// Build the command.
		$command = sprintf(
			"wp search-replace '%s' '%s' --all-tables",
			$from . $site_url_without_protocol,
			$to . $site_url_without_protocol
		);

		// Execute the command.
		exec(
			$command,
			$output,
			$status
		);

		// Check for errors during the import.
		if ( ! empty( $status ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Apply the mixed content fixer.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $buffer The page content.
	 *
	 * @return string         Fixed content.
	 */
	public function filter_buffer( $buffer ) {
		return $this->replace_insecure_links( $buffer );
	}

	/**
	 * Start buffer.
	 *
	 * @since  5.0.0
	 */
	public function start_buffer() {
		ob_start( array( $this, 'filter_buffer' ) );
	}

	/**
	 * End the buffer.
	 *
	 * @since  5.0.0
	 */
	public function end_buffer() {
		if ( ob_get_length() ) {
			ob_end_flush();
		}
	}

	/**
	 * Creates an array of insecure links that should be https and an array of secure links to replace with
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function get_url_list() {
		$home_no_www = str_replace( '://www.', '://', get_option( 'home' ) );
		$home_yes_www = str_replace( '://', '://www.', $home_no_www );

		// Build the search links.
		$search = array(
			str_replace( 'https://', 'http://', $home_yes_www ),
			str_replace( 'https://', 'http://', $home_no_www ),
			"src='http://",
			'src="http://',
		);

		return array(
			'search' => $search, // The search links.
			'replace' => str_replace( 'http://', 'https://', $search ), // The replace links.
		);
	}

	/**
	 * Replace all insecure links before the page is sent to the visitor's browser.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $content The page content.
	 *
	 * @return string          Modified content.
	 */
	private function replace_insecure_links( $content ) {
		// Get the url list.
		$urls = $this->get_url_list();

		// now replace these links.
		$content = str_replace( $urls['search'], $urls['replace'], $content );

		// Replace all http links except hyperlinks
		// All tags with src attr are already fixed by str_replace.
		$pattern = array(
			'/url\([\'"]?\K(http:\/\/)(?=[^)]+)/i',
			'/<link .*?href=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
			'/<meta property="og:image" .*?content=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
			'/<form [^>]*?action=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
		);

		// Return modified content.
		return preg_replace( $pattern, 'https://', $content );
	}
}
