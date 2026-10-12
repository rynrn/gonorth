<?php
if (!defined('ABSPATH')) { exit(1); }
$ids = [56, 233, 327, 423];
$out = ['created_at_utc' => gmdate('c'), 'places' => []];
foreach ($ids as $id) {
    $post = get_post($id);
    if (!$post || $post->post_type !== 'gd_place' || $post->post_status !== 'publish') {
        fwrite(STDERR, "Unexpected place state: $id\n");
        exit(1);
    }
    $out['places'][(string)$id] = [
        'id' => $id,
        'title' => $post->post_title,
        'slug' => $post->post_name,
        'status' => $post->post_status,
        'content' => $post->post_content,
        'sha256' => hash('sha256', $post->post_content),
        'url' => get_permalink($id),
    ];
}
$target = '/home/claude/issue94/prechange.json';
if (file_put_contents($target, wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
    fwrite(STDERR, "Snapshot write failed\n");
    exit(1);
}
echo wp_json_encode(['snapshot'=>$target,'count'=>count($out['places']),'ids'=>$ids], JSON_UNESCAPED_UNICODE) . "\n";
