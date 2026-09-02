<?php
/**
 * Read-only integration checks for the place analytics contract.
 *
 * Run with: wp eval-file wp-content/plugins/gonorth-place-data/tests/analytics-integration.php
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$place_ids = get_posts(
	array(
		'post_type'      => 'gd_place',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$failures = array();
foreach ( $place_ids as $place_id ) {
	$context = GoNorth_Place_Analytics::get_place_context( (int) $place_id );
	if ( ! $context || (string) $place_id !== $context['place_id'] || empty( $context['place_slug'] ) || empty( $context['place_name'] ) ) {
		$failures[] = sprintf( '%d: invalid analytics context', $place_id );
	}

	$faq      = GoNorth_Place_SEO::get_faq_items( (int) $place_id );
	$faq_ids  = array_column( $faq, 'id' );
	$topics   = array_column( $faq, 'topic' );
	if ( ! $faq || count( $faq_ids ) !== count( array_unique( $faq_ids ) ) || in_array( '', $faq_ids, true ) || in_array( '', $topics, true ) ) {
		$failures[] = sprintf( '%d: invalid FAQ analytics identifiers', $place_id );
	}
}

if ( $failures ) {
	WP_CLI::error( implode( "\n", $failures ) );
}

WP_CLI::success( sprintf( 'Analytics context and stable FAQ identifiers passed for %d places.', count( $place_ids ) ) );
