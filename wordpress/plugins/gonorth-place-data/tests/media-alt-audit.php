<?php
/**
 * Audit explicit image roles and ALT policy for issue #34.
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

$attachment_ids = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

$counts = array(
	'images'            => count( $attachment_ids ),
	'content'           => 0,
	'decorative'        => 0,
	'intentional_empty' => 0,
	'missing'           => 0,
	'unclassified'      => 0,
	'failures'          => 0,
);
$failures = array();

foreach ( $attachment_ids as $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	$role          = (string) get_post_meta( $attachment_id, GoNorth_Media_Alt::ROLE_META, true );
	$alt           = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	$file          = (string) get_post_meta( $attachment_id, '_wp_attached_file', true );

	if ( GoNorth_Media_Alt::DECORATIVE === $role ) {
		++$counts['decorative'];
		if ( '' !== $alt ) {
			$failures[] = sprintf( '%d (%s) is decorative but ALT is not empty.', $attachment_id, $file );
		} else {
			++$counts['intentional_empty'];
		}
		continue;
	}

	if ( GoNorth_Media_Alt::CONTENT !== $role ) {
		++$counts['unclassified'];
		$failures[] = sprintf( '%d (%s) has no explicit image role.', $attachment_id, $file );
		continue;
	}

	++$counts['content'];
	if ( '' === $alt ) {
		++$counts['missing'];
		$failures[] = sprintf( '%d (%s) is content but has no ALT.', $attachment_id, $file );
	} elseif ( mb_strlen( $alt ) > 125 ) {
		$failures[] = sprintf( '%d (%s) ALT exceeds 125 characters.', $attachment_id, $file );
	}
}

$counts['failures'] = count( $failures );
WP_CLI::log( wp_json_encode( $counts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
foreach ( $failures as $failure ) {
	WP_CLI::warning( $failure );
}

if ( $failures ) {
	WP_CLI::error( 'Media ALT audit failed.' );
}
if ( 3 !== $counts['decorative'] || 3 !== $counts['intentional_empty'] ) {
	WP_CLI::error( 'Expected exactly three intentional decorative images.' );
}
if ( ! has_action( 'added_post_meta', array( 'GoNorth_Media_Alt', 'maybe_fill_featured_alt' ) ) ) {
	WP_CLI::error( 'Featured-image ALT safeguard is not registered.' );
}

WP_CLI::success( 'Every content image has useful ALT and intentional empty ALT is explicitly classified.' );

