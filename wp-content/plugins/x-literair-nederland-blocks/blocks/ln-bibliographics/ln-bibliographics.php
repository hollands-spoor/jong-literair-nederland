<?php

/**
 *
 * isbn:9789002219788
 * price:9.95
 * title:Het generatiepact
 * author:B. Huygebaert
 * pages:168
 * language:nl
 * publisher:Standaard Uitgeverij - Algemeen
 * publishingdate:20060701
 */

if ( ! defined( 'LN_BIBLIOGRAPHICS_ENTRIES_META_KEY' ) ) {
	define( 'LN_BIBLIOGRAPHICS_ENTRIES_META_KEY', 'ln_bibliographics_entries' );
}

require_once __DIR__ . '/ln-bibliographics-legacy.php';
require_once __DIR__ . '/ln-bibliographics-rest.php';
require_once __DIR__ . '/ln-bibliographics-oogst.php';

$isbn_apis = array(
	'easycbapi'          => 'https://easycbapi.nl/isbn/%s',
	'hollands-spoor.com' => 'https://hollands-spoor.com/api/isbn/%s',
);

$buy_buttons = array();

$default_vendor            = 'libris';
$bibliographic_fields      = array();
$bibliographic_api_mapping = array();

add_action( 'show_buy_button', 'ln_bibliographics_do_buy_button', 10, 1 );

function ln_bibliographics_do_buy_button( $isbn ) {
	global $buy_buttons, $default_vendor;

	$vendor = $default_vendor;

	if ( ! isset( $buy_buttons[ $vendor ] ) ) {
		return;
	}

	$button = $buy_buttons[ $vendor ];
	$url    = sprintf( $button['url'], rawurlencode( $isbn ) );
	$label  = $button['label'];

	echo sprintf(
		'<a href="%s" target="_blank" rel="noopener" class="ln-bibliographics__buy-button">%s</a>',
		esc_url( $url ),
		esc_html( $label )
	);
}

/**
 * Sanitize numeric bibliographic meta values.
 *
 * @param mixed  $value       Raw meta value.
 * @param string $meta_key    Meta key.
 * @param string $object_type Object type.
 *
 * @return float|string Sanitized numeric value or empty string.
 */
function ln_bibliographics_sanitize_number_meta( $value, $meta_key, $object_type ) {
	unset( $meta_key, $object_type );

	if ( '' === $value || null === $value ) {
		return '';
	}

	return floatval( $value );
}

/**
 * Ensure the structured bibliographic entries meta always stores an array.
 *
 * @param mixed  $value       Raw meta value.
 * @param string $meta_key    Meta key.
 * @param string $object_type Object type.
 *
 * @return array Sanitized entries array.
 */
function ln_bibliographics_sanitize_entries_meta( $value, $meta_key, $object_type ) {
	unset( $meta_key, $object_type );

	if ( ! is_array( $value ) ) {
		return array();
	}

	return $value;
}

/**
 * Central mapping from external API keys to internal bibliographic field slugs.
 *
 * @return array
 */
function ln_get_bibliographic_api_mapping() {
	return array(
		// 'isbn' is intentionally omitted, as it is already provided by the user.
		'title'     => 'boektitel',
		'author'    => 'auteur_boek',
		'price'     => 'prijs',
		'pages'     => 'aantal_paginas',
		'publisher' => 'uitgever',
	);
}

/**
 * Initialize bibliographic field configuration and API mapping.
 *
 * Runs on init to avoid triggering just-in-time translation loading too early.
 */
