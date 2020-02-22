<?php
/**
 * Compatibility file for WooCommerce Subscriptions
 *
 * @author      StoreApps
 * @since       3.3.0
 * @version     1.1
 * @package     WooCommerce Smart Coupons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCS_SC_Compatibility' ) ) {

	/**
	 * Class for handling compatibility with WooCommerce Subscriptions
	 */
	class WCS_SC_Compatibility {

		/**
		 * Variable to hold instance of WCS_SC_Compatibility
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
				add_action( 'wp_loaded', array( $this, 'sc_wcs_renewal_filters' ), 20 );
				add_filter( 'woocommerce_subscriptions_validate_coupon_type', array( $this, 'smart_coupon_as_valid_subscription_coupon_type' ), 10, 3 );
				add_filter( 'wc_smart_coupons_settings', array( $this, 'smart_coupons_settings' ) );
				add_action( 'admin_footer', array( $this, 'sc_wcs_styles_and_scripts' ) );
				add_action( 'admin_init', array( $this, 'sc_wcs_settings' ) );
			}

		}

		/**
		 * Get single instance of WCS_SC_Compatibility
		 *
		 * @return WCS_SC_Compatibility Singleton object of WCS_SC_Compatibility
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Handle call to functions which is not available in this class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			global $woocommerce_smart_coupon;

			if ( ! is_callable( array( $woocommerce_smart_coupon, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $woocommerce_smart_coupon, $function_name ), $arguments );
			} else {
				return call_user_func( array( $woocommerce_smart_coupon, $function_name ) );
			}
		}

		/**
		 * Function to manage appropriate filter for applying Smart Coupons feature in renewal order
		 */
		public function sc_wcs_renewal_filters() {
			if ( self::is_wcs_gte( '2.0.0' ) ) {
				add_filter( 'wcs_get_subscription', array( $this, 'sc_wcs_modify_subscription' ) );
				add_filter( 'wcs_renewal_order_meta', array( $this, 'sc_wcs_renewal_order_meta' ), 10, 3 );
				add_filter( 'wcs_new_order_created', array( $this, 'sc_wcs_modify_renewal_order_meta' ), 10, 2 );
				add_filter( 'wcs_renewal_order_items', array( $this, 'sc_wcs_modify_renewal_order' ), 10, 3 );
				add_filter( 'wcs_renewal_order_items', array( $this, 'sc_wcs_renewal_order_items' ), 10, 3 );
				add_filter( 'wcs_renewal_order_created', array( $this, 'sc_wcs_renewal_complete_payment' ), 10, 2 );
				add_action( 'woocommerce_update_order', array( $this, 'smart_coupons_contribution' ), 8 );
				add_filter( 'is_show_gift_certificate_receiver_detail_form', array( $this, 'is_show_gift_certificate_receiver_detail_form' ), 10, 2 );
			} else {
				add_filter( 'woocommerce_subscriptions_renewal_order_items', array( $this, 'sc_modify_renewal_order' ), 10, 5 );
				add_filter( 'woocommerce_subscriptions_renewal_order_items', array( $this, 'sc_subscriptions_renewal_order_items' ), 10, 5 );
				add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'sc_renewal_complete_payment' ), 10, 4 );
			}
		}

		/**
		 * Function to manage payment method for renewal orders based on availability of store credit (WCS 2.0+)
		 *
		 * @param WC_Subscription $subscription Subscription object.
		 * @return WC_Subscription $subscription
		 */
		public function sc_wcs_modify_subscription( $subscription = null ) {

			if ( did_action( 'woocommerce_scheduled_subscription_payment' ) < 1 ) {
				return $subscription;
			}

			if ( ! empty( $subscription ) && $subscription instanceof WC_Subscription ) {

				$pay_from_credit_of_original_order = get_option( 'pay_from_smart_coupon_of_original_order', 'yes' );

				if ( 'yes' !== $pay_from_credit_of_original_order ) {
					return $subscription;
				}

				if ( $this->is_wc_gte_30() ) {
					$subscription_parent_order = $subscription->get_parent();
					$original_order_id         = ( is_object( $subscription_parent_order ) && is_callable( array( $subscription_parent_order, 'get_id' ) ) ) ? $subscription_parent_order->get_id() : 0;
				} else {
					$original_order_id = ( ! empty( $subscription->order->id ) ) ? $subscription->order->id : 0;
				}

				if ( empty( $original_order_id ) ) {
					return $subscription;
				}

				$renewal_total                 = $subscription->get_total();
				$original_order                = wc_get_order( $original_order_id );
				$coupon_used_in_original_order = ( is_object( $original_order ) && is_callable( array( $original_order, 'get_used_coupons' ) ) ) ? $original_order->get_used_coupons() : array();

				if ( $this->is_wc_gte_30() ) {
					$order_payment_method = $original_order->get_payment_method();
				} else {
					$order_payment_method = ( ! empty( $original_order->payment_method ) ) ? $original_order->payment_method : 0;
				}

				if ( count( $coupon_used_in_original_order ) > 0 ) {
					foreach ( $coupon_used_in_original_order as $coupon_code ) {
						$coupon = new WC_Coupon( $coupon_code );
						if ( $this->is_wc_gte_30() ) {
							$coupon_amount = $coupon->get_amount();
							$discount_type = $coupon->get_discount_type();
						} else {
							$coupon_amount = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
							$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
						}
						if ( ! empty( $discount_type ) && 'smart_coupon' === $discount_type && ! empty( $coupon_amount ) ) {
							if ( $coupon_amount >= $renewal_total ) {
								$subscription->set_payment_method( '' );
							} else {
								$payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
								if ( ! empty( $payment_gateways[ $order_payment_method ] ) ) {
									$payment_method = $payment_gateways[ $order_payment_method ];
									$subscription->set_payment_method( $payment_method );
								}
							}
						}
					}
				}
			}

			return $subscription;
		}

		/**
		 * Function to add meta which is necessary for coupon processing, in order
		 *
		 * @param   array           $meta Order meta.
		 * @param   WC_Order        $to_order Order to copy meta to.
		 * @param   WC_Subscription $from_order Order to copy meta from.
		 * @return  array $meta
		 */
		public function sc_wcs_renewal_order_meta( $meta, $to_order, $from_order ) {

			if ( $this->is_wc_gte_30() ) {
				$order    = $from_order->get_parent();
				$order_id = ( is_object( $order ) && is_callable( array( $order, 'get_id' ) ) ) ? $order->get_id() : 0;
			} else {
				$order    = $from_order->order;
				$order_id = ( ! empty( $order->id ) ) ? $order->id : 0;
			}

			if ( empty( $order_id ) ) {
				return $meta;
			}

			$meta_exists = array(
				'coupon_sent'                => false,
				'gift_receiver_email'        => false,
				'gift_receiver_message'      => false,
				'sc_called_credit_details'   => false,
				'smart_coupons_contribution' => false,
			);

			foreach ( $meta as $index => $data ) {
				if ( $this->is_wcs_gte( '2.2.0' ) ) {
					if ( ! empty( $data['meta_key'] ) ) {
						$prefixed_key   = wcs_maybe_prefix_key( $data['meta_key'] );
						$unprefixed_key = ( $data['meta_key'] === $prefixed_key ) ? substr( $data['meta_key'], 1 ) : $data['meta_key'];
						if ( array_key_exists( $unprefixed_key, $meta_exists ) ) {
							unset( $meta[ $index ] );
						}
					}
				} else {
					if ( ! empty( $data['meta_key'] ) && array_key_exists( $data['meta_key'], $meta_exists ) ) {
						$meta_exists[ $data['meta_key'] ] = true; // WPCS: slow query ok.
					}
				}
			}

			foreach ( $meta_exists as $key => $value ) {
				if ( $value ) {
					continue;
				}
				$meta_value = get_post_meta( $order_id, $key, true );

				if ( empty( $meta_value ) ) {
					continue;
				}

				if ( $this->is_wcs_gte( '2.2.0' ) ) {
					$renewal_order_id = ( is_object( $to_order ) && is_callable( array( $to_order, 'get_id' ) ) ) ? $to_order->get_id() : 0;
					if ( 'coupon_sent' === $key ) {
						// update_post_meta( $renewal_order_id, $key, 'no' );
						// No need to update meta 'coupon_sent' as it's being updated by function sc_modify_renewal_order in this class.
						continue;
					} elseif ( 'smart_coupons_contribution' === $key ) {
						update_post_meta( $renewal_order_id, $key, array() );
					} else {
						update_post_meta( $renewal_order_id, $key, $meta_value );
					}
				} else {
					if ( ! isset( $meta ) || ! is_array( $meta ) ) {
						$meta = array();
					}
					$meta[] = array(
						'meta_key'   => $key,
						'meta_value' => $meta_value,
					);  // WPCS: slow query ok.
				}
			}

			return $meta;
		}

		/**
		 * Function to modify renewal order meta
		 *
		 * @param WC_Order        $renewal_order Order created on subscription renewal.
		 * @param WC_Subscription $subscription Subscription we're basing the order off of.
		 * @return WC_Order $renewal_order
		 */
		public function sc_wcs_modify_renewal_order_meta( $renewal_order = null, $subscription = null ) {
			global $wpdb;

			if ( $this->is_wc_gte_30() ) {
				$renewal_order_id = ( is_object( $renewal_order ) && is_callable( array( $renewal_order, 'get_id' ) ) ) ? $renewal_order->get_id() : 0;
			} else {
				$renewal_order_id = ( ! empty( $renewal_order->id ) ) ? $renewal_order->id : 0;
			}

			if ( empty( $renewal_order_id ) ) {
				return $renewal_order;
			}

			$sc_called_credit_details = get_post_meta( $renewal_order_id, 'sc_called_credit_details', true );
			if ( empty( $sc_called_credit_details ) ) {
				return $renewal_order;
			}

			$old_order_item_ids = ( ! empty( $sc_called_credit_details ) ) ? array_keys( $sc_called_credit_details ) : array();

			if ( ! empty( $old_order_item_ids ) ) {

				$meta_keys   = array( '_variation_id', '_product_id' );
				$how_many    = count( $old_order_item_ids );
				$placeholder = array_fill( 0, $how_many, '%d' );

				// @codingStandardsIgnoreStart.
				$query_to_fetch_product_ids = $wpdb->prepare(
					"SELECT woim.order_item_id,
					(CASE
						WHEN woim.meta_key = %s AND woim.meta_value > 0 THEN woim.meta_value
						WHEN woim.meta_key = %s AND woim.meta_value > 0 THEN woim.meta_value
					END) AS product_id
					FROM {$wpdb->prefix}woocommerce_order_itemmeta AS woim
					WHERE woim.order_item_id IN ( " . implode( ',', $placeholder ) . ' )
						AND woim.meta_key IN ( %s, %s )
					GROUP BY woim.order_item_id',
					array_merge( $meta_keys, $old_order_item_ids, array_reverse( $meta_keys ) )
				);
				// @codingStandardsIgnoreEnd.

				$product_ids_results = $wpdb->get_results( $query_to_fetch_product_ids, 'ARRAY_A' ); // WPCS: cache ok, db call ok, unprepared SQL ok.

				if ( ! is_wp_error( $product_ids_results ) && ! empty( $product_ids_results ) ) {
					$product_to_old_item = array();
					foreach ( $product_ids_results as $result ) {
						$product_to_old_item[ $result['product_id'] ] = $result['order_item_id'];
					}

					$found_product_ids = ( ! empty( $product_to_old_item ) ) ? $product_to_old_item : array();

					$query_to_fetch_new_order_item_ids = $wpdb->prepare(
						"SELECT woim.order_item_id,
																			(CASE
																				WHEN woim.meta_value > 0 THEN woim.meta_value
																			END) AS product_id
																			FROM {$wpdb->prefix}woocommerce_order_items AS woi
																				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim
																					ON (woim.order_item_id = woi.order_item_id AND woim.meta_key IN ( %s, %s ))
																			WHERE woi.order_id = %d
																				AND woim.order_item_id IS NOT NULL
																			GROUP BY woim.order_item_id",
						'_product_id',
						'_variation_id',
						$renewal_order_id
					);

					$new_order_item_ids_result = $wpdb->get_results( $query_to_fetch_new_order_item_ids, 'ARRAY_A' ); // WPCS: cache ok, db call ok, unprepared SQL ok.

					if ( ! is_wp_error( $new_order_item_ids_result ) && ! empty( $new_order_item_ids_result ) ) {
						$product_to_new_item = array();
						foreach ( $new_order_item_ids_result as $result ) {
							$product_to_new_item[ $result['product_id'] ] = $result['order_item_id'];
						}
					}
				}
			}
			foreach ( $sc_called_credit_details as $item_id => $credit_amount ) {
				$product_id = array_search( $item_id, $product_to_old_item, true );
				if ( false !== $product_id ) {
					$sc_called_credit_details[ $product_to_new_item[ $product_id ] ] = $credit_amount;
					unset( $sc_called_credit_details[ $product_to_old_item[ $product_id ] ] );
				}
			}

			update_post_meta( $renewal_order_id, 'sc_called_credit_details', $sc_called_credit_details );
			return $renewal_order;
		}

		/**
		 * New function to handle auto generation of coupon from renewal orders (WCS 2.0+)
		 *
		 * @param array           $order_items Order items.
		 * @param WC_Order        $renewal_order Order created on subscription renewal.
		 * @param WC_Subscription $subscription Subscription we're basing the order off of.
		 * @return array $order_items
		 */
		public function sc_wcs_modify_renewal_order( $order_items = null, $renewal_order = null, $subscription = null ) {

			if ( $this->is_wc_gte_30() ) {
				$subscription_parent_order = $subscription->get_parent();
				$subscription_order_id     = ( is_object( $subscription_parent_order ) && is_callable( array( $subscription_parent_order, 'get_id' ) ) ) ? $subscription_parent_order->get_id() : 0;
				$renewal_order_id          = ( is_callable( array( $renewal_order, 'get_id' ) ) ) ? $renewal_order->get_id() : 0;
			} else {
				$subscription_order_id = ( ! empty( $subscription->order->id ) ) ? $subscription->order->id : 0;
				$renewal_order_id      = ( ! empty( $renewal_order->id ) ) ? $renewal_order->id : 0;
			}

			$order_items = $this->sc_modify_renewal_order( $order_items, $subscription_order_id, $renewal_order_id );
			return $order_items;
		}

		/**
		 * New function to modify order_items of renewal order (WCS 2.0+)
		 *
		 * @param array           $order_items Order items.
		 * @param WC_Order        $renewal_order Order created on subscription renewal.
		 * @param WC_Subscription $subscription Subscription we're basing the order off of.
		 * @return array $order_items
		 */
		public function sc_wcs_renewal_order_items( $order_items = null, $renewal_order = null, $subscription = null ) {

			if ( $this->is_wc_gte_30() ) {
				$subscription_parent_order = $subscription->get_parent();
				$subscription_order_id     = ( is_object( $subscription_parent_order ) && is_callable( array( $subscription_parent_order, 'get_id' ) ) ) ? $subscription_parent_order->get_id() : 0;
				$renewal_order_id          = ( is_callable( array( $renewal_order, 'get_id' ) ) ) ? $renewal_order->get_id() : 0;
			} else {
				$subscription_order_id = ( ! empty( $subscription->order->id ) ) ? $subscription->order->id : 0;
				$renewal_order_id      = ( ! empty( $renewal_order->id ) ) ? $renewal_order->id : 0;
			}

			$order_items = $this->sc_subscriptions_renewal_order_items( $order_items, $subscription_order_id, $renewal_order_id, 0, 'child' );
			return $order_items;
		}

		/**
		 * New function to mark payment complete for renewal order (WCS 2.0+)
		 *
		 * @param WC_Order        $renewal_order Order object.
		 * @param WC_Subscription $subscription Subscription we're basing the order off of.
		 * @return WC_Order $renewal_order
		 */
		public function sc_wcs_renewal_complete_payment( $renewal_order = null, $subscription = null ) {
			$this->sc_renewal_complete_payment( $renewal_order );
			return $renewal_order;
		}

		/**
		 * Function to save Smart Coupon's contribution in discount
		 *
		 * @param int $order_id Order ID.
		 */
		public function smart_coupons_contribution( $order_id = 0 ) {

			if ( self::is_wcs_gte( '2.0.0' ) ) {
				$is_renewal_order = wcs_order_contains_renewal( $order_id );
			} else {
				$is_renewal_order = WC_Subscriptions_Renewal_Order::is_renewal( $order_id );
			}

			if ( ! $is_renewal_order ) {
				return;
			}

			$applied_coupons = ( is_object( WC()->cart ) && isset( WC()->cart->applied_coupons ) ) ? WC()->cart->applied_coupons : array();

			if ( ! empty( $applied_coupons ) ) {

				foreach ( $applied_coupons as $code ) {

					$smart_coupon = new WC_Coupon( $code );

					if ( $this->is_wc_gte_30() ) {
						$discount_type = $smart_coupon->get_discount_type();
					} else {
						$discount_type = ( ! empty( $smart_coupon->discount_type ) ) ? $smart_coupon->discount_type : '';
					}

					if ( 'smart_coupon' === $discount_type ) {

						$smart_coupon_credit_used = get_post_meta( $order_id, 'smart_coupons_contribution', true );

						$cart_smart_coupon_credit_used = WC()->cart->smart_coupon_credit_used;

						$update = false;

						if ( ! empty( $smart_coupon_credit_used ) ) {
							if ( ! empty( $cart_smart_coupon_credit_used ) ) {
								foreach ( $cart_smart_coupon_credit_used as $code => $amount ) {
									$smart_coupon_credit_used[ $code ] = $amount;
									$update                            = true;
								}
							}
						} else {
							$smart_coupon_credit_used = $cart_smart_coupon_credit_used;
							$update                   = true;
						}

						if ( $update ) {
							update_post_meta( $order_id, 'smart_coupons_contribution', $smart_coupon_credit_used );
						}
					}
				}
			}
		}

		/**
		 * Set 'coupon_sent' as 'no' for renewal order to allow auto generation of coupons (if applicable)
		 *
		 * @param array  $order_items Associative array of order items.
		 * @param int    $original_order_id Post ID of the order being used to purchased the subscription being renewed.
		 * @param int    $renewal_order_id Post ID of the order created for renewing the subscription.
		 * @param int    $product_id ID of the product being renewed.
		 * @param string $new_order_role The role the renewal order is taking, one of 'parent' or 'child'.
		 * @return array $order_items
		 */
		public function sc_modify_renewal_order( $order_items = null, $original_order_id = 0, $renewal_order_id = 0, $product_id = 0, $new_order_role = null ) {

			if ( self::is_wcs_gte( '2.0.0' ) ) {
				$is_subscription_order = wcs_order_contains_subscription( $original_order_id );
			} else {
				$is_subscription_order = WC_Subscriptions_Order::order_contains_subscription( $original_order_id );
			}
			if ( $is_subscription_order ) {
				$return = false;
			} else {
				$return = true;
			}
			if ( $return ) {
				return $order_items;
			}

			$is_recursive = false;
			if ( ! empty( $order_items ) ) {
				foreach ( $order_items as $order_item ) {
					$send_coupons_on_renewals = ( ! empty( $order_item['product_id'] ) ) ? get_post_meta( $order_item['product_id'], 'send_coupons_on_renewals', true ) : 'no';
					if ( 'yes' === $send_coupons_on_renewals ) {
						$is_recursive = true;
						break;  // if in any order item recursive is enabled, it will set coupon_sent as 'no'.
					}
				}
			}
			$stop_recursive_coupon_generation = get_option( 'stop_recursive_coupon_generation', 'no' );
			if ( ( empty( $stop_recursive_coupon_generation ) || 'no' === $stop_recursive_coupon_generation ) && $is_recursive ) {
				update_post_meta( $renewal_order_id, 'coupon_sent', 'no' );
			} else {
				update_post_meta( $renewal_order_id, 'coupon_sent', 'yes' );
			}

			return $order_items;
		}

		/**
		 * Function to modify order_items of renewal order
		 *
		 * @param array  $order_items Associative array of order items.
		 * @param int    $original_order_id Post ID of the order being used to purchased the subscription being renewed.
		 * @param int    $renewal_order_id Post ID of the order created for renewing the subscription.
		 * @param int    $product_id ID of the product being renewed.
		 * @param string $new_order_role The role the renewal order is taking, one of 'parent' or 'child'.
		 * @return array $order_items
		 */
		public function sc_subscriptions_renewal_order_items( $order_items = null, $original_order_id = 0, $renewal_order_id = 0, $product_id = 0, $new_order_role = null ) {

			if ( self::is_wcs_gte( '2.0.0' ) ) {
				$is_subscription_order = wcs_order_contains_subscription( $original_order_id );
			} else {
				$is_subscription_order = WC_Subscriptions_Order::order_contains_subscription( $original_order_id );
			}
			if ( $is_subscription_order ) {
				$return = false;
			} else {
				$return = true;
			}
			if ( $return ) {
				return $order_items;
			}

			$pay_from_credit_of_original_order = get_option( 'pay_from_smart_coupon_of_original_order', 'yes' );

			if ( 'child' !== $new_order_role ) {
				return $order_items;
			}
			if ( empty( $renewal_order_id ) || empty( $original_order_id ) ) {
				return $order_items;
			}

			$original_order = wc_get_order( $original_order_id );
			$renewal_order  = wc_get_order( $renewal_order_id );

			$coupon_used_in_original_order = ( is_object( $original_order ) && is_callable( array( $original_order, 'get_used_coupons' ) ) ) ? $original_order->get_used_coupons() : array();
			$coupon_used_in_renewal_order  = ( is_object( $renewal_order ) && is_callable( array( $renewal_order, 'get_used_coupons' ) ) ) ? $renewal_order->get_used_coupons() : array();

			if ( $this->is_wc_gte_30() ) {
				$renewal_order_billing_email = ( is_callable( array( $renewal_order, 'get_billing_email' ) ) ) ? $renewal_order->get_billing_email() : '';
			} else {
				$renewal_order_billing_email = ( ! empty( $renewal_order->billing_email ) ) ? $renewal_order->billing_email : '';
			}

			$all_coupons = array_merge( $coupon_used_in_original_order, $coupon_used_in_renewal_order );
			$all_coupons = array_unique( $all_coupons );

			if ( count( $all_coupons ) > 0 ) {
				$smart_coupons_contribution = array();
				foreach ( $all_coupons as $coupon_code ) {
					$coupon = new WC_Coupon( $coupon_code );
					if ( $this->is_wc_gte_30() ) {
						$coupon_amount = $coupon->get_amount();
						$discount_type = $coupon->get_discount_type();
					} else {
						$coupon_amount = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
						$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
					}

					if ( ! empty( $discount_type ) && 'smart_coupon' === $discount_type && ! empty( $coupon_amount ) ) {
						if ( 'yes' !== $pay_from_credit_of_original_order && in_array( $coupon_code, $coupon_used_in_original_order, true ) ) {
							continue;
						}
						$renewal_order_total = $renewal_order->get_total();
						$discount            = min( $renewal_order_total, $coupon_amount );
						if ( $discount > 0 ) {
							$new_order_total = $renewal_order_total - $discount;
							update_post_meta( $renewal_order_id, '_order_total', $new_order_total );
							update_post_meta( $renewal_order_id, '_order_discount', $discount );
							if ( $new_order_total <= floatval( 0 ) ) {
								update_post_meta( $renewal_order_id, '_renewal_paid_by_smart_coupon', 'yes' );
							}
							if ( $this->is_wc_gte_30() ) {
								$item = new WC_Order_Item_Coupon();
								$item->set_props(
									array(
										'code'     => $coupon_code,
										'discount' => $discount,
										'order_id' => ( is_object( $renewal_order ) && is_callable( array( $renewal_order, 'get_id' ) ) ) ? $renewal_order->get_id() : 0,
									)
								);
								$item->save();
								$renewal_order->add_item( $item );
							} else {
								$renewal_order->add_coupon( $coupon_code, $discount );
							}
							$smart_coupons_contribution[ $coupon_code ] = $discount;
						}
					}
				}
				if ( ! empty( $smart_coupons_contribution ) ) {
					update_post_meta( $renewal_order_id, 'smart_coupons_contribution', $smart_coupons_contribution );
				}
			}

			return $order_items;
		}

		/**
		 * Function to trigger complete payment for renewal if it's paid by smart coupons
		 *
		 * @param WC_Order $renewal_order Order created on subscription renewal.
		 * @param WC_Order $original_order Order being used to purchased the subscription.
		 * @param int      $product_id ID of the product being renewed.
		 * @param string   $new_order_role The role the renewal order is taking, one of 'parent' or 'child'.
		 */
		public function sc_renewal_complete_payment( $renewal_order = null, $original_order = null, $product_id = 0, $new_order_role = null ) {
			global $store_credit_label;

			if ( $this->is_wc_gte_30() ) {
				$renewal_order_id = ( is_object( $renewal_order ) && is_callable( array( $renewal_order, 'get_id' ) ) ) ? $renewal_order->get_id() : 0;
			} else {
				$renewal_order_id = ( ! empty( $renewal_order->id ) ) ? $renewal_order->id : 0;
			}

			if ( empty( $renewal_order_id ) ) {
				return;
			}
			if ( self::is_wcs_gte( '2.0.0' ) ) {
				$is_renewal_order = wcs_order_contains_renewal( $renewal_order_id );
			} else {
				$is_renewal_order = WC_Subscriptions_Renewal_Order::is_renewal( $renewal_order_id );
			}
			if ( $is_renewal_order ) {
				$return = false;
			} else {
				$return = true;
			}
			if ( $return ) {
				return;
			}

			$order_needs_processing = false;

			if ( count( $renewal_order->get_items() ) > 0 ) {
				foreach ( $renewal_order->get_items() as $item ) {
					$_product = $renewal_order->get_product_from_item( $item );

					if ( $_product instanceof WC_Product ) {
						$virtual_downloadable_item = $_product->is_downloadable() && $_product->is_virtual();

						if ( apply_filters( 'woocommerce_order_item_needs_processing', ! $virtual_downloadable_item, $_product, $renewal_order_id ) ) {
							$order_needs_processing = true;
							break;
						}
					} else {
						$order_needs_processing = true;
						break;
					}
				}
			}

			$is_renewal_paid_by_smart_coupon = get_post_meta( $renewal_order_id, '_renewal_paid_by_smart_coupon', true );
			if ( ! empty( $is_renewal_paid_by_smart_coupon ) && 'yes' === $is_renewal_paid_by_smart_coupon ) {

				/* translators: %s: singular name for store credit */
				$order_paid_txt = ! empty( $store_credit_label['singular'] ) ? sprintf( __( 'Order paid by %s', 'woocommerce-smart-coupons' ), strtolower( $store_credit_label['singular'] ) ) : __( 'Order paid by store credit.', 'woocommerce-smart-coupons' );
				$renewal_order->update_status( apply_filters( 'woocommerce_payment_complete_order_status', $order_needs_processing ? 'processing' : 'completed', $renewal_order_id ), $order_paid_txt );
			}
		}

		/**
		 * Get valid_subscription_coupon array and add smart_coupon type
		 *
		 * @param bool      $is_validate_for_subscription Validate coupon or not.
		 * @param WC_Coupon $coupon Coupon object.
		 * @param bool      $valid Coupon Validity.
		 * @return bool $is_validate_for_subscription whether to validate coupon for subscription or not.
		 */
		public function smart_coupon_as_valid_subscription_coupon_type( $is_validate_for_subscription, $coupon, $valid ) {

			if ( $this->is_wc_gte_30() ) {
				$discount_type = ( ! empty( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : 0;
			} else {
				$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
			}

			if ( ! empty( $discount_type ) && 'smart_coupon' === $discount_type ) {
				$is_validate_for_subscription = false;
			}

			return $is_validate_for_subscription;
		}

		/**
		 * Function to show gift certificate received details form based on product type
		 *
		 * @param  boolean $is_show Whether to show or not.
		 * @param  array   $args    Additional arguments.
		 * @return boolean          [description]
		 */
		public function is_show_gift_certificate_receiver_detail_form( $is_show = false, $args = array() ) {

			if ( wcs_cart_contains_renewal() ) {
				return false;
			}

			return $is_show;
		}

		/**
		 * Function to add subscription specific settings
		 *
		 * @param  array $settings Existing settings.
		 * @return array  $settings
		 */
		public function smart_coupons_settings( $settings = array() ) {

			$wc_subscriptions_options = array(
				array(
					'name'          => __( 'Recurring Subscriptions', 'woocommerce-smart-coupons' ),
					'desc'          => __( 'Use store credit applied in first subscription order for subsequent renewals until credit reaches zero', 'woocommerce-smart-coupons' ),
					'id'            => 'pay_from_smart_coupon_of_original_order',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'start',
				),
				array(
					'desc'          => __( 'Renewal orders should not generate coupons even when they include a product that issues coupons', 'woocommerce-smart-coupons' ),
					'id'            => 'stop_recursive_coupon_generation',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'end',
				),
			);

			array_splice( $settings, ( count( $settings ) - 14 ), 0, $wc_subscriptions_options );

			return $settings;

		}

		/**
		 * Function to add styles & scripts for WCS compatibility
		 */
		public function sc_wcs_styles_and_scripts() {
			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			if ( ! wp_script_is( 'jquery-effects-core' ) ) {
				wp_enqueue_script( 'jquery-effects-core' );
			}
			if ( ! wp_script_is( 'jquery-effects-highlight' ) ) {
				wp_enqueue_script( 'jquery-effects-highlight' );
			}
			?>
			<script type="text/javascript">
				jQuery(function(){
					jQuery( '#woocommerce_smart_coupon_apply_before_tax, #pay_from_smart_coupon_of_original_order' ).on( 'change', function(){
						checkbox_id = ( 'woocommerce_smart_coupon_apply_before_tax' === this.id ) ? 'pay_from_smart_coupon_of_original_order' : 'woocommerce_smart_coupon_apply_before_tax';

						if ( jQuery( '#' + checkbox_id ).is( ':checked' )  ) {
							var current_color = jQuery( '#' + checkbox_id ).css('background-color');
							jQuery( '#' + checkbox_id ).parent().effect('highlight', { color: '#ff8866' }, 1500);

							jQuery( '#' + checkbox_id ).prop('checked', false);
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Function to handle sc specific settings
		 */
		public function sc_wcs_settings() {
			$is_apply_before_tax      = get_option( 'woocommerce_smart_coupon_apply_before_tax' );
			$is_pay_from_store_credit = get_option( 'pay_from_smart_coupon_of_original_order' );
			if ( $is_apply_before_tax === $is_pay_from_store_credit ) {
				if ( 'yes' === $is_apply_before_tax ) {
					update_option( 'pay_from_smart_coupon_of_original_order', 'no' );
				} else {
					update_option( 'pay_from_smart_coupon_of_original_order', $is_pay_from_store_credit );
				}
			}
		}

		/**
		 * Function to check if cart contains subscription
		 *
		 * @return bool whether cart contains subscription or not
		 */
		public static function is_cart_contains_subscription() {
			if ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) {
				return true;
			}
			return false;
		}

		/**
		 * Function to check WooCommerce Subscription version
		 *
		 * @param string $version Subscription version.
		 * @return bool whether passed version is greater than or equal to current version of WooCommerce Subscription
		 */
		public static function is_wcs_gte( $version = null ) {
			if ( null === $version ) {
				return false;
			}
			if ( ! class_exists( 'WC_Subscriptions' ) || empty( WC_Subscriptions::$version ) ) {
				return false;
			}
			return version_compare( WC_Subscriptions::$version, $version, '>=' );
		}



	}

}

WCS_SC_Compatibility::get_instance();
