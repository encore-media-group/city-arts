<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress class that handle comment updates and purge the cache.
 */
class Supercacher_Postmeta extends Supercacher_Posts {
	/**
	 * Array of all metas that should be ignored.
	 *
	 * @var array $ignored_metas Array of all metas that should be ignored.
	 */
	private $ignored_metas = array(
		'_edit_lock',
		'_edit_last',
		'_pingme',
		'_encloseme',
	);

	/**
	 * Add the hooks when the cache has to be purged.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		add_action( 'deleted_post_meta', array( $this, 'purge_postmeta' ), 10, 5 );
		add_action( 'added_post_meta', array( $this, 'purge_postmeta' ), 10, 5 );

		add_filter( 'update_post_metadata', array( $this, 'purge_update_postmeta' ), 10, 5 );
	}

	/**
	 * Purge comment post cache.
	 *
	 * @since  5.0.0
	 *
	 * @param int    $meta_ids    The meta ID after successful update.
	 * @param int    $object_id   Object ID.
	 * @param string $meta_key    Meta key.
	 * @param mixed  $_meta_value Meta value.
	 */
	public function purge_postmeta( $meta_ids, $object_id, $meta_key, $_meta_value ) {
		if ( ! in_array( $meta_key, $this->ignored_metas ) ) {
			// Purge the post cache.
			$this->purge_post_cache( $object_id );
		}
	}

	/**
	 * Purge comment post cache.
	 *
	 * @since  5.0.0
	 *
	 * @param null|bool $check      Whether to allow updating metadata for the given type.
	 * @param int       $object_id  Object ID.
	 * @param string    $meta_key   Meta key.
	 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
	 * @param mixed     $prev_value Optional. If specified, only update existing
	 *                              metadata entries with the specified value.
	 *                              Otherwise, update all entries.
	 */
	public function purge_update_postmeta( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if (
			! in_array( $meta_key, $this->ignored_metas ) &&
			$meta_value !== $prev_value
		) {
			// Purge the post cache.
			$this->purge_post_cache( $object_id );
		}

		return $check;
	}

}
