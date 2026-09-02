<?php
/**
 * Transactional preview for a complete seven-day business-hours schedule.
 *
 * Run with:
 * wp eval-file wp-content/plugins/gonorth-place-data/tests/hours-preview.php
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$post_id = 87;
$before  = GoNorth_Place_Data::get( $post_id );
$hours   = '["Mo 09:00-20:00","Tu 09:00-20:00","We 09:00-20:00","Th 09:00-20:00","Fr 09:00-15:00","Sa 09:00-20:00","Su 09:00-20:00"]';

$wpdb->query( 'START TRANSACTION' );

try {
	$result = GoNorth_Place_Data::update( $post_id, array( 'business_hours' => $hours ), true );
	if ( is_wp_error( $result ) ) {
		throw new RuntimeException( $result->get_error_message() );
	}

	$stored    = GoNorth_Place_Data::get( $post_id );
	$formatted = GoNorth_Place_Data::format_business_hours( (string) $stored['business_hours'] );
	$expected  = array(
		'יום שני'   => '09:00 - 20:00',
		'יום שלישי' => '09:00 - 20:00',
		'יום רביעי' => '09:00 - 20:00',
		'יום חמישי' => '09:00 - 20:00',
		'יום שישי'  => '09:00 - 15:00',
		'שבת'       => '09:00 - 20:00',
		'יום ראשון' => '09:00 - 20:00',
	);

	if ( $expected !== $formatted ) {
		throw new RuntimeException( 'Formatted hours do not match the expected seven-day schedule: ' . wp_json_encode( $formatted, JSON_UNESCAPED_UNICODE ) );
	}
	if ( empty( $stored['practical_checked_at'] ) ) {
		throw new RuntimeException( 'Verified practical timestamp was not stored.' );
	}

	echo 'PASS: seven-day opening-hours preview renders correctly.' . PHP_EOL;
	foreach ( $formatted as $day => $range ) {
		echo $day . ': ' . $range . PHP_EOL;
	}
} finally {
	$wpdb->query( 'ROLLBACK' );
	wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
	if ( $before !== GoNorth_Place_Data::get( $post_id ) ) {
		throw new RuntimeException( 'Rollback verification failed.' );
	}
	echo 'PASS: transaction rolled back; production data is unchanged.' . PHP_EOL;
}
