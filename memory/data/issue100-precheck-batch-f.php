<?php
if(!defined('ABSPATH')){exit;}
$targets=[['title'=>'מלון גליליון','place_id'=>'ChIJ-dkHnfyhHhURLLWGYN0f55o'],['title'=>'שמורת עין תאו','place_id'=>'ChIJN1UVpIKhHhUR05ABPzqnSjA']];$out=[];
foreach($targets as $t){$out[]=['title'=>$t['title'],'title_matches'=>get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','title'=>$t['title']]),'place_id'=>$t['place_id'],'place_matches'=>get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$t['place_id']]]])];}
echo wp_json_encode(['checked_at'=>'2026-10-01','targets'=>$out],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
