<?php

if ( ! defined( 'LN_DONEREN_ADMIN_PER_PAGE' ) ) {
    define( 'LN_DONEREN_ADMIN_PER_PAGE', 20 );
}

function ln_doneren_get_status_options() {
    return array(
        'ALL'       => __( 'ALL', 'doneren-met-ing' ),
        'CREATED'   => __( 'CREATED', 'doneren-met-ing' ),
        'PENDING'   => __( 'PENDING', 'doneren-met-ing' ),
        'PAID'      => __( 'PAID', 'doneren-met-ing' ),
        'CANCELLED' => __( 'CANCELLED', 'doneren-met-ing' ),
        'FAILED'    => __( 'FAILED', 'doneren-met-ing' ),
    );
}

function ln_doneren_get_status_filter() {
    $options = ln_doneren_get_status_options();
    $status  = isset( $_GET['status'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['status'] ) ) ) : 'ALL';

    if ( ! array_key_exists( $status, $options ) ) {
        $status = 'ALL';
    }

    return $status;
}

function ln_doneren_get_donations_data( $page = 1, $per_page = LN_DONEREN_ADMIN_PER_PAGE, $status_filter = 'ALL' ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ln_donations';
    $offset     = ( $page - 1 ) * $per_page;
    $where_sql  = '';

    if ( 'ALL' !== $status_filter ) {
        $where_sql = $wpdb->prepare( ' WHERE UPPER(hs_status) = %s ', $status_filter );
    }

    $items = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_name {$where_sql}ORDER BY hs_created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ) );

    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name {$where_sql}" );

    return array(
        'items' => $items,
        'total' => $total,
    );
}

