<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Helper\Helper;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;

/**
 * SG Combinator main plugin class
 */
class Fonts_Combinator {

	/**
	 * Dir where the we will store the Google fonts css.
	 *
	 * @since 5.3.6
	 *
	 * @var string|null Path to fonts dir.
	 */
	public $fonts_dir = 'google-fonts';

	/**
	 * Regex parts.
	 *
	 * @since 5.3.4
	 *
	 * @var array Google Fonts regular expression
	 */
	private $regex_parts = array(
		'~', // The php quotes.
		'<link', // Match the opening part of link tags.
		'(?:\s+(?:(?!href\s*=\s*)[^>])+)?', // Negative lookahead aserting the regex does not match href attribute.
		'(?:\s+href\s*=\s*(?P<quotes>[\'|"]))', // Match the href attribute followed by single or double quotes. Create a `quotes` group, so we can use it later.
		'(', // Open the capturing group for the href value.
			'(?:https?:)?', // Match the protocol, which is optional. Sometimes the fons is added. without protocol i.e. //fonts.googleapi.com/css.
			'\/\/fonts\.googleapis\.com\/css', // Match that the href value is google font link.
			'(?:(?!(?P=quotes)).)+', // Match anything in the href attribute until the closing quote.
		')', // Close the capturing group.
		'(?P=quotes)', // Match the closing quote.
		'(?:\s+.*?)?', // Match anything else after the href tag.
		'[>]', // Until the closing tag if found.
		'~', // The php quotes.
		'ims',
	);

	/**
	 * The constructor.
	 *
	 * @since 5.3.4
	 */
	public function __construct() {
		// Bail if it's admin page.
		if ( is_admin() ) {
			return;
		}

		// Add the hooks that we will use t ominify the html.
		add_action( 'init', array( $this, 'start_bufffer' ) );
		add_action( 'shutdown', array( $this, 'end_buffer' ) );
	}

