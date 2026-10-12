<?php
if (!defined('ABSPATH')) { exit; }
$records=[
 ['title'=>'שוורצע (משק שוורץ)','excerpt'=>'מחלבת בוטיק וחנות משק בשדה אליעזר, עם גבינות כבשים בעבודת יד ומוצרים גליליים נוספים.','content'=>'<p>שוורצע, משק שוורץ, היא מחלבה משפחתית וחנות משק בשדה אליעזר. במקום מיוצרות גבינות כבשים בעבודת יד, לצד מוצרים מקומיים נוספים שנמכרים בחנות.</p><p>לפי מקור עסקי אזורי, משפחת שוורץ מייצרת במקום גבינות זה שלושה דורות. המידע אומת גם מול רשומת Google Places הפעילה של העסק.</p><p><strong>מומלץ להתקשר לפני ההגעה</strong> כדי לוודא זמינות ושעות פתיחה.</p>','category'=>'ochel','address'=>'הזית 47','city'=>'שדה אליעזר','lat'=>'33.047803','lng'=>'35.5612092','phone'=>'054-800-7606','website'=>'','place_id'=>'ChIJM5EjMbQhHBURl3TFbMs_-PE','rating'=>4.9,'reviews'=>87,'source'=>'https://easy.co.il/page/26550011'],
 ['title'=>'Peti Peti — פטי פטי','excerpt'=>'מסעדה אסייתית כשרה בצומת שדה אליעזר, עם סושי, מנות חמות, מנות לחלוקה וקוקטיילים.','content'=>'<p>Peti Peti היא מסעדה אסייתית כשרה בצומת שדה אליעזר, סמוך לתחנת הדלק. התפריט כולל סושי, מנות אסייתיות חמות, מנות לחלוקה וקוקטיילים.</p><p>המסעדה מתאימה לארוחה זוגית, משפחתית או קבוצתית במהלך טיול בעמק החולה ובגליל העליון. ניתן להגיע גם דרך קישור ההזמנות המקוון של העסק.</p><p><strong>מומלץ להזמין מקום ולבדוק את שעות הפעילות</strong> לפני ההגעה.</p>','category'=>'restaurants-sub','address'=>'תחנת דלק סונול','city'=>'שדה אליעזר','lat'=>'33.0463051','lng'=>'35.5706815','phone'=>'04-681-7855','website'=>'https://tabitisrael.co.il/tabit-order?siteName=petipeti&step=enter','place_id'=>'ChIJmeF0nmUfHBURwC8gAyBgeJg','rating'=>4.3,'reviews'=>190,'source'=>'https://www.bordo100.co.il/מסעדה/Peti+Peti+|+פטי+פטי.html']
];
$created=[];
foreach($records as $r){
 $q=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','title'=>$r['title'],'posts_per_page'=>1,'fields'=>'ids']);
 if($q->posts){throw new RuntimeException('Exact title exists: '.$r['title']);}
 $mq=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','meta_query'=>[['key'=>'google_place_id','value'=>$r['place_id']]],'posts_per_page'=>1,'fields'=>'ids']);
 if($mq->posts){throw new RuntimeException('Place ID exists: '.$r['place_id']);}
 $id=wp_insert_post(['post_type'=>'gd_place','post_status'=>'pending','post_title'=>$r['title'],'post_excerpt'=>$r['excerpt'],'post_content'=>$r['content']],true);
 if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}
 wp_set_object_terms($id,$r['category'],'gd_placecategory',false); wp_set_object_terms($id,'galil','gn_region',false);
 update_post_meta($id,'geodir_post_latitude',$r['lat']); update_post_meta($id,'geodir_post_longitude',$r['lng']);
 update_post_meta($id,'_gonorth_verification_source_url',$r['source']); update_post_meta($id,'_gonorth_verification_source_type',$r['website']?'official_ordering_plus_regional':'alternative_regional_plus_google');
 update_post_meta($id,'_gonorth_verification_checked_at','2026-10-01T20:15:00+03:00');
 $data=['street'=>$r['address'],'city'=>$r['city'],'phone'=>$r['phone'],'google_place_id'=>$r['place_id'],'google_rating'=>$r['rating'],'google_review_count'=>$r['reviews']];
 if($r['website']){$data['website']=$r['website'];}
 $result=GoNorth_Place_Data::update($id,$data,true,true);
 if(is_wp_error($result)){wp_trash_post($id);throw new RuntimeException($result->get_error_message());}
 $created[]=['ID'=>$id,'title'=>$r['title'],'status'=>get_post_status($id)];
}
echo wp_json_encode(['created'=>$created],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;

