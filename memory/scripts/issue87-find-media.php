<?php
$ids=get_posts(['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>-1,'fields'=>'ids']);
$out=[];
foreach($ids as $id){
 $p=get_post($id);if(!$p||strpos((string)$p->post_mime_type,'image/')!==0)continue;
 $text=$p->post_title.' '.get_post_meta($id,'_wp_attachment_image_alt',true).' '.get_attached_file($id);
 if(!preg_match('/(ויל|צימר|בקת|אגמון|חולה|hula|villa|cabin|lodg)/iu',$text))continue;
 $m=wp_get_attachment_metadata($id);
 $out[]=['id'=>$id,'title'=>$p->post_title,'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true),
 'url'=>wp_get_attachment_url($id),'width'=>$m['width']??null,'height'=>$m['height']??null,
 'role'=>get_post_meta($id,'_gonorth_image_role',true),
 'source'=>get_post_meta($id,'_source_url',true),
 'google_photo'=>get_post_meta($id,'_gonorth_google_photo_name',true),
 'attribution'=>get_post_meta($id,'_gonorth_google_photo_attribution_name',true)];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
