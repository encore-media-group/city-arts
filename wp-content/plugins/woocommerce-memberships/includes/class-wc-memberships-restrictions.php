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
 * @package   WC-Memberships/Frontend/Checkout
 * @author    SkyVerge
 * @category  Frontend
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * General handler of restrictions settings.
 *
 * Initializes restrictions in the frontend and provides an API to fetch and adjust restriction settings.
 *
 * @since 1.1.0
 */
class WC_Memberships_Restrictions {


	/** @var string the restriction mode option key */
	private $restriction_mode_option = 'wc_memberships_restriction_mode';

	/** @var string the restriction mode as per plugin settings */
	private $restriction_mode;

	/** @var string the redirect page ID option key */
	private $redirect_page_id_option = 'wc_memberships_redirect_page_id';

	/** @var int the ID of the page to redirect to, when redirection mode is enabled */
	private $redirect_page_id;

	/** @var string hiding restricted products option key */
	private $hide_restricted_products_option = 'wc_memberships_hide_restricted_products';

	/** @var bool whether we are hiding completely products from catalog & search, based on setting. */
	private $hiding_restricted_products;

	/** @var string showing restricted content excerpts option key */
	private $show_excerpts_option = 'wc_memberships_show_excerpts';

	/** @var bool whether we are showing excerpts on restricted content, based on setting. */
	private $showing_excerpts;

	/** @var \WC_Memberships_Posts_Restrictions instance of general content restrictions handler */
	private $posts_restrictions;

	/** @var \WC_Memberships_Products_Restrictions instance of products restrictions handler */
	private $products_restrictions;

	/** @var array cached user access conditions */
	private $user_content_access_conditions = array();


	/**
	 * Initializes content restrictions settings and handlers.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		// init restriction options
		$this->restriction_mode           = $this->get_restriction_mode();
		$this->hiding_restricted_products = $this->hiding_restricted_products();
		$this->showing_excerpts           = $this->showing_excerpts();

		// load restriction handlers
		if ( ! is_admin() ) {
			$this->posts_restrictions    = $this->get_posts_restrictions_instance();
			$this->products_restrictions = $this->get_products_restrictions_instance();
		}
	}


	/**
	 * Returns the general content restrictions handler.
	 *
	 * @since 1.9.0
	 *
	 * @return null|\WC_Memberships_Posts_Restrictions
	 */
	public function get_posts_restrictions_instance() {

		if ( ! $this->posts_restrictions instanceof \WC_Memberships_Posts_Restrictions ) {
			$this->posts_restrictions = wc_memberships()->load_class( '/includes/frontend/class-wc-memberships-posts-restrictions.php', 'WC_Memberships_Posts_Restrictions' );
		}

		return $this->posts_restrictions;
	}


	/**
	 * Returns the products restrictions handler.
	 *
	 * @since 1.9.0
	 *
	 * @return null|\WC_Memberships_Products_Restrictions
	 */
	public function get_products_restrictions_instance() {

		if ( ! $this->products_restrictions instanceof \WC_Memberships_Products_Restrictions ) {
			$this->products_restrictions = wc_memberships()->load_class( '/includes/frontend/class-wc-memberships-products-restrictions.php', 'WC_Memberships_Products_Restrictions' );
		}

		return $this->products_restrictions;
	}


	/**
	 * Returns valid restriction modes.
	 *
	 * @since 1.9.0
	 *
	 * @param bool $with_labels whether to return mode keys or including their labels
	 * @return array string array or associative array
	 */
	public function get_restriction_modes( $with_labels = true ) {

		$modes = array(
			'hide'         => __( 'Hide completely',   'woocommerce-memberships' ),
			'hide_content' => __( 'Hide content only', 'woocommerce-memberships' ),
			'redirect'     => __( 'Redirect to page',  'woocommerce-memberships' ),
		);

		return false === $with_labels ? array_keys( $modes ) : $modes;
	}


	/**
	 * Returns the current restriction mode.
	 *
	 * @since 1.7.4
	 *
	 * @return string Possible values: 'hide', 'redirect', or 'hide_content' (default mode).
	 */
	public function get_restriction_mode() {

		if ( null === $this->restriction_mode ) {

			$default_mode     = 'hide_content';
			$restriction_mode = get_option( $this->restriction_mode_option, $default_mode );

			$this->restriction_mode = in_array( $restriction_mode, $this->get_restriction_modes( false ), true ) ? $restriction_mode : $default_mode;
		}

		return $this->restriction_mode;
	}


