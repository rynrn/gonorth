<?php
$ids = get_posts( array(
	'post_type' => 'gd_place',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'orderby' => 'ID',
	'order' => 'ASC',
	'fields' => 'ids',
) );
$fields = array( 'address', 'city', 'latitude', 'longitude', 'phone', 'website', 'business_hours', 'price_type', 'practical_checked_at', 'google_place_id', 'google_rating', 'google_review_count', 'google_checked_at', 'business_status' );
$places = array();
foreach ( $ids as $id ) {
	$data = GoNorth_Place_Data::get( (int) $id );
	$row = array(
		'id' => (int) $id,
		'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
		'url' => get_permalink( $id ),
	);
	foreach ( $fields as $field ) {
		$row[ $field ] = isset( $data[ $field ] ) ? $data[ $field ] : '';
	}
	$places[] = $row;
}
echo wp_json_encode( array( 'generated_at_utc' => gmdate( 'c' ), 'total' => count( $places ), 'places' => $places ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
