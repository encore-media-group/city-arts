<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Class handling integrations and compatibility issues with other plugins:
 *
 * - bbPress: https://bbpress.org/
 * - WooCommerce Bookings: https://woocommerce.com/products/woocommerce-bookings/
 * - Groups: https://wordpress.org/plugins/groups/
 * - qTranslate X: https://wordpress.org/plugins/qtranslate-x/
 * - WooCommerce Measurement Price Calculator https://woocommerce.com/products/measurement-price-calculator/
 * - WooCommerce One Page Checkout https://woocommerce.com/products/woocommerce-one-page-checkout
 * - WooCommerce Subscriptions: https://woocommerce.com/products/woocommerce-subscriptions/
 * - User Switching: https://wordpress.org/plugins/user-switching/
 *
 * @since 1.6.0
 */
class WC_Memberships_Integrations {


	/** @var null|\WC_Memberships_Integration_Bbpress instance */
	private $bbpress;

	/** @var null|\WC_Memberships_Integration_Bookings instance */
	private $bookings;

	/** @var null|\WC_Memberships_Integration_Groups instance */
	private $groups;

	/** @var null|\WC_Memberships_Integration_Measurement_Price_Calculator instance */
	private $measurement_price_calculator;

	/** @var null|\WC_Memberships_Integration_One_Page_Checkout instance */
	private $one_page_checkout;

	/** @var null|\WC_Memberships_Integration_Subscriptions instance */
	private $subscriptions;

	/** @var null|\WC_Memberships_Integration_User_Switching instance */
	private $user_switching;


	/**
	 * Loads integrations.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {

		// bbPress
		if ( $this->is_bbpress_active() ) {
			$this->bbpress = wc_memberships()->load_class( '/includes/integrations/bbpress/class-wc-memberships-integration-bbpress.php', 'WC_Memberships_Integration_Bbpress' );
		}

		// Bookings
		if ( $this->is_bookings_active() ) {
			$this->bookings = wc_memberships()->load_class( '/includes/integrations/bookings/class-wc-memberships-integration-bookings.php', 'WC_Memberships_Integration_Bookings' );
		}

		// Groups
		if ( $this->is_groups_active() ) {
			$this->groups = wc_memberships()->load_class( '/includes/integrations/groups/class-wc-memberships-integration-groups.php', 'WC_Memberships_Integration_Groups' );
		}

		// qTranslate-x
		// the translation plugin could trigger server errors when restricting the
		// whole content - see https://github.com/qTranslate-Team/qtranslate-x/issues/449
		remove_action( 'pre_get_posts', 'qtranxf_pre_get_posts', 99 );

		if ( $this->is_measurement_price_calculator_active() ) {
			$this->measurement_price_calculator = wc_memberships()->load_class( '/includes/integrations/measurement-price-calculator/class-wc-memberships-integration-measurement-price-calculator.php', 'WC_Memberships_Integration_Measurement_Price_Calculator' );
		}

		// One Page Checkout
		if ( $this->is_one_page_checkout_active() ) {
			$this->one_page_checkout = wc_memberships()->load_class( '/includes/integrations/one-page-checkout/class-wc-memberships-integration-one-page-checkout.php', 'WC_Memberships_Integration_One_Page_Checkout' );
		}

		// Subscriptions
		if ( $this->is_subscriptions_active() ) {
			$this->subscriptions = wc_memberships()->load_class( '/includes/integrations/subscriptions/class-wc-memberships-integration-subscriptions.php', 'WC_Memberships_Integration_Subscriptions' );
		}

		// User Switching
		if ( $this->is_user_switching_active() ) {
			$this->user_switching = wc_memberships()->load_class( '/includes/integrations/user-switching/class-wc-memberships-integration-user-switching.php', 'WC_Memberships_Integration_User_Switching' );
		}

		// print a notice in admin if an incompatible plugin is found
		$this->handle_incompatible_plugins();
	}


	/**
	 * Returns the bbPress integration instance.
	 *
	 * @since 1.8.5
	 *
	 * @return null|\WC_Memberships_Integration_Bbpress
	 */
	public function get_bbpress_instance() {
		return $this->bbpress;
	}


	/**
	 * Returns the Bookings integration instance.
	 *
	 * @since 1.6.0
	 *
	 * @return null|\WC_Memberships_Integration_Bookings
	 */
	public function get_bookings_instance() {
		return $this->bookings;
	}


	/**
	 * Returns the Groups integration instance.
	 *
	 * @since 1.6.0
	 *
	 * @return null|\WC_Memberships_Integration_Groups
	 */
	public function get_groups_instance() {
		return $this->groups;
	}