	/**
	 * Checks which restriction mode is being used.
	 *
	 * @since 1.7.4
	 *
	 * @param string|array $mode Compare with one (string) or more modes (array).
	 * @return bool
	 */
	public function is_restriction_mode( $mode ) {
		return is_array( $mode ) ? in_array( $this->get_restriction_mode(), $mode, true ) : $mode === $this->restriction_mode;
	}


	/**
	 * Sets the content restriction mode.
	 *
	 * @since 1.9.0
	 *
	 * @param string $mode
	 */
	public function set_restriction_mode( $mode ) {

		if ( array_key_exists( $mode, $this->get_restriction_modes() ) ) {

			update_option( $this->restriction_mode_option, $mode );

			$this->restriction_mode = $mode;
		}
	}


	/**
	 * Returns the redirect page ID used when in 'redirect' restriction mode.
	 *
	 * @since 1.7.4
	 *
	 * @return int
	 */
	public function get_restricted_content_redirect_page_id() {

		if ( null === $this->redirect_page_id || ! $this->redirect_page_id > 0 ) {

			$this->redirect_page_id = (int) get_option( $this->redirect_page_id_option );
		}

		return $this->redirect_page_id;
	}


	/**
	 * Checks whether a page is the page to redirect to in restricted content redirection mode.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post|int|string $page_id page object, ID or slug
	 * @return bool
	 */
	public function is_restricted_content_redirect_page( $page_id ) {

		if ( $page_id instanceof \WP_Post ) {
			$page_id  = (int) $page_id->ID;
		} elseif( is_numeric( $page_id ) ) {
			$page_id  = (int) $page_id;
		} elseif ( is_string( $page_id ) ) {
			$post_obj = get_post( $page_id );
			$page_id  = $post_obj ? (int) $post_obj->ID : null;
		}

		return is_int( $page_id ) ? $page_id > 0 && $page_id === $this->get_restricted_content_redirect_page_id() : false;
	}


	/**
	 * Sets the page to redirect to when using redirection mode.
	 *
	 * @since 1.9.0
	 *
	 * @param int|string|\WP_Post $page_id page object, slug or ID
	 * @return bool success
	 */
	public function set_restricted_content_redirect_page_id( $page_id ) {

		$success = false;

		if ( is_string( $page_id ) && ! is_numeric( $page_id ) ) {
			$page    = get_post( $page_id );
			$page_id = $page ? $page->ID : 0;
		} elseif ( $page_id instanceof \WP_Post ) {
			$page_id = $page_id->ID;
		}

		if ( is_numeric( $page_id ) && 'page' === get_post_type( $page_id ) ) {

			$success = update_option( $this->redirect_page_id_option, (int) $page_id );

			if ( $success ) {
				$this->redirect_page_id = (int) $page_id;
			}
		}

		return $success;
	}


	/**
	 * Returns a restricted content redirect URL.
	 *
	 * @since 1.9.0
	 *
	 * @param int $redirect_from_id the ID of the page, product, post or term redirecting from
	 * @param string|null $redirect_from_object_type optional for posts and pages: the type of object to redirecting from (normally: post_type or taxonomy)
	 * @param string|null $redirect_from_object_type_name optional for posts and pages: the name of the type of object redirect from (e.g. category, product, post, product_cat...)
	 * @return string URL with query arguments
	 */
	public function get_restricted_content_redirect_url( $redirect_from_id, $redirect_from_object_type = null, $redirect_from_object_type_name = null ) {

		$redirect_args               = array( 'r' => (int) $redirect_from_id );
		$restricted_content_page_id  = $this->get_restricted_content_redirect_page_id();
		$restricted_content_page_url = $restricted_content_page_id > 0 ? get_permalink( $restricted_content_page_id ) : null;

		// special handling for when My Account is used as the Redirect Page
		if ( $restricted_content_page_url && $restricted_content_page_id === (int) wc_get_page_id( 'myaccount' ) ) {
			$restricted_content_page          = get_post( $restricted_content_page_id );
			$redirect_args['wcm_redirect_to'] = $restricted_content_page && 'page' === $restricted_content_page->post_type ? 'page' : 'post';
			$redirect_args['wcm_redirect_id'] = (int) $redirect_from_id;
		// additional arguments useful when redirecting from a term archive
		} elseif ( $redirect_from_object_type && $redirect_from_object_type_name && is_string( $redirect_from_object_type ) && is_string( $redirect_from_object_type_name ) ) {
			$redirect_args['wcm_redirect_to'] = $redirect_from_object_type_name;
			$redirect_args['wcm_redirect_id'] = (int) $redirect_from_id;
		}

		return add_query_arg( $redirect_args, ! $restricted_content_page_url ? home_url() : $restricted_content_page_url );
	}


