<?php
/**
 * LN Query block variation support logic.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize LN Query runtime globals.
 */
function ln_query_init_state() {
	$GLOBALS['ln_query_posts_shown'] = array();
	$GLOBALS['ln_query_ctx']        = array();
	$GLOBALS['ln_query_result_counts'] = array();
}

ln_query_init_state();

/**
 * Reset stored post IDs and context at the start of every request.
 */
function ln_query_reset_state() {
	ln_query_init_state();

	if ( is_singular() ) {
		$current_post_id = (int) get_queried_object_id();

		if ( $current_post_id > 0 ) {
			$GLOBALS['ln_query_posts_shown'][] = $current_post_id;
		}
	}
}
add_action( 'wp', 'ln_query_reset_state', 1 );

/**
 * Capture parent core/query attributes before rendering starts.
 */
function ln_query_capture_core_query_context( $pre_render, $block ) {
	global $ln_query_ctx;

	if ( isset( $block['blockName'] ) && 'core/query' === $block['blockName'] ) {
		$attrs = $block['attrs'] ?? array();
		$qid   = $attrs['queryId'] ?? null;

		if ( null !== $qid ) {
			$ln_query_ctx[ $qid ] = array(
				'namespace' => $attrs['namespace'] ?? null,
				'layoutPos' => $attrs['query']['layoutPos'] ?? null,
				'queryType' => $attrs['query']['queryType'] ?? null,
			);
		}
	}

	return $pre_render;
}
add_filter( 'pre_render_block', 'ln_query_capture_core_query_context', 9, 2 );

/**
 * Clear captured context when the query block finishes rendering.
 */
function ln_query_release_core_query_context( $content, $block ) {
	global $ln_query_ctx;

	if ( isset( $block['blockName'] ) && 'core/query' === $block['blockName'] ) {
		$qid = $block['attrs']['queryId'] ?? null;
		if ( null !== $qid ) {
			unset( $ln_query_ctx[ $qid ] );
		}
	}

	return $content;
}
add_filter( 'render_block', 'ln_query_release_core_query_context', 9, 2 );

/**
 * Remember which posts have been rendered via LN Query blocks.
 */
function ln_query_remember_posts( $posts, $query ) {
	global $ln_query_posts_shown, $ln_query_result_counts;

	if ( $query->get( 'is_ln_query' ) ) {
		$qid = $query->get( 'ln_query_id' );

		if ( null !== $qid && '' !== $qid ) {
			$ln_query_result_counts[ (string) $qid ] = count( $posts );
		}

		$ids               = array_map(
			static function ( $p ) {
				return is_object( $p ) ? (int) $p->ID : (int) $p;
			},
			$posts
		);
		$ln_query_posts_shown = array_values( array_unique( array_merge( $ln_query_posts_shown, $ids ) ) );
	}

	return $posts;
}
add_filter( 'the_posts', 'ln_query_remember_posts', 10, 2 );

/**
 * Modify query vars for LN Query variations during render.
 */
