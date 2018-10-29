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
 * Main admin class.
 *
 * @since 1.0.0
 */
class WC_Memberships_Admin {


	/** @var \SV_WP_Admin_Message_Handler instance */
	public $message_handler; // this is passed from \WC_Memberships and can't be protected

	/** @var \WC_Memberships_Admin_Import_Export_Handler instance */
	protected $import_export;

	/** @var \WC_Memberships_Admin_User_Memberships instance */
	protected $user_memberships;

	/** @var \WC_Memberships_Admin_Membership_Plans instance */
	protected $membership_plans;

	/** @var \WC_Memberships_Admin_Users instance */
	protected $users;

	/** @var \WC_Memberships_Admin_Orders instance */
	protected $orders;

	/** @var \WC_Memberships_Admin_Products instance */
	protected $products;

	/** @var stdClass container of modals instances */
	protected $modals;

	/** @var stdClass container of meta boxes instances */
	protected $meta_boxes;


	/**
	 * Init Memberships admin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load rules admin helper static class
		require_once( wc_memberships()->get_plugin_path() . '/includes/admin/class-wc-memberships-admin-membership-plan-rules.php' );

		// display admin messages
		add_action( 'admin_notices', array( $this, 'show_admin_messages' ) );

		// init settings page
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );
		// email settings hooks
		add_action( 'woocommerce_email_settings_after', array( $this, 'handle_email_settings_pages' ) );
		// render Memberships admin tabs for pages with Memberships' own custom post types
		add_action( 'all_admin_notices', array( $this, 'render_tabs' ), 5 );
		// init content in Memberships tabbed admin pages
		add_action( 'current_screen', array( $this, 'init' ) );
		// init import/export page
		add_action( 'admin_menu',  array( $this, 'add_import_export_admin_page' ) );

		// conditionally remove duplicate submenu link
		add_action( 'admin_menu', array( $this, 'remove_submenu_link' ) );
		// remove "New User Membership" item from Admin bar
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 9999 );

		// enqueue admin scripts & styles
		add_action( 'admin_enqueue_scripts', array( $this,  'enqueue_scripts_and_styles' ) );
		// load admin scripts & styles
		add_filter( 'woocommerce_screen_ids', array( $this, 'load_wc_scripts' ) );
	}


	/**
	 * Returns the Message Handler instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \SV_WP_Admin_Message_Handler
	 */
	public function get_message_handler() {
		// note: this property is public since it needs to be passed from the main class
		return $this->message_handler;
	}


	/**
	 * Returns the Users admin handler instance.
	 *
	 * @since 1.7.4
	 *
	 * @return \WC_Memberships_Admin_Users
	 */
	public function get_users_instance() {
		return $this->users;
	}


	/**
	 * Returns the User Memberships admin handler instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Admin_User_Memberships
	 */
	public function get_user_memberships_instance() {
		return $this->user_memberships;
	}


	/**
	 * Returns the User Memberships admin handler instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Admin_Membership_Plans
	 */
	public function get_membership_plans_instance() {
		return $this->membership_plans;
	}


	/**
	 * Returns the Import / Export Handler instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Admin_Import_Export_Handler
	 */
	public function get_import_export_handler_instance() {
		return $this->import_export;
	}


	/**
	 * Returns the products admin handler instance.
	 *
	 * @since 1.9.0
	 *
	 * @return \WC_Memberships_Admin_Products
	 */
	public function get_products_instance() {
		return $this->products;
	}


	/**
	 * Returns the orders admin handler instance.
	 *
	 * @since 1.9.0
	 *
	 * @return \WC_Memberships_Admin_Orders
	 */
	public function get_orders_instance() {
		return $this->orders;
	}


