<?php
/**
 * Server-side rendering for the `jln/jln-titel` block.
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// gets book-author - book-title chapeau for reviews and oogst, if available 


$context_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;
$fallback_post_id = (int) get_the_ID();
$queried_post_id = (int) get_queried_object_id();

$post_id = $context_post_id > 0 ? $context_post_id : ( $fallback_post_id > 0 ? $fallback_post_id : $queried_post_id );

if ( $post_id <= 0 ) {
	return '';
}

$post_title = get_the_title( $post_id );
$date_format = get_option( 'date_format' );
$post_date = get_the_date( $date_format, $post_id );

$main_category_slug = '';
$post_categories = get_the_category( $post_id );
if ( ! empty( $post_categories ) && ! is_wp_error( $post_categories ) ) {
    $post_categories = array_values(
        array_filter(
            $post_categories,
            static function ( $category ) {
                return isset( $category->parent ) && 0 === (int) $category->parent;
            }
        )
    );

    $main_category = reset( $post_categories );
    if ( $main_category && ! empty( $main_category->slug ) ) {
        $main_category_slug = sanitize_html_class( $main_category->slug );
    }
}
$is_column = $main_category_slug == 'column';


$title_level = isset( $attributes['titleLevel'] ) ? strtolower( (string) $attributes['titleLevel'] ) : 'h1';
$allowed_levels = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
if ( ! in_array( $title_level, $allowed_levels, true ) ) {
    $title_level = 'h3';
}

$show_date = ! isset( $attributes['showDate'] ) || (bool) $attributes['showDate'];
$show_boek_info = ! isset( $attributes['showBoekInfo'] ) || (bool) $attributes['showBoekInfo'];
$show_recensent = ! isset( $attributes['showRecensent'] ) || (bool) $attributes['showRecensent'];

$hs = '';

$chapeau = $show_boek_info ? get_chapeau( $post_id, $main_category_slug ) : false;
$has_date = $show_date && $post_date;

if ( $chapeau || $has_date ) {
    $hs .= '<div class="ln-titel__post-meta">';
    if ( $chapeau ) {
        $hs .= $chapeau;
    }
    if ( $has_date ) {
        $hs .= '<div class="ln-titel__post-date">' . esc_html( $post_date ) . '</div>';
    }

    $hs .= '</div>';
}

if ( $is_column ) {
    $column_show_face = true;
    if( !is_single() ) {
        $column_show_face = false;
    }
    $column_has_face = false;
    $medewerker_foto = null;
    $medewerker_id = get_post_meta( $post_id, 'medewerker_id', true );
    $medewerker_title = '';
    if ( $medewerker_id ) {
        $medewerker_title = get_the_title( $medewerker_id );
        $medewerker_foto = get_the_post_thumbnail( $medewerker_id, 'thumbnail' );
    } else {
        $medewerker_title = get_post_meta( $post_id, 'medewerker', true );
    }
    if( empty( $medewerker_title ) ) {
        $recensent_id = get_post_meta( $post_id, 'auteur_recensie', true );
        if ( $recensent_id ) {
            $medewerker_title = get_the_title( $recensent_id );
        }
    }

    if(  $show_recensent && $medewerker_title  ) {
        $hs .= '<div class="ln-titel__column-recensent"><div class="medewerker">' . esc_html( $medewerker_title ) . '</div>';
        if( !is_archive()) {
            $hs .= '<div class="intro">Column</div>';
        }
        $hs .= '</div>';
    } else {
        $hs .= '<div class="ln-titel__column-label">Column</div>';
    }

    if( $show_recensent && $medewerker_foto && ! is_wp_error( $medewerker_foto ) && $column_show_face ) {
        $column_has_face = true;
    }

} 
if ( $is_column && $column_has_face ) {
    $hs .= '<div class="ln-titel__column-with-face">';
} 

$hs .= '<' . $title_level . ' class="ln-titel__post-title"><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $post_title ) . '</a></' . $title_level . '>';

if( $is_column && $column_has_face ) {
    $hs .= '<div class="columnist-face">' . $medewerker_foto . '</div>';
    $hs .= '</div>';
}   

if ( !$is_column && $show_recensent  ) {
    $door_regel = get_door_regel( $post_id, $main_category_slug );
    if ( !empty ( $door_regel ) ) {
        $hs .= '<div class="ln-titel__ln_auteur">' . $door_regel . '</div>';
    }
}



$wrapper_class = 'ln-titel';
if ( '' !== $main_category_slug ) {
    $wrapper_class .= ' category-' . $main_category_slug;
}

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );
echo '<div ' . $wrapper_attributes . '>';
echo $hs;
echo '</div>';

return;