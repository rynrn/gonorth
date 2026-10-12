<?php
// Issue #92 rollback: restores only the two editorial bodies from their pre-change exports.
$base = '/home/claude/issue92/';
foreach ( array( 270, 431 ) as $id ) {
    $before = json_decode( file_get_contents( $base . 'post-' . $id . '-before.json' ), true );
    if ( ! is_array( $before ) || (int) $before['ID'] !== $id || ! isset( $before['post_content'] ) ) {
        fwrite( STDERR, "Missing snapshot for {$id}\n" );
        exit( 2 );
    }
    $result = wp_update_post( array( 'ID' => $id, 'post_content' => $before['post_content'] ), true );
    if ( is_wp_error( $result ) || (int) $result !== $id ) {
        fwrite( STDERR, "Restore failed for {$id}\n" );
        exit( 3 );
    }
    echo $id . ' restored\n';
}
