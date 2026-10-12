<?php
$post = get_post(448);
if (!$post) {
    echo wp_json_encode(['error' => 'post 448 missing']);
    return;
}
$before = (string) $post->post_content;
$after = str_replace(
    [
        'https://gonorth.co.il/water-trails-north-israel/',
        'https://gonorth.co.il/water-trails-north-israel',
    ],
    [
        'https://gonorth.co.il/maslulei-mayim-batzafon/',
        'https://gonorth.co.il/maslulei-mayim-batzafon/',
    ],
    $before,
    $count
);
if ($count > 0) {
    $result = wp_update_post(['ID' => 448, 'post_content' => $after], true);
    if (is_wp_error($result)) {
        echo wp_json_encode(['error' => $result->get_error_message()]);
        return;
    }
}
echo wp_json_encode([
    'ID' => 448,
    'replacements' => $count,
    'before_hash' => hash('sha256', $before),
    'after_hash' => hash('sha256', $after),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