	/**
	 * Returns Memberships admin screen IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $context when a context is specified, screens for a particular group are displayed (default null: display all)
	 * @return string[] list of admin screen IDs where Memberships does something
	 */
	public function get_screen_ids( $context = null ) {

		$settings_page_id   = Framework\SV_WC_Plugin_Compatibility::normalize_wc_screen_id();

		$tabs_screens       = array(
			// User Membership screens:
			'wc_user_membership',
			'edit-wc_user_membership',
			// Membership Plan screens:
			'wc_membership_plan',
			'edit-wc_membership_plan',
			// User Memberships Import/Export screens:
			'wc_memberships_import_export',
			'admin_page_wc_memberships_import_export',
		);

		$modal_screens      = array(
			// User Membership screens:
			'wc_user_membership',
			'edit-wc_user_membership',
			// Membership Plan screens:
			'wc_membership_plan',
			'edit-wc_membership_plan',
			// WooCommerce Settings page tab
			$settings_page_id,
			// User Memberships Import/Export screens:
			'wc_memberships_import_export',
			'admin_page_wc_memberships_import_export',
		);

		$scripts_screens    = array(
			// User screens:
			'users',
			'user-edit',
			'profile',
			// WooCommerce Settings page tab
			$settings_page_id,
		);

		$meta_boxes_screens = array(
			// User Membership screens:
			'wc_user_membership',
			'edit-wc_user_membership',
			// Membership Plan screens:
			'wc_membership_plan',
			'edit-wc_membership_plan',
		);

		if ( class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ) {
			// post types edit screens, including products, where plan rules are applicable
			foreach ( array_keys( WC_Memberships_Admin_Membership_Plan_Rules::get_valid_post_types_for_content_restriction_rules( false ) ) as $post_type ) {
				$meta_boxes_screens[] = $post_type;
				$meta_boxes_screens[] = "edit-{$post_type}";
			}
		}

		/**
		 * Filters Memberships admin screen IDs.
		 *
		 * @since 1.9.0
		 *
		 * @param array $screen_ids associative array organized by context
		 */
		$screen_ids = (array) apply_filters( 'wc_memberships_admin_screen_ids', array(
			'meta_boxes' => $meta_boxes_screens,
			'modals'     => $modal_screens,
			'scripts'    => array_merge( $tabs_screens, $scripts_screens, $meta_boxes_screens, $modal_screens ),
			'tabs'       => $tabs_screens,
		) );

		// return all screens or screens belonging to a particular group
		if ( null !== $context && isset( $screen_ids[ $context ] ) ) {
			$screen_ids = $screen_ids[ $context ];
		}

		// apparently here in some circumstances we need a sort argument or an array to string notice may be thrown...
		return array_unique( array_values( $screen_ids ), SORT_REGULAR );
	}


	/**
	 * Checks if we are on a Memberships admin screen.
	 *
	 * @since 1.6.0
	 *
	 * @param string $screen_id a screen ID to check - default blank, will try to determine the current admin screen
	 * @param string|string[] $which check for a specific screen type (or array of types) or leave 'any' to check if the current screen is one of the memberships screens
	 * @param bool $exclude_content if set to false (default) the check will exclude Memberships restrictable post types edit screens
	 * @return bool
	 */
	public function is_memberships_admin_screen( $screen_id = '', $which = 'any', $exclude_content = false ) {

		$screen = empty( $screen_id ) ? get_current_screen() : $screen_id;

		if ( $screen instanceof \WP_Screen ) {
			$screen_id = $screen->id;
		}

		$is_screen = false;

		if ( is_string( $screen_id ) ) {

			$screen_ids = $this->get_screen_ids();

			if ( true === $exclude_content ) {
				unset( $screen_ids['content'] );
			}

			$is_screen = $screen_id && in_array( $screen_id, $screen_ids, true );

			if ( 'any' !== $which ) {
				if ( is_array( $which ) ) {
					$is_screen = in_array( $screen_id, $which, true );
				} else {
					$is_screen = $is_screen && $screen_id === $which;
				}
			}
		}

		return $is_screen;
	}


	/**
	 * Checks if the current screen is a Memberships import or export page.
	 *
	 * @since 1.9.0
	 *
	 * @param null|\WP_Screen|string $screen optional, defaults to current screen global
	 * @return bool
	 */
	public function is_memberships_import_export_admin_screen( $screen = null ) {
		return $this->is_memberships_admin_screen( $screen, 'admin_page_wc_memberships_import_export', true );
	}


	/**
	 * Checks if the current screen is a screen that contains a membership modal.
	 *
	 * @since 1.9.0
	 *
	 * @param null|\WP_Screen|string $screen optional, defaults to current screen global
	 * @return bool
	 */
	public function is_memberships_modal_admin_screen( $screen = null ) {
		return $this->is_memberships_admin_screen( $screen, $this->get_screen_ids( 'modals' ) );
	}


	/**
	 * Adds Memberships settings page to WooCommerce settings.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings
	 * @return array
	 */
	public function add_settings_page( $settings ) {

		$settings[] = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-settings.php', 'WC_Settings_Memberships' );

		return $settings;
	}


	/**
	 * Ensures that there are no ongoing batch jobs when opening the emails settings pages.
	 *
	 * There can be only one batch job at any time so if a job was abandoned, but for some reason it wasn't cancelled, it can be cancelled before a new modal is opened.
	 *
	 * @internal
	 *
	 * @since 1.10.0
	 */
	public function handle_email_settings_pages() {

		if ( isset( $_GET['tab'], $_GET['section'] ) && 'email' === $_GET['tab'] && in_array( $_GET['section'], array( 'wc_memberships_user_membership_ending_soon_email', 'wc_memberships_user_membership_renewal_reminder_email' ), true ) ) {

			$handler = wc_memberships()->get_utilities_instance()->get_user_memberships_reschedule_events_instance();

			if ( $current_job = $handler->get_job() ) {
				$handler->delete_job( $current_job->id );
			}
		}
	}


