<?php

add_action( 'wp_enqueue_scripts', 'jln_child_theme_enqueue_assets' );
add_action( 'admin_enqueue_scripts', 'jln_child_theme_enqueue_admin_assets' );
/**
 * Load parent + child theme assets compiled via Webpack.
 */
function jln_child_theme_enqueue_assets() {
	$parent_style_handle = 'twentytwentyfive-style';
	$parent_stylesheet   = get_template_directory_uri() . '/style.css';
	$parent_version      = wp_get_theme( 'twentytwentyfive' )->get( 'Version' );

	wp_enqueue_style( $parent_style_handle, $parent_stylesheet, array(), $parent_version );

	$child_css_path = get_stylesheet_directory() . '/css/style.css';
	$child_css_uri  = get_stylesheet_directory_uri() . '/css/style.css';
	$child_version  = file_exists( $child_css_path ) ? filemtime( $child_css_path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'jong-literair-nederland-style', $child_css_uri, array( $parent_style_handle ), $child_version );

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

