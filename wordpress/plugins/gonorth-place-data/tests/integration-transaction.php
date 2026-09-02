<?php
/**
 * Transactional smoke test for the complete structured-data contract.
 *
 * Run with:
 * wp eval-file wp-content/plugins/gonorth-place-data/tests/integration-transaction.php
 *
 * The transaction is always rolled back; no sample facts remain in production.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$post_id = 86;
$before  = GoNorth_Place_Data::get( $post_id );
$wpdb->query( 'START TRANSACTION' );

try {
	$result = GoNorth_Place_Data::update(
		$post_id,
		array(
			'phone'               => '04-0000000',
			'website'             => 'https://example.com/%D7%91%D7%93%D7%99%D7%A7%D7%94/',
			'business_hours'      => '["Mo 09:00-17:00","Sa 10:00-14:00"]',
			'price_type'          => 'free',
			'open_saturday'       => 'yes',
			'google_place_id'     => 'transaction-test',
			'google_rating'       => 4.8,
			'google_review_count' => 123,
		),
		true,
		true
	);
	if ( is_wp_error( $result ) ) {
		throw new RuntimeException( $result->get_error_message() );
	}

	$stored = GoNorth_Place_Data::get( $post_id );
	$hours  = GoNorth_Place_Data::format_business_hours( (string) $stored['business_hours'] );
	$checks = array(
		'phone'               => '04-0000000' === $stored['phone'],
		'percent-encoded URL' => 'https://example.com/%D7%91%D7%93%D7%99%D7%A7%D7%94/' === $stored['website'],
		'price type'          => 'free' === $stored['price_type'],
		'open Saturday'       => 'yes' === $stored['open_saturday'],
		'per-day hours'       => 7 === count( $hours ) && 'סגור' !== $hours['יום שני'],
		'Google Place ID'     => 'transaction-test' === $stored['google']['place_id'],
		'Google rating'       => 4.8 === (float) $stored['google']['rating'],
		'Google review count' => 123 === (int) $stored['google']['review_count'],
		'Google freshness'    => ! empty( $stored['google']['checked_at'] ),
		'practical freshness' => ! empty( $stored['practical_checked_at'] ),
		'theme phone alias'   => '04-0000000' === get_post_meta( $post_id, 'geodir_post_phone', true ),
		'theme Google alias'  => 'transaction-test' === get_post_meta( $post_id, 'google_place_id', true ),
	);

	foreach ( $checks as $label => $passed ) {
		if ( ! $passed ) {
			throw new RuntimeException( 'Failed check: ' . $label );
		}
	}

	echo 'PASS: full structured-data contract stored and read successfully.' . PHP_EOL;
} finally {
	$wpdb->query( 'ROLLBACK' );
	wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
	$after = GoNorth_Place_Data::get( $post_id );
	if ( $before !== $after ) {
		throw new RuntimeException( 'Rollback verification failed.' );
	}
	echo 'PASS: transaction rolled back; production data is unchanged.' . PHP_EOL;
}
