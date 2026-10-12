<?php
$urls=[
 'https://gonorth.co.il/wp-content/uploads/2026/07/place_photo-33.jpg',
 'https://gonorth.co.il/wp-content/uploads/2026/07/place_photo-25.jpg'
];
$out=[];
foreach($urls as $url){
 $id=attachment_url_to_postid($url);
 $p=$id?get_post($id):null;
 $meta=$id?wp_get_attachment_metadata($id):[];
 $out[]=[
  'url'=>$url,'id'=>$id,'title'=>$p?$p->post_title:null,'parent'=>$p?$p->post_parent:null,
  'caption'=>$p?$p->post_excerpt:null,'description'=>$p?$p->post_content:null,
  'alt'=>$id?get_post_meta($id,'_wp_attachment_image_alt',true):null,
  'width'=>$meta['width']??null,'height'=>$meta['height']??null,
  'role'=>$id?get_post_meta($id,'_gonorth_image_role',true):null,
  'attribution_name'=>$id?get_post_meta($id,'_gonorth_google_photo_attribution_name',true):null,
  'attribution_uri'=>$id?get_post_meta($id,'_gonorth_google_photo_attribution_uri',true):null,
  'source'=>$id?get_post_meta($id,'_source_url',true):null
 ];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
