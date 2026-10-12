<?php
if (!defined('ABSPATH')) { exit; }
$payload = json_decode(file_get_contents('/tmp/issue100-final-candidates.json'), true);
$out = [];
foreach ($payload['places'] as $r) {
    $titles = get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','title'=>$r['title']]);
    $ids = get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$r['google_place_id']]]]);
    $out[] = ['title'=>$r['title'],'title_matches'=>$titles,'place_id'=>$r['google_place_id'],'place_id_matches'=>$ids];
}
echo wp_json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;

