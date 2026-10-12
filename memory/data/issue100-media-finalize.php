<?php
if(!defined('ABSPATH')){exit;}
$ids=['trio'=>504,'shiri'=>505,'camping'=>506,'hotel'=>507,'klompus'=>508,'tank'=>509];
$rows=[];foreach(file('/tmp/issue100-photo-metadata.jsonl',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){$r=json_decode($line,true);$rows[$r['slug'].'-'.$r['index']]=$r;}
$map=[
 'trio'=>['post'=>498,'index'=>2,'alt'=>'חזית טריו קפה והעגלה הוורודה בשאר ישוב'],
 'shiri'=>['post'=>499,'index'=>3,'alt'=>'עגלת האוכל הממותגת של שירי פסטה בדפנה'],
 'camping'=>['post'=>500,'index'=>2,'alt'=>'שורות אוהלים במתחם קמפינג מטיילים דפנה'],
 'hotel'=>['post'=>501,'index'=>1,'alt'=>'מבנה אירוח ומדשאה במלון מטיילים ארץ דפנה'],
 'klompus'=>['post'=>502,'index'=>1,'alt'=>'חלל הישיבה במסעדת קלומפוס בדפנה'],
 'tank'=>['post'=>503,'index'=>3,'alt'=>'הטנק ההפוך לצד ערוץ נחל חרמון']
];
$out=[];
foreach($map as $slug=>$m){$aid=$ids[$slug];$photo=$rows[$slug.'-'.$m['index']];$attr=$photo['authorAttributions'][0]??[];update_post_meta($aid,'_wp_attachment_image_alt',$m['alt']);update_post_meta($aid,'_gonorth_image_role','content');update_post_meta($aid,'_gonorth_google_photo_fetched_at','2026-10-02T03:15:00+03:00');update_post_meta($aid,'_gonorth_google_photo_attribution_name',$attr['displayName']??'');update_post_meta($aid,'_gonorth_google_photo_attribution_uri',$attr['uri']??'');update_post_meta($aid,'_gonorth_google_photo_name',$photo['name']);set_post_thumbnail($m['post'],$aid);$out[]=['post_id'=>$m['post'],'attachment_id'=>$aid,'url'=>wp_get_attachment_url($aid)];}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
