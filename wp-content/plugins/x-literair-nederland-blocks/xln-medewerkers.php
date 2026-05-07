<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const XLN_MEDEWERKERS_CONFIG = array(
	'apply_to'          => array(
		'post_types' => array(
			'post',
		),
		'categories' => array(),
	),
	'field_types'       => array(
		'medewerker' => array(
			'post_type'        => 'medewerker',
			'meta_key'         => 'medewerker_id',
			'legacy_text_meta' => 'medewerker',
			'label'            => 'Contributor',
		),
		'recensent'  => array(
			'post_type'        => 'recensent',
			'meta_key'         => 'auteur_recensie',
			'legacy_text_meta' => '',
			'label'            => 'Reviewer',
		),
	),
	'rest_namespace'    => 'x-ln/v1',
	'rest_route'        => '/medewerkers',
	'search_min_length' => 2,
);

function xln_medewerkers_get_config( $key = null ) {
	if ( null === $key ) {
		return XLN_MEDEWERKERS_CONFIG;
	}

	return XLN_MEDEWERKERS_CONFIG[ $key ] ?? null;
}

function xln_medewerkers_get_target_post_types() {
	$apply_to   = xln_medewerkers_get_config( 'apply_to' );
	$post_types = is_array( $apply_to ) && isset( $apply_to['post_types'] ) ? $apply_to['post_types'] : array();

	if ( ! is_array( $post_types ) ) {
		return array();
	}

	return array_values(
		array_unique(
			array_filter(
				array_map( 'sanitize_key', $post_types )
			)
		)
	);
}

function xln_medewerkers_get_field_types() {
	$field_types = xln_medewerkers_get_config( 'field_types' );

	return is_array( $field_types ) ? $field_types : array();
}

function xln_medewerkers_get_field_type_config( $field_type ) {
	$field_type  = sanitize_key( $field_type );
	$field_types = xln_medewerkers_get_field_types();

	return isset( $field_types[ $field_type ] ) && is_array( $field_types[ $field_type ] )
		? $field_types[ $field_type ]
		: null;
}

function xln_medewerkers_is_supported_field_type( $field_type ) {
	return null !== xln_medewerkers_get_field_type_config( $field_type );
}

function xln_medewerkers_get_related_post_type( $field_type ) {
	$config = xln_medewerkers_get_field_type_config( $field_type );

	return $config && ! empty( $config['post_type'] ) ? sanitize_key( $config['post_type'] ) : '';
}

function xln_medewerkers_get_meta_key_for_field_type( $field_type ) {
	$config = xln_medewerkers_get_field_type_config( $field_type );

	return $config && ! empty( $config['meta_key'] ) ? sanitize_key( $config['meta_key'] ) : '';
}

function xln_medewerkers_get_legacy_text_meta_key( $field_type ) {
	$config = xln_medewerkers_get_field_type_config( $field_type );

	return $config && ! empty( $config['legacy_text_meta'] ) ? sanitize_key( $config['legacy_text_meta'] ) : '';
}

function xln_medewerkers_get_query_statuses() {
	return array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' );
}

function xln_medewerkers_format_result( $post, $field_type ) {
	if ( ! ( $post instanceof WP_Post ) ) {
		return null;
	}

	if ( $post->post_type !== xln_medewerkers_get_related_post_type( $field_type ) ) {
		return null;
	}

	return array(
		'id'        => (int) $post->ID,
		'label'     => get_the_title( $post ),
		'post_type' => $post->post_type,
		'status'    => $post->post_status,
	);
}

function xln_medewerkers_find_existing_by_title( $title, $field_type ) {
	global $wpdb;

	$title     = trim( (string) $title );
	$post_type = xln_medewerkers_get_related_post_type( $field_type );
	$statuses  = xln_medewerkers_get_query_statuses();

	if ( '' === $title || '' === $post_type ) {
		return null;
	}

	$status_placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
	$sql                 = $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND post_status IN ({$status_placeholders}) ORDER BY ID ASC LIMIT 1",
		array_merge( array( $title, $post_type ), $statuses )
	);
	$post_id             = (int) $wpdb->get_var( $sql );

	return $post_id > 0 ? get_post( $post_id ) : null;
}

