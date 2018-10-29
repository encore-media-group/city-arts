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

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;


/**
 * Restricts content to specified membership plans.
 *
 * @since 1.0.0
 *
 * @param string $content
 * @param string|int|array $membership_plans optional: the membership plan or plans to check against - accepts a plan slug, id, or an array of slugs or IDs (default: all plans)
 * @param string|null $delay default none (empty)
 * @param bool $exclude_trial default false
 */
function wc_memberships_restrict( $content, $membership_plans = null, $delay = null, $exclude_trial = false ) {

	$has_access   = false;
	$member_since = null;
	$access_time  = null;

	// grant access to super users
	if ( current_user_can( 'wc_memberships_access_all_restricted_content' ) ) {
		$has_access = true;
	}

	// convert to an array in all cases
	$membership_plans = (array) $membership_plans;

	// default to use all plans if no plan is specified
	if ( empty( $membership_plans ) ) {
		$membership_plans = wc_memberships_get_membership_plans();
	}

	foreach ( $membership_plans as $plan_id_or_slug ) {

		$membership_plan = wc_memberships_get_membership_plan( $plan_id_or_slug );

		if ( $membership_plan && wc_memberships_is_user_active_member( get_current_user_id(), $membership_plan->get_id() ) ) {

			$has_access = true;

			if ( ! $delay && ! $exclude_trial ) {
				break;
			}

			// determine the earliest membership for the user
			if ( $user_membership = wc_memberships()->get_user_memberships_instance()->get_user_membership( get_current_user_id(), $membership_plan->get_id() ) ) {

				// create a pseudo-rule to help applying filters
				$rule = new \WC_Memberships_Membership_Plan_Rule( array(
					'access_schedule_exclude_trial' => $exclude_trial ? 'yes' : 'no'
				) );

				/** This filter is documented in includes/class-wc-memberships-capabilities.php **/
				$from_time = apply_filters( 'wc_memberships_access_from_time', $user_membership->get_start_date( 'timestamp' ), $rule, $user_membership );

				// if there is no time to calculate the access time from,
				// simply use the current time as access start time
				if ( ! $from_time ) {
					$from_time = current_time( 'timestamp', true );
				}

				if ( null === $member_since || $from_time < $member_since ) {
					$member_since = $from_time;
				}
			}
		}
	}

	// add delay
	if ( $has_access && ( $delay || $exclude_trial ) && $member_since ) {

		$access_time = $member_since;

		// determine access time
		if ( strpos( $delay, 'month' ) !== false ) {

			$parts  = explode( ' ', $delay );
			$amount = isset( $parts[1] ) ? (int) $parts[0] : '';

			$access_time = wc_memberships_add_months_to_timestamp( $member_since, $amount );

		} elseif ( $delay ) {

			$access_time = strtotime( $delay, $member_since );

		}

		// output or show delayed access message
		if ( $access_time <= current_time( 'timestamp', true ) ) {
			echo $content;
		} else {
			echo \WC_Memberships_User_Messages::get_message_html( 'content_delayed', array( 'access_time' => $access_time ) );
		}

	} elseif ( $has_access ) {

		echo $content;
	}
}


/**
 * Checks if a post/page content is restricted.
 *
 * @since 1.0.0
 *
 * @param int|\WP_Post|null $post_id optional, defaults to current post
 * @return bool
 */
function wc_memberships_is_post_content_restricted( $post_id = null ) {
	global $post;

	if ( ! $post_id && $post && isset( $post->ID ) ) {
		$post_id = $post->ID;
	} elseif ( $post_id instanceof \WP_Post ) {
		$post_id = $post_id->ID;
	}

	$rules = is_numeric( $post_id ) && (int) $post_id > 0 ? wc_memberships()->get_rules_instance()->get_post_content_restriction_rules( $post_id ) : '';

	return ! empty( $rules ) && 'yes' !== wc_memberships_get_content_meta( $post_id, '_wc_memberships_force_public' );
}


/**
 * Checks if a taxonomy term is restricted.
 *
 * Note: does not check if any of its ancestors are restricted.
 *
 * @since 1.11.1
 *
 * @param null|int|\WP_Term $term_id term ID
 * @param null|string $taxonomy taxonomy name (unused when checking directly a WP_Term object)
 * @return bool
 */
