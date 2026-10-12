<?php
$ids = [128,173,399,405,419];
$out = [];
foreach ($ids as $id) {
    $p = get_post($id);
    $out[] = [
        'ID' => $id,
        'type' => $p ? $p->post_type : '',
        'title' => $p ? get_the_title($id) : '',
        'url' => $p ? get_permalink($id) : '',
        'content' => $p ? $p->post_content : '',
        'hash' => $p ? hash('sha256', $p->post_content) : '',
    ];
}
echo wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