function xln_medewerkers_search_items( $field_type, $search = '', $selected_id = 0 ) {
	$search      = trim( (string) $search );
	$selected_id = absint( $selected_id );
	$results     = array();
	$post_type   = xln_medewerkers_get_related_post_type( $field_type );

	if ( '' === $post_type ) {
		return array();
	}

	if ( '' !== $search ) {
		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'post_status'            => xln_medewerkers_get_query_statuses(),
				'posts_per_page'         => 20,
				's'                      => $search,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$item = xln_medewerkers_format_result( $post, $field_type );

				if ( null !== $item ) {
					$results[ $item['id'] ] = $item;
				}
			}
		}

		wp_reset_postdata();
	}

	if ( $selected_id > 0 ) {
		$item = xln_medewerkers_format_result( get_post( $selected_id ), $field_type );

		if ( null !== $item ) {
			$results = array( $item['id'] => $item ) + $results;
		}
	}

	return array_values( $results );
}

function xln_medewerkers_delete_meta_if_needed( $post_id, $meta_key ) {
	$meta_key = sanitize_key( $meta_key );

	if ( '' === $meta_key ) {
		return;
	}

	delete_post_meta( $post_id, $meta_key );
}

function xln_medewerkers_normalize_post_meta( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return;
	}

	$medewerker_meta_key   = xln_medewerkers_get_meta_key_for_field_type( 'medewerker' );
	$recensent_meta_key    = xln_medewerkers_get_meta_key_for_field_type( 'recensent' );
	$legacy_medewerker_key = xln_medewerkers_get_legacy_text_meta_key( 'medewerker' );
	$medewerker_id         = absint( get_post_meta( $post_id, $medewerker_meta_key, true ) );
	$recensent_id          = absint( get_post_meta( $post_id, $recensent_meta_key, true ) );

	if ( $medewerker_id > 0 ) {
		update_post_meta( $post_id, $medewerker_meta_key, $medewerker_id );
		xln_medewerkers_delete_meta_if_needed( $post_id, $recensent_meta_key );

		if ( '' !== $legacy_medewerker_key ) {
			update_post_meta( $post_id, $legacy_medewerker_key, get_the_title( $medewerker_id ) );
		}

		return;
	}

	if ( $recensent_id > 0 ) {
		update_post_meta( $post_id, $recensent_meta_key, $recensent_id );
		xln_medewerkers_delete_meta_if_needed( $post_id, $medewerker_meta_key );

		if ( '' !== $legacy_medewerker_key ) {
			xln_medewerkers_delete_meta_if_needed( $post_id, $legacy_medewerker_key );
		}

		return;
	}

	xln_medewerkers_delete_meta_if_needed( $post_id, $medewerker_meta_key );
	xln_medewerkers_delete_meta_if_needed( $post_id, $recensent_meta_key );

	if ( '' !== $legacy_medewerker_key ) {
		xln_medewerkers_delete_meta_if_needed( $post_id, $legacy_medewerker_key );
	}
}

