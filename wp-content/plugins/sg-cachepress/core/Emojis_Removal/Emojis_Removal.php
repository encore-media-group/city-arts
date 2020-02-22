<?php
namespace SiteGround_Optimizer\Emojis_Removal;

/**
 * SG Emojis_Removal main plugin class
 */
class Emojis_Removal {

	/**
	 * Create a {@link Supercacher} instance.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'disable_emojis' ) );
	}


	/**
	 * Disable the emojis.
	 *
	 * @since  5.0.0
	 */
	public function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch' ), 10, 2 );
	}

	/**
	 * Remove the tinymce emoji plugin
	 *
	 * @since  5.0.0
	 *
	 * @param  array $plugins An array of default TinyMCE plugins.
	 *
	 * @return array          Difference betwen the two arrays.
	 */
	public function disable_emojis_tinymce( $plugins ) {
		// Bail if the plugins is not an array.
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		// Remove the `wpemoji` plugin and return everything else.
		return array_diff( $plugins, array( 'wpemoji' ) );
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param  array  $urls          URLs to print for resource hints.
	 * @param  string $relation_type The relation type the URLs are printed for.
	 * @return array                 Difference betwen the two arrays.
	 */
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {

		if ( 'dns-prefetch' == $relation_type ) {
			// Strip out any URLs referencing the WordPress.org emoji location.
			foreach ( $urls as $key => $url ) {
				// Continue with other urls if the url doens't match.
				if ( strpos( $url, 'https://s.w.org/images/core/emoji/' ) === false ) {
					continue;
				}

				// Remove the url.
				unset( $urls[ $key ] );
			}
		}

		// Finally return the urls.
		return $urls;
	}

}
