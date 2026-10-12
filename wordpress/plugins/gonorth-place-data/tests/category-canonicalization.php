<?php
/**
 * Smoke tests for legacy GeoDirectory category redirects.
 *
 * Run with:
 * wp eval-file wp-content/plugins/gonorth-place-data/tests/category-canonicalization.php
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checks = array(
	'places/category/atraktziot'        => home_url( '/places/category/atraktziot-main/' ),
	'/places/category/restaurants/'     => home_url( '/places/category/restaurants-sub/' ),
	'places/category/agalot-kafe-2'     => home_url( '/places/category/agalot-kafe/' ),
	'places/category/atraktziot/page/2' => home_url( '/places/category/atraktziot-main/page/2/' ),
);

foreach ( $checks as $request_path => $expected ) {
	$actual = GoNorth_Place_SEO::get_legacy_category_redirect_url( $request_path );
	if ( $expected !== $actual ) {
		throw new RuntimeException( sprintf( 'Unexpected redirect for %s: %s', $request_path, $actual ) );
	}
}

$canonical_paths = array(
	'places/category/atraktziot-main',
	'places/category/restaurants-sub',
	'places/category/agalot-kafe',
	'places/category/yakavim',
);

foreach ( $canonical_paths as $request_path ) {
	if ( '' !== GoNorth_Place_SEO::get_legacy_category_redirect_url( $request_path ) ) {
		throw new RuntimeException( sprintf( 'Canonical path was redirected: %s', $request_path ) );
	}
}

$empty_term = get_term_by( 'slug', 'ochel', 'gd_placecategory' );
if ( $empty_term ) {
	$original_query = $GLOBALS['wp_query'];
	$GLOBALS['wp_query'] = new WP_Query();
	$GLOBALS['wp_query']->is_tax = true;
	$GLOBALS['wp_query']->queried_object = $empty_term;
	$robots = GoNorth_Place_SEO::filter_empty_category_robots( array( 'index' => 'index', 'follow' => 'follow' ) );
	$GLOBALS['wp_query'] = $original_query;

	if ( 'noindex' !== ( $robots['index'] ?? '' ) ) {
		throw new RuntimeException( 'Empty place category was not marked noindex.' );
	}
}

echo 'PASS: legacy category redirects resolve only retired paths.' . PHP_EOL;
