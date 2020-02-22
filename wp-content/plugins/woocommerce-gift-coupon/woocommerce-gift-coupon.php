<?php
/**
 * Plugin Name: WooCommerce Gift Coupon
 * Description: Buy coupons as a product to give a friend. Generate coupon code PDF and send it by email or download manually on the user profile.
 * Depends: WooCommerce
 * Version: 3.3.0
 * Author: Alberto Pérez
 * Text Domain: woocommerce-gift-coupon
 * WC tested up to: 3.5
 * Domain Path: /languages
 * Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ
 *
 * @package WooCommerce Gift Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOOCOMMERCE_GIFT_COUPON_VERSION', '3.3.0' );
define( 'WOOCOMMERCE_GIFT_COUPON_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_GIFT_COUPON_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOCOMMERCE_GIFT_COUPON_BASENAME', plugin_basename( __FILE__ ) );
define( 'WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN', 'woocommerce-gift-coupon' );

register_activation_hook( __FILE__, 'woocommerce_gift_coupon_activation' );
register_deactivation_hook( __FILE__, 'woocommerce_gift_coupon_deactivation' );
register_uninstall_hook( __FILE__, 'woocommerce_gift_coupon_uninstall' );

/**
 * Helper function to activate the plugin.
 */
function woocommerce_gift_coupon_activation() {
	if ( ! function_exists( 'WC' ) ) {
		throw new Exception( __( 'Please active first WooCommerce plugin.', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) );
	}

	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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

	$post = array(
		'post_author'  => 1,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_title'   => 'Coupon example product',
		'post_parent'  => '',
		'post_type'    => 'product',
	);

	$post_id = wp_insert_post( $post );

	wp_set_object_terms( $post_id, 'simple', 'product_type' );
	update_post_meta( $post_id, 'giftcoupon', 'yes' );
	update_post_meta( $post_id, '_visibility', 'visible' );
	update_post_meta( $post_id, '_stock_status', 'instock' );
	update_post_meta( $post_id, 'total_sales', '0' );
	update_post_meta( $post_id, '_downloadable', 'no' );
	update_post_meta( $post_id, '_virtual', 'no' );
	update_post_meta( $post_id, '_regular_price', '30' );
	update_post_meta( $post_id, '_sale_price', '' );
	update_post_meta( $post_id, '_purchase_note', '' );
	update_post_meta( $post_id, '_featured', 'no' );
	update_post_meta( $post_id, '_weight', '' );
	update_post_meta( $post_id, '_length', '' );
	update_post_meta( $post_id, '_width', '' );
	update_post_meta( $post_id, '_height', '' );
	update_post_meta( $post_id, '_sku', '' );
	update_post_meta( $post_id, '_product_attributes', array() );
	update_post_meta( $post_id, '_sale_price_dates_from', '' );
	update_post_meta( $post_id, '_sale_price_dates_to', '' );
	update_post_meta( $post_id, '_price', '30' );
	update_post_meta( $post_id, '_sold_individually', '' );
	update_post_meta( $post_id, '_manage_stock', 'no' );
	update_post_meta( $post_id, '_backorders', 'no' );
	update_post_meta( $post_id, '_stock', '' );
	update_post_meta( $post_id, 'coupon_amount', '30' );
}

/**
 * Helper function to desactivate the plugin.
 */
function woocommerce_gift_coupon_deactivation() {
	flush_rewrite_rules();
}

require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/class-wc-gift-coupon-upgrades.php';
require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/class-wc-gift-coupon-meta-box-coupon-data.php';
