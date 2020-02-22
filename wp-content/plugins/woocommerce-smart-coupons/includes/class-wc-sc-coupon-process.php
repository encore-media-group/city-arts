<?php
/**
 * Processing of coupons
 *
 * @author      StoreApps
 * @since       3.3.0
 * @version     1.1
 *
 * @package     woocommerce-smart-coupons/includes/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SC_Coupon_Process' ) ) {

	/**
	 * Class for handling processes of coupons
	 */
	class WC_SC_Coupon_Process {

		/**
		 * Variable to hold instance of WC_SC_Coupon_Process
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'woocommerce_new_order', array( $this, 'add_gift_certificate_receiver_details_in_order' ) );
			add_action( 'woocommerce_new_order', array( $this, 'smart_coupons_contribution' ), 8 );
			add_action( 'woocommerce_before_checkout_process', array( $this, 'verify_gift_certificate_receiver_details' ) );

			add_action( 'woocommerce_order_status_completed', array( $this, 'sa_add_coupons' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'coupons_used' ), 10 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'sa_add_coupons' ), 19 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'coupons_used' ), 10 );
			add_action( 'woocommerce_order_status_refunded', array( $this, 'sa_remove_coupons' ), 19 );
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'sa_remove_coupons' ), 19 );
			add_action( 'woocommerce_order_status_failed', array( $this, 'sa_remove_coupons' ), 19 );
			add_action( 'woocommerce_order_status_processing_to_refunded', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_processing_to_cancelled', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_processing_to_failed', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_completed_to_refunded', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_completed_to_cancelled', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_completed_to_failed', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_on-hold_to_cancelled', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );
			add_action( 'woocommerce_order_status_on-hold_to_failed', array( $this, 'sa_restore_smart_coupon_amount' ), 19 );

			add_filter( 'woocommerce_gift_certificates_email_template', array( $this, 'woocommerce_gift_certificates_email_template_path' ) );

			add_action( 'woocommerce_order_status_pending_to_on-hold', array( $this, 'update_smart_coupon_balance' ) );
			add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'update_smart_coupon_balance' ) );
			add_action( 'woocommerce_order_status_pending_to_completed', array( $this, 'update_smart_coupon_balance' ) );
			add_action( 'sc_after_order_calculate_discount_amount', array( $this, 'update_smart_coupon_balance' ), 10 );

			add_filter( 'woocommerce_paypal_args', array( $this, 'modify_paypal_args' ), 11, 2 );

			add_action( 'admin_init', array( $this, 'delete_smart_coupons_contribution' ), 11 );
		}

		/**
		 * Get single instance of WC_SC_Coupon_Process
		 *
		 * @return WC_SC_Coupon_Process Singleton object of WC_SC_Coupon_Process
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
		 * @param string $function_name The function name.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return result of function call
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
		 * Function to add gift certificate receiver's details in order itself
		 *
		 * @param int $order_id The order id.
		 */
		public function add_gift_certificate_receiver_details_in_order( $order_id ) {

			$request_gift_receiver_email   = ( isset( $_REQUEST['gift_receiver_email'] ) ) ? wc_clean( wp_unslash( $_REQUEST['gift_receiver_email'] ) ) : array(); // phpcs:ignore
			$request_gift_receiver_message = ( isset( $_REQUEST['gift_receiver_message'] ) ) ? wc_clean( wp_unslash( $_REQUEST['gift_receiver_message'] ) ) : array(); // phpcs:ignore
			$request_billing_email         = ( isset( $_REQUEST['billing_email'] ) ) ? wc_clean( wp_unslash( $_REQUEST['billing_email'] ) ) : ''; // phpcs:ignore
			$request_is_gift               = ( isset( $_REQUEST['is_gift'] ) ) ? wc_clean( wp_unslash( $_REQUEST['is_gift'] ) ) : ''; // phpcs:ignore
			$request_sc_send_to            = ( isset( $_REQUEST['sc_send_to'] ) ) ? wc_clean( wp_unslash( $_REQUEST['sc_send_to'] ) ) : ''; // phpcs:ignore

			if ( empty( $request_gift_receiver_email ) || count( $request_gift_receiver_email ) <= 0 ) {
				return;
			}

			if ( ! empty( $request_gift_receiver_email ) || ( ! empty( $request_billing_email ) && $request_billing_email !== $request_gift_receiver_email ) ) {

				if ( 'yes' === $request_is_gift ) {
					if ( ! empty( $request_sc_send_to ) ) {
						switch ( $request_sc_send_to ) {
							case 'one':
								$email_for_one   = ( isset( $request_gift_receiver_email[0][0] ) && ! empty( $request_gift_receiver_email[0][0] ) && is_email( $request_gift_receiver_email[0][0] ) ) ? $request_gift_receiver_email[0][0] : $request_billing_email;
								$message_for_one = ( isset( $request_gift_receiver_message[0][0] ) && ! empty( $request_gift_receiver_message[0][0] ) ) ? $request_gift_receiver_message[0][0] : '';
								unset( $request_gift_receiver_email[0][0] );
								unset( $request_gift_receiver_message[0][0] );
								foreach ( $request_gift_receiver_email as $coupon_id => $emails ) {
									foreach ( $emails as $key => $email ) {
										$request_gift_receiver_email[ $coupon_id ][ $key ]   = $email_for_one;
										$request_gift_receiver_message[ $coupon_id ][ $key ] = $message_for_one;
									}
								}
								if ( ! empty( $request_gift_receiver_message ) && '' !== $request_gift_receiver_message ) {
									update_post_meta( $order_id, 'gift_receiver_message', $request_gift_receiver_message );
								}
								break;

							case 'many':
								if ( isset( $request_gift_receiver_email[0][0] ) && ! empty( $request_gift_receiver_email[0][0] ) ) {
									unset( $request_gift_receiver_email[0][0] );
								}
								if ( isset( $request_gift_receiver_message[0][0] ) && ! empty( $request_gift_receiver_message[0][0] ) ) {
									unset( $request_gift_receiver_message[0][0] );
								}
								if ( ! empty( $request_gift_receiver_message ) && '' !== $request_gift_receiver_message ) {
									update_post_meta( $order_id, 'gift_receiver_message', $request_gift_receiver_message );
								}
								break;
						}
					}
					update_post_meta( $order_id, 'is_gift', 'yes' );
				} else {
					if ( ! empty( $request_gift_receiver_email[0][0] ) && is_array( $request_gift_receiver_email[0][0] ) ) {
						unset( $request_gift_receiver_email[0][0] );
						foreach ( $request_gift_receiver_email as $coupon_id => $emails ) {
							foreach ( $emails as $key => $email ) {
								$request_gift_receiver_email[ $coupon_id ][ $key ] = $request_billing_email;
							}
						}
					}
					update_post_meta( $order_id, 'is_gift', 'no' );
				}

				update_post_meta( $order_id, 'gift_receiver_email', $request_gift_receiver_email );

			}

		}

		/**
		 * Function to verify gift certificate form details
		 */
		public function verify_gift_certificate_receiver_details() {
			global $store_credit_label;

			$post_gift_receiver_email = ( ! empty( $_POST['gift_receiver_email'] ) ) ? wc_clean( wp_unslash( $_POST['gift_receiver_email'] ) ) : array(); // phpcs:ignore
			$post_billing_email       = ( ! empty( $_POST['billing_email'] ) ) ? wc_clean( wp_unslash( $_POST['billing_email'] ) ) : ''; // phpcs:ignore

			if ( empty( $post_gift_receiver_email ) || ! is_array( $post_gift_receiver_email ) ) {
				return;
			}

			foreach ( $post_gift_receiver_email as $key => $emails ) {
				if ( ! empty( $emails ) ) {
					foreach ( $emails as $index => $email ) {

						$placeholder  = __( 'Email address', 'woocommerce-smart-coupons' );
						$placeholder .= '...';

						if ( empty( $email ) || $email === $placeholder ) {
							$post_gift_receiver_email[ $key ][ $index ] = ( ! empty( $post_billing_email ) ) ? $post_billing_email : '';
						} elseif ( ! empty( $email ) && ! is_email( $email ) ) {
							if ( ! empty( $store_credit_label['singular'] ) ) {
								/* translators: %s: singular name for store credit */
								wc_add_notice( sprintf( __( 'Error: %s Receiver&#146;s E-mail address is invalid.', 'woocommerce-smart-coupons' ), ucwords( $store_credit_label['singular'] ) ), 'error' );
							} else {
								wc_add_notice( __( 'Error: Gift Card Receiver&#146;s E-mail address is invalid.', 'woocommerce-smart-coupons' ), 'error' );
							}
							return;
						}
					}
				}
			}

			$_POST['gift_receiver_email'] = $post_gift_receiver_email; // phpcs:ignore

		}

		/**
		 * Function to save Smart Coupon's contribution in discount
		 *
		 * @param int $order_id The order id.
		 */
		public function smart_coupons_contribution( $order_id ) {

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

						update_post_meta( $order_id, 'smart_coupons_contribution', WC()->cart->smart_coupon_credit_used );

					}
				}
			}
		}

		/**
		 * Function to delete smart coupons contribution on removal of coupon
		 */
		public function delete_smart_coupons_contribution() {

			$post_action   = ( ! empty( $_POST['action'] ) ) ? wc_clean( wp_unslash( $_POST['action'] ) ) : ''; // phpcs:ignore
			$post_order_id = ( ! empty( $_POST['order_id'] ) ) ? wc_clean( wp_unslash( $_POST['order_id'] ) ) : 0; // phpcs:ignore
			$post_coupon   = ( ! empty( $_POST['coupon'] ) ) ? wc_clean( wp_unslash( $_POST['coupon'] ) ) : ''; // phpcs:ignore

			if ( 'woocommerce_remove_order_coupon' === $post_action && ! empty( $post_order_id ) && ! empty( $post_coupon ) ) {
				$smart_coupons_contribution = get_post_meta( $post_order_id, 'smart_coupons_contribution', true );

				if ( isset( $smart_coupons_contribution[ $post_coupon ] ) ) {
					unset( $smart_coupons_contribution[ $post_coupon ] );

					$_POST['smart_coupon_removed'] = $post_coupon;

					if ( empty( $smart_coupons_contribution ) ) {
						delete_post_meta( $post_order_id, 'smart_coupons_contribution' );
					} else {
						update_post_meta( $post_order_id, 'smart_coupons_contribution', $smart_coupons_contribution );
					}
				}

				$order        = wc_get_order( $post_order_id );
				$order_status = $order->get_status();

				if ( $order->has_status( array( 'on-hold', 'auto-draft', 'pending' ) ) ) {
					$this->sa_restore_smart_coupon_amount( $post_order_id );
				}
			}
		}

		/**
		 * Function to update Store Credit / Gift Ceritficate balance
		 *
		 * @param int $order_id The order id.
		 */
		public function update_smart_coupon_balance( $order_id ) {

			if ( ( ! empty( $_POST['post_type'] ) && 'shop_order' === $_POST['post_type'] && ! empty( $_POST['action'] ) && 'editpost' === $_POST['action'] ) // phpcs:ignore
					|| ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'woocommerce_mark_order_status', 'mark_on-hold', 'mark_processing', 'mark_completed' ), true ) ) // phpcs:ignore
				) {
				return;
			}

			$order = wc_get_order( $order_id );

			$order_used_coupons = $order->get_used_coupons();

			if ( $order_used_coupons ) {

				$smart_coupons_contribution = get_post_meta( $order_id, 'smart_coupons_contribution', true );

				if ( ! isset( $smart_coupons_contribution ) || empty( $smart_coupons_contribution ) || ( is_array( $smart_coupons_contribution ) && count( $smart_coupons_contribution ) <= 0 ) ) {
					return;
				}

				foreach ( $order_used_coupons as $code ) {

					if ( array_key_exists( $code, $smart_coupons_contribution ) ) {

						$smart_coupon = new WC_Coupon( $code );

						if ( $this->is_wc_gte_30() ) {
							if ( ! is_object( $smart_coupon ) || ! is_callable( array( $smart_coupon, 'get_id' ) ) ) {
								continue;
							}
							$coupon_id = $smart_coupon->get_id();
							if ( empty( $coupon_id ) ) {
								continue;
							}
							$coupon_amount = $smart_coupon->get_amount();
							$discount_type = $smart_coupon->get_discount_type();
						} else {
							$coupon_id     = ( ! empty( $smart_coupon->id ) ) ? $smart_coupon->id : 0;
							$coupon_amount = ( ! empty( $smart_coupon->amount ) ) ? $smart_coupon->amount : 0;
							$discount_type = ( ! empty( $smart_coupon->discount_type ) ) ? $smart_coupon->discount_type : '';
						}

						if ( 'smart_coupon' === $discount_type ) {

							$discount_amount  = round( ( $coupon_amount - $smart_coupons_contribution[ $code ] ), get_option( 'woocommerce_price_num_decimals', 2 ) );
							$credit_remaining = max( 0, $discount_amount );

							if ( $credit_remaining <= 0 && get_option( 'woocommerce_delete_smart_coupon_after_usage' ) === 'yes' ) {
								update_post_meta( $coupon_id, 'coupon_amount', 0 );
								wp_trash_post( $coupon_id );
							} else {
								update_post_meta( $coupon_id, 'coupon_amount', $credit_remaining );
							}
						}
					}
				}
			}
		}

		/**
		 * Update discount details in PayPal args if store credit is applied
		 *
		 * @param  array    $args PayPal args.
		 * @param  WC_Order $order The order object.
		 *
		 * @return array $args Modified PayPal args
		 */
		public function modify_paypal_args( $args, $order ) {
			global $store_credit_label;

			$apply_before_tax = get_option( 'woocommerce_smart_coupon_apply_before_tax', 'no' );

			if ( 'yes' === $apply_before_tax ) {
				return $args;
			}

			$is_order_contains_store_credit = $this->is_order_contains_store_credit( $order );

			if ( ! $is_order_contains_store_credit ) {
				return $args;
			}

			$discount_amount_cart = ( ! empty( $args['discount_amount_cart'] ) ) ? $args['discount_amount_cart'] : 0;

			if ( empty( $discount_amount_cart ) ) {
				return $args;
			}

			$item_total = 0;

			foreach ( $args as $key => $value ) {
				if ( strpos( $key, 'amount_' ) === 0 ) {
					$index       = str_replace( 'amount_', '', $key );
					$qty         = ( ! empty( $args[ 'quantity_' . $index ] ) ) ? $args[ 'quantity_' . $index ] : 1;
					$item_total += ( $value * $qty );
				}
			}

			if ( $discount_amount_cart > $item_total ) {
				$difference                   = $discount_amount_cart - $item_total;
				$args['discount_amount_cart'] = $item_total;

				if ( $this->is_wc_gte_30() ) {
					$order_id = ( is_object( $order ) && is_callable( array( $order, 'get_id' ) ) ) ? $order->get_id() : 0;
				} else {
					$order_id = ( ! empty( $order->id ) ) ? $order->id : 0;
				}

				$coupons     = $order->get_items( 'coupon' );
				$order_total = $order->get_total();
				$order_note  = array();

				foreach ( $coupons as $item_id => $item ) {
					if ( empty( $difference ) ) {
						break;
					}
					$code   = trim( $item['name'] );
					$coupon = new WC_Coupon( $code );
					if ( $this->is_wc_gte_30() ) {
						$discount_type = $coupon->get_discount_type();
					} else {
						$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
					}
					if ( 'smart_coupon' === $discount_type && ! empty( $item['discount_amount'] ) ) {
						$new_discount  = 0;
						$item_discount = $item['discount_amount'];
						$cut_amount    = min( $difference, $item_discount );
						$new_discount  = $item_discount - $cut_amount;
						$difference   -= $cut_amount;
						$item_args     = array(
							'discount_amount' => $new_discount,
						);
						if ( $this->is_wc_gte_30() ) {
							$item = $order->get_item( $item_id );

							if ( ! is_object( $item ) || ! is_callable( array( $item, 'get_id' ) ) ) {
								continue;
							}

							if ( ! is_object( $item ) || ! $item->is_type( 'coupon' ) ) {
								$discount_updated = false;
							}
							if ( ! $order->get_id() ) {
								$order->save(); // Order must exist.
							}

							// BW compatibility for old args.
							if ( isset( $item_args['discount_amount'] ) ) {
								$item_args['discount'] = $item_args['discount_amount'];
							}
							if ( isset( $item_args['discount_amount_tax'] ) ) {
								$item_args['discount_tax'] = $item_args['discount_amount_tax'];
							}

							$item->set_order_id( $order->get_id() );
							$item->set_props( $item_args );
							$item->save();

							do_action( 'woocommerce_order_update_coupon', $order->get_id(), $item->get_id(), $item_args );
							$discount_updated = true;
						} else {
							$discount_updated = $order->update_coupon( $item_id, $item_args );
						}

						if ( $discount_updated ) {
							$order_total               += $cut_amount;
							$smart_coupons_contribution = get_post_meta( $order_id, 'smart_coupons_contribution', true );
							if ( empty( $smart_coupons_contribution ) || ! is_array( $smart_coupons_contribution ) ) {
								$smart_coupons_contribution = array();
							}
							$smart_coupons_contribution[ $code ] = $item_args['discount_amount'];
							update_post_meta( $order_id, 'smart_coupons_contribution', $smart_coupons_contribution );

							if ( ! empty( $store_credit_label['singular'] ) ) {
								/* translators: 1. amount of store credit 2. store credit label 3. coupon code */
								$order_note[] = sprintf( __( '%1$s worth of %2$s restored to coupon %3$s.', 'woocommerce-smart-coupons' ), '<strong>' . wc_price( $cut_amount ) . '</strong>', ucwords( $store_credit_label['singular'] ), '<code>' . $code . '</code>' );
							} else {
								/* translators: 1. amount of store credit 2. coupon code */
								$order_note[] = sprintf( __( '%1$s worth of Store Credit restored to coupon %2$s.', 'woocommerce-smart-coupons' ), '<strong>' . wc_price( $cut_amount ) . '</strong>', '<code>' . $code . '</code>' );
							}
						}
					}
				}
				$order->set_total( $order_total, 'total' );
				if ( ! empty( $order_note ) ) {
					/* translators: Order notes */
					$note = sprintf( __( '%s Because PayPal doesn\'t accept discount on shipping & tax.', 'woocommerce-smart-coupons' ), implode( ', ', $order_note ) );
					$order->add_order_note( $note );
					if ( ! wc_has_notice( $note ) ) {
						wc_add_notice( $note );
					}
				}
			}

			return $args;
		}

		/**
		 * Function to track whether coupon is used or not
		 *
		 * @param int $order_id The order id.
		 */
		public function coupons_used( $order_id ) {

			$order = wc_get_order( $order_id );

			$email = get_post_meta( $order_id, 'gift_receiver_email', true );

			if ( $order->get_used_coupons() ) {
				$this->update_coupons( $order->get_used_coupons(), $email, '', 'remove' );
			}
		}

		/**
		 * Function to update details related to coupons
		 *
		 * @param array  $coupon_titles The coupon codes.
		 * @param mixed  $email Email addresses.
		 * @param array  $product_ids Array of product ids.
		 * @param string $operation Operation to perform.
		 * @param array  $order_item The order item.
		 * @param array  $gift_certificate_receiver Array of gift receiver emails.
		 * @param array  $gift_certificate_receiver_name Array of gift receiver name.
		 * @param string $message_from_sender The message from sender.
		 * @param string $gift_certificate_sender_name Name of the sender.
		 * @param string $gift_certificate_sender_email Email address of the sender.
		 * @param int    $order_id The order id.
		 */
		public function update_coupons( $coupon_titles = array(), $email, $product_ids = '', $operation, $order_item = null, $gift_certificate_receiver = false, $gift_certificate_receiver_name = '', $message_from_sender = '', $gift_certificate_sender_name = '', $gift_certificate_sender_email = '', $order_id = '' ) {

			global $smart_coupon_codes;

			$temp_gift_card_receivers_emails = array();
			if ( ! empty( $order_id ) ) {
				$receivers_messages              = get_post_meta( $order_id, 'gift_receiver_message', true );
				$temp_gift_card_receivers_emails = get_post_meta( $order_id, 'temp_gift_card_receivers_emails', true );
			}

			$prices_include_tax = ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) ? true : false;

			if ( ! empty( $coupon_titles ) ) {

				if ( $this->is_wc_gte_30() ) {
					$item_qty              = ( ! empty( $order_item ) && is_callable( array( $order_item, 'get_quantity' ) ) ) ? $order_item->get_quantity() : 1;
					$item_sc_called_credit = ( ! empty( $order_item ) && is_callable( array( $order_item, 'get_meta' ) ) ) ? $order_item->get_meta( 'sc_called_credit' ) : 0;
					$item_total            = ( ! empty( $order_item ) && is_callable( array( $order_item, 'get_total' ) ) ) ? $order_item->get_total() : 0;
					$item_tax              = ( ! empty( $order_item ) && is_callable( array( $order_item, 'get_total_tax' ) ) ) ? $order_item->get_total_tax() : 0;
				} else {
					$item_qty              = ( ! empty( $order_item['qty'] ) ) ? $order_item['qty'] : 1;
					$item_sc_called_credit = ( ! empty( $order_item['sc_called_credit'] ) ) ? $order_item['sc_called_credit'] : 0;
					$item_total            = ( ! empty( $order_item['line_total'] ) ) ? $order_item['line_total'] : 0;
					$item_tax              = ( ! empty( $order_item['line_tax'] ) ) ? $order_item['line_tax'] : 0;
				}

				if ( ! empty( $item_qty ) ) {
					$qty = $item_qty;
				} else {
					$qty = 1;
				}

				foreach ( $coupon_titles as $coupon_title ) {

					$coupon = new WC_Coupon( $coupon_title );

					if ( $this->is_wc_gte_30() ) {
						if ( ! is_object( $coupon ) || ! is_callable( array( $coupon, 'get_id' ) ) ) {
							continue;
						}
						$coupon_id = $coupon->get_id();
						if ( empty( $coupon_id ) ) {
							continue;
						}
						$coupon_amount    = $coupon->get_amount();
						$is_free_shipping = ( $coupon->get_free_shipping() ) ? 'yes' : 'no';
						$discount_type    = $coupon->get_discount_type();
						$expiry_date      = $coupon->get_date_expires();
						$coupon_code      = $coupon->get_code();
					} else {
						$coupon_id        = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
						$coupon_amount    = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
						$is_free_shipping = ( ! empty( $coupon->free_shipping ) ) ? $coupon->free_shipping : '';
						$discount_type    = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
						$expiry_date      = ( ! empty( $coupon->expiry_date ) ) ? $coupon->expiry_date : '';
						$coupon_code      = ( ! empty( $coupon->code ) ) ? $coupon->code : '';
					}

					$auto_generation_of_code = get_post_meta( $coupon_id, 'auto_generate_coupon', true );

					if ( ! empty( $item_sc_called_credit ) && 'smart_coupon' === $discount_type ) {
						continue;    // because it is already processed.
					}

					$email_id = ( 'yes' === $auto_generation_of_code && 'smart_coupon' !== $discount_type && ! empty( $temp_gift_card_receivers_emails[ $coupon_id ][0] ) ) ? $temp_gift_card_receivers_emails[ $coupon_id ][0] : $gift_certificate_sender_email;

					if ( ( 'yes' === $auto_generation_of_code || 'smart_coupon' === $discount_type ) && 'add' === $operation ) {

						if ( get_post_meta( $coupon_id, 'is_pick_price_of_product', true ) === 'yes' && 'smart_coupon' === $discount_type ) {
							$products_price = ( ! $prices_include_tax ) ? $item_total : $item_total + $item_tax;
							$amount         = $products_price / $qty;
						} else {
							$amount = $coupon_amount;
						}

						if ( ! empty( $temp_gift_card_receivers_emails ) ) {
							$email = $temp_gift_card_receivers_emails;
						}

						$is_auto_generate = $amount > 0 || 'yes' === $is_free_shipping;

						$is_auto_generate = apply_filters(
							'wc_sc_is_auto_generate', $is_auto_generate, array(
								'coupon_id'     => $coupon_id,
								'auto_generate' => $auto_generation_of_code,
								'coupon_obj'    => $coupon,
							)
						);

						if ( $is_auto_generate ) {
							$message_index = ( ! empty( $email[ $coupon_id ] ) && is_array( $email[ $coupon_id ] ) ) ? array_search( $email_id, $email[ $coupon_id ], true ) : false;

							if ( false !== $message_index && isset( $receivers_messages[ $coupon_id ][ $message_index ] ) && ! empty( $receivers_messages[ $coupon_id ][ $message_index ] ) ) {
								$message_from_sender = $receivers_messages[ $coupon_id ][ $message_index ];
								unset( $email[ $coupon_id ][ $message_index ] );
								update_post_meta( $order_id, 'temp_gift_card_receivers_emails', $email );
							} else {
								$message_from_sender = '';
							}
							for ( $i = 0; $i < $qty; $i++ ) {
								if ( 'yes' === $auto_generation_of_code || 'smart_coupon' === $discount_type ) {
									$email_id = ! empty( $temp_gift_card_receivers_emails[ $coupon_id ][ $i ] ) ? $temp_gift_card_receivers_emails[ $coupon_id ][ $i ] : $gift_certificate_sender_email;
									if ( isset( $receivers_messages[ $coupon_id ][ $i ] ) && ! empty( $receivers_messages[ $coupon_id ][ $i ] ) ) {
										$message_from_sender = $receivers_messages[ $coupon_id ][ $i ];
										unset( $email[ $coupon_id ][ $i ] );
										update_post_meta( $order_id, 'temp_gift_card_receivers_emails', $email );
									} else {
										$message_from_sender = '';
									}

									$this->generate_smart_coupon( $email_id, $amount, $order_id, $coupon, $discount_type, $gift_certificate_receiver_name, $message_from_sender, $gift_certificate_sender_name, $gift_certificate_sender_email );
									$smart_coupon_codes = array();
								}
							}
						}
					} else {

						$coupon_receiver_email = ( ! empty( $temp_gift_card_receivers_emails[ $coupon_id ][0] ) ) ? $temp_gift_card_receivers_emails[ $coupon_id ][0] : $gift_certificate_sender_email;

						$sc_disable_email_restriction = get_post_meta( $coupon_id, 'sc_disable_email_restriction', true );

						if ( ( 'no' === $sc_disable_email_restriction || empty( $sc_disable_email_restriction ) ) ) {
							$old_customers_email_ids = (array) maybe_unserialize( get_post_meta( $coupon_id, 'customer_email', true ) );
						}

						if ( 'add' === $operation && 'yes' !== $auto_generation_of_code && 'smart_coupon' !== $discount_type ) {
							$message_index = ( ! empty( $temp_gift_card_receivers_emails[ $coupon_id ] ) && is_array( $temp_gift_card_receivers_emails[ $coupon_id ] ) ) ? array_search( $email_id, $temp_gift_card_receivers_emails[ $coupon_id ], true ) : false;

							if ( false !== $message_index && isset( $receivers_messages[ $coupon_id ][ $message_index ] ) && ! empty( $receivers_messages[ $coupon_id ][ $message_index ] ) ) {
								$message_from_sender = $receivers_messages[ $coupon_id ][ $message_index ];
								unset( $temp_gift_card_receivers_emails[ $coupon_id ][ $message_index ] );
								update_post_meta( $order_id, 'temp_gift_card_receivers_emails', $temp_gift_card_receivers_emails );
							} else {
								$message_from_sender = '';
							}

							for ( $i = 0; $i < $qty; $i++ ) {

								$coupon_receiver_email = ( ! empty( $temp_gift_card_receivers_emails[ $coupon_id ][ $i ] ) ) ? $temp_gift_card_receivers_emails[ $coupon_id ][ $i ] : $coupon_receiver_email;
								$message_from_sender   = ( ! empty( $receivers_messages[ $coupon_id ][ $i ] ) ) ? $receivers_messages[ $coupon_id ][ $i ] : '';

								$coupon_details = array(
									$coupon_receiver_email => array(
										'parent' => $coupon_id,
										'code'   => $coupon_title,
										'amount' => $coupon_amount,
									),
								);

								$receiver_name = '';

								$this->sa_email_coupon( $coupon_details, $discount_type, $order_id, $receiver_name, $message_from_sender );

							}

							if ( $qty > 0 && ( 'no' === $sc_disable_email_restriction || empty( $sc_disable_email_restriction ) ) ) {
								for ( $i = 0; $i < $qty; $i++ ) {
									$old_customers_email_ids[] = $coupon_receiver_email;
								}
							}
						} elseif ( 'remove' === $operation && 'smart_coupon' !== $discount_type && ( 'no' === $sc_disable_email_restriction || empty( $sc_disable_email_restriction ) ) ) {

							$key = array_search( $coupon_receiver_email, $old_customers_email_ids, true );

							if ( false !== $key ) {
								unset( $old_customers_email_ids[ $key ] );
							}
						}

						if ( ( 'no' === $sc_disable_email_restriction || empty( $sc_disable_email_restriction ) ) ) {
							update_post_meta( $coupon_id, 'customer_email', $old_customers_email_ids );
						}
					}
				}
			}

		}

		/**
		 * Get receiver's email addresses
		 *
		 * @param array  $coupon_details The coupon details.
		 * @param string $gift_certificate_sender_email Sender email address.
		 * @return array $receivers_email Array of receiver's email
		 */
		public function get_receivers_detail( $coupon_details = array(), $gift_certificate_sender_email = '' ) {

			if ( count( $coupon_details ) <= 0 ) {
				return 0;
			}

			$all_discount_types = wc_get_coupon_types();

			$receivers_email = array();

			foreach ( $coupon_details as $coupon_id => $emails ) {
				$discount_type = get_post_meta( $coupon_id, 'discount_type', true );
				if ( ! empty( $discount_type ) && array_key_exists( $discount_type, $all_discount_types ) ) {
					$receivers_email = array_merge( $receivers_email, array_diff( $emails, array( $gift_certificate_sender_email ) ) );
				}
			}

			return $receivers_email;
		}

		/**
		 * Function to process coupons based on change in order status
		 *
		 * @param int    $order_id The order id.
		 * @param string $operation Operation.
		 */
		public function process_coupons( $order_id, $operation ) {
			global $smart_coupon_codes;

			$smart_coupon_codes  = array();
			$message_from_sender = '';

			$receivers_emails   = get_post_meta( $order_id, 'gift_receiver_email', true );
			$receivers_messages = get_post_meta( $order_id, 'gift_receiver_message', true );
			$is_coupon_sent     = get_post_meta( $order_id, 'coupon_sent', true );

			if ( 'yes' === $is_coupon_sent ) {
				return;
			}

			$sc_called_credit_details = get_post_meta( $order_id, 'sc_called_credit_details', true );

			$order       = wc_get_order( $order_id );
			$order_items = (array) $order->get_items();

			if ( count( $order_items ) <= 0 ) {
				return;
			}

			if ( $this->is_wc_gte_30() ) {
				$order_billing_email      = $order->get_billing_email();
				$order_billing_first_name = $order->get_billing_first_name();
				$order_billing_last_name  = $order->get_billing_last_name();
			} else {
				$order_billing_email      = ( ! empty( $order->billing_email ) ) ? $order->billing_email : '';
				$order_billing_first_name = ( ! empty( $order->billing_first_name ) ) ? $order->billing_first_name : '';
				$order_billing_last_name  = ( ! empty( $order->billing_last_name ) ) ? $order->billing_last_name : '';
			}

			if ( is_array( $receivers_emails ) && ! empty( $receivers_emails ) ) {

				foreach ( $receivers_emails as $coupon_id => $emails ) {
					foreach ( $emails as $key => $email ) {
						if ( empty( $email ) ) {
							$email                                  = $order_billing_email;
							$receivers_emails[ $coupon_id ][ $key ] = $email;
						}
					}
				}

				if ( count( $receivers_emails ) > 1 && isset( $receivers_emails[0][0] ) ) {
					unset( $receivers_emails[0] );   // Disable sending to one customer.
				}
				$email = $receivers_emails;
			} else {
				$email = '';
			}

			$receivers_emails_list = $receivers_emails;
			if ( ! empty( $email ) ) {
				update_post_meta( $order_id, 'temp_gift_card_receivers_emails', $email );
			}

			$gift_certificate_receiver      = true;
			$gift_certificate_sender_name   = $order_billing_first_name . ' ' . $order_billing_last_name;
			$gift_certificate_sender_email  = $order_billing_email;
			$gift_certificate_receiver_name = '';

			$receivers_detail = array();
			$email_to_credit  = array();
			$receiver_count   = 0;

			if ( is_array( $sc_called_credit_details ) && count( $sc_called_credit_details ) > 0 && 'add' === $operation ) {

				foreach ( $order_items as $item_id => $item ) {

					$product = $order->get_product_from_item( $item );

					if ( $this->is_wc_gte_30() ) {
						$product_type = ( is_object( $product ) && is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';
						$product_id   = ( in_array( $product_type, array( 'variable', 'variable-subscription', 'variation', 'subscription_variation' ), true ) ) ? ( ( is_object( $product ) && is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0 ) : ( ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0 );
					} else {
						$product_id = ( ! empty( $product->id ) ) ? $product->id : 0;
					}

					$coupon_titles = get_post_meta( $product_id, '_coupon_title', true );

					if ( $coupon_titles ) {

						foreach ( $coupon_titles as $coupon_title ) {
							$coupon = new WC_Coupon( $coupon_title );
							if ( $this->is_wc_gte_30() ) {
								if ( ! is_object( $coupon ) || ! is_callable( array( $coupon, 'get_id' ) ) ) {
									continue;
								}
								$coupon_id = $coupon->get_id();
								if ( empty( $coupon_id ) ) {
									continue;
								}
								$coupon_amount = $coupon->get_amount();
							} else {
								$coupon_id     = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
								$coupon_amount = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
							}

							if ( ! isset( $receivers_emails[ $coupon_id ] ) ) {
								continue;
							}
							for ( $i = 0; $i < $item['qty']; $i++ ) {
								if ( isset( $receivers_emails[ $coupon_id ][0] ) ) {
									if ( ! isset( $email_to_credit[ $receivers_emails[ $coupon_id ][0] ] ) ) {
										$email_to_credit[ $receivers_emails[ $coupon_id ][0] ] = array();
									}
									if ( isset( $sc_called_credit_details[ $item_id ] ) && ! empty( $sc_called_credit_details[ $item_id ] ) ) {

										if ( $this->is_coupon_amount_pick_from_product_price( array( $coupon_title ) ) ) {
											$email_to_credit[ $receivers_emails[ $coupon_id ][0] ][] = $coupon_id . ':' . $sc_called_credit_details[ $item_id ];
										} else {
											$email_to_credit[ $receivers_emails[ $coupon_id ][0] ][] = $coupon_id . ':' . $coupon_amount;
										}

										unset( $receivers_emails[ $coupon_id ][0] );
										$receivers_emails[ $coupon_id ] = array_values( $receivers_emails[ $coupon_id ] );
									}
								}
							}
						}
					}
					if ( $this->is_coupon_amount_pick_from_product_price( $coupon_titles ) && $product->get_price() >= 0 ) {
						$item['sc_called_credit'] = ( ! empty( $sc_called_credit_details[ $item_id ] ) ) ? $sc_called_credit_details[ $item_id ] : '';
					}
				}
			}

			if ( ! empty( $email_to_credit ) && count( $email_to_credit ) > 0 ) {
				$update_temp_email = false;
				foreach ( $email_to_credit as $email_id => $credits ) {
					$email_to_credit[ $email_id ] = array_count_values( $credits );
					foreach ( $email_to_credit[ $email_id ] as $coupon_credit => $qty ) {
						$coupon_details = explode( ':', $coupon_credit );
						$coupon_title   = get_the_title( $coupon_details[0] );
						$coupon         = new WC_Coupon( $coupon_title );
						$credit_amount  = $coupon_details[1];
						if ( $this->is_wc_gte_30() ) {
							if ( ! is_object( $coupon ) || ! is_callable( array( $coupon, 'get_id' ) ) ) {
								continue;
							}
							$coupon_id = $coupon->get_id();
							if ( empty( $coupon_id ) ) {
								continue;
							}
							$discount_type = $coupon->get_discount_type();
						} else {
							$coupon_id     = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
							$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
						}
						$message_index = array_search( $email_id, $email[ $coupon_id ], true );
						if ( false !== $message_index && isset( $receivers_messages[ $coupon_id ][ $message_index ] ) && ! empty( $receivers_messages[ $coupon_id ][ $message_index ] ) ) {
							$message_from_sender = $receivers_messages[ $coupon_id ][ $message_index ];
						} else {
							$message_from_sender = '';
						}
						for ( $i = 0; $i < $qty; $i++ ) {
							if ( 'smart_coupon' !== $discount_type ) {
								continue; // only process smart_coupon here, rest coupon will be processed by function update_coupon.
							}
							$this->generate_smart_coupon( $email_id, $credit_amount, $order_id, $coupon, 'smart_coupon', $gift_certificate_receiver_name, $message_from_sender, $gift_certificate_sender_name, $gift_certificate_sender_email );
							$smart_coupon_codes = array();
						}
					}
				}
				foreach ( $email_to_credit as $email => $coupon_detail ) {
					if ( $email === $gift_certificate_sender_email ) {
						continue;
					}
					$receiver_count += count( $coupon_detail );
				}
			}

			if ( count( $order_items ) > 0 ) {

				$flag = false;

				foreach ( $order_items as $item_id => $item ) {

					$product = $order->get_product_from_item( $item );
					if ( $this->is_wc_gte_30() ) {
						$product_type = ( is_object( $product ) && is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';
						$product_id   = ( in_array( $product_type, array( 'variable', 'variable-subscription', 'variation', 'subscription_variation' ), true ) ) ? ( ( is_object( $product ) && is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0 ) : ( ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0 );
					} else {
						$product_id = ( ! empty( $product->id ) ) ? $product->id : 0;
					}

					$coupon_titles = get_post_meta( $product_id, '_coupon_title', true );

					if ( $coupon_titles ) {

						$flag = true;

						if ( $this->is_coupon_amount_pick_from_product_price( $coupon_titles ) && $product->get_price() >= 0 ) {
							$item['sc_called_credit'] = ( ! empty( $sc_called_credit_details[ $item_id ] ) ) ? $sc_called_credit_details[ $item_id ] : '';
						}

						$this->update_coupons( $coupon_titles, $email, '', $operation, $item, $gift_certificate_receiver, $gift_certificate_receiver_name, $message_from_sender, $gift_certificate_sender_name, $gift_certificate_sender_email, $order_id );

						if ( 'add' === $operation && ! empty( $receivers_emails_list ) ) {
							$receivers_detail += $this->get_receivers_detail( $receivers_emails_list, $gift_certificate_sender_email );
						}
					}
				}

				if ( $flag && 'add' === $operation ) {
					update_post_meta( $order_id, 'coupon_sent', 'yes' );              // to know whether coupon has sent or not.
				}
			}

			$is_send_email = get_option( 'smart_coupons_is_send_email', 'yes' );

			if ( 'yes' === $is_send_email && ( count( $receivers_detail ) + $receiver_count ) > 0 ) {
				$this->acknowledge_gift_certificate_sender( $receivers_detail, $gift_certificate_receiver_name, $email, $gift_certificate_sender_email, ( count( $receivers_detail ) ) );
			}

			if ( 'add' === $operation ) {
				delete_post_meta( $order_id, 'temp_gift_card_receivers_emails' );
			}
			unset( $smart_coupon_codes );
		}

		/**
		 * Function to acknowledge sender of gift credit
		 *
		 * @param array  $receivers_detail Receiver's details.
		 * @param string $gift_certificate_receiver_name  Receiver's name.
		 * @param mixed  $email Email address.
		 * @param string $gift_certificate_sender_email Sender email.
		 * @param int    $receiver_count Receiver count.
		 */
		public function acknowledge_gift_certificate_sender( $receivers_detail = array(), $gift_certificate_receiver_name = '', $email = '', $gift_certificate_sender_email = '', $receiver_count = '' ) {
			global $store_credit_label;

			if ( empty( $receiver_count ) ) {
				return;
			}

			ob_start();

			/* translators: %s: singular name for store credit */
			$subject = ! empty( $store_credit_label['singular'] ) ? sprintf( esc_html__( '%s sent successfully', 'woocommerce-smart-coupons' ), esc_html( ucwords( $store_credit_label['singular'] ) ) ) : __( 'Gift Card sent successfully!', 'woocommerce-smart-coupons' );

			do_action( 'woocommerce_email_header', $subject );

			if ( ! empty( $store_credit_label['singular'] ) && ! empty( $store_credit_label['plural'] ) ) {
				/* translators: 1. Receiver's count 2. Singular/Plural label for store credit(s) 3. Receiver name 4. Receiver details */
				echo esc_html( sprintf( __( 'You have successfully sent %1$d %2$s to %3$s (%4$s)', 'woocommerce-smart-coupons' ), $receiver_count, ucwords( _n( $store_credit_label['singular'], $store_credit_label['plural'], count( $receivers_detail ) ) ), $gift_certificate_receiver_name, implode( ', ', array_unique( $receivers_detail ) ) ) ); // phpcs:ignore
			} else {
				/* translators: 1. Receiver's count 2. Gift Card/s 3. Receiver name 4. Receiver details */
				echo esc_html( sprintf( __( 'You have successfully sent %1$d %2$s to %3$s (%4$s)', 'woocommerce-smart-coupons' ), $receiver_count, _n( 'Gift Card', 'Gift Cards', count( $receivers_detail ), 'woocommerce-smart-coupons' ), $gift_certificate_receiver_name, implode( ', ', array_unique( $receivers_detail ) ) ) );
			}

			do_action( 'woocommerce_email_footer' );

			$message = ob_get_clean();

			if ( ! class_exists( 'WC_Email' ) ) {
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
			}

			$mailer      = new WC_Email();
			$mailer->id  = 'wc_sc_ack_coupon_sender';
			$headers     = $mailer->get_headers();
			$attachments = $mailer->get_attachments();

			wc_mail( $gift_certificate_sender_email, $subject, $message, $headers, $attachments );

		}

		/**
		 * Function to add details to coupons
		 *
		 * @param int $order_id The order id.
		 */
		public function sa_add_coupons( $order_id ) {
			$this->process_coupons( $order_id, 'add' );
		}

		/**
		 * Function to remove details from coupons
		 *
		 * @param int $order_id The order id.
		 */
		public function sa_remove_coupons( $order_id ) {
			$this->process_coupons( $order_id, 'remove' );
		}

		/**
		 * Function to Restore Smart Coupon Amount back, when an order which was created using this coupon, is refunded or cancelled,
		 *
		 * @param int $order_id The order id.
		 */
		public function sa_restore_smart_coupon_amount( $order_id = 0 ) {

			if ( empty( $order_id ) ) {
				return;
			}

			$order = wc_get_order( $order_id );

			$order_id = ( is_object( $order ) && is_callable( array( $order, 'get_id' ) ) ) ? $order->get_id() : 0;

			if ( empty( $order_id ) ) {
				return;
			}

			$coupons = $order->get_items( 'coupon' );

			if ( ! empty( $coupons ) ) {

				foreach ( $coupons as $item_id => $item ) {

					if ( empty( $item['name'] ) ) {
						continue;
					}

					$coupon = new WC_Coupon( $item['name'] );

					if ( $this->is_wc_gte_30() ) {
						if ( ! is_object( $coupon ) || ! is_callable( array( $coupon, 'get_id' ) ) ) {
							continue;
						}
						$coupon_id = $coupon->get_id();
						if ( empty( $coupon_id ) ) {
							continue;
						}
						$coupon_amount = $coupon->get_amount();
						$discount_type = $coupon->get_discount_type();
						$usage_count   = $coupon->get_usage_count();
					} else {
						$coupon_id     = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
						$coupon_amount = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
						$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
						$usage_count   = ( ! empty( $coupon->usage_count ) ) ? $coupon->usage_count : 0;
					}

					if ( empty( $discount_type ) || 'smart_coupon' !== $discount_type ) {
						continue;
					}

					if ( ! empty( $_POST['action'] ) && 'woocommerce_remove_order_coupon' === wc_clean( wp_unslash( $_POST['action'] ) ) && ! empty( $_POST['smart_coupon_removed'] ) && sanitize_text_field( wp_unslash( $_POST['smart_coupon_removed'] ) ) !== $item['name'] ) { // WPCS: sanitization ok, CSRF ok.
						continue;
					}

					$update = false;
					if ( ! empty( $item['discount_amount'] ) ) {
						$coupon_amount += $item['discount_amount'];
						$usage_count--;
						if ( $usage_count < 0 ) {
							$usage_count = 0;
						}
						$update = true;
					}

					if ( $update ) {
						update_post_meta( $coupon_id, 'coupon_amount', $coupon_amount );
						update_post_meta( $coupon_id, 'usage_count', $usage_count );
						wc_update_order_item_meta( $item_id, 'discount_amount', 0 );
					}
				}
			}

		}

		/**
		 * Allow overridding of Smart Coupon's template for email
		 *
		 * @param string $template The template name.
		 * @return mixed $template
		 */
		public function woocommerce_gift_certificates_email_template_path( $template ) {

			$template_name = 'email.php';

			$template = $this->locate_template_for_smart_coupons( $template_name, $template );

			return $template;

		}

	}

}

WC_SC_Coupon_Process::get_instance();
