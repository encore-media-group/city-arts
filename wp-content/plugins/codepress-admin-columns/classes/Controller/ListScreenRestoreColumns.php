<?php

namespace AC\Controller;

use AC\Capabilities;
use AC\ListScreenRepository\Aggregate;
use AC\Message\Notice;
use AC\Registrable;

class ListScreenRestoreColumns implements Registrable {

	/** @var Aggregate */
	private $repository;

	public function __construct( Aggregate $repository ) {
		$this->repository = $repository;
	}

	public function register() {
		add_action( 'admin_init', [ $this, 'handle_request' ] );
	}

	public function handle_request() {
		if ( ! current_user_can( Capabilities::MANAGE ) ) {
			return;
		}

		switch ( filter_input( INPUT_POST, 'action' ) ) {

			case 'restore_by_type' :
				if ( $this->verify_nonce( 'restore-type' ) ) {

					$list_screen = $this->repository->find( filter_input( INPUT_POST, 'layout' ) );

					if ( ! $list_screen ) {
						return;
					}

					$list_screen->set_settings( [] );
					$this->repository->save( $list_screen );

					$notice = new Notice( sprintf( __( 'Settings for %s restored successfully.', 'codepress-admin-columns' ), "<strong>" . esc_html( $list_screen->get_title() ) . "</strong>" ) );
					$notice->register();
				}
				break;
		}
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	private function verify_nonce( $action ) {
		return wp_verify_nonce( filter_input( INPUT_POST, '_ac_nonce' ), $action );
	}

}