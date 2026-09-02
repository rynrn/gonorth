<?php
/**
 * Canonical place data model and presentation helpers.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Place_Data {
	/** @var array<int,bool> Prevent duplicate output when GeoDirectory nests the_content filters. */
	private static array $practical_information_rendered = array();

	/**
	 * Initialize runtime integration.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'the_content', array( __CLASS__, 'append_practical_information' ), 20 );
		add_filter( 'geodir_pre_get_post_meta', array( __CLASS__, 'compat_geodir_meta' ), 20, 4 );
		add_filter( 'get_post_metadata', array( __CLASS__, 'compat_wordpress_meta' ), 20, 5 );
	}

	/**
	 * Install the GeoDirectory schema when the plugin is activated.
	 */
	public static function activate(): void {
		if ( ! function_exists( 'geodir_custom_field_save' ) ) {
			deactivate_plugins( plugin_basename( GONORTH_PLACE_DATA_FILE ) );
			wp_die( esc_html__( 'GoNorth Place Data requires GeoDirectory.', 'gonorth-place-data' ) );
		}

		$result = self::install_schema();
		if ( is_wp_error( $result ) ) {
			deactivate_plugins( plugin_basename( GONORTH_PLACE_DATA_FILE ) );
			wp_die( esc_html( $result->get_error_message() ) );
		}
		if ( class_exists( 'GoNorth_Place_Attributes' ) ) {
			$result = GoNorth_Place_Attributes::install_definitions();
			if ( is_wp_error( $result ) ) {
				deactivate_plugins( plugin_basename( GONORTH_PLACE_DATA_FILE ) );
				wp_die( esc_html( $result->get_error_message() ) );
			}
		}
	}

	/**
	 * Return the versioned field contract.
	 *
	 * GeoDirectory's built-in address field owns street, city, latitude and
	 * longitude. The fields below extend that native model rather than creating
	 * another storage layer.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function schema(): array {
		$common = array(
			'post_type'         => 'gd_place',
			'default_value'     => '',
			'is_active'         => 1,
			'is_default'        => 0,
			'is_required'       => 0,
			'required_msg'      => '',
			'placeholder_value' => '',
			'option_values'     => '',
			'show_in'           => '',
			'cat_sort'          => 0,
			'cat_filter'        => 0,
			'for_admin_use'     => 0,
			'validation_pattern'=> '',
			'validation_msg'    => '',
			'css_class'         => '',
			'field_icon'        => '',
			'frontend_desc'     => '',
		);

		$fields = array(
			'phone' => array(
				'data_type'      => 'VARCHAR',
				'field_type'     => 'phone',
				'field_type_key' => 'phone',
				'admin_title'    => 'טלפון',
				'frontend_title' => 'טלפון',
				'htmlvar_name'   => 'phone',
				'field_icon'     => 'fas fa-phone',
			),
			'website' => array(
				'data_type'      => 'TEXT',
				'field_type'     => 'url',
				'field_type_key' => 'website',
				'admin_title'    => 'אתר רשמי',
				'frontend_title' => 'אתר רשמי',
				'htmlvar_name'   => 'website',
				'field_icon'     => 'fas fa-external-link-alt',
			),
			'business_hours' => array(
				'data_type'      => 'TEXT',
				'field_type'     => 'business_hours',
				'field_type_key' => 'business_hours',
				'admin_title'    => 'שעות פתיחה',
				'frontend_title' => 'שעות פתיחה',
				'htmlvar_name'   => 'business_hours',
				'frontend_desc'  => 'שעות פתיחה לפי יום. יש להזין רק ממקור אמין.',
				'field_icon'     => 'fas fa-clock',
			),
			'price_type' => array(
				'data_type'      => 'VARCHAR',
				'field_type'     => 'select',
				'field_type_key' => 'select',
				'admin_title'    => 'סוג מחיר',
				'frontend_title' => 'מחיר',
				'htmlvar_name'   => 'price_type',
				'option_values'  => 'בחר/,חינם/free,בתשלום/paid,לא ידוע/unknown',
				'field_icon'     => 'fas fa-shekel-sign',
				'cat_filter'     => 1,
			),
			'open_saturday' => array(
				'data_type'      => 'VARCHAR',
				'field_type'     => 'select',
				'field_type_key' => 'select',
				'admin_title'    => 'פתוח בשבת',
				'frontend_title' => 'פתוח בשבת',
				'htmlvar_name'   => 'open_saturday',
				'option_values'  => 'בחר/,כן/yes,לא/no,לא ידוע/unknown',
				'field_icon'     => 'fas fa-calendar-day',
				'cat_filter'     => 1,
			),
			'google_place_id' => array(
				'data_type'      => 'VARCHAR',
				'field_type'     => 'text',
				'field_type_key' => 'text',
				'admin_title'    => 'Google Place ID',
				'frontend_title' => 'Google Place ID',
				'htmlvar_name'   => 'google_place_id',
				'for_admin_use'  => 1,
			),
			'google_rating' => array(
				'data_type'      => 'DECIMAL',
				'decimal_point'  => 1,
				'field_type'     => 'text',
				'field_type_key' => 'text',
				'admin_title'    => 'דירוג Google',
				'frontend_title' => 'דירוג Google',
				'htmlvar_name'   => 'google_rating',
				'for_admin_use'  => 1,
			),
			'google_review_count' => array(
				'data_type'      => 'INT',
				'field_type'     => 'text',
				'field_type_key' => 'text',
				'admin_title'    => 'מספר ביקורות Google',
				'frontend_title' => 'מספר ביקורות Google',
				'htmlvar_name'   => 'google_review_count',
				'for_admin_use'  => 1,
			),
			'google_checked_at' => array(
				'data_type'      => 'VARCHAR',
				'field_type'     => 'text',
				'field_type_key' => 'text',
				'admin_title'    => 'Google checked at',
				'frontend_title' => 'Google checked at',
				'htmlvar_name'   => 'google_checked_at',
				'for_admin_use'  => 1,
			),
			'practical_checked_at' => array(
				'data_type'      => 'VARCHAR',
				'field_type'     => 'text',
				'field_type_key' => 'text',
				'admin_title'    => 'Practical data checked at',
				'frontend_title' => 'Practical data checked at',
				'htmlvar_name'   => 'practical_checked_at',
				'for_admin_use'  => 1,
			),
		);

		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = array_merge( $common, $field );
		}

		return $fields;
	}

	/**
	 * Idempotently install or update the field contract.
	 *
	 * @return true|WP_Error
	 */
	public static function install_schema() {
		if ( ! function_exists( 'geodir_custom_field_save' ) || ! function_exists( 'geodir_get_field_infoby' ) ) {
			return new WP_Error( 'missing_geodirectory', 'GeoDirectory field API is unavailable.' );
		}

		foreach ( self::schema() as $field ) {
			$existing = geodir_get_field_infoby( 'htmlvar_name', $field['htmlvar_name'], 'gd_place' );
			if ( $existing ) {
				$field['field_id'] = is_object( $existing ) ? (int) $existing->id : (int) $existing['id'];
			}

			$result = geodir_custom_field_save( $field );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		update_option( 'gonorth_place_data_schema_version', GONORTH_PLACE_DATA_VERSION, false );
		return true;
	}

	/**
	 * Read canonical structured data, with legacy metadata as a rollback fallback.
	 *
	 * @param int $post_id Place ID.
	 * @return array<string,mixed>
	 */
	public static function get( int $post_id ): array {
		$detail = function_exists( 'geodir_get_post_info' ) ? geodir_get_post_info( $post_id ) : null;
		$value  = static function ( string $canonical, string $legacy = '' ) use ( $detail, $post_id ) {
			$current = is_object( $detail ) && isset( $detail->{$canonical} ) ? $detail->{$canonical} : null;
			if ( '' !== $current && null !== $current ) {
				return $current;
			}
			return $legacy ? self::get_legacy_meta( $post_id, $legacy ) : null;
		};

		return array(
			'address'              => $value( 'street', 'geodir_post_address' ),
			'city'                 => $value( 'city', 'geodir_post_city' ),
			'latitude'             => $value( 'latitude', 'geodir_post_latitude' ),
			'longitude'            => $value( 'longitude', 'geodir_post_longitude' ),
			'phone'                => $value( 'phone', 'geodir_post_phone' ),
			'website'              => $value( 'website', 'geodir_post_website' ),
			'business_hours'       => $value( 'business_hours' ),
			'price_type'           => $value( 'price_type' ),
			'open_saturday'        => $value( 'open_saturday' ),
			'practical_checked_at' => $value( 'practical_checked_at' ),
			'google'               => array(
				'place_id'     => $value( 'google_place_id', 'google_place_id' ),
				'rating'       => $value( 'google_rating', 'geodir_post_rating' ),
				'review_count' => $value( 'google_review_count' ),
				'checked_at'   => $value( 'google_checked_at' ),
			),
		);
	}

	/**
	 * Read a legacy wp_postmeta value without GeoDirectory's metadata filters.
	 *
	 * The old field names do not exist as detail-table columns, so routing them
	 * through get_post_meta() produces SQL errors before migration.
	 *
	 * @param int    $post_id Place ID.
	 * @param string $meta_key Legacy key.
	 * @return mixed
	 */
	public static function get_legacy_meta( int $post_id, string $meta_key ) {
		global $wpdb;
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id ASC LIMIT 1",
				$post_id,
				$meta_key
			)
		);
		return null === $value ? null : maybe_unserialize( $value );
	}

	/**
	 * Route the child theme's legacy geodir_post_* reads to canonical columns.
	 *
	 * @param mixed  $value Existing filtered value.
	 * @param int    $post_id Place ID.
	 * @param string $meta_key GeoDirectory key without geodir_ prefix.
	 * @param bool   $single Single value requested.
	 * @return mixed
	 */
	public static function compat_geodir_meta( $value, $post_id, $meta_key, $single ) {
		unset( $single );
		$map = array(
			'post_address'   => 'street',
			'post_city'      => 'city',
			'post_latitude'  => 'latitude',
			'post_longitude' => 'longitude',
			'post_phone'     => 'phone',
			'post_website'   => 'website',
			'post_rating'    => 'google_rating',
		);
		if ( ! isset( $map[ $meta_key ] ) || 'gd_place' !== get_post_type( $post_id ) ) {
			return $value;
		}
		$detail    = function_exists( 'geodir_get_post_info' ) ? geodir_get_post_info( $post_id ) : null;
		$canonical = is_object( $detail ) && isset( $detail->{$map[ $meta_key ]} ) ? $detail->{$map[ $meta_key ]} : null;
		return '' !== $canonical && null !== $canonical ? $canonical : $value;
	}

	/**
	 * Keep the current Google integration working while its theme call still
	 * requests an unprefixed WordPress meta key.
	 *
	 * @param mixed  $value Existing short-circuit value.
	 * @param int    $post_id Place ID.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Single value requested.
	 * @param string $meta_type Metadata type.
	 * @return mixed
	 */
	public static function compat_wordpress_meta( $value, $post_id, $meta_key, $single, $meta_type ) {
		unset( $meta_type );
		if ( 'google_place_id' !== $meta_key || 'gd_place' !== get_post_type( $post_id ) ) {
			return $value;
		}
		$detail    = function_exists( 'geodir_get_post_info' ) ? geodir_get_post_info( $post_id ) : null;
		$canonical = is_object( $detail ) && isset( $detail->google_place_id ) ? $detail->google_place_id : null;
		if ( '' === $canonical || null === $canonical ) {
			return $value;
		}
		return $single ? $canonical : array( $canonical );
	}

	/**
	 * Save trusted structured data through GeoDirectory's detail-table API.
	 *
	 * Callers are responsible for authorization. Unknown keys are ignored.
	 * A checked timestamp is written only when the caller explicitly says that
	 * the corresponding external block was verified.
	 *
	 * @param int                 $post_id Place ID.
	 * @param array<string,mixed> $data Data to save.
	 * @param bool                $practical_verified Practical data was checked.
	 * @param bool                $google_verified Google data was checked.
	 * @return true|WP_Error
	 */
	public static function update( int $post_id, array $data, bool $practical_verified = false, bool $google_verified = false ) {
		if ( 'gd_place' !== get_post_type( $post_id ) || ! function_exists( 'geodir_save_post_meta' ) ) {
			return new WP_Error( 'invalid_place', 'A valid GeoDirectory place is required.' );
		}

		$sanitizers = array(
			'street'              => 'sanitize_text_field',
			'city'                => 'sanitize_text_field',
			'latitude'            => array( __CLASS__, 'sanitize_latitude' ),
			'longitude'           => array( __CLASS__, 'sanitize_longitude' ),
			'phone'               => 'sanitize_text_field',
			'website'             => 'esc_url_raw',
			'business_hours'      => array( __CLASS__, 'sanitize_business_hours' ),
			'price_type'          => array( __CLASS__, 'sanitize_price_type' ),
			'open_saturday'       => array( __CLASS__, 'sanitize_tristate' ),
			'google_place_id'     => 'sanitize_text_field',
			'google_rating'       => array( __CLASS__, 'sanitize_rating' ),
			'google_review_count' => 'absint',
		);

		$clean_data = array();
		foreach ( $sanitizers as $key => $sanitizer ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}
			$clean = call_user_func( $sanitizer, $data[ $key ] );
			if ( is_wp_error( $clean ) ) {
				return $clean;
			}
			$clean_data[ $key ] = $clean;
		}

		foreach ( $clean_data as $key => $clean ) {
			$saved = self::save_canonical_field( $post_id, $key, $clean );
			if ( is_wp_error( $saved ) ) {
				return $saved;
			}
		}

		$now = gmdate( 'c' );
		if ( $practical_verified ) {
			self::save_canonical_field( $post_id, 'practical_checked_at', $now );
		}
		if ( $google_verified ) {
			self::save_canonical_field( $post_id, 'google_checked_at', $now );
		}

		do_action( 'gonorth_place_data_updated', $post_id, $clean_data );

		return true;
	}

	/**
	 * Safely write one allowlisted GeoDirectory detail column.
	 *
	 * GeoDirectory 2.8.160 interpolates custom-field values into a query before
	 * calling wpdb::prepare(), which breaks percent-encoded URLs. wpdb::update()
	 * keeps the value parameterized and works for every supported field value.
	 *
	 * @param int    $post_id Place ID.
	 * @param string $key Canonical field key.
	 * @param mixed  $value Sanitized value.
	 * @return true|WP_Error
	 */
	private static function save_canonical_field( int $post_id, string $key, $value ) {
		global $wpdb;
		$formats = array(
			'street'                 => '%s',
			'city'                   => '%s',
			'latitude'               => '%s',
			'longitude'              => '%s',
			'phone'                  => '%s',
			'website'                => '%s',
			'business_hours'         => '%s',
			'price_type'             => '%s',
			'open_saturday'          => '%s',
			'google_place_id'        => '%s',
			'google_rating'          => '%f',
			'google_review_count'    => '%d',
			'google_checked_at'      => '%s',
			'practical_checked_at'   => '%s',
		);
		if ( ! isset( $formats[ $key ] ) ) {
			return new WP_Error( 'invalid_field', sprintf( 'Unsupported canonical field %s.', $key ) );
		}

		$table  = $wpdb->prefix . 'geodir_gd_place_detail';
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$table} WHERE post_id = %d", $post_id ) );
		if ( ! $exists ) {
			$inserted = $wpdb->insert( $table, array( 'post_id' => $post_id ), array( '%d' ) );
			if ( false === $inserted ) {
				return new WP_Error( 'save_failed', $wpdb->last_error ?: 'Could not create GeoDirectory detail row.' );
			}
		}

		$result = $wpdb->update(
			$table,
			array( $key => $value ),
			array( 'post_id' => $post_id ),
			array( $formats[ $key ] ),
			array( '%d' )
		);
		if ( false === $result ) {
			return new WP_Error( 'save_failed', $wpdb->last_error ?: sprintf( 'Could not save canonical field %s.', $key ) );
		}

		wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
		return true;
	}

	/** @return string|WP_Error */
	public static function sanitize_latitude( $value ) {
		$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
		return false !== $value && $value >= -90 && $value <= 90 ? (string) $value : new WP_Error( 'invalid_latitude', 'Invalid latitude.' );
	}

	/** @return string|WP_Error */
	public static function sanitize_longitude( $value ) {
		$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
		return false !== $value && $value >= -180 && $value <= 180 ? (string) $value : new WP_Error( 'invalid_longitude', 'Invalid longitude.' );
	}

	/** @return string */
	public static function sanitize_business_hours( $value ): string {
		return function_exists( 'geodir_sanitize_business_hours' ) ? geodir_sanitize_business_hours( (string) $value ) : sanitize_text_field( (string) $value );
	}

	/** @return string|WP_Error */
	public static function sanitize_price_type( $value ) {
		$value = sanitize_key( (string) $value );
		return in_array( $value, array( 'free', 'paid', 'unknown', '' ), true ) ? $value : new WP_Error( 'invalid_price_type', 'Invalid price type.' );
	}

	/** @return string|WP_Error */
	public static function sanitize_tristate( $value ) {
		$value = sanitize_key( (string) $value );
		return in_array( $value, array( 'yes', 'no', 'unknown', '' ), true ) ? $value : new WP_Error( 'invalid_tristate', 'Invalid tri-state value.' );
	}

	/** @return string|WP_Error */
	public static function sanitize_rating( $value ) {
		$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
		return false !== $value && $value >= 0 && $value <= 5 ? number_format( (float) $value, 1, '.', '' ) : new WP_Error( 'invalid_rating', 'Invalid Google rating.' );
	}

	/**
	 * Enqueue the small practical-information component stylesheet.
	 */
	public static function enqueue_assets(): void {
		if ( is_singular( 'gd_place' ) ) {
			wp_enqueue_style(
				'gonorth-place-data',
				plugins_url( 'assets/place-data.css', GONORTH_PLACE_DATA_FILE ),
				array(),
				GONORTH_PLACE_DATA_VERSION
			);
			wp_enqueue_script(
				'gonorth-place-data',
				plugins_url( 'assets/place-data.js', GONORTH_PLACE_DATA_FILE ),
				array(),
				GONORTH_PLACE_DATA_VERSION,
				true
			);
		}
	}

	/**
	 * Append only meaningful structured values after the editorial description.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function append_practical_information( string $content ): string {
		if ( ! is_singular( 'gd_place' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id || isset( self::$practical_information_rendered[ $post_id ] ) || false !== strpos( $content, 'class="gn-practical-data"' ) ) {
			return $content;
		}

		$data  = self::get( $post_id );
		$items = array();

		if ( 'free' === $data['price_type'] ) {
			$items[] = array( 'מחיר', 'הכניסה חינם' );
		} elseif ( 'paid' === $data['price_type'] ) {
			$items[] = array( 'מחיר', 'הכניסה בתשלום' );
		}

		if ( 'yes' === $data['open_saturday'] ) {
			$items[] = array( 'שבת', 'פתוח בשבת' );
		} elseif ( 'no' === $data['open_saturday'] ) {
			$items[] = array( 'שבת', 'סגור בשבת' );
		}

		$hours = self::format_business_hours( (string) $data['business_hours'] );
		if ( ! $items && ! $hours ) {
			return $content;
		}
		self::$practical_information_rendered[ $post_id ] = true;
		$title_id       = 'gn-practical-data-title-' . $post_id;
		$hours_note_id  = 'gn-practical-data-hours-note-' . $post_id;
		$weekday_number = array(
			'יום שני'   => 1,
			'יום שלישי' => 2,
			'יום רביעי' => 3,
			'יום חמישי' => 4,
			'יום שישי'  => 5,
			'שבת'       => 6,
			'יום ראשון' => 7,
		);

		ob_start();
		?>
		<section class="gn-practical-data" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
			<h3 id="<?php echo esc_attr( $title_id ); ?>">מידע שימושי</h3>
			<?php if ( $items ) : ?>
				<dl class="gn-practical-data__facts">
					<?php foreach ( $items as $item ) : ?>
						<div><dt><?php echo esc_html( $item[0] ); ?></dt><dd><?php echo esc_html( $item[1] ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
			<?php if ( $hours ) : ?>
				<div class="gn-practical-data__hours-section">
					<h4>שעות פתיחה</h4>
					<p class="gn-practical-data__hours-intro">השעות המוצגות הן שעות הפעילות הרגילות.</p>
					<dl class="gn-practical-data__hours" aria-describedby="<?php echo esc_attr( $hours_note_id ); ?>">
						<?php foreach ( $hours as $day => $range ) : ?>
							<div class="gn-practical-data__hours-row" data-weekday="<?php echo esc_attr( (string) ( $weekday_number[ $day ] ?? '' ) ); ?>">
								<dt><span><?php echo esc_html( $day ); ?></span><span class="gn-practical-data__today" hidden>היום</span></dt>
								<dd><bdi><?php echo esc_html( $range ); ?></bdi></dd>
							</div>
						<?php endforeach; ?>
					</dl>
					<p id="<?php echo esc_attr( $hours_note_id ); ?>" class="gn-practical-data__hours-note">שעות הפעילות עשויות להשתנות בחגים ובאירועים. מומלץ לוודא מול המקום לפני ההגעה.</p>
				</div>
			<?php endif; ?>
		</section>
		<?php
		return $content . (string) ob_get_clean();
	}

	/**
	 * Convert GeoDirectory hours to a predictable Hebrew day/range map.
	 *
	 * @param string $raw GeoDirectory business-hours schema.
	 * @return array<string,string>
	 */
	public static function format_business_hours( string $raw ): array {
		if ( ! $raw || ! function_exists( 'geodir_get_business_hours' ) ) {
			return array();
		}
		$parsed = geodir_get_business_hours( $raw, 'IL' );
		if ( empty( $parsed['days'] ) ) {
			return array();
		}

		$names  = array( 1 => 'יום שני', 2 => 'יום שלישי', 3 => 'יום רביעי', 4 => 'יום חמישי', 5 => 'יום שישי', 6 => 'שבת', 7 => 'יום ראשון' );
		$result = array();
		foreach ( $parsed['days'] as $day ) {
			$day_no = isset( $day['day_no'] ) ? (int) $day['day_no'] : 0;
			if ( ! isset( $names[ $day_no ] ) ) {
				continue;
			}
			if ( ! empty( $day['closed'] ) ) {
				$result[ $names[ $day_no ] ] = 'סגור';
				continue;
			}
			$ranges = array();
			foreach ( $day['slots'] as $slot ) {
				if ( ! empty( $slot['range'] ) ) {
					$ranges[] = 'Open 24 hours' === $slot['range'] ? 'פתוח 24 שעות' : $slot['range'];
				}
			}
			$result[ $names[ $day_no ] ] = implode( ', ', $ranges );
		}
		return $result;
	}
}

/**
 * Stable public accessor for theme code and future recommendation/SEO features.
 *
 * @param int $post_id Place ID.
 * @return array<string,mixed>
 */
function gonorth_get_place_data( int $post_id ): array {
	return GoNorth_Place_Data::get( $post_id );
}

/**
 * Persist verified Google fields and their freshness timestamp.
 *
 * @param int                 $post_id Place ID.
 * @param array<string,mixed> $data Google data.
 * @return true|WP_Error
 */
function gonorth_store_google_place_data( int $post_id, array $data ) {
	$allowed = array_intersect_key(
		$data,
		array_flip( array( 'google_place_id', 'google_rating', 'google_review_count' ) )
	);
	return GoNorth_Place_Data::update( $post_id, $allowed, false, true );
}
