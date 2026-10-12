<?php
foreach (array_slice($argv, 1) as $encoded) {
    $title = base64_decode($encoded, true);
    if ($title === false) { continue; }
    $posts = get_posts([
        'post_type' => 'gd_place',
        'post_status' => 'any',
        'posts_per_page' => -1,
        's' => $title,
    ]);
    foreach ($posts as $post) {
        if (trim(wp_strip_all_tags($post->post_title)) === trim($title)) {
            echo wp_json_encode([
                'ID' => $post->ID,
                'title' => $post->post_title,
                'status' => $post->post_status,
            ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
    }
}
