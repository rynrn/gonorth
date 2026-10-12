<?php
if(!defined('ABSPATH')){exit;}
foreach([498,499,500,501,502,503] as $id){wp_update_post(['ID'=>$id,'post_status'=>'draft']);}
$s=json_decode(file_get_contents('/tmp/2026-10-02-issue-100-banias-before.json'),true);
wp_update_post(['ID'=>191,'post_excerpt'=>$s['excerpt'],'post_content'=>$s['content']]);
foreach(['_gonorth_verification_source_url'=>'source_url','_gonorth_verification_source_type'=>'source_type','_gonorth_verification_checked_at'=>'source_checked_at'] as $meta=>$key){if($s[$key]===''){delete_post_meta(191,$meta);}else{update_post_meta(191,$meta,$s[$key]);}}
wp_cache_flush();
echo "Issue #100 final batch rolled back to draft and Banias restored.\n";
