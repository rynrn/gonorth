<?php
if (!defined('ABSPATH')) { exit; }
require_once ABSPATH.'wp-admin/includes/image.php';
$rows = [];
foreach (file('/tmp/issue100-photo-metadata.jsonl', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    $r=json_decode($line,true);
    $rows[$r['slug'].'-'.$r['index']]=$r;
}
$map=[
 ['id'=>498,'slug'=>'trio','index'=>2,'filename'=>'issue100-trio-google.jpg','title'=>'TRIO — טריו קפה','alt'=>'חזית טריו קפה והעגלה הוורודה בשאר ישוב'],
 ['id'=>499,'slug'=>'shiri','index'=>3,'filename'=>'issue100-shiri-pasta-google.jpg','title'=>'שירי פסטה','alt'=>'עגלת האוכל הממותגת של שירי פסטה בדפנה'],
 ['id'=>500,'slug'=>'camping','index'=>2,'filename'=>'issue100-dafna-camping-google.jpg','title'=>'קמפינג מטיילים דפנה','alt'=>'שורות אוהלים במתחם קמפינג מטיילים דפנה'],
 ['id'=>501,'slug'=>'hotel','index'=>1,'filename'=>'issue100-eretz-dafna-hotel-google.jpg','title'=>'מלון מטיילים ארץ דפנה','alt'=>'מבנה אירוח ומדשאה במלון מטיילים ארץ דפנה'],
 ['id'=>502,'slug'=>'klompus','index'=>1,'filename'=>'issue100-klompus-google.jpg','title'=>'קלומפוס','alt'=>'חלל הישיבה במסעדת קלומפוס בדפנה'],
 ['id'=>503,'slug'=>'tank','index'=>3,'filename'=>'issue100-overturned-syrian-tank-google.jpg','title'=>'הטנק הסורי ההפוך','alt'=>'הטנק ההפוך לצד ערוץ נחל חרמון'],
];
$out=[];
foreach($map as $m){
 $source='/tmp/issue100-remaining-photos/'.$m['slug'].'-'.$m['index'].'.jpg';
 if(!is_file($source)){throw new RuntimeException('Missing image: '.$source);}
 $bits=wp_upload_bits($m['filename'],null,file_get_contents($source));
 if(!empty($bits['error'])){throw new RuntimeException($bits['error']);}
 $attachment=wp_insert_attachment(['post_mime_type'=>'image/jpeg','post_title'=>$m['title'],'post_status'=>'inherit','post_parent'=>$m['id']],$bits['file'],$m['id'],true);
 if(is_wp_error($attachment)){throw new RuntimeException($attachment->get_error_message());}
 wp_update_attachment_metadata($attachment,wp_generate_attachment_metadata($attachment,$bits['file']));
 update_post_meta($attachment,'_wp_attachment_image_alt',$m['alt']);
 update_post_meta($attachment,'_gonorth_image_role','content');
 update_post_meta($attachment,'_gonorth_google_photo_fetched_at','2026-10-02T03:15:00+03:00');
 $photo=$rows[$m['slug'].'-'.$m['index']]??null;
 if(!$photo){throw new RuntimeException('Missing attribution for '.$m['slug']);}
 $attr=$photo['authorAttributions'][0]??[];
 update_post_meta($attachment,'_gonorth_google_photo_attribution_name',$attr['displayName']??'');
 update_post_meta($attachment,'_gonorth_google_photo_attribution_uri',$attr['uri']??'');
 update_post_meta($attachment,'_gonorth_google_photo_name',$photo['name']);
 set_post_thumbnail($m['id'],$attachment);
 $out[]=['post_id'=>$m['id'],'attachment_id'=>$attachment,'url'=>wp_get_attachment_url($attachment)];
}
echo wp_json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