function xln_medewerkers_register_meta() {
	$field_types = xln_medewerkers_get_field_types();

	if ( empty( $field_types ) ) {
		return;
	}

	foreach ( xln_medewerkers_get_target_post_types() as $post_type ) {
		foreach ( array_keys( $field_types ) as $field_type ) {
			$meta_key = xln_medewerkers_get_meta_key_for_field_type( $field_type );

			if ( '' === $meta_key ) {
				continue;
			}

			register_post_meta(
				$post_type,
				$meta_key,
				array(
					'single'            => true,
					'show_in_rest'      => true,
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'xln_medewerkers_register_meta' );

/**
 * Prevent false update failures for post meta that is effectively unchanged.
 *
 * WordPress stores all meta values as strings. When the REST API or block
 * editor sends a typed value (e.g. int or identical string) for a meta key
 * whose DB value is unchanged, update_metadata() may report 0 rows affected
 * and WordPress surfaces a spurious "could not update meta" error.
 *
 * This filter short-circuits the update with a success signal when the
 * serialized value is identical to what is already stored.
 */
function xln_medewerkers_prevent_noop_meta_failure( $check, $object_id, $meta_key, $meta_value ) {
	$old_value = get_post_meta( $object_id, $meta_key, true );

	if ( maybe_serialize( $meta_value ) === maybe_serialize( $old_value ) ) {
		return true;
	}

	return $check;
}
add_filter( 'update_post_metadata', 'xln_medewerkers_prevent_noop_meta_failure', 10, 4 );

function xln_medewerkers_sync_relationship_meta( $post_id, $post ) {
	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! in_array( $post->post_type, xln_medewerkers_get_target_post_types(), true ) ) {
		return;
	}

	xln_medewerkers_normalize_post_meta( $post_id );
}
add_action( 'save_post', 'xln_medewerkers_sync_relationship_meta', 20, 2 );

function xln_medewerkers_register_rest_routes() {
	$namespace = xln_medewerkers_get_config( 'rest_namespace' );
	$route     = xln_medewerkers_get_config( 'rest_route' );

	register_rest_route(
		$namespace,
		$route,
		array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => static function ( WP_REST_Request $request ) {
					$field_type  = sanitize_key( (string) $request->get_param( 'fieldType' ) );
					$search      = (string) $request->get_param( 'search' );
					$selected_id = absint( $request->get_param( 'selected' ) );

					if ( ! xln_medewerkers_is_supported_field_type( $field_type ) ) {
						return new WP_Error( 'xln_medewerkers_invalid_field_type', __( 'Invalid relationship field.', 'x-literair-nederland-blocks' ), array( 'status' => 400 ) );
					}

					return new WP_REST_Response(
						array(
							'success' => true,
							'items'   => xln_medewerkers_search_items( $field_type, $search, $selected_id ),
						),
						200
					);
				},
				'permission_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => static function ( WP_REST_Request $request ) {
					$field_type = sanitize_key( (string) $request->get_param( 'fieldType' ) );
					$name       = trim( sanitize_text_field( (string) $request->get_param( 'name' ) ) );

					if ( ! xln_medewerkers_is_supported_field_type( $field_type ) ) {
						return new WP_Error( 'xln_medewerkers_invalid_field_type', __( 'Invalid relationship field.', 'x-literair-nederland-blocks' ), array( 'status' => 400 ) );
					}

					if ( '' === $name ) {
						return new WP_Error( 'xln_medewerkers_empty_name', __( 'Please enter a name first.', 'x-literair-nederland-blocks' ), array( 'status' => 400 ) );
					}

					$existing_post = xln_medewerkers_find_existing_by_title( $name, $field_type );

					if ( $existing_post instanceof WP_Post ) {
						return new WP_REST_Response(
							array(
								'success' => true,
								'created' => false,
								'item'    => xln_medewerkers_format_result( $existing_post, $field_type ),
							),
							200
						);
					}

					$post_id = wp_insert_post(
						array(
							'post_author'  => get_current_user_id(),
							'post_title'   => $name,
							'post_content' => '',
							'post_excerpt' => '',
							'post_status'  => 'draft',
							'post_type'    => xln_medewerkers_get_related_post_type( $field_type ),
						),
						true
					);

					if ( is_wp_error( $post_id ) ) {
						return new WP_Error( 'xln_medewerkers_create_failed', $post_id->get_error_message(), array( 'status' => 500 ) );
					}

					return new WP_REST_Response(
						array(
							'success' => true,
							'created' => true,
							'item'    => xln_medewerkers_format_result( get_post( $post_id ), $field_type ),
						),
						201
					);
				},
				'permission_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
			),
		)
	);
}
add_action( 'rest_api_init', 'xln_medewerkers_register_rest_routes' );

