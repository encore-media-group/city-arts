<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Memberships shortcodes.
 *
 * This class is responsible for adding and handling shortcodes for Memberships.
 *
 * @since 1.0.0
 */
class WC_Memberships_Shortcodes {


	/**
	 * Initializes and registers Memberships shortcodes.
	 *
	 * @since 1.0.0
	 */
	public static function initialize() {

		$shortcodes = array(
			'wcm_restrict'           => __CLASS__ . '::restrict',
			'wcm_nonmember'          => __CLASS__ . '::nonmember',
			'wcm_content_restricted' => __CLASS__ . '::content_restricted',
		);

		foreach ( $shortcodes as $shortcode => $function ) {

			/**
			 * Filter a Memberships shortcode tag.
			 *
			 * @since 1.0.0
			 *
			 * @param string $shortcode shortcode tag
			 */
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}


	/**
	 * Restrict content shortcode.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts shortcode attributes
	 * @param string|null $content the content
	 * @return string HTML output
	 */
	public static function restrict( $atts, $content = null ) {

		if ( isset( $atts['plans'] ) ) {
			$atts['plans'] = array_map( 'trim', explode( ',', $atts['plans'] ) );
		}

		if ( isset( $atts['start_after_trial'] ) ) {
			$atts['start_after_trial'] = 'yes' === $atts['start_after_trial'];
		}

		$atts = shortcode_atts( array(
			'plans'             => null,
			'delay'             => null,
			'start_after_trial' => false,
		), $atts );

		ob_start();

		wc_memberships_restrict( do_shortcode( $content ), $atts['plans'], $atts['delay'], $atts['start_after_trial'] );

		return ob_get_clean();
	}


	/**
	 * Nonmember content shortcode.
	 *
	 * When no attributes are specified, only non-members (including non-active members of any plan) will see shortcode content.
	 * When a `plans` attribute is used, non-members but also members who are not in the plans specified will see the content.
	 *
	 * @internal
	 *
	 * @since 1.1.0
	 *
	 * @param array $atts shortcode attributes
	 * @param string|null $content the shortcode content
	 * @return string content intended to non-members (or empty string)
	 */
	public static function nonmember( $atts, $content = null ) {

		$non_member_content = '';

		// hide non-member messages for super users
		if ( ! current_user_can( 'wc_memberships_access_all_restricted_content' ) ) {

			$plans         = wc_memberships_get_membership_plans();
			$exclude_plans = array();
			$non_member    = true;

			// handle optional shortcode attribute
			if ( ! empty( $atts['plans'] ) ) {
				$exclude_plans = array_map( 'trim', explode( ',', $atts['plans'] ) );
			}

			foreach ( $plans as $plan ) {

				// excluded plans can use plan IDs or slugs
				if ( ! empty( $exclude_plans ) && ! in_array( $plan->get_id(), $exclude_plans, false ) && ! in_array( $plan->get_slug(), $exclude_plans, false ) ) {
					continue;
				}

				if ( wc_memberships_is_user_active_member( get_current_user_id(), $plan ) ) {
					$non_member = false;
					break;
				}
			}

			if ( $non_member ) {
				$non_member_content = do_shortcode( $content );
			}
		}

		return $non_member_content;
	}


	/**
	 * Restricted content messages shortcode.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts shortcode attributes
	 * @param string|null $content content
	 * @return string HTML shortcode result
	 */
	public static function content_restricted( $atts, $content = null ) {

		$object_id = isset( $_GET['r'] ) && is_numeric( $_GET['r'] ) ? absint( $_GET['r'] ) : null;
		$post      = null;
		$term      = null;
		$output    = '';

		if ( isset( $_GET['wcm_redirect_to'], $_GET['wcm_redirect_id'] ) && is_numeric( $_GET['wcm_redirect_id'] ) ) {

			$object_id        = absint( $_GET['wcm_redirect_id'] );
			$object_type_name = (string) $_GET['wcm_redirect_to'];

			if ( in_array( $object_type_name, get_post_types(), true ) ) {
				$post = get_post( $object_id );
			} else {
				$term = get_term( $object_id, $object_type_name );
			}

		} elseif ( $object_id > 0 ) {

			$term = get_term( $object_id );
			$post = get_post( $object_id );
		}

		if ( $term instanceof \WP_Term ) {

			if ( 'product_cat' === $term->taxonomy ) {

				if ( ! current_user_can( 'wc_memberships_view_restricted_product_taxonomy_term', $term->taxonomy, $term->term_id ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'product_category_viewing_restricted', array( 'term' => $term ) );
				} elseif ( ! current_user_can( 'wc_memberships_view_delayed_taxonomy_term', $term->taxonomy, $term->term_id ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'product_category_viewing_delayed', array( 'term' => $term ) );
				}

			} else {

				if ( ! current_user_can( 'wc_memberships_view_restricted_taxonomy_term', $term->taxonomy, $term->term_id ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'content_category_restricted', array( 'term' => $term ) );
				} elseif ( ! current_user_can( 'wc_memberships_view_delayed_taxonomy_term', $term->taxonomy, $term->term_id ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'content_category_delayed', array( 'term' => $term ) );
				}
			}

		} elseif ( $post instanceof \WP_Post ) {

			if ( in_array( $post->post_type, array( 'product', 'product_variation' ) ) ) {

				if ( ! current_user_can( 'wc_memberships_view_restricted_product', $post->ID ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'product_viewing_restricted', array( 'post' => $post ) );
				} elseif ( ! current_user_can( 'wc_memberships_view_delayed_product', $post->ID ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'product_access_delayed', array( 'post' => $post ) );
				}

			} else {

				if ( ! current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'content_restricted', array( 'post' => $post ) );
				} elseif ( ! current_user_can( 'wc_memberships_view_delayed_post_content', $post->ID ) ) {
					$output .= \WC_Memberships_User_Messages::get_message_html( 'content_delayed', array( 'post' => $post ) );
				}
			}
		}

		return $output;
	}


}