	/**
	 * Checks whether it is chosen in settings to hide restricted products from catalog and search.
	 *
	 * @see \WC_Memberships_Restrictions::showing_restricted_products()
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function showing_restricted_products() {
		return ! $this->hiding_restricted_products();
	}


	/**
	 * Checks whether it is chosen in settings to hide restricted products from catalog and search.
	 *
	 * @see \WC_Memberships_Restrictions::hiding_restricted_products()
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function hiding_restricted_products() {

		if ( null === $this->hiding_restricted_products ) {

			$this->hiding_restricted_products = 'yes' === get_option( $this->hide_restricted_products_option );
		}

		return $this->hiding_restricted_products;
	}


	/**
	 * Sets the visibility of restricted products.
	 *
	 * @since 1.9.0
	 *
	 * @param string $visibility either 'hide' or 'show' (default)
	 */
	public function set_restricted_products_visibility( $visibility ) {

		if ( 'hide' === $visibility ) {

			update_option( $this->hide_restricted_products_option, 'yes' );

			$this->hiding_restricted_products = true;

		} else {

			update_option( $this->hide_restricted_products_option, 'no' );

			$this->hiding_restricted_products = false;
		}
	}


	/**
	 * Checks whether an option is set to show excerpts for restricted content.
	 *
	 * @see \WC_Memberships_Restrictions::hiding_excerpts()
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function showing_excerpts() {

		if ( null === $this->showing_excerpts ) {

			$this->showing_excerpts = 'yes' === get_option( $this->show_excerpts_option );
		}

		return $this->showing_excerpts;
	}


	/**
	 * Checks whether an option is set to hide excerpts for restricted content.
	 *
	 * @see \WC_Memberships_Restrictions::showing_excerpts()
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function hiding_excerpts() {
		return ! $this->showing_excerpts();
	}


	/**
	 * Sets the content excerpts visibility.
	 *
	 * @since 1.9.0
	 *
	 * @param string $visibility either 'hide' or 'show' (default)
	 */
	public function set_excerpts_visibility( $visibility ) {

		if ( 'hide' === $visibility ) {

			update_option( $this->show_excerpts_option, 'no' );

			$this->showing_excerpts = false;

		} else {

			update_option( $this->show_excerpts_option, 'yes' );

			$this->showing_excerpts = true;
		}
	}