function ln_bibliographics_init_config() {
	global $bibliographic_fields, $bibliographic_api_mapping, $buy_buttons;

	$buy_buttons = array(
		'libris' => array(
			'url'   => 'https://libris.nl/boeken/?tt=33780_12_463014_&r=%s',
			'label' => __( 'Buy with Libris', 'x-literair-nederland-blocks' ),
		),
		'bol'    => array(
			'url'   => 'https://www.bol.com/nl/nl/s/?searchtext=%s',
			'label' => __( 'Buy with Bol', 'x-literair-nederland-blocks' ),
		),
	);

	$bibliographic_fields = array(
		'isbn'                  => array(
			'type'  => 'textline',
			'label' => __( 'ISBN', 'x-literair-nederland-blocks' ),
		),
		'boektitel'             => array(
			'type'  => 'richtext',
			'label' => __( 'Book title', 'x-literair-nederland-blocks' ),
		),
		'auteur_boek'           => array(
			'type'  => 'textline',
			'label' => __( 'Author', 'x-literair-nederland-blocks' ),
		),
		'auteur_boek_url'       => array(
			'type'  => 'url',
			'label' => __( 'Author website', 'x-literair-nederland-blocks' ),
		),
		'uitgever'              => array(
			'type'  => 'textline',
			'label' => __( 'Publisher', 'x-literair-nederland-blocks' ),
		),
		'uitgever_url'          => array(
			'type'  => 'url',
			'label' => __( 'Publisher website', 'x-literair-nederland-blocks' ),
		),
		'aantal_paginas'        => array(
			'type'  => 'number',
			'label' => __( 'Number of pages', 'x-literair-nederland-blocks' ),
		),
		'prijs'                 => array(
			'type'  => 'textline',
			'label' => __( 'Price', 'x-literair-nederland-blocks' ),
		),
		'vrije_regel'           => array(
			'type'  => 'richtext',
			'label' => __( 'Free line', 'x-literair-nederland-blocks' ),
		),
		'vertaling_door'        => array(
			'type'  => 'textline',
			'label' => __( 'Translation by', 'x-literair-nederland-blocks' ),
		),
		'oorspronkelijke_titel' => array(
			'type'  => 'textline',
			'label' => __( 'Original title', 'x-literair-nederland-blocks' ),
		),
		'nawoord_door'          => array(
			'type'  => 'textline',
			'label' => __( 'Afterword by', 'x-literair-nederland-blocks' ),
		),
		'illustraties_door'     => array(
			'type'  => 'textline',
			'label' => __( 'Illustrations by', 'x-literair-nederland-blocks' ),
		),
		'omslag'                => array(
			'type'  => 'image',
			'label' => __( 'Cover image URL', 'x-literair-nederland-blocks' ),
		),
		'omslag_id'             => array(
			'type'   => 'number',
			'label'  => __( 'Cover image ID', 'x-literair-nederland-blocks' ),
			'hidden' => true,
		),
	);

	$bibliographic_fields      = apply_filters( 'bibliographic_fields', $bibliographic_fields );
	$bibliographic_api_mapping = ln_get_bibliographic_api_mapping();
}
add_action( 'init', 'ln_bibliographics_init_config', 0 );

$bibliographic_api_mapping = ln_get_bibliographic_api_mapping();

/**
 * Register post meta for every bibliographic field slug.
 */
