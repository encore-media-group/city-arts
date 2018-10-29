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

namespace SkyVerge\WooCommerce\Memberships\API;

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Memberships REST API controller.
 *
 * @since 1.11.0
 */
class Controller extends \WC_REST_Posts_Controller {


	/** @var string REST API version supported by the controller, e.g. v1, v2... */
	protected $version = 'v1';


	/**
	 * Memberships object REST API controller constructor.
	 *
	 * @since 1.11.0
	 */
	public function __construct() {

		$this->public = false;
	}


	/**
	 * Returns the version of the REST API supported.
	 *
	 * @since 1.11.0
	 *
	 * @return string
	 */
	public function get_version() {

		return $this->version;
	}


	/**
	 * Returns the full REST namespace used by the controller.
	 *
	 * @since 1.11.0
	 *
	 * @return string
	 */
	public function get_namespace() {

		return $this->namespace;
	}


	/**
	 * Returns the current WooCommerce root namespace.
	 *
	 * @since 1.11.0
	 *
	 * @return string
	 */
	protected function get_woocommerce_namespace() {

		return "wc/{$this->version}";
	}


	/**
	 * Returns the REST base appended to the namespace.
	 *
	 * @since 1.11.0
	 *
	 * @return string
	 */
	public function get_rest_base() {

		return $this->rest_base;
	}


	/**
	 * Returns the related post type.
	 *
	 * @since 1.11.0
	 *
	 * @return string
	 */
	public function get_post_type() {

		return $this->post_type;
	}


	/**
	 * Prepares a collection with paginated results.
	 *
	 * @since 1.11.0
	 *
	 * @param \WP_REST_Request $request
	 * @param array $collection collection of response objects
	 * @param \WP_Query $posts_query query results
	 * @param array $query_args
	 * @return \WP_REST_Response
	 */
	protected function prepare_response_collection_paginated( $request, $collection, $posts_query, $query_args ) {

		$page        = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {

			unset( $query_args['paged'] );

			$count_query = new \WP_Query();

			$count_query->query( $query_args );

			$total_posts = $count_query->found_posts;
		}

		$max_pages = (int) ceil( $total_posts / (int) $query_args['posts_per_page'] );
		$response  = rest_ensure_response( $collection );

		$response->header( 'X-WP-Total',      $total_posts );
		$response->header( 'X-WP-TotalPages', $max_pages );

		$request_params = $request->get_query_params();

		if ( ! empty( $request_params['filter'] ) ) {
			unset( $request_params['filter']['posts_per_page'], $request_params['filter']['paged'] );
		}

		$base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {

			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );

			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $page ) {

			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}


	/**
	 * Gets a memberships response item for REST API consumption.
	 *
	 * @see \WC_REST_Posts_Controller::get_item()
	 *
	 * @since 1.11.0
	 *
	 * @param \WP_REST_Request $request request object
	 * @return array|\WP_Error|\WP_REST_Response response object
	 */
	public function get_item( $request ) {

		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( ! $post || $this->post_type !== $post->post_type ) {

			if ( false === $post->post_type ) {
				$error_message = __( 'Invalid ID.', 'woocommerce-memberships' );
			} else {
				/* translators: Placeholder: %d - post ID */
				$error_message = sprintf( __( 'Object with ID %d is not a valid memberships object.', 'woocommerce-memberships' ), (int) $post->ID );
			}

			$response = new \WP_Error( "woocommerce_rest_invalid_{$this->post_type}_id", $error_message, array( 'status' => 404 ) );

		} else {

			$response = $this->prepare_item_for_response( $post, $request );
		}

		return $response;
	}


	/**
	 * Prepares memberships object meta data for a response item.
	 *
	 * @since 1.11.0
	 *
	 * @param \WC_Memberships_User_Membership|\WC_Memberships_Membership_Plan $object membership object
	 * @return array associative array of formatted meta data
	 */
	protected function prepare_item_meta_data( $object ) {
		global $wpdb;

		$formatted = array();
		$raw_meta  = $wpdb->get_results( $wpdb->prepare("
			SELECT * FROM $wpdb->postmeta 
			WHERE post_id  = %d
		", $object->get_id() ) );

		if ( ! empty( $raw_meta ) ) {

			$post_type        = $this->get_post_type();
			$wp_internal_keys = array(
				'_edit_lock',
				'_edit_last',
				'_wp_old_slug',
			);

			if ( 'wc_membership_plan' === $post_type ) {
				$object_name = 'membership_plan';
			} elseif ( 'wc_user_membership' === $post_type ) {
				$object_name = 'user_membership';
			} else {
				$object_name = $post_type;
			}

			/**
			 * Filters the list of meta data keys to exclude from REST API responses.
			 *
			 * @since 1.11.0
			 *
			 * @param array $excluded_keys keys to exclude from memberships item meta data list
			 * @param \WC_Memberships_User_Membership|\WC_Memberships_Membership_Plan $object memberships object
			 */
			$excluded_keys = apply_filters( "wc_memberships_rest_api_{$object_name}_excluded_meta_keys", array_merge( $object->get_meta_keys(), $wp_internal_keys ), $object );

			foreach( $raw_meta as $meta_object ) {

				if ( empty( $excluded_keys ) || ! in_array( $meta_object->meta_key, $excluded_keys, true ) ) {

					$formatted[] = array(
						'id'    => (int) $meta_object->meta_id,
						'key'   => (string) $meta_object->meta_key,
						'value' => $meta_object->meta_value,
					);
				}
			}
		}

		return $formatted;
	}


}
