<?php
/**
 * Plugin Name:       Jong LN Blocks
 * Description:       Site-specific block library for Jong Literair Nederland.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Hollands Spoor
 * Text Domain:       jong-literair-nederland-blocks
 *
 * @package JongLnBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jong_ln_blocks = array( 'blocks/jln-floating-text' );

foreach ( $jong_ln_blocks as $relative_dir ) {
	$block_runtime = __DIR__ . '/' . $relative_dir . '/' . basename( $relative_dir ) . '.php';

	if ( file_exists( $block_runtime ) ) {
		require_once $block_runtime;
	}
}

/**
 * Register every block directory listed in $jong_ln_blocks using the blocks manifest APIs.
 * Mirrors the approach used in x-literair-nederland-blocks so we can add new blocks later on.
 */
function jong_ln_blocks_init() {
	global $jong_ln_blocks;

	foreach ( $jong_ln_blocks as $relative_dir ) {
		$block_dir     = __DIR__ . '/' . $relative_dir;
		$manifest_path = $block_dir . '/build/blocks-manifest.php';

		if ( ! file_exists( $manifest_path ) ) {
			continue;
		}

		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
			wp_register_block_types_from_metadata_collection( $block_dir, $manifest_path );
			continue;
		}

		if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( $block_dir, $manifest_path );
		}

		$manifest_data = require $manifest_path;
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			register_block_type( "{$block_dir}/build/{$block_type}" );
		}
	}
}
add_action( 'init', 'jong_ln_blocks_init' );

add_filter(
	'block_categories_all',
	function( array $categories ) {
		$slug = 'literair-nederland';

		foreach ( $categories as $category ) {
			if ( $category['slug'] === $slug ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => $slug,
				'title' => __( 'Literair Nederland', 'jong-literair-nederland-blocks' ),
				'icon'  => 'book',
			)
		);

		return $categories;
	}
);

