<?php
/**
 * Plugin Name:       Jong LN Blocks
 * Description:       Site-specific block library for Jong Literair Nederland.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Hollands Spoor
 * Text Domain:       jln-blocks
 *
 * @package JongLnBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/jln-helpers.php';
require_once __DIR__ . '/includes/jln-options.php';


$jong_ln_blocks = array( 'blocks/jln-floating-text', 'blocks/jln-current-year', 'blocks/jln-logo', 'blocks/jln-titel' );

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

	if ( ! is_array( $jong_ln_blocks ) ) {
		$jong_ln_blocks = array( 'blocks/jln-floating-text', 'blocks/jln-current-year', 'blocks/jln-logo', 'blocks/jln-titel' );
	}

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
		if ( ! is_array( $manifest_data ) ) {
			continue;
		}

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
				'title' => __( 'Jong Literair Nederland', 'jln-blocks' ),
				'icon'  => 'book',
			)
		);

		return $categories;
	}
);

