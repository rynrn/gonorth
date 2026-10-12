<?php
/**
 * Contextual internal-link network for guides, hubs, regions and places.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Internal_Links {
	private const EXCLUDED_GUIDES = array( 84, 123, 126, 127, 131, 135, 144, 251, 252, 256, 257, 258, 267 );
	private const GUIDE_LIMIT = 4;
	private const PLACE_LIMIT = 4;

	/** @var array<int,array<string,mixed>>|null */
	private static ?array $guides = null;

	/** @var array<int,bool> Prevent duplicate output in nested content passes. */
	private static array $rendered = array();

	/** @var array<string,array<string,string>> Cached published attribute landing links. */
	private static array $attribute_landings = array();

	/** Register the archive and content integrations. */
	public static function init(): void {
		add_action( 'astra_content_before', array( __CLASS__, 'render_category_archive_links' ), 19 );
		add_action( 'gonorth_after_region_intro', array( __CLASS__, 'render_region_archive_links' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'the_content', array( __CLASS__, 'append_contextual_links' ), 40 );
	}

	/** Enqueue the shared stylesheet wherever the network can render. */
	public static function enqueue_assets(): void {
		$should_enqueue = is_tax( array( 'gd_placecategory', 'gn_region' ) ) || is_singular( 'gd_place' );
		if ( is_singular( array( 'post', 'page' ) ) ) {
			$should_enqueue = isset( self::get_guides()[ get_queried_object_id() ] );
		}
		if ( ! $should_enqueue ) {
			return;
		}
		wp_enqueue_style(
			'gonorth-place-data',
			plugins_url( 'assets/place-data.css', GONORTH_PLACE_DATA_FILE ),
			array(),
			GONORTH_PLACE_DATA_VERSION
		);
	}

	/**
	 * Keyword rules used only to classify existing editorial guides.
	 *
	 * @return array<string,array<string,array<string>>>
	 */
	public static function guide_rules(): array {
		return array(
			'categories' => array(
				'accommodation'  => array( 'לינה', 'צימר', 'מלון', 'וילה', 'וילות', 'קמפינג', 'קיבוצ', 'ללון' ),
				'restaurants-sub' => array( 'מסעד', 'אוכל', 'לאכול', 'קולינר' ),
				'agalot-kafe'     => array( 'עגלת קפה', 'עגלות קפה', 'קפה' ),
				'yakavim'         => array( 'יקב', 'יקבים', 'יין', 'טעימות' ),
				'teva'            => array( 'מסלול', 'מסלולי', 'טבע', 'מים', 'מעיינ', 'נחל', 'חוף', 'אגמון', 'חורש' ),
				'atraktziot-main' => array( 'אטרקצי', 'ילדים', 'משפחה', 'משפחתי', 'באולינג', 'חרמון', 'חמת גדר' ),
				'tours'           => array( 'סיור', 'סיורים' ),
			),
			'regions' => array(
				'golan'  => array( 'גולן', 'חרמון', 'חמת גדר', 'מגדל שמס', 'בניאס', 'תל פארס' ),
				'galil'  => array( 'גליל', 'צפת', 'עכו', 'נהריה', 'גשר הזיו', 'דפנה', 'אזור דן', 'אגמון החולה', 'חורשת טל' ),
				'carmel' => array( 'כרמל', 'חוף דור', 'עמק יזרעאל' ),
			),
		);
	}

	/**
	 * Return every published guide that has a useful taxonomy context.
	 *
	 * @return array<int,array{id:int,title:string,url:string,categories:array<string>,regions:array<string>,modified:string}>
	 */
	public static function get_guides(): array {
		if ( null !== self::$guides ) {
			return self::$guides;
		}
		self::$guides = array();
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);
		foreach ( $posts as $post ) {
			$profile = self::classify_guide( $post );
			if ( $profile ) {
				self::$guides[ $post->ID ] = $profile;
			}
		}
		return self::$guides;
	}

	/**
	 * Classify a guide from its title and stable slug without inferring facts.
	 *
	 * @param WP_Post $post Published post or page.
	 * @return array{id:int,title:string,url:string,categories:array<string>,regions:array<string>,modified:string}|array<empty>
	 */
	public static function classify_guide( WP_Post $post ): array {
		if ( 'publish' !== $post->post_status || ! in_array( $post->post_type, array( 'post', 'page' ), true ) || in_array( $post->ID, self::EXCLUDED_GUIDES, true ) ) {
			return array();
		}
		if ( preg_match( '/20\d{2}/', $post->post_title ) ) {
			return array();
		}
		$haystack = wp_strip_all_tags( $post->post_title . ' ' . urldecode( $post->post_name ) );
		$rules    = self::guide_rules();
		$profile  = array(
			'id'         => (int) $post->ID,
			'title'      => (string) get_the_title( $post ),
			'url'        => (string) get_permalink( $post ),
			'categories' => self::matching_keys( $haystack, $rules['categories'] ),
			'regions'    => self::matching_keys( $haystack, $rules['regions'] ),
			'modified'   => (string) $post->post_modified_gmt,
		);

		if ( false !== mb_stripos( $haystack, 'סוף שבוע' ) ) {
			$profile['categories'] = array_values( array_unique( array_merge( $profile['categories'], array( 'accommodation', 'restaurants-sub', 'teva' ) ) ) );
			$profile['regions']    = array_values( array_unique( array_merge( $profile['regions'], array( 'galil', 'golan', 'carmel' ) ) ) );
		}

		return $profile['categories'] || $profile['regions'] ? $profile : array();
	}

	/** Render category-to-region and category-to-guide connections. */
	public static function render_category_archive_links(): void {
		if ( ! is_tax( 'gd_placecategory' ) ) {
			return;
		}
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			echo self::render_archive_context( $term ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/** Render region-to-category and region-to-guide connections. */
	public static function render_region_archive_links(): void {
		if ( ! is_tax( 'gn_region' ) ) {
			return;
		}
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			echo self::render_archive_context( $term ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Build a contextual archive block from real intersections and guides.
	 *
	 * @param WP_Term $term Category or region term.
	 */
	public static function render_archive_context( WP_Term $term ): string {
		if ( ! in_array( $term->taxonomy, array( 'gd_placecategory', 'gn_region' ), true ) || $term->count < 1 ) {
			return '';
		}
		$is_category = 'gd_placecategory' === $term->taxonomy;
		$other_tax   = $is_category ? 'gn_region' : 'gd_placecategory';
		$other_terms = get_terms( array( 'taxonomy' => $other_tax, 'hide_empty' => true ) );
		$paths       = array();
		if ( ! is_wp_error( $other_terms ) ) {
			foreach ( $other_terms as $other_term ) {
				$count = self::intersection_count( $term, $other_term );
				if ( $count < 1 ) {
					continue;
				}
				$url = get_term_link( $other_term );
				if ( is_wp_error( $url ) ) {
					continue;
				}
				$paths[] = array(
					'url'   => $url,
					'label' => $is_category
						? sprintf( '%s %s', $term->name, self::region_phrase( $other_term->slug ) )
						: sprintf( '%s %s', $other_term->name, self::region_phrase( $term->slug ) ),
					'count' => $count,
				);
			}
		}
		$guides = self::relevant_guides(
			$is_category ? array( $term->slug ) : array(),
			$is_category ? array() : array( $term->slug ),
			self::GUIDE_LIMIT
		);
		return self::archive_markup( $paths, $guides );
	}

	/** Append a useful context block only to the main singular content pass. */
	public static function append_contextual_links( string $content ): string {
		if ( ! is_singular( array( 'gd_place', 'post', 'page' ) ) || ! in_the_loop() || ! is_main_query() || false !== strpos( $content, 'class="gn-context-links"' ) ) {
			return $content;
		}
		$post_id = get_the_ID();
		if ( isset( self::$rendered[ $post_id ] ) ) {
			return $content;
		}
		if ( 'gd_place' === get_post_type( $post_id ) ) {
			$context = self::render_place_context( $post_id );
		} else {
			$guides  = self::get_guides();
			$context = isset( $guides[ $post_id ] ) ? self::render_guide_context( $guides[ $post_id ], $content ) : '';
		}
		if ( '' !== $context ) {
			self::$rendered[ $post_id ] = true;
		}
		return $content . $context;
	}

	/** Return relevant guides ranked by category and region overlap. */
	public static function relevant_guides( array $categories, array $regions, int $limit = self::GUIDE_LIMIT ): array {
		$ranked = array();
		foreach ( self::get_guides() as $guide ) {
			$category_hits = count( array_intersect( $categories, $guide['categories'] ) );
			$region_hits   = count( array_intersect( $regions, $guide['regions'] ) );
			if ( ! $category_hits && ! $region_hits ) {
				continue;
			}
			$guide['score'] = ( $category_hits * 3 ) + ( $region_hits * 2 ) + ( $category_hits && $region_hits ? 3 : 0 );
			$ranked[]       = $guide;
		}
		usort(
			$ranked,
			static function ( array $a, array $b ): int {
				return $a['score'] === $b['score'] ? strcmp( $b['modified'], $a['modified'] ) : $b['score'] <=> $a['score'];
			}
		);
		return array_slice( $ranked, 0, max( 1, min( 8, $limit ) ) );
	}

	/** Render links from one place back to its hubs, region and best guides. */
	public static function render_place_context( int $post_id ): string {
		$categories = wp_get_post_terms( $post_id, 'gd_placecategory' );
		$regions    = wp_get_post_terms( $post_id, 'gn_region' );
		$categories = is_wp_error( $categories ) ? array() : $categories;
		$regions    = is_wp_error( $regions ) ? array() : $regions;
		$cat_slugs  = wp_list_pluck( $categories, 'slug' );
		$reg_slugs  = wp_list_pluck( $regions, 'slug' );
		$guides     = self::relevant_guides( $cat_slugs, $reg_slugs, 3 );
		$hub_links  = array();
		foreach ( array_merge( $categories, $regions ) as $term ) {
			$url = get_term_link( $term );
			if ( ! is_wp_error( $url ) ) {
				$hub_links[] = array(
					'url'   => $url,
					'label' => 'gn_region' === $term->taxonomy ? 'עוד מקומות ' . self::region_phrase( $term->slug ) : 'עוד ב' . $term->name,
				);
			}
		}
		if ( 'yes' === GoNorth_Place_Attributes::get_state( $post_id, 'open_saturday' ) ) {
			$saturday_landing = self::attribute_landing_link( 'open_saturday' );
			if ( $saturday_landing ) {
				$hub_links[] = $saturday_landing;
			}
		}
		return self::singular_markup( 'ממשיכים לתכנן', $hub_links, $guides, array() );
	}


	/** Return one published feature landing link from its code-owned profile. */
	private static function attribute_landing_link( string $attribute ): array {
		if ( isset( self::$attribute_landings[ $attribute ] ) ) {
			return self::$attribute_landings[ $attribute ];
		}
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_key'       => GoNorth_Attribute_Landing_Pages::META_KEY,
				'meta_value'     => $attribute,
			)
		);
		self::$attribute_landings[ $attribute ] = $pages ? array(
			'url'   => (string) get_permalink( $pages[0] ),
			'label' => 'מקומות פתוחים בשבת בצפון',
		) : array();
		return self::$attribute_landings[ $attribute ];
	}

	/** Render links from a guide to its hubs and a small set of matching places. */
	public static function render_guide_context( array $profile, string $content ): string {
		$hub_links = array();
		foreach ( array( 'gd_placecategory' => $profile['categories'], 'gn_region' => $profile['regions'] ) as $taxonomy => $slugs ) {
			foreach ( $slugs as $slug ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( ! $term || $term->count < 1 ) {
					continue;
				}
				$url = get_term_link( $term );
				if ( is_wp_error( $url ) || false !== strpos( $content, $url ) ) {
					continue;
				}
				$hub_links[] = array(
					'url'   => $url,
					'label' => 'gn_region' === $taxonomy ? 'מקומות ' . self::region_phrase( $slug ) : $term->name . ' בצפון',
				);
			}
		}
		$places = self::places_for_guide( $profile, $content );
		$related_guides = array_values(
			array_filter(
				self::guide_neighbors( $profile, 3 ),
				static fn( array $guide ): bool => false === strpos( $content, $guide['url'] )
			)
		);
		return self::singular_markup( 'ממשיכים מהמדריך', $hub_links, $related_guides, $places );
	}

	/**
	 * Pick the next guide in each shared topic ring.
	 *
	 * A stable ring gives older guides an inbound path instead of repeatedly
	 * favouring only the newest items.
	 */
	public static function guide_neighbors( array $profile, int $limit = 3 ): array {
		$neighbors = array();
		$groups    = array(
			'categories' => $profile['categories'],
			'regions'    => $profile['regions'],
		);
		foreach ( $groups as $profile_key => $slugs ) {
			foreach ( $slugs as $slug ) {
				$members = array_values(
					array_filter(
						self::get_guides(),
						static fn( array $guide ): bool => in_array( $slug, $guide[ $profile_key ], true )
					)
				);
				usort( $members, static fn( array $a, array $b ): int => $a['id'] <=> $b['id'] );
				if ( count( $members ) < 2 ) {
					continue;
				}
				$current_index = array_search( $profile['id'], array_column( $members, 'id' ), true );
				if ( false === $current_index ) {
					continue;
				}
				$neighbor = $members[ ( $current_index + 1 ) % count( $members ) ];
				$neighbors[ $neighbor['id'] ] = $neighbor;
				if ( count( $neighbors ) >= $limit ) {
					break 2;
				}
			}
		}
		return array_values( $neighbors );
	}

	/** Get a deterministic, published place sample for a guide. */
	private static function places_for_guide( array $profile, string $content ): array {
		$tax_query = array( 'relation' => 'AND' );
		if ( $profile['categories'] ) {
			$tax_query[] = array( 'taxonomy' => 'gd_placecategory', 'field' => 'slug', 'terms' => $profile['categories'] );
		}
		if ( $profile['regions'] ) {
			$tax_query[] = array( 'taxonomy' => 'gn_region', 'field' => 'slug', 'terms' => $profile['regions'] );
		}
		$query = new WP_Query(
			array(
				'post_type'           => 'gd_place',
				'post_status'         => 'publish',
				'posts_per_page'      => self::PLACE_LIMIT * 2,
				'orderby'             => array( 'modified' => 'DESC', 'ID' => 'DESC' ),
				'tax_query'           => $tax_query,
				'meta_query'          => self::active_place_meta_query(),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		$places = array();
		foreach ( $query->posts as $place ) {
			$url = get_permalink( $place );
			if ( false !== strpos( $content, $url ) ) {
				continue;
			}
			$places[] = array( 'url' => $url, 'title' => get_the_title( $place ) );
			if ( self::PLACE_LIMIT === count( $places ) ) {
				break;
			}
		}
		return $places;
	}

	/** Exclude listings whose verified business status is closed. */
	private static function active_place_meta_query(): array {
		return array(
			'relation' => 'OR',
			array(
				'key'     => '_gonorth_business_status',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_gonorth_business_status',
				'value'   => array( 'temporarily_closed', 'permanently_closed' ),
				'compare' => 'NOT IN',
			),
		);
	}

	/** Count published places at one category/region intersection. */
	private static function intersection_count( WP_Term $a, WP_Term $b ): int {
		$query = new WP_Query(
			array(
				'post_type'      => 'gd_place',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => self::active_place_meta_query(),
				'tax_query'      => array(
					'relation' => 'AND',
					array( 'taxonomy' => $a->taxonomy, 'field' => 'term_id', 'terms' => $a->term_id ),
					array( 'taxonomy' => $b->taxonomy, 'field' => 'term_id', 'terms' => $b->term_id ),
				),
			)
		);
		return (int) $query->found_posts;
	}

	/** @param array<string,array<string>> $rules */
	private static function matching_keys( string $haystack, array $rules ): array {
		$matches = array();
		foreach ( $rules as $key => $needles ) {
			foreach ( $needles as $needle ) {
				if ( false !== mb_stripos( $haystack, $needle ) ) {
					$matches[] = $key;
					break;
				}
			}
		}
		return $matches;
	}

	/** Hebrew region phrase for natural anchors. */
	private static function region_phrase( string $slug ): string {
		return array(
			'galil'  => 'בגליל',
			'golan'  => 'בגולן',
			'carmel' => 'בכרמל ובעמק יזרעאל',
		)[ $slug ] ?? 'בצפון';
	}

	/** Render one archive network block. */
	private static function archive_markup( array $paths, array $guides ): string {
		if ( ! $paths && ! $guides ) {
			return '';
		}
		ob_start();
		?>
		<nav class="gn-context-links gn-context-links--archive gn-container" dir="rtl" aria-labelledby="gn-context-archive-title">
			<h2 id="gn-context-archive-title"><?php echo esc_html__( 'המשיכו לפי אזור או מדריך', 'gonorth-place-data' ); ?></h2>
			<?php if ( $paths ) : ?>
				<ul class="gn-context-links__pills">
					<?php foreach ( $paths as $path ) : ?>
						<li><a href="<?php echo esc_url( $path['url'] ); ?>"><?php echo esc_html( $path['label'] ); ?> <small>(<?php echo esc_html( (string) $path['count'] ); ?>)</small></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $guides ) : ?>
				<div class="gn-context-links__guides"><strong><?php echo esc_html__( 'מדריכים שימושיים', 'gonorth-place-data' ); ?></strong><ul>
					<?php foreach ( $guides as $guide ) : ?><li><a href="<?php echo esc_url( $guide['url'] ); ?>"><?php echo esc_html( $guide['title'] ); ?></a></li><?php endforeach; ?>
				</ul></div>
			<?php endif; ?>
		</nav>
		<?php
		return (string) ob_get_clean();
	}

	/** Render a compact singular-page network block. */
	private static function singular_markup( string $title, array $hub_links, array $guides, array $places ): string {
		if ( ! $hub_links && ! $guides && ! $places ) {
			return '';
		}
		ob_start();
		?>
		<nav class="gn-context-links gn-context-links--singular" dir="rtl" aria-label="<?php echo esc_attr( $title ); ?>">
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php if ( $hub_links ) : ?><ul class="gn-context-links__pills"><?php foreach ( $hub_links as $link ) : ?><li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li><?php endforeach; ?></ul><?php endif; ?>
			<?php if ( $guides || $places ) : ?><ul class="gn-context-links__list">
				<?php foreach ( $guides as $guide ) : ?><li><a href="<?php echo esc_url( $guide['url'] ); ?>"><?php echo esc_html( $guide['title'] ); ?></a></li><?php endforeach; ?>
				<?php foreach ( $places as $place ) : ?><li><a href="<?php echo esc_url( $place['url'] ); ?>"><?php echo esc_html( $place['title'] ); ?></a></li><?php endforeach; ?>
			</ul><?php endif; ?>
		</nav>
		<?php
		return (string) ob_get_clean();
	}
}

