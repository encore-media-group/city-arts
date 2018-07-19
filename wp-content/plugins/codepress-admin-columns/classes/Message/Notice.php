<?php

namespace AC\Message;

use AC\Message;
use AC\View;

class Notice extends Message {

	public static function with_register() {
		$notice = new self();
		$notice->register();

		return $notice;
	}

	public function create_view() {
		$data = array(
			'message' => $this->message,
			'type'    => $this->type,
			'id'      => $this->id,
		);

		$view = new View( $data );
		$view->set_template( 'message/notice' );

		return $view;
	}

	public function register() {
		if ( apply_filters( 'ac/suppress_site_wide_notices', false ) ) {
			return;
		}

		add_action( 'admin_notices', array( $this, 'display' ) );
		add_action( 'network_admin_notices', array( $this, 'display' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts & styles
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'ac-message', AC()->get_url() . 'assets/css/notice.css', array(), AC()->get_version() );
	}

}