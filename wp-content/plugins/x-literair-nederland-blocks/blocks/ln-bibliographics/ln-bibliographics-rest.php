<?php

/**
 * Fetch raw bibliographic data for a given ISBN from the external API.
 * 
 * TODO: make this a wrapper function that calls the API of choice ( easycbapi for now, but we might want to switch to something else in the future ) and does the mapping to internal keys, so we can easily swap out the API later if needed.
 *
 * @param string $isbn ISBN to look up.
 * @return array|WP_Error Parsed key => value pairs on success, or WP_Error on failure.
 */
function ln_fetch_bibliographics( $isbn ) {
	$isbn = trim( (string) $isbn );

	if ( '' === $isbn ) {
		return new WP_Error( 'ln_bibliographics_empty_isbn', __( 'ISBN is empty.', 'x-literair-nederland-blocks' ) );
	}

	$url = 'https://easycbapi.nl/isbn/' . rawurlencode( $isbn );

	$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	if ( 200 !== (int) $code || '' === $body ) {
		return new WP_Error(
			'ln_bibliographics_http_error',
			__( 'Failed to fetch bibliographic data.', 'x-literair-nederland-blocks' ),
			array(
				'code' => $code,
			)
		);
	}

	$lines = preg_split( '/\r?\n/', $body );
	$data  = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line || false === strpos( $line, ':' ) ) {
			continue;
		}

		list( $key, $value ) = explode( ':', $line, 2 );
		$key   = trim( $key );
		$value = trim( $value );

		if ( '' === $key ) {
			continue;
		}

		$data[ $key ] = $value;
	}

	global $bibliographic_api_mapping;
	$mapped = array();

	foreach ( $bibliographic_api_mapping as $external_key => $internal_slug ) {
		if ( isset( $data[ $external_key ] ) && '' !== $data[ $external_key ] ) {
			$mapped[ $internal_slug ] = $data[ $external_key ];
		}
	}

	$cover_image_url = sprintf( 'https://easycbapi.nl/resources/jpg/%s_VRK.jpg', rawurlencode( $isbn ) );

	if ( ! isset( $mapped['omslag'] ) && ! isset( $mapped['omslag_id'] ) ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$temp_file = download_url( $cover_image_url );

		if ( ! is_wp_error( $temp_file ) ) {
			$file_array = array(
				'name'     => $isbn . '-omslag.jpg',
				'tmp_name' => $temp_file,
			);

			$attachment_id = media_handle_sideload(
				$file_array,
				0,
				/* translators: %s: ISBN number. */
				sprintf( __( 'Cover image fetched for ISBN %s', 'x-literair-nederland-blocks' ), $isbn )
			);

			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $temp_file );
			} else {
				$cover_url = wp_get_attachment_url( $attachment_id );

				if ( $cover_url ) {
					$mapped['omslag'] = $cover_url;
				}

				$mapped['omslag_id'] = $attachment_id;
			}
		}
	}

	return $mapped;
}

/**
 * Return bibliographic values for a post, with legacy fallback.
 *
 * Current meta keys are preferred. For empty current values, the function
 * attempts to read matching legacy keys from besproken_boeken_{index}_{slug}.
 *
 * @param int $post_id Post ID.
 * @return array Slug => value map.
 */
function ln_get_bibliographics_post_fields( $post_id ) {
	global $bibliographic_fields;

	$post_id = (int) $post_id;
	if ( $post_id <= 0 || empty( $bibliographic_fields ) || ! is_array( $bibliographic_fields ) ) {
		return array();
	}

	$slugs  = array_keys( $bibliographic_fields );
	$values = array();

	foreach ( $slugs as $slug ) {
		$current = get_post_meta( $post_id, $slug, true );
		if ( '' !== $current && null !== $current ) {
			$values[ $slug ] = $current;
		}
	}

	$all_meta = get_post_meta( $post_id );

	foreach ( $slugs as $slug ) {
		if ( isset( $values[ $slug ] ) && '' !== $values[ $slug ] && null !== $values[ $slug ] ) {
			continue;
		}

		$pattern = '/^besproken_boeken_\d+_' . preg_quote( $slug, '/' ) . '$/';

		foreach ( $all_meta as $meta_key => $meta_values ) {
			if ( ! preg_match( $pattern, (string) $meta_key ) ) {
				continue;
			}

			if ( ! is_array( $meta_values ) || empty( $meta_values ) ) {
				continue;
			}

			$legacy_value = $meta_values[0];
			if ( '' === $legacy_value || null === $legacy_value ) {
				continue;
			}

			$values[ $slug ] = $legacy_value;
			break;
		}
	}

	$cover_attachment_id = 0;

	if ( isset( $values['omslag_id'] ) && '' !== $values['omslag_id'] ) {
		$cover_attachment_id = absint( $values['omslag_id'] );
	}

	if ( ! $cover_attachment_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			$cover_attachment_id = absint( $thumbnail_id );
		}
	}

	if ( $cover_attachment_id && ( ! isset( $values['omslag_id'] ) || '' === $values['omslag_id'] ) ) {
		$values['omslag_id'] = $cover_attachment_id;
	}

	if ( $cover_attachment_id && ( ! isset( $values['omslag'] ) || '' === $values['omslag'] ) ) {
		$cover_url = wp_get_attachment_image_url( $cover_attachment_id, 'thumbnail' );

		if ( ! $cover_url ) {
			$cover_url = wp_get_attachment_url( $cover_attachment_id );
		}

		if ( $cover_url ) {
			$values['omslag'] = $cover_url;
		}
	}

	return $values;
}

/**
 * REST endpoint used by the block editor to fetch bibliographic data.
 */
function ln_register_bibliographics_rest_routes() {
	register_rest_route(
		'x-ln/v1',
		'/bibliographics-post-meta/(?P<id>\\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => function ( WP_REST_Request $request ) {
				$post_id = (int) $request->get_param( 'id' );
				$fields  = ln_get_bibliographics_post_fields( $post_id );

				return new WP_REST_Response(
					array(
						'success' => true,
						'fields'  => $fields,
					),
					200
				);
			},
			'permission_callback' => function ( WP_REST_Request $request ) {
				$post_id = (int) $request->get_param( 'id' );

				return $post_id > 0 && current_user_can( 'edit_post', $post_id );
			},
		)
	);

	register_rest_route(
		'x-ln/v1',
		'/bibliographics',
		array(
			'methods'             => 'GET',
			'callback'            => function ( WP_REST_Request $request ) {
				$isbn   = $request->get_param( 'isbn' );
				$result = ln_fetch_bibliographics( $isbn );

				if ( is_wp_error( $result ) ) {
					return new WP_REST_Response(
						array(
							'success' => false,
							'message' => $result->get_error_message(),
						),
						400
					);
				}

				return new WP_REST_Response(
					array(
						'success' => true,
						'fields'  => $result,
					),
					200
				);
			},
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_rest_route(
		'x-ln/v1',
		'/bibliographics-oogst-entries/(?P<id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => function ( WP_REST_Request $request ) {
				$post_id = (int) $request->get_param( 'id' );
				$entries = ln_get_oogst_bibliographics_entries( $post_id );

				return new WP_REST_Response(
					array(
						'success' => true,
						'entries' => $entries,
					),
					200
				);
			},
			'permission_callback' => function ( WP_REST_Request $request ) {
				$post_id = (int) $request->get_param( 'id' );

				return $post_id > 0 && current_user_can( 'edit_post', $post_id );
			},
		)
	);
}
add_action( 'rest_api_init', 'ln_register_bibliographics_rest_routes' );