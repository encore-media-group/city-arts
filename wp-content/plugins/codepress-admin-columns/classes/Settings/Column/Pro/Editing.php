<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AC_Settings_Column_Pro_Editing extends AC_Settings_Column_Pro {

	protected function get_label() {
		return __( 'Editing', 'codepress-admin-columns' );
	}

	protected function get_tooltip() {
		return __( 'Edit your content directly from the overview.', 'codepress-admin-columns' );
	}

	protected function define_options() {
		return array( 'edit' );
	}

}
