<?php
if (!defined('ABSPATH')) { exit; }
$targets=[
 ['title'=>'פאן ריידר בצפון','place_id'=>'ChIJs8iEj3KhHhURvORrh6SY09E'],
 ['title'=>'Nice to Meat','place_id'=>'ChIJT1nuvL8fHBURJh8BTXoSWQQ']
];
$out=[];
foreach($targets as $t){
 $title_ids=get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','title'=>$t['title']]);
 $place_ids=get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$t['place_id']]]]);
 $out[]=['target'=>$t,'title_matches'=>$title_ids,'place_id_matches'=>$place_ids];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;

