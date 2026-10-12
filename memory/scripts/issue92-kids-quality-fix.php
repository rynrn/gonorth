<?php
// Issue #92 final quality fix for the children's guide only.
$id = 270;
$expected = '3dcbd7f7c7199d36d0ef66cc43d25e7e0b79353d30cdc801ce0442a45b2004ed';
$before = get_post_field( 'post_content', $id );
if ( hash( 'sha256', $before ) !== $expected ) {
    fwrite( STDERR, "Post changed since previous QA; no write made\n" );
    exit( 2 );
}
$next = file_get_contents( '/home/claude/issue92/kids-updated.html' );
if ( ! $next || strpos( $next, 'issue92-rainy-day' ) === false ) {
    fwrite( STDERR, "Draft invalid\n" );
    exit( 3 );
}
$result = wp_update_post( array( 'ID' => $id, 'post_content' => $next ), true );
if ( is_wp_error( $result ) || (int) $result !== $id ) {
    fwrite( STDERR, "Update failed\n" );
    exit( 4 );
}
if ( get_post_field( 'post_content', $id ) !== $next ) {
    wp_update_post( array( 'ID' => $id, 'post_content' => $before ) );
    fwrite( STDERR, "Mismatch; previous content restored\n" );
    exit( 5 );
}
echo hash( 'sha256', $next ) . "\n";
