<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AC_Preferences_User extends AC_Preferences {

	/**
	 * @return bool
	 */
	public function save() {
		return (bool) update_user_meta( $this->get_user_id(), $this->get_key(), $this->data );
	}

	/**
	 * @return false|array
	 */
	protected function load() {
		return get_user_meta( $this->get_user_id(), $this->get_key(), true );
	}

}
