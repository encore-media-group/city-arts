<?php
namespace SiteGround_Optimizer\Rest;

/**
 * Handle PHP compatibility checks.
 */
class Rest {

	const REST_NAMESPACE = 'siteground-optimizer/v1';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->helper      = new Rest_Helper();
		$this->webp_helper = new Rest_Helper_Webp();

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Check if a given request has admin access
	 *
	 * @since  5.0.13
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function check_permissions( $request ) {
		return current_user_can( 'activate_plugins' );
	}

	/**
	 * Register rest routes.
	 *
	 * @since  5.0.0
	 */
	public function register_rest_routes() {
		$this->register_webp_routes();
		$this->register_images_routes();
		$this->register_cache_routes();
		$this->register_options_routes();
		$this->register_compatibility_routes();
		$this->register_php_and_ssl_routes();
		$this->register_multisite_rest_routes();
		$this->register_misc_rest_routes();
	}

	/**
	 * Register php and ssl rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_php_and_ssl_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/switch-php/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'switch_php' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/enable-ssl/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'enable_ssl' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-ssl/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'disable_ssl' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register compatibility checks rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_compatibility_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/check-compatibility/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'handle_compatibility_check' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/check-compatibility-status/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'handle_compatibility_status_check' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register options rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_options_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/enable-option/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'enable_option_from_rest' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-option/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'disable_option_from_rest' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/fetch-options/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'fetch_options' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register cache rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_cache_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/update-excluded-urls/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'update_excluded_urls' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/test-url-cache/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'test_cache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/purge-cache/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'purge_cache_from_rest' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/enable-memcache/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'enable_memcache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-memcache/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'disable_memcache' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register the rest routes for images optimization.
	 *
	 * @since  5.4.0
	 */
	public function register_images_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/optimize-images/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'optimize_images' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/stop-images-optimization/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'stop_images_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/check-image-optimizing-status/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'check_image_optimizing_status' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/reset-images-optimization/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'reset_images_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register the rest routes for webp conversion.
	 *
	 * @since  5.4.0
	 */
	public function register_webp_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/delete-webp-files/', array(
				'methods'  => 'GET',
				'callback' => array( $this->webp_helper, 'delete_webp_files' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/generate-webp-files/', array(
				'methods'  => 'GET',
				'callback' => array( $this->webp_helper, 'generate_webp_files' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/stop-webp-conversion/', array(
				'methods'  => 'GET',
				'callback' => array( $this->webp_helper, 'stop_webp_conversion' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/check-webp-conversion-status/', array(
				'methods'  => 'GET',
				'callback' => array( $this->webp_helper, 'check_webp_conversion_status' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register multisite rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_multisite_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/enable-multisite-optimization/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'enable_multisite_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/disable-multisite-optimization/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'disable_multisite_optimization' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Register misc rest routes.
	 *
	 * @since  5.4.0
	 */
	public function register_misc_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE, '/hide-rating/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'handle_hide_rating' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/get-assets/', array(
				'methods'  => 'GET',
				'callback' => array( $this->helper, 'get_assets' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/update-exclude-list/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'update_exclude_list' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE, '/run-analysis/', array(
				'methods'  => 'POST',
				'callback' => array( $this->helper, 'run_analysis' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}
}