	/**
	 * Adds Import / Export page for Memberships admin page.
	 *
	 * @internal
	 *
	 * @since 1.6.0
	 */
	public function add_import_export_admin_page() {

		/**
		 * Set minimum capability to use Import / Export features.
		 *
		 * @since 1.6.0
		 *
		 * @param string $capability defaults to Shop Managers with 'manage_woocommerce'
		 */
		$capability = apply_filters( 'woocommerce_memberships_can_import_export', 'manage_woocommerce' );

		add_submenu_page(
			'',
			__( 'Import / Export', 'woocommerce-memberships' ),
			__( 'Import / Export', 'woocommerce-memberships' ),
			$capability,
			'wc_memberships_import_export',
			array( $this, 'render_import_export_admin_page' )
		);
	}


	/**
	 * Renders the Import / Export admin page.
	 *
	 * @internal
	 *
	 * @since 1.6.0
	 */
	public function render_import_export_admin_page() {

		/**
		 * Outputs the Import / Export admin page.
		 *
		 * @since 1.6.0
		 */
		do_action( 'wc_memberships_render_import_export_page' );
	}


	/**
	 * Initializes the main admin screen.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( $screen = get_current_screen() ) {

			switch ( $screen->id ) {

				// subscriptions are correctly handled here as orders subclasses
				case 'shop_order' :
				case 'edit-shop_order' :
				case 'shop_subscription' :
				case 'edit-shop_subscription' :
					$this->orders           = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-admin-orders.php', 'WC_Memberships_Admin_Orders' );
				break;

				case 'product' :
				case 'edit-product' :
					$this->products         = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-admin-products.php', 'WC_Memberships_Admin_Products' );
				break;

				case 'wc_membership_plan' :
				case 'edit-wc_membership_plan' :
					$this->membership_plans = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-admin-membership-plans.php',  'WC_Memberships_Admin_Membership_Plans');
				break;

				case 'wc_user_membership' :
				case 'edit-wc_user_membership' :
					$this->user_memberships = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-admin-user-memberships.php',  'WC_Memberships_Admin_User_Memberships' );
					// the import / export handler runs bulk export on User Memberships screen
					$this->import_export    = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-import-export-handler.php', 'WC_Memberships_Admin_Import_Export_Handler' );
				break;

				case 'admin_page_wc_memberships_import_export' :
					$this->import_export    = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-import-export-handler.php', 'WC_Memberships_Admin_Import_Export_Handler' );
				break;

				case 'users' :
				case 'user-edit' :
				case 'profile' :
					$this->users            = wc_memberships()->load_class( '/includes/admin/class-wc-memberships-admin-users.php', 'WC_Memberships_Admin_Users' );
				break;
			}

			// init modals in screens where they could be opened
			if ( in_array( $screen->id, $this->get_screen_ids( 'modals' ), true ) ) {
				$this->init_modals();
			}

			// init meta boxes on restrictable post types edit screens
			if ( in_array( $screen->id, $this->get_screen_ids( 'meta_boxes' ), true ) ) {
				$this->init_meta_boxes();
			}
		}
	}


	/**
	 * Loads and instantiates classes helper.
	 *
	 * @since 1.9.0
	 *
	 * @param string $prefix prefix of each class name
	 * @param string[] $object_names array of class names
	 * @param string $path relative path to where the classes to load are
	 * @return array
	 */
	private function init_objects( $prefix, $object_names, $path ) {

		$objects = array();

		foreach ( $object_names as $class ) {

			$file_name  = 'class-'. strtolower( str_replace( '_', '-', $class ) ) . '.php';
			$file_path  = wc_memberships()->get_plugin_path() . $path . $file_name;

			if ( is_readable( $file_path ) && ! class_exists( $class ) ) {

				require_once( $file_path );

				if ( class_exists( $class ) ) {

					$object_name = strtolower( str_replace( $prefix . '_', '', $class ) );

					$objects[ $object_name ] = new $class();
				}
			}
		}

		return $objects;
	}