function wc_memberships_is_term_restricted( $term_id = null, $taxonomy = null ) {
	global $wp_query;

	$restricted = false;

	if ( null === $term_id && null === $taxonomy && ( $wp_query->is_tax() || $wp_query->is_category() || $wp_query->is_tag() ) ) {

		$term = get_queried_object();

		if ( $term instanceof \WP_Term ) {
			$taxonomy = $term->taxonomy;
			$term_id  = $term->term_id;
		}

	} elseif ( $term_id instanceof \WP_Term ) {

		$taxonomy = $term_id->taxonomy;
		$term_id  = $term_id->term_id;
	}

	if ( (int) $term_id > 0 && is_string( $taxonomy ) ) {

		if ( 'product_cat' === $taxonomy ) {
			$rules = wc_memberships()->get_rules_instance()->get_taxonomy_term_product_restriction_rules( $taxonomy, $term_id );
		} elseif ( '' !== $taxonomy ) {
			$rules = wc_memberships()->get_rules_instance()->get_taxonomy_term_content_restriction_rules( $taxonomy, $term_id );
		}

		$restricted = ! empty( $rules );
	}

	return $restricted;
}


/**
 * Checks if a product category term is restricted from viewing.
 *
 * @since 1.11.1
 *
 * @param int|\WP_Term $category term ID or object
 * @return bool
 */
function wc_memberships_is_product_category_viewing_restricted( $category ) {

	return wc_memberships_is_term_restricted( $category, 'product_cat' );
}


/**
 * Checks if viewing a product is restricted.
 *
 * @since 1.0.0
 *
 * @param int|\WC_Product|\WP_Post|null $post_id optional, defaults to current post
 * @return bool
 */
function wc_memberships_is_product_viewing_restricted( $post_id = null ) {
	global $post;

	if ( ! $post_id && $post && isset( $post->ID ) ) {
		$post_id = $post->ID;
	} elseif ( $post_id instanceof \WP_Post ) {
		$post_id = $post_id->ID;
	} elseif ( $post_id instanceof \WC_Product ) {
		$post_id = $post_id->get_id();
	}

	$rules         = is_numeric( $post_id ) && (int) $post_id > 0 ? wc_memberships()->get_rules_instance()->get_product_restriction_rules( $post_id ) : null;
	$is_restricted = false;

	if ( ! empty( $rules ) ) {
		foreach ( $rules as $rule ) {
			if ( 'view' === $rule->get_access_type() ) {
				$is_restricted = true;
			}
		}
	}

	return $is_restricted && 'yes' !== wc_memberships_get_content_meta( $post_id, '_wc_memberships_force_public' );
}


/**
 * Checks if purchasing a product is restricted.
 *
 * @since 1.0.0
 *
 * @param int|\WC_Product|\WP_Post|null $post_id optional, defaults to current post
 * @return bool
 */
function wc_memberships_is_product_purchasing_restricted( $post_id = null ) {
	global $post;

	if ( ! $post_id && $post && isset( $post->ID ) ) {
		$post_id   = $post->ID;
		$post_type = get_post_type( $post );
	} elseif ( $post_id instanceof \WP_Post ) {
		$post_id   = $post_id->ID;
		$post_type = get_post_type( $post_id );
	} elseif ( $post_id instanceof \WC_Product ) {
		$post_id   = $post_id->get_id();
		$post_type = 'product';
	} elseif ( is_numeric( $post_id ) ) {
		$post_type = get_post_type( $post_id );
	} else {
		$post_type = '';
	}

	if ( ! $post_id || 'product' !== $post_type ) {

		$is_restricted = false;

	} else {

		$rules         = wc_memberships()->get_rules_instance()->get_product_restriction_rules( $post_id );
		$is_restricted = false;

		if ( ! empty( $rules ) ) {
			foreach ( $rules as $rule ) {
				if ( 'purchase' === $rule->get_access_type() ) {
					$is_restricted = true;
				}
			}
		}
	}

	return $is_restricted && 'yes' !== wc_memberships_get_content_meta( $post_id, '_wc_memberships_force_public' );
}


/**
 * Checks if the product (or current product) has any member discounts.
 *
 * @since 1.0.0
 *
 * @param int|\WP_Post|\WC_Product|null $the_product product ID: optional, defaults to current product
 * @return bool
 */
