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
 * Subscription-tied user memberships handler.
 *
 * @since 1.8.0
 */
class WC_Memberships_Integration_Subscriptions_User_Memberships {


	/**
	 * Initializes handler's hooks.
	 *
	 * @since 1.8.0
	 */
	public function __construct() {

		// helper object for subscription-tied user memberships
		require( wc_memberships()->get_plugin_path() . '/includes/integrations/subscriptions/class-wc-memberships-integration-subscriptions-user-membership.php' );

		// init hooks that need to be executed early
		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'wc_memberships_new_membership_data',     array( $this, 'adjust_new_membership_data' ), 10, 2 );
		add_filter( 'wc_memberships_user_membership_created', array( $this, 'upon_new_membership_created' ) );
		add_filter( 'wc_memberships_renew_membership',        array( $this, 'renew_membership' ), 10, 3 );

		// adjust Memberships access dates
		add_filter( 'wc_memberships_access_from_time', array( $this, 'adjust_post_access_from_time' ), 10, 3 );

		// handle Membership expiration by cron event
		add_filter( 'wc_memberships_expire_user_membership', array( $this, 'handle_membership_expiry_by_scheduled_event' ), 5, 2 );

		// skip Membership Ending Soon emails for memberships linked to a subscription
		add_filter( 'woocommerce_email_enabled_WC_Memberships_User_Membership_Ending_Soon_Email', array( $this, 'skip_ending_soon_emails' ), 20, 2 );

		// extend Memberships WP REST API support with Subscriptions data
		add_filter( 'wc_memberships_rest_api_user_membership_excluded_meta_keys', array( $this, 'exclude_user_membership_api_item_meta_keys' ), 1, 2 );
		add_filter( 'wc_memberships_rest_api_user_membership_data',               array( $this, 'add_user_membership_api_item_data_subscription_id' ), 1, 2 );
		add_filter( 'wc_memberships_rest_api_user_membership_links',              array( $this, 'add_user_membership_api_item_data_subscription_link' ), 1, 2 );
		add_filter( 'wc_memberships_rest_api_user_membership_schema',             array( $this, 'handle_user_membership_api_schema' ), 1 );