	/**
	 * Loads modal templates.
	 *
	 * @since 1.9.0
	 */
	private function init_modals() {

		if ( $screen = get_current_screen() ) {

			// load abstracts
			require_once( wc_memberships()->get_plugin_path() . '/includes/admin/modals/abstract-wc-memberships-modal.php' );
			require_once( wc_memberships()->get_plugin_path() . '/includes/admin/modals/abstract-wc-memberships-member-modal.php' );
			require_once( wc_memberships()->get_plugin_path() . '/includes/admin/modals/abstract-wc-memberships-batch-job-modal.php' );

			$this->modals   = new stdClass();
			$modals_classes = array();

			// new user membership screen
			if ( 'edit-wc_user_membership' === $screen->id ) {
				$modals_classes[] = 'WC_Memberships_Modal_Add_User_Membership';
				$modals_classes[] = 'WC_Memberships_Modal_Import_Export_User_Memberships';
			// edit user membership screen
			} elseif ( 'wc_user_membership' === $screen->id ) {
				$modals_classes[] = 'WC_Memberships_Modal_Add_User_Membership';
				$modals_classes[] = 'WC_Memberships_Modal_Transfer_User_Membership';
				$modals_classes[] = 'WC_Memberships_Modal_Import_Export_User_Memberships';
			// membership plan screens
			} elseif ( in_array( $screen->id, array( 'wc_membership_plan', 'edit-wc-membership-plan' ), true ) ) {
				$modals_classes[] = 'WC_Memberships_Modal_Grant_Access_Membership_Plan';
			// user memberships import/export screens
			} elseif ( 'admin_page_wc_memberships_import_export' === $screen->id ) {
				$modals_classes[] = 'WC_Memberships_Modal_Import_Export_User_Memberships';
			// email settings screens
			} elseif ( isset( $_GET['tab'], $_GET['section'] ) && 'email' === $_GET['tab'] && in_array( $_GET['section'], array( 'wc_memberships_user_membership_ending_soon_email', 'wc_memberships_user_membership_renewal_reminder_email' ), true ) && Framework\SV_WC_Plugin_Compatibility::normalize_wc_screen_id() === $screen->id ) {
				$modals_classes[] = 'WC_Memberships_Modal_Reschedule_User_Memberships_Events';
			}

			// load and instantiate objects
			$modals = $this->init_objects( 'WC_Memberships_Modal', $modals_classes, '/includes/admin/modals/' );

			/**
			 * Filter Memberships admin modals.
			 *
			 * @since 1.9.0
			 *
			 * @param \WC_Memberships_Modal[] $modals an associative array of modals names and instances
			 * @param \WP_Screen $screen the current screen
			 */
			$modals = apply_filters( 'wc_memberships_modals', $modals, $screen );

			foreach ( $modals as $modal_name => $modal_object ) {
				if ( ! empty( $modal_name ) ) {
					$this->modals->$modal_name = $modal_object;
				}
			}
		}
	}


	/**
	 * Loads meta boxes.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 */
	private function init_meta_boxes() {
		global $pagenow;

		$screen = get_current_screen();

		// bail out if not on a new post / edit post screen
		if ( ! $screen || ( 'post-new.php' !== $pagenow && 'post.php' !== $pagenow ) ) {
			return;
		}

		$meta_box_classes = array();

		// load meta boxes abstract class
		if ( ! class_exists( 'WC_Memberships_Meta_Box' ) ) {
			require_once( wc_memberships()->get_plugin_path() . '/includes/admin/meta-boxes/abstract-wc-memberships-meta-box.php' );
		}

		$this->meta_boxes = new stdClass();

		// load restriction meta boxes on post screen only
		$meta_box_classes[] = 'WC_Memberships_Meta_Box_Post_Memberships_Data';

		// product-specific meta boxes
		if ( 'product' === $screen->id ) {
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_Product_Memberships_Data';
		}

		// load user membership meta boxes on user membership screen only
		if ( 'wc_membership_plan' === $screen->id ) {
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_Membership_Plan_Data';
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_Membership_Plan_Email_Content_Merge_Tags';
		}

		// load user membership meta boxes on user membership screen only
		if ( 'wc_user_membership' === $screen->id ) {
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_User_Membership_Data';
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_User_Membership_Notes';
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_User_Membership_Member_Details';
			$meta_box_classes[] = 'WC_Memberships_Meta_Box_User_Membership_Recent_Activity';
		}

		// load and instantiate objects
		$meta_boxes = $this->init_objects( 'WC_Memberships_Meta_Box', array_unique( $meta_box_classes ), '/includes/admin/meta-boxes/' );

		/**
		 * Filter Memberships admin meta boxes.
		 *
		 * @since 1.9.0
		 *
		 * @param \WC_Memberships_Meta_Box[] $meta_boxes an associative array of meta boxes names and instances
		 * @param \WP_Screen $screen the current screen
		 */
		$meta_boxes = apply_filters( 'wc_memberships_meta_boxes', $meta_boxes, $screen );

		foreach ( $meta_boxes as $meta_box_name => $meta_box_object ) {
			$this->meta_boxes->$meta_box_name = $meta_box_object;
		}
	}


	/**
	 * Returns meta boxes instances.
	 *
	 * @since 1.0.0
	 *
	 * @return \stdClass object containing \WC_Memberships_Meta_Box instances for properties
	 */
	public function get_meta_boxes() {
		return $this->meta_boxes;
	}


