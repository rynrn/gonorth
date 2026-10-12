<?php
$attrs = array('free','open_saturday','children','accessible');
$ids = get_posts(array('post_type'=>'gd_place','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC'));
$out = array('generated_at_utc'=>gmdate('c'),'total'=>count($ids),'coverage'=>array(),'places'=>array());
foreach($attrs as $a){$out['coverage'][$a]=array('yes'=>0,'no'=>0,'unknown'=>0);}
foreach($ids as $id){
  $row=array('id'=>$id,'title'=>get_the_title($id),'url'=>get_permalink($id),'states'=>array());
  $data=gonorth_get_place_data($id);
  $row['practical_checked_at']=$data['practical_checked_at'] ?? '';
  foreach($attrs as $a){$s=GoNorth_Place_Attributes::get_state($id,$a);$row['states'][$a]=$s;$out['coverage'][$a][$s]++;}
  $out['places'][]=$row;
}
echo wp_json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);