<?php
/**
 * WooCommerce Gift Coupon Uninstall
 *
 * Uninstalling WooCommerce Gift Coupon options.
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Uninstaller
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

delete_option( 'woocommerce_gift_coupon_hide_amount' );
delete_option( 'woocommerce_gift_coupon_show_logo' );
delete_option( 'woocommerce_gift_coupon_logo' );
delete_option( 'woocommerce_gift_coupon_send' );
delete_option( 'woocommerce_gift_coupon_bg_color_header' );
delete_option( 'woocommerce_gift_coupon_bg_color_footer' );
delete_option( 'woocommerce_gift_coupon_bg_color_title' );
delete_option( 'woocommerce_gift_coupon_info_paragraph_type' );
delete_option( 'woocommerce_gift_coupon_info_paragraph' );
delete_option( 'woocommerce_gift_coupon_info_footer' );
delete_option( 'woocommerce_gift_coupon_title_type' );
delete_option( 'woocommerce_gift_coupon_title_h' );
delete_option( 'woocommerce_gift_coupon_subject' );
delete_option( 'woocommerce_gift_coupon_email_message' );

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}woocommerce_gift_coupon" );

// Clear any cached data that has been removed.
wp_cache_flush();
