<?php
if (!defined('ABSPATH')) { exit(1); }
$base = '/home/claude/issue94';
$snapshot = json_decode(file_get_contents($base . '/prechange.json'), true);
if (!is_array($snapshot) || count($snapshot['places'] ?? []) !== 4) { throw new RuntimeException('Missing place snapshot'); }
$manifestPath = $base . '/manifest.json';
if (file_exists($manifestPath)) { throw new RuntimeException('Manifest already exists; inspect before retry'); }
foreach ($snapshot['places'] as $id => $row) {
    $post = get_post((int)$id);
    if (!$post || hash('sha256', $post->post_content) !== $row['sha256']) {
        throw new RuntimeException("Place changed since snapshot: $id");
    }
}
$specs = [
    'yizreel' => [
        'file' => 'yizreel.html',
        'title' => 'מסלול יום בעמק יזרעאל: נחל השופט, בית שערים וכפר יהושע',
        'slug' => 'yizreel-valley-day-trip',
        'excerpt' => 'מסלול יום בעמק יזרעאל: נחל השופט, בית שערים, תחנת רכבת העמק ועצירת קפה בכפר יהושע. סדר תחנות, חלופות ומה לבדוק לפני שיוצאים.',
        'seo_title' => 'מסלול יום בעמק יזרעאל: טבע, מורשת וקפה | כיוון צפון',
        'seo_description' => 'מסלול יום בעמק יזרעאל דרך נחל השופט, בית שערים וכפר יהושע. ארבע תחנות לבחירה, מקורות רשמיים וטיפים לבדיקה לפני היציאה.',
        'focus' => 'מסלול יום בעמק יזרעאל',
    ],
    'kinneret' => [
        'file' => 'south-kinneret.html',
        'title' => 'מסלול יום בדרום הכנרת: חמת טבריה, חצר כנרת וקיאקים',
        'slug' => 'south-kinneret-day-trip',
        'excerpt' => 'מסלול יום בדרום הכנרת: חמת טבריה, חצר כנרת וקיאק־כנרת. שלוש תחנות לבחירה, מידע על תיאום ובטיחות ומקורות רשמיים.',
        'seo_title' => 'מסלול יום בדרום הכנרת: מורשת וקיאקים | כיוון צפון',
        'seo_description' => 'יום בדרום הכנרת: חמת טבריה, חצר כנרת וקיאקים ליד דגניה. סדר תחנות, חלופות ומידע שצריך לאמת מול המפעילים.',
        'focus' => 'מסלול יום בדרום הכנרת',
    ],
];
foreach ($specs as $key => $s) {
    if (get_page_by_path($s['slug'], OBJECT, ['post', 'page'])) { throw new RuntimeException("Slug exists: $key"); }
    if (mb_strlen($s['seo_title']) > 60 || mb_strlen($s['seo_description']) > 160) { throw new RuntimeException("SEO length: $key"); }
    if (!is_file($base . '/' . $s['file'])) { throw new RuntimeException("Missing draft: $key"); }
}
$manifest = ['created_at_utc'=>gmdate('c'), 'posts'=>[], 'backlinked_places'=>[]];
$save = function () use (&$manifest, $manifestPath) {
    file_put_contents($manifestPath, wp_json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
};
foreach ($specs as $key => $s) {
    $content = trim(file_get_contents($base . '/' . $s['file']));
    if (substr_count($content, '<h2>') < 4 || substr_count($content, 'data-gn-place-card="1"') < 2) { throw new RuntimeException("Draft validation: $key"); }
    $id = wp_insert_post([
        'post_type'=>'post', 'post_status'=>'draft', 'post_author'=>3,
        'post_title'=>$s['title'], 'post_name'=>$s['slug'],
        'post_excerpt'=>$s['excerpt'], 'post_content'=>$content,
    ], true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    $manifest['posts'][$key] = (int)$id;
    $save();
    wp_set_post_categories($id, [21, 25]);
    update_post_meta($id, '_yoast_wpseo_title', $s['seo_title']);
    update_post_meta($id, '_yoast_wpseo_metadesc', $s['seo_description']);
    update_post_meta($id, '_yoast_wpseo_focuskw', $s['focus']);
    $stored = get_post($id);
    if (substr_count($stored->post_content, 'data-gn-place-card="1"') < 2) { throw new RuntimeException("Tracking attributes stripped: $key"); }
}
foreach ($manifest['posts'] as $key => $id) {
    $result = wp_update_post(['ID'=>$id, 'post_status'=>'publish'], true);
    if (is_wp_error($result) || get_post_status($id) !== 'publish') { throw new RuntimeException("Publish failed: $key"); }
}
$guideUrls = [
    'yizreel' => get_permalink($manifest['posts']['yizreel']),
    'kinneret' => get_permalink($manifest['posts']['kinneret']),
];
$placeGuides = [56=>'yizreel',233=>'yizreel',327=>'kinneret',423=>'kinneret'];
foreach ($placeGuides as $id => $key) {
    $post = get_post($id);
    $expected = $snapshot['places'][(string)$id]['sha256'];
    if (hash('sha256', $post->post_content) !== $expected) { throw new RuntimeException("Place changed before backlink: $id"); }
    $label = $key === 'yizreel' ? 'מסלול יום בעמק יזרעאל' : 'מסלול יום בדרום הכנרת';
    $append = "\n\n<p>לתכנון ביקור באזור, ראו את <a href=\"" . esc_url($guideUrls[$key]) . "\">" . esc_html($label) . "</a>, עם סדר תחנות ומידע שצריך לבדוק לפני היציאה.</p>";
    $result = wp_update_post(['ID'=>$id, 'post_content'=>$post->post_content . $append], true);
    if (is_wp_error($result)) { throw new RuntimeException("Backlink failed: $id"); }
    $manifest['backlinked_places'][] = (int)$id;
    $save();
}
echo wp_json_encode(['posts'=>$manifest['posts'], 'urls'=>$guideUrls, 'backlinked_places'=>$manifest['backlinked_places']], JSON_UNESCAPED_UNICODE) . "\n";
