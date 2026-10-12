<?php
if (!defined('ABSPATH')) { exit(1); }
$base = '/home/claude/issue94';
$manifest = json_decode(file_get_contents($base . '/manifest.json'), true);
if (!is_array($manifest) || count($manifest['posts'] ?? []) !== 2) { throw new RuntimeException('Missing manifest'); }
$map = ['yizreel'=>'yizreel.html','kinneret'=>'south-kinneret.html'];
$before = ['created_at_utc'=>gmdate('c'),'posts'=>[]];
foreach ($map as $key=>$file) {
    $id = (int)$manifest['posts'][$key];
    $post = get_post($id);
    if (!$post || $post->post_status !== 'publish' || strpos($post->post_content, '<table>') === false) { throw new RuntimeException("Unexpected current body: $key"); }
    $before['posts'][$key] = ['id'=>$id,'content'=>$post->post_content,'sha256'=>hash('sha256',$post->post_content)];
    $draft = trim(file_get_contents($base . '/' . $file));
    if (strpos($draft, '<figure><p>') === false || strpos($draft, '<table>') !== false || substr_count($draft, 'data-gn-place-card="1"') < 2) { throw new RuntimeException("Invalid new body: $key"); }
}
file_put_contents($base . '/posts-before-mobile-fix.json', wp_json_encode($before, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
foreach ($map as $key=>$file) {
    $id = (int)$manifest['posts'][$key];
    $draft = trim(file_get_contents($base . '/' . $file));
    if (hash('sha256', get_post($id)->post_content) !== $before['posts'][$key]['sha256']) { throw new RuntimeException("Changed during update: $key"); }
    $result = wp_update_post(['ID'=>$id,'post_content'=>$draft],true);
    if (is_wp_error($result) || strpos(get_post($id)->post_content,'<table>') !== false) { throw new RuntimeException("Update failed: $key"); }
}
echo wp_json_encode(['updated'=>$manifest['posts'],'figures'=>2],JSON_UNESCAPED_UNICODE) . "\n";
