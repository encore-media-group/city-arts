<?php

namespace AC\Helper\Select;

use AC\ArrayIterator;

class Entities extends ArrayIterator {

	/**
	 * @param array $entities
	 * @param Value $value
	 */
	public function __construct( array $entities, Value $value ) {
		$value_entity_map = array();

		foreach ( $entities as $entity ) {
			$value_entity_map[ $value->get_value( $entity ) ] = $entity;
		}

		parent::__construct( $value_entity_map );
	}

}