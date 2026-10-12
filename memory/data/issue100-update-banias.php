<?php
if(!defined('ABSPATH')){exit;}
$id=191;
$content='<p>שמורת טבע נחל חרמון, המוכרת כבניאס, משלבת מים זורמים, צמחיית נחלים, עתיקות ומסלולי הליכה למרגלות החרמון. בשמורה שני מתחמי ביקור מרכזיים: אזור המעיינות והעתיקות, ואזור מפל הבניאס והשביל התלוי. כדאי לבחור מראש את נקודת ההתחלה ואת אורך המסלול.</p><h2>מפל הבניאס והשביל התלוי</h2><p>ממתחם המפל יוצאים אל מפל הבניאס ואל השביל התלוי, המכונה גם הגשר התלוי. זהו אותו מקטע ביקור בשמורה, ולכן שני השמות מתועדים כאן בדף אחד ולא כשני מקומות נפרדים. השביל עובר מעל הערוץ ומאפשר מבט קרוב על הזרימה.</p><h2>אזור המעיינות והמסלולים</h2><p>במתחם המעיינות פוגשים את מקורות הנחל ואת שרידי המקדשים והמבנים הקדומים. קיימים מסלולים באורכים שונים בין חלקי השמורה, וחלקם כוללים מדרגות, ירידות ושבילים שאינם נגישים.</p><h2>תכנון ובטיחות</h2><p>לפני ההגעה יש לבדוק באתר רשות הטבע והגנים את שעות הפתיחה, מצב המסלולים, הנחיות הביטחון והצורך בהרשמה. שעות וקטעי מסלול עשויים להשתנות לפי עונה, מזג אוויר וזרימה. השבילים עלולים להיות רטובים וחלקים; יש להישאר בשביל המסומן, להשגיח על ילדים ולציית לשילוט ולהוראות הצוות.</p><p>לעוד אפשרויות מים באזור ראו את <a href="https://gonorth.co.il/maslulei-mayim-batzafon/">מדריך מסלולי המים בצפון</a>.</p>';
$res=wp_update_post(['ID'=>$id,'post_excerpt'=>'שמורת נחל חרמון עם אזור המעיינות, מפל הבניאס והשביל התלוי — הגשר התלוי — במסלולים בדרגות קושי שונות.','post_content'=>$content],true);
if(is_wp_error($res)){throw new RuntimeException($res->get_error_message());}
update_post_meta($id,'_gonorth_verification_source_url','https://www.parks.org.il/wp-content/uploads/2018/06/%D7%91%D7%A0%D7%99%D7%90%D7%A1.pdf');
update_post_meta($id,'_gonorth_verification_source_type','official_rta');
update_post_meta($id,'_gonorth_verification_checked_at','2026-10-02T03:30:00+03:00');
echo wp_json_encode(['ID'=>$id,'status'=>get_post_status($id),'url'=>get_permalink($id),'content'=>get_post($id)->post_content,'data'=>GoNorth_Place_Data::get($id)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL;
