<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress class that handle term actions and purge the cache.
 */
class Supercacher_Terms extends Supercacher {

	/**
	 * Array of all taxonomies that should be ignored.
	 *
	 * @var array $ignored_taxonomies Array of all taxonomies that should be ignored.
	 */
	private $ignored_taxonomies = array(
		'product_type',
		'product_visibility',
	);

	/**
	 * Add the hooks when the cache has to be purged.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		// Purge everything when a term is added/deleted.
		add_action( 'create_term', array( $this, 'purge_everything' ) );
		add_action( 'delete_term', array( $this, 'purge_everything' ) );

		// Purge only single term link when it has been edited.
		add_action( 'edit_term', array( $this, 'purge_term_and_index_cache' ) );
	}

	/**
	 * Purge single term cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $term_id The term id.
	 *
	 * @return bool         True on success, false on failure.
	 */
	public function purge_term_cache( $term_id ) {
		// Get the term.
		$term = \get_term( $term_id );

		// Bail if we shounl ignore the taxonomy.
		if ( in_array( $term->taxonomy, $this->ignored_taxonomies ) ) {
			return;
		}

		// Get term link.
		$term_url = \get_term_link( $term_id );

		if ( empty( $term_url ) ) {
			return;
		}

		// Purge the term cache.
		$this->purge_cache_request( $term_url );
	}

	/**
	 * Purge the term and index.php cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $term_id The term id.
	 */
	public function purge_term_and_index_cache( $term_id ) {
		// Purge the term cache.
		$this->purge_term_cache( $term_id );

		// Purge the index.php cache.
		$this->purge_index_cache();
	}

}
