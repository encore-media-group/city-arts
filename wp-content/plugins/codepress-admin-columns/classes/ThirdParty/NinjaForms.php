<?php

namespace AC\ThirdParty;

use AC\Registrable;

class NinjaForms implements Registrable {

	public function register() {
		add_filter( 'ac/post_types', array( $this, 'remove_nf_sub' ) );
	}

	public function remove_nf_sub( $post_types ) {
		if ( class_exists( 'Ninja_Forms', false ) ) {
			if ( isset( $post_types['nf_sub'] ) ) {
				unset( $post_types['nf_sub'] );
			}
		}

		return $post_types;
	}

}