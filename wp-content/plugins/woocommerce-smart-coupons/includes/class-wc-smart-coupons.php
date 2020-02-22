<?php
/**
 * Main class for Smart Coupons
 *
 * @author      StoreApps
 * @since       3.3.0
 * @version     1.0
 *
 * @package     woocommerce-smart-coupons/includes/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Smart_Coupons' ) ) {

	/**
	 *  Main WooCommerce Smart Coupons Class.
	 *
	 * @return object of WC_Smart_Coupons having all functionality of Smart Coupons
	 */
	class WC_Smart_Coupons {

		/**
		 * Text Domain
		 *
		 * @var $text_domain
		 */
		public static $text_domain = 'woocommerce-smart-coupons';

		/**
		 * Text Domain
		 *
		 * @var $text_domain
		 */
		public $plugin_data = array();

		/**
		 * Variable to hold instance of Smart Coupons
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Smart Coupons.
		 *
		 * @return WC_Smart_Coupons Singleton object of WC_Smart_Coupons
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 3.3.0
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-smart-coupons' ), '3.3.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 3.3.0
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-smart-coupons' ), '3.3.0' );
		}

		/**
		 * Constructor
		 */
		private function __construct() {

			$this->define_constants();
			$this->define_label_for_store_credit();
			$this->includes();

			$this->plugin_data = self::get_smart_coupons_plugin_data();

			add_option( 'woocommerce_delete_smart_coupon_after_usage', 'no' );
			add_option( 'woocommerce_smart_coupon_apply_before_tax', 'no' );
			add_option( 'woocommerce_smart_coupon_show_my_account', 'yes' );

			add_option( 'smart_coupons_is_show_associated_coupons', 'no' );
			add_option( 'smart_coupons_show_coupon_description', 'no' );
			add_option( 'smart_coupons_is_send_email', 'yes' );
			add_option( 'show_coupon_received_on_my_account', 'no' );
			add_option( 'pay_from_smart_coupon_of_original_order', 'yes' );
			add_option( 'stop_recursive_coupon_generation', 'no' );
			add_option( 'sc_gift_certificate_shop_loop_button_text', __( 'Select options', 'woocommerce-smart-coupons' ) );
			add_option( 'wc_sc_setting_max_coupon_to_show', '5' );
			add_option( 'smart_coupons_show_invalid_coupons_on_myaccount', 'no' );

			add_filter( 'woocommerce_coupon_is_valid', array( $this, 'is_smart_coupon_valid' ), 10, 2 );
			add_filter( 'woocommerce_coupon_is_valid', array( $this, 'is_user_usage_limit_valid' ), 10, 3 );
			add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'smart_coupons_is_valid_for_product' ), 10, 4 );
			add_filter( 'woocommerce_apply_individual_use_coupon', array( $this, 'smart_coupons_override_individual_use' ), 10, 3 );
			add_filter( 'woocommerce_apply_with_individual_use_coupon', array( $this, 'smart_coupons_override_with_individual_use' ), 10, 4 );

			add_action( 'parse_request', array( $this, 'woocommerce_admin_coupon_search' ) );
			add_filter( 'get_search_query', array( $this, 'woocommerce_admin_coupon_search_label' ) );

			add_action( 'restrict_manage_posts', array( $this, 'woocommerce_restrict_manage_smart_coupons' ), 20 );
			add_action( 'admin_init', array( $this, 'woocommerce_export_coupons' ) );

			add_action( 'personal_options_update', array( $this, 'my_profile_update' ) );
			add_action( 'edit_user_profile_update', array( $this, 'my_profile_update' ) );

			add_filter( 'generate_smart_coupon_action', array( $this, 'generate_smart_coupon_action' ), 1, 9 );

			add_action( 'wc_sc_new_coupon_generated', array( $this, 'smart_coupons_plugin_used' ) );

			if ( $this->is_wc_gte_26() ) {
				// Actions used to insert a new endpoint in the WordPress.
				add_action( 'init', array( $this, 'sc_add_endpoints' ) );
			}

			add_action( 'init', array( $this, 'register_plugin_styles' ) );

			add_filter( 'wc_smart_coupons_export_headers', array( $this, 'wc_smart_coupons_export_headers' ) );
			add_filter( 'woocommerce_email_footer_text', array( $this, 'email_footer_replace_site_title' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'smart_coupon_styles_and_scripts' ), 20 );

			add_filter( 'is_protected_meta', array( $this, 'make_sc_meta_protected' ), 10, 3 );

			add_filter( 'plugin_action_links_' . plugin_basename( WC_SC_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			if ( ! $this->is_wc_gte_25() ) {
				add_action( 'admin_notices', array( $this, 'needs_wc_25_above' ) );
			}

			add_action( 'wp_loaded', array( $this, 'sc_handle_store_credit_application' ), 15 );

			add_filter( 'woocommerce_debug_tools', array( $this, 'clear_cache_tool' ) );
		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_3_4', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_3_4::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_3_4::' . $function_name );
			}

		}

		/**
		 * Define SC constants.
		 */
		private function define_constants() {
			if ( ! defined( 'WC_SC_COUPON_CODE_LENGTH' ) ) {
				define( 'WC_SC_COUPON_CODE_LENGTH', $this->get_coupon_code_length() );
			}
		}

		/**
		 * Include files
		 */
		public function includes() {

			include_once 'compat/class-sa-wc-compatibility-2-5.php';
			include_once 'compat/class-sa-wc-compatibility-2-6.php';
			include_once 'compat/class-sa-wc-compatibility-3-0.php';
			include_once 'compat/class-sa-wc-compatibility-3-1.php';
			include_once 'compat/class-sa-wc-compatibility-3-2.php';
			include_once 'compat/class-sa-wc-compatibility-3-3.php';
			include_once 'compat/class-sa-wc-compatibility-3-4.php';
			include_once 'class-wc-sc-admin-welcome.php';
			include_once 'class-wc-sc-admin-pages.php';

			include_once 'class-wc-sc-ajax.php';
			include_once 'class-wc-sc-display-coupons.php';

			include_once 'class-wc-sc-settings.php';
			include_once 'class-wc-sc-wpml-compatibility.php';
			include_once 'class-wcs-sc-compatibility.php';
			include_once 'class-wc-sc-shortcode.php';
			include_once 'class-wc-sc-purchase-credit.php';
			include_once 'class-wc-sc-url-coupon.php';
			include_once 'class-wc-sc-coupon-fields.php';
			include_once 'class-wc-sc-product-fields.php';
			include_once 'class-wc-sc-order-fields.php';
			include_once 'class-wc-sc-coupon-process.php';
			include_once 'class-wc-sc-global-coupons.php';
			include_once 'class-wc-sc-duplicate-coupon.php';
			include_once 'class-wc-sc-privacy.php';
			include_once 'class-wc-sc-coupon-actions.php';

		}

		/**
		 * Function to log messages generated by Smart Coupons plugin
		 *
		 * @param  string $level   Message type. Valid values: debug, info, notice, warning, error, critical, alert, emergency.
		 * @param  string $message The message to log.
		 */
		public function log( $level = 'notice', $message = '' ) {

			if ( empty( $message ) ) {
				return;
			}

			$logger  = wc_get_logger();
			$context = array( 'source' => WC_SC_PLUGIN_DIRNAME );

			$logger->log( $level, $message, $context );

		}

		/**
		 * Coupon's expiration date (formatted)
		 *
		 * @param int $expiry_date Expirty date of coupon.
		 * @return string $expires_string Formatted expiry date
		 */
		public function get_expiration_format( $expiry_date ) {

			if ( $this->is_wc_gte_30() && $expiry_date instanceof WC_DateTime ) {
				$expiry_date = $expiry_date->getTimestamp();
			} elseif ( ! is_int( $expiry_date ) ) {
				$expiry_date = strtotime( $expiry_date );
			}

			$expiry_days = (int) ( ( $expiry_date - time() ) / ( 24 * 60 * 60 ) );

			if ( $expiry_days < 1 ) {

				$expires_string = __( 'Expires Today ', 'woocommerce-smart-coupons' );

			} elseif ( $expiry_days < 31 ) {

				$expires_string = __( 'Expires in ', 'woocommerce-smart-coupons' ) . $expiry_days . _n( ' day', ' days', $expiry_days, 'woocommerce-smart-coupons' );

			} else {

				$expires_string = __( 'Expires on ', 'woocommerce-smart-coupons' ) . esc_html( date_i18n( get_option( 'date_format', 'F j, Y' ), $expiry_date ) );

			}
			return $expires_string;

		}


		/**
		 * Function to send e-mail containing coupon code to customer
		 *
		 * @param array   $coupon_title Associative array containing receiver's details.
		 * @param string  $discount_type Type of coupon.
		 * @param int     $order_id Associated order id.
		 * @param array   $gift_certificate_receiver_name Array of receiver's name.
		 * @param string  $message_from_sender Message added by sender.
		 * @param string  $gift_certificate_sender_name Sender name.
		 * @param string  $gift_certificate_sender_email Sender email.
		 * @param boolean $is_gift Whether it is a gift certificate or store credit.
		 */
		public function sa_email_coupon( $coupon_title, $discount_type, $order_id = '', $gift_certificate_receiver_name = '', $message_from_sender = '', $gift_certificate_sender_name = '', $gift_certificate_sender_email = '', $is_gift = '' ) {
			global $store_credit_label;

			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			$is_send_email = get_option( 'smart_coupons_is_send_email', 'yes' );

			if ( $this->is_wc_gte_30() ) {
				$page_id = wc_get_page_id( 'shop' );
			} else {
				$page_id = woocommerce_get_page_id( 'shop' );
			}

			$url = ( get_option( 'permalink_structure' ) ) ? get_permalink( $page_id ) : get_post_type_archive_link( 'product' );

			if ( 'smart_coupon' === $discount_type && 'yes' === $is_gift ) {
				$gift_certificate_sender_name = trim( $gift_certificate_sender_name );
				$sender                       = ( ! empty( $gift_certificate_sender_name ) ) ? $gift_certificate_sender_name : '';
				$sender                      .= ( ! empty( $gift_certificate_sender_name ) ) ? ' (' : '';
				$sender                      .= ( ! empty( $gift_certificate_sender_email ) ) ? $gift_certificate_sender_email : '';
				$sender                      .= ( ! empty( $gift_certificate_sender_name ) ) ? ')' : '';
				$from                         = ' ' . __( 'from', 'woocommerce-smart-coupons' ) . ' ';
				$smart_coupon_type            = __( 'Gift Card', 'woocommerce-smart-coupons' );
			} else {
				$from              = '';
				$smart_coupon_type = __( 'Store Credit', 'woocommerce-smart-coupons' );
			}

			if ( ! empty( $store_credit_label['singular'] ) ) {
				$smart_coupon_type = ucwords( $store_credit_label['singular'] );
			}

			/* translators: %s: coupon type */
			$subject_string  = sprintf( __( "Congratulations! You've received a %s ", 'woocommerce-smart-coupons' ), ( ( 'smart_coupon' === $discount_type && ! empty( $smart_coupon_type ) ) ? $smart_coupon_type : 'coupon' ) );
			$subject_string  = ( get_option( 'smart_coupon_email_subject' ) && '' !== get_option( 'smart_coupon_email_subject' ) ) ? get_option( 'smart_coupon_email_subject' ) : $subject_string;
			$subject_string .= ( ! empty( $gift_certificate_sender_name ) ) ? $from . $gift_certificate_sender_name : '';

			$subject = apply_filters( 'woocommerce_email_subject_gift_certificate', sprintf( '%1$s: %2$s', $blogname, $subject_string ) );

			$all_discount_types = wc_get_coupon_types();

			foreach ( $coupon_title as $email => $coupon ) {

				$_coupon = new WC_Coupon( $coupon['code'] );

				if ( $this->is_wc_gte_30() ) {
					$_is_free_shipping            = ( $_coupon->get_free_shipping() ) ? 'yes' : 'no';
					$_discount_type               = $_coupon->get_discount_type();
					$_product_ids                 = $_coupon->get_product_ids();
					$_excluded_product_ids        = $_coupon->get_excluded_product_ids();
					$_product_categories          = $_coupon->get_product_categories();
					$_excluded_product_categories = $_coupon->get_excluded_product_categories();
				} else {
					$_is_free_shipping            = ( ! empty( $_coupon->free_shipping ) ) ? $_coupon->free_shipping : '';
					$_discount_type               = ( ! empty( $_coupon->discount_type ) ) ? $_coupon->discount_type : '';
					$_product_ids                 = ( ! empty( $_coupon->product_ids ) ) ? $_coupon->product_ids : array();
					$_excluded_product_ids        = ( ! empty( $_coupon->exclude_product_ids ) ) ? $_coupon->exclude_product_ids : array();
					$_product_categories          = ( ! empty( $_coupon->product_categories ) ) ? $_coupon->product_categories : array();
					$_excluded_product_categories = ( ! empty( $_coupon->exclude_product_categories ) ) ? $_coupon->exclude_product_categories : array();
				}

				$amount      = $coupon['amount'];
				$coupon_code = $coupon['code'];

				switch ( $discount_type ) {

					case 'smart_coupon':
						/* translators: 1: coupon type 2: coupon amount */
						$email_heading = sprintf( __( 'You have received a %1$s worth %2$s ', 'woocommerce-smart-coupons' ), $smart_coupon_type, wc_price( $amount ) );
						break;

					case 'fixed_cart':
						/* translators: %s: coupon amount */
						$email_heading = sprintf( __( 'You have received a coupon worth %s (for entire purchase) ', 'woocommerce-smart-coupons' ), wc_price( $amount ) );
						break;

					case 'fixed_product':
						if ( ! empty( $_product_ids ) || ! empty( $_excluded_product_ids ) || ! empty( $_product_categories ) || ! empty( $_excluded_product_categories ) ) {
							$_discount_for_text = __( 'for some products', 'woocommerce-smart-coupons' );
						} else {
							$_discount_for_text = __( 'for all products', 'woocommerce-smart-coupons' );
						}

						/* translators: 1: coupon amount 2: discount for text */
						$email_heading = sprintf( __( 'You have received a coupon worth %1$s (%2$s) ', 'woocommerce-smart-coupons' ), wc_price( $amount ), $_discount_for_text );
						break;

					case 'percent_product':
						if ( ! empty( $_product_ids ) || ! empty( $_excluded_product_ids ) || ! empty( $_product_categories ) || ! empty( $_excluded_product_categories ) ) {
							$_discount_for_text = __( 'for some products', 'woocommerce-smart-coupons' );
						} else {
							$_discount_for_text = __( 'for all products', 'woocommerce-smart-coupons' );
						}

						/* translators: 1: coupon amount 2: discount for text */
						$email_heading = sprintf( __( 'You have received a coupon worth %1$s%% (%2$s) ', 'woocommerce-smart-coupons' ), $amount, $_discount_for_text );
						break;

					case 'percent':
						if ( ! empty( $_product_ids ) || ! empty( $_excluded_product_ids ) || ! empty( $_product_categories ) || ! empty( $_excluded_product_categories ) ) {
							$_discount_for_text = __( 'for some products', 'woocommerce-smart-coupons' );
						} else {
							$_discount_for_text = __( 'for entire purchase', 'woocommerce-smart-coupons' );
						}

						/* translators: 1: coupon amount 2: discount for text */
						$email_heading = sprintf( __( 'You have received a coupon worth %1$s%% (%2$s) ', 'woocommerce-smart-coupons' ), $amount, $_discount_for_text );
						break;

					default:
						$default_coupon_type = ( ! empty( $all_discount_types[ $discount_type ] ) ) ? $all_discount_types[ $discount_type ] : ucwords( str_replace( array( '_', '-' ), ' ', $discount_type ) );
						$coupon_type         = apply_filters( 'wc_sc_coupon_type', $default_coupon_type, $_coupon, $all_discount_types );
						$coupon_amount       = apply_filters( 'wc_sc_coupon_amount', $amount, $_coupon );

						/* translators: 1: coupon type 2: coupon amount */
						$email_heading = sprintf( __( 'You have received %1$s coupon of %2$s', 'woocommerce-smart-coupons' ), $coupon_type, $coupon_amount ); /* translators: 1: coupon type 2: coupon amount */
						$email_heading = apply_filters( 'wc_sc_email_heading', $email_heading, $_coupon );
						break;

				}

				if ( 'yes' === $_is_free_shipping && in_array( $_discount_type, array( 'fixed_cart', 'fixed_product', 'percent_product', 'percent' ), true ) ) {
					/* translators: 1: email heading 2: suffix */
					$email_heading = sprintf( __( '%1$s Free Shipping%2$s', 'woocommerce-smart-coupons' ), ( ( ! empty( $amount ) ) ? $email_heading . __( '&', 'woocommerce-smart-coupons' ) : __( 'You have received a', 'woocommerce-smart-coupons' ) ), ( ( empty( $amount ) ) ? __( ' coupon', 'woocommerce-smart-coupons' ) : '' ) );
				}

				if ( empty( $email ) ) {
					$email = $gift_certificate_sender_email;
				}

				if ( ! empty( $order_id ) ) {
					$coupon_receiver_details = get_post_meta( $order_id, 'sc_coupon_receiver_details', true );
					if ( ! is_array( $coupon_receiver_details ) || empty( $coupon_receiver_details ) ) {
						$coupon_receiver_details = array();
					}
					$coupon_receiver_details[] = array(
						'code'    => $coupon_code,
						'amount'  => $amount,
						'email'   => $email,
						'message' => $message_from_sender,
					);
					update_post_meta( $order_id, 'sc_coupon_receiver_details', $coupon_receiver_details );
				}

				if ( 'yes' === $is_send_email ) {

					$design           = get_option( 'wc_sc_setting_coupon_design', 'round-dashed' );
					$background_color = get_option( 'wc_sc_setting_coupon_background_color', '#39cccc' );
					$foreground_color = get_option( 'wc_sc_setting_coupon_foreground_color', '#30050b' );
					$coupon_styles    = $this->get_coupon_styles( $design );

					ob_start();

					include apply_filters( 'woocommerce_gift_certificates_email_template', 'templates/email.php' );

					$message = ob_get_clean();

					if ( ! class_exists( 'WC_Email' ) ) {
						include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
					}

					$mailer         = new WC_Email();
					$mailer->id     = 'wc_sc_coupon';
					$mailer->object = wc_get_order( $order_id );
					$headers        = $mailer->get_headers();
					$attachments    = $mailer->get_attachments();

					wc_mail( $email, $subject, $message, $headers, $attachments );

				}
			}

		}

		/**
		 * Register new endpoint to use inside My Account page.
		 */
		public function sc_add_endpoints() {

			add_rewrite_endpoint( WC_SC_Display_Coupons::$endpoint, EP_ROOT | EP_PAGES );
			$this->sc_check_if_flushed_rules();
		}

		/**
		 * To register Smart Coupons Endpoint after plugin is activated - Necessary
		 */
		public function sc_check_if_flushed_rules() {
			$sc_check_flushed_rules = get_option( 'sc_flushed_rules', 'notfound' );
			if ( 'notfound' === $sc_check_flushed_rules ) {
				flush_rewrite_rules();
				update_option( 'sc_flushed_rules', 'found' );
			}
		}

		/**
		 * Register & enqueue Smart Coupons CSS
		 */
		public function register_plugin_styles() {
			global $pagenow;

			$is_frontend         = ( ! is_admin() ) ? true : false;
			$is_valid_post_page  = ( ! empty( $pagenow ) && in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ), true ) ) ? true : false;
			$is_valid_admin_page = ( ( ! empty( $_GET['page'] ) && 'wc-smart-coupons' === wc_clean( wp_unslash( $_GET['page'] ) ) ) || ( ! empty( $_GET['tab'] ) && 'wc-smart-coupons' === wc_clean( wp_unslash( $_GET['tab'] ) ) ) ) ? true : false; // phpcs:ignore

			if ( $is_frontend || $is_valid_admin_page || $is_valid_post_page ) {

				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				wp_register_style( 'smart-coupon', untrailingslashit( plugins_url( '/', WC_SC_PLUGIN_FILE ) ) . '/assets/css/smart-coupon' . $suffix . '.css', array(), $this->plugin_data['Version'] );
				wp_enqueue_style( 'smart-coupon' );
			}

		}

		/**
		 * Get coupon style attributes
		 *
		 * @return string The coupon style attribute
		 */
		public function get_coupon_style_attributes() {

			$styles = array(
				'background-color: ' . get_option( 'wc_sc_setting_coupon_background_color', '#39cccc' ) . ' !important;',
				'color: ' . get_option( 'wc_sc_setting_coupon_foreground_color', '#30050b' ) . ' !important;',
				'border-color: ' . get_option( 'wc_sc_setting_coupon_foreground_color', '#30050b' ) . ' !important;',
			);

			$styles = implode( ' ', $styles );

			return apply_filters( 'wc_sc_coupon_style_attributes', $styles );

		}

		/**
		 * Get coupon container classes
		 *
		 * @return string The coupon container classes
		 */
		public function get_coupon_container_classes() {

			return implode( ' ', apply_filters( 'wc_sc_coupon_container_classes', array( 'medium', get_option( 'wc_sc_setting_coupon_design', 'round-dashed' ) ) ) );

		}

		/**
		 * Get coupon content classes
		 *
		 * @return string The coupon content classes
		 */
		public function get_coupon_content_classes() {

			return implode( ' ', apply_filters( 'wc_sc_coupon_content_classes', array( 'dashed', 'small' ) ) );

		}

		/**
		 * Formatted coupon data
		 *
		 * @param WC_Coupon $coupon Coupon object.
		 * @return array $coupon_data Associative array containing formatted coupon data.
		 */
		public function get_coupon_meta_data( $coupon ) {
			global $store_credit_label;

			$all_discount_types = wc_get_coupon_types();

			if ( $this->is_wc_gte_30() ) {
				$coupon_amount = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_amount' ) ) ) ? $coupon->get_amount() : 0;
				$discount_type = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';
			} else {
				$coupon_amount = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
				$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
			}

			$coupon_data = array();
			switch ( $discount_type ) {
				case 'smart_coupon':
					$coupon_data['coupon_type']   = ! empty( $store_credit_label['singular'] ) ? ucwords( $store_credit_label['singular'] ) : __( 'Store Credit', 'woocommerce-smart-coupons' );
					$coupon_data['coupon_amount'] = wc_price( $coupon_amount );
					break;

				case 'fixed_cart':
					$coupon_data['coupon_type']   = __( 'Cart Discount', 'woocommerce-smart-coupons' );
					$coupon_data['coupon_amount'] = wc_price( $coupon_amount );
					break;

				case 'fixed_product':
					$coupon_data['coupon_type']   = __( 'Product Discount', 'woocommerce-smart-coupons' );
					$coupon_data['coupon_amount'] = wc_price( $coupon_amount );
					break;

				case 'percent_product':
					$coupon_data['coupon_type']   = __( 'Product Discount', 'woocommerce-smart-coupons' );
					$coupon_data['coupon_amount'] = $coupon_amount . '%';
					break;

				case 'percent':
					$coupon_data['coupon_type']   = ( $this->is_wc_gte_30() ) ? __( 'Percentage Discount', 'woocommerce-smart-coupons' ) : __( 'Cart Discount', 'woocommerce-smart-coupons' );
					$coupon_data['coupon_amount'] = $coupon_amount . '%';
					break;

				default:
					$default_coupon_type          = ( ! empty( $all_discount_types[ $discount_type ] ) ) ? $all_discount_types[ $discount_type ] : ucwords( str_replace( array( '_', '-' ), ' ', $discount_type ) );
					$coupon_data['coupon_type']   = apply_filters( 'wc_sc_coupon_type', $default_coupon_type, $coupon, $all_discount_types );
					$coupon_data['coupon_amount'] = apply_filters( 'wc_sc_coupon_amount', $coupon_amount, $coupon );
					break;

			}
			return $coupon_data;
		}

		/**
		 * Update coupon's email id with the updation of customer profile
		 *
		 * @param int $user_id User ID of the user being saved.
		 */
		public function my_profile_update( $user_id ) {

			global $wpdb;

			if ( current_user_can( 'edit_user', $user_id ) ) {

				$current_user = get_userdata( $user_id );

				$old_customers_email_id = $current_user->data->user_email;

				$post_email = ( isset( $_POST['email'] ) ) ? wc_clean( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore

				if ( ! empty( $post_email ) && $post_email !== $old_customers_email_id ) {

					$result = wp_cache_get( 'wc_sc_customers_coupon_ids_' . sanitize_key( $old_customers_email_id ), 'woocommerce_smart_coupons' );

					if ( false === $result ) {
						$result = $wpdb->get_col( // phpcs:ignore
							$wpdb->prepare(
								"SELECT post_id
									FROM $wpdb->postmeta
									WHERE meta_key = %s
									AND meta_value LIKE %s
									AND post_id IN ( SELECT ID
														FROM $wpdb->posts
														WHERE post_type = %s)",
								'customer_email',
								'%' . $wpdb->esc_like( $old_customers_email_id ) . '%',
								'shop_coupon'
							)
						);
						wp_cache_set( 'wc_sc_customers_coupon_ids_' . sanitize_key( $old_customers_email_id ), $result, 'woocommerce_smart_coupons' );
						$this->maybe_add_cache_key( 'wc_sc_customers_coupon_ids_' . sanitize_key( $old_customers_email_id ) );
					}

					if ( ! empty( $result ) ) {

						foreach ( $result as $post_id ) {

							$coupon_meta = get_post_meta( $post_id, 'customer_email', true );

							foreach ( $coupon_meta as $key => $email_id ) {

								if ( $email_id === $old_customers_email_id ) {

									$coupon_meta[ $key ] = $post_email;
								}
							}

							update_post_meta( $post_id, 'customer_email', $coupon_meta );

						} //end foreach
					}
				}
			}
		}

		/**
		 * Method to check whether 'pick_price_from_product' is set or not
		 *
		 * @param array $coupons Array of coupon codes.
		 * @return boolean
		 */
		public function is_coupon_amount_pick_from_product_price( $coupons ) {

			if ( empty( $coupons ) ) {
				return false;
			}

			foreach ( $coupons as $coupon_code ) {
				$coupon = new WC_Coupon( $coupon_code );
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
				if ( 'smart_coupon' === $discount_type && 'yes' === get_post_meta( $coupon_id, 'is_pick_price_of_product', true ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Function to find if order is discounted with store credit
		 *
		 * @param  WC_Order $order Order object.
		 * @return boolean
		 */
		public function is_order_contains_store_credit( $order = null ) {

			if ( empty( $order ) ) {
				return false;
			}

			$coupons = $order->get_items( 'coupon' );

			foreach ( $coupons as $item_id => $item ) {
				$code   = trim( $item['name'] );
				$coupon = new WC_Coupon( $code );
				if ( $this->is_wc_gte_30() ) {
					$discount_type = $coupon->get_discount_type();
				} else {
					$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
				}
				if ( 'smart_coupon' === $discount_type ) {
					return true;
				}
			}

			return false;

		}

		/**
		 * Function to validate smart coupon for product
		 *
		 * @param bool            $valid Coupon validity.
		 * @param WC_Product|null $product Product object.
		 * @param WC_Coupon|null  $coupon Coupon object.
		 * @param array|null      $values Values.
		 * @return bool           $valid
		 */
		public function smart_coupons_is_valid_for_product( $valid, $product = null, $coupon = null, $values = null ) {

			if ( empty( $product ) || empty( $coupon ) ) {
				return $valid;
			}

			if ( $this->is_wc_gte_30() ) {
				$product_id                         = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
				$product_parent_id                  = ( is_object( $product ) && is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0;
				$product_variation_id               = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
				$discount_type                      = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';
				$coupon_product_ids                 = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_product_ids' ) ) ) ? $coupon->get_product_ids() : '';
				$coupon_product_categories          = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_product_categories' ) ) ) ? $coupon->get_product_categories() : '';
				$coupon_excluded_product_ids        = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_excluded_product_ids' ) ) ) ? $coupon->get_excluded_product_ids() : '';
				$coupon_excluded_product_categories = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_excluded_product_categories' ) ) ) ? $coupon->get_excluded_product_categories() : '';
				$is_exclude_sale_items              = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_exclude_sale_items' ) ) ) ? ( ( $coupon->get_exclude_sale_items() ) ? 'yes' : 'no' ) : '';
			} else {
				$product_id                         = ( ! empty( $product->id ) ) ? $product->id : 0;
				$product_parent_id                  = ( ! empty( $product ) && is_callable( array( $product, 'get_parent' ) ) ) ? $product->get_parent() : 0;
				$product_variation_id               = ( ! empty( $product->variation_id ) ) ? $product->variation_id : 0;
				$discount_type                      = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
				$coupon_product_ids                 = ( ! empty( $coupon->product_ids ) ) ? $coupon->product_ids : array();
				$coupon_product_categories          = ( ! empty( $coupon->product_categories ) ) ? $coupon->product_categories : array();
				$coupon_excluded_product_ids        = ( ! empty( $coupon->exclude_product_ids ) ) ? $coupon->exclude_product_ids : array();
				$coupon_excluded_product_categories = ( ! empty( $coupon->exclude_product_categories ) ) ? $coupon->exclude_product_categories : array();
				$is_exclude_sale_items              = ( ! empty( $coupon->exclude_sale_items ) ) ? $coupon->exclude_sale_items : '';
			}

			if ( 'smart_coupon' === $discount_type ) {

				$product_cats = wc_get_product_cat_ids( $product_id );

				// Specific products get the discount.
				if ( count( $coupon_product_ids ) > 0 ) {

					if ( in_array( $product_id, $coupon_product_ids, true ) || ( isset( $product_variation_id ) && in_array( $product_variation_id, $coupon_product_ids, true ) ) || in_array( $product_parent_id, $coupon_product_ids, true ) ) {
						$valid = true;
					}

					// Category discounts.
				} elseif ( count( $coupon_product_categories ) > 0 ) {

					if ( count( array_intersect( $product_cats, $coupon_product_categories ) ) > 0 ) {
						$valid = true;
					}
				} else {
					// No product ids - all items discounted.
					$valid = true;
				}

				// Specific product ID's excluded from the discount.
				if ( count( $coupon_excluded_product_ids ) > 0 ) {
					if ( in_array( $product_id, $coupon_excluded_product_ids, true ) || ( isset( $product_variation_id ) && in_array( $product_variation_id, $coupon_excluded_product_ids, true ) ) || in_array( $product_parent_id, $coupon_excluded_product_ids, true ) ) {
						$valid = false;
					}
				}

				// Specific categories excluded from the discount.
				if ( count( $coupon_excluded_product_categories ) > 0 ) {
					if ( count( array_intersect( $product_cats, $coupon_excluded_product_categories ) ) > 0 ) {
						$valid = false;
					}
				}

				// Sale Items excluded from discount.
				if ( 'yes' === $is_exclude_sale_items ) {
					$product_ids_on_sale = wc_get_product_ids_on_sale();

					if ( in_array( $product_id, $product_ids_on_sale, true ) || ( isset( $product_variation_id ) && in_array( $product_variation_id, $product_ids_on_sale, true ) ) || in_array( $product_parent_id, $product_ids_on_sale, true ) ) {
						$valid = false;
					}
				}
			}

			return $valid;
		}

		/**
		 * Function to keep valid coupons when individual use coupon is applied
		 *
		 * @param  array              $coupons_to_keep Coupons to keep.
		 * @param  WC_Coupons|boolean $the_coupon Coupon object.
		 * @param  array              $applied_coupons Array of applied coupons.
		 * @return array              $coupons_to_keep
		 */
		public function smart_coupons_override_individual_use( $coupons_to_keep = array(), $the_coupon = false, $applied_coupons = array() ) {

			if ( $this->is_wc_gte_30() ) {
				foreach ( $applied_coupons as $code ) {
					$coupon = new WC_Coupon( $code );
					if ( 'smart_coupon' === $coupon->get_discount_type() && ! $coupon->get_individual_use() && ! in_array( $code, $coupons_to_keep, true ) ) {
						$coupons_to_keep[] = $code;
					}
				}
			}

			return $coupons_to_keep;
		}

		/**
		 * Force apply store credit even if the individual coupon already exists in cart
		 *
		 * @param  boolean            $is_apply Apply with individual use coupon.
		 * @param  WC_Coupons|boolean $the_coupon Coupon object.
		 * @param  WC_Coupons|boolean $applied_coupon Coupon object.
		 * @param  array              $applied_coupons Array of applied coupons.
		 * @return boolean
		 */
		public function smart_coupons_override_with_individual_use( $is_apply = false, $the_coupon = false, $applied_coupon = false, $applied_coupons = array() ) {

			if ( $this->is_wc_gte_30() ) {
				if ( ! $is_apply && 'smart_coupon' === $the_coupon->get_discount_type() && ! $the_coupon->get_individual_use() ) {
					$is_apply = true;
				}
			}

			return $is_apply;
		}

		/**
		 * Function to add appropriate discount total filter
		 */
		public function smart_coupons_discount_total_filters() {
			if ( WCS_SC_Compatibility::is_cart_contains_subscription() && WCS_SC_Compatibility::is_wcs_gte( '2.0.0' ) ) {
				add_action( 'woocommerce_after_calculate_totals', array( $this, 'smart_coupons_after_calculate_totals' ) );
			} else {
				add_action( 'woocommerce_after_calculate_totals', array( $this, 'smart_coupons_after_calculate_totals' ) );
				global $current_screen;
				if ( ! empty( $current_screen ) && 'edit-shop_order' !== $current_screen ) {
					add_filter( 'woocommerce_order_get_total', array( $this, 'smart_coupons_order_discounted_total' ), 10, 2 );
				}
			}
		}

		/**
		 * Function to handle store credit application
		 */
		public function sc_handle_store_credit_application() {
			$apply_before_tax = get_option( 'woocommerce_smart_coupon_apply_before_tax', 'no' );

			if ( $this->is_wc_gte_30() && wc_tax_enabled() && 'yes' === $apply_before_tax ) {
				include_once 'class-wc-sc-apply-before-tax.php';
			} else {
				add_action( 'wp_loaded', array( $this, 'smart_coupons_discount_total_filters' ), 20 );
				add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'order_calculate_discount_amount' ), 10, 2 );
			}
		}

		/**
		 * Function to set store credit amount for orders that are manually created and updated from backend
		 *
		 * @param bool     $and_taxes Calc taxes if true.
		 * @param WC_Order $order Order object.
		 */
		public function order_calculate_discount_amount( $and_taxes, $order ) {

			$order_actions = array( 'woocommerce_add_coupon_discount', 'woocommerce_calc_line_taxes', 'woocommerce_save_order_items' );

			$post_action    = ( ! empty( $_POST['action'] ) ) ? wc_clean( wp_unslash( $_POST['action'] ) ) : ''; // phpcs:ignore
			$post_post_type = ( ! empty( $_POST['post_type'] ) ) ? wc_clean( wp_unslash( $_POST['post_type'] ) ) : ''; // phpcs:ignore

			if ( $order instanceof WC_Order && ! empty( $post_action ) && ( in_array( $post_action, $order_actions, true ) || ( ! empty( $post_post_type ) && 'shop_order' === $post_post_type && 'editpost' === $post_action ) ) ) {
				if ( ! is_object( $order ) || ! is_callable( array( $order, 'get_id' ) ) ) {
					return;
				}
				$order_id = $order->get_id();
				if ( empty( $order_id ) ) {
					return;
				}
				$coupons = $order->get_items( 'coupon' );

				if ( ! empty( $coupons ) ) {
					foreach ( $coupons as $item_id => $item ) {
						if ( empty( $item['name'] ) ) {
							continue;
						}

						$coupon_code   = $item['name'];
						$coupon        = new WC_Coupon( $coupon_code );
						$discount_type = $coupon->get_discount_type();

						if ( 'smart_coupon' === $discount_type ) {
							$total                      = $order->get_total();
							$discount_amount            = wc_get_order_item_meta( $item_id, 'discount_amount', true );
							$smart_coupons_contribution = get_post_meta( $order_id, 'smart_coupons_contribution', true );
							$smart_coupons_contribution = ! empty( $smart_coupons_contribution ) ? $smart_coupons_contribution : array();

							if ( ! empty( $smart_coupons_contribution ) && count( $smart_coupons_contribution ) > 0 && array_key_exists( $coupon_code, $smart_coupons_contribution ) ) {
								$discount = $smart_coupons_contribution[ $coupon_code ];
							} elseif ( ! empty( $discount_amount ) ) {
								$discount = $discount_amount;
							} else {
								$discount = $this->sc_order_get_discount_amount( $total, $coupon, $order );
							}

							$discount                = min( $total, $discount );
							$item['discount_amount'] = $discount;

							$order->set_total( $total - $discount );

							$smart_coupons_contribution[ $coupon_code ] = $discount;

							update_post_meta( $order_id, 'smart_coupons_contribution', $smart_coupons_contribution );

							if ( 'woocommerce_add_coupon_discount' === $post_action && $order->has_status( array( 'on-hold', 'auto-draft', 'pending' ) ) ) {
								do_action( 'sc_after_order_calculate_discount_amount', $order_id );
							}
						}
					}
				}
			}
		}

		/**
		 * Function to get discount amount for orders
		 *
		 * @param  float     $total Order total.
		 * @param  WC_Coupon $coupon Coupon object.
		 * @param  WC_Order  $order Order object.
		 * @return float     $discount
		 */
		public function sc_order_get_discount_amount( $total, $coupon, $order ) {
			$discount = 0;

			if ( $coupon instanceof WC_Coupon && $order instanceof WC_Order ) {
				$coupon_amount             = $coupon->get_amount();
				$discount_type             = $coupon->get_discount_type();
				$coupon_code               = $coupon->get_code();
				$coupon_product_ids        = $coupon->get_product_ids();
				$coupon_product_categories = $coupon->get_product_categories();

				if ( 'smart_coupon' === $discount_type ) {

					$calculated_total = $total;

					if ( count( $coupon_product_ids ) > 0 || count( $coupon_product_categories ) > 0 ) {

						$discount            = 0;
						$line_totals         = 0;
						$line_taxes          = 0;
						$discounted_products = array();

						$order_items = $order->get_items( 'line_item' );

						foreach ( $order_items as $order_item_id => $order_item ) {
							if ( $discount >= $coupon_amount ) {
								break;
							}

							$product_cats = wc_get_product_cat_ids( $order_item['product_id'] );

							if ( count( $coupon_product_categories ) > 0 ) {

								$continue = false;

								if ( ! empty( $order_item_id ) && ! empty( $discounted_products ) && is_array( $discounted_products ) && in_array( $order_item_id, $discounted_products, true ) ) {
									$continue = true;
								}

								if ( ! $continue && count( array_intersect( $product_cats, $coupon_product_categories ) ) > 0 ) {

									$discounted_products[] = ( ! empty( $order_item_id ) ) ? $order_item_id : '';

									$line_totals += $order_item['line_total'];
									$line_taxes  += $order_item['line_tax'];

								}
							}

							if ( count( $coupon_product_ids ) > 0 ) {

								$continue = false;

								if ( ! empty( $order_item_id ) && ! empty( $discounted_products ) && is_array( $discounted_products ) && in_array( $order_item_id, $discounted_products, true ) ) {
									$continue = true;
								}

								if ( ! $continue && in_array( $order_item['product_id'], $coupon_product_ids, true ) || in_array( $order_item['variation_id'], $coupon_product_ids, true ) ) {

									$discounted_products[] = ( ! empty( $order_item_id ) ) ? $order_item_id : '';

									$line_totals += $order_item['line_total'];
									$line_taxes  += $order_item['line_tax'];

								}
							}
						}

						$calculated_total = round( ( $line_totals + $line_taxes ), wc_get_price_decimals() );

					}
					$discount = min( $calculated_total, $coupon_amount );
				}
			}

			return $discount;
		}

		/**
		 * Function to apply smart coupons discount
		 *
		 * @param  float   $total Cart total.
		 * @param  WC_Cart $cart Cart object.
		 * @param  boolean $cart_contains_subscription Is cart contains subscription.
		 * @param  string  $calculation_type           The calculation type.
		 * @return float   $total
		 */
		public function smart_coupons_discounted_totals( $total = 0, $cart = null, $cart_contains_subscription = false, $calculation_type = '' ) {

			if ( empty( $total ) ) {
				return $total;
			}

			$applied_coupons = ( is_object( WC()->cart ) && is_callable( array( WC()->cart, 'get_applied_coupons' ) ) ) ? WC()->cart->get_applied_coupons() : array();

			if ( ! empty( $applied_coupons ) ) {
				foreach ( $applied_coupons as $code ) {
					$coupon   = new WC_Coupon( $code );
					$discount = $this->sc_cart_get_discount_amount( $total, $coupon );

					if ( ! empty( $discount ) ) {
						$discount = min( $total, $discount );
						$total    = $total - $discount;
						$this->manage_smart_coupon_credit_used( $coupon, $discount, $cart_contains_subscription, $calculation_type );
					}
				}
			}

			return $total;
		}

		/**
		 * Function to apply smart coupons discount after calculating tax
		 *
		 * @param  WC_Cart $cart Cart object.
		 */
		public function smart_coupons_after_calculate_totals( $cart = null ) {

			if ( empty( $cart ) || ! ( $cart instanceof WC_Cart ) ) {
				return;
			}

			$cart_total = ( $this->is_wc_greater_than( '3.1.2' ) ) ? $cart->get_total( 'edit' ) : $cart->total;

			if ( ! empty( $cart_total ) ) {

				$stop_at = 1;

				$cart_contains_subscription = WCS_SC_Compatibility::is_cart_contains_subscription();
				$calculation_type           = '';

				if ( $cart_contains_subscription ) {
					$stop_at          = 2;
					$calculation_type = WC_Subscriptions_Cart::get_calculation_type();
				}

				if ( did_action( 'smart_coupons_after_calculate_totals' ) > $stop_at ) {
					return;
				}

				if ( 'recurring_total' === $calculation_type ) {
					$total = $cart_total;
				} else {
					$total = $this->smart_coupons_discounted_totals( $cart_total, $cart, $cart_contains_subscription, $calculation_type );
				}

				if ( $this->is_wc_greater_than( '3.1.2' ) ) {
					$cart->set_total( $total );
				} else {
					$cart->total = $total;
				}

				do_action( 'smart_coupons_after_calculate_totals' );

			}

		}

		/**
		 * Function to get discount amount
		 *
		 * @param  float     $total The total.
		 * @param  WC_Coupon $coupon The coupon object.
		 * @return float     $discount
		 */
		public function sc_cart_get_discount_amount( $total = 0, $coupon = '' ) {

			$discount = 0;

			if ( $coupon instanceof WC_Coupon ) {

				if ( $coupon->is_valid() && $coupon->is_type( 'smart_coupon' ) ) {

					if ( $this->is_wc_gte_30() ) {
						$coupon_amount             = $coupon->get_amount();
						$coupon_code               = $coupon->get_code();
						$coupon_product_ids        = $coupon->get_product_ids();
						$coupon_product_categories = $coupon->get_product_categories();
					} else {
						$coupon_amount             = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
						$coupon_code               = ( ! empty( $coupon->code ) ) ? $coupon->code : '';
						$coupon_product_ids        = ( ! empty( $coupon->product_ids ) ) ? $coupon->product_ids : array();
						$coupon_product_categories = ( ! empty( $coupon->product_categories ) ) ? $coupon->product_categories : array();
					}

					$calculated_total = $total;

					if ( count( $coupon_product_ids ) > 0 || count( $coupon_product_categories ) > 0 ) {

						$discount            = 0;
						$line_totals         = 0;
						$line_taxes          = 0;
						$discounted_products = array();

						foreach ( WC()->cart->cart_contents as $cart_item_key => $product ) {

							if ( $discount >= $coupon_amount ) {
								break;
							}

							$product_cats = wc_get_product_cat_ids( $product['product_id'] );

							if ( count( $coupon_product_categories ) > 0 ) {

								$continue = false;

								if ( ! empty( $cart_item_key ) && ! empty( $discounted_products ) && is_array( $discounted_products ) && in_array( $cart_item_key, $discounted_products, true ) ) {
									$continue = true;
								}

								if ( ! $continue && count( array_intersect( $product_cats, $coupon_product_categories ) ) > 0 ) {

									$discounted_products[] = ( ! empty( $cart_item_key ) ) ? $cart_item_key : '';

									$line_totals += $product['line_total'];
									$line_taxes  += $product['line_tax'];

								}
							}

							if ( count( $coupon_product_ids ) > 0 ) {

								$continue = false;

								if ( ! empty( $cart_item_key ) && ! empty( $discounted_products ) && is_array( $discounted_products ) && in_array( $cart_item_key, $discounted_products, true ) ) {
									$continue = true;
								}

								if ( ! $continue && in_array( $product['product_id'], $coupon_product_ids, true ) || in_array( $product['variation_id'], $coupon_product_ids, true ) || in_array( $product['data']->get_parent(), $coupon_product_ids, true ) ) {

									$discounted_products[] = ( ! empty( $cart_item_key ) ) ? $cart_item_key : '';

									$line_totals += $product['line_total'];
									$line_taxes  += $product['line_tax'];

								}
							}
						}

						$calculated_total = round( ( $line_totals + $line_taxes ), wc_get_price_decimals() );

					}

					$discount = min( $calculated_total, $coupon_amount );
				}
			}

			return $discount;
		}

		/**
		 * Function to manage store credit used
		 *
		 * @param WC_Coupon $coupon The coupon object.
		 * @param float     $discount The discount.
		 * @param bool      $cart_contains_subscription Is cart contains subscription.
		 * @param string    $calculation_type Calculation type.
		 */
		public function manage_smart_coupon_credit_used( $coupon = '', $discount = 0, $cart_contains_subscription = false, $calculation_type = '' ) {
			if ( is_object( $coupon ) && $coupon instanceof WC_Coupon ) {

				if ( $this->is_wc_gte_30() ) {
					$coupon_code = $coupon->get_code();
				} else {
					$coupon_code = ( ! empty( $coupon->code ) ) ? $coupon->code : '';
				}

				if ( $cart_contains_subscription ) {
					if ( WCS_SC_Compatibility::is_wcs_gte( '2.0.10' ) ) {
						if ( $this->is_wc_greater_than( '3.1.2' ) ) {
							$coupon_discount_totals = WC()->cart->get_coupon_discount_totals();
							if ( empty( $coupon_discount_totals ) || ! is_array( $coupon_discount_totals ) ) {
								$coupon_discount_totals = array();
							}
							if ( empty( $coupon_discount_totals[ $coupon_code ] ) ) {
								$coupon_discount_totals[ $coupon_code ] = $discount;
							} else {
								$coupon_discount_totals[ $coupon_code ] += $discount;
							}
							WC()->cart->set_coupon_discount_totals( $coupon_discount_totals );
						} else {
							$coupon_discount_amounts = ( is_object( WC()->cart ) && isset( WC()->cart->coupon_discount_amounts ) ) ? WC()->cart->coupon_discount_amounts : array();
							if ( empty( $coupon_discount_amounts ) || ! is_array( $coupon_discount_amounts ) ) {
								$coupon_discount_amounts = array();
							}
							if ( empty( $coupon_discount_amounts[ $coupon_code ] ) ) {
								$coupon_discount_amounts[ $coupon_code ] = $discount;
							} else {
								$coupon_discount_amounts[ $coupon_code ] += $discount;
							}
							WC()->cart->coupon_discount_amounts = $coupon_discount_amounts;
						}
					} elseif ( WCS_SC_Compatibility::is_wcs_gte( '2.0.0' ) ) {
						WC_Subscriptions_Coupon::increase_coupon_discount_amount( WC()->cart, $coupon_code, $discount );
					} else {
						WC_Subscriptions_Cart::increase_coupon_discount_amount( $coupon_code, $discount );
					}
				} else {
					if ( $this->is_wc_greater_than( '3.1.2' ) ) {
						$coupon_discount_totals = WC()->cart->get_coupon_discount_totals();
						if ( empty( $coupon_discount_totals ) || ! is_array( $coupon_discount_totals ) ) {
							$coupon_discount_totals = array();
						}
						if ( empty( $coupon_discount_totals[ $coupon_code ] ) ) {
							$coupon_discount_totals[ $coupon_code ] = $discount;
						} else {
							$coupon_discount_totals[ $coupon_code ] += $discount;
						}
						WC()->cart->set_coupon_discount_totals( $coupon_discount_totals );
					} else {
						$coupon_discount_amounts = ( is_object( WC()->cart ) && isset( WC()->cart->coupon_discount_amounts ) ) ? WC()->cart->coupon_discount_amounts : array();
						if ( empty( $coupon_discount_amounts ) || ! is_array( $coupon_discount_amounts ) ) {
							$coupon_discount_amounts = array();
						}
						if ( empty( $coupon_discount_amounts[ $coupon_code ] ) ) {
							$coupon_discount_amounts[ $coupon_code ] = $discount;
						} else {
							$coupon_discount_amounts[ $coupon_code ] += $discount;
						}
						WC()->cart->coupon_discount_amounts = $coupon_discount_amounts;
					}
				}

				if ( isset( WC()->session->reload_checkout ) ) {        // reload_checkout is triggered when customer is registered from checkout.
					unset( WC()->cart->smart_coupon_credit_used );  // reset store credit used data for re-calculation.
				}

				$smart_coupon_credit_used = ( is_object( WC()->cart ) && isset( WC()->cart->smart_coupon_credit_used ) ) ? WC()->cart->smart_coupon_credit_used : array();

				if ( empty( $smart_coupon_credit_used ) || ! is_array( $smart_coupon_credit_used ) ) {
					$smart_coupon_credit_used = array();
				}
				if ( empty( $smart_coupon_credit_used[ $coupon_code ] ) || ( $cart_contains_subscription && ( 'combined_total' === $calculation_type || 'sign_up_fee_total' === $calculation_type ) ) ) {
					$smart_coupon_credit_used[ $coupon_code ] = $discount;
				} else {
					$smart_coupon_credit_used[ $coupon_code ] += $discount;
				}
				WC()->cart->smart_coupon_credit_used = $smart_coupon_credit_used;

			}
		}

		/**
		 * Apply store credit discount in order during recalculation
		 *
		 * @param  float    $total The total.
		 * @param  WC_Order $order The order object.
		 * @return float    $total
		 */
		public function smart_coupons_order_discounted_total( $total = 0, $order = null ) {

			if ( ! $this->is_wc_gte_30() ) {

				$is_proceed = check_ajax_referer( 'calc-totals', 'security', false );

				if ( ! $is_proceed ) {
					return $total;
				}

				$called_by = ( ! empty( $_POST['action'] ) ) ? wc_clean( wp_unslash( $_POST['action'] ) ) : ''; // phpcs:ignore

				if ( 'woocommerce_calc_line_taxes' !== $called_by ) {
					return $total;
				}
			}

			if ( empty( $order ) ) {
				return $total;
			}

			$coupons = ( is_object( $order ) && is_callable( array( $order, 'get_items' ) ) ) ? $order->get_items( 'coupon' ) : array();

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					$code = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_code' ) ) ) ? $coupon->get_code() : '';
					if ( empty( $code ) ) {
						continue;
					}
					$_coupon       = new WC_Coupon( $code );
					$discount_type = ( is_object( $_coupon ) && is_callable( array( $_coupon, 'get_discount_type' ) ) ) ? $_coupon->get_discount_type() : '';
					if ( ! empty( $discount_type ) && 'smart_coupon' === $discount_type ) {
						$discount         = ( is_object( $_coupon ) && is_callable( array( $_coupon, 'get_amount' ) ) ) ? $_coupon->get_amount() : 0;
						$applied_discount = min( $total, $discount );
						if ( $this->is_wc_gte_30() ) {
							$coupon->set_discount( $applied_discount );
							$coupon->save();
						}
						$total = $total - $applied_discount;
					}
				}
			}

			return $total;
		}

		/**
		 * Add tool for clearing cache
		 *
		 * @param  array $tools Existing tools.
		 * @return array $tools
		 */
		public function clear_cache_tool( $tools = array() ) {

			$tools['wc_sc_clear_cache'] = array(
				'name'     => __( 'WooCommerce Smart Coupons Cache', 'woocommerce-smart-coupons' ),
				'button'   => __( 'Clear Smart Coupons Cache', 'woocommerce-smart-coupons' ),
				'desc'     => __( 'This tool will clear the cache created by WooCommerce Smart Coupons.', 'woocommerce-smart-coupons' ),
				'callback' => array(
					$this,
					'clear_cache',
				),
			);

			return $tools;
		}

		/**
		 * Clear cache
		 *
		 * @return string $message
		 */
		public function clear_cache() {

			$message = ( is_callable( array( 'WC_SC_Act_Deact', 'clear_cache' ) ) ) ? WC_SC_Act_Deact::clear_cache() : '';

			return $message;
		}

		/**
		 * Function to return validity of Store Credit / Gift Certificate
		 *
		 * @param boolean   $valid Coupon validity.
		 * @param WC_Coupon $coupon Coupon object.
		 * @return boolean  $valid TRUE if smart coupon valid, FALSE otherwise
		 */
		public function is_smart_coupon_valid( $valid, $coupon ) {

			if ( $this->is_wc_gte_30() ) {
				$coupon_amount = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_amount' ) ) ) ? $coupon->get_amount() : 0;
				$discount_type = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';
				$coupon_code   = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_code' ) ) ) ? $coupon->get_code() : '';
			} else {
				$coupon_amount = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
				$discount_type = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
				$coupon_code   = ( ! empty( $coupon->code ) ) ? $coupon->code : '';
			}

			if ( 'smart_coupon' !== $discount_type ) {
				return $valid;
			}

			$applied_coupons = ( is_object( WC()->cart ) && is_callable( array( WC()->cart, 'get_applied_coupons' ) ) ) ? WC()->cart->get_applied_coupons() : array();

			if ( empty( $applied_coupons ) || ( ! empty( $applied_coupons ) && ! in_array( $coupon_code, $applied_coupons, true ) ) ) {
				return $valid;
			}

			if ( is_wc_endpoint_url( 'order-received' ) ) {
				return $valid;
			}

			if ( $valid && $coupon_amount <= 0 ) {
				WC()->cart->remove_coupon( $coupon_code );
				/* translators: The coupon code */
				wc_add_notice( sprintf( __( 'Coupon removed. There is no credit remaining in %s.', 'woocommerce-smart-coupons' ), '<strong>' . $coupon_code . '</strong>' ), 'error' );
				return false;
			}

			return $valid;
		}

		/**
		 * Strict check if user is valid as per usage limit
		 *
		 * @param  boolean      $is_valid  Is valid.
		 * @param  WC_Coupon    $coupon    The coupon object.
		 * @param  WC_Discounts $discounts The discounts object.
		 * @return boolean
		 */
		public function is_user_usage_limit_valid( $is_valid = false, $coupon = null, $discounts = null ) {

			if ( is_admin() ) {
				return $is_valid;
			}

			if ( true !== $is_valid ) {
				return $is_valid;
			}

			global $wpdb;

			if ( $this->is_wc_gte_30() ) {
				$coupon_id = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
			} else {
				$coupon_id = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
			}

			$for_new_user = get_post_meta( $coupon_id, 'sc_restrict_to_new_user', true );

			if ( 'yes' === $for_new_user ) {

				$user_id_1 = 0;
				$user_id_2 = 0;
				$user_id_3 = 0;

				$current_user = wp_get_current_user();

				$email = ( ! empty( $current_user->data->user_email ) ) ? $current_user->data->user_email : '';

				$email = ( ! empty( $_REQUEST['billing_email'] ) ) ? sanitize_email( wp_unslash( $_REQUEST['billing_email'] ) ) : $email; // phpcs:ignore

				if ( ! empty( $email ) && is_email( $email ) ) {

					$user_id_1 = wp_cache_get( 'wc_sc_user_id_by_user_email_' . sanitize_key( $email ), 'woocommerce_smart_coupons' );
					if ( false === $user_id_1 ) {
						$user_id_1 = $wpdb->get_var( // phpcs:ignore
							$wpdb->prepare(
								"SELECT ID
									FROM {$wpdb->prefix}users
									WHERE user_email = %s",
								$email
							)
						);
						wp_cache_set( 'wc_sc_user_id_by_user_email_' . sanitize_key( $email ), $user_id_1, 'woocommerce_smart_coupons' );
						$this->maybe_add_cache_key( 'wc_sc_user_id_by_user_email_' . sanitize_key( $email ) );
					}

					$user_id_2 = wp_cache_get( 'wc_sc_user_id_by_billing_email_' . sanitize_key( $email ), 'woocommerce_smart_coupons' );
					if ( false === $user_id_2 ) {
						$user_id_2 = $wpdb->get_var( // phpcs:ignore
							$wpdb->prepare(
								"SELECT user_id
									FROM {$wpdb->prefix}usermeta
									WHERE meta_key = %s
										AND meta_value = %s",
								'billing_email',
								$email
							)
						);
						wp_cache_set( 'wc_sc_user_id_by_billing_email_' . sanitize_key( $email ), $user_id_2, 'woocommerce_smart_coupons' );
						$this->maybe_add_cache_key( 'wc_sc_user_id_by_billing_email_' . sanitize_key( $email ) );
					}
				}

				$user_id_3 = get_current_user_id();

				$user_ids = array( $user_id_1, $user_id_2, $user_id_3 );
				$user_ids = array_unique( array_filter( $user_ids ) );

				if ( ! empty( $user_ids ) ) {

					$unique_user_ids = array_unique( $user_ids );

					$order_id = wp_cache_get( 'wc_sc_order_for_user_id_' . implode( '_', $unique_user_ids ), 'woocommerce_smart_coupons' );

					if ( false === $order_id ) {
						$query = $wpdb->prepare(
							"SELECT ID
								FROM $wpdb->posts AS p
								LEFT JOIN $wpdb->postmeta AS pm
									ON ( p.ID = pm.post_id AND pm.meta_key = %s )
								WHERE p.post_type = %s
									AND p.post_status IN ( %s, %s )",
							'_customer_user',
							'shop_order',
							'wc-processing',
							'wc-completed'
						);

						$how_many_user_ids = count( $user_ids );
						$id_placeholder    = array_fill( 0, $how_many_user_ids, '%s' );

						// phpcs:disable
						$query .= $wpdb->prepare(
							' AND pm.meta_value IN (' . implode( ',', $id_placeholder ) . ')',
							$user_ids
						);
						// phpcs:enable

						$order_id = $wpdb->get_var( $query ); // phpcs:ignore

						wp_cache_set( 'wc_sc_order_for_user_id_' . implode( '_', $unique_user_ids ), $order_id, 'woocommerce_smart_coupons' );
						$this->maybe_add_cache_key( 'wc_sc_order_for_user_id_' . implode( '_', $unique_user_ids ) );
					}

					if ( ! empty( $order_id ) ) {
						return false;
					}
				} elseif ( ! empty( $email ) ) {

					$order_id = wp_cache_get( 'wc_sc_order_id_by_billing_email_' . sanitize_key( $email ), 'woocommerce_smart_coupons' );

					if ( false === $order_id ) {
						$order_id = $wpdb->get_var( // phpcs:ignore
							$wpdb->prepare(
								"SELECT ID
									FROM $wpdb->posts AS p
									LEFT JOIN $wpdb->postmeta AS pm
										ON ( p.ID = pm.post_id AND pm.meta_key = %s )
									WHERE p.post_type = %s
										AND pm.meta_value = %s",
								'_billing_email',
								'shop_order',
								$email
							)
						);

						wp_cache_set( 'wc_sc_order_id_by_billing_email_' . sanitize_key( $email ), $order_id, 'woocommerce_smart_coupons' );
						$this->maybe_add_cache_key( 'wc_sc_order_id_by_billing_email_' . sanitize_key( $email ) );
					}

					if ( ! empty( $order_id ) ) {
						return false;
					}
				}
			}

			return $is_valid;
		}

		/**
		 * Locate template for Smart Coupons
		 *
		 * @param string $template_name The template name.
		 * @param mixed  $template Default template.
		 * @return mixed $template
		 */
		public function locate_template_for_smart_coupons( $template_name = '', $template = '' ) {

			$default_path = untrailingslashit( plugin_dir_path( WC_SC_PLUGIN_FILE ) ) . '/templates/';

			$plugin_base_dir = substr( plugin_basename( WC_SC_PLUGIN_FILE ), 0, strpos( plugin_basename( WC_SC_PLUGIN_FILE ), '/' ) + 1 );

			// Look within passed path within the theme - this is priority.
			$template = locate_template(
				array(
					'woocommerce/' . $plugin_base_dir . $template_name,
					$plugin_base_dir . $template_name,
					$template_name,
				)
			);

			// Get default template.
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}

			return $template;
		}

		/**
		 * Check whether credit is sent or not
		 *
		 * @param string    $email_id The email address.
		 * @param WC_Coupon $coupon The coupon object.
		 * @return boolean
		 */
		public function is_credit_sent( $email_id, $coupon ) {

			global $smart_coupon_codes;

			if ( isset( $smart_coupon_codes[ $email_id ] ) && count( $smart_coupon_codes[ $email_id ] ) > 0 ) {
				if ( $this->is_wc_gte_30() ) {
					$coupon_id = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
				} else {
					$coupon_id = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
				}
				foreach ( $smart_coupon_codes[ $email_id ] as $generated_coupon_details ) {
					if ( $generated_coupon_details['parent'] === $coupon_id ) {
						return true;
					}
				}
			}

			return false;

		}

		/**
		 * Generate unique string to be used as coupon code. Also add prefix or suffix if already set
		 *
		 * @param string    $email The email address.
		 * @param WC_Coupon $coupon The coupon object.
		 * @return string $unique_code
		 */
		public function generate_unique_code( $email = '', $coupon = '' ) {
			$unique_code = '';
			srand( (double) microtime( true ) * 1000000 ); // phpcs:ignore

			$chars = array_merge( range( 'a', 'z' ), range( '1', '9' ) );
			for ( $rand = 1; $rand <= WC_SC_COUPON_CODE_LENGTH; $rand++ ) {
				$random       = rand( 0, count( $chars ) - 1 ); // phpcs:ignore
				$unique_code .= $chars[ $random ];
			}

			if ( $this->is_wc_gte_30() ) {
				$coupon_id = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
			} else {
				$coupon_id = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
			}

			if ( ! empty( $coupon_id ) && get_post_meta( $coupon_id, 'auto_generate_coupon', true ) === 'yes' ) {
				$prefix      = get_post_meta( $coupon_id, 'coupon_title_prefix', true );
				$suffix      = get_post_meta( $coupon_id, 'coupon_title_suffix', true );
				$unique_code = $prefix . $unique_code . $suffix;
			}

			return apply_filters(
				'wc_sc_generate_unique_coupon_code', $unique_code, array(
					'email'  => $email,
					'coupon' => $coupon,
				)
			);
		}

		/**
		 * Function for generating Gift Certificate
		 *
		 * @param mixed     $email The email address.
		 * @param float     $amount The amount.
		 * @param int       $order_id The order id.
		 * @param WC_Coupon $coupon The coupon object.
		 * @param string    $discount_type The discount type.
		 * @param array     $gift_certificate_receiver_name Receiver name.
		 * @param string    $message_from_sender Message from sender.
		 * @param string    $gift_certificate_sender_name Sender name.
		 * @param string    $gift_certificate_sender_email Sender email.
		 * @return array of generated coupon details
		 */
		public function generate_smart_coupon( $email, $amount, $order_id = '', $coupon = '', $discount_type = 'smart_coupon', $gift_certificate_receiver_name = '', $message_from_sender = '', $gift_certificate_sender_name = '', $gift_certificate_sender_email = '' ) {
			return apply_filters( 'generate_smart_coupon_action', $email, $amount, $order_id, $coupon, $discount_type, $gift_certificate_receiver_name, $message_from_sender, $gift_certificate_sender_name, $gift_certificate_sender_email );
		}

		/**
		 * Function for generating Gift Certificate
		 *
		 * @param mixed     $email The email address.
		 * @param float     $amount The amount.
		 * @param int       $order_id The order id.
		 * @param WC_Coupon $coupon The coupon object.
		 * @param string    $discount_type The discount type.
		 * @param array     $gift_certificate_receiver_name Receiver name.
		 * @param string    $message_from_sender Message from sender.
		 * @param string    $gift_certificate_sender_name Sender name.
		 * @param string    $gift_certificate_sender_email Sender email.
		 * @return array $smart_coupon_codes associative array containing generated coupon details
		 */
		public function generate_smart_coupon_action( $email, $amount, $order_id = '', $coupon = '', $discount_type = 'smart_coupon', $gift_certificate_receiver_name = '', $message_from_sender = '', $gift_certificate_sender_name = '', $gift_certificate_sender_email = '' ) {

			if ( '' === $email ) {
				return false;
			}

			global $smart_coupon_codes;

			if ( $this->is_wc_gte_30() ) {
				$coupon_id                          = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
				$is_free_shipping                   = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_free_shipping' ) ) ) ? ( ( $coupon->get_free_shipping() ) ? 'yes' : 'no' ) : '';
				$discount_type                      = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';
				$expiry_date                        = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_date_expires' ) ) ) ? $coupon->get_date_expires() : '';
				$coupon_product_ids                 = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_product_ids' ) ) ) ? $coupon->get_product_ids() : '';
				$coupon_product_categories          = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_product_categories' ) ) ) ? $coupon->get_product_categories() : '';
				$coupon_excluded_product_ids        = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_excluded_product_ids' ) ) ) ? $coupon->get_excluded_product_ids() : '';
				$coupon_excluded_product_categories = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_excluded_product_categories' ) ) ) ? $coupon->get_excluded_product_categories() : '';
				$coupon_minimum_amount              = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_minimum_amount' ) ) ) ? $coupon->get_minimum_amount() : '';
				$coupon_maximum_amount              = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_maximum_amount' ) ) ) ? $coupon->get_maximum_amount() : '';
				$coupon_usage_limit                 = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_usage_limit' ) ) ) ? $coupon->get_usage_limit() : '';
				$coupon_usage_limit_per_user        = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_usage_limit_per_user' ) ) ) ? $coupon->get_usage_limit_per_user() : '';
				$coupon_limit_usage_to_x_items      = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_limit_usage_to_x_items' ) ) ) ? $coupon->get_limit_usage_to_x_items() : '';
				$is_exclude_sale_items              = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_exclude_sale_items' ) ) ) ? ( ( $coupon->get_exclude_sale_items() ) ? 'yes' : 'no' ) : '';
				$is_individual_use                  = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_individual_use' ) ) ) ? ( ( $coupon->get_individual_use() ) ? 'yes' : 'no' ) : '';
			} else {
				$coupon_id                          = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
				$is_free_shipping                   = ( ! empty( $coupon->free_shipping ) ) ? $coupon->free_shipping : '';
				$discount_type                      = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
				$expiry_date                        = ( ! empty( $coupon->expiry_date ) ) ? $coupon->expiry_date : '';
				$coupon_product_ids                 = ( ! empty( $coupon->product_ids ) ) ? $coupon->product_ids : '';
				$coupon_product_categories          = ( ! empty( $coupon->product_categories ) ) ? $coupon->product_categories : '';
				$coupon_excluded_product_ids        = ( ! empty( $coupon->exclude_product_ids ) ) ? $coupon->exclude_product_ids : '';
				$coupon_excluded_product_categories = ( ! empty( $coupon->exclude_product_categories ) ) ? $coupon->exclude_product_categories : '';
				$coupon_minimum_amount              = ( ! empty( $coupon->minimum_amount ) ) ? $coupon->minimum_amount : '';
				$coupon_maximum_amount              = ( ! empty( $coupon->maximum_amount ) ) ? $coupon->maximum_amount : '';
				$coupon_usage_limit                 = ( ! empty( $coupon->usage_limit ) ) ? $coupon->usage_limit : '';
				$coupon_usage_limit_per_user        = ( ! empty( $coupon->usage_limit_per_user ) ) ? $coupon->usage_limit_per_user : '';
				$coupon_limit_usage_to_x_items      = ( ! empty( $coupon->limit_usage_to_x_items ) ) ? $coupon->limit_usage_to_x_items : '';
				$is_exclude_sale_items              = ( ! empty( $coupon->exclude_sale_items ) ) ? $coupon->exclude_sale_items : '';
				$is_individual_use                  = ( ! empty( $coupon->individual_use ) ) ? $coupon->individual_use : '';
			}

			if ( ! is_array( $email ) ) {
				$emails = array( $email => 1 );
			} else {
				$temp_email = get_post_meta( $order_id, 'temp_gift_card_receivers_emails', true );
				if ( ! empty( $temp_email ) && count( $temp_email ) > 0 ) {
					$email = $temp_email;
				}
				$emails = ( ! empty( $coupon_id ) ) ? array_count_values( $email[ $coupon_id ] ) : array();
			}

			if ( ! empty( $order_id ) ) {
				$receivers_messages = get_post_meta( $order_id, 'gift_receiver_message', true );
			}

			foreach ( $emails as $email_id => $qty ) {

				if ( $this->is_credit_sent( $email_id, $coupon ) ) {
					continue;
				}

				$smart_coupon_code = $this->generate_unique_code( $email_id, $coupon );

				$coupon_post = ( ! empty( $coupon_id ) ) ? get_post( $coupon_id ) : new stdClass();

				$smart_coupon_args = array(
					'post_title'   => strtolower( $smart_coupon_code ),
					'post_excerpt' => ( ! empty( $coupon_post->post_excerpt ) ) ? $coupon_post->post_excerpt : '',
					'post_content' => '',
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_type'    => 'shop_coupon',
				);

				$smart_coupon_id = wp_insert_post( $smart_coupon_args );

				$type                   = ( ! empty( $discount_type ) ) ? $discount_type : 'smart_coupon';
				$individual_use         = ( ! empty( $is_individual_use ) ) ? $is_individual_use : 'no';
				$minimum_amount         = ( ! empty( $coupon_minimum_amount ) ) ? $coupon_minimum_amount : '';
				$maximum_amount         = ( ! empty( $coupon_maximum_amount ) ) ? $coupon_maximum_amount : '';
				$product_ids            = ( ! empty( $coupon_product_ids ) ) ? implode( ',', $coupon_product_ids ) : '';
				$exclude_product_ids    = ( ! empty( $coupon_excluded_product_ids ) ) ? implode( ',', $coupon_excluded_product_ids ) : '';
				$usage_limit            = ( ! empty( $coupon_usage_limit ) ) ? $coupon_usage_limit : '';
				$usage_limit_per_user   = ( ! empty( $coupon_usage_limit_per_user ) ) ? $coupon_usage_limit_per_user : '';
				$limit_usage_to_x_items = ( ! empty( $coupon_limit_usage_to_x_items ) ) ? $coupon_limit_usage_to_x_items : '';
				$sc_coupon_validity     = ( ! empty( $coupon_id ) ) ? get_post_meta( $coupon_id, 'sc_coupon_validity', true ) : '';

				if ( $this->is_wc_gte_30() && $expiry_date instanceof WC_DateTime ) {
					$expiry_date = $expiry_date->getTimestamp();
				} elseif ( ! is_int( $expiry_date ) ) {
					$expiry_date = strtotime( $expiry_date );
				}

				if ( ! empty( $coupon_id ) && ! empty( $sc_coupon_validity ) ) {
					$is_parent_coupon_expired = ( ! empty( $expiry_date ) && ( $expiry_date < time() ) ) ? true : false;
					if ( ! $is_parent_coupon_expired ) {
						$validity_suffix = get_post_meta( $coupon_id, 'validity_suffix', true );
						$expiry_date     = strtotime( "+$sc_coupon_validity $validity_suffix" );
					}
				}

				$expiry_date                = ( ! empty( $expiry_date ) ) ? date( 'Y-m-d', intval( $expiry_date ) ) : '';
				$free_shipping              = ( ! empty( $is_free_shipping ) ) ? $is_free_shipping : 'no';
				$product_categories         = ( ! empty( $coupon_product_categories ) ) ? $coupon_product_categories : array();
				$exclude_product_categories = ( ! empty( $coupon_excluded_product_categories ) ) ? $coupon_excluded_product_categories : array();

				update_post_meta( $smart_coupon_id, 'discount_type', $type );
				update_post_meta( $smart_coupon_id, 'coupon_amount', $amount );
				update_post_meta( $smart_coupon_id, 'individual_use', $individual_use );
				update_post_meta( $smart_coupon_id, 'minimum_amount', $minimum_amount );
				update_post_meta( $smart_coupon_id, 'maximum_amount', $maximum_amount );
				update_post_meta( $smart_coupon_id, 'product_ids', $product_ids );
				update_post_meta( $smart_coupon_id, 'exclude_product_ids', $exclude_product_ids );
				update_post_meta( $smart_coupon_id, 'usage_limit', $usage_limit );
				update_post_meta( $smart_coupon_id, 'usage_limit_per_user', $usage_limit_per_user );
				update_post_meta( $smart_coupon_id, 'limit_usage_to_x_items', $limit_usage_to_x_items );
				update_post_meta( $smart_coupon_id, 'expiry_date', $expiry_date );

				$is_disable_email_restriction = ( ! empty( $coupon_id ) ) ? get_post_meta( $coupon_id, 'sc_disable_email_restriction', true ) : '';
				if ( empty( $is_disable_email_restriction ) || 'no' === $is_disable_email_restriction ) {
					update_post_meta( $smart_coupon_id, 'customer_email', array( $email_id ) );
				}

				if ( ! $this->is_wc_gte_30() ) {
					$apply_before_tax = ( ! empty( $coupon->apply_before_tax ) ) ? $coupon->apply_before_tax : 'no';
					update_post_meta( $smart_coupon_id, 'apply_before_tax', $apply_before_tax );
				}

				update_post_meta( $smart_coupon_id, 'free_shipping', $free_shipping );
				update_post_meta( $smart_coupon_id, 'product_categories', $product_categories );
				update_post_meta( $smart_coupon_id, 'exclude_product_categories', $exclude_product_categories );
				update_post_meta( $smart_coupon_id, 'exclude_sale_items', $is_exclude_sale_items );
				update_post_meta( $smart_coupon_id, 'generated_from_order_id', $order_id );

				$sc_restrict_to_new_user = get_post_meta( $coupon_id, 'sc_restrict_to_new_user', true );
				update_post_meta( $smart_coupon_id, 'sc_restrict_to_new_user', $sc_restrict_to_new_user );

				/**
				 * Hook for 3rd party developers to add data in generated coupon
				 *
				 * New coupon id    new_coupon_id Newly generated coupon post id
				 * Reference coupon ref_coupon This is the coupon from which meta will be copied to newly created coupon
				 */
				do_action(
					'wc_sc_new_coupon_generated', array(
						'new_coupon_id' => $smart_coupon_id,
						'ref_coupon'    => $coupon,
					)
				);

				$generated_coupon_details = array(
					'parent' => ( ! empty( $coupon_id ) ) ? $coupon_id : 0,
					'code'   => $smart_coupon_code,
					'amount' => $amount,
				);

				$smart_coupon_codes[ $email_id ][] = $generated_coupon_details;

				if ( ! empty( $order_id ) ) {
					$is_gift = get_post_meta( $order_id, 'is_gift', true );
				} else {
					$is_gift = 'no';
				}

				if ( is_array( $email ) && ! empty( $coupon_id ) && isset( $email[ $coupon_id ] ) ) {
					$message_index = array_search( $email_id, $email[ $coupon_id ], true );
					if ( false !== $message_index && isset( $receivers_messages[ $coupon_id ][ $message_index ] ) && ! empty( $receivers_messages[ $coupon_id ][ $message_index ] ) ) {
						$message_from_sender = $receivers_messages[ $coupon_id ][ $message_index ];
						unset( $email[ $coupon_id ][ $message_index ] );
						update_post_meta( $order_id, 'temp_gift_card_receivers_emails', $email );
					}
				}
				$this->sa_email_coupon( array( $email_id => $generated_coupon_details ), $type, $order_id, $gift_certificate_receiver_name, $message_from_sender, $gift_certificate_sender_name, $gift_certificate_sender_email, $is_gift );

			}

			return $smart_coupon_codes;

		}

		/**
		 * Function to set that Smart Coupons plugin is used to auto generate a coupon
		 *
		 * @param array $args Data.
		 */
		public function smart_coupons_plugin_used( $args = array() ) {

			$is_show_review_notice = get_option( 'wc_sc_is_show_review_notice' );

			if ( false === $is_show_review_notice ) {
				update_option( 'wc_sc_is_show_review_notice', time() );
			}

		}

		/**
		 * Funtion to show search result based on email id included in customer email
		 *
		 * @param object $wp WP object.
		 */
		public function woocommerce_admin_coupon_search( $wp ) {
			global $pagenow, $wpdb;

			if ( 'edit.php' !== $pagenow ) {
				return;
			}
			if ( ! isset( $wp->query_vars['s'] ) ) {
				return;
			}
			if ( 'shop_coupon' !== $wp->query_vars['post_type'] ) {
				return;
			}

			$e = substr( $wp->query_vars['s'], 0, 6 );

			if ( 'Email:' === substr( $wp->query_vars['s'], 0, 6 ) ) {

				$email = trim( substr( $wp->query_vars['s'], 6 ) );

				if ( ! $email ) {
					return;
				}

				$post_ids = wp_cache_get( 'wc_sc_search_customer_coupon_ids_for_' . sanitize_key( $email ), 'woocommerce_smart_coupons' );

				if ( false === $post_ids ) {
					$post_ids = $wpdb->get_col( // phpcs:ignore
						$wpdb->prepare(
							"SELECT post_id
								FROM {$wpdb->postmeta}
								WHERE meta_key = %s
									AND meta_value LIKE %s;",
							'customer_email',
							'%' . $wpdb->esc_like( $email ) . '%'
						)
					);
					wp_cache_set( 'wc_sc_search_customer_coupon_ids_for_' . sanitize_key( $email ), $post_ids, 'woocommerce_smart_coupons' );
					$this->maybe_add_cache_key( 'wc_sc_order_for_user_id_' . implode( '_', $unique_user_ids ) );
				}

				if ( ! $post_ids ) {
					return;
				}

				unset( $wp->query_vars['s'] );

				$wp->query_vars['post__in'] = $post_ids;

				$wp->query_vars['email'] = $email;
			}

		}

		/**
		 * Function to show label of the search result on email
		 *
		 * @param string $query The query.
		 * @return string $query
		 */
		public function woocommerce_admin_coupon_search_label( $query ) {
				global $pagenow, $typenow, $wp;

			if ( 'edit.php' !== $pagenow ) {
				return $query;
			}
			if ( 'shop_coupon' !== $typenow ) {
				return $query;
			}

			$s = get_query_var( 's' );
			if ( $s ) {
				return $query;
			}

			$email = get_query_var( 'email' );

			if ( $email ) {

				$post_type = get_post_type_object( $wp->query_vars['post_type'] );
				/* translators: 1: Coupon 2: Search text */
				return sprintf( __( '[%1$s with email: %2$s]', 'woocommerce-smart-coupons' ), $post_type->labels->singular_name, $email );
			}

			return $query;
		}

		/**
		 * Add button to export coupons on Coupons admin page
		 */
		public function woocommerce_restrict_manage_smart_coupons() {
			global $typenow, $wp_query, $wp, $woocommerce_smart_coupon;

			if ( 'shop_coupon' !== $typenow ) {
				return;
			}

			?>
				<style type="text/css">
					button#export_coupons {
						padding: 0px 5px;
					}
					button#export_coupons > span.dashicons {
						transform: translateY(15%);
					}
				</style>
				<div class="alignright" style="margin-top: 1px;" >
					<?php
					if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
						echo '<input type="hidden" name="sc_export_query_args" value="' . esc_attr( wc_clean( wp_unslash( $_SERVER['QUERY_STRING'] ) ) ) . '">'; // phpcs:ignore
					}
					?>
					<button type="submit" class="button" id="export_coupons" name="export_coupons" value="<?php echo esc_attr__( 'Export', 'woocommerce-smart-coupons' ); ?>"><span class="dashicons dashicons-upload"></span><?php echo esc_html__( 'Export', 'woocommerce-smart-coupons' ); ?></button>
				</div>
			<?php
		}

		/**
		 * Export coupons
		 */
		public function woocommerce_export_coupons() {
			global $typenow, $wp_query,$wp,$post;

			if ( is_admin() && isset( $_GET['export_coupons'] ) && current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore

				$args = array(
					'post_status'    => '',
					'post_type'      => '',
					'm'              => '',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);

				if ( ! empty( $_REQUEST['sc_export_query_args'] ) ) { // phpcs:ignore
					parse_str( wc_clean( wp_unslash( $_REQUEST['sc_export_query_args'] ) ), $sc_args ); // phpcs:ignore
				}
				$args = array_merge( $args, $sc_args );

				$get_coupon_type = ( ! empty( $_GET['coupon_type'] ) ) ? wc_clean( wp_unslash( $_GET['coupon_type'] ) ) : ''; // phpcs:ignore
				$get_post        = ( ! empty( $_GET['post'] ) ) ? wc_clean( wp_unslash( $_GET['post'] ) ) : ''; // phpcs:ignore

				if ( isset( $get_coupon_type ) && '' !== $get_coupon_type ) {
					$args['meta_query'] = array( // phpcs:ignore
						array(
							'key'   => 'discount_type',
							'value' => $get_coupon_type,
						),
					);
				}

				if ( ! empty( $get_post ) ) {
					$args['post__in'] = $get_post;
				}

				foreach ( $args as $key => $value ) {
					if ( array_key_exists( $key, wc_clean( wp_unslash( $_GET ) ) ) ) { // phpcs:ignore
						$args[ $key ] = wc_clean( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore
					}
				}

				if ( 'all' === $args['post_status'] ) {
					$args['post_status'] = array( 'publish', 'draft', 'pending', 'private', 'future' );

				}

				$query = new WP_Query( $args );

				$post_ids = $query->posts;

				$this->export_coupon( '', wc_clean( wp_unslash( $_GET ) ), $post_ids ); // phpcs:ignore
			}
		}

		/**
		 * Generate coupon code
		 *
		 * @param array $post POST.
		 * @param array $get GET.
		 * @param array $post_ids Post ids.
		 * @param array $coupon_postmeta_headers Coupon postmeta headers.
		 * @return array $data associative array of generated coupon
		 */
		public function generate_coupons_code( $post = array(), $get = array(), $post_ids = array(), $coupon_postmeta_headers = array() ) {
			global $wpdb, $wp, $wp_query;

			$data = array();
			if ( ! empty( $post ) && isset( $post['generate_and_import'] ) ) {

				$customer_emails = array();
				$unique_code     = '';
				if ( ! empty( $post['customer_email'] ) ) {
					$emails = explode( ',', $post['customer_email'] );
					if ( is_array( $emails ) && count( $emails ) > 0 ) {
						for ( $j = 1; $j <= $post['no_of_coupons_to_generate']; $j++ ) {
							$customer_emails[ $j ] = ( isset( $emails[ $j - 1 ] ) && is_email( $emails[ $j - 1 ] ) ) ? $emails[ $j - 1 ] : '';
						}
					}
				}

				$all_discount_types = wc_get_coupon_types();
				$generated_codes    = array();

				for ( $i = 1; $i <= $post['no_of_coupons_to_generate']; $i++ ) {
					$customer_email = ( ! empty( $customer_emails[ $i ] ) ) ? $customer_emails[ $i ] : '';
					$unique_code    = $this->generate_unique_code( $customer_email );
					if ( ! empty( $generated_codes ) && in_array( $unique_code, $generated_codes, true ) ) {
						$max = ( $post['no_of_coupons_to_generate'] * 10 ) - 1;
						do {
							$unique_code_temp = $unique_code . wp_rand( 0, $max );
						} while ( in_array( $unique_code_temp, $generated_codes, true ) );
						$unique_code = $unique_code_temp;
					}
					$generated_codes[] = $unique_code;
					$code              = $post['coupon_title_prefix'] . $unique_code . $post['coupon_title_suffix'];

					$data[ $i ]['post_title'] = strtolower( $code );

					$discount_type = ( ! empty( $post['discount_type'] ) ) ? $post['discount_type'] : 'percent';

					if ( ! empty( $all_discount_types[ $discount_type ] ) ) {
						$data[ $i ]['discount_type'] = $all_discount_types[ $discount_type ];
					} else {
						if ( $this->is_wc_gte_30() ) {
							$data[ $i ]['discount_type'] = 'Percentage discount';
						} else {
							$data[ $i ]['discount_type'] = 'Cart % Discount';
						}
					}

					if ( $this->is_wc_gte_30() ) {
						$post['product_ids']         = ( ! empty( $post['product_ids'] ) ) ? ( ( is_array( $post['product_ids'] ) ) ? implode( ',', $post['product_ids'] ) : $post['product_ids'] ) : '';
						$post['exclude_product_ids'] = ( ! empty( $post['exclude_product_ids'] ) ) ? ( ( is_array( $post['exclude_product_ids'] ) ) ? implode( ',', $post['exclude_product_ids'] ) : $post['exclude_product_ids'] ) : '';
					}

					$data[ $i ]['coupon_amount']          = $post['coupon_amount'];
					$data[ $i ]['individual_use']         = ( isset( $post['individual_use'] ) ) ? 'yes' : 'no';
					$data[ $i ]['product_ids']            = ( isset( $post['product_ids'] ) ) ? str_replace( array( ',', ' ' ), array( '|', '' ), $post['product_ids'] ) : '';
					$data[ $i ]['exclude_product_ids']    = ( isset( $post['exclude_product_ids'] ) ) ? str_replace( array( ',', ' ' ), array( '|', '' ), $post['exclude_product_ids'] ) : '';
					$data[ $i ]['usage_limit']            = ( isset( $post['usage_limit'] ) ) ? $post['usage_limit'] : '';
					$data[ $i ]['usage_limit_per_user']   = ( isset( $post['usage_limit_per_user'] ) ) ? $post['usage_limit_per_user'] : '';
					$data[ $i ]['limit_usage_to_x_items'] = ( isset( $post['limit_usage_to_x_items'] ) ) ? $post['limit_usage_to_x_items'] : '';
					if ( empty( $post['expiry_date'] ) && ! empty( $post['sc_coupon_validity'] ) && ! empty( $post['validity_suffix'] ) ) {
						$data[ $i ]['expiry_date'] = date( 'Y-m-d', strtotime( '+' . $post['sc_coupon_validity'] . ' ' . $post['validity_suffix'] ) );
					} else {
						$data[ $i ]['expiry_date'] = $post['expiry_date'];
					}
					$data[ $i ]['free_shipping']                = ( isset( $post['free_shipping'] ) ) ? 'yes' : 'no';
					$data[ $i ]['product_categories']           = ( isset( $post['product_categories'] ) ) ? implode( '|', $post['product_categories'] ) : '';
					$data[ $i ]['exclude_product_categories']   = ( isset( $post['exclude_product_categories'] ) ) ? implode( '|', $post['exclude_product_categories'] ) : '';
					$data[ $i ]['exclude_sale_items']           = ( isset( $post['exclude_sale_items'] ) ) ? 'yes' : 'no';
					$data[ $i ]['minimum_amount']               = ( isset( $post['minimum_amount'] ) ) ? $post['minimum_amount'] : '';
					$data[ $i ]['maximum_amount']               = ( isset( $post['maximum_amount'] ) ) ? $post['maximum_amount'] : '';
					$data[ $i ]['customer_email']               = ( ! empty( $customer_emails ) ) ? $customer_emails[ $i ] : '';
					$data[ $i ]['sc_coupon_validity']           = ( isset( $post['sc_coupon_validity'] ) ) ? $post['sc_coupon_validity'] : '';
					$data[ $i ]['validity_suffix']              = ( isset( $post['validity_suffix'] ) ) ? $post['validity_suffix'] : '';
					$data[ $i ]['is_pick_price_of_product']     = ( isset( $post['is_pick_price_of_product'] ) ) ? 'yes' : 'no';
					$data[ $i ]['sc_disable_email_restriction'] = ( isset( $post['sc_disable_email_restriction'] ) ) ? 'yes' : 'no';
					$data[ $i ]['sc_is_visible_storewide']      = ( isset( $post['sc_is_visible_storewide'] ) ) ? 'yes' : 'no';
					$data[ $i ]['coupon_title_prefix']          = ( isset( $post['coupon_title_prefix'] ) ) ? $post['coupon_title_prefix'] : '';
					$data[ $i ]['coupon_title_suffix']          = ( isset( $post['coupon_title_suffix'] ) ) ? $post['coupon_title_suffix'] : '';
					$data[ $i ]['sc_restrict_to_new_user']      = ( isset( $post['sc_restrict_to_new_user'] ) ) ? $post['sc_restrict_to_new_user'] : '';
					$data[ $i ]['post_status']                  = 'publish';

					$data[ $i ] = apply_filters( 'sc_generate_coupon_meta', $data[ $i ], $post );

				}
			}

			if ( ! empty( $get ) && isset( $get['export_coupons'] ) ) {

				$headers            = array_keys( $coupon_postmeta_headers );
				$how_many_headers   = count( $headers );
				$header_placeholder = array_fill( 0, $how_many_headers, '%s' );

				$how_many_ids   = count( $post_ids );
				$id_placeholder = array_fill( 0, $how_many_ids, '%d' );

				$wpdb->query( $wpdb->prepare( 'SET SESSION group_concat_max_len=%d', 999999 ) ); // phpcs:ignore

				$unique_post_ids = array_unique( $post_ids );

				$results = wp_cache_get( 'wc_sc_exported_coupon_data_' . implode( '_', $unique_post_ids ), 'woocommerce_smart_coupons' );

				if ( false === $results ) {
					$results = $wpdb->get_results( // phpcs:ignore
						// phpcs:disable
						$wpdb->prepare(
							"SELECT p.ID,
									p.post_title,
									p.post_excerpt,
									p.post_status,
									p.post_parent,
									p.menu_order,
									DATE_FORMAT(p.post_date,'%%d-%%m-%%Y %%h:%%i') AS post_date,
									GROUP_CONCAT(pm.meta_key order by pm.meta_id SEPARATOR '###') AS coupon_meta_key,
									GROUP_CONCAT(pm.meta_value order by pm.meta_id SEPARATOR '###') AS coupon_meta_value
								FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as pm ON (p.ID = pm.post_id
									AND pm.meta_key IN (" . implode( ',', $header_placeholder ) . ') )
								WHERE p.ID IN (' . implode( ',', $id_placeholder ) . ')
								GROUP BY p.id  ORDER BY p.id',
							array_merge( $headers, $post_ids )
						),
						// phpcs:enable
						ARRAY_A
					);
					wp_cache_set( 'wc_sc_exported_coupon_data_' . implode( '_', $unique_post_ids ), $results, 'woocommerce_smart_coupons' );
					$this->maybe_add_cache_key( 'wc_sc_exported_coupon_data_' . implode( '_', $unique_post_ids ) );
				}

				foreach ( $results as $result ) {

					$coupon_meta_key   = explode( '###', $result['coupon_meta_key'] );
					$coupon_meta_value = explode( '###', $result['coupon_meta_value'] );

					unset( $result['coupon_meta_key'] );
					unset( $result['coupon_meta_value'] );

					$id          = $result['ID'];
					$data[ $id ] = $result;

					foreach ( $coupon_meta_key as $index => $key ) {
						if ( 'product_ids' === $key || 'exclude_product_ids' === $key ) {
							$data[ $id ][ $key ] = ( isset( $coupon_meta_value[ $index ] ) ) ? str_replace( array( ',', ' ' ), array( '|', '' ), $coupon_meta_value[ $index ] ) : '';
						} elseif ( 'product_categories' === $key || 'exclude_product_categories' === $key ) {
							$data[ $id ][ $key ] = ( ! empty( $coupon_meta_value[ $index ] ) ) ? implode( '|', maybe_unserialize( stripslashes( $coupon_meta_value[ $index ] ) ) ) : '';
						} elseif ( '_used_by' === $key ) {
							if ( ! isset( $data[ $id ][ $key ] ) ) {
								$data[ $id ][ $key ] = '';
							}
							$data[ $id ][ $key ] .= '|' . $coupon_meta_value[ $index ];
							$data[ $id ][ $key ]  = trim( $data[ $id ][ $key ], '|' );
						} elseif ( 'ID' !== $key ) {
							if ( is_serialized( $coupon_meta_value[ $index ] ) ) {
								$temp_data         = maybe_unserialize( stripslashes( $coupon_meta_value[ $index ] ) );
								$current_temp_data = current( $temp_data );
								if ( ! is_array( $current_temp_data ) ) {
									$temp_data = implode( ',', $temp_data );
								} else {
									$temp_data = apply_filters(
										'wc_sc_export_coupon_meta_data', $temp_data, array(
											'coupon_id'   => $id,
											'index'       => $index,
											'meta_keys'   => $coupon_meta_key,
											'meta_values' => $coupon_meta_value,
										)
									);
								}
							} else {
								$temp_data = $coupon_meta_value[ $index ];
							}
							$data[ $id ][ $key ] = $temp_data;
						}
					}
				}
			}

			return $data;

		}

		/**
		 * Export coupon CSV data
		 *
		 * @param array $columns_header Column header.
		 * @param array $data The data.
		 * @return array $file_data
		 */
		public function export_coupon_csv( $columns_header, $data ) {

			$getfield = '';

			foreach ( $columns_header as $key => $value ) {
				$getfield .= $key . ',';
			}

			$fields = substr_replace( $getfield, '', -1 );

			$each_field = array_keys( $columns_header );

			$csv_file_name = get_bloginfo( 'name' ) . gmdate( 'd-M-Y_H_i_s' ) . '.csv';

			foreach ( (array) $data as $row ) {
				$count_columns_header = count( $columns_header );
				for ( $i = 0; $i < $count_columns_header; $i++ ) {
					if ( 0 === $i ) {
						$fields .= "\n";
					}

					if ( array_key_exists( $each_field[ $i ], $row ) ) {
						$row_each_field = $row[ $each_field[ $i ] ];
					} else {
						$row_each_field = '';
					}

					$array = str_replace( array( "\n", "\n\r", "\r\n", "\r" ), "\t", $row_each_field );

					$array = str_getcsv( $array, ',', '"', '\\' );

					$str = ( $array && is_array( $array ) ) ? implode( ', ', $array ) : '';

					$str = addslashes( $str );

					$fields .= '"' . $str . '",';
				}
				$fields = substr_replace( $fields, '', -1 );
			}
			$upload_dir = wp_upload_dir();

			$file_data                  = array();
			$file_data['wp_upload_dir'] = $upload_dir['path'] . '/';
			$file_data['file_name']     = $csv_file_name;
			$file_data['file_content']  = $fields;

			return $file_data;
		}

		/**
		 * Smart Coupons export headers
		 *
		 * @param array $coupon_postmeta_headers Existing.
		 * @return array $coupon_postmeta_headers Including additional headers.
		 */
		public function wc_smart_coupons_export_headers( $coupon_postmeta_headers = array() ) {

			$sc_postmeta_headers = array(
				'sc_coupon_validity'           => __( 'Coupon Validity', 'woocommerce-smart-coupons' ),
				'validity_suffix'              => __( 'Validity Suffix', 'woocommerce-smart-coupons' ),
				'auto_generate_coupon'         => __( 'Auto Generate Coupon', 'woocommerce-smart-coupons' ),
				'coupon_title_prefix'          => __( 'Coupon Title Prefix', 'woocommerce-smart-coupons' ),
				'coupon_title_suffix'          => __( 'Coupon Title Suffix', 'woocommerce-smart-coupons' ),
				'is_pick_price_of_product'     => __( 'Is Pick Price of Product', 'woocommerce-smart-coupons' ),
				'sc_disable_email_restriction' => __( 'Disable Email Restriction', 'woocommerce-smart-coupons' ),
				'sc_is_visible_storewide'      => __( 'Coupon Is Visible Storewide', 'woocommerce-smart-coupons' ),
				'sc_restrict_to_new_user'      => __( 'For new user only?', 'woocommerce-smart-coupons' ),
			);

			return array_merge( $coupon_postmeta_headers, $sc_postmeta_headers );

		}

		/**
		 * Filter callback to replace {site_title} in email footer
		 *
		 * @param  string $string Email footer text.
		 * @return string         Email footer text with any replacements done.
		 */
		public function email_footer_replace_site_title( $string ) {
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			return str_replace( '{site_title}', $blogname, $string );
		}

		/**
		 * Write to file after exporting
		 *
		 * @param array $post POST.
		 * @param array $get GET.
		 * @param array $post_ids Post ids.
		 */
		public function export_coupon( $post = array(), $get = array(), $post_ids = array() ) {
			// Run a capability check before attempting to export coupons.
			if ( ! is_admin() && ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			$coupon_posts_headers = array(
				'post_title'   => __( 'Coupon Code', 'woocommerce-smart-coupons' ),
				'post_excerpt' => __( 'Post Excerpt', 'woocommerce-smart-coupons' ),
				'post_status'  => __( 'Post Status', 'woocommerce-smart-coupons' ),
				'post_parent'  => __( 'Post Parent', 'woocommerce-smart-coupons' ),
				'menu_order'   => __( 'Menu Order', 'woocommerce-smart-coupons' ),
				'post_date'    => __( 'Post Date', 'woocommerce-smart-coupons' ),
			);

			$coupon_postmeta_headers = apply_filters(
				'wc_smart_coupons_export_headers',
				array(
					'discount_type'              => __( 'Discount Type', 'woocommerce-smart-coupons' ),
					'coupon_amount'              => __( 'Coupon Amount', 'woocommerce-smart-coupons' ),
					'free_shipping'              => __( 'Free shipping', 'woocommerce-smart-coupons' ),
					'expiry_date'                => __( 'Expiry date', 'woocommerce-smart-coupons' ),
					'minimum_amount'             => __( 'Minimum Spend', 'woocommerce-smart-coupons' ),
					'maximum_amount'             => __( 'Maximum Spend', 'woocommerce-smart-coupons' ),
					'individual_use'             => __( 'Individual USe', 'woocommerce-smart-coupons' ),
					'exclude_sale_items'         => __( 'Exclude Sale Items', 'woocommerce-smart-coupons' ),
					'product_ids'                => __( 'Product IDs', 'woocommerce-smart-coupons' ),
					'exclude_product_ids'        => __( 'Exclude product IDs', 'woocommerce-smart-coupons' ),
					'product_categories'         => __( 'Product categories', 'woocommerce-smart-coupons' ),
					'exclude_product_categories' => __( 'Exclude Product categories', 'woocommerce-smart-coupons' ),
					'customer_email'             => __( 'Customer Email', 'woocommerce-smart-coupons' ),
					'usage_limit'                => __( 'Usage Limit', 'woocommerce-smart-coupons' ),
					'usage_limit_per_user'       => __( 'Usage Limit Per User', 'woocommerce-smart-coupons' ),
					'limit_usage_to_x_items'     => __( 'Limit Usage to X Items', 'woocommerce-smart-coupons' ),
					'usage_count'                => __( 'Usage Count', 'woocommerce-smart-coupons' ),
					'_used_by'                   => __( 'Used By', 'woocommerce-smart-coupons' ),
					'sc_restrict_to_new_user'    => __( 'For new user only?', 'woocommerce-smart-coupons' ),
				)
			);

			$column_headers = array_merge( $coupon_posts_headers, $coupon_postmeta_headers );

			if ( ! empty( $post ) ) {
				$data = $this->generate_coupons_code( $post, '', '', array() );
			} elseif ( ! empty( $get ) ) {
				$data = $this->generate_coupons_code( '', $get, $post_ids, $coupon_postmeta_headers );
			}

			$file_data = $this->export_coupon_csv( $column_headers, $data );

			if ( ( isset( $post['generate_and_import'] ) && ! empty( $post['smart_coupons_generate_action'] ) && 'sc_export_and_import' === $post['smart_coupons_generate_action'] ) || isset( $get['export_coupons'] ) ) {

				if ( ob_get_level() ) {
					$levels = ob_get_level();
					for ( $i = 0; $i < $levels; $i++ ) {
						ob_end_clean();
					}
				} else {
					ob_end_clean();
				}
				nocache_headers();
				header( 'X-Robots-Tag: noindex, nofollow', true );
				header( 'Content-Type: text/x-csv; charset=UTF-8' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $file_data['file_name'] ) . '";' );

				echo $file_data['file_content']; // phpcs:ignore
				exit;
			} else {

				// Create CSV file.
				$csv_folder  = $file_data['wp_upload_dir'];
				$filename    = str_replace( array( '\'', '"', ',', ';', '<', '>', '/', ':' ), '', $file_data['file_name'] );
				$csvfilename = $csv_folder . $filename;
				$fp          = fopen( $csvfilename, 'w' ); // phpcs:ignore
				file_put_contents( $csvfilename, $file_data['file_content'] ); // phpcs:ignore
				fclose( $fp ); // phpcs:ignore

				return $csvfilename;
			}

		}

		/**
		 * Function to enqueue additional styles & scripts for Smart Coupons
		 */
		public function smart_coupon_styles_and_scripts() {
			global $post, $pagenow, $typenow;

			if ( ! empty( $pagenow ) ) {
				$show_css_for_smart_coupon_tab = false;
				$get_post_type                 = ( ! empty( $_GET['post_type'] ) ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore
				$get_page                      = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
				if ( 'edit.php' === $pagenow && 'shop_coupon' === $get_post_type ) {
					$show_css_for_smart_coupon_tab = true;
				}
				if ( 'admin.php' === $pagenow && 'wc-smart-coupons' === $get_page ) {
					$show_css_for_smart_coupon_tab = true;
				}
				if ( $show_css_for_smart_coupon_tab ) {
					?>
					<style type="text/css">
						div#smart_coupons_tabs h2 {
							margin-bottom: 10px;
						}
					</style>
					<?php
				}
			}

			if ( ! empty( $post->post_type ) && 'product' === $post->post_type ) {
				if ( wp_script_is( 'select2' ) ) {
					wp_localize_script(
						'select2', 'smart_coupons_select_params', array(
							'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce-smart-coupons' ),
							'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce-smart-coupons' ),
							'ajax_url'                  => admin_url( 'admin-ajax.php' ),
							'search_products_nonce'     => wp_create_nonce( 'search-products' ),
							'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
						)
					);
				}
			}

		}

		/**
		 * Add if cache key doesn't exists
		 *
		 * @param  string $key The cache key.
		 */
		public function maybe_add_cache_key( $key = '' ) {
			if ( ! empty( $key ) ) {
				$all_cache_key = get_option( 'wc_sc_all_cache_key' );
				if ( false !== $all_cache_key ) {
					if ( empty( $all_cache_key ) || ! is_array( $all_cache_key ) ) {
						$all_cache_key = array();
					}
					if ( ! in_array( $key, $all_cache_key, true ) ) {
						$all_cache_key[] = $key;
						update_option( 'wc_sc_all_cache_key', $all_cache_key );
					}
				}
			}
		}

		/**
		 * Make meta data of this plugin, protected
		 *
		 * @param bool   $protected Is protected.
		 * @param string $meta_key the meta key.
		 * @param string $meta_type The meta type.
		 * @return bool $protected
		 */
		public function make_sc_meta_protected( $protected, $meta_key, $meta_type ) {
			$sc_meta = array(
				'auto_generate_coupon',
				'coupon_sent',
				'coupon_title_prefix',
				'coupon_title_suffix',
				'generated_from_order_id',
				'gift_receiver_email',
				'gift_receiver_message',
				'is_gift',
				'is_pick_price_of_product',
				'sc_called_credit_details',
				'sc_coupon_receiver_details',
				'sc_coupon_validity',
				'sc_disable_email_restriction',
				'sc_is_visible_storewide',
				'send_coupons_on_renewals',
				'smart_coupons_contribution',
				'temp_gift_card_receivers_emails',
				'validity_suffix',
				'sc_restrict_to_new_user',
			);
			if ( in_array( $meta_key, $sc_meta, true ) ) {
				return true;
			}
			return $protected;
		}

		/**
		 * Get the order from the PayPal 'Custom' variable.
		 *
		 * Credit: WooCommerce
		 *
		 * @param  string $raw_custom JSON Data passed back by PayPal.
		 * @return bool|WC_Order object
		 */
		public function get_paypal_order( $raw_custom ) {

			if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
				include_once WC()->plugin_path() . '/includes/gateways/paypal/class-wc-gateway-paypal.php';
			}
			// We have the data in the correct format, so get the order.
			if ( ( $custom = json_decode( $raw_custom ) ) && is_object( $custom ) ) { // phpcs:ignore
				$order_id  = $custom->order_id;
				$order_key = $custom->order_key;

				// Fallback to serialized data if safe. This is @deprecated in 2.3.11.
			} elseif ( preg_match( '/^a:2:{/', $raw_custom ) && ! preg_match( '/[CO]:\+?[0-9]+:"/', $raw_custom ) && ( $custom = maybe_unserialize( $raw_custom ) ) ) { // phpcs:ignore
				$order_id  = $custom[0];
				$order_key = $custom[1];

				// Nothing was found.
			} else {
				WC_Gateway_Paypal::log( 'Error: Order ID and key were not found in "custom".' );
				return false;
			}

			if ( ! $order = wc_get_order( $order_id ) ) { // phpcs:ignore
				// We have an invalid $order_id, probably because invoice_prefix has changed.
				$order_id = wc_get_order_id_by_order_key( $order_key );
				$order    = wc_get_order( $order_id );
			}

			if ( $this->is_wc_gte_30() ) {
				$_order_key = ( ! empty( $order ) && is_callable( array( $order, 'get_order_key' ) ) ) ? $order->get_order_key() : '';
			} else {
				$_order_key = ( ! empty( $order->order_key ) ) ? $order->order_key : '';
			}

			if ( ! $order || $_order_key !== $order_key ) {
				WC_Gateway_Paypal::log( 'Error: Order Keys do not match.' );
				return false;
			}

			return $order;
		}

		/**
		 * Get all coupon styles
		 *
		 * @return array
		 */
		public function get_wc_sc_coupon_styles() {

			$all_styles = array(
				'inner'        => __( 'Style 1', 'woocommerce-smart-coupons' ),
				'round-corner' => __( 'Style 2', 'woocommerce-smart-coupons' ),
				'round-dashed' => __( 'Style 3', 'woocommerce-smart-coupons' ),
				'outer-dashed' => __( 'Style 4', 'woocommerce-smart-coupons' ),
				'left'         => __( 'Style 5', 'woocommerce-smart-coupons' ),
				'bottom'       => __( 'Style 6', 'woocommerce-smart-coupons' ),
			);

			return apply_filters( 'wc_sc_get_wc_sc_coupon_styles', $all_styles );

		}

		/**
		 * Get coupon display styles
		 *
		 * @param  string $style_name The style name.
		 * @return string
		 */
		public function get_coupon_styles( $style_name = '' ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			$all_styles = $this->get_wc_sc_coupon_styles();

			ob_start();

			if ( empty( $style_name ) ) {
				foreach ( $all_styles as $style => $style_label ) {
					$file = trailingslashit( WP_PLUGIN_DIR . '/' . WC_SC_PLUGIN_DIRNAME ) . 'assets/css/wc-sc-style-' . $style . $suffix . '.css';
					if ( file_exists( $file ) ) {
						include $file;
					} else {
						/* translators: File path */
						$this->log( 'error', sprintf( __( 'File not found %s', 'woocommerce-smart-coupons' ), '<code>' . $file . '</code>' ) . ' ' . __FILE__ . ' ' . __LINE__ );
					}
				}
			} else {
				$file = trailingslashit( WP_PLUGIN_DIR . '/' . WC_SC_PLUGIN_DIRNAME ) . 'assets/css/wc-sc-style-' . $style_name . $suffix . '.css';
				if ( file_exists( $file ) ) {
					include $file;
				} else {
					/* translators: File path */
					$this->log( 'error', sprintf( __( 'File not found %s', 'woocommerce-smart-coupons' ), '<code>' . $file . '</code>' ) . ' ' . __FILE__ . ' ' . __LINE__ );
				}
			}

			$styles = ob_get_clean();

			return apply_filters( 'wc_sc_get_coupon_styles', $styles, $style_name );

		}

		/**
		 * Function to add more action on plugins page
		 *
		 * @param array $links Existing links.
		 * @return array $links
		 */
		public function plugin_action_links( $links ) {
			$action_links = array(
				'about' => '<a href="' . esc_url( admin_url( 'admin.php?page=sc-about' ) ) . '" title="' . esc_attr( __( 'Know Smart Coupons', 'woocommerce-smart-coupons' ) ) . '">' . esc_html__( 'About', 'woocommerce-smart-coupons' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * To generate unique id
		 *
		 * Credit: WooCommerce
		 */
		public function generate_unique_id() {

			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher = new PasswordHash( 8, false );
			return md5( $hasher->get_random_bytes( 32 ) );

		}

		/**
		 * To get cookie life
		 */
		public function get_cookie_life() {

			$life = get_option( 'wc_sc_coupon_cookie_life', 180 );

			return apply_filters( 'wc_sc_coupon_cookie_life', time() + ( 60 * 60 * 24 * $life ) );

		}

		/**
		 * Show notice on admin panel about minimum required version of WooCommerce
		 */
		public function needs_wc_25_above() {
			$plugin_data = self::get_smart_coupons_plugin_data();
			$plugin_name = $plugin_data['Name'];
			?>
			<div class="updated error">
				<p>
				<?php
					echo '<strong>' . esc_html__( 'Important', 'woocommerce-smart-coupons' ) . ':</strong> ' . esc_html( $plugin_name ) . ' ' . esc_html__( 'is active but it will only work with WooCommerce 2.5+.', 'woocommerce-smart-coupons' ) . ' <a href="' . esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ) ) . '" target="_blank" >' . esc_html__( 'Please update WooCommerce to the latest version', 'woocommerce-smart-coupons' ) . '</a>.';
				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Function to fetch plugin's data
		 */
		public static function get_smart_coupons_plugin_data() {
			return get_plugin_data( WC_SC_PLUGIN_FILE );
		}

		/**
		 * Function to get singular/plural name for store credit
		 */
		public static function define_label_for_store_credit() {
			global $store_credit_label;

			$store_credit_singular = get_option( 'sc_store_credit_singular_text' );
			$store_credit_plural   = get_option( 'sc_store_credit_plural_text' );

			if ( ! empty( $store_credit_singular ) && ! empty( $store_credit_plural ) ) {
				$store_credit_label = array(
					'singular' => $store_credit_singular,
					'plural'   => $store_credit_plural,
				);
			}
		}

		/**
		 * Function to get length of auto generated coupon code
		 */
		public function get_coupon_code_length() {
			$coupon_code_length = get_option( 'wc_sc_coupon_code_length' );
			return ! empty( $coupon_code_length ) ? $coupon_code_length : 13; // Default coupon code length is 13.
		}

	}//end class

} // End class exists check
