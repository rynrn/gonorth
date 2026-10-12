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
			SUM(google_review_count IS NOT NULL AND google_review_count > 0) AS google_review_counts,
			SUM(google_checked_at IS NOT NULL AND google_checked_at != '') AS google_freshness,
			SUM(google_place_id IS NOT NULL AND google_place_id != ''
				AND google_rating IS NOT NULL AND google_rating > 0
				AND google_review_count IS NOT NULL AND google_review_count > 0
				AND google_checked_at IS NOT NULL AND google_checked_at != '') AS complete_google_bundles,
			SUM(google_checked_at IS NOT NULL AND google_checked_at != ''
				AND google_checked_at < UTC_TIMESTAMP() - INTERVAL 30 DAY) AS google_stale_30d
			FROM {$table} d
			INNER JOIN {$wpdb->posts} p ON p.ID = d.post_id
			WHERE p.post_type = 'gd_place' AND p.post_status = 'publish'",
			ARRAY_A
		);
		WP_CLI\Utils\format_items( 'table', array( $row ), array_keys( $row ) );
	}

	/**
	 * Export the canonical data projection for local ledger reconciliation.
	 *
	 * This command is intentionally read-only. It only emits published place
	 * fields, so the local place-facts CLI can compare its approved facts before
	 * any later synchronization command is allowed to write.
	 */
	public function export(): void {
		$ids = get_posts(
			array(
				'post_type'      => 'gd_place',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		$records = array();
		foreach ( $ids as $post_id ) {
			$data      = GoNorth_Place_Data::get( (int) $post_id );
			$records[] = array(
				'id'                   => (int) $post_id,
				'address'              => $data['address'],
				'city'                 => $data['city'],
				'phone'                => $data['phone'],
				'website'              => $data['website'],
				'business_hours'       => $data['business_hours'],
				'practical_checked_at' => $data['practical_checked_at'],
				'google'               => $data['google'],
			);
		}

		WP_CLI::line(
			wp_json_encode(
				array(
					'generated_at' => gmdate( 'c' ),
					'records'      => $records,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
	}

	/**
	 * Validate a place-facts synchronization package, optionally apply it, or
	 * restore a backup produced by an earlier apply.
	 *
	 * ## OPTIONS
	 *
	 * <package>
	 * : JSON package emitted by place-facts export-wordpress-sync.
	 *
	 * [--apply]
	 * : Apply after all records pass the optimistic-concurrency dry run.
	 *
	 * [--backup-dir=<path>]
	 * : Writable server directory for the pre-apply backup.
	 *
	 * ## EXAMPLES
	 *
	 *     wp gonorth place-data sync /tmp/place-facts.json
	 *     wp gonorth place-data sync /tmp/place-facts.json --apply
	 *
	 * @param array<int,string>    $args Positional arguments.
	 * @param array<string,string> $assoc_args Named arguments.
	 */
	public function sync( array $args, array $assoc_args ): void {
		$package = $this->read_json_file( $args[0] ?? '' );
		if ( 'gonorth-place-facts-wordpress-sync/v1' !== ( $package['format'] ?? '' ) ) {
			WP_CLI::error( 'Unsupported place-facts sync package format.' );
		}
		$changes = $package['changes'] ?? null;
		if ( ! is_array( $changes ) ) {
			WP_CLI::error( 'Sync package does not contain changes.' );
		}

		$results = array();
		$prepared = array();
		foreach ( $changes as $change ) {
			$result = $this->prepare_sync_change( $change );
			if ( is_wp_error( $result ) ) {
				$results[] = array(
					'change_ids' => is_array( $change ) && isset( $change['change_ids'] ) ? array_map( 'absint', (array) $change['change_ids'] ) : array(),
					'status'     => 'error',
					'message'    => $result->get_error_message(),
				);
				continue;
			}
			$prepared[] = $result;
			$results[]  = array(
				'change_ids' => $result['change_ids'],
				'status'     => empty( $assoc_args['apply'] ) ? 'would_sync' : 'ready',
				'message'    => empty( $assoc_args['apply'] ) ? 'Validated without writing.' : 'Validated for apply.',
			);
		}

		if ( ! empty( $assoc_args['apply'] ) && ! array_filter( $results, static fn( array $row ): bool => 'error' === $row['status'] ) ) {
			$backup = $this->apply_sync_changes( $prepared, $assoc_args['backup-dir'] ?? '' );
			foreach ( $results as &$result ) {
				$result['status']  = 'synced';
				$result['message'] = 'Applied successfully.';
				$result['backup']  = $backup;
			}
			unset( $result );
		}

		WP_CLI::line(
			wp_json_encode(
				array(
					'format'  => 'gonorth-place-facts-wordpress-sync-receipt/v1',
					'applied' => ! empty( $assoc_args['apply'] ),
					'results' => $results,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
	}

	/**
	 * @param mixed $change A single untrusted package change.
	 * @return array<string,mixed>|WP_Error
	 */
	private function prepare_sync_change( $change ) {
		if ( ! is_array( $change ) ) {
			return new WP_Error( 'invalid_package_change', 'Each sync change must be an object.' );
		}
		$post_id = absint( $change['wordpress_id'] ?? 0 );
		if ( ! $post_id || 'gd_place' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			return new WP_Error( 'invalid_place', 'Sync change references a non-published GeoDirectory place.' );
		}
		$change_ids = array_values( array_filter( array_map( 'absint', (array) ( $change['change_ids'] ?? array() ) ) ) );
		$updates    = $change['updates'] ?? null;
		$expected   = $change['expected'] ?? null;
		if ( ! $change_ids || ! is_array( $updates ) || ! is_array( $expected ) || ! $updates ) {
			return new WP_Error( 'invalid_package_change', 'Sync change is missing IDs, expected values, or updates.' );
		}
		$allowed = array( 'street', 'city', 'phone', 'website', 'business_hours', 'google_place_id', 'google_rating', 'google_review_count' );
		foreach ( array_keys( $updates ) as $field ) {
			if ( ! in_array( $field, $allowed, true ) || ! array_key_exists( $field, $expected ) ) {
				return new WP_Error( 'invalid_package_field', 'Sync change contains an unsupported or unchecked field.' );
			}
		}

		$current = GoNorth_Place_Data::get( $post_id );
		$map     = array(
			'street'          => 'address',
			'city'            => 'city',
			'phone'           => 'phone',
			'website'         => 'website',
			'business_hours'  => 'business_hours',
			'google_place_id' => 'google.place_id',
			'google_rating'   => 'google.rating',
			'google_review_count' => 'google.review_count',
		);
		foreach ( $updates as $field => $value ) {
			$current_value = $this->nested_value( $current, $map[ $field ] );
			if ( (string) $current_value !== (string) $expected[ $field ] ) {
				return new WP_Error( 'stale_wordpress_value', sprintf( 'Place %d has changed in WordPress for %s; re-run reconciliation.', $post_id, $field ) );
			}
		}
		$verification = is_array( $change['verification'] ?? null ) ? $change['verification'] : array();
		return array(
			'post_id'              => $post_id,
			'change_ids'           => $change_ids,
			'updates'              => $updates,
			'practical_verified'   => ! empty( $verification['practical_verified'] ),
			'google_verified'      => ! empty( $verification['google_verified'] ),
			'before'               => $current,
		);
	}

	/** @param array<string,mixed> $record */
	private function nested_value( array $record, string $path ) {
		$value = $record;
		foreach ( explode( '.', $path ) as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return null;
			}
			$value = $value[ $segment ];
		}
		return $value;
	}

	/** @param array<int,array<string,mixed>> $prepared */
	private function apply_sync_changes( array $prepared, string $backup_dir ): string {
		global $wpdb;
		$backup = $this->write_sync_backup( $prepared, $backup_dir );
		$wpdb->query( 'START TRANSACTION' );
		try {
			foreach ( $prepared as $change ) {
				$result = GoNorth_Place_Data::update(
					$change['post_id'],
					$change['updates'],
					$change['practical_verified'],
					$change['google_verified']
				);
				if ( is_wp_error( $result ) ) {
					throw new RuntimeException( $result->get_error_message() );
				}
			}
			$wpdb->query( 'COMMIT' );
		} catch ( Throwable $error ) {
			$wpdb->query( 'ROLLBACK' );
			foreach ( $prepared as $change ) {
				wp_cache_delete( 'gd_post_' . $change['post_id'], 'gd_post' );
				clean_post_cache( $change['post_id'] );
			}
			WP_CLI::error( 'Sync rolled back: ' . $error->getMessage() );
		}
		foreach ( $prepared as $change ) {
			wp_cache_delete( 'gd_post_' . $change['post_id'], 'gd_post' );
			clean_post_cache( $change['post_id'] );
		}
		return $backup;
	}

	/** @param array<int,array<string,mixed>> $prepared */
	private function write_sync_backup( array $prepared, string $backup_dir ): string {
		$uploads = wp_upload_dir();
		$dir     = $backup_dir ? $backup_dir : trailingslashit( $uploads['basedir'] ) . 'gonorth-place-data-backups';
		if ( ! wp_mkdir_p( $dir ) || ! is_writable( $dir ) ) {
			WP_CLI::error( 'Sync backup directory is not writable.' );
		}
		$path = trailingslashit( $dir ) . 'place-sync-' . gmdate( 'Ymd-His' ) . '.json';
		if ( file_exists( $path ) ) {
			WP_CLI::error( 'Refusing to overwrite an existing sync backup.' );
		}
		$data = array(
			'format'     => 'gonorth-place-facts-wordpress-backup/v1',
			'created_at' => gmdate( 'c' ),
			'changes'    => $prepared,
		);
		if ( false === file_put_contents( $path, wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) ) {
			WP_CLI::error( 'Could not write sync backup.' );
		}
		return $path;
	}

	/** @return array<string,mixed> */
	private function read_json_file( string $path ): array {
		if ( ! $path || ! is_readable( $path ) ) {
			WP_CLI::error( 'Sync JSON file is not readable.' );
		}
		$decoded = json_decode( (string) file_get_contents( $path ), true );
		if ( ! is_array( $decoded ) || JSON_ERROR_NONE !== json_last_error() ) {
			WP_CLI::error( 'Sync JSON file is invalid: ' . json_last_error_msg() );
		}
		return $decoded;
	}

	/**
	 * Restore canonical values from a backup created by the sync command.
	 *
	 * ## OPTIONS
	 *
	 * <backup>
	 * : Backup JSON path printed by a successful sync --apply run.
	 *
	 * @param array<int,string> $args Positional arguments.
	 */
	public function rollback( array $args ): void {
		$backup = $this->read_json_file( $args[0] ?? '' );
		if ( 'gonorth-place-facts-wordpress-backup/v1' !== ( $backup['format'] ?? '' ) || ! is_array( $backup['changes'] ?? null ) ) {
			WP_CLI::error( 'Unsupported place-facts sync backup format.' );
		}
		global $wpdb;
		$prepared = array();
		foreach ( $backup['changes'] as $change ) {
			if ( ! is_array( $change ) || empty( $change['post_id'] ) || ! is_array( $change['before'] ?? null ) ) {
				WP_CLI::error( 'Sync backup contains an invalid change.' );
			}
			$post_id = absint( $change['post_id'] );
			if ( 'gd_place' !== get_post_type( $post_id ) ) {
				WP_CLI::error( sprintf( 'Backup references an invalid place: %d.', $post_id ) );
			}
			$prepared[] = array( 'post_id' => $post_id, 'data' => $this->restore_data( $change['before'] ) );
		}
		$wpdb->query( 'START TRANSACTION' );
		try {
			foreach ( $prepared as $change ) {
				$result = GoNorth_Place_Data::restore( $change['post_id'], $change['data'] );
				if ( is_wp_error( $result ) ) {
					throw new RuntimeException( $result->get_error_message() );
				}
			}
			$wpdb->query( 'COMMIT' );
		} catch ( Throwable $error ) {
			$wpdb->query( 'ROLLBACK' );
			WP_CLI::error( 'Rollback failed: ' . $error->getMessage() );
		}
		foreach ( $prepared as $change ) {
			wp_cache_delete( 'gd_post_' . $change['post_id'], 'gd_post' );
			clean_post_cache( $change['post_id'] );
		}
		WP_CLI::success( sprintf( 'Restored %d place records from backup.', count( $prepared ) ) );
	}

	/** @param array<string,mixed> $before @return array<string,mixed> */
	private function restore_data( array $before ): array {
		$google = is_array( $before['google'] ?? null ) ? $before['google'] : array();
		return array(
			'street'               => $before['address'] ?? '',
			'city'                 => $before['city'] ?? '',
			'phone'                => $before['phone'] ?? '',
			'website'              => $before['website'] ?? '',
			'business_hours'       => $before['business_hours'] ?? '',
			'google_place_id'      => $google['place_id'] ?? '',
			'google_rating'        => $google['rating'] ?? '',
			'google_review_count'  => $google['review_count'] ?? '',
			'practical_checked_at' => $before['practical_checked_at'] ?? '',
			'google_checked_at'    => $google['checked_at'] ?? '',
		);
	}
	/**
	 * Resolve and optionally persist complete Google rating bundles.
	 *
	 * The command is dry-run by default. It never accepts a key on the command
	 * line; configure `gonorth_google_server_api_key`, the
	 * `GONORTH_GOOGLE_SERVER_API_KEY` constant or environment variable instead.
	 *
	 * ## OPTIONS
	 *
	 * [--apply]
	 * : Persist safe, complete responses. Without this flag nothing is written.
	 *
	 * [--post_id=<id>]
	 * : Process one published place.
	 *
	 * [--limit=<number>]
	 * : Limit the number of places processed.
	 *
	 * [--force]
	 * : Refresh even when the bundle is less than 30 days old.
	 *
	 * [--format=<format>]
	 * : Output format: table (default) or json. JSON includes Place IDs for review.
	 *
	 * ## EXAMPLES
	 *
	 *     wp gonorth place-data google_sync --post_id=48
	 *     wp gonorth place-data google_sync --limit=10 --apply
	 *
	 * @param array<int,string>    $args Positional arguments.
	 * @param array<string,string> $assoc_args Named arguments.
	 */
	public function google_sync( array $args, array $assoc_args ): void {
		unset( $args );
		$apply = WP_CLI\Utils\get_flag_value( $assoc_args, 'apply', false );
		$force = WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );
		$limit  = isset( $assoc_args['limit'] ) ? max( 1, absint( $assoc_args['limit'] ) ) : -1;
		$format = isset( $assoc_args['format'] ) ? sanitize_key( (string) $assoc_args['format'] ) : 'table';
		$key    = self::google_server_api_key();
		if ( ! $key ) {
			WP_CLI::error( 'Configure a server-authorized Google Places API key before syncing.' );
		}
		if ( ! in_array( $format, array( 'table', 'json' ), true ) ) {
			WP_CLI::error( 'Format must be table or json.' );
		}

		$query = array(
			'post_type'      => 'gd_place',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);
		if ( ! empty( $assoc_args['post_id'] ) ) {
			$query['post__in']       = array( absint( $assoc_args['post_id'] ) );
			$query['posts_per_page'] = 1;
		}

		$rows     = array();
		$stored   = 0;
		$dry_run  = 0;
		$skipped  = 0;
		$failures = 0;
		foreach ( get_posts( $query ) as $post_id ) {
			$data       = GoNorth_Place_Data::get( (int) $post_id );
			$checked_at = strtotime( (string) ( $data['google']['checked_at'] ?? '' ) );
			if ( ! $force && $checked_at && $checked_at >= time() - 30 * DAY_IN_SECONDS ) {
				++$skipped;
				$rows[] = array( 'id' => $post_id, 'title' => get_the_title( $post_id ), 'status' => 'fresh', 'candidate' => '', 'km' => '', 'score' => '', 'rating' => '', 'reviews' => '', 'business' => '', 'place_id' => '', 'moved_place_id' => '' );
				continue;
			}

			$bundle = GoNorth_Google_Place_Sync::resolve( (int) $post_id, $key );
			if ( is_wp_error( $bundle ) ) {
				++$failures;
				$error_data = $bundle->get_error_data();
				$error_data = is_array( $error_data ) ? $error_data : array();
				$rows[] = array(
					'id'             => $post_id,
					'title'          => get_the_title( $post_id ),
					'status'         => $bundle->get_error_code(),
					'candidate'      => $error_data['candidate_name'] ?? '',
					'km'             => isset( $error_data['distance_km'] ) ? number_format( (float) $error_data['distance_km'], 2, '.', '' ) : '',
					'score'          => isset( $error_data['name_score'] ) ? number_format( (float) $error_data['name_score'], 2, '.', '' ) : '',
					'rating'         => $error_data['google_rating'] ?? '',
					'reviews'        => $error_data['google_review_count'] ?? '',
					'business'       => $error_data['business_status'] ?? '',
					'place_id'       => $error_data['google_place_id'] ?? '',
					'moved_place_id' => $error_data['moved_place_id'] ?? '',
				);
				continue;
			}

			$business_status = (string) ( $bundle['business_status'] ?? 'unknown' );
			if ( in_array( $business_status, array( 'closed_temporarily', 'closed_permanently' ), true ) ) {
				++$failures;
				$rows[] = array(
					'id'        => $post_id,
					'title'     => get_the_title( $post_id ),
					'status'    => 'closure_review',
					'candidate' => $bundle['candidate_name'],
					'km'        => number_format( (float) $bundle['distance_km'], 2, '.', '' ),
					'score'     => number_format( (float) $bundle['name_score'], 2, '.', '' ),
					'rating'    => $bundle['google_rating'],
					'reviews'   => $bundle['google_review_count'],
					'business'      => $business_status,
					'place_id'      => $bundle['google_place_id'],
					'moved_place_id' => $bundle['moved_place_id'],
				);
				continue;
			}

			$status = 'dry-run';
			if ( $apply ) {
				$result = GoNorth_Google_Place_Sync::store( (int) $post_id, $bundle );
				if ( is_wp_error( $result ) ) {
					++$failures;
					$status = $result->get_error_code();
				} else {
					++$stored;
					$status = 'stored';
				}
			} else {
				++$dry_run;
			}
			$rows[] = array(
				'id'        => $post_id,
				'title'     => get_the_title( $post_id ),
				'status'    => $status,
				'candidate' => $bundle['candidate_name'],
				'km'        => number_format( (float) $bundle['distance_km'], 2, '.', '' ),
				'score'     => number_format( (float) $bundle['name_score'], 2, '.', '' ),
				'rating'    => $bundle['google_rating'],
				'reviews'   => $bundle['google_review_count'],
				'business'      => $business_status,
				'place_id'      => $bundle['google_place_id'],
				'moved_place_id' => $bundle['moved_place_id'],
			);
		}

		if ( 'json' === $format ) {
			WP_CLI::line(
				wp_json_encode(
					array(
						'summary' => array(
							'stored'           => $stored,
							'dry_run_matches'  => $dry_run,
							'fresh_skipped'    => $skipped,
							'failures_rejected'=> $failures,
							'applied'          => $apply,
						),
						'results' => $rows,
					),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
				)
			);
			return;
		}

		WP_CLI\Utils\format_items( 'table', $rows, array( 'id', 'title', 'status', 'candidate', 'km', 'score', 'rating', 'reviews', 'business' ) );
		WP_CLI::log( sprintf( 'Stored: %d; dry-run matches: %d; fresh skipped: %d; failures/rejected: %d.', $stored, $dry_run, $skipped, $failures ) );
		if ( $failures ) {
			WP_CLI::warning( 'Some places require manual matching, closure review or API configuration; no weak/closed match was stored.' );
		}
		WP_CLI::success( $apply ? 'Google sync completed.' : 'Google sync dry-run completed; no data changed.' );
	}

	/** Resolve the server-only API key without exposing it in CLI arguments. */
	private static function google_server_api_key(): string {
		if ( defined( 'GONORTH_GOOGLE_SERVER_API_KEY' ) ) {
			return trim( (string) constant( 'GONORTH_GOOGLE_SERVER_API_KEY' ) );
		}
		$environment = getenv( 'GONORTH_GOOGLE_SERVER_API_KEY' );
		if ( is_string( $environment ) && '' !== trim( $environment ) ) {
			return trim( $environment );
		}
		$server_key = get_option( 'gonorth_google_server_api_key' );
		if ( is_string( $server_key ) && '' !== trim( $server_key ) ) {
			return trim( $server_key );
		}
		$fallback = get_option( 'gonorth_google_api_key' );
		return is_string( $fallback ) ? trim( $fallback ) : '';
	}
}
