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
 * Memberships lifecycle upgrades.
 *
 * This class handles actions triggered upon plugin updates from an earlier to the current latest version.
 *
 * @since 1.6.2
 *
 * @method \WC_Memberships get_plugin()
 */
class WC_Memberships_Upgrade extends Framework\Plugin\Lifecycle {


	/**
	 * Lifecycle handler constructor.
	 *
	 * @since 1.11.0
	 *
	 * @param \WC_Memberships|\SkyVerge\WooCommerce\PluginFramework\v5_3_0\SV_WC_Plugin $wc_memberships
	 */
	public function __construct( \SkyVerge\WooCommerce\PluginFramework\v5_3_0\SV_WC_Plugin $wc_memberships ) {

		parent::__construct( $wc_memberships );

		// lifecycle: activation
		add_action( 'admin_init', array ( $this, 'activate' ) );
		// lifecycle: deactivation
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}


	/**
	 * Handles plugin activation.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	public function activate() {

		$is_active = get_option( 'wc_memberships_is_active', false );

		if ( ! $is_active ) {

			update_option( 'wc_memberships_is_active', true );

			/**
			 * Runs when Memberships is activated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_memberships_activated' );

			$this->get_plugin()->add_rewrite_endpoints();

			flush_rewrite_rules();
		}
	}


	/**
	 * Handles plugin deactivation.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	public function deactivate() {

		delete_option( 'wc_memberships_is_active' );

		/**
		 * Runs when Memberships is deactivated.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_memberships_deactivated' );

		flush_rewrite_rules();
	}


	/**
	 * Installs default settings & pages.
	 *
	 * @since 1.11.0
	 */
	protected function install() {

		// install default "content restricted" page
		$title   = _x( 'Content restricted', 'Page title', 'woocommerce-memberships' );
		$slug    = _x( 'content-restricted', 'Page slug', 'woocommerce-memberships' );
		$content = '[wcm_content_restricted]';

		wc_create_page( esc_sql( $slug ), 'wc_memberships_redirect_page_id', $title, $content );

		// show a notice about restricted content to admin users as they get started
		update_option( 'wc_memberships_admin_restricted_content_notice', 'yes' );

		// load settings and install default values
		include_once( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php' );

		/* @type \WC_Settings_Memberships $settings_page */
		$settings_page     = $this->get_plugin()->load_class( '/includes/admin/class-wc-memberships-settings.php', 'WC_Settings_Memberships' );
		$settings_sections = array_keys( $settings_page->get_sections() );

		foreach ( $settings_sections as $section ) {

			$settings = $settings_page->get_settings( $section );

			// special handling for messages
			if ( 'messages' === $section ) {

				foreach ( $settings as $i => $settings_data ) {

					if (    isset( $settings_data['id'], $settings_data['default'] )
					     && Framework\SV_WC_Helper::str_ends_with( $settings_data['id'], ']' ) ) {

						unset( $settings[ $i ] );
					}
				}
			}

			$this->install_default_settings( $settings );
		}

		// wipe a membership plan ID that may have been created in the setup wizard in case a user chooses to start over
		if ( $wizard = $this->get_plugin()->get_setup_wizard_handler() ) {

			$wizard->delete_my_first_membership_plan_id();
		}
	}


	/**
	 * Runs upgrade scripts.
	 *
	 * @since 1.11.0
	 *
	 * @param string $installed_version semver
	 */
	protected function upgrade( $installed_version ) {

		if ( ! empty( $installed_version ) ) {

			$update_path = array(
				'1.1.0'  => 'update_to_1_1_0',
				'1.4.0'  => 'update_to_1_4_0',
				'1.7.0'  => 'update_to_1_7_0',
				'1.9.0'  => 'update_to_1_9_0',
				'1.9.2'  => 'update_to_1_9_2',
				'1.10.0' => 'update_to_1_10_0',
				'1.10.5' => 'update_to_1_10_5',
				'1.11.0' => 'update_to_1_11_0',
				'1.11.1' => 'update_to_1_11_1',
			);

			foreach ( $update_path as $update_to_version => $update_script ) {

				if ( version_compare( $installed_version, $update_to_version, '<' ) ) {

					$this->$update_script();

					$this->get_plugin()->log( sprintf( 'Completed upgrade to version %1$s from version %2$s.', $update_to_version, $installed_version ) );
				}
			}
		}

		$this->get_plugin()->add_rewrite_endpoints();

		flush_rewrite_rules();
	}


	/**
	 * Runs updates.
	 *
	 * TODO remove this method by version 1.13.0 {FN 2018-07-06}
	 *
	 * @since 1.6.2
	 * @deprecated since 1.11.0
	 *
	 * @param string $installed_version semver
	 */
	public function run_update_scripts( $installed_version ) {

		_deprecated_function( 'WC_Memberships_Upgrade::run_update_scripts()', '1.11.0' );

		$this->upgrade( $installed_version );
	}


	/**
	 * Updates to v1.1.0
	 *
	 * @since 1.6.2
	 */
	private function update_to_1_1_0() {

		$all_rules = array();

		// merge rules from different options into a single option
		foreach ( array( 'content_restriction', 'product_restriction', 'purchasing_discount' ) as $rule_type ) {

			$rules = get_option( "wc_memberships_{$rule_type}_rules" );

			if ( is_array( $rules ) && ! empty( $rules ) ) {

				foreach ( $rules as $rule ) {

					// skip empty/corrupt rules
					if ( empty( $rule ) || ( isset( $rule[0] ) && ! $rule[0] ) ) {
						continue;
					}

					$rule['rule_type'] = $rule_type;
					$all_rules[] = $rule;
				}
			}

			delete_option( "wc_memberships_{$rule_type}_rules" );
		}

		update_option( 'wc_memberships_rules', $all_rules );
	}


	/**
	 * Updates to v1.4.0
	 *
	 * @since 1.6.2
	 */
	private function update_to_1_4_0() {

		// product category custom restriction messages in settings options
		update_option( 'wc_memberships_product_category_viewing_restricted_message', __( 'This product category can only be viewed by members. To view this category, sign up by purchasing {products}.', 'woocommerce-memberships' ) );
		update_option( 'wc_memberships_product_category_viewing_restricted_message_no_products', __( 'Displays if viewing a product category is restricted to a membership that cannot be purchased.', 'woocommerce-memberships' ) );
	}


	/**
	 * Updates to v1.7.0
	 *
	 * This will transition legacy Memberships expiry events set on WP Cron to utilize the newer Action Scheduler.
	 *
	 * The update won't unschedule the memberships expiration events to prevent possible timeouts or out of memory errors on very large installs while the wp cron array in option has to be updated several times.
	 * However, such events won't have a callback attached anymore and thus gracefully disappear when they are naturally due.
	 *
	 * @since 1.7.0
	 */
	private function update_to_1_7_0() {

		// get all wp cron events to process the memberships expiry ones
		$cron_events = get_option( 'cron' );

		// this would hardly happen on a healthy install...
		if ( empty( $cron_events ) ) {
			return;
		}

		wc_memberships()->log( sprintf( 'Starting upgrade to 1.7.0 for %d events', count( $cron_events ) ) );

		// process 50 events at one time, so in case of timeouts
		// one can always resume the script by activating again...
		do {

			$key_offset   = (int) get_option( 'wc_memberships_cron_offset', 0 );
			$events_chunk = array_slice( $cron_events, $key_offset, 50, true );

			if ( empty( $events_chunk ) ) {
				break;
			}

			// process the chunk of events
			foreach ( $events_chunk as $timestamp => $scheduled ) {

				// convert memberships expiry events to use the Action Scheduler
				if ( is_array( $scheduled ) && 'wc_memberships_user_membership_expiry' === key( $scheduled ) ) {

					$expiration_event   = array_values( current( $scheduled ) );
					$user_membership_id = isset( $expiration_event[0]['args'][0] ) ? $expiration_event[0]['args'][0] : null;

					if ( is_numeric( $user_membership_id ) && $user_membership = wc_memberships_get_user_membership( $user_membership_id ) ) {

						// re-schedule events using the action scheduler
						$user_membership->schedule_expiration_events( (int) $timestamp );
					}
				}
			}

			// update offset to move the pointer 50 items forward in the next batch
			update_option( 'wc_memberships_cron_offset', $key_offset + 50 );

		} while ( count( $events_chunk ) === 50 );

		// once the while loop is complete we can delete the offset option
		delete_option( 'wc_memberships_cron_offset' );
	}


	/**
	 * Updates to 1.9.0
	 *
	 * - Move all user messages into a single option array and remove obsolete option keys.
	 * - Adds a "Details" members area section that became available in the new version.
	 * - Compacts rules for all plans to improve plan handling and general performance.
	 *
	 * @since 1.9.0
	 */
	private function update_to_1_9_0() {

		$new_messages    = array();
		$legacy_messages = array(
			'member_login_message',
			'content_restricted_message',
			'page_content_restricted_message',
			'post_content_restricted_message',
			'content_restricted_message_no_products',
			'page_content_restricted_message_no_products',
			'post_content_restricted_message_no_products',
			'content_delayed_message',
			'page_content_delayed_message',
			'post_content_delayed_message',
			'product_discount_message',
			'product_discount_message_no_products',
			'product_purchasing_delayed_message',
			'product_purchasing_restricted_message',
			'product_purchasing_restricted_message_no_products',
			'product_viewing_delayed_message',
			'product_viewing_restricted_message',
			'product_viewing_restricted_message_no_products',
		);
		$unused_options  = array(
			'memberships_options',
			'memberships_products_options',
			'memberships_messages',
			'memberships_other_messages',
			'memberships_page_restriction_messages',
			'memberships_post_restriction_messages',
			'memberships_product_messages',
			'memberships_restriction_messages',
			'product_category_viewing_delayed_message',
			'product_category_viewing_restricted_message',
			'product_category_viewing_restricted_message_no_products',
			'wc_memberships_subscriptions_version',
			'wc_memberships_product_category_delayed_message',
			'wc_memberships_product_category_restricted_message',
			'wc_memberships_product_category_restricted_message_no_products',
		);

		foreach ( $legacy_messages as $message_code ) {

			// we use one key for both product purchasing delayed and product viewing delayed cases
			if ( 'product_purchasing_delayed_message' === $message_code ) {
				$message_code = 'product_access_delayed_message';
			} elseif ( 'product_viewing_delayed_message' === $message_code ) {
				continue;
			}

			$option_key     = "wc_memberships_{$message_code}";
			$legacy_message = get_option( $option_key, \WC_Memberships_User_Messages::get_message( $message_code ) );

			$new_messages[ $message_code ] = $legacy_message;

			$unused_options[] = $option_key;
		}

		// update messages in a single array
		update_option( 'wc_memberships_messages', $new_messages );

		// delete legacy options
		foreach ( $unused_options as $legacy_option ) {
			delete_option( $legacy_option );
		}

		wc_memberships()->log( 'Moved all user messages into a single option' );

		// add the new "Manage" membership members area section to existing plans
		$plans = wc_memberships()->get_plans_instance()->get_membership_plans( array( 'post_status' => 'any' ) );

		foreach ( $plans as $plan ) {
			$plan->set_members_area_sections( array_merge( $plan->get_members_area_sections(), array( 'my-membership-details' ) ) );
		}

		wc_memberships()->log( 'Updated membership plans members area sections' );

		// optimize the plan rules using the new rules compacting feature
		wc_memberships()->get_rules_instance()->compact_rules();

		wc_memberships()->log( 'Compacted membership plans rules' );
	}


	/**
	 * Updates to 1.9.2
	 *
	 * Repair custom taxonomy product rules that may have been corrupted after saving in 1.9.0
	 *
	 * @since 1.9.2
	 */
	private function update_to_1_9_2() {

		$raw_rules = get_option( 'wc_memberships_rules' );

		// back up the rules, just in case (will be deleted in 1.10.0 upgrade path)
		update_option( 'wc_memberships_rules_backup', $raw_rules );

		// get all product rules
		// non-taxonomy rules are filtered out below
		$product_rules = wc_memberships()->get_rules_instance()->get_rules( array(
			'rule_type' => array(
				'product_restriction',
				'purchasing_discount',
			),
		) );

		foreach ( $product_rules as $rule_key => $rule ) {

			// sanity check, or if the rule has a taxonomy name already, there's nothing to repair
			if ( ! $rule instanceof \WC_Memberships_Membership_Plan_Rule || 'taxonomy' !== $rule->get_content_type() || $rule->get_content_type_name() ) {
				continue;
			}

			$term_ids = $rule->get_object_ids();

			// nothing we can do if there are no terms to check
			if ( empty( $term_ids ) ) {
				continue;
			}

			$term = get_term( current( $term_ids ) );

			if ( ! is_wp_error( $term ) && $term && ! empty( $term->taxonomy ) ) {

				$product_rules[ $rule_key ]->set_content_type_name( $term->taxonomy );
				continue;
			}
		}

		wc_memberships()->get_rules_instance()->update_rules( $product_rules );
	}


	/**
	 * Updates to 1.10.0
	 *
	 * @since 1.10.0
	 */
	private function update_to_1_10_0() {

		delete_option( 'wc_memberships_rules_backup' );
	}


	/**
	 * Updates to 1.10.5
	 *
	 * @since 1.10.5
	 */
	private static function update_to_1_10_5() {

		delete_option( 'wc_memberships_product_category_viewing_restricted_message' );
		delete_option( 'wc_memberships_product_category_viewing_restricted_message_no_products' );
	}


	/**
	 * Updates to 1.11.0
	 *
	 * @since 1.11.0
	 */
	private function update_to_1_11_0() {

		// skips the wizard if not a new installation
		if ( $wizard = $this->get_plugin()->get_setup_wizard_handler() ) {

			$wizard->complete_setup();
		}
	}


	/**
	 * Updates to 1.11.1
	 *
	 * @since 1.11.1
	 */
	private function update_to_1_11_1() {

		// add a flag to display a notice about Jilt advanced emails on upgrade
		update_option( 'wc_memberships_show_advanced_emails_notice', 'yes' );
	}


}
