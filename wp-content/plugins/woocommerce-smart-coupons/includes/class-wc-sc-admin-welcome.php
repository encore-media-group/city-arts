<?php
/**
 * A Welcome page for store admin
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

if ( ! class_exists( 'WC_SC_Admin_Welcome' ) ) {

	/**
	 * WC_SC_Admin_Welcome class
	 */
	class WC_SC_Admin_Welcome {

		/**
		 * Variable to hold instance of WC_SC_Admin_Welcome
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_init', array( $this, 'sc_welcome' ) );
		}

		/**
		 * Get single instance of WC_SC_Admin_Welcome
		 *
		 * @return WC_SC_Admin_Welcome Singleton object of WC_SC_Admin_Welcome
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( empty( $get_page ) ) {
				return;
			}

			$welcome_page_name  = __( 'About Smart Coupons', 'woocommerce-smart-coupons' );
			$welcome_page_title = __( 'Welcome to Smart Coupons', 'woocommerce-smart-coupons' );

			switch ( $get_page ) {
				case 'sc-about':
					add_submenu_page( 'woocommerce', $welcome_page_title, $welcome_page_name, 'manage_options', 'sc-about', array( $this, 'about_screen' ) );
					break;
				case 'sc-faqs':
					add_submenu_page( 'woocommerce', $welcome_page_title, $welcome_page_name, 'manage_options', 'sc-faqs', array( $this, 'faqs_screen' ) );
					break;
			}
		}

		/**
		 * Add styles just for this page, and remove dashboard page links.
		 */
		public function admin_head() {
			remove_submenu_page( 'woocommerce', 'sc-about' );
			remove_submenu_page( 'woocommerce', 'sc-faqs' );

			?>
			<style type="text/css">
				/*<![CDATA[*/
				.about-wrap h3 {
					margin-top: 1em;
					margin-right: 0em;
					margin-bottom: 0.1em;
					font-size: 1.25em;
					line-height: 1.3em;
				}
				.about-wrap .button-primary {
					margin-top: 18px;
				}
				.about-wrap .button-hero {
					color: #FFF!important;
					border-color: #03a025!important;
					background: #03a025 !important;
					box-shadow: 0 1px 0 #03a025;
					font-size: 1em;
					font-weight: bold;
				}
				.about-wrap .button-hero:hover {
					color: #FFF!important;
					background: #0AAB2E!important;
					border-color: #0AAB2E!important;
				}
				.about-wrap p {
					margin-top: 0.6em;
					margin-bottom: 0.8em;
					line-height: 1.6em;
					font-size: 14px;
				}
				.about-wrap .feature-section {
					padding-bottom: 5px;
				}
				/*]]>*/
			</style>
			<?php
		}

		/**
		 * Intro text/links shown on all about pages.
		 */
		private function intro() {

			if ( is_callable( 'WC_Smart_Coupons::get_smart_coupons_plugin_data' ) ) {
				$plugin_data = WC_Smart_Coupons::get_smart_coupons_plugin_data();
				$version     = $plugin_data['Version'];
			} else {
				$version = '';
			}

			?>
			<h1><?php echo esc_html__( 'Welcome to Smart Coupons', 'woocommerce-smart-coupons' ) . ' ' . esc_html( $version ); ?></h1>

			<h3><?php echo esc_html__( 'Thanks for installing! We hope you enjoy using Smart Coupons.', 'woocommerce-smart-coupons' ); ?></h3>

			<div class="feature-section col two-col" style="margin-bottom:30px!important;">
				<div class="col">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_coupon' ) ); ?>" class="button button-hero"><?php echo esc_html__( 'Go To Coupons', 'woocommerce-smart-coupons' ); ?></a>
				</div>

				<div class="col last-feature">
					<p align="right">
						<?php
							$settings_tab_url = add_query_arg(
								array(
									'page' => 'wc-settings',
									'tab'  => 'wc-smart-coupons',
								),
								admin_url( 'admin.php' )
							);
						?>
						<a href="<?php echo esc_url( $settings_tab_url ); ?>" class="button button-primary" target="_blank"><?php echo esc_html__( 'Settings', 'woocommerce-smart-coupons' ); ?></a>
						<a href="<?php echo esc_url( apply_filters( 'smart_coupons_docs_url', 'http://docs.woothemes.com/document/smart-coupons/', 'woocommerce-smart-coupons' ) ); ?>" class="docs button button-primary" target="_blank"><?php echo esc_html__( 'Docs', 'woocommerce-smart-coupons' ); ?></a>
					</p>
				</div>
			</div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab 
				<?php

				$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

				if ( 'sc-about' === $get_page ) {
					echo 'nav-tab-active';
				}
				?>
				" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'sc-about' ), 'admin.php' ) ) ); ?>">
					<?php echo esc_html__( 'Know Smart Coupons', 'woocommerce-smart-coupons' ); ?>
				</a>
				<a class="nav-tab 
				<?php
				if ( 'sc-faqs' === $get_page ) {
					echo 'nav-tab-active';
				}
				?>
				" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'sc-faqs' ), 'admin.php' ) ) ); ?>">
					<?php echo esc_html__( "FAQ's", 'woocommerce-smart-coupons' ); ?>
				</a>
			</h2>
			<?php
		}

		/**
		 * Output the about screen.
		 */
		public function about_screen() {
			?>

			<script type="text/javascript">
				jQuery(document).on('ready', function(){
					jQuery('#toplevel_page_woocommerce').find('a[href$=shop_coupon]').addClass('current');
					jQuery('#toplevel_page_woocommerce').find('a[href$=shop_coupon]').parent().addClass('current');
				});
			</script>

			<div class="wrap about-wrap" style="max-width: unset !important;">

			<?php $this->intro(); ?>

				<div>
					<div class="feature-section col three-col">
						<div class="col">
							<h4><?php echo esc_html__( 'What is Smart Coupons?', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'Smart Coupons is a WooCommerce extension, which adds a new discount type for WooCommerce Coupons. It\'s called as "Store Credit / Gift Certificate".', 'woocommerce-smart-coupons' ); ?>
								<?php echo esc_html__( 'In addition to this, it also adds more functionality in other discount types as well. Smart Coupons enables coupons to become an automatic/interactive system.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
						<div class="col">
							<h4><?php echo esc_html__( 'What is "Store Credit / Gift Certificate"?', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'This is a new discount type added by this plugin in WooCommerce Coupons. A coupon having this discount type can be called as either Smart Coupon or Store Credit or Gift Certificate. This coupon\'s amount can be called as balance.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
						<div class="col last-feature">
							<h4><?php echo esc_html__( 'What\'s new?', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'Store Credit is a unique discount type, in which coupon\'s amount keeps reducing per usage. It behaves in same way as credit, which can be used untill its amount becomes zero. Therefore this coupon\'s amount is also refered to as balance.', 'woocommerce-smart-coupons' ); ?>
							</p>
							<p>
								<?php echo esc_html__( 'Since Store Credit\'s balance keeps reducing per usage, this plugin restricts, all automatically created store credit to one user. Additionally, it also provides the setting to remove the restriction, but you should be aware of what it can cause.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
					</div>
					<center><h3><?php echo esc_html__( 'What you can achieve using Smart Coupons', 'woocommerce-smart-coupons' ); ?></h3></center>
					<div class="feature-section col three-col" >
						<div class="col">
							<h4><?php echo esc_html__( 'Sell store credit / gift certificate', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'Smart Coupons helps you configure products which can be used to sell store credit / gift certificate. You can sell store credit in 3 ways', 'woocommerce-smart-coupons' ) . ': <a href="https://docs.woothemes.com/document/smart-coupons/#section-13" target="_blank">' . esc_html__( 'fixed amount', 'woocommerce-smart-coupons' ) . '</a>, <a href="http://docs.woothemes.com/document/smart-coupons/#section-13" target="_blank">' . esc_html__( 'variable but fixed amount', 'woocommerce-smart-coupons' ) . '</a> & <a href="http://docs.woothemes.com/document/smart-coupons/#section-8" target="_blank">' . esc_html__( 'any amount', 'woocommerce-smart-coupons' ) . '</a>.'; ?>
							</p>
						</div>
						<div class="col">
							<h4><?php echo esc_html__( 'Automatically give discounts to your customer for next purchase', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'You can give a coupon to your customer after every purchase, which can encourage them to purchase again from you.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
						<div class="col last-feature">
							<h4><?php echo esc_html__( 'Bulk create unique coupons & email them', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'If you\'ve a list of email addresses of your customers who haven\'t purchase any product for a long time, you can send unique coupons to each of them in bulk.', 'woocommerce-smart-coupons' ) . ' <a href="http://docs.woothemes.com/document/smart-coupons/#section-11" target="_blank">' . esc_html__( 'See how', 'woocommerce-smart-coupons' ) . '</a>.'; ?>
							</p>
						</div>
					</div>
					<div class="feature-section col three-col" >
						<div class="col">
							<h4><?php echo esc_html__( 'Import / export coupons', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'You can import / export coupons. This can be helpful when you are moving your store or when you want to move coupons from other store to new one.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
						<div class="col">
							<h4><?php echo esc_html__( 'Automatic payment for subscription renewals', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'If your store is using WooCommerce subscription and your customer has purchased a subscription using a Store Credit. If that store credit has balance left in it, store will automatically use it for renewing that subscription.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
						<div class="col last-feature">
							<h4><?php echo esc_html__( 'Make your customer\'s coupon usage, easy & simple', 'woocommerce-smart-coupons' ); ?></h4>
							<p>
								<?php echo esc_html__( 'Smart Coupons makes life of your customer really easy by showing only valid coupons to your customer (if logged in) on', 'woocommerce-smart-coupons' ) . ' <a href="http://docs.woothemes.com/document/smart-coupons/#section-16" target="_blank">' . esc_html__( 'cart', 'woocommerce-smart-coupons' ) . '</a>, ' . esc_html__( 'checkout & My Account page. In addition to that those coupons can be applied with single click on it. So, no need to remember the coupon code or copy-pasting.', 'woocommerce-smart-coupons' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Output the FAQ's screen.
		 */
		public function faqs_screen() {
			?>

			<script type="text/javascript">
				jQuery(document).on('ready', function(){
					jQuery('#toplevel_page_woocommerce').find('a[href$=shop_coupon]').addClass('current');
					jQuery('#toplevel_page_woocommerce').find('a[href$=shop_coupon]').parent().addClass('current');
				});
			</script>

			<div class="wrap about-wrap" style="max-width: unset !important;">

				<?php $this->intro(); ?>

				<h3><?php echo esc_html__( 'FAQ / Common Problems', 'woocommerce-smart-coupons' ); ?></h3>

				<?php
					$faqs = array(
						array(
							'que' => esc_html__( 'When trying to add Smart Coupon, I get "Invalid post type" message.', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Make sure use of coupon is enabled in your store. You can find this setting', 'woocommerce-smart-coupons' ) . ' <a href="' . add_query_arg(
								array(
									'page' => 'wc-settings',
									'tab'  => 'checkout',
								), admin_url( 'admin.php' )
							) . '" target="_blank">' . __( 'here', 'woocommerce-smart-coupons' ) . '</a>.',
						),
						array(
							'que' => esc_html__( 'How to translate texts from Smart Coupons?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Simplest method is by installing', 'woocommerce-smart-coupons' ) . ' <a href="https://wordpress.org/plugins/loco-translate/" target="_blank">' . esc_html__( 'Loco Translate', 'woocommerce-smart-coupons' ) . '</a> ' . esc_html__( 'plugin. If you want to keep, translated file outside this plugin, refer', 'woocommerce-smart-coupons' ) . ' <a href="http://docs.woothemes.com/document/smart-coupons/#section-19" target="_blank">' . __( 'this article', 'woocommerce-smart-coupons' ) . '</a>.',
						),
						array(
							'que' => esc_html__( 'Smart Coupon\'s fields are broken?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Make sure you are using the latest version of Smart Coupons. If still the issue persist, deactivate all plugins except WooCommerce & Smart Coupons. Recheck the issue, if the issue still persists, contact us. If the issue goes away, re-activate other plugins one-by-one & re-checking the fields, to find out which plugin is conflicting. Inform us about this issue.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'Do not want to tie store credit to be used by only one customer?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'You\'ll need to check "Disable Email Restriction" setting (in main coupon which is entered in "Coupons" field of the product edit page) under "Usage Restrictions" tab on edit coupon page.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'On WooCommerce 2.6+, getting \'Page Not Found Error\' when accessing Coupons tab from My Account Page?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Go to WordPress -> Settings -> Permalinks and click on Save Settings once.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'Is there any reference file for creating an import file for coupons?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'There is one file which is located inside the plugin. The file name is', 'woocommerce-smart-coupons' ) . ' <code>sample.csv</code> ' . esc_html__( 'If you want to import coupon through file, the file should be like', 'woocommerce-smart-coupons' ) . ' <code>sample.csv</code>',
						),
						array(
							'que' => esc_html__( 'Is Smart Coupons, WPML compatible?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'All texts used in Smart Coupons are translatable & can be translated using', 'woocommerce-smart-coupons' ) . ' <code>.po</code> & <code>.mo</code> ' . esc_html__( 'file. Secondly, the texts from this plugin remain in the plugin, it doesn\'t get saved in database, only values are getting saved in database, which cannot be translated.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'I\'m using WPML & WPML provides support for multi-currency, but Smart Coupons only changes currency symbol & the price value remains same. Can Smart Coupons change the currency symbol and the price value associated with it?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Currently, It can only change the currency symbol the price value remains the same. Smart Coupon is not compatible with multi-currency plugin. You may find this in some future version.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'How to change texts of email, sent from Smart Coupons?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'You can do this by 2 methods, either by changing the texts directly in email template file or overriding the email template.', 'woocommerce-smart-coupons' ) . ' <a href="http://docs.woothemes.com/document/smart-coupons/#section-18" target="_blank">' . esc_html__( 'How to override email template', 'woocommerce-smart-coupons' ) . '</a>.',
						),
						array(
							'que' => esc_html__( 'Available coupons are not visible on Cart, Checkout & My Account page?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Smart Coupons uses hooks of Cart, Checkout & My Account page to display available coupons. If your theme is not using those hooks in cart, checkout & my-account template, coupons will not be displayed.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'How can I resend gift card coupon bought by customers?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'You can resend them from order admin edit page.', 'woocommerce-smart-coupons' ) . ' <a href="http://docs.woothemes.com/document/smart-coupons/#section-15" target="_blank">' . __( 'See how', 'woocommerce-smart-coupons' ) . '</a>.',
						),
						array(
							'que' => esc_html__( 'Uncheck "Auto-generate" option in Store Credit is not saving? Is it always checked?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Store Credit\'s default behavior is auto-generate because, when using a store credit, it\'s balance keeps reducing. Therefore it should be uniquely created for every user automatically.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'Smart Coupons is not sending emails?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Smart Coupons sends email only after order completion. Make sure all settings of coupons, products are in place. Check if order complete email is sending. Also check by switching your theme.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( '"Store Credit Receiver detail" form not appearing on checkout page?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'This form is displayed using a hook which is available in My Account template. Make sure your theme\'s my-account template contains all hooks required for that template.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'Is it compatible with WooCommerce Subscription?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'Yes, it does work with WooCommerce Subscription.', 'woocommerce-smart-coupons' ),
						),
						array(
							'que' => esc_html__( 'Does it allow printing of coupon as Gift Card?', 'woocommerce-smart-coupons' ),
							'ans' => esc_html__( 'No, it doesn\'t provide any feature which enables you to take a printout of the generated coupon, but if you can take printout from your email, you can use it as an alternative.', 'woocommerce-smart-coupons' ),
						),

					);

					$faqs = array_chunk( $faqs, 2 );

					echo '<div>';
				foreach ( $faqs as $fqs ) {
					echo '<div class="two-col">';
					foreach ( $fqs as $index => $faq ) {
						echo '<div' . ( ( 1 === absint( $index ) ) ? ' class="col last-feature"' : ' class="col"' ) . '>';
						echo '<h4>' . $faq['que'] . '</h4>'; // phpcs:ignore
						echo '<p>' . $faq['ans'] . '</p>'; // phpcs:ignore
						echo '</div>';
					}
					echo '</div>';
				}
					echo '</div>';
				?>

			</div>

			<?php
		}


		/**
		 * Sends user to the welcome page on first activation.
		 */
		public function sc_welcome() {

			if ( ! get_transient( '_smart_coupons_activation_redirect' ) ) {
				return;
			}

			// Delete the redirect transient.
			delete_transient( '_smart_coupons_activation_redirect' );

			wp_safe_redirect( admin_url( 'admin.php?page=sc-about' ) );
			exit;

		}
	}

}

WC_SC_Admin_Welcome::get_instance();
