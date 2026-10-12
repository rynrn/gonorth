<?php
if ( count( $args ) !== 1 ) { fwrite( STDERR, "Usage: expected_sha256\n" ); exit( 2 ); }
$id = 439;
$post = get_post( $id );
if ( ! $post || 'page' !== $post->post_type || 'draft' !== $post->post_status ) { fwrite( STDERR, "Unexpected post state\n" ); exit( 3 ); }
if ( hash( 'sha256', $post->post_content ) !== strtolower( $args[0] ) ) { fwrite( STDERR, "Concurrent content change\n" ); exit( 4 ); }
if ( 'open_saturday' !== get_post_meta( $id, '_gonorth_attribute_landing', true ) ) { fwrite( STDERR, "Unexpected landing profile\n" ); exit( 5 ); }
if ( ! class_exists( 'GoNorth_Attribute_Landing_Pages' ) || count( GoNorth_Attribute_Landing_Pages::get_verified_places( 'open_saturday' ) ) < 12 ) { fwrite( STDERR, "Verified inventory below threshold\n" ); exit( 6 ); }
$result = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
if ( is_wp_error( $result ) ) { fwrite( STDERR, $result->get_error_message() . "\n" ); exit( 7 ); }
update_post_meta( $id, '_yoast_wpseo_title', 'מקומות פתוחים בשבת בצפון | כיוון צפון' );
update_post_meta( $id, '_yoast_wpseo_metadesc', 'מצאו מקומות פתוחים בשבת בצפון מתוך מידע מעשי מאומת: אטרקציות, טבע, אוכל ופעילויות בגליל ובגולן, עם קישורים ישירים ותאריך בדיקה.' );
update_post_meta( $id, '_yoast_wpseo_focuskw', 'מקומות פתוחים בשבת בצפון' );
clean_post_cache( $id );
$saved = get_post( $id );
if ( ! $saved || 'publish' !== $saved->post_status || 'open-saturday' !== $saved->post_name ) { fwrite( STDERR, "Post-write verification failed\n" ); exit( 8 ); }
echo wp_json_encode( array( 'id' => $id, 'status' => $saved->post_status, 'url' => get_permalink( $id ), 'verified_places' => count( GoNorth_Attribute_Landing_Pages::get_verified_places( 'open_saturday' ) ), 'verified' => true ) );