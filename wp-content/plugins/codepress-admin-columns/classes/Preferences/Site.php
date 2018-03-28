<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AC_Preferences_Site extends AC_Preferences {

	/**
	 * return array|false
	 */
	protected function load() {
		return get_user_option( $this->get_key(), $this->get_user_id() );
	}

	/**
	 * @return bool
	 */
	public function save() {
		return (bool) update_user_option( $this->get_user_id(), $this->get_key(), $this->data );
	}

}
