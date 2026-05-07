<?php
/**
 * Server-side render for LN Jubileumpost block.
 *
 * Finds the first published post that matches the configured anniversary year
 * priorities from xln_options['year-preference'] (comma-separated), within a
 * ±7-day window around each target date. If no qualifying post is found the
 * block outputs nothing.
 *
 * Uses a static closure so the helper can be defined safely when multiple
 * instances of this block appear on the same page.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (unused – fully dynamic).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the published post closest to $target within a ±7-day window,
 * or null when no post qualifies.
 *
 * Using a static closure avoids re-declaration errors when the block is
 * rendered more than once per page request.
 *
 * @param int[] $term_ids  Array of category term IDs to search in.
 * @param int   $target    Unix timestamp (UTC) of the anniversary date.
 * @return WP_Post|null
 */
$find_closest = static function ( array $term_ids, int $target ) {
	$window = 7 * DAY_IN_SECONDS;

	$query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'category__in'   => $term_ids,
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'date_query'     => array(
				array(
					'column'    => 'post_date_gmt',
					'after'     => gmdate( 'Y-m-d H:i:s', $target ),
					'inclusive' => true,
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return null;
	}

	$post  = $query->posts[0];
	$delta = (int) strtotime( $post->post_date_gmt ) - $target;

	return $delta <= $window ? $post : null;
};

// ---------------------------------------------------------------------------
// Resolve category slugs → term IDs.
// ---------------------------------------------------------------------------

$category_slugs = isset( $attributes['categories'] ) && is_array( $attributes['categories'] )
	? $attributes['categories']
	: array( 'nieuws', 'recensies', 'oogst' );

$term_ids = array();
foreach ( $category_slugs as $slug ) {
	$term = get_term_by( 'slug', sanitize_key( $slug ), 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$term_ids[] = $term->term_id;
	}
}

if ( empty( $term_ids ) ) {
	return;
}

// ---------------------------------------------------------------------------
// Try anniversary years in configured order.
// ---------------------------------------------------------------------------

$now        = time();
$post       = null;
$years_ago  = null;
$year_order = array( 25, 20, 15, 10, 5 );

$xln_options = get_option( 'xln_options', array() );
if ( is_array( $xln_options ) && ! empty( $xln_options['year-preference'] ) ) {
	$parsed_years = array();
	$raw_years    = explode( ',', (string) $xln_options['year-preference'] );

	foreach ( $raw_years as $raw_year ) {
		$years = (int) trim( $raw_year );

		if ( $years <= 0 ) {
			continue;
		}

		if ( ! in_array( $years, $parsed_years, true ) ) {
			$parsed_years[] = $years;
		}
	}

	if ( ! empty( $parsed_years ) ) {
		$year_order = $parsed_years;
	}
}

foreach ( $year_order as $years ) {
	$target = strtotime( "-{$years} years", $now );
	$found  = $find_closest( $term_ids, $target );
	if ( $found ) {
		$post      = $found;
		$years_ago = $years;
		break;
	}
}

if ( ! $post ) {
	return;
}

// ---------------------------------------------------------------------------
// Render.
// ---------------------------------------------------------------------------

$title     = get_the_title( $post );
$permalink = get_permalink( $post );
$excerpt   = get_the_excerpt( $post );
$post_date = mysql2date( get_option( 'date_format' ), $post->post_date );
$thumb_id  = (int) get_post_thumbnail_id( $post );

$thumb_html = '';
if ( $thumb_id ) {
	$thumb_html = wp_get_attachment_image(
		$thumb_id,
		'medium_large',
		false,
		array(
			'class'   => 'ln-anniversary__image',
			'loading' => 'lazy',
			'alt'     => wp_strip_all_tags( $title ),
		)
	);
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'ln-anniversary' ) );
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() is safe. ?>>

	<div class="ln-anniversary__body">	
		<p class="ln-anniversary__years-ago"><?php 
			/* translators: %d: Number of years ago. */	
			echo esc_html( sprintf( __( 'Literair Nederland %d years ago', 'x-literair-nederland-blocks' ), $years_ago ) ); ?></p>
		<time class="ln-anniversary__date" datetime="<?php echo esc_attr( mysql2date( 'Y-m-d', $post->post_date ) ); ?>"><?php echo esc_html( $post_date ); ?></time>
		<h3 class="ln-anniversary__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>
		<?php if ( $excerpt ) : ?>
			<p class="ln-anniversary__excerpt"><?php echo wp_kses_post( $excerpt ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $thumb_html ) : ?>
		<a class="ln-anniversary__image-link" href="<?php echo esc_url( $permalink ); ?>">
			<?php echo $thumb_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() is safe. ?>
		</a>
	<?php endif; ?>
</div>