		// extends Memberships WP REST API queries with more filters
		add_filter( 'woocommerce_rest_wc_user_memberships_query_args',            array( $this, 'rest_api_query_user_memberships_by_subscription' ), 1, 2 );
		add_filter( 'wc_memberships_rest_api_user_memberships_collection_params', array( $this, 'rest_api_user_memberships_collection_params' ), 1, 2 );
	}


	/**
	 * Initializes early hooks.
	 *
	 * @internal
	 *
	 * @since 1.8.0
	 */
	public function init() {

		// filter memberships objects
		add_filter( 'wc_memberships_user_membership',      array( $this, 'get_user_membership' ), 2, 1 );
		// set the user membership to subscription type if the membership is tied to a subscription
		add_filter( 'wc_memberships_user_membership_type', array( $this, 'get_subscription_tied_membership_type' ), 1, 2 );

		// do not automatically renew cancelled memberships if tied to a subscription
		add_filter( 'wc_memberships_renew_cancelled_membership', array( $this, 'handle_cancelled_membership_renewal' ), 10, 2 );
	}


	/**
	 * Filters a User Membership to return a subscription-tied User Membership.
	 *
	 * This method is a filter callback and should not be used directly.
	 * @see \wc_memberships_get_user_membership() instead.
	 *
	 * @internal
	 *
	 * @since 1.8.0
	 *
	 * @param \WC_Memberships_User_Membership $user_membership the user membership object
	 * @return \WC_Memberships_Integration_Subscriptions_User_Membership|\WC_Memberships_User_Membership
	 */
	public function get_user_membership( $user_membership ) {

		return wc_memberships_is_user_membership_linked_to_subscription( $user_membership ) ? new \WC_Memberships_Integration_Subscriptions_User_Membership( $user_membership->post ) : $user_membership;
	}


	/**
	 * Filters the membership type.
	 *
	 * @internal
	 *
	 * @since 1.8.0
	 *
	 * @param string $membership_type the membership type to filter.
	 * @param \WC_Memberships_User_Membership|\WC_Memberships_Integration_Subscriptions_User_Membership $user_membership the user membership object
	 * @return string
	 */
	public function get_subscription_tied_membership_type( $membership_type, $user_membership ) {

		return wc_memberships_is_user_membership_linked_to_subscription( $user_membership ) ? 'subscription' : $membership_type;
	}


	/**
	 * Handles a cancelled user membership renewal when tied to a subscription.
	 *
	 * @internal
	 * @see wc_memberships_create_user_membership()
	 *
	 * @since 1.9.0
	 *
	 * @param bool $renew whether to renew a cancelled user membership that may be tied to a subscription
	 * @param \WC_Memberships_User_Membership|\WC_Memberships_Integration_Subscriptions_User_Membership $user_membership
	 * @return bool
	 */
	public function handle_cancelled_membership_renewal( $renew, $user_membership ) {

		$integration = wc_memberships()->get_integrations_instance()->get_subscriptions_instance();

		if (      $integration
		     &&   wc_memberships_is_user_membership_linked_to_subscription( $user_membership )
		     && ! $integration->has_membership_installment_plan( $user_membership ) ) {

			$renew = false;
		}

		return $renew;
	}


	/**
	 * Adjusts a user membership post scheduled content 'access from' time for subscription-based memberships.
	 *
	 * @internal
	 *
	 * @since 1.8.0
	 *
	 * @param int $from_time "access from" time, as a timestamp
	 * @param \WC_Memberships_Membership_Plan_rule $rule related plan rule
	 * @param \WC_Memberships_User_Membership $user_membership the user membership
	 * @return int modified $from_time, as timestamp
	 */
	public function adjust_post_access_from_time( $from_time, \WC_Memberships_Membership_Plan_Rule $rule, \WC_Memberships_User_Membership $user_membership ) {

		if ( $rule->is_access_schedule_excluding_trial() ) {

			$subscription_user_membership = new \WC_Memberships_Integration_Subscriptions_User_Membership( $user_membership->get_id() );

			if ( $subscription_user_membership->has_subscription() && ( $trial_end_date = $subscription_user_membership->get_free_trial_end_date( 'timestamp' ) ) ) {

				return $trial_end_date;
			}
		}

		return $from_time;
	}


	/**
	 * Adjusts whether a membership should be renewed or not.
	 *
	 * @internal
	 *
	 * @since 1.8.0
	 *
	 * @param bool $renew
	 * @param \WC_Memberships_Membership_Plan $plan
	 * @param array $args
	 * @return bool
	 */
	public function renew_membership( $renew, $plan, $args ) {

		if ( $plan && ! empty( $args['product_id'] ) && ( $product = wc_get_product( $args['product_id'] ) ) ) {

			$plans_handler = wc_memberships()->get_integrations_instance()->get_subscriptions_instance()->get_plans_instance();

			if ( $plans_handler && $product && \WC_Subscriptions_Product::is_subscription( $product ) ) {

				$renew = $plans_handler->grant_access_while_subscription_active( $plan );
			}
		}

		return $renew;
	}


	/**
	 * Adjusts new membership data.
	 *
	 * Sets the end date to match subscription end date.
	 *
	 * @internal
	 *
	 * @since 1.8.0
	 *
	 * @param array $data original membership data
	 * @param array $args array of arguments
	 * @return array modified membership data
	 */
	public function adjust_new_membership_data( $data, $args ) {

		$product = isset( $args['product_id'] ) ? wc_get_product( $args['product_id'] ) : null;

		if (    $product
		     && isset( $args['order_id'] )
		     && (int) $args['order_id'] > 0
		     && \WC_Subscriptions_Product::is_subscription( $product ) ) {

			$subscription = wc_memberships_get_order_subscription($args['order_id'], $product->get_id() );
		    $integration  = wc_memberships()->get_integrations_instance()->get_subscriptions_instance();

			if ( $subscription && $integration ) {

				$trial_end = $integration->get_subscription_event_time( $subscription, 'trial_end' );

				if ( $trial_end && $trial_end > current_time( 'timestamp', true ) ) {

					$data['post_status'] = 'wcm-free_trial';
				}
			}
		}

		return $data;
	}


	/**
	 * Handles meta data when a new membership is created (not necessarily subscription tied).
	 *
	 * @internal
	 *
	 * @since 1.7.1
	 *
	 * @param \WC_Memberships_User_Membership $user_membership the new user membership.
	 */
	public function upon_new_membership_created( $user_membership ) {

		if ( ! empty( $user_membership->post ) && ( wc_memberships_is_user_membership_linked_to_subscription( $user_membership ) || wc_memberships_has_subscription_product_granted_access( $user_membership ) ) ) {

			$subscription_tied_membership = new \WC_Memberships_Integration_Subscriptions_User_Membership( $user_membership->post );

			// Maybe set the free trial end date meta if subscription is on trial.
			if ( $subscription_tied_membership->has_status( 'free_trial' ) ) {

				$subscription_trial_end_date = wc_memberships()->get_integrations_instance()->get_subscriptions_instance()->get_subscription_event_date( $subscription_tied_membership->get_subscription(), 'trial_end' );

				$subscription_tied_membership->set_free_trial_end_date( $subscription_trial_end_date );
			}
		}
	}


	/**
	 * Checks if a Subscription-tied membership should really expire.
	 *
	 * It does so by comparing either the Subscription's or the User Membership's expiry date.
	 *
	 * @internal
	 *
	 * @since 1.5.4
	 *
	 * @param bool $maybe_expire Whether the User Membership is set to expire (true) or not (false)
	 * @param \WC_Memberships_User_Membership $user_membership the User Membership object set to expire
	 * @return bool true to confirm expiration, false to prevent it
	 */
	public function handle_membership_expiry_by_scheduled_event( $maybe_expire, $user_membership ) {

		$integration     = wc_memberships()->get_integrations_instance()->get_subscriptions_instance();
		$subscription    = $integration->get_subscription_from_membership( $user_membership->get_id() );
		$user_membership = new \WC_Memberships_Integration_Subscriptions_User_Membership( $user_membership->post );

		// sanity checks
		if (    ! $subscription
		     || ! $user_membership
		     || ! $user_membership->has_subscription() ) {

			return $maybe_expire;

		} elseif ( true === $maybe_expire && $user_membership->has_installment_plan() ) {

			$integration->unlink_membership( $user_membership->get_id(), $user_membership->get_subscription() );

			$maybe_expire = false;

		} else {

			$subscription_end_date = $integration->get_subscription_event_date( $subscription, 'end' );

			// expire only if the scheduled date matches the subscription end date...
			if ( $subscription_end_date === $user_membership->get_end_date() ) {

				$today = date( 'Y-m-d', current_time( 'timestamp', true ) );

				// ...and it's scheduled to expire today
				if ( 0 === strpos( $subscription_end_date, $today ) ) {
					$maybe_expire = true;
				}
			}
		}

		return $maybe_expire;
	}


	/**
	 * Disables Ending Soon emails for memberships tied to a subscription.
	 *
	 * Currently, a subscription cannot be renewed before its expiration date.
	 *
	 * TODO however this could change in the future if Subscriptions introduces early renewals {FN 2017-04-04}
	 *
	 * @internal
	 *
	 * @since 1.8.2
	 *
	 * @param bool $is_enabled whether the email is enabled in the first place
	 * @param int|\WC_Memberships_User_Membership|\WC_Memberships_Integration_Subscriptions_User_Membership $user_membership a user membership which could be tied to a subscription
	 * @return bool
	 */
	public function skip_ending_soon_emails( $is_enabled, $user_membership ) {

		if ( $is_enabled ) {

			if ( is_numeric( $user_membership ) ) {
				$user_membership = wc_memberships_get_user_membership( $user_membership );
			}

			// if it's linked to a subscription, skip
			$is_enabled = wc_memberships_is_user_membership_linked_to_subscription( $user_membership ) ? false : $is_enabled;
		}

		return $is_enabled;
	}


	/**
	 * Extends the collection parameters to filter REST API queries for user memberships.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 *
	 * @param array $params array of collection parameters
	 * @return array
	 */
	public function rest_api_user_memberships_collection_params( array $params ) {

		$params['subscription'] = array(
			'description'       => __( 'Limit results to user memberships linked to a specific subscription (matched by ID).', 'woocommerce-memberships' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}


	/**
	 * Filters query arguments for the REST API to fetch a collection of user memberships for API consumption.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 *
	 * @param array $query_args \WP_Query arguments
	 * @param \WP_REST_Request $request request object
	 * @return array
	 */
	public function rest_api_query_user_memberships_by_subscription( array $query_args, $request ) {

		// filter by subscription
		if ( isset( $request['subscription'] ) ) {

			if ( ! isset( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$query_args['meta_query'][] = array(
				'key'   => '_subscription_id',
				'value' => (int) $request['subscription'],
				'type'  => 'numeric',
			);

			if ( count( $query_args['meta_query'] ) > 1 ) {
				$query_args['meta_query']['relation'] = 'AND';
			}
		}

		return $query_args;
	}


	/**
	 * Excludes subscription-specific user membership meta keys from REST API responses objects.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 *
	 * @param string[] $meta_keys array of meta key names
	 * @param \WC_Memberships_User_Membership|\WC_Memberships_Integration_Subscriptions_User_Membership $user_membership user membership which could be subscription-linked
	 * @return string[]
	 */
	public function exclude_user_membership_api_item_meta_keys( $meta_keys, $user_membership ) {

		if ( $user_membership instanceof \WC_Memberships_User_Membership ) {

			$subscription_membership = new WC_Memberships_Integration_Subscriptions_User_Membership( $user_membership->post );

			$meta_keys = array_unique( array_merge( $meta_keys, $subscription_membership->get_meta_keys() ) );
		}

		return $meta_keys;
	}


	/**
	 * Extends the user membership API object with a subscription ID if the membership is tied to a subscription.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 *
	 * @param array $data associative array of API item data
	 * @param \WC_Memberships_User_Membership|\WC_Memberships_Integration_Subscriptions_User_Membership $user_membership the user membership object, which might be linked to a subscription
	 * @return array
	 */
	public function add_user_membership_api_item_data_subscription_id( $data, $user_membership ) {

		$data = Framework\SV_WC_Helper::array_insert_after( $data, 'product_id', array( 'subscription_id' => null ) );

		if ( $user_membership instanceof \WC_Memberships_Integration_Subscriptions_User_Membership && ( $subscription = $user_membership->get_subscription() ) ) {

			$data['subscription_id'] = Framework\SV_WC_Order_Compatibility::get_prop( $subscription, 'id' );
		}

		return $data;
	}


	/**
	 * Extends the user membership API object with a subscription link if the membership is tied to a subscription.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 *
	 * @param array $links
	 * @param \WC_Memberships_User_Membership|\WC_Memberships_Integration_Subscriptions_User_Membership $user_membership a membership object possibly linked to a subscription
	 * @return array
	 */
	public function add_user_membership_api_item_data_subscription_link( $links, $user_membership ) {

		if ( $user_membership instanceof \WC_Memberships_Integration_Subscriptions_User_Membership && ( $subscription = $user_membership->get_subscription() ) ) {

			$links['subscription'] = array(
				'href' => rest_url( sprintf( '/%s/subscriptions/%d', 'wc/v1', Framework\SV_WC_Order_Compatibility::get_prop( $subscription, 'id' ) ) ),
			);
		}

		return $links;
	}


	/**
	 * Extends the user membership API item schema.
	 *
	 * @since 1.11.0
	 *
	 * @param array $schema associative array
	 * @return array
	 */
	public function handle_user_membership_api_schema( $schema ) {

		$schema['properties']['subscription_id'] = array(
			'description' => __( 'Unique identifier of a subscription the membership is tied to.', 'woocommerce-memberships' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit' ),
		);

		return $schema;
	}


}
