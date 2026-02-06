<?php
/*
Plugin Name: Doneren met ING Checkout
Description: Receive donations via ING Checkout
Version: 1.0
Author: hollands-spoor.com
Author URI: https://hollands-spoor.com
Text Domain: doneren-met-ing
*/

/**
 * 
 * Pages:
 * 
 * donation_page_id: main donation page containing the checkout form shortcode
 * 
 * thank-you-page
 * cancel-page: 'donation_cancelled',
 * pending-page: 'donation_pending',
 * failed-page: 'donation_failed',
 * 
 * exchange-page
 * return-page
 * 
 * 
 * shortcodes:
 * 
 * [doneren_met_ing_checkout_form]
 * 
 */

require_once plugin_dir_path(__FILE__) . 'classes/class-ln-doneren-options.php';
LN_Doneren_Options::instance();

require_once plugin_dir_path(__FILE__) . 'classes/class-ln-doneren.php';
$page_id = get_the_ID();
$donatie = LN_Doneren::instance($page_id);
$donation_options = get_option( 'ln_doneren_options' );


function ln_doneren_shortcode_handler( $atts ) {
    global $donatie, $donation_options;

    /**
     * Alles gaat via deze shortcode handler [ln_donate]
     * 
     * Er zijn 5 scenario's:
     * 
     * 1. als $atts['stempel'] = 'yes' dan toon alleen het stempel
     */
    if( isset($atts) && isset( $atts['stempel'] ) && $atts['stempel'] === 'yes' ) {
        return $donatie->render_stamp();

    }

    /**
     * 2. als de exchange_url is aangeroepen door Pay.nl, verwerk de exchange.
     *    test:  This GET params are passed: 
     *           [payment_session_id] => 3259328022
     *           [order_id] => 3259328022X2a7ee 
     *           also check if order_id is in DB
     */
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['action'] ) && isset( $_GET['payment_session_id'] ) && isset ( $_GET['order_id'] ) ) {
        $payment_session_id = sanitize_text_field( $_GET['payment_session_id'] );
        $order_id = sanitize_text_field( $_GET['order_id'] );
        $action = sanitize_text_field( $_GET['action'] );
        $exchange_result = $donatie->process_exchange( $action, $payment_session_id, $order_id );

        nocache_headers();
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: text/plain; charset=utf-8', true, 200);
        echo 'TRUE';
        exit;
    }

    /**
     * 3. if return_url after payment is called by Pay.nl, route the visitor to the appropriate page
     *   Test: These GET params are passed:
     *           [orderId] => 3259328022X2a7ee
     *           [orderStatusId] => 2
     *           [paymentSessionId] => 3259328022
     * 
     */    
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['orderId'] ) && isset ( $_GET['orderStatusId'] ) ) {
        $order_id = sanitize_text_field( $_GET['orderId'] );
        $order_status_id = sanitize_text_field( $_GET['orderStatusId'] );
        $payment_session_id = sanitize_text_field( $_GET['paymentSessionId'] ?? '' );
        // TODO: check also $payment_session_id 
        // TODO: check if orderId is in our database
        error_log( 'Processing return_url callback for orderId: ' . $order_id . ' status: ' . $order_status_id );
        $donatie->redirect_after_payment( $order_id, $order_status_id, $payment_session_id );
        // die here?



        return;
    }

    // 4. als formulier is ingevuld, verwerk de donatie
    //   $donatie->process_form($atts);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ln_donate_nonce'] ) && isset( $_POST['hs_did'])) {
        // hier iets retourneren? of alleen maar doen?
        return $donatie->process_form($atts);
    }
    // 5.   Toon het formulier 
    //      Met content van de pagina met id $options['donation_general']
    $form_page = '';
    $general_page_id = $donation_options['donation_general'] ?? 0;
    if ($general_page_id) {
        $general_content = get_post_field('post_content', $general_page_id);
        if ($general_content) {
            $form_page .= apply_filters('the_content', $general_content);
        }
    }
    $form_page .= $donatie->render_donation_form( $atts );

    return $form_page;
}

add_shortcode('ln_donate', 'ln_doneren_shortcode_handler' );


function ln_donations_admin() {
    include_once plugin_dir_path(__FILE__) . 'admin/ln-donations-admin.php';
    ln_doneren_render_admin_table();
}

