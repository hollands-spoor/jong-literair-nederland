<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! function_exists( 'get_door_regel' ) ) {
    function get_door_regel( $post_id, $category) {
        $auteur_recensenie_id = get_post_meta( $post_id, 'auteur_recensie', true );
        $medewerker_id = get_post_meta( $post_id, 'medewerker_id', true );
        $door_intro = get_post_meta( $post_id, 'door_intro', true );
        $intro = '';
        $medewerker = '';
        if ( in_array( $category, array( 'recensies', 'recensie' ), true ) ) {
            $intro = 'Recensie door:';
            if ( $auteur_recensenie_id && $auteur_recensenie_id != '' && $auteur_recensenie_id != 0 && $auteur_recensenie_id != 'null' ) {
                $medewerker = get_the_title( $auteur_recensenie_id );
                $link = get_permalink( $auteur_recensenie_id );
                $medewerker = '<a href="' . esc_url( $link ) . '">' . esc_html( $medewerker ) . '</a>';
                return $intro . ' '. $medewerker;
            } elseif ( $medewerker_id ) {
                $medewerker = get_the_title( $medewerker_id );
                return $intro . ' '. $medewerker;
            } else {
                return '';
            }
        } elseif ( in_array( $category, array( 'oogst', 'jonge-oogst' ), true ) ) {
            $intro = 'Door:';
            if( $door_intro) {
                // $intro = $door_intro; No more door intro's for oogst, as per request of redactie.
            }
            if ( $medewerker_id ) {
                $medewerker = get_the_title( $medewerker_id );
                return $intro . ' '. $medewerker;
            } elseif ( $auteur_recensenie_id ) {
                $medewerker = get_the_title( $auteur_recensenie_id );
                return $intro . ' '. $medewerker;
            } else {
                return '';
            }
        } elseif ( 'column' === $category ) {
            // column is done elsewhere;
            return '';
        } else {
            $intro = 'Door:';
            if( $door_intro) {
                $intro = $door_intro;
            }

            if ( $medewerker_id ) {
                $medewerker = get_the_title( $medewerker_id );
                return $intro . ' ' . $medewerker;
            } elseif ( $auteur_recensenie_id ) {
                $medewerker = get_the_title( $auteur_recensenie_id );
                return $intro . ' ' . $medewerker;
            } else {
                return '';
            }
        }
    }
}

if( !function_exists( 'get_besproken_boeken')) {

// postmeta keys 'besproken_boeken_{x}_boektitel' and 'besproken_boeken_{x}_auteur_boek' are written now for legacy purposes.
// however legacy keys are besproken_boeken_{x}_titel and besproken_boeken_{x}_auteur


    function get_besproken_boeken( $post_id, $max = 1 ) {
        global $wpdb;

        $post_id = (int) $post_id;
        $max = (int) $max;

        if ( $post_id <= 0 ) {
            return [];
        }

        if ( $max < 1 ) {
            $max = 1;
        }

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value
                 FROM {$wpdb->postmeta}
                 WHERE post_id = %d
                   AND ( meta_key LIKE %s OR meta_key LIKE %s
                   )",
                $post_id,
                'besproken_boeken_%_auteur_boek',
                'besproken_boeken_%_boektitel'
            ),
            ARRAY_A
        );

        $indexed = [];
        foreach ( $rows as $row ) {
            $meta_key = (string) ( $row['meta_key'] ?? '' );
            $meta_value = maybe_unserialize( $row['meta_value'] ?? '' );

            if ( preg_match( '/^besproken_boeken_(\d+)_auteur_boek$/', $meta_key, $matches ) ) {
                $index = (int) $matches[1];
                if ( ! isset( $indexed[ $index ] ) ) {
                    $indexed[ $index ] = [
                        'index'       => $index,
                        'auteur_boek' => '',
                        'boektitel'   => '',
                    ];
                }
                $indexed[ $index ]['auteur_boek'] = (string) $meta_value;
                continue;
            }

            if ( preg_match( '/^besproken_boeken_(\d+)_boektitel$/', $meta_key, $matches ) ) {
                $index = (int) $matches[1];
                if ( ! isset( $indexed[ $index ] ) ) {
                    $indexed[ $index ] = [
                        'index'       => $index,
                        'auteur_boek' => '',
                        'boektitel'   => '',
                    ];
                }
                $indexed[ $index ]['boektitel'] = (string) $meta_value;
            }
        }

        if ( empty( $indexed ) ) {
            $legacy_auteur_boek = get_post_meta( $post_id, 'auteur_boek', true );
            $legacy_boektitel = get_post_meta( $post_id, 'boektitel', true );

            if ( $legacy_auteur_boek || $legacy_boektitel ) {
                return [
                    [
                        'index'       => 0,
                        'auteur_boek' => (string) $legacy_auteur_boek,
                        'boektitel'   => (string) $legacy_boektitel,
                    ],
                ];
            }

            // for legacy oogst berichten:
            $legacy_auteur_boek = get_post_meta( $post_id, 'besproken_boeken_0_auteur', true );
            $legacy_boektitel = get_post_meta( $post_id, 'besproken_boeken_0_titel', true );

            if ( $legacy_auteur_boek || $legacy_boektitel ) {
                return [
                    [
                        'index'       => 0,
                        'auteur_boek' => (string) $legacy_auteur_boek,
                        'boektitel'   => (string) $legacy_boektitel,
                    ],
                ];
            }


            return [];
        }

        ksort( $indexed, SORT_NUMERIC );

        $result = [];
        foreach ( $indexed as $item ) {
            if ( count( $result ) >= $max ) {
                break;
            }

            if ( ! empty( $item['auteur_boek'] ) || ! empty( $item['boektitel'] ) ) {
                $result[] = $item;
            }
        }

        return $result;
    }

}

