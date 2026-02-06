<?php
$boektitel = get_post_meta( get_the_ID(), 'boektitel', true );
$isbn      = get_post_meta( get_the_ID(), 'isbn', true );

if ( ! $boektitel && ! $isbn ) {
    return '';
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php if ( $boektitel ) : ?>
        <h2><?php echo wp_kses_post( $boektitel ); ?></h2>
    <?php endif; ?>
    <?php if ( $isbn ) : ?>
        <p><?php echo esc_html__( 'ISBN:', 'ln-bibliographics' ) . ' ' . esc_html( $isbn ); ?></p>
    <?php endif; ?>
</div>
<?php
