<?php
$id = 554;
$path = '/home/claude/issue93-nahal-content.html';
$expected_hash = '7ac478d8c7a44637a4c51046fd30f35bbb207c65d2d375937269bc8d5b4563d2';
$post = get_post( $id );
if ( ! $post || $post->post_type !== 'gd_place' || $post->post_status !== 'pending' || $post->post_title !== 'שמורת טבע נחל המערות' || $post->post_content !== '' || $post->post_excerpt !== '' ) {
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
$excerpt = 'שמורת טבע ומורשת בחוף הכרמל עם מערות האדם הקדמון. הכניסה מכביש 4 בין גבע כרמל לעין כרמל; בדקו מראש הוראות הגעה והודעות מסלול.';
$result = wp_update_post( array( 'ID' => $id, 'post_content' => $body, 'post_excerpt' => $excerpt ), true );
if ( is_wp_error( $result ) ) {
    fwrite( STDERR, "Post update failed\n" );
    exit( 3 );
}
$categories = wp_set_object_terms( $id, array( 17, 35 ), 'gd_placecategory', false );
$region = wp_set_object_terms( $id, array( 30 ), 'gn_region', false );
if ( is_wp_error( $categories ) || is_wp_error( $region ) ) {
    fwrite( STDERR, "Term update failed; inspect canonical record\n" );
    exit( 4 );
}
echo json_encode( array( 'id' => $id, 'status' => get_post_status( $id ), 'categories' => $categories, 'region' => $region ), JSON_UNESCAPED_UNICODE ) . PHP_EOL;