function ln_query_filter_query_loop_vars( $query, $block, $page ) {
	global $ln_query_posts_shown, $ln_query_ctx;

	$qid = $block->context['queryId'] ?? null;
	$ctx = ( null !== $qid && isset( $ln_query_ctx[ $qid ] ) ) ? $ln_query_ctx[ $qid ] : null;

	if ( ! $ctx || ( $ctx['namespace'] ?? null ) !== 'ln-query' ) {
		return $query;
	}


	if ( ! empty( $ctx['layoutPos'] ) ) {
		$meta_query = array(
			'key'   => 'layout_pos',
			'value' => $ctx['layoutPos'],
		);

		if ( ! empty( $query['meta_query'] ) ) {
			$query['meta_query'][] = $meta_query;
		} else {
			$query['meta_query'] = array( $meta_query );
		}
	}

	if ( 'verwant' === ( $ctx['queryType'] ?? '' ) ) {
		$query['orderby'] = 'rand';
		$has_related_tags = false;

		if ( is_singular() ) {
			$current_post_id = (int) get_queried_object_id();
			$tag_ids         = $current_post_id > 0
				? wp_get_post_terms( $current_post_id, 'post_tag', array( 'fields' => 'ids' ) )
				: array();

			if ( ! is_wp_error( $tag_ids ) && ! empty( $tag_ids ) ) {
				$tax_query = array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $tag_ids ),
					'operator' => 'IN',
				);

				if ( ! empty( $query['tax_query'] ) ) {
					$query['tax_query'][] = $tax_query;
				} else {
					$query['tax_query'] = array( $tax_query );
				}

				$has_related_tags = true;
			}
		}

		if ( ! $has_related_tags ) {
			$query['post__in'] = array( 0 );
		}
	}

	if ( 'recensent' === ( $ctx['queryType'] ?? '' ) ) {
		$current_post_id = is_singular() ? (int) get_queried_object_id() : 0;
		
		$author_meta = $current_post_id > 0 ? get_post_meta( $current_post_id, 'auteur_recensie', true ) : '';
		if(  $current_post_id > 0 && $author_meta === '' ) {
			$post_type = get_post_type( $current_post_id );
			if ( 'recensent' === $post_type ) {
				// the current post is a recensent, show recensies by this recensent
				$author_meta = $current_post_id;
			}
		}

		if ( is_scalar( $author_meta ) && '' !== trim( (string) $author_meta ) ) {
			$meta_query = array(
				'key'     => 'auteur_recensie',
				'value'   => (string) $author_meta,
				'compare' => '=',
			);

			if ( ! empty( $query['meta_query'] ) ) {
				$query['meta_query'][] = $meta_query;
			} else {
				$query['meta_query'] = array( $meta_query );
			}
		} else {
			$query['post__in'] = array( 0 );
		}
	}

	if ( null !== $qid ) {
		$query['ln_query_id'] = (string) $qid;
	}

	if ( ! empty( $ln_query_posts_shown ) ) {
		$existing             = isset( $query['post__not_in'] ) ? array_map( 'intval', (array) $query['post__not_in'] ) : array();
		$query['post__not_in'] = array_values( array_unique( array_merge( $existing, $ln_query_posts_shown ) ) );
	}

	$query['is_ln_query'] = true;

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'ln_query_filter_query_loop_vars', 10, 3 );

/**
 * Hide LN Query block output when no posts are returned.
 */
function ln_query_hide_empty_query_block( $content, $block ) {
	global $ln_query_result_counts;

	if ( ! isset( $block['blockName'] ) || 'core/query' !== $block['blockName'] ) {
		return $content;
	}

	$attrs = $block['attrs'] ?? array();

	if ( ( $attrs['namespace'] ?? null ) !== 'ln-query' ) {
		return $content;
	}

	$qid = $attrs['queryId'] ?? null;

	if ( null === $qid ) {
		return $content;
	}

	$count = $ln_query_result_counts[ (string) $qid ] ?? null;

	if ( null !== $count && (int) $count < 1 ) {
		return '';
	}

	return $content;
}
add_filter( 'render_block', 'ln_query_hide_empty_query_block', 11, 2 );

/**
 * Apply the same constraints to REST post previews.
 */
