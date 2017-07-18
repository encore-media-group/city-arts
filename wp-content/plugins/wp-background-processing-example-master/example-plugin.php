<?php
/*
Plugin Name: Example Background Processing
Plugin URI: https://github.com/A5hleyRich/wp-background-processing
Description: Background processing in WordPress.
Author: Ashley Rich
Version: 0.1
Author URI: https://deliciousbrains.com/
Text Domain: example-plugin
Domain Path: /languages/
*/

class Example_Background_Processing {

	/**
	 * @var WP_Example_Request
	 */
	protected $process_single;

	/**
	 * @var WP_Example_Process
	 */
	protected $process_all;

	/**
	 * Example_Background_Processing constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 100 );
		add_action( 'init', array( $this, 'process_handler' ) );
	}

	/**
	 * Init
	 */
	public function init() {
		require_once plugin_dir_path( __FILE__ ) . 'class-logger.php';
		require_once plugin_dir_path( __FILE__ ) . 'async-requests/class-example-request.php';
		require_once plugin_dir_path( __FILE__ ) . 'background-processes/class-example-process.php';

		$this->process_single = new WP_Example_Request();
		$this->process_all    = new WP_Example_Process();
	}

	/**
	 * Admin bar
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_menu( array(
			'id'    => 'example-plugin',
			'title' => __( 'Process', 'example-plugin' ),
			'href'  => '#',
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'example-plugin',
			'id'     => 'example-plugin-single',
			'title'  => __( 'Single User', 'example-plugin' ),
			'href'   => wp_nonce_url( admin_url( '?process=single'), 'process' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'example-plugin',
			'id'     => 'example-plugin-all',
			'title'  => __( 'All Users', 'example-plugin' ),
			'href'   => wp_nonce_url( admin_url( '?process=all'), 'process' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'example-plugin',
			'id'     => 'example-plugin-all-images',
			'title'  => __( 'All Images', 'example-plugin' ),
			'href'   => wp_nonce_url( admin_url( '?process=allimages'), 'process' ),
		) );

	}

	/**
	 * Process handler
	 */
	public function process_handler() {
		if ( ! isset( $_GET['process'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'process') ) {
			return;
		}

		if ( 'single' === $_GET['process'] ) {
			$this->handle_single();
		}

		if ( 'all' === $_GET['process'] ) {
			$this->handle_all();
		}

		if ( 'allimages' === $_GET['process'] ) {
			$this->images_all();
		}
	}

	/**
	 * Handle single
	 */
	protected function handle_single() {
		$names = $this->get_names();
		$rand  = array_rand( $names, 1 );
		$name  = $names[ $rand ];

		$this->process_single->data( array( 'name' => $name ) )->dispatch();
	}

	/**
	 * Handle all
	 */
	protected function handle_all() {
		$names = $this->get_names();

		foreach ( $names as $name ) {
			$this->process_all->push_to_queue( $name );
		}

		$this->process_all->save()->dispatch();
	}

	protected function images_all() {
		//get list of posts with iamges

		$myrows = get_all_images();
		if ($myrows) {
			foreach ( $myrows as $myrow ) {
				$this->process_all->push_to_queue( $myrow );
			}
		}

		//get images list
		//for each image call run the process...

		$this->process_all->save()->dispatch();
	}


	/**
	 * Get names
	 *
	 * @return array
	 */
	protected function get_names() {
		return array(
			'Grant Buel',
			'Bryon Pennywell',
			'Jarred Mccuiston',
			'Reynaldo Azcona',
			'Jarrett Pelc',
			'Blake Terrill',
			'Romeo Tiernan',
			'Marion Buckle',
			'Theodore Barley',
			'Carmine Hopple',
			'Suzi Rodrique',
			'Fran Velez',
			'Sherly Bolten',
			'Rossana Ohalloran',
			'Sonya Water',
			'Marget Bejarano',
			'Leslee Mans',
			'Fernanda Eldred',
			'Terina Calvo',
			'Dawn Partridge',
		);
	}

}

new Example_Background_Processing();
