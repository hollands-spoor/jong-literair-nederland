<?php
/**
 * Server-side rendering for the `jln/jln-current-year` block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$prefix = isset( $attributes['prefix'] ) ? wp_strip_all_tags( (string) $attributes['prefix'] ) : '';
$suffix = isset( $attributes['suffix'] ) ? wp_strip_all_tags( (string) $attributes['suffix'] ) : '';
$site_name = isset( $attributes['siteName'] ) ? wp_strip_all_tags( (string) $attributes['siteName'] ) : '';
$current_year = wp_date( 'Y' );

if ( '' === trim( $site_name ) ) {
	$site_name = get_bloginfo( 'name' );
}

$parts = array_filter(
	array( $prefix, $current_year, $site_name, $suffix ),
	static function ( $part ) {
		return '' !== trim( (string) $part );
	}
);

$content = implode( ' ', array_map( 'trim', $parts ) );

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'ln-current-year' ) );

echo '<span ' . $wrapper_attributes . '>' . esc_html( $content ) . '</span>';

return;
