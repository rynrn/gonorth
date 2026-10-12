<?php
if (!defined('ABSPATH')) { exit(1); }
$base = '/home/claude/issue94';
$snapshot = json_decode(file_get_contents($base . '/prechange.json'), true);
$manifest = json_decode(file_get_contents($base . '/manifest.json'), true);
if (!is_array($snapshot) || !is_array($manifest)) { throw new RuntimeException('Missing recovery files'); }
foreach (($manifest['posts'] ?? []) as $key => $id) {
    $post = get_post((int)$id);
    if ($post && $post->post_type === 'post' && $post->post_status !== 'draft') {
        $result = wp_update_post(['ID'=>(int)$id, 'post_status'=>'draft'], true);
        if (is_wp_error($result)) { throw new RuntimeException("Could not unpublish $id"); }
    }
}
foreach (($manifest['backlinked_places'] ?? []) as $id) {
    $row = $snapshot['places'][(string)$id] ?? null;
    if (!$row) { throw new RuntimeException("Missing original body: $id"); }
    $post = get_post((int)$id);
    if (!$post || $post->post_type !== 'gd_place') { throw new RuntimeException("Place missing: $id"); }
    $result = wp_update_post(['ID'=>(int)$id, 'post_content'=>$row['content']], true);
    if (is_wp_error($result) || hash('sha256', get_post((int)$id)->post_content) !== $row['sha256']) {
        throw new RuntimeException("Could not restore place: $id");
    }
}
echo wp_json_encode(['drafted_posts'=>$manifest['posts'] ?? [], 'restored_places'=>$manifest['backlinked_places'] ?? []], JSON_UNESCAPED_UNICODE) . "\n";
