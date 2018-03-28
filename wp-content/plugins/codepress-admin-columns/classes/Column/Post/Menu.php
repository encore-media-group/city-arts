<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AC_Column_Post_Menu extends AC_Column_Menu {

	/**
	 * @return string
	 */
	public function get_object_type() {
		return $this->get_post_type();
	}

	/**
	 * @return string
	 */
	public function get_item_type() {
		return 'post_type';
	}

}
