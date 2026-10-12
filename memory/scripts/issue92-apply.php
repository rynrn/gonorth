<?php
// Issue #92: scoped editorial update with optimistic lock and rollback on failure.
$base = '/home/claude/issue92/';
$targets = array(
    270 => array(
        'expected' => '72ec8f1d0d180a5343427e9b2ca2050aeadabb9e44032583de12eabaaa75fd69',
        'draft' => 'kids-updated.html',
        'before' => 'post-270-before.json',
    ),
    431 => array(
        'expected' => '28885a97d29b36a27a59e7857dcb052f101b4d573be53be6326cddd644a503d2',
        'draft' => 'villas-updated.html',
        'before' => 'post-431-before.json',
    ),
);
$originals = array();
foreach ( $targets as $id => $target ) {
    $post = get_post( $id );
    if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
        fwrite( STDERR, "Invalid target {$id}\n" );
        exit( 2 );
    }
    if ( hash( 'sha256', $post->post_content ) !== $target['expected'] ) {
        fwrite( STDERR, "Content changed since export for {$id}; no write made\n" );
        exit( 3 );
    }
    $before = json_decode( file_get_contents( $base . $target['before'] ), true );
    $draft = file_get_contents( $base . $target['draft'] );
    if ( ! is_array( $before ) || (int) $before['ID'] !== $id || $before['post_content'] !== $post->post_content || ! $draft ) {
        fwrite( STDERR, "Snapshot or draft invalid for {$id}; no write made\n" );
        exit( 4 );
    }
    $originals[ $id ] = $post->post_content;
}
$written = array();
foreach ( $targets as $id => $target ) {
    $draft = file_get_contents( $base . $target['draft'] );
    $result = wp_update_post( array( 'ID' => $id, 'post_content' => $draft ), true );
    if ( is_wp_error( $result ) || (int) $result !== $id ) {
        foreach ( $written as $restore_id ) {
            wp_update_post( array( 'ID' => $restore_id, 'post_content' => $originals[ $restore_id ] ) );
        }
        fwrite( STDERR, "Update failed for {$id}; earlier updates restored\n" );
        exit( 5 );
    }
    $written[] = $id;
}
foreach ( $written as $id ) {
    $expected_new = file_get_contents( $base . $targets[ $id ]['draft'] );
    if ( get_post_field( 'post_content', $id ) !== $expected_new ) {
        fwrite( STDERR, "Post-write mismatch for {$id}; use restore script\n" );
        exit( 6 );
    }
    echo $id . ' ' . hash( 'sha256', $expected_new ) . "\n";
}
