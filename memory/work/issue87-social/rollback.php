<?php
$ids = [26, 439];
$key = '_yoast_wpseo_opengraph-image';
$url = 'https://gonorth.co.il/wp-content/themes/gonorth-child/assets/social/gonorth-og-home.jpg';
foreach ($ids as $id) {
    if (get_post_meta($id, $key, true) !== $url) {
        fwrite(STDERR, "Guard failed for page $id.\n");
        exit(2);
    }
}
if (getenv('ISSUE87_SOCIAL_ROLLBACK_APPLY') !== '1') {
    echo "Rollback check passed; no change.\n";
    exit(0);
}
foreach ($ids as $id) {
    delete_post_meta($id, $key);
    wp_update_post(['ID' => $id]);
    if (get_post_meta($id, $key, true) !== '') {
        fwrite(STDERR, "Rollback verification failed for page $id.\n");
        exit(3);
    }
}
echo "Per-page OG image overrides removed; page bodies unchanged.\n";

