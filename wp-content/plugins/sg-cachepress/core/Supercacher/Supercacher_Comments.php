<?php
namespace SiteGround_Optimizer\Supercacher;

/**
 * SG CachePress class that handle comment updates and purge the cache.
 */
class Supercacher_Comments extends Supercacher_Posts {

	/**
	 * Add the hooks when the cache has to be purged.
	 *
	 * @since  5.0.0
	 */
	public function run() {
		add_action( 'comment_post', array( $this, 'purge_comment_post' ) );
		add_action( 'edit_comment', array( $this, 'purge_comment_post' ) );
		add_action( 'delete_comment', array( $this, 'purge_comment_post' ) );
		add_action( 'wp_set_comment_status', array( $this, 'purge_comment_post' ) );
		add_action( 'wp_insert_comment', array( $this, 'purge_comment_post' ) );
	}

	/**
	 * Purge comment post cache.
	 *
	 * @since  5.0.0
	 *
	 * @param  int $comment_id The comment ID.
	 */
	public function purge_comment_post( $comment_id ) {
		// Get the comment data.
		$commentdata = get_comment( $comment_id, OBJECT );

		// Get the post id from the comment.
		$comment_post_id = is_object( $commentdata ) ? $commentdata->comment_post_ID : $commentdata['comment_post_ID'];

		// Purge the post cache.
		$this->purge_post_cache( $comment_post_id );
	}

}
