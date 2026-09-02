<?php
/**
 * Read-only audit for contextual trip-planning recommendations.
 *
 * Usage: wp eval-file tests/trip-planner-audit.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$rules = GoNorth_Trip_Planner::rules();
if ( count( $rules ) < 3 ) {
	WP_CLI::error( 'Fewer than three contextual recommendation groups are configured.' );
}

$sample_ids = array( 50, 86, 385 );
foreach ( $sample_ids as $post_id ) {
	$groups    = GoNorth_Trip_Planner::get_groups( $post_id );
	$seen      = array();
	$summary   = array();
	if ( ! $groups ) {
		WP_CLI::error( sprintf( '%d: no contextual groups returned.', $post_id ) );
	}

	foreach ( $groups as $key => $group ) {
		if ( empty( $rules[ $key ] ) || empty( $group['places'] ) ) {
			WP_CLI::error( sprintf( '%d: invalid or empty group %s.', $post_id, $key ) );
		}
		foreach ( $group['places'] as $place ) {
			if ( $post_id === $place['id'] ) {
				WP_CLI::error( sprintf( '%d: current place was recommended.', $post_id ) );
			}
			if ( isset( $seen[ $place['id'] ] ) ) {
				WP_CLI::error( sprintf( '%d: place %d appears in multiple groups.', $post_id, $place['id'] ) );
			}
			if ( $place['distance_km'] > (float) $rules[ $key ]['radius_km'] ) {
				WP_CLI::error( sprintf( '%d: place %d exceeds the %s radius.', $post_id, $place['id'], $key ) );
			}
			if ( 0 !== strpos( $place['url'], home_url( '/' ) ) ) {
				WP_CLI::error( sprintf( '%d: place %d is not an internal link.', $post_id, $place['id'] ) );
			}
			$seen[ $place['id'] ] = true;
		}
		$distances       = wp_list_pluck( $group['places'], 'distance_km' );
		$summary[ $key ] = sprintf( '%d results, %.2f–%.2f km', count( $group['places'] ), min( $distances ), max( $distances ) );
	}

	WP_CLI::log(
		sprintf(
			'%d (%s): %s',
			$post_id,
			wp_strip_all_tags( get_the_title( $post_id ) ),
			wp_json_encode( $summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		)
	);
}

$all_ids       = get_posts( array( 'post_type' => 'gd_place', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$with_groups   = 0;
$group_counts  = array();
$duplicate_ids = array();
foreach ( $all_ids as $post_id ) {
	$groups = GoNorth_Trip_Planner::get_groups( $post_id );
	if ( $groups ) {
		++$with_groups;
	}
	$seen = array();
	foreach ( $groups as $key => $group ) {
		$group_counts[ $key ] = ( $group_counts[ $key ] ?? 0 ) + 1;
		foreach ( $group['places'] as $place ) {
			if ( isset( $seen[ $place['id'] ] ) ) {
				$duplicate_ids[] = $post_id;
			}
			$seen[ $place['id'] ] = true;
		}
	}
}

if ( $duplicate_ids ) {
	WP_CLI::error( 'Cross-group duplicates found on: ' . implode( ', ', array_unique( $duplicate_ids ) ) );
}

WP_CLI::success(
	sprintf(
		'Audited %d places; %d have contextual groups. Group coverage: %s',
		count( $all_ids ),
		$with_groups,
		wp_json_encode( $group_counts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
	)
);
