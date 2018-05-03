<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AC_Settings_Column_Pro extends AC_Settings_Column {

	abstract protected function get_label();

	abstract protected function get_tooltip();

	public function create_view() {
		$setting = $this->create_element( 'radio' )
		                ->set_options( array(
			                'on'  => __( 'Yes' ),
			                'off' => __( 'No' ),
		                ) )
		                ->set_value( 'off' );

		$view = new AC_View();
		$view->set( 'label', $this->get_label() )
		     ->set( 'tooltip', $this->get_tooltip() )
		     ->set( 'setting', $setting )
		     ->set_template( 'settings/setting-pro' );

		return $view;
	}

}
