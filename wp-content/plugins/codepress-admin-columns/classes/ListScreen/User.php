<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AC_ListScreen_User extends AC_ListScreenWP {

	public function __construct() {

		$this->set_label( __( 'Users' ) );
		$this->set_singular_label( __( 'User' ) );
		$this->set_meta_type( 'user' );
		$this->set_screen_base( 'users' );
		$this->set_screen_id( 'users' );
		$this->set_key( 'wp-users' );
		$this->set_group( 'user' );
	}

	/**
	 * @see set_manage_value_callback()
	 */
	public function set_manage_value_callback() {
		add_filter( 'manage_users_custom_column', array( $this, 'manage_value' ), 100, 3 );
	}

	/**
	 * @return WP_Users_List_Table
	 */
	public function get_list_table() {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php' );

		return new WP_Users_List_Table( array( 'screen' => $this->get_screen_id() ) );
	}

	/**
	 * @since 2.4.10
	 */
	public function is_current_screen( $wp_screen ) {
		return parent::is_current_screen( $wp_screen ) && 'delete' !== filter_input( INPUT_GET, 'action' );
	}

	/**
	 * @since 2.0.2
	 *
	 * @param string $value
	 * @param string $column_name
	 * @param int    $user_id
	 */
	public function manage_value( $value, $column_name, $user_id ) {
		return $this->get_display_value_by_column_name( $column_name, $user_id, $value );
	}

	/**
	 * @param int $id
	 *
	 * @return WP_User
	 */
	protected function get_object( $id ) {
		return get_userdata( $id );
	}

	/**
	 * @since 3.0
	 *
	 * @param int $id
	 *
	 * @return string HTML
	 */
	public function get_single_row( $id ) {
		return $this->get_list_table()->single_row( $this->get_object( $id ) );
	}

	protected function register_column_types() {
		$this->register_column_type( new AC_Column_CustomField );
		$this->register_column_type( new AC_Column_Actions );

		$this->register_column_types_from_dir( AC()->get_plugin_dir() . 'classes/Column/User', AC()->get_prefix() );
	}

}
