<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AC_ListScreen_Media extends AC_ListScreenPost {

	public function __construct() {
		parent::__construct( 'attachment' );

		$this->set_screen_id( 'upload' );
		$this->set_screen_base( 'upload' );
		$this->set_key( 'wp-media' );
		$this->set_group( 'media' );
		$this->set_label( __( 'Media' ) );
	}

	public function set_manage_value_callback() {
		add_action( 'manage_media_custom_column', array( $this, 'manage_value' ), 100, 2 );
	}

	/**
	 * @return WP_Media_List_Table
	 */
	public function get_list_table() {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );

		return new WP_Media_List_Table( array( 'screen' => $this->get_screen_id() ) );
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public function get_single_row( $id ) {
		// Author column depends on this global to be set.
		global $authordata;

		$authordata = get_userdata( get_post_field( 'post_author', $id ) );

		return parent::get_single_row( $id );
	}

	/**
	 * @since 2.4.7
	 */
	public function manage_value( $column_name, $id ) {
		echo $this->get_display_value_by_column_name( $column_name, $id );
	}

	protected function register_column_types() {
		parent::register_column_types();

		$this->register_column_types_from_dir( AC()->get_plugin_dir() . 'classes/Column/Media', AC()->get_prefix() );
	}

}
