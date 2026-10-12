<?php
$post = get_post(126);
if (!$post) {
    echo wp_json_encode(['error' => 'post 126 missing']);
    return;
}
$before = (string) $post->post_content;
$after = str_replace(
    [
        'https://gonorth.co.il/places/category/attractions/',
        'https://gonorth.co.il/places/category/attractions',
    ],
    [
        'https://gonorth.co.il/places/category/atraktziot-main/',
        'https://gonorth.co.il/places/category/atraktziot-main/',
    ],
    $before,
    $count
);
if ($count > 0) {
    $result = wp_update_post(['ID' => 126, 'post_content' => $after], true);
    if (is_wp_error($result)) {
        echo wp_json_encode(['error' => $result->get_error_message()]);
        return;
    }
}
echo wp_json_encode(['ID' => 126, 'replacements' => $count, 'before_hash' => hash('sha256', $before), 'after_hash' => hash('sha256', $after)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
