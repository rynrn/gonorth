<?php
/**
 * Image-role classification and contextual ALT safeguards.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Media_Alt {
	public const ROLE_META = '_gonorth_image_role';
	public const CONTENT = 'content';
	public const DECORATIVE = 'decorative';

	/**
	 * Register media-library and featured-image integrations.
	 */
	public static function init(): void {
		add_action( 'added_post_meta', array( __CLASS__, 'maybe_fill_featured_alt' ), 10, 4 );
		add_action( 'updated_post_meta', array( __CLASS__, 'maybe_fill_featured_alt' ), 10, 4 );
		add_filter( 'attachment_fields_to_edit', array( __CLASS__, 'add_role_field' ), 20, 2 );
		add_filter( 'attachment_fields_to_save', array( __CLASS__, 'save_role_field' ), 20, 2 );
	}

	/**
	 * Fill a missing ALT whenever an image becomes a featured image.
	 *
	 * @param int    $meta_id    Metadata row ID.
	 * @param int    $object_id  Post ID receiving the featured image.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Attachment ID.
	 */
	public static function maybe_fill_featured_alt( $meta_id, $object_id, $meta_key, $meta_value ): void {
		unset( $meta_id );
		if ( '_thumbnail_id' !== $meta_key ) {
			return;
		}

		$attachment_id = (int) $meta_value;
		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}
		if ( self::DECORATIVE === get_post_meta( $attachment_id, self::ROLE_META, true ) ) {
			return;
		}

		update_post_meta( $attachment_id, self::ROLE_META, self::CONTENT );
		if ( '' === trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) {
			$alt = self::alt_for_post( (int) $object_id );
			if ( '' !== $alt ) {
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
			}
		}
	}

	/**
	 * Create concise Hebrew ALT text from a post's verified title and location.
	 */
	public static function alt_for_post( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		$alt = html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' );
		$alt = trim( preg_replace( '/\s+/u', ' ', $alt ) );

		if ( 'gd_place' === $post->post_type ) {
			$location = trim( (string) get_post_meta( $post_id, 'geodir_post_city', true ) );
			if ( '' === $location ) {
				$regions = wp_get_post_terms( $post_id, 'gn_region', array( 'fields' => 'names' ) );
				if ( ! is_wp_error( $regions ) && $regions ) {
					$location = trim( (string) $regions[0] );
				}
			}

			if ( '' !== $location && false === mb_stripos( $alt, $location ) ) {
				$alt .= ', ' . $location;
			}
		}

		return mb_substr( $alt, 0, 125 );
	}

	/**
	 * Add an explicit content/decorative selector to media attachment details.
	 *
	 * @param array<string,mixed> $form_fields Existing attachment fields.
	 * @param WP_Post             $post        Attachment post.
	 * @return array<string,mixed>
	 */
	public static function add_role_field( array $form_fields, WP_Post $post ): array {
		if ( ! wp_attachment_is_image( $post->ID ) ) {
			return $form_fields;
		}

		$role = (string) get_post_meta( $post->ID, self::ROLE_META, true );
		$form_fields['gonorth_image_role'] = array(
			'label' => 'תפקיד התמונה',
			'input' => 'html',
			'html'  => sprintf(
				'<select name="attachments[%1$d][gonorth_image_role]"><option value="">לא מסווג</option><option value="content"%2$s>תמונת תוכן</option><option value="decorative"%3$s>דקורטיבית — ALT ריק בכוונה</option></select>',
				$post->ID,
				selected( $role, self::CONTENT, false ),
				selected( $role, self::DECORATIVE, false )
			),
			'helps' => 'תמונת תוכן חייבת ALT שימושי. תמונה דקורטיבית נשמרת עם ALT ריק.',
		);

		return $form_fields;
	}

	/**
	 * Persist the media role and enforce its ALT policy.
	 *
	 * @param array<string,mixed> $post       Attachment update data.
	 * @param array<string,mixed> $attachment Submitted attachment fields.
	 * @return array<string,mixed>
	 */
	public static function save_role_field( array $post, array $attachment ): array {
		if ( empty( $post['ID'] ) || ! isset( $attachment['gonorth_image_role'] ) ) {
			return $post;
		}

		$attachment_id = (int) $post['ID'];
		$role          = sanitize_key( (string) $attachment['gonorth_image_role'] );
		if ( ! in_array( $role, array( self::CONTENT, self::DECORATIVE ), true ) ) {
			delete_post_meta( $attachment_id, self::ROLE_META );
			return $post;
		}

		update_post_meta( $attachment_id, self::ROLE_META, $role );
		if ( self::DECORATIVE === $role ) {
			$attachment['image_alt'] = '';
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', '' );
		} elseif ( '' === trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) {
			$parent_id = (int) wp_get_post_parent_id( $attachment_id );
			$alt       = $parent_id ? self::alt_for_post( $parent_id ) : '';
			if ( '' !== $alt ) {
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
			}
		}

		return $post;
	}
}

