<?php
$post = get_post(126);
$before = (string) $post->post_content;
$after = str_replace(
    [
        'https://gonorth.co.il/places/category/restaurants/',
        'https://gonorth.co.il/places/category/restaurants',
    ],
    [
        'https://gonorth.co.il/places/category/restaurants-sub/',
        'https://gonorth.co.il/places/category/restaurants-sub/',
    ],
    $before,
    $count
);
if ($count) wp_update_post(['ID' => 126, 'post_content' => $after]);
echo wp_json_encode(['ID' => 126, 'replacements' => $count, 'after_hash' => hash('sha256', $after)], JSON_PRETTY_PRINT);
