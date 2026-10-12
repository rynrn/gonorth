<?php
if(!defined('ABSPATH')){exit;}
$records=[
['title'=>'מלון אחוזת הירדן — לשעבר סביוני הגליל','excerpt'=>'מלון בוטיק ביסוד המעלה, המוכר גם בשמו הקודם סביוני הגליל, סמוך לעמק החולה ולאתרי הגליל העליון.','content'=>'<p>מלון אחוזת הירדן הוא מלון בוטיק ביסוד המעלה, במתחם שפעל בעבר בשם מלון סביוני הגליל. המיקום סמוך לעמק החולה ומתאים כבסיס לטיולים בגליל העליון וברמת הגולן.</p><p>במלון חדרי אירוח, בריכה ושטחים משותפים. פרטי החדרים, הארוחות והמתקנים עשויים להשתנות בין חבילות ועונות.</p><p><strong>מומלץ ליצור קשר ישירות עם המלון</strong> ולוודא זמינות, מחירים ושירותים לפני ההזמנה.</p>','category'=>'accommodation','address'=>'המייסדים','city'=>'יסוד המעלה','lat'=>'33.0441711','lng'=>'35.5731678','phone'=>'077-482-0502','website'=>'https://hotelboutique.co.il/','place_id'=>'ChIJ6TyNuOMfHBURJIWElcvImbQ','rating'=>4.0,'reviews'=>646,'source'=>'https://hotelboutique.co.il/'],
['title'=>'Norah Café — קפה נורה','excerpt'=>'בית קפה במתחם אחוזת ירדן ביסוד המעלה, עם ארוחות בוקר, כריכים, סלטים, קינוחים וישיבה בחוץ.','content'=>'<p>Norah Café, קפה נורה, הוא בית קפה במתחם אחוזת ירדן ביסוד המעלה. המקום מציע קפה, ארוחות בוקר, כריכים, סלטים וקינוחים, עם אזורי ישיבה בפנים ובחוץ.</p><p>המיקום מתאים לעצירה לפני או אחרי טיול בעמק החולה ובסביבת יסוד המעלה.</p><p><strong>מומלץ לבדוק את שעות הפעילות בעמוד הרשמי</strong> או להתקשר לפני ההגעה.</p>','category'=>'batei-kafe','address'=>'מתחם אחוזת ירדן','city'=>'יסוד המעלה','lat'=>'33.0457574','lng'=>'35.5732432','phone'=>'04-641-4814','website'=>'https://www.instagram.com/norah_cafe','place_id'=>'ChIJA8-i1YGhHhURZDxFMbqONQs','rating'=>4.5,'reviews'=>218,'source'=>'https://www.instagram.com/norah_cafe']
];
$created=[];
foreach($records as $r){
$q=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','title'=>$r['title'],'posts_per_page'=>1,'fields'=>'ids']);if($q->posts){throw new RuntimeException('Title exists');}
$mq=new WP_Query(['post_type'=>'gd_place','post_status'=>'any','meta_query'=>[['key'=>'google_place_id','value'=>$r['place_id']]],'posts_per_page'=>1,'fields'=>'ids']);if($mq->posts){throw new RuntimeException('Place ID exists');}
$id=wp_insert_post(['post_type'=>'gd_place','post_status'=>'pending','post_title'=>$r['title'],'post_excerpt'=>$r['excerpt'],'post_content'=>$r['content']],true);if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}
wp_set_object_terms($id,$r['category'],'gd_placecategory',false);wp_set_object_terms($id,'galil','gn_region',false);
update_post_meta($id,'geodir_post_latitude',$r['lat']);update_post_meta($id,'geodir_post_longitude',$r['lng']);update_post_meta($id,'_gonorth_verification_source_url',$r['source']);update_post_meta($id,'_gonorth_verification_checked_at','2026-10-01T21:00:00+03:00');
$res=GoNorth_Place_Data::update($id,['street'=>$r['address'],'city'=>$r['city'],'phone'=>$r['phone'],'website'=>$r['website'],'google_place_id'=>$r['place_id'],'google_rating'=>$r['rating'],'google_review_count'=>$r['reviews']],true,true);if(is_wp_error($res)){wp_trash_post($id);throw new RuntimeException($res->get_error_message());}
$created[]=['ID'=>$id,'title'=>$r['title'],'status'=>get_post_status($id)];
}
echo wp_json_encode(['created'=>$created],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;

