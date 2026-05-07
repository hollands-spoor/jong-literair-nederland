<?php

/**
 * Normalize a single legacy oogst row into current bibliographic field keys.
 *
 * @param array $row Legacy oogst row.
 * @return array|null Normalized oogst entry or null when empty.
 */
function ln_map_oogst_legacy_row_to_entry( array $row ) {
	$titel          = isset( $row['titel'] ) ? $row['titel'] : '';
	$auteur         = isset( $row['auteur'] ) ? $row['auteur'] : '';
	$uitgever       = isset( $row['uitgever'] ) ? $row['uitgever'] : '';
	$prijs          = isset( $row['prijs'] ) ? $row['prijs'] : '';
	$aantal_paginas = isset( $row['aantal_paginas'] ) ? $row['aantal_paginas'] : '';
	$vertaler       = isset( $row['vertaler'] ) ? $row['vertaler'] : '';
	$vrije_regel    = isset( $row['vrije_regel'] ) ? $row['vrije_regel'] : '';
	$isbn           = isset( $row['isbn'] ) ? $row['isbn'] : '';
	$content        = isset( $row['beschrijving'] ) ? $row['beschrijving'] : '';
	$cover_id       = 0;
	$cover_url      = '';

	if ( ! empty( $row['afbeelding'] ) && is_array( $row['afbeelding'] ) ) {
		if ( ! empty( $row['afbeelding']['ID'] ) ) {
			$cover_id = absint( $row['afbeelding']['ID'] );
		} elseif ( ! empty( $row['afbeelding']['id'] ) ) {
			$cover_id = absint( $row['afbeelding']['id'] );
		}

		if ( ! empty( $row['afbeelding']['url'] ) ) {
			$cover_url = esc_url_raw( $row['afbeelding']['url'] );
		}
	}

	if ( $cover_id && '' === $cover_url ) {
		$cover_url = wp_get_attachment_image_url( $cover_id, 'thumbnail' );

		if ( ! $cover_url ) {
			$cover_url = wp_get_attachment_url( $cover_id );
		}
	}

	$entry = array(
		'bibliographic' => array(
			'boektitel'      => is_scalar( $titel ) ? (string) $titel : '',
			'auteur_boek'    => is_scalar( $auteur ) ? (string) $auteur : '',
			'uitgever'       => is_scalar( $uitgever ) ? (string) $uitgever : '',
			'prijs'          => is_scalar( $prijs ) ? (string) $prijs : '',
			'aantal_paginas' => is_scalar( $aantal_paginas ) ? (string) $aantal_paginas : '',
			'vertaling_door' => is_scalar( $vertaler ) ? (string) $vertaler : '',
			'vrije_regel'    => is_scalar( $vrije_regel ) ? (string) $vrije_regel : '',
			'isbn'           => is_scalar( $isbn ) ? (string) $isbn : '',
			'omslag_id'      => $cover_id ? (string) $cover_id : '',
			'omslag'         => $cover_url ? (string) $cover_url : '',
		),
		'content'       => is_scalar( $content ) ? (string) $content : '',
	);

	foreach ( $entry['bibliographic'] as $value ) {
		if ( '' !== $value && null !== $value ) {
			return $entry;
		}
	}

	return '' !== $entry['content'] ? $entry : null;
}

/**
 * Read oogst entries from the ACF repeater/flexible content field when available.
 *
 * @param int $post_id Post ID.
 * @return array Ordered oogst entries.
 */
function ln_get_oogst_entries_from_acf( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows = get_field( 'besproken_boeken', $post_id );

	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return array();
	}

	$entries = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$entry = ln_map_oogst_legacy_row_to_entry( $row );

		if ( null === $entry ) {
			continue;
		}

		$entries[] = $entry;
	}

	return $entries;
}

/**
 * Read oogst entries from flattened legacy post meta.
 *
 * @param int $post_id Post ID.
 * @return array Ordered oogst entries.
 */
function ln_get_oogst_entries_from_meta( $post_id ) {
	$all_meta = get_post_meta( $post_id );

	if ( empty( $all_meta ) || ! is_array( $all_meta ) ) {
		return array();
	}

	$legacy_to_current = array(
		'titel'        => 'boektitel',
		'auteur'       => 'auteur_boek',
		'vertaler'     => 'vertaling_door',
		'afbeelding'   => 'omslag_id',
		'beschrijving' => 'content',
	);
	$entries = array();

	foreach ( $all_meta as $meta_key => $meta_values ) {
		if ( ! preg_match( '/^besproken_boeken_(\d+)_(.+)$/', (string) $meta_key, $matches ) ) {
			continue;
		}

		if ( ! is_array( $meta_values ) || empty( $meta_values ) ) {
			continue;
		}

		$index = (int) $matches[1];
		$key   = (string) $matches[2];
		$value = $meta_values[0];

		if ( '' === $value || null === $value ) {
			continue;
		}

		if ( isset( $legacy_to_current[ $key ] ) ) {
			$key = $legacy_to_current[ $key ];
		}

		if ( ! isset( $entries[ $index ] ) ) {
			$entries[ $index ] = array(
				'bibliographic' => array(),
				'content'       => '',
			);
		}

		if ( 'content' === $key ) {
			$entries[ $index ]['content'] = is_scalar( $value ) ? (string) $value : '';
			continue;
		}

		if ( 'omslag_id' === $key ) {
			$attachment_id = absint( $value );

			if ( $attachment_id > 0 ) {
				$entries[ $index ]['bibliographic']['omslag_id'] = (string) $attachment_id;

				if ( empty( $entries[ $index ]['bibliographic']['omslag'] ) ) {
					$cover_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

					if ( ! $cover_url ) {
						$cover_url = wp_get_attachment_url( $attachment_id );
					}

					if ( $cover_url ) {
						$entries[ $index ]['bibliographic']['omslag'] = $cover_url;
					}
				}
			}

			continue;
		}

		$entries[ $index ]['bibliographic'][ $key ] = is_scalar( $value ) ? (string) $value : '';
	}

	if ( empty( $entries ) ) {
		return array();
	}

	ksort( $entries, SORT_NUMERIC );

	return array_values(
		array_filter(
			$entries,
			static function ( $entry ) {
				if ( ! empty( $entry['content'] ) ) {
					return true;
				}

				foreach ( $entry['bibliographic'] as $value ) {
					if ( '' !== $value && null !== $value ) {
						return true;
					}
				}

				return false;
			}
		)
	);
}

/**
 * Return ordered oogst entries for editor-side conversion.
 *
 * @param int $post_id Post ID.
 * @return array Ordered oogst entries.
 */
function ln_get_oogst_bibliographics_entries( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return array();
	}

	$acf_entries = ln_get_oogst_entries_from_acf( $post_id );

	if ( ! empty( $acf_entries ) ) {
		return $acf_entries;
	}

	return ln_get_oogst_entries_from_meta( $post_id );
}