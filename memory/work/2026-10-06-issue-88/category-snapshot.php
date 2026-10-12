<?php
$ids=[94,95,96,97,427,428,464];
$rows=[];
foreach($ids as $id){$post=get_post($id);$terms=wp_get_post_terms($id,'category',['fields'=>'ids']);$rows[]=['id'=>$id,'title'=>$post?$post->post_title:null,'status'=>$post?$post->post_status:null,'term_ids'=>$terms];}
echo wp_json_encode($rows,JSON_UNESCAPED_UNICODE);
