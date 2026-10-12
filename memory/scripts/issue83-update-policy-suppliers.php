<?php
$post = get_post(47);
$expected = trim((string) file_get_contents('/home/claude/issue83/privacy-post.stage1.html'));
$new = trim((string) file_get_contents('/home/claude/issue83/privacy-post.html'));
if (!$post || trim($post->post_content) !== $expected || !$new) WP_CLI::error('Policy precondition failed.');
$id = wp_update_post(array('ID' => 47, 'post_content' => $new), true);
if (is_wp_error($id) || 47 !== (int) $id) WP_CLI::error('Policy update failed.');
WP_CLI::success('Supplier note added to policy.');