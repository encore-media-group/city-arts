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
 * User Membership notification emails abstract class.
 *
 * Handles common methods and hooks for emails  related to a user membership's events.
 *
 * @since 1.7.0
 */
abstract class WC_Memberships_User_Membership_Email extends \WC_Email {


	/** @var string the email content body */
	protected $body = '';

	/** @var int default schedule (optional, used by some emails) */
	protected $default_schedule = 0;

	/** @var string rescheduling events action description (optional, used by some emails) */
	protected $reschedule_description = '';


	/**
	 * Checks if it's a customer email.
	 *
	 * @since 1.7.0
	 *
	 * @return true overrides parent method to always return true
	 */
	public function is_customer_email() {
		return true;
	}


	/**
	 * Parses the email's body merge tags.
	 *
	 * @since 1.7.0
	 */
	protected function parse_merge_tags() {

		if ( ! $this->object instanceof \WC_Memberships_User_Membership ) {
			return;
		}

		$user_membership = $this->object;

		// get member data
		$member            = get_user_by( 'id', $user_membership->get_user_id() );
		$member_name       = ! empty( $member->display_name ) ? $member->display_name : '';
		$member_first_name = ! empty( $member->first_name )   ? $member->first_name   : $member_name;
		$member_last_name  = ! empty( $member->last_name )    ? $member->last_name    : '';
		$member_full_name  = $member_first_name && $member_last_name ? $member_first_name . ' ' . $member->last_name : $member_name;

		// membership expiry date
		$expiration_date_timestamp = $user_membership->get_local_end_date( 'timestamp' );

		// placeholders
		$email_merge_tags = array(
			'member_name'                 => $member_name,
			'member_first_name'           => $member_first_name,
			'member_last_name'            => $member_last_name,
			'member_full_name'            => $member_full_name,
			'membership_plan'             => $user_membership->get_plan() ? $user_membership->get_plan()->get_name() : '',
			'membership_expiration_date'  => date_i18n( wc_date_format(), $expiration_date_timestamp ),
			'membership_expiry_time_diff' => human_time_diff( current_time( 'timestamp', true ), $expiration_date_timestamp ),
			'membership_renewal_url'      => esc_url( $user_membership->get_renew_membership_url() ),
		);

		if ( Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte( '3.2' ) ) {

			foreach ( $email_merge_tags as $find => $replace ) {
				$this->placeholders[ '{' . $find . '}' ] = $replace;
			}

		} else {

			foreach ( $email_merge_tags as $find => $replace ) {
				$this->find[ $find ]    = '{' . $find . '}';
				$this->replace[ $find ] = $replace;
			}
		}
	}


	/**
	 * Returns the email default body content.
	 *
	 * This method should be overridden by child classes.
	 *
	 * @since 1.7.0
	 *
	 * @return string HTML
	 */
	public function get_default_body() {
		return '';
	}


	/**
	 * Returns the email body content.
	 *
	 * @since 1.7.0
	 *
	 * @return string HTML
	 */
	public function get_body() {

		$email_id = strtolower( $this->id );

		/**
		 * Filters the membership email body.
		 *
		 * @since 1.7.0
		 *
		 * @param string $body email body content
		 * @param \WC_Memberships_User_Membership_Email current email instance
		 */
		$body = (string) apply_filters( "{$email_id}_email_body", $this->format_string( $this->body ), $this->object );

		if ( empty( $body ) || ! is_string( $body ) || '' === trim( $body ) ) {
			$body = $this->get_default_body();
		}

		// convert relative URLs to absolute for links href and images src attributes
		$domain  = get_home_url();
		$replace = array();
		$replace['/href="(?!https?:\/\/)(?!data:)(?!#)/'] = 'href="' . $domain;
		$replace['/src="(?!https?:\/\/)(?!data:)(?!#)/']  = 'src="'  . $domain;

		$body = preg_replace( array_keys( $replace ), array_values( $replace ), $body );

		return $body;
	}


	/**
	 * Enables the email.
	 *
	 * @since 1.11.0
	 */
	public function enable() {

		$email_settings = get_option( "woocommerce_{$this->id}_settings", array() );

		$email_settings['enabled'] = 'yes';

		update_option( "woocommerce_{$this->id}_settings", $email_settings );

		$this->enabled = true;
	}


	/**
	 * Disables the email.
	 *
	 * @since 1.11.0
	 */
	public function disable() {

		$email_settings = get_option( "woocommerce_{$this->id}_settings", array() );

		$email_settings['enabled'] = 'no';

		update_option( "woocommerce_{$this->id}_settings", $email_settings );

		$this->enabled = false;
	}


	/**
	 * Returns the email schedule, in days (if applicable).
	 *
	 * @since 1.11.0
	 *
	 * @return int number of days (minimum 1)
	 */
	public function get_schedule() {

		$schedule = null;

		if ( ! empty( $this->schedule_name ) && is_string( $this->schedule_name ) ) {

			$email_settings = get_option( "woocommerce_{$this->id}_settings", array() );
			$schedule       = ! isset( $email_settings[ $this->schedule_name ] ) || ! is_numeric( $email_settings[ $this->schedule_name ] ) ? $this->default_schedule : $email_settings[ $this->schedule_name ];
		}

		return max( 1, (int) $schedule );
	}


	/**
	 * Sets the email schedule (if applicable).
	 *
	 * @since 1.11.0
	 *
	 * @param null|int $days number of days to set the schedule (cannot be lower than 1)
	 * @return false|int returns false upon failure or gives the number of days set upon success
	 */
	public function set_schedule( $days = null ) {

		$success  = false;
		$schedule = null === $days ? $this->default_schedule : $days;

		if ( ! empty( $this->schedule_name ) && is_string( $this->schedule_name ) && is_numeric( $schedule ) ) {

			$schedule       = max( 1, absint( (int) $schedule ) );
			$email_settings = get_option( "woocommerce_{$this->id}_settings", array() );

			$email_settings[ $this->schedule_name ] = $schedule;

			if ( update_option( "woocommerce_{$this->id}_settings", $email_settings ) ) {
				$success = $schedule;
			}
		}

		return $success;
	}


}
