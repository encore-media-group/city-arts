<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
/**
 * SG Combinator main plugin class
 */
class Combinator {
	/**
	 * WordPress filesystem.
	 *
	 * @since 5.0.0
	 *
	 * @var object|null WordPress filesystem.
	 */
	private $wp_filesystem = null;

	/**
	 * Array containing all styles that will be loaded.
	 *
	 * @since 5.1.0
	 *
	 * @var array Array containing all styles that will be loaded.
	 */
	private $combined_styles_data = array(
		'header' => array(
			'handle'   => 'siteground-optimizer-combined-styles-header',
		),
		'footer' => array(
			'handle'   => 'siteground-optimizer-combined-styles-footer',
		),
	);

	/**
	 * Array containing all styles that will be loaded.
	 *
	 * @since 5.1.0
	 *
	 * @var array Array containing all styles that will be loaded.
	 */
	private $combined_styles_exclude_list = array(
		'siteground-optimizer-combined-styles-header',
		'siteground-optimizer-combined-styles-footer',
		'elementor-frontend', // Excluded in 5.1.3.
		'elementor-pro', // Excluded in 5.2.2.
		'elementor-global', // Excluded in 5.2.5.
		'tve_style_family_tve_flt', // Excluded in 5.3.0.
		'siteorigin-widget-icon-font-fontawesome',
		'woocommerce-smallscreen',
	);

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		// Bail if it's admin page.
		if ( is_admin() ) {
			return;
		}

