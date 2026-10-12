<?php
/** Integration assertions for verified attribute landing pages. */

if ( ! class_exists( 'GoNorth_Attribute_Landing_Pages' ) ) {
	throw new RuntimeException( 'Attribute landing-page service is unavailable.' );
}

$profiles = GoNorth_Attribute_Landing_Pages::profiles();
if ( array( 'open_saturday' ) !== array_keys( $profiles ) ) {
	throw new RuntimeException( 'Sparse attribute profiles must not become public landing pages.' );
}

$places = GoNorth_Attribute_Landing_Pages::get_verified_places( 'open_saturday' );
if ( count( $places ) < 12 ) {
	throw new RuntimeException( 'Open-Saturday verified inventory is below the quality floor.' );
}

$urls = array();
foreach ( $places as $place ) {
	if ( 'publish' !== get_post_status( $place['id'] ) ) {
		throw new RuntimeException( sprintf( 'Landing result %d is not published.', $place['id'] ) );
	}
	if ( 'yes' !== GoNorth_Place_Attributes::get_state( $place['id'], 'open_saturday' ) ) {
		throw new RuntimeException( sprintf( 'Landing result %d is not explicitly open Saturday.', $place['id'] ) );
	}
	if ( empty( $place['practical_checked_at'] ) ) {
		throw new RuntimeException( sprintf( 'Landing result %d lacks practical verification freshness.', $place['id'] ) );
	}
	$urls[] = $place['url'];
}
if ( count( array_unique( $urls ) ) !== count( $places ) ) {
	throw new RuntimeException( 'Attribute landing page contains duplicate place URLs.' );
}

$html = GoNorth_Attribute_Landing_Pages::render_shortcode( array( 'attribute' => 'open_saturday' ) );
if ( substr_count( $html, 'class="gn-attribute-card"' ) !== count( $places ) ) {
	throw new RuntimeException( 'Visible dynamic list and verified result set differ.' );
}
foreach ( array( 'מצבי לא ידוע אינם נכללים', '02.09.2026', 'מקומות פתוחים בשבת בצפון' ) as $needle ) {
	if ( false === mb_strpos( $html, $needle ) ) {
		throw new RuntimeException( sprintf( 'Landing copy is missing: %s', $needle ) );
	}
}

$landing_pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => array( 'draft', 'publish' ),
		'posts_per_page' => 2,
		'meta_key'       => GoNorth_Attribute_Landing_Pages::META_KEY,
		'meta_value'     => 'open_saturday',
	)
);
if ( 1 !== count( $landing_pages ) ) {
	throw new RuntimeException( 'Expected one canonical open-Saturday page.' );
}
if ( 'draft' !== $landing_pages[0]->post_status ) {
	throw new RuntimeException( 'Open-Saturday page must remain a review draft until user approval.' );
}
if ( 'places/open-saturday' !== get_page_uri( $landing_pages[0] ) ) {
	throw new RuntimeException( 'Open-Saturday page is not nested below the canonical places page.' );
}

$previous_query = $GLOBALS['wp_query'] ?? null;
$previous_post  = $GLOBALS['post'] ?? null;
$GLOBALS['wp_query'] = new WP_Query(
	array(
		'page_id'     => $landing_pages[0]->ID,
		'post_type'   => 'page',
		'post_status' => 'draft',
	)
);
$GLOBALS['post'] = $landing_pages[0];
setup_postdata( $landing_pages[0] );
$graph = GoNorth_Attribute_Landing_Pages::filter_schema_graph( array(), null );
wp_reset_postdata();
$GLOBALS['wp_query'] = $previous_query;
$GLOBALS['post']     = $previous_post;

if ( 1 !== count( $graph ) || 'ItemList' !== $graph[0]['@type'] || count( $graph[0]['itemListElement'] ) !== count( $places ) ) {
	throw new RuntimeException( 'ItemList does not match the visible verified inventory.' );
}

printf( "Attribute landing pages: OK\nopen_saturday=%d\nsparse_profiles=0\nitemlist=%d\n", count( $places ), count( $graph[0]['itemListElement'] ) );
