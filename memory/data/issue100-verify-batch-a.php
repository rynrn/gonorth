<?php
if (!defined('ABSPATH')) { exit; }
$out=[];
foreach([470,471] as $id){
 $p=get_post($id);
 $out[]=[
  'ID'=>$id,'title'=>$p?$p->post_title:null,'status'=>get_post_status($id),'slug'=>$p?$p->post_name:null,
  'permalink'=>get_permalink($id),'thumbnail_id'=>get_post_thumbnail_id($id),
  'categories'=>wp_get_post_terms($id,'gd_placecategory',['fields'=>'slugs']),
  'regions'=>wp_get_post_terms($id,'gn_region',['fields'=>'slugs']),
  'coordinates'=>[get_post_meta($id,'geodir_post_latitude',true),get_post_meta($id,'geodir_post_longitude',true)],
  'data'=>GoNorth_Place_Data::get($id)
 ];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