	/**
	 * Returns content access conditions for the current user.
	 *
	 * Note: third party code should refrain from using or extending this method.
	 *
	 * @since 1.1.0
	 *
	 * @return array an associative array of restricted and granted content based on the content and product restriction rules
	 */
	public function get_user_content_access_conditions() {
		global $wpdb;

		if ( empty( $this->user_content_access_conditions ) ) {

			// prevent infinite loops
			remove_filter( 'pre_get_posts', array( $this->get_posts_restrictions_instance(), 'exclude_restricted_posts' ), 999 );
			remove_filter( 'get_terms_args', array( $this->get_posts_restrictions_instance(), 'handle_get_terms_args' ), 999 );
			remove_filter( 'terms_clauses',  array( $this->get_posts_restrictions_instance(), 'handle_terms_clauses' ), 999 );

			$rules      = wc_memberships()->get_rules_instance()->get_rules( array( 'rule_type' => array( 'content_restriction', 'product_restriction' ), ) );
			$restricted = $granted = array(
				'posts'      => array(),
				'post_types' => array(),
				'terms'      => array(),
				'taxonomies' => array(),
			);

			$conditions = array(
				'restricted' => $restricted,
				'granted'    => $granted,
			);

			// shop managers/admins can access everything
			if ( is_user_logged_in() && current_user_can( 'wc_memberships_access_all_restricted_content' ) ) {

				$this->user_content_access_conditions = $conditions;

			} else {

				// get all the content that is either restricted or granted for the user
				if ( ! empty( $rules ) ) {

					$user_id = get_current_user_id();

					foreach ( $rules as $rule ) {

						// skip rule if the plan is not published
						if ( 'publish' !== get_post_status( $rule->get_membership_plan_id() ) ) {
							continue;
						}

						// skip non-view product restriction rules
						if ( 'product_restriction' === $rule->get_rule_type() && 'view' !== $rule->get_access_type() ) {
							continue;
						}

						// check if user is an active member of the plan
						$plan_id          = $rule->get_membership_plan_id();
						$is_active_member = $user_id > 0 && wc_memberships_is_user_active_member( $user_id, $plan_id );
						$has_access       = false;

						// check if user has scheduled access to the content
						if ( $is_active_member && ( $user_membership = wc_memberships()->get_user_memberships_instance()->get_user_membership( $user_id, $plan_id ) ) ) {

							/** this filter is documented in includes/class-wc-memberships-capabilities.php **/
							$from_time = apply_filters( 'wc_memberships_access_from_time', $user_membership->get_start_date( 'timestamp' ), $rule, $user_membership );

							// sanity check: bail out if there's no valid set start date
							if ( ! $from_time || ! is_numeric( $from_time ) ) {
								break;
							}

							$inactive_time    = $user_membership->get_total_inactive_time();
							$current_time     = current_time( 'timestamp', true );
							$rule_access_time = $rule->get_access_start_time( $from_time );

							$has_access = $rule_access_time + $inactive_time <= $current_time;
						}

						$condition = $has_access ? 'granted' : 'restricted';

						// find posts that are either restricted or granted access to
						if ( 'post_type' === $rule->get_content_type() ) {

							if ( $rule->has_objects() ) {

								$post_type  = $rule->get_content_type_name();
								$post_ids   = array();
								$object_ids = $rule->get_object_ids();

								// leave out posts that have restrictions disabled
								if ( is_array( $object_ids ) ) {
									foreach ( $rule->get_object_ids() as $post_id ) {
										if ( 'yes' !== wc_memberships_get_content_meta( $post_id, '_wc_memberships_force_public', true ) ) {
											$post_ids[] = $post_id;
										}
									}
								}

								// if there are no posts left, continue to next rule
								if ( empty( $post_ids ) ) {
									continue;
								}

								if ( ! isset( $conditions[ $condition ]['posts'][ $post_type ] ) ) {
									$conditions[ $condition ]['posts'][ $post_type ] = array();
								}

								$conditions[ $condition ]['posts'][ $post_type ] = array_unique( array_merge( $conditions[ $condition ][ 'posts' ][ $post_type ], $post_ids ) );

							} else {

								// find post types that are either restricted or granted access to
								$conditions[ $condition ]['post_types'] = array_unique( array_merge( $conditions[ $condition ][ 'post_types' ], (array) $rule->get_content_type_name() ) );
							}

						} elseif ( 'taxonomy' === $rule->get_content_type() ) {

							if ( $rule->has_objects() ) {

								// find taxonomy terms that are either restricted or granted access to
								$taxonomy = $rule->get_content_type_name();

								if ( ! isset( $conditions[ $condition ][ 'terms' ][ $taxonomy ] ) ) {
									$conditions[ $condition ]['terms'][ $taxonomy ] = array();
								}

								$object_ids = array();

								// ensure child terms inherit any restriction from their ancestors
								foreach ( $rule->get_object_ids() as $object_id ) {

									$child_object_ids = get_term_children( $object_id, $taxonomy );

									if ( is_array( $child_object_ids ) ) {
										$object_ids = array_merge( $object_ids, $child_object_ids );
									}

									$object_ids[] = $object_id;
								}

								$conditions[ $condition ]['terms'][ $taxonomy ] = array_unique( array_merge( $conditions[ $condition ]['terms'][ $taxonomy ], $object_ids ) );

							} else {

								$conditions[ $condition ]['taxonomies'] = array_unique( array_merge( $conditions[ $condition ]['taxonomies'], (array) $rule->get_content_type_name() ) );
							}
						}
					}
				}

				// loop over granted content and check if the user has access to delayed content
				foreach ( $conditions['granted'] as $content_type => $values ) {

					if ( empty( $values ) || ! is_array( $values ) ) {
						continue;
					}

					foreach ( $values as $key => $value ) {

						switch ( $content_type ) {

							case 'posts':
								if ( is_array( $value ) ) {
									foreach ( $value as $post_key => $post_id ) {
										if ( ! current_user_can( 'wc_memberships_view_delayed_post_content', $post_id ) ) {
											unset( $conditions['granted'][ $content_type ][ $key ][ $post_key ] );
										}
									}
								}
							break;

							case 'post_types':
								if ( ! current_user_can( 'wc_memberships_view_delayed_post_type', $value ) ) {
									unset( $conditions['granted'][ $content_type ][ $key ] );
								}
							break;

							case 'taxonomies':
								if ( ! current_user_can( 'wc_memberships_view_delayed_taxonomy', $value ) ) {
									unset( $conditions['granted'][ $content_type ][ $key ] );
								}
							break;

							case 'terms':
								if ( is_array( $value ) ) {
									foreach ( $value as $term_key => $term ) {
										if ( ! current_user_can( 'wc_memberships_view_delayed_taxonomy_term', $key, $term ) ) {
											unset( $conditions['granted'][ $content_type ][ $key ][ $term_key ] );
										}
									}
								}
							break;
						}
					}
				}

				// remove restricted items that should be granted for the current user
				// content types are high-level restriction items - posts, post_types, terms, and taxonomies
				foreach ( $conditions['restricted'] as $content_type => $object_types ) {

					if ( empty( $conditions['granted'][ $content_type ] ) || empty( $object_types ) ) {
						continue;
					}

					// object types are child elements of a content type,
					// e.g. for the posts content type, object types are post_types( post and product)
					// for a term content type, object types are taxonomy names (e.g. category)
					foreach ( $object_types as $object_type_name => $object_ids ) {

						if ( empty( $conditions['granted'][ $content_type ][ $object_type_name ] ) || empty( $object_ids ) ) {
							continue;
						}

						if ( is_array( $object_ids ) ) {
							// if the restricted object ID is also granted, remove it from restrictions
							foreach ( $object_ids as $object_id_index => $object_id ) {

								if ( in_array( $object_id, $conditions['granted'][ $content_type ][ $object_type_name ], false ) ) {
									unset( $conditions['restricted'][ $content_type ][ $object_type_name ][ $object_id_index ] );
								}
							}
						} else {
							// post type handling
							if ( in_array( $object_ids, $conditions['granted'][ $content_type ], false ) ) {
								unset( $conditions['restricted'][ $content_type ][ array_search( $object_ids, $conditions['restricted'][ $content_type ], false ) ] );
							}
						}
					}
				}

				// grant access to posts that have restrictions disabled
				$public_posts = $wpdb->get_results( "
					SELECT p.ID, p.post_type FROM $wpdb->posts p
					LEFT JOIN $wpdb->postmeta pm
					ON p.ID = pm.post_id
					WHERE pm.meta_key = '_wc_memberships_force_public'
					AND pm.meta_value = 'yes'
				" );

				if ( ! empty( $public_posts ) ) {
					foreach ( $public_posts as $post ) {

						if ( ! isset( $conditions['granted']['posts'][ $post->post_type ] ) ) {
							$conditions['granted']['posts'][ $post->post_type ] = array();
						}

						$conditions['granted']['posts'][ $post->post_type ][] = $post->ID;
					}
				}
			}

			$this->user_content_access_conditions = $conditions;

			// add back post restriction filters that were removed prior to calculating the access conditions, in order to prevent infinite filter loops
			add_filter( 'pre_get_posts',  array( $this->get_posts_restrictions_instance(), 'exclude_restricted_posts' ), 999 );
			add_filter( 'get_terms_args', array( $this->get_posts_restrictions_instance(), 'handle_get_terms_args' ), 999, 2 );
			add_filter( 'terms_clauses',  array( $this->get_posts_restrictions_instance(), 'handle_terms_clauses' ), 999 );
		}

		return $this->user_content_access_conditions;
	}


	/**
	 * Returns a list of object IDs for the specified access condition.
	 *
	 * General method to get a list of object IDs (posts or terms) that are either restricted or granted for the current user.
	 * The list can be limited to specific post types or taxonomies.
	 *
	 * @since 1.9.0
	 *
	 * @param string $condition either 'restricted' or 'granted'
	 * @param string $content_type either 'posts' or 'terms'
	 * @param string|string[]|null $content_type_name optional: post type or taxonomy name (or names) to get object IDs for; if empty (default) will return all object IDs
	 * @return int[]|null
	 */
	private function get_user_content_for_access_condition( $condition, $content_type, $content_type_name = null ) {

		$conditions = $this->get_user_content_access_conditions();

		if ( is_string( $content_type_name ) ) {

			$objects = isset( $conditions[ $condition ][ $content_type ][ $content_type_name ] ) ? $conditions[ $condition ][ $content_type ][ $content_type_name ] : null;

		} else {

			$objects    = array( array() );
			$conditions = ! empty( $conditions[ $condition ][ $content_type ] ) && is_array( $conditions[ $condition ][ $content_type ] ) ? $conditions[ $condition ][ $content_type ] : array();

			foreach ( $conditions as $restricted_content_type_name => $restricted_objects ) {

				if ( ! $content_type_name || in_array( $restricted_content_type_name, $content_type_name, true ) ) {

					$objects[] = $restricted_objects;
				}
			}

			$objects = call_user_func_array( 'array_merge', $objects );
		}

		return ! empty( $objects ) ? $objects : null;
	}


	/**
	 * Returns a list of restricted post IDs for the current user.
	 *
	 * @since 1.1.0
	 *
	 * @param $post_type string optional post type to get restricted post IDs for - if empty, will return all post IDs
	 * @return int[]|null array of post IDs or null if none found
	 */
	public function get_user_restricted_posts( $post_type = null ) {
		return $this->get_user_content_for_access_condition( 'restricted', 'posts', $post_type );
	}


	/**
	 * Returns a list of granted post IDs for the current user.
	 *
	 * @since 1.1.0
	 *
	 * @param $post_type string optional post type to get granted post IDs for - if empty, will return all post IDs
	 * @return int[]|null Array of post IDs or null if none found
	 */
	public function get_user_granted_posts( $post_type = null ) {
		return $this->get_user_content_for_access_condition( 'granted', 'posts', $post_type );
	}


	/**
	 * Returns a list of restricted term IDs for the current user.
	 *
	 * @since 1.9.0
	 *
	 * @param $taxonomy string|array optional taxonomy or array of taxonomies to get term IDs for - if empty, will return all term IDs
	 * @return int[]|null array of term IDs or null if none found
	 */
	public function get_user_restricted_terms( $taxonomy = null ) {
		return $this->get_user_content_for_access_condition( 'restricted', 'terms', $taxonomy );
	}


	/**
	 * Returns a list of granted term IDs for the current user.
	 *
	 * @since 1.9.0
	 *
	 * @param $taxonomy string|array optional taxonomy or array of taxonomies to get term IDs for - if empty, will return all term IDs
	 * @return int[]|null array of term IDs or null if none found
	 */
	public function get_user_granted_terms( $taxonomy = null ) {
		return $this->get_user_content_for_access_condition( 'granted', 'terms', $taxonomy );
	}


	/**
	 * Retrieves all products that may grant access to a plan for viewing a piece of content or product.
	 *
	 * For products, this may include either full access or purchase ability.
	 *
	 * @since 1.11.1
	 *
	 * @param \WP_Post|\WC_Product|\WP_Term|int $restricted_content a product, post or term
	 * @param array $args optional arguments used in filters
	 * @return int[] array of product IDs
	 */
	public function get_products_that_grant_access( $restricted_content, $args = array() ) {

		$access_products = array();

		if ( $restricted_content instanceof \WC_Product ) {
			$object = Framework\SV_WC_Product_Compatibility::get_prop( $restricted_content, 'post' );
		} else {
			$object = $restricted_content; // post or term: if it's an integer we must assume post ID
		}

		if ( $object instanceof \WP_Post || is_numeric( $object ) ) {

			$post_id       = is_numeric( $object ) ? $object : $object->ID;
			$rules_handler = wc_memberships()->get_rules_instance();

			if ( in_array( get_post_type( $object ), array( 'product', 'product_variation' ), true ) ) {
				$rules     = $rules_handler->get_product_restriction_rules( $post_id );
				$rule_type = 'product_restriction';
			} else {
				$rules     = $rules_handler->get_post_content_restriction_rules( $post_id );
				$rule_type = 'content_restriction';
			}

			$access_products = $rules_handler->get_products_to_purchase_from_rules( $rules, $object, $rule_type, $args );

		} elseif ( $object instanceof \WP_Term ) {

			$taxonomy = $object->taxonomy;
			$terms    = array_unique( array_merge( array( $object->term_id ), get_ancestors( $object->term_id, $taxonomy, 'taxonomy' ) ) );
			$args     = array(
				'fields'    => 'ids',
				'nopaging'  => true,
				'tax_query' => array(),
			);

			foreach ( $terms as $term_id ) {

				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $term_id,
				);
			}

			if ( count( $args['tax_query'] ) > 1 ) {
				$args['tax_query']['relation'] = 'OR';
			}

			if ( 'product_cat' === $object->taxonomy ) {
				$args['post_type'] = 'product';
			}

			$posts       = get_posts( $args );
			$product_ids = array( array() );

			foreach ( $posts as $post_id ) {
				$product_ids[] = $this->get_products_that_grant_access( $post_id, $args );
			}

			$access_products = call_user_func_array( 'array_merge', $product_ids );
		}

		return array_unique( $access_products );
	}


