<?php
/**
 * Factual SEO, FAQ and freshness output for GoNorth places.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Place_SEO {
	/**
	 * Register frontend integrations.
	 */
	public static function init(): void {
		add_filter( 'geodir_details_schema', array( __CLASS__, 'filter_place_schema' ), 20, 2 );
		add_filter( 'wpseo_schema_graph', array( __CLASS__, 'filter_schema_graph' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'filter_place_title' ), 20 );
		add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'filter_place_title' ), 20 );
		add_filter( 'wpseo_twitter_title', array( __CLASS__, 'filter_place_title' ), 20 );
		add_filter( 'wpseo_opengraph_image', array( __CLASS__, 'filter_social_image' ), 20 );
		add_filter( 'wpseo_twitter_image', array( __CLASS__, 'filter_social_image' ), 20 );
		add_filter( 'pre_get_document_title', array( __CLASS__, 'filter_place_title' ), 20 );
		add_filter( 'wpseo_metadesc', array( __CLASS__, 'filter_meta_description' ) );
		add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'filter_meta_description' ) );
		add_filter( 'wpseo_twitter_description', array( __CLASS__, 'filter_meta_description' ) );
		add_filter( 'wpseo_schema_webpage', array( __CLASS__, 'filter_webpage_schema' ) );
		add_filter( 'wpseo_robots_array', array( __CLASS__, 'filter_empty_category_robots' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_legacy_category_url' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'output_faq_schema' ), 30 );
		add_action( 'geodir_single_tab_content_after', array( __CLASS__, 'render_page_sections' ), 30 );
	}

	/**
	 * Build the local-search title for the current place page.
	 *
	 * @param mixed $title Existing title.
	 */
	public static function filter_place_title( $title ): string {
		$title = is_string( $title ) ? $title : '';
		if ( ! is_singular( 'gd_place' ) ) {
			return $title;
		}

		return self::get_place_seo_title( get_queried_object_id() );
	}

	/**
	 * Return a verified locality, then a region, then the honest site-wide fallback.
	 *
	 * @param int $post_id Place ID.
	 */
	public static function get_place_location_label( int $post_id ): string {
		$data = GoNorth_Place_Data::get( $post_id );
		$city = trim( (string) ( $data['city'] ?? '' ) );
		if ( $city ) {
			return $city;
		}

		$regions = wp_get_post_terms(
			$post_id,
			'gn_region',
			array(
				'fields'  => 'names',
				'orderby' => 'term_id',
				'order'   => 'ASC',
			)
		);
		if ( ! is_wp_error( $regions ) && $regions ) {
			return trim( (string) reset( $regions ) );
		}

		return 'צפון ישראל';
	}

	/**
	 * Create a unique, location-aware title without repeating a location already
	 * present in the place name. Preserve the location and brand when shortening.
	 *
	 * @param int $post_id Place ID.
	 */
	public static function get_place_seo_title( int $post_id ): string {
		$name     = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$location = self::get_place_location_label( $post_id );
		return self::build_place_title( $name, $location );
	}

	/**
	 * Pure title formatter, exposed for deterministic regression tests.
	 *
	 * @param string $name Place name.
	 * @param string $location Verified locality or region.
	 * @param string $brand Brand label.
	 * @param int    $max_length Preferred maximum title length.
	 */
	public static function build_place_title( string $name, string $location, string $brand = 'כיוון צפון', int $max_length = 60 ): string {
		$name     = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $name ) ) );
		$name     = str_replace( array( '–', '—' ), '-', $name );
		$location = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $location ) ) );
		$brand    = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $brand ) ) );
		$normalize = static function ( string $value ): string {
			return (string) preg_replace( '/[^\p{L}\p{N}]+/u', '', mb_strtolower( $value, 'UTF-8' ) );
		};

		$name_key     = $normalize( $name );
		$location_key = $normalize( $location );
		$location_is_in_name = $location_key && false !== mb_strpos( $name_key, $location_key, 0, 'UTF-8' );

		$parts = array( $name );
		if ( $location && ! $location_is_in_name ) {
			$parts[] = $location;
		}
		if ( $brand ) {
			$parts[] = $brand;
		}
		$full = implode( ' | ', array_filter( $parts, static fn( $part ) => '' !== $part ) );
		if ( mb_strlen( $full, 'UTF-8' ) <= $max_length ) {
			return $full;
		}

		$suffix_parts = array();
		if ( $location && ! $location_is_in_name ) {
			$suffix_parts[] = $location;
		}
		if ( $brand ) {
			$suffix_parts[] = $brand;
		}
		$suffix = $suffix_parts ? ' | ' . implode( ' | ', $suffix_parts ) : '';
		$budget = $max_length - mb_strlen( $suffix, 'UTF-8' ) - 1;
		if ( $budget < 1 ) {
			return mb_substr( $full, 0, $max_length, 'UTF-8' );
		}

		$short_name = mb_substr( $name, 0, $budget, 'UTF-8' );
		if ( mb_strlen( $name, 'UTF-8' ) > $budget && ! preg_match( '/\s/u', mb_substr( $name, $budget, 1, 'UTF-8' ) ) ) {
			$short_name = preg_replace( '/\s+\S*$/u', '', $short_name );
		}
		$short_name = rtrim( (string) $short_name, " \t\n\r\0\x0B,.;:|–—-" );
		if ( '' === $short_name ) {
			$short_name = mb_substr( $name, 0, $budget, 'UTF-8' );
		}

		return $short_name . '…' . $suffix;
	}

	/**
	 * Return the canonical featured-image contract shared by Yoast and the place graph.
	 *
	 * @param int $post_id Place ID.
	 * @return array{id:string,url:string,width:int,height:int,caption:string,alt:string}|array{}
	 */
	public static function get_place_image_data( int $post_id ): array {
		$attachment_id = get_post_thumbnail_id( $post_id );
		$image         = $attachment_id ? wp_get_attachment_image_src( $attachment_id, 'full' ) : false;
		if ( ! $attachment_id || ! $image || empty( $image[0] ) ) {
			return array();
		}

		return array(
			'id'      => get_permalink( $post_id ) . '#primaryimage',
			'url'     => (string) $image[0],
			'width'   => (int) ( $image[1] ?? 0 ),
			'height'  => (int) ( $image[2] ?? 0 ),
			'caption' => trim( (string) wp_get_attachment_caption( $attachment_id ) ),
			'alt'     => trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ),
		);
	}

	/**
	 * Keep Open Graph and Twitter on the same canonical featured image.
	 *
	 * @param mixed $image Existing image URL.
	 */
	public static function filter_social_image( $image ): string {
		$image = is_string( $image ) ? $image : '';
		if ( ! is_singular( 'gd_place' ) ) {
			return $image;
		}

		$canonical = self::get_place_image_data( get_queried_object_id() );
		return $canonical ? $canonical['url'] : $image;
	}

	/**
	 * Enrich Yoast's one primary ImageObject with attachment metadata.
	 *
	 * The attachment alt is represented by the schema.org `name` property;
	 * ImageObject has no dedicated `alt` property.
	 *
	 * @param array<int,array<string,mixed>> $graph Yoast graph.
	 * @param mixed                          $context Yoast context.
	 * @return array<int,array<string,mixed>>
	 */
	public static function filter_schema_graph( array $graph, $context ): array {
		unset( $context );
		if ( ! is_singular( 'gd_place' ) ) {
			return $graph;
		}

		$image = self::get_place_image_data( get_queried_object_id() );
		if ( ! $image ) {
			return $graph;
		}

		foreach ( $graph as &$piece ) {
			$types = isset( $piece['@type'] ) ? (array) $piece['@type'] : array();
			if ( ! in_array( 'ImageObject', $types, true ) || ( $piece['@id'] ?? '' ) !== $image['id'] ) {
				continue;
			}
			$piece['url']        = $image['url'];
			$piece['contentUrl'] = $image['url'];
			if ( $image['width'] ) {
				$piece['width'] = $image['width'];
			}
			if ( $image['height'] ) {
				$piece['height'] = $image['height'];
			}
			if ( $image['caption'] ) {
				$piece['caption'] = $image['caption'];
			}
			if ( $image['alt'] ) {
				$piece['name'] = $image['alt'];
			}
			break;
		}
		unset( $piece );

		return $graph;
	}

	/**
	 * Keep empty place-category archives out of the search index.
	 *
	 * @param array<string,string> $robots Existing Yoast robots directives.
	 * @return array<string,string>
	 */
	public static function filter_empty_category_robots( array $robots ): array {
		if ( ! is_tax( 'gd_placecategory' ) ) {
			return $robots;
		}

		$term = get_queried_object();
		if ( $term instanceof WP_Term && $term->count < 1 ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}

		return $robots;
	}

	/**
	 * Redirect retired GeoDirectory category paths to their canonical archive.
	 */
	public static function redirect_legacy_category_url(): void {
		global $wp;

		$request_path = isset( $wp->request ) ? (string) $wp->request : '';
		$redirect_url = self::get_legacy_category_redirect_url( $request_path );
		if ( ! $redirect_url ) {
			return;
		}

		wp_safe_redirect( $redirect_url, 301, 'GoNorth Place Data' );
		exit;
	}

	/**
	 * Resolve a legacy category request path without redirect side effects.
	 *
	 * Pagination and feed suffixes are retained beneath the canonical term.
	 *
	 * @param string $request_path Request path or URL.
	 */
	public static function get_legacy_category_redirect_url( string $request_path ): string {
		$path = wp_parse_url( $request_path, PHP_URL_PATH );
		$path = is_string( $path ) ? trim( $path, '/' ) : '';
		if ( ! $path ) {
			return '';
		}

		$redirects = array(
			'places/category/atraktziot'   => 'places/category/atraktziot-main',
			'places/category/restaurants'  => 'places/category/restaurants-sub',
			'places/category/agalot-kafe-2' => 'places/category/agalot-kafe',
		);

		foreach ( $redirects as $legacy_path => $canonical_path ) {
			if ( $path !== $legacy_path && ! str_starts_with( $path, $legacy_path . '/' ) ) {
				continue;
			}

			$suffix = substr( $path, strlen( $legacy_path ) );
			return home_url( trailingslashit( $canonical_path . $suffix ) );
		}

		return '';
	}

	/**
	 * Build only questions backed by an explicit structured value.
	 *
	 * @param int $post_id Place ID.
	 * @return array<int,array{id:string,topic:string,question:string,answer:string}>
	 */
	public static function get_faq_items( int $post_id ): array {
		if ( 'gd_place' !== get_post_type( $post_id ) ) {
			return array();
		}

		$data  = GoNorth_Place_Data::get( $post_id );
		$title = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$items = array();

		if ( in_array( $data['open_saturday'], array( 'yes', 'no' ), true ) ) {
			$items[] = array(
				'id'       => 'saturday',
				'topic'    => 'saturday_hours',
				'question' => sprintf( 'האם %s פתוח בשבת?', $title ),
				'answer'   => 'yes' === $data['open_saturday'] ? 'כן, לפי המידע המעודכן שבידינו המקום פתוח בשבת.' : 'לא, לפי המידע המעודכן שבידינו המקום סגור בשבת.',
			);
		}

		$hours = GoNorth_Place_Data::format_business_hours( (string) $data['business_hours'] );
		if ( $hours ) {
			$formatted = array();
			foreach ( $hours as $day => $range ) {
				$formatted[] = $day . ': ' . $range;
			}
			$items[] = array(
				'id'       => 'opening-hours',
				'topic'    => 'opening_hours',
				'question' => sprintf( 'מהן שעות הפתיחה של %s?', $title ),
				'answer'   => implode( '; ', $formatted ) . '. מומלץ לוודא מול המקום לפני הגעה.',
			);
		}

		if ( in_array( $data['price_type'], array( 'free', 'paid' ), true ) ) {
			$items[] = array(
				'id'       => 'price',
				'topic'    => 'price',
				'question' => sprintf( 'האם הכניסה ל%s חינם?', $title ),
				'answer'   => 'free' === $data['price_type'] ? 'כן, הכניסה למקום חינם.' : 'לא, הכניסה או הפעילות במקום כרוכה בתשלום.',
			);
		}

		$location = GoNorth_Place_Data::format_location( $data['address'], $data['city'] );
		if ( $location ) {
			$items[] = array(
				'id'       => 'location',
				'topic'    => 'location',
				'question' => sprintf( 'איפה נמצא %s?', $title ),
				'answer'   => sprintf( '%s נמצא ב%s.', $title, $location ),
			);
		}

		if ( class_exists( 'GoNorth_Place_Attributes' ) ) {
			$children = GoNorth_Place_Attributes::get_state( $post_id, 'children' );
			if ( in_array( $children, array( 'yes', 'no' ), true ) ) {
				$items[] = array(
					'id'       => 'children',
					'topic'    => 'children',
					'question' => sprintf( 'האם %s מתאים לילדים?', $title ),
					'answer'   => 'yes' === $children ? 'כן, המקום מסומן כמתאים לילדים.' : 'לא, המקום אינו מסומן כמתאים לילדים.',
				);
			}
		}

		$items = apply_filters( 'gonorth_place_faq_items', $items, $post_id, $data );
		foreach ( $items as $index => &$item ) {
			$item['id']    = sanitize_key( $item['id'] ?? 'faq-' . ( $index + 1 ) );
			$item['topic'] = sanitize_key( $item['topic'] ?? 'other' );
		}
		unset( $item );
		return $items;
	}

	/**
	 * Return the most reliable freshness signal without claiming an unverified check.
	 *
	 * @param int $post_id Place ID.
	 * @return array{timestamp:int,iso:string,label:string,verified:bool}|array{}
	 */
	public static function get_freshness( int $post_id ): array {
		$data       = GoNorth_Place_Data::get( $post_id );
		$verified   = array_filter( array( $data['practical_checked_at'], $data['google']['checked_at'] ) );
		$timestamps = array();
		foreach ( $verified as $value ) {
			$timestamp = strtotime( (string) $value );
			if ( $timestamp ) {
				$timestamps[] = $timestamp;
			}
		}

		if ( $timestamps ) {
			$timestamp = max( $timestamps );
			$label     = 'המידע נבדק לאחרונה';
			$is_verified = true;
		} else {
			$timestamp   = (int) get_post_modified_time( 'U', true, $post_id );
			$label       = 'העמוד עודכן לאחרונה';
			$is_verified = false;
		}

		if ( ! $timestamp ) {
			return array();
		}

		return array(
			'timestamp' => $timestamp,
			'iso'       => gmdate( 'c', $timestamp ),
			'label'     => $label,
			'verified'  => $is_verified,
		);
	}

	/**
	 * Render FAQ and freshness after map/nearby sections.
	 *
	 * @param object $tab GeoDirectory tab object.
	 */
	public static function render_page_sections( $tab ): void {
		if ( empty( $tab->tab_key ) || 'post_content' !== $tab->tab_key || ! is_singular( 'gd_place' ) ) {
			return;
		}

		$post_id  = get_queried_object_id();
		$faq      = self::get_faq_items( $post_id );
		$freshness = self::get_freshness( $post_id );

		if ( $faq ) {
			$title_id = 'gn-place-faq-title-' . $post_id;
			?>
			<section class="gn-place-faq" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
				<header class="gn-place-faq__header">
					<p><?php echo esc_html__( 'תשובות מהירות', 'gonorth-place-data' ); ?></p>
					<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html__( 'שאלות נפוצות', 'gonorth-place-data' ); ?></h2>
				</header>
				<div class="gn-place-faq__items">
					<?php foreach ( $faq as $item ) : ?>
						<details class="gn-place-faq__item" data-faq-id="<?php echo esc_attr( $item['id'] ); ?>" data-faq-topic="<?php echo esc_attr( $item['topic'] ); ?>">
							<summary><?php echo esc_html( $item['question'] ); ?></summary>
							<p><?php echo esc_html( $item['answer'] ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</section>
			<?php
		}

		if ( $freshness ) {
			$format = date_i18n( 'j בF Y', $freshness['timestamp'] );
			echo '<p class="gn-place-freshness">';
			echo '<span aria-hidden="true">✓</span> ' . esc_html( $freshness['label'] ) . ': ';
			echo '<time datetime="' . esc_attr( $freshness['iso'] ) . '">' . esc_html( $format ) . '</time>';
			echo '</p>';
		}
	}

	/**
	 * Add a separate FAQPage graph only when the same factual FAQ is visible.
	 */
	public static function output_faq_schema(): void {
		if ( ! is_singular( 'gd_place' ) ) {
			return;
		}
		$post_id = get_queried_object_id();
		$items   = self::get_faq_items( $post_id );
		if ( ! $items ) {
			return;
		}

		$questions = array();
		foreach ( $items as $item ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['answer'],
				),
			);
		}

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'FAQPage',
			'@id'         => get_permalink( $post_id ) . '#faq',
			'inLanguage'  => 'he-IL',
			'mainEntity'  => $questions,
		);
		echo '<script type="application/ld+json" class="gonorth-faq-schema">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Improve GeoDirectory's existing business graph instead of duplicating it.
	 *
	 * @param array<string,mixed> $schema Existing schema.
	 * @param object              $post Place object.
	 * @return array<string,mixed>
	 */
	public static function filter_place_schema( array $schema, $post ): array {
		$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
		if ( ! $post_id || 'gd_place' !== get_post_type( $post_id ) ) {
			return $schema;
		}

		$data      = GoNorth_Place_Data::get( $post_id );
		$permalink = get_permalink( $post_id );
		$schema_type                = self::get_schema_type( $post_id );
		$schema['@type']            = $schema_type;
		$schema['@id']              = $permalink . '#place';
		$schema['url']              = $permalink;
		$schema['mainEntityOfPage'] = array( '@id' => $permalink );
		$image = self::get_place_image_data( $post_id );
		if ( $image ) {
			$schema['image'] = array( '@id' => $image['id'] );
		} else {
			unset( $schema['image'] );
		}
		if ( empty( $schema['description'] ) ) {
			$schema['description'] = self::get_generated_description( $post_id );
		}
		$business_types = array( 'LocalBusiness', 'LodgingBusiness', 'Restaurant', 'CafeOrCoffeeShop', 'Winery' );
		if ( in_array( $schema_type, $business_types, true ) ) {
			if ( 'free' === $data['price_type'] ) {
				$schema['priceRange'] = 'חינם';
			} elseif ( 'paid' === $data['price_type'] && empty( $schema['priceRange'] ) ) {
				$schema['priceRange'] = 'בתשלום';
			}
		}

		return array_filter( $schema, static fn( $value ) => '' !== $value && null !== $value && array() !== $value );
	}

	/**
	 * Put the freshness date on Yoast's WebPage node, where dateModified belongs.
	 *
	 * @param array<string,mixed> $schema Yoast WebPage graph piece.
	 * @return array<string,mixed>
	 */
	public static function filter_webpage_schema( array $schema ): array {
		if ( ! is_singular( 'gd_place' ) ) {
			return $schema;
		}
		$post_id        = get_queried_object_id();
		$schema['name'] = self::get_place_seo_title( $post_id );
		$freshness     = self::get_freshness( $post_id );
		if ( $freshness ) {
			$schema['dateModified'] = $freshness['iso'];
		}
		return $schema;
	}

	/**
	 * Keep existing Yoast descriptions and fill only genuinely empty output.
	 *
	 * @param string $description Existing description.
	 * @return string
	 */
	public static function filter_meta_description( $description ): string {
		$description = is_string( $description ) ? $description : '';
		if ( trim( $description ) || ! is_singular( 'gd_place' ) ) {
			return $description;
		}
		return self::get_generated_description( get_queried_object_id() );
	}

	/**
	 * Generate a concise Hebrew fallback description.
	 *
	 * @param int $post_id Place ID.
	 */
	public static function get_generated_description( int $post_id ): string {
		$post    = get_post( $post_id );
		$data    = GoNorth_Place_Data::get( $post_id );
		$title   = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$content = $post ? ( $post->post_excerpt ?: $post->post_content ) : '';
		$content = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $content ) ) ) );
		if ( $content ) {
			$description = $content;
		} else {
			$location    = $data['city'] ? ' ב' . $data['city'] : ' בצפון ישראל';
			$description = $title . $location . ' — מידע שימושי, מיקום, ניווט ומקומות מומלצים בסביבה באתר GoNorth.';
		}
		if ( mb_strlen( $description ) > 155 ) {
			$description = rtrim( mb_substr( $description, 0, 152 ), " \t\n\r\0\x0B,.;:-" ) . '…';
		}
		return $description;
	}

	/**
	 * Map GoNorth's primary category to a more specific schema.org type.
	 *
	 * @param int $post_id Place ID.
	 */
	private static function get_schema_type( int $post_id ): string {
		$mapping = array(
			'accommodation'   => 'LodgingBusiness',
			'restaurants-sub' => 'Restaurant',
			'agalot-kafe'     => 'CafeOrCoffeeShop',
			'yakavim'         => 'Winery',
			'teva'            => 'TouristAttraction',
			'atraktziot-main' => 'TouristAttraction',
			'tours'           => 'TouristAttraction',
		);
		$terms = get_the_terms( $post_id, 'gd_placecategory' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( isset( $mapping[ $term->slug ] ) ) {
					return $mapping[ $term->slug ];
				}
			}
		}
		return 'LocalBusiness';
	}
}
