<?php
$post = get_post(47);
$original = trim((string) file_get_contents('/home/claude/issue83/privacy-post.pre.html'));
$new = trim((string) file_get_contents('/home/claude/issue83/privacy-post.html'));
if (!$post || 'publish' !== $post->post_status || trim($post->post_content) !== $original || !$new) {
    WP_CLI::error('Privacy policy precondition failed; no update.');
}
$id = wp_update_post(array('ID' => 47, 'post_content' => $new), true);
if (is_wp_error($id) || 47 !== (int) $id) {
    WP_CLI::error('Privacy policy update failed.');
}
WP_CLI::success('Privacy policy #47 updated.');