function wc_memberships_product_has_member_discount( $the_product = null ) {
	global $product;

	if ( is_numeric( $the_product ) || $the_product instanceof \WP_Post ) {
		$the_product = wc_get_product( $the_product );
	}

	if ( ! $the_product ) {
		$the_product = $product;
	}

	if ( ! $the_product instanceof \WC_Product || wc_memberships_is_product_excluded_from_member_discounts( $the_product ) ) {
		$has_member_discount = false;
	} else {
		$has_member_discount = wc_memberships()->get_rules_instance()->product_has_purchasing_discount_rules( $the_product->get_id() );
	}

	return $has_member_discount && 'yes' !== wc_memberships_get_content_meta( $the_product, '_wc_memberships_exclude_discounts' );
}


/**
 * Checks if a product is set to be excluded from member discount rules.
 *
 * @since 1.7.0
 *
 * @param int|\WC_Product $product the product object or ID
 * @return bool if false, discounts may still apply depending on the rules and member status
 */
function wc_memberships_is_product_excluded_from_member_discounts( $product ) {
	return wc_memberships()->get_member_discounts_instance()->is_product_excluded_from_member_discounts( $product );
}


/**
 * Checks if a user is eligible for member discount for the current product.
 *
 * @since 1.0.0
 *
 * @param int|\WC_Product|null $product optional, product id or object, if not set will attempt to get the current one
 * @param int|\WP_User|null $member optional, user to check for (defaults to current logged in user)
 * @return bool
 */
function wc_memberships_user_has_member_discount( $product = null, $member = null ) {
	return wc_memberships()->get_member_discounts_instance()->user_has_member_discount( $product, $member );
}


/**
 * Returns the user access start timestamp for a content or product.
 *
 * It will returns the time in local time (according to site timezone).
 *
 * TODO for now $target only supports 'post' => id or 'product' => id  {FN 2016-04-26}
 *
 * @since 1.4.0
 *
 * @param int $user_id user to get access time for
 * @param array $content associative array of content type and content id to access to
 * @param string $action type of access, 'view' or 'purchase' (products only)
 * @param bool $gmt whether to return a UTC timestamp (default false, uses site timezone)
 * @return int|null timestamp of start access time
 */
function wc_memberships_get_user_access_start_time( $user_id, $action, $content, $gmt = false ) {

	$access_time = wc_memberships()->get_capabilities_instance()->get_user_access_start_time_for_post( $user_id, reset( $content ), $action );

	if ( null !== $access_time ) {
		return ! $gmt ? wc_memberships_adjust_date_by_timezone( $access_time, 'timestamp' ) : $access_time;
	}

	return null;
}


/**
 * Echoes the member discount badge for the loop.
 *
 * @since 1.0.0
 */
function wc_memberships_show_product_loop_member_discount_badge() {
	wc_get_template( 'loop/member-discount-badge.php' );
}


/**
 * Echoes the member discount badge for the single product page.
 *
 * @since 1.0.0
 */
function wc_memberships_show_product_member_discount_badge() {
	wc_get_template( 'single-product/member-discount-badge.php' );
}


/**
 * Returns the member discount badge HTML content.
 *
 * @since 1.6.4
 *
 * @param \WC_Product $product the product object to output a badge for (passed to filter)
 * @param bool $variation whether to output a discount badge for a product variation (default false)
 * @return string HTML
 */
function wc_memberships_get_member_discount_badge( $product, $variation = false ) {
	return wc_memberships()->get_member_discounts_instance()->get_member_discount_badge( $product, $variation );
}


/**
 * Returns the product discount for a member.
 *
 * @since 1.4.0
 *
 * @param \WC_Memberships_User_Membership $user_membership the user membership object
 * @param int|\WC_Product $product the product object or id to get discount for
 * @param bool $formatted whether to return a formatted amount or a numerical string (default false, return a discount as a fixed amount or a percentage amount)
 * @return string HTML or empty string
 */
function wc_memberships_get_member_product_discount( $user_membership, $product, $formatted = false ) {

	$plan     = $user_membership->get_plan();
	$discount = '';

	if ( $plan ) {
		$discount = $formatted ? $plan->get_formatted_product_discount( $product ) : $plan->get_product_discount( $product );
	}

	return $discount;
}



