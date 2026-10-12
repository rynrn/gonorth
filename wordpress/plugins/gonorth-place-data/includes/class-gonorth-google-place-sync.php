<?php
/**
 * Safe Google Places API (New) lookup and persistence for existing listings.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Google_Place_Sync {
	private const SEARCH_ENDPOINT  = 'https://places.googleapis.com/v1/places:searchText';
	private const DETAILS_ENDPOINT = 'https://places.googleapis.com/v1/places/';
	private const PLACE_FIELDS     = 'id,displayName,formattedAddress,location,rating,userRatingCount,businessStatus,movedPlaceId';

	/**
	 * Resolve a complete Google data bundle for one published place.
	 *
	 * Existing Place IDs are refreshed directly. A missing ID is discovered only
	 * when the candidate has a defensible name and coordinate match.
	 *
	 * @param int    $post_id Place post ID.
	 * @param string $api_key Server-authorized Google Places API key.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function resolve( int $post_id, string $api_key ) {
		if ( 'gd_place' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			return new WP_Error( 'invalid_place', 'A published GeoDirectory place is required.' );
		}
		if ( '' === trim( $api_key ) ) {
			return new WP_Error( 'missing_api_key', 'A server-authorized Google Places API key is required.' );
		}

		$data     = GoNorth_Place_Data::get( $post_id );
		$place_id = trim( (string) ( $data['google']['place_id'] ?? '' ) );
		if ( $place_id ) {
			$details = self::request(
				'GET',
				self::DETAILS_ENDPOINT . rawurlencode( $place_id ) . '?languageCode=he&regionCode=IL',
				$api_key,
				self::PLACE_FIELDS
			);
			if ( is_wp_error( $details ) ) {
				return $details;
			}
			return self::complete_bundle( $post_id, $details, true );
		}

		$latitude  = (float) ( $data['latitude'] ?? 0 );
		$longitude = (float) ( $data['longitude'] ?? 0 );
		if ( ! $latitude || ! $longitude ) {
			return new WP_Error( 'missing_coordinates', 'Coordinates are required before discovering a Google Place ID.' );
		}

		$query = trim( get_the_title( $post_id ) . ' ' . (string) ( $data['city'] ?? '' ) );
		$found = self::request(
			'POST',
			self::SEARCH_ENDPOINT,
			$api_key,
			'places.' . str_replace( ',', ',places.', self::PLACE_FIELDS ),
			array(
				'textQuery'    => $query,
				'languageCode' => 'he',
				'regionCode'   => 'IL',
				'pageSize'     => 1,
				'locationBias' => array(
					'circle' => array(
						'center' => array(
							'latitude'  => $latitude,
							'longitude' => $longitude,
						),
						'radius' => 2000.0,
					),
				),
			)
		);
		if ( is_wp_error( $found ) ) {
			return $found;
		}
		$candidates = $found['places'] ?? array();
		if ( 1 !== count( $candidates ) ) {
			return new WP_Error( 'ambiguous_match', sprintf( 'Expected one Google candidate; received %d.', count( $candidates ) ) );
		}

		$bundle = self::complete_bundle( $post_id, $candidates[0], false );
		if ( is_wp_error( $bundle ) ) {
			return $bundle;
		}
		if ( ! self::candidate_is_safe( $bundle['name_score'], $bundle['distance_km'] ) ) {
			return new WP_Error(
				'weak_match',
				sprintf( 'Candidate rejected: name score %.2f, distance %.2f km.', $bundle['name_score'], $bundle['distance_km'] ),
				$bundle
			);
		}
		return $bundle;
	}

	/** Persist one already resolved and complete bundle. */
	public static function store( int $post_id, array $bundle ) {
		foreach ( array( 'google_place_id', 'google_rating', 'google_review_count' ) as $required ) {
			if ( ! array_key_exists( $required, $bundle ) || '' === (string) $bundle[ $required ] ) {
				return new WP_Error( 'incomplete_google_bundle', 'Google ID, rating and review count must be stored together.' );
			}
		}
		return gonorth_store_google_place_data(
			$post_id,
			array_intersect_key(
				$bundle,
				array_flip( array( 'google_place_id', 'google_rating', 'google_review_count' ) )
			)
		);
	}

	/** A conservative confidence gate used before importing a newly discovered ID. */
	public static function candidate_is_safe( float $name_score, float $distance_km ): bool {
		return ( $distance_km <= 2.0 && $name_score >= 0.34 ) || ( $distance_km <= 0.15 && $name_score >= 0.20 );
	}

	/** Name-token overlap, normalized for punctuation and Hebrew/Latin case. */
	public static function name_score( string $expected, string $candidate ): float {
		$tokenize = static function ( string $value ): array {
			$value  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
			$value  = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $value );
			$tokens = preg_split( '/\s+/u', trim( (string) $value ), -1, PREG_SPLIT_NO_EMPTY );
			return array_values( array_unique( array_filter( $tokens, static fn( string $token ): bool => strlen( $token ) > 1 ) ) );
		};
		$expected_tokens  = $tokenize( $expected );
		$candidate_tokens = $tokenize( $candidate );
		if ( ! $expected_tokens || ! $candidate_tokens ) {
			return 0.0;
		}
		$intersection = array_intersect( $expected_tokens, $candidate_tokens );
		return count( $intersection ) / min( count( $expected_tokens ), count( $candidate_tokens ) );
	}

	/** Haversine distance in kilometres. */
	public static function distance_km( float $lat_a, float $lng_a, float $lat_b, float $lng_b ): float {
		$lat_delta = deg2rad( $lat_b - $lat_a );
		$lng_delta = deg2rad( $lng_b - $lng_a );
		$a         = sin( $lat_delta / 2 ) ** 2 + cos( deg2rad( $lat_a ) ) * cos( deg2rad( $lat_b ) ) * sin( $lng_delta / 2 ) ** 2;
		return 6371.0 * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
	}

	/** Convert a Places API (New) Place object to the only bundle that may be persisted. */
	private static function complete_bundle( int $post_id, array $candidate, bool $known_id ) {
		$place_id = trim( (string) ( $candidate['id'] ?? '' ) );
		$rating   = $candidate['rating'] ?? null;
		$reviews  = $candidate['userRatingCount'] ?? null;
		if ( ! $place_id || ! is_numeric( $rating ) || (float) $rating <= 0 || ! is_numeric( $reviews ) || (int) $reviews <= 0 ) {
			return new WP_Error( 'incomplete_google_response', 'Google returned an incomplete rating bundle.' );
		}

		$data          = GoNorth_Place_Data::get( $post_id );
		$candidate_lat = (float) ( $candidate['location']['latitude'] ?? 0 );
		$candidate_lng = (float) ( $candidate['location']['longitude'] ?? 0 );
		$distance      = $candidate_lat && $candidate_lng
			? self::distance_km( (float) $data['latitude'], (float) $data['longitude'], $candidate_lat, $candidate_lng )
			: ( $known_id ? 0.0 : INF );
		$candidate_name = (string) ( $candidate['displayName']['text'] ?? '' );

		return array(
			'google_place_id'     => $place_id,
			'google_rating'       => (float) $rating,
			'google_review_count' => (int) $reviews,
			'candidate_name'      => sanitize_text_field( $candidate_name ),
			'name_score'          => $known_id ? 1.0 : self::name_score( get_the_title( $post_id ), $candidate_name ),
			'distance_km'         => $distance,
			'match_type'          => $known_id ? 'known_id' : 'discovered',
			'business_status'     => sanitize_key( strtolower( (string) ( $candidate['businessStatus'] ?? 'unknown' ) ) ),
			'moved_place_id'      => sanitize_text_field( (string) ( $candidate['movedPlaceId'] ?? '' ) ),
		);
	}

	/** Execute one server-side Places API (New) request without logging the key. */
	private static function request( string $method, string $endpoint, string $api_key, string $field_mask, array $body = array() ) {
		$options = array(
			'timeout' => 20,
			'headers' => array(
				'Accept'           => 'application/json',
				'Content-Type'     => 'application/json; charset=utf-8',
				'X-Goog-Api-Key'   => $api_key,
				'X-Goog-FieldMask' => $field_mask,
			),
		);
		if ( 'POST' === $method ) {
			$options['body'] = wp_json_encode( $body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$response        = wp_remote_post( $endpoint, $options );
		} else {
			$response = wp_remote_get( $endpoint, $options );
		}
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			return new WP_Error( 'invalid_google_response', 'Google Places returned invalid JSON.' );
		}
		$status_code = (int) wp_remote_retrieve_response_code( $response );
		if ( $status_code < 200 || $status_code >= 300 ) {
			$message = (string) ( $decoded['error']['message'] ?? 'Google Places returned an HTTP error.' );
			return new WP_Error( 'google_http_' . $status_code, sanitize_text_field( $message ) );
		}
		return $decoded;
	}
}
