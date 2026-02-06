<?php
/**
 * Plugin Name:       X LN Blocks
 * Description:       Portable collection of LN blocks (or variations). These are used in Literair Nederland ánd in Jong Literair Nederland.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Hollands Spoor
 * Text Domain:       x-literair-nederland-blocks
 *
 * @package XLn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/blocks/ln-bibliographics/ln-bibliographics.php';

$x_ln_blocks = array( 'blocks/ln-donation', 'blocks/ln-bibliographics', 'blocks/ln-query' );



/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function x_ln_blocks_init() {

    global $x_ln_blocks;


    foreach ( $x_ln_blocks as $dir ) {
        /**
         * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
         * based on the registered block metadata.
         * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
         *
         * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
         */
        if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
            wp_register_block_types_from_metadata_collection( 
                __DIR__ . '/' . $dir , 
                __DIR__ . '/' . $dir . '/build/blocks-manifest.php' );
            continue;
        }

        /**
         * Registers the block(s) metadata from the `blocks-manifest.php` file.
         * Added to WordPress 6.7 to improve the performance of block type registration.
         *
         * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
         */
        if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
            wp_register_block_metadata_collection( 
                __DIR__ . '/' . $dir , 
                __DIR__ . '/' . $dir . '/build/blocks-manifest.php' );
        }
        /**
         * Registers the block type(s) in the `blocks-manifest.php` file.
         *
         * @see https://developer.wordpress.org/reference/functions/register_block_type/
         */
        $manifest_data = require __DIR__ . '/' . $dir . '/build/blocks-manifest.php';
        foreach ( array_keys( $manifest_data ) as $block_type ) {
            register_block_type( __DIR__ . "/{$dir}/build/{$block_type}" );
        }

    }
}
add_action( 'init', 'x_ln_blocks_init' );

require_once __DIR__ . '/includes/ln-query.php';


add_filter(
    'block_categories_all',
    function( array $categories ) {
        $slug = 'literair-nederland';

        foreach ( $categories as $category ) {
            if ( $category['slug'] === $slug ) {
                return $categories;
            }
        }
        
        $new_entry = array(
            'slug'  => $slug,
            'title' => __( 'Literair Nederland', 'x-literair-nederland-blocks' ),
            'icon'  => 'book',
        );
        
        array_unshift( $categories, $new_entry );
        return $categories;
    }
);