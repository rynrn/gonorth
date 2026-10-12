<?php
/**
 * Regression checks for deterministic place SEO title formatting.
 *
 * Usage: wp eval-file tests/seo-title-formatting.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$cases = array(
	array( 'פארק המעיינות', 'ניר דוד', 'פארק המעיינות | ניר דוד | כיוון צפון' ),
	array( 'רוג׳ום — קיר טיפוס בקריית שמונה', 'קריית שמונה', 'רוג׳ום - קיר טיפוס בקריית שמונה | כיוון צפון' ),
	array( 'שמורת טבע יהודיה', 'רמת הגולן', 'שמורת טבע יהודיה | רמת הגולן | כיוון צפון' ),
	array( 'אתר בלי יישוב ואזור', 'צפון ישראל', 'אתר בלי יישוב ואזור | צפון ישראל | כיוון צפון' ),
);

foreach ( $cases as $case ) {
	$actual = GoNorth_Place_SEO::build_place_title( $case[0], $case[1] );
	if ( $actual !== $case[2] ) {
		WP_CLI::error( sprintf( 'Expected "%s"; received "%s".', $case[2], $actual ) );
	}
}

$long = GoNorth_Place_SEO::build_place_title(
	'מרכז מבקרים וחוויות משפחתיות ארוכות במיוחד בצפון הארץ ובסביבתה',
	'קריית שמונה'
);
if ( mb_strlen( $long, 'UTF-8' ) > 60 || false === mb_strpos( $long, 'קריית שמונה', 0, 'UTF-8' ) || ! str_ends_with( $long, 'כיוון צפון' ) ) {
	WP_CLI::error( 'Long title was not shortened while preserving location and brand: ' . $long );
}

WP_CLI::success( 'SEO title formatting passed for duplicate, fallback and length cases.' );
