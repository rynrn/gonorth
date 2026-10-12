<?php
$posts = get_posts(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'ID','order'=>'DESC']);
$rows=[];
foreach($posts as $post){
 $cats=wp_get_post_terms($post->ID,'category',['fields'=>'names']);
 $words=preg_match_all('/[\p{L}\p{N}]+/u',html_entity_decode(wp_strip_all_tags(strip_shortcodes($post->post_content)),ENT_QUOTES|ENT_HTML5,'UTF-8'));
 $rows[]=['id'=>$post->ID,'title'=>$post->post_title,'category'=>implode(', ',$cats),'words'=>$words,'minutes'=>max(1,(int)ceil($words/200))];
}
echo wp_json_encode($rows,JSON_UNESCAPED_UNICODE);
