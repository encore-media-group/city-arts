<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class AC_Column_Media_AlternateText extends AC_Column_Meta {

	public function __construct() {
		$this->set_type( 'column-alternate_text' );
		$this->set_label( __( 'Alternative Text', 'codepress-admin-columns' ) );
	}

	public function get_meta_key() {
		return '_wp_attachment_image_alt';
	}

	public function get_value( $id ) {
		$value = ac_helper()->string->strip_trim( $this->get_raw_value( $id ) );

		if ( ! $value ) {
			return $this->get_empty_char();
		}

		return $value;
	}

	public function get_raw_value( $id ) {
		return $this->get_meta_value( $id, $this->get_meta_key() );
	}

}
