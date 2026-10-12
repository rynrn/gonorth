<?php
/**
 * Site entity and AboutPage relationships for Yoast's canonical schema graph.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Organization_Schema {
	/** Register the single Yoast graph integration. */
	public static function init(): void {
		add_filter( 'wpseo_schema_graph', array( __CLASS__, 'filter_graph' ), 40, 2 );
	}

	/** Return the stable site Organization identifier. */
	public static function organization_id(): string {
		return home_url( '/#organization' );
	}

	/** Return the canonical organization node from verified site-owned URLs. */
	public static function organization_node(): array {
		return array(
			'@type'         => 'Organization',
			'@id'           => self::organization_id(),
			'name'          => 'כיוון צפון',
			'alternateName' => 'GoNorth',
			'url'           => home_url( '/' ),
			'logo'          => array(
				'@type'  => 'ImageObject',
				'@id'    => home_url( '/#organization-logo' ),
				'url'    => trailingslashit( get_stylesheet_directory_uri() ) . 'assets/gonorth-organic-mark.svg',
				'width'  => 300,
				'height' => 130,
			),
			'contactPoint'  => array(
				'@type'             => 'ContactPoint',
				'contactType'       => 'שירות לקוחות',
				'url'               => home_url( '/contact/' ),
				'availableLanguage' => array( 'עברית' ),
			),
			'sameAs'        => array( home_url( '/about/' ) ),
		);
	}

	/**
	 * Add or enrich one Organization node and link it to WebSite/AboutPage.
	 *
	 * @param array<int,array<string,mixed>> $graph   Yoast graph.
	 * @param mixed                          $context Yoast context.
	 * @return array<int,array<string,mixed>>
	 */
	public static function filter_graph( array $graph, $context ): array {
		unset( $context );
		$organization_id    = self::organization_id();
		$organization_index = null;

		foreach ( $graph as $index => &$piece ) {
			$types = isset( $piece['@type'] ) ? (array) $piece['@type'] : array();
			if ( ( $piece['@id'] ?? '' ) === $organization_id ) {
				$organization_index = $index;
			}
			if ( in_array( 'WebSite', $types, true ) && ( $piece['@id'] ?? '' ) === home_url( '/#website' ) ) {
				$piece['publisher'] = array( '@id' => $organization_id );
			}
			if ( is_page( 'about' ) && in_array( 'WebPage', $types, true ) ) {
				$piece['@type']     = 'AboutPage';
				$piece['about']     = array( '@id' => $organization_id );
				$piece['mainEntity'] = array( '@id' => $organization_id );
			}
		}
		unset( $piece );

		$organization = self::organization_node();
		if ( null === $organization_index ) {
			$graph[] = $organization;
		} else {
			$graph[ $organization_index ] = array_replace_recursive( $graph[ $organization_index ], $organization );
		}

		return array_values( $graph );
	}
}

