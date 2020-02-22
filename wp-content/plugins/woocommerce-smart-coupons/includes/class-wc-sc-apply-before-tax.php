<?php
/**
 * Processing of coupons
 *
 * @author      StoreApps
 * @since       3.3.0
 * @version     1.0
 * @package     WooCommerce Smart Coupons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SC_Apply_Before_Tax' ) ) {

	/**
	 * Class for applying store credit before tax calculation
	 */
	class WC_SC_Apply_Before_Tax {

		/**
		 * Variable to hold instance of WC_SC_Apply_Before_Tax
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'woocommerce_order_before_calculate_totals', array( $this, 'order_calculate_discount_amount_before_tax' ), 10, 2 );

			add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'order_set_discount_total' ), 10, 2 );
			add_action( 'woocommerce_checkout_create_order', array( $this, 'cart_set_discount_total' ), 10, 1 );

			add_action( 'wp_loaded', array( $this, 'cart_calculate_discount_amount' ), 20 );
			add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'cart_return_discount_amount' ), 10, 5 );
			add_action( 'woocommerce_calculate_totals', array( $this, 'manage_store_credit_used' ), 10 );

			add_filter( 'woocommerce_add_cart_item', array( $this, 'sc_mnm_compat' ), 20, 2 );
		}

		/**
		 * Get single instance of WC_SC_Apply_Before_Tax
		 *
		 * @return WC_SC_Apply_Before_Tax Singleton object of WC_SC_Apply_Before_Tax
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
		 * Function to apply store credit before tax calculation for orders that are manually created and updated from backend
		 *
		 * @param bool     $and_taxes Calc taxes if true.
		 * @param WC_Order $order Order object.
		 */
		public function order_calculate_discount_amount_before_tax( $and_taxes, $order ) {
			$order_actions = array( 'woocommerce_add_coupon_discount', 'woocommerce_calc_line_taxes', 'woocommerce_save_order_items' );

			if ( $order instanceof WC_Order && ! empty( $_POST['action'] ) && ( in_array( wp_unslash( $_POST['action'] ), $order_actions, true ) || ( ! empty( $_POST['post_type'] ) && 'shop_order' === wp_unslash( $_POST['post_type'] ) && 'editpost' === wp_unslash( $_POST['action'] ) ) ) ) { // WPCS: input var ok, sanitization ok, CSRF ok.
				if ( ! is_object( $order ) || ! is_callable( array( $order, 'get_id' ) ) ) {
					return;
				}
				$order_id = $order->get_id();
				if ( empty( $order_id ) ) {
					return;
				}
				$coupons     = $order->get_items( 'coupon' );
				$order_items = $order->get_items( 'line_item' );

				if ( empty( $order_items ) && empty( $coupons ) ) {
					return;
				}

				foreach ( $coupons as $item_id => $item ) {

					if ( empty( $item['name'] ) ) {
						continue;
					}

					$coupon_code = $item['name'];
					$coupon      = new WC_Coupon( $coupon_code );

					$discount_type = $coupon->get_discount_type();

					if ( 'smart_coupon' === $discount_type ) {
						$smart_coupons_contribution = get_post_meta( $order_id, 'smart_coupons_contribution', true );
						$smart_coupons_contribution = ( ! empty( $smart_coupons_contribution ) ) ? $smart_coupons_contribution : array();

						$discount_amount = wc_get_order_item_meta( $item_id, 'discount_amount', true );

						if ( is_array( $smart_coupons_contribution ) && count( $smart_coupons_contribution ) > 0 && array_key_exists( $coupon_code, $smart_coupons_contribution ) ) {
							$item['discount_amount'] = $smart_coupons_contribution[ $coupon_code ];
						} elseif ( ! empty( $discount_amount ) ) {
							$item['discount_amount']                    = $discount_amount;
							$smart_coupons_contribution[ $coupon_code ] = $discount_amount;
						} else {
							$coupon_amount       = $coupon->get_amount();
							$coupon_product_ids  = $coupon->get_product_ids();
							$coupon_category_ids = $coupon->get_product_categories();

							$subtotal              = 0;
							$items_to_apply_credit = array();

							if ( count( $coupon_product_ids ) > 0 || count( $coupon_category_ids ) > 0 ) {
								foreach ( $order_items as $order_item_id => $order_item ) {

									$product_category_ids = wc_get_product_cat_ids( $order_item['product_id'] );

									if ( count( $coupon_product_ids ) > 0 && count( $coupon_category_ids ) > 0 ) {
										if ( ( in_array( $order_item['product_id'], $coupon_product_ids, true ) || in_array( $order_item['variation_id'], $coupon_product_ids, true ) ) && count( array_intersect( $product_category_ids, $coupon_category_ids ) ) > 0 ) {
											$items_to_apply_credit[] = $order_item_id;
										}
									} else {
										if ( in_array( $order_item['product_id'], $coupon_product_ids, true ) || in_array( $order_item['variation_id'], $coupon_product_ids, true ) || count( array_intersect( $product_category_ids, $coupon_category_ids ) ) > 0 ) {
											$items_to_apply_credit[] = $order_item_id;
										}
									}
								}
							} else {
								$items_to_apply_credit = array_keys( $order_items );
							}

							$subtotal = array_sum( array_map( array( $this, 'sc_get_order_subtotal' ), $items_to_apply_credit ) );

							if ( $subtotal <= 0 ) {
								continue;
							}

							$store_credit_used = 0;

							foreach ( $items_to_apply_credit as $order_item_id ) {
								$order_item         = $order_items[ $order_item_id ];
								$discounting_amount = $order_item->get_total();
								$quantity           = $order_item->get_quantity();

								$discount = $this->sc_get_discounted_price( $discounting_amount, $quantity, $subtotal, $coupon_amount );

								$order_item->set_total( $discounting_amount - $discount );

								$store_credit_used += $discount;
							}

							$item['discount_amount'] = $store_credit_used;

							$smart_coupons_contribution[ $coupon_code ] = $store_credit_used;

							update_post_meta( $order_id, 'smart_coupons_contribution', $smart_coupons_contribution );
						}

						$order->sc_total_credit_used = $smart_coupons_contribution;
					}
				}
			}
		}

		/**
		 * Function to calculate subtotal of items in order which is necessary for applying store credit before tax calculation
		 *
		 * @param  int $order_item_id Item ID.
		 * @return float  $subtotal
		 */
		private function sc_get_order_subtotal( $order_item_id ) {
			$order_item = WC_Order_Factory::get_order_item( $order_item_id );
			$subtotal   = $order_item->get_total();

			return $subtotal;
		}

		/**
		 * Function to update_discount_total for an order
		 *
		 * @param  WC_Order $order Order object.
		 * @param  float    $total_credit_used Total store credit used.
		 */
		public function update_discount_total( $order = '', $total_credit_used = 0 ) {
			if ( $order instanceof WC_Order ) {
				$discount_total = $order->get_discount_total();
				$sc_credit_used = min( $discount_total, $total_credit_used );
				$order->set_discount_total( $discount_total - $sc_credit_used );
			}
		}

		/**
		 * Function to set discount total for orders that are created manually
		 *
		 * @param bool     $and_taxes Calc taxes if true.
		 * @param WC_Order $order Order object.
		 */
		public function order_set_discount_total( $and_taxes, $order ) {
			if ( isset( $order->sc_total_credit_used ) && is_array( $order->sc_total_credit_used ) && count( $order->sc_total_credit_used ) > 0 ) {
				$total_credit_used = array_sum( $order->sc_total_credit_used );
				$this->update_discount_total( $order, $total_credit_used );

				if ( ! empty( $_POST['action'] ) && 'woocommerce_add_coupon_discount' === wp_unslash( $_POST['action'] ) && $order->has_status( array( 'on-hold', 'auto-draft', 'pending' ) ) ) { // WPCS: input var ok, sanitization ok, CSRF ok.
					do_action( 'sc_after_order_calculate_discount_amount', $order->get_id() );
				}
			}
		}

		/**
		 * Function to set discount total for a new order
		 *
		 * @param  WC_Order $order Order object.
		 */
		public function cart_set_discount_total( $order ) {
			if ( isset( WC()->cart->sc_total_credit_used ) && is_array( WC()->cart->sc_total_credit_used ) && count( WC()->cart->sc_total_credit_used ) > 0 ) {
				$total_credit_used = array_sum( WC()->cart->sc_total_credit_used );
				$this->update_discount_total( $order, $total_credit_used );
			}
		}

		/**
		 * Function to apply store credit before tax calculation for cart items
		 */
		public function cart_calculate_discount_amount() {
			$cart = ( isset( WC()->cart ) ) ? WC()->cart : '';

			if ( ! empty( $cart ) && $cart instanceof WC_Cart ) {
				$coupons = $cart->get_coupons();

				if ( ! empty( $coupons ) ) {
					$sc_credit_used        = array();
						$total_credit_used = array();

					foreach ( $coupons as $coupon_code => $coupon ) {
						$discount_type = $coupon->get_discount_type();

						if ( 'smart_coupon' === $discount_type ) {
							$coupon_amount       = $coupon->get_amount();
							$coupon_product_ids  = $coupon->get_product_ids();
							$coupon_category_ids = $coupon->get_product_categories();

							$subtotal              = 0;
							$items_to_apply_credit = array();

							if ( count( $coupon_product_ids ) > 0 || count( $coupon_category_ids ) > 0 ) {
								foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
									$product_category_ids = wc_get_product_cat_ids( $cart_item['product_id'] );

									if ( count( $coupon_product_ids ) > 0 && count( $coupon_category_ids ) > 0 ) {
										if ( ( in_array( $cart_item['product_id'], $coupon_product_ids, true ) || in_array( $cart_item['variation_id'], $coupon_product_ids, true ) ) && count( array_intersect( $product_category_ids, $coupon_category_ids ) ) > 0 ) {
											$items_to_apply_credit[] = $cart_item_key;
										}
									} else {
										if ( in_array( $cart_item['product_id'], $coupon_product_ids, true ) || in_array( $cart_item['variation_id'], $coupon_product_ids, true ) || count( array_intersect( $product_category_ids, $coupon_category_ids ) ) > 0 ) {
											$items_to_apply_credit[] = $cart_item_key;
										}
									}
								}
							} else {
								$items_to_apply_credit = array_keys( WC()->cart->cart_contents );
							}

							$subtotal = array_sum( array_map( array( $this, 'sc_get_cart_subtotal' ), $items_to_apply_credit ) );

							if ( $subtotal <= 0 ) {
								continue;
							}

							$discounted_items = array();

							$store_credit_used = 0;

							foreach ( $items_to_apply_credit as $cart_item_key ) {
								$discounting_amount = WC()->cart->cart_contents[ $cart_item_key ]['line_total'];
								$quantity           = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];

								$discount = $this->sc_get_discounted_price( $discounting_amount, $quantity, $subtotal, $coupon_amount );

								WC()->cart->cart_contents[ $cart_item_key ]['line_total'] = $discounting_amount - $discount;

								$discounted_items[ $cart_item_key ] = $discount;

								$store_credit_used += ( $discount * $quantity );
							}

							$sc_credit_used[ $coupon_code ]    = $discounted_items;
							$total_credit_used[ $coupon_code ] = $store_credit_used;
						}
					}

					if ( count( $sc_credit_used ) > 0 ) {
						WC()->cart->sc_credit_used = $sc_credit_used;
					}

					if ( count( $total_credit_used ) > 0 ) {
						WC()->cart->sc_total_credit_used = $total_credit_used;
					}
				}
			}
		}

		/**
		 * Function to calculate subtotal of items in cart which is necessary for applying store credit before tax calculation
		 *
		 * @param  string $cart_item_key Cart item key.
		 * @return float  $subtotal
		 */
		private function sc_get_cart_subtotal( $cart_item_key ) {
			$subtotal = WC()->cart->cart_contents[ $cart_item_key ]['line_total'];

			return $subtotal;
		}

		/**
		 * Get discount amount for a cart item.
		 *
		 * @param  float      $discount Amount this coupon has discounted.
		 * @param  float      $discounting_amount Amount the coupon is being applied to.
		 * @param  array|null $cart_item Cart item being discounted if applicable.
		 * @param  bool       $single True if discounting a single qty item, false if its the line.
		 * @param  WC_Coupon  $coupon Coupon object.
		 * @return float      $discount
		 */
		public function cart_return_discount_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ) {
			$coupon_code = $coupon->get_code();
			if ( isset( WC()->cart->sc_credit_used ) && is_array( WC()->cart->sc_credit_used ) && array_key_exists( $coupon_code, WC()->cart->sc_credit_used ) ) {
				$credit_used = WC()->cart->sc_credit_used[ $coupon_code ];

				if ( array_key_exists( $cart_item['key'], $credit_used ) ) {
					$discount = $credit_used[ $cart_item['key'] ];
				}
			}

			return $discount;
		}

		/**
		 * Function to handle store credit used
		 *
		 * @param WC_Cart $cart Cart Object.
		 */
		public function manage_store_credit_used( $cart ) {
			if ( isset( $cart->sc_total_credit_used ) && is_array( $cart->sc_total_credit_used ) && count( $cart->sc_total_credit_used ) > 0 ) {
				$total_credit_used = $cart->sc_total_credit_used;

				WC()->cart->smart_coupon_credit_used = $total_credit_used;
			}
		}

		/**
		 * Function to calulate discount amount for an item
		 *
		 * @param  float $discounting_amount Amount the coupon is being applied to.
		 * @param  int   $quantity Item quantity.
		 * @param  float $subtotal Cart/Order subtotal.
		 * @param  float $coupon_amount Coupon amount.
		 * @return float $discount
		 */
		public function sc_get_discounted_price( $discounting_amount = 0, $quantity = 1, $subtotal = 0, $coupon_amount = 0 ) {
			$discount           = 0;
			$discounting_amount = $discounting_amount / $quantity;
			$discount_percent   = ( $discounting_amount * $quantity ) / $subtotal;

			$discount = ( $coupon_amount * $discount_percent ) / $quantity;
			$discount = min( $discount, $discounting_amount );

			return $discount;
		}

		/**
		 * Function to add cart item key for MNM child items.
		 * This was need because MNM child items didn't had cart item key inside $cart_item_data array and the
		 * function WC_SC_Apply_Before_Tax::cart_return_discount_amount() uses cart item key to set discount amount.
		 *
		 * @param  array  $cart_item_data Cart item data.
		 * @param  string $cart_item_key Cart item key.
		 * @return float $cart_item_data
		 */
		public function sc_mnm_compat( $cart_item_data, $cart_item_key ) {
			if ( ! empty( $cart_item_data['mnm_container'] ) ) {
				$cart_item_data['key'] = $cart_item_key;
			}

			return $cart_item_data;
		}
	}
}

WC_SC_Apply_Before_Tax::get_instance();
