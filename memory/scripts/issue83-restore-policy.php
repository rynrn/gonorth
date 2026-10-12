<?php
$post = get_post(47);
$original = trim((string) file_get_contents('/home/claude/issue83/privacy-post.pre.html'));
if (!$post || !$original) {
    WP_CLI::error('Privacy policy rollback source missing.');
}
$id = wp_update_post(array('ID' => 47, 'post_content' => $original), true);
if (is_wp_error($id) || 47 !== (int) $id) {
    WP_CLI::error('Privacy policy rollback failed.');
}
WP_CLI::success('Privacy policy #47 restored.');