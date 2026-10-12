<?php
/** Integration assertions for Google Preferred Sources. */

if ( ! class_exists( 'GoNorth_Preferred_Source' ) ) {
	throw new RuntimeException( 'Preferred Sources service is unavailable.' );
}

$html = GoNorth_Preferred_Source::render( 'footer', 'article' );
foreach ( array( 'data-gn-preferred-source="1"', 'data-placement="footer"', 'data-page-type="article"', 'google.com/preferences/source?q=gonorth.co.il', 'הוסיפו את כיוון צפון כמקור מועדף', 'אינה מבטיחה דירוג או מיקום' ) as $needle ) {
	if ( false === strpos( $html, $needle ) ) {
		throw new RuntimeException( 'Preferred Sources markup is missing: ' . $needle );
	}
}
if ( '' !== GoNorth_Preferred_Source::render( 'footer', 'article' ) ) {
	throw new RuntimeException( 'Preferred Sources rendered more than once.' );
}

$asset_dir = dirname( __DIR__ ) . '/assets/';
$sdk_js    = (string) file_get_contents( $asset_dir . 'preferred-source.js' );
$analytics = (string) file_get_contents( $asset_dir . 'place-analytics.js' );
foreach ( array( 'https://news.google.com/swg/js/v1/publisher.js', "script.async = true", "'preferred-sources-control', 'manual'", 'gn_cookie_ok', '__goNorthPreferredSourceScriptRequested', 'addPreferredSource()', "lang: 'he'" ) as $needle ) {
	if ( false === strpos( $sdk_js, $needle ) ) {
		throw new RuntimeException( 'Preferred Sources SDK integration is missing: ' . $needle );
	}
}
if ( 1 !== substr_count( $analytics, "send( 'preferred_source_click'" ) ) {
	throw new RuntimeException( 'preferred_source_click must have one delegated send path.' );
}
foreach ( array( 'placement:', 'page_type:', 'place_id:', 'page_location:' ) as $needle ) {
	if ( false === strpos( $analytics, $needle ) ) {
		throw new RuntimeException( 'Analytics payload is missing: ' . $needle );
	}
}
if ( false !== strpos( $analytics, 'preferred_source_complete' ) || false !== strpos( $sdk_js, 'preferred_source_complete' ) ) {
	throw new RuntimeException( 'Completion must not be inferred without an official callback.' );
}

echo "Preferred Sources: OK\n";
echo "official_sdk=1\nasync_once=1\nfallback=1\nconsent_gate=1\ncompletion_inferred=0\n";
