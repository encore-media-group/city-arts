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
 * @package   WC-Memberships/Admin
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

use SkyVerge\WooCommerce\PluginFramework\v5_3_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Memberships settings class.
 *
 * @since 1.0.0
 */
class WC_Settings_Memberships extends \WC_Settings_Page {


	/**
	 * Constructs the "Memberships" settings tab.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id    = 'memberships';
		$this->label = __( 'Memberships', 'woocommerce-memberships' );

		parent::__construct();

		// set the endpoint slug for Members Area in My Account (since WC 3.4 these have moved into the "Advanced" tab)
		if ( Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte( '3.4.0' ) ) {
			add_filter( 'woocommerce_settings_pages',   array( $this, 'add_my_account_endpoints_options' ) );
		} else {
			add_filter( 'woocommerce_account_settings', array( $this, 'add_my_account_endpoints_options' ) );
		}
	}


	/**
	 * Filters WooCommerce Settings sections to add new sections for the Memberships tab.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''         => __( 'General', 'woocommerce-memberships' ),  // handles general content settings
			'products' => __( 'Products', 'woocommerce-memberships' ), // handles products settings
			'messages' => __( 'Messages', 'woocommerce-memberships' ), // handles messages
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}


	/**
	 * Returns the settings array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $current_section optional, defaults to empty string
	 * @return array array of settings
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'products' === $current_section ) {

			/**
			 * Filter Memberships products settings.
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings associative array of the plugin settings
			 */
			$settings = (array) apply_filters( 'wc_memberships_products_settings', array(

				array(
					'name'    => __( 'Products', 'woocommerce-memberships' ),
					'type'    => 'title',
				),

				array(
					'type'    => 'checkbox',
					'id'      => 'wc_memberships_allow_cumulative_access_granting_orders',
					'name'    => __( 'Allow cumulative purchases', 'woocommerce-memberships' ),
					'desc'    => __( 'Purchasing products that grant access to a membership in the same order extends the length of the membership.', 'woocommerce-memberships' ),
					'default' => 'no',
				),

				array(
					'type'    => 'checkbox',
					'id'      => 'wc_memberships_exclude_on_sale_products_from_member_discounts',
					'name'    => __( 'Exclude products on sale from member discounts', 'woocommerce-memberships' ),
					'desc'    => __( 'Do not apply member discounts from any membership plan discount rules to products that are currently on sale.', 'woocommerce-memberships' ),
					'default' => 'no',
				),

				array(
					'type'    => 'checkbox',
					'id'      => 'wc_memberships_hide_restricted_products',
					'name'    => __( 'Hide restricted products', 'woocommerce-memberships' ),
					'desc'    => __( 'If enabled, products with viewing restricted will be hidden from the shop catalog. Products will still be accessible directly, unless Content Restriction Mode is "Hide completely".', 'woocommerce-memberships' ),
					'default' => 'no',
				),

				array(
					'type'    => 'sectionend',
				),

			) );

		} elseif ( 'messages' === $current_section ) {

			$legend  = '<p>' . __( 'Customize restriction and discount messages displayed to non-members and members. Basic HTML is allowed. You can also use the following merge tags:', 'woocommerce-memberships' ) . '</p>';

			$legend_keys = array(
				/* translators: Placeholder: %s shows a message merge tag to be used */
				'<strong><code>{products}</code></strong>'  => __( '%s automatically inserts the product(s) needed to gain access.', 'woocommerce-memberships' ),
				/* translators: Placeholder: %s shows a message merge tag to be used */
				'<strong><code>{date}</code></strong>'      => __( '%s inserts the date when the member will gain access to delayed content.', 'woocommerce-memberships' ),
				/* translators: Placeholder: %s shows a message merge tag to be used */
				'<strong><code>{login_url}</code></strong>' => __( '%s inserts the URL to the "My Account" page with the login form.', 'woocommerce-memberships' ),
				/* translators: Placeholder: %s shows a message merge tag to be used */
				'<strong><code>{login}</code></strong>'     => __( '%s inserts a login link to the "My Account" page with the login form.', 'woocommerce-memberships' ),
			);

			foreach ( $legend_keys as $merge_tag => $instruction ) {
				$legend .= '<li>' . sprintf( $instruction, $merge_tag ) . '</li>';
			}

			$legend .= '</ul>';

			/**
			 * Filters Memberships products settings.
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings associative array of the plugin settings
			 */
			$settings = (array) apply_filters( 'wc_memberships_messages_settings', array(

				array(
					'title'    => __( 'Messages', 'woocommerce-memberships' ),
					'type'     => 'title',
					'desc'     => $legend,
				),

				array(
					'type'     => 'select',
					'class'    => 'wc-enhanced-select js-select-edit-message-group',
					'name'     => __( 'Edit messages for:', 'woocommerce-memberships' ),
					'options'  => array(
						'.messages-group-posts'     => __( 'Blog Posts Restriction', 'woocommerce-memberships' ),
						'.messages-group-pages'     => __( 'Pages Restriction', 'woocommerce-memberships' ),
						'.messages-group-content'   => __( 'Content Restriction', 'woocommerce-memberships' ),
						'.messages-group-products'  => __( 'Products Restriction', 'woocommerce-memberships' ),
						'.messages-group-discounts' => __( 'Purchasing Discount', 'woocommerce-memberships' ),
					),
					'default'  => 'posts',
				),

				array(
					'type'     => 'sectionend',
				),

				// =====================
				//  Blog Posts Messages
				// ============-========

				array(
					'name'     => __( 'Post Restricted Messages', 'woocommerce-memberships' ),
					'type'     => 'title',
					'class'    => 'messages-group-posts',
					'desc'     => __( 'The following messages may be shown to members and non-members when trying to access a blog post.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[post_content_restricted_message]',
					'class'    => 'input-text wide-input messages-group-posts',
					'name'     => __( 'Post Restricted (Product Purchase Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays when purchase is required to view the blog post.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'post_content_restricted_message' ),
					'desc_tip' => __( 'Message displayed if visitor does not have access to the post, but can purchase it.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[post_content_restricted_message_no_products]',
					'class'    => 'input-text wide-input messages-group-posts',
					'name'     => __( 'Post Restricted (Membership Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if the blog post is restricted to a membership that cannot be purchased.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'post_content_restricted_message_no_products' ),
					'desc_tip' => __( 'Message displayed if visitor does not have access to the post and no products can grant access.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[post_content_delayed_message]',
					'class'    => 'input-text wide-input messages-group-posts',
					'name'     => __( 'Post Delayed (Members)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if access to blog post is not available yet.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'post_content_delayed_message' ),
					'desc_tip' => __( 'Message displayed if the current user is a member but does not have access to the post yet.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'sectionend',
					'class'    => 'messages-group-posts',
				),

				// ================
				//  Pages Messages
				// ================

				array(
					'name'     => __( 'Page Restricted Messages', 'woocommerce-memberships' ),
					'type'     => 'title',
					'class'    => 'messages-group-pages',
					'desc'     => __( 'The following messages may be shown to members and non-members when trying to access a page.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[page_content_restricted_message]',
					'class'    => 'input-text wide-input messages-group-pages',
					'name'     => __( 'Page Restricted (Product Purchase Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays when purchase is required to view the page.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'page_content_restricted_message' ),
					'desc_tip' => __( 'Message displayed if visitor does not have access to the page, but can purchase it.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[page_content_restricted_message_no_products]',
					'class'    => 'input-text wide-input messages-group-pages',
					'name'     => __( 'Page Restricted (Membership Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if the page is restricted to a membership that cannot be purchased.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'page_content_restricted_message_no_products' ),
					'desc_tip' => __( 'Message displayed if visitor does not have access to the page and no products can grant access.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[page_content_delayed_message]',
					'class'    => 'input-text wide-input messages-group-pages',
					'name'     => __( 'Page Delayed (Members)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if access to page is not available yet.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'page_content_delayed_message' ),
					'desc_tip' => __( 'Message displayed if the current user is a member but does not have access to the page yet.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'sectionend',
					'class'    => 'messages-group-pages',
				),

				// ==========================
				//  Generic Content Messages
				// ==========================

				array(
					'title'    => __( 'Content Restricted Messages', 'woocommerce-memberships' ),
					'class'    => 'messages-group-content',
					'type'     => 'title',
					'desc'     => __( 'The following messages may be shown to members and non-members when trying to access content that is not a product, blog post, or page (such as a custom content type).', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[content_restricted_message]',
					'class'    => 'input-text wide-input messages-group-content',
					'name'     => __( 'Content Restricted (Product Purchase Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays when purchase is required to view the content.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'content_restricted_message' ),
					'desc_tip' => __( 'Message displayed if visitor does not have access to the content, but can purchase it.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[content_restricted_message_no_products]',
					'class'    => 'input-text wide-input messages-group-content',
					'name'     => __( 'Content Restricted (Membership Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if the content is restricted to a membership that cannot be purchased.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'content_restricted_message_no_products' ),
					'desc_tip' => __( 'Message displayed if visitor does not have access to the content and no products can grant access.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[content_delayed_message]',
					'class'    => 'input-text wide-input messages-group-content',
					'name'     => __( 'Content Delayed (Members)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if access to content is not available yet.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'content_delayed_message' ),
					'desc_tip' => __( 'Message displayed if the current user is a member but does not have access to content yet.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'sectionend',
					'class'    => 'messages-group-content',
				),

				// ===================
				//  Products Messages
				// ===================

				array(
					'name'     => __( 'Product Restriction Messages', 'woocommerce-memberships' ),
					'type'     => 'title',
					'class'    => 'messages-group-products',
					'desc'     =>  __( 'The following messages may be shown to members and non-members when trying to view or purchase products.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_access_delayed_message]',
					'class'    => 'input-text wide-input messages-group-products',
					'name'     => __( 'Product Viewing or Purchasing Delayed (Members)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if access for viewing or purchasing a product is not available yet.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'product_access_delayed_message' ),
					'desc_tip' => __( 'Message displayed if the current user is a member but does not have access yet to view or purchase the product.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_viewing_restricted_message]',
					'class'    => 'input-text wide-input messages-group-products',
					'name'     => __( 'Product Viewing Restricted (Purchase Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays when purchase is required to view the product.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'product_viewing_restricted_message' ),
					'desc_tip' => __( 'Message displayed if viewing is restricted to members but access can be purchased.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_viewing_restricted_message_no_products]',
					'class'    => 'input-text wide-input messages-group-products',
					'name'     => __( 'Product Viewing Restricted (Membership Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if viewing is restricted to a membership that cannot be purchased.', 'woocommerce-memberships' ),
					'default'  => WC_Memberships_User_Messages::get_message( 'product_viewing_restricted_message_no_products' ),
					'desc_tip' => __( 'Message displayed if viewing is restricted to members and no products can grant access.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_purchasing_restricted_message]',
					'class'    => 'input-text wide-input messages-group-products',
					'name'     => __( 'Product Buying Restricted (Purchase Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays when purchase is required to buy the product.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'product_purchasing_restricted_message' ),
					'desc_tip' => __( 'Message displayed if purchasing is restricted to members but access can be purchased.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_purchasing_restricted_message_no_products]',
					'class'    => 'input-text wide-input messages-group-products',
					'name'     => __( 'Product Buying Restricted (Membership Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Displays if purchasing is restricted to a membership that cannot be purchased.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'product_purchasing_restricted_message_no_products' ),
					'desc_tip' => __( 'Message displayed if purchasing is restricted to members and no products can grant access.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'sectionend',
					'class'    => 'messages-group-products',
				),

				// ================
				//  Other Messages
				// ================

				array(
					'name'     => __( 'Purchasing Discount', 'woocommerce-memberships' ),
					'type'     => 'title',
					'desc'     => __( 'The following messages may be used to inform non-members of discounts.', 'woocommerce-memberships' ),
					'class'    => 'messages-group-discounts',
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_discount_message]',
					'class'    => 'input-text wide-input messages-group-discounts',
					'name'     => __( 'Product Discounted (Purchase Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Message displayed to non-members if the product has a member discount.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'product_discount_message' ),
					'desc_tip' => __( 'Displays below add to cart buttons. Leave blank to disable.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[product_discount_message_no_products]',
					'class'    => 'input-text wide-input messages-group-discounts',
					'name'     => __( 'Product Discounted (Membership Required)', 'woocommerce-memberships' ),
					'desc'     => __( 'Message displayed to non-members if the product has a member discount, but no products can grant access.', 'woocommerce-memberships' ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'product_discount_message_no_products' ),
					'desc_tip' => __( 'Displays below add to cart buttons. Leave blank to disable.', 'woocommerce-memberships' ),
				),

				array(
					'type'     => 'select',
					'id'       => 'wc_memberships_display_member_login_notice',
					'name'     => __( 'Member Discount Login Reminder', 'woocommerce-memberships' ),
					'options'  => array(
						'never'    => __( 'Do not show', 'woocommerce-memberships' ),
						'cart'     => __( 'Show on Cart Page', 'woocommerce-memberships' ),
						'checkout' => __( 'Show on Checkout Page', 'woocommerce-memberships' ),
						'both'     => __( 'Show on both Cart & Checkout Page', 'woocommerce-memberships' ),
					),
					'class'    => 'wc-enhanced-select messages-group-discounts',
					'desc_tip' => __( 'Select when & where to display login reminder notice for guests if products in cart have member discounts.', 'woocommerce-memberships' ),
					'default'  => 'both',
				),

				array(
					'type'     => 'textarea',
					'id'       => 'wc_memberships_messages[member_login_message]',
					'class'    => 'input-text wide-input messages-group-discounts',
					'name'     => __( 'Member Discount Login Message', 'woocommerce-memberships' ),
					'desc'     => __( 'Message to remind members to log in to claim a discount. Leave blank to use the default log in message.', 'woocommerce-memberships' ),
					/* translators: Placeholder: %s - a message text example */
					'placeholder' => sprintf( __( 'for example: "%s"', 'woocommerce-memberships' ), \WC_Memberships_User_Messages::get_message( 'cart_items_discount_message' ) ),
					'default'  => \WC_Memberships_User_Messages::get_message( 'member_login_message' ),
				),

				array(
					'type'     => 'sectionend',
					'class'    => 'messages-group-discounts',
				),

			) );

		} else { // general section

			// add this only if GDPR handling is available in WordPress
			$privacy_settings = version_compare( get_bloginfo( 'version' ), '4.9.5', '>' ) ? array(

				array(
					'name'    => __( 'Privacy', 'woocommerce-memberships' ),
					'type'    => 'title',
				),

				array(
					'type'    => 'checkbox',
					'id'      => 'wc_memberships_privacy_erasure_request_delete_user_memberships',
					'name'    => __( 'Account Erasure Requests', 'woocommerce-memberships' ),
					/* translators: Placeholders: %1$s - opening HTML <a> link tag , %2$s - closing HTML </a> link tag */
					'desc'    => sprintf( __( 'Delete all matching memberships when %1$susers request to erase their personal data%2$s.', 'woocommerce-memberships' ), '<a href="' . admin_url( 'tools.php?page=remove_personal_data' ) . '">', '</a>' ),
					'default' => 'no',
				),

				array(
					'type'    => 'sectionend',
				),

			) : array();

			/**
			 * Filters Memberships general settings.
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings associative array of the plugin settings
			 */
			$settings = (array) apply_filters( 'wc_memberships_general_settings', array_merge( array(

				array(
					'name'     => __( 'General', 'woocommerce-memberships' ),
					'type'     => 'title',
				),

				array(
					'type'     => 'select',
					'id'       => 'wc_memberships_restriction_mode',
					'name'     => __( 'Content Restriction Mode', 'woocommerce-memberships' ),
					'options'  => array(
						'hide'         => __( 'Hide completely', 'woocommerce-memberships' ),
						'hide_content' => __( 'Hide content only', 'woocommerce-memberships' ),
						'redirect'     => __( 'Redirect to page', 'woocommerce-memberships' ),
					),
					'class'    => 'wc-enhanced-select',
					'desc_tip' => __( 'Specifies the way content is restricted: whether to show nothing, excerpts, or send to a landing page.', 'woocommerce-memberships' ),
					'desc'     => '<ul><li>' . __( '"Hide completely" removes all traces of content for non-members, search engines and 404s restricted pages.', 'woocommerce-memberships' ) . '</li><li>' . __( '"Hide content only" will show items in archives, but protect page or post content and comments.', 'woocommerce-memberships' ) . '</li></ul>',
					'default'  => 'hide_content',
				),

				array(
					'title'    => __( 'Redirect Page', 'woocommerce-memberships' ),
					'desc'     => __( 'Select the page to redirect non-members to - should contain the [wcm_content_restricted] shortcode.', 'woocommerce-memberships' ),
					'id'       => 'wc_memberships_redirect_page_id',
					'type'     => 'single_select_page',
					'class'    => 'wc-enhanced-select-nostd js-redirect-page',
					'css'      => 'min-width:300px;',
					'desc_tip' => true,
				),

				array(
					'type'     => 'checkbox',
					'id'       => 'wc_memberships_show_excerpts',
					'name'     => __( 'Show Excerpts', 'woocommerce-memberships' ),
					'desc'     => __( 'If enabled, an excerpt of the protected content will be displayed to non-members & search engines.', 'woocommerce-memberships' ),
					'default'  => 'yes',
				),

				array(
					'type'     => 'sectionend',
				),

			), $privacy_settings ) );
		}

		/**
		 * Filters Memberships Settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array $settings array of the plugin settings
		 * @param string $current_section the current section being output
		 */
		return apply_filters( "woocommerce_get_settings_{$this->id}", $settings, $current_section );
	}


	/**
	 * Outputs the settings fields.
	 *
	 * @since 1.0.0
	 */
	public function output() {
		global $current_section;

		\WC_Admin_Settings::output_fields( $this->get_settings( $current_section ) );
	}


	/**
	 * Saves the settings.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function save() {
		global $current_section;

		\WC_Admin_Settings::save_fields( $this->get_settings( $current_section ) );
	}


	/**
	 * Adds custom slugs for endpoints in My Account page.
	 *
	 * Filter callback for woocommerce_account_settings.
	 *
	 * @internal
	 *
	 * @since 1.4.0
	 *
	 * @param array $settings
	 * @return array $settings
	 */
	public function add_my_account_endpoints_options( $settings ) {

		$new_settings = array();

		foreach ( $settings as $setting ) {

			$new_settings[] = $setting;

			if ( isset( $setting['id'] ) && 'woocommerce_logout_endpoint' === $setting['id'] ) {

				$new_settings[] = array(
					'title'    => __( 'My Membership', 'woocommerce-memberships' ),
					'desc'     => __( 'Endpoint for the My Account &rarr; My Membership', 'woocommerce-memberships' ),
					'id'       => 'woocommerce_myaccount_members_area_endpoint',
					'type'     => 'text',
					'default'  => 'members-area',
					'desc_tip' => true,
				);
			}
		}

		return $new_settings;
	}


}