function ln_query_filter_rest_query( $args, $request ) {
	global $ln_query_posts_shown;

	$layoutpos  = $request->get_param( 'layoutPos' );
	$query_type = $request->get_param( 'queryType' );

	if ( empty( $layoutpos ) && ! in_array( $query_type, array( 'verwant', 'recensent' ), true ) ) {
		return $args;
	}

	if ( ! empty( $layoutpos ) ) {
		$meta_query = array(
			'key'   => 'layout_pos',
			'value' => $layoutpos,
		);

		if ( ! empty( $args['meta_query'] ) ) {
			$args['meta_query'][] = $meta_query;
		} else {
			$args['meta_query'] = array( $meta_query );
		}
	}

	if ( 'verwant' === $query_type ) {
		$args['orderby'] = 'rand';

		$current_post_id = (int) $request->get_param( 'currentPostId' );
		if ( $current_post_id <= 0 ) {
			$current_post_id = (int) $request->get_param( 'postId' );
		}
		if ( $current_post_id <= 0 ) {
			$current_post_id = (int) $request->get_param( 'post_id' );
		}
		if ( $current_post_id <= 0 && is_singular() ) {
			$current_post_id = (int) get_queried_object_id();
		}

		$tag_ids = $current_post_id > 0
			? wp_get_post_terms( $current_post_id, 'post_tag', array( 'fields' => 'ids' ) )
			: array();

		if ( ! is_wp_error( $tag_ids ) && ! empty( $tag_ids ) ) {
			$tax_query = array(
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $tag_ids ),
				'operator' => 'IN',
			);

			if ( ! empty( $args['tax_query'] ) ) {
				$args['tax_query'][] = $tax_query;
			} else {
				$args['tax_query'] = array( $tax_query );
			}
		} else {
			$args['post__in'] = array( 0 );
		}
	}

	if ( 'recensent' === $query_type ) {
		$current_post_id = (int) $request->get_param( 'currentPostId' );
		if ( $current_post_id <= 0 ) {
			$current_post_id = (int) $request->get_param( 'postId' );
		}
		if ( $current_post_id <= 0 ) {
			$current_post_id = (int) $request->get_param( 'post_id' );
		}
		if ( $current_post_id <= 0 && is_singular() ) {
			$current_post_id = (int) get_queried_object_id();
		}

		$author_meta = $current_post_id > 0 ? get_post_meta( $current_post_id, 'auteur_recensie', true ) : '';

		if ( is_scalar( $author_meta ) && '' !== trim( (string) $author_meta ) ) {
			$meta_query = array(
				'key'     => 'auteur_recensie',
				'value'   => (string) $author_meta,
				'compare' => '=',
			);

			if ( ! empty( $args['meta_query'] ) ) {
				$args['meta_query'][] = $meta_query;
			} else {
				$args['meta_query'] = array( $meta_query );
			}
		} else {
			$args['post__in'] = array( 0 );
		}
	}

	if ( ! empty( $ln_query_posts_shown ) ) {
		$existing            = isset( $args['post__not_in'] ) ? array_map( 'intval', (array) $args['post__not_in'] ) : array();
		$args['post__not_in'] = array_values( array_unique( array_merge( $existing, $ln_query_posts_shown ) ) );
	}

	$args['is_ln_query'] = true;

	return $args;
}
add_filter( 'rest_post_query', 'ln_query_filter_rest_query', 10, 2 );

/**
 * Allow orderby=rand for all REST post collections so previews match the frontend.
 *
 * @param array $params Collection params.
 *
 * @return array
 */
function ln_query_allow_rand_orderby( $params ) {
	if ( isset( $params['orderby']['enum'] ) && is_array( $params['orderby']['enum'] ) ) {
		if ( ! in_array( 'rand', $params['orderby']['enum'], true ) ) {
			$params['orderby']['enum'][] = 'rand';
		}
	}

	return $params;
}

/**
 * Hook the REST enum filter for every show_in_rest post type.
 */
function ln_query_register_rand_support() {
	$post_types = get_post_types( array( 'show_in_rest' => true ), 'names' );

	foreach ( $post_types as $post_type ) {
		add_filter( "rest_{$post_type}_collection_params", 'ln_query_allow_rand_orderby', 10 );
	}
}
add_action( 'init', 'ln_query_register_rand_support', 20 );

/**
 * Enqueue the editor script that registers the block variation.
 */
function ln_query_enqueue_editor_assets() {
	$plugin_root = dirname( __DIR__ );
	$asset_path  = $plugin_root . '/blocks/ln-query/build/index.asset.php';

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset = include $asset_path;
	$deps  = isset( $asset['dependencies'] ) ? (array) $asset['dependencies'] : array();
	$ver   = $asset['version'] ?? filemtime( $plugin_root . '/blocks/ln-query/build/index.js' );

	wp_enqueue_script(
		'ln-query-variation',
		plugins_url( 'blocks/ln-query/build/index.js', $plugin_root . '/x-literair-nederland-blocks.php' ),
		$deps,
		$ver,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'ln_query_enqueue_editor_assets' );



