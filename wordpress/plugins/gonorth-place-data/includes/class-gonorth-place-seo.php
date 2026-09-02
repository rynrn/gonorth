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
		add_filter( 'wpseo_metadesc', array( __CLASS__, 'filter_meta_description' ) );
		add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'filter_meta_description' ) );
		add_filter( 'wpseo_twitter_description', array( __CLASS__, 'filter_meta_description' ) );
		add_filter( 'wpseo_schema_webpage', array( __CLASS__, 'filter_webpage_schema' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_faq_schema' ), 30 );
		add_action( 'geodir_single_tab_content_after', array( __CLASS__, 'render_page_sections' ), 30 );
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

		$location = implode( ', ', array_unique( array_filter( array( $data['address'], $data['city'] ) ) ) );
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
		if ( ! empty( $data['google']['rating'] ) && ! empty( $data['google']['review_count'] ) ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) $data['google']['rating'],
				'bestRating'  => 5,
				'worstRating' => 1,
				'ratingCount' => (int) $data['google']['review_count'],
			);
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
		$freshness = self::get_freshness( get_queried_object_id() );
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
