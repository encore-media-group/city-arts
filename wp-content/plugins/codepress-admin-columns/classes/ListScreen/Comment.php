<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class AC_ListScreen_Comment extends AC_ListScreenWP {

	public function __construct() {

		$this->set_label( __( 'Comments' ) );
		$this->set_singular_label( __( 'Comment' ) );
		$this->set_meta_type( 'comment' );
		$this->set_screen_base( 'edit-comments' );
		$this->set_key( 'wp-comments' );
		$this->set_screen_id( 'edit-comments' );
		$this->set_group( 'comment' );
	}

	/**
	 * @param int $id
	 *
	 * @return WP_Comment
	 */
	protected function get_object( $id ) {
		return get_comment( $id );
	}

	/**
	 * @return WP_Comments_List_Table
	 */
	public function get_list_table() {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-comments-list-table.php' );

		$table = new WP_Comments_List_Table( array( 'screen' => $this->get_screen_id() ) );

		// Since 4.4 the `floated_admin_avatar` filter is added in the constructor of the `WP_Comments_List_Table` class.
		// Here we remove the filter from the constructor.
		remove_filter( 'comment_author', array( $table, 'floated_admin_avatar' ), 10 );

		return $table;
	}

	public function set_manage_value_callback() {
		add_action( 'manage_comments_custom_column', array( $this, 'manage_value' ), 100, 2 );
	}

	/**
	 * @since 3.5
	 */
	public function get_table_attr_id() {
		return '#the-comment-list';
	}

	/**
	 * @param string $column_name
	 * @param int    $id
	 */
	public function manage_value( $column_name, $id ) {
		echo $this->get_display_value_by_column_name( $column_name, $id );
	}

	/**
	 * Register column types
	 */
	protected function register_column_types() {
		$this->register_column_type( new AC_Column_CustomField );
		$this->register_column_type( new AC_Column_Actions );

		$this->register_column_types_from_dir( AC()->get_plugin_dir() . 'classes/Column/Comment', AC()->get_prefix() );
	}

}
