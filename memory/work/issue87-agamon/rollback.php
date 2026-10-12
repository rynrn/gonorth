<?php
$post_id = 432;
$image_id = 523;
$live_hash = '761040bbc96269b8f3eb49348b284d6a8918859f64da89df9dee1fe618657318';
$before_hash = '00510b0fe714e49a961294a8c98bc1ec519cb59332c65a5fae9f9dd339a5779d';
$backup = json_decode(file_get_contents('/home/claude/.local/share/gonorth-issue87/post-432-before.json'), true);
if (!is_array($backup) || !isset($backup['post_content']) || hash('sha256', $backup['post_content']) !== $before_hash) {
    fwrite(STDERR, "Backup verification failed.\n");
    exit(2);
}
$live = get_post_field('post_content', $post_id);
if (hash('sha256', $live) !== $live_hash || (int) get_post_thumbnail_id($post_id) !== $image_id) {
    fwrite(STDERR, "Live guard failed; no rollback attempted.\n");
    exit(3);
}
if (getenv('ISSUE87_ROLLBACK_APPLY') !== '1') {
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
echo "Post content and thumbnail restored. Attachment 523 retained for review.\n";