// Register a dedicated Donaties menu entry in WP admin.
function ln_doneren_register_admin_menu() {
    add_menu_page(
        __( 'Donaties', 'doneren-met-ing' ),
        __( 'Donaties', 'doneren-met-ing' ),
        'manage_options',
        'ln-donaties',
        'ln_doneren_render_admin_page',
        'dashicons-heart',
        56
    );
}
add_action( 'admin_menu', 'ln_doneren_register_admin_menu' );

function ln_doneren_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have permission to access this page.', 'doneren-met-ing' ) );
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Donaties', 'doneren-met-ing' ) . '</h1>';
    ln_donations_admin();
    echo '</div>';
}

add_action( 'admin_enqueue_scripts', 'ln_doneren_admin_enqueue_assets' );
function ln_doneren_admin_enqueue_assets( $hook_suffix ) {
    if ( 'toplevel_page_ln-donaties' !== $hook_suffix ) {
        return;
    }

    wp_register_style( 'ln-donaties-admin-inline', false );
    wp_enqueue_style( 'ln-donaties-admin-inline' );

    $css = 'body.toplevel_page_ln-donaties #wpfooter { position: static; }';
    wp_add_inline_style( 'ln-donaties-admin-inline', $css );

    wp_register_script( 'ln-donaties-admin-inline', '', array( 'jquery' ), false, true );
    wp_enqueue_script( 'ln-donaties-admin-inline' );

    $script_data = array(
        'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
        'nonce'       => wp_create_nonce( 'ln_delete_donation' ),
        'confirmText' => __( 'Weet je zeker dat je deze donatie wilt verwijderen?', 'doneren-met-ing' ),
        'errorText'   => __( 'Verwijderen mislukt. Probeer het opnieuw.', 'doneren-met-ing' ),
        'successText' => __( 'Donatie verwijderd.', 'doneren-met-ing' ),
        'loadingText' => __( 'Verwijderen...', 'doneren-met-ing' ),
    );

    $inline_js  = 'window.LNDonatiesAdmin = ' . wp_json_encode( $script_data ) . ';';
    $inline_js .= '(function($){$(document).on("click",".ln-donation-delete",function(e){e.preventDefault();var cfg=window.LNDonatiesAdmin||{};if(!cfg.ajaxUrl){return;}if(!window.confirm(cfg.confirmText||"")){return;}var $btn=$(this);if($btn.prop("disabled")){return;}var donationId=$btn.data("donationId");if(!donationId){return;}var originalText=$btn.text();$btn.prop("disabled",true).text(cfg.loadingText||"...");$.post(cfg.ajaxUrl,{action:"ln_delete_donation",donation_id:donationId,nonce:cfg.nonce}).done(function(response){if(response&&response.success){var $row=$btn.closest("tr");$row.fadeOut(200,function(){$row.remove();});}else{alert((response&&response.data)?response.data:(cfg.errorText||"Error"));$btn.prop("disabled",false).text(originalText);}}).fail(function(){alert(cfg.errorText||"Error");$btn.prop("disabled",false).text(originalText);});});})(jQuery);';

    wp_add_inline_script( 'ln-donaties-admin-inline', $inline_js );
}

add_action( 'wp_ajax_ln_delete_donation', 'ln_doneren_handle_delete_donation' );
function ln_doneren_handle_delete_donation() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'Geen toestemming om te verwijderen.', 'doneren-met-ing' ), 403 );
    }

    check_ajax_referer( 'ln_delete_donation', 'nonce' );

    $donation_id = isset( $_POST['donation_id'] ) ? absint( $_POST['donation_id'] ) : 0;
    if ( ! $donation_id ) {
        wp_send_json_error( __( 'Ongeldig donatie-ID.', 'doneren-met-ing' ), 400 );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ln_donations';
    $deleted    = $wpdb->delete( $table_name, array( 'id' => $donation_id ), array( '%d' ) );

    if ( false === $deleted ) {
        wp_send_json_error( __( 'Verwijderen mislukt in de database.', 'doneren-met-ing' ), 500 );
    }

    wp_send_json_success();
}


function ln_create_donation_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ln_donations';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        hs_did varchar(255) NOT NULL,
        hs_status varchar(50) NOT NULL DEFAULT 'new',
        hs_bedrag decimal(10,2) NOT NULL,
        hs_naam varchar(255) DEFAULT NULL,
        hs_email varchar(255) DEFAULT NULL,
        hs_betaalprovider varchar(100) DEFAULT NULL,
        hs_betaaldata text DEFAULT NULL,
        hs_created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        hs_updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        hs_transactieID varchar(255) DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY hs_did (hs_did)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'ln_create_donation_table');

