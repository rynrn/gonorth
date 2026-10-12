<?php
/**
 * Dynamic, evidence-gated landing pages for verified place attributes.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Attribute_Landing_Pages {
	public const META_KEY = '_gonorth_attribute_landing';
	private const MIN_INVENTORY = 12;

	/** @var array<string,array<int,array<string,mixed>>> Request-local verified place cache. */
	private static array $place_cache = array();

	/** Register public hooks. */
	public static function init(): void {
		add_shortcode( 'gonorth_attribute_landing', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'wpseo_title', array( __CLASS__, 'filter_title' ), 30 );
		add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'filter_title' ), 30 );
		add_filter( 'wpseo_twitter_title', array( __CLASS__, 'filter_title' ), 30 );
		add_filter( 'wpseo_metadesc', array( __CLASS__, 'filter_description' ), 30 );
		add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'filter_description' ), 30 );
		add_filter( 'wpseo_twitter_description', array( __CLASS__, 'filter_description' ), 30 );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'filter_canonical' ), 30 );
		add_filter( 'wpseo_robots_array', array( __CLASS__, 'filter_robots' ), 30 );
		add_filter( 'wpseo_schema_graph', array( __CLASS__, 'filter_schema_graph' ), 30, 2 );
	}

	/**
	 * Code-owned landing profiles. Sparse attributes are intentionally absent.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function profiles(): array {
		return array(
			'open_saturday' => array(
				'title'       => 'מקומות פתוחים בשבת בצפון | כיוון צפון',
				'description' => 'מצאו מקומות פתוחים בשבת בצפון מתוך מידע מעשי מאומת: אטרקציות, טבע, אוכל ופעילויות בגליל ובגולן, עם קישורים ישירים ותאריך בדיקה.',
				'intro'       => array(
					'מחפשים מקומות פתוחים בשבת בצפון? הרשימה מרכזת אטרקציות, שמורות טבע, מקומות אוכל ופעילויות שבהם קיים סימון חיובי ומאומת לפתיחה בשבת. היא נבנית אוטומטית מנתוני המקום ולא מתוך טקסט חופשי.',
					'כדי לתכנן יום נוח, התחילו באזור אחד ובדקו את שעות הפעילות המדויקות בדף המקום. שעות עשויות להשתנות בחגים, באירועים מיוחדים או בגלל מזג האוויר, ולכן מומלץ לאמת אותן שוב מול המקום סמוך ליציאה.',
				),
				'attribute'   => 'open_saturday',
			),
		);
	}

	/** Whether the current request is a configured landing page. */
	public static function is_landing_page(): bool {
		return (bool) self::current_profile();
	}

	/** Load the component stylesheet only where needed. */
	public static function enqueue_assets(): void {
		if ( ! self::is_landing_page() ) {
			return;
		}
		wp_enqueue_style(
			'gonorth-place-data',
			plugins_url( 'assets/place-data.css', GONORTH_PLACE_DATA_FILE ),
			array(),
			GONORTH_PLACE_DATA_VERSION
		);
	}

	/** Render a verified, dynamically generated place list. */
	public static function render_shortcode( array $atts = array() ): string {
		$atts      = shortcode_atts( array( 'attribute' => '' ), $atts, 'gonorth_attribute_landing' );
		$attribute = sanitize_key( (string) $atts['attribute'] );
		$profiles  = self::profiles();
		if ( ! isset( $profiles[ $attribute ] ) ) {
			return '';
		}

		$places = self::get_verified_places( $attribute );
		if ( count( $places ) < self::MIN_INVENTORY ) {
			return '<p class="gn-attribute-landing__empty">' . esc_html__( 'אין כרגע מספיק מקומות מאומתים להצגת העמוד.', 'gonorth-place-data' ) . '</p>';
		}

		$profile = $profiles[ $attribute ];
		$groups  = array();
		foreach ( $places as $place ) {
			$region = $place['regions'] ? (string) reset( $place['regions'] ) : __( 'צפון ישראל', 'gonorth-place-data' );
			$groups[ $region ][] = $place;
		}
		ksort( $groups, SORT_NATURAL | SORT_FLAG_CASE );

		ob_start();
		?>
		<section class="gn-attribute-landing" dir="rtl" aria-labelledby="gn-attribute-intro-title">
			<div class="gn-attribute-landing__intro">
				<h2 id="gn-attribute-intro-title"><?php esc_html_e( 'מקומות פתוחים בשבת בצפון — לפי אזור', 'gonorth-place-data' ); ?></h2>
				<?php foreach ( $profile['intro'] as $paragraph ) : ?>
					<p><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
			</div>

			<aside class="gn-attribute-landing__method" aria-label="<?php esc_attr_e( 'שיטת הבחירה ותאריך הבדיקה', 'gonorth-place-data' ); ?>">
				<strong><?php esc_html_e( 'איך בחרנו?', 'gonorth-place-data' ); ?></strong>
				<?php
				printf(
					/* translators: 1: number of places, 2: verification date range. */
					esc_html__( 'מוצגים %1$d מקומות שבהם המאפיין "פתוח בשבת" הוא כן וקיים תאריך אימות מעשי. מצבי לא ידוע אינם נכללים. תאריך בדיקת הנתונים: %2$s.', 'gonorth-place-data' ),
					count( $places ),
					esc_html( self::verification_label( $places ) )
				);
				?>
			</aside>

			<?php foreach ( $groups as $region => $region_places ) : ?>
				<?php $region_id = 'gn-attribute-region-' . substr( md5( $region ), 0, 10 ); ?>
				<section class="gn-attribute-landing__region" aria-labelledby="<?php echo esc_attr( $region_id ); ?>">
					<h2 id="<?php echo esc_attr( $region_id ); ?>">
						<?php echo esc_html( sprintf( 'פתוח בשבת באזור %s', $region ) ); ?>
					</h2>
					<ol class="gn-attribute-landing__grid">
						<?php foreach ( $region_places as $place ) : ?>
							<li class="gn-attribute-card">
								<a href="<?php echo esc_url( $place['url'] ); ?>">
									<?php if ( $place['thumbnail_id'] ) : ?>
										<?php echo wp_get_attachment_image( $place['thumbnail_id'], 'medium_large', false, array( 'class' => 'gn-attribute-card__image', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php endif; ?>
									<span class="gn-attribute-card__body">
										<strong><?php echo esc_html( $place['title'] ); ?></strong>
										<?php if ( $place['excerpt'] ) : ?><span><?php echo esc_html( $place['excerpt'] ); ?></span><?php endif; ?>
										<small><?php echo esc_html( implode( ' · ', array_merge( $place['regions'], $place['categories'] ) ) ); ?></small>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endforeach; ?>

			<section class="gn-attribute-landing__notes" aria-labelledby="gn-attribute-notes-title">
				<h2 id="gn-attribute-notes-title"><?php esc_html_e( 'מה חשוב לבדוק לפני שיוצאים', 'gonorth-place-data' ); ?></h2>
				<ul>
					<li><?php esc_html_e( 'בדקו את שעות שבת המדויקות בדף המקום ובערוץ הרשמי.', 'gonorth-place-data' ); ?></li>
					<li><?php esc_html_e( 'בררו אם נדרשת הזמנה מראש ואם קיימת שעת כניסה אחרונה.', 'gonorth-place-data' ); ?></li>
					<li><?php esc_html_e( 'בחגים ובמזג אוויר חריג ייתכנו שינויים גם במקום שמסומן כפתוח.', 'gonorth-place-data' ); ?></li>
				</ul>
				<p><a href="<?php echo esc_url( home_url( '/places/' ) ); ?>"><?php esc_html_e( 'לכל המקומות בצפון', 'gonorth-place-data' ); ?></a></p>
			</section>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/** Apply the code-owned SEO title. */
	public static function filter_title( string $title ): string {
		$profile = self::current_profile();
		return $profile ? (string) $profile['title'] : $title;
	}

	/** Apply the code-owned SEO description. */
	public static function filter_description( string $description ): string {
		$profile = self::current_profile();
		return $profile ? (string) $profile['description'] : $description;
	}

	/** Keep one canonical URL for the configured page. */
	public static function filter_canonical( string $canonical ): string {
		return self::is_landing_page() ? (string) get_permalink( get_queried_object_id() ) : $canonical;
	}

	/** Prevent indexing if verified inventory later falls below the quality floor. */
	public static function filter_robots( array $robots ): array {
		$profile = self::current_profile();
		if ( $profile && count( self::get_verified_places( (string) $profile['attribute'] ) ) < self::MIN_INVENTORY ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	}

	/** Add one ItemList built from the same verified result set as the visible list. */
	public static function filter_schema_graph( array $graph, $context ): array {
		unset( $context );
		$profile = self::current_profile();
		if ( ! $profile ) {
			return $graph;
		}
		$places = self::get_verified_places( (string) $profile['attribute'] );
		if ( count( $places ) < self::MIN_INVENTORY ) {
			return $graph;
		}
		foreach ( $graph as $piece ) {
			$types = isset( $piece['@type'] ) ? (array) $piece['@type'] : array();
			if ( in_array( 'ItemList', $types, true ) ) {
				return $graph;
			}
		}
		$url   = (string) get_permalink( get_queried_object_id() );
		$items = array();
		foreach ( $places as $index => $place ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $place['title'],
				'url'      => $place['url'],
			);
		}
		$graph[] = array(
			'@type'           => 'ItemList',
			'@id'             => untrailingslashit( $url ) . '/#itemlist',
			'url'             => $url,
			'name'            => 'מקומות פתוחים בשבת בצפון',
			'numberOfItems'   => count( $items ),
			'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
			'itemListElement' => $items,
		);
		return $graph;
	}

	/** @return array<string,mixed> */
	private static function current_profile(): array {
		if ( ! is_singular( 'page' ) ) {
			return array();
		}
		$attribute = sanitize_key( (string) get_post_meta( get_queried_object_id(), self::META_KEY, true ) );
		return self::profiles()[ $attribute ] ?? array();
	}

	/**
	 * Fetch only positive-state places with a factual verification timestamp.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_verified_places( string $attribute ): array {
		if ( isset( self::$place_cache[ $attribute ] ) ) {
			return self::$place_cache[ $attribute ];
		}
		$query = gonorth_query_places_by_attributes(
			array( $attribute ),
			array(
				'posts_per_page'         => -1,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
			)
		);
		if ( is_wp_error( $query ) ) {
			self::$place_cache[ $attribute ] = array();
			return array();
		}

		$places = array();
		foreach ( $query->posts as $post ) {
			$data = GoNorth_Place_Data::get( $post->ID );
			if ( empty( $data['practical_checked_at'] ) ) {
				continue;
			}
			$regions    = wp_get_post_terms( $post->ID, 'gn_region', array( 'fields' => 'names' ) );
			$categories = wp_get_post_terms( $post->ID, 'gd_placecategory', array( 'fields' => 'names' ) );
			$excerpt    = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 24, '…' );
			$places[]   = array(
				'id'                   => $post->ID,
				'title'                => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
				'url'                  => esc_url_raw( get_permalink( $post ) ),
				'excerpt'              => html_entity_decode( wp_strip_all_tags( $excerpt ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
				'thumbnail_id'         => get_post_thumbnail_id( $post->ID ),
				'regions'              => is_wp_error( $regions ) ? array() : array_values( $regions ),
				'categories'           => is_wp_error( $categories ) ? array() : array_values( $categories ),
				'practical_checked_at' => (string) $data['practical_checked_at'],
			);
		}
		usort(
			$places,
			static function ( array $left, array $right ): int {
				$left_region  = $left['regions'] ? (string) reset( $left['regions'] ) : '';
				$right_region = $right['regions'] ? (string) reset( $right['regions'] ) : '';
				$region_order = strnatcasecmp( $left_region, $right_region );
				return 0 !== $region_order ? $region_order : strnatcasecmp( (string) $left['title'], (string) $right['title'] );
			}
		);
		self::$place_cache[ $attribute ] = $places;
		return $places;
	}

	/** Format the oldest/newest factual verification date represented in the list. */
	private static function verification_label( array $places ): string {
		$timestamps = array_filter( array_map( static fn( array $place ): int => strtotime( (string) $place['practical_checked_at'] ) ?: 0, $places ) );
		if ( ! $timestamps ) {
			return '';
		}
		$oldest = wp_date( 'd.m.Y', min( $timestamps ) );
		$newest = wp_date( 'd.m.Y', max( $timestamps ) );
		return $oldest === $newest ? $newest : sprintf( '%s–%s', $oldest, $newest );
	}
}