/**
 * Returns the members area URL pointing to a specific plan and section.
 *
 * Leave arguments empty to return the base URL only.
 *
 * @since 1.4.0
 *
 * @param null|int|\WC_Memberships_Membership_Plan $membership_plan optional plan object or id
 * @param string $members_area_section optional, which section of the members area to point to
 * @param int|string $paged optional, for paged sections
 * @return string unescaped URL
 */
function wc_memberships_get_members_area_url( $membership_plan = null, $members_area_section = '', $paged = '' ) {

	$url                = '';
	$my_account_page_id = wc_get_page_id( 'myaccount' );
	$membership_plan_id = $membership_plan instanceof \WC_Memberships_Membership_Plan ? $membership_plan->get_id() : (int) $membership_plan;

	// bail out if something is wrong (wc_get_page_id() may return negative int)
	if ( $my_account_page_id > 0 ) {

		$using_permalinks = get_option( 'permalink_structure' );

		// grab base URL according to rewrite structure used
		if ( $using_permalinks ) {
			$my_account_url = wc_get_page_permalink( 'myaccount' );
		} else {
			$my_account_url = get_home_url();
		}

		// grab any query strings (sometimes set by translation plugins, e.g. ?lang=it)
		$url_pieces     = parse_url( $my_account_url );
		$query_strings  = ! empty( $url_pieces['query'] ) && is_string( $url_pieces['query'] ) ? explode( '&', $url_pieces['query'] ) : array();
		$my_account_url = preg_replace( '/\?.*/', '', $my_account_url );
		$endpoint       = $using_permalinks ? get_option( 'woocommerce_myaccount_members_area_endpoint', 'members-area' ) : 'members_area';

		if ( $using_permalinks ) {

			// using permalinks
			// e.g. /my-account/members-area/
			$url = trailingslashit( $my_account_url ) . $endpoint . '/';

		} else {

			// not using permalinks
			// e.g. /?page_id=123&members_area
			$url = add_query_arg(
				array(
					'page_id' => $my_account_page_id,
					$endpoint => '',
				),
				trailingslashit( $my_account_url )
			);
		}

		// grab optional members area section and paged requests
		if ( $membership_plan_id > 0 ) {

			if ( $using_permalinks ) {

				// using permalinks
				// e.g. /my-account/members-area/123/
				$url = trailingslashit( $url ) . $membership_plan_id . '/';

			} else {

				// not using permalinks
				// e.g. /?page_id=123&members_area=123
				$url = add_query_arg(
					array(
						$endpoint => $membership_plan_id,
					),
					remove_query_arg( $endpoint, $url )
				);
			}

			// if unspecified, will get the first tab as set in membership plan in admin
			if ( empty( $members_area_section ) ) {

				$membership_plan = is_numeric( $membership_plan ) ? wc_memberships_get_membership_plan( $membership_plan_id ) : $membership_plan;

				if ( $membership_plan instanceof \WC_Memberships_Membership_Plan ) {

					$plan_sections        = (array) $membership_plan->get_members_area_sections();
					$available_sections   = array_intersect_key( wc_memberships_get_members_area_sections(), array_flip( $plan_sections ) );
					$members_area_section = key( $available_sections );
				}
			}

			if ( ! empty( $members_area_section ) ) {

				$paged = ! empty( $paged ) ? max( absint( $paged ), 1 ) : '';

				if ( $using_permalinks ) {

					// Using permalinks:
					// e.g. /my-account/members-area/123/my-membership-content/2
					$url = trailingslashit( $url ) . "{$members_area_section}/{$paged}";

				} else {

					$url_args = array( 'members_area_section' => $members_area_section );

					if ( $paged > 0 )  {
						$url_args['members_area_section_page'] = $paged;
					}

					// Not using permalinks:
					// e.g. /?page_id=123&members_area=456&members_area_section=my_membership_content&members_area_section_page=2
					$url = add_query_arg( $url_args, $url );
				}
			}
		}

		// puts back any query arg at the end of the Members Area URL
		if ( ! empty( $query_strings ) ) {

			foreach ( $query_strings as $query_string ) {

				$arg = explode( '=', $query_string );
				$url = add_query_arg( array( $arg[0] => isset( $arg[1] ) ? $arg[1] : '' ), $url );
			}
		}
	}

	return $url;
}


