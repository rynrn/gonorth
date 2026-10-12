<?php
/**
 * Regression checks for locality/address display formatting.
 *
 * Usage: wp eval-file tests/location-formatting.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$cases = array(
	array( 'כביש 669 ליד גן השלושה, ניר דוד', 'ניר דוד', 'כביש 669 ליד גן השלושה, ניר דוד' ),
	array( 'מגד אל-כרום, גליל תחתון', "מג'ד אל-כרום", 'מגד אל-כרום, גליל תחתון' ),
	array( 'משתלת שמיר, כפר יהושוע', 'כפר יהושע', 'משתלת שמיר, כפר יהושוע' ),
	array( 'עורב, חספין, רמת הגולן', 'חיספין', 'עורב, חספין, רמת הגולן' ),
	array( 'כביש 87', 'קצרין', 'כביש 87, קצרין' ),
	array( '', 'אודם', 'אודם' ),
	array( 'תל סאקי, דרום רמת הגולן', '', 'תל סאקי, דרום רמת הגולן' ),
);

foreach ( $cases as $index => $case ) {
	$actual = GoNorth_Place_Data::format_location( $case[0], $case[1] );
	if ( $actual !== $case[2] ) {
		WP_CLI::error( sprintf( 'Location-format case %d failed: expected "%s", got "%s".', $index + 1, $case[2], $actual ) );
	}
}

WP_CLI::success( sprintf( 'Location formatting passed for %d cases.', count( $cases ) ) );
