<?php
/**
 * Transactional integration test for the tri-state attribute model.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$post_id      = 86;
$before_terms = wp_get_object_terms( $post_id, GoNorth_Place_Attributes::TAXONOMY, array( 'fields' => 'slugs' ) );
$before_false = get_post_meta( $post_id, GoNorth_Place_Attributes::FALSE_META, true );
$before_false = is_array( $before_false ) ? $before_false : array();
$before_data  = GoNorth_Place_Data::get( $post_id );

$wpdb->query( 'START TRANSACTION' );

try {
	$changes = array(
		'accessible'    => 'yes',
		'children'      => 'yes',
		'dogs_allowed'  => 'no',
		'shaded'        => 'unknown',
		'free'          => 'yes',
		'open_saturday' => 'yes',
	);
	foreach ( $changes as $slug => $state ) {
		$result = GoNorth_Place_Attributes::set_state( $post_id, $slug, $state );
		if ( is_wp_error( $result ) ) {
			throw new RuntimeException( $result->get_error_message() );
		}
	}

	$query = GoNorth_Place_Attributes::query(
		array( 'accessible', 'children' ),
		array( 'post__in' => array( $post_id ), 'posts_per_page' => -1 )
	);
	$data  = GoNorth_Place_Data::get( $post_id );
	$checks = array(
		'positive state'       => 'yes' === GoNorth_Place_Attributes::get_state( $post_id, 'accessible' ),
		'negative state'       => 'no' === GoNorth_Place_Attributes::get_state( $post_id, 'dogs_allowed' ),
		'unknown state'        => 'unknown' === GoNorth_Place_Attributes::get_state( $post_id, 'shaded' ),
		'no contradiction'     => ! has_term( 'dogs_allowed', GoNorth_Place_Attributes::TAXONOMY, $post_id ),
		'multi-attribute query'=> ! is_wp_error( $query ) && 1 === count( $query->posts ),
		'free synchronization' => 'free' === $data['price_type'],
		'Saturday sync'        => 'yes' === $data['open_saturday'],
		'chip labels'          => isset( GoNorth_Place_Attributes::get_positive( $post_id )['accessible'] ),
	);
	foreach ( $checks as $label => $passed ) {
		if ( ! $passed ) {
			throw new RuntimeException( 'Failed check: ' . $label );
		}
	}
	echo 'PASS: tri-state attributes, synchronization and AND query work.' . PHP_EOL;
} finally {
	$wpdb->query( 'ROLLBACK' );
	clean_object_term_cache( $post_id, 'gd_place' );
	wp_cache_delete( $post_id, 'post_meta' );
	wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
	$after_terms = wp_get_object_terms( $post_id, GoNorth_Place_Attributes::TAXONOMY, array( 'fields' => 'slugs' ) );
	$after_false = get_post_meta( $post_id, GoNorth_Place_Attributes::FALSE_META, true );
	$after_false = is_array( $after_false ) ? $after_false : array();
	$after_data  = GoNorth_Place_Data::get( $post_id );
	sort( $before_terms );
	sort( $after_terms );
	if ( $before_terms !== $after_terms || $before_false !== $after_false || $before_data !== $after_data ) {
		throw new RuntimeException( 'Rollback verification failed.' );
	}
	echo 'PASS: transaction rolled back; production assignments are unchanged.' . PHP_EOL;
}
