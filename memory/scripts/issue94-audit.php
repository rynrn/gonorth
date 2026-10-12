<?php
if (!defined('ABSPATH')) { exit(1); }
$manifest=json_decode(file_get_contents('/home/claude/issue94/manifest.json'),true);
$out=['checked_at_utc'=>gmdate('c'),'posts'=>[],'places'=>[]];
foreach (($manifest['posts']??[]) as $key=>$id) {
  $p=get_post((int)$id); $terms=wp_get_post_terms((int)$id,'category',['fields'=>'slugs']);
  $out['posts'][$key]=['id'=>(int)$id,'status'=>$p->post_status,'url'=>get_permalink($p),'title'=>$p->post_title,'categories'=>$terms,'body_sha256'=>hash('sha256',$p->post_content),'figures'=>substr_count($p->post_content,'<figure>'),'tables'=>substr_count($p->post_content,'<table>'),'tracked_links'=>substr_count($p->post_content,'data-gn-place-card="1"'),'seo_title'=>get_post_meta($id,'_yoast_wpseo_title',true),'seo_description'=>get_post_meta($id,'_yoast_wpseo_metadesc',true)];
}
foreach ([56,233,327,423] as $id) {
  $p=get_post($id); $key=in_array($id,[56,233],true)?'yizreel':'kinneret'; $url=$out['posts'][$key]['url'];
  $out['places'][(string)$id]=['status'=>$p->post_status,'url'=>get_permalink($p),'backlink'=>strpos($p->post_content,$url)!==false,'body_sha256'=>hash('sha256',$p->post_content)];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
