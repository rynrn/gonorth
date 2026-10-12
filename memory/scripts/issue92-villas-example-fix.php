<?php
// Issue #92 final example correction for villas guide only.
$id = 431;
$expected = '7e4a7489b00c170ba736daf76e029155c3821ba556206a55f05d73cb3dc0b936';
$before = get_post_field( 'post_content', $id );
if ( hash( 'sha256', $before ) !== $expected ) {
    fwrite( STDERR, "Post changed since previous QA; no write made\n" );
    exit( 2 );
}
$next = file_get_contents( '/home/claude/issue92/villas-updated.html' );
if ( ! $next || strpos( $next, 'issue92-villas-decision' ) === false ) {
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
