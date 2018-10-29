<?php
/**
 * Smart Coupons Admin Pages
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

if ( ! class_exists( 'WC_SC_Admin_Pages' ) ) {

	/**
	 * Class for handling admin pages of Smart Coupons
	 */
	class WC_SC_Admin_Pages {

		/**
		 * Variable to hold instance of WC_SC_Admin_Pages
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'views_edit-shop_coupon', array( $this, 'smart_coupons_views_row' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'generate_coupon_styles_and_scripts' ) );
			add_action( 'admin_notices', array( $this, 'woocommerce_show_import_message' ) );

			add_action( 'wp_ajax_wc_sc_review_notice_action', array( $this, 'wc_sc_review_notice_action' ) );
			add_action( 'wp_ajax_wc_sc_380_notice_action', array( $this, 'wc_sc_380_notice_action' ) );
			add_action( 'admin_notices', array( $this, 'show_plugin_notice' ) );

			add_action( 'admin_menu', array( $this, 'woocommerce_coupon_admin_menu' ) );
			add_action( 'admin_head', array( $this, 'woocommerce_coupon_admin_head' ) );

			add_action( 'admin_footer', array( $this, 'smart_coupons_script_in_footer' ) );
			add_action( 'admin_init', array( $this, 'woocommerce_coupon_admin_init' ) );

			add_action( 'smart_coupons_display_views', array( $this, 'smart_coupons_display_views' ) );

		}

		/**
		 * Get single instance of WC_SC_Admin_Pages
		 *
		 * @return WC_SC_Admin_Pages Singleton object of WC_SC_Admin_Pages
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
		 * Function to trigger an additional hook while creating different views
		 *
		 * @param array $views Available views.
		 * @return array $views
		 */
		public function smart_coupons_views_row( $views = null ) {

			global $typenow;

			if ( 'shop_coupon' === $typenow ) {
				do_action( 'smart_coupons_display_views' );
			}

			return $views;

		}

		/**
		 * Function to add tabs to access Smart Coupons' feature
		 */
		public function smart_coupons_display_views() {
			global $store_credit_label;

			?>
			<div id="smart_coupons_tabs">
				<h2 class="nav-tab-wrapper">
					<?php
						echo '<a href="' . esc_url( add_query_arg( array( 'post_type' => 'shop_coupon' ), admin_url( 'edit.php' ) ) ) . '" class="nav-tab nav-tab-active">' . esc_html__( 'Coupons', 'woocommerce-smart-coupons' ) . '</a>';
						echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'wc-smart-coupons' ), admin_url( 'admin.php' ) ) ) . '" class="nav-tab">' . esc_html__( 'Bulk Generate', 'woocommerce-smart-coupons' ) . '</a>';
						echo '<a href="' . esc_url(
							add_query_arg(
								array(
									'page' => 'wc-smart-coupons',
									'tab'  => 'import-smart-coupons',
								), admin_url( 'admin.php' )
							)
						) . '" class="nav-tab">' . esc_html__( 'Import Coupons', 'woocommerce-smart-coupons' ) . '</a>';
						echo '<a href="' . esc_url(
							add_query_arg(
								array(
									'page' => 'wc-smart-coupons',
									'tab'  => 'send-smart-coupons',
								), admin_url( 'admin.php' )
							)
							/* translators: %s: singular name for store credit */
						) . '" class="nav-tab">' . ( ! empty( $store_credit_label['singular'] ) ? sprintf( esc_html__( 'Send %s', 'woocommerce-smart-coupons' ), esc_html( ucwords( $store_credit_label['singular'] ) ) ) : esc_html__( 'Send Store Credit', 'woocommerce-smart-coupons' ) ) . '</a>';
					?>
				</h2>
			</div>
			<?php
		}

		/**
		 * Function to include styles & script for 'Generate Coupon' page
		 */
		public function generate_coupon_styles_and_scripts() {
			global $pagenow, $wp_scripts;
			if ( empty( $pagenow ) || 'admin.php' !== $pagenow ) {
				return;
			}

			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			if ( 'wc-smart-coupons' !== $get_page ) {
				return;
			}

			$suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

			wp_enqueue_style( 'woocommerce_admin_menu_styles', WC()->plugin_url() . '/assets/css/menu.css', array(), WC()->version );
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC()->version );
			wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version );

			$woocommerce_admin_params = array(
				/* translators: Decimal point */
				'i18n_decimal_error'               => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
				/* translators: Decimal point */
				'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
				'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
				'decimal_point'                    => $decimal,
				'mon_decimal_point'                => wc_get_price_decimal_separator(),
				'strings'                          => array(
					'import_products' => __( 'Import', 'woocommerce' ),
					'export_products' => __( 'Export', 'woocommerce' ),
				),
				'urls'                             => array(
					'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
					'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
				),
			);

			$woocommerce_admin_meta_boxes_params = array(
				'remove_item_notice'            => __( 'Are you sure you want to remove the selected items? If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'woocommerce' ),
				'i18n_select_items'             => __( 'Please select some items.', 'woocommerce' ),
				'i18n_do_refund'                => __( 'Are you sure you wish to process this refund? This action cannot be undone.', 'woocommerce' ),
				'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'woocommerce' ),
				'i18n_delete_tax'               => __( 'Are you sure you wish to delete this tax column? This action cannot be undone.', 'woocommerce' ),
				'remove_item_meta'              => __( 'Remove this item meta?', 'woocommerce' ),
				'remove_attribute'              => __( 'Remove this attribute?', 'woocommerce' ),
				'name_label'                    => __( 'Name', 'woocommerce' ),
				'remove_label'                  => __( 'Remove', 'woocommerce' ),
				'click_to_toggle'               => __( 'Click to toggle', 'woocommerce' ),
				'values_label'                  => __( 'Value(s)', 'woocommerce' ),
				'text_attribute_tip'            => __( 'Enter some text, or some attributes by pipe (|) separating values.', 'woocommerce' ),
				'visible_label'                 => __( 'Visible on the product page', 'woocommerce' ),
				'used_for_variations_label'     => __( 'Used for variations', 'woocommerce' ),
				'new_attribute_prompt'          => __( 'Enter a name for the new attribute term:', 'woocommerce' ),
				'calc_totals'                   => __( 'Calculate totals based on order items, discounts, and shipping?', 'woocommerce' ),
				'calc_line_taxes'               => __( 'Calculate line taxes? This will calculate taxes based on the customers country. If no billing/shipping is set it will use the store base country.', 'woocommerce' ),
				'copy_billing'                  => __( 'Copy billing information to shipping information? This will remove any currently entered shipping information.', 'woocommerce' ),
				'load_billing'                  => __( 'Load the customer\'s billing information? This will remove any currently entered billing information.', 'woocommerce' ),
				'load_shipping'                 => __( 'Load the customer\'s shipping information? This will remove any currently entered shipping information.', 'woocommerce' ),
				'featured_label'                => __( 'Featured', 'woocommerce' ),
				'prices_include_tax'            => esc_attr( get_option( 'woocommerce_prices_include_tax' ) ),
				'round_at_subtotal'             => esc_attr( get_option( 'woocommerce_tax_round_at_subtotal' ) ),
				'no_customer_selected'          => __( 'No customer selected', 'woocommerce' ),
				'plugin_url'                    => WC()->plugin_url(),
				'ajax_url'                      => admin_url( 'admin-ajax.php' ),
				'order_item_nonce'              => wp_create_nonce( 'order-item' ),
				'add_attribute_nonce'           => wp_create_nonce( 'add-attribute' ),
				'save_attributes_nonce'         => wp_create_nonce( 'save-attributes' ),
				'calc_totals_nonce'             => wp_create_nonce( 'calc-totals' ),
				'get_customer_details_nonce'    => wp_create_nonce( 'get-customer-details' ),
				'search_products_nonce'         => wp_create_nonce( 'search-products' ),
				'grant_access_nonce'            => wp_create_nonce( 'grant-access' ),
				'revoke_access_nonce'           => wp_create_nonce( 'revoke-access' ),
				'add_order_note_nonce'          => wp_create_nonce( 'add-order-note' ),
				'delete_order_note_nonce'       => wp_create_nonce( 'delete-order-note' ),
				'calendar_image'                => WC()->plugin_url() . '/assets/images/calendar.png',
				'post_id'                       => '',
				'base_country'                  => WC()->countries->get_base_country(),
				'currency_format_num_decimals'  => wc_get_price_decimals(),
				'currency_format_symbol'        => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
				'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
				'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS.
				'rounding_precision'            => WC_ROUNDING_PRECISION,
				'tax_rounding_mode'             => WC_TAX_ROUNDING_MODE,
				'product_types'                 => array_map(
					'sanitize_title', get_terms(
						'product_type', array(
							'hide_empty' => false,
							'fields'     => 'names',
						)
					)
				),
				'i18n_download_permission_fail' => __( 'Could not grant access - the user may already have permission for this file or billing email is not set. Ensure the billing email is set, and the order has been saved.', 'woocommerce' ),
				'i18n_permission_revoke'        => __( 'Are you sure you want to revoke access to this download?', 'woocommerce' ),
				'i18n_tax_rate_already_exists'  => __( 'You cannot add the same tax rate twice!', 'woocommerce' ),
				'i18n_product_type_alert'       => __( 'Your product has variations! Before changing the product type, it is a good idea to delete the variations to avoid errors in the stock reports.', 'woocommerce' ),
			);

			if ( ! wp_script_is( 'wc-admin-coupon-meta-boxes' ) ) {
				wp_enqueue_script( 'wc-admin-coupon-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-coupon' . $suffix . '.js', array( 'woocommerce_admin', 'wc-enhanced-select', 'wc-admin-meta-boxes' ), WC()->version, false );
				wp_localize_script( 'wc-admin-meta-boxes', 'woocommerce_admin_meta_boxes', $woocommerce_admin_meta_boxes_params );
				wp_enqueue_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC()->version, false );
				wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $woocommerce_admin_params );
			}

		}

		/**
		 * Function to show import message
		 */
		public function woocommerce_show_import_message() {
			global $pagenow,$typenow;

			$get_show_import_message = ( ! empty( $_GET['show_import_message'] ) ) ? wc_clean( wp_unslash( $_GET['show_import_message'] ) ) : ''; // phpcs:ignore
			$get_imported            = ( ! empty( $_GET['imported'] ) ) ? wc_clean( wp_unslash( $_GET['imported'] ) ) : 0; // phpcs:ignore
			$get_skipped             = ( ! empty( $_GET['skipped'] ) ) ? wc_clean( wp_unslash( $_GET['skipped'] ) ) : 0; // phpcs:ignore

			if ( empty( $get_show_import_message ) ) {
				return;
			}

			if ( 'true' === $get_show_import_message ) {
				if ( 'edit.php' === $pagenow && 'shop_coupon' === $typenow ) {

					$imported = $get_imported;
					$skipped  = $get_skipped;

					echo '<div id="message" class="updated fade"><p>' . esc_html__( 'Import complete - imported', 'woocommerce-smart-coupons' ) . ' <strong>' . esc_html( $imported ) . '</strong>, ' . esc_html__( 'skipped', 'woocommerce-smart-coupons' ) . ' <strong>' . esc_html( $skipped ) . '</strong></p></div>';
				}
			}
		}

		/**
		 * Handle Smart Coupons review notice action
		 */
		public function wc_sc_review_notice_action() {

			check_ajax_referer( 'wc-sc-review-notice-action', 'security' );

			$post_do = ( ! empty( $_POST['do'] ) ) ? wc_clean( wp_unslash( $_POST['do'] ) ) : ''; // phpcs:ignore

			$option = strtotime( '+1 month' );
			if ( 'remove' === $post_do ) {
				$option = 'no';
			}

			update_option( 'wc_sc_is_show_review_notice', $option );

			wp_send_json( array( 'success' => 'yes' ) );

		}

		/**
		 * Handle Smart Coupons version 3.8.0 notice action
		 */
		public function wc_sc_380_notice_action() {

			check_ajax_referer( 'wc-sc-380-notice-action', 'security' );

			update_option( 'wc_sc_is_show_380_notice', 'no' );

			wp_send_json( array( 'success' => 'yes' ) );

		}

		/**
		 * Show plugin review notice
		 */
		public function show_plugin_notice() {

			global $pagenow, $post;

			$valid_post_types      = array( 'shop_coupon', 'shop_order', 'product' );
			$valid_pagenow         = array( 'edit.php', 'post.php' );
			$is_show_review_notice = get_option( 'wc_sc_is_show_review_notice' );
			$is_show_380_notice    = get_option( 'wc_sc_is_show_380_notice', 'yes' );
			$is_coupon_enabled     = get_option( 'woocommerce_enable_coupons' );
			$get_post_type         = ( ! empty( $_GET['post_type'] ) ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore
			$get_page              = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$get_tab               = ( ! empty( $_GET['tab'] ) ) ? wc_clean( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
			$current_post_type     = ( ! empty( $post->post_type ) ) ? $post->post_type : '';

			$is_page = (
							( in_array( $pagenow, $valid_pagenow, true ) && in_array( $get_post_type, $valid_post_types, true ) )
							|| ( 'admin.php' === $pagenow && ( 'wc-smart-coupons' === $get_page || 'wc-smart-coupons' === $get_tab ) )
						);

			if ( $is_page && 'yes' !== $is_coupon_enabled ) {
				?>
				<div id="wc_sc_coupon_disabled" class="updated fade error">
					<p>
						<?php
						echo '<strong>' . esc_html__( 'Important', 'woocommerce-smart-coupons' ) . ':</strong> ' . esc_html__( 'Setting "Enable the use of coupon codes" is disabled.', 'woocommerce-smart-coupons' ) . ' ' . sprintf(
							'<a href="%s">%s</a>', esc_url(
								add_query_arg(
									array(
										'page' => 'wc-settings',
										'tab'  => 'general',
									), admin_url( 'admin.php' )
								)
							), esc_html__( 'Enable', 'woocommerce-smart-coupons' )
						) . ' ' . esc_html__( 'it to use', 'woocommerce-smart-coupons' ) . ' <strong>' . esc_html__( 'WooCommerce Smart Coupons', 'woocommerce-smart-coupons' ) . '</strong> ' . esc_html__( 'features.', 'woocommerce-smart-coupons' );
						?>
					</p>
				</div>
				<?php
			}

			if ( $is_page && ! empty( $is_show_review_notice ) && 'no' !== $is_show_review_notice && time() >= absint( $is_show_review_notice ) ) {
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}
				?>
				<style type="text/css" media="screen">
					#wc_sc_review_notice .wc_sc_review_notice_action {
						float: right;
						padding: 0.5em 0;
						text-align: right;
					}
				</style>
				<script type="text/javascript">
					jQuery(function(){
						jQuery('body').on('click', '#wc_sc_review_notice .wc_sc_review_notice_action a.wc_sc_review_notice_remind', function( e ){
							jQuery.ajax({
								url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
								type: 'post',
								dataType: 'json',
								data: {
									action: 'wc_sc_review_notice_action',
									security: '<?php echo esc_html( wp_create_nonce( 'wc-sc-review-notice-action' ) ); ?>',
									do: 'remind'
								},
								success: function( response ){
									if ( response.success != undefined && response.success != '' && response.success == 'yes' ) {
										jQuery('#wc_sc_review_notice').fadeOut(500, function(){ jQuery('#wc_sc_review_notice').remove(); });
									}
								}
							});
							return false;
						});
						jQuery('body').on('click', '#wc_sc_review_notice .wc_sc_review_notice_action a.wc_sc_review_notice_remove', function(){
							jQuery.ajax({
								url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
								type: 'post',
								dataType: 'json',
								data: {
									action: 'wc_sc_review_notice_action',
									security: '<?php echo esc_html( wp_create_nonce( 'wc-sc-review-notice-action' ) ); ?>',
									do: 'remove'
								},
								success: function( response ){
									if ( response.success != undefined && response.success != '' && response.success == 'yes' ) {
										jQuery('#wc_sc_review_notice').fadeOut(500, function(){ jQuery('#wc_sc_review_notice').remove(); });
									}
								}
							});
							return false;
						});
					});
				</script>
				<div id="wc_sc_review_notice" class="updated fade">
					<div class="wc_sc_review_notice_action">
						<a href="javascript:void(0)" class="wc_sc_review_notice_remind"><?php echo esc_html__( 'Remind me after a month', 'woocommerce-smart-coupons' ); ?></a><br>
						<a href="javascript:void(0)" class="wc_sc_review_notice_remove"><?php echo esc_html__( 'Never show again', 'woocommerce-smart-coupons' ); ?></a>
					</div>
					<p>
						<?php echo esc_html__( 'Awesome, you successfully auto-generated a coupon! Are you having a great experience with', 'woocommerce-smart-coupons' ) . ' <strong>' . esc_html__( 'WooCommerce Smart Coupons', 'woocommerce-smart-coupons' ) . '</strong> ' . esc_html__( 'so far?', 'woocommerce-smart-coupons' ) . '<br>' . esc_html__( 'Please consider', 'woocommerce-smart-coupons' ) . ' <a href="' . esc_url( 'https://woocommerce.com/products/smart-coupons/#comments' ) . '">' . esc_html__( 'leaving a review', 'woocommerce-smart-coupons' ) . '</a> ' . esc_html__( '! If things aren\'t going quite as expected, we\'re happy to help -- please reach out to', 'woocommerce-smart-coupons' ) . ' <a href="' . esc_url( 'https://woocommerce.com/my-account/create-a-ticket/' ) . '">' . esc_html__( 'our support team', 'woocommerce-smart-coupons' ) . '</a>.'; ?>
					</p>
				</div>
				<?php
			}

			if ( $is_page && 'yes' === $is_show_380_notice ) {
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}
				?>
				<style type="text/css" media="screen">
					#wc_sc_380_notice .wc_sc_380_notice_action {
						float: right;
						padding: 0.5em 0;
						text-align: right;
					}
					#wc_sc_380_notice .wc_sc_380_notice_action.bottom {
						margin-top: -3em;
					}
				</style>
				<script type="text/javascript">
					jQuery(function(){
						jQuery('body').on('click', '#wc_sc_380_notice .wc_sc_380_notice_action a.wc_sc_380_notice_remove', function( e ){
							jQuery.ajax({
								url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
								type: 'post',
								dataType: 'json',
								data: {
									action: 'wc_sc_380_notice_action',
									security: '<?php echo esc_html( wp_create_nonce( 'wc-sc-380-notice-action' ) ); ?>'
								},
								success: function( response ){
									if ( response.success != undefined && response.success != '' && response.success == 'yes' ) {
										jQuery('#wc_sc_380_notice').fadeOut(500, function(){ jQuery('#wc_sc_380_notice').remove(); });
									}
								}
							});
							return false;
						});
						jQuery( '#wc_sc_380_notice a.wc-sc-rating-link' ).click( function() {
							jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
						});
					});
				</script>
				<div id="wc_sc_380_notice" class="updated fade">
					<div class="wc_sc_380_notice_action">
						<a href="javascript:void(0)" class="wc_sc_380_notice_remove" title="<?php echo esc_attr__( 'Dismiss', 'woocommerce-smart-coupons' ); ?>"><?php echo esc_html__( 'I have seen these features, now hide this', 'woocommerce-smart-coupons' ); ?></a>
					</div>
					<div class="wc_sc_380_notice_content">
						<p><strong><?php echo esc_html__( '[New Features]', 'woocommerce-smart-coupons' ); ?></strong> <?php echo esc_html__( 'WooCommerce Smart Coupons', 'woocommerce-smart-coupons' ); ?></p>
						<ol>
							<li><?php echo esc_html__( 'Customizable coupons designs', 'woocommerce-smart-coupons' ); ?> &mdash; <small><i><a href="https://docs.woocommerce.com/document/smart-coupons/#section-17" target="sa_wc_smart_coupons_docs"><?php echo esc_html__( '[Know more]', 'woocommerce-smart-coupons' ); ?></a></i></small></li>
							<li><?php echo esc_html__( 'Coupon for new users only', 'woocommerce-smart-coupons' ); ?> &mdash; <small><i><a href="https://docs.woocommerce.com/document/smart-coupons/#section-18" target="sa_wc_smart_coupons_docs"><?php echo esc_html__( '[Know more]', 'woocommerce-smart-coupons' ); ?></a></i></small></li>
							<li><?php echo esc_html__( 'Coupon action - Add products with/without discount', 'woocommerce-smart-coupons' ); ?> &mdash; <small><i><a href="https://docs.woocommerce.com/document/smart-coupons/#section-19" target="sa_wc_smart_coupons_docs"><?php echo esc_html__( '[Know more]', 'woocommerce-smart-coupons' ); ?></a></i></small></li>
							<li><?php echo esc_html__( 'Label "Store Credit / Gift Certificate"', 'woocommerce-smart-coupons' ); ?> &mdash; <small><i><a href="https://docs.woocommerce.com/document/smart-coupons/#section-20" target="sa_wc_smart_coupons_docs"><?php echo esc_html__( '[Know more]', 'woocommerce-smart-coupons' ); ?></a></i></small></li>
							<li><?php echo esc_html__( 'Customizable coupon code length', 'woocommerce-smart-coupons' ); ?> &mdash; <small><i><a href="https://docs.woocommerce.com/document/smart-coupons/#section-21" target="sa_wc_smart_coupons_docs"><?php echo esc_html__( '[Know more]', 'woocommerce-smart-coupons' ); ?></a></i></small></li>
						</ol>
					</div>
					<div class="wc_sc_380_notice_action bottom">
						<?php echo esc_html__( 'Rate', 'woocommerce-smart-coupons' ); ?>&nbsp;<a href="https://woocommerce.com/products/smart-coupons/#comments" target="_blank" class="wc-sc-rating-link" data-rated="<?php echo esc_attr__( 'Thanks :)', 'woocommerce-smart-coupons' ); ?>" title="<?php echo esc_attr__( 'Rate WooCommerce Smart Coupons', 'woocommerce-smart-coupons' ); ?>">&#9733;&#9733;&#9733;&#9733;&#9733;</a>
					</div>
				</div>
				<?php
			}

		}

		/**
		 * Function to include script in admin footer
		 */
		public function smart_coupons_script_in_footer() {

			global $pagenow, $wp_scripts;
			if ( empty( $pagenow ) || 'admin.php' !== $pagenow ) {
				return;
			}
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			if ( 'wc-smart-coupons' !== $get_page ) {
				return;
			}

			?>
			<script type="text/javascript">
				jQuery(function(){
					jQuery(document).on('ready', function(){
						var element = jQuery('li#toplevel_page_woocommerce ul li').find('a[href="edit.php?post_type=shop_coupon"]');
						element.addClass('current');
						element.parent().addClass('current');
					});
				});
			</script>
			<?php

		}

		/**
		 * Funtion to register the coupon importer
		 */
		public function woocommerce_coupon_admin_init() {

			$get_import = ( isset( $_GET['import'] ) ) ? wc_clean( wp_unslash( $_GET['import'] ) ) : ''; // phpcs:ignore
			$get_page   = ( isset( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$get_action = ( isset( $_GET['action'] ) ) ? wc_clean( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore

			$post_smart_coupon_email   = ( isset( $_POST['smart_coupon_email'] ) ) ? wc_clean( wp_unslash( $_POST['smart_coupon_email'] ) ) : ''; // phpcs:ignore
			$post_smart_coupon_amount  = ( isset( $_POST['smart_coupon_amount'] ) ) ? wc_clean( wp_unslash( $_POST['smart_coupon_amount'] ) ) : 0; // phpcs:ignore
			$post_smart_coupon_message = ( isset( $_POST['smart_coupon_message'] ) ) ? wp_kses_post( wp_unslash( $_POST['smart_coupon_message'] ) ) : ''; // phpcs:ignore

			if ( 'wc-sc-coupons' === $get_import || 'wc-smart-coupons' === $get_page ) {
				ob_start();
			}

			if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
				register_importer( 'wc-sc-coupons', __( 'WooCommerce Coupons (CSV)', 'woocommerce-smart-coupons' ), __( 'Import <strong>coupons</strong> to your store via a csv file.', 'woocommerce-smart-coupons' ), array( $this, 'coupon_importer' ) );
			}

			if ( 'sent_gift_certificate' === $get_action && 'wc-smart-coupons' === $get_page ) {
				$email   = $post_smart_coupon_email;
				$amount  = $post_smart_coupon_amount;
				$message = $post_smart_coupon_message;
				$this->send_gift_certificate( $email, $amount, $message );
			}
		}

		/**
		 * Function to process & send gift certificate
		 *
		 * @param string $email Comma separated email address.
		 * @param float  $amount Coupon amount.
		 * @param string $message Optional.
		 */
		public function send_gift_certificate( $email, $amount, $message = '' ) {

			$emails = explode( ',', $email );

			foreach ( $emails as $email ) {

				$email = trim( $email );

				if ( count( $emails ) === 1 && ( ! $email || ! is_email( $email ) ) ) {

					$location = add_query_arg(
						array(
							'page'        => 'wc-smart-coupons',
							'tab'         => 'send-smart-coupons',
							'email_error' => 'yes',
						),
						admin_url( 'admin.php' )
					);

				} elseif ( count( $emails ) === 1 && ( ! $amount || ! is_numeric( $amount ) ) ) {

					$location = add_query_arg(
						array(
							'page'         => 'wc-smart-coupons',
							'tab'          => 'send-smart-coupons',
							'amount_error' => 'yes',
						),
						admin_url( 'admin.php' )
					);

				} elseif ( is_email( $email ) && is_numeric( $amount ) ) {

					$coupon_title = $this->generate_smart_coupon( $email, $amount, null, null, 'smart_coupon', null, $message );

					$location = add_query_arg(
						array(
							'page' => 'wc-smart-coupons',
							'tab'  => 'send-smart-coupons',
							'sent' => 'yes',
						),
						admin_url( 'admin.php' )
					);

				}
			}

			wp_safe_redirect( $location );
			exit;
		}

		/**
		 * Funtion to perform importing of coupon from csv file
		 */
		public function coupon_importer() {

			if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'page' => 'wc-smart-coupons',
							'tab'  => 'import-smart-coupons',
						), admin_url( 'admin.php' )
					)
				);
				exit;
			}

			// Load Importer API.
			require_once ABSPATH . 'wp-admin/includes/import.php';

			if ( ! class_exists( 'WP_Importer' ) ) {

				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

				if ( file_exists( $class_wp_importer ) ) {
					require $class_wp_importer;
				}
			}

			// includes.
			require 'class-wc-sc-coupon-import.php';
			require 'class-wc-sc-coupon-parser.php';

			$wc_csv_coupon_import = new WC_SC_Coupon_Import();

			$wc_csv_coupon_import->dispatch();

		}

		/**
		 * Function to add submenu page for Coupon CSV Import
		 */
		public function woocommerce_coupon_admin_menu() {
			add_submenu_page( 'woocommerce', __( 'Smart Coupon', 'woocommerce-smart-coupons' ), __( 'Smart Coupon', 'woocommerce-smart-coupons' ), 'manage_woocommerce', 'wc-smart-coupons', array( $this, 'admin_page' ) );
		}

		/**
		 * Function to remove submenu link for Smart Coupons
		 */
		public function woocommerce_coupon_admin_head() {
			remove_submenu_page( 'woocommerce', 'wc-smart-coupons' );
		}

		/**
		 * Funtion to show content on the Coupon CSV Importer page
		 */
		public function admin_page() {
			global $store_credit_label;

			$tab = ( ! empty( $_GET['tab'] ) ? ( $_GET['tab'] == 'send-smart-coupons' ? 'send-smart-coupons' : 'import-smart-coupons' ) : 'generate_bulk_coupons' ); // phpcs:ignore

			?>

			<div class="wrap woocommerce">
				<h2>
					<?php echo esc_html__( 'Coupons', 'woocommerce-smart-coupons' ); ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'shop_coupon' ), admin_url( 'post-new.php' ) ) ); ?>" class="add-new-h2"><?php echo esc_html__( 'Add Coupon', 'woocommerce-smart-coupons' ); ?></a>
				</h2>
				<div id="smart_coupons_tabs">
					<h2 class="nav-tab-wrapper">
						<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'shop_coupon' ), admin_url( 'edit.php' ) ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Coupons', 'woocommerce-smart-coupons' ); ?></a>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'wc-smart-coupons' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab <?php echo ( 'generate_bulk_coupons' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Bulk Generate', 'woocommerce-smart-coupons' ); ?></a>
						<?php
							$import_tab_url = add_query_arg(
								array(
									'page' => 'wc-smart-coupons',
									'tab'  => 'import-smart-coupons',
								), admin_url( 'admin.php' )
							);
						?>
						<a href="<?php echo esc_url( $import_tab_url ); ?>" class="nav-tab <?php echo ( 'import-smart-coupons' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Import Coupons', 'woocommerce-smart-coupons' ); ?></a>
						<?php
							$send_credit_tab_url = add_query_arg(
								array(
									'page' => 'wc-smart-coupons',
									'tab'  => 'send-smart-coupons',
								), admin_url( 'admin.php' )
							);
						?>
						<a href="<?php echo esc_url( $send_credit_tab_url ); ?>" class="nav-tab <?php echo ( 'send-smart-coupons' === $tab ) ? 'nav-tab-active' : ''; ?>">
							<?php
								/* translators: %s: sigular name for store credit */
								echo ( ! empty( $store_credit_label['singular'] ) ? sprintf( esc_html__( 'Send %s', 'woocommerce-smart-coupons' ), esc_html( ucwords( $store_credit_label['singular'] ) ) ) : esc_html__( 'Send Store Credit', 'woocommerce-smart-coupons' ) );
							?>
						</a>
					</h2>
				</div>
				<?php
				if ( ! function_exists( 'mb_detect_encoding' ) && 'send-smart-coupons' !== $tab ) {
					echo '<div class="message error"><p><strong>' . esc_html__( 'Required', 'woocommerce-smart-coupons' ) . ':</strong> ' . esc_html__( 'Please install and enable PHP extension', 'woocommerce-smart-coupons' ) . ' <code>mbstring</code> <a href="http://www.php.net/manual/en/mbstring.installation.php" target="_blank">' . esc_html__( 'Click here', 'woocommerce-smart-coupons' ) . '</a> ' . esc_html__( 'for more details.', 'woocommerce-smart-coupons' ) . '</p></div>';
				}

				switch ( $tab ) {
					case 'send-smart-coupons':
						$this->admin_send_certificate();
						break;
					case 'import-smart-coupons':
						$this->admin_import_page();
						break;
					default:
						$this->admin_generate_bulk_coupons_and_export();
						break;
				}
				?>

			</div>
			<?php

		}

		/**
		 * Coupon Import page content
		 */
		public function admin_import_page() {

			// Load Importer API.
			require_once ABSPATH . 'wp-admin/includes/import.php';

			if ( ! class_exists( 'WP_Importer' ) ) {

				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

				if ( file_exists( $class_wp_importer ) ) {
					require $class_wp_importer;
				}
			}

			// includes.
			require 'class-wc-sc-coupon-import.php';
			require 'class-wc-sc-coupon-parser.php';

			$coupon_importer = WC_SC_Coupon_Import::get_instance();
			$coupon_importer->dispatch();

		}

		/**
		 * Send Gift Certificate page content
		 */
		public function admin_send_certificate() {
			global $store_credit_label;

			$get_sent         = ( ! empty( $_GET['sent'] ) ) ? wc_clean( wp_unslash( $_GET['sent'] ) ) : ''; // phpcs:ignore
			$get_email_error  = ( ! empty( $_GET['email_error'] ) ) ? wc_clean( wp_unslash( $_GET['email_error'] ) ) : ''; // phpcs:ignore
			$get_amount_error = ( ! empty( $_GET['amount_error'] ) ) ? wc_clean( wp_unslash( $_GET['amount_error'] ) ) : ''; // phpcs:ignore

			if ( 'yes' === $get_sent ) {
				/* translators: %s: singular name for store credit */
				$ack_message = ! empty( $store_credit_label['singular'] ) ? sprintf( esc_html__( '%s sent successfully.', 'woocommerce-smart-coupons' ), esc_html( ucfirst( $store_credit_label['singular'] ) ) ) : esc_html__( 'Store Credit / Gift Card sent successfully.', 'woocommerce-smart-coupons' );
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $ack_message ) . '</strong></p></div>';
			}

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			$message     = '';
			$editor_args = array(
				'textarea_name' => 'smart_coupon_message',
				'textarea_rows' => 10,
				'editor_class'  => 'wp-editor-message',
				'media_buttons' => true,
				'tinymce'       => true,
			);
			$editor_id   = 'edit_smart_coupon_message';

			?>
			<style type="text/css">
				.sc-required-mark {
					color: red !important;
				}
				.sc-send-smart-coupon-container {
					margin-top: 1em;
				}
				.sc-send-smart-coupon-container form {
					padding: 0 1.5em;
				}
				.sc-preview-email-container {
					margin: 1em 0 2em;
				}
				.sc-email-content {
					padding: 1.5em;
				}
				textarea#<?php echo 'edit_smart_coupon_message'; ?> {
					width: 100%;
				}
				.sc-send-smart-coupon-container form table tbody tr td #amount {
					vertical-align: initial;
				}
			</style>

			<script type="text/javascript">
				jQuery(function(){
					var editor_id = '<?php echo esc_html( $editor_id ); ?>';
					var sc_check_decimal = function( amount ){
						var ex = /^\d*\.?(\d{1,2})?$/;
						if ( ex.test( amount ) == false ) {
							amt = amount.substring( 0, amount.length - 1 );
							return amt;
						}
						return amount;
					};
					jQuery('#sc-preview-email').on('click', function(){
						if ( ! jQuery('.sc-preview-email-container').is(':visible') ) {
							jQuery('.sc-preview-email-container').slideDown();
							jQuery('html, body').animate( { scrollTop: jQuery('#sc-preview-email').offset().top }, 'slow' );
						} else {
							jQuery('.sc-preview-email-container').slideUp();
						}
					});
					jQuery('.sc-send-smart-coupon-container #amount').on('keypress keyup change', function(){
						var el = jQuery(this);
						var amount = el.val().toString();
						var new_amount = sc_check_decimal( amount );
						if ( new_amount != amount ) {
							el.val( new_amount );
						}
					});
					jQuery('.sc-send-smart-coupon-container #amount').on('keyup change', function(){
						var price_content = jQuery('.sc-email-content h1 span.woocommerce-Price-amount.amount').contents();
						price_content[price_content.length-1].nodeValue = parseFloat(jQuery(this).val()).toFixed(2);
						var html = jQuery('.sc-email-content span.woocommerce-Price-amount.amount').html();
						jQuery('.sc-email-content span.woocommerce-Price-amount.amount').html(html);
						var price_html = '<span class="woocommerce-Price-amount amount">' + html + '</span>';
						jQuery('.sc-email-content .discount-info').html(price_html + ' <?php echo ! empty( $store_credit_label['singular'] ) ? esc_html( ucwords( $store_credit_label['singular'] ) ) : esc_html__( 'Store Credit', 'woocommerce-smart-coupons' ); ?>');
					});
					setTimeout(function(){
						var content;
						if ( jQuery('#wp-' + editor_id + '-wrap').hasClass('tmce-active') ){
							tinyMCE.activeEditor.on('change', function(ed) {
								tinyMCE.editors[ editor_id ].save();
								content = tinyMCE.editors[ editor_id ].getContent();
								jQuery('#' + editor_id).text( content ).trigger('change');
							});
						}
					},100);
					jQuery(document).on('ready', function(){
						jQuery('.sc-email-content #body_content_inner').prepend('<p class="sc-credit-message"></p>');
					});
					jQuery('#' + editor_id).on('keyup change', function(){
						var element = jQuery(this);
						var content = '';
						if ( jQuery('#wp-' + editor_id + '-wrap').hasClass('tmce-active') ){
							content = element.text();
						} else {
							content = element.val();
						}
						jQuery('.sc-email-content .sc-credit-message').html(content);
					});
				});
			</script>

			<p class="description">
			<?php
			if ( ! empty( $store_credit_label['singular'] ) ) {
				/* translators: %s: singular name for store credit */
				echo sprintf( esc_html__( 'Quickly create and email %s to one or more people.', 'woocommerce-smart-coupons' ), esc_html( strtolower( $store_credit_label['singular'] ) ) );
			} else {
				echo esc_html__( 'Quickly create and email Store Credit or Gift Card to one or more people.', 'woocommerce-smart-coupons' );
			}
			?>
			</p>

			<div class="tool-box postbox sc-send-smart-coupon-container">

				<form action="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'page'   => 'wc-smart-coupons',
							'action' => 'sent_gift_certificate',
						), admin_url( 'admin.php' )
					)
				);
				?>
								" method="post">

					<table class="form-table">
						<tr>
							<th>
								<label for="smart_coupon_email"><?php echo esc_html__( 'Send to', 'woocommerce-smart-coupons' ); ?><span class="sc-required-mark">*</span></label>
							</th>
							<td>
								<input type="text" name="smart_coupon_email" id="email" required class="input-text" style="width: 100%;" placeholder="johnsmith@example.com" />
							</td>
							<td>
								<?php
								if ( 'yes' === $get_email_error ) {
									echo '<div id="message" class="error fade"><p><strong>' . esc_html__( 'Invalid email address.', 'woocommerce-smart-coupons' ) . '</strong></p></div>';
								}
								?>
								<span class="description"><?php echo esc_html__( 'Use comma "," to separate multiple email addresses', 'woocommerce-smart-coupons' ); ?></span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="smart_coupon_amount"><?php echo esc_html__( 'Worth', 'woocommerce-smart-coupons' ); ?><span class="sc-required-mark">*</span></label>
							</th>
							<td>
								<?php
									$price_format = get_woocommerce_price_format();
									echo sprintf( $price_format, '<span class="woocommerce-Price-currencySymbol">' . esc_html( get_woocommerce_currency_symbol() ) . '</span>', '&nbsp;<input type="text" name="smart_coupon_amount" id="amount" required placeholder="' . esc_attr__( '0.00', 'woocommerce-smart-coupons' ) . '" class="input-text" style="width: 100px;" />&nbsp;' ); // phpcs:ignore
								?>
							</td>
							<td>
								<?php
								if ( 'yes' === $get_amount_error ) {
									echo '<div id="message" class="error fade"><p><strong>' . esc_html__( 'Invalid amount.', 'woocommerce-smart-coupons' ) . '</strong></p></div>';
								}
								?>
							</td>
						</tr>

						<tr>
							<th>
								<label for="smart_coupon_message"><?php echo esc_html__( 'Message', 'woocommerce-smart-coupons' ); ?> <small><?php echo esc_html__( '(optional)', 'woocommerce-smart-coupons' ); ?></small></label>
							</th>
							<td colspan="2">
								<?php wp_editor( $message, $editor_id, $editor_args ); ?>
							</td>
						</tr>

					</table>

					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__( 'Send', 'woocommerce-smart-coupons' ); ?>">
						<?php
							global $wpdb;

							$coupon_code = wp_cache_get( 'wc_sc_any_coupon_code', 'woocommerce_smart_coupons' );

						if ( false === $coupon_code ) {
							$coupon_code = $wpdb->get_var( // phpcs:ignore
								$wpdb->prepare(
									"SELECT post_title
										FROM $wpdb->posts AS p
											LEFT JOIN $wpdb->postmeta AS pm
												ON (p.ID = pm.post_id)
										WHERE post_status = %s
											AND post_type = %s
											AND ( pm.meta_key = %s AND pm.meta_value = %s )
										LIMIT 1",
									'publish',
									'shop_coupon',
									'discount_type',
									'smart_coupon'
								)
							);
							wp_cache_set( 'wc_sc_any_coupon_code', $coupon_code, 'woocommerce_smart_coupons' );
							$this->maybe_add_cache_key( 'wc_sc_any_coupon_code' );
						}

						if ( ! empty( $coupon_code ) ) {
							?>
						<input type="button" id="sc-preview-email" class="button button-secondary" value="<?php echo esc_attr__( 'Preview Email', 'woocommerce-smart-coupons' ); ?>">
						<?php } ?>
					</p>
				</form>
			</div>
			<div class="sc-preview-email-container postbox" style="display: none;">
				<div class="sc-email-content">
					<?php
					if ( ! empty( $coupon_code ) ) {
						if ( ! empty( $store_credit_label['singular'] ) ) {
							/* translators: 1: Store Credit label 2: Store Credit amount */
							$email_heading = sprintf( __( 'You have received a %1$s worth %2$s', 'woocommerce-smart-coupons' ), ucwords( $store_credit_label['singular'] ), wc_price( 0 ) );
						} else {
							/* translators: Store Credit amount */
							$email_heading = sprintf( __( 'You have received a Store Credit worth %s', 'woocommerce-smart-coupons' ), wc_price( 0 ) );
						}

						$message_from_sender = '';
						$from                = '';
						$design              = get_option( 'wc_sc_setting_coupon_design', 'round-dashed' );
						$background_color    = get_option( 'wc_sc_setting_coupon_background_color', '#39cccc' );
						$foreground_color    = get_option( 'wc_sc_setting_coupon_foreground_color', '#30050b' );
						$coupon_styles       = $this->get_coupon_styles( $design );
						ob_start();
						wc_get_template( 'emails/email-styles.php' );
						$css = ob_get_clean();
						$css = apply_filters( 'woocommerce_email_styles', $css );
						ob_start();
						echo '<style type="text/css">' . $css . '</style>'; // WPCS: XSS ok.
						include apply_filters( 'woocommerce_gift_certificates_email_template', 'templates/email.php' );
						echo ob_get_clean(); // phpcs:ignore
					}
					?>
				</div>
			</div>

			<?php
		}

		/**
		 * Form to show 'Auto generate Bulk Coupons' with other fields
		 */
		public function admin_generate_bulk_coupons_and_export() {

			global $woocommerce_smart_coupon, $post;

			$empty_reference_coupon = get_option( 'empty_reference_smart_coupons' );

			if ( false === $empty_reference_coupon ) {
				$args              = array(
					'post_status' => 'auto-draft',
					'post_type'   => 'shop_coupon',
				);
				$reference_post_id = wp_insert_post( $args );
				update_option( 'empty_reference_smart_coupons', $reference_post_id );
			} else {
				$reference_post_id = $empty_reference_coupon;
			}

			$post = get_post( $reference_post_id ); // phpcs:ignore

			if ( empty( $post ) ) {
				$args              = array(
					'post_status' => 'auto-draft',
					'post_type'   => 'shop_coupon',
				);
				$reference_post_id = wp_insert_post( $args );
				update_option( 'empty_reference_smart_coupons', $reference_post_id );
				$post = get_post( $reference_post_id ); // phpcs:ignore
			}

			if ( ! class_exists( 'WC_Meta_Box_Coupon_Data' ) ) {
				require_once WC()->plugin_path() . '/includes/admin/meta-boxes/class-wc-meta-box-coupon-data.php';
			}

			$upload_url  = wp_upload_dir();
			$upload_path = $upload_url['path'];
			$assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			$is_post_generate_and_import        = ( isset( $_POST['generate_and_import'] ) ) ? true : false; // phpcs:ignore
			$post_smart_coupons_generate_action = ( ! empty( $_POST['smart_coupons_generate_action'] ) ) ? wc_clean( wp_unslash( $_POST['smart_coupons_generate_action'] ) ) : ''; // phpcs:ignore

			if ( $is_post_generate_and_import && 'sc_export_and_import' === $post_smart_coupons_generate_action ) {

				$this->export_coupon( $_POST, '', '' ); // phpcs:ignore
			}
			?>

			<script type="text/javascript">
				jQuery(function(){

					jQuery('input#generate_and_import').on('click', function(){

						if( jQuery('input#no_of_coupons_to_generate').val() == "" ){
							jQuery("div#message").removeClass("updated fade").addClass("error fade");
							jQuery('div#message p').html( "<?php echo esc_html__( 'Please enter a valid value for Number of Coupons to Generate', 'woocommerce-smart-coupons' ); ?>");
							return false;
						} else {
							jQuery("div#message").removeClass("error fade").addClass("updated fade").hide();
							return true;
						}
					});

					var showHideBulkSmartCouponsOptions = function() {
						jQuery('input#sc_coupon_validity').parent('p').show();
						jQuery('div#for_prefix_suffix').show();
					};

					setTimeout(function(){
						showHideBulkSmartCouponsOptions();
					}, 101);

					jQuery('select#discount_type').on('change', function(){
						setTimeout(function(){
							showHideBulkSmartCouponsOptions();
						}, 101);
					});

				});
			</script>

			<div id="message"><p></p></div>
			<div class="tool-box">

				<p class="description"><?php echo esc_html__( 'Need a lot of coupons? You can easily do that with Smart Coupons.', 'woocommerce-smart-coupons' ); ?></p>

				<style type="text/css">
					.coupon_actions {
						margin-left: 14px;
					}
					#smart-coupon-action-panel p label {
						width: 30%;
					}
					#smart-coupon-action-panel {
						width: 100% !important;
					}
					.sc-required-mark {
						color: red;
					}
				</style>
				<?php
					$import_step_2_url = add_query_arg(
						array(
							'page' => 'wc-smart-coupons',
							'tab'  => 'import-smart-coupons',
							'step' => '2',
						), admin_url( 'admin.php' )
					);
				?>
				<form id="generate_coupons" action="<?php echo esc_url( $import_step_2_url ); ?>" method="post">
					<?php wp_nonce_field( 'import-woocommerce-coupon' ); ?>
					<div id="poststuff">
						<div id="woocommerce-coupon-data" class="postbox " >
							<h3><span class="coupon_actions"><?php echo esc_html__( 'Action', 'woocommerce-smart-coupons' ); ?></span></h3>
							<div class="inside">
								<div class="panel-wrap">
									<div id="smart-coupon-action-panel" class="panel woocommerce_options_panel">

										<p class="form-field">
											<label for="no_of_coupons_to_generate"><?php echo esc_html__( 'How many coupons do you want to generate?', 'woocommerce-smart-coupons' ); ?>&nbsp;<span title="<?php echo esc_attr__( 'Required', 'woocommerce-smart-coupons' ); ?>" class="sc-required-mark">*</span></label>
											<input type="number" name="no_of_coupons_to_generate" id="no_of_coupons_to_generate" placeholder="<?php echo esc_attr__( '10', 'woocommerce-smart-coupons' ); ?>" class="short" min="1" required />
										</p>

										<p class="form-field">
											<label><?php echo esc_html__( 'Generate coupons and', 'woocommerce-smart-coupons' ); ?></label>
											<input type="radio" name="smart_coupons_generate_action" value="add_to_store" id="add_to_store" checked="checked"/>&nbsp;
											<strong><?php echo esc_html__( 'Add to store', 'woocommerce-smart-coupons' ); ?></strong>
										</p>

										<p class="form-field">
											<label><?php echo '&nbsp;'; ?></label>
											<input type="radio" name="smart_coupons_generate_action" value="sc_export_and_import" id="sc_export_and_import" />&nbsp;
											<strong><?php echo esc_html__( 'Export to CSV', 'woocommerce-smart-coupons' ); ?></strong>
											<?php
												$import_tab_url = add_query_arg(
													array(
														'page' => 'wc-smart-coupons',
														'tab'  => 'import-smart-coupons',
													), admin_url( 'admin.php' )
												);
											?>
											<span class="description">
											<?php
											echo esc_html__( '(Does not add to store, but creates a .csv file, that you can', 'woocommerce-smart-coupons' ) . ' <a href="' . esc_url( $import_tab_url ) . '">' . esc_html__( 'import', 'woocommerce-smart-coupons' ) . '</a> ' . esc_html__( 'later', 'woocommerce-smart-coupons' ) . ')';
											?>
											</span>
										</p>

										<p class="form-field">
											<label><?php echo '&nbsp;'; ?></label>
											<input type="radio" name="smart_coupons_generate_action" value="woo_sc_is_email_imported_coupons" id="woo_sc_is_email_imported_coupons" />&nbsp;
											<strong><?php echo esc_html__( 'Email to recipients', 'woocommerce-smart-coupons' ); ?></strong>
											<span class="description"><?php echo esc_html__( '(Add to store and email generated coupons to recipients)', 'woocommerce-smart-coupons' ); ?></span>
										</p>

									</div>
								</div>
							</div>
						</div>
						<div id="woocommerce-coupon-data" class="postbox " >
							<h3><span class="coupon_actions"><?php echo esc_html__( 'Coupon Data', 'woocommerce-smart-coupons' ); ?></span></h3>
							<div class="inside">
								<?php WC_Meta_Box_Coupon_Data::output( $post ); ?>
							</div>
						</div>
					</div>

					<p class="submit"><input id="generate_and_import" name="generate_and_import" type="submit" class="button button-primary button-hero" value="<?php echo esc_attr__( 'Apply', 'woocommerce-smart-coupons' ); ?>" /></p>

				</form>
			</div>
			<?php

		}

	}

}

WC_SC_Admin_Pages::get_instance();