	/**
	 * Combine the google fonts.
	 *
	 * @since  5.3.4
	 *
	 * @param  string $html The page html.
	 *
	 * @return string       Modified html with combined Google font.
	 */
	public function combine_google_fonts( $html ) {
		// Get fonts if any.
		$fonts = $this->get_fonts( $html );
		// Bail if there are no fonts or if there is only one font.
		if ( empty( $fonts ) ) {
			return $html;
		}

		$_fonts = $fonts;

		// The methods that should be called to combine the fonts.
		$methods = array(
			'parse_fonts', // Parse fonts.
			'beautify', // Beautify and remove duplicates.
			'implode_pieces', // Beautify and remove duplicates.
			'get_combined_css', // Get combined css.
		);

		foreach ( $methods as $method ) {
			$_fonts = call_user_func( array( $this, $method ), $_fonts );
		}

		$html = str_replace( '</head>', $_fonts . '</head>', $html );

		// Remove old fonts.
		foreach ( $fonts as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		return $html;
	}

	/**
	 * Get all Google fonts from the html.
	 *
	 * @since  5.3.4
	 *
	 * @param  string $html The page html.
	 *
	 * @return array       Array with all Google fonts
	 */
	public function get_fonts( $html ) {
		// Build the regular expression.
		// ~<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*(?P<quotes>['|"]))((?:https?:)?\/\/fonts\.googleapis\.com\/css(?:(?!(?P=quotes)).)+)(?P=quotes)(?:\s+.*?)?[>]~imsg.
		$regex = implode( '', $this->regex_parts );

		// Check for Google fonts.
		preg_match_all( $regex, $html, $matches, PREG_SET_ORDER );

		// Return the matches.
		return $matches;
	}

	/**
	 * Parse and get Google fonts details.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Google fonts found in the page html.
	 *
	 * @return array        Google fonts details.
	 */
	public function parse_fonts( $fonts ) {
		$parts = array(
			'fonts'  => array(),
			'subset' => array(),
		);

		foreach ( $fonts as $font ) {
			// Decode the entities.
			$url   = html_entity_decode( $font[2] );
			// Parse the url and get the query string.
			$query_string = wp_parse_url( $url, PHP_URL_QUERY );

			// Bail if the query string is empty.
			if ( ! isset( $query_string ) ) {
				return;
			}

			// Parse the query args.
			$parsed_font = wp_parse_args( $query_string );

			$parts['fonts'][] = $parsed_font['family'];

			// Add subset to collection.
			if ( isset( $parsed_font['subset'] ) ) {
				$parts['subset'][] = $parsed_font['subset'];
			}
		}

		return $parts;
	}

	/**
	 * Convert all special chars, htmlentities and remove duplicates.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $parts The Google font details.
	 *
	 * @return arrray        Beatified font details.
	 */
	public function beautify( $parts ) {
		// URL encode & convert characters to HTML entities.
		$parts = array_map( function( $item ) {
			return array_map(
				'rawurlencode',
				array_map(
					'htmlentities',
					$item
				)
			);
		}, $parts);

		// Remove duplicates.
		return array_map(
			'array_filter',
			array_map(
				'array_unique',
				$parts
			)
		);
	}

	/**
	 * Implode Google fonts and subsets, so they can be used in combined tag.
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Font deatils.
	 *
	 * @return array        Imploaded fonts and subsets.
	 */
	public function implode_pieces( $fonts ) {
		return array(
			'fonts'   => implode( '%7C', $fonts['fonts'] ),
			'subsets' => implode( ',', $fonts['subset'] ),
		);
	}

	/**
	 * Combine Google fonts in one tag
	 *
	 * @since  5.3.4
	 *
	 * @param  array $fonts Fonts data.
	 *
	 * @return string        Combined tag.
	 */
	public function get_combined_css( $fonts ) {
		$display = apply_filters( 'sgo_google_fonts_display', 'swap' );

		// Combined url for Google fonts.
		$url          = 'https://fonts.googleapis.com/css?family=' . $fonts['fonts'] . '&subset=' . $fonts['subsets'] . '&display=' . $display;
		// Build the combined tag in case the css is missing or the request fail.
		$combined_tag = '<link rel="stylesheet" data-provider="sgoptimizer" href="' . $url . '" />';

		// Get the fonts css.
		$css = $this->get_fonts_css( $url );

		// Return the combined tag if the css is empty.
		if ( false === $css ) {
			return $combined_tag;
		}

		// Return the inline css.
		return '<style type="text/css">' . $css . '</style>';
	}

	/**
	 * Get the fonts css.
	 *
	 * @since  5.3.6
	 *
	 * @param  string $url Google fonts url.
	 *
	 * @return bool|string Fonts css on success, false on failure.
	 */
	public function get_fonts_css( $url ) {
		// Generate unique hash tag unsing the font url.
		$hash     = md5( $url );
		// Build the fonts dir.
		$dir      = Front_End_Optimization::get_instance()->assets_dir . $this->fonts_dir;
		// Build the css path.
		$css_path = $dir . '/' . $hash . '.css';

		// Setup the WP Filesystem.
		$wp_filesystem = Helper::setup_wp_filesystem();

		// Check if cached version fo the css exists.
		if ( $wp_filesystem->exists( $css_path ) ) {
			// Get the file content.
			$content = $wp_filesystem->get_contents( $css_path );

			// Return the file content if it's not empty.
			if ( ! empty( $content ) ) {
				return $content;
			}
		}

		// THE FILE DOESN'T EXIST.

		// Create the fonts dir if doesn't exists.
		if ( ! $wp_filesystem->exists( $dir ) ) {
			$is_dir_created = $wp_filesystem->mkdir( $dir );
		}

		// Try to fetch the fonts css.
		$request = wp_remote_get( $url );

		// Bail if the request fails.
		if ( is_wp_error( $request ) ) {
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return false;
		}

		// Try to create the file and bail if for some reason it's not created.
		if ( false === $wp_filesystem->touch( $css_path ) ) {
			return false;
		}

		// Get the css from the request.
		$css = wp_remote_retrieve_body( $request );

		// Add the css in the file, so it can be cached.
		$wp_filesystem->put_contents(
			$css_path,
			$css
		);

		// Finally return the fonts css.
		return $css;
	}

	/**
	 * Start buffer.
	 *
	 * @since  5.0.0
	 */
	public function start_bufffer() {
		ob_start( array( $this, 'combine_google_fonts' ) );
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

}
