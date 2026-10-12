<?php
if (!defined('ABSPATH')) { exit; }
$targets=[['title'=>'שוורצע (משק שוורץ)','place_id'=>'ChIJM5EjMbQhHBURl3TFbMs_-PE'],['title'=>'Peti Peti — פטי פטי','place_id'=>'ChIJmeF0nmUfHBURwC8gAyBgeJg']];
$out=[];
foreach($targets as $t){
 $titles=get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','title'=>$t['title']]);
 $ids=get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$t['place_id']]]]);
 $out[]=['target'=>$t,'title_matches'=>$titles,'place_id_matches'=>$ids];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;

