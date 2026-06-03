<?php

add_action( 'wp_enqueue_scripts', 'jln_child_theme_enqueue_assets' );
add_action( 'admin_enqueue_scripts', 'jln_child_theme_enqueue_admin_assets' );
add_action( 'after_setup_theme', 'jln_theme_setup_editor_styles' );
/**
 * Load theme assets compiled via Webpack.
 */
function jln_child_theme_enqueue_assets() {
	$child_css_path = get_stylesheet_directory() . '/css/style.css';
	$child_css_uri  = get_stylesheet_directory_uri() . '/css/style.css';
	$child_version  = file_exists( $child_css_path ) ? filemtime( $child_css_path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'jong-literair-nederland-style', $child_css_uri, array(), $child_version );

	$child_js_path = get_stylesheet_directory() . '/js/main.js';
	if ( file_exists( $child_js_path ) ) {
		wp_enqueue_script(
			'jong-literair-nederland-frontend',
			get_stylesheet_directory_uri() . '/js/main.js',
			array(),
			filemtime( $child_js_path ),
			true
		);
	}
}

/**
 * Load admin-only assets compiled via Webpack.
 */
function jln_child_theme_enqueue_admin_assets() {
	$admin_js_path = get_stylesheet_directory() . '/js/admin.js';

	if ( file_exists( $admin_js_path ) ) {
		wp_enqueue_script(
			'jong-literair-nederland-admin',
			get_stylesheet_directory_uri() . '/js/admin.js',
			array( 'wp-element' ),
			filemtime( $admin_js_path ),
			true
		);
	}
}

/**
 * Load theme styles inside the block editor.
 */
function jln_theme_setup_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'css/editor-style.css' );
}

/**
 * Override LN quick-start presets for JLN context.
 *
 * Keep LN defaults for template and block behavior and only change
 * category slugs for JLN taxonomy naming.
 * 
 * TODO: this is not a theme thing, move it to a plugin, jong-literair-nederland-overwrites.php o.i.d.
 *
 * @param array<string, array<string, string>> $presets Quick-start presets.
 *
 * @return array<string, array<string, string>>
 */
function jln_override_new_post_presets( array $presets ): array {
	if ( empty( $presets['review'] ) || ! is_array( $presets['review'] ) ) {
		$presets['review'] = [];
	}

	$presets['review']['category_slug'] = 'recensie';

	if ( empty( $presets['oogst'] ) || ! is_array( $presets['oogst'] ) ) {
		$presets['oogst'] = [];
	}

	$presets['oogst']['category_slug'] = 'jonge-oogst';

	return $presets;
}
add_filter( 'xln_new_post_presets', 'jln_override_new_post_presets' );


add_filter('wpfts_widget_html', function( $out, $preset, $preset_id ) {

    // Dirty filtering of html. WPFTS (plugin slug="fulltext-search" ) doesn't provide any hooks to change the markup of the search widget, so we have to do it ourselves.
    // if html in $out contains class "main-search" then replace <input type="submit" class="search-submit" value=""> with
    // an input type="image" with src in stylesheetdir/assets/icons/search.svg

    if ( false === strpos( $out, 'main-search' ) ) {
        return $out;
    }

    $search_icon_url = get_stylesheet_directory_uri() . '/assets/icons/search.svg';
    $replacement     = '<input type="image" class="search-submit" src="' . esc_url( $search_icon_url ) . '" alt="Zoeken">';

    $out = preg_replace(
        '/<input\b(?=[^>]*\btype=(["\'])submit\1)(?=[^>]*\bclass=(["\'])[^"\']*\bsearch-submit\b[^"\']*\2)[^>]*>/i',
        $replacement,
        $out,
        1
    );

    return $out;
}, 10, 3);


/** For pagination in archives previous / next buttons justified with space between, see
 * https://hollands-spoor.com/fixing-pagination/
 */

add_filter('render_block_core/query-pagination-previous', function(  $block_content, $parsed_block, $block_object ) {
    if( '' === $block_content) {
        $block_content = '<span class="wp-block-query-pagination-previous" style="margin-inline-end: auto;"></span>';
    }
    return $block_content;
}, 10, 3 );

add_filter('render_block_core/query-pagination-next', function(  $block_content, $parsed_block, $block_object ) {
    if( '' === $block_content) {
        $block_content = '<span class="wp-block-query-pagination-next" style="margin-inline-start: auto;"></span>';
    }
    return $block_content;
}, 10, 3 );
