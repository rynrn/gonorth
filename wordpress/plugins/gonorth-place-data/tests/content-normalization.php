<?php
/**
 * Smoke tests for place-content line-break normalization.
 *
 * Run with:
 * wp eval-file wp-content/plugins/gonorth-place-data/tests/content-normalization.php
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plain      = 'פסקה ראשונה\\n\\nפסקה שנייה';
$normalized = GoNorth_Place_Content::normalize_literal_line_breaks( $plain );

if ( "פסקה ראשונה\n\nפסקה שנייה" !== $normalized ) {
	throw new RuntimeException( 'Visible literal line breaks were not normalized.' );
}

$protected = '[sample value="line\\nvalue"] לפני\\n\\nאחרי <script type="application/ld+json">{"text":"line\\nvalue"}</script>';
$expected  = "[sample value=\"line\\nvalue\"] לפני\n\nאחרי <script type=\"application/ld+json\">{\"text\":\"line\\nvalue\"}</script>";

if ( $expected !== GoNorth_Place_Content::normalize_literal_line_breaks( $protected ) ) {
	throw new RuntimeException( 'Protected shortcode or script content was changed.' );
}

$place_data = array(
	'post_type'    => 'gd_place',
	'post_content' => wp_slash( $plain ),
);
$place_data = GoNorth_Place_Content::filter_insert_post_data( $place_data );

if ( "פסקה ראשונה\n\nפסקה שנייה" !== wp_unslash( $place_data['post_content'] ) ) {
	throw new RuntimeException( 'gd_place save filtering failed.' );
}

$unslashed_place_data = array(
	'post_type'    => 'gd_place',
	'post_content' => $plain,
);
$unslashed_place_data = GoNorth_Place_Content::filter_insert_post_data( $unslashed_place_data );

if ( "פסקה ראשונה\n\nפסקה שנייה" !== $unslashed_place_data['post_content'] ) {
	throw new RuntimeException( 'Unslashed gd_place import filtering failed.' );
}

$post_data = array(
	'post_type'    => 'post',
	'post_content' => wp_slash( $plain ),
);

if ( $post_data !== GoNorth_Place_Content::filter_insert_post_data( $post_data ) ) {
	throw new RuntimeException( 'Non-place content was changed.' );
}

echo 'PASS: place content line-break normalization is scoped and safe.' . PHP_EOL;
