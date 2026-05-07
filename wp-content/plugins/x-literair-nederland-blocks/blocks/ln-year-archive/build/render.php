<?php
/**
 * Server-side render for LN Year Archive block.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = isset( $attributes['categories'] ) && is_array( $attributes['categories'] )
	? $attributes['categories']
	: array( 'recensies', 'interviews', 'oogst' );

$categories = array_values(
	array_unique(
		array_filter(
			array_map( 'sanitize_title', $categories )
		)
	)
);

if ( empty( $categories ) ) {
	return;
}

$default_mode = isset( $attributes['defaultMode'] ) && in_array( $attributes['defaultMode'], array( 'image', 'text' ), true )
	? $attributes['defaultMode']
	: 'image';

$default_year = isset( $attributes['defaultYear'] ) ? (int) $attributes['defaultYear'] : 0;
$query_var_year = isset( $attributes['queryVarYear'] ) ? sanitize_key( (string) $attributes['queryVarYear'] ) : 'ln_year';
$query_var_mode = isset( $attributes['queryVarMode'] ) ? sanitize_key( (string) $attributes['queryVarMode'] ) : 'ln_mode';

if ( '' === $query_var_year ) {
	$query_var_year = 'ln_year';
}
if ( '' === $query_var_mode ) {
	$query_var_mode = 'ln_mode';
}

$raw_mode = isset( $_GET[ $query_var_mode ] ) ? sanitize_key( wp_unslash( (string) $_GET[ $query_var_mode ] ) ) : '';
$effective_mode = in_array( $raw_mode, array( 'image', 'text' ), true ) ? $raw_mode : $default_mode;

$current_year = (int) wp_date( 'Y' );

$term_ids = array();
foreach ( $categories as $slug ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$term_ids[] = (int) $term->term_id;
	}
}

if ( empty( $term_ids ) ) {
	return;
}

$year_seed = new WP_Query(
	array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'category__in'           => $term_ids,
		'posts_per_page'         => 1,
		'orderby'                => 'date',
		'order'                  => 'ASC',
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
	)
);

$min_year = $current_year;
if ( ! empty( $year_seed->posts ) ) {
	$seed_id = (int) $year_seed->posts[0];
	$seed_year = (int) get_the_date( 'Y', $seed_id );
	if ( $seed_year > 0 ) {
		$min_year = $seed_year;
	}
}

if ( $min_year > $current_year ) {
	$min_year = $current_year;
}

$years = range( $current_year, $min_year );

$raw_year = isset( $_GET[ $query_var_year ] ) ? (int) wp_unslash( (string) $_GET[ $query_var_year ] ) : 0;
$effective_year = $raw_year > 0 ? $raw_year : $default_year;
if ( $effective_year <= 0 ) {
	$effective_year = $current_year;
}
if ( $effective_year < $min_year ) {
	$effective_year = $min_year;
}
if ( $effective_year > $current_year ) {
	$effective_year = $current_year;
}

$posts_per_page_text = isset( $attributes['postsPerPageTextMode'] ) ? (int) $attributes['postsPerPageTextMode'] : 0;
$enable_cover_fallback = ! empty( $attributes['enableCoverFallback'] );

$query_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'category__in'   => $term_ids,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'posts_per_page' => ( 'text' === $effective_mode && $posts_per_page_text > 0 ) ? $posts_per_page_text : -1,
	'date_query'     => array(
		array(
			'year' => $effective_year,
		),
	),
);

$posts = get_posts( $query_args );

$get_book_author = static function ( int $post_id ): string {
	$book_author = (string) get_post_meta( $post_id, 'besproken_boeken_0_auteur_boek', true );
	if ( '' === $book_author ) {
		$book_author = (string) get_post_meta( $post_id, 'auteur_boek', true );
	}
	return $book_author;
};

$get_book_title = static function ( int $post_id ): string {
	$book_title = (string) get_post_meta( $post_id, 'besproken_boeken_0_boektitel', true );
	if ( '' === $book_title ) {
		$book_title = (string) get_post_meta( $post_id, 'boektitel', true );
	}
	return $book_title;
};

$get_reviewer = static function ( int $post_id ): array {
	$reviewer_id = (int) get_post_meta( $post_id, 'auteur_recensie', true );
	if ( $reviewer_id > 0 ) {
		return array(
			'name' => get_the_title( $reviewer_id ),
			'url'  => get_permalink( $reviewer_id ),
		);
	}

	$staff_id = (int) get_post_meta( $post_id, 'medewerker_id', true );
	if ( $staff_id > 0 ) {
		return array(
			'name' => get_the_title( $staff_id ),
			'url'  => get_permalink( $staff_id ),
		);
	}

	$fallback = (string) get_post_meta( $post_id, 'medewerker', true );

	return array(
		'name' => $fallback,
		'url'  => '',
	);
};

$resolve_fallback_cover = static function ( int $post_id ): array {
	$id_keys = array(
		'besproken_boeken_0_omslag_id',
		'besproken_boeken_0_afbeelding',
		'omslag_id',
		'afbeelding',
		'cover_image_id',
	);

	foreach ( $id_keys as $meta_key ) {
		$attachment_id = (int) get_post_meta( $post_id, $meta_key, true );
		if ( $attachment_id > 0 ) {
			$image = wp_get_attachment_image_src( $attachment_id, 'medium_large' );
			if ( ! empty( $image[0] ) ) {
				return array(
					'type'          => 'attachment',
					'attachment_id' => $attachment_id,
					'url'           => $image[0],
				);
			}
		}
	}

	$url_keys = array(
		'besproken_boeken_0_afbeelding_url',
		'besproken_boeken_0_omslag',
		'omslag_url',
		'afbeelding_url',
	);

	foreach ( $url_keys as $meta_key ) {
		$image_url = (string) get_post_meta( $post_id, $meta_key, true );
		if ( '' !== $image_url ) {
			$image_url = esc_url_raw( $image_url );
			if ( '' !== $image_url ) {
				return array(
					'type'          => 'url',
					'attachment_id' => 0,
					'url'           => $image_url,
				);
			}
		}
	}

	return array(
		'type'          => '',
		'attachment_id' => 0,
		'url'           => '',
	);
};

$entries = array();

foreach ( $posts as $post ) {
	$post_id = (int) $post->ID;
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	$cover = array(
		'type'          => '',
		'attachment_id' => 0,
		'url'           => '',
	);

	if ( $thumb_id > 0 ) {
		$image = wp_get_attachment_image_src( $thumb_id, 'medium_large' );
		$cover = array(
			'type'          => 'featured',
			'attachment_id' => $thumb_id,
			'url'           => ! empty( $image[0] ) ? $image[0] : '',
		);
	} elseif ( $enable_cover_fallback ) {
		$cover = $resolve_fallback_cover( $post_id );
	}

	if ( 'image' === $effective_mode && '' === $cover['url'] ) {
		continue;
	}

	$book_author = $get_book_author( $post_id );
	$book_title = $get_book_title( $post_id );
	$reviewer = $get_reviewer( $post_id );

	$entries[] = array(
		'id'          => $post_id,
		'permalink'   => get_permalink( $post_id ),
		'title'       => get_the_title( $post_id ),
		'date_human'  => get_the_date( get_option( 'date_format' ), $post_id ),
		'date_iso'    => get_the_date( 'c', $post_id ),
		'book_author' => $book_author,
		'book_title'  => $book_title,
		'reviewer'    => $reviewer,
		'cover'       => $cover,
	);
}

$build_url = static function ( array $updates ) use ( $query_var_year, $query_var_mode ): string {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path = strtok( $request_uri, '?' );
	if ( false === $path ) {
		$path = '/';
	}
	$base_url = home_url( $path );

	$query_args = array();
	if ( isset( $_GET[ $query_var_year ] ) ) {
		$query_args[ $query_var_year ] = sanitize_text_field( wp_unslash( (string) $_GET[ $query_var_year ] ) );
	}
	if ( isset( $_GET[ $query_var_mode ] ) ) {
		$query_args[ $query_var_mode ] = sanitize_text_field( wp_unslash( (string) $_GET[ $query_var_mode ] ) );
	}

	foreach ( $updates as $k => $v ) {
		$query_args[ $k ] = $v;
	}

	return add_query_arg( $query_args, $base_url );
};

$show_field_book_author = ! isset( $attributes['showFieldBookAuthor'] ) || ! empty( $attributes['showFieldBookAuthor'] );
$show_field_book_title  = ! isset( $attributes['showFieldBookTitle'] ) || ! empty( $attributes['showFieldBookTitle'] );
$show_field_reviewer    = ! isset( $attributes['showFieldReviewer'] ) || ! empty( $attributes['showFieldReviewer'] );
$show_field_date        = ! isset( $attributes['showFieldDate'] ) || ! empty( $attributes['showFieldDate'] );

$desktop_cols = isset( $attributes['imageColumnsDesktop'] ) ? max( 1, min( 12, (int) $attributes['imageColumnsDesktop'] ) ) : 8;
$tablet_cols = isset( $attributes['imageColumnsTablet'] ) ? max( 1, min( 8, (int) $attributes['imageColumnsTablet'] ) ) : 4;
$mobile_cols = isset( $attributes['imageColumnsMobile'] ) ? max( 1, min( 4, (int) $attributes['imageColumnsMobile'] ) ) : 2;

$classes = array( 'ln-year-archive', 'is-mode-' . $effective_mode );
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $classes ),
		'style' => sprintf(
			'--xln-archive-cols-desktop:%1$d;--xln-archive-cols-tablet:%2$d;--xln-archive-cols-mobile:%3$d;',
			$desktop_cols,
			$tablet_cols,
			$mobile_cols
		),
		'data-mode' => $effective_mode,
		'data-year' => (string) $effective_year,
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes is escaped. ?> >
	<div class="ln-year-archive__controls">
		<nav aria-label="<?php echo esc_attr__( 'Archive mode', 'x-literair-nederland-blocks' ); ?>" class="ln-year-archive__modes">
			<a class="ln-year-archive__mode-link <?php echo 'image' === $effective_mode ? 'is-active' : ''; ?>" href="<?php echo esc_url( $build_url( array( $query_var_mode => 'image', $query_var_year => $effective_year ) ) ); ?>"><?php echo esc_html__( 'Image', 'x-literair-nederland-blocks' ); ?></a>
			<a class="ln-year-archive__mode-link <?php echo 'text' === $effective_mode ? 'is-active' : ''; ?>" href="<?php echo esc_url( $build_url( array( $query_var_mode => 'text', $query_var_year => $effective_year ) ) ); ?>"><?php echo esc_html__( 'Text', 'x-literair-nederland-blocks' ); ?></a>
		</nav>

		<ul class="ln-year-archive__years" aria-label="<?php echo esc_attr__( 'Archive years', 'x-literair-nederland-blocks' ); ?>">
			<?php foreach ( $years as $year_item ) : ?>
				<li>
					<a class="ln-year-archive__year-link" href="<?php echo esc_url( $build_url( array( $query_var_year => $year_item, $query_var_mode => $effective_mode ) ) ); ?>" <?php echo (int) $year_item === (int) $effective_year ? 'aria-current="page"' : ''; ?>><?php echo esc_html( (string) $year_item ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php if ( empty( $entries ) ) : ?>
		<p class="ln-year-archive__empty">
			<?php 
			/* translators: %d: Year number, %s: Mode (image or text). */
			echo esc_html( sprintf( __( 'No results in %1$d (%2$s).', 'x-literair-nederland-blocks' ), $effective_year, 'image' === $effective_mode ? __( 'image mode', 'x-literair-nederland-blocks' ) : __( 'text mode', 'x-literair-nederland-blocks' ) ) ); 
			?>
		</p>
	<?php elseif ( 'image' === $effective_mode ) : ?>
		<ul class="ln-year-archive__grid">
			<?php foreach ( $entries as $entry ) : ?>
				<li class="ln-year-archive__card">
					<a
						class="ln-year-archive__card-link"
						href="<?php echo esc_url( $entry['permalink'] ); ?>"
						data-ln-post-id="<?php echo esc_attr( (string) $entry['id'] ); ?>"
						data-ln-post-title="<?php echo esc_attr( $entry['title'] ); ?>"
						data-ln-post-date="<?php echo esc_attr( $entry['date_human'] ); ?>"
						data-ln-book-author="<?php echo esc_attr( $entry['book_author'] ); ?>"
						data-ln-book-title="<?php echo esc_attr( $entry['book_title'] ); ?>"
						data-ln-reviewer="<?php echo esc_attr( $entry['reviewer']['name'] ); ?>"
					>
						<?php
						if ( 'url' === $entry['cover']['type'] ) {
							echo '<img class="ln-year-archive__cover" loading="lazy" alt="' . esc_attr( $entry['title'] ) . '" src="' . esc_url( $entry['cover']['url'] ) . '" />';
						} else {
							echo wp_get_attachment_image( $entry['cover']['attachment_id'], 'medium_large', false, array( 'class' => 'ln-year-archive__cover', 'loading' => 'lazy', 'alt' => $entry['title'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
						<span class="screen-reader-text"><?php echo esc_html( $entry['title'] . ' - ' . $entry['date_human'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div id="archive-tool-tip" class="ln-year-archive__tooltip" role="tooltip" aria-hidden="true">
			<h3 class="ln-year-archive__tooltip-title" data-ln-tooltip-title></h3>
			<p class="ln-year-archive__tooltip-meta" data-ln-tooltip-book-title></p>
			<p class="ln-year-archive__tooltip-meta" data-ln-tooltip-author></p>
			<p class="ln-year-archive__tooltip-meta" data-ln-tooltip-reviewer></p>
			<p class="ln-year-archive__tooltip-meta" data-ln-tooltip-date></p>
		</div>
	<?php else : ?>
		<ul class="ln-year-archive__text-list">
			<?php foreach ( $entries as $entry ) : ?>
				<li class="ln-year-archive__text-item">
					<h3 class="ln-year-archive__text-title"><a href="<?php echo esc_url( $entry['permalink'] ); ?>"><?php echo esc_html( $entry['title'] ); ?></a></h3>
					<?php
					$meta_chunks = array();
					if ( $show_field_book_author && '' !== $entry['book_author'] ) {
						$meta_chunks[] = $entry['book_author'];
					}
					if ( $show_field_book_title && '' !== $entry['book_title'] ) {
						$meta_chunks[] = $entry['book_title'];
					}
					if ( $show_field_reviewer && '' !== $entry['reviewer']['name'] ) {
						$meta_chunks[] = $entry['reviewer']['name'];
					}
					if ( $show_field_date && '' !== $entry['date_human'] ) {
						$meta_chunks[] = $entry['date_human'];
					}
					?>
					<?php if ( ! empty( $meta_chunks ) ) : ?>
						<p class="ln-year-archive__meta"><?php echo esc_html( implode( ' | ', $meta_chunks ) ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<ul class="ln-year-archive__years" aria-label="<?php echo esc_attr__( 'Archive years at bottom', 'x-literair-nederland-blocks' ); ?>">
		<?php foreach ( $years as $year_item ) : ?>
			<li>
				<a class="ln-year-archive__year-link" href="<?php echo esc_url( $build_url( array( $query_var_year => $year_item, $query_var_mode => $effective_mode ) ) ); ?>" <?php echo (int) $year_item === (int) $effective_year ? 'aria-current="page"' : ''; ?>><?php echo esc_html( (string) $year_item ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	
</div>