function ln_doneren_render_admin_table() {
    $per_page = LN_DONEREN_ADMIN_PER_PAGE;
    $page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
    $status_filter = ln_doneren_get_status_filter();

    $results    = ln_doneren_get_donations_data( $page, $per_page, $status_filter );
    $donations  = $results['items'];
    $total      = $results['total'];
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );

    if ( empty( $donations ) ) {
        echo '<p>' . esc_html__( 'Nog geen donaties gevonden.', 'doneren-met-ing' ) . '</p>';
        return;
    }

    $date_format = trim( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

    ln_doneren_render_tablenav( $page, $total_pages, $total, 'top', $status_filter );

    echo '<table class="wp-list-table widefat fixed striped table-view-list">';
    echo '<thead><tr>';
    echo '<th scope="col">' . esc_html__( 'Datum', 'doneren-met-ing' ) . '</th>';
    echo '<th scope="col">' . esc_html__( 'Donateur', 'doneren-met-ing' ) . '</th>';
    echo '<th scope="col">' . esc_html__( 'Bedrag', 'doneren-met-ing' ) . '</th>';
    echo '<th scope="col">' . esc_html__( 'Status', 'doneren-met-ing' ) . '</th>';
    echo '<th scope="col">' . esc_html__( 'E-mail', 'doneren-met-ing' ) . '</th>';
    echo '<th scope="col">' . esc_html__( 'Transactie-ID', 'doneren-met-ing' ) . '</th>';
    echo '<th scope="col" class="column-actions">' . esc_html__( 'Acties', 'doneren-met-ing' ) . '</th>';
    echo '</tr></thead>';

    echo '<tbody>';
    foreach ( $donations as $donation ) {
        $donor_name = $donation->hs_naam ? $donation->hs_naam : __( 'Anonieme donateur', 'doneren-met-ing' );
        $created_at = $donation->hs_created_at ? mysql2date( $date_format, $donation->hs_created_at ) : '';
        $status     = $donation->hs_status ? strtoupper( $donation->hs_status ) : '';
        $email      = $donation->hs_email ? esc_html( $donation->hs_email ) : '&mdash;';
        $transaction = $donation->hs_transactieID ? esc_html( $donation->hs_transactieID ) : '&mdash;';
        $donation_id = isset( $donation->id ) ? absint( $donation->id ) : 0;
        $amount     = sprintf( '€ %s', number_format_i18n( (float) $donation->hs_bedrag, 2 ) );

        echo '<tr>';
        echo '<td>' . ( $created_at ? esc_html( $created_at ) : '&mdash;' ) . '</td>';
        echo '<td><strong>' . esc_html( $donor_name ) . '</strong></td>';
        echo '<td>' . esc_html( $amount ) . '</td>';
        echo '<td>' . ( $status ? esc_html( $status ) : '&mdash;' ) . '</td>';
        echo '<td>' . $email . '</td>';
        echo '<td>' . $transaction . '</td>';
        echo '<td class="ln-donation-actions">';
        if ( $donation_id ) {
            echo '<button type="button" class="button button-small ln-donation-delete" data-donation-id="' . esc_attr( $donation_id ) . '">' . esc_html__( 'Verwijder', 'doneren-met-ing' ) . '</button>';
        } else {
            echo '&mdash;';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';

    ln_doneren_render_tablenav( $page, $total_pages, $total, 'bottom', $status_filter );
}

function ln_doneren_render_tablenav( $page, $total_pages, $total_items, $position = 'top', $status_filter = 'ALL' ) {
    $base_url = menu_page_url( 'ln-donaties', false );
    $base_args = array();

    if ( 'ALL' !== $status_filter ) {
        $base_args['status'] = $status_filter;
    }

    $prev_url = add_query_arg( array_merge( $base_args, array( 'paged' => max( 1, $page - 1 ) ) ), $base_url );
    $next_url = add_query_arg( array_merge( $base_args, array( 'paged' => min( $total_pages, $page + 1 ) ) ), $base_url );

    $displaying_num = sprintf(
        _n( '%s donatie', '%s donaties', $total_items, 'doneren-met-ing' ),
        number_format_i18n( $total_items )
    );

    $wrap_bottom = ( 'bottom' === $position );
    if ( $wrap_bottom ) {
        echo '<div class="wrap ln-donaties-tablenav-bottom">';
    }

    echo '<div class="tablenav ' . esc_attr( $position ) . '">';

    if ( 'top' === $position ) {
        $options = ln_doneren_get_status_options();
        echo '<div class="alignleft actions">';
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="ln-donaties" />';
        echo '<label for="ln-donation-status-filter" class="screen-reader-text">' . esc_html__( 'Filter op status', 'doneren-met-ing' ) . '</label>';
        echo '<select name="status" id="ln-donation-status-filter">';
        foreach ( $options as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $status_filter, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '<input type="submit" class="button" value="' . esc_attr__( 'Filter', 'doneren-met-ing' ) . '" />';
        echo '</form>';
        echo '</div>';
    }

    echo '<div class="tablenav-pages">';
    echo '<span class="displaying-num">' . esc_html( $displaying_num ) . '</span>';
    echo '<span class="pagination-links">';

    if ( $page > 1 ) {
        echo '<a class="prev-page button" href="' . esc_url( $prev_url ) . '"><span class="screen-reader-text">' . esc_html__( 'Vorige pagina', 'doneren-met-ing' ) . '</span><span aria-hidden="true">&lsaquo;</span></a>';
    } else {
        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
    }

    echo '<span class="tablenav-pages-navspan spacer" aria-hidden="true"></span>';

    echo '<span class="paging-input" style="margin:0 8px;">';
    echo '<span class="tablenav-paging-text">' . sprintf(
        esc_html__( '%1$s van %2$s', 'doneren-met-ing' ),
        esc_html( number_format_i18n( $page ) ),
        esc_html( number_format_i18n( $total_pages ) )
    ) . '</span>';
    echo '</span>';

    echo '<span class="tablenav-pages-navspan spacer" aria-hidden="true"></span>';

    if ( $page < $total_pages ) {
        echo '<a class="next-page button" href="' . esc_url( $next_url ) . '"><span class="screen-reader-text">' . esc_html__( 'Volgende pagina', 'doneren-met-ing' ) . '</span><span aria-hidden="true">&rsaquo;</span></a>';
    } else {
        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
    }

    echo '</span>';
    echo '</div>';
    echo '</div>'; // .tablenav-pages
    echo '</div>'; // .tablenav

    if ( $wrap_bottom ) {
        echo '</div>'; // .wrap
    }
}