	/**
	 * Returns the admin meta box IDs.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] array of meta box IDs
	 */
	public function get_meta_box_ids() {

		$ids = array();

		foreach ( (array) $this->get_meta_boxes() as $meta_box ) {
			$ids[] = $meta_box->get_id();
		}

		return $ids;
	}


	/**
	 * Returns modals instances.
	 *
	 * @since 1.9.0
	 *
	 * @return \stdClass object containing instances of \WC_Memberships_Modal for properties
	 */
	public function get_modals() {
		return $this->modals;
	}


	/**
	 * Enqueues admin scripts & styles conditionally.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts_and_styles() {

		$screen = get_current_screen();

		if ( $screen && in_array( $screen->id, $this->get_screen_ids( 'scripts' ), true ) ) {

			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
	}


	/**
	 * Enqueues admin styles.
	 *
	 * @since 1.8.0
	 */
	private function enqueue_styles() {

		wp_enqueue_style( 'wc-memberships-admin', wc_memberships()->get_plugin_url() . '/assets/css/admin/wc-memberships-admin.min.css', array(), \WC_Memberships::VERSION );
	}


	/**
	 * Enqueues admin scripts conditionally.
	 *
	 * @since 1.8.0
	 */
	private function enqueue_scripts() {

		$screen   = get_current_screen();
		$path     = wc_memberships()->get_plugin_url() . '/assets/js/admin/';
		$ver      = \WC_Memberships::VERSION;

		// base scripts
		wp_register_script( 'wc-memberships-enhanced-select', $path . 'wc-memberships-enhanced-select.min.js', array( 'jquery', 'select2' ), $ver );
		wp_register_script( 'wc-memberships-rules',           $path . 'wc-memberships-rules.min.js',           array( 'wc-memberships-enhanced-select' ), $ver );
		wp_register_script( 'wc-memberships-modal',           $path . 'wc-memberships-modal.min.js',           array( 'jquery', 'backbone', 'wc-backbone-modal' ), $ver );
		wp_register_script( 'wc-memberships-modals',          $path . 'wc-memberships-member-modals.min.js',   array( 'wc-memberships-modal', 'wc-memberships-enhanced-select' ), $ver );
		wp_enqueue_script(  'wc-memberships-admin',           $path . 'wc-memberships-admin.min.js',           array( 'wc-memberships-rules' ), $ver );

		// plans edit screens
		if ( $screen && in_array( $screen->id, array( 'wc_membership_plan', 'edit-wc_membership_plan' ), false ) ) {

			wp_enqueue_script( 'wc-memberships-membership-plans', $path . 'wc-memberships-plans.min.js', array( 'wc-memberships-admin', 'wc-memberships-modal', 'jquery-ui-datepicker' ), $ver );

		// user memberships screens and import export screens
		} elseif ( $screen && in_array( $screen->id, array( 'admin_page_wc_memberships_import_export', 'wc_user_membership', 'edit-wc_user_membership' ), false ) ) {

			// user memberships screens only
			if ( in_array( $screen->id, array( 'wc_user_membership', 'edit-wc_user_membership' ), false ) ) {

				wp_enqueue_script( 'wc-memberships-user-memberships', $path . 'wc-memberships-user-memberships.min.js', array( 'wc-memberships-modals', 'jquery-ui-datepicker' ), $ver );
			}

			// export scripts are also loaded on the memberships edit screen for bulk exports
			wp_enqueue_script( 'wc-memberships-import-export', $path . 'wc-memberships-import-export.min.js', array( 'wc-memberships-modal', 'wc-memberships-enhanced-select', 'jquery-ui-datepicker' ), $ver );

		// product screens
		} elseif ( $screen && in_array( $screen->id, array( 'product', 'edit-product' ), true ) ) {

			wp_enqueue_script( 'wc-memberships-modals' );

		// settings pages, including memberships emails settings
		} elseif ( wc_memberships()->is_plugin_settings() ) {

			wp_enqueue_script( 'wc-memberships-settings', $path . 'wc-memberships-settings.min.js', array( 'wc-memberships-modal', 'wc-memberships-enhanced-select', 'jquery-ui-datepicker' ), $ver );
		}

		// localize the main admin script to add variable properties and localization strings.
		wp_localize_script( 'wc-memberships-admin', 'wc_memberships_admin', array(

			// add any config/state properties here, for example:
			// 'is_user_logged_in' => is_user_logged_in()

			'ajax_url'                                  => admin_url( 'admin-ajax.php' ),
			'new_membership_url'                        => admin_url( 'post-new.php?post_type=wc_user_membership' ),
			'select2_version'                           => Framework\SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ? '4.0.3' : '3.5.3',
			'wc_plugin_url'                             => WC()->plugin_url(),
			'calendar_image'                            => WC()->plugin_url() . '/assets/images/calendar.png',
			'user_membership_url'                       => admin_url( 'edit.php?post_type=wc_user_membership' ),
			'new_user_membership_url'                   => admin_url( 'post-new.php?post_type=wc_user_membership' ),
			'restrictable_post_types'                   => array_keys( WC_Memberships_Admin_Membership_Plan_Rules::get_valid_post_types_for_content_restriction_rules( false ) ),
			'search_products_nonce'                     => wp_create_nonce( 'search-products' ),
			'search_posts_nonce'                        => wp_create_nonce( 'search-posts' ),
			'search_terms_nonce'                        => wp_create_nonce( 'search-terms' ),
			'get_membership_date_nonce'                 => wp_create_nonce( 'get-membership-date' ),
			'search_customers_nonce'                    => wp_create_nonce( 'search-customers' ),
			'add_user_membership_note_nonce'            => wp_create_nonce( 'add-user-membership-note' ),
			'create_user_for_membership_nonce'          => wp_create_nonce( 'create-user-for-membership' ),
			'transfer_user_membership_nonce'            => wp_create_nonce( 'transfer-user-membership' ),
			'delete_user_membership_note_nonce'         => wp_create_nonce( 'delete-user-membership-note' ),
			'delete_user_membership_subscription_nonce' => wp_create_nonce( 'delete-user-membership-with-subscription' ),
			'get_memberships_batch_job_nonce'           => wp_create_nonce( 'get-memberships-batch-job' ),
			'remove_memberships_batch_job_nonce'        => wp_create_nonce( 'remove-memberships-batch-job' ),
			'grant_retroactive_access_nonce'            => wp_create_nonce( 'grant-retroactive-access' ),
			'reschedule_user_memberships_events_nonce'  => wp_create_nonce( 'reschedule-user-memberships-events' ),
			'export_user_memberships_nonce'             => wp_create_nonce( 'export-user-memberships' ),
			'import_user_memberships_nonce'             => wp_create_nonce( 'import-user-memberships' ),

			'i18n' => array(

				// add i18n strings here, for example:
				// 'log_in' => __( 'Log In', 'woocommerce-memberships' )

				'delete_membership_confirm'  => __( 'Are you sure that you want to permanently delete this membership?', 'woocommerce-memberships' ),
				'delete_memberships_confirm' => __( 'Are you sure that you want to permanently delete these memberships?', 'woocommerce-memberships' ),
				'please_select_user'         => __( 'Please select a user.', 'woocommerce-memberships' ),
				'reschedule'                 => __( 'Reschedule', 'woocommerce-memberships' ),
				'export_user_memberships'    => __( 'Export User Memberships', 'woocommerce-memberships' ),
				'import_file_missing'        => __( 'Please upload a file to import memberships from.', 'woocommerce-memberships' ),
				'confirm_export_cancel'      => __( 'Are you sure you want to cancel this export?', 'woocommerce-memberships' ),
				'confirm_import_cancel'      => __( 'Are you sure you want to cancel this import?', 'woocommerce-memberships' ),
				'confirm_stop_batch_job'     => __( 'Are you sure you want to stop the current batch process?', 'woocommerce-memberships' ),

			),
		) );
	}


