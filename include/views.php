<?php
/**
 * Post view counter helpers. Owner: caching agent.
 *
 * Counting happens only through the REST route POST sokal/v1/view/{id}
 * (include/performance.php). This file is read-only.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a post's view count. Never writes.
 *
 * @param int $postID Post ID.
 * @return string The count as a numeric string, '0' when there is none.
 */
function getPostViews( $postID ) {
	$count = get_post_meta( (int) $postID, 'post_views_count', true );
	if ( '' === $count || false === $count || null === $count ) {
		return '0';
	}
	return (string) absint( $count );
}
