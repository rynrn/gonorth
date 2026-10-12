<?php
if (!defined('ABSPATH')) { exit; }
$out=[];
foreach([476,477] as $id){
 $p=get_post($id); $thumb=get_post_thumbnail_id($id);
 $out[]=[
  'ID'=>$id,'title'=>$p?$p->post_title:null,'status'=>get_post_status($id),'slug'=>$p?$p->post_name:null,
  'permalink'=>get_permalink($id),'thumbnail_id'=>$thumb,'thumbnail_url'=>wp_get_attachment_url($thumb),
  'alt'=>get_post_meta($thumb,'_wp_attachment_image_alt',true),
  'attribution_name'=>get_post_meta($thumb,'_gonorth_google_photo_attribution_name',true),
  'attribution_uri'=>get_post_meta($thumb,'_gonorth_google_photo_attribution_uri',true),
  'google_photo_name'=>get_post_meta($thumb,'_gonorth_google_photo_name',true),
  'categories'=>wp_get_post_terms($id,'gd_placecategory',['fields'=>'slugs']),
  'regions'=>wp_get_post_terms($id,'gn_region',['fields'=>'slugs']),
  'coordinates'=>[get_post_meta($id,'geodir_post_latitude',true),get_post_meta($id,'geodir_post_longitude',true)],
  'data'=>GoNorth_Place_Data::get($id)
 ];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;