	/**
	 * Adds settings/export screen ID to the list of pages for WC to load its JS on.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $screen_ids
	 * @return array
	 */
	public function load_wc_scripts( $screen_ids ) {
		return array_merge( $screen_ids, $this->get_screen_ids( 'scripts' ) );
	}


	/**
	 * Removes the duplicate submenu link for Memberships custom post type that is not being viewed.
	 *
	 * It's easier to add both submenu links via register_post_type() and conditionally remove them here than it is try to add them both correctly.
	 *
	 * @internal
	 *
	 * @since 1.2.0
	 */
	public function remove_submenu_link() {
		global $pagenow, $typenow;

		$submenu_slug = 'edit.php?post_type=wc_membership_plan';

		// remove user membership submenu page when viewing or editing membership plans
		if (    ( 'edit.php' === $pagenow && 'wc_membership_plan' === $typenow )
		     || ( 'post.php' === $pagenow && isset( $_GET['post'] ) && 'wc_membership_plan' === get_post_type( $_GET['post'] ) ) ) {

			$submenu_slug = 'edit.php?post_type=wc_user_membership';
		}

		remove_submenu_page( 'woocommerce', $submenu_slug );
	}


	/**
	 * Returns the current admin tab.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param string $current_tab current tab slug, defaults to user memberships
	 * @return string
	 */
	public function get_current_tab( $current_tab = 'members' ) {

		if ( $screen = get_current_screen() ) {
			if ( in_array( $screen->id, array( 'wc_membership_plan', 'edit-wc_membership_plan' ), true ) ) {
				$current_tab = 'memberships';
			} elseif ( in_array( $screen->id, array( 'wc_user_membership', 'edit-wc_user_membership' ), true ) ) {
				$current_tab = 'members';
			} elseif ( $this->is_memberships_import_export_admin_screen() ) {
				$current_tab = 'import-export';
			}
		}

		/**
		 * Filters the current Memberships Admin tab.
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_tab the current tab (defaults to 'members')
		 * @param \WP_Screen $screen the current screen
		 */
		return (string) apply_filters( 'wc_memberships_admin_current_tab', $current_tab, $screen );
	}


