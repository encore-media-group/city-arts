<?php
/**
 * Public function to purge cache.
 *
 * @since  5.0.0
 *
 * @param  string|bool $url The URL.
 *
 * @return bool True if the cache is deleted, false otherwise.
 */
function sg_cachepress_purge_cache( $url = false ) {
	global $siteground_optimizer_helper;

	$url = empty( $url ) ? get_home_url( '/' ) : $url;

	do_action( 'siteground_optimizer_flush_cache', $url );

	return $siteground_optimizer_helper->supercacher->purge_cache_request( $url );
}