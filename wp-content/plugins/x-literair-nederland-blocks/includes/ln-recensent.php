<?php
if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'xln_register_recensent_post_type' ) ) {
	/**
	 * Register the recensent post type when not already available.
	 *
	 * Allows this plugin to run in environments where another plugin
	 * may already register the same post type.
	 */
	function xln_register_recensent_post_type() {
		if ( post_type_exists( 'recensent' ) ) {
			return;
		}

		register_post_type(
			'recensent',
			array(
				'labels' => array(
					'name' => __( 'Reviewers', 'x-literair-nederland-blocks' ),
					'singular_name' => __( 'Reviewer', 'x-literair-nederland-blocks' ),
					'add_new' => _x( 'Add New Reviewer', 'recensent', 'x-literair-nederland-blocks' ),
					'new_item' => _x( 'Add New Reviewer', 'recensent', 'x-literair-nederland-blocks' ),
					'add_new_item' => _x( 'Add New Reviewer', 'recensent', 'x-literair-nederland-blocks' ),
				),
				'taxonomies' => array( 'category' ),
				'public' => true,
				'show_in_rest' => true,
				'publicly_queryable' => true,
				'has_archive' => true,
				'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ),
			)
		);
	}
}

add_action( 'init', 'xln_register_recensent_post_type' );

