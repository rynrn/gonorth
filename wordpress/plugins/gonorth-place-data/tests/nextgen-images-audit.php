<?php
/**
 * Audit WebP sidecars and future-upload policy for issue #35.
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$uploads = wp_get_upload_dir();
$root    = realpath( (string) $uploads['basedir'] );
$counts  = array(
	'sources'           => 0,
	'webp_sidecars'     => 0,
	'nonbeneficial'     => 0,
	'error_markers'     => 0,
	'missing_large'     => 0,
	'source_bytes'      => 0,
	'webp_bytes'        => 0,
	'saved_bytes'       => 0,
	'largest_webp'      => 0,
	'oversized_display' => 0,
	'failures'          => 0,
);
$failures = array();

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);
foreach ( $iterator as $file ) {
	if ( ! $file->isFile() || ! preg_match( '/\.(?:jpe?g|png)$/i', $file->getFilename() ) ) {
		continue;
	}

	$source       = $file->getPathname();
	$source_bytes = (int) $file->getSize();
	$sidecar      = $source . '.webp';
	++$counts['sources'];
	$counts['source_bytes'] += $source_bytes;
	if ( ! is_file( $sidecar ) ) {
		if ( is_file( $sidecar . '.skip' ) ) {
			++$counts['nonbeneficial'];
		} elseif ( is_file( $sidecar . '.error' ) ) {
			++$counts['error_markers'];
			$failures[] = sprintf( 'WebP generation failed for %s.', $source );
		} elseif ( $source_bytes > 10240 ) {
			++$counts['missing_large'];
			$failures[] = sprintf( 'Missing WebP sidecar for %s (%d bytes).', $source, $source_bytes );
		} else {
			++$counts['nonbeneficial'];
		}
		continue;
	}

	$webp_bytes = (int) filesize( $sidecar );
	++$counts['webp_sidecars'];
	$counts['webp_bytes']   += $webp_bytes;
	$counts['saved_bytes']  += $source_bytes - $webp_bytes;
	$counts['largest_webp']  = max( $counts['largest_webp'], $webp_bytes );
	if ( $webp_bytes > MB_IN_BYTES ) {
		++$counts['oversized_display'];
		$failures[] = sprintf( 'WebP exceeds the 1 MiB display target: %s (%d bytes).', $sidecar, $webp_bytes );
	}
	if ( $webp_bytes >= $source_bytes ) {
		$failures[] = sprintf( 'WebP is not smaller than source: %s.', $source );
	}
}

if ( GoNorth_Nextgen_Images::MAX_UPLOAD_DIMENSION !== apply_filters( 'big_image_size_threshold', 2560 ) ) {
	$failures[] = 'Future-upload maximum dimension is not active.';
}
if ( ! has_filter( 'wp_generate_attachment_metadata', array( 'GoNorth_Nextgen_Images', 'generate_attachment_sidecars' ) ) ) {
	$failures[] = 'Future WebP generation hook is not active.';
}
if ( ! has_filter( 'wp_get_attachment_image', array( 'GoNorth_Nextgen_Images', 'wrap_attachment_image' ) ) ) {
	$failures[] = 'Frontend WebP picture-source hook is not active.';
}

$counts['saved_mb']   = round( $counts['saved_bytes'] / MB_IN_BYTES, 2 );
$counts['saving_pct'] = $counts['source_bytes'] > 0
	? round( 100 * $counts['saved_bytes'] / $counts['source_bytes'], 1 )
	: 0;
$counts['failures']   = count( $failures );

WP_CLI::log( wp_json_encode( $counts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) );
foreach ( $failures as $failure ) {
	WP_CLI::warning( $failure );
}
if ( $failures ) {
	WP_CLI::error( 'Next-generation image audit failed.' );
}
WP_CLI::success( 'WebP sidecars and future-upload policy are complete.' );
