<?php
namespace SiteGround_Optimizer\Htaccess;

use SiteGround_Optimizer;
use SiteGround_Optimizer\Helper\Helper;

class Htaccess {

	/**
	 * Path to htaccess file.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var string The path to htaccess file.
	 */
	private $path = null;

	/**
	 * WordPress filesystem.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 */
	private $wp_filesystem = null;

	/**
	 * The singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @var \Htaccess The singleton instance.
	 */
	private static $instance;

	/**
	 * Regular expressions to check if a rules is enabled.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @var array Regular expressions to check if a rules is enabled.
	 */
	private $types = array(
		'gzip'            => array(
			'enabled'  => '/\#\s+GZIP enabled by SG-Optimizer/si',
			'disabled' => '/\#\s+GZIP enabled by SG-Optimizer(.+?)\#\s+END\s+GZIP\n/ims',
			'disable_all' => '/\#\s+GZIP enabled by SG-Optimizer(.+?)\#\s+END\s+GZIP\n|<IfModule mod_deflate\.c>(.*?\n)<\/IfModule>|# BEGIN WP Rocket(.*)# END WP Rocket/ims',
		),
		'browser-caching' => array(
			'enabled'  => '/\#\s+Leverage Browser Caching by SG-Optimizer/si',
			'disabled' => '/\#\s+Leverage Browser Caching by SG-Optimizer(.+?)\#\s+END\s+LBC\n/ims',
			'disable_all' => '/\#\s+Leverage Browser Caching by SG-Optimizer(.+?)\#\s+END\s+LBC\n|<IfModule mod_expires\.c>(.*?\n?)(<\/IfModule>\n\s)?<\/IfModule>/ims',
		),
		'ssl'           => array(
			'enabled'     => '/HTTPS forced by SG-Optimizer/si',
			'disabled'    => '/\#\s+HTTPS\s+forced\s+by\s+SG-Optimizer(.+?)\#\s+END\s+HTTPS(\n)?/ims',
			'disable_all' => '/\#\s+HTTPS\s+forced\s+by\s+SG-Optimizer(.+?)\#\s+END\s+HTTPS(\n)?/ims',
		),
		'php'           => array(
			'enabled'  => '/START PHP VERSION CHANGE forced by SG Optimizer/si',
			'disabled' => '/\#\s+START PHP VERSION CHANGE forced by SG Optimizer(.+?)\#\s+END PHP VERSION CHANGE\n|(AddHandler\s+application\/x-httpd-.*?$)/ims',
			'disable_all' => '/\#\s+START PHP VERSION CHANGE forced by SG Optimizer(.+?)\#\s+END PHP VERSION CHANGE\n|(AddHandler\s+application\/x-httpd-.*?$)/ims',
		),
	);

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper::setup_wp_filesystem();
		}

		if ( null === $this->path ) {
			$this->set_htaccess_path();
		}

		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.0.0
	 *
	 * @return \Supercacher The singleton instance.
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Set the htaccess path.
	 *
	 * @since 5.0.0
	 */
	public function set_htaccess_path() {
		// Build the filepath.
		$filepath = $this->wp_filesystem->abspath() . '.htaccess';

		// Create the htaccess if it doesn't exists.
		if ( ! is_file( $filepath ) ) {
			$this->wp_filesystem->touch( $filepath );
		}

		// Bail if it isn't writable.
		if ( ! $this->wp_filesystem->is_writable( $filepath ) ) {
			return false;
		}
		// Finally set the path.
		$this->path = $filepath;
	}

	/**
	 * Return the htaccess path.
	 *
	 * @since  5.0.0
	 *
	 * @return mixed The htaccess path or null it's not set.
	 */
	private function get_htaccess_path() {
		return $this->path;
	}

	/**
	 * Remove the rule in htaccess that enable the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @param string $type The rule type to disable.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function disable( $type ) {
		// Bail if htaccess doesn't exists.
		if (
			null === $this->path ||
			! array_key_exists( $type, $this->types )
		) {
			return false;
		}

		// Bail if the rile is already disabled.
		if ( ! $this->is_enabled( $type ) ) {
			return true;
		}

		// Get the content of htaccess.
		$content = $this->wp_filesystem->get_contents( $this->path );

		$new_content = preg_replace( $this->types[ $type ]['disabled'], '', $content );

		return $this->lock_and_write( $new_content );
	}

	/**
	 * Add rule to htaccess that enables the ssl.
	 *
	 * @since  5.0.0
	 *
	 * @param string $type        The rule type to enable.
	 * @param array  $replacement Array containing search and replace strings.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function enable( $type, $replacement = array() ) {
		// Bail if htaccess doesn't exists.
		if ( null === $this->path ) {
			return false;
		}

		// Disable all other rules first.
		$content = $this->wp_filesystem->get_contents( $this->path );

		if ( ! empty( $this->types[ $type ]['disable_all'] ) ) {
			$content = preg_replace( $this->types[ $type ]['disable_all'], '', $content );
		}

		// Get the new rule.
		$new_rule = $this->wp_filesystem->get_contents( SiteGround_Optimizer\DIR . '/templates/' . $type . '.tpl' );

		// Check for replacement.
		if ( ! empty( $replacement ) ) {
			$new_rule = str_replace( $replacement['search'], $replacement['replace'], $new_rule );
		}

		// Generate the new content of htaccess.
		$new_content = $new_rule . PHP_EOL . $content;

		// Return the result.
		return $this->lock_and_write( $new_content );
	}

	/**
	 * Lock file and write something in it.
	 *
	 * @since  5.0.0
	 *
	 * @param  string $content Content to add.
	 *
	 * @return bool            True on success, false otherwise.
	 */
	private function lock_and_write( $content ) {
		$fp = fopen( $this->path, 'w+' );

		if ( flock( $fp, LOCK_EX ) ) {
			fwrite( $fp, $content );
			flock( $fp, LOCK_UN );
			fclose( $fp );
			return true;
		}

		fclose( $fp );
		return false;
	}

	/**
	 * Check if rule is enabled.
	 *
	 * @since  5.0.0
	 *
	 * @param string $type The rule type.
	 *
	 * @return boolean True if the rule is enabled, false otherwise.
	 */
	public function is_enabled( $type ) {
		// Bail if the type doesn't exists in rule types.
		if ( ! array_key_exists( $type, $this->types ) ) {
			return false;
		}

		// Get the content of htaccess.
		$content = $this->wp_filesystem->get_contents( $this->path );

		// Return the result.
		return preg_match( $this->types[ $type ]['enabled'], $content );
	}

	/**
	 * Return the current php version.
	 *
	 * @since  5.0.0
	 *
	 * @return float $php_version The php version.
	 */
	public function get_php_version() {
		// Try to get the php version from htaccess.
		$maybe_php_version = $this->check_htaccess_php_version( get_home_path() );

		// Get the server php version if it was not found in htaccess files.
		if ( false === $maybe_php_version ) {
			if ( Helper::is_avalon() ) {
				return array(
					'version'          => 'recommended-php',
					'has_been_changed' => 1,
				);
			}
			return array(
				'version'          => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
				'has_been_changed' => 0,
			);
		}

		// Finally return the php version info.
		return array(
			'version'          => $maybe_php_version,
			'has_been_changed' => 1,
		);
	}

	/**
	 * Check recursively for php in htaccess files.
	 *
	 * @since  5.1.2
	 *
	 * @param  string $path The path to wp dir.
	 *
	 * @return mixed        Php version if found, false otherwise.
	 */
	private function check_htaccess_php_version( $path ) {
		$file = trailingslashit( $path ) . '.htaccess';


		// Check if the file exists.
		if ( file_exists( $file ) && is_readable( $file ) ) {
			// Check if the version has changed in .htaccess.
			preg_match(
				'/^(?:\s+)?AddHandler\s+application\/x-httpd-(?:php)?(\w+(?:\-php)?)\s+\.php\s+\.php5\s+\.php4\s+\.php3/m',
				$this->wp_filesystem->get_contents( $file ),
				$matches
			);

			// Generate the php version from matches.
			if ( ! empty( $matches[1] ) ) {
				// Get the recommended version from database
				// if the htaccess has rule for recommended php version.
				if ( 'recommended-php' === $matches[1] ) {
					return $matches[1];
				}

				// Build the php version.
				$split = str_split( $matches[1] );
				return $split[0] . '.' . $split[1];
			}

			return $this->check_htaccess_php_version( dirname( $path ) );
		}


		// Bail if the path if the main dir.
		if ( '/' === $path ) {
			return false;
		}

		// Continue with parent directories.
		return $this->check_htaccess_php_version( dirname( $path ) );
	}

}