function ln_register_bibliographics_meta() {
	global $bibliographic_fields;

	if ( empty( $bibliographic_fields ) || ! is_array( $bibliographic_fields ) ) {
		return;
	}

	foreach ( $bibliographic_fields as $slug => $config ) {
		$field_type = isset( $config['type'] ) ? $config['type'] : 'textline';

		switch ( $field_type ) {
			case 'number':
				$type              = 'number';
				$sanitize_callback = 'ln_bibliographics_sanitize_number_meta';
				break;
			case 'richtext':
				$type              = 'string';
				$sanitize_callback = 'wp_kses_post';
				break;
			case 'url':
				$type              = 'string';
				$sanitize_callback = 'esc_url_raw';
				break;
			default:
				$type              = 'string';
				$sanitize_callback = 'sanitize_text_field';
				break;
		}

		register_post_meta(
			'',
			$slug,
			array(
				'single'            => true,
				'show_in_rest'      => true,
				'type'              => $type,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	register_post_meta(
		'',
		LN_BIBLIOGRAPHICS_ENTRIES_META_KEY,
		array(
			'single'            => true,
			// Internal snapshot meta generated from block content on save_post.
			// Keep this out of REST so Gutenberg does not try to persist it directly.
			'show_in_rest'      => false,
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'ln_bibliographics_sanitize_entries_meta',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'ln_register_bibliographics_meta' );

/**
 * On save_post, mirror all bibliographic attribute values into post meta
 * using the field slug as the meta key.
 */
function ln_sync_bibliographics_meta( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( 'revision' === $post->post_type ) {
		return;
	}

	global $bibliographic_fields;

	if ( empty( $bibliographic_fields ) || ! is_array( $bibliographic_fields ) ) {
		return;
	}

	$slugs = array_keys( $bibliographic_fields );

	$raw_content = isset( $post->post_content ) ? $post->post_content : '';

	// If the post does not contain the block, leave legacy post meta untouched.
	// This keeps existing bibliographic data intact for legacy posts that are saved
	// without having been converted to the block yet.
	if ( ! has_block( 'ln/ln-bibliographics', $raw_content ) ) {
		delete_post_meta( $post_id, LN_BIBLIOGRAPHICS_ENTRIES_META_KEY );
		return;
	}

	$blocks  = parse_blocks( $raw_content );
	$entries = ln_collect_bibliographics_entries( $blocks, $slugs );

	if ( empty( $entries ) ) {
		delete_post_meta( $post_id, LN_BIBLIOGRAPHICS_ENTRIES_META_KEY );
		return;
	}

	update_post_meta( $post_id, LN_BIBLIOGRAPHICS_ENTRIES_META_KEY, $entries );

	// Clear all legacy meta before writing the current snapshot so removed entries disappear.
	ln_delete_legacy_bibliographics_meta( $post_id, $slugs );

	foreach ( $entries as $index => $entry ) {
		$legacy_values = ln_extract_bibliographics_legacy_values( $entry, $slugs );
		ln_sync_legacy_bibliographics_fields( $post_id, $slugs, $legacy_values, $index );
	}
}
add_action( 'save_post', 'ln_sync_bibliographics_meta', 10, 3 );

/**
 * Recursively collect all bibliographic block entries in display order.
 *
 * @param array $blocks Parsed blocks array.
 * @param array $slugs  Allowed field slugs.
 * @param array $entries Accumulator for recursion.
 *
 * @return array Structured bibliographic entries.
 */
function ln_collect_bibliographics_entries( array $blocks, array $slugs, array $entries = array() ) {
	foreach ( $blocks as $block ) {
		if ( empty( $block['blockName'] ) ) {
			continue;
		}

		if ( 'ln/ln-bibliographics' === $block['blockName'] ) {
			$fields = array();

			if ( ! empty( $block['attrs']['bibliographic'] ) && is_array( $block['attrs']['bibliographic'] ) ) {
				foreach ( $slugs as $slug ) {
					if ( array_key_exists( $slug, $block['attrs']['bibliographic'] ) ) {
						$fields[ $slug ] = $block['attrs']['bibliographic'][ $slug ];
					}
				}
			}

			$entries[] = array(
				'index'  => count( $entries ),
				'fields' => $fields,
			);
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$entries = ln_collect_bibliographics_entries( $block['innerBlocks'], $slugs, $entries );
		}
	}

	return $entries;
}

/**
 * Reduce structured entries back into a slug => value map (last non-empty wins).
 *
 * @param array $entry Structured bibliographic entry.
 * @param array $slugs Allowed field slugs.
 *
 * @return array Legacy slug => value pairs.
 */
function ln_extract_bibliographics_legacy_values( array $entry, array $slugs ) {
	$legacy_values = array();
	if ( empty( $entry['fields'] ) || ! is_array( $entry['fields'] ) ) {
		return $legacy_values;
	}

	foreach ( $slugs as $slug ) {
		if ( isset( $entry['fields'][ $slug ] ) && '' !== $entry['fields'][ $slug ] ) {
			$legacy_values[ $slug ] = $entry['fields'][ $slug ];
		}
	}

	return $legacy_values;
}

/**
 * Delete all legacy per-field bibliographic meta rows for a given post.
 *
 * @param int   $post_id Post identifier.
 * @param array $slugs   Field slugs to clear.
 */
function ln_delete_legacy_bibliographics_meta( $post_id, array $slugs ) {
	foreach ( $slugs as $slug ) {
		delete_post_meta( $post_id, $slug );
	}

	ln_delete_post_meta_with_prefix( $post_id, 'besproken_boeken_' );
}

/**
 * Delete post meta rows whose meta_key starts with a given prefix for a post.
 *
 * @param int    $post_id Post identifier.
 * @param string $prefix  Meta key prefix to remove.
 */
function ln_delete_post_meta_with_prefix( $post_id, $prefix ) {
	global $wpdb;

	$prefix = (string) $prefix;
	if ( '' === $prefix ) {
		return;
	}

	$like = $wpdb->esc_like( $prefix ) . '%';

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s",
			$post_id,
			$like
		)
	);

	// Bust the post meta cache since we manipulated the table directly.
	wp_cache_delete( $post_id, 'post_meta' );
}

/**
 * Write legacy per-field bibliographic meta rows based on structured entries.
 *
 * @param int   $post_id       Post identifier.
 * @param array $slugs         Field slugs to sync.
 * @param array $legacy_values Slug => value map.
 * @param int   $index         Entry index.
 */
function ln_sync_legacy_bibliographics_fields( $post_id, array $slugs, array $legacy_values, $index = 0 ) {
	$prefix = sprintf( 'besproken_boeken_%d_', $index );
	foreach ( $slugs as $slug ) {
		$value = isset( $legacy_values[ $slug ] ) ? $legacy_values[ $slug ] : '';

		if ( '' === $value || null === $value ) {
			delete_post_meta( $post_id, $prefix . $slug );
			continue;
		}

		update_post_meta( $post_id, $prefix . $slug, $value );
	}
}

/**
 * Expose the (filtered) bibliographic fields configuration to the block editor.
 */
function ln_bibliographics_enqueue_editor_assets() {
	global $bibliographic_fields, $buy_buttons;

	if ( empty( $bibliographic_fields ) || ! is_array( $bibliographic_fields ) ) {
		return;
	}

	$data = array(
		'fields'     => $bibliographic_fields,
		'buyButtons' => $buy_buttons,
	);

	$debug_enabled = false;

	if ( current_user_can( 'manage_options' ) && isset( $_GET['lnAutoInsertDebug'] ) ) {
		$debug_enabled = true;
	}

	$debug_enabled = (bool) apply_filters( 'ln_auto_insert_bibliographics_debug', $debug_enabled );

	wp_add_inline_script(
		'wp-block-editor',
		'window.lnBibliographicFields = ' . wp_json_encode( $data ) . ';window.lnAutoInsertBibliographicsDebug = ' . wp_json_encode( $debug_enabled ) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'ln_bibliographics_enqueue_editor_assets' );

/**
 * Convenience helper to retrieve structured bibliographic entries for a post.
 *
 * @param int $post_id Post identifier.
 * @return array Structured entries for the post.
 */
function ln_get_bibliographics_entries( $post_id ) {
	$entries = get_post_meta( $post_id, LN_BIBLIOGRAPHICS_ENTRIES_META_KEY, true );

	return is_array( $entries ) ? $entries : array();
}

/**
 * Resolve a cover attachment ID from bibliographics data for a given post.
 *
 * @param int $post_id Post identifier.
 * @return int Attachment ID, or 0 if unavailable.
 */
function ln_get_bibliographics_cover_image_id( $post_id ) {
	$post_id = absint( $post_id );

	if ( ! $post_id ) {
		return 0;
	}

	$entries = ln_get_bibliographics_entries( $post_id );

	if ( ! empty( $entries ) && is_array( $entries ) ) {
		foreach ( $entries as $entry ) {
			if ( empty( $entry['fields'] ) || ! is_array( $entry['fields'] ) ) {
				continue;
			}

			if ( ! empty( $entry['fields']['omslag_id'] ) ) {
				$cover_id = absint( $entry['fields']['omslag_id'] );

				if ( $cover_id ) {
					return $cover_id;
				}
			}
		}
	}

	$legacy_cover_id = absint( get_post_meta( $post_id, 'besproken_boeken_0_omslag_id', true ) );

	if ( $legacy_cover_id ) {
		return $legacy_cover_id;
	}

	return absint( get_post_meta( $post_id, 'omslag_id', true ) );
}

/**
 * Fallback render for core/post-featured-image using LN bibliographics cover image.
 *
 * @param string        $block_content Rendered block content.
 * @param array         $parsed_block  Parsed block data.
 * @param WP_Block|null $block         Block instance.
 * @return string
 */
function ln_bibliographics_render_post_featured_image_fallback( $block_content, $parsed_block, $block ) {
	if ( '' !== trim( (string) $block_content ) ) {
		return $block_content;
	}

	if ( ! ( $block instanceof WP_Block ) || empty( $block->context['postId'] ) ) {
		return $block_content;
	}

	$post_id = absint( $block->context['postId'] );

	if ( ! $post_id || has_post_thumbnail( $post_id ) ) {
		return $block_content;
	}

	$cover_image_id = ln_get_bibliographics_cover_image_id( $post_id );

	if ( ! $cover_image_id ) {
		return $block_content;
	}

	if ( ! function_exists( 'render_block_core_post_featured_image' ) ) {
		return $block_content;
	}

	$thumbnail_filter = function ( $thumbnail_id, $post ) use ( $post_id, $cover_image_id ) {
		$thumbnail_post_id = 0;

		if ( $post instanceof WP_Post ) {
			$thumbnail_post_id = (int) $post->ID;
		} else {
			$thumbnail_post_id = absint( $post );
		}

		if ( $thumbnail_post_id === $post_id ) {
			return $cover_image_id;
		}

		return $thumbnail_id;
	};

	add_filter( 'post_thumbnail_id', $thumbnail_filter, 10, 2 );

	$attributes = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] )
		? $parsed_block['attrs']
		: array();

	if ( ! array_key_exists( 'useFirstImageFromPost', $attributes ) ) {
		$attributes['useFirstImageFromPost'] = false;
	}

	$fallback_content = render_block_core_post_featured_image( $attributes, '', $block );

	remove_filter( 'post_thumbnail_id', $thumbnail_filter, 10 );

	if ( '' !== trim( (string) $fallback_content ) && class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$processor = new WP_HTML_Tag_Processor( $fallback_content );

		if ( $processor->next_tag( 'figure' ) ) {
			$processor->add_class( 'wp-block-post-featured-image' );
			$fallback_content = $processor->get_updated_html();
		}
	}

	return '' !== trim( (string) $fallback_content ) ? $fallback_content : $block_content;
}

add_filter( 'render_block_core/post-featured-image', 'ln_bibliographics_render_post_featured_image_fallback', 10, 3 );

