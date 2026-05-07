<?php
/**
 * Advertentie CPT + helpers for LN Ad block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const LN_AD_CPT = 'advertentie';
const LN_AD_POSITION_META_KEY = 'positie_advertentie';
const LN_AD_CLICK_QUERY_VAR = 'ln_ad_click';
const LN_AD_CLICK_BASE = 'ln-ad-click';
const LN_AD_ROUND_ROBIN_TRANSIENT_PREFIX = 'ln_ad_rr_';
const LN_AD_UNIQUE_COOKIE = 'ln_ad';


/**
 * ad positions can be populated by the scanning of templates for ln/ln-ad blocks, and getting the position attribute of those blocks. For now, we hardcode them.
 */

$ln_ad_positions = array();

function ln_ad_init_positions() {
	global $ln_ad_positions;

	$ln_ad_positions = array(
		'header' => array(
			'label' => __( 'Header', 'x-literair-nederland-blocks' ),
			'description' => __( 'Top of the page, next to the website title. This position is very prominent and is shown on all pages.', 'x-literair-nederland-blocks' ),
			'dimensions' => array( 'width' => 600, 'height' => 200 ),
			'template-part' => 'header',
		),
		'sidebar-top' => array(
			'label' => __( 'Sidebar (top)', 'x-literair-nederland-blocks' ),
			'description' => __( 'Top of the sidebar. This position is clearly visible but less prominent than the header, and is shown on the front page.', 'x-literair-nederland-blocks' ),
			'dimensions' => array( 'width' => 600, 'height' => 600 ),
			'page-template' => 'ln-voorpagina',
		),
		'content_front_page_top' => array(
			'label' => __( 'Content (top)', 'x-literair-nederland-blocks' ),
			'description' => __( 'Top of the front page content area. This position is clearly visible and is only shown on the front page.', 'x-literair-nederland-blocks' ),
			'dimensions' => array( 'width' => 1500, 'height' => 600 ),
			'page-template' => 'ln-voorpagina',
		),
		'content_front_page_middle' => array(
			'label' => __( 'Content (middle)', 'x-literair-nederland-blocks' ),
			'description' => __( 'Middle of the front page content area. This position is only shown on the front page.', 'x-literair-nederland-blocks' ),
			'dimensions' => array( 'width' => 1500, 'height' => 600 ),
			'page-template' => 'ln-voorpagina',
		),
	);
}
add_action( 'init', 'ln_ad_init_positions', 0 );



function scan_ad_positions() {
	/**
	 * TODO:
	 * Iterate through all templates, find all ln/ln-ad blocks, and collect the unique positions by getting the position attribute of all the ln/ln-ad blocks in the template. 
	 * The list of positions is stored in an option of x-literair-nederland-blocks, and is used to populate the dropdown in the block editor and the postmeta that goes with the advertentie cpt.
	 * 
	 *  
	 */
}


/** 
 * Fill the ACF with key 'positie_advertentie' and field_key 'field_55a38c773f5c3' with the positions found in the scan_ad_positions function, so they can be selected when editing an advertentie.
 */
function populate_ad_positions_acf() {
	global $ln_ad_positions;
	$choices = array();
	if ( is_array( $ln_ad_positions ) ) {
		foreach ( $ln_ad_positions as $key => $value ) {
			$label = is_array( $value ) && isset( $value['label'] ) ? $value['label'] : $key;
			$choices[ $key ] = $label;
		}
	}

	return $choices;
}

add_filter( 'acf/load_field/key=field_55a38c773f5c3', function( $field ) {
	$field['choices'] = populate_ad_positions_acf();
	return $field;
} );




