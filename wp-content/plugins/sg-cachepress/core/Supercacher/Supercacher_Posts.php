<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress main plugin class
 */
class Supercacher_Posts extends Supercacher {

	/**
	 * Add the hooks when the cache has to be purged.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		add_action( 'save_post', array( $this, 'purge_all_post_cache' ) );
		add_action( 'wp_trash_post', array( $this, 'purge_all_post_cache' ) );
	}

	/**
	 * Purge the post cache and all child paths.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function purge_post_cache( $post_id ) {
		// Purge the post cache.
		return $this->purge_cache_request( get_permalink( $post_id ) );
	}

	/**
	 * Purge the parent pages cache of certain post.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 */
	public function purge_parents_cache( $post_id ) {
		// Get post parents.
		$parents = get_ancestors(
			$post_id,
			get_post_type( $post_id ),
			'post_type'
		);

		// Bail if the post top level post.
		if ( empty( $parents ) ) {
			return;
		}

		// Purge the cache of all parents.
		foreach ( $parents as $id ) {
			$this->purge_post_cache( $id );
		}
	}

	/**
	 * Purge all post terms cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 */
	public function purge_post_terms( $post_id ) {
		// Get all post taxonomies.
		$taxonomies = get_post_taxonomies( $post_id );

		// Get term ids.
		$term_ids = wp_get_object_terms(
			$post_id,
			$taxonomies,
			array(
				'fields' => 'ids',
			)
		);

		// Bail if there are no term_ids.
		if ( empty( $term_ids ) ) {
			return;
		}

		// Init the terms cacher.
		$supercacher_terms = new Supercacher_Terms();

		// Loop through all terms ids and purge the cache.
		foreach ( $term_ids as $id ) {
			$supercacher_terms->purge_term_cache( $id );
		}
	}

	/**
	 * Purge the cache of the post that has been changed and
	 * it's parents, the index cache, and the post categories.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $post_id The post id.
	 */
	public function purge_all_post_cache( $post_id ) {
		// Delete the index page only if this is the front page.
		if ( (int) get_option( 'page_on_front' ) === $post_id ) {
			// Purge the index cache.
			$this->purge_index_cache();
			return;
		}

		// Get the post.
		$post = get_post( $post_id );

		// Do not purge the cache for revisions and auto-drafts.
		if (
			'auto-draft' === $post->post_status ||
			'revision' === $post->post_type ||
			'trash' === $post->post_status
		) {
			return;
		}

		// Purge the post cache.
		$this->purge_post_cache( $post_id );
		// Purge post parents cache.
		$this->purge_parents_cache( $post_id );
		// Purge post terms cache.
		$this->purge_post_terms( $post_id );
		// Purge the index cache.
		$this->purge_index_cache();
	}

}


