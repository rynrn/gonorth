<?php
/**
 * WP-CLI commands for place attributes.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Place_Attributes_CLI {
	/** Install or update centralized attribute definitions. */
	public function install(): void {
		$result = GoNorth_Place_Attributes::install_definitions();
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::success( 'GoNorth place attributes are installed.' );
	}

	/** List centralized definitions and current usage. */
	public function definitions(): void {
		$rows = array();
		foreach ( GoNorth_Place_Attributes::definitions() as $slug => $definition ) {
			$term = get_term_by( 'slug', $slug, GoNorth_Place_Attributes::TAXONOMY );
			$rows[] = array(
				'slug'        => $slug,
				'label'       => $definition['label'],
				'yes_places'  => $term ? (int) $term->count : 0,
				'description' => $definition['description'],
			);
		}
		WP_CLI\Utils\format_items( 'table', $rows, array( 'slug', 'label', 'yes_places', 'description' ) );
	}

	/**
	 * Set one verified attribute state on a place.
	 *
	 * ## OPTIONS
	 *
	 * <post_id>
	 * : Existing gd_place ID.
	 *
	 * <attribute>
	 * : Central attribute slug.
	 *
	 * <state>
	 * : yes, no or unknown.
	 *
	 * @param array<int,string> $args Positional arguments.
	 */
	public function set( array $args ): void {
		$post_id = absint( $args[0] ?? 0 );
		$slug    = sanitize_key( $args[1] ?? '' );
		$state   = sanitize_key( $args[2] ?? '' );
		$result  = GoNorth_Place_Attributes::set_state( $post_id, $slug, $state );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::success( sprintf( 'Place %d: %s=%s.', $post_id, $slug, $state ) );
	}

	/**
	 * Query places that match every supplied attribute.
	 *
	 * ## OPTIONS
	 *
	 * <attributes>
	 * : Comma-separated attribute slugs.
	 *
	 * @param array<int,string> $args Positional arguments.
	 */
	public function query( array $args ): void {
		$attributes = array_filter( array_map( 'trim', explode( ',', $args[0] ?? '' ) ) );
		$query      = GoNorth_Place_Attributes::query( $attributes, array( 'posts_per_page' => -1 ) );
		if ( is_wp_error( $query ) ) {
			WP_CLI::error( $query->get_error_message() );
		}
		$rows = array();
		foreach ( $query->posts as $post ) {
			$rows[] = array( 'ID' => $post->ID, 'title' => $post->post_title );
		}
		WP_CLI\Utils\format_items( 'table', $rows, array( 'ID', 'title' ) );
		WP_CLI::log( sprintf( 'Matches: %d.', count( $rows ) ) );
	}

	/** Audit contradictions and usage counts without changing data. */
	public function audit(): void {
		$contradictions = 0;
		$explicit_no    = 0;
		$places         = get_posts(
			array(
				'post_type'      => 'gd_place',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		foreach ( $places as $post_id ) {
			$false = get_post_meta( $post_id, GoNorth_Place_Attributes::FALSE_META, true );
			$false = is_array( $false ) ? $false : array();
			$explicit_no += count( $false );
			foreach ( $false as $slug ) {
				if ( has_term( $slug, GoNorth_Place_Attributes::TAXONOMY, $post_id ) ) {
					++$contradictions;
				}
			}
		}
		WP_CLI::log( sprintf( 'Published places: %d; explicit no states: %d; contradictions: %d.', count( $places ), $explicit_no, $contradictions ) );
		if ( $contradictions ) {
			WP_CLI::error( 'Attribute contradictions found.' );
		}
		WP_CLI::success( 'Attribute audit passed.' );
	}
}
