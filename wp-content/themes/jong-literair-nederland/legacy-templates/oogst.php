<?php

if ( ! isset( $post ) || ! $post instanceof WP_Post ) {
	return;
}

$post_id = $post->ID;
$books   = array();

if ( function_exists( 'get_field' ) ) {
	$acf_books   = array();
	$all_fields  = function_exists( 'get_fields' ) ? get_fields( $post_id ) : false;
	$indexed_raw = array();

	if ( is_array( $all_fields ) ) {
		foreach ( $all_fields as $field_slug => $field_value ) {
			if ( ! is_string( $field_slug ) || ! preg_match( '/^oogst_boek_(\d+)$/', $field_slug, $matches ) ) {
				continue;
			}

			if ( ! is_array( $field_value ) ) {
				continue;
			}

			$index = (int) $matches[1];
			if ( $index <= 0 ) {
				continue;
			}

			$indexed_raw[ $index ] = $field_value;
		}

		if ( ! empty( $indexed_raw ) ) {
			ksort( $indexed_raw, SORT_NUMERIC );
			$acf_books = array_values( $indexed_raw );
		}
	}

	if ( empty( $acf_books ) ) {
		$acf_books = get_field( 'besproken_boeken', $post_id );
	}

	if ( is_array( $acf_books ) ) {
		$books = $acf_books;
	}
}

if ( ! empty( $books ) ) :
?>
	<div class="ln-legacy-bibliographics-list ln-legacy-bibliographics-list--oogst">
		<?php foreach ( $books as $book ) : ?>
			<?php
			$titel           = isset( $book['oogst_boektitel'] ) ? trim( (string) $book['oogst_boektitel'] ) : '';
			$auteur          = isset( $book['boekauteur'] ) ? trim( (string) $book['boekauteur'] ) : '';
			$beschrijving    = isset( $book['oogst_bespreking'] ) ? $book['oogst_bespreking'] : '';
			$uitgever        = isset( $book['uitgeverij'] ) ? trim( (string) $book['uitgeverij'] ) : '';
			$isbn            = isset( $book['isbn'] ) ? trim( (string) $book['isbn'] ) : '';
			$aantal_paginas  = isset( $book['aantal_paginas'] ) ? trim( (string) $book['aantal_paginas'] ) : '';
			$vertaler        = isset( $book['vertaling_door'] ) ? trim( (string) $book['vertaling_door'] ) : '';
			//$vrije_regel     = isset( $book['vrije_regel'] ) ? trim( (string) $book['vrije_regel'] ) : '';
			$vrije_regel 	 = '';

			$formatted_price = ln_format_legacy_bibliographics_price( isset( $book['prijs'] ) ? $book['prijs'] : '' );
			$afbeelding_id   = 0;

			if ( ! empty( $book['afbeelding']['ID'] ) ) {
				$afbeelding_id = absint( $book['afbeelding']['ID'] );
			} elseif ( ! empty( $book['afbeelding']['id'] ) ) {
				$afbeelding_id = absint( $book['afbeelding']['id'] );
			}

			$afbeelding_url = isset( $book['oogst_boek_afbeelding'] ) ? trim( (string) $book['oogst_boek_afbeelding'] ) : '';

			/* FIX: nog te verwerken:
			vrije_regel ontbreekt in ACF veldgroep, dus deze leeg laten voor nu
			$book['boekinformatie'] (altijd null? )
			$book['oogst_recensie_koppeling_1']
			$book['website_auteur']
			$book['verschenen']
			$book['illustrator']
			$book['website_illustrator']
			$book['voorwoord']
			$book['leeftijdscategorie']

			*/


			?>
			<div class="besproken-boek oogst wp-block-ln-ln-bibliographics ln-legacy-bibliographics ln-legacy-bibliographics--oogst">
				<?php if ( $titel ) : ?>
					<h2 class="ln-boektitel"><?php echo esc_html( $titel ); ?></h2>
				<?php endif; ?>

				<div class="wp-block-columns ln-legacy-oogst-columns is-layout-flex">
					<div class="wp-block-column ln-legacy-oogst-columns__content" style="flex-basis:66.66%">
						<?php if ( $beschrijving ) : ?>
							<div class="oogst-beschrijving"><?php echo wp_kses_post( wpautop( $beschrijving ) ); ?></div>
						<?php endif; ?>
					</div>

					<div class="wp-block-column ln-legacy-oogst-columns__bibliographics" style="flex-basis:33.33%">
						<?php if ( $afbeelding_id ) : ?>
							<?php
							echo wp_get_attachment_image(
								$afbeelding_id,
								'medium_large',
								false,
								array(
									'class'   => 'ln-bibliographics__cover',
									'alt'     => $titel ? $titel : '',
									'loading' => 'lazy',
								)
							);
							?>
						<?php elseif ( $afbeelding_url ) : ?>
							<img
								class="ln-bibliographics__cover"
								src="<?php echo esc_url( $afbeelding_url ); ?>"
								alt="<?php echo esc_attr( $titel ? $titel : '' ); ?>"
								loading="lazy"
								style="max-width:100%;height:auto;"
							>
						<?php endif; ?>

						<?php if ( $auteur ) : ?>
							<p class="ln-auteur"><?php echo esc_html( $auteur ); ?></p>
						<?php endif; ?>

						<?php if ( $vrije_regel ) : ?>
							<p><?php echo esc_html( $vrije_regel ); ?></p>
						<?php endif; ?>

						<?php if ( $vertaler ) : ?>
							<p><?php echo esc_html__( 'Translation by:', 'literair-nederland-25' ) . ' ' . esc_html( $vertaler ); ?></p>
						<?php endif; ?>

						<?php if ( $uitgever ) : ?>
							<p><?php echo esc_html__( 'Uitgever:', 'literair-nederland-25' ) . ' ' . esc_html( $uitgever ); ?></p>
						<?php endif; ?>

						<?php if ( $isbn ) : ?>
							<p class="ln-isbn"><?php echo esc_html__( 'ISBN', 'literair-nederland-25' ) . ' ' . esc_html( $isbn ); ?></p>
						<?php endif; ?>

						<?php if ( $aantal_paginas ) : ?>
							<p class="ln-aantal-paginas"><?php echo esc_html( $aantal_paginas ) . ' pagina\'s'; ?></p>
						<?php endif; ?>

						<?php if ( $formatted_price ) : ?>
							<p class="ln-prijs"><?php echo esc_html__( 'Prijs:', 'literair-nederland-25' ) . ' ' . esc_html( $formatted_price ); ?></p>
						<?php endif; ?>
						<?php if ( $isbn ) : ?>
						<?php do_action( 'show_buy_button', $isbn ); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php
	return;
endif;
?>
</div>