/**
 * Returns the Members Area action links.
 *
 * @since 1.4.0
 *
 * @param string $section members area section to display actions for
 * @param \WC_Memberships_User_Membership $user_membership the user membership the members area is for
 * @param \WC_Product|\WP_Post|object $object an object to pass to a filter hook (optional)
 * @return string action links HTML
 */
function wc_memberships_get_members_area_action_links( $section, $user_membership, $object = null ) {

	$default_actions = array();
	$object_id       = 0;

	if ( $object instanceof \WC_Product ) {
		$object_id = $object->get_id();
	} elseif ( $object instanceof \WP_Post || isset( $object->ID ) ) {
		$object_id = $object->ID;
	}

	switch ( $section ) {

		case 'my-memberships' :
		case 'my-membership-details' :

			$members_area = $user_membership->get_plan()->get_members_area_sections();

			// Renew: Show only for expired memberships that can be renewed
			if (    $user_membership->is_expired()
			        && $user_membership->can_be_renewed()
			        && current_user_can( 'wc_memberships_renew_membership', $user_membership->get_id() ) ) {

				$default_actions['renew'] = array(
					'url'  => $user_membership->get_renew_membership_url(),
					'name' => __( 'Renew', 'woocommerce-memberships' ),
				);
			}

			// Cancel: Show only for memberships that can be cancelled
			if (    $user_membership->can_be_cancelled()
			     && current_user_can( 'wc_memberships_cancel_membership', $user_membership->get_id() ) ) {

				$default_actions['cancel'] = array(
					'url'  => $user_membership->get_cancel_membership_url(),
					'name' => __( 'Cancel', 'woocommerce-memberships' ),
				);
			}

			// View: Do not show for cancelled, expired, paused memberships, or memberships without a Members Area
			if (    'my-membership-details' !== $section
			     && ( ! empty ( $members_area ) && is_array( $members_area ) )
			     && $user_membership->has_status( wc_memberships()->get_user_memberships_instance()->get_active_access_membership_statuses() ) ) {

				$sections = $user_membership->get_plan()->get_members_area_sections();

				// perhaps open the my content section, if available, or default to the first section in array
				if ( in_array( 'my-membership-content', $sections, true ) ) {
					$url_section = 'my-membership-content';
				} else {
					$url_section = current( $sections );
				}

				$default_actions['view'] = array(
					'url' => wc_memberships_get_members_area_url( $user_membership->get_plan_id(), $url_section ),
					'name' => __( 'View', 'woocommerce-memberships' ),
				);
			}

		break;

		case 'my-membership-content'   :

			if ( ! empty( $object_id ) && wc_memberships_user_can( $user_membership->get_user_id(), 'view', array( 'post' => $object_id ) ) ) {

				$default_actions['view'] = array(
					'url'  => get_permalink( $object_id ),
					'name' => __( 'View', 'woocommerce-memberships' ),
				);
			}

		break;

		case 'my-membership-products'  :
		case 'my-membership-discounts' :

			$can_view_product     = wc_memberships_user_can( $user_membership->get_user_id(), 'view',     array( 'product' => $object_id ) );
			$can_purchase_product = wc_memberships_user_can( $user_membership->get_user_id(), 'purchase', array( 'product' => $object_id ) );

			if ( $can_view_product ) {

				$default_actions['view'] = array(
					'url'  => get_permalink( $object_id ),
					'name' => __( 'View', 'woocommerce-memberships' ),
				);
			}

			if ( $can_view_product && $can_purchase_product && $object instanceof \WC_Product ) {

				$default_actions['add-to-cart'] = array(
					'url'	=> $object->add_to_cart_url(),
					'name'	=> $object->add_to_cart_text(),
				);
			}

		break;

	}

	/**
	 * Filter membership actions on My Account and Members Area pages.
	 *
	 * @since 1.4.0
	 *
	 * @param array $default_actions associative array of actions
	 * @param \WC_Memberships_User_Membership $user_membership User Membership object
	 * @param \WC_Product|\WP_Post|object $object current object where the action is run (optional)
	 */
	$actions = apply_filters( "wc_memberships_members_area_{$section}_actions", $default_actions, $user_membership, $object );

	$links = '';

	if ( ! empty( $actions ) ) {
		foreach ( $actions as $key => $action ) {
			$links .= '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a> ';
		}
	}

	return $links;
}