// Helper function for ln-titel render.php
if( ! function_exists( 'get_chapeau' ) ) {

    function get_chapeau( $post_id, $category ) {
        if ( in_array( (string) $category, array( 'jonge-oogst', 'oogst' ), true ) ) {
            $option_suffix = is_single() ? 'single' : 'archive';
            $weergave = get_option('xln_options')["oogst-titel-{$option_suffix}"] ?? 'none';
            
            if ( 'custom' === $weergave ) {
                $custom_line = get_option('xln_options')["custom-line-{$option_suffix}"] ?? '';
                return $custom_line ? '<div class="ln-titel__chapeau">' . wp_kses_post( $custom_line ) . '</div>' : false;
            }   
            if( 'none' === $weergave ) {
                return false;
            }
            if( 'all' === $weergave ) {
                $besproken_boeken = get_besproken_boeken( $post_id, 10  );
            }
            if( 'first' === $weergave ) {
                $besproken_boeken = get_besproken_boeken( $post_id, 1  );
            }
             if ( empty( $besproken_boeken ) ) {
                return false;
            }
            $hs = '<div class="ln-titel__chapeau">';
            $first = true;
            foreach ( $besproken_boeken as $boek ) {
                if ( ! $first ) {
                    $hs .= ', ';
                }
                $first = false;
                if( ! empty( $boek['auteur_boek'] ) ) {
                    $hs .= esc_html( $boek['auteur_boek'] );
                }
                if( ! empty( $boek['auteur_boek'] ) && ! empty( $boek['boektitel'] ) ) {
                    $hs .= ' - ';
                }
                if( ! empty( $boek['boektitel'] ) ) {
                    $hs .= esc_html( $boek['boektitel'] );
                }
            }
            $hs .= '</div>';
            return $hs;
        } else {
            $auteur_boek = get_post_meta( $post_id, 'besproken_boeken_0_auteur_boek', true );
        // legacy fallback
            if( ! $auteur_boek ) {
                $auteur_boek = get_post_meta( $post_id, 'auteur_boek', true );
            }
            $boektitel = get_post_meta( $post_id, 'besproken_boeken_0_boektitel', true );
            // legacy fallback
            if( ! $boektitel ) {
                $boektitel = get_post_meta( $post_id, 'boektitel', true );
            }
            if( $auteur_boek || $boektitel ) {
                $hs = '<div class="ln-titel__chapeau">';
                if( $auteur_boek ) {
                    $hs .= esc_html( $auteur_boek );
                }
                if( $auteur_boek && $boektitel ) {
                    $hs .= ' - ';
                }
                if( $boektitel ) {
                    $hs .= esc_html( $boektitel );
                }
                $hs .= '</div>';
                return $hs;
            }
        }
        return false;
    }
}