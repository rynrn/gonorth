<?php
/**
 * WP-CLI commands for schema installation, migration and audits.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Place_Data_CLI {
	/**
	 * Install or update the canonical GeoDirectory fields.
	 */
	public function install_schema(): void {
		$result = GoNorth_Place_Data::install_schema();
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::success( 'GoNorth place-data schema is installed.' );
	}

	/**
	 * Copy legacy GoNorth values into canonical GeoDirectory columns.
	 *
	 * ## OPTIONS
	 *
	 * [--post_id=<id>]
	 * : Migrate one place only.
	 *
	 * [--dry-run]
	 * : Report changes without writing them.
	 *
	 * [--force]
	 * : Replace a non-empty canonical value.
	 *
	 * ## EXAMPLES
	 *
	 *     wp gonorth place-data migrate --post_id=86 --dry-run
	 *     wp gonorth place-data migrate
	 *
	 * @param array<int,string>    $args Positional arguments.
	 * @param array<string,string> $assoc_args Named arguments.
	 */
	public function migrate( array $args, array $assoc_args ): void {
		unset( $args );
		$dry_run = WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
		$force   = WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );
		$query   = array(
			'post_type'      => 'gd_place',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);
		if ( ! empty( $assoc_args['post_id'] ) ) {
			$query['post__in'] = array( absint( $assoc_args['post_id'] ) );
		}

		$ids      = get_posts( $query );
		$changed  = 0;
		$skipped  = 0;
		$failures = 0;
		$map      = array(
			'street'        => 'geodir_post_address',
			'city'          => 'geodir_post_city',
			'latitude'      => 'geodir_post_latitude',
			'longitude'     => 'geodir_post_longitude',
			'phone'         => 'geodir_post_phone',
			'website'       => 'geodir_post_website',
			'google_rating' => 'geodir_post_rating',
		);

		foreach ( $ids as $post_id ) {
			$detail  = function_exists( 'geodir_get_post_info' ) ? geodir_get_post_info( $post_id ) : null;
			$updates = array();
			foreach ( $map as $canonical => $legacy ) {
				$old = GoNorth_Place_Data::get_legacy_meta( (int) $post_id, $legacy );
				$new = is_object( $detail ) && isset( $detail->{$canonical} ) ? $detail->{$canonical} : null;
				if ( '' === $old || null === $old || ( ! $force && '' !== $new && null !== $new ) ) {
					continue;
				}
				$updates[ $canonical ] = $old;
			}

			if ( ! $updates ) {
				++$skipped;
				continue;
			}

			WP_CLI::log( sprintf( '%s place %d: %s', $dry_run ? 'Would migrate' : 'Migrating', $post_id, implode( ', ', array_keys( $updates ) ) ) );
			if ( $dry_run ) {
				++$changed;
				continue;
			}

			$result = GoNorth_Place_Data::update( (int) $post_id, $updates );
			if ( is_wp_error( $result ) ) {
				++$failures;
				WP_CLI::warning( sprintf( 'Place %d: %s', $post_id, $result->get_error_message() ) );
				continue;
			}
			update_post_meta( $post_id, '_gonorth_place_data_migrated_at', gmdate( 'c' ) );
			++$changed;
		}

		WP_CLI::log( sprintf( 'Changed: %d; skipped: %d; failures: %d.', $changed, $skipped, $failures ) );
		if ( $failures ) {
			WP_CLI::error( 'Migration completed with failures.' );
		}
		WP_CLI::success( $dry_run ? 'Dry run completed.' : 'Migration completed.' );
	}

	/**
	 * Audit canonical coverage without changing data.
	 */
	public function audit(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'geodir_gd_place_detail';
		$row   = $wpdb->get_row(
			"SELECT COUNT(*) AS published_places,
			SUM(latitude IS NOT NULL AND latitude != '') AS coordinates,
			SUM(street IS NOT NULL AND street != '') AS addresses,
			SUM(phone IS NOT NULL AND phone != '') AS phones,
			SUM(website IS NOT NULL AND website != '') AS websites,
			SUM(business_hours IS NOT NULL AND business_hours != '') AS business_hours,
			SUM(google_rating IS NOT NULL AND google_rating > 0) AS google_ratings,
			SUM(google_checked_at IS NOT NULL AND google_checked_at != '') AS google_freshness
			FROM {$table} d
			INNER JOIN {$wpdb->posts} p ON p.ID = d.post_id
			WHERE p.post_type = 'gd_place' AND p.post_status = 'publish'",
			ARRAY_A
		);
		WP_CLI\Utils\format_items( 'table', array( $row ), array_keys( $row ) );
	}
}
