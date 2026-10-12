<?php
/** Deterministic checks for Google candidate matching and bundle integrity. */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$checks = array(
	'identical Hebrew name' => GoNorth_Google_Place_Sync::name_score( 'יקב הר אודם', 'יקב הר אודם' ) === 1.0,
	'descriptor tolerant'   => GoNorth_Google_Place_Sync::name_score( 'שמורת טבע החולה', 'שמורת החולה' ) >= 0.66,
	'near strong match'     => GoNorth_Google_Place_Sync::candidate_is_safe( 0.66, 1.5 ),
	'far match rejected'    => ! GoNorth_Google_Place_Sync::candidate_is_safe( 1.0, 3.0 ),
	'weak match rejected'   => ! GoNorth_Google_Place_Sync::candidate_is_safe( 0.10, 0.10 ),
	'distance identity'     => GoNorth_Google_Place_Sync::distance_km( 33.191991, 35.751795, 33.191991, 35.751795 ) < 0.001,
);

foreach ( $checks as $label => $passed ) {
	if ( ! $passed ) {
		WP_CLI::error( 'Google sync check failed: ' . $label );
	}
}


$post_id = 0;
foreach ( get_posts( array( 'post_type' => 'gd_place', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $candidate_post_id ) {
	$candidate_data = GoNorth_Place_Data::get( (int) $candidate_post_id );
	if ( empty( $candidate_data['google']['place_id'] ) && ! empty( $candidate_data['latitude'] ) && ! empty( $candidate_data['longitude'] ) ) {
		$post_id = (int) $candidate_post_id;
		break;
	}
}
if ( ! $post_id ) {
	WP_CLI::error( 'Google sync request mock requires one published place without a stored Place ID.' );
}
$before = GoNorth_Place_Data::get( $post_id );
$captured_http = array();
$mock_http     = static function ( $preempt, $args, $url ) use ( &$captured_http, $post_id, $before ) {
	unset( $preempt );
	$captured_http = array(
		'args' => $args,
		'url'  => $url,
	);
	return array(
		'headers'  => array(),
		'body'     => wp_json_encode(
			array(
				'places' => array(
					array(
						'id'               => 'new-api-test-place',
						'displayName'      => array( 'text' => get_the_title( $post_id ) ),
						'formattedAddress' => 'Mock address',
						'location'         => array(
							'latitude'  => (float) $before['latitude'],
							'longitude' => (float) $before['longitude'],
						),
						'rating'           => 4.6,
						'userRatingCount'  => 246,
						'businessStatus'   => 'OPERATIONAL',
					),
				),
			)
		),
		'response' => array( 'code' => 200, 'message' => 'OK' ),
		'cookies'  => array(),
		'filename' => null,
	);
};
add_filter( 'pre_http_request', $mock_http, 10, 3 );
try {
	$resolved = GoNorth_Google_Place_Sync::resolve( $post_id, 'unit-test-secret' );
} finally {
	remove_filter( 'pre_http_request', $mock_http, 10 );
}
if ( is_wp_error( $resolved ) ) {
	WP_CLI::error( 'Places API (New) mock failed: ' . $resolved->get_error_message() );
}
$request_body = json_decode( (string) ( $captured_http['args']['body'] ?? '' ), true );
$request_url  = (string) ( $captured_http['url'] ?? '' );
$request_mask = (string) ( $captured_http['args']['headers']['X-Goog-FieldMask'] ?? '' );
$new_api_checks = array(
	'new Text Search endpoint' => 'https://places.googleapis.com/v1/places:searchText' === $request_url,
	'POST request'             => 'POST' === ( $captured_http['args']['method'] ?? '' ),
	'key absent from URL'      => false === strpos( $request_url, 'unit-test-secret' ),
	'explicit field mask'      => false !== strpos( $request_mask, 'places.id' ) && false === strpos( $request_mask, '*' ),
	'Hebrew request'           => 'he' === ( $request_body['languageCode'] ?? '' ),
	'normalized Place ID'      => 'new-api-test-place' === ( $resolved['google_place_id'] ?? '' ),
	'normalized rating'        => 4.6 === ( $resolved['google_rating'] ?? null ),
	'normalized reviews'       => 246 === ( $resolved['google_review_count'] ?? null ),
	'normalized status'        => 'operational' === ( $resolved['business_status'] ?? '' ),
);
foreach ( $new_api_checks as $label => $passed ) {
	if ( ! $passed ) {
		WP_CLI::error( 'Places API (New) check failed: ' . $label );
	}
}

global $wpdb;
$wpdb->query( 'START TRANSACTION' );
try {
	$partial = GoNorth_Place_Data::update(
		$post_id,
		array(
			'google_place_id'     => 'partial-test',
			'google_rating'       => 4.7,
			'google_review_count' => 321,
		),
		false,
		false
	);
	if ( is_wp_error( $partial ) ) {
		throw new RuntimeException( $partial->get_error_message() );
	}
	$hidden = GoNorth_Place_Data::get( $post_id );
	if ( ! empty( $hidden['google']['complete'] ) ) {
		throw new RuntimeException( 'Partial Google bundle was exposed.' );
	}

	$complete = gonorth_store_google_place_data(
		$post_id,
		array(
			'google_place_id'     => 'complete-test',
			'google_rating'       => 4.8,
			'google_review_count' => 456,
		)
	);
	if ( is_wp_error( $complete ) ) {
		throw new RuntimeException( $complete->get_error_message() );
	}
	$visible = GoNorth_Place_Data::get( $post_id );
	if ( empty( $visible['google']['complete'] ) || 456 !== (int) $visible['google']['review_count'] ) {
		throw new RuntimeException( 'Complete verified Google bundle was not exposed.' );
	}
	$schema = GoNorth_Place_SEO::filter_place_schema(
		array( '@context' => 'https://schema.org', '@type' => 'LocalBusiness' ),
		get_post( $post_id )
	);
	if ( isset( $schema['aggregateRating'] ) ) {
		throw new RuntimeException( 'External Google ratings must not be emitted as GoNorth AggregateRating.' );
	}
} finally {
	$wpdb->query( 'ROLLBACK' );
	wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
	if ( $before !== GoNorth_Place_Data::get( $post_id ) ) {
		throw new RuntimeException( 'Google sync transaction rollback failed.' );
	}
}

WP_CLI::success( 'Google Places API (New) request, confidence and bundle-integrity checks passed.' );
