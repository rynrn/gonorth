<?php
/**
 * Geographic nearby-place recommendations.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Nearby_Places {
	private const DEFAULT_LIMIT = 6;
	private const DEFAULT_RADIUS_KM = 50.0;
	private const CATEGORY_REPEAT_PENALTY_KM = 5.0;
	private const CACHE_TTL = 12 * HOUR_IN_SECONDS;
	private const CACHE_VERSION_OPTION = 'gonorth_nearby_cache_version';

	/** @var array<int,bool> Prevent duplicate output when GeoDirectory nests the_content filters. */
	private static array $rendered = array();

	/** @var bool Prevent duplicate cache-version bumps during one save operation. */
	private static bool $cache_invalidated = false;

	/**
	 * Register frontend and invalidation hooks.
	 */
	public static function init(): void {
		add_filter( 'the_content', array( __CLASS__, 'append_nearby_places' ), 30 );
		add_action( 'save_post_gd_place', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'gonorth_place_data_updated', array( __CLASS__, 'invalidate_cache' ) );
	}

	/**
	 * Find diverse nearby published places, ordered by geographic distance.
	 *
	 * @param int   $post_id Place ID.
	 * @param int   $limit Maximum result count.
	 * @param float $radius_km Maximum radius in kilometres.
	 * @return array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}>
	 */
	public static function get_nearby_places( int $post_id, int $limit = self::DEFAULT_LIMIT, float $radius_km = self::DEFAULT_RADIUS_KM ): array {
		$limit     = max( 1, min( 12, $limit ) );
		$radius_km = max( 1.0, min( 150.0, $radius_km ) );
		$data      = GoNorth_Place_Data::get( $post_id );
		$latitude  = isset( $data['latitude'] ) && is_numeric( $data['latitude'] ) ? (float) $data['latitude'] : null;
		$longitude = isset( $data['longitude'] ) && is_numeric( $data['longitude'] ) ? (float) $data['longitude'] : null;

		if ( null === $latitude || null === $longitude || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
			return array();
		}

		$version   = max( 1, (int) get_option( self::CACHE_VERSION_OPTION, 1 ) );
		$cache_key = 'gn_nearby_' . md5( implode( '|', array( $version, $post_id, $limit, $radius_km ) ) );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$candidates = self::query_candidates( $post_id, $latitude, $longitude, $radius_km, max( 24, $limit * 4 ) );
		$selected   = self::prefer_category_diversity( $candidates, $limit );
		set_transient( $cache_key, $selected, self::CACHE_TTL );

		return $selected;
	}

	/**
	 * Find nearby places limited to one or more GeoDirectory category slugs.
	 *
	 * This is the reusable geographic primitive for contextual recommendations.
	 * Results share the same versioned cache as the generic nearby component.
	 *
	 * @param int           $post_id Place ID.
	 * @param array<string> $category_slugs Allowed category slugs.
	 * @param int           $limit Result limit.
	 * @param float         $radius_km Maximum radius in kilometres.
	 * @param array<int>    $exclude_ids Additional place IDs to exclude.
	 * @return array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}>
	 */
	public static function get_nearby_places_by_categories( int $post_id, array $category_slugs, int $limit = 2, float $radius_km = 25.0, array $exclude_ids = array() ): array {
		$category_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_key', $category_slugs ) ) ) );
		$exclude_ids    = array_values( array_unique( array_filter( array_map( 'absint', $exclude_ids ) ) ) );
		$limit          = max( 1, min( 12, $limit ) );
		$radius_km      = max( 1.0, min( 150.0, $radius_km ) );
		if ( ! $category_slugs ) {
			return array();
		}

		$data      = GoNorth_Place_Data::get( $post_id );
		$latitude  = isset( $data['latitude'] ) && is_numeric( $data['latitude'] ) ? (float) $data['latitude'] : null;
		$longitude = isset( $data['longitude'] ) && is_numeric( $data['longitude'] ) ? (float) $data['longitude'] : null;
		if ( null === $latitude || null === $longitude || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
			return array();
		}

		sort( $category_slugs );
		sort( $exclude_ids );
		$version   = max( 1, (int) get_option( self::CACHE_VERSION_OPTION, 1 ) );
		$cache_key = 'gn_nearby_context_' . md5( wp_json_encode( array( $version, $post_id, $category_slugs, $limit, $radius_km, $exclude_ids ) ) );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$places = self::query_category_candidates( $post_id, $latitude, $longitude, $category_slugs, $radius_km, $limit, $exclude_ids );
		set_transient( $cache_key, $places, self::CACHE_TTL );
		return $places;
	}

	/**
	 * Append the nearby component to the meaningful GeoDirectory content pass.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function append_nearby_places( string $content ): string {
		if ( ! is_singular( 'gd_place' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id || ! apply_filters( 'gonorth_nearby_places_in_content', true, $post_id ) || false !== strpos( $content, 'class="gn-nearby-places"' ) ) {
			return $content;
		}
		return $content . self::render_nearby_places( $post_id );
	}

	/**
	 * Render the nearby component for placement by a theme or block.
	 *
	 * @param int $post_id Place ID.
	 * @return string
	 */
	public static function render_nearby_places( int $post_id ): string {
		if ( isset( self::$rendered[ $post_id ] ) ) {
			return '';
		}
		if ( ! apply_filters( 'gonorth_nearby_places_component_enabled', true, $post_id ) ) {
			return '';
		}

		$limit     = (int) apply_filters( 'gonorth_nearby_places_limit', self::DEFAULT_LIMIT, $post_id );
		$radius_km = (float) apply_filters( 'gonorth_nearby_places_radius_km', self::DEFAULT_RADIUS_KM, $post_id );
		$places    = self::get_nearby_places( $post_id, $limit, $radius_km );
		if ( ! $places ) {
			return '';
		}

		self::$rendered[ $post_id ] = true;
		$title_id = 'gn-nearby-places-title-' . $post_id;

		ob_start();
		?>
		<section class="gn-nearby-places" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
			<header class="gn-nearby-places__header">
				<p><?php echo esc_html__( 'לגלות בסביבה', 'gonorth-place-data' ); ?></p>
				<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html__( 'מה יש ליד?', 'gonorth-place-data' ); ?></h2>
			</header>
			<ul class="gn-nearby-places__grid">
				<?php foreach ( $places as $position => $place ) : ?>
					<li>
						<a class="gn-nearby-place" href="<?php echo esc_url( $place['url'] ); ?>" data-gn-place-card="1" data-target-place-id="<?php echo esc_attr( (string) $place['id'] ); ?>" data-place-name="<?php echo esc_attr( $place['title'] ); ?>" data-place-category="<?php echo esc_attr( $place['category'] ); ?>" data-discovery-surface="nearby" data-related-section="nearby" data-position="<?php echo esc_attr( (string) ( $position + 1 ) ); ?>">
							<strong><?php echo esc_html( $place['title'] ); ?></strong>
							<span class="gn-nearby-place__meta">
								<span><?php echo esc_html( $place['category'] ); ?></span>
								<span aria-hidden="true">·</span>
								<span><bdi><?php echo esc_html( self::format_distance( $place['distance_km'] ) ); ?></bdi> <?php echo esc_html__( 'ק״מ', 'gonorth-place-data' ); ?></span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Invalidate all nearby-query caches by changing their shared version.
	 *
	 * @param int $post_id Updated place ID.
	 */
	public static function invalidate_cache( int $post_id ): void {
		if ( self::$cache_invalidated || 'gd_place' !== get_post_type( $post_id ) ) {
			return;
		}
		self::$cache_invalidated = true;
		update_option( self::CACHE_VERSION_OPTION, max( 1, (int) get_option( self::CACHE_VERSION_OPTION, 1 ) ) + 1, false );
	}

	/**
	 * Query the closest candidates with one prepared SQL statement.
	 *
	 * @param int   $post_id Current place ID.
	 * @param float $latitude Current latitude.
	 * @param float $longitude Current longitude.
	 * @param float $radius_km Maximum radius.
	 * @param int   $candidate_limit Candidate pool size.
	 * @return array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}>
	 */
	private static function query_candidates( int $post_id, float $latitude, float $longitude, float $radius_km, int $candidate_limit ): array {
		global $wpdb;

		$detail_table = $wpdb->prefix . 'geodir_gd_place_detail';
		$earth_radius = 6371.0088;
		$distance_sql = '( %f * 2 * ASIN( SQRT( POWER( SIN( ( %f - d.latitude ) * PI() / 180 / 2 ), 2 ) + COS( %f * PI() / 180 ) * COS( d.latitude * PI() / 180 ) * POWER( SIN( ( %f - d.longitude ) * PI() / 180 / 2 ), 2 ) ) ) )';
		$sql = "SELECT p.ID, p.post_title, place_category.category_id, t.name AS category_name, {$distance_sql} AS distance_km
			FROM {$detail_table} d
			INNER JOIN {$wpdb->posts} p ON p.ID = d.post_id
			LEFT JOIN (
				SELECT tr.object_id, MIN( tt.term_id ) AS category_id
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				WHERE tt.taxonomy = %s
				GROUP BY tr.object_id
			) place_category ON place_category.object_id = p.ID
			LEFT JOIN {$wpdb->terms} t ON t.term_id = place_category.category_id
			WHERE p.post_type = %s
				AND p.post_status = %s
				AND p.ID <> %d
				AND d.latitude IS NOT NULL AND d.latitude <> ''
				AND d.longitude IS NOT NULL AND d.longitude <> ''
			HAVING distance_km <= %f
			ORDER BY distance_km ASC, p.ID ASC
			LIMIT %d";
		$prepared = $wpdb->prepare(
			$sql,
			$earth_radius,
			$latitude,
			$latitude,
			$longitude,
			'gd_placecategory',
			'gd_place',
			'publish',
			$post_id,
			$radius_km,
			$candidate_limit
		);
		$rows = $wpdb->get_results( $prepared );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$places = array();
		foreach ( $rows as $row ) {
			$places[] = array(
				'id'          => (int) $row->ID,
				'title'       => (string) $row->post_title,
				'url'         => get_permalink( (int) $row->ID ),
				'category'    => $row->category_name ? (string) $row->category_name : __( 'מקום', 'gonorth-place-data' ),
				'category_id' => (int) $row->category_id,
				'distance_km' => round( (float) $row->distance_km, 2 ),
			);
		}

		return $places;
	}

	/**
	 * Query category-filtered candidates with the same Haversine calculation.
	 *
	 * @param int           $post_id Current place ID.
	 * @param float         $latitude Current latitude.
	 * @param float         $longitude Current longitude.
	 * @param array<string> $category_slugs Allowed category slugs.
	 * @param float         $radius_km Maximum radius.
	 * @param int           $limit Result limit.
	 * @param array<int>    $exclude_ids Additional IDs to exclude.
	 * @return array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}>
	 */
	private static function query_category_candidates( int $post_id, float $latitude, float $longitude, array $category_slugs, float $radius_km, int $limit, array $exclude_ids ): array {
		global $wpdb;

		$detail_table          = $wpdb->prefix . 'geodir_gd_place_detail';
		$earth_radius          = 6371.0088;
		$distance_sql          = '( %f * 2 * ASIN( SQRT( POWER( SIN( ( %f - d.latitude ) * PI() / 180 / 2 ), 2 ) + COS( %f * PI() / 180 ) * COS( d.latitude * PI() / 180 ) * POWER( SIN( ( %f - d.longitude ) * PI() / 180 / 2 ), 2 ) ) ) )';
		$category_placeholders = implode( ', ', array_fill( 0, count( $category_slugs ), '%s' ) );
		$excluded              = array_values( array_unique( array_merge( array( $post_id ), $exclude_ids ) ) );
		$exclude_placeholders  = implode( ', ', array_fill( 0, count( $excluded ), '%d' ) );

		$sql = "SELECT p.ID, p.post_title, MIN( t.term_id ) AS category_id, MIN( t.name ) AS category_name, {$distance_sql} AS distance_km
			FROM {$detail_table} d
			INNER JOIN {$wpdb->posts} p ON p.ID = d.post_id
			INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s
				AND t.slug IN ( {$category_placeholders} )
				AND p.post_type = %s
				AND p.post_status = %s
				AND p.ID NOT IN ( {$exclude_placeholders} )
				AND d.latitude IS NOT NULL AND d.latitude <> ''
				AND d.longitude IS NOT NULL AND d.longitude <> ''
			GROUP BY p.ID, p.post_title, d.latitude, d.longitude
			HAVING distance_km <= %f
			ORDER BY distance_km ASC, p.ID ASC
			LIMIT %d";
		$params = array_merge(
			array( $earth_radius, $latitude, $latitude, $longitude, 'gd_placecategory' ),
			$category_slugs,
			array( 'gd_place', 'publish' ),
			$excluded,
			array( $radius_km, $limit )
		);
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$places = array();
		foreach ( $rows as $row ) {
			$places[] = array(
				'id'          => (int) $row->ID,
				'title'       => (string) $row->post_title,
				'url'         => get_permalink( (int) $row->ID ),
				'category'    => $row->category_name ? (string) $row->category_name : __( 'מקום', 'gonorth-place-data' ),
				'category_id' => (int) $row->category_id,
				'distance_km' => round( (float) $row->distance_km, 2 ),
			);
		}
		return $places;
	}

	/**
	 * Balance proximity with a small penalty for repeated categories.
	 *
	 * @param array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}> $candidates Ordered candidates.
	 * @param int $limit Result limit.
	 * @return array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}>
	 */
	private static function prefer_category_diversity( array $candidates, int $limit ): array {
		$selected = array();
		$used_ids = array();
		$category_counts = array();

		while ( count( $selected ) < $limit && count( $used_ids ) < count( $candidates ) ) {
			$best = null;
			$best_score = PHP_FLOAT_MAX;
			foreach ( $candidates as $candidate ) {
				if ( isset( $used_ids[ $candidate['id'] ] ) ) {
					continue;
				}
				$category_key = $candidate['category_id'] ?: 'uncategorized';
				$repeat_count = $category_counts[ $category_key ] ?? 0;
				$score = $candidate['distance_km'] + ( $repeat_count * self::CATEGORY_REPEAT_PENALTY_KM );
				if ( $score < $best_score || ( $score === $best_score && ( null === $best || $candidate['distance_km'] < $best['distance_km'] ) ) ) {
					$best = $candidate;
					$best_score = $score;
				}
			}
			if ( null === $best ) {
				break;
			}
			$selected[] = $best;
			$used_ids[ $best['id'] ] = true;
			$category_key = $best['category_id'] ?: 'uncategorized';
			$category_counts[ $category_key ] = ( $category_counts[ $category_key ] ?? 0 ) + 1;
		}

		usort(
			$selected,
			static fn( array $a, array $b ): int => $a['distance_km'] <=> $b['distance_km']
		);
		return $selected;
	}

	/**
	 * Format distance without implying false precision.
	 *
	 * @param float $distance_km Distance in kilometres.
	 * @return string
	 */
	public static function format_distance( float $distance_km ): string {
		$decimals = $distance_km < 10 ? 1 : 0;
		return number_format_i18n( $distance_km, $decimals );
	}
}
