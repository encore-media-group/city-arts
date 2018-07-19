<?php

namespace AC;

class ListScreenFactory {

	/**
	 * @param string $type
	 * @param int    $id Optional (layout) ID
	 *
	 * @return ListScreen|false
	 */
	public static function create( $type, $id = null ) {
		$list_screens = AC()->get_list_screens();

		if ( ! isset( $list_screens[ $type ] ) ) {
			return false;
		}

		$list_screen = clone $list_screens[ $type ];

		$list_screen->set_layout_id( $id );

		return $list_screen;
	}

}