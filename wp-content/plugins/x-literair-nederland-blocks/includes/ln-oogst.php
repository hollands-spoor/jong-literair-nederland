<?php
/**
 * Oogst featured image fallback support.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether the given post is an oogst post.
 *
 * @param int|WP_Post|null $post Optional post reference.
 * @return bool
 */
function x_ln_oogst_is_post( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return false;
	}

	return has_category( 'oogst', $post );
}

/**
 * Resolve the first legacy oogst cover attachment ID.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function x_ln_oogst_get_fallback_thumbnail_id( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return 0;
	}

	$all_meta = get_post_meta( $post_id );

	if ( empty( $all_meta ) || ! is_array( $all_meta ) ) {
		return 0;
	}

	$candidate_ids = array();

	foreach ( $all_meta as $meta_key => $meta_values ) {
		if ( ! preg_match( '/^besproken_boeken_(\d+)_(afbeelding|omslag_id)$/', (string) $meta_key, $matches ) ) {
			continue;
		}

		if ( ! is_array( $meta_values ) || empty( $meta_values ) ) {
			continue;
		}

		$attachment_id = absint( $meta_values[0] );
		if ( $attachment_id <= 0 ) {
			continue;
		}

		$index = (int) $matches[1];
		if ( isset( $candidate_ids[ $index ] ) ) {
			continue;
		}

		$candidate_ids[ $index ] = $attachment_id;
	}

	if ( empty( $candidate_ids ) ) {
		return 0;
	}

	ksort( $candidate_ids, SORT_NUMERIC );

	foreach ( $candidate_ids as $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( $attachment instanceof WP_Post && 'attachment' === $attachment->post_type ) {
			return $attachment_id;
		}
	}

	return 0;
}

/**
 * Provide a legacy oogst image when no featured image is set.
 *
 * @param int|false        $thumbnail_id Post thumbnail ID or false if the post does not exist.
 * @param int|WP_Post|null $post         Post ID or WP_Post object.
 * @return int
 */
function x_ln_oogst_filter_post_thumbnail_id( $thumbnail_id, $post ) {
	if ( ! empty( $thumbnail_id ) ) {
		return (int) $thumbnail_id;
	}

	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || ! x_ln_oogst_is_post( $post ) ) {
		return (int) $thumbnail_id;
	}

	$stored_thumbnail_id = absint( get_post_meta( $post->ID, '_thumbnail_id', true ) );
	if ( $stored_thumbnail_id > 0 ) {
		return $stored_thumbnail_id;
	}

	return x_ln_oogst_get_fallback_thumbnail_id( $post->ID );
}
add_filter( 'post_thumbnail_id', 'x_ln_oogst_filter_post_thumbnail_id', 10, 2 );

/**
 * Enqueue Oogst editor restrictions independent of quick-start widget state.
 *
 * @return void
 */
function x_ln_oogst_enqueue_editor_assets() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$asset_rel_path = 'assets/xln-oogst-inserter-guard.js';
	$asset_path     = dirname( __DIR__ ) . '/' . $asset_rel_path;

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	wp_enqueue_script(
		'xln-oogst-inserter-guard',
		plugins_url( $asset_rel_path, dirname( __DIR__ ) . '/x-literair-nederland-blocks.php' ),
		array( 'wp-data', 'wp-hooks', 'wp-block-editor', 'wp-edit-post', 'wp-notices' ),
		(string) filemtime( $asset_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'x_ln_oogst_enqueue_editor_assets' );