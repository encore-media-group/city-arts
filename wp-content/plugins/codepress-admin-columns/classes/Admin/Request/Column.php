<?php

namespace AC\Admin\Request;

use AC;
use AC\Column\Placeholder;
use AC\View;

abstract class Column extends AC\Admin\Request\Handler {

	/**
	 * @return AC\Column
	 */
	abstract public function get_column( AC\Request $request, AC\ListScreen $list_screen );

	public function request( AC\Request $request ) {
		parse_str( $request->get( 'data' ), $formdata );

		$list_screen = AC\ListScreenTypes::instance()->get_list_screen_by_key( $formdata['list_screen'] );

		if ( ! $list_screen ) {
			wp_die();
		}

		$column = $this->get_column( $request, $list_screen );

		if ( ! $column ) {
			wp_send_json_error( array(
				'type'  => 'message',
				'error' => sprintf( __( 'Please visit the %s screen once to load all available columns', 'codepress-admin-columns' ), ac_helper()->html->link( $list_screen->get_screen_link(), $list_screen->get_label() ) ),
			) );
		}

		$current_original_columns = (array) $request->get( 'current_original_columns', array() );

		// Not cloneable message
		if ( in_array( $column->get_type(), $current_original_columns ) ) {
			wp_send_json_error( array(
				'type'  => 'message',
				'error' => sprintf(
					__( '%s column is already present and can not be duplicated.', 'codepress-admin-columns' ),
					'<strong>' . $column->get_label() . '</strong>' ),
			) );
		}

		// Placeholder message
		if ( $column instanceof Placeholder ) {
			wp_send_json_error( array(
				'type'  => 'message',
				'error' => $column->get_message(),
			) );
		}

		wp_send_json_success( $this->render_column( $column ) );
	}

	/**
	 * @param AC\Column $column
	 *
	 * @return string
	 */
	private function render_column( AC\Column $column ) {
		$view = new View( array(
			'column' => $column,
		) );

		$view->set_template( 'admin/edit-column' );

		return $view->render();
	}

}