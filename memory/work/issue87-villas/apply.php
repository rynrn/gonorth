<?php
$post_id = 431;
$image_id = 524;
$expected = '251850e9c01f2a0d1f546c5ecb259b4ee91deee58242b59efc1d21490f8f98c1';
$old = get_post_field('post_content', $post_id);
if (hash('sha256', $old) !== $expected || get_post_thumbnail_id($post_id)) {
    fwrite(STDERR, "Guard failed: post content or thumbnail changed.\n");
    exit(2);
}
if (get_post_type($image_id) !== 'attachment' || !wp_attachment_is_image($image_id)) {
    fwrite(STDERR, "Guard failed: attachment invalid.\n");
    exit(2);
}
$new = file_get_contents('/home/claude/.local/share/gonorth-issue87-villas/post-431-after.html');
if ($new === false || !str_contains($new, 'gn-photo-credit') || str_contains($new, 'place_photo-33.jpg')) {
    fwrite(STDERR, "Guard failed: prepared content invalid.\n");
    exit(2);
}
$result = wp_update_post(['ID' => $post_id, 'post_content' => $new], true);
if (is_wp_error($result) || !$result) {
    fwrite(STDERR, "Content update failed.\n");
    exit(3);
}
$thumbnail_ok = set_post_thumbnail($post_id, $image_id);
$actual = get_post_field('post_content', $post_id);
if (!$thumbnail_ok || $actual !== $new || (int) get_post_thumbnail_id($post_id) !== $image_id) {
    wp_update_post(['ID' => $post_id, 'post_content' => $old]);
    delete_post_thumbnail($post_id);
    fwrite(STDERR, "Verification failed; content and thumbnail rollback attempted.\n");
    exit(4);
}
wp_update_post(['ID' => $post_id]); // Refresh Yoast indexable after thumbnail assignment.
echo json_encode(['status' => 'updated', 'post_id' => $post_id, 'image_id' => $image_id, 'content_sha256' => hash('sha256', get_post_field('post_content', $post_id)), 'image_url' => wp_get_attachment_url($image_id)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";

