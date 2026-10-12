<?php
/**
 * End-to-end audit of every published place title.
 *
 * Usage: wp eval-file tests/seo-title-audit.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$ids = get_posts(
	array(
		'post_type'      => 'gd_place',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);
$failures = array();
$titles   = array();
$lengths  = array();

foreach ( $ids as $index => $post_id ) {
	$post_id  = (int) $post_id;
	$expected = GoNorth_Place_SEO::get_place_seo_title( $post_id );
	$location = GoNorth_Place_SEO::get_place_location_label( $post_id );
	$url      = add_query_arg( 'issue27_audit', '1', get_permalink( $post_id ) );
	$response = wp_remote_get( $url, array( 'timeout' => 20 ) );
	if ( is_wp_error( $response ) ) {
		$failures[] = sprintf( '%d HTTP error: %s', $post_id, $response->get_error_message() );
		continue;
	}
	$status = wp_remote_retrieve_response_code( $response );
	$html   = (string) wp_remote_retrieve_body( $response );
	if ( 200 !== $status ) {
		$failures[] = sprintf( '%d returned HTTP %d.', $post_id, $status );
		continue;
	}

	preg_match( '~<title>(.*?)</title>~is', $html, $title_match );
	preg_match( '~<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']*)~is', $html, $og_match );
	preg_match( '~<meta[^>]+name=["\']twitter:title["\'][^>]+content=["\']([^"\']*)~is', $html, $twitter_match );
	preg_match_all( '~<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>~is', $html, $schema_matches );
	$title = trim( html_entity_decode( wp_strip_all_tags( $title_match[1] ?? '' ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$og    = trim( html_entity_decode( $og_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$twitter = trim( html_entity_decode( $twitter_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( $title !== $expected ) {
		$failures[] = sprintf( '%d title mismatch: expected "%s", found "%s".', $post_id, $expected, $title );
	}
	if ( $og !== $expected ) {
		$failures[] = sprintf( '%d Open Graph title mismatch: expected "%s", found "%s".', $post_id, $expected, $og );
	}
	if ( $twitter !== $expected ) {
		$failures[] = sprintf( '%d Twitter title mismatch: expected "%s", found "%s".', $post_id, $expected, $twitter );
	}
	$webpage_names = array();
	$walk_schema   = static function ( $value ) use ( &$walk_schema, &$webpage_names ): void {
		if ( ! is_array( $value ) ) {
			return;
		}
		$types = isset( $value['@type'] ) ? (array) $value['@type'] : array();
		if ( in_array( 'WebPage', $types, true ) ) {
			$webpage_names[] = trim( (string) ( $value['name'] ?? '' ) );
		}
		foreach ( $value as $child ) {
			$walk_schema( $child );
		}
	};
	foreach ( $schema_matches[1] as $raw_schema ) {
		$decoded = json_decode( html_entity_decode( $raw_schema, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
		if ( is_array( $decoded ) ) {
			$walk_schema( $decoded );
		}
	}
	if ( ! in_array( $expected, $webpage_names, true ) ) {
		$failures[] = sprintf( '%d WebPage schema name is missing the expected title "%s".', $post_id, $expected );
	}
	if ( false === mb_strpos( $title, 'כיוון צפון', 0, 'UTF-8' ) ) {
		$failures[] = sprintf( '%d title is missing the brand.', $post_id );
	}
	$name_key = preg_replace( '/[^\p{L}\p{N}]+/u', '', mb_strtolower( get_the_title( $post_id ), 'UTF-8' ) );
	$location_key = preg_replace( '/[^\p{L}\p{N}]+/u', '', mb_strtolower( $location, 'UTF-8' ) );
	$title_key = preg_replace( '/[^\p{L}\p{N}]+/u', '', mb_strtolower( $title, 'UTF-8' ) );
	if ( $location_key && false === mb_strpos( $title_key, $location_key, 0, 'UTF-8' ) && false === mb_strpos( $name_key, $location_key, 0, 'UTF-8' ) ) {
		$failures[] = sprintf( '%d title is missing location "%s".', $post_id, $location );
	}
	$length = mb_strlen( $title, 'UTF-8' );
	if ( $length > 60 ) {
		$failures[] = sprintf( '%d title is %d characters.', $post_id, $length );
	}
	if ( isset( $titles[ $title ] ) ) {
		$failures[] = sprintf( '%d duplicates title used by %d: "%s".', $post_id, $titles[ $title ], $title );
	}
	$titles[ $title ] = $post_id;
	$lengths[]        = $length;

	if ( 0 === ( $index + 1 ) % 20 ) {
		WP_CLI::log( sprintf( 'Checked %d/%d public titles.', $index + 1, count( $ids ) ) );
	}
}

$summary = array(
	'published'      => count( $ids ),
	'checked'        => count( $lengths ),
	'unique'         => count( $titles ),
	'min_length'     => $lengths ? min( $lengths ) : 0,
	'max_length'     => $lengths ? max( $lengths ) : 0,
	'average_length' => $lengths ? round( array_sum( $lengths ) / count( $lengths ), 1 ) : 0,
	'failures'       => count( $failures ),
);
WP_CLI::log( wp_json_encode( $summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
foreach ( $failures as $failure ) {
	WP_CLI::warning( $failure );
}
if ( $failures || 109 !== count( $ids ) || 109 !== count( $titles ) ) {
	WP_CLI::error( 'Place title audit failed.' );
}
WP_CLI::success( 'All published place titles are local, branded, unique and at most 60 characters.' );
