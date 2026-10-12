<?php
/** Integration assertions for the contextual internal-link network. */

if ( ! class_exists( 'GoNorth_Internal_Links' ) ) {
	throw new RuntimeException( 'Internal-link service is unavailable.' );
}

/** @return array<int,string> */
$extract_hrefs = static function ( string $html ): array {
	preg_match_all( '/href=["\']([^"\']+)["\']/u', $html, $matches );
	return array_values( array_unique( array_map( 'html_entity_decode', $matches[1] ?? array() ) ) );
};

/** @return array<int,string> */
$extract_anchors = static function ( string $html ): array {
	preg_match_all( '/<a\b[^>]*>(.*?)<\/a>/us', $html, $matches );
	return array_map( static fn( string $text ): string => trim( wp_strip_all_tags( $text ) ), $matches[1] ?? array() );
};

$guides = GoNorth_Internal_Links::get_guides();
if ( count( $guides ) < 35 ) {
	throw new RuntimeException( sprintf( 'Only %d published guides received a useful context.', count( $guides ) ) );
}
if ( isset( $guides[126] ) || isset( $guides[131] ) ) {
	throw new RuntimeException( 'A stale or out-of-scope guide entered the link network.' );
}

$published_urls = array();
foreach ( get_posts( array( 'post_type' => array( 'post', 'page', 'gd_place' ), 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $post ) {
	$published_urls[] = get_permalink( $post );
}
foreach ( array( 'gd_placecategory', 'gn_region' ) as $taxonomy ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) ) {
		throw new RuntimeException( $terms->get_error_message() );
	}
	foreach ( $terms as $term ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) {
			$published_urls[] = $url;
		}
	}
}
$published_urls = array_flip( array_unique( $published_urls ) );

$all_hrefs          = array();
$closed_place_urls  = array();
foreach ( get_posts( array(
	'post_type'      => 'gd_place',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key'     => '_gonorth_business_status',
			'value'   => array( 'temporarily_closed', 'permanently_closed' ),
			'compare' => 'IN',
		),
	),
) ) as $closed_place ) {
	$closed_place_urls[] = get_permalink( $closed_place );
}
$guide_inbound  = array_fill_keys( array_column( $guides, 'url' ), 0 );
$archive_blocks = 0;
foreach ( array( 'gd_placecategory', 'gn_region' ) as $taxonomy ) {
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
	foreach ( $terms as $term ) {
		$html = GoNorth_Internal_Links::render_archive_context( $term );
		if ( '' === $html ) {
			throw new RuntimeException( sprintf( '%s/%s has no contextual archive block.', $taxonomy, $term->slug ) );
		}
		++$archive_blocks;
		$anchors = $extract_anchors( $html );
		if ( count( $anchors ) !== count( array_unique( $anchors ) ) ) {
			throw new RuntimeException( sprintf( '%s/%s repeats an anchor inside one block.', $taxonomy, $term->slug ) );
		}
		foreach ( $extract_hrefs( $html ) as $href ) {
			$all_hrefs[] = $href;
			if ( isset( $guide_inbound[ $href ] ) ) {
				++$guide_inbound[ $href ];
			}
		}
	}
}

$place_count = 0;
$places = get_posts( array( 'post_type' => 'gd_place', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
foreach ( $places as $place ) {
	$categories = wp_get_post_terms( $place->ID, 'gd_placecategory', array( 'fields' => 'ids' ) );
	$regions    = wp_get_post_terms( $place->ID, 'gn_region', array( 'fields' => 'ids' ) );
	if ( ( is_wp_error( $categories ) || ! $categories ) && ( is_wp_error( $regions ) || ! $regions ) ) {
		throw new RuntimeException( sprintf( 'Place %d has no inbound archive path.', $place->ID ) );
	}
	$html = GoNorth_Internal_Links::render_place_context( $place->ID );
	if ( '' === $html ) {
		throw new RuntimeException( sprintf( 'Place %d has no contextual links.', $place->ID ) );
	}
	foreach ( $extract_hrefs( $html ) as $href ) {
		$all_hrefs[] = $href;
		if ( isset( $guide_inbound[ $href ] ) ) {
			++$guide_inbound[ $href ];
		}
	}
	++$place_count;
}

foreach ( $guides as $guide ) {
	$html = GoNorth_Internal_Links::render_guide_context( $guide, '' );
	if ( '' === $html ) {
		throw new RuntimeException( sprintf( 'Guide %d has no outbound context.', $guide['id'] ) );
	}
	$anchors = $extract_anchors( $html );
	if ( count( $anchors ) !== count( array_unique( $anchors ) ) ) {
		throw new RuntimeException( sprintf( 'Guide %d repeats an anchor inside its context.', $guide['id'] ) );
	}
	foreach ( $extract_hrefs( $html ) as $href ) {
		$all_hrefs[] = $href;
		if ( isset( $guide_inbound[ $href ] ) && $href !== $guide['url'] ) {
			++$guide_inbound[ $href ];
		}
	}
}

$promoted_closed_places = array_values( array_intersect( array_unique( $all_hrefs ), $closed_place_urls ) );
if ( $promoted_closed_places ) {
	throw new RuntimeException( 'Closed places entered generated recommendations: ' . implode( ', ', $promoted_closed_places ) );
}

$orphan_guides = array_keys( array_filter( $guide_inbound, static fn( int $count ): bool => $count < 1 ) );
if ( $orphan_guides ) {
	throw new RuntimeException( 'Orphan guides remain: ' . implode( ', ', $orphan_guides ) );
}
foreach ( array_unique( $all_hrefs ) as $href ) {
	if ( 0 !== strpos( $href, home_url( '/' ) ) || ! isset( $published_urls[ $href ] ) ) {
		throw new RuntimeException( 'Generated link is not a known public target: ' . $href );
	}
}

echo "Internal links: OK\n";
echo 'guides=' . count( $guides ) . "\n";
echo 'places=' . $place_count . "\n";
echo 'archive_blocks=' . $archive_blocks . "\n";
echo 'orphan_guides=0' . "\n";
echo 'invalid_targets=0' . "\n";
