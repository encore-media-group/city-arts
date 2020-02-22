<?php
/**
 * Smart Coupons Storewide Settings
 *
 * @author      StoreApps
 * @since       3.3.0
 * @version     1.0
 * @package     WooCommerce Smart Coupons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SC_Settings' ) ) {

	/**
	 * Class for handling storewide settings for Smart Coupons
	 */
	class WC_SC_Settings {

		/**
		 * The WooCommerce settings tab name
		 *
		 * @since 3.4.0
		 * @var string
		 */
		public static $tab_slug = 'wc-smart-coupons';

		/**
		 * Variable to hold instance of WC_SC_Settings
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Array of Smart Coupons General Settings
		 *
		 * @var array
		 */
		public $sc_general_settings;

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'add_smart_coupon_admin_settings' ) );
			add_action( 'admin_init', array( $this, 'add_delete_credit_after_usage_notice' ) );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_smart_coupon_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_' . self::$tab_slug, array( $this, 'smart_coupon_settings_page' ) );
			add_action( 'woocommerce_update_options_' . self::$tab_slug, array( $this, 'save_smart_coupon_admin_settings' ) );
		}

		/**
		 * Get single instance of WC_SC_Settings
		 *
		 * @return WC_SC_Settings Singleton object of WC_SC_Settings
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
		 * Function to add setting tab for Smart Coupons
		 *
		 * @param array $settings_tabs Existing tabs.
		 * @return array
		 */
		public function add_smart_coupon_settings_tab( $settings_tabs ) {

			$settings_tabs[ self::$tab_slug ] = __( 'Smart Coupons', 'woocommerce-smart-coupons' );

			return $settings_tabs;
		}

		/**
		 * Function to add styles and script for Smart Coupons settings page
		 */
		public function sc_settings_page_styles_scripts() {

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			if ( ! wp_style_is( 'smart-coupon' ) ) {
				wp_enqueue_style( 'smart-coupon' );
			}

			?>
			<style type="text/css">
				#TB_window img#TB_Image {
					border: none !important;
				}
				.form-table th {
					width: 25% !important;
				}
				span.select2-results {
					width: 100%;
				}
				span.select2-results ul {
					overflow: hidden;
					margin: 0;
					padding: 0;
				}
				span.select2-results ul li {
					list-style: none;
					float: left;
					text-align: center;
					width: 50%;
					box-sizing: border-box;
					background-color: transparent !important; 
				}
			</style>
			<style type="text/css"><?php echo $this->get_coupon_styles(); // WPCS: XSS ok. ?></style>
			<script type="text/javascript">
				jQuery(function(){

					var wc_sc_get_coupon_html = function( style ) {
						if ( undefined === style || '' === style ) {
							var style = {
								id: jQuery('#wc_sc_setting_coupon_design').find(':selected').val(),
								text: jQuery('#wc_sc_setting_coupon_design').find(':selected').text()
							};
						}
						var coupon_html = ' <style type="text/css">\
												.coupon-container.left:before,\
												.coupon-container.bottom:before {\
													background: ' + jQuery('#wc_sc_setting_coupon_foreground_color').val() + ' !important;\
												}\
												.coupon-container.left:hover, .coupon-container.left:focus, .coupon-container.left:active,\
												.coupon-container.bottom:hover, .coupon-container.bottom:focus, .coupon-container.bottom:active {\
													color: ' + jQuery('#wc_sc_setting_coupon_background_color').val() + ' !important;\
												}\
											</style>\
											<span class="wc-sc-coupon-style-preview">\
												<div class="coupon-container ' + style.id + ' medium" style="background-color: ' + jQuery('#wc_sc_setting_coupon_background_color').val() + '; color: ' + jQuery('#wc_sc_setting_coupon_foreground_color').val() + '; border-color: ' + jQuery('#wc_sc_setting_coupon_foreground_color').val() + ';">\
													<div class="coupon-content dashed small">\
														<div class="discount-info">&nbsp;</div>\
														<div class="code">' + style.text + '</div>\
														<div class="coupon-expire">&nbsp;</div>\
													</div>\
												</div>\
											</span>';

						return coupon_html;

					};

					var wc_sc_reload_coupon_preview = function() {
						var preview = wc_sc_get_coupon_html();
						jQuery('#wc_sc_setting_coupon_background_color').parent().find('.wc-sc-coupon-preview-container').remove();
						jQuery('#wc_sc_setting_coupon_background_color').parent().append( '<span class="wc-sc-coupon-preview-container">' + preview + '</span>' );
					};

					var wc_sc_add_coupon_style_block = function( option ) {
						if ( ! option.id ) {
							return option.text;
						}
						return wc_sc_get_coupon_html( option );
					};

					var wc_sc_reload_coupon_design = function() {
						var coupon_style_element = jQuery('#wc_sc_setting_coupon_design');
						var options = coupon_style_element.data('select2').options.options;
						options.allowClear = false;
						options.escapeMarkup = function( m ) {
							return m;
						};
						options.minimumResultsForSearch = -1;
						options.templateResult = wc_sc_add_coupon_style_block;
						coupon_style_element.select2('destroy');
						coupon_style_element.select2( options );
					};

					jQuery(document).ready(function(){
						wc_sc_reload_coupon_design();
						wc_sc_reload_coupon_preview();
					});

					jQuery('#wc_sc_setting_coupon_background_color, #wc_sc_setting_coupon_foreground_color').on('change keyup irischange', function(){
						wc_sc_reload_coupon_design();
						wc_sc_reload_coupon_preview();
					});

					jQuery('#wc_sc_setting_coupon_design').on('change', function(){
						wc_sc_reload_coupon_preview();
					});

				});
			</script>
			<?php
		}

		/**
		 * Function to display Smart Coupons settings
		 */
		public function smart_coupon_settings_page() {
			add_thickbox();
			woocommerce_admin_fields( $this->sc_general_settings );
			wp_nonce_field( 'wc_smart_coupons_settings', 'sc_security', false );
			$this->sc_settings_page_styles_scripts();
		}

		/**
		 * Function to add smart coupons admin settings
		 */
		public function add_smart_coupon_admin_settings() {
			$this->sc_general_settings = array(
				array(
					'title' => __( 'Smart Coupons Settings', 'woocommerce-smart-coupons' ),
					'type'  => 'title',
					'desc'  => __( 'Set up Smart Coupons the way you like. Use these options to configure/change the way Smart Coupons works.', 'woocommerce-smart-coupons' ),
					'id'    => 'sc_display_coupon_settings',
				),
				array(
					'name'              => __( 'Coupon design', 'woocommerce-smart-coupons' ),
					'desc'              => __( 'Choose a design of the coupon', 'woocommerce-smart-coupons' ),
					'id'                => 'wc_sc_setting_coupon_design',
					'default'           => 'round-dashed',
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'css'               => 'min-width: 350px;',
					'desc_tip'          => true,
					'options'           => $this->get_wc_sc_coupon_styles(),
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select a coupon design&hellip;', 'woocommerce-smart-coupons' ),
					),
				),
				array(
					'name'        => '&nbsp;',
					'desc'        => __( 'Choose the background color for the coupon', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Choose a background color&helip;', 'woocommerce-smart-coupons' ),
					'id'          => 'wc_sc_setting_coupon_background_color',
					'type'        => 'color',
					'desc_tip'    => true,
					'css'         => 'width:6em;',
					'default'     => '#39cccc',
				),
				array(
					'name'        => '&nbsp;',
					'desc'        => __( 'Choose a color for the texts & border of the coupon', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Choose a text color&helip;', 'woocommerce-smart-coupons' ),
					'id'          => 'wc_sc_setting_coupon_foreground_color',
					'type'        => 'color',
					'desc_tip'    => true,
					'css'         => 'width:6em;',
					'default'     => '#30050b',
				),
				array(
					'name'     => __( 'Number of coupons to show', 'woocommerce-smart-coupons' ),
					'desc'     => __( 'How many coupons (at max) should be shown on cart/checkout page?', 'woocommerce-smart-coupons' ),
					'id'       => 'wc_sc_setting_max_coupon_to_show',
					'type'     => 'number',
					'desc_tip' => true,
					'css'      => 'min-width:300px;',
				),
				array(
					'name'              => __( 'Number of characters in auto-generated coupon code', 'woocommerce-smart-coupons' ),
					'desc'              => __( 'Number of characters in auto-generated coupon code will be restricted to this number excluding prefix and/or suffix. The default length will be 13. It is recommended to keep this number between 10 to 15 to avoid coupon code duplication.', 'woocommerce-smart-coupons' ),
					'id'                => 'wc_sc_coupon_code_length',
					'type'              => 'number',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'min'  => 7,
						'step' => 1,
						'max'  => 20,
					),
					'placeholder'       => '13',
					'css'               => 'min-width:300px;',
				),
				array(
					'name'        => __( 'Subject for Coupon emails', 'woocommerce-smart-coupons' ),
					'desc'        => __( 'Subject for coupon details emails to customers', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Congratulations! You\'ve received a coupon', 'woocommerce-smart-coupons' ),
					'id'          => 'smart_coupon_email_subject',
					'type'        => 'textarea',
					'desc_tip'    => true,
					'css'         => 'min-width:300px;',
				),
				array(
					'name'          => __( 'Displaying Coupons', 'woocommerce-smart-coupons' ),
					/* translators: %s: Preview link */
					'desc'          => sprintf( __( 'Include coupon details on product\'s page, for products that issue coupons %s', 'woocommerce-smart-coupons' ), '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-associated-coupons.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>' ),
					'id'            => 'smart_coupons_is_show_associated_coupons',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'start',
				),
				array(
					/* translators: %s: Preview link */
					'desc'          => sprintf( __( 'Show coupons available to customers on their My Account > Coupons page %s', 'woocommerce-smart-coupons' ), '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-myaccount.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>' ),
					'id'            => 'woocommerce_smart_coupon_show_my_account',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'checkboxgroup' => '',
				),
				array(
					/* translators: %s: Preview link */
					'desc'          => sprintf( __( 'Include coupons received from other people on My Account > Coupons page %s', 'woocommerce-smart-coupons' ), '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-coupon-received.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>' ),
					'id'            => 'show_coupon_received_on_my_account',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => '',
				),
				array(
					/* translators: %s: Preview link */
					'desc'          => sprintf( __( 'Show invalid or used coupons in My Account > Coupons %s', 'woocommerce-smart-coupons' ), '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-invalid-used-coupons.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>' ),
					'id'            => 'smart_coupons_show_invalid_coupons_on_myaccount',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => '',
				),
				array(
					/* translators: %s: Preview link */
					'desc'          => sprintf( __( 'Display coupon description along with coupon code (on site as well as in emails) %s', 'woocommerce-smart-coupons' ), '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-coupon-description.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>' ),
					'id'            => 'smart_coupons_show_coupon_description',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'end',
				),
				array(
					'name'          => __( 'Automatic Deletion', 'woocommerce-smart-coupons' ),
					/* translators: %s: Note for admin */
					'desc'          => sprintf( __( 'Delete the store credit/gift coupon when entire credit amount is used up %s', 'woocommerce-smart-coupons' ), '<small>' . __( '(Note: It\'s recommended to keep it Disabled)', 'woocommerce-smart-coupons' ) . '</small>' ),
					'id'            => 'woocommerce_delete_smart_coupon_after_usage',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'start',
				),
				array(
					'name'          => __( 'Coupon Emails', 'woocommerce-smart-coupons' ),
					'desc'          => __( 'Email auto generated coupons to recipients', 'woocommerce-smart-coupons' ),
					'id'            => 'smart_coupons_is_send_email',
					'type'          => 'checkbox',
					'default'       => 'yes',
					'checkboxgroup' => 'start',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'sc_display_coupon_settings',
				),
				array(
					'title' => __( 'Labels', 'woocommerce-smart-coupons' ),
					'type'  => 'title',
					'desc'  => __( 'Call it something else! Use these to quickly change coupon text labels through your store. Use translations for complete control.', 'woocommerce-smart-coupons' ),
					'id'    => 'sc_setting_labels',
				),
				array(
					'name'        => __( 'Store Credit / Gift Certificate', 'woocommerce-smart-coupons' ),
					'desc'        => '<a href="https://docs.woocommerce.com/document/smart-coupons/#section-2" target="_blank"><small>' . __( '[Read More]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'sc_store_credit_singular_text',
					'type'        => 'text',
					'placeholder' => __( 'Singular name', 'woocommerce-smart-coupons' ),
					'desc_tip'    => __( 'Give alternate singular name to Store Credit / Gift Certficate.', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'id'          => 'sc_store_credit_plural_text',
					'type'        => 'text',
					'desc_tip'    => __( 'Give plural name for the above singular name.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Plural name', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'name'        => __( 'Store Credit Product CTA', 'woocommerce-smart-coupons' ),
					'desc'        => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-purchase-credit-shop-text.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'sc_gift_certificate_shop_loop_button_text',
					'type'        => 'text',
					'desc_tip'    => __( 'This is what will be shown instead of "Add to Cart" for products that sell store credits.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Select options', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'name'        => __( 'While purchasing Store Credits', 'woocommerce-smart-coupons' ),
					'desc'        => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-purchase-credit-product-page-text.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'smart_coupon_store_gift_page_text',
					'type'        => 'text',
					'desc_tip'    => __( 'When you opt to allow people to buy store credits of any amount, this label will be used.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Purchase Credit worth', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'name'        => __( '"Coupons with Product" description', 'woocommerce-smart-coupons' ),
					'desc'        => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-associated-coupon-description-front.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'smart_coupon_product_page_text',
					'type'        => 'text',
					'desc_tip'    => __( 'This is the heading above coupon details displayed on products that issue coupons.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'You will get following coupon(s) when you buy this item', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'name'        => __( 'On Cart/Checkout pages', 'woocommerce-smart-coupons' ),
					'desc'        => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-coupon-cart-checkout-title.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'smart_coupon_cart_page_text',
					'type'        => 'text',
					'desc_tip'    => __( 'This is the title for the list of available coupons, shown on Cart and Checkout pages.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Available Coupons (click on a coupon to use it)', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'name'        => __( 'My Account page', 'woocommerce-smart-coupons' ),
					'desc'        => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-myaccount-title.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'smart_coupon_myaccount_page_text',
					'type'        => 'text',
					'desc_tip'    => __( 'Title of available coupons list on My Account page.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Available Coupons & Store Credits', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'sc_setting_labels',
				),
				array(
					'title' => __( 'Coupon Receiver Details during Checkout', 'woocommerce-smart-coupons' ),
					'type'  => 'title',
					'desc'  => __( 'Buyers can send purchased coupons to anyone – right while they\'re checking out.', 'woocommerce-smart-coupons' ),
					'id'    => 'sc_coupon_receiver_settings',
				),
				array(
					'name'        => __( 'Title', 'woocommerce-smart-coupons' ),
					'desc'        => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-title-coupon-receiver-form.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'          => 'smart_coupon_gift_certificate_form_page_text',
					'type'        => 'text',
					'desc_tip'    => __( 'The title for coupon receiver details block.', 'woocommerce-smart-coupons' ),
					'placeholder' => __( 'Send Coupons to...', 'woocommerce-smart-coupons' ),
					'css'         => 'min-width:300px;',
				),
				array(
					'name'     => __( 'Description', 'woocommerce-smart-coupons' ),
					'desc'     => '<a class="thickbox" href="' . add_query_arg( array( 'TB_iframe' => 'true' ), 'https://docs.woocommerce.com/wp-content/uploads/2012/08/sc-coupon-receiver-form-description.png' ) . '"><small>' . __( '[Preview]', 'woocommerce-smart-coupons' ) . '</small></a>',
					'id'       => 'smart_coupon_gift_certificate_form_details_text',
					'type'     => 'text',
					'desc_tip' => __( 'Additional text below the title.', 'woocommerce-smart-coupons' ),
					'css'      => 'min-width:300px;',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'sc_coupon_receiver_settings',
				),
			);

			if ( $this->is_wc_gte_30() && wc_tax_enabled() ) {
				$before_tax_option[] = array(
					'name'          => __( 'Apply Before Tax', 'woocommerce-smart-coupons' ),
					'desc'          => __( 'Deduct credit/gift before doing tax calculations', 'woocommerce-smart-coupons' ),
					'id'            => 'woocommerce_smart_coupon_apply_before_tax',
					'type'          => 'checkbox',
					'default'       => 'no',
					'checkboxgroup' => 'start',

				);

				array_splice( $this->sc_general_settings, 12, 0, $before_tax_option );
			}

			$this->sc_general_settings = apply_filters( 'wc_smart_coupons_settings', $this->sc_general_settings );

		}

		/**
		 * Function for saving settings for Gift Certificate
		 */
		public function save_smart_coupon_admin_settings() {
			if ( empty( $_POST['sc_security'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sc_security'] ), 'wc_smart_coupons_settings' ) ) { // WPCS: input var ok, sanitization ok.
				return;
			}

			woocommerce_update_options( $this->sc_general_settings );
		}

		/**
		 * Function to Add Delete Credit After Usage Notice
		 */
		public function add_delete_credit_after_usage_notice() {

			$is_delete_smart_coupon_after_usage = get_option( 'woocommerce_delete_smart_coupon_after_usage' );

			if ( 'yes' !== $is_delete_smart_coupon_after_usage ) {
				return;
			}

			$admin_email = get_option( 'admin_email' );

			$user = get_user_by( 'email', $admin_email );

			$current_user_id = get_current_user_id();

			if ( ! empty( $current_user_id ) && ! empty( $user->ID ) && $current_user_id === $user->ID ) {
				add_action( 'admin_notices', array( $this, 'delete_credit_after_usage_notice' ) );
				add_action( 'admin_footer', array( $this, 'ignore_delete_credit_after_usage_notice' ) );
			}

		}

		/**
		 * Function to Delete Credit After Usage Notice
		 */
		public function delete_credit_after_usage_notice() {
			$current_user_id                   = get_current_user_id();
			$is_hide_delete_after_usage_notice = get_user_meta( $current_user_id, 'hide_delete_credit_after_usage_notice', true ); // @codingStandardsIgnoreLine
			if ( 'yes' !== $is_hide_delete_after_usage_notice ) {
				echo '<div class="error"><p>';
				if ( ! empty( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && empty( $_GET['tab'] ) ) { // WPCS: input var ok, CSRF ok.
					/* translators: 1: plugin name 2: page based text 3: Hide notice text */
					echo sprintf( esc_html__( '%1$s: %2$s to avoid issues related to missing data for store credits. %3$s', 'woocommerce-smart-coupons' ), '<strong>' . esc_html__( 'WooCommerce Smart Coupons', 'woocommerce-smart-coupons' ) . '</strong>', esc_html__( 'Uncheck', 'woocommerce-smart-coupons' ) . ' &quot;<strong>' . esc_html__( 'Delete Gift / Credit, when credit is used up', 'woocommerce-smart-coupons' ) . '</strong>&quot;', '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=wc-smart-coupons' ) ) . '">' . esc_html__( 'Setting', 'woocommerce-smart-coupons' ) . '</a>' ) . ' <button type="button" class="button" id="hide_notice_delete_credit_after_usage">' . esc_html__( 'Hide this notice', 'woocommerce-smart-coupons' ) . '</button>'; // phpcs ignore.
				} else {
					/* translators: 1: plugin name 2: page based text 3: Hide notice text */
					echo sprintf( esc_html__( '%1$s: %2$s to avoid issues related to missing data for store credits. %3$s', 'woocommerce-smart-coupons' ), '<strong>' . esc_html__( 'WooCommerce Smart Coupons', 'woocommerce-smart-coupons' ) . '</strong>', '<strong>' . esc_html__( 'Important setting', 'woocommerce-smart-coupons' ) . '</strong>', '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=wc-smart-coupons' ) ) . '">' . esc_html__( 'Setting', 'woocommerce-smart-coupons' ) . '</a>' ) . ' <button type="button" class="button" id="hide_notice_delete_credit_after_usage">' . esc_html__( 'Hide this notice', 'woocommerce-smart-coupons' ) . '</button>'; // phpcs ignore.
				}
				echo '</p></div>';
			}

		}

		/**
		 * Function to Ignore Delete Credit After Usage Notice
		 */
		public function ignore_delete_credit_after_usage_notice() {

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			?>
			<script type="text/javascript">
				jQuery(function(){
					jQuery('body').on('click', 'button#hide_notice_delete_credit_after_usage', function(){
						jQuery.ajax({
							url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
							type: 'post',
							dataType: 'json',
							data: {
								action: 'hide_notice_delete_after_usage',
								security: '<?php echo esc_html( wp_create_nonce( 'hide-smart-coupons-notice' ) ); ?>'
							},
							success: function( response ) {
								if ( response.message == 'success' ) {
									jQuery('button#hide_notice_delete_credit_after_usage').parent().parent().remove();
								}
							}
						});
					});
				});
			</script>
			<?php

		}

	}

}

WC_SC_Settings::get_instance();