	/**
	 * Retrieves all products that may grant access to a plan that gives a discount on a product or product category.
	 *
	 * Passing a post that is not a product type or a category that is not a product category would obviously produce no results.
	 *
	 * @since 1.11.1
	 *
	 * @param \WP_Post|\WC_Product|\WP_Term|int $restricted_shop_content product, post object or term object
	 * @param array $args optional arguments used in filters
	 * @return int[] array of product IDs
	 */
	public function get_products_that_grant_discount( $restricted_shop_content, $args = array() ) {

		$discount_access_products = array();

		if ( $restricted_shop_content instanceof \WC_Product ) {
			$object = Framework\SV_WC_Product_Compatibility::get_prop( $restricted_shop_content, 'post' );
		} else {
			$object = $restricted_shop_content; // post or term: if it's an integer we must assume post ID
		}

		if ( is_numeric( $object ) || ( $object instanceof \WP_Post && 'product' === $object->post_type ) ) {

			$product_id               = is_numeric( $object ) ? $object : $object->ID;
			$rules_handler            = wc_memberships()->get_rules_instance();
			$rules                    = $rules_handler->get_product_purchasing_discount_rules( $product_id );
			$discount_access_products = $rules_handler->get_products_to_purchase_from_rules( $rules, $object, 'purchasing_discount', $args );

		} elseif ( $object instanceof \WP_Term && 'product_cat' === $object->taxonomy ) {

			$taxonomy = $object->taxonomy;
			$terms    = array_unique( array_merge( array( $object->term_id ), get_ancestors( $object->term_id, $taxonomy, 'taxonomy' ) ) );
			$args     = array(
				'post_type' => 'product',
				'fields'    => 'ids',
				'nopaging'  => true,
				'tax_query' => array()
			);

			foreach ( $terms as $term_id ) {

				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $term_id,
				);
			}

			if ( count( $args['tax_query'] ) > 1 ) {
				$args['tax_query']['relation'] = 'OR';
			}

			$posts       = get_posts( $args );
			$product_ids = array( array() );

			foreach ( $posts as $post_id ) {
				$product_ids[] = $this->get_products_that_grant_discount( $post_id, $args );
			}

			$discount_access_products = call_user_func_array( 'array_merge', $product_ids );
		}

