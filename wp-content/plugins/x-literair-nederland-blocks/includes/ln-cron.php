<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




const XLN_CRON_NIGHTLY_EVENT = 'xln_cron_nightly_event';
const XLN_CRON_NIGHTLY_HOOK  = 'xln_cron_nightly_handlers';

/**
 * Schedule the nightly cron event if it is not already scheduled.
 *
 * @return void
 */
function xln_cron_schedule_nightly_event(): void {
    if ( wp_next_scheduled( XLN_CRON_NIGHTLY_EVENT ) ) {
        return;
    }

    wp_schedule_event( xln_cron_get_next_midnight_timestamp(), 'daily', XLN_CRON_NIGHTLY_EVENT );
}
add_action( 'init', 'xln_cron_schedule_nightly_event' );

/**
 * Unschedule all queued nightly cron events.
 *
 * @return void
 */
function xln_cron_unschedule_nightly_event(): void {
    $timestamp = wp_next_scheduled( XLN_CRON_NIGHTLY_EVENT );

    while ( false !== $timestamp ) {
        wp_unschedule_event( $timestamp, XLN_CRON_NIGHTLY_EVENT );
        $timestamp = wp_next_scheduled( XLN_CRON_NIGHTLY_EVENT );
    }
}

/**
 * Returns the next midnight timestamp in site timezone.
 *
 * @return int
 */
function xln_cron_get_next_midnight_timestamp(): int {
    $now      = new DateTimeImmutable( 'now', wp_timezone() );
    $midnight = $now->setTime( 0, 0 )->modify( '+1 day' );

    return $midnight->getTimestamp();
}

/**
 * Dispatch all nightly cron handlers.
 *
 * @return void
 */
function xln_cron_run_nightly_handlers(): void {
    error_log( '[LN Cron] Nightly dispatcher start.' );
    do_action( XLN_CRON_NIGHTLY_HOOK );
    error_log( '[LN Cron] Nightly dispatcher end.' );
}
add_action( XLN_CRON_NIGHTLY_EVENT, 'xln_cron_run_nightly_handlers' );

/**
 * Return the configured number of days after which breaking news expires.
 *
 * @return int
 */
function xln_cron_get_breaking_news_expiry_days(): int {
    $days = 1;

    if ( function_exists( 'xln_get_options' ) ) {
        $options = xln_get_options();
        if ( isset( $options['breaking_news_expires'] ) ) {
            $days = (int) $options['breaking_news_expires'];
        }
    } else {
        $options = get_option( 'xln_options', array() );
        if ( is_array( $options ) && isset( $options['breaking_news_expires'] ) ) {
            $days = (int) $options['breaking_news_expires'];
        }
    }

    return max( 1, $days );
}

/**
 * Nightly handler: move expired breaking news posts from "breaking" to "rij-1".
 * News is breaking when it has the meta key "layout_pos" with value "breaking". 
 * When news expires, the meta value is updated to "rij-1".
 *
 * @return void
 */
function xln_cron_handle_breaking_news_expiry(): void {
    $expiry_days = xln_cron_get_breaking_news_expiry_days();
    $threshold   = time() - ( $expiry_days * DAY_IN_SECONDS );
    $moved_count = 0;

    error_log( sprintf( '[LN Cron] Breaking news handler start. expiry_days=%d', $expiry_days ) );

    $query = new WP_Query(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query'             => array(
                array(
                    'key'   => 'layout_pos',
                    'value' => 'breaking',
                ),
            ),
        )
    );

    if ( empty( $query->posts ) ) {
        error_log( '[LN Cron] Breaking news handler found no posts with layout_pos=breaking.' );
        return;
    }

    foreach ( $query->posts as $post_id ) {
        $published_gmt = (int) get_post_time( 'U', true, (int) $post_id );

        if ( $published_gmt <= 0 || $published_gmt > $threshold ) {
            continue;
        }

        update_post_meta( (int) $post_id, 'layout_pos', 'rij-1' );
        $moved_count++;
        error_log( sprintf( '[LN Cron] %s is moved to rij-1.', get_the_title( (int) $post_id ) ) );
    }

    error_log( sprintf( '[LN Cron] Breaking news handler end. moved=%d', $moved_count ) );
}
add_action( XLN_CRON_NIGHTLY_HOOK, 'xln_cron_handle_breaking_news_expiry' );

