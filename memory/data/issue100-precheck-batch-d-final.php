<?php
if(!defined('ABSPATH')){exit;}
$out=[];
foreach([['t'=>'מלון אחוזת הירדן — לשעבר סביוני הגליל','p'=>'ChIJ6TyNuOMfHBURJIWElcvImbQ'],['t'=>'Norah Café — קפה נורה','p'=>'ChIJA8-i1YGhHhURZDxFMbqONQs']] as $x){$out[]=['title'=>$x['t'],'title_matches'=>get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','title'=>$x['t']]),'place_id'=>$x['p'],'place_matches'=>get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$x['p']]]])];}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;

