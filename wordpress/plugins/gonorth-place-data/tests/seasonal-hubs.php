<?php
/** Integration assertions for evergreen seasonal hubs. */

if ( ! class_exists( 'GoNorth_Seasonal_Hubs' ) ) {
	throw new RuntimeException( 'Seasonal hub service is unavailable.' );
}

$profiles = GoNorth_Seasonal_Hubs::profiles();
if ( array( 'rosh-hashanah', 'sukkot' ) !== array_keys( $profiles ) ) {
	throw new RuntimeException( 'Seasonal hub profiles changed unexpectedly.' );
}
foreach ( $profiles as $key => $profile ) {
	if ( mb_strlen( $profile['title'] ) > 60 || mb_strlen( $profile['description'] ) > 160 ) {
		throw new RuntimeException( sprintf( '%s has invalid SEO metadata length.', $key ) );
	}
}

$pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => array( 'draft', 'publish' ),
		'posts_per_page' => 4,
		'meta_key'       => GoNorth_Seasonal_Hubs::META_KEY,
	)
);
if ( 2 !== count( $pages ) ) {
	throw new RuntimeException( 'Expected exactly two evergreen seasonal pages.' );
}

$expected_slugs = array(
	'rosh-hashanah' => 'rosh-hashanah-batzafon',
	'sukkot'        => 'sukkot-batzafon',
);
foreach ( $pages as $page ) {
	$key = (string) get_post_meta( $page->ID, GoNorth_Seasonal_Hubs::META_KEY, true );
	if ( ! isset( $expected_slugs[ $key ] ) || $expected_slugs[ $key ] !== $page->post_name ) {
		throw new RuntimeException( sprintf( 'Seasonal page %d has an invalid evergreen slug.', $page->ID ) );
	}
	if ( preg_match( '/20\d{2}/', $page->post_name ) ) {
		throw new RuntimeException( sprintf( 'Seasonal page %d includes a year in its URL.', $page->ID ) );
	}
	if ( 'draft' !== $page->post_status ) {
		throw new RuntimeException( sprintf( 'Seasonal page %d must remain a review draft.', $page->ID ) );
	}
	$content = (string) $page->post_content;
	$words   = preg_split( '/\s+/u', trim( wp_strip_all_tags( $content ) ), -1, PREG_SPLIT_NO_EMPTY );
	if ( count( $words ) < 500 || false === mb_strpos( $content, '2026' ) || false === mb_strpos( $content, 'עודכן לאחרונה' ) ) {
		throw new RuntimeException( sprintf( 'Seasonal page %d lacks unique, dated content.', $page->ID ) );
	}
	foreach ( array( '_gonorth_seasonal_promo_start', '_gonorth_seasonal_promo_end', '_gonorth_seasonal_event_year', '_gonorth_seasonal_updated_at' ) as $meta_key ) {
		if ( ! get_post_meta( $page->ID, $meta_key, true ) ) {
			throw new RuntimeException( sprintf( 'Seasonal page %d lacks %s.', $page->ID, $meta_key ) );
		}
	}
}

ob_start();
GoNorth_Seasonal_Hubs::render_promotion();
$promotion = ob_get_clean();
if ( '' !== $promotion ) {
	throw new RuntimeException( 'A private seasonal draft leaked a public promotion.' );
}

foreach ( array( 94, 95, 262, 269, 270, 427 ) as $post_id ) {
	if ( '1' !== (string) get_post_meta( $post_id, '_gonorth_seasonal_promote', true ) ) {
		throw new RuntimeException( sprintf( 'Relevant article %d is not ready for seasonal linking.', $post_id ) );
	}
}

echo "Seasonal hubs: OK\npages=2\nyear_in_slugs=0\npublic_promotions=0\nrelevant_articles=6\n";
