<?php

if ( ! defined( 'LN_BIBLIOGRAPHICS_MIGRATION_STATUS_META_KEY' ) ) {
	define( 'LN_BIBLIOGRAPHICS_MIGRATION_STATUS_META_KEY', 'ln_bibliographics_migration_status' );
}

function ln_register_bibliographics_migration_status_meta() {
	register_post_meta(
		'post',
		LN_BIBLIOGRAPHICS_MIGRATION_STATUS_META_KEY,
		array(
			'single'            => true,
			// Internal status computed from post content/category; do not persist via REST.
			'show_in_rest'      => false,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'auth_callback'     => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'ln_register_bibliographics_migration_status_meta' );

function ln_get_bibliographics_content_type_slug( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return null;
	}

	if ( has_category( 'oogst', $post ) ) {
		return 'oogst';
	}

	if ( has_category( 'recensies', $post ) ) {
		return 'recensie';
	}

	return null;
}

function ln_is_bibliographics_content_type( $post = null ) {
	return null !== ln_get_bibliographics_content_type_slug( $post );
}

function ln_has_bibliographics_block( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	return has_block( 'ln/ln-bibliographics', $post->post_content );
}

function ln_is_legacy_bibliographics_post( $post = null ) {
	return ln_is_bibliographics_content_type( $post ) && ! ln_has_bibliographics_block( $post );
}

function ln_get_bibliographics_migration_status( $post = null ) {
	if ( ! ln_is_bibliographics_content_type( $post ) ) {
		return '';
	}

	if ( ln_has_bibliographics_block( $post ) ) {
		return 'blocktheme';
	}

	return 'legacy';
}

function ln_get_legacy_bibliographics_meta( $post_id, $key ) {
	$value = get_post_meta( $post_id, $key, true );

	if ( is_string( $value ) ) {
		return trim( $value );
	}

	return $value;
}

function ln_format_legacy_bibliographics_price( $raw_price ) {
	$raw_price = trim( (string) $raw_price );

	if ( '' === $raw_price ) {
		return '';
	}

	$numeric = preg_replace( '/[^0-9,\.]/', '', $raw_price );

	if ( '' !== $numeric ) {
		if ( false !== strpos( $numeric, ',' ) && false !== strpos( $numeric, '.' ) ) {
			$numeric = str_replace( '.', '', $numeric );
			$numeric = str_replace( ',', '.', $numeric );
		} elseif ( false !== strpos( $numeric, ',' ) ) {
			$numeric = str_replace( ',', '.', $numeric );
		}

		if ( is_numeric( $numeric ) ) {
			return sprintf( '€ %s', number_format_i18n( (float) $numeric, 2 ) );
		}
	}

	return $raw_price;
}

function ln_get_legacy_bibliographics_template_path( $post = null ) {
	$template_slug = ln_get_bibliographics_content_type_slug( $post );

	if ( ! $template_slug ) {
		return '';
	}

	$template_path = get_stylesheet_directory() . '/legacy-templates/' . $template_slug . '.php';

	if ( ! file_exists( $template_path ) ) {
		return '';
	}

	return $template_path;
}

function ln_render_legacy_bibliographics_template( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || ! ln_is_legacy_bibliographics_post( $post ) ) {
		return '';
	}

	$template_path = ln_get_legacy_bibliographics_template_path( $post );

	if ( ! $template_path ) {
		return '';
	}

	$template_slug = ln_get_bibliographics_content_type_slug( $post );

	ob_start();
	include $template_path;
	return trim( (string) ob_get_clean() );
}

function ln_render_legacy_bibliographics_content( $content ) {
	if ( is_admin() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post || ! ln_is_legacy_bibliographics_post( $post ) ) {
		return $content;
	}

	$legacy_markup = ln_render_legacy_bibliographics_template( $post );

	if ( '' === $legacy_markup ) {
		return $content;
	}

	if ( 'oogst' === ln_get_bibliographics_content_type_slug( $post ) ) {
		return $content . $legacy_markup;
	}

	return $content;
}
add_filter( 'the_content', 'ln_render_legacy_bibliographics_content', 10 );

