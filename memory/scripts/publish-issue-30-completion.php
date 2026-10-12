<?php
$ids = array(50,61,63,88,173,177,181,195,197,202,207,212,217,244,327,329,331,335,337,339,341,343,345,347,349,353,361,373,377,397,403,407,421,425);
foreach ( $ids as $id ) {
  if ( 'publish' !== get_post_status( $id ) || 'gd_place' !== get_post_type( $id ) ) {
    throw new RuntimeException( "Unexpected post state for {$id}; refusing update." );
  }
  $data = GoNorth_Place_Data::get( $id );
  if ( ! empty( $data['practical_checked_at'] ) ) {
    throw new RuntimeException( "Verification timestamp already exists for {$id}; refusing update." );
  }
}
$out = array();
foreach ( $ids as $id ) {
  $updated = GoNorth_Place_Data::update( $id, array(), true, false );
  if ( is_wp_error( $updated ) ) {
    throw new RuntimeException( $updated->get_error_message() );
  }
  clean_post_cache( $id );
  $data = GoNorth_Place_Data::get( $id );
  $out[] = array(
    'id' => $id,
    'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
    'url' => get_permalink( $id ),
    'practical_checked_at' => $data['practical_checked_at'],
    'phone' => $data['phone'],
    'website' => $data['website'],
    'business_hours' => $data['business_hours'],
    'price_type' => $data['price_type'],
  );
}
wp_cache_flush();
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