/**
 * Returns Members Area pagination links.
 *
 * @since 1.4.0
 *
 * @param false|int|\WC_Memberships_Membership_Plan $membership_plan membership plan object
 * @param string $section Members Area section
 * @param \WP_Query|\WP_Comment_Query $query current query
 * @return string HTML or empty output if query is not paged
 */
function wc_memberships_get_members_area_page_links( $membership_plan, $section, $query ) {

	$links     = '';
	$max_pages = (int) $query->max_num_pages;

	if ( $max_pages > 1 ) {

		$current_page = (int) $query->get( 'paged' );

		if ( is_numeric( $membership_plan ) ) {
			$membership_plan = wc_memberships_get_membership_plan( (int) $membership_plan );
		}

		if ( $membership_plan ) {

			$links .= '<span class="wc-memberships-members-area-pagination">';

			// page navigation entities
			$first         = '<span class="first">&#x25C4;</span>';
			$first_tooltip = __( 'First', 'woocommerce-memberships' );
			$prev          = '<span class="prev">&#x25C2;</span>';
			$prev_tooltip  = __( 'Previous', 'woocommerce-memberships' );
			$current       = ' &nbsp; <span class="current">' . $current_page . '</span> &nbsp; ';
			$next          = '<span class="next">&#x25B8;</span>';
			$next_tooltip  = __( 'Next', 'woocommerce-memberships' );
			$last          = '<span class="last">&#x25BA;</span>';
			$last_tooltip  = __( 'Last', 'woocommerce-memberships' );

			if ( 1 === $current_page ) {
				// first page, show next
				$links .= $current;
				$links .= ' <a title="' . esc_html( $next_tooltip )   . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, 2 ) )                . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-pagination-next">' . $next . '</a> ';
				$links .= ' <a title="' . esc_html( $last_tooltip )   . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, $max_pages ) )             . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-page-link wc-memberships-members-area-pagination-last">' . $last . '</a> ';
			} elseif ( $max_pages === $current_page ) {
				// last page, show prev
				$links .= ' <a title="' . esc_html( $first_tooltip ) . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, 1 ) )                 . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-page-link wc-memberships-members-area-pagination-first">' . $first . '</a> ';
				$links .= ' <a title="' . esc_html( $prev_tooltip )  . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, $current_page - 1 ) ) . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-page-link wc-memberships-members-area-pagination-prev">' . $prev . '</a> ';
				$links .= $current;
			} else {
				// in the middle of pages, show both
				$links .= ' <a title="' . esc_html( $first_tooltip ) . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, 1 ) )                 . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-page-link wc-memberships-members-area-pagination-first">' . $first . '</a> ';
				$links .= ' <a title="' . esc_html( $prev_tooltip )  . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, $current_page - 1 ) ) . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-page-link wc-memberships-members-area-pagination-prev">' . $prev . '</a> ';
				$links .= $current;
				$links .= ' <a title="' . esc_html( $next_tooltip )  . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, $current_page + 1 ) ) . '" class="wc-memberships-members-area-page-link wc-memberships-members-area-page-link wc-memberships-members-area-pagination-next">' . $next . '</a> ';
				$links .= ' <a title="' . esc_html( $last_tooltip )  . '" href="' . esc_url( wc_memberships_get_members_area_url( $membership_plan, $section, $max_pages ) )              . '" class="wc-memberships-members-area-page-linkwc-memberships-members-area-page-link wc-memberships-members-area-pagination-last">' . $last . '</a> ';
			}

			$links .= '</span>';
		}
	}

	/**
	 * Filters the members area pagination links.
	 *
	 * @since 1.9.0
	 *
	 * @param string $links HTML
	 * @param \WC_Memberships_Membership_Plan $membership_plan the plan the links are related to
	 * @param string $section the current section displayed
	 * @param \WP_Query|\WP_Comment_Query $query the current query (content or comments for notes)
	 */
	return (string) apply_filters( 'wc_memberships_members_area_pagination_links', $links, $membership_plan, $section, $query );
}


