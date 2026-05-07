<?php
/**
 * Plugin Name:       X LN Blocks
 * Description:       Portable collection of LN blocks (or variations). These are used in Literair Nederland and in Jong Literair Nederland.
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

/**
 * Load plugin translations.
 */
function xln_blocks_load_textdomain() {
    load_plugin_textdomain(
        'x-literair-nederland-blocks',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'init', 'xln_blocks_load_textdomain', 0 );


require_once __DIR__ . '/blocks/ln-bibliographics/ln-bibliographics.php';
require_once __DIR__ . '/blocks/ln-ad/ln-ad.php';
require_once __DIR__ . '/xln-medewerkers.php';
require_once __DIR__ . '/includes/xln-options.php';
require_once __DIR__ . '/includes/xln-quick-start-widget.php';
require_once __DIR__ . '/includes/ln-recensent.php';
require_once __DIR__ . '/includes/xln-tools.php';
require_once __DIR__ . '/includes/ln-cron.php';

if ( xln_is_tools_page_enabled() ) {
    new Xln_Tools();
}

function x_ln_get_default_block_dirs() {
    return array(
        'blocks/ln-donation',
        'blocks/ln-bibliographics',
        'blocks/ln-boek',
        'blocks/ln-oogst',
        'blocks/ln-ad',
        'blocks/ln-anniversary',
        'blocks/ln-year-archive',
    );
}

$x_ln_blocks = x_ln_get_default_block_dirs();



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

    $block_dirs = is_array( $x_ln_blocks ) ? $x_ln_blocks : x_ln_get_default_block_dirs();

    foreach ( $block_dirs as $dir ) {
        $manifest_path = __DIR__ . '/' . $dir . '/build/blocks-manifest.php';

        if ( ! file_exists( $manifest_path ) ) {
            continue;
        }

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
                $manifest_path );
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
                $manifest_path );
        }
        /**
         * Registers the block type(s) in the `blocks-manifest.php` file.
         *
         * @see https://developer.wordpress.org/reference/functions/register_block_type/
         */
        $manifest_data = require $manifest_path;
        foreach ( array_keys( $manifest_data ) as $block_type ) {
            register_block_type( __DIR__ . "/{$dir}/build/{$block_type}" );
        }

    }
}
add_action( 'init', 'x_ln_blocks_init' );

/**
 * Plugin activation tasks.
 *
 * @return void
 */
function xln_blocks_activation(): void {
    ln_ad_activation();

    if ( function_exists( 'xln_cron_schedule_nightly_event' ) ) {
        xln_cron_schedule_nightly_event();
    }
}

/**
 * Plugin deactivation tasks.
 *
 * @return void
 */
function xln_blocks_deactivation(): void {
    ln_ad_deactivation();

    if ( function_exists( 'xln_cron_unschedule_nightly_event' ) ) {
        xln_cron_unschedule_nightly_event();
    }
}

register_activation_hook( __FILE__, 'xln_blocks_activation' );
register_deactivation_hook( __FILE__, 'xln_blocks_deactivation' );

require_once __DIR__ . '/includes/ln-oogst.php';
require_once __DIR__ . '/includes/ln-query.php';

/**
 * Read and normalize the custom JLN icon SVG.
 *
 * @return string
 */
function x_ln_get_jong_social_icon_svg() {
    $icon_file = __DIR__ . '/assets/jln-splash.svg';

    if ( ! file_exists( $icon_file ) ) {
        return '';
    }

    $svg = file_get_contents( $icon_file );

    if ( false === $svg ) {
        return '';
    }

    $svg = preg_replace( '/^\s*<\?xml[^>]*>\s*/i', '', $svg );

    return trim( $svg );
}

/**
 * Register custom social-link service for frontend rendering.
 *
 * @param array $services_data Social services list.
 * @return array
 */
function x_ln_register_jong_social_service( $services_data ) {
    $icon_svg = x_ln_get_jong_social_icon_svg();

    if ( '' === $icon_svg ) {
        return $services_data;
    }

    $services_data['jong-literair-nederland'] = array(
        'name' => __( 'Jong Literair Nederland', 'x-literair-nederland-blocks' ),
        'icon' => $icon_svg,
    );

    return $services_data;
}
add_filter( 'block_core_social_link_get_services', 'x_ln_register_jong_social_service' );

/**
 * Enqueue editor script that adds custom Social Link variation.
 *
 * @return void
 */
function x_ln_enqueue_social_link_editor_assets() {
    wp_enqueue_script(
        'x-ln-jong-social-link-variation',
        plugin_dir_url( __FILE__ ) . 'includes/ln-jln-social-icon/jong-social-link-variation.js',
        array( 'wp-blocks', 'wp-element', 'wp-dom-ready', 'wp-i18n' ),
        '0.1.0',
        true
    );
}
add_action( 'enqueue_block_editor_assets', 'x_ln_enqueue_social_link_editor_assets' );

/**
 * Enqueue styles for the custom JLN social icon.
 *
 * @return void
 */
function x_ln_enqueue_jong_social_icon_styles() {
    wp_enqueue_style(
        'x-ln-jong-social-link-style',
        plugin_dir_url( __FILE__ ) . 'includes/ln-jln-social-icon/jong-social-link.css',
        array(),
        '0.1.0'
    );
}
add_action( 'enqueue_block_assets', 'x_ln_enqueue_jong_social_icon_styles' );


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


// Allow LN custom blocks to contribute to automatically generated excerpts.
add_filter('excerpt_allowed_blocks', function( $allowed_blocks ) {
    $allowed_blocks[] = 'ln/boek';
    $allowed_blocks[] = 'ln/oogst';

    return array_values( array_unique( $allowed_blocks ) );
});

add_filter('excerpt_allowed_wrapper_blocks', function( $allowed_wrapper_blocks ) {
    $allowed_wrapper_blocks[] = 'ln/boek';
    $allowed_wrapper_blocks[] = 'ln/oogst';

    return array_values( array_unique( $allowed_wrapper_blocks ) );
});

