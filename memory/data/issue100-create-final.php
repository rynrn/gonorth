<?php
if (!defined('ABSPATH')) { exit; }
$payload = json_decode(file_get_contents('/tmp/issue100-final-candidates.json'), true);
if (!is_array($payload) || empty($payload['places'])) { throw new RuntimeException('Invalid candidate payload'); }
$created = [];
foreach ($payload['places'] as $r) {
    $titles = get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>1,'fields'=>'ids','title'=>$r['title']]);
    if ($titles) { throw new RuntimeException('Title exists: '.$r['title']); }
    $ids = get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$r['google_place_id']]]]);
    if ($ids) { throw new RuntimeException('Place ID exists: '.$r['google_place_id']); }
    $id = wp_insert_post(['post_type'=>'gd_place','post_status'=>'pending','post_title'=>$r['title'],'post_excerpt'=>$r['excerpt'],'post_content'=>$r['content']], true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    wp_set_object_terms($id, $r['category'], 'gd_placecategory', false);
    if (!empty($r['extra_category'])) { wp_set_object_terms($id, $r['extra_category'], 'gd_placecategory', true); }
    wp_set_object_terms($id, 'galil', 'gn_region', false);
    update_post_meta($id, 'geodir_post_latitude', (string)$r['latitude']);
    update_post_meta($id, 'geodir_post_longitude', (string)$r['longitude']);
    update_post_meta($id, '_gonorth_verification_source_url', $r['source_url']);
    update_post_meta($id, '_gonorth_verification_source_type', $r['source_type']);
    update_post_meta($id, '_gonorth_verification_checked_at', '2026-10-02T03:00:00+03:00');
    $data = ['street'=>$r['address'],'city'=>$r['city'],'google_place_id'=>$r['google_place_id'],'google_rating'=>$r['google_rating'],'google_review_count'=>$r['google_review_count']];
    if (!empty($r['phone'])) { $data['phone']=$r['phone']; }
    if (!empty($r['website'])) { $data['website']=$r['website']; }
    $res = GoNorth_Place_Data::update($id, $data, true, true);
    if (is_wp_error($res)) { wp_trash_post($id); throw new RuntimeException($res->get_error_message()); }
    $created[] = ['ID'=>$id,'title'=>$r['title'],'status'=>get_post_status($id)];
}
echo wp_json_encode(['created'=>$created], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;

