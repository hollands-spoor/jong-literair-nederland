<?php
/**
 * Runtime helpers for the Floating Text block.
 *
 * @package JongLnBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve the available font options exposed by the current theme.
 *
 * @return array[]
 */
function jln_floating_text_get_font_options() {
	$fonts            = array();
	$font_collections = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );

	if ( is_array( $font_collections ) ) {
		foreach ( $font_collections as $collection ) {
			if ( ! is_array( $collection ) ) {
				continue;
			}

			foreach ( $collection as $font ) {
				if ( empty( $font['fontFamily'] ) ) {
					continue;
				}

				$label                     = $font['name'] ?? ( $font['slug'] ?? $font['fontFamily'] );
				$fonts[ $font['fontFamily'] ] = array(
					'label' => $label,
					'value' => $font['fontFamily'],
				);
			}
		}
	}

	/**
	 * Filter the font options exposed to the Floating Text block.
	 *
	 * @param array[] $fonts Font option arrays with label + value keys.
	 */
	return apply_filters( 'jln_floating_text_font_options', array_values( $fonts ) );
}

/**
 * Localize the Floating Text block data for the editor context.
 */
function jln_floating_text_localize_editor_assets() {
	$font_options = jln_floating_text_get_font_options();

	wp_add_inline_script(
		'wp-blocks',
		sprintf(
			'window.JLNFloatingText = window.JLNFloatingText || {}; window.JLNFloatingText.fontOptions = %s;',
			wp_json_encode( $font_options )
		),
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'jln_floating_text_localize_editor_assets', 5 );
