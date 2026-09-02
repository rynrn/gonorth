<?php
/**
 * Read-only SEO audit for representative place pages.
 *
 * Usage: wp eval-file tests/seo-audit.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$sample_ids = array( 50, 86, 149 );

foreach ( $sample_ids as $post_id ) {
	$response = wp_remote_get( get_permalink( $post_id ), array( 'timeout' => 20 ) );
	if ( is_wp_error( $response ) ) {
		WP_CLI::warning( sprintf( '%d: %s', $post_id, $response->get_error_message() ) );
		continue;
	}

	$html = wp_remote_retrieve_body( $response );
	preg_match( '~<title>(.*?)</title>~is', $html, $title_match );
	preg_match( '~<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)~is', $html, $canonical_match );
	preg_match( '~<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)~is', $html, $description_match );
	preg_match_all( '~<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>~is', $html, $schema_matches );

	$types = array();
	$nodes = array();
	$walk  = static function ( $value ) use ( &$walk, &$types, &$nodes ): void {
		if ( ! is_array( $value ) ) {
			return;
		}
		if ( isset( $value['@type'] ) ) {
			foreach ( (array) $value['@type'] as $type ) {
				$types[] = $type;
				if ( in_array( $type, array( 'WebPage', 'LocalBusiness', 'TouristAttraction', 'Restaurant', 'LodgingBusiness', 'FAQPage' ), true ) ) {
					$nodes[] = array(
						'type'         => $type,
						'id'           => $value['@id'] ?? '',
						'dateModified' => $value['dateModified'] ?? '',
						'mainEntity'   => isset( $value['mainEntity'] ) ? count( (array) $value['mainEntity'] ) : 0,
					);
				}
			}
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

	$title       = html_entity_decode( wp_strip_all_tags( $title_match[1] ?? '' ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$description = html_entity_decode( $description_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	WP_CLI::log(
		wp_json_encode(
			array(
				'id'               => $post_id,
				'status'           => wp_remote_retrieve_response_code( $response ),
				'title'            => $title,
				'title_length'     => mb_strlen( $title ),
				'description'      => $description,
				'description_length'=> mb_strlen( $description ),
				'canonical'        => html_entity_decode( $canonical_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
				'jsonld_scripts'   => count( $schema_matches[1] ),
				'visible_faq'      => substr_count( $html, 'class="gn-place-faq"' ),
				'visible_questions'=> substr_count( $html, 'class="gn-place-faq__item"' ),
				'freshness'        => substr_count( $html, 'class="gn-place-freshness"' ),
				'types'            => array_values( array_unique( $types ) ),
				'nodes'            => $nodes,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