/**
 * Returns a members area sorting link.
 *
 * @since 1.9.0
 *
 * @param string $sort_key sort key (e.g. title, type for post type...)
 * @param string $sort_label label text to use
 * @return string HTML
 */
function wc_memberships_get_members_area_sorting_link( $sort_key, $sort_label ) {

	if ( $members_area = wc_memberships()->get_frontend_instance()->get_members_area_instance() ) {

		$sorting_link = '<span class="wc-memberships-members-area-sorting">';
		$sorting_args = $members_area->get_members_area_sorting_args();

		if ( empty( $sorting_args ) ) {

			$sorting_link .= '<span class="sort-status unsorted">';

			if ( 'title' === $sort_key ) {
				$sorting_link .= '<a class="sort-by-post-title" href="' . esc_url( add_query_arg( array( 'sort_by' => 'title', 'sort_order' => 'ASC' ) ) ) . '">' . esc_html( $sort_label ) . '</a>';
			} elseif ( 'type' === $sort_key ) {
				$sorting_link .= '<a class="sort-by-post-type" href="' . esc_url( add_query_arg( array( 'sort_by' => 'post_type', 'sort_order' => 'ASC' ) ) ) . '">' . esc_html( $sort_label ) . '</a>';
			}

			$sorting_link .= '<span class="sort-order-icon sort-asc"> &nbsp; &#x25B4;</span> ';
			$sorting_link .= '<span class="sort-order-icon sort-desc" style="display:none;"> &nbsp; &#x25BE;</span> ';
			$sorting_link .= '</span>';

		} else {

			$is_current = isset( $sorting_args['orderby'] ) && $sorting_args['orderby'] === $sort_key;
			$sort_order = isset( $sorting_args['order'] ) ? strtoupper( $sorting_args['order'] ) : 'ASC';

			if ( $is_current ) {
				$sort_url   = add_query_arg( array( 'sort_by' => $sort_key, 'sort_order' => $sort_order === 'ASC' ? 'DESC' : 'ASC' ) );
				$sort_order = strtolower( $sort_order );
				$sort_class = "sorted sort-{$sort_order}";
			} else {
				$sort_url   = add_query_arg( array( 'sort_by' => $sort_key, 'sort_order' => 'ASC' ) );
				$sort_class = 'unsorted';
			}

			$sorting_link .= '<span class="sort-status ' . $sort_class . '">';

			if ( 'title' === $sort_key ) {
				$sorting_link .= '<a class="sort-by-post-title" href="' . esc_url( $sort_url ) . '">' . esc_html( $sort_label ) . '</a>';
			} elseif ( 'type' === $sort_key ) {
				$sorting_link .= '<a class="sort-by-post-type" href="' . esc_url( $sort_url ) . '">' . esc_html( $sort_label ) . '</a>';
			}

			$sorting_link .= '<span class="sort-order-icon sort-asc"> &nbsp; &#x25B4;</span>';
			$sorting_link .= '<span class="sort-order-icon sort-desc"> &nbsp; &#x25BE;</span>';
			$sorting_link .= '</span>';
		}

		$sorting_link .= '</span>';

	} else {

		$sorting_link = $sort_label;
	}

	/**
	 * Filters a sorting link in the members area.
	 *
	 * @since 1.9.0
	 *
	 * @param string $sorting_link HTML
	 * @param string $sort_key the key to sort
	 * @param string $sort_label the label used for the sort element
	 */
	return (string) apply_filters( 'wc_memberships_members_area_sorting_link', $sorting_link, $sort_key, $sort_label );
}


/**
 * Checks if we are on the Members Area content.
 *
 * @since 1.9.0
 *
 * @return bool
 */
function wc_memberships_is_members_area() {
	return wc_memberships()->get_frontend_instance()->get_members_area_instance()->is_members_area();
}


/**
 * Checks if we are currently viewing a Members Area section.
 *
 * @since 1.9.0
 *
 * @param null|array|string $section optional: check against a specific section, an array of sections or any valid section (null)
 * @return bool
 */
function wc_memberships_is_members_area_section( $section = null ) {
	return wc_memberships()->get_frontend_instance()->get_members_area_instance()->is_members_area_section( $section );
}
