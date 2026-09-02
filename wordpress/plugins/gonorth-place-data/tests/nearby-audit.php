<?php
/**
 * Read-only WP-CLI audit for nearby-place recommendations.
 *
 * Usage: wp eval-file tests/nearby-audit.php
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$sample_ids = array( 50, 86, 149 );
foreach ( $sample_ids as $sample_id ) {
	$results = GoNorth_Nearby_Places::get_nearby_places( $sample_id );
	if ( ! $results ) {
		WP_CLI::error( sprintf( 'Place %d returned no nearby results.', $sample_id ) );
	}
	$distances = array_column( $results, 'distance_km' );
	$ids       = array_column( $results, 'id' );
	if ( in_array( $sample_id, $ids, true ) ) {
		WP_CLI::error( sprintf( 'Place %d included itself.', $sample_id ) );
	}
	$sorted = $distances;
	sort( $sorted, SORT_NUMERIC );
	if ( $sorted !== $distances ) {
		WP_CLI::error( sprintf( 'Place %d results are not ordered by distance.', $sample_id ) );
	}
	WP_CLI::log( sprintf( '%d: %d results, %.2f–%.2f km', $sample_id, count( $results ), min( $distances ), max( $distances ) ) );
}

global $wpdb;
$detail_table = $wpdb->prefix . 'geodir_gd_place_detail';
$geocoded_ids = $wpdb->get_col(
	"SELECT p.ID
	FROM {$wpdb->posts} p
	INNER JOIN {$detail_table} d ON d.post_id = p.ID
	WHERE p.post_type = 'gd_place'
		AND p.post_status = 'publish'
		AND d.latitude IS NOT NULL AND d.latitude <> ''
		AND d.longitude IS NOT NULL AND d.longitude <> ''"
);

$without_results = array();
$invalid_results = array();
foreach ( $geocoded_ids as $geocoded_id ) {
	$geocoded_id = (int) $geocoded_id;
	$results = GoNorth_Nearby_Places::get_nearby_places( $geocoded_id );
	if ( ! $results ) {
		$without_results[] = (int) $geocoded_id;
		continue;
	}
	$result_ids = array_column( $results, 'id' );
	if ( count( $results ) > 6 || count( $result_ids ) !== count( array_unique( $result_ids ) ) || in_array( $geocoded_id, $result_ids, true ) ) {
		$invalid_results[] = $geocoded_id;
		continue;
	}
	foreach ( $results as $result ) {
		if ( $result['distance_km'] < 0 || $result['distance_km'] > 50 || ! $result['url'] || ! $result['category'] || 'publish' !== get_post_status( $result['id'] ) ) {
			$invalid_results[] = $geocoded_id;
			break;
		}
	}
}

if ( $without_results ) {
	WP_CLI::warning( 'No results within the default radius: ' . implode( ', ', $without_results ) );
}
if ( $invalid_results ) {
	WP_CLI::error( 'Invalid nearby result sets: ' . implode( ', ', array_unique( $invalid_results ) ) );
}

WP_CLI::success(
	sprintf(
		'Audited %d geocoded published places; %d have nearby results.',
		count( $geocoded_ids ),
		count( $geocoded_ids ) - count( $without_results )
	)
);