	/**
	 * Returns the MPC integration instance.
	 *
	 * @since 1.8.8
	 *
	 * @return null|\WC_Memberships_Integration_Measurement_Price_Calculator
	 */
	public function get_measurement_price_calculator_instance() {
		return $this->measurement_price_calculator;
	}


	/**
	 * Returns the One Page Checkout integration instance.
	 *
	 * @since 1.10.6
	 *
	 * @return null|\WC_Memberships_Integration_One_Page_Checkout
	 */
	public function get_one_page_checkout_instance() {

		return $this->one_page_checkout;
	}


	/**
	 * Returns the Subscriptions integration instance.
	 *
	 * @since 1.6.0
	 *
	 * @return null|\WC_Memberships_Integration_Subscriptions
	 */
	public function get_subscriptions_instance() {
		return $this->subscriptions;
	}


	/**
	 * Returns the User Switching integration instance.
	 *
	 * @since 1.6.0
	 *
	 * @return null|\WC_Memberships_Integration_User_Switching
	 */
	public function get_user_switching_instance() {
		return $this->user_switching;
	}


	/**
	 * Checks if bbPress is active.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 */
	public function is_bbpress_active() {
		return wc_memberships()->is_plugin_active( 'bbpress.php' );
	}


	/**
	 * Checks if Bookings is active.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function is_bookings_active() {
		// the misspelling is intentional, as Bookings only fixed the typo for the main plugin file in v1.9.11
		// TODO: Remove the bookings misspelling on or after 2017-09-01 {BR 2016-11-14}
		return wc_memberships()->is_plugin_active( 'woocommmerce-bookings.php' ) || wc_memberships()->is_plugin_active( 'woocommerce-bookings.php' );
	}


	/**
	 * Checks if Groups is active.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function is_groups_active() {
		return wc_memberships()->is_plugin_active( 'groups.php' );
	}


	/**
	 * Checks if MPC is active.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	public function is_measurement_price_calculator_active() {
		return wc_memberships()->is_plugin_active( 'woocommerce-measurement-price-calculator.php' );
	}


	/**
	 * Checks if One Page Checkout is active
	 *
	 * @since 1.10.6
	 *
	 * @return bool
	 */
	public function is_one_page_checkout_active() {

		return wc_memberships()->is_plugin_active( 'woocommerce-one-page-checkout.php' );
	}


	/**
	 * Checks is Subscriptions is active.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function is_subscriptions_active() {
		return wc_memberships()->is_plugin_active( 'woocommerce-subscriptions.php' ) && class_exists( 'WC_Subscription' );
	}


	/**
	 * Checks if User Switching is active.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function is_user_switching_active() {
		return wc_memberships()->is_plugin_active( 'user-switching.php' );
	}


	/**
	 * Handles notices when incompatible plugins are found active along with Memberships.
	 *
	 * @since 1.9.4
	 */
	private function handle_incompatible_plugins() {

		if ( is_admin() ) {

			$memberships          = wc_memberships();
			$found_plugins        = array();
			$incompatible_plugins = array(
				'post-type-switcher' => 'Post Type Switcher',
			);

			foreach ( $incompatible_plugins as $plugin_main_file => $plugin_name ) {
				if ( $memberships->is_plugin_active( "{$plugin_main_file}.php" ) ) {
					$found_plugins[ $plugin_main_file ] = '<strong>' . $plugin_name . '</strong>';
				}
			}

			if ( ! empty( $found_plugins ) ) {

				$memberships->get_admin_notice_handler()->add_admin_notice(
					/* translators: Placeholders: %1$s - plugin or list of plugins, %2$s - opening HTML <a> link tag, %3%s - closing </a> HTML link tag */
					sprintf( _n( 'It looks like you have the following plugin installed which is not compatible with WooCommerce Memberships: %1$s. You may run into issues with Memberships while this is active. Please consult the %2$sdocumentation%3$s for more information.', 'It looks like you have the following plugins installed which are not compatible with Memberships: %1$s. You may run into issues with Memberships while these are active. Please consult the %2$sdocumentation%3$s for more information.', count( $found_plugins ), 'woocommerce-memberships' ),
						wc_memberships_list_items( $found_plugins, __( 'and', 'woocommerce-memberships' ) ),
						'<a href="' . $memberships->get_documentation_url() . '">',
						'</a>'
					),
					'woocommerce-memberships-incompatible-plugins-' . implode( '-', array_keys( $found_plugins ) ),
					array( 'notice_class' => 'error' )
				);
			}
		}
	}


}
