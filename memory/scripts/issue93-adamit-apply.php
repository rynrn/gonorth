<?php
$id = 556;
$path = '/home/claude/issue93-adamit-content.html';
$expected_hash = '4d10f2e814a829fe59f06a8e0b27d66ca6dbe9823d58ee163dd90aea20c2741b';
$post = get_post( $id );
if ( ! $post || $post->post_type !== 'gd_place' || $post->post_status !== 'pending' || $post->post_title !== 'פארק אדמית ומערת קשת' || $post->post_content !== '' || $post->post_excerpt !== '' ) {
    fwrite( STDERR, "Unexpected post state\n" );
    exit( 2 );
}
if ( get_post_meta( $id, '_gonorth_hide_driving_navigation', true ) !== '1' ) {
    fwrite( STDERR, "Navigation flag missing\n" );
    exit( 2 );
}
if ( ! is_file( $path ) || hash_file( 'sha256', $path ) !== $expected_hash ) {
    fwrite( STDERR, "Content hash mismatch\n" );
    exit( 2 );
}
$body = file_get_contents( $path );
$excerpt = 'פארק אדמית ומערת קשת סגורים לציבור לפי הודעת קק״ל מ־04.10.2026 עקב עבודות תשתית. אין להגיע עד אישור פתיחה עדכני מהמפעיל.';
$result = wp_update_post( array( 'ID' => $id, 'post_content' => $body, 'post_excerpt' => $excerpt ), true );
if ( is_wp_error( $result ) ) {
    fwrite( STDERR, "Post update failed\n" );
    exit( 3 );
}
$categories = wp_set_object_terms( $id, array( 17, 35 ), 'gd_placecategory', false );
$region = wp_set_object_terms( $id, array( 28 ), 'gn_region', false );
if ( is_wp_error( $categories ) || is_wp_error( $region ) ) {
    fwrite( STDERR, "Term update failed; inspect canonical record\n" );
    exit( 4 );
}
echo json_encode( array( 'id' => $id, 'status' => get_post_status( $id ), 'categories' => $categories, 'region' => $region ), JSON_UNESCAPED_UNICODE ) . PHP_EOL;


