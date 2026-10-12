<?php
if (!defined('ABSPATH')){exit;}
$terms=['סביוני הגליל','אחוזת ירדן','Yarden Estate','Norah Café','קפה נורה'];
$out=[];
$posts=get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1]);
foreach($posts as $p){foreach($terms as $t){if(mb_stripos($p->post_title,$t)!==false){$out[]=['ID'=>$p->ID,'title'=>$p->post_title,'status'=>$p->post_status,'term'=>$t,'place_id'=>get_post_meta($p->ID,'google_place_id',true)];}}}
foreach(['ChIJtRgS9_0fHBUR13OMYCCut_0','ChIJA8-i1YGhHhURZDxFMbqONQs'] as $pid){$ids=get_posts(['post_type'=>'gd_place','post_status'=>'any','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>[['key'=>'google_place_id','value'=>$pid]]]);$out[]=['place_id_check'=>$pid,'matches'=>$ids];}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;

