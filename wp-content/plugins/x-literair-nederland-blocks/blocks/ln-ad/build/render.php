<?php
/**
 * Server-side render for LN Advertentie block.
 *
 * IMPORTANT: Edit this source file only.
 * The build file at blocks/ln-ad/build/render.php is generated and will be overwritten.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option('xln_options', []);
$show_ads_in_admin = is_array($options) ? ($options['show_ads_in_admin'] ?? 'default') : 'default';
$is_admin_user = is_user_logged_in() && current_user_can( 'manage_options' );
if( 'hide-all' === $show_ads_in_admin && $is_admin_user ) {
	return;
}



$position = isset( $attributes['position'] ) ? sanitize_text_field( $attributes['position'] ) : '';
$fallback_type_attr = isset( $attributes['fallbackType'] ) ? (string) $attributes['fallbackType'] : 'none';
$fallback_contents_attr = isset( $attributes['fallbackContents'] ) ? (string) $attributes['fallbackContents'] : '';

$fallback_type = $fallback_type_attr;
$fallback_contents = $fallback_contents_attr;

$fallback_output = '';
if ( 'html' === $fallback_type ) {
	$fallback_output = do_shortcode( wp_kses_post( $fallback_contents ) );
} elseif ( 'block' === $fallback_type ) {
	if ( ! empty( $fallback_contents ) ) {
		$fallback_output = do_shortcode( do_blocks( $fallback_contents ) );
	} else {
		$inner_content = $content;
		if ( '' === trim( $inner_content ) && $block instanceof WP_Block && ! empty( $block->parsed_block['innerBlocks'] ) ) {
			$inner_content = do_blocks( serialize_blocks( $block->parsed_block['innerBlocks'] ) );
		}
		$fallback_output = $inner_content;
	}
}

$ad_classes=sprintf( "ln-ad ln-padding ln-ad--position-%s", esc_attr( $position ) );

$wrapper = get_block_wrapper_attributes( array( 'class' => $ad_classes ) );
// No, when no ad, then no wrapper $output = '' === $fallback_output ? '' : sprintf( '<div %s>%s</div>', $wrapper, $fallback_output );
$output = '' === $fallback_output ? '' : $fallback_output;

if ( '' === $position ) {
	//
} else {
	$ad_ids = ln_ad_query_ads( $position );
	$ad_id = ln_ad_round_robin_pick( $ad_ids, $position );

	if ( $ad_id ) {
		$image_id = (int) get_post_meta( $ad_id, 'advertentie_afbeelding', true );
		if ( $image_id )  {
			$image_html = wp_get_attachment_image( $image_id, 'full', false, array( 'class' => 'ln-ad__image' ) );
			if ( !empty( $image_html ) ) {
				$click_url = ln_ad_get_click_url( $ad_id );
				if ( !empty( $click_url ) ) {
					ln_ad_track_view( $ad_id );
					$output = sprintf(
						'<div %s><a class="ln-ad__link" href="%s" rel="sponsored" target="_blank">%s</a></div>',
						$wrapper,
						esc_url( $click_url ),
						$image_html
					);
				}
			}
		}
	}
}
if( $is_admin_user ) {
	if( 'default' === $show_ads_in_admin ) {
		echo  $output;
		return;
	}
	if( 'show-all' === $show_ads_in_admin ) {
		echo  '<div class="ln-full-width" style="padding:var(--wp--ln--half--gap-width); background:#ffffff"><div class="add-marker ln-full-width" style="border: 1px solid #ccc; padding: 10px; margin: 0; background: #f9f9f9;">';
		echo '<h5>Advertentie</h5>';
		echo '<ul>';
		echo '<li><strong>Positie:</strong> ' . esc_html( $position ) . '</li>';
		echo '<li><strong>Fallback type:</strong> ' . esc_html( $fallback_type ) . '</li>';
		if( 'block' === $fallback_type ) {
			echo( '<li><strong>Fallback inhoud:</strong></li>' );	
			echo '<div class="fallback-preview" style= "border: 1px dashed #ccc; padding: 10px; margin-top: 5px; background: #fff;">';
			echo do_blocks( $fallback_contents );
			echo '</div>';
		} elseif ( 'html' === $fallback_type ) {
			$fallback_contents_sliced = strlen( $fallback_contents ) > 100 ? substr( $fallback_contents, 0, 100 ) . '...' : $fallback_contents;
			echo '<li><strong>Fallback inhoud:</strong> ' . esc_html( $fallback_contents_sliced ) . '</li>';
		}
	
		echo '</ul>';
		echo '</div></div>';
		return;
	}
}

echo $output;
