<?php
$place_id = 554;
$media_id = 555;
$place = get_post( $place_id );
$media = get_post( $media_id );
if ( ! $place || $place->post_status !== 'pending' || $place->post_title !== 'שמורת טבע נחל המערות' || ! $media || $media->post_type !== 'attachment' || (int) $media->post_parent !== $place_id || $media->post_title !== 'Nahal-Mearot-Visitor-Center' || get_post_thumbnail_id( $place_id ) ) {
    fwrite( STDERR, "Unexpected place or media state\n" );
    exit( 2 );
}
$source = 'https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg';
$license = 'https://creativecommons.org/licenses/by-sa/4.0/';
$caption = 'מרכז המבקרים בשמורת נחל המערות, צילום מ־18.10.2016: ד״ר אבישי טייכר / Avi1111, <a href="' . $source . '">מקור Wikimedia Commons</a>, <a href="' . $license . '">CC BY-SA 4.0</a>. WordPress הקטין את הקובץ ויצר גרסאות תצוגה. הצילום אינו מאמת מצב נוכחי.';
$description = 'צילום מרכז המבקרים בשמורת נחל המערות מ־18.10.2016. יוצר: ד״ר אבישי טייכר / Avi1111. מקור: ' . $source . ' רישיון: CC BY-SA 4.0, ' . $license . ' הקובץ הוקטן ל־1920×1440 ו־WordPress יצר גרסאות תצוגה.';
$result = wp_update_post( array( 'ID' => $media_id, 'post_title' => 'מרכז המבקרים בשמורת נחל המערות — צילום מ־2016', 'post_excerpt' => $caption, 'post_content' => $description ), true );
if ( is_wp_error( $result ) ) {
    fwrite( STDERR, "Media metadata failed\n" );
    exit( 3 );
}
update_post_meta( $media_id, '_wp_attachment_image_alt', 'מבנה מרכז המבקרים בשמורת נחל המערות, צילום משנת 2016' );
if ( ! set_post_thumbnail( $place_id, $media_id ) ) {
    fwrite( STDERR, "Featured-image update failed\n" );
    exit( 4 );
}
echo json_encode( array( 'place_id' => $place_id, 'media_id' => $media_id, 'status' => get_post_status( $place_id ), 'thumbnail_id' => get_post_thumbnail_id( $place_id ) ), JSON_UNESCAPED_UNICODE ) . PHP_EOL;
