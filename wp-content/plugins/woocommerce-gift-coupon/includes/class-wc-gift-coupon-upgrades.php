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

load_plugin_textdomain( WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN, false, dirname( WOOCOMMERCE_GIFT_COUPON_BASENAME ) . '/languages' );

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

	global $wpdb;

	$wpdb->hide_errors();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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
	// Update in Version 3.2.8
	if ( $new_verion == '3.2.8' ) {
		// Create primary key column.
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}woocommerce_gift_coupon` ADD id_wgc INT(10) AUTO_INCREMENT PRIMARY KEY FIRST" );
		$wpdb->query( "SET FOREIGN_KEY_CHECKS = 0" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}woocommerce_gift_coupon` MODIFY COLUMN id_user BIGINT(20) UNSIGNED NULL" );
		$wpdb->query( "SET FOREIGN_KEY_CHECKS = 1" );
		$wpdb->query( "UPDATE `{$wpdb->prefix}woocommerce_gift_coupon` SET id_wgc = id_wgc+1" );
	}
	// Update in Version 3.2.9
	if ( $new_verion == '3.2.9' ) {
		// Get ids of current coupons.
		$coupons = $wpdb->get_results( "SELECT ID FROM `{$wpdb->prefix}posts` WHERE post_type='shop_coupon'" );
		$coupons_ids = array();
		if ( !empty ( $coupons) ) {
			foreach ( $coupons as $coupon ) {
				$coupons_ids[] = $coupon->ID;
			}
		}
		// Get ids of coupons already registered on woocommerce_gift_coupons table.
		$wgc_coupons = $wpdb->get_results( "SELECT id_coupon FROM `{$wpdb->prefix}woocommerce_gift_coupon`" );
		$wgc_coupons_ids = array();
		if ( !empty ( $wgc_coupons) ) {
			foreach ( $wgc_coupons as $coupon ) {
				$wgc_coupons_ids[] = $coupon->id_coupon;
			}
			$wgc_coupons_ids = array_unique( $wgc_coupons_ids );
		}
		// Remove old registers.
		$coupons_rm = array_diff( $wgc_coupons_ids, $coupons_ids );
		if ( !empty( $coupons_rm ) ) {
			$coupons_rm = implode( ",", $coupons_rm );
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}woocommerce_gift_coupon` WHERE id_coupon IN ({$coupons_rm})" );
		}
		// Remove foreign key of users.
		$wpdb->query( "SET FOREIGN_KEY_CHECKS = 0" );
		$wpdb->query( "ALTER TABLE `{$wpdb->prefix}woocommerce_gift_coupon` DROP FOREIGN KEY wp_woocommerce_gift_coupon_ibfk_1" );
		$wpdb->query( "SET FOREIGN_KEY_CHECKS = 1" );
	}
	// Update in Version 3.3.0
	if ( $new_verion == '3.3.0' ) {
		$db_table = $wpdb->prefix . 'woocommerce_gift_coupon';
		
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $db_table . '(
			id_wgc INT(10) AUTO_INCREMENT PRIMARY KEY,
			id_user BIGINT(20) UNSIGNED NULL,
			id_coupon BIGINT(20) UNSIGNED NOT NULL,
			id_order BIGINT(20) UNSIGNED NOT NULL,
			KEY woocomerce_key_coupon_generate_coupons (id_coupon),
			KEY woocomerce_key_order_generate_coupons (id_order),
			FOREIGN KEY (id_coupon) REFERENCES ' . $wpdb->prefix . 'posts(ID) ON DELETE CASCADE,
			FOREIGN KEY (id_order) REFERENCES ' . $wpdb->prefix . 'woocommerce_order_items(order_id) ON DELETE CASCADE
			) ' . $collate;

		dbDelta( $sql );
	}
	// Update version in DB.
	do_action( 'woocommerce_gift_coupon_upgrade', $new_verion, $old_version );
	WC_Gift_Coupon_Upgrades::update_option( 'version', $new_verion );
}
