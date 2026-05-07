<?php

$bibliographic = isset( $attributes['bibliographic'] ) && is_array( $attributes['bibliographic'] )
    ? $attributes['bibliographic']
    : array();

$boektitel = $attributes['boektitel'] ?? ( $attributes['boektitel'] ?? '' );
$auteur    = $bibliographic['auteur_boek'] ?? '';
$isbn      = $bibliographic['isbn'] ?? ( $attributes['isbn'] ?? '' );
$uitgever  = $bibliographic['uitgever'] ?? '';
$omslag_id = isset( $bibliographic['omslag_id'] ) ? absint( $bibliographic['omslag_id'] ) : 0;
$raw_price = isset( $bibliographic['prijs'] ) ? trim( (string) $bibliographic['prijs'] ) : '';
$formatted_price = '';

if ( '' !== $raw_price ) {
    $numeric = preg_replace( '/[^0-9,\.]/', '', $raw_price );

    if ( '' !== $numeric ) {
        if ( false !== strpos( $numeric, ',' ) && false !== strpos( $numeric, '.' ) ) {
            $numeric = str_replace( '.', '', $numeric );
            $numeric = str_replace( ',', '.', $numeric );
        } elseif ( false !== strpos( $numeric, ',' ) ) {
            $numeric = str_replace( ',', '.', $numeric );
        }

        if ( is_numeric( $numeric ) ) {
            $amount          = (float) $numeric;
            $formatted_price = sprintf( '€ %s', number_format_i18n( $amount, 2 ) );
        }
    }

    if ( '' === $formatted_price ) {
        $formatted_price = $raw_price;
    }
}

$aantal_paginas = $bibliographic['aantal_paginas'] ?? '';
$vertaling_door = $bibliographic['vertaling_door'] ?? '';
$vrije_regel = $bibliographic['vrije_regel'] ?? '';
$oorspronkelijke_titel = $bibliographic['oorspronkelijke_titel'] ?? '';
$nawoord_door = $bibliographic['nawoord_door'] ?? '';
$voorwoord_door = $bibliographic['voorwoord_door'] ?? '';
$illustraties_door = $bibliographic['illustraties_door'] ?? '';
$auteur_url = $bibliographic['auteur_boek_url'] ?? '';
$uitgever_url = $bibliographic['uitgever_url'] ?? '';



$show_buy_button = ! empty( $attributes['showBuyButton'] );
$is_sticky       = ! empty( $attributes['isSticky'] );

$wrapper_attributes = array();
if ( $is_sticky ) {
    $wrapper_attributes['style'] = 'position:sticky; top:0;';
}

// Vendor no longer is an indivdual attribute, we make it a global setting later.
$buy_vendor      = 'libris';
$buy_button_url  = '';
$buy_button_label = '';

if ( $show_buy_button && $buy_vendor && $isbn ) {
    global $buy_buttons;

    if ( isset( $buy_buttons[ $buy_vendor ] ) ) {
        $vendor_config = $buy_buttons[ $buy_vendor ];
        $template_url  = isset( $vendor_config['url'] ) ? $vendor_config['url'] : '';

        if ( $template_url ) {
            $buy_button_url = sprintf( $template_url, rawurlencode( $isbn ) );
            $buy_button_label = isset( $vendor_config['label'] ) ? $vendor_config['label'] : __( 'Buy now', 'x-literair-nederland-blocks' );
        }
    }
}
?>
<div <?php echo get_block_wrapper_attributes( $wrapper_attributes ); ?>>
    <?php if ( $omslag_id ) : ?>
        <?php
        $cover_image = wp_get_attachment_image(
            $omslag_id,
            'medium_large',
            false,
            array(
                'class'   => 'ln-bibliographics__cover',
                'alt'     => $boektitel ? wp_strip_all_tags( $boektitel ) : '',
                'loading' => 'lazy',
            )
        );

        if ( $cover_image ) {
            echo $cover_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() is escaped.
        }
        ?>
    <?php endif; ?>
    <?php if ( $boektitel ) : ?>
        <h2 class="ln-boektitel"><?php echo wp_kses_post( $boektitel ); ?></h2>
    <?php endif; ?>
    <?php if ( $auteur ) : ?>
        <p class="ln-auteur">
            <?php 
                if( $auteur_url ) {
                    echo '<a href="' . esc_url( $auteur_url ) . '" target="_blank" rel="noopener">' . esc_html( $auteur ) . '</a>';
                } else {
                    echo esc_html( $auteur );
                }
            ?>
        </p>
    <?php endif; ?>
    <?php if ( $vrije_regel ) : ?>
        <p><?php echo esc_html( $vrije_regel ); ?></p>
    <?php endif; ?>
    <?php if ( $vertaling_door ) : ?>
        <p><?php echo esc_html__( 'Translation by:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $vertaling_door ); ?></p>
    <?php endif; ?>
    <?php if ( $oorspronkelijke_titel ) : ?>
        <p><?php echo esc_html__( 'Original title:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $oorspronkelijke_titel ); ?></p>
    <?php endif; ?>
    <?php if ( $voorwoord_door ) : ?>
        <p><?php echo esc_html__( 'Foreword by:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $voorwoord_door ); ?></p>
    <?php endif; ?>
    <?php if ( $nawoord_door ) : ?>
        <p><?php echo esc_html__( 'Afterword by:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $nawoord_door ); ?></p>
    <?php endif; ?>
    <?php if ( $illustraties_door ) : ?>
        <p><?php echo esc_html__( 'Illustrations by:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $illustraties_door ); ?></p>
    <?php endif; ?>
    <?php if ( $uitgever ) : ?>
		<p><?php echo esc_html__( 'Publisher:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $uitgever ); ?></p>
    <?php endif; ?>
    <?php if ( $isbn ) : ?>
        <p class="ln-isbn"><?php echo esc_html__( 'ISBN', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $isbn ); ?></p>
    <?php endif; ?>
    <?php if( $aantal_paginas ) : ?>
		<p class="ln-aantal-paginas"><?php echo esc_html( $aantal_paginas ) . ' pages'; ?></p>
    <?php endif; ?>
    <?php if ( $formatted_price ) : ?>
		<p class="ln-prijs"><?php echo esc_html__( 'Price:', 'x-literair-nederland-blocks' ) . ' ' . esc_html( $formatted_price ); ?></p>
    <?php endif; ?>
    <?php if ( $buy_button_url ) : ?>
        <a
            class="ln-bibliographics__buy-button"
            href="<?php echo esc_url( $buy_button_url ); ?>"
            target="_blank"
            rel="nofollow sponsored noopener"
        >
            <?php echo esc_html( $buy_button_label ); ?>
        </a>
    <?php endif; ?>
</div>
<?php
