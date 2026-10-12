<?php
/**
 * Google Preferred Sources CTA and consent-aware asset loading.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Preferred_Source {
	private const DEEPLINK = 'https://www.google.com/preferences/source?q=gonorth.co.il';

	/** Prevent duplicate automatic/shortcode components. */
	private static bool $rendered = false;

	/** Register the reusable shortcode and automatic footer placement. */
	public static function init(): void {
		add_shortcode( 'gonorth_preferred_source', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 35 );
		add_action( 'wp_footer', array( __CLASS__, 'render_footer' ), 5 );
	}

	/** Load only local, non-blocking integration code on eligible surfaces. */
	public static function enqueue_assets(): void {
		if ( ! self::should_render() ) {
			return;
		}
		wp_enqueue_style(
			'gonorth-place-data',
			plugins_url( 'assets/place-data.css', GONORTH_PLACE_DATA_FILE ),
			array(),
			GONORTH_PLACE_DATA_VERSION
		);
		wp_enqueue_script(
			'gonorth-preferred-source',
			plugins_url( 'assets/preferred-source.js', GONORTH_PLACE_DATA_FILE ),
			array( 'gonorth-place-analytics' ),
			GONORTH_PLACE_DATA_VERSION,
			true
		);
	}

	/** Render one compact footer CTA on the selected public page types. */
	public static function render_footer(): void {
		if ( ! self::automatic_surface() ) {
			return;
		}
		echo self::render( 'footer', self::page_type() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Reusable shortcode for an editor-selected placement.
	 *
	 * @param array<string,string> $attributes Shortcode attributes.
	 */
	public static function shortcode( array $attributes = array() ): string {
		$attributes = shortcode_atts( array( 'placement' => 'content' ), $attributes, 'gonorth_preferred_source' );
		return self::render( sanitize_key( $attributes['placement'] ), self::page_type() );
	}

	/** Return the complete accessible CTA markup. */
	public static function render( string $placement, string $page_type ): string {
		if ( self::$rendered ) {
			return '';
		}
		self::$rendered = true;
		$place_id = 'place' === $page_type ? get_queried_object_id() : 0;

		ob_start();
		?>
		<section class="gn-preferred-source gn-container" dir="rtl" aria-labelledby="gn-preferred-source-title" data-gn-preferred-source-component="1">
			<div class="gn-preferred-source__copy">
				<span><?php echo esc_html__( 'העדפות Google', 'gonorth-place-data' ); ?></span>
				<h2 id="gn-preferred-source-title"><?php echo esc_html__( 'רוצים למצוא שוב את כיוון צפון בגוגל?', 'gonorth-place-data' ); ?></h2>
				<p><?php echo esc_html__( 'אפשר להוסיף אותנו כמקור מועדף. הבחירה אישית ועשויה להדגיש את התוכן שלנו במשטחי Google נתמכים; היא אינה מבטיחה דירוג או מיקום.', 'gonorth-place-data' ); ?></p>
			</div>
			<a class="gn-preferred-source__button" href="<?php echo esc_url( self::DEEPLINK ); ?>" target="_blank" rel="noopener" data-gn-preferred-source="1" data-placement="<?php echo esc_attr( $placement ?: 'content' ); ?>" data-page-type="<?php echo esc_attr( $page_type ); ?>"<?php echo $place_id ? ' data-place-id="' . esc_attr( (string) $place_id ) . '"' : ''; ?>>
				<?php echo esc_html__( 'הוסיפו את כיוון צפון כמקור מועדף', 'gonorth-place-data' ); ?>
			</a>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/** Whether assets or a component can be needed in this request. */
	private static function should_render(): bool {
		if ( self::automatic_surface() ) {
			return true;
		}
		if ( ! is_singular() ) {
			return false;
		}
		$post = get_post( get_queried_object_id() );
		return $post instanceof WP_Post && has_shortcode( $post->post_content, 'gonorth_preferred_source' );
	}

	/** Automatic surfaces chosen to stay visible without interrupting content. */
	private static function automatic_surface(): bool {
		return is_front_page() || is_home() || is_singular( array( 'post', 'gd_place' ) );
	}

	/** Stable analytics page type. */
	private static function page_type(): string {
		if ( is_front_page() ) return 'homepage';
		if ( is_home() ) return 'blog_index';
		if ( is_singular( 'gd_place' ) ) return 'place';
		if ( is_singular( 'post' ) ) return 'article';
		if ( is_singular( 'page' ) ) return 'page';
		return 'other';
	}
}
