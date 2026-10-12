<?php
/**
 * Contextual correction reports and private editorial triage.
 *
 * @package GoNorth_Place_Data
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Corrections {
	private const TYPE = 'gn_correction';
	private const FIELDS = array(
		'general'       => 'מידע כללי',
		'status'        => 'סטטוס המקום',
		'hours'         => 'שעות פעילות',
		'phone'         => 'טלפון',
		'website'       => 'אתר או קישור',
		'address'       => 'כתובת או מיקום',
		'accessibility' => 'נגישות',
		'price'         => 'מחיר',
		'media'         => 'תמונה',
		'content'       => 'תוכן המדריך',
	);
	private const STATES = array(
		'new'       => 'חדש',
		'reviewing' => 'בבדיקה',
		'verified'  => 'אומת — ממתין לתיקון מאושר',
		'rejected'  => 'לא אומת',
		'resolved'  => 'טופל',
	);

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_type' ) );
		add_filter( 'the_content', array( __CLASS__, 'append_frontend' ), 60 );
		add_action( 'admin_post_nopriv_gn_submit_correction', array( __CLASS__, 'submit' ) );
		add_action( 'admin_post_gn_submit_correction', array( __CLASS__, 'submit' ) );
		add_action( 'add_meta_boxes_' . self::TYPE, array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . self::TYPE, array( __CLASS__, 'save_queue_meta' ) );
		add_filter( 'manage_' . self::TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::TYPE . '_posts_custom_column', array( __CLASS__, 'column_value' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_style' ) );
	}

	public static function register_type(): void {
		$caps = array();
		foreach ( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts' ) as $cap ) {
			$caps[ $cap ] = 'manage_options';
		}
		register_post_type( self::TYPE, array(
			'labels' => array( 'name' => 'דיווחי תיקון', 'singular_name' => 'דיווח תיקון', 'edit_item' => 'בדיקת דיווח', 'all_items' => 'תור דיווחים' ),
			'public' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_rest' => false,
			'show_in_nav_menus' => false,
			'has_archive' => false,
			'capability_type' => 'post',
			'capabilities' => $caps,
			'map_meta_cap' => false,
			'menu_icon' => 'dashicons-flag',
			'supports' => array( 'title', 'editor' ),
		) );
	}

	public static function enqueue_style(): void {
		if ( ! is_page( 'contact' ) && ! is_singular( array( 'post', 'page', 'gd_place' ) ) ) {
			return;
		}
		wp_enqueue_style( 'gonorth-corrections', plugins_url( 'assets/corrections.css', GONORTH_PLACE_DATA_FILE ), array(), GONORTH_PLACE_DATA_VERSION );
	}

	private static function source_post( int $id ): ?WP_Post {
		$post = get_post( $id );
		if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, array( 'post', 'page', 'gd_place' ), true ) ) {
			return null;
		}
		return $post;
	}

	public static function append_frontend( string $content ): string {
		if ( ! in_the_loop() || ! is_main_query() || ! is_singular( array( 'post', 'page', 'gd_place' ) ) ) {
			return $content;
		}
		$id = get_queried_object_id();
		if ( is_page( 'contact' ) ) {
			return $content . self::form();
		}
		if ( in_array( $id, array( 16, 18, 19, 20, 21, 23, 24, 25, 28, 46, 47, 100 ), true ) ) {
			return $content;
		}
		$post = self::source_post( $id );
		if ( ! $post ) {
			return $content;
		}
		$url = add_query_arg( array( 'source_id' => $id, 'field' => 'general' ), home_url( '/contact/' ) );
		return $content . '<aside class="gn-correction-link" dir="rtl"><a href="' . esc_url( $url ) . '">מצאתם טעות בעמוד? דווחו לנו</a></aside>';
	}

	private static function form(): string {
		$source_id = isset( $_GET['source_id'] ) ? absint( wp_unslash( $_GET['source_id'] ) ) : 27;
		$source = self::source_post( $source_id );
		if ( ! $source ) {
			$source_id = 27;
			$source = self::source_post( 27 );
		}
		$field = isset( $_GET['field'] ) ? sanitize_key( wp_unslash( $_GET['field'] ) ) : 'general';
		if ( ! isset( self::FIELDS[ $field ] ) ) {
			$field = 'general';
		}
		$status = isset( $_GET['report_status'] ) ? sanitize_key( wp_unslash( $_GET['report_status'] ) ) : '';
		$html = '<section id="gn-correction-form" class="gn-correction-form" dir="rtl" aria-labelledby="gn-correction-title"><h2 id="gn-correction-title">דיווח על טעות במידע</h2>';
		if ( 'received' === $status ) {
			$html .= '<p class="gn-correction-notice" role="status">הדיווח התקבל ונכנס לבדיקה. המידע באתר ישתנה רק לאחר אימות.</p>';
		} elseif ( 'error' === $status ) {
			$html .= '<p class="gn-correction-error" role="alert">הדיווח לא נשלח. בדקו שמילאתם תיאור ברור ונסו שוב.</p>';
		}
		$html .= '<p>אין צורך למסור שם, טלפון או דוא״ל. אנא אל תכללו פרטים אישיים בתיאור. דיווח של מבקר אינו משנה מידע באתר אוטומטית.</p>';
		$html .= '<p>העמוד המדווח: <a href="' . esc_url( get_permalink( $source_id ) ) . '">' . esc_html( get_the_title( $source_id ) ) . '</a></p>';
		$html .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		$html .= '<input type="hidden" name="action" value="gn_submit_correction"><input type="hidden" name="source_id" value="' . esc_attr( (string) $source_id ) . '">';
		$html .= wp_nonce_field( 'gn_submit_correction', 'gn_correction_nonce', false, false );
		$html .= '<label for="gn-correction-field">באיזה פרט יש טעות?</label><select id="gn-correction-field" name="field" required>';
		foreach ( self::FIELDS as $key => $label ) {
			$html .= '<option value="' . esc_attr( $key ) . '"' . selected( $key, $field, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$html .= '</select><label for="gn-correction-text">מה צריך לבדוק?</label><textarea id="gn-correction-text" name="report_text" rows="5" minlength="15" maxlength="800" required></textarea>';
		$html .= '<div class="gn-correction-trap" aria-hidden="true"><label for="gn-correction-website">השאירו ריק</label><input id="gn-correction-website" type="text" name="website_check" tabindex="-1" autocomplete="off"></div>';
		$html .= '<button type="submit">שליחת דיווח לבדיקה</button></form></section>';
		return $html;
	}

	private static function redirect( string $status ): void {
		wp_safe_redirect( add_query_arg( 'report_status', $status, home_url( '/contact/' ) ) . '#gn-correction-form', 303 );
		exit;
	}

	public static function submit(): void {
		$nonce = isset( $_POST['gn_correction_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['gn_correction_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'gn_submit_correction' ) ) {
			self::redirect( 'error' );
		}
		if ( ! empty( $_POST['website_check'] ) ) {
			self::redirect( 'received' );
		}
		$source_id = isset( $_POST['source_id'] ) ? absint( wp_unslash( $_POST['source_id'] ) ) : 0;
		$source = self::source_post( $source_id );
		$field = isset( $_POST['field'] ) ? sanitize_key( wp_unslash( $_POST['field'] ) ) : '';
		$body = isset( $_POST['report_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['report_text'] ) ) : '';
		if ( ! $source || ! isset( self::FIELDS[ $field ] ) || mb_strlen( $body ) < 15 || mb_strlen( $body ) > 800 ) {
			self::redirect( 'error' );
		}
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$rate_key = 'gn_corr_' . substr( hash_hmac( 'sha256', $remote, wp_salt( 'nonce' ) ), 0, 32 );
		$count = (int) get_transient( $rate_key );
		if ( $count >= 5 ) {
			self::redirect( 'error' );
		}
		set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );
		$owner_ids = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID', 'orderby' => 'ID' ) );
		$owner_id = $owner_ids ? (int) reset( $owner_ids ) : 0;
		$place_id = 'gd_place' === $source->post_type ? $source_id : 0;
		$id = wp_insert_post( array(
			'post_type' => self::TYPE,
			'post_status' => 'private',
			'post_author' => 0,
			'post_title' => 'דיווח: ' . self::FIELDS[ $field ] . ' — ' . wp_strip_all_tags( $source->post_title ),
			'post_content' => $body,
		), true );
		if ( is_wp_error( $id ) || ! $id ) {
			self::redirect( 'error' );
		}
		update_post_meta( $id, '_gn_source_post_id', $source_id );
		update_post_meta( $id, '_gn_source_url', get_permalink( $source_id ) );
		update_post_meta( $id, '_gn_place_id', $place_id );
		update_post_meta( $id, '_gn_field', $field );
		update_post_meta( $id, '_gn_workflow_status', 'new' );
		update_post_meta( $id, '_gn_owner_user_id', $owner_id );
		update_post_meta( $id, '_gn_submitted_at', gmdate( 'Y-m-d H:i:s' ) );
		update_post_meta( $id, '_gn_due_at', gmdate( 'Y-m-d H:i:s', strtotime( '+5 weekdays' ) ) );
		self::redirect( 'received' );
	}

	public static function add_meta_box(): void {
		add_meta_box( 'gn-correction-queue', 'בדיקה ותיעוד טיפול', array( __CLASS__, 'meta_box' ), self::TYPE, 'normal', 'high' );
	}

	public static function meta_box( WP_Post $post ): void {
		$source_id = (int) get_post_meta( $post->ID, '_gn_source_post_id', true );
		$field = (string) get_post_meta( $post->ID, '_gn_field', true );
		$state = (string) get_post_meta( $post->ID, '_gn_workflow_status', true );
		$owner = (int) get_post_meta( $post->ID, '_gn_owner_user_id', true );
		wp_nonce_field( 'gn_correction_queue_' . $post->ID, 'gn_correction_queue_nonce' );
		echo '<p><strong>מקור:</strong> <a href="' . esc_url( get_permalink( $source_id ) ) . '">' . esc_html( get_the_title( $source_id ) ) . '</a> (#' . esc_html( (string) $source_id ) . ')</p>';
		echo '<p><strong>שדה:</strong> ' . esc_html( self::FIELDS[ $field ] ?? $field ) . ' · <strong>יעד בדיקה פנימי:</strong> ' . esc_html( (string) get_post_meta( $post->ID, '_gn_due_at', true ) ) . ' UTC</p>';
		echo '<p><label for="gn-correction-state">מצב טיפול</label> <select id="gn-correction-state" name="gn_correction_state">';
		foreach ( self::STATES as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $state, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></p><p><label for="gn-correction-owner">אחראי</label> ';
		wp_dropdown_users( array( 'role' => 'administrator', 'name' => 'gn_correction_owner', 'id' => 'gn-correction-owner', 'selected' => $owner, 'show_option_none' => 'בחרו אחראי' ) );
		echo '</p><p>דיווח גולש נשאר לבדיקה בלבד. שינוי שעות, סטטוס, טלפון, אתר או כתובת עובר דרך place-facts והביקורת הקנונית לפני סנכרון לאתר.</p>';
		$place_id = (int) get_post_meta( $post->ID, '_gn_place_id', true );
		if ( $place_id ) {
			echo '<p><strong>משטחים לבדיקת סתירות אחרי שינוי מאושר:</strong> גוף דף המקום, FAQ ונתונים מובנים בדף הציבורי.</p>';
			$impacts = self::impact_candidates( $place_id );
			echo '<p><strong>מדריכים ועמודים שמזכירים את המקום:</strong></p><ul>';
			if ( ! $impacts ) {
				echo '<li>לא נמצאו אזכורים ישירים; חיפוש זה אינו שולל אזכורים עקיפים.</li>';
			}
			foreach ( $impacts as $row ) {
				echo '<li><a href="' . esc_url( get_edit_post_link( $row['id'] ) ) . '">' . esc_html( $row['title'] ) . '</a></li>';
			}
			echo '</ul>';
		}
	}

	public static function impact_candidates( int $place_id ): array {
		$place = self::source_post( $place_id );
		if ( ! $place || 'gd_place' !== $place->post_type ) {
			return array();
		}
		$url = rawurldecode( (string) get_permalink( $place_id ) );
		$name = wp_strip_all_tags( $place->post_title );
		$rows = array();
		$posts = get_posts( array( 'post_type' => array( 'post', 'page' ), 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		foreach ( $posts as $post ) {
			$content = rawurldecode( $post->post_content );
			if ( false !== mb_stripos( $content, $url ) || ( mb_strlen( $name ) >= 6 && false !== mb_stripos( wp_strip_all_tags( $content ), $name ) ) ) {
				$rows[] = array( 'id' => (int) $post->ID, 'title' => get_the_title( $post ) );
			}
		}
		return $rows;
	}

	public static function save_queue_meta( int $post_id ): void {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['gn_correction_queue_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['gn_correction_queue_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'gn_correction_queue_' . $post_id ) ) {
			return;
		}
		$state = isset( $_POST['gn_correction_state'] ) ? sanitize_key( wp_unslash( $_POST['gn_correction_state'] ) ) : '';
		if ( ! isset( self::STATES[ $state ] ) ) {
			return;
		}
		$old = (string) get_post_meta( $post_id, '_gn_workflow_status', true );
		update_post_meta( $post_id, '_gn_workflow_status', $state );
		if ( 'new' === $old && 'new' !== $state && ! get_post_meta( $post_id, '_gn_first_triaged_at', true ) ) {
			update_post_meta( $post_id, '_gn_first_triaged_at', gmdate( 'Y-m-d H:i:s' ) );
		}
		if ( in_array( $state, array( 'rejected', 'resolved' ), true ) && ! get_post_meta( $post_id, '_gn_resolved_at', true ) ) {
			update_post_meta( $post_id, '_gn_resolved_at', gmdate( 'Y-m-d H:i:s' ) );
		}
		$owner = isset( $_POST['gn_correction_owner'] ) ? absint( wp_unslash( $_POST['gn_correction_owner'] ) ) : 0;
		if ( $owner && user_can( $owner, 'manage_options' ) ) {
			update_post_meta( $post_id, '_gn_owner_user_id', $owner );
		}
	}

	public static function columns( array $columns ): array {
		return array( 'cb' => $columns['cb'], 'title' => 'דיווח', 'gn_source' => 'עמוד', 'gn_field' => 'שדה', 'gn_state' => 'מצב', 'gn_owner' => 'אחראי', 'gn_age' => 'זמן בתור', 'date' => $columns['date'] );
	}

	public static function column_value( string $column, int $post_id ): void {
		if ( 'gn_source' === $column ) {
			$id = (int) get_post_meta( $post_id, '_gn_source_post_id', true );
			echo esc_html( get_the_title( $id ) ) . ' (#' . esc_html( (string) $id ) . ')';
		} elseif ( 'gn_field' === $column ) {
			$field = (string) get_post_meta( $post_id, '_gn_field', true );
			echo esc_html( self::FIELDS[ $field ] ?? $field );
		} elseif ( 'gn_state' === $column ) {
			$state = (string) get_post_meta( $post_id, '_gn_workflow_status', true );
			echo esc_html( self::STATES[ $state ] ?? $state );
		} elseif ( 'gn_owner' === $column ) {
			$owner = get_userdata( (int) get_post_meta( $post_id, '_gn_owner_user_id', true ) );
			echo $owner ? esc_html( $owner->display_name ) : 'לא שויך';
		} elseif ( 'gn_age' === $column ) {
			$submitted = self::meta_utc_timestamp( $post_id, '_gn_submitted_at' );
			$due = self::meta_utc_timestamp( $post_id, '_gn_due_at' );
			echo $submitted ? esc_html( human_time_diff( $submitted, time() ) ) : '—';
			if ( $due && $due < time() && ! get_post_meta( $post_id, '_gn_resolved_at', true ) ) {
				echo ' <strong>באיחור</strong>';
			}
		}
	}

	private static function meta_utc_timestamp( int $post_id, string $key ): ?int {
		$value = (string) get_post_meta( $post_id, $key, true );
		if ( '' === $value ) {
			return null;
		}
		$timestamp = strtotime( $value . ' UTC' );
		return false === $timestamp ? null : $timestamp;
	}
	public static function cli_summary(): void {
		$reports = get_posts( array( 'post_type' => self::TYPE, 'post_status' => array( 'private', 'pending', 'draft' ), 'posts_per_page' => -1 ) );
		$counts = array_fill_keys( array_keys( self::STATES ), 0 );
		$overdue = 0;
		$triage_seconds = array();
		foreach ( $reports as $post ) {
			$state = (string) get_post_meta( $post->ID, '_gn_workflow_status', true );
			if ( isset( $counts[ $state ] ) ) {
				$counts[ $state ]++;
			}
			$due = self::meta_utc_timestamp( $post->ID, '_gn_due_at' );
			if ( $due && $due < time() && ! in_array( $state, array( 'rejected', 'resolved' ), true ) ) {
				$overdue++;
			}
			$start = self::meta_utc_timestamp( $post->ID, '_gn_submitted_at' );
			$first = self::meta_utc_timestamp( $post->ID, '_gn_first_triaged_at' );
			if ( $start && $first ) {
				$triage_seconds[] = max( 0, $first - $start );
			}
		}
		$result = array( 'total' => count( $reports ), 'states' => $counts, 'overdue' => $overdue, 'triaged_count' => count( $triage_seconds ), 'mean_first_review_hours' => $triage_seconds ? round( array_sum( $triage_seconds ) / count( $triage_seconds ) / HOUR_IN_SECONDS, 2 ) : null );
		WP_CLI::line( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) );
	}
}