function xln_medewerkers_enqueue_editor_assets() {
	$handle = 'xln-medewerkers-editor';
	$build_dir = plugin_dir_path( __FILE__ ) . 'includes/xln-medewerkers-editor/build/';
	$asset_path = $build_dir . 'index.asset.php';
	$script_path = $build_dir . 'index.js';

	if ( ! file_exists( $asset_path ) || ! file_exists( $script_path ) ) {
		return;
	}

	$asset = require $asset_path;

	wp_enqueue_script(
		$handle,
		plugin_dir_url( __FILE__ ) . 'includes/xln-medewerkers-editor/build/index.js',
		isset( $asset['dependencies'] ) ? $asset['dependencies'] : array(),
		isset( $asset['version'] ) ? $asset['version'] : '0.1.0',
		true
	);

	wp_localize_script(
		$handle,
		'XlnMedewerkersSettings',
		array(
			'restPath'        => '/' . trim( xln_medewerkers_get_config( 'rest_namespace' ), '/' ) . xln_medewerkers_get_config( 'rest_route' ),
			'targetPostTypes' => xln_medewerkers_get_target_post_types(),
			'fieldTypes'      => array(
				'medewerker' => array(
					'metaKey'           => xln_medewerkers_get_meta_key_for_field_type( 'medewerker' ),
					'postType'          => xln_medewerkers_get_related_post_type( 'medewerker' ),
					'legacyTextMetaKey' => xln_medewerkers_get_legacy_text_meta_key( 'medewerker' ),
				),
				'recensent'  => array(
					'metaKey'           => xln_medewerkers_get_meta_key_for_field_type( 'recensent' ),
					'postType'          => xln_medewerkers_get_related_post_type( 'recensent' ),
					'legacyTextMetaKey' => xln_medewerkers_get_legacy_text_meta_key( 'recensent' ),
				),
			),
			'searchMinLength' => (int) xln_medewerkers_get_config( 'search_min_length' ),
			'labels'          => array(
				'panelTitle'               => __( 'Authors', 'x-literair-nederland-blocks' ),
				'medewerkerTitle'          => __( 'Contributor', 'x-literair-nederland-blocks' ),
				'recensentTitle'           => __( 'Reviewer', 'x-literair-nederland-blocks' ),
				'medewerkerPlaceholder'    => __( 'Search for a contributor', 'x-literair-nederland-blocks' ),
				'recensentPlaceholder'     => __( 'Search for a reviewer', 'x-literair-nederland-blocks' ),
				'medewerkerCreateButton'   => __( 'Create new contributor', 'x-literair-nederland-blocks' ),
				'recensentCreateButton'    => __( 'Create new reviewer', 'x-literair-nederland-blocks' ),
				'medewerkerSuccess'        => __( 'Contributor linked.', 'x-literair-nederland-blocks' ),
				'recensentSuccess'         => __( 'Reviewer linked.', 'x-literair-nederland-blocks' ),
				'medewerkerCreateSuccess'  => __( 'New contributor created and linked.', 'x-literair-nederland-blocks' ),
				'recensentCreateSuccess'   => __( 'New reviewer created and linked.', 'x-literair-nederland-blocks' ),
				'loading'                  => __( 'Searching...', 'x-literair-nederland-blocks' ),
				'noResults'                => __( 'No results found.', 'x-literair-nederland-blocks' ),
				'minLength'                => __( 'Type at least 2 characters to search.', 'x-literair-nederland-blocks' ),
				'helper'                   => __( 'Fill in a contributor or a reviewer. If you choose one, the other field is cleared automatically.', 'x-literair-nederland-blocks' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'xln_medewerkers_enqueue_editor_assets' );

function xln_medewerkers_get_writer_post_for_post( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return null;
	}

	$recensent_id  = absint( get_post_meta( $post_id, 'auteur_recensie', true ) );
	$medewerker_id = absint( get_post_meta( $post_id, 'medewerker_id', true ) );
	$writer_id     = $recensent_id > 0 ? $recensent_id : $medewerker_id;

	if ( $writer_id <= 0 ) {
		return null;
	}

	$writer_post = get_post( $writer_id );

	if ( ! ( $writer_post instanceof WP_Post ) || '' === $writer_post->post_title ) {
		return null;
	}

	return $writer_post;
}

function xln_medewerkers_add_post_list_door_column( $columns ) {
	$columns_with_door = array();

	foreach ( $columns as $key => $label ) {
		$columns_with_door[ $key ] = $label;

		if ( 'author' === $key ) {
			$columns_with_door['xln_door'] = __( 'By:', 'x-literair-nederland-blocks' );
		}
	}

	if ( ! isset( $columns_with_door['xln_door'] ) ) {
		$columns_with_door['xln_door'] = __( 'By:', 'x-literair-nederland-blocks' );
	}

	return $columns_with_door;
}
add_filter( 'manage_edit-post_columns', 'xln_medewerkers_add_post_list_door_column' );

function xln_medewerkers_render_post_list_door_column( $column_name, $post_id ) {
	if ( 'xln_door' !== $column_name ) {
		return;
	}

	$writer_post = xln_medewerkers_get_writer_post_for_post( $post_id );

	if ( ! ( $writer_post instanceof WP_Post ) ) {
		echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . esc_html__( 'No linked author', 'x-literair-nederland-blocks' ) . '</span>';
		return;
	}

	$edit_url = admin_url( 'post.php?post=' . (int) $writer_post->ID . '&action=edit' );

	echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $writer_post->post_title ) . '</a>';
}
add_action( 'manage_post_posts_custom_column', 'xln_medewerkers_render_post_list_door_column', 10, 2 );