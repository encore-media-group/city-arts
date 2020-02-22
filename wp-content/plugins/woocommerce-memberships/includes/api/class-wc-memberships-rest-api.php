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

namespace SkyVerge\WooCommerce\Memberships;

use SkyVerge\WooCommerce\Memberships\API\v2\Membership_Plans;
use SkyVerge\WooCommerce\Memberships\API\v2\User_Memberships;
use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Memberships main REST API handler.
 *
 * @since 1.11.0
 *
 * @method \WC_Memberships get_plugin()
 */
class REST_API extends Framework\REST_API {


	/** @var \SkyVerge\WooCommerce\Memberships\API\v2\Membership_Plans instance */
	private $membership_plans_v2;

	/** @var \SkyVerge\WooCommerce\Memberships\API\v2\User_Memberships instance */
	private $user_memberships_v2;

	/** @var \SkyVerge\WooCommerce\Memberships\API\Webhooks instance */
	private $webhooks;


	/**
	 * Extends the WP REST API.
	 *
	 * @since 1.11.0
	 *
	 * @param \WC_Memberships $plugin main plugin class instance
	 */
	public function __construct( $plugin ) {

		parent::__construct( $plugin );

		$this->webhooks = $this->get_plugin()->load_class( '/includes/api/class-wc-memberships-webhooks.php', '\SkyVerge\WooCommerce\Memberships\API\Webhooks' );
	}


	/**
	 * Adds new routes to the WP REST API.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	public function register_routes() {

		if ( $membership_plans_api = $this->get_membership_plans() ) {
			$membership_plans_api->register_routes();
		}

		if ( $user_memberships_api = $this->get_user_memberships() ) {
			$user_memberships_api->register_routes();
		}
	}


	/**
	 * Returns the User Memberships REST API handler instance.
	 *
	 * @since 1.11.0
	 *
	 * @param string $version optional API version handler to get (gets latest)
	 * @return null|\SkyVerge\WooCommerce\Memberships\API\v2\User_Memberships
	 */
	public function get_user_memberships( $version = 'v2' ) {

		// abstract controller
		require_once( $this->get_plugin()->get_plugin_path() . '/includes/api/class-wc-memberships-rest-api-controller.php' );

		$user_memberships_api = null;

		if ( 'v2' === strtolower( $version ) ) {

			if ( ! $this->user_memberships_v2 instanceof \User_Memberships ) {
				$this->user_memberships_v2 = $this->get_plugin()->load_class( '/includes/api/class-wc-memberships-rest-api-v2-user-memberships.php', '\SkyVerge\WooCommerce\Memberships\API\V2\User_Memberships' );
			}

			$user_memberships_api = $this->user_memberships_v2;
		}

		return $user_memberships_api;
	}


	/**
	 * Returns the Membership Plans REST API handler instance.
	 *
	 * @since 1.11.0
	 *
	 * @param string $version optional API version handler to get (gets latest by default)
	 * @return null|\SkyVerge\WooCommerce\Memberships\API\v2\Membership_Plans
	 */
	public function get_membership_plans( $version = 'v2' ) {

		// abstract controller
		require_once( $this->get_plugin()->get_plugin_path() . '/includes/api/class-wc-memberships-rest-api-controller.php' );

		$membership_plan_api = null;

		if ( 'v2' === strtolower( $version ) ) {

			if ( ! $this->membership_plans_v2 instanceof \Membership_Plans ) {
				$this->membership_plans_v2 = $this->get_plugin()->load_class( '/includes/api/class-wc-memberships-rest-api-v2-membership-plans.php', '\SkyVerge\WooCommerce\Memberships\API\V2\Membership_Plans' );
			}

			$membership_plan_api = $this->membership_plans_v2;
		}

		return $membership_plan_api;
	}


	/**
	 * Returns the webhooks handler instance.
	 *
	 * @since 1.11.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\API\Webhooks
	 */
	public function get_webhooks() {

		return $this->webhooks;
	}


}
