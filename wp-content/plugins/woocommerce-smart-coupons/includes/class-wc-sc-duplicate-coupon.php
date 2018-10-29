<?php
/**
 * Duplication of coupon feature
 *
 * @author      StoreApps
 * @since       3.3.0
 * @version     1.0
 * @package     WooCommerce Smart Coupons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SC_Duplicate_Coupon' ) ) {

	/**
	 * Class for handling duplication of coupon
	 */
	class WC_SC_Duplicate_Coupon {

		/**
		 * Variable to hold instance of WC_SC_Duplicate_Coupon
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'post_row_actions', array( $this, 'woocommerce_duplicate_coupon_link_row' ), 1, 2 );
			add_action( 'admin_action_duplicate_coupon', array( $this, 'woocommerce_duplicate_coupon_action' ) );

		}

		/**
		 * Handle call to functions which is not available in this class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
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
		 * Get single instance of WC_SC_Duplicate_Coupon
		 *
		 * @return WC_SC_Duplicate_Coupon Singleton object of WC_SC_Duplicate_Coupon
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Funtion to add "duplicate" action for coupons
		 *
		 * @param array   $actions Array of existing actions.
		 * @param WP_Post $post Post object.
		 * @return array  $actions including duplicate action of coupons
		 */
		public function woocommerce_duplicate_coupon_link_row( $actions, $post ) {
			if ( function_exists( 'duplicate_post_plugin_activation' ) ) {
				return $actions;
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return $actions;
			}

			if ( 'shop_coupon' !== $post->post_type ) {
				return $actions;
			}

			$actions['duplicate'] = '<a href="' . wp_nonce_url( admin_url( 'admin.php?action=duplicate_coupon&amp;post=' . $post->ID ), 'woocommerce-duplicate-coupon_' . $post->ID ) . '" title="' . __( 'Make a duplicate from this coupon', 'woocommerce-smart-coupons' )
			. '" rel="permalink">' . __( 'Duplicate', 'woocommerce-smart-coupons' ) . '</a>';

			return $actions;
		}

		/**
		 * Function to insert post meta values for duplicate coupon
		 *
		 * @param int $id ID of parent coupon.
		 * @param int $new_id ID of duplicated coupon.
		 */
		public function woocommerce_duplicate_coupon_post_meta( $id, $new_id ) {
			global $wpdb;

			$meta_keys = array( 'expiry_date', 'usage_count', '_used_by' );

			$how_many     = count( $meta_keys );
			$placeholders = array_fill( 0, $how_many, '%s' );

			$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d AND meta_key NOT IN ( " . implode( ',', $placeholders ) . ' )', array_merge( array( $id ), $meta_keys ) ) ); // WPCS: cache ok, db call ok, unprepared SQL ok.

			if ( 0 !== count( $post_meta_infos ) ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ( $post_meta_infos as $meta_info ) {
						$meta_key        = $meta_info->meta_key;
						$meta_value      = $meta_info->meta_value;
						$sql_query_sel[] = $wpdb->prepare( 'SELECT %d, %s, %s', $new_id, $meta_key, $meta_value );
				}
				$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
				$wpdb->query( $sql_query ); // WPCS: cache ok, db call ok, unprepared SQL ok.
			}
		}


		/**
		 * Function to duplicate post taxonomies for the duplicate coupon
		 *
		 * @param int    $id ID of parent coupon.
		 * @param int    $new_id ID of duplicated coupon.
		 * @param string $post_type Post type being duplicated.
		 */
		public function woocommerce_duplicate_coupon_post_taxonomies( $id, $new_id, $post_type ) {
			global $wpdb;
			$taxonomies = get_object_taxonomies( $post_type );
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms       = wp_get_object_terms( $id, $taxonomy );
				$post_terms_count = count( $post_terms );
				for ( $i = 0; $i < $post_terms_count; $i++ ) {
						wp_set_object_terms( $new_id, $post_terms[ $i ]->slug, $taxonomy, true );
				}
			}
		}

		/**
		 * Function to create duplicate coupon and copy all properties of the coupon to duplicate coupon
		 *
		 * @param WP_Post $post Post object.
		 * @param int     $parent Post parent ID.
		 * @param string  $post_status Post status.
		 * @return int $new_post_id
		 */
		public function woocommerce_create_duplicate_from_coupon( $post, $parent = 0, $post_status = '' ) {
			global $wpdb;

			$new_post_author   = wp_get_current_user();
			$new_post_date     = current_time( 'mysql' );
			$new_post_date_gmt = get_gmt_from_date( $new_post_date );

			if ( $parent > 0 ) {
				$post_parent = $parent;
				$post_status = $post_status ? $post_status : 'publish';
				$suffix      = '';
			} else {
				$post_parent = $post->post_parent;
				$post_status = $post_status ? $post_status : 'draft';
				$suffix      = __( '(Copy)', 'woocommerce-smart-coupons' );
			}

				$new_post_type         = $post->post_type;
				$post_content          = str_replace( "'", "''", $post->post_content );
				$post_content_filtered = str_replace( "'", "''", $post->post_content_filtered );
				$post_excerpt          = str_replace( "'", "''", $post->post_excerpt );
				$post_title            = strtolower( str_replace( "'", "''", $post->post_title ) . $suffix );
				$post_name             = str_replace( "'", "''", $post->post_name );
				$comment_status        = str_replace( "'", "''", $post->comment_status );
				$ping_status           = str_replace( "'", "''", $post->ping_status );

				$wpdb->insert(
					$wpdb->posts,
					array(
						'post_author'           => $new_post_author->ID,
						'post_date'             => $new_post_date,
						'post_date_gmt'         => $new_post_date_gmt,
						'post_content'          => $post_content,
						'post_content_filtered' => $post_content_filtered,
						'post_title'            => $post_title,
						'post_excerpt'          => $post_excerpt,
						'post_status'           => $post_status,
						'post_type'             => $new_post_type,
						'comment_status'        => $comment_status,
						'ping_status'           => $ping_status,
						'post_password'         => $post->post_password,
						'to_ping'               => $post->to_ping,
						'pinged'                => $post->pinged,
						'post_modified'         => $new_post_date,
						'post_modified_gmt'     => $new_post_date_gmt,
						'post_parent'           => $post_parent,
						'menu_order'            => $post->menu_order,
						'post_mime_type'        => $post->post_mime_type,
					)
				); // WPCS: db call ok.

				$new_post_id = $wpdb->insert_id;

				$this->woocommerce_duplicate_coupon_post_taxonomies( $post->ID, $new_post_id, $post->post_type );

				$this->woocommerce_duplicate_coupon_post_meta( $post->ID, $new_post_id );

				return $new_post_id;
		}

		/**
		 * Function to return post id of the duplicate coupon to be created
		 *
		 * @param int $id ID of the coupon to duplicate.
		 * @return object $post Duplicated post object.
		 */
		public function woocommerce_get_coupon_to_duplicate( $id ) {
			global $wpdb;
				$post = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID=%d", $id ) ); // WPCS: cache ok, db call ok.
			if ( isset( $post->post_type ) && 'revision' === $post->post_type ) {
				$id   = $post->post_parent;
				$post = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID=%d", $id ) ); // WPCS: cache ok, db call ok.
			}
				return $post[0];
		}

		/**
		 * Function to validate condition and create duplicate coupon
		 */
		public function woocommerce_duplicate_coupon() {
			if ( empty( $_REQUEST['post'] ) || ( isset( $_REQUEST['action'] ) && 'duplicate_post_save_as_new_page' === $_REQUEST['action'] ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'No coupon to duplicate has been supplied!', 'woocommerce-smart-coupons' ) );
			}

			// Get the original page.
			$id = absint( $_REQUEST['post'] ); // WPCS: input var ok.

			check_admin_referer( 'woocommerce-duplicate-coupon_' . $id );

			$post = $this->woocommerce_get_coupon_to_duplicate( $id );

			if ( isset( $post ) && null !== $post ) {
				$new_id = $this->woocommerce_create_duplicate_from_coupon( $post );

				// If you have written a plugin which uses non-WP database tables to save
				// information about a page you can hook this action to dupe that data.
				do_action( 'woocommerce_duplicate_coupon', $new_id, $post );

				// Redirect to the edit screen for the new draft page.
				wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
				exit;
			} else {
				/* translators: %d: Post ID */
				wp_die( sprintf( esc_html__( 'Coupon creation failed, could not find original product: %d', 'woocommerce-smart-coupons' ), esc_html( $id ) ) );
			}
		}

		/**
		 * Function to call function to create duplicate coupon
		 */
		public function woocommerce_duplicate_coupon_action() {
			$this->woocommerce_duplicate_coupon();
		}
	}
}

WC_SC_Duplicate_Coupon::get_instance();