function ln_ad_register_cpt() {
	$labels = array(
		'name'               => __( 'Ads', 'x-literair-nederland-blocks' ),
		'singular_name'      => __( 'Ad', 'x-literair-nederland-blocks' ),
		'add_new'            => __( 'Add New Ad', 'x-literair-nederland-blocks' ),
		'add_new_item'       => __( 'Add New Ad', 'x-literair-nederland-blocks' ),
		'edit_item'          => __( 'Edit Ad', 'x-literair-nederland-blocks' ),
		'new_item'           => __( 'New Ad', 'x-literair-nederland-blocks' ),
		'view_item'          => __( 'View Ad', 'x-literair-nederland-blocks' ),
		'search_items'       => __( 'Search Ads', 'x-literair-nederland-blocks' ),
		'not_found'          => __( 'No ads found', 'x-literair-nederland-blocks' ),
		'not_found_in_trash' => __( 'No ads found in Trash', 'x-literair-nederland-blocks' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-megaphone',
		'has_archive'        => false,
		'rewrite'            => array( 'slug' => LN_AD_CPT ),
		'supports'           => array( 'title' ),
		'capability_type'    => 'post',
	);

	register_post_type( LN_AD_CPT, $args );
}
add_action( 'init', 'ln_ad_register_cpt' );

function ln_ad_register_meta() {
	register_post_meta(
		LN_AD_CPT,
		LN_AD_POSITION_META_KEY,
		array(
			'single'            => true,
			'show_in_rest'      => true,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'ln_ad_register_meta' );

function ln_ad_register_rewrite() {
	add_rewrite_tag( '%' . LN_AD_CLICK_QUERY_VAR . '%', '([0-9]+)' );
	add_rewrite_rule( '^' . LN_AD_CLICK_BASE . '/([0-9]+)/?$', 'index.php?' . LN_AD_CLICK_QUERY_VAR . '=$matches[1]', 'top' );
}
add_action( 'init', 'ln_ad_register_rewrite' );

function ln_ad_handle_click_redirect() {
	$ad_id = get_query_var( LN_AD_CLICK_QUERY_VAR );
	if ( empty( $ad_id ) ) {
		return;
	}

	$ad_id = (int) $ad_id;
	if ( $ad_id <= 0 || LN_AD_CPT !== get_post_type( $ad_id ) ) {
		wp_redirect( home_url( '/' ) );
		exit;
	}

	$target_url = get_post_meta( $ad_id, 'url_van_adverteerder', true );
	if ( ! empty( $target_url ) ) {
		$target_url = esc_url_raw( $target_url );
	}

	ln_ad_increment_clicks( $ad_id );

	wp_redirect( ! empty( $target_url ) ? $target_url : home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'ln_ad_handle_click_redirect' );

function ln_ad_activation() {
	ln_ad_register_rewrite();
	flush_rewrite_rules();
}

function ln_ad_deactivation() {
	flush_rewrite_rules();
}

function ln_ad_increment_clicks( $ad_id ) {
	$count = (int) get_post_meta( $ad_id, 'aantal_clicks', true );
	$count++;
	update_post_meta( $ad_id, 'aantal_clicks', $count );
}

function ln_ad_increment_views( $ad_id ) {
	$count = (int) get_post_meta( $ad_id, 'aantal_views', true );
	$count++;
	update_post_meta( $ad_id, 'aantal_views', $count );
}

function ln_ad_increment_unique_views( $ad_id ) {
	$count = (int) get_post_meta( $ad_id, 'aantal_unique_views', true );
	$count++;
	update_post_meta( $ad_id, 'aantal_unique_views', $count );
}

function ln_ad_track_view( $ad_id ) {
	if ( is_admin() ) {
		return;
	}

	ln_ad_increment_views( $ad_id );

	$seen_ids = ln_ad_get_seen_ids();
	if ( in_array( $ad_id, $seen_ids, true ) ) {
		return;
	}

	$seen_ids[] = $ad_id;
	ln_ad_set_seen_ids( $seen_ids );
	ln_ad_increment_unique_views( $ad_id );
}

function ln_ad_get_seen_ids() {
	if ( empty( $_COOKIE[ LN_AD_UNIQUE_COOKIE ] ) ) {
		return array();
	}

	$raw = wp_unslash( $_COOKIE[ LN_AD_UNIQUE_COOKIE ] );
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return array();
	}

	$ids = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	return array_values( array_unique( $ids ) );
}

function ln_ad_set_seen_ids( array $ids ) {
	$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
	$value = implode( ',', $ids );

	if ( ! headers_sent() ) {
		setcookie( LN_AD_UNIQUE_COOKIE, $value, 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	}
	$_COOKIE[ LN_AD_UNIQUE_COOKIE ] = $value;
}

function ln_ad_get_click_url( $ad_id ) {
	$base = home_url( '/' . trim( LN_AD_CLICK_BASE, '/' ) . '/' . (int) $ad_id . '/' );
	return user_trailingslashit( $base );
}

function ln_ad_query_ads( $position ) {
	$today = wp_date( 'Ymd' );
	$position_meta_key = apply_filters( 'ln_ad_position_meta_key', LN_AD_POSITION_META_KEY );

	$meta_query = array(
		'relation' => 'AND',
		array(
			'key'     => $position_meta_key,
			'value'   => $position,
			'compare' => '=',
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => 'begindatum_advertentie',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'begindatum_advertentie',
				'value'   => $today,
				'compare' => '<=',
				'type'    => 'NUMERIC',
			),
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => 'einddatum_advertentie',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'einddatum_advertentie',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		),
	);

	$query_args = array(
		'post_type'      => LN_AD_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => $meta_query,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	);

	$ids = get_posts( $query_args );
	return array_map( 'intval', $ids );
}

function ln_ad_round_robin_pick( array $ids, $position ) {
	if ( empty( $ids ) ) {
		return 0;
	}

	$key = LN_AD_ROUND_ROBIN_TRANSIENT_PREFIX . md5( $position );
	$idx = (int) get_transient( $key );
	$count = count( $ids );

	$selected = $ids[ $idx % $count ];
	$next     = ( $idx + 1 ) % $count;

	$ttl = (int) apply_filters( 'ln_ad_round_robin_ttl', DAY_IN_SECONDS, $position );
	set_transient( $key, $next, $ttl );

	return (int) $selected;
}

function ln_ad_find_first_block( array $blocks ) {
	foreach ( $blocks as $block ) {
		if ( empty( $block['blockName'] ) ) {
			continue;
		}

		if ( 'ln/ln-ad' === $block['blockName'] ) {
			return $block;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = ln_ad_find_first_block( $block['innerBlocks'] );
			if ( $found ) {
				return $found;
			}
		}
	}

	return null;
}

// Extra Columns in Advertentie CPT list table


function ln_ad_admin_columns( $columns ) {
	if ( ! is_array( $columns ) ) {
		return $columns;
	}

	$new_columns = array();
	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;
		if ( 'title' === $key ) {
			$new_columns['ln_ad_dates'] = __( 'Period', 'x-literair-nederland-blocks' );
			$new_columns['ln_ad_views'] = __( 'Views (unique)', 'x-literair-nederland-blocks' );
			$new_columns['ln_ad_clicks'] = __( 'Clicks', 'x-literair-nederland-blocks' );
		}
	}

	return $new_columns;
}


add_filter( 'manage_' . LN_AD_CPT . '_posts_columns', 'ln_ad_admin_columns' );

function ln_ad_admin_columns_content( $column, $post_id ) {
	if ( LN_AD_CPT !== get_post_type( $post_id ) ) {
		return;
	}

	if ( 'ln_ad_dates' === $column ) {
		$begin_raw = get_post_meta( $post_id, 'begindatum_advertentie', true );
		$end_raw = get_post_meta( $post_id, 'einddatum_advertentie', true );

		$begin_raw = is_string( $begin_raw ) ? trim( $begin_raw ) : '';
		$end_raw = is_string( $end_raw ) ? trim( $end_raw ) : '';

		$begin_dt = '' !== $begin_raw ? DateTimeImmutable::createFromFormat( 'Ymd', $begin_raw, wp_timezone() ) : false;
		$end_dt = '' !== $end_raw ? DateTimeImmutable::createFromFormat( 'Ymd', $end_raw, wp_timezone() ) : false;

		$begin = $begin_dt ? $begin_dt->format( 'd-m-Y' ) : '';
		$end = $end_dt ? $end_dt->format( 'd-m-Y' ) : '';

		$begin = '' !== $begin ? $begin : '-';
		$end = '' !== $end ? $end : '-';

		echo esc_html( $begin . ' - ' . $end );
		return;
	}

	if ( 'ln_ad_views' === $column ) {
		$views = (int) get_post_meta( $post_id, 'aantal_views', true );
		$unique = (int) get_post_meta( $post_id, 'aantal_unique_views', true );
		echo esc_html( $views . ' (' . $unique . ')' );
		return;
	}

	if ( 'ln_ad_clicks' === $column ) {
		$clicks = (int) get_post_meta( $post_id, 'aantal_clicks', true );
		echo esc_html( (string) $clicks );
		return;
	}
}
add_action( 'manage_' . LN_AD_CPT . '_posts_custom_column', 'ln_ad_admin_columns_content', 10, 2 );


