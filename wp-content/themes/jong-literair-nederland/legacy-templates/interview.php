<?php

if ( ! isset( $post ) || ! $post instanceof WP_Post ) {
	return;
}

$post_id = $post->ID;

$boektitel             = ln_get_legacy_bibliographics_meta( $post_id, 'boektitel' );
$auteur                = ln_get_legacy_bibliographics_meta( $post_id, 'auteur_boek' );
$auteur_url            = ln_get_legacy_bibliographics_meta( $post_id, 'auteur_boek_url' );
$uitgever              = ln_get_legacy_bibliographics_meta( $post_id, 'uitgever' );
$uitgever_url          = ln_get_legacy_bibliographics_meta( $post_id, 'uitgever_url' );
$isbn                  = ln_get_legacy_bibliographics_meta( $post_id, 'isbn' );
$vrije_regel           = ln_get_legacy_bibliographics_meta( $post_id, 'vrije_regel' );
$vertaling_door        = ln_get_legacy_bibliographics_meta( $post_id, 'vertaling_door' );
$oorspronkelijke_titel = ln_get_legacy_bibliographics_meta( $post_id, 'oorspronkelijke_titel' );
$nawoord_door          = ln_get_legacy_bibliographics_meta( $post_id, 'nawoord_door' );
$illustraties_door     = ln_get_legacy_bibliographics_meta( $post_id, 'illustraties_door' );
$aantal_paginas        = ln_get_legacy_bibliographics_meta( $post_id, 'aantal_paginas' );
$formatted_price       = ln_format_legacy_bibliographics_price( ln_get_legacy_bibliographics_meta( $post_id, 'prijs' ) );
$omslag_id             = absint( ln_get_legacy_bibliographics_meta( $post_id, 'omslag_id' ) );
$omslag_foto           = ln_get_legacy_bibliographics_meta( $post_id, 'omslag_foto' );

if ( ! $omslag_id ) {
	$omslag_id = get_post_thumbnail_id( $post_id );
}

$has_bibliographics = (bool) array_filter(
	array(
		$boektitel,
		$auteur,
		$uitgever,
		$isbn,
		$vrije_regel,
		$vertaling_door,
		$oorspronkelijke_titel,
		$nawoord_door,
		$illustraties_door,
		$aantal_paginas,
		$formatted_price,
		$omslag_id,
		$omslag_foto,
	)
);

if ( ! $has_bibliographics ) {
	return;
}
?>
<div class="wp-block-ln-ln-bibliographics ln-legacy-bibliographics ln-legacy-bibliographics--interview">
	<?php if ( $omslag_id ) : ?>
		<?php
		echo wp_get_attachment_image(
			$omslag_id,
			'medium_large',
			false,
			array(
				'class'   => 'ln-bibliographics__cover',
				'alt'     => $boektitel ? wp_strip_all_tags( $boektitel ) : '',
				'loading' => 'lazy',
			)
		);
		?>
	<?php elseif ( $omslag_foto ) : ?>
		<img
			class="ln-bibliographics__cover"
			src="<?php echo esc_url( $omslag_foto ); ?>"
			alt="<?php echo esc_attr( $boektitel ? wp_strip_all_tags( $boektitel ) : '' ); ?>"
			loading="lazy"
		
		>
	<?php endif; ?>

	<?php if ( $boektitel ) : ?>
		<h2 class="ln-boektitel"><?php echo wp_kses_post( $boektitel ); ?></h2>
	<?php endif; ?>

	<?php if ( $auteur ) : ?>
		<p class="ln-auteur">
			<?php if ( $auteur_url ) : ?>
				<a href="<?php echo esc_url( $auteur_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $auteur ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $auteur ); ?>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( $vrije_regel ) : ?>
		<p><?php echo esc_html( $vrije_regel ); ?></p>
	<?php endif; ?>

	<?php if ( $vertaling_door ) : ?>
		<p><?php echo esc_html__( 'Translation by:', 'jong-literair-nederland' ) . ' ' . esc_html( $vertaling_door ); ?></p>
	<?php endif; ?>

	<?php if ( $oorspronkelijke_titel ) : ?>
		<p><?php echo esc_html__( 'Original title:', 'jong-literair-nederland' ) . ' ' . esc_html( $oorspronkelijke_titel ); ?></p>
	<?php endif; ?>

	<?php if ( $nawoord_door ) : ?>
		<p><?php echo esc_html__( 'Afterword by:', 'jong-literair-nederland' ) . ' ' . esc_html( $nawoord_door ); ?></p>
	<?php endif; ?>

	<?php if ( $illustraties_door ) : ?>
		<p><?php echo esc_html__( 'Illustrations by:', 'jong-literair-nederland' ) . ' ' . esc_html( $illustraties_door ); ?></p>
	<?php endif; ?>

	<?php if ( $uitgever ) : ?>
		<p>
			<?php echo esc_html__( 'Uitgever:', 'jong-literair-nederland' ) . ' '; ?>
			<?php if ( $uitgever_url ) : ?>
				<a href="<?php echo esc_url( $uitgever_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $uitgever ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $uitgever ); ?>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( $isbn ) : ?>
		<p class="ln-isbn"><?php echo esc_html__( 'ISBN', 'jong-literair-nederland' ) . ' ' . esc_html( $isbn ); ?></p>
	<?php endif; ?>

	<?php if ( $aantal_paginas ) : ?>
		<p class="ln-aantal-paginas"><?php echo esc_html( $aantal_paginas ) . ' pagina\'s'; ?></p>
	<?php endif; ?>

	<?php if ( $formatted_price ) : ?>
		<p class="ln-prijs"><?php echo esc_html__( 'Prijs:', 'jong-literair-nederland' ) . ' ' . esc_html( $formatted_price ); ?></p>
	<?php endif; ?>

	<?php if ( $isbn ) : ?>
		<?php do_action( 'show_buy_button', $isbn ); ?>
	<?php endif; ?>

</div>
