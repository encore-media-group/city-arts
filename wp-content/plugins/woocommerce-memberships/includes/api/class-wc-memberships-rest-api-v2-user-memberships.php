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

namespace SkyVerge\WooCommerce\Memberships\API\v2;

use SkyVerge\WooCommerce\Memberships\API\Controller;
use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * User Memberships REST API handler.
 *
 * @since 1.11.0
 */
class User_Memberships extends Controller {


	/**
	 * User Memberships REST API constructor.
	 *
	 * @since 1.11.0
	 */
	public function __construct() {

		parent::__construct();

		$this->version   = 'v2';
		$this->namespace = 'wc/v2/memberships';
		$this->rest_base = 'members';
		$this->post_type = 'wc_user_membership';
	}


	/**
	 * Registers user memberships WP REST API routes.
	 *
	 * @see \SkyVerge\WooCommerce\Memberships\REST_API::register_routes()
	 *
	 * @since 1.11.0
	 */
	public function register_routes() {

		// endpoint: 'wc/v2/memberships/members/' => list all user memberships
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		), true );

		// endpoint: 'wc/v2/memberships/members/<id>' => get a specific user membership
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args'   => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'woocommerce-memberships' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		), true );
	}


	/**
	 * Returns the available query parameters for collections.
	 *
	 * @since 1.11.0
	 *
	 * @return array associative array
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		unset( $params['order'], $params['orderby'], $params['before'], $params['after'] );

		$params['status'] = array(
			'default'           => 'any',
			'description'       => __( 'Limit results to user memberships of a specific status.', 'woocommerce-memberships' ),
			'type'              => 'string',
			'enum'              => array_merge( array( 'any' ), wc_memberships_get_user_membership_statuses( false, false ) ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['plan'] = array(
			'description'       => __( 'Limit results to user memberships for a specific plan (matched by ID or slug).', 'woocommerce-memberships' ),
			'type'              => 'mixed',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['customer'] = array(
			'description'       => __( 'Limit results to user memberships belonging to a specific customer (matched by ID, login name or email address).', 'woocommerce-memberships' ),
			'type'              => 'mixed',
			'sanitize_callback' => 'strval',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['order'] = array(
			'description'       => __( 'Limit results to user memberships related to a specific order (matched by ID).', 'woocommerce-memberships' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['product'] = array(
			'description'       => __( 'Limit results to user memberships granted after the purchase of a specific product (matched by ID).', 'woocommerce-memberships' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		/**
		 * Filters the user membership collection params for REST API queries.
		 *
		 * @since 1.11.0
		 *
		 * @param array $params associative array
		 */
		return (array) apply_filters( 'wc_memberships_rest_api_user_memberships_collection_params', $params );
	}


	/**
	 * Prepares query args for items collection query.
	 *
	 * @since 1.11.0
	 *
	 * @param array|\WP_REST_Request $request request object (with array access)
	 * @return array
	 */
	private function prepare_items_query_args( $request ) {

		// query args defaults
		$query_args = array(
			'post_type'           => $this->post_type,
			'offset'              => $request['offset'],
			'paged'               => $request['page'],
			'post__in'            => $request['include'],
			'post__not_in'        => $request['exclude'],
			'posts_per_page'      => $request['per_page'],
			'post_parent__in'     => $request['parent'],
			'post_parent__not_in' => $request['parent_exclude'],
		);

		// filter by status (default: any status)
		if ( 'any' !== $request['status'] ) {
			$query_args['post_status'] = Framework\SV_WC_Helper::str_starts_with( $query_args['post_status'], 'wcm-' ) ? $query_args['post_status'] : 'wcm-' . $request['status'];
		} else {
			$query_args['post_status'] = 'any';
		}

		// filter by plan
		if ( isset( $request['plan'] ) ) {

			if ( is_numeric( $request['plan'] ) ) {
				$plan_id = (int) $request['plan'];
			} elseif ( is_string( $request['plan'] ) && ( $plan = wc_memberships_get_membership_plan( $request['plan'] ) ) ) {
				$plan_id = $plan->get_id();
			} elseif( is_array( $request['plan'] ) ) {
				$plan_id = array_unique( array_map( 'absint', $request['plan'] ) );
			} else {
				$plan_id = 0;
			}

			if ( is_array( $plan_id ) ) {
				$query_args['post_parent__in'] = $plan_id;
			}  else {
				$query_args['post_parent'] = $plan_id;
			}
		}

		// filter by customer
		if ( isset( $request['customer'] ) ) {

			if ( is_numeric( $request['customer'] ) ) {
				$customer_id = (int) $request['customer'];
			} elseif ( is_email( $request['customer'] )  && ( $customer = get_user_by( 'email', $request['customer'] ) ) ) {
				$customer_id = (int) $customer->ID;
			} elseif ( is_string( $request['customer'] ) && ( $customer = get_user_by( 'login', $request['customer'] ) ) ) {
				$customer_id = (int) $customer->ID;
			} else {
				$customer_id = 0;
			}

			$query_args['author'] = $customer_id;
		}

		// filter by order
		if ( isset( $request['order'] ) ) {

			if ( ! isset( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$query_args['meta_query'][] = array(
				'key'   => '_order_id',
				'value' => (int) $request['order'],
				'type'  => 'numeric',
			);
		}

		// filter by product
		if ( isset( $request['product'] ) ) {

			if ( ! isset( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$query_args['meta_query'][] = array(
				'key'   => '_product_id',
				'value' => (int) $request['product'],
				'type'  => 'numeric',
			);
		}

		if ( isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) && count( $query_args['meta_query'] ) > 1 ) {
			$query_args['meta_query']['relation'] = 'AND';
		}

		/**
		 * Filters the WP API query arguments for user memberships.
		 *
		 * This filter's name follows the WooCommerce core pattern.
		 * @see \WC_REST_Posts_Controller::get_items()
		 *
		 * @since 1.11.0
		 *
		 * @param array $args associative array of query args
		 * @param \WP_REST_Request $request request object
		 */
		return (array) apply_filters( 'woocommerce_rest_wc_user_memberships_query_args', $query_args, $request );
	}


	/**
	 * Gets a collection of User Membership items.
	 *
	 * @see \WC_REST_Posts_Controller::get_items()
	 *
	 * @since 1.11.0
	 *
	 * @param \WP_REST_Request $request request object
	 * @return \WP_REST_Response response object
	 */
	public function get_items( $request ) {

		$collection  = array();
		$query_args  = $this->prepare_items_query_args( $request );
		$posts_query = new \WP_Query( $this->prepare_items_query( $query_args, $request ) );

		if ( ! empty( $posts_query->posts ) ) {

			foreach ( $posts_query->posts as $post ) {

				if ( ! wc_rest_check_post_permissions( $this->post_type, 'read', $post->ID ) ) {
					continue;
				}

				if ( $user_membership = wc_memberships_get_user_membership( $post ) ) {

					$response_data = $this->prepare_item_for_response( $user_membership, $request );
					$collection[]  = $this->prepare_response_for_collection( $response_data );
				}
			}
		}

		return $this->prepare_response_collection_paginated( $request, $collection, $posts_query, $query_args );
	}


	/**
	 * Returns user membership data for API responses.
	 *
	 * @since 1.11.0
	 *
	 * @param null|int|\WP_Post|\WC_Memberships_User_Membership $user_membership user membership
	 * @param null|\WP_REST_Response optional response object
	 * @return array associative array of data
	 */
	public function get_formatted_item_data( $user_membership, $request = null ) {

		if ( is_numeric( $user_membership ) || $user_membership instanceof \WP_Post ) {
			$user_membership = wc_memberships_get_user_membership( $user_membership );
		}

		if ( $user_membership instanceof \WC_Memberships_User_Membership ) {

			$order   = $user_membership->get_order();
			$product = $user_membership->get_product( true );
			$data    = array(
				'id'                 => $user_membership->get_id(),
				'customer_id'        => $user_membership->get_user_id(),
				'plan_id'            => $user_membership->get_plan_id(),
				'status'             => $user_membership->get_status(),
				'order_id'           => $order ? Framework\SV_WC_Order_Compatibility::get_prop( $order, 'id' ) : null,
				'product_id'         => $product ? $product->get_id() : null,
				'date_created'       => wc_memberships_format_date( $user_membership->post->post_date, DATE_ATOM ),
				'date_created_gmt'   => wc_memberships_format_date( $user_membership->post->post_date_gmt, DATE_ATOM ),
				'start_date'         => $user_membership->get_local_start_date( DATE_ATOM ),
				'start_date_gmt'     => $user_membership->get_start_date( DATE_ATOM ),
				'end_date'           => $user_membership->get_local_end_date( DATE_ATOM ),
				'end_date_gmt'       => $user_membership->get_end_date( DATE_ATOM ),
				'paused_date'        => $user_membership->get_local_paused_date( DATE_ATOM ),
				'paused_date_gmt'    => $user_membership->get_paused_date( DATE_ATOM ),
				'cancelled_date'     => $user_membership->get_local_cancelled_date( DATE_ATOM ),
				'cancelled_date_gmt' => $user_membership->get_cancelled_date( DATE_ATOM ),
				'view_url'           => $user_membership->get_view_membership_url(),
				'meta_data'          => $this->prepare_item_meta_data( $user_membership ),
			);

		} else {

			$data            = array();
			$user_membership = null;
		}

		if ( $request ) {

			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
			$fields  = $this->add_additional_fields_to_object( $data, $request );
			$data    = $this->filter_response_by_context( $fields, $context );
		}

		/**
		 * Filters the user membership data for the REST API.
		 *
		 * @since 1.11.0
		 *
		 * @param array $data associative array of membership data
		 * @param null|\WC_Memberships_User_Membership $user_membership membership object or null if undetermined
		 * @param null|\WP_REST_Request optional request object
		 */
		return (array) apply_filters( 'wc_memberships_rest_api_user_membership_data', $data, $user_membership, $request );
	}


	/**
	 * Prepares an individual User Membership object data for API response.
	 *
	 * @since 1.11.0
	 *
	 * @param int|\WP_Post|\WC_Memberships_User_Membership $user_membership user membership object, ID or post object
	 * @param null|\WP_REST_Request $request WP API request, optional
	 * @return \WP_REST_Response response data
	 */
	public function prepare_item_for_response( $user_membership, $request = null ) {

		if ( is_numeric( $user_membership ) || $user_membership instanceof \WP_Post ) {
			$user_membership = wc_memberships_get_user_membership( $user_membership );
		}

		// build the response
		$response = rest_ensure_response( $this->get_formatted_item_data( $user_membership, $request ) );

		// add additional links to the response
		$response->add_links( $this->prepare_links( $user_membership, $request ) );

		/**
		 * Filters the data for a response.
		 *
		 * This filter's name follows the WooCommerce core pattern.
		 * @see \WC_REST_Posts_Controller::prepare_item_for_response()
		 *
		 * @since 1.11.0
		 *
		 * @param \WP_REST_Response $response the response object
		 * @param null|\WP_Post $post the user membership post object
		 * @param \WP_REST_Request $request the request object
		 */
		return apply_filters( 'woocommerce_rest_prepare_wc_membership_plan', $response, $user_membership ? $user_membership->post : null, $request );
	}


	/**
	 * Prepares links to be added to user membership objects.
	 *
	 * @since 1.11.0
	 *
	 * @param \WC_Memberships_User_Membership $user_membership user membership object
	 * @param \WP_REST_Request $request WP API request
	 * @return array associative array
	 */
	protected function prepare_links( $user_membership, $request ) {

		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $user_membership->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
			'customer'   => array(
				'href' => rest_url( sprintf( '/%s/customers/%d', $this->get_woocommerce_namespace(), $user_membership->get_user_id() ) ),
			),
		);

		// an order may not be associated to a membership
		if ( $order = $user_membership->get_order() ) {
			$links['order'] = array(
				'href' => rest_url( sprintf( '/%s/orders/%d', $this->get_woocommerce_namespace(), Framework\SV_WC_Order_Compatibility::get_prop( $order, 'id' ) ) ),
			);
		}

		// likewise, a product might not be present
		if ( $product = $user_membership->get_product( true ) ) {
			$links['product'] = array(
				'href' => rest_url( sprintf( '/%s/products/%d', $this->namespace, $product->get_id() ) ),
			);
		}

		/**
		 * Filters the user membership item's links for WP API output.
		 *
		 * @since 1.11.0
		 *
		 * @param array $links associative array
		 * @param \WC_Memberships_User_Membership $user_membership membership object
		 * @param null|\WP_REST_Request $request WP API request
		 * @param \SkyVerge\WooCommerce\Memberships\API\v2\User_Memberships handler instance
		 */
		return (array) apply_filters( 'wc_memberships_rest_api_user_membership_links', $links, $user_membership, $request, $this );
	}


	/**
	 * Returns the user membership REST API schema.
	 *
	 * @since 1.11.0
	 *
	 * @return array
	 */
	public function get_item_schema() {

		/**
		 * Filters the WP API user membership schema.
		 *
		 * @since 1.11.0
		 *
		 * @param array associative array
		 */
		$schema = (array) apply_filters( 'wc_memberships_rest_api_user_membership_schema', array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id'                 => array(
					'description' => __( 'Unique identifier of the user membership.', 'woocommerce-memberships' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'customer_id'        => array(
					'description' => __( 'Unique identifier of the user the membership belongs to.', 'woocommerce-memberships' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'plan_id'            => array(
					'description' => __( 'Unique identifier of the plan the user membership grants access to.', 'woocommerce-memberships' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'status'             => array(
					'description' => __( 'User membership status.', 'woocommerce-membership' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'order_id'           => array(
					'description' => __( 'Unique identifier of the Order that granted the user membership (optional).', 'woocommerce-memberships' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'product_id'         => array(
					'description' => __( 'Unique identifier of the purchased Product, or its Variation, that granted access.', 'woocommerce-memberships' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'date_created'       => array(
					'description' => __( 'The date when the user membership was created, in the site timezone.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_created_gmt'   => array(
					'description' => __( 'The date when the user membership was created, in UTC.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'start_date'         => array(
					'description' => __( 'The date when the user membership started being active, in the site timezone.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'start_date_gmt'     => array(
					'description' => __( 'The date when the user membership started being active, in UTC.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'end_date'           => array(
					'description' => __( 'The date when the user membership ended, in the site timezone.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'end_date_gmt'       => array(
					'description' => __( 'The date when the user membership ended, in UTC.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'paused_date'        => array(
					'description' => __( 'The date when the user membership was last paused, in the site timezone.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'paused_date_gmt'    => array(
					'description' => __( 'The date when the user membership was last paused, in UTC.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'cancelled_date'     => array(
					'description' => __( 'The date when the user membership was cancelled, in the site timezone.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'cancelled_date_gmt' => array(
					'description' => __( 'The date when the user membership was cancelled, in UTC.', 'woocommerce-memberships' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'view_url'           => array(
					'description' => __( 'The URL pointing to the Members Area to view the membership.', 'woocommerce-memberships' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'meta_data'          => array(
					'description' => __( 'User membership additional meta data.', 'woocommerce-memberships' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'     => array(
								'description' => __( 'Meta ID.', 'woocommerce-memberships' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'key'    => array(
								'description' => __( 'Meta key.', 'woocommerce-memberships' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'value'  => array(
								'description' => __( 'Meta value.', 'woocommerce-memberships' ),
								'type'        => 'mixed',
								'context'     => array( 'view', 'edit' ),
							),
						),
					),
				),
			),
		) );

		return $this->add_additional_fields_schema( $schema );
	}


}
