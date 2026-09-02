<?php
/**
 * Read-only integration checks for factual FAQ, freshness and place schema.
 *
 * Usage: wp eval-file tests/seo-integration.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$samples = array(
	50  => array( 'minimum_faq' => 3, 'type' => 'LodgingBusiness' ),
	86  => array( 'minimum_faq' => 4, 'type' => 'TouristAttraction' ),
	149 => array( 'minimum_faq' => 3, 'type' => 'Restaurant' ),
);

foreach ( $samples as $post_id => $expected ) {
	$faq = GoNorth_Place_SEO::get_faq_items( $post_id );
	if ( count( $faq ) < $expected['minimum_faq'] ) {
		WP_CLI::error( sprintf( '%d: expected at least %d factual questions, got %d.', $post_id, $expected['minimum_faq'], count( $faq ) ) );
	}

	$questions = wp_list_pluck( $faq, 'question' );
	if ( 50 === $post_id && preg_grep( '/שבת|שעות הפתיחה/u', $questions ) ) {
		WP_CLI::error( '50: unknown Saturday/hours data generated a question.' );
	}
	if ( 86 === $post_id && ! preg_grep( '/שבת/u', $questions ) ) {
		WP_CLI::error( '86: known Saturday data did not generate a question.' );
	}

	$freshness = GoNorth_Place_SEO::get_freshness( $post_id );
	if ( empty( $freshness['timestamp'] ) ) {
		WP_CLI::error( sprintf( '%d: expected a valid freshness signal.', $post_id ) );
	}

	$description = GoNorth_Place_SEO::get_generated_description( $post_id );
	if ( ! $description || mb_strlen( $description ) > 155 ) {
		WP_CLI::error( sprintf( '%d: generated description is empty or too long.', $post_id ) );
	}

	$schema = GoNorth_Place_SEO::filter_place_schema(
		array( '@context' => 'https://schema.org', '@type' => 'LocalBusiness' ),
		get_post( $post_id )
	);
	if ( $expected['type'] !== $schema['@type'] || empty( $schema['@id'] ) || isset( $schema['dateModified'] ) ) {
		WP_CLI::error( sprintf( '%d: enhanced place schema is incomplete.', $post_id ) );
	}
	WP_CLI::log( sprintf( '%d: %d questions, %s schema, %d-char fallback description.', $post_id, count( $faq ), $schema['@type'], mb_strlen( $description ) ) );
}

WP_CLI::success( 'Factual FAQ, honest freshness and enhanced place schema checks passed.' );
