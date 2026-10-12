<?php
/**
 * Evergreen seasonal hub metadata and self-expiring promotion links.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Seasonal_Hubs {
	public const META_KEY = '_gonorth_seasonal_hub';
	private const PROMO_START_META = '_gonorth_seasonal_promo_start';
	private const PROMO_END_META = '_gonorth_seasonal_promo_end';
	private static ?WP_Post $active_promotion = null;
	private static bool $promotion_resolved = false;

	/** Register public hooks. */
	public static function init(): void {
		add_action( 'astra_content_before', array( __CLASS__, 'render_promotion' ), 8 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'wpseo_title', array( __CLASS__, 'filter_title' ), 30 );
		add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'filter_title' ), 30 );
		add_filter( 'wpseo_twitter_title', array( __CLASS__, 'filter_title' ), 30 );
		add_filter( 'wpseo_metadesc', array( __CLASS__, 'filter_description' ), 30 );
		add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'filter_description' ), 30 );
		add_filter( 'wpseo_twitter_description', array( __CLASS__, 'filter_description' ), 30 );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'filter_canonical' ), 30 );
	}

	/** @return array<string,array<string,string>> */
	public static function profiles(): array {
		return array(
			'rosh-hashanah' => array(
				'title'       => 'ראש השנה בצפון: מדריך לתכנון החג | כיוון צפון',
				'description' => 'מתכננים ראש השנה בצפון? תאריכי החג המעודכנים, רעיונות לטיול בגליל ובגולן, קישורים למקומות ורשימת בדיקות לשעות חג והזמנות.',
				'promo'       => 'מתכננים את ראש השנה בצפון? עברו למדריך החג המעודכן.',
			),
			'sukkot' => array(
				'title'       => 'סוכות בצפון: מדריך לטיולים בחג | כיוון צפון',
				'description' => 'מתכננים סוכות בצפון? תאריכי החג וחול המועד, רעיונות לטיולים בגליל ובגולן, קישורים למקומות וטיפים לעומסים, מזג אוויר והזמנה.',
				'promo'       => 'יוצאים לצפון בסוכות? עברו למדריך החג ולתכנון לפי אזור.',
			),
		);
	}

	/** Enqueue shared styles on a seasonal page or where a promotion is visible. */
	public static function enqueue_assets(): void {
		if ( ! self::current_profile() && ! self::should_show_promotion() ) {
			return;
		}
		wp_enqueue_style(
			'gonorth-place-data',
			plugins_url( 'assets/place-data.css', GONORTH_PLACE_DATA_FILE ),
			array(),
			GONORTH_PLACE_DATA_VERSION
		);
	}

	/** Render one promotion only during its configured annual window. */
	public static function render_promotion(): void {
		if ( ! self::should_show_promotion() ) {
			return;
		}
		$page    = self::active_promotion();
		$profile = $page ? self::profile_for_post( $page->ID ) : array();
		if ( ! $page || ! $profile ) {
			return;
		}
		echo '<aside class="gn-seasonal-promo gn-container" dir="rtl" aria-label="' . esc_attr__( 'מדריך עונתי', 'gonorth-place-data' ) . '">';
		echo '<a href="' . esc_url( get_permalink( $page ) ) . '"><span>' . esc_html__( 'מדריך עונתי', 'gonorth-place-data' ) . '</span><strong>' . esc_html( $profile['promo'] ) . '</strong></a>';
		echo '</aside>';
	}

	/** Apply the unique evergreen SEO title. */
	public static function filter_title( string $title ): string {
		$profile = self::current_profile();
		return $profile ? $profile['title'] : $title;
	}

	/** Apply the unique evergreen meta description. */
	public static function filter_description( string $description ): string {
		$profile = self::current_profile();
		return $profile ? $profile['description'] : $description;
	}

	/** Preserve the year-free WordPress permalink as canonical. */
	public static function filter_canonical( string $canonical ): string {
		return self::current_profile() ? (string) get_permalink( get_queried_object_id() ) : $canonical;
	}

	/** @return array<string,string> */
	private static function current_profile(): array {
		if ( ! is_singular( 'page' ) ) {
			return array();
		}
		return self::profile_for_post( get_queried_object_id() );
	}

	/** @return array<string,string> */
	private static function profile_for_post( int $post_id ): array {
		$key = sanitize_key( (string) get_post_meta( $post_id, self::META_KEY, true ) );
		return self::profiles()[ $key ] ?? array();
	}

	/** Whether the current surface is approved for a seasonal inbound link. */
	private static function should_show_promotion(): bool {
		if ( ! self::active_promotion() ) {
			return false;
		}
		if ( is_front_page() || is_home() || is_tax( array( 'gd_placecategory', 'gn_region' ) ) ) {
			return true;
		}
		return is_singular( 'post' ) && '1' === (string) get_post_meta( get_queried_object_id(), '_gonorth_seasonal_promote', true );
	}

	/** Find a published seasonal page whose promotion window includes today. */
	private static function active_promotion(): ?WP_Post {
		if ( self::$promotion_resolved ) {
			return self::$active_promotion;
		}
		self::$promotion_resolved = true;
		$today = current_datetime()->format( 'Y-m-d' );
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 4,
				'meta_key'       => self::META_KEY,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);
		foreach ( $pages as $page ) {
			$start = sanitize_text_field( (string) get_post_meta( $page->ID, self::PROMO_START_META, true ) );
			$end   = sanitize_text_field( (string) get_post_meta( $page->ID, self::PROMO_END_META, true ) );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end ) && $today >= $start && $today <= $end ) {
				self::$active_promotion = $page;
				break;
			}
		}
		return self::$active_promotion;
	}
}
