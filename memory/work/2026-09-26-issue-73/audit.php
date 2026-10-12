<?php
$ids = [16,18,19,20,21,46,84,94,100,123,126,127,131,135,144,147,251,252,256,257,258,262,267,268,269,270,429,433,447];
$out = [];
foreach ($ids as $id) {
    $post = get_post($id);
    if (!$post) {
        $out[] = ['ID' => $id, 'missing' => true];
        continue;
    }
    $plain = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($post->post_content)));
    preg_match_all('/[\p{L}\p{N}]+/u', $plain, $matches);
    $out[] = [
        'ID' => $id,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => html_entity_decode(get_the_title($id), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'slug' => $post->post_name,
        'url' => get_permalink($id),
        'modified' => $post->post_modified,
        'word_count' => count($matches[0]),
        'canonical_meta' => get_post_meta($id, '_yoast_wpseo_canonical', true),
        'robots_noindex' => get_post_meta($id, '_yoast_wpseo_meta-robots-noindex', true),
        'seo_title' => get_post_meta($id, '_yoast_wpseo_title', true),
        'seo_description' => get_post_meta($id, '_yoast_wpseo_metadesc', true),
        'content' => $post->post_content,
    ];
}
echo wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
