<?php
/**
 * Contextual trip-planning recommendations for GoNorth places.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Trip_Planner {
	/**
	 * Register the contextual component after the location block and before FAQ.
	 */
	public static function init(): void {
		add_filter( 'gonorth_nearby_places_component_enabled', array( __CLASS__, 'disable_generic_nearby' ), 10, 2 );
		add_action( 'geodir_single_tab_content_after', array( __CLASS__, 'render_page_section' ), 25 );
	}

	/**
	 * Central recommendation rules. Rules can evolve without changing rendering.
	 *
	 * @return array<string,array{label:string,title:string,categories:array<string>,radius_km:float,limit:int}>
	 */
	public static function rules(): array {
		$rules = array(
			'coffee' => array(
				'label'      => __( 'עצירת קפה', 'gonorth-place-data' ),
				'title'      => __( 'קפה בדרך', 'gonorth-place-data' ),
				'categories' => array( 'agalot-kafe', 'batei-kafe' ),
				'radius_km'  => 18.0,
				'limit'      => 2,
			),
			'food' => array(
				'label'      => __( 'אוכל באזור', 'gonorth-place-data' ),
				'title'      => __( 'איפה אוכלים', 'gonorth-place-data' ),
				'categories' => array( 'restaurants-sub' ),
				'radius_km'  => 22.0,
				'limit'      => 2,
			),
			'activity' => array(
				'label'      => __( 'להמשיך את היום', 'gonorth-place-data' ),
				'title'      => __( 'עוד מה לעשות', 'gonorth-place-data' ),
				'categories' => array( 'atraktziot-main', 'tours' ),
				'radius_km'  => 28.0,
				'limit'      => 2,
			),
			'nature' => array(
				'label'      => __( 'לצאת לטבע', 'gonorth-place-data' ),
				'title'      => __( 'טבע ומסלול קרוב', 'gonorth-place-data' ),
				'categories' => array( 'teva' ),
				'radius_km'  => 30.0,
				'limit'      => 2,
			),
			'stay' => array(
				'label'      => __( 'להישאר באזור', 'gonorth-place-data' ),
				'title'      => __( 'איפה לנים', 'gonorth-place-data' ),
				'categories' => array( 'accommodation' ),
				'radius_km'  => 35.0,
				'limit'      => 2,
			),
		);

		return apply_filters( 'gonorth_trip_planner_rules', $rules );
	}

	/**
	 * Build non-overlapping recommendation groups from configured rules.
	 *
	 * @param int $post_id Place ID.
	 * @return array<string,array{label:string,title:string,places:array<int,array{id:int,title:string,url:string,category:string,category_id:int,distance_km:float}>}>
	 */
	public static function get_groups( int $post_id ): array {
		if ( 'gd_place' !== get_post_type( $post_id ) || ! class_exists( 'GoNorth_Nearby_Places' ) ) {
			return array();
		}

		$groups   = array();
		$used_ids = array();
		foreach ( self::rules() as $key => $rule ) {
			if ( empty( $rule['categories'] ) || empty( $rule['radius_km'] ) || empty( $rule['limit'] ) ) {
				continue;
			}
			$places = GoNorth_Nearby_Places::get_nearby_places_by_categories(
				$post_id,
				(array) $rule['categories'],
				(int) $rule['limit'],
				(float) $rule['radius_km'],
				$used_ids
			);
			if ( ! $places ) {
				continue;
			}
			foreach ( $places as $place ) {
				$used_ids[] = $place['id'];
			}
			$groups[ sanitize_key( $key ) ] = array(
				'label'  => (string) $rule['label'],
				'title'  => (string) $rule['title'],
				'places' => $places,
			);
		}

		return apply_filters( 'gonorth_trip_planner_groups', $groups, $post_id );
	}

	/**
	 * Suppress the older generic nearby block when contextual groups take over.
	 *
	 * @param bool $enabled Existing state.
	 * @param int  $post_id Place ID.
	 */
	public static function disable_generic_nearby( bool $enabled, int $post_id ): bool {
		return is_singular( 'gd_place' ) && $post_id === get_queried_object_id() ? false : $enabled;
	}

	/**
	 * Return a small decorative line icon for a recommendation context.
	 *
	 * The markup is code-owned and contains no dynamic values.
	 *
	 * @param string $key Recommendation group key.
	 * @return string
	 */
	private static function get_group_icon( string $key ): string {
		$icons = array(
			'coffee'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 8h11v6a5 5 0 0 1-5 5H10a5 5 0 0 1-5-5V8Z"/><path d="M16 10h1.5a2.5 2.5 0 0 1 0 5H16M8 4v2m4-2v2"/></svg>',
			'food'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3v7m-3-7v4a3 3 0 0 0 6 0V3M7 10v11M16 3v18m0-18c3 2 4 5 4 8h-4"/></svg>',
			'activity' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/></svg>',
			'nature'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 4C11 4 5 8 5 14c0 3 2 5 5 5 6 0 10-6 10-15Z"/><path d="M4 21c3-6 7-9 12-12"/></svg>',
			'stay'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 9-7 9 7"/><path d="M5 10v10h14V10M9 20v-6h6v6"/></svg>',
		);

		return $icons[ $key ] ?? $icons['activity'];
	}

	/**
	 * Render the concise trip-planning section.
	 *
	 * @param object $tab GeoDirectory tab object.
	 */
	public static function render_page_section( $tab ): void {
		if ( empty( $tab->tab_key ) || 'post_content' !== $tab->tab_key || ! is_singular( 'gd_place' ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		$groups  = self::get_groups( $post_id );
		if ( ! $groups ) {
			return;
		}
		$title_id = 'gn-trip-planner-title-' . $post_id;
		?>
		<section class="gn-trip-planner" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
			<header class="gn-trip-planner__header">
				<p><?php echo esc_html__( 'להפוך ביקור ליום שלם', 'gonorth-place-data' ); ?></p>
				<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html__( 'תכננו סביב המקום', 'gonorth-place-data' ); ?></h2>
				<p class="gn-trip-planner__intro"><?php echo esc_html__( 'הצעות קרובות שנבחרו לפי סוג המקום והמרחק ממנו.', 'gonorth-place-data' ); ?></p>
			</header>
			<div class="gn-trip-planner__groups">
				<?php foreach ( $groups as $key => $group ) : ?>
					<section class="gn-trip-group gn-trip-group--<?php echo esc_attr( $key ); ?>">
						<header class="gn-trip-group__heading">
							<span class="gn-trip-group__icon" aria-hidden="true"><?php echo self::get_group_icon( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span>
								<span class="gn-trip-group__label"><?php echo esc_html( $group['label'] ); ?></span>
								<h3><?php echo esc_html( $group['title'] ); ?></h3>
							</span>
						</header>
						<ul>
							<?php foreach ( $group['places'] as $position => $place ) : ?>
								<li>
									<a href="<?php echo esc_url( $place['url'] ); ?>" data-gn-place-card="1" data-target-place-id="<?php echo esc_attr( (string) $place['id'] ); ?>" data-place-name="<?php echo esc_attr( $place['title'] ); ?>" data-place-category="<?php echo esc_attr( $place['category'] ); ?>" data-discovery-surface="plan_around" data-related-section="plan_around" data-position="<?php echo esc_attr( (string) ( $position + 1 ) ); ?>">
										<span class="gn-trip-place__content">
											<strong><?php echo esc_html( $place['title'] ); ?></strong>
											<span class="gn-trip-place__meta">
												<?php echo esc_html( $place['category'] ); ?>
												<span class="gn-trip-place__distance"><bdi><?php echo esc_html( GoNorth_Nearby_Places::format_distance( $place['distance_km'] ) ); ?></bdi> <?php echo esc_html__( 'ק״מ', 'gonorth-place-data' ); ?></span>
											</span>
										</span>
										<span class="gn-trip-place__arrow" aria-hidden="true">←</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
