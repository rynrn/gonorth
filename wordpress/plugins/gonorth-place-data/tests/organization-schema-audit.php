<?php
/** Audit the site Organization and AboutPage graph for issue #37. */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$pages = array(
	'homepage' => home_url( '/' ),
	'about'    => home_url( '/about/' ),
);
$failures = array();

foreach ( $pages as $label => $url ) {
	$response = wp_remote_get( add_query_arg( 'issue37_audit', microtime( true ), $url ), array( 'timeout' => 25 ) );
	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$failures[] = "$label: page did not return HTTP 200";
		continue;
	}

	$html = (string) wp_remote_retrieve_body( $response );
	if ( ! preg_match( '/<script type="application\/ld\+json" class="yoast-schema-graph">(.*?)<\/script>/s', $html, $match ) ) {
		$failures[] = "$label: Yoast graph is missing";
		continue;
	}
	$schema = json_decode( html_entity_decode( $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
	$graph  = is_array( $schema['@graph'] ?? null ) ? $schema['@graph'] : array();
	if ( ! $graph || JSON_ERROR_NONE !== json_last_error() ) {
		$failures[] = "$label: graph is invalid JSON";
		continue;
	}

	$organizations = array_values(
		array_filter(
			$graph,
			static fn( $piece ) => in_array( 'Organization', (array) ( $piece['@type'] ?? array() ), true )
		)
	);
	if ( 1 !== count( $organizations ) ) {
		$failures[] = sprintf( '%s: expected one Organization, found %d', $label, count( $organizations ) );
		continue;
	}
	$organization = $organizations[0];
	$expected_id  = home_url( '/#organization' );
	foreach ( array( '@id', 'name', 'alternateName', 'url', 'logo', 'contactPoint', 'sameAs' ) as $property ) {
		if ( empty( $organization[ $property ] ) ) {
			$failures[] = "$label: Organization missing $property";
		}
	}
	if ( $expected_id !== ( $organization['@id'] ?? '' ) || 'כיוון צפון' !== ( $organization['name'] ?? '' ) || 'GoNorth' !== ( $organization['alternateName'] ?? '' ) ) {
		$failures[] = "$label: Organization identity is inconsistent";
	}
	if ( home_url( '/contact/' ) !== ( $organization['contactPoint']['url'] ?? '' ) ) {
		$failures[] = "$label: contactPoint does not use the verified contact page";
	}

	$websites = array_values(
		array_filter(
			$graph,
			static fn( $piece ) => in_array( 'WebSite', (array) ( $piece['@type'] ?? array() ), true )
		)
	);
	if ( 1 !== count( $websites ) || $expected_id !== ( $websites[0]['publisher']['@id'] ?? '' ) ) {
		$failures[] = "$label: WebSite is not linked to Organization";
	}

	if ( 'about' === $label ) {
		$about_pages = array_values(
			array_filter(
				$graph,
				static fn( $piece ) => in_array( 'AboutPage', (array) ( $piece['@type'] ?? array() ), true )
			)
		);
		if ( 1 !== count( $about_pages ) ) {
			$failures[] = sprintf( 'about: expected one AboutPage, found %d', count( $about_pages ) );
		} elseif ( $expected_id !== ( $about_pages[0]['about']['@id'] ?? '' ) || $expected_id !== ( $about_pages[0]['mainEntity']['@id'] ?? '' ) ) {
			$failures[] = 'about: AboutPage is not linked to Organization';
		}
	}

	foreach ( array( $organization['url'] ?? '', $organization['logo']['url'] ?? '', $organization['contactPoint']['url'] ?? '', $organization['sameAs'][0] ?? '' ) as $linked_url ) {
		$linked_response = wp_remote_head( $linked_url, array( 'timeout' => 20 ) );
		if ( ! $linked_url || is_wp_error( $linked_response ) || 200 !== (int) wp_remote_retrieve_response_code( $linked_response ) ) {
			$failures[] = "$label: linked Organization URL failed HTTP 200 ($linked_url)";
		}
	}
}

WP_CLI::log( sprintf( 'Pages checked: %d', count( $pages ) ) );
WP_CLI::log( sprintf( 'Failures: %d', count( $failures ) ) );
foreach ( $failures as $failure ) {
	WP_CLI::warning( $failure );
}
if ( $failures ) {
	WP_CLI::error( 'Organization schema audit failed.' );
}
WP_CLI::success( 'Organization, WebSite and AboutPage form one valid linked graph.' );

