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
 * User Membership object.
 *
 * This class represents a single user's membership, ie. a user belonging to a User Membership. A single user can have multiple memberships.
 *
 * @since 1.0.0
 */
class WC_Memberships_User_Membership {


	/** @var int User Membership (post) ID */
	public $id;

	/** @var int User Membership plan id */
	public $plan_id;

	/** @var \WC_Memberships_Membership_Plan User Membership plan */
	public $plan;

	/** @var int User Membership user (author) id */
	public $user_id;

	/** @var string User Membership (post) status */
	public $status;

	/** @var \WP_Post User Membership post object */
	public $post;

	/** @var \WC_Product the product that granted access */
	private $product;

	/** @var string Membership type */
	protected $type = '';

	/** @var string start date meta */
	protected $start_date_meta = '';

	/** @var string end date meta */
	protected $end_date_meta = '';

	/** @var string cancelled date meta */
	protected $cancelled_date_meta = '';

	/** @var string paused date meta */
	protected $paused_date_meta = '';

	/** @var string paused intervals meta */
	protected $paused_intervals_meta = '';

	/** @var string product id meta */
	protected $product_id_meta = '';

	/** @var string order id meta */
	protected $order_id_meta = '';

	/** @var string previous owners meta */
	protected $previous_owners_meta = '';

	/** @var string meta data key for storing a login token for automatic login */
	protected $renewal_login_token_meta = '';

	/** @var string meta data key for storing a lock when performing operation sensitive to race conditions */
	protected $locked_meta = '';


	/**
	 * User Membership Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|\WP_Post|\WC_Memberships_User_Membership $id User Membership ID or post object
	 * @param int $user_id optional User / Member ID, used only for new memberships
	 */
	public function __construct( $id, $user_id = null ) {

		if ( ! $id ) {
			return;
		}

		if ( is_numeric( $id ) ) {
			$this->post = get_post( $id );
		} elseif ( is_object( $id ) ) {
			$this->post = $id;
		}

		if ( $this->post ) {

			// load in post data...
			$this->id      = $this->post->ID;
			// the post author from WordPress could be a numerical string!
			$this->user_id = (int) $this->post->post_author;
			$this->plan_id = $this->post->post_parent;
			$this->status  = $this->post->post_status;

		} elseif ( $user_id ) {

			// ...or at least user ID, if provided, ensuring it's an integer
			$this->user_id = (int) $user_id;
		}

		$this->set_meta_keys();

		// set membership type
		$this->type = $this->get_type();
	}


	/**
	 * Returns the user membership ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int User Membership ID
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Returns the user ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int User ID
	 */
	public function get_user_id() {
		return $this->user_id;
	}


	/**
	 * Returns the user object.
	 *
	 * @since 1.9.0
	 *
	 * @return \WP_User|null
	 */
	public function get_user() {

		$user = $this->user_id > 0 ? get_user_by( 'id', $this->user_id ) : null;

		return ! empty( $user ) ? $user : null;
	}


	/**
	 * Returns the associated plan ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int Membership Plan ID
	 */
	public function get_plan_id() {
		return $this->plan_id;
	}


	/**
	 * Returns the associated plan object.
	 *
	 * @since 1.0.0
	 *
	 * @return \WC_Memberships_Membership_Plan
	 */
	public function get_plan() {

		if ( ! $this->plan ) {
			// get the plan if not already set
			$this->plan = $plan = wc_memberships_get_membership_plan( $this->plan_id, $this );
		} else {
			// get the plan already set but make sure it comes out filtered
			$plan = $this->plan;
			$post = ! empty( $this->plan ) ? $plan->post : null;
			/** this filter is documented in /includes/class-wc-memberships-membership-plans.php */
			$plan = apply_filters( 'wc_memberships_membership_plan', $plan, $post, $this );
		}

		return $plan;
	}


	/**
	 * Returns the membership status.
	 *
	 * Note: trims the `wcm-` internal prefix from the returned status.
	 *
	 * @since 1.0.0
	 *
	 * @return string status slug
	 */
	public function get_status() {
		return 0 === strpos( $this->status, 'wcm-' ) ? substr( $this->status, 4 ) : $this->status;
	}


	/**
	 * Returns the Members Area URL to view the membership.
	 *
	 * @since 1.11.0
	 *
	 * @return string
	 */
	public function get_view_membership_url() {

		return wc_memberships_get_members_area_url( $this->get_plan() );
	}


	/**
	 * Returns meta keys used to store user membership meta data.
	 *
	 * @since 1.11.0
	 *
	 * @return string[]
	 */
	public function get_meta_keys() {

		return array(
			'_start_date',
			'_end_date',
			'_cancelled_date',
			'_paused_date',
			'_paused_intervals',
			'_product_id',
			'_order_id',
			'_previous_owners',
			'_renewal_login_token',
			'_locked',
		);
	}


	/**
	 * Sets the user membership meta keys for storing meta data.
	 *
	 * @since 1.11.1
	 */
	protected function set_meta_keys() {

		foreach ( $this->get_meta_keys() as $meta_key ) {

			$property = ltrim( $meta_key, '_' ) . '_meta';

			$this->$property = $meta_key;
		}
	}


	/**
	 * Returns the user membership type.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_type() {

		$type = 'manually-assigned';
		$plan = $this->get_plan();

		if ( $plan ) {

			$access_method = $plan->get_access_method();

			if ( 'signup' === $access_method ) {
				$type = 'free';
			} elseif ( 'purchase' === $access_method ) {
				// if there is no order or product, this must have been admin assigned
				$type = $this->get_order_id() && $this->get_product_id() ? 'purchased' : $type;
			}
		}

		/**
		 * Filter a user membership type.
		 *
		 * @since 1.7.0
		 *
		 * @param string $type user membership type
		 * @param \WC_Memberships_User_Membership $user_membership current user membership object
		 */
		$this->type = apply_filters( 'wc_memberships_user_membership_type', $type, $this );