	/**
	 * Renders tabs on our custom post types pages.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function render_tabs() {
		global $post, $typenow;

		$screen = get_current_screen();

		// handle tabs on the relevant WooCommerce pages
		if ( $screen && in_array( $screen->id, $this->get_screen_ids( 'tabs' ), true ) ) :

			$tabs = apply_filters( 'wc_memberships_admin_tabs', array(
				'members'       => array(
					'title' => __( 'Members', 'woocommerce-memberships' ),
					'url'   => admin_url( 'edit.php?post_type=wc_user_membership' ),
				),
				'memberships'   => array(
					'title' => wp_is_mobile() ? __( 'Plans', 'woocommerce-memberships' ) : __( 'Membership Plans', 'woocommerce-memberships' ),
					'url'   => admin_url( 'edit.php?post_type=wc_membership_plan' ),
				),
				'import-export' => array(
					'title' => __( 'Import / Export', 'woocommerce-memberships' ),
					'url'   => admin_url( 'admin.php?page=wc_memberships_import_export' ),
				),
			) );

			if ( is_array( $tabs ) ) :

				?>
				<div class="wrap woocommerce">
					<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
						<?php $current_tab = $this->get_current_tab(); ?>
						<?php $current_tab = 'members' === $current_tab && 'admin_page_wc_memberships_import_export' === $screen->id ? 'import-export' : $current_tab; ?>
						<?php foreach ( $tabs as $tab_id => $tab ) : ?>
							<?php $class = $tab_id === $current_tab ? array( 'nav-tab', 'nav-tab-active' ) : array( 'nav-tab' ); ?>
							<?php printf( '<a href="%1$s" class="%2$s">%3$s</a>', esc_url( $tab['url'] ), implode( ' ', array_map( 'sanitize_html_class', $class ) ), esc_html( $tab['title'] ) ); ?>
						<?php endforeach; ?>
					</h2>
				</div>
				<?php

			endif;

		// warn users against the usage of 'woocommerce_my_account' deprecated shortcode attributes as these could conflict with Memberships and trigger a server error in the Members Area
		elseif ( 'page' === $typenow && ( $post && ( (int) $post->ID === (int) wc_get_page_id( 'myaccount' ) || has_shortcode( $post->post_content, 'woocommerce_my_account' ) ) ) ) :

			preg_match_all('/' . get_shortcode_regex() .'/s', $post->post_content, $matches );

			if ( isset( $matches[2], $matches[3] ) && ( is_array( $matches[2] ) && is_array( $matches[3] ) ) ) {

				$position = null;

				foreach ( $matches[2] as $key => $found_shortcode ) {
					if ( 'woocommerce_my_account' === $found_shortcode ) {
						$position = $key;
						break;
					}
				}

				if ( null !== $position && ! empty( $matches[3][ $position ] ) ) {

					$has_atts = trim( $matches[3][ $position ] );

					if ( ! empty( $has_atts ) ) {

						?>
						<div class="notice notice-warning">
							<p><?php
								/* translators: Placeholders: %1$s - the 'woocommerce_my_account' shortcode, %2$s - the 'order_count' shortcode attribute */
								printf( __( 'It looks like you might be using the %1$s shortcode with deprecated attributes, such as %2$s. These attributes have been deprecated since WooCommerce 2.6 and may no longer have any effect on the shortcode output. Furthermore, they might cause a server error when visiting the Members Area while WooCommerce Memberships is active.', 'woocommerce-memberships' ), '<code>woocommerce_my_account</code>', '<code>order_count</code>' );
								?></p>
						</div>
						<?php
					}
				}
			}

		endif;
	}


	/**
	 * Displays admin messages.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function show_admin_messages() {

		$this->message_handler->show_messages();
	}


	/**
	 * Removes New User Membership menu option from Admin Bar.
	 *
	 * @internal
	 *
	 * @since 1.3.0
	 *
	 * @param \WP_Admin_Bar $admin_bar WP_Admin_Bar instance, passed by reference
	 */
	public function admin_bar_menu( $admin_bar ) {

		$admin_bar->remove_menu( 'new-wc_user_membership' );
	}


	/**
	 * Backwards compatibility handler for deprecated methods.
	 *
	 * TODO remove deprecated methods when they are at least 3 minor versions older (as in x.Y.z semantic versioning) {FN 2017-23-06}
	 *
	 * @since 1.6.0
	 * @param string $method method called
	 * @param void|string|array|mixed $args optional argument(s)
	 * @return null|void|mixed
	 */
	public function __call( $method, $args ) {

		$deprecated = "WC_Memberships_Admin::{$method}()";

		switch ( $method ) {

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'get_valid_post_types_for_content_restriction' :
				_deprecated_function( "WC_Memberships_Admin::{$method}", '1.9.0', 'WC_Memberships_Admin_Membership_Plan_Rules::get_valid_post_types_for_content_restriction_rules()' );
				return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_post_types_for_content_restriction_rules() : null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'get_valid_taxonomies_for_rule_type' :
				_deprecated_function( "WC_Memberships_Admin::{$method}", '1.9.0', 'WC_Memberships_Admin_Membership_Plan_Rules class methods' );
				switch ( isset( $args[0] ) ? $args[0] : $args ) {
					case 'content_restriction' :
						return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_content_restriction_rules() : null;
					case 'product_restriction' :
						return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_product_restriction_rules() : null;
					case 'purchasing_discount' :
						return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_purchasing_discounts_rules() : null;
					default :
						return null;
				}

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'get_valid_taxonomies_for_content_restriction' :
				_deprecated_function( "WC_Memberships_Admin::{$method}", '1.9.0', 'WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_content_restriction_rules()' );
				return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_content_restriction_rules() : null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'get_valid_taxonomies_for_product_restriction' :
				_deprecated_function( "WC_Memberships_Admin::{$method}", '1.9.0', 'WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_product_restriction_rules()' );
				return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_product_restriction_rules() : null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'get_valid_taxonomies_for_purchasing_discounts' :
				_deprecated_function( "WC_Memberships_Admin::{$method}", '1.9.0', 'WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_purchasing_discounts_rules()' );
				return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::get_valid_taxonomies_for_purchasing_discounts_rules() : null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'update_rules' :
				_deprecated_function( "WC_Memberships_Admin::{$method}()", '1.9.0', 'WC_Memberships_Admin_Membership_Plan_Rules::save_rules()' );
				$post_id    = isset( $args[0] ) ? $args[0] : 0;
				$rule_types = isset( $args[1] ) ? $args[1] : array();
				$target     = isset( $args[2] ) ? $args[2] : 'plan';
				return class_exists( 'WC_Memberships_Admin_Membership_Plan_Rules' ) ? WC_Memberships_Admin_Membership_Plan_Rules::save_rules( $_POST, $post_id, $rule_types, $target ) : null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'load_meta_boxes' :
				_deprecated_function( $deprecated, '1.9.0' );
				$this->init_meta_boxes();
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'render_order_data' :
				_deprecated_function( $deprecated, '1.9.0', 'wc_memberships()->get_admin_instance()->get_orders_instance()->render_memberships_order_data()' );
				wc_memberships()->get_admin_instance()->get_orders_instance()->render_memberships_order_data( isset( $args[0] ) ? $args[0] : $args );
				return null;

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'set_current_tab' :
				_deprecated_function( $deprecated, '1.9.0', 'wc_memberships()->get_admin_instance()->get_current_tab()' );
				return $this->get_current_tab( '' );

			/** @deprecated since 1.9.0 - remove by 1.12.0 or higher */
			case 'update_custom_message' :
				_deprecated_function( $deprecated, '1.9.0', 'WC_Memberships_User_Messages::set_message()' );
				$post_id  = isset( $args[0] ) ? $args[0] : 0;
				$messages = isset( $args[1] ) ? $args[1] : array();
				foreach ( $messages as $message_type ) {

					$message      = '';
					$message_code = "{$message_type}_message";
					$use_custom   = 'no';

					if ( ! empty( $_POST["_wc_memberships_{$message_code}"] ) ) {
						$message    = wp_unslash( sanitize_post_field( 'post_content', $_POST["_wc_memberships_{$message_type}_message"], 0, 'db' ) );
					}
					if ( isset( $_POST["_wc_memberships_use_custom_{$message_code}"] ) && 'yes' === $_POST["_wc_memberships_use_custom_{$message_type}_message"] ) {
						$use_custom = 'yes';
					}

					// save the message
					\WC_Memberships_User_Messages::set_message( $message_code, $message );

					// set the flag to use a custom message (for admin UI)
					wc_memberships_set_content_meta( $post_id, "_wc_memberships_use_custom_{$message_code}", $use_custom );
				}
				return null;

			/** @deprecated since 1.10.0 - remove by 1.13.0 or higher */
			case 'process_import_export_form' :
				_deprecated_function( $deprecated, '1.10.0' );
				return null;

		}

		// you're probably doing it wrong
		trigger_error( "Call to undefined method {$deprecated}", E_USER_ERROR );
		return null;
	}


}
