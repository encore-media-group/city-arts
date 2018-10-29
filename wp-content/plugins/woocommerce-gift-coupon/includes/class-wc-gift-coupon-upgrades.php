<?php
/**
 * WooCommerce Gift Coupon Settings
 *
 * Set WooCommerce Gift Coupon options.
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/functions.php';
require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/mail.php';

load_plugin_textdomain( 'woocommerce-gift-coupon', false, dirname( WOOCOMMERCE_GIFT_COUPON_BASENAME ) . '/languages' );

/**
 * WC_Gift_Coupon_Upgrades Class.
 */
class WC_Gift_Coupon_Upgrades {

	/**
	 * Get the version
	 *
	 * @param string $name Name of metadata.
	 * @param string $default Version value.
	 */
	public static function get_option( $name, $default = false ) {
		$option = get_option( 'woocommerce_gift_coupon' );

		if ( false == $option ) {
			return $default;
		}

		if ( isset( $option[ $name ] ) ) {
			return $option[ $name ];
		} else {
			return $default;
		}
	}

	/**
	 * Update the version
	 *
	 * @param string $name Name of metadata.
	 * @param string $value Version value.
	 */
	public static function update_option( $name, $value ) {
		$option = get_option( 'woocommerce_gift_coupon' );
		$option = ( false == $option ) ? array() : (array) $option;
		$option = array_merge( $option, array( $name => $value ) );
		update_option( 'woocommerce_gift_coupon', $option );
	}
}

add_action( 'admin_init', 'woocommerce_gift_coupon_upgrade' );

/**
 * Helper function to upgrade database.
 */
function woocommerce_gift_coupon_upgrade() {
	$old_version = WC_Gift_Coupon_Upgrades::get_option( 'version', '0' );
	$new_verion  = WOOCOMMERCE_GIFT_COUPON_VERSION;

	if ( $old_version == $new_verion ) {
		return;
	}

	// Update in Version 2.7.
	if ( $new_verion == '2.7' ) {
		$cat_gift_coupoon = get_term_by( 'slug', 'giftcoupon', 'product_cat' );
		if ( ! empty( $cat_gift_coupoon ) && isset( $cat_gift_coupoon->term_id ) ) {
			$args     = array(
				'post_type' => 'product',
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $cat_gift_coupoon->term_id,
						'operator' => 'IN',
					),
				),
			);
			$products = new WP_Query( $args );
			while ( $products->have_posts() ) {
				$products->the_post();
				$id_coupon_product = get_the_ID();
				update_post_meta( $id_coupon_product, 'giftcoupon', 'yes' );
			}
		}
	}
	// Update in Version 2.8.
	if ( $new_verion == '2.8' ) {
		update_option( 'woocommerce_gift_coupon_title_type', 0 );
		update_option( 'woocommerce_gift_coupon_info_paragraph_type', 0 );
	}
	// Update in Version 2.9.
	if ( $new_verion == '2.9' ) {
		update_option( 'woocommerce_gift_coupon_hide_amount', 0 );
	}
	// Update in Version 3.2.4.
	if ( $new_verion == '3.2.4' ) {
		// Remove tmp pdfs.
		$upload_dir = wp_upload_dir();
		$pathupload = $upload_dir['basedir'] . '/woocommerce-gift-coupon';
		$files      = glob( $pathupload . '/*' );
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
		// Change htaccess to allow.
		if ( wp_mkdir_p( $pathupload ) ) {
			$pdf_handle = fopen( trailingslashit( $pathupload ) . '.htaccess', 'w' );
			if ( $pdf_handle ) {
				fwrite( $pdf_handle, 'allow from all' );
				fclose( $pdf_handle );
			}
		}
	}

	// Update version in DB.
	do_action( 'woocommerce_gift_coupon_upgrade', $new_verion, $old_version );
	WC_Gift_Coupon_Upgrades::update_option( 'version', $new_verion );
}
