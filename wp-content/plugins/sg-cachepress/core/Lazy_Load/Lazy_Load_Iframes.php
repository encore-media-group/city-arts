<?php
namespace SiteGround_Optimizer\Lazy_Load;

use SiteGround_Optimizer\Options\Options;
/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load_Iframes {

	/**
	 * The constructor.
	 *
	 * @since 5.4.3
	 */
	public function __construct() {
		add_filter( 'the_content', array( $this, 'filter_html' ) );
	}

	/**
	 * Filter the html output.
	 *
	 * @since  5.4.3
	 *
	 * @param  string $content The content.
	 *
	 * @return string          Modified content.
	 */
	public function filter_html( $content ) {
		// Bail if it's feed or if the content is empty.
		if (
			is_feed() ||
			empty( $content ) ||
			is_admin() ||
			( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) ||
			method_exists( 'FLBuilderModel', 'is_builder_enabled' )
		) {
			return $content;
		}

		preg_match_all( '/(?:<iframe[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/i', $content, $matches );

		$search  = array();
		$replace = array();

		foreach ( $matches[0] as $iframe ) {
			// Skip already replaced iframes.
			if ( preg_match( "/class=['\"][\w\s]*(lazyload)+[\w\s]*['\"]/is", $iframe ) ) {
				continue;
			}

			// Get iframe classes.
			preg_match( '/class=["\'](.*?)["\']/is', $iframe, $class_matches );


			if ( ! empty( $class_matches[1] ) ) {
				$classes = $class_matches[1];
				// Load the ignored iframe classes.
				$ignored_classes = apply_filters( 'sgo_lazy_load_exclude_classes', get_option( 'siteground_optimizer_excluded_lazy_load_classes', array() ) );

				// Convert all classes to array.
				$iframe_classes = explode( ' ', $class_matches[1] );

				// Check if the iframe has ignored class and bail if has.
				if ( array_intersect( $iframe_classes, $ignored_classes ) ) {
					continue;
				}

				$orig_iframe = str_replace( $classes, $classes . ' lazyload', $iframe );
			} else {
				$orig_iframe = str_replace( '<iframe', '<iframe class="lazyload"', $iframe );
			}

			// Search patterns.
			$patterns = array(
				'/(<iframe.*?)(src)=["|\']((?!data).*?)["|\']/i',
			);

			// Replacements.
			$replacements = array(
				'$1data-$2="$3"',
			);

			// Finally do the search/replace and return modified content.
			$new_iframe = preg_replace(
				$patterns,
				$replacements,
				$orig_iframe
			);

			array_push( $search, $iframe );
			array_push( $replace, $new_iframe );
		}

		return str_replace( $search, $replace, $content );
	}
}