function ln_add_legacy_bibliographics_post_class( $classes, $class, $post_id ) {
	if ( ln_is_legacy_bibliographics_post( $post_id ) ) {
		$classes[] = 'is-legacy';
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'post_class', 'ln_add_legacy_bibliographics_post_class', 10, 3 );

function ln_add_legacy_class_to_post_content_block( $block_content, $block ) {
	if ( is_admin() || ! is_singular( 'post' ) ) {
		return $block_content;
	}

	if ( empty( $block['blockName'] ) || 'core/post-content' !== $block['blockName'] ) {
		return $block_content;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post || ! ln_is_legacy_bibliographics_post( $post ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$template_slug = ln_get_bibliographics_content_type_slug( $post );

	if ( 'recensie' === $template_slug ) {
		$legacy_markup = ln_render_legacy_bibliographics_template( $post );

		if ( '' !== $legacy_markup ) {
			return '<div class="wp-block-columns ln-legacy-recensie-columns is-layout-flex">'
				. '<div class="wp-block-column ln-legacy-recensie-columns__content" style="flex-basis:66.66%">'
				. $block_content
				. '</div>'
				. '<div class="wp-block-column ln-legacy-recensie-columns__bibliographics" style="flex-basis:33.33%">'
				. $legacy_markup
				. '</div>'
				. '</div>';
		}
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag() ) {
		return $block_content;
	}

	$processor->add_class( 'is-legacy' );

	return $processor->get_updated_html();
}
add_filter( 'render_block', 'ln_add_legacy_class_to_post_content_block', 10, 2 );

function ln_sync_bibliographics_migration_status_meta( $post_id, $post, $update ) {
	unset( $update );

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return;
	}

	$status = ln_get_bibliographics_migration_status( $post );

	if ( '' === $status ) {
		delete_post_meta( $post_id, LN_BIBLIOGRAPHICS_MIGRATION_STATUS_META_KEY );
		return;
	}

	update_post_meta( $post_id, LN_BIBLIOGRAPHICS_MIGRATION_STATUS_META_KEY, $status );
}
add_action( 'save_post', 'ln_sync_bibliographics_migration_status_meta', 20, 3 );

function ln_get_current_acf_post_id() {
	if ( function_exists( 'acf_get_form_data' ) ) {
		$post_id = acf_get_form_data( 'post_id' );

		if ( is_numeric( $post_id ) ) {
			return (int) $post_id;
		}

		if ( is_string( $post_id ) && 0 === strpos( $post_id, 'post_' ) ) {
			return absint( substr( $post_id, 5 ) );
		}
	}

	if ( isset( $_GET['post'] ) ) {
		return absint( $_GET['post'] );
	}

	if ( isset( $_POST['post_ID'] ) ) {
		return absint( $_POST['post_ID'] );
	}

	return 0;
}


function ln_should_hide_legacy_bibliographics_acf_fields( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : ln_get_current_acf_post_id();

	if ( $post_id <= 0 ) {
		return false;
	}

	return 'blocktheme' === ln_get_bibliographics_migration_status( $post_id );
}

function ln_get_acf_field_original_name( $field ) {
	if ( ! is_array( $field ) ) {
		return '';
	}

	if ( ! empty( $field['_name'] ) && is_string( $field['_name'] ) ) {
		return $field['_name'];
	}

	if ( ! empty( $field['name'] ) && is_string( $field['name'] ) && 0 !== strpos( $field['name'], 'acf[' ) ) {
		return $field['name'];
	}

	return '';
}

function ln_should_hide_acf_field_when_converted( $field ) {
	if ( ! ln_should_hide_legacy_bibliographics_acf_fields() ) {
		return false;
	}

	if ( ! is_array( $field ) ) {
		return false;
	}

	if ( 'besproken_boeken' === ln_get_acf_field_original_name( $field ) ) {
		return true;
	}

	return false;
}

function ln_hide_besproken_boeken_acf_field_when_converted( $field ) {
	if ( ! function_exists( 'acf_get_field' ) ) {
		return $field;
	}

	if ( ln_should_hide_acf_field_when_converted( $field ) ) {
		return false;
	}

	return $field;
}
add_filter( 'acf/prepare_field', 'ln_hide_besproken_boeken_acf_field_when_converted' );

function ln_hide_acf_field_groups_when_converted( $field_groups, $acf_post_type ) {
	if ( 'acf-field-group' !== $acf_post_type ) {
		return $field_groups;
	}

	if ( ! is_admin() || ! ln_should_hide_legacy_bibliographics_acf_fields() ) {
		return $field_groups;
	}

	if ( ! is_array( $field_groups ) || empty( $field_groups ) ) {
		return $field_groups;
	}

	return array_values(
		array_filter(
			$field_groups,
			static function ( $field_group ) {
				if ( ! is_array( $field_group ) ) {
					return true;
				}

				$title = isset( $field_group['title'] ) ? sanitize_title( (string) $field_group['title'] ) : '';

				return 'recensie' !== $title;
			}
		)
	);
}
add_filter( 'acf/load_field_groups', 'ln_hide_acf_field_groups_when_converted', 30, 2 );

