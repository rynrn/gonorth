<?php
/**
 * End-to-end audit of canonical images in every published place schema.
 *
 * Usage: wp eval-file tests/schema-image-audit.php
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
$checked  = 0;

foreach ( $ids as $index => $post_id ) {
	$post_id = (int) $post_id;
	$image   = GoNorth_Place_SEO::get_place_image_data( $post_id );
	if ( ! $image ) {
		$failures[] = sprintf( '%d has no canonical featured image.', $post_id );
		continue;
	}
	$response = wp_remote_get( add_query_arg( 'issue32_audit', '1', get_permalink( $post_id ) ), array( 'timeout' => 20 ) );
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
	if ( false !== stripos( $html, 's.wordpress.com/mshots' ) || false !== stripos( $html, 'website screenshot' ) ) {
		$failures[] = sprintf( '%d still exposes an mShots image.', $post_id );
	}

	preg_match( '~<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']*)~is', $html, $og_match );
	preg_match( '~<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']*)~is', $html, $twitter_match );
	$og      = html_entity_decode( $og_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$twitter = html_entity_decode( $twitter_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	if ( $og !== $image['url'] || $twitter !== $image['url'] ) {
		$failures[] = sprintf( '%d social image does not match the canonical image.', $post_id );
	}

	preg_match_all( '~<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>~is', $html, $schema_matches );
	$place_images = array();
	$image_nodes  = array();
	$walk = static function ( $value ) use ( &$walk, &$place_images, &$image_nodes ): void {
		if ( ! is_array( $value ) ) {
			return;
		}
		$types = isset( $value['@type'] ) ? (array) $value['@type'] : array();
		if ( array_intersect( $types, array( 'LocalBusiness', 'LodgingBusiness', 'Restaurant', 'CafeOrCoffeeShop', 'Winery', 'TouristAttraction' ) ) && isset( $value['image'] ) ) {
			$place_images[] = $value['image'];
		}
		if ( in_array( 'ImageObject', $types, true ) ) {
			$image_nodes[] = $value;
		}
		foreach ( $value as $child ) {
			$walk( $child );
		}
	};
	foreach ( $schema_matches[1] as $raw_schema ) {
		$decoded = json_decode( html_entity_decode( $raw_schema, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
		if ( is_array( $decoded ) ) {
			$walk( $decoded );
		}
	}
	if ( 1 !== count( $place_images ) || array( '@id' => $image['id'] ) !== reset( $place_images ) ) {
		$failures[] = sprintf( '%d place graph does not contain exactly one canonical image reference.', $post_id );
	}
	$primary_nodes = array_values( array_filter( $image_nodes, static fn( $node ) => ( $node['@id'] ?? '' ) === $image['id'] ) );
	if ( 1 !== count( $primary_nodes ) ) {
		$failures[] = sprintf( '%d has %d primary ImageObject nodes.', $post_id, count( $primary_nodes ) );
	} else {
		$node = reset( $primary_nodes );
		if ( ( $node['url'] ?? '' ) !== $image['url'] || ( $node['contentUrl'] ?? '' ) !== $image['url'] ) {
			$failures[] = sprintf( '%d ImageObject URL mismatch.', $post_id );
		}
		if ( (int) ( $node['width'] ?? 0 ) !== $image['width'] || (int) ( $node['height'] ?? 0 ) !== $image['height'] ) {
			$failures[] = sprintf( '%d ImageObject dimensions mismatch.', $post_id );
		}
		if ( $image['caption'] && ( $node['caption'] ?? '' ) !== $image['caption'] ) {
			$failures[] = sprintf( '%d ImageObject caption mismatch.', $post_id );
		}
		if ( $image['alt'] && ( $node['name'] ?? '' ) !== $image['alt'] ) {
			$failures[] = sprintf( '%d ImageObject alt/name mismatch.', $post_id );
		}
	}
	++$checked;

	if ( 0 === ( $index + 1 ) % 20 ) {
		WP_CLI::log( sprintf( 'Checked %d/%d public schemas.', $index + 1, count( $ids ) ) );
	}
}

$summary = array(
	'published' => count( $ids ),
	'checked'   => $checked,
	'failures'  => count( $failures ),
);
WP_CLI::log( wp_json_encode( $summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
foreach ( $failures as $failure ) {
	WP_CLI::warning( $failure );
}
if ( $failures || 109 !== count( $ids ) || 109 !== $checked ) {
	WP_CLI::error( 'Canonical schema image audit failed.' );
}
WP_CLI::success( 'All published place schemas use one canonical featured image without mShots.' );
