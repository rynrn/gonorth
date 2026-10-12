<?php
if (count($args) !== 3) { fwrite(STDERR, "Usage: id expected_sha256 content_file\n"); exit(2); }
$id = (int) $args[0];
$expected = strtolower($args[1]);
$file = $args[2];
$current = get_post_field('post_content', $id, 'raw');
if (!is_string($current) || hash('sha256', $current) !== $expected) { fwrite(STDERR, "Concurrent content change for {$id}\n"); exit(3); }
$new_content = file_get_contents($file);
if (false === $new_content) { fwrite(STDERR, "Cannot read payload for {$id}\n"); exit(4); }
$result = wp_update_post(array('ID' => $id, 'post_content' => $new_content), true);
if (is_wp_error($result)) { fwrite(STDERR, $result->get_error_message() . "\n"); exit(5); }
$saved = get_post_field('post_content', $id, 'raw');
if (hash('sha256', $saved) !== hash('sha256', $new_content)) { fwrite(STDERR, "Post-write mismatch for {$id}\n"); exit(6); }
echo wp_json_encode(array('id' => $id, 'verified' => true, 'sha256' => hash('sha256', $saved), 'modified_gmt' => get_post_field('post_modified_gmt', $id, 'raw')));
