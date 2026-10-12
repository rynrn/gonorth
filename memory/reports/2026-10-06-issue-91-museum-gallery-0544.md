# #91 — פיילוט גלריה מורשית: מוזיאון האדם הקדמון (#494)

תאריך: 06.10.2026, 05:44 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/91; משימת־אב: https://github.com/rynrn/gonorth/issues/74. דף ציבורי: https://gonorth.co.il/places/%d7%9e%d7%95%d7%96%d7%99%d7%90%d7%95%d7%9f-%d7%94%d7%90%d7%93%d7%9d-%d7%94%d7%a7%d7%93%d7%9e%d7%95%d7%9f/

## תוצאה ומקורות

במקום השני מבין עשרת מועמדי הפיילוט הוחלפה תמונת הנושא הישנה (#496) בצילום ארכיוני מזוהה של חזית המוזיאון, והתווספה גלריה בת שתי תמונות. צילום הפנים מציג תצוגה במוזיאון. הכיתובים מדגישים שתמונות ישנות אינן מעידות על מצב הכניסה או התצוגה כיום. קרדיט, קישור לקובץ, רישיון והמרה ל־WebP גלויים לכל צילום. #496 נשמר לשחזור.

מקורות שנבדקו ב־06.10.2026:
- חזית: https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_3230_Tourism_in_Israel.jpg — אבישי טייכר / PikiWiki Israel, CC BY 2.5, https://creativecommons.org/licenses/by/2.5/ . תיאור הקובץ כללי; השיוך לקטגוריית המוזיאון והתאמה חזותית לצילום חזית עצמאי https://commons.wikimedia.org/wiki/File:Prehistoric_man_museum.jpg אישרו את הזיהוי. הקובץ הועלה ב־2009; מועד הצילום אינו מוכח.
- תצוגה: https://commons.wikimedia.org/wiki/File:Tel_Hai_123.jpg — Talmoryair, CC BY-SA 4.0, https://creativecommons.org/licenses/by-sa/4.0/ . תיאור וקטגוריה מזהים את המוזיאון; נתוני הקובץ מציינים צילום ב־2017.
- אתר המוזיאון: https://ugmp.co.il/en/home-eng/ — זיהוי המקום פעיל, לא בסיס לזכויות בצילומים.

שני הצילומים נבדקו חזותית. מנהל הסוכנים ותת־סוכן איכות תוכן אישרו באופן עצמאי את זיהוי התמונות, ALT, הכיתובים והייחוס לפני שינוי חי. המקור שומר ב־memory/work; הקבצים הוקטנו ללא חיתוך והומרו ל־WebP: חזית 1200×900, 207,712 בתים; פנים 1200×674, 94,814 בתים. הגרסאות ששירתו את Chrome במובייל: 30,514 ו־33,514 בתים. צילום הפנים הנגזר נשאר תחת CC BY-SA 4.0.

## שינוי ובדיקות

רמת סיכון L2; כותב קנוני WordPress/WP-CLI. snapshot טרום שינוי: memory/work/issue91-494-before.json ובשרת /home/claude/issue91-494-before.json, תמונת נושא #496. כל העובדות הדינמיות של המקום נשארו ללא שינוי; tools/validate-place-records.py עבר על נתוני המיקום הקיימים ומקור המוזיאון. שתי התמונות יובאו דרך --skip-copy לתיקייה ייעודית, בלי שינוי הרשאות של תיקיית ההעלאות החודשית; מזהי המדיה #529 ו־#530.

סקריפט memory/work/issue91-494-gallery.php עבר php -l ובדיקת מצב מקדים, ופעולת apply המוגנת ב־checksum שינתה רק post_content ותמונת נושא. מצב אחרי נשמר ב־memory/work/issue91-494-after.json ובשרת /home/claude/issue91-494-after.json. שחזור מוגן: wp eval-file /home/claude/issue91-494-gallery.php rollback --path=/var/www/gonorth --allow-root. הוא פועל רק אם checksum התוכן ותמונת הנושא תואמים למצב אחרי, מחזיר את התוכן ואת #496 ומשאיר קבצי מדיה חדשים ללא מחיקה.

בדיקות Chrome ברוחב 390 ו־1280: HTTP 200, H1 תקין, תמונת OG היא צילום החזית החדש, שתי תמונות גלריה נטענות, ALT/כיתובים וקישורי מקור/רישיון נכונים, srcset ו־lazy, ללא שגיאת JavaScript או גלישה אופקית. התמונה הישנה אינה מופיעה ברשימת תמונות הדף. צילום מסך נבדק חזותית: memory/work/issue91-494-gallery-mobile.png.

## גבולות והמשך

אין צילום עכשווי מאומת של הכניסה או התצוגה; הכיתובים מציגים אותן במפורש כתיעוד היסטורי. זהו מקום שני מתוך עשרה. הרשאות התמונות הישנות במדגם 20 המקומות עדיין אינן מוכחות; שמונה גלריות פיילוט נוספות דורשות תמונות מורשות או חריג מנומק. לא הייתה רכישה. זמן קבלת הרשאה מתצלומי עסק נותר unknown.

status: partial
scope: listing 494, two Commons assets, public mobile/desktop
risk_level: L2
canonical_writer: WordPress WP-CLI post/media APIs
changes_made: attachments 529/530, gallery in post_content, thumbnail 529
unchanged: old attachment 496, dynamic place facts
validation: content-quality review, PHP lint, place validator, canonical guard, Chrome 390/1280, visual screenshot
snapshot: memory/work/issue91-494-before.json
rollback: memory/work/issue91-494-gallery.php mode rollback, checksum-guarded
blockers: contemporary museum photos and eight other pilot galleries
remaining_risks: historic photos cannot document current entrance or exhibit layout; rights of legacy media unresolved
issue_recommendation: keep-open

