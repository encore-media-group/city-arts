<?php
namespace SiteGround_Optimizer\Lazy_Load;

use SiteGround_Optimizer\Options\Options;
/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load_Images {

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		// Replace the 'src' attr with 'data-src' in the_content.
		add_filter( 'the_content', array( $this, 'filter_html' ) );

		// If enabled replace the 'src' attr with 'data-src' in text widgets.
		if ( Options::is_enabled( 'siteground_optimizer_lazyload_textwidgets' ) ) {
			add_filter( 'widget_text', array( $this, 'filter_html' ) );
		}

		// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
		if ( Options::is_enabled( 'siteground_optimizer_lazyload_thumbnails' ) ) {
			add_filter( 'post_thumbnail_html', array( $this, 'filter_html' ) );
		}

		// If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail.
		if ( Options::is_enabled( 'siteground_optimizer_lazyload_gravatars' ) ) {
			add_filter( 'get_avatar', array( $this, 'filter_html' ) );
		}

		// If enabled replace the 'src' attr with 'data-src' in text widgets.
		if ( Options::is_enabled( 'siteground_optimizer_lazyload_woocommerce' ) ) {
			add_filter( 'woocommerce_product_get_image', array( $this, 'filter_html' ) );
			add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'filter_html' ) );
		}
	}

	/**
	 * Filter the html output.
	 *
	 * @since  5.0.0
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

		preg_match_all( '/<img[\s\r\n]+.*?>/is', $content, $matches );

		$search  = array();
		$replace = array();

		foreach ( $matches[0] as $image ) {
			// Skip already replaced images.
			if ( preg_match( "/src=['\"]data:image/is", $image ) ) {
				continue;
			}

			// Get image classes.
			preg_match( '/class=["\'](.*?)["\']/is', $image, $class_matches );

			if ( ! empty( $class_matches[1] ) ) {
				$classes = $class_matches[1];
				// Load the ignored image classes.
				$ignored_classes = apply_filters( 'sgo_lazy_load_exclude_classes', get_option( 'siteground_optimizer_excluded_lazy_load_classes', array() ) );

				// Convert all classes to array.
				$image_classes = explode( ' ', $class_matches[1] );

				// Check if the image has ignored class and bail if has.
				if ( array_intersect( $image_classes, $ignored_classes ) ) {
					continue;
				}

				$orig_image = str_replace( $classes, $classes . ' lazyload', $image );
			} else {
				$orig_image = str_replace( 'src=', 'class="lazyload" src=', $image );
			}
			// Search patterns.
			$patterns = array(
				'/(?<!noscript\>)((<img.*?src=["|\'].*?["|\']).*?(\/?>))/i',
				'/(?<!noscript\>)(<img.*?)(src)=["|\']((?!data).*?)["|\']/i',
				'/(?<!noscript\>)(<img.*?)((srcset)=["|\'](.*?)["|\'])/i',
			);

			// Replacements.
			$replacements = array(
				'$1<noscript>$1</noscript>',
				'$1src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-$2="$3"',
				'$1data-$3="$4"',
			);

			// Finally do the search/replace and return modified content.
			$new_image = preg_replace(
				$patterns,
				$replacements,
				$orig_image
			);

			array_push( $search, $image );
			array_push( $replace, $new_image );
		}

		return str_replace( $search, $replace, $content );
	}
}
