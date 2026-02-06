<?php

function ln_register_bibliographics_meta() {
    register_post_meta(
        '',
        'boektitel',
        array(
            'single'            => true,
            'show_in_rest'      => true,
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'auth_callback'     => function() {
                return current_user_can( 'edit_posts' );
            },
        )
    );

    register_post_meta(
        '',
        'isbn',
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
add_action( 'init', 'ln_register_bibliographics_meta' );

function ln_sync_bibliographics_isbn_meta( $post_id, $post, $update ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    if ( 'revision' === $post->post_type ) {
        return;
    }

    $raw_content = $post->post_content ?? '';

    if ( ! has_block( 'ln/ln-bibliographics', $raw_content ) ) {
        delete_post_meta( $post_id, 'isbn' );
        return;
    }

    delete_post_meta( $post_id, 'isbn' );

    $blocks = parse_blocks( $raw_content );
    $isbns  = ln_collect_bibliographics_isbns( $blocks );

    foreach ( $isbns as $isbn ) {
        $sanitized = sanitize_text_field( $isbn );
        if ( '' !== $sanitized ) {
            add_post_meta( $post_id, 'isbn', $sanitized, false );
        }
    }
}
add_action( 'save_post', 'ln_sync_bibliographics_isbn_meta', 10, 3 );

function ln_collect_bibliographics_isbns( array $blocks ) {
    $isbns = array();

    foreach ( $blocks as $block ) {
        if ( empty( $block['blockName'] ) ) {
            continue;
        }

        if ( 'ln/ln-bibliographics' === $block['blockName'] ) {
            $maybe_isbn = isset( $block['attrs']['isbn'] ) ? $block['attrs']['isbn'] : '';
            if ( '' !== $maybe_isbn ) {
                $isbns[] = $maybe_isbn;
            }
        }

        if ( ! empty( $block['innerBlocks'] ) ) {
            $isbns = array_merge( $isbns, ln_collect_bibliographics_isbns( $block['innerBlocks'] ) );
        }
    }

    return $isbns;
}
