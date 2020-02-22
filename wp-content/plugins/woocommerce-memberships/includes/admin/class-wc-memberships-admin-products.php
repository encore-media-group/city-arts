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
 * @package   WC-Memberships/Admin
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Handle memberships product data in admin screens.
 *
 * @since 1.9.0
 */
class WC_Memberships_Admin_Products {


	/**
	 * Products memberships data admin handler constructor.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {

		// duplicate memberships settings for products
		if ( Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
			add_action( 'woocommerce_product_duplicate', array( $this, 'duplicate_product_memberships_data' ), 10, 2 );
		} else {
			add_action( 'woocommerce_duplicate_product', array( $this, 'duplicate_product_memberships_data' ), 10, 2 );
		}
	}


	/**
	 * Duplicates memberships data for a product.
	 *
	 * TODO update phpdoc and method when WC 3.0 is the minimal requirement {FN 2017-01-13}
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WC_Product $new_product new product (was product id in WC versions earlier than 3.0)
	 * @param \WP_Post|\WC_Product $old_product old product (was old post object in WC versions earlier than 3.0)
	 */
	public function duplicate_product_memberships_data( $new_product, $old_product ) {

		if ( Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
			$new_product_id        = $new_product->get_id();
			$old_product_id        = $old_product->get_id();
			$old_product_post_type = get_post_type( $old_product );
		} else {
			$new_product_id        = $new_product;
			$new_product           = wc_get_product( $new_product_id );
			$old_product_id        = $old_product->ID;
			$old_product_post_type = $old_product->post_type;
		}

		// get product restriction rules
		$product_restriction_rules = wc_memberships()->get_rules_instance()->get_rules( array(
			'rule_type'         => 'product_restriction',
			'object_id'         => $old_product_id,
			'content_type'      => 'post_type',
			'content_type_name' => $old_product_post_type,
			'exclude_inherited' => true,
			'plan_status'       => 'any',
		) );

		// get purchasing discount rules
		$purchasing_discount_rules = wc_memberships()->get_rules_instance()->get_rules( array(
			'rule_type'         => 'purchasing_discount',
			'object_id'         => $old_product_id,
			'content_type'      => 'post_type',
			'content_type_name' => $old_product_post_type,
			'exclude_inherited' => true,
			'plan_status'       => 'any',
		) );

		$product_rules = array_merge( $product_restriction_rules, $purchasing_discount_rules );

		// duplicate rules
		if ( ! empty( $product_rules ) ) {

			$all_rules = get_option( 'wc_memberships_rules' );

			foreach ( $product_rules as $rule ) {

				$new_rule               = $rule->get_raw_data();
				$new_rule['object_ids'] = array( $new_product_id );
				$all_rules[]            = $new_rule;
			}

			update_option( 'wc_memberships_rules', $all_rules );
		}

		// duplicate custom messages
		foreach ( array( 'product_viewing_restricted', 'product_purchasing_restricted' ) as $message_type ) {

			if ( $use_custom = wc_memberships_get_content_meta( $old_product, "_wc_memberships_use_custom_{$message_type}_message", true ) ) {
				wc_memberships_set_content_meta( $new_product, "_wc_memberships_use_custom_{$message_type}_message", $use_custom );
			}

			if ( $message = wc_memberships_get_content_meta( $old_product, "_wc_memberships_{$message_type}_message", true ) ) {
				wc_memberships_set_content_meta( $new_product, "_wc_memberships_{$message_type}_message", $message );
			}
		}

		$plans = wc_memberships_get_membership_plans();

		if ( ! empty( $plans ) ) {

			// duplicate 'grants access to'
			foreach ( $plans as $plan ) {

				if ( $plan->has_product( $old_product_id ) ) {
					// add new product id to product ids
					$plan->set_product_ids( $new_product_id, true );
				}
			}
		}

		// duplicate other settings
		wc_memberships_set_content_meta( $new_product, '_wc_memberships_force_public',      wc_memberships_get_content_meta( $old_product, '_wc_memberships_force_public', true      ) );
		wc_memberships_set_content_meta( $new_product, '_wc_memberships_exclude_discounts', wc_memberships_get_content_meta( $old_product, '_wc_memberships_exclude_discounts', true ) );
	}


}
