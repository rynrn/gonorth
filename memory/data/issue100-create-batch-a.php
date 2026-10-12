<?php
if (!defined('ABSPATH')) { exit; }
$records = [
 [
  'title'=>'המקום של ארנון',
  'excerpt'=>'בית קפה קהילתי בגן ירוק ביסוד המעלה, עם קפה, מאפים, יין ופינות ישיבה באווירה גלילית.',
  'content'=>'<p>המקום של ארנון הוא בית קפה קהילתי הפועל בגן ארנון ביסוד המעלה. סביב בית הקפה יש עצי פרי, צמחייה ופינות ישיבה, והוא מציע קפה, מאפים, יין ומטעמים נוספים.</p><p>במקום ניתן לשבת ולעבוד עם חיבור Wi-Fi וחשמל, ומתקיימות בו גם פעילויות ומפגשים קהילתיים. לפי האתר הרשמי המקום כשר למהדרין.</p><p><strong>מומלץ להתקשר לפני ההגעה</strong> ולוודא את שעות הפעילות, במיוחד בחורף.</p>',
  'category'=>'batei-kafe','address'=>'רחוב השזיף פינת המגדל','city'=>'יסוד המעלה','lat'=>'33.0596945','lng'=>'35.6125568',
  'phone'=>'050-277-0613','website'=>'https://arnons-place.co.il/','place_id'=>'ChIJd1H9FQChHhURr33cM2c8b38','rating'=>4.9,'reviews'=>41,
  'source'=>'https://arnons-place.co.il/'
 ],
 [
  'title'=>'רנטו בר יין ומעדנייה',
  'excerpt'=>'בר יין ומעדנייה בכיכר המושבה ביסוד המעלה, עם יינות, גבינות, דליקטסים ומוצרים לאירוח.',
  'content'=>'<p>רנטו הוא בר יין ומעדנייה בכיכר המושבה ביסוד המעלה. במקום תמצאו מבחר יינות, גבינות, דליקטסים ומוצרים שנבחרו לאירוח ולבילוי באווירה גלילית.</p><p>המעדנייה הוקמה על ידי מושיקו ממן ושיר בן־מובחר לאחר שנים של עשייה קולינרית, ומשלבת חנות עם אפשרות לישיבה ולאירוח.</p><p><strong>מומלץ להתקשר לפני ההגעה</strong> ולוודא את שעות הפעילות.</p>',
  'category'=>'restaurants-sub','address'=>'כיכר המושבה, המייסדים 130','city'=>'יסוד המעלה','lat'=>'33.0573462','lng'=>'35.6090588',
  'phone'=>'054-670-0937','website'=>'https://renatodeli.co.il/','place_id'=>'ChIJ0853nvohHBURd5AEOnjKy_A','rating'=>4.7,'reviews'=>72,
  'source'=>'https://renatodeli.co.il/terms/'
 ],
];
$created=[];
foreach($records as $r){
 $q=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','title'=>$r['title'],'posts_per_page'=>1,'fields'=>'ids']);
 if($q->posts){ throw new RuntimeException('Exact title already exists: '.$r['title'].' ID '.$q->posts[0]); }
 $id=wp_insert_post(['post_type'=>'gd_place','post_status'=>'pending','post_title'=>$r['title'],'post_excerpt'=>$r['excerpt'],'post_content'=>$r['content']],true);
 if(is_wp_error($id)){ throw new RuntimeException($id->get_error_message()); }
 wp_set_object_terms($id,$r['category'],'gd_placecategory',false);
 wp_set_object_terms($id,'galil','gn_region',false);
 update_post_meta($id,'geodir_post_latitude',$r['lat']);
 update_post_meta($id,'geodir_post_longitude',$r['lng']);
 update_post_meta($id,'_gonorth_verification_source_url',$r['source']);
 update_post_meta($id,'_gonorth_verification_checked_at','2026-10-01T00:00:00+03:00');
 $result=GoNorth_Place_Data::update($id,[
  'street'=>$r['address'],'city'=>$r['city'],'phone'=>$r['phone'],'website'=>$r['website'],
  'google_place_id'=>$r['place_id'],'google_rating'=>$r['rating'],'google_review_count'=>$r['reviews']
 ],true,true);
 if(is_wp_error($result)){ wp_trash_post($id); throw new RuntimeException($result->get_error_message()); }
 $created[]=['ID'=>$id,'title'=>$r['title'],'status'=>get_post_status($id)];
}
echo wp_json_encode(['created'=>$created],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
