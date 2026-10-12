<?php
/**
 * Generate WebP sidecars while preserving JPEG/PNG source URLs as fallbacks.
 *
 * @package GoNorth_Place_Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GoNorth_Nextgen_Images {
	public const MAX_UPLOAD_DIMENSION = 1920;
	public const WEBP_QUALITY = 82;

	/**
	 * Register future-upload generation and size policy.
	 */
	public static function init(): void {
		add_filter( 'big_image_size_threshold', array( __CLASS__, 'max_upload_dimension' ) );
		add_filter( 'wp_generate_attachment_metadata', array( __CLASS__, 'generate_attachment_sidecars' ), 30, 2 );
		add_filter( 'wp_get_attachment_image', array( __CLASS__, 'wrap_attachment_image' ), 20, 5 );
		add_action( 'delete_attachment', array( __CLASS__, 'delete_attachment_sidecars' ) );
		add_action( 'gonorth_generate_webp_batch', array( __CLASS__, 'run_generation_batch' ) );
	}

	/**
	 * Cap the longest side of future uploads before derivatives are generated.
	 */
	public static function max_upload_dimension(): int {
		return self::MAX_UPLOAD_DIMENSION;
	}

	/**
	 * Generate sidecars for a new or regenerated attachment.
	 *
	 * @param array<string,mixed> $metadata      Attachment metadata.
	 * @param int                 $attachment_id Attachment ID.
	 * @return array<string,mixed>
	 */
	public static function generate_attachment_sidecars( array $metadata, int $attachment_id ): array {
		$files = self::attachment_files( $attachment_id, $metadata );
		foreach ( $files as $file ) {
			self::generate_file( $file );
		}

		update_post_meta( $attachment_id, '_gonorth_webp_generated_at', gmdate( 'c' ) );
		return $metadata;
	}

	/**
	 * Return source files represented by an attachment and its registered sizes.
	 *
	 * @param int                 $attachment_id Attachment ID.
	 * @param array<string,mixed> $metadata      Optional metadata override.
	 * @return string[]
	 */
	public static function attachment_files( int $attachment_id, array $metadata = array() ): array {
		$original = get_attached_file( $attachment_id );
		if ( ! $original ) {
			return array();
		}
		if ( ! $metadata ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			$metadata = is_array( $metadata ) ? $metadata : array();
		}

		$files = array( $original );
		$dir   = dirname( $original );
		foreach ( (array) ( $metadata['sizes'] ?? array() ) as $size ) {
			if ( ! empty( $size['file'] ) ) {
				$files[] = $dir . '/' . basename( (string) $size['file'] );
			}
		}

		return array_values( array_unique( $files ) );
	}

	/**
	 * Create a smaller .webp sidecar next to one JPEG or PNG source.
	 *
	 * @return array{status:string,source_bytes:int,webp_bytes:int}
	 */
	public static function generate_file( string $source, bool $force = false ): array {
		$result = array(
			'status'       => 'unsupported',
			'source_bytes' => is_file( $source ) ? (int) filesize( $source ) : 0,
			'webp_bytes'   => 0,
		);
		if ( ! function_exists( 'imagewebp' ) || ! is_readable( $source ) ) {
			return $result;
		}

		$type = @exif_imagetype( $source );
		if ( ! in_array( $type, array( IMAGETYPE_JPEG, IMAGETYPE_PNG ), true ) ) {
			return $result;
		}

		$destination = $source . '.webp';
		$skip_marker = $destination . '.skip';
		$error_marker = $destination . '.error';
		if ( ! $force && is_file( $destination ) && filemtime( $destination ) >= filemtime( $source ) ) {
			$source_dimensions = @getimagesize( $source );
			$webp_dimensions   = @getimagesize( $destination );
			$source_within_cap = ! is_array( $source_dimensions )
				|| max( (int) $source_dimensions[0], (int) $source_dimensions[1] ) <= self::MAX_UPLOAD_DIMENSION;
			$webp_within_cap   = is_array( $webp_dimensions )
				&& max( (int) $webp_dimensions[0], (int) $webp_dimensions[1] ) <= self::MAX_UPLOAD_DIMENSION;
			if ( $source_within_cap || $webp_within_cap ) {
				$result['status']     = 'current';
				$result['webp_bytes'] = (int) filesize( $destination );
				return $result;
			}
		}
		if ( ! $force && is_file( $skip_marker ) && filemtime( $skip_marker ) >= filemtime( $source ) ) {
			$result['status'] = 'not_smaller_current';
			return $result;
		}
		if ( ! $force && is_file( $error_marker ) && filemtime( $error_marker ) >= filemtime( $source ) ) {
			$result['status'] = 'error_current';
			return $result;
		}
		if ( $force ) {
			@unlink( $skip_marker );
			@unlink( $error_marker );
		}

		$image = IMAGETYPE_JPEG === $type ? @imagecreatefromjpeg( $source ) : @imagecreatefrompng( $source );
		if ( ! $image ) {
			$result['status'] = 'error';
			@touch( $error_marker );
			return $result;
		}
		if ( IMAGETYPE_PNG === $type ) {
			imagealphablending( $image, true );
			imagesavealpha( $image, true );
		}

		$width  = imagesx( $image );
		$height = imagesy( $image );
		if ( max( $width, $height ) > self::MAX_UPLOAD_DIMENSION ) {
			$scale         = self::MAX_UPLOAD_DIMENSION / max( $width, $height );
			$target_width  = max( 1, (int) round( $width * $scale ) );
			$target_height = max( 1, (int) round( $height * $scale ) );
			$resized       = imagecreatetruecolor( $target_width, $target_height );
			if ( IMAGETYPE_PNG === $type ) {
				imagealphablending( $resized, false );
				imagesavealpha( $resized, true );
				$transparent = imagecolorallocatealpha( $resized, 0, 0, 0, 127 );
				imagefilledrectangle( $resized, 0, 0, $target_width, $target_height, $transparent );
			}
			imagecopyresampled( $resized, $image, 0, 0, 0, 0, $target_width, $target_height, $width, $height );
			imagedestroy( $image );
			$image = $resized;
		}
		imageinterlace( $image, true );

		$temp = $destination . '.tmp-' . getmypid();
		$ok   = @imagewebp( $image, $temp, self::WEBP_QUALITY );
		imagedestroy( $image );
		if ( ! $ok || ! is_file( $temp ) ) {
			@unlink( $temp );
			$result['status'] = 'error';
			@touch( $error_marker );
			return $result;
		}

		$webp_bytes = (int) filesize( $temp );
		if ( $webp_bytes >= $result['source_bytes'] ) {
			@unlink( $temp );
			@unlink( $destination );
			@touch( $skip_marker );
			$result['status'] = 'not_smaller';
			return $result;
		}

		if ( ! @rename( $temp, $destination ) ) {
			@unlink( $temp );
			$result['status'] = 'error';
			@touch( $error_marker );
			return $result;
		}
		@chmod( $destination, fileperms( $source ) & 0777 );
		@unlink( $skip_marker );
		@unlink( $error_marker );

		$result['status']     = 'converted';
		$result['webp_bytes'] = $webp_bytes;
		return $result;
	}

	/**
	 * Wrap a frontend attachment image in a WebP picture source when available.
	 *
	 * @param string               $html          Original IMG markup.
	 * @param int                  $attachment_id Attachment ID.
	 * @param string|int[]         $size          Requested image size.
	 * @param bool                 $icon          Whether this is an icon.
	 * @param array<string,string> $attr          Requested attributes.
	 */
	public static function wrap_attachment_image( $html, $attachment_id, $size, $icon, $attr ): string {
		unset( $attachment_id, $size, $icon, $attr );
		if ( is_admin() || false !== stripos( (string) $html, '<picture' ) ) {
			return (string) $html;
		}

		preg_match( '/\ssrc=["\']([^"\']+)["\']/i', (string) $html, $src_match );
		preg_match( '/\ssrcset=["\']([^"\']+)["\']/i', (string) $html, $srcset_match );
		preg_match( '/\ssizes=["\']([^"\']+)["\']/i', (string) $html, $sizes_match );
		$src = html_entity_decode( $src_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( '' === $src ) {
			return (string) $html;
		}

		$candidates = array();
		$raw_srcset = html_entity_decode( $srcset_match[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( '' !== $raw_srcset ) {
			foreach ( explode( ',', $raw_srcset ) as $candidate ) {
				$candidate = trim( $candidate );
				if ( preg_match( '/^(\S+)(?:\s+(.+))?$/', $candidate, $parts ) ) {
					$webp_url = self::webp_url( $parts[1] );
					if ( $webp_url ) {
						$candidates[] = $webp_url . ( empty( $parts[2] ) ? '' : ' ' . trim( $parts[2] ) );
					}
				}
			}
		}
		if ( ! $candidates ) {
			$webp_url = self::webp_url( $src );
			if ( $webp_url ) {
				$candidates[] = $webp_url;
			}
		}
		if ( ! $candidates ) {
			return (string) $html;
		}

		$sizes = isset( $sizes_match[1] ) ? ' sizes="' . esc_attr( html_entity_decode( $sizes_match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) . '"' : '';
		return '<picture class="gonorth-nextgen-picture"><source type="image/webp" srcset="'
			. esc_attr( implode( ', ', $candidates ) ) . '"' . $sizes . '>' . $html . '</picture>';
	}

	/**
	 * Resolve a public uploads URL to a smaller generated WebP sidecar URL.
	 */
	private static function webp_url( string $url ): string {
		$uploads = wp_get_upload_dir();
		$clean   = strtok( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), '?' );
		$baseurl = untrailingslashit( (string) $uploads['baseurl'] );
		if ( 0 !== strpos( $clean, $baseurl . '/' ) ) {
			return '';
		}

		$relative = rawurldecode( substr( $clean, strlen( $baseurl ) + 1 ) );
		$source   = trailingslashit( (string) $uploads['basedir'] ) . ltrim( $relative, '/' );
		$sidecar  = $source . '.webp';
		if ( ! is_file( $source ) || ! is_file( $sidecar ) || filesize( $sidecar ) >= filesize( $source ) ) {
			return '';
		}

		return add_query_arg( 'ver', (string) filemtime( $sidecar ), $clean . '.webp' );
	}

	/**
	 * Generate a bounded batch under PHP-FPM/www-data via WP-Cron.
	 */
	public static function run_generation_batch(): void {
		$uploads   = wp_get_upload_dir();
		$root      = realpath( (string) $uploads['basedir'] );
		$limit     = 60;
		$processed = 0;
		$stats     = get_option( 'gonorth_webp_generation_stats', array() );
		$stats     = is_array( $stats ) ? $stats : array();
		$stats     = wp_parse_args(
			$stats,
			array(
				'status'       => 'running',
				'converted'    => 0,
				'not_smaller'  => 0,
				'errors'       => 0,
				'saved_bytes'  => 0,
			)
		);

		if ( ! $root ) {
			$stats['status'] = 'error';
			update_option( 'gonorth_webp_generation_stats', $stats, false );
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
		);
		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || ! preg_match( '/\.(?:jpe?g|png)$/i', $file->getFilename() ) ) {
				continue;
			}
			$result = self::generate_file( $file->getPathname() );
			if ( in_array( $result['status'], array( 'current', 'not_smaller_current', 'error_current' ), true ) ) {
				continue;
			}

			++$processed;
			if ( 'converted' === $result['status'] ) {
				++$stats['converted'];
				$stats['saved_bytes'] += $result['source_bytes'] - $result['webp_bytes'];
			} elseif ( 'not_smaller' === $result['status'] ) {
				++$stats['not_smaller'];
			} else {
				++$stats['errors'];
			}
			if ( $processed >= $limit ) {
				break;
			}
		}

		$stats['last_batch']  = $processed;
		$stats['updated_at']  = gmdate( 'c' );
		$stats['status']      = $processed >= $limit ? 'running' : 'complete';
		update_option( 'gonorth_webp_generation_stats', $stats, false );
		if ( 'running' === $stats['status'] ) {
			wp_schedule_single_event( time() + 1, 'gonorth_generate_webp_batch' );
		}
	}

	/**
	 * Delete generated sidecars when WordPress deletes their attachment.
	 */
	public static function delete_attachment_sidecars( int $attachment_id ): void {
		foreach ( self::attachment_files( $attachment_id ) as $source ) {
			foreach ( array( $source . '.webp', $source . '.webp.skip', $source . '.webp.error' ) as $generated ) {
				if ( is_file( $generated ) ) {
					wp_delete_file( $generated );
				}
			}
		}
	}
}
