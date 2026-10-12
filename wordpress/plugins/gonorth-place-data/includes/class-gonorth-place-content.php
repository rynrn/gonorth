<?php
/**
 * Content hygiene for GoNorth place listings.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevent escaped line breaks from leaking into place content.
 */
final class GoNorth_Place_Content {
	/**
	 * Register content filters.
	 */
	public static function init(): void {
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'filter_insert_post_data' ), 10, 4 );
	}

	/**
	 * Normalize place content immediately before WordPress stores it.
	 *
	 * The wp_insert_post_data filter can receive either one or multiple
	 * backslashes before a line-break marker, depending on the import path.
	 * Normalization therefore runs on the slashed value and consumes the full
	 * escape sequence before WordPress unslashes the data for storage.
	 *
	 * @param array<string,mixed> $data Processed post data.
	 * @return array<string,mixed>
	 */
	public static function filter_insert_post_data( array $data ): array {
		if ( 'gd_place' !== ( $data['post_type'] ?? '' ) || ! isset( $data['post_content'] ) ) {
			return $data;
		}

		$content    = (string) $data['post_content'];
		$normalized = self::normalize_literal_line_breaks( $content );

		if ( $normalized !== $content ) {
			$data['post_content'] = $normalized;
		}

		return $data;
	}

	/**
	 * Convert literal backslash line-break sequences to real line breaks.
	 *
	 * Shortcode tags and code-bearing HTML elements are preserved verbatim so
	 * encoded values inside attributes, code samples or JSON-LD are not changed.
	 *
	 * @param string $content Post content.
	 */
	public static function normalize_literal_line_breaks( string $content ): string {
		if ( false === strpos( $content, '\\n' ) && false === strpos( $content, '\\r' ) ) {
			return $content;
		}

		$pattern = '~<(?:pre|code|script|style)\\b[^>]*>.*?</(?:pre|code|script|style)\\s*>|\\[[^]\\r\\n]*\\]|\\\\+r\\\\+n|\\\\+n|\\\\+r~is';
		$result  = preg_replace_callback(
			$pattern,
			static function ( array $matches ): string {
				$last_character = substr( $matches[0], -1 );
				if ( 'n' === $last_character || 'r' === $last_character ) {
					return "\n";
				}

				return $matches[0];
			},
			$content
		);

		return null === $result ? $content : $result;
	}
}