		// Setup wp filesystem.
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper::setup_wp_filesystem();
		}

		$this->assets_dir = Front_End_Optimization::get_instance()->assets_dir;
		$this->combined_styles_exclude_list = array_merge(
			$this->combined_styles_exclude_list,
			get_option( 'siteground_optimizer_combine_css_exclude', array() )
		);

		// Minify the css files.
		add_action( 'wp_print_styles', array( $this, 'pre_combine_header_styles' ), 10 );
		add_action( 'print_embed_styles', array( $this, 'pre_combine_header_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_header_combined_styles' ) );
		add_action( 'enqueue_embed_scripts', array( $this, 'enqueue_header_combined_styles' ) );
		// We may combine footer styles in upcoming versions.
		// add_action( 'wp_print_footer_scripts', array( $this, 'combine_styles' ), 10 );
		// add_action( 'get_footer', array( $this, 'enqueue_footer_combined_styles' ) );
	}

	/**
	 * Enqueue the combined styles in header.
	 *
	 * @since  5.1.0
	 */
	public function enqueue_header_combined_styles() {
		wp_enqueue_style(
			'siteground-optimizer-combined-styles-header',
			'/siteground-optimizer-header-style.css',
			array(),
			\SiteGround_Optimizer\VERSION,
			'all'
		);
	}

	/**
	 * Enqueue the combined styles in footer.
	 *
	 * @since  5.1.0
	 */
	public function enqueue_footer_combined_styles() {
		wp_enqueue_style(
			'siteground-optimizer-combined-styles-footer',
			'/siteground-optimizer-footer-style.css',
			array(),
			\SiteGround_Optimizer\VERSION,
			'all'
		);
	}

	/**
	 * Wrapper function for header css combination
	 *
	 * @since  5.1.0
	 */
	public function pre_combine_header_styles() {
		$this->combine_styles( true );
	}

	/**
	 * Combine styles included in header and footer
	 *
	 * @param bool $in_header Whether we should combine header or footer styles.
	 *
	 * @since  5.1.0
	 */
	public function combine_styles( $in_header = false ) {
		global $wp_styles;

		// Bail if the scripts object is empty.
		if ( ! is_object( $wp_styles ) ) {
			return;
		}

		$styles = wp_clone( $wp_styles );
		$styles->all_deps( $styles->queue );

		// Combined styles content.
		$content       = array();
		$inline_styles = '';

		// Get the excluded styles list.
		$excluded_styles = apply_filters( 'sgo_css_combine_exclude', $this->combined_styles_exclude_list );

		// Get groups of handles.
		foreach ( $styles->to_do as $handle ) {
			// Get the src host.
			$host = parse_url( $wp_styles->registered[ $handle ]->src, PHP_URL_HOST );

			if (
				( true === $in_header && $styles->groups[ $handle ] > 0 ) || // Bail if the style is not in the header/footer.
				in_array( $handle, $excluded_styles ) || // If the style is excluded from combination.
				false === $wp_styles->registered[ $handle ]->src || // If the source is empty.
				(
					@strpos( Helper::get_home_url(), parse_url( $wp_styles->registered[ $handle ]->src, PHP_URL_HOST ) ) === false && // Skip all external sources.
					! strpos( $wp_styles->registered[ $handle ]->src, 'wp-includes' ) // Do not exclude wp-includes styles.
				) ||
				pathinfo( $wp_styles->registered[ $handle ]->src, PATHINFO_EXTENSION ) === 'php' || // If it's dynamically generated css.
				is_int( strpos( $handle, 'elementor-post-' ) ) || // Exclude all elementor styles.
				! empty( $wp_styles->registered[ $handle ]->extra['conditional'] ) // Do not combine conditional styles.
			) {
				continue;
			}

			// Check for inline styles.
			$item_inline_style = $styles->get_data( $handle, 'after' );

			if ( ! empty( $item_inline_style ) ) {
				// Check for inline styles.
				$inline_styles .= implode( "\n", $item_inline_style );
			}

			$content[ $wp_styles->registered[ $handle ]->src ] = $this->get_style_content( $wp_styles->registered[ $handle ]->src );

			// Remove the style from registered styles.
			unset( $wp_styles->registered[ $handle ] );
		}

		// Get the combined styles handle.
		$combined_styles_handle = ( true === $in_header ) ? $this->combined_styles_data['header']['handle'] : $this->combined_styles_data['footer']['handle'];

		// Add the inline styles after the combined style.
		wp_add_inline_style( $combined_styles_handle, $inline_styles );

		// Unregister the combined style and return.
		if ( empty( $content ) ) {
			unset( $wp_styles->registered[ $combined_styles_handle ] );
			return;
		}

		$new_file_data = $this->create_temp_style_and_get_url( $content, $combined_styles_handle );

		// Finally change the source to combined style.
		$wp_styles->registered[ $combined_styles_handle ]->src    = $new_file_data['url'];
		$wp_styles->registered[ $combined_styles_handle ]->handle = $new_file_data['handle'];
	}

	/**
	 * Return the style content.
	 *
	 * @since  5.1.0
	 *
	 * @param string $url Link to the file.
	 *
	 * @return string The stylesheet content.
	 */
	public function get_style_content( $url ) {
		// Get the original filepath.
		$filepath = Front_End_Optimization::get_original_filepath( $url );
		// Get the content of the file, but first remove the query strings.
		return $this->wp_filesystem->get_contents( Front_End_Optimization::remove_query_strings( $filepath ) );
	}

	/**
	 * Replace all url to full urls.
	 *
	 * @since  5.1.0
	 *
	 * @param  string $contents Array with link to styles and style content.
	 *
	 * @return string       Content with replaced urls.
	 */
	public function get_style_content_with_replacements( $contents ) {
		// Set the new content var.
		$new_content = array();

		foreach ( $contents as $url => $content ) {
			$dir = trailingslashit( dirname( $url ) );

			$regex = '/url\s*\(\s*(?!["\']?data:)(?![\'|\"]?[\#|\%|])([^)]+)\s*\)([^;},\s]*)/i';

			$replacements = array();

			preg_match_all( $regex, $content, $matches );

			if ( ! empty( $matches ) ) {
				foreach ( $matches[1] as $index => $match ) {
					$match = trim( $match, " \t\n\r\0\x0B\"'" );

					// Bail if the url is valid.
					if ( false == preg_match( '~(http(?:s)?:)?\/\/(?:[\w-]+\.)*([\w-]{1,63})(?:\.(?:\w{3}|\w{2}))(?:$|\/)~', $match ) ) {
						$replacement = str_replace( $match, $dir . $match, $matches[0][ $index ] );

						$replacements[ $matches[0][ $index ] ] = $replacement;
					}
				}
			}

			$keys = array_map( 'strlen', array_keys( $replacements ) );
			array_multisort( $keys, SORT_DESC, $replacements );

			$new_content[] = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
		}

		return implode( "\n", $new_content );
	}

	/**
	 * Create new stylesheet and return the url to it.
	 *
	 * @since  5.1.0
	 *
	 * @param  string $content The file content.
	 * @param  string $handle  Stylesheet handle.
	 *
	 * @return string          The url to the new file.
	 */
	public function create_temp_style_and_get_url( $content, $handle ) {
		$style_hash = md5( implode( '', $content ) );
		$new_file   = $this->assets_dir . 'siteground-optimizer-combined-styles-' . $style_hash . '.css';
		$url        = str_replace( ABSPATH, Helper::get_home_url(), $new_file );

		$data = array(
			'handle' => 'siteground-optimizer-combined-styles-' . $style_hash,
			'url'    => $url,
		);

		if ( is_file( $new_file ) ) {
			return $data;
		}

		// Create the new file.
		$this->wp_filesystem->touch( $new_file );

		// Add the new content into the file.
		$this->wp_filesystem->put_contents(
			$new_file,
			$this->get_style_content_with_replacements( $content )
		);

		return $data;
	}
}
