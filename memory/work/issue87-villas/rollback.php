<?php
$post_id = 431;
$image_id = 524;
$live_hash = '140c8700e0ce68630730e27d707833d5bc8e46ca8d3bc5632ea9e7e50c676577';
$before_hash = '251850e9c01f2a0d1f546c5ecb259b4ee91deee58242b59efc1d21490f8f98c1';
$backup = json_decode(file_get_contents('/home/claude/.local/share/gonorth-issue87-villas/post-431-before.json'), true);
if (!is_array($backup) || !isset($backup['post_content']) || hash('sha256', $backup['post_content']) !== $before_hash) {
    fwrite(STDERR, "Backup verification failed.\n");
    exit(2);
}
$live = get_post_field('post_content', $post_id);
if (hash('sha256', $live) !== $live_hash || (int) get_post_thumbnail_id($post_id) !== $image_id) {
    fwrite(STDERR, "Live guard failed; no rollback attempted.\n");
    exit(3);
}
if (getenv('ISSUE87_VILLAS_ROLLBACK_APPLY') !== '1') {
    echo "Rollback check passed; no change.\n";
    exit(0);
}
$result = wp_update_post(['ID' => $post_id, 'post_content' => $backup['post_content']], true);
if (is_wp_error($result) || !$result) {
    fwrite(STDERR, "Rollback content update failed.\n");
    exit(4);
}
delete_post_thumbnail($post_id);
if (hash('sha256', get_post_field('post_content', $post_id)) !== $before_hash || get_post_thumbnail_id($post_id)) {
    fwrite(STDERR, "Rollback verification failed.\n");
    exit(5);
}
echo "Post content and thumbnail restored. Attachment 524 retained for review.\n";