		return $this->type;
	}


	/**
	 * Checks if the membership is of the specified type.
	 *
	 * @since 1.7.0
	 *
	 * @param array|string $type the membership type (or types, if array) to check
	 * @return bool
	 */
	public function is_type( $type ) {
		return is_array( $type ) ? in_array( $this->get_type(), $type, true ) : $type === $this->get_type();
	}


	/**
	 * Sets the membership start datetime.
	 *
	 * @since 1.6.2
	 *
	 * @param string $date a date in Y-m-d H:i:s MySQL format
	 */
	public function set_start_date( $date ) {

		$start_date = wc_memberships_parse_date( $date, 'mysql' );

		if ( ! $start_date ) {
			$start_date = date( 'Y-m-d H:i:s', current_time( 'timestamp', true ) );
		}

		update_post_meta( $this->id, $this->start_date_meta, $start_date );

		if ( 'delayed' !== $this->get_status() && strtotime( 'today', strtotime( $start_date ) ) > current_time( 'timestamp', true ) ) {

			$this->update_status( 'delayed' );
		}
	}


	/**
	 * Returns the membership start datetime.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @return null|int|string start date in the chosen format
	 */
	public function get_start_date( $format = 'mysql' ) {

		$date = get_post_meta( $this->id, $this->start_date_meta, true );

		return ! empty( $date ) ? wc_memberships_format_date( $date, $format ) : null;
	}


	/**
	 * Returns the membership start local datetime.
	 *
	 * @since 1.3.8
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @return null|int|string localized start date in the chosen format
	 */
	public function get_local_start_date( $format = 'mysql' ) {

		// get the date timestamp
		$date = $this->get_start_date( 'timestamp' );

		// adjust the date to the site's local timezone
		return ! empty( $date ) ? wc_memberships_adjust_date_by_timezone( $date, $format ) : null;
	}


	/**
	 * Checks whether the membership has a set start date.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function has_start_date() {
		return is_numeric( $this->get_start_date( 'timestamp' ) );
	}


	/**
	 * Sets the membership end datetime.
	 *
	 * @since 1.0.0
	 * @param string|int $date end date either as a unix timestamp or mysql datetime string - defaults to empty string (unlimited membership, no end date)
	 */
	public function set_end_date( $date = '' ) {

		$end_timestamp = '';
		$end_date      = '';

		if ( is_numeric( $date ) ) {
			$end_timestamp = (int) $date;
		} elseif ( is_string( $date ) ) {
			$end_timestamp = strtotime( $date );
		}

		if ( ! empty( $end_timestamp ) ) {

			// for fixed date memberships set end date to the end of the day
			$end_timestamp = $this->get_plan() && $this->plan->is_access_length_type( 'fixed' ) ? wc_memberships_adjust_date_by_timezone( strtotime( 'midnight', $end_timestamp ), 'timestamp', wc_timezone_string() ) : $end_timestamp;

			$end_date = date( 'Y-m-d H:i:s', (int) $end_timestamp );
		}

		// update end date in post meta
		update_post_meta( $this->id, $this->end_date_meta, $end_date );

		// set expiration scheduled events
		$this->schedule_expiration_events( $end_timestamp );
	}


	/**
	 * Returns the membership end datetime.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @param bool $include_paused optional: whether to include the time this membership has been paused (defaults to true)
	 * @return null|int|string the end date in the chosen format
	 */
	public function get_end_date( $format = 'mysql', $include_paused = true ) {

		$date = get_post_meta( $this->id, $this->end_date_meta, true );

		// adjust end/expiry date if paused date exists
		if ( $date && $include_paused && ( $paused_date = $this->get_paused_date( 'timestamp' ) ) ) {

			$difference    = current_time( 'timestamp', true ) - $paused_date;
			$end_timestamp = strtotime( $date ) + $difference;

			$date = date( 'Y-m-d H:i:s', $end_timestamp );
		}

		return ! empty( $date ) ? wc_memberships_format_date( $date, $format ) : null;
	}


	/**
	 * Returns the membership end local datetime.
	 *
	 * @since 1.3.8
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @param bool $include_paused optional: whether to include the time this membership has been paused (defaults to true)
	 * @return null|int|string the localized end date in the chosen format
	 */
	public function get_local_end_date( $format = 'mysql', $include_paused = true ) {

		// get the date timestamp
		$date = $this->get_end_date( 'timestamp', $include_paused );

		// adjust the date to the site's local timezone
		return ! empty( $date ) ? wc_memberships_adjust_date_by_timezone( $date, $format ) : null;
	}


	/**
	 * Checks whether a membership is unlimited in time (never expires).
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function has_end_date() {
		return is_numeric( $this->get_end_date( 'timestamp', false ) );
	}


	/**
	 * Returns the membership cancelled datetime.
	 *
	 * @since 1.6.2
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @return null|int|string the cancelled date in the chosen format
	 */
	public function get_cancelled_date( $format = 'mysql' ) {

		$date = get_post_meta( $this->id, $this->cancelled_date_meta, true );

		return ! empty( $date ) ? wc_memberships_format_date( $date, $format ) : null;
	}


	/**
	 * Returns the membership cancelled local datetime.
	 *
	 * @since 1.6.2
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @return null|int|string the localized cancelled date in the chosen format
	 */
	public function get_local_cancelled_date( $format = 'mysql' ) {

		// get the date timestamp
		$date = $this->get_cancelled_date( 'timestamp' );

		// adjust the date to the site's local timezone
		return ! empty( $date ) ? wc_memberships_adjust_date_by_timezone( $date, $format ) : null;
	}


	/**
	 * Sets the membership cancelled datetime.
	 *
	 * @since 1.6.2
	 *
	 * @param string $date a date in MySQL format
	 */
	public function set_cancelled_date( $date ) {

		if ( $cancelled_date = wc_memberships_parse_date( $date, 'mysql' ) ) {

			update_post_meta( $this->id, $this->cancelled_date_meta, $cancelled_date );
		}
	}


	/**
	 * Returns the membership paused datetime.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @return null|int|string the paused date in the chosen format
	 */
	public function get_paused_date( $format = 'mysql' ) {

		$date = get_post_meta( $this->id, $this->paused_date_meta, true );

		return ! empty( $date ) ? wc_memberships_format_date( $date, $format ) : null;
	}


	/**
	 * Returns the membership end local datetime.
	 *
	 * @since 1.3.8
	 *
	 * @param string $format optional, defaults to 'mysql'
	 * @return null|int|string the localized paused date in the chosen format
	 */
	public function get_local_paused_date( $format = 'mysql' ) {

		// get the date timestamp
		$date = $this->get_paused_date( 'timestamp' );

		// adjust the date to the site's local timezone
		return ! empty( $date ) ? wc_memberships_adjust_date_by_timezone( $date, $format ) : null;
	}


	/**
	 * Sets the membership paused datetime.
	 *
	 * @since 1.6.2
	 *
	 * @param string $date a date in MySQL format
	 */
	public function set_paused_date( $date ) {

		if ( $paused_date = wc_memberships_parse_date( $date, 'mysql' ) ) {

			update_post_meta( $this->id, $this->paused_date_meta, $paused_date );
		}
	}


	/**
	 * Removes the membership paused datetime information.
	 *
	 * @since 1.6.2
	 */
	public function delete_paused_date() {

		delete_post_meta( $this->id, $this->paused_date_meta );
	}


	/**
	 * Returns the membership paused periods as an associative array of timestamps.
	 *
	 * @since 1.6.2
	 *
	 * @return array associative array of start => end ranges of paused intervals
	 */
	public function get_paused_intervals() {

		$intervals = get_post_meta( $this->id, $this->paused_intervals_meta, true );

		return is_array( $intervals ) ? $intervals : array();
	}


	/**
	 * Adds a record to the membership pausing registry.
	 *
	 * @since 1.6.2
	 *
	 * @param string $interval either 'start' or 'end'
	 * @param int $time a valid timestamp in UTC
	 */
	public function set_paused_interval( $interval, $time ) {

		if ( ! is_numeric( $time ) || (int) $time <= 0 ) {
			return;
		}

		$intervals = $this->get_paused_intervals();

		if ( 'start' === $interval ) {

			// sanity check to avoid overwriting an existing key
			if ( ! array_key_exists( $time, $intervals ) ) {
				$intervals[ (int) $time ] = '';
			}

		} elseif ( 'end' === $interval ) {

			if ( ! empty( $intervals ) ) {

				// get the last timestamp when the membership was paused
				end( $intervals );
				$last = key( $intervals );

				// sanity check to avoid overwriting an existing value
				if ( is_numeric( $last ) && empty( $intervals[ $last ] ) ) {
					$intervals[ (int) $last ] = (int) $time;
				}

			// this might be the case where a paused membership didn't have interval tracking yet
			} elseif ( $this->is_paused() && ( $paused_date = $this->get_paused_date( 'timestamp' ) ) ) {

				$intervals[ (int) $paused_date ] = (int) $time;
			}
		}

		update_post_meta( $this->id, $this->paused_intervals_meta, $intervals );
	}


	/**
	 * Deletes the paused intervals data.
	 *
	 * @since 1.7.0
	 */
	public function delete_paused_intervals() {

		delete_post_meta( $this->id, $this->paused_intervals_meta );
	}


	/**
	 * Returns the total active or inactive time of a membership.
	 *
	 * @since 1.6.2
	 *
	 * @param string $type either 'active' or 'inactive'
	 * @param string $format optional, can be either 'timestamp' (default) or 'human'
	 * @return null|int|string timestamp or human readable string
	 */
	private function get_total_time( $type, $format = 'timestamp' ) {

		$total  = null;
		$time   = 0; // time as 0 seconds
		$start  = $this->get_start_date( 'timestamp' );
		$pauses = $this->get_paused_intervals();

		// set 'time' as now or the most recent time when the membership was active
		if ( 'active' === $type ) {

			if ( $this->is_expired() ) {
				$time = $this->get_end_date( 'timestamp' );
			} elseif ( $this->is_cancelled() ) {
				$time = $this->get_cancelled_date( 'timestamp' );
			}

			if ( empty( $total ) ) {
				$time = current_time( 'timestamp', true );
			}
		}

		if ( ! empty( $pauses ) ) {

			end( $pauses );
			$last = key( $pauses );

			// if the membership is currently paused, add the time until now
			if ( isset( $pauses[ $last ] ) && '' === $pauses[ $last ] && $this->is_paused() ) {
				$pauses[ $last ] = current_time( 'timestamp', true );
			}

			reset( $pauses );

			$previous_start = (int) $start;

			foreach ( $pauses as $pause_start => $pause_end ) {

				// sanity check, see if there is a previous interval without an end record
				// or if the start record in the key is invalid
				if ( empty( $pause_end ) || $pause_start < $previous_start ) {
					continue;
				}

				if ( 'active' === $type ) {
					// subtract from the most recent active time paused intervals
					$time -= max( 0, (int) $pause_end - (int) $pause_start );
				} elseif ( 'inactive' === $type ) {
					// add up from 0s the time this membership has been inactive
					$time += max( 0, (int) $pause_end - (int) $pause_start );
				}

				$previous_start = (int) $pause_start;
			}
		}

		// get the total as a difference
		if ( 'active' === $type ) {
			$total = max( 0, $time - $start );
		} elseif ( 'inactive' === $type ) {
			$total = max( 0, $time );
		}

		// maybe humanize the output
		if ( 'human' === $format && is_int( $total ) ) {

			$time_diff = max( $start, $start + $total );
			$total     = $time_diff !== $start && $time_diff > 0 ? human_time_diff( $start, $time_diff ) : 0;
		}

		return $total;
	}


	/**
	 * Returns the total amount of time the membership has been active since its start date.
	 *
	 * @since 1.6.2
	 *
	 * @param string $format optional, can be either 'timestamp' (default) or 'human' for a human readable span relative to the start date
	 * @return int|string timestamp or human readable string
	 */
	public function get_total_active_time( $format = 'timestamp' ) {
		return $this->get_total_time( 'active', $format );
	}


	/**
	 * Returns the total amount of time the membership has been inactive since its start date.
	 *
	 * @since 1.6.2
	 *
	 * @param string $format optional, can be either 'timestamp' (default) or 'human' for a human readable inactive time span
	 * @return int|string timestamp or human readable string
	 */
	public function get_total_inactive_time( $format = 'timestamp' ) {
		return $this->get_total_time( 'inactive', $format );
	}


	/**
	 * Unschedules expiration events.
	 *
	 * @since 1.7.0
	 */
	public function unschedule_expiration_events() {

		$hook_args = array( 'user_membership_id' => $this->id );

		// set a post meta to use as a lock to ensure all events are unscheduled before scheduling new ones
		if ( ! get_post_meta( $this->id, $this->locked_meta, true ) ) {
			add_post_meta( $this->id, $this->locked_meta, true, true );
		}

		// unschedule any previous expiry hooks
		if ( (bool) as_next_scheduled_action( 'wc_memberships_user_membership_expiry', $hook_args, 'woocommerce-memberships'  ) ) {
			as_unschedule_action( 'wc_memberships_user_membership_expiry', $hook_args, 'woocommerce-memberships' );
		}

		// unschedule any previous expiring soon hooks
		if ( (bool) as_next_scheduled_action( 'wc_memberships_user_membership_expiring_soon', $hook_args, 'woocommerce-memberships' ) ) {
			as_unschedule_action( 'wc_memberships_user_membership_expiring_soon', $hook_args, 'woocommerce-memberships' );
		}

		// unschedule any previous renewal reminder hooks
		if ( (bool) as_next_scheduled_action( 'wc_memberships_user_membership_renewal_reminder', $hook_args, 'woocommerce-memberships' ) ) {
			as_unschedule_action( 'wc_memberships_user_membership_renewal_reminder', $hook_args, 'woocommerce-memberships' );
		}

		// remove the lock
		delete_post_meta( $this->id, $this->locked_meta );
	}


	/**
	 * Sets expiration events for this membership.
	 *
	 * @see \WC_Memberships_User_Membership::set_end_date()
	 * @see \WC_Memberships_User_Membership::expire_membership()
	 * @see \WC_Memberships_User_Memberships::trigger_expiration_events()
	 *
	 * @since 1.7.0
	 *
	 * @param int|null $end_timestamp membership end date timestamp: when empty (unlimited membership), it will just clear any existing scheduled event
	 */
	public function schedule_expiration_events( $end_timestamp = null ) {

		$now = current_time( 'timestamp', true );

		// always unschedule events for the same membership first
		$this->unschedule_expiration_events();

		// avoid race conditions by introducing a recursion if a lock is found
		if ( get_post_meta( $this->id, $this->locked_meta, true ) ) {
			$this->schedule_expiration_events( $end_timestamp );
			return;
		}

		// schedule membership expiration hooks, provided there's an end date and it's after the beginning of today's date
		if ( is_numeric( $end_timestamp ) && (int) $end_timestamp > strtotime( 'today', $now ) ) {

			$hook_args = array( 'user_membership_id' => $this->id );

			// Schedule the membership expiration event:
			as_schedule_single_action( $end_timestamp, 'wc_memberships_user_membership_expiry', $hook_args, 'woocommerce-memberships' );

			// Schedule the membership ending soon event:
			$days_before = wc_memberships()->get_user_memberships_instance()->get_ending_soon_days();
			$time_before = $end_timestamp - ( $days_before * DAY_IN_SECONDS );
			// sanity check: the future can't be in the past :)
			$days_before_expiry = $time_before > current_time( 'timestamp', true ) ? $time_before : $end_timestamp - DAY_IN_SECONDS;

			if ( $end_timestamp > $now ) {

				if ( $days_before_expiry > $now ) {
					// if there's at least one day before the expiry date, use the email setting (days before)
					as_schedule_single_action( $days_before_expiry, 'wc_memberships_user_membership_expiring_soon', $hook_args, 'woocommerce-memberships' );
				} else {
					// if it's less than one day, schedule as a median time between now and the effective end date (in the course of the last remaining day)
					as_schedule_single_action( max( $now + MINUTE_IN_SECONDS, round( ( $now + $end_timestamp ) / 2 ) ), 'wc_memberships_user_membership_expiring_soon', $hook_args, 'woocommerce-memberships' );
				}
			}
		}
	}


	/**
	 * Sets post-expiration events for this membership.
	 *
	 * @see \WC_Memberships_User_Membership::schedule_expiration_events()
	 * @see \WC_Memberships_User_Membership::expire_membership()
	 *
	 * @since 1.10.0
	 *
	 * @param int $expiration_time timestamp when the membership expired or is set to expire
	 */
	public function schedule_post_expiration_events( $expiration_time ) {

		$hook_args = array( 'user_membership_id' => $this->id );

		// unschedule any previously set renewal reminder event
		if ( (bool) as_next_scheduled_action( 'wc_memberships_user_membership_renewal_reminder', $hook_args, 'woocommerce-memberships' ) ) {
			as_unschedule_action( 'wc_memberships_user_membership_renewal_reminder', $hook_args, 'woocommerce-memberships' );
		}

		// set the renewal reminder event if can be renewed
		if ( $this->can_be_renewed() ) {

			$days_after = wc_memberships()->get_user_memberships_instance()->get_renewal_reminder_days();

			as_schedule_single_action( $expiration_time + ( $days_after * DAY_IN_SECONDS ), 'wc_memberships_user_membership_renewal_reminder', $hook_args, 'woocommerce-memberships' );
		}
	}


	/**
	 * Sets the order id that granted access.
	 *
	 * @since 1.7.0
	 *
	 * @param int $order_id WC_Order ID
	 */
	public function set_order_id( $order_id ) {

		$order_id = is_numeric( $order_id ) ? (int) $order_id : 0;

		if ( $order = $order_id > 0 ? wc_get_order( $order_id ) : null ) {

			update_post_meta( $this->id, $this->order_id_meta, $order_id );

			// sanity check, ensures the matching order has a grant access record
			if ( ! wc_memberships_has_order_granted_access( $order, array( 'user_membership' => $this ) ) ) {

				wc_memberships_set_order_access_granted_membership( $order, $this, array(
					'already_granted'       => 'yes',
					'granting_order_status' => $order->get_status(),
				) );
			}
		}
	}


	/**
	 * Returns the order id that granted access.
	 *
	 * @since 1.0.0
	 *
	 * @return null|int Order id
	 */
	public function get_order_id() {

		$order_id = get_post_meta( $this->id, $this->order_id_meta, true );

		return is_numeric( $order_id ) ? (int) $order_id : null;
	}


	/**
	 * Returns the order that granted access.
	 *
	 * @since 1.0.0
	 *
	 * @return \WC_Order|false|null
	 */
	public function get_order() {

		$order_id = $this->get_order_id();

		return $order_id ? wc_get_order( $order_id ) : null;
	}


	/**
	 * Deletes the order information.
	 *
	 * @since 1.7.0
	 *
	 * @return bool success
	 */
	public function delete_order_id() {

		return delete_post_meta( $this->id, $this->order_id_meta );
	}


	/**
	 * Sets the product ID that granted access.
	 *
	 * @since 1.7.0
	 *
	 * @param int $product_id WC_Product ID
	 */
	public function set_product_id( $product_id ) {

		$product_id = is_numeric( $product_id ) ? (int) $product_id : 0;

		// check that the id belongs to an actual product
		if ( $product_id > 0 && wc_get_product( $product_id ) ) {

			update_post_meta( $this->id, $this->product_id_meta, $product_id );
			unset( $this->product );
		}
	}


	/**
	 * Returns the product id that granted access.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $get_variation_id return the variation ID if the product that granted access was a variable one
	 * @return int|null product ID if found or null if not set
	 */
	public function get_product_id( $get_variation_id = false ) {

		$product_id = get_post_meta( $this->id, $this->product_id_meta, true );

		if ( $get_variation_id && $product_id > 0 ) {

			$product = wc_get_product( $product_id );
			$order   = $this->get_order();

			if ( $order && $product && $product->is_type( 'variable' ) ) {

				foreach ( $order->get_items() as $item ) {

					if ( ! empty( $item['variation_id'] ) && $item['variation_id'] > 0 ) {

						$variation_product = wc_get_product( $item['variation_id'] );

						if ( $variation_product && $variation_product->is_type( 'variation' ) ) {

							$parent    = Framework\SV_WC_Product_Compatibility::get_parent( $variation_product );
							$parent_id = $parent ? $parent->get_id() : null;

							if ( $product_id && $parent_id === (int) $product_id ) {

								$product_id = $variation_product->get_id();
								break;
							}
						}
					}
				}
			}
		}

		return $product_id ? (int) $product_id : null;
	}


	/**
	 * Returns the product that granted access as an object.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $get_variation whether to return the individual variation if the product that granted access was a variable one
	 * @return \WC_Product|false|null
	 */
	public function get_product( $get_variation = false ) {

		$product    = null;
		$product_id = $this->get_product_id( $get_variation );

		if ( $get_variation ) {
			$product = wc_get_product( $product_id );
		} elseif ( ! isset( $this->product ) ) {
			$this->product = $product_id ? wc_get_product( $product_id ) : null;
		}

		return $get_variation ? $product : $this->product;
	}


	/**
	 * Deletes the granting access product ID information.
	 *
	 * @since 1.7.0
	 *
	 * @return bool success
	 */
	public function delete_product_id() {

		$success = delete_post_meta( $this->id, $this->product_id_meta );

		if ( $success ) {
			unset( $this->product );
		}

		return $success;
	}


	/**
	 * Checks and returns true if the membership has the given status.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $status single status or array of statuses
	 * @return bool
	 */
	public function has_status( $status ) {

		$has_status = ( ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status );

		/**
		 * Filter if User Membership has a status.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $has_status whether the User Membership has a certain status
		 * @param \WC_Memberships_User_Membership $user_membership instance of the User Membership object
		 * @param array|string $status one (string) status or any statuses (array) to check
		 */
		return (bool) apply_filters( 'woocommerce_memberships_membership_has_status', $has_status, $this, $status );
	}


	/**
	 * Updates the status of the membership.
	 *
	 * @since 1.0.0
	 *
	 * @param string $new_status status to change the order to (note: no internal `wcm-` prefix is required!)
	 * @param string $note optional note to add (default empty as none)
	 */
	public function update_status( $new_status, $note = '' ) {

		if ( ! $this->id ) {
			return;
		}

		// standardise status names
		$new_status = 0 === strpos( $new_status, 'wcm-' ) ? substr( $new_status, 4 ) : $new_status;
		$old_status = $this->get_status();

		// get valid statuses
		$valid_statuses = wc_memberships_get_user_membership_statuses();

		// only update if they differ - and ensure post_status is a 'wcm' status.
		if ( $new_status !== $old_status && array_key_exists( 'wcm-' . $new_status, $valid_statuses ) ) {

			// note will be added to the membership by the general User_Memberships utility class,
			// so that we add only 1 note instead of 2 when updating the status
			wc_memberships()->get_user_memberships_instance()->set_membership_status_transition_note( $note );

			// update the order
			wp_update_post( array(
				'ID'          => $this->id,
				'post_status' => 'wcm-' . $new_status,
			) );

			$this->status = 'wcm-' . $new_status;
		}
	}


	/**
	 * Checks if the membership has been cancelled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_cancelled() {
		return 'cancelled' === $this->get_status();
	}


	/**
	 * Checks if the membership is expired.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_expired() {
		return 'expired' === $this->get_status();
	}


	/**
	 * Checks if the membership is paused.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_paused() {
		return 'paused' === $this->get_status();
	}


	/**
	 * Checks if the membership has a delayed activation.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function is_delayed() {

		if ( 'delayed' === $this->get_status() ) {

			// always perform a check until start date is in the past...
			if ( $this->get_start_date( 'timestamp' ) <= current_time( 'timestamp', true ) ) {
				// ... so we can activate the membership finally
				$this->activate_membership();
			} else {
				return true;
			}
		}

		return false;
	}


	/***
	 * Checks if a membership is currently in active status.
	 *
	 * If the membership is not in the active period it will move to expired.
	 * Note: this checks whether member has access, according to plan rules, 'active' status is not the only status that can grant access to membership holder.
	 *
	 * @since 1.6.4
	 *
	 * @return bool
	 */
	public function is_active() {

		$current_status = $this->get_status();
		$active_period  = $this->is_in_active_period();
		$is_active      = in_array( $current_status, wc_memberships()->get_user_memberships_instance()->get_active_access_membership_statuses(), true );

		// sanity check: an active membership should always be within the active period time range
		if ( $is_active && ! $active_period ) {

			// this means the status is active, but the current time is out of the start/end dates boundaries
			if ( $this->get_start_date( 'timestamp' ) > current_time( 'timestamp', true ) ) {
				// if we're before the start date, membership should be delayed
				$this->update_status( 'delayed' );
			} else {
				// if we're beyond the end date, the membership should expire
				$this->expire_membership();
			}

			$is_active = false;

		// the membership status is not active, yet the current time is between the start/end dates, so perhaps should be activated
		} elseif ( $active_period ) {

			if ( 'delayed' === $current_status ) {

				// the time has come and membership is ready for activation
				$this->activate_membership();

				$is_active = true;

			} elseif ( 'expired' ===  $current_status ) {

				// if the membership is expired, we don't reactivate it, but it can't be in active period, so we update the end date to now
				$this->set_end_date( current_time( 'mysql', true ) );

				$is_active = false;
			}
		}

		return $is_active;
	}


	/**
	 * Checks if the membership has started, but not expired.
	 *
	 * Note: this does not check the User Membership access status itself.
	 * @see \WC_Memberships_User_Membership::is_active()
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_in_active_period() {

		$start = $this->get_start_date( 'timestamp' );
		$now   = current_time( 'timestamp', true );
		$end   = $this->get_end_date( 'timestamp', ! $this->is_expired() );

		return ( $start ? $start <= $now : true ) && ( $end ? $now <= $end : true );
	}


	/**
	 * Pauses the user membership.
	 *
	 * @since 1.0.0
	 *
	 * @param string $note optional note to add (leave empty to use the default note content)
	 */
	public function pause_membership( $note = null ) {

		// bail out if paused already
		if ( $this->is_paused() ) {
			return;
		}

		$this->update_status( 'paused', ! empty( $note ) ? $note : __( 'Membership paused.', 'woocommerce-memberships' ) );
		$this->set_paused_date( current_time( 'mysql', true ) );

		/**
		 * Upon User Membership pausing.
		 *
		 * @since 1.7.0
		 *
		 * @param \WC_Memberships_User_Membership $user_membership the user membership being paused
		 */
		do_action( 'wc_memberships_user_membership_paused', $this );
	}


	/**
	 * Cancels the user membership.
	 *
	 * @since 1.0.0
	 *
	 * @param string $note optional note to add (leave empty to use the default note content)
	 */
	public function cancel_membership( $note = null ) {

		// bail out if cancelled already
		if ( $this->is_cancelled() ) {
			return;
		}

		$this->update_status( 'cancelled', ! empty( $note ) ? $note : __( 'Membership cancelled.', 'woocommerce-memberships' ) );
		$this->set_cancelled_date( current_time( 'mysql', true ) );

		/**
		 * Upon User Membership cancellation.
		 *
		 * @since 1.7.0
		 *
		 * @param \WC_Memberships_User_Membership $user_membership user membershp being cancelled.
		 */
		do_action( 'wc_memberships_user_membership_cancelled', $this );
	}


	/**
	 * Expires the user membership.
	 *
	 * This also schedules the renewal reminder expiration event.
	 *
	 * @see \WC_Memberships_User_Membership::schedule_expiration_events()
	 * @see \WC_Memberships_User_Memberships::trigger_expiration_events()
	 *
	 * @since 1.6.2
	 */
	public function expire_membership() {

		// bail out if expired already
		if ( $this->is_expired() ) {
			return;
		}

		/**
		 * Confirm expire User Membership.
		 *
		 * @since 1.5.4
		 *
		 * @param bool $expire true will expire this membership, false will retain it - default: true, expire it
		 * @param \WC_Memberships_User_Membership $user_membership the User Membership object being expired
		 */
		if ( true === apply_filters( 'wc_memberships_expire_user_membership', true, $this ) ) {

			$current_time = current_time( 'timestamp', true );

			// expire the membership
			$this->update_status( 'expired', __( 'Membership expired.', 'woocommerce-memberships' ) );

			// set the expiration date to always match the current time,
			// since this could have been forcefully expired before the planned end date
			update_post_meta( $this->id, $this->end_date_meta, date( 'Y-m-d H:i:s', $current_time ) );

			$this->schedule_post_expiration_events( $current_time );

			/**
			 * Upon User Membership expiration.
			 *
			 * @since 1.7.0
			 *
			 * @param int $user_membership_id the expired user membership ID
			 */
			do_action( 'wc_memberships_user_membership_expired', $this->id );
		}
	}


	/**
	 * Activates the user membership.
	 *
	 * @since 1.0.0
	 *
	 * @param null|string $note optional note to add (leave empty to use the default note content)
	 */
	public function activate_membership( $note = null ) {

		$previous_status = $this->get_status();
		$was_paused      = 'paused'  === $previous_status;
		$was_delayed     = 'delayed' === $previous_status;

		if ( ! $was_delayed && $this->is_active() ) {
			// bail out if already active (check for delay prevents infinite loops)
			return;
		} elseif ( $was_paused ) {
			// reactivation
			$default_note = __( 'Membership resumed.', 'woocommerce-memberships' );
			$this->set_paused_interval( 'end', current_time( 'timestamp', true ) );
		} else {
			// activation
			$default_note = __( 'Membership activated.', 'woocommerce-memberships' );
		}

		$start_date = $this->get_start_date();
		$start_time = $start_date ? strtotime( 'today', strtotime( $start_date ) ) : null;
		$is_delayed = $start_time && $start_time > current_time( 'timestamp', true );

		// sanity check for delayed start
		if ( ! $was_delayed && $is_delayed ) {
			$this->update_status( 'delayed', empty( $note ) ? $default_note : $note );
		} elseif ( 'active' !== $previous_status && ! $is_delayed ) {
			$this->update_status( 'active', empty( $note )  ? $default_note : $note );
		} else {
			return;
		}

		/**
		 * Upon User Membership activation or re-activation.
		 *
		 * @since 1.7.0
		 *
		 * @param \WC_Memberships_User_Membership $user_membership the membership object
		 * @param bool $was_paused whether this is a reactivation of a paused membership
		 * @param string $previous_status the status the membership was set before activation
		 */
		do_action( 'wc_memberships_user_membership_activated', $this, $was_paused, $previous_status );
	}


	/**
	 * Checks whether the user membership can be cancelled by the user.
	 *
	 * Note: does not check whether the current user has capability to cancel the related post object.
	 * A Cancelled Membership does not equate to a deleted post.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function can_be_cancelled() {

		// check if membership has eligible status for cancellation
		$can_be_cancelled = in_array( $this->get_status(), wc_memberships()->get_user_memberships_instance()->get_valid_user_membership_statuses_for_cancellation(), true );

		/**
		 * Whether a user membership can be cancelled.
		 *
		 * This does not imply that it will be cancelled but should meet the characteristics to be cancelled by a user that has capability to cancel.
		 *
		 * @since 1.7.0
		 *
		 * @param bool $can_be_cancelled whether can be cancelled by a user
		 * @param \WC_Memberships_User_Membership $user_membership the Membership to be cancelled
		 */
		return (bool) apply_filters( 'wc_memberships_user_membership_can_be_cancelled', $can_be_cancelled, $this );
	}


	/**
	 * Returns the cancel membership URL for frontend use.
	 *
	 * @since 1.0.0
	 *
	 * @return string cancel URL (unescaped)
	 */
	public function get_cancel_membership_url() {

		$cancel_endpoint = wc_get_page_permalink( 'myaccount' );

		if ( false === strpos( $cancel_endpoint, '?' ) ) {
			$cancel_endpoint = trailingslashit( $cancel_endpoint );
		}

		$cancel_url = wp_nonce_url(
			add_query_arg( array(
				'cancel_membership' => $this->id,
			), $cancel_endpoint ),
			'wc_memberships-cancel_membership_' . $this->id
		);

		/**
		 * Filter the cancel membership URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url URL string
		 * @param \WC_Memberships_User_Membership $user_membership the related membership
		 */
		return apply_filters( 'wc_memberships_get_cancel_membership_url', $cancel_url, $this );
	}


	/**
	 * Returns the first product suitable to renew the membership.
	 *
	 * Ideally it will try to pick the one that originally granted access.
	 *
	 * @see \WC_Memberships_User_Membership::get_products_for_renewal()
	 *
	 * @since 1.7.0
	 *
	 * @return null|\WC_Product product object
	 */
	public function get_product_for_renewal() {

		$products_for_renewal = $this->get_products_for_renewal();
		$product_for_renewal  = ! empty( $products_for_renewal ) && is_array( $products_for_renewal ) ? reset( $products_for_renewal ) : null;

		/**
		 * Filters the product for renewing membership access.
		 *
		 * @since 1.9.1
		 *
		 * @param null|\WC_Product $product_for_renewal a product object or null if no product can renew access
		 * @param array|\WC_Product[] $products_for_renewal products that may grant renewed access or empty array if no products
		 * @param \WC_Memberships_User_Membership $user_membership
		 */
		return apply_filters( 'wc_memberships_user_membership_get_product_for_renewal', $product_for_renewal instanceof \WC_Product ? $product_for_renewal : null, $products_for_renewal, $this );
	}


	/**
	 * Returns products suitable to renew this membership.
	 *
	 * @since 1.7.0
	 *
	 * @return \WC_Product[] array of product objects
	 */
	public function get_products_for_renewal() {

		$renewal_products = array();
		$original_product = $this->get_product();

		// make sure the original product is the first in array
		if ( $original_product && $original_product->is_purchasable() ) {

			$renewal_products[ $original_product->get_id() ] = $original_product;
		}

		$plan = $this->get_plan();

		// get all the other purchasable products according to the plan settings
		if ( $plan && ( $products = $plan->get_products() ) ) {

			foreach ( $products as $product_id => $product ) {

				if ( $product->is_purchasable() ) {

					$renewal_products[ $product_id ] = $product;
				}
			}
		}

		return $renewal_products;
	}


	/**
	 * Checks whether the user membership can be renewed by the user.
	 *
	 * Note: does not check whether the user has capability to renew.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function can_be_renewed() {

		// check first if the status allows renewal
		$membership_plan = $this->plan instanceof \WC_Memberships_Membership_Plan ? $this->plan : $this->get_plan();
		$can_be_renewed  = $membership_plan && in_array( $this->get_status(), wc_memberships()->get_user_memberships_instance()->get_valid_user_membership_statuses_for_renewal(), true );

		if ( $can_be_renewed ) {

			if ( $membership_plan->is_access_method( 'manual-only' ) ) {

				// if membership has no other access method than manual assignment
				// then it shouldn't be renewed by the user, but only by an admin
				// (note we don't check for the membership $type property
				// but the plan's access method)
				$can_be_renewed = false;
			}

			if ( $membership_plan->is_access_length_type( 'fixed' ) ) {

				$fixed_end_date = $membership_plan->get_access_end_date( 'timestamp' );

				// fixed length memberships with an end date in the past
				// shouldn't be renewable (unless an admin changes the plan end date)
				if ( ! empty( $fixed_end_date ) && current_time( 'timestamp', true ) > $fixed_end_date ) {
					$can_be_renewed = false;
				}
			}

			if ( $membership_plan->has_products() ) {

				// plan has products but let's see if any are purchasable
				if ( ! $this->get_product_for_renewal() ) {
					$can_be_renewed = false;
				}

			} else {

				// if plan has no products, can't be renewed via purchase
				$can_be_renewed = false;
			}
		}

		/**
		 * Filter whether a user membership can be renewed.
		 *
		 * This does not imply that it will be renewed but should meet the characteristics to be renewable by a user that has capability to renew.
		 *
		 * @since 1.7.0
		 *
		 * @param bool $can_be_renewed whether can be renewed by a user
		 * @param \WC_Memberships_User_Membership $user_membership the Membership to renew
		 */
		return (bool) apply_filters( 'wc_memberships_user_membership_can_be_renewed', $can_be_renewed, $this );
	}


	/**
	 * Returns the renewal login token.
	 *
	 * @since 1.9.0
	 *
	 * @return array associative array of data
	 */
	public function get_renewal_login_token() {

		$token = get_post_meta( $this->id, $this->renewal_login_token_meta, true );

		return ! is_array( $token ) ? array() : $token;
	}


	/**
	 * Sets a renewal login token.
	 *
	 * @see \wp_generate_password() we don't use this to avoid possible filtering disrupting token generation
	 *
	 * @since 1.9.0
	 *
	 * @return array token data
	 */
	public function generate_renewal_login_token() {

		// the following code to create a $token replaces usage of wp_generate_password() to avoid possible filtering
		$token = '';
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		for ( $i = 0; $i < 32; $i++ ) {
			$token .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}

		$user_token = array(
			'expires' => strtotime( '+30 days' ),
			'token'   => $token,
		);

		update_post_meta( $this->id, $this->renewal_login_token_meta, $user_token );

		return $user_token;
	}


	/**
	 * Deletes the renewal login token.
	 *
	 * @since 1.9.0
	 */
	public function delete_renewal_login_token() {

		delete_post_meta( $this->id, $this->renewal_login_token_meta );
	}


	/**
	 * Returns the renew membership URL for frontend use.
	 *
	 * @since 1.0.0
	 *
	 * @return string renew URL (unescaped)
	 */
	public function get_renew_membership_url() {

		$user_token = $this->get_renewal_login_token();

		// See if we have an existing token we should be using first so we don't break URLs in previous emails.
		// Regenerate it if our token is expired anyway.
		if (      empty( $user_token )
		     || ! isset( $user_token['token'] )
		     ||   (int) $user_token['expires'] < time() ) {

			$user_token = $this->generate_renewal_login_token();
		}

		$renew_endpoint = wc_get_page_permalink( 'myaccount' );

		if ( false === strpos( $renew_endpoint, '?' ) ) {
			$renew_endpoint = trailingslashit( $renew_endpoint );
		}

		// use a user token rather than a nonce to validate the login request
		// given we don't want a 24 hr limit and a nonce isn't best for validating this anyway
		$renew_url = add_query_arg( array(
			'renew_membership' => $this->id,
			'user_token'       => $user_token['token'],
		), $renew_endpoint );

		/**
		 * Filters the renew membership URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url URL
		 * @param \WC_Memberships_User_Membership $user_membership the related user membership
		 */
		return (string) apply_filters( 'wc_memberships_get_renew_membership_url', $renew_url, $this );
	}


	/**
	 * Checks whether the membership can be transferred to another user.
	 *
	 * @since 1.9.1
	 *
	 * @param null|int|\WP_User $to_user optional user to transfer the membership to (used in filter)
	 * @return bool
	 */
	public function can_be_transferred( $to_user = null ) {

		if ( $to_user instanceof \WP_User ) {
			$to_user = $to_user->ID;
		}

		/**
		 * Filters whether the membership can be transferred to another user.
		 *
		 * @since 1.9.1
		 *
		 * @param bool $can_be_transferred whether the membership can be transferred (default true)
		 * @param \WC_Memberships_User_Membership $user_membership the membership to be transferred
		 * @param int $from_user ID of the user the current membership belongs to
		 * @param null|int $to_user optional ID of the user the membership should be transferred to
		 */
		return (bool) apply_filters( 'wc_memberships_user_membership_can_be_transferred', true, $this, $this->user_id, $to_user );
	}


	/**
	 * Transfers the User Membership from its current user to another user.
	 *
	 * If a transfer is successful it will also record the ownership passage in a post meta.
	 *
	 * @since 1.6.0
	 *
	 * @param \WP_User|int $to_user user (object or ID) to transfer membership to
	 * @return bool whether the transfer was successful
	 * @throws Framework\SV_WC_Plugin_Exception in case of errors throws an exception
	 */
	public function transfer_ownership( $to_user ) {

		if ( is_numeric( $to_user ) ) {
			// we always grab the user object to verify user existence and grab nicename later below
			$to_user = get_user_by( 'id', (int) $to_user );
		}

		$user_membership_id = (int) $this->id;
		$previous_owner     = $this->get_user();
		$new_owner          = $to_user;
		$error              = array();
		$default_error      = array( 0 => __( 'An error occurred.', 'woocommerce-memberships' ) );

		if ( ! $new_owner instanceof \WP_User ) {
			$error[1] = __( 'Please select a valid user to transfer the membership to.', 'woocommerce-memberships' );
		} elseif ( ! $this->can_be_transferred( $new_owner->ID ) ) {
			$error[2] = __( 'This membership cannot be transferred to this user.', 'woocommerce-memberships' );
		} elseif ( $previous_owner instanceof \WP_User && $new_owner->ID === $previous_owner->ID ) {
			$error[3] = __( 'The user you have selected to transfer the membership to is the same user owning the membership to be transferred. Please select a different user.', 'woocommerce-memberships' );
		} elseif ( wc_memberships_is_user_member( $new_owner->ID, $this->get_plan_id(), false ) ) {
			$error[4] = __( 'The selected user to transfer the membership to is already a member.', 'woocommerce-memberships' );
		} elseif ( ! $previous_owner instanceof \WP_User || $user_membership_id < 1 ) {
			$error = $default_error;
		}

		if ( ! empty( $error ) ) {

			/**
			 * Filters the membership transfer error.
			 *
			 * @since 1.9.1
			 *
			 * @param array $error a single element array with an error code (index key) and an error message (value)
			 * @param \WC_Memberships_User_Membership $user_membership the membership being transferred
			 * @param \WP_User|null $previous_owner the current owner of the membership (null on exceptional cases)
			 * @param \WP_User|null $new_owner the owner to transfer the membership to (null if invalid or an error occurred)
			 */
			$error = apply_filters( 'wc_memberships_user_membership_can_be_transferred_error', $error, $this, $previous_owner, $new_owner );

			throw new Framework\SV_WC_Plugin_Exception( current( $error ), key( $error ) );
		}

		$updated = wp_update_post( array(
			'ID'          => $user_membership_id,
			'post_type'   => 'wc_user_membership',
			'post_author' => $new_owner->ID,
		) );

		if ( (int) $this->id !== (int) $updated ) {
			throw new Framework\SV_WC_Plugin_Exception( current( $default_error ), key( $default_error ) );
		}

		// update the user id for the current instance of this membership
		$this->user_id = (int) $new_owner->ID;

		$owners          = $this->get_previous_owners();
		$last_owner      = array( current_time( 'timestamp', true ) => $previous_owner->ID );
		$previous_owners = ! empty( $owners ) && is_array( $owners ) ? array_merge( $owners, $last_owner ) : $last_owner;

		// update the ownership history
		update_post_meta( $user_membership_id, $this->previous_owners_meta, $previous_owners );

		// add a note to membership about the transfer event
		$this->add_note(
			/* translators: Membership transferred from user %1$s to user %2$s */
			sprintf( __( 'Membership transferred from %1$s to %2$s.', 'woocommerce-memberships' ),
				$previous_owner->user_nicename,
				$new_owner->user_nicename
			)
		);

		/**
		 * Fires when the membership is transferred from a user to another.
		 *
		 * @since 1.9.4
		 *
		 * @param \WC_Memberships_User_Membership $user_membership The membership that was transferred from a user to another
		 * @param \WP_User $new_owner The membership new owner
		 * @param \WP_User $previous_owner The membership old owner
		 */
		do_action( 'wc_memberships_user_membership_transferred', $this, $new_owner, $previous_owner );

		// we keep returning true for legacy reasons (exceptions on failure were introduced later)
		return true;
	}


	/**
	 * Returns the User Membership's previous owners.
	 *
	 * If the User Membership has been previously transferred from an user to another,
	 * this method will return its ownership history as an associative array of timestamps (time of transfer) and user IDs.
	 *
	 * @since 1.6.0
	 *
	 * @return array associative array of timestamps (keys) and user ids (values)
	 */
	public function get_previous_owners() {

		$previous_owners = get_post_meta( $this->id, $this->previous_owners_meta, true );

		return ! empty( $previous_owners ) && is_array( $previous_owners ) ? $previous_owners : array();
	}


	/**
	 * Return the membership's notes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filter optional: 'customer' or 'private', default 'all'
	 * @param int $paged optional: pagination
	 * @return \WP_Comment[] array of comment (membership notes) objects
	 */
	public function get_notes( $filter = 'all', $paged = 1 ) {

		$args = array(
			'post_id' => $this->id,
			'approve' => 'approve',
			'type'    => 'user_membership_note',
			'paged'   => (int) $paged,
		);

		// avoid internal filtering issues
		remove_filter( 'comments_clauses', array( wc_memberships()->get_user_memberships_instance(), 'exclude_membership_notes_from_queries' ), 10 );

		$comments = (array) get_comments( $args );
		$notes    = array();

		if ( in_array( $filter, array( 'customer', 'private' ), true ) ) {

			foreach ( $comments as $note ) {

				$notified = get_comment_meta( $note->comment_ID, 'notified', true );

				if ( $notified && 'customer' === $filter )  {
					$notes[] = $note;
				} elseif ( ! $notified && 'private' === $filter ) {
					$notes[] = $note;
				}
			}

		} else {

			$notes = $comments;
		}

		// add comment clauses exclusions back
		add_filter( 'comments_clauses', array( wc_memberships()->get_user_memberships_instance(), 'exclude_membership_notes_from_queries' ), 10 );

		return $notes;
	}


	/**
	 * Adds a note to the membership.
	 *
	 * @since 1.0.0
	 *
	 * @param string $note note to add (content)
	 * @param bool $notify optional: whether to notify member or not (default false, do not notify)
	 * @return int|false note (comment) ID, false on error
	 */
	public function add_note( $note, $notify = false ) {

		$note = trim( $note );

		if ( empty( $note ) ) {

			// a note can't be empty
			return false;

		} if ( is_user_logged_in() && current_user_can( 'edit_post', $this->id ) ) {

			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;

		} else {

			$comment_author       = __( 'WooCommerce', 'woocommerce-memberships' );

			$comment_author_email = strtolower( __( 'WooCommerce', 'woocommerce-memberships' ) ) . '@';
			$comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) : 'noreply.com';

			$comment_author_email = sanitize_email( $comment_author_email );
		}

		$comment_post_ID    = $this->id;
		$comment_author_url = '';
		$comment_content    = $note;
		$comment_agent      = 'WooCommerce';
		$comment_type       = 'user_membership_note';
		$comment_parent     = 0;
		$comment_approved   = 1;

		/**
		 * Filter new user membership note data.
		 *
		 * @since 1.0.0
		 *
		 * @param array $commentdata array of arguments to insert the note as a comment to the user membership
		 * @param array $args extra arguments like user membership id and whether to notify member of the new note...
		 */
		$commentdata = apply_filters( 'wc_memberships_new_user_membership_note_data', compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' ), array( 'user_membership_id' => $this->id, 'notify' => $notify ) );

		$comment_id = wp_insert_comment( $commentdata );

		// set whether the member has received an email notification for this note
		add_comment_meta( $comment_id, 'notified', $notify );

		// prepare args for filter and send email notification
		$new_membership_note_args =  array(
			'user_membership_id' => $this->id,
			'membership_note'    => $note,
			'notify'             => $notify,
		);

		/**
		 * Fires after a new membership note is added.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_membership_note_args arguments
		 */
		do_action( 'wc_memberships_new_user_membership_note', $new_membership_note_args );

		// maybe notify the member
		if ( true === $notify ) {
			wc_memberships()->get_emails_instance()->send_new_membership_note_email( $new_membership_note_args );
		}

		return $comment_id;
	}


}
