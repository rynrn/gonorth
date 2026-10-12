<?php
$target_ids = [16,18,19,20,21,46,84,94,100,123,126,127,131,135,144,147,251,252,256,257,258,262,267,268,269,270,429,433,447];
$targets = [];
foreach ($target_ids as $id) {
    $url = get_permalink($id);
    $targets[$id] = ['url' => $url, 'path' => wp_parse_url($url, PHP_URL_PATH), 'inbound' => [], 'outbound' => []];
}
$sources = get_posts([
    'post_type' => 'any',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);
foreach ($sources as $source) {
    $content = (string) $source->post_content;
    if ($content === '') continue;
    preg_match_all('/href\s*=\s*["\']([^"\']+)["\']/iu', $content, $m);
    $source_out = [];
    foreach ($m[1] as $href) {
        $decoded = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $path = wp_parse_url($decoded, PHP_URL_PATH);
        if (!$path) continue;
        $path = trailingslashit($path);
        foreach ($targets as $tid => $target) {
            if ($path === trailingslashit($target['path'])) {
                if ((int)$source->ID !== (int)$tid) {
                    $targets[$tid]['inbound'][(string)$source->ID] = [
                        'ID' => (int)$source->ID,
                        'title' => get_the_title($source->ID),
                        'url' => get_permalink($source->ID),
                    ];
                }
                $source_out[(string)$tid] = ['ID' => $tid, 'url' => $target['url']];
            }
        }
    }
    if (isset($targets[$source->ID])) {
        $targets[$source->ID]['outbound'] = array_values($source_out);
    }
}
foreach ($targets as &$t) {
    $t['inbound'] = array_values($t['inbound']);
    $t['inbound_count'] = count($t['inbound']);
    $t['outbound_count'] = count($t['outbound']);
}
unset($t);
echo wp_json_encode($targets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
