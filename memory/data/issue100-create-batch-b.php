<?php
if (!defined('ABSPATH')) { exit; }
$records = [
 ['title'=>'פאן ריידר בצפון','excerpt'=>'טיולי ריידרים חשמליים בעמק החולה ובגליל העליון, למשפחות, זוגות וקבוצות, בליווי מדריך מקצועי.','content'=>'<p>פאן ריידר בצפון מציעה טיולי ריידרים חשמליים בשטח בעמק החולה ובגליל העליון. הפעילות מתאימה למשפחות, זוגות וקבוצות ומתקיימת בליווי מדריך מקצועי.</p><p>החברה פועלת מקיבוץ חולתה והיא חלק מקבוצת פאן, העוסקת בפעילויות ספורט ושטח משנת 2009.</p><p><strong>מומלץ לתאם מראש</strong> ולוודא את המסלול, הזמינות ותנאי ההשתתפות לפני ההגעה.</p>','category'=>'tours','address'=>'השמורה','city'=>'חולתה','lat'=>'33.0539083','lng'=>'35.6117251','phone'=>'052-383-3515','website'=>'https://www.funraiderbzafon.co.il/','place_id'=>'ChIJs8iEj3KhHhURvORrh6SY09E','rating'=>4.9,'reviews'=>43,'source'=>'https://funraiderbzafon.co.il/אודות/'],
 ['title'=>'Nice to Meat','excerpt'=>'מסעדת שף בשרית כשרה בצומת יסוד המעלה, עם מנות בשר, תפריט יצירתי ואווירת בר־מסעדה.','content'=>'<p>Nice to Meat היא מסעדת שף בשרית כשרה בצומת יסוד המעלה. התפריט מתמקד במנות בשר ומשלב יצירתיות קולינרית באווירת בר־מסעדה.</p><p>המיקום בצומת יסוד המעלה הופך אותה לעצירה נוחה במהלך טיול בעמק החולה ובגליל העליון.</p><p><strong>מומלץ להזמין מקום ולבדוק את שעות הפעילות</strong> לפני ההגעה.</p>','category'=>'restaurants-sub','address'=>'צומת יסוד המעלה','city'=>'יסוד המעלה','lat'=>'33.0450249','lng'=>'35.5734238','phone'=>'054-211-6643','website'=>'https://www.nicetomeatrest.co.il/','place_id'=>'ChIJT1nuvL8fHBURJh8BTXoSWQQ','rating'=>4.3,'reviews'=>275,'source'=>'https://www.nicetomeatrest.co.il/']
];
$created=[];
foreach($records as $r){
 $q=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','title'=>$r['title'],'posts_per_page'=>1,'fields'=>'ids']);
 if($q->posts){ throw new RuntimeException('Exact title already exists: '.$r['title'].' ID '.$q->posts[0]); }
 $mq=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','meta_key'=>'google_place_id','meta_value'=>$r['place_id'],'posts_per_page'=>1,'fields'=>'ids']);
 if($mq->posts){ throw new RuntimeException('Google Place ID already exists: '.$r['place_id'].' ID '.$mq->posts[0]); }
 $id=wp_insert_post(['post_type'=>'gd_place','post_status'=>'pending','post_title'=>$r['title'],'post_excerpt'=>$r['excerpt'],'post_content'=>$r['content']],true);
 if(is_wp_error($id)){ throw new RuntimeException($id->get_error_message()); }
 wp_set_object_terms($id,$r['category'],'gd_placecategory',false); wp_set_object_terms($id,'galil','gn_region',false);
 update_post_meta($id,'geodir_post_latitude',$r['lat']); update_post_meta($id,'geodir_post_longitude',$r['lng']);
 update_post_meta($id,'_gonorth_verification_source_url',$r['source']); update_post_meta($id,'_gonorth_verification_checked_at','2026-10-01T00:00:00+03:00');
 $result=GoNorth_Place_Data::update($id,['street'=>$r['address'],'city'=>$r['city'],'phone'=>$r['phone'],'website'=>$r['website'],'google_place_id'=>$r['place_id'],'google_rating'=>$r['rating'],'google_review_count'=>$r['reviews']],true,true);
 if(is_wp_error($result)){ wp_trash_post($id); throw new RuntimeException($result->get_error_message()); }
 $created[]=['ID'=>$id,'title'=>$r['title'],'status'=>get_post_status($id)];
}
echo wp_json_encode(['created'=>$created],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;

