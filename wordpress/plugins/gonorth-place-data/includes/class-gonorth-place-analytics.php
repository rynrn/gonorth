<?php
/**
 * Shared GA4 analytics contract for GoNorth places.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Place_Analytics {
	/** Register the frontend asset. */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_asset' ), 30 );
	}

	/** Load one helper on every public surface that can contain a place card. */
	public static function enqueue_asset(): void {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_script(
			'gonorth-place-analytics',
			plugins_url( 'assets/place-analytics.js', GONORTH_PLACE_DATA_FILE ),
			array(),
			GONORTH_PLACE_DATA_VERSION,
			true
		);

		$place = null;
		if ( is_singular( 'gd_place' ) ) {
			$place = self::get_place_context( get_queried_object_id() );
		}

		$debug = isset( $_GET['gn_analytics_debug'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['gn_analytics_debug'] ) );
		wp_localize_script(
			'gonorth-place-analytics',
			'GoNorthAnalyticsConfig',
			array(
				'currentPlace'     => $place,
				'discoverySurface' => self::get_discovery_surface(),
				'debug'            => (bool) apply_filters( 'gonorth_place_analytics_debug', $debug ),
			)
		);
	}

	/**
	 * Return stable, non-personal reporting parameters for one place.
	 *
	 * @param int $post_id Place post ID.
	 * @return array<string,string>|null
	 */
	public static function get_place_context( int $post_id ): ?array {
		if ( 'gd_place' !== get_post_type( $post_id ) ) {
			return null;
		}

		$data       = GoNorth_Place_Data::get( $post_id );
		$categories = wp_get_post_terms( $post_id, 'gd_placecategory', array( 'fields' => 'names' ) );
		$regions    = wp_get_post_terms( $post_id, 'gn_region', array( 'fields' => 'names' ) );

		return array(
			'place_id'       => (string) $post_id,
			'place_slug'     => (string) get_post_field( 'post_name', $post_id ),
			'place_name'     => html_entity_decode( get_the_title( $post_id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'place_category' => ! is_wp_error( $categories ) && $categories ? (string) reset( $categories ) : '',
			'place_city'     => isset( $data['city'] ) ? (string) $data['city'] : '',
			'place_region'   => ! is_wp_error( $regions ) && $regions ? (string) reset( $regions ) : '',
		);
	}

	/** Normalize the page on which a place was discovered. */
	private static function get_discovery_surface(): string {
		if ( is_search() ) return 'search';
		if ( is_tax( 'gd_placecategory' ) || is_post_type_archive( 'gd_place' ) ) return 'category';
		if ( is_front_page() || is_home() ) return 'homepage';
		if ( is_singular( 'gd_place' ) ) return 'related';
		return 'editorial';
	}
}