		return array_unique( $discount_access_products );
	}


	/**
	 * Handles deprecated methods.
	 *
	 * TODO remove deprecated methods when they are at least 3 minor versions older (as in x.Y.z semantic versioning) {FN 2017-07-03}
	 *
	 * @since 1.9.0
	 *
	 * @param string $method method called
	 * @param array $args optional arguments passed to invoked method
	 * @return null|mixed
	 */
	public function __call( $method, $args ) {

		$deprecated            = "WC_Memberships_Restrictions::{$method}";
		$posts_restrictions    = 'WC_Memberships_Posts_Restrictions';
		$products_restrictions = 'WC_Memberships_Products_Restrictions';

		switch ( $method ) {

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'exclude_restricted_comments' :
				_deprecated_function( $deprecated, '1.9.0' );
				global $wp_query;
				return $this->get_posts_restrictions_instance()->exclude_restricted_content_comments( array(), $wp_query );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'exclude_restricted_recent_comments' :
				_deprecated_function( $deprecated, '1.9.0' );
				return $this->get_posts_restrictions_instance()->exclude_restricted_content_recent_comments( isset( $args[0] ) ? $args[0] : $args );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'exclude_restricted_posts' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_posts_restrictions_instance()->exclude_restricted_posts( isset( $args[0] ) ? $args[0] : $args );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'exclude_restricted_pages' :
				_deprecated_function( $deprecated, '1.9.0' );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'get_restricted_product_category_excluded_tree' :
				_deprecated_function( $deprecated, '1.9.0' );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'get_terms_args' :
				_deprecated_function( $deprecated, '1.9.0', "{$posts_restrictions}::get_term_args()" );
				$arguments  = isset( $args[0] ) ? $args[0] : array();
				$taxonomies = isset( $args[1] ) ? $args[1] : array();
				return $this->get_posts_restrictions_instance()->handle_get_terms_args( $arguments, $taxonomies );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'hide_invisible_variations' :
				_deprecated_function( $deprecated, '1.9.0' );
				$is_visible = isset( $args[0] ) ? $args[0] : null;
				$product_id = isset( $args[1] ) ? $args[1] : null;
				$variation  = isset( $args[2] ) ? $args[2] : null;
				return $this->get_products_restrictions_instance()->hide_invisible_variations( $is_visible, $product_id, $variation );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'hide_restricted_content_comments' :
				_deprecated_function( $deprecated, '1.9.0' );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'hide_restricted_product_price' :
				_deprecated_function( $deprecated, '1.9.0' );
				$price   = isset( $args[0] ) ? $args[0] : '';
				$product = isset( $args[1] ) ? $args[1] : null;
				return $this->get_products_restrictions_instance()->hide_restricted_product_price( $price , $product );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'hide_widget_product_categories' :
				_deprecated_function( $deprecated, '1.9.0' );
				return $this->get_products_restrictions_instance()->hide_widget_product_categories( isset( $args[0] ) ? $args[0] : $args );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'hide_widget_product_dropdown_categories' :
				_deprecated_function( $deprecated, '1.9.0' );
				return $this->get_products_restrictions_instance()->hide_widget_product_categories( isset( $args[0] ) ? $args[0] : $args );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'maybe_close_comments' :
				_deprecated_function( $deprecated, '1.9.0' );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'maybe_password_protect_product' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_products_restrictions_instance()->password_protect_restricted_product();
				return null;

			case 'maybe_render_product_category_restricted_message' :
				_deprecated_function( $deprecated, '1.9.0' );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'maybe_remove_product_thumbnail' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_products_restrictions_instance()->remove_product_thumbnail();
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'posts_clauses' :
				_deprecated_function( $deprecated, '1.9.0' );
				$pieces = isset( $args[0] ) ? $args[0] : '';
				$query  = isset( $args[1] ) ? $args[1] : null;
				return $this->get_posts_restrictions_instance()->handle_posts_clauses( $pieces, $query );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'product_is_purchasable' :
				_deprecated_function( $deprecated, '1.9.0', "{$products_restrictions}::product_is_purchasable()" );
				$purchasable  = isset( $args[0] ) ? $args[0] : null;
				$product      = isset( $args[1] ) ? $args[1] : null;
				$restrictions = $this->get_products_restrictions_instance();
				return $restrictions && $restrictions->product_is_purchasable( $purchasable, $product );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'product_is_visible' :
				_deprecated_function( $deprecated, '1.9.0', "{$products_restrictions}::product_is_visible()" );
				$visible      = isset( $args[0] ) ? $args[0] : null;
				$product_id   = isset( $args[1] ) ? $args[1] : null;
				$restrictions = $this->get_products_restrictions_instance();
				return $restrictions && $restrictions->product_is_visible( $visible, $product_id );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'redirect_restricted_content' :
				_deprecated_function( $deprecated, '1.9.0' );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'restore_product_thumbnail' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_products_restrictions_instance()->restore_product_thumbnail();
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'restrict_content' :
				_deprecated_function( $deprecated, '1.9.0' );
				global $post;
				$this->get_posts_restrictions_instance()->restrict_post( $post );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'restrict_product_content' :
				_deprecated_function( $deprecated, '1.9.0' );
				return $this->get_products_restrictions_instance()->restrict_product_content( isset( $args[0] ) ? $args[0] : $args );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'single_product_member_discount_message' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_products_restrictions_instance()->display_product_purchasing_discount_message();
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'single_product_purchasing_restricted_message' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_products_restrictions_instance()->display_product_purchasing_restricted_message();
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'terms_clauses' :
				_deprecated_function( $deprecated, '1.9.0' );
				return $this->get_posts_restrictions_instance()->handle_terms_clauses( isset( $args[0] ) ? $args[0] : $args );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'template_loop_product_thumbnail_placeholder' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->get_products_restrictions_instance()->template_loop_product_thumbnail_placeholder();
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher  */
			case 'variation_is_visible' :
				_deprecated_function( $deprecated, '1.9.0', "{$products_restrictions}::variation_is_visible()" );
				$is_visible   = isset( $args[0] ) ? $args[0] : null;
				$variation_id = isset( $args[1] ) ? $args[1] : null;
				$parent_id    = isset( $args[2] ) ? $args[2] : null;
				return $this->get_products_restrictions_instance()->variation_is_visible( $is_visible, $variation_id, $parent_id );

			// you're probably doing it wrong...
			default :
				trigger_error( "Call to undefined method {$deprecated}", E_USER_ERROR );
				return null;
		}
	}


}
