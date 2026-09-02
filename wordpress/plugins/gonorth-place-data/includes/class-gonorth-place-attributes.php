<?php
/**
 * Structured, tri-state attributes for GoNorth places.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Place_Attributes {
	public const TAXONOMY = 'gn_place_attribute';
	public const FALSE_META = '_gn_place_attribute_false';
	private const NONCE_ACTION = 'gonorth_save_place_attributes';
	private const NONCE_NAME = 'gonorth_place_attributes_nonce';

	/** @var bool Prevent canonical-field synchronization loops. */
	private static bool $syncing = false;

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 20 );
		add_action( 'add_meta_boxes_gd_place', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_gd_place', array( __CLASS__, 'save_meta_box' ), 20, 3 );
		add_action( 'gonorth_place_data_updated', array( __CLASS__, 'sync_from_place_data' ), 10, 2 );
		add_filter( 'the_content', array( __CLASS__, 'prepend_chips' ), 5 );
	}

	/**
	 * Central attribute definitions.
	 *
	 * Unknown is represented by absence from both the positive taxonomy and the
	 * explicit-false meta array. Definitions are code-owned so labels and query
	 * semantics cannot drift through ad-hoc term editing.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function definitions(): array {
		return array(
			'free'                 => array( 'label' => 'חינם', 'description' => 'הכניסה או הפעילות ללא תשלום.' ),
			'kosher'               => array( 'label' => 'כשר', 'description' => 'קיימת כשרות מאומתת.' ),
			'indoor'               => array( 'label' => 'ממוזג / מקורה', 'description' => 'הפעילות מתקיימת בחלל מקורה או ממוזג.' ),
			'shaded'               => array( 'label' => 'מוצל', 'description' => 'קיים צל משמעותי באזור הפעילות.' ),
			'water'                => array( 'label' => 'פעילות מים / ליד מים', 'description' => 'החוויה כוללת מים או נמצאת בצמוד למקור מים.' ),
			'accessible'           => array( 'label' => 'נגיש', 'description' => 'קיימת נגישות מאומתת לאנשים עם מוגבלות.' ),
			'children'             => array( 'label' => 'מתאים לילדים', 'description' => 'המקום או הפעילות מתאימים לילדים.' ),
			'couples'              => array( 'label' => 'מתאים לזוגות', 'description' => 'המקום או הפעילות מתאימים לבילוי זוגי.' ),
			'dogs_allowed'         => array( 'label' => 'כלבים מורשים', 'description' => 'מותר להגיע עם כלבים בהתאם לכללי המקום.' ),
			'parking'              => array( 'label' => 'חניה זמינה', 'description' => 'קיימת חניה זמינה למבקרים.' ),
			'reservation_required' => array( 'label' => 'נדרשת הזמנה', 'description' => 'יש להזמין מקום מראש.' ),
			'open_saturday'        => array( 'label' => 'פתוח בשבת', 'description' => 'המקום פתוח בשבת לפי מידע מאומת.' ),
		);
	}

	/**
	 * Register the positive-state query index.
	 */
	public static function register_taxonomy(): void {
		register_taxonomy(
			self::TAXONOMY,
			array( 'gd_place' ),
			array(
				'labels' => array(
					'name'          => __( 'מאפייני מקומות', 'gonorth-place-data' ),
					'singular_name' => __( 'מאפיין מקום', 'gonorth-place-data' ),
				),
				'public'             => false,
				'hierarchical'       => false,
				'show_ui'            => false,
				'show_in_rest'       => false,
				'show_admin_column'  => false,
				'query_var'          => false,
				'rewrite'            => false,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'capabilities'       => array(
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				),
			)
		);
	}

	/**
	 * Seed or update the code-owned terms.
	 *
	 * @return true|WP_Error
	 */
	public static function install_definitions() {
		self::register_taxonomy();
		foreach ( self::definitions() as $slug => $definition ) {
			$existing = term_exists( $slug, self::TAXONOMY );
			if ( ! $existing ) {
				$result = wp_insert_term(
					$definition['label'],
					self::TAXONOMY,
					array( 'slug' => $slug, 'description' => $definition['description'] )
				);
			} else {
				$term_id = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
				$result  = wp_update_term(
					$term_id,
					self::TAXONOMY,
					array( 'name' => $definition['label'], 'description' => $definition['description'], 'slug' => $slug )
				);
			}
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
		update_option( 'gonorth_place_attributes_version', GONORTH_PLACE_DATA_VERSION, false );
		return true;
	}

	/**
	 * Add the protected tri-state editor.
	 */
	public static function add_meta_box(): void {
		add_meta_box(
			'gonorth-place-attributes',
			__( 'מאפייני המקום', 'gonorth-place-data' ),
			array( __CLASS__, 'render_meta_box' ),
			'gd_place',
			'side',
			'default'
		);
	}

	/**
	 * Render the attribute editor.
	 *
	 * @param WP_Post $post Current place.
	 */
	public static function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		echo '<p>' . esc_html__( 'בחרו כן או לא רק כאשר המידע אומת. אחרת השאירו לא ידוע.', 'gonorth-place-data' ) . '</p>';
		foreach ( self::definitions() as $slug => $definition ) {
			$state = self::get_state( $post->ID, $slug );
			?>
			<p>
				<label for="gn-attribute-<?php echo esc_attr( $slug ); ?>"><strong><?php echo esc_html( $definition['label'] ); ?></strong></label><br>
				<select id="gn-attribute-<?php echo esc_attr( $slug ); ?>" name="gonorth_attributes[<?php echo esc_attr( $slug ); ?>]" class="widefat">
					<option value="unknown" <?php selected( $state, 'unknown' ); ?>><?php esc_html_e( 'לא ידוע', 'gonorth-place-data' ); ?></option>
					<option value="yes" <?php selected( $state, 'yes' ); ?>><?php esc_html_e( 'כן', 'gonorth-place-data' ); ?></option>
					<option value="no" <?php selected( $state, 'no' ); ?>><?php esc_html_e( 'לא', 'gonorth-place-data' ); ?></option>
				</select>
			</p>
			<?php
		}
	}

	/**
	 * Save the protected attribute editor.
	 *
	 * @param int     $post_id Place ID.
	 * @param WP_Post $post Place object.
	 * @param bool    $update Whether this is an update.
	 */
	public static function save_meta_box( int $post_id, WP_Post $post, bool $update ): void {
		unset( $post, $update );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		$submitted = isset( $_POST['gonorth_attributes'] ) && is_array( $_POST['gonorth_attributes'] ) ? wp_unslash( $_POST['gonorth_attributes'] ) : array();
		foreach ( self::definitions() as $slug => $definition ) {
			unset( $definition );
			$state = isset( $submitted[ $slug ] ) ? sanitize_key( $submitted[ $slug ] ) : 'unknown';
			self::set_state( $post_id, $slug, $state );
		}
	}

	/**
	 * Return yes, no or unknown for one attribute.
	 *
	 * @param int    $post_id Place ID.
	 * @param string $slug Attribute slug.
	 * @return string
	 */
	public static function get_state( int $post_id, string $slug ): string {
		if ( has_term( $slug, self::TAXONOMY, $post_id ) ) {
			return 'yes';
		}
		$false = get_post_meta( $post_id, self::FALSE_META, true );
		$false = is_array( $false ) ? $false : array();
		return in_array( $slug, $false, true ) ? 'no' : 'unknown';
	}

	/**
	 * Set a validated tri-state value without allowing contradictions.
	 *
	 * @param int    $post_id Place ID.
	 * @param string $slug Attribute slug.
	 * @param string $state yes, no or unknown.
	 * @param bool   $sync_canonical Synchronize fields owned by issue #4.
	 * @return true|WP_Error
	 */
	public static function set_state( int $post_id, string $slug, string $state, bool $sync_canonical = true ) {
		if ( 'gd_place' !== get_post_type( $post_id ) ) {
			return new WP_Error( 'invalid_place', 'A valid place is required.' );
		}
		if ( ! isset( self::definitions()[ $slug ] ) ) {
			return new WP_Error( 'invalid_attribute', 'Unknown attribute.' );
		}
		if ( ! in_array( $state, array( 'yes', 'no', 'unknown' ), true ) ) {
			return new WP_Error( 'invalid_state', 'Attribute state must be yes, no or unknown.' );
		}

		if ( ! term_exists( $slug, self::TAXONOMY ) ) {
			$installed = self::install_definitions();
			if ( is_wp_error( $installed ) ) {
				return $installed;
			}
		}

		if ( 'yes' === $state ) {
			$result = wp_set_object_terms( $post_id, $slug, self::TAXONOMY, true );
		} else {
			$result = wp_remove_object_terms( $post_id, $slug, self::TAXONOMY );
		}
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$false = get_post_meta( $post_id, self::FALSE_META, true );
		$false = is_array( $false ) ? array_values( array_intersect( $false, array_keys( self::definitions() ) ) ) : array();
		$false = array_values( array_diff( $false, array( $slug ) ) );
		if ( 'no' === $state ) {
			$false[] = $slug;
		}
		$false = array_values( array_unique( $false ) );
		sort( $false );
		if ( $false ) {
			update_post_meta( $post_id, self::FALSE_META, $false );
		} else {
			delete_post_meta( $post_id, self::FALSE_META );
		}

		if ( $sync_canonical && ! self::$syncing ) {
			self::$syncing = true;
			if ( 'free' === $slug ) {
				$mapped = array( 'yes' => 'free', 'no' => 'paid', 'unknown' => 'unknown' );
				$result = GoNorth_Place_Data::update( $post_id, array( 'price_type' => $mapped[ $state ] ) );
			} elseif ( 'open_saturday' === $slug ) {
				$result = GoNorth_Place_Data::update( $post_id, array( 'open_saturday' => $state ) );
			}
			self::$syncing = false;
			if ( isset( $result ) && is_wp_error( $result ) ) {
				return $result;
			}
		}

		clean_object_term_cache( $post_id, 'gd_place' );
		return true;
	}

	/**
	 * Keep the taxonomy query index aligned with canonical data imports.
	 *
	 * @param int                 $post_id Place ID.
	 * @param array<string,mixed> $data Updated canonical fields.
	 */
	public static function sync_from_place_data( int $post_id, array $data ): void {
		if ( self::$syncing ) {
			return;
		}
		self::$syncing = true;
		if ( isset( $data['price_type'] ) ) {
			$state = 'free' === $data['price_type'] ? 'yes' : ( 'paid' === $data['price_type'] ? 'no' : 'unknown' );
			self::set_state( $post_id, 'free', $state, false );
		}
		if ( isset( $data['open_saturday'] ) ) {
			self::set_state( $post_id, 'open_saturday', (string) $data['open_saturday'], false );
		}
		self::$syncing = false;
	}

	/**
	 * Return positive attributes in centralized display order.
	 *
	 * @param int $post_id Place ID.
	 * @return array<string,string>
	 */
	public static function get_positive( int $post_id ): array {
		$positive = array();
		foreach ( self::definitions() as $slug => $definition ) {
			if ( 'yes' === self::get_state( $post_id, $slug ) ) {
				$positive[ $slug ] = $definition['label'];
			}
		}
		return $positive;
	}

	/**
	 * Create an efficient AND query for one or more positive attributes.
	 *
	 * @param array<int,string>    $attributes Attribute slugs.
	 * @param array<string,mixed>  $args Additional WP_Query arguments.
	 * @return WP_Query|WP_Error
	 */
	public static function query( array $attributes, array $args = array() ) {
		$attributes = array_values( array_unique( array_map( 'sanitize_key', $attributes ) ) );
		if ( array_diff( $attributes, array_keys( self::definitions() ) ) ) {
			return new WP_Error( 'invalid_attributes', 'The query contains an unknown attribute.' );
		}
		$defaults = array(
			'post_type'      => 'gd_place',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'no_found_rows'  => true,
		);
		if ( $attributes ) {
			$defaults['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $attributes,
					'operator' => 'AND',
				),
			);
		}
		return new WP_Query( array_merge( $defaults, $args ) );
	}

	/**
	 * Prepend concise positive-state chips to the editorial content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function prepend_chips( string $content ): string {
		if ( ! is_singular( 'gd_place' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post_id = get_the_ID();
		if ( ! apply_filters( 'gonorth_place_attributes_in_content', true, $post_id ) ) {
			return $content;
		}
		return self::render_chips( $post_id ) . $content;
	}

	/**
	 * Render positive-state chips for placement by a theme or block.
	 *
	 * @param int $post_id Place ID.
	 * @return string
	 */
	public static function render_chips( int $post_id ): string {
		$attributes = self::get_positive( $post_id );
		if ( ! $attributes ) {
			return '';
		}

		$html = '<div class="gn-place-attributes" aria-label="' . esc_attr__( 'מאפייני המקום', 'gonorth-place-data' ) . '">';
		foreach ( $attributes as $slug => $label ) {
			$html .= '<span class="gn-place-attribute gn-place-attribute--' . esc_attr( $slug ) . '">' . esc_html( $label ) . '</span>';
		}
		$html .= '</div>';
		return $html;
	}
}

/**
 * Public query helper for archive/search integrations.
 *
 * @param array<int,string>   $attributes Attribute slugs that must all match.
 * @param array<string,mixed> $args Additional WP_Query arguments.
 * @return WP_Query|WP_Error
 */
function gonorth_query_places_by_attributes( array $attributes, array $args = array() ) {
	return GoNorth_Place_Attributes::query( $attributes, $args );
